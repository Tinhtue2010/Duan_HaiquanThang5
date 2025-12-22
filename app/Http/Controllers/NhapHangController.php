<?php

namespace App\Http\Controllers;

use Yajra\DataTables\Facades\DataTables;
use App\Models\HangHoa;
use App\Models\HangHoaDaHuy;
use App\Models\HangTrongCont;
use App\Models\LoaiHang;
use App\Models\LoaiHinh;
use App\Models\NhapHang;
use App\Models\NhapHangDaHuy;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ToKhaiExport;
use App\Models\ChuHang;
use App\Models\CongChuc;
use App\Models\Container;
use App\Models\DoanhNghiep;
use App\Models\HaiQuan;
use App\Models\HangHoaSua;
use App\Models\NhapHangSua;
use App\Models\NiemPhong;
use App\Models\Seal;
use App\Models\TaiKhoan;
use App\Models\TienTrinh;
use App\Models\TheoDoiHangHoa;
use App\Models\XuatHang;
use App\Models\XuatHangCont;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class NhapHangController extends Controller
{
    public function danhSachToKhai()
    {
        $query = NhapHang::whereIn('trang_thai', ['1', '3', '10']);

        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $maDoanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->value('ma_doanh_nghiep');
            $query->where('ma_doanh_nghiep', $maDoanhNghiep);
        }

        $data = $query->orderBy('ngay_dang_ky', 'desc')->get();
        return view('nhap-hang.quan-ly-nhap-hang', compact('data'));
    }

    public function toKhaiDaNhapHang()
    {
        $statuses = ['2', '4', '7', '6', '5'];

        $query = NhapHang::whereIn('trang_thai', $statuses)
            ->with(['doanhNghiep' => function ($query) {
                $query->select('ma_doanh_nghiep', 'ten_doanh_nghiep');
            }])
            ->orderByDesc('so_to_khai_nhap');

        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $query->whereHas('doanhNghiep', function ($q) {
                $q->where('ma_tai_khoan', Auth::user()->ma_tai_khoan);
            });
        }
        $data = $query->get();
        return view('nhap-hang.to-khai-da-nhap-hang', compact('data'));
    }

    public function  toKhaiDaHuy()
    {
        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $data = NhapHangDaHuy::where('trang_thai', '0')
                ->orderBy('id_huy', 'desc')
                ->get();
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $maDoanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep;
            $data = NhapHangDaHuy::where('trang_thai', '0')
                ->where('ma_doanh_nghiep', $maDoanhNghiep)
                ->orderBy('id_huy', 'desc')
                ->get();
        }
        return view('nhap-hang.to-khai-da-huy-nhap', data: compact(var_name: 'data'));
    }

    public function themToKhaiNhap()
    {
        if (Auth::user()->loai_tai_khoan !== "Doanh nghiệp") {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            return redirect()->back();
        }
        $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
        $congChucs = CongChuc::all();
        return view('nhap-hang.them-to-khai-nhap', [
            'chuHangs' => ChuHang::all(),
            'haiQuans' => HaiQuan::all(),
            'doanhNghiep' => $doanhNghiep,
            'xuatXus' => $this->getXuatXu(),
            'donViTinhs' => $this->getDonViTinh(),
            'loaiHinhs' => LoaiHinh::all(),
            'loaiHangs' => LoaiHang::all(),
            'loaiHangFiles' => LoaiHang::all(),
            'hangHoaRows' => null,
            'congChucs' => $congChucs
        ]);
    }

    public function themToKhaiNhapSubmit(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $formattedDate = $this->formatDate($request->ngay_thong_quan);
                $nhapHang = NhapHang::find($request->so_to_khai_nhap);
                if ($nhapHang && $nhapHang->trang_thai != '0') {
                    return redirect()->back()->with('alert-danger', 'Số tờ khai nhập đã được sử dụng');
                }

                $rowsData = json_decode($request->rows_data, true);
                $containers = array_column($rowsData, 'so_container');
                $uniqueContainers = array_unique($containers);
                $so_containers = implode('; ', $uniqueContainers);

                $this->themNhapHang($request, $formattedDate, '1', $so_containers);
                $this->xuLyThemHangHoa($request);
                $this->themTienTrinh($request->so_to_khai_nhap, "Doanh nghiệp tạo tờ khai nhập hàng số " . $request->so_to_khai_nhap, '');

                return redirect()
                    ->route('nhap-hang.show', ['so_to_khai_nhap' => $request->so_to_khai_nhap])
                    ->with('alert-success', 'Thêm tờ khai mới thành công!');
            });
        } catch (\Exception $e) {
            Log::error('Error in themToKhaiNhapSubmit: ' . $e->getMessage());
            session()->flash('alert-danger', 'Có lỗi xảy ra trong hệ thống');
            return redirect()->back();
        }
    }

    public function thongTinToKhai($so_to_khai_nhap)
    {
        $nhapHang = NhapHang::find($so_to_khai_nhap);
        $hangHoaRows = $nhapHang->hangHoa;
        return view('nhap-hang.thong-tin-nhap-hang', [
            'nhapHang' => $nhapHang,
            'hangHoaRows' => $hangHoaRows,
            'soLuongSum' => $hangHoaRows->sum('so_luong_khai_bao'),
            'triGiaSum' => $hangHoaRows->sum('tri_gia'),
            'tienTrinhs' => TienTrinh::where('so_to_khai_nhap', $so_to_khai_nhap)
                ->leftJoin('cong_chuc', 'tien_trinh.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
                ->get(),
            'congChucs' => CongChuc::where('status', 1)->get(),
            'chuHangs' => ChuHang::all(),
            'seals' => Seal::where('trang_thai', 0)->get(),
            'containers' => HangHoa::where('so_to_khai_nhap', $so_to_khai_nhap)->groupBy('so_container_khai_bao')->get()
        ]);
    }

    private function themNhapHang($request, $formattedDate, $trang_thai, $so_containers)
    {
        $doanhNghiep = $this->getDoanhNghiepHienTai();
        NhapHang::insert([
            'so_to_khai_nhap' => $request->so_to_khai_nhap,
            'ma_chu_hang' => $request->ma_chu_hang,
            'ma_loai_hinh' => $request->ma_loai_hinh,
            'ma_hai_quan' => $request->ma_hai_quan,
            'ma_doanh_nghiep' => $doanhNghiep->ma_doanh_nghiep,
            'ngay_dang_ky' => $formattedDate,
            'ngay_thong_quan' => $formattedDate,
            'trang_thai' => $trang_thai,
            'phuong_tien_vt_nhap' => $request->phuong_tien_vt_nhap,
            'ptvt_ban_dau' => $request->phuong_tien_vt_nhap,
            'trong_luong' => $request->trong_luong,
            'ten_doan_tau' => $request->ten_doan_tau,
            'container_ban_dau' => $so_containers,
            'created_at' => now(),
        ]);
    }

    public function suaToKhaiNhap($so_to_khai_nhap)
    {
        if ($this->kiemTraTinhTrangXuatHang($so_to_khai_nhap)) {
            return redirect()->back()->with('alert-danger', 'Không thể sửa tờ khai này do đã chọn hàng để xuất');
        }
        $nhapHang = NhapHang::findOrFail($so_to_khai_nhap);
        if ($nhapHang->trang_thai == '3') {
            $nhapHang = NhapHangSua::where('so_to_khai_nhap', $so_to_khai_nhap)
                ->orderByDesc('ma_nhap_sua') // or ->orderBy('id', 'desc')
                ->first();
            $hangHoa = HangHoaSua::leftJoin('cong_chuc', 'cong_chuc.ma_cong_chuc', '=', 'hang_hoa_sua.cong_chuc_go_seal')
                ->where('hang_hoa_sua.ma_nhap_sua', $nhapHang->ma_nhap_sua)
                ->get();
        } else {
            $nhapHang = NhapHang::findOrFail($so_to_khai_nhap);
            $hangHoa = HangHoa::leftJoin('cong_chuc', 'cong_chuc.ma_cong_chuc', '=', 'hang_hoa.cong_chuc_go_seal')
                ->where('hang_hoa.so_to_khai_nhap', $so_to_khai_nhap)
                ->get();
        }
        $doanhNghiep = $this->getDoanhNghiepHienTai();
        return view('nhap-hang.sua-to-khai-nhap', [
            'nhapHang' => $nhapHang,
            'hangHoaRows' => $hangHoa,
            'xuatXus' => $this->getXuatXu(),
            'donViTinhs' => $this->getDonViTinh(),
            'haiQuans' => HaiQuan::all(),
            'doanhNghiep' => $doanhNghiep,
            'loaiHinhs' => LoaiHinh::all(),
            'loaiHangs' => LoaiHang::all(),
            'chuHangs' => ChuHang::all(),
            'congChucs' => CongChuc::all(),
            'xuatXu' => $hangHoa->first()->xuat_xu ?? '',
        ]);
    }
    public function suaToKhaiNhapCongChuc($so_to_khai_nhap)
    {
        $nhapHang = NhapHang::findOrFail($so_to_khai_nhap);
        $congChucs = CongChuc::where('ma_cong_chuc', '!=', 0)->where('status', 1)->get();
        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            // if ($this->kiemTraTinhTrangXuatHang($so_to_khai_nhap)) {
            //     return redirect()->back()->with('alert-danger', 'Không thể sửa tờ khai này do đã chọn hàng để xuất');
            // }
            $doanhNghiep = $this->getDoanhNghiepHienTai();

            return view('nhap-hang.sua-to-khai-nhap-cong-chuc', [
                'nhapHang' => $nhapHang,
                'hangHoaRows' => $nhapHang->hangHoa,
                'xuatXus' => $this->getXuatXu(),
                'donViTinhs' => $this->getDonViTinh(),
                'haiQuans' => HaiQuan::all(),
                'doanhNghiep' => $doanhNghiep,
                'loaiHinhs' => LoaiHinh::all(),
                'loaiHangs' => LoaiHang::all(),
                'chuHangs' => ChuHang::all(),
                'congChucs' => $congChucs,
            ]);
        } else {
            return view('nhap-hang.sua-to-khai-nhap-cong-chuc', [
                'nhapHang' => $nhapHang,
                'hangHoaRows' => $nhapHang->hangHoa,
                'xuatXus' => $this->getXuatXu(),
                'donViTinhs' => $this->getDonViTinh(),
                'haiQuans' => HaiQuan::all(),
                'loaiHinhs' => LoaiHinh::all(),
                'loaiHangs' => LoaiHang::all(),
                'chuHangs' => ChuHang::all(),
                'congChucs' => $congChucs,
            ]);
        }
    }
    public function suaToKhaiNhapChiTiet($so_to_khai_nhap)
    {
        $nhapHang = NhapHang::findOrFail($so_to_khai_nhap);
        $hangHoaRows = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('niem_phong', 'hang_trong_cont.so_container', '=', 'niem_phong.so_container')
            ->where('nhap_hang.so_to_khai_nhap', $so_to_khai_nhap)
            ->get();
        return view('nhap-hang.sua-to-khai-nhap-chi-tiet', [
            'nhapHang' => $nhapHang,
            'hangHoaRows' => $hangHoaRows,
        ]);
    }
    public function xemSuaToKhai(Request $request)
    {
        $nhapHang = NhapHang::find($request->so_to_khai_nhap);
        $nhapHangSua = NhapHangSua::where('so_to_khai_nhap', $request->so_to_khai_nhap)->orderByDesc('ma_nhap_sua')->first();

        $hangHoaRows = HangHoa::leftJoin('cong_chuc', 'cong_chuc.ma_cong_chuc', 'hang_hoa.cong_chuc_go_seal')
            ->where('hang_hoa.so_to_khai_nhap', $request->so_to_khai_nhap)
            ->get();
        $hangHoaSuaRows = HangHoaSua::leftJoin('cong_chuc', 'cong_chuc.ma_cong_chuc', 'hang_hoa_sua.cong_chuc_go_seal')
            ->where('hang_hoa_sua.ma_nhap_sua', $nhapHangSua->ma_nhap_sua)
            ->get();

        $doanhNghiep = DoanhNghiep::find($nhapHang->ma_doanh_nghiep);
        $is_chi_xem = false;
        return view('nhap-hang.xem-sua-nhap-hang', compact('nhapHang', 'nhapHangSua', 'hangHoaRows', 'hangHoaSuaRows', 'doanhNghiep', 'is_chi_xem'));
    }
    public function xemSuaNhapTheoLan(Request $request)
    {
        $nhapHangSua = NhapHangSua::find($request->ma_nhap_sua);
        $nhapHang = NhapHangSua::where('so_to_khai_nhap', $nhapHangSua->so_to_khai_nhap)
            ->where('ma_nhap_sua', '<', $nhapHangSua->ma_nhap_sua)
            ->orderByDesc('ma_nhap_sua')
            ->first();

        $hangHoaRows = HangHoa::leftJoin('cong_chuc', 'cong_chuc.ma_cong_chuc', 'hang_hoa.cong_chuc_go_seal')
            ->where('hang_hoa.so_to_khai_nhap', $request->so_to_khai_nhap)
            ->get();
        $hangHoaSuaRows = HangHoaSua::leftJoin('cong_chuc', 'cong_chuc.ma_cong_chuc', 'hang_hoa_sua.cong_chuc_go_seal')
            ->where('hang_hoa_sua.ma_nhap_sua', $nhapHangSua->ma_nhap_sua)
            ->get();
        $is_chi_xem = true;
        $doanhNghiep = DoanhNghiep::find($nhapHang->ma_doanh_nghiep);
        return view('nhap-hang.xem-sua-nhap-hang', compact('nhapHang', 'nhapHangSua', 'hangHoaRows', 'hangHoaSuaRows', 'doanhNghiep', 'is_chi_xem'));
    }

    public function suaToKhaiNhapSubmit(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $nhapHang = $this->kiemTraSoToKhaiNhapSua($request);
                $rowsData = json_decode($request->rows_data, true);
                $containers = array_column($rowsData, 'so_container');
                $uniqueContainers = array_unique($containers);
                $so_containers = implode('; ', $uniqueContainers);

                if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                    if ($nhapHang->trang_thai == '1') {
                        $this->suaNhapHangDangChoDuyet($request, $nhapHang, $so_containers);
                    } else if ($nhapHang->trang_thai == '2') {
                        $this->suaNhapHangDaDuyet($request, so_containers: $so_containers);
                    } else {
                        $this->suaNhapHangCongChuc($request, $so_containers);
                    }
                } else {
                    $this->suaNhapHangCongChuc($request, $so_containers);
                }


                return redirect()
                    ->route('nhap-hang.show', ['so_to_khai_nhap' => $request->so_to_khai_nhap])
                    ->with('alert-success', 'Sửa tờ khai thành công!');
            });
        } catch (\Exception $e) {
            Log::error('Error in suaToKhaiNhapSubmit: ' . $e->getMessage());
            return redirect()->back()->with('alert-danger', 'Có lỗi xảy ra trong hệ thống');
        }
    }

    private function suaNhapHangCongChuc($request, $so_containers)
    {
        $rowsData = json_decode($request->rows_data, true);
        $formattedDate = $this->formatDate($request->ngay_thong_quan);
        $nhapHang = NhapHang::find($request->so_to_khai_nhap);

        foreach ($rowsData as $row) {
            $hangHoa = HangHoa::find($row['ma_hang']);
            if (Auth::user()->loai_tai_khoan == "Cán bộ công chức" || Auth::user()->loai_tai_khoan == "Admin") {
                $this->suaSoContainerSoLuong($hangHoa, $row);
                HangHoa::find($row['ma_hang'])->update([
                    'so_luong_khai_bao' => $row['so_luong'],
                ]);
                $ptvt_goc = $nhapHang->ptvt_ban_dau;
                $ptvt_moi = $request->phuong_tien_vt_nhap;
                if ($ptvt_goc != $ptvt_moi) {
                    $this->suaTauBanDau($request->so_to_khai_nhap, $ptvt_goc, $ptvt_moi);
                }
                HangHoa::find($row['ma_hang'])->update([
                    'ten_hang' => $row['ten_hang'],
                    'loai_hang' => $row['loai_hang'],
                    'xuat_xu' => $request->xuat_xu,
                    'so_container_khai_bao' => $row['so_container'],
                    'so_luong_khai_bao' => $row['so_luong'],
                    'don_vi_tinh' => $row['don_vi_tinh'],
                    'don_gia' => $row['don_gia'],
                    'tri_gia' => $row['tri_gia'],
                ]);
                NhapHang::find($request->so_to_khai_nhap)->update([
                    'ma_chu_hang' => $request->ma_chu_hang,
                    'ma_loai_hinh' => $request->ma_loai_hinh,
                    'ma_hai_quan' => $request->ma_hai_quan,
                    'ngay_dang_ky' => $formattedDate,
                    'ngay_thong_quan' => $formattedDate,
                    'trong_luong' => $request->trong_luong,
                    'ten_doan_tau' => $request->ten_doan_tau,

                    'phuong_tien_vt_nhap' => $request->phuong_tien_vt_nhap,
                    'ptvt_ban_dau' => $request->phuong_tien_vt_nhap,
                    'container_ban_dau' => $so_containers,
                ]);
            } else {
                HangHoa::find($row['ma_hang'])->update([
                    // 'ten_hang' => $row['ten_hang'],
                    'loai_hang' => $row['loai_hang'],
                    // 'xuat_xu' => $row['xuat_xu'],
                    // 'so_container_khai_bao' => $row['so_container'],
                    // 'so_luong_khai_bao' => $row['so_luong'],
                    'don_vi_tinh' => $row['don_vi_tinh'],
                    'don_gia' => $row['don_gia'],
                    'tri_gia' => $row['tri_gia'],
                ]);
                NhapHang::find($request->so_to_khai_nhap)->update([
                    'ma_chu_hang' => $request->ma_chu_hang,
                    'ma_loai_hinh' => $request->ma_loai_hinh,
                    'ma_hai_quan' => $request->ma_hai_quan,
                    'ngay_dang_ky' => $formattedDate,
                    'ngay_thong_quan' => $formattedDate,
                    'trong_luong' => $request->trong_luong,

                    // 'phuong_tien_vt_nhap' => $request->phuong_tien_vt_nhap,
                    // 'ptvt_ban_dau' => $request->phuong_tien_vt_nhap,
                    // 'container_ban_dau' => $so_containers,
                    // 'created_at' => now(),
                ]);
            }
            //No
            // if ($hangHoa->so_container_khai_bao != $row['so_container'] || $nhapHang->phuong_tien_vt_nhap != $request->phuong_tien_vt_nhap) {
            //     $this->suaXuatHangCont($request, $nhapHang, $row);
            // }
        }

        // $this->xuLyContainer($request);
        $this->themTienTrinh($request->so_to_khai_nhap, "Công chức đã sửa tờ khai nhập số " . $request->so_to_khai_nhap, $this->getCongChucHienTai()->ma_cong_chuc ?? '');
    }
    public function suaTauBanDau($so_to_khai_nhap, $so_tau_goc, $so_tau_moi)
    {

        $nhapHang = NhapHang::find($so_to_khai_nhap);
        XuatHangCont::where('so_to_khai_nhap', $so_to_khai_nhap)
            ->where('phuong_tien_vt_nhap', $so_tau_goc)
            ->update([
                'phuong_tien_vt_nhap' => $so_tau_moi,
            ]);

        NhapHang::find($so_to_khai_nhap)->update([
            'phuong_tien_vt_nhap' => $so_tau_moi,
            'ptvt_ban_dau' => $so_tau_moi,
        ]);
        NiemPhong::where('so_container', $nhapHang->container_ban_dau)
            ->update([
                'phuong_tien_vt_nhap' => $so_tau_moi,
            ]);
    }
    private function suaXuatHangCont($request, $nhapHang, $row)
    {
        $hangTrongConts = HangTrongCont::where('ma_hang', $row['ma_hang'])
            ->get();
        foreach ($hangTrongConts as $hangTrongCont) {
            XuatHangCont::where('so_to_khai_nhap', $request->so_to_khai_nhap)
                ->where('ma_hang_cont', $hangTrongCont->ma_hang_cont)
                ->where('so_container', $hangTrongCont->so_container)
                ->where('phuong_tien_vt_nhap', $request->phuong_tien_vt_nhap)
                ->update([
                    'so_container' => $row['so_container'],
                    'phuong_tien_vt_nhap' => $nhapHang->phuong_tien_vt_nhap,
                ]);
        }
    }
    private function suaSoContainerSoLuong($hangHoa, $row)
    {
        if ($hangHoa->so_luong_khai_bao != $row['so_luong']) {
            $so_luong_thay_doi = $row['so_luong'] - $hangHoa->so_luong_khai_bao;
            HangTrongCont::where('ma_hang', $row['ma_hang'])
                ->where('is_da_chuyen_cont', 0)
                ->update([
                    'ma_hang' => $hangHoa->ma_hang,
                    // 'so_container' => $row['so_container'],
                    'so_luong' => DB::raw('so_luong + ' . (int) $so_luong_thay_doi),
                ]);
        } else {
            HangTrongCont::where('ma_hang', $row['ma_hang'])
                ->update([
                    'ma_hang' => $hangHoa->ma_hang,
                    // 'so_container' => $row['so_container'],
                ]);
        }
    }


    private function suaNhapHangDangChoDuyet($request, $nhapHang, $so_containers)
    {
        $trang_thai = $nhapHang->trang_thai;
        $formattedDate = $this->formatDate($request->ngay_thong_quan);

        $rowsData = json_decode($request->rows_data, true);

        $containers = array_column($rowsData, 'so_container');
        $uniqueContainers = array_unique($containers);
        $so_containers = implode('; ', $uniqueContainers);

        $this->xoaNhapHangCu($request);
        $this->themNhapHang($request, $formattedDate, $trang_thai, $so_containers);
        $this->xuLyThemHangHoaSua($request, $trang_thai);
        $this->themTienTrinh($request->so_to_khai_nhap, "Doanh nghiệp sửa tờ khai nhập hàng số " . $request->so_to_khai_nhap, '');
    }



    private function suaNhapHangDaDuyet($request, $so_containers)
    {
        $doanhNghiep = $this->getDoanhNghiepHienTai();
        $formattedDate = $this->formatDate($request->ngay_thong_quan);
        $rowsData = json_decode($request->rows_data, true);
        $nhapHang = NhapHang::find($request->so_to_khai_nhap);

        if ($nhapHang->trang_thai == '3') {
            $nhapHangSua = NhapHangSua::where('so_to_khai_nhap', $request->so_to_khai_nhap)
                ->orderByDesc('ma_nhap_sua')
                ->first();
            $nhapHangSua->update([
                'ma_loai_hinh' => $request->ma_loai_hinh,
                'ma_chu_hang' => $request->ma_chu_hang,
                'ma_hai_quan' => $request->ma_hai_quan,
                'ma_doanh_nghiep' => $doanhNghiep->ma_doanh_nghiep,
                'ngay_dang_ky' => $formattedDate,
                'ngay_thong_quan' => $formattedDate,
                'phuong_tien_vt_nhap' => $request->phuong_tien_vt_nhap,
                'ptvt_ban_dau' => $request->phuong_tien_vt_nhap,
                'trong_luong' => $request->trong_luong,
                'ten_doan_tau' => $request->ten_doan_tau,
                'container_ban_dau' => $so_containers,
                'ma_cong_chuc' => $request->ma_cong_chuc,
            ]);
            HangHoaSua::where('ma_nhap_sua', $nhapHangSua->ma_nhap_sua)->delete();
            foreach ($rowsData as $row) {
                HangHoaSua::insert([
                    'ma_hang' => $row['ma_hang'],
                    'ten_hang' => $row['ten_hang'],
                    'loai_hang' => $row['loai_hang'],
                    'xuat_xu' => $request->xuat_xu,
                    'so_luong_khai_bao' => $row['so_luong'],
                    'don_vi_tinh' => $row['don_vi_tinh'],
                    'don_gia' => $row['don_gia'],
                    'tri_gia' => $row['tri_gia'],
                    'so_container_khai_bao' => trim($row['so_container']),
                    'so_seal' => $row['so_seal'],
                    'so_seal_dinh_vi' => $row['so_seal_dinh_vi'],
                    'cong_chuc_go_seal' => $row['ma_cong_chuc_go_seal'],
                    'so_to_khai_nhap' => $request->so_to_khai_nhap,
                    'ma_nhap_sua' => $nhapHangSua->ma_nhap_sua,
                ]);
            }
            $nhapHang->save();
        } else {
            $nhapHang->trang_thai = '3';
            $nhapHang->save();
            $isExists = NhapHangSua::where('so_to_khai_nhap', $request->so_to_khai_nhap)->exists();
            if (!$isExists) {
                $nhapHang = NhapHang::find($request->so_to_khai_nhap);
                $hangHoas = HangHoa::where('so_to_khai_nhap', $request->so_to_khai_nhap)->get();
                $nhapHangSua = NhapHangSua::create($nhapHang->toArray());
                foreach ($hangHoas as $hangHoa) {
                    HangHoaSua::insert([
                        'ma_hang' => $hangHoa->ma_hang,
                        'ten_hang' => $hangHoa->ten_hang,
                        'loai_hang' => $hangHoa->loai_hang,
                        'xuat_xu' => $request->xuat_xu,
                        'so_luong_khai_bao' => $hangHoa->so_luong_khai_bao,
                        'don_vi_tinh' => $hangHoa->don_vi_tinh,
                        'don_gia' => $hangHoa->don_gia,
                        'tri_gia' => $hangHoa->tri_gia,
                        'so_container_khai_bao' => $hangHoa->so_container,
                        'so_seal' => $hangHoa->so_seal,
                        'so_seal_dinh_vi' => $hangHoa->so_seal_dinh_vi,
                        'cong_chuc_go_seal' => $hangHoa->cong_chuc_go_seal,
                        'so_to_khai_nhap' => $request->so_to_khai_nhap,
                        'ma_nhap_sua' => $nhapHangSua->ma_nhap_sua,
                    ]);
                }
            }

            $nhapHangSua = NhapHangSua::create([
                'so_to_khai_nhap' => $request->so_to_khai_nhap,
                'ma_loai_hinh' => $request->ma_loai_hinh,
                'ma_chu_hang' => $request->ma_chu_hang,
                'ma_hai_quan' => $request->ma_hai_quan,
                'ma_doanh_nghiep' => $doanhNghiep->ma_doanh_nghiep,
                'ngay_dang_ky' => $formattedDate,
                'ngay_thong_quan' => $formattedDate,
                'trang_thai' => '2',
                'phuong_tien_vt_nhap' => $request->phuong_tien_vt_nhap,
                'ptvt_ban_dau' => $request->phuong_tien_vt_nhap,
                'trong_luong' => $request->trong_luong,
                'ten_doan_tau' => $request->ten_doan_tau,
                'container_ban_dau' => $so_containers,
                'ma_cong_chuc' => $request->ma_cong_chuc,
                'created_at' => now(),
            ]);

            foreach ($rowsData as $row) {
                HangHoaSua::insert([
                    'ma_hang' => $row['ma_hang'],
                    'ten_hang' => $row['ten_hang'],
                    'loai_hang' => $row['loai_hang'],
                    'xuat_xu' => $request->xuat_xu,
                    'so_luong_khai_bao' => $row['so_luong'],
                    'don_vi_tinh' => $row['don_vi_tinh'],
                    'don_gia' => $row['don_gia'],
                    'tri_gia' => $row['tri_gia'],
                    'so_container_khai_bao' => trim($row['so_container']),
                    'so_seal' => $row['so_seal'],
                    'so_seal_dinh_vi' => $row['so_seal_dinh_vi'],
                    'cong_chuc_go_seal' => $row['ma_cong_chuc_go_seal'],
                    'so_to_khai_nhap' => $request->so_to_khai_nhap,
                    'ma_nhap_sua' => $nhapHangSua->ma_nhap_sua,
                ]);
            }
        }

        $this->xuLyContainer($request);
        $this->themTienTrinh($request->so_to_khai_nhap, "Doanh nghiệp yêu cầu sửa tờ khai nhập hàng số " . $request->so_to_khai_nhap, '');
    }

    public function huySuaYeuCau(Request $request)
    {
        $nhapHang = NhapHang::find($request->so_to_khai_nhap);
        $nhapHang->trang_thai = '2';
        $nhapHang->save();

        $nhapHangSua = NhapHangSua::where('so_to_khai_nhap', $request->so_to_khai_nhap)->orderByDesc('ma_nhap_sua')->first();
        HangHoaSua::where('ma_nhap_sua', $nhapHangSua->ma_nhap_sua)->delete();
        $nhapHangSua->delete();

        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $nhapHang->ghi_chu = "Doanh nghiệp hủy yêu cầu sửa: " . $request->ghi_chu;
            $this->themTienTrinh($nhapHang->so_to_khai_nhap, "Doanh nghiệp hủy yêu cầu tờ khai nhập số " . $nhapHang->so_to_khai_nhap, '');
        } else {
            $nhapHang->ghi_chu = "Công chức từ chối yêu cầu sửa: " . $request->ghi_chu;
            $this->themTienTrinh($nhapHang->so_to_khai_nhap, "Công chức từ chối yêu cầu tờ khai nhập số " . $nhapHang->so_to_khai_nhap, $this->getCongChucHienTai()->ma_cong_chuc ?? '');
        }

        $nhapHang->save();
        session()->flash('alert-success', 'Hủy yêu cầu sửa thành công!');
        return redirect()->route('nhap-hang.show', ['so_to_khai_nhap' => $nhapHang->so_to_khai_nhap]);
    }

    public function duyetSuaYeuCau(Request $request)
    {
        try {
            DB::beginTransaction();
            $nhapHangSua = NhapHangSua::where('so_to_khai_nhap', $request->so_to_khai_nhap)->orderByDesc('ma_nhap_sua')->first();
            $hangHoaSuas = HangHoaSua::where('ma_nhap_sua', $nhapHangSua->ma_nhap_sua)->get();

            $nhapHang = NhapHang::find($request->so_to_khai_nhap);
            if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
                $hangHoas =  HangHoa::where('so_to_khai_nhap', $request->so_to_khai_nhap)->get();
                foreach ($hangHoas as $hangHoa) {
                    HangTrongCont::where('ma_hang', $hangHoa->ma_hang)->delete();
                    $hangHoa->delete();
                }

                $nhapHang->update([
                    'so_to_khai_nhap' => $nhapHangSua->so_to_khai_nhap,
                    'ma_loai_hinh' => $nhapHangSua->ma_loai_hinh,
                    'ma_chu_hang' => $nhapHangSua->ma_chu_hang,
                    'ma_hai_quan' => $nhapHangSua->ma_hai_quan,
                    'ma_doanh_nghiep' => $nhapHangSua->ma_doanh_nghiep,
                    'ngay_dang_ky' => $nhapHangSua->ngay_dang_ky,
                    'ngay_thong_quan' => $nhapHangSua->ngay_thong_quan,
                    'trang_thai' => '2',
                    'phuong_tien_vt_nhap' => $nhapHangSua->phuong_tien_vt_nhap,
                    'ptvt_ban_dau' => $nhapHangSua->ptvt_ban_dau,
                    'trong_luong' => $nhapHangSua->trong_luong,
                    'ten_doan_tau' => $nhapHangSua->ten_doan_tau,
                    'container_ban_dau' => $nhapHangSua->container_ban_dau,
                    // 'ma_cong_chuc' => $nhapHangSua->ma_cong_chuc,
                ]);
                foreach ($hangHoaSuas as $hangHoaSua) {
                    $hangHoa = HangHoa::create([
                        'ten_hang' => $hangHoaSua->ten_hang,
                        'loai_hang' => $hangHoaSua->loai_hang,
                        'xuat_xu' => $hangHoaSua->xuat_xu,
                        'so_luong_khai_bao' => $hangHoaSua->so_luong_khai_bao,
                        'don_vi_tinh' => $hangHoaSua->don_vi_tinh,
                        'don_gia' => $hangHoaSua->don_gia,
                        'tri_gia' => $hangHoaSua->tri_gia,
                        'so_container_khai_bao' => $hangHoaSua->so_container_khai_bao,
                        'so_to_khai_nhap' => $hangHoaSua->so_to_khai_nhap,
                        'so_seal' => $hangHoaSua->so_seal,
                        'so_seal_dinh_vi' => $hangHoaSua->so_seal_dinh_vi,
                        'cong_chuc_go_seal' => $hangHoaSua->cong_chuc_go_seal,
                    ]);
                    HangTrongCont::insert([
                        'ma_hang' => $hangHoa->ma_hang,
                        'so_container' => $hangHoa->so_container_khai_bao,
                        'so_luong' => $hangHoa->so_luong_khai_bao,
                    ]);
                    NiemPhong::where('so_container', $hangHoa->so_container_khai_bao)
                        ->update([
                            'phuong_tien_vt_nhap' => $nhapHangSua->phuong_tien_vt_nhap,
                            'ten_doan_tau' => $nhapHangSua->ten_doan_tau,
                        ]);
                }
                $this->themTienTrinh($request->so_to_khai_nhap, "Lãnh đạo đã duyệt yêu cầu sửa tờ khai nhập hàng số " . $request->so_to_khai_nhap, $this->getCongChucHienTai()->ma_cong_chuc ?? '');
            }

            DB::commit();
            return redirect()->route('nhap-hang.show', ['so_to_khai_nhap' => $request->so_to_khai_nhap]);
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetSuaYeuCau: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function suaSealDienTuToKhai(Request $request)
    {
        try {
            DB::beginTransaction();
            $nhapHang = NhapHang::find($request->so_to_khai_nhap);
            HangHoa::where('so_to_khai_nhap', $request->so_to_khai_nhap)
                ->where('so_container_khai_bao', $request->so_container_khai_bao)
                ->update([
                    'so_seal' => $request->so_seal,
                    'so_seal_dinh_vi' => $request->so_seal_dinh_vi,
                    'cong_chuc_go_seal' => $request->cong_chuc_go_seal,
                ]);
            $this->themTienTrinh($request->so_to_khai_nhap, "Đã sửa seal định vị của container số " . $request->so_container_khai_bao, $this->getCongChucHienTai()->ma_cong_chuc ?? '');


            DB::commit();
            return redirect()->route('nhap-hang.show', ['so_to_khai_nhap' => $request->so_to_khai_nhap]);
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetSuaYeuCau: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function thongTinToKhaiHuy($id_huy)
    {
        $nhapHang = NhapHangDaHuy::find($id_huy);
        $hangHoaRows = $nhapHang->hangHoaDaHuy;
        $soLuongSum = $hangHoaRows->sum('so_luong_khai_bao');
        $triGiaSum = $hangHoaRows->sum('tri_gia');
        $tienTrinhs = null;
        $congChucs = CongChuc::where('is_chi_xem', 0)->where('status', 1)->get();
        $chuHangs = ChuHang::all();
        return view('nhap-hang.thong-tin-nhap-hang', compact('nhapHang', 'hangHoaRows', 'soLuongSum', 'triGiaSum', 'tienTrinhs', 'congChucs', 'chuHangs')); // Pass data to the view
    }
    public function toKhaiDaQua14Ngay()
    {
        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức" || Auth::user()->loai_tai_khoan == "Admin") {
            $nhapHangs = NhapHang::where('ngay_tiep_nhan', '<=', now()->subDays(14))
                ->where('ngay_tiep_nhan', '>=', Carbon::create(2025, 8, 15))
                ->orderBy('ngay_tiep_nhan', 'asc')
                ->where('trang_thai', '2')
                ->get();
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $maDoanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep;
            $nhapHangs = NhapHang::where('ngay_tiep_nhan', '<=', now()->subDays(14))
                ->where('trang_thai', '2')
                ->where('ngay_tiep_nhan', '>=', Carbon::create(2025, 8, 15))
                ->where('ma_doanh_nghiep', $maDoanhNghiep)
                ->orderBy('ngay_tiep_nhan', 'asc')
                ->get();
        }
        return view('nhap-hang.to-khai-da-qua-14-ngay', compact('nhapHangs'));
    }

    public function viTriHangHienTai($so_to_khai_nhap)
    {
        $hangTrongConts = HangTrongCont::with(['hangHoa.nhapHang'])
            ->leftJoin('niem_phong', 'hang_trong_cont.so_container', '=', 'niem_phong.so_container')
            ->whereHas('hangHoa.nhapHang', function ($query) use ($so_to_khai_nhap) {
                $query->where('so_to_khai_nhap', $so_to_khai_nhap);
            })
            ->select(
                'hang_trong_cont.ma_hang_cont',
                'hang_trong_cont.ma_hang',
                'hang_trong_cont.so_luong',
                'hang_trong_cont.so_container',
                'hang_trong_cont.is_da_chuyen_cont',
                'niem_phong.phuong_tien_vt_nhap',
            )
            ->distinct()
            ->get();

        $sum = $hangTrongConts->sum('so_luong');
        $nhapHang = NhapHang::find($so_to_khai_nhap);
        return view('nhap-hang.vi-tri-hang-hien-tai', compact('hangTrongConts', 'nhapHang'));
    }

    public function phieuXuatCuaToKhai($so_to_khai_nhap)
    {
        $xuatHangs = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->join('xuat_hang_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->where('nhap_hang.so_to_khai_nhap', $so_to_khai_nhap)
            ->groupBy('xuat_hang.so_to_khai_xuat')
            ->orderBy('xuat_hang.so_to_khai_xuat', 'desc')
            ->get();
        return view('nhap-hang.phieu-xuat-cua-to-khai', compact('xuatHangs', 'so_to_khai_nhap'));
    }

    public function duyetToKhaiNhap(Request $request)
    {
        $nhapHang = NhapHang::findOrFail($request->so_to_khai_nhap);
        if ($nhapHang->trang_thai == "1") {
            try {
                return DB::transaction(function () use ($request, $nhapHang) {
                    $hangHoas = HangHoa::where('so_to_khai_nhap', $request->so_to_khai_nhap)->get();
                    $congChuc = $this->getCongChucHienTai();

                    $hangTrongContData = $hangHoas->map(function ($hangHoa) use ($nhapHang) {
                        return [
                            'ma_hang' => $hangHoa->ma_hang,
                            'so_container' => $hangHoa->so_container_khai_bao,
                            'so_luong' => $hangHoa->so_luong_khai_bao,
                            'so_seal' => $hangHoa->so_seal,
                        ];
                    })->toArray();

                    $insertData = array_map(function ($item) {
                        unset($item['so_seal']);
                        return $item;
                    }, $hangTrongContData);

                    HangTrongCont::insert($insertData);

                    foreach ($hangTrongContData as $hangTrongCont) {
                        $this->xuLyTauContainer($hangTrongCont['so_container'], $hangTrongCont['so_seal'], $nhapHang->phuong_tien_vt_nhap, $nhapHang->ten_doan_tau);
                        // $this->xuLySeal($hangTrongCont['so_container'], $hangTrongCont['so_seal'], $request->phuong_tien_vt_nhap);
                    }
                    $nhapHang->update([
                        'trang_thai' => '2',
                        'ngay_tiep_nhan' => now(),
                        'ma_cong_chuc' => $congChuc->ma_cong_chuc,
                    ]);

                    $this->themTienTrinh($request->so_to_khai_nhap, "Cán bộ công chức đã duyệt tờ khai, phân công cho cán bộ công chức " . $congChuc->ten_cong_chuc, $congChuc->ma_cong_chuc);
                    session()->flash('alert-success', 'Duyệt tờ khai thành công!');
                    return redirect()->route('nhap-hang.quan-ly-nhap-hang');
                });
            } catch (\Exception $e) {
                Log::error('Error in duyetToKhai: ' . $e->getMessage());

                return redirect()->back()
                    ->with('alert-error', 'Có lỗi xảy ra trong quá trình xử lý. Vui lòng thử lại.')
                    ->withInput();
            }
        }
        session()->flash('alert-success', 'Duyệt tờ khai thành công!');
        return redirect()->route('nhap-hang.quan-ly-nhap-hang');
    }

    public function yeuCauHuyToKhai(Request $request, $so_to_khai_nhap)
    {
        $nhapHang = NhapHang::findOrFail($so_to_khai_nhap);
        if ($nhapHang->trang_thai == 1) {
            $huyNhapHang = $this->thucHienHuy($so_to_khai_nhap);
            return redirect()->route('nhap-hang.show-huy', ['id_huy' => $huyNhapHang->id_huy])
                ->with('alert-success', 'Hủy tờ khai thành công!');
        } else {
            $nhapHang->update(['ghi_chu' => $request->ghi_chu, 'trang_thai' => '10']);
            return redirect()->back()->with('alert-success', 'Yêu cầu hủy tờ khai thành công!');
        }
    }
    public function thuHoiHuyToKhai(Request $request, $so_to_khai_nhap)
    {
        $nhapHang = NhapHang::findOrFail($so_to_khai_nhap);
        $nhapHang->update(['trang_thai' => '2']);
        return redirect()->back()->with('alert-success', 'Thu hồi hủy tờ khai thành công!');
    }
    public function duyetHuyToKhai($so_to_khai_nhap)
    {
        try {
            return DB::transaction(function () use ($so_to_khai_nhap) {
                $huyNhapHang = $this->thucHienHuy($so_to_khai_nhap);
                return redirect()->route('nhap-hang.show-huy', ['id_huy' => $huyNhapHang->id_huy])
                    ->with('alert-success', 'Hủy tờ khai thành công!');
            });
        } catch (\Exception $e) {
            Log::error('Error huyToKhaiNhap: ' . $e->getMessage());

            return redirect()->back()
                ->with('alert-danger', 'Có lỗi xảy ra trong quá trình xử lý.')
                ->withInput();
        }
    }

    private function thucHienHuy($so_to_khai_nhap)
    {
        $nhapHang = NhapHang::findOrFail($so_to_khai_nhap);
        $nhapHang->update(['trang_thai' => '0']);

        $huyNhapHang = $this->themNhapHangDaHuy($nhapHang);
        $this->diChuyenHangHoaDaHuy($so_to_khai_nhap, $huyNhapHang->id_huy);
        $this->xoaThongTinToKhaiNhapDaHuy($so_to_khai_nhap, $nhapHang);

        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $congChuc = $this->getCongChucHienTai();
            $this->themTienTrinh($so_to_khai_nhap, "Cán bộ công chức đã hủy tờ khai nhập số " . $so_to_khai_nhap, $congChuc->ma_cong_chuc);
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $this->themTienTrinh($so_to_khai_nhap, "Doanh nghiệp đã hủy tờ khai nhập số " . $so_to_khai_nhap, '');
        }
        return $huyNhapHang;
    }
    private function kiemTraTinhTrangXuatHang($so_to_khai_nhap)
    {
        return XuatHangCont::join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->where('so_to_khai_nhap', $so_to_khai_nhap)
            ->where('trang_thai', '!=', '0')
            ->exists();
    }

    private function xoaNhapHangCu($request)
    {
        NhapHang::find($request->so_to_khai_nhap)->delete();
        HangHoa::where('so_to_khai_nhap', $request->so_to_khai_nhap)->delete();
    }

    private function xuLyThemHangHoaSua($request, $trang_thai)
    {
        $rowsData = json_decode($request->rows_data, true);

        foreach ($rowsData as $row) {
            $this->themLoaiHang($row);
            $hangHoa = $this->themHangHoa($request, $row);

            if ($trang_thai == "2") {
                $this->themHangTrongCont($hangHoa, $request);
            }
        }
    }
    private function themHangTrongCont($hangHoa, $request)
    {
        HangTrongCont::insert([
            'ma_hang' => $hangHoa->ma_hang,
            'so_container' => $hangHoa->so_container_khai_bao,
            'so_luong' => $hangHoa->so_luong_khai_bao,
        ]);
    }

    private function formatDate($date)
    {
        return DateTime::createFromFormat('d/m/Y', $date)->format('Y/m/d');
    }

    private function getCongChucHienTai()
    {
        return CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
    }

    private function themNhapHangDaHuy($nhapHang)
    {
        return NhapHangDaHuy::create($nhapHang->toArray());
    }

    private function diChuyenHangHoaDaHuy($so_to_khai_nhap, $id_huy)
    {
        $hangHoas = HangHoa::where('so_to_khai_nhap', $so_to_khai_nhap)->get();

        $hangHoaDaHuyData = $hangHoas->map(function ($hangHoa) use ($id_huy) {
            return [
                'ten_hang' => $hangHoa->ten_hang,
                'loai_hang' => $hangHoa->loai_hang,
                'xuat_xu' => $hangHoa->xuat_xu,
                'don_vi_tinh' => $hangHoa->don_vi_tinh,
                'don_gia' => $hangHoa->don_gia,
                'tri_gia' => $hangHoa->tri_gia,
                'so_luong_khai_bao' => $hangHoa->so_luong_khai_bao,
                'so_container_khai_bao' => $hangHoa->so_container_khai_bao,
                'id_huy' => $id_huy,
            ];
        })->toArray();

        HangHoaDaHuy::insert($hangHoaDaHuyData);
    }

    private function xoaThongTinToKhaiNhapDaHuy($so_to_khai_nhap, $nhapHang)
    {
        if ($nhapHang->trang_thai == '2') {
            HangTrongCont::join('hang_hoa', 'hang_hoa.ma_hang', 'hang_trong_cont.ma_hang')
                ->where('so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->delete();
        }

        $ma_hangs = HangHoa::where('so_to_khai_nhap', $so_to_khai_nhap)->pluck('ma_hang');
        HangTrongCont::whereIn('ma_hang', $ma_hangs)->delete();
        HangHoa::where('so_to_khai_nhap', $so_to_khai_nhap)->delete();

        NhapHang::where('so_to_khai_nhap', $so_to_khai_nhap)->delete();
        HangHoa::where('so_to_khai_nhap', $so_to_khai_nhap)->delete();
        TienTrinh::where('so_to_khai_nhap', $so_to_khai_nhap)->delete();
        TheoDoiHangHoa::where('so_to_khai_nhap', $so_to_khai_nhap)->delete();

        $so_to_khai_xuats = XuatHangCont::where('so_to_khai_nhap', $so_to_khai_nhap)->pluck('so_to_khai_xuat');
        XuatHangCont::where('so_to_khai_nhap', $so_to_khai_nhap)->delete();
        // XuatHang::whereIn('so_to_khai_xuat', $so_to_khai_xuats)->delete();
    }


    public function uploadFileNhapAjax(Request $request)
    {
        $file = $request->file('hys_file'); // The uploaded file
        $requiredColumns = ['tên hàng', 'lượng', 'đvt', 'trị giá (usd)']; // Required columns
        $optionalColumns = ['đơn giá (usd)']; // Optional column
        $extension = $file->getClientOriginalExtension();

        if ($extension === 'csv') {
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
        } elseif (in_array($extension, ['xls', 'xlsx'])) {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $csvData = [];

            foreach ($worksheet->getRowIterator() as $row) {
                $rowData = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowData[] = $cell->getFormattedValue();
                }
                $csvData[] = $rowData;
            }
        } else {
            return response("Không hỗ trợ định dạng file này, hệ thống chỉ hỗ trợ định dạng .xls và .csv");
        }
        $loai_hang = '';
        $so_container = '';
        $so_seal_dinh_vi = '';
        $so_seal = '';
        $ten_cong_chuc_go_seal = '';
        $ma_cong_chuc_go_seal = '';

        if ($request->loai_hang) {
            $loai_hang = $request->loai_hang;
        }
        if ($request->so_container) {
            $so_container = $request->so_container;
        }
        if ($request->so_seal_dinh_vi) {
            $so_seal_dinh_vi = $request->so_seal_dinh_vi;
        }
        if ($request->so_seal) {
            $so_seal = $request->so_seal;
        }
        if ($request->ten_cong_chuc_go_seal) {
            $ten_cong_chuc_go_seal = $request->ten_cong_chuc_go_seal;
        }
        if ($request->ma_cong_chuc_go_seal) {
            $ma_cong_chuc_go_seal = $request->ma_cong_chuc_go_seal;
        }


        $headerRowIndex = -1;
        $foundColumns = [];

        foreach ($csvData as $index => $row) {
            $normalizedRow = array_map('mb_strtolower', array_map('trim', $row));

            foreach (array_merge($requiredColumns, $optionalColumns) as $column) {
                if (collect($normalizedRow)->contains(fn($col) => is_string($col) && str_contains($col, $column))) {
                    $foundColumns[] = $column;
                }
            }

            if (count(array_intersect($requiredColumns, $foundColumns)) === count($requiredColumns)) {
                $headerRowIndex = $index;
                break;
            }
        }

        // Find missing columns
        $missingColumns = array_diff($requiredColumns, $foundColumns);

        if ($headerRowIndex === -1 || !empty($missingColumns)) {
            return response("Trong file thiếu các cột sau: " . implode(', ', $missingColumns));
        }


        $header = array_map('mb_strtolower', array_map('trim', $csvData[$headerRowIndex]));

        $mappedColumns = [];

        foreach (array_merge($requiredColumns, $optionalColumns) as $column) {
            $mappedColumns[$column] = collect($header)->search(fn($col) => is_string($col) && str_contains($col, $column));
        }

        // Optional columns default to -1 if missing
        foreach ($optionalColumns as $column) {
            if ($mappedColumns[$column] === false) {
                $mappedColumns[$column] = -1;
            }
        }

        // Prepare HangHoa records
        $data = [];
        foreach (array_slice($csvData, $headerRowIndex + 1) as $row) {
            if (!$row[$mappedColumns['tên hàng']]) {
                return response()->json(['data' => $data]);
            }

            $triGia = (float) str_replace(',', '', $row[$mappedColumns['trị giá (usd)']] ?? 0);

            // Check if 'đơn giá (usd)' exists in the row
            $donGia = $mappedColumns['đơn giá (usd)'] !== -1
                ? (float) str_replace(',', '', $row[$mappedColumns['đơn giá (usd)']] ?? 0)
                : null;

            $so_luong = (int) ($row[$mappedColumns['lượng']] ?? 0);

            $data[] = [
                'ten_hang'   => $row[$mappedColumns['tên hàng']] ?? '',
                'loai_hang'  => $loai_hang,
                'so_luong_khai_bao'   => $so_luong,
                'don_vi_tinh' => $row[$mappedColumns['đvt']] ?? '',
                'don_gia'    => $donGia ?? ($so_luong > 0 ? $triGia / $so_luong : 0), // Calculate if missing
                'tri_gia'    => $triGia,
                'so_container'    => $so_container,
                'so_seal'    => $so_seal,
                'so_seal_dinh_vi'    => $so_seal_dinh_vi,
                'ten_cong_chuc_go_seal'    => $ten_cong_chuc_go_seal,
                'ma_cong_chuc_go_seal'    => $ma_cong_chuc_go_seal,
            ];
        }
        return response()->json(['data' => $data]);
    }

    // private function xuLySeal($so_container, $so_seal, $phuong_tien_vt_nhap)
    // {
    //     $so_container_no_space = str_replace(' ', '', $so_container); // Remove spaces
    //     $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

    //     $record = NiemPhong::where('so_container',  $so_container)->first();

    //     if (!$record) {
    //         NiemPhong::insert([
    //             'so_container' => $so_container,
    //             'so_seal' => $so_seal,
    //             'ngay_niem_phong' => now(),
    //             'phuong_tien_vt_nhap' => $phuong_tien_vt_nhap,
    //         ]);
    //     } else {
    //         NiemPhong::whereIn('so_container',  [$so_container_no_space, $so_container_with_space])->update([
    //             'phuong_tien_vt_nhap' => $phuong_tien_vt_nhap,
    //             'so_seal' => $so_seal,
    //         ]);
    //     }
    // }

    private function kiemTraSoToKhaiNhapSua($request)
    {
        $nhapHang = NhapHang::find($request->so_to_khai_nhap);

        if ($nhapHang && $request->so_to_khai_nhap != $request->so_to_khai_nhap) {
            if ($nhapHang->trang_thai != '0') {
                throw new \Exception('Số tờ khai nhập đã được sử dụng');
            }
        }
        return $nhapHang;
    }

    private function getDoanhNghiepHienTai()
    {
        return DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->firstOrFail();
    }

    private function xuLyThemHangHoa($request)
    {
        $rowsData = json_decode($request->rows_data, true);

        foreach ($rowsData as $row) {
            $this->themLoaiHang($row);
            $this->themHangHoa($request, $row);
            $this->xuLyContainer($row['so_container']);
        }
    }

    private function themLoaiHang($row)
    {
        if (!LoaiHang::where('ten_loai_hang', $row['loai_hang'])->exists()) {
            LoaiHang::create([
                'ten_loai_hang' => $row['loai_hang'],
                'don_vi_tinh' => $row['don_vi_tinh'],
            ]);
        }
    }

    private function themHangHoa($request, $row)
    {
        return HangHoa::create([
            'ten_hang' => $row['ten_hang'],
            'loai_hang' => $row['loai_hang'],
            'xuat_xu' => $request->xuat_xu ?? '',
            'so_luong_khai_bao' => $row['so_luong'],
            'don_vi_tinh' => $row['don_vi_tinh'],
            'don_gia' => $row['don_gia'],
            'tri_gia' => $row['tri_gia'],
            'so_to_khai_nhap' => $request->so_to_khai_nhap,
            'so_container_khai_bao' => trim($row['so_container']),
            'so_seal' => $row['so_seal'],
            'so_seal_dinh_vi' => $row['so_seal_dinh_vi'],
            'cong_chuc_go_seal' => $row['ma_cong_chuc_go_seal'],
        ]);
    }
    private function xuLyContainer($so_container)
    {
        if (!Container::find($so_container)) {
            Container::insert([
                'so_container' => $so_container,
            ]);
        }
    }
    private function xuLyTauContainer($so_container, $so_seal, $ptvt, $ten_doan_tau)
    {
        if (!Container::find($so_container)) {
            Container::insert([
                'so_container' => $so_container,
            ]);
        }

        $so_container_no_space = str_replace(' ', '', $so_container); // Remove spaces
        $so_container_with_space = substr($so_container_no_space, 0, length: 4) . ' ' . substr($so_container_no_space, 4);

        $record = NiemPhong::where('so_container',  $so_container)->first();

        if ($so_seal == null) {
            if (!$record) {
                NiemPhong::insert([
                    'so_container' => $so_container,
                    'ngay_niem_phong' => now(),
                    'phuong_tien_vt_nhap' => $ptvt,
                    'ten_doan_tau' => $ten_doan_tau
                ]);
            } else {
                NiemPhong::whereIn('so_container',  [$so_container_no_space, $so_container_with_space])->update([
                    'phuong_tien_vt_nhap' => $ptvt,
                    'ten_doan_tau' => $ten_doan_tau
                ]);
            }
        } else {
            if (!$record) {
                NiemPhong::insert([
                    'so_container' => $so_container,
                    'so_seal' => $so_seal,
                    'ngay_niem_phong' => now(),
                    'phuong_tien_vt_nhap' => $ptvt,
                    'ten_doan_tau' => $ten_doan_tau
                ]);
            } else {
                NiemPhong::whereIn('so_container',  [$so_container_no_space, $so_container_with_space])->update([
                    'phuong_tien_vt_nhap' => $ptvt,
                    'so_seal' => $so_seal,
                    'ten_doan_tau' => $ten_doan_tau
                ]);
            }
        }
    }

    private function themTienTrinh($so_to_khai_nhap, $ten_cong_viec, $ma_cong_chuc)
    {
        TienTrinh::insert([
            'so_to_khai_nhap' => $so_to_khai_nhap,
            'ten_cong_viec' => $ten_cong_viec,
            'ngay_thuc_hien' => now(),
            'ma_cong_chuc' => $ma_cong_chuc
        ]);
    }
    private function getXuatXu()
    {
        $countries = [
            "China",
            "Taiwan",
            "MaCao",
            "HongKong",
            "Afghanistan",
            "Albania",
            "Algeria",
            "Andorra",
            "Angola",
            "Antigua and Barbuda",
            "Argentina",
            "Armenia",
            "Australia",
            "Austria",
            "Azerbaijan",
            "Bahamas",
            "Bahrain",
            "Bangladesh",
            "Barbados",
            "Belarus",
            "Belgium",
            "Belize",
            "Benin",
            "Bhutan",
            "Bolivia",
            "Bosnia and Herzegovina",
            "Botswana",
            "Brazil",
            "Brunei",
            "Bulgaria",
            "Burkina Faso",
            "Burundi",
            "Cabo Verde",
            "Cambodia",
            "Cameroon",
            "Canada",
            "Central African Republic",
            "Chad",
            "Chile",
            "Colombia",
            "Comoros",
            "Congo (Congo-Brazzaville)",
            "Congo (Congo-Kinshasa)",
            "Costa Rica",
            "Croatia",
            "Cuba",
            "Cyprus",
            "Czechia (Czech Republic)",
            "Denmark",
            "Djibouti",
            "Dominica",
            "Dominican Republic",
            "Ecuador",
            "Egypt",
            "El Salvador",
            "Equatorial Guinea",
            "Eritrea",
            "Estonia",
            "Eswatini (fmr. \"Swaziland\")",
            "Ethiopia",
            "Fiji",
            "Finland",
            "France",
            "Gabon",
            "Gambia",
            "Georgia",
            "Germany",
            "Ghana",
            "Greece",
            "Grenada",
            "Guatemala",
            "Guinea",
            "Guinea-Bissau",
            "Guyana",
            "Haiti",
            "Honduras",
            "Hungary",
            "Iceland",
            "India",
            "Indonesia",
            "Iran",
            "Iraq",
            "Ireland",
            "Israel",
            "Italy",
            "Ivory Coast",
            "Jamaica",
            "Japan",
            "Jordan",
            "Kazakhstan",
            "Kenya",
            "Kiribati",
            "Kosovo",
            "Kuwait",
            "Kyrgyzstan",
            "Laos",
            "Latvia",
            "Lebanon",
            "Lesotho",
            "Liberia",
            "Libya",
            "Liechtenstein",
            "Lithuania",
            "Luxembourg",
            "Madagascar",
            "Malawi",
            "Malaysia",
            "Maldives",
            "Mali",
            "Malta",
            "Marshall Islands",
            "Mauritania",
            "Mauritius",
            "Mexico",
            "Micronesia",
            "Moldova",
            "Monaco",
            "Mongolia",
            "Montenegro",
            "Morocco",
            "Mozambique",
            "Myanmar (formerly Burma)",
            "Namibia",
            "Nauru",
            "Nepal",
            "Netherlands",
            "New Zealand",
            "Nicaragua",
            "Niger",
            "Nigeria",
            "North Korea",
            "North Macedonia",
            "Norway",
            "Oman",
            "Pakistan",
            "Palau",
            "Palestine",
            "Panama",
            "Papua New Guinea",
            "Paraguay",
            "Peru",
            "Philippines",
            "Poland",
            "Portugal",
            "Qatar",
            "Romania",
            "Russia",
            "Rwanda",
            "Saint Kitts and Nevis",
            "Saint Lucia",
            "Saint Vincent and the Grenadines",
            "Samoa",
            "San Marino",
            "Sao Tome and Principe",
            "Saudi Arabia",
            "Senegal",
            "Serbia",
            "Seychelles",
            "Sierra Leone",
            "Singapore",
            "Slovakia",
            "Slovenia",
            "Solomon Islands",
            "Somalia",
            "South Africa",
            "South Korea",
            "South Sudan",
            "Spain",
            "Sri Lanka",
            "Sudan",
            "Suriname",
            "Sweden",
            "Switzerland",
            "Syria",
            "Tajikistan",
            "Tanzania",
            "Thailand",
            "Timor-Leste (East Timor)",
            "Togo",
            "Tonga",
            "Trinidad and Tobago",
            "Tunisia",
            "Turkey",
            "Turkmenistan",
            "Tuvalu",
            "Uganda",
            "Ukraine",
            "United Arab Emirates",
            "UAE",
            "United Kingdom",
            "United States of America",
            "Uruguay",
            "Uzbekistan",
            "Vanuatu",
            "Vatican City",
            "Venezuela",
            "Vietnam",
            "Yemen",
            "Zambia",
            "Zimbabwe"
        ];
        return $countries;
    }



    public function getDonViTinh()
    {
        $units = [
            "Kiện",
            "Hộp",
            "Bao",
            "PP",
            "Pallet",
            "Kiện/Hộp/Bao",
            "Thùng",
            "Đôi",
            "Tá",
            "Chục",
            "Cuộn",
            "Sợi",
            "Tờ",
            "Quyển",
            "Viên",
            "Vỉ",
            "Cặp",
            "Thẻ",
            "Lon",
            "Chai",
            "Ống",
            "Tuýp",
            "Bịch",
            "Miếng",
            "Tấm",
            "Cây",
            "Khối"
        ];
        return $units;
    }
    public function exportToKhaiNhap($so_to_khai_nhap)
    {
        $nhapHang = NhapHang::where('so_to_khai_nhap', $so_to_khai_nhap)->firstOrFail();
        $hangHoaRows = HangHoa::where('so_to_khai_nhap', $nhapHang->so_to_khai_nhap)->get();
        $fileName = 'Tờ khai nhập số ' . $nhapHang->so_to_khai_nhap . '.xlsx';

        return Excel::download(new ToKhaiExport($nhapHang, $hangHoaRows), $fileName);
    }

    public function lichSuSuaNhap($so_to_khai_nhap)
    {
        $nhapHangs = NhapHangSua::where('so_to_khai_nhap', $so_to_khai_nhap)->get();
        return view('nhap-hang.lich-su-sua-nhap', compact('nhapHangs'));
    }

    public function getNhapHangDaDuyets(Request $request)
    {
        if ($request->ajax()) {
            $statuses = ['2', '4', '7', '6', '5'];

            $query = NhapHang::whereIn('trang_thai', $statuses)
                ->join('doanh_nghiep', 'nhap_hang.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
                ->leftJoin('chu_hang', 'nhap_hang.ma_chu_hang', '=', 'chu_hang.ma_chu_hang')
                ->select(
                    'nhap_hang.so_to_khai_nhap',
                    'nhap_hang.ngay_dang_ky',
                    'nhap_hang.trang_thai',
                    'nhap_hang.created_at',
                    DB::raw('chu_hang.ten_chu_hang as ten_chu_hang'),
                    DB::raw('doanh_nghiep.ten_doanh_nghiep as ten_doanh_nghiep'),
                );

            if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                $query->where('doanh_nghiep.ma_tai_khoan', Auth::id());
            }

            return DataTables::eloquent($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];

                        $query->where(function ($q) use ($search) {
                            $q->orWhere('nhap_hang.so_to_khai_nhap', 'LIKE', "%{$search}%")
                                ->orWhereRaw("DATE_FORMAT(nhap_hang.ngay_dang_ky, '%d-%m-%Y') LIKE ?", ["%{$search}%"])
                                ->orWhere('chu_hang.ten_chu_hang', 'LIKE', "%{$search}%")
                                ->orWhere('doanh_nghiep.ten_doanh_nghiep', 'LIKE', "%{$search}%");
                        });
                    }
                })
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($nhapHang) {
                    return '';  // Will be automatically filled by DataTables
                })
                ->editColumn('ngay_dang_ky', function ($nhapHang) {
                    return Carbon::parse($nhapHang->ngay_dang_ky)->format('d-m-Y');
                })
                ->addColumn('ten_doanh_nghiep', function ($nhapHang) {
                    return $nhapHang->ten_doanh_nghiep ?? 'Unknown';
                })
                ->addColumn('ten_chu_hang', function ($nhapHang) {
                    return $nhapHang->ten_chu_hang ?? 'Unknown';
                })
                ->editColumn('trang_thai', function ($nhapHang) {
                    $status = trim($nhapHang->trang_thai);

                    $statusLabels = [
                        '2' => ['text' => 'Đã nhập hàng', 'class' => 'text-success'],
                        '4' => ['text' => 'Đã xuất hết', 'class' => 'text-success'],
                        '6' => ['text' => 'Quay về kho ban đầu', 'class' => 'text-success'],
                        '7' => ['text' => 'Đã bàn giao hồ sơ', 'class' => 'text-success'],
                        '5' => ['text' => 'Đã tiêu hủy', 'class' => 'text-danger'],
                        '3' => ['text' => 'Doanh nghiệp yêu cầu sửa tờ khai', 'class' => 'text-warning'],
                    ];

                    return isset($statusLabels[$status])
                        ? "<span class='{$statusLabels[$status]['class']}'>{$statusLabels[$status]['text']}</span>"
                        : '<span class="text-muted">Trạng thái không xác định</span>';
                })
                ->addColumn('action', function ($nhapHang) {
                    return '<a href="' . route('nhap-hang.show', $nhapHang->so_to_khai_nhap) . '" class="btn btn-primary btn-sm">Xem</a>';
                })
                ->rawColumns(['trang_thai', 'action'])
                ->make(true);
        }
    }
}
