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
    private function getDanhSachToKhai(string $trangThai, string $view)
    {
        $query = NhapHang::where('trang_thai', $trangThai);

        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $maDoanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->value('ma_doanh_nghiep');
            $query->where('ma_doanh_nghiep', $maDoanhNghiep);
        }

        $data = $query->orderBy('ngay_dang_ky', 'desc')->get();
        return view($view, compact('data'));
    }

    public function danhSachToKhai()
    {
        $query = NhapHang::whereIn('trang_thai', ['Đang chờ duyệt', 'Doanh nghiệp yêu cầu sửa tờ khai']);

        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $maDoanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->value('ma_doanh_nghiep');
            $query->where('ma_doanh_nghiep', $maDoanhNghiep);
        }

        $data = $query->orderBy('ngay_dang_ky', 'desc')->get();
        return view('nhap-hang.quan-ly-nhap-hang', compact('data'));
    }

    public function toKhaiDaNhapHang()
    {
        $statuses = ['Đã nhập hàng', 'Đã xuất hết', 'Đã bàn giao hồ sơ', 'Quay về kho ban đầu', 'Đã tiêu hủy'];

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
            $data = NhapHangDaHuy::where('trang_thai', 'Đã hủy')
                ->orderBy('id_huy', 'desc')
                ->get();
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $maDoanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep;
            $data = NhapHangDaHuy::where('trang_thai', 'Đã hủy')
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

        return view('nhap-hang.them-to-khai-nhap', [
            'chuHangs' => ChuHang::all(),
            'haiQuans' => HaiQuan::all(),
            'doanhNghiep' => $doanhNghiep,
            'loaiHinhs' => LoaiHinh::all(),
            'loaiHangs' => LoaiHang::all(),
            'loaiHangFiles' => LoaiHang::all(),
            'hangHoaRows' => null
        ]);
    }

    public function themToKhaiNhapSubmit(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $formattedDate = $this->formatDate($request->ngay_thong_quan);
                $nhapHang = NhapHang::find($request->so_to_khai_nhap);
                if ($nhapHang && $nhapHang->trang_thai != 'Đã hủy') {
                    return redirect()->back()->with('alert-danger', 'Số tờ khai nhập đã được sử dụng');
                }

                $this->themNhapHang($request, $formattedDate, 'Đang chờ duyệt');
                $this->xuLyThemHangHoa($request);
                $this->xuLyContainer($request);
                $this->xuLySeal($request, now());
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
            'congChucs' => CongChuc::where('is_chi_xem',0)->get(),
            'chuHangs' => ChuHang::all(),
            'seals' => Seal::where('trang_thai', 0)->get()
        ]);
    }

    private function getDoanhNghiepHienTai()
    {
        return DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->firstOrFail();
    }


    private function themNhapHang($request, $formattedDate, $trang_thai)
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
            'container_ban_dau' => $request->so_container,
            'created_at' => now(),
        ]);
    }
    private function xuLyThemHangHoa($request)
    {
        $rowsData = json_decode($request->rows_data, true);

        foreach ($rowsData as $row) {
            $this->themLoaiHang($row);
            $this->themHangHoa($request, $row);
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
            'xuat_xu' => $row['xuat_xu'],
            'so_luong_khai_bao' => $row['so_luong'],
            'don_vi_tinh' => $row['don_vi_tinh'],
            'don_gia' => $row['don_gia'],
            'tri_gia' => $row['tri_gia'],
            'so_to_khai_nhap' => $request->so_to_khai_nhap,
        ]);
    }
    private function xuLyContainer($request)
    {
        if (!Container::find($request->so_container)) {
            Container::insert([
                'so_container' => $request->so_container,
            ]);
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
    public function suaToKhaiNhap($so_to_khai_nhap)
    {
        if ($this->kiemTraTinhTrangXuatHang($so_to_khai_nhap)) {
            return redirect()->back()->with('alert-danger', 'Không thể sửa tờ khai này do đã chọn hàng để xuất');
        }

        $nhapHang = NhapHang::findOrFail($so_to_khai_nhap);
        $doanhNghiep = $this->getDoanhNghiepHienTai();

        return view('nhap-hang.sua-to-khai-nhap', [
            'nhapHang' => $nhapHang,
            'hangHoaRows' => $nhapHang->hangHoa,
            'haiQuans' => HaiQuan::all(),
            'doanhNghiep' => $doanhNghiep,
            'loaiHinhs' => LoaiHinh::all(),
            'loaiHangs' => LoaiHang::all(),
            'chuHangs' => ChuHang::all(),
        ]);
    }
    public function xemSuaToKhai(Request $request)
    {
        $nhapHang = NhapHang::find($request->so_to_khai_nhap);
        $nhapHangSua = NhapHangSua::find($request->so_to_khai_nhap);

        $hangHoaRows = HangHoa::where('so_to_khai_nhap', $request->so_to_khai_nhap)->get();
        $hangHoaSuaRows = HangHoaSua::where('so_to_khai_nhap', $request->so_to_khai_nhap)->get();

        $doanhNghiep = DoanhNghiep::find($nhapHang->ma_doanh_nghiep);
        return view('nhap-hang.xem-sua-nhap-hang', compact('nhapHang', 'nhapHangSua', 'hangHoaRows', 'hangHoaSuaRows', 'doanhNghiep'));
    }

    public function suaToKhaiNhapSubmit(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                // Validate and prepare data
                $formattedDate = $this->formatDate($request->ngay_thong_quan);
                $nhapHang = $this->kiemTraSoToKhaiNhapSua($request);
                if ($nhapHang->trang_thai == 'Đang chờ duyệt') {
                    $this->suaNhapHangDangChoDuyet($request, $nhapHang);
                } else {
                    $this->suaNhapHangDaDuyet($request);
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
    private function suaNhapHangDangChoDuyet($request, $nhapHang)
    {
        $trang_thai = $nhapHang->trang_thai;
        $formattedDate = $this->formatDate($request->ngay_thong_quan);
        $this->xoaNhapHangCu($request);
        $this->themNhapHang($request, $formattedDate, $trang_thai);
        $this->xuLyThemHangHoaSua($request, $trang_thai);

        $this->xuLyContainer($request);
        $this->themTienTrinh($request->so_to_khai_nhap, "Doanh nghiệp sửa tờ khai nhập hàng số " . $request->so_to_khai_nhap, '');
    }

    private function suaNhapHangDaDuyet($request)
    {
        $doanhNghiep = $this->getDoanhNghiepHienTai();
        $formattedDate = $this->formatDate($request->ngay_thong_quan);
        $nhapHang = NhapHang::find($request->so_to_khai_nhap);
        $nhapHang->trang_thai = 'Doanh nghiệp yêu cầu sửa tờ khai';
        $nhapHang->save();
        $nhapHangSua = NhapHangSua::create([
            'so_to_khai_nhap' => $request->so_to_khai_nhap,
            'ma_loai_hinh' => $request->ma_loai_hinh,
            'ma_chu_hang' => $request->ma_chu_hang,
            'ma_hai_quan' => $request->ma_hai_quan,
            'ma_doanh_nghiep' => $doanhNghiep->ma_doanh_nghiep,
            'ngay_dang_ky' => $formattedDate,
            'ngay_thong_quan' => $formattedDate,
            'trang_thai' => 'Đã nhập hàng',
            'phuong_tien_vt_nhap' => $request->phuong_tien_vt_nhap,
            'ptvt_ban_dau' => $request->phuong_tien_vt_nhap,
            'trong_luong' => $request->trong_luong,
            'container_ban_dau' => $request->so_container,
            'ma_cong_chuc' => $request->ma_cong_chuc,
            'created_at' => now(),
        ]);

        $rowsData = json_decode($request->rows_data, true);

        foreach ($rowsData as $row) {
            $this->themLoaiHang($row);
            HangHoaSua::insert([
                'ten_hang' => $row['ten_hang'],
                'loai_hang' => $row['loai_hang'],
                'xuat_xu' => $row['xuat_xu'],
                'so_luong_khai_bao' => $row['so_luong'],
                'don_vi_tinh' => $row['don_vi_tinh'],
                'don_gia' => $row['don_gia'],
                'tri_gia' => $row['tri_gia'],
                'so_to_khai_nhap' => $request->so_to_khai_nhap,
            ]);
        }
        $this->xuLyContainer($request);
        $this->themTienTrinh($request->so_to_khai_nhap, "Doanh nghiệp yêu cầu sửa tờ khai nhập hàng số " . $request->so_to_khai_nhap, '');
    }
    public function huySuaYeuCau(Request $request)
    {
        $nhapHang = NhapHang::find($request->so_to_khai_nhap);
        $nhapHang->trang_thai = 'Đã nhập hàng';
        $nhapHang->save();

        NhapHangSua::find($request->so_to_khai_nhap)->delete();
        HangHoaSua::where('so_to_khai_nhap', $request->so_to_khai_nhap)->delete();

        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $nhapHang->ghi_chu = "Doanh nghiệp hủy yêu cầu sửa: " . $request->ghi_chu;
            $this->themTienTrinh($nhapHang->so_to_khai_nhap, "Doanh nghiệp hủy yêu cầu tờ khai nhập số " . $nhapHang->so_to_khai_nhap, '');
        } else {
            $nhapHang->ghi_chu = "Công chức từ chối yêu cầu sửa: " . $request->ghi_chu;
            $this->themTienTrinh($nhapHang->so_to_khai_nhap, "Công chức từ chối yêu cầu tờ khai nhập số " . $nhapHang->so_to_khai_nhap, $this->getCongChucHienTai()->ma_cong_chuc);
        }

        $nhapHang->save();
        session()->flash('alert-success', 'Hủy yêu cầu sửa thành công!');
        return redirect()->route('nhap-hang.show', ['so_to_khai_nhap' => $nhapHang->so_to_khai_nhap]);
    }

    public function duyetSuaYeuCau(Request $request)
    {
        try {
            DB::beginTransaction();
            $nhapHangSua = NhapHangSua::find($request->so_to_khai_nhap);
            $hangHoaSuas = HangHoaSua::where('so_to_khai_nhap', $request->so_to_khai_nhap)->get();

            NhapHang::find($request->so_to_khai_nhap)->delete();
            $hangHoas =  HangHoa::where('so_to_khai_nhap', $request->so_to_khai_nhap)->get();
            foreach ($hangHoas as $hangHoa) {
                HangTrongCont::where('ma_hang', $hangHoa->ma_hang)->delete();
                $hangHoa->delete();
            }

            $nhapHang = NhapHang::create([
                'so_to_khai_nhap' => $nhapHangSua->so_to_khai_nhap,
                'ma_loai_hinh' => $nhapHangSua->ma_loai_hinh,
                'ma_chu_hang' => $nhapHangSua->ma_chu_hang,
                'ma_hai_quan' => $nhapHangSua->ma_hai_quan,
                'ma_doanh_nghiep' => $nhapHangSua->ma_doanh_nghiep,
                'ngay_dang_ky' => $nhapHangSua->ngay_dang_ky,
                'ngay_thong_quan' => $nhapHangSua->ngay_thong_quan,
                'trang_thai' => 'Đã nhập hàng',
                'phuong_tien_vt_nhap' => $nhapHangSua->phuong_tien_vt_nhap,
                'ptvt_ban_dau' => $nhapHangSua->ptvt_ban_dau,
                'trong_luong' => $nhapHangSua->trong_luong,
                'container_ban_dau' => $nhapHangSua->container_ban_dau,
                'ma_cong_chuc' => $nhapHangSua->ma_cong_chuc,
                'created_at' => now(),
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
                    'so_to_khai_nhap' => $request->so_to_khai_nhap,
                ]);
                HangTrongCont::insert([
                    'ma_hang' => $hangHoa->ma_hang,
                    'so_container' => $nhapHangSua->container_ban_dau,
                    'so_luong' => $hangHoa->so_luong_khai_bao,
                ]);
            }

            $this->themTienTrinh($request->so_to_khai_nhap, "Công chức đã duyệt yêu cầu sửa tờ khai nhập hàng số " . $request->so_to_khai_nhap, $this->getCongChucHienTai()->ma_cong_chuc);

            $nhapHangSua = NhapHangSua::find($request->so_to_khai_nhap)->delete();
            $hangHoaSuas = HangHoaSua::where('so_to_khai_nhap', $request->so_to_khai_nhap)->delete();

            DB::commit();
            return redirect()->route('nhap-hang.show', ['so_to_khai_nhap' => $request->so_to_khai_nhap]);
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetSuaYeuCau: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    private function kiemTraTinhTrangXuatHang($so_to_khai_nhap)
    {
        return XuatHangCont::join('xuat_hang', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->where('so_to_khai_nhap', $so_to_khai_nhap)
            ->where('trang_thai', '!=', 'Đã hủy')
            ->exists();
    }

    private function kiemTraSoToKhaiNhapSua($request)
    {
        $nhapHang = NhapHang::find($request->so_to_khai_nhap);

        if ($nhapHang && $request->so_to_khai_nhap != $request->so_to_khai_nhap) {
            if ($nhapHang->trang_thai != 'Đã hủy') {
                throw new \Exception('Số tờ khai nhập đã được sử dụng');
            }
        }
        return $nhapHang;
    }

    private function xoaHangTrongCont($nhapHang)
    {
        HangTrongCont::join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->where('hang_hoa.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
            ->delete();
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

            if ($trang_thai == "Đã nhập hàng") {
                $this->themHangTrongCont($hangHoa, $request);
            }
        }
    }
    private function themHangTrongCont($hangHoa, $request)
    {
        HangTrongCont::insert([
            'ma_hang' => $hangHoa->ma_hang,
            'so_container' => $request->so_container,
            'so_luong' => $hangHoa->so_luong_khai_bao,
        ]);
    }

    private function formatDate($date)
    {
        return DateTime::createFromFormat('d/m/Y', $date)->format('Y/m/d');
    }

    public function thongTinToKhaiHuy($id_huy)
    {
        // $nhapHang = NhapHang::find($so_to_khai_nhap);
        // $hangHoaRows = $nhapHang->hangHoa;
        // return view('nhap-hang.thong-tin-nhap-hang', [
        //     'nhapHang' => $nhapHang,
        //     'hangHoaRows' => $hangHoaRows,
        //     'soLuongSum' => $hangHoaRows->sum('so_luong_khai_bao'),
        //     'triGiaSum' => $hangHoaRows->sum('tri_gia'),
        //     'tienTrinhs' => TienTrinh::where('so_to_khai_nhap', $so_to_khai_nhap)
        //         ->leftJoin('cong_chuc', 'tien_trinh.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
        //         ->get(),
        //     'congChucs' => CongChuc::where('is_chi_xem',0)->get(),
        //     'chuHangs' => ChuHang::all(),
        //     'seals' => Seal::where('trang_thai', 0)->get()
        // ]);

        $nhapHang = NhapHangDaHuy::find($id_huy);
        $hangHoaRows = $nhapHang->hangHoaDaHuy;
        $soLuongSum = $hangHoaRows->sum('so_luong_khai_bao');
        $triGiaSum = $hangHoaRows->sum('tri_gia');
        $tienTrinhs = null;
        $congChucs = CongChuc::where('is_chi_xem',0)->get();
        $chuHangs = ChuHang::all();
        return view('nhap-hang.thong-tin-nhap-hang', compact('nhapHang', 'hangHoaRows', 'soLuongSum', 'triGiaSum', 'tienTrinhs', 'congChucs', 'chuHangs')); // Pass data to the view
    }

    public function viTriHangHienTai($so_to_khai_nhap)
    {
        $hangTrongConts = HangTrongCont::with(['hangHoa.nhapHang'])
            ->whereHas('hangHoa.nhapHang', function ($query) use ($so_to_khai_nhap) {
                $query->where('so_to_khai_nhap', $so_to_khai_nhap);
            })
            ->select(
                'hang_trong_cont.ma_hang_cont',
                'hang_trong_cont.ma_hang',
                'hang_trong_cont.so_luong',
                'hang_trong_cont.so_container',
                'hang_trong_cont.is_da_chuyen_cont'
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
        if ($nhapHang->trang_thai == "Đang chờ duyệt") {
            try {
                return DB::transaction(function () use ($request, $nhapHang) {
                    $hangHoas = HangHoa::where('so_to_khai_nhap', $request->so_to_khai_nhap)->get();
                    $congChuc = $this->getCongChucHienTai();

                    $hangTrongContData = $hangHoas->map(function ($hangHoa) use ($nhapHang) {
                        return [
                            'ma_hang' => $hangHoa->ma_hang,
                            'so_container' => $nhapHang->container_ban_dau,
                            'so_luong' => $hangHoa->so_luong_khai_bao,
                        ];
                    })->toArray();

                    HangTrongCont::insert($hangTrongContData);

                    $nhapHang->update([
                        'trang_thai' => 'Đã nhập hàng',
                        'ma_cong_chuc' => $congChuc->ma_cong_chuc,
                    ]);

                    // $this->xuLySealMoi($nhapHang, $request);
                    $this->themTienTrinh($request->so_to_khai_nhap, "Cán bộ công chức đã duyệt tờ khai, phân công cho cán bộ công chức " . $congChuc->ten_cong_chuc, $congChuc->ma_cong_chuc);
                    // return redirect()->back()->with('alert-success', 'Trạng thái đã được cập nhật thành công!');

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

    public function huyToKhai(Request $request, $so_to_khai_nhap)
    {
        try {
            return DB::transaction(function () use ($request, $so_to_khai_nhap) {
                $nhapHang = NhapHang::findOrFail($so_to_khai_nhap);
                $nhapHang->update(['ghi_chu' => $request->ghi_chu, 'trang_thai' => 'Đã hủy']);

                $huyNhapHang = $this->themNhapHangDaHuy($nhapHang);
                $this->diChuyenHangHoaDaHuy($so_to_khai_nhap, $huyNhapHang->id_huy);
                $this->xoaThongTinToKhaiNhapDaHuy($so_to_khai_nhap, $nhapHang);

                if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
                    $congChuc = $this->getCongChucHienTai();
                    $this->themTienTrinh($so_to_khai_nhap, "Cán bộ công chức đã hủy tờ khai nhập số " . $so_to_khai_nhap, $congChuc->ma_cong_chuc);
                } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                    $this->themTienTrinh($so_to_khai_nhap, "Doanh nghiệp đã hủy tờ khai nhập số " . $so_to_khai_nhap, '');
                }

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
                'id_huy' => $id_huy,
            ];
        })->toArray();

        HangHoaDaHuy::insert($hangHoaDaHuyData);
    }

    private function xoaThongTinToKhaiNhapDaHuy($so_to_khai_nhap, $nhapHang)
    {
        if ($nhapHang->trang_thai == 'Đã nhập hàng') {
            HangTrongCont::where('so_container', $nhapHang->container_ban_dau)->delete();
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
        $xuat_xu = '';
        $loai_hang = '';

        if ($request->xuat_xu) {
            $xuat_xu = $request->xuat_xu;
        }
        if ($request->loai_hang) {
            $loai_hang = $request->loai_hang;
        }


        // Find the header row
        $headerRowIndex = -1;
        $foundColumns = [];
        foreach ($csvData as $index => $row) {
            $normalizedRow = array_map('mb_strtolower', array_map('trim', $row));

            foreach ($requiredColumns as $column) {
                if (collect($normalizedRow)->contains(fn($col) => is_string($col) && str_contains($col, $column))) {
                    $foundColumns[] = $column;
                }
            }

            if (count($foundColumns) === count($requiredColumns)) {
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
        foreach ($requiredColumns as $column) {
            $mappedColumns[$column] = collect($header)->search(function ($col) use ($column) {
                return is_string($col) && str_contains($col, $column);
            }) ?? -1;
        }

        // Prepare HangHoa records
        $data = [];
        foreach (array_slice($csvData, $headerRowIndex + 1) as $row) {
            if (!$row[$mappedColumns['tên hàng']]) {
                return response()->json(['data' => $data]);
            }
            $triGia = (float) str_replace(',', '', $row[$mappedColumns['trị giá (usd)']] ?? 0);
            $so_luong = (int) $row[$mappedColumns['lượng']] ?? 0;
            $data[] = [
                'ten_hang'   => $row[$mappedColumns['tên hàng']] ?? '',
                'loai_hang'  => $loai_hang,
                'xuat_xu'    => $xuat_xu,
                'so_luong_khai_bao'   => $so_luong,
                'don_vi_tinh' => $row[$mappedColumns['đvt']] ?? '',
                'don_gia'    => $triGia / $so_luong,
                'tri_gia'    => $triGia,
            ];
        }
        return response()->json(['data' => $data]);
    }

    public function uploadFileNhap(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                // $haiQuans = HaiQuan::all();
                // $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
                // $loaiHinhs = LoaiHinh::all();
                // $loaiHangs = LoaiHang::all();
                $hangHoaRows = null;
                $file = $request->file('hys_file'); // The uploaded file
                $requiredColumns = ['ngày tk', 'số tk nhập', 'tên hàng', 'số lượng', 'đvt', 'trị giá (usd)', 'trọng lượng (kg)']; // Required columns
                $optionalColumn = 'loại hàng'; // Optional column
                $extension = $file->getClientOriginalExtension();

                if ($extension === 'csv') {
                    $csvData = array_map('str_getcsv', file($file->getRealPath()));
                } else if (in_array($extension, ['xls', 'xlsx'])) {
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
                    return response()->json(['error' => 'Unsupported file type. Only .csv, .xls, and .xlsx are allowed.'], 400);
                }

                // Find the header row
                $headerRowIndex = -1;
                foreach ($csvData as $index => $row) {
                    $normalizedRow = array_map('mb_strtolower', array_map('trim', $row));
                    if (collect($requiredColumns)->every(function ($column) use ($normalizedRow) {
                        return collect($normalizedRow)->contains(function ($col) use ($column) {
                            return is_string($col) && str_contains($col, $column);
                        });
                    })) {
                        $headerRowIndex = $index;
                        break;
                    }
                }

                // If no valid header row found
                if ($headerRowIndex === -1) {
                    session()->flash('alert-danger', 'Không tìm thấy dòng tiêu đề chứa tất cả các cột yêu cầu.');
                    return redirect()->back();
                }

                // Extract header and normalize it
                $header = array_map('mb_strtolower', array_map('trim', $csvData[$headerRowIndex]));

                // Map column names to their positions
                $mappedColumns = [];
                foreach (array_merge($requiredColumns, [$optionalColumn]) as $column) {
                    $mappedColumns[$column] = collect($header)->search(function ($col) use ($column) {
                        return is_string($col) && str_contains($col, $column);
                    }) ?? -1;
                }

                // Prepare HangHoa records
                $hangHoaRows = [];
                foreach (array_slice($csvData, $headerRowIndex + 1) as $row) {
                    // $convertedDate = Carbon::createFromFormat('m/d/Y', $row[$mappedColumns['ngày tk']])->format('Y-m-d');
                    $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
                    if (!$row[$mappedColumns['số tk nhập']]) {
                        session()->flash('alert-success', 'Nhập danh sách thành công');
                        return redirect()->back();
                    }
                    $convertedDate = Carbon::createFromFormat('m/d/Y', $row[$mappedColumns['ngày tk']])->format('Y-m-d');
                    $nhapHang = NhapHang::find($row[$mappedColumns['số tk nhập']]);
                    $trong_luong = (float) str_replace(',', '', $row[$mappedColumns['trọng lượng (kg)']]) / 1000;

                    if (!$nhapHang) {
                        NhapHang::insert([
                            'so_to_khai_nhap' => $row[$mappedColumns['số tk nhập']],
                            'ma_chu_hang' => '',
                            'ma_loai_hinh' => '',
                            'ma_hai_quan' => '',
                            'ma_doanh_nghiep' => $doanhNghiep->ma_doanh_nghiep,
                            'ngay_dang_ky' => $convertedDate,
                            'ngay_thong_quan' => $convertedDate,
                            'trang_thai' => 'Đang chờ duyệt',
                            'phuong_tien_vt_nhap' => '',
                            'ptvt_ban_dau' => '',
                            'container_ban_dau' => '',
                            'trong_luong' => $trong_luong,
                            'created_at' => now(),
                        ]);
                    } else {
                        $nhapHang->update([
                            'trong_luong' => $nhapHang->trong_luong + $trong_luong

                        ]);
                    }
                    $triGia = (float) str_replace(',', '', $row[$mappedColumns['trị giá (usd)']]);
                    $so_luong = (int) $row[$mappedColumns['lượng']];
                    $hangHoa = HangHoa::where('so_to_khai_nhap', $row[$mappedColumns['số tk nhập']])
                        ->where('ten_hang', $row[$mappedColumns['tên hàng']])
                        ->first();
                    if (is_null($hangHoa)) {
                        HangHoa::insert([
                            'so_to_khai_nhap' => $row[$mappedColumns['số tk nhập']],
                            'ten_hang'        => $row[$mappedColumns['tên hàng']] ?? '',
                            'xuat_xu'         => '',
                            'loai_hang'       => '',
                            'so_luong_khai_bao' => $so_luong,
                            'don_gia'         => $triGia / $so_luong,
                            'tri_gia'         => $triGia ?? 0,
                            'don_vi_tinh'     => $row[$mappedColumns['đvt']] ?? '',
                        ]);
                    } else {
                        $hangHoa->update([
                            'so_luong_khai_bao' => (int)$hangHoa->so_luong_khai_bao + (int)$row[$mappedColumns['lượng']] ?? 0,
                            'tri_gia' => (float)$hangHoa->tri_gia + $triGia,
                        ]);
                    }
                }
                return redirect()->back();
            });
        } catch (\Exception $e) {
            Log::error('Error uploadFileNhap: ' . $e->getMessage());
            return redirect()->back()
                ->with('alert-danger', 'Có lỗi xảy ra trong quá trình xử lý.')
                ->withInput();
        }
    }
    private function xuLySeal($request, $formattedDate)
    {
        $record = NiemPhong::where('so_container', $request->so_container)->first();

        if (!$record) {
            NiemPhong::insert([
                'so_container' => $request->so_container,
                'so_seal' => '',
                'ngay_niem_phong' => $formattedDate,
            ]);
        }
    }
    public function exportToKhaiNhap($so_to_khai_nhap)
    {
        $nhapHang = NhapHang::where('so_to_khai_nhap', $so_to_khai_nhap)->firstOrFail();
        $hangHoaRows = HangHoa::where('so_to_khai_nhap', $nhapHang->so_to_khai_nhap)->get();
        $fileName = 'Tờ khai nhập số ' . $nhapHang->so_to_khai_nhap . '.xlsx';

        return Excel::download(new ToKhaiExport($nhapHang, $hangHoaRows), $fileName);
    }

    public function getNhapHangDaDuyets(Request $request)
    {
        if ($request->ajax()) {
            $statuses = ['Đã nhập hàng', 'Đã xuất hết', 'Đã bàn giao hồ sơ', 'Quay về kho ban đầu', 'Đã tiêu hủy'];

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
                    $statusClasses = [
                        'Đã nhập hàng' => 'text-success',
                        'Đã xuất hết' => 'text-success',
                        'Quay về kho ban đầu' => 'text-success',
                        'Đã bàn giao hồ sơ' => 'text-success',
                        'Đã tiêu hủy' => 'text-danger',
                        'Quá hạn' => 'text-danger',
                        'Doanh nghiệp yêu cầu sửa tờ khai' => 'text-warning',
                    ];
                    $class = $statusClasses[$status] ?? 'text-dark';

                    return '<span class="' . $class . '">' . $status . '</span>';
                })
                ->addColumn('action', function ($nhapHang) {
                    return '<a href="' . route('nhap-hang.show', $nhapHang->so_to_khai_nhap) . '" class="btn btn-primary btn-sm">Xem</a>';
                })
                ->rawColumns(['trang_thai', 'action'])
                ->make(true);
        }
    }
}
