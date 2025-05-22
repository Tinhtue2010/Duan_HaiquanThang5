<?php

namespace App\Http\Controllers;

use Yajra\DataTables\Facades\DataTables;
use App\Exports\ToKhaiXuatExport;
use App\Models\XuatHangChiTietSua;
use App\Models\PTVTXuatCanhCuaPhieu;
use App\Models\CongChuc;
use App\Models\DoanhNghiep;
use App\Models\HangTrongCont;
use App\Models\LoaiHinh;
use App\Models\NhapHang;
use App\Models\PTVTXuatCanh;
use App\Models\PTVTXuatCanhCuaPhieuSua;
use App\Models\PTVTXuatCanhCuaPhieuTruocSua;
use App\Models\XuatHang;
use App\Models\XuatHangCont;
use App\Models\ChiTietXuatCanh;
use App\Models\TheoDoiHangHoa;
use App\Models\TheoDoiTruLui;
use App\Models\TheoDoiTruLuiChiTiet;
use App\Models\XuatCanhChiTiet;
use App\Models\XuatHangSua;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use App\Services\XuatHangService;
use PhpOffice\PhpSpreadsheet\IOFactory;


class XuatHangController extends Controller
{
    protected $xuatHangService;

    public function __construct(XuatHangService $xuatHangService)
    {
        $this->xuatHangService = $xuatHangService;
    }
    public function danhSachToKhai()
    {
        $statuses = [
            '1',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '10'
        ];
        $xuatHangs = $this->xuatHangService->getToKhaiXuat($statuses);

        return view('xuat-hang.quan-ly-xuat-hang', ['xuatHangs' => $xuatHangs]);
    }

    public function listToKhaiDaXuatHang()
    {
        return view('xuat-hang.to-khai-da-xuat');
    }

    public function listToKhaiDaHuy()
    {
        $statuses = ['0'];
        $xuatHangs = $this->xuatHangService->getToKhaiXuat($statuses);

        return view('xuat-hang.to-khai-xuat-da-huy', ['xuatHangs' => $xuatHangs]);
    }

    public function themToKhaiXuat()
    {
        $containers = $this->xuatHangService->getThongTinHangHoaHienTai();
        $loaiHinhs = LoaiHinh::all();
        $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
        $ptvtXuatCanhs = PTVTXuatCanh::where('trang_thai', '2')->get();
        $nhapHangs = NhapHang::where('ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
            ->where('trang_thai', '2')
            ->get();

        return view('xuat-hang.them-to-khai-xuat', data: compact('containers', 'loaiHinhs', 'nhapHangs', 'doanhNghiep', 'ptvtXuatCanhs')); // Pass the data to the view
    }

    public function themToKhaiXuatSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $rowsData = json_decode($request->rows_data, true);
            $ptvtRowsData = json_decode($request->ptvt_rows_data, true);

            $xuatHang = $this->xuatHangService->themXuatHang($request);
            $this->xuatHangService->themPTVTCuaPhieu($xuatHang->so_to_khai_xuat, $ptvtRowsData);
            $this->xuatHangService->themXuatHangConts($xuatHang->so_to_khai_xuat, $rowsData);

            $xuatHang->tong_so_luong = $this->xuatHangService->getTongSoLuongHangXuat($xuatHang->so_to_khai_xuat);
            $xuatHang->ten_phuong_tien_vt = $this->xuatHangService->getPTVTXuatCanhCuaPhieu($xuatHang->so_to_khai_xuat);
            $xuatHang->save();

            $this->themTienTrinh($xuatHang, "tạo");

            DB::commit();
            $xuatHangConts = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                ->where('xuat_hang.so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                ->get();
            foreach ($xuatHangConts as $xuatHangCont) {
                $hangHoaXuat = $this->xuatHangService->getThongTinHangHoaXuat($xuatHangCont);
                $this->xuatHangService->themTheoDoi($xuatHang, $xuatHangCont, $hangHoaXuat, '');
                $this->xuatHangService->capNhatSoLuongHang($xuatHangCont, $hangHoaXuat);
            }

            session()->flash('alert-success', 'Thêm phiếu xuất mới thành công!');
            return redirect()->route('xuat-hang.thong-tin-xuat-hang', ['so_to_khai_xuat' => $xuatHang->so_to_khai_xuat]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in themToKhaiXuatSubmit: ' . $e->getMessage());
            return redirect()->back();
        }
    }


    public function duyetNhanhPhieuXuat(Request $request)
    {
        $ptvtXuatCanhs = PTVTXuatCanh::where('trang_thai', '2')->get();
        $congChucs = CongChuc::where('is_chi_xem', 0)->get();
        return view('xuat-hang.duyet-nhanh-phieu-xuat', data: compact('ptvtXuatCanhs', 'congChucs'));
    }

    public function duyetNhanhPhieuXuatSubmit(Request $request)
    {
        $rowsData = json_decode($request->rows_data, true);
        foreach ($rowsData as $row) {
            $this->xuatHangService->xuLyDuyetPhieuXuat($request->ma_cong_chuc, $row["so_to_khai_xuat"]);
        }
        session()->flash('alert-success', 'Duyệt các phiếu xuất thành công!');
        return redirect()->route('xuat-hang.quan-ly-xuat-hang');
    }


    public function lichSuSuaXuatHang($so_to_khai_xuat)
    {
        $xuatHangs = XuatHangSua::where('so_to_khai_xuat', $so_to_khai_xuat)->get();
        return view('xuat-hang.lich-su-sua-xuat-hang', compact('xuatHangs'));
    }


    public function suaToKhaiXuat($so_to_khai_xuat)
    {
        $xuatHang = XuatHang::find($so_to_khai_xuat);
        $loaiHinhs = LoaiHinh::all();
        $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
        $ptvtXuatCanhs = PTVTXuatCanh::where('trang_thai', 2)->get();

        if (in_array($xuatHang->trang_thai, ['3', '4', '5', '6'])) {
            $xuatHang = XuatHangSua::where('so_to_khai_xuat', $so_to_khai_xuat)
                ->orderByDesc('ma_yeu_cau') // or ->orderBy('id', 'desc')
                ->first();
            $xuatHangConts = XuatHangChiTietSua::where('ma_yeu_cau', $xuatHang->ma_yeu_cau)
                ->join('hang_trong_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_chi_tiet_sua.ma_hang_cont')
                ->join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                ->get();
            $ptvts = PTVTXuatCanhCuaPhieuSua::where('ma_yeu_cau', $xuatHang->ma_yeu_cau)
                ->with('PTVTXuatCanh')
                ->get();
            $PTVTcount = $ptvts->count();
        } else {
            $xuatHang = XuatHang::find($so_to_khai_xuat);
            $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $so_to_khai_xuat)
                ->join('hang_trong_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
                ->join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                ->get();
            $ptvts = PTVTXuatCanhCuaPhieu::where('so_to_khai_xuat', $so_to_khai_xuat)
                ->with('PTVTXuatCanh')
                ->get();
            $PTVTcount = $ptvts->count();
        }

        $nhapHangs = NhapHang::where('ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
            ->where('trang_thai', '2')
            ->get();
        $containers = $this->xuatHangService->getThongTinHangHoaHienTai();
        $xuatHangContMap = $xuatHangConts->pluck('so_luong_xuat', 'ma_hang_cont');

        foreach ($containers as $container) {
            $container->so_luong_xuat = $xuatHangContMap[$container->ma_hang_cont] ?? 0;
        }
        return view('xuat-hang.sua-to-khai-xuat', data: compact('containers', 'loaiHinhs', 'doanhNghiep', 'ptvtXuatCanhs', 'xuatHang', 'ptvts', 'PTVTcount', 'nhapHangs', 'xuatHangConts')); // Pass the data to the view
    }

    public function suaToKhaiXuatSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $rowsData = json_decode($request->rows_data, true);
            $rowsData = array_filter($rowsData, function ($row) {
                return !empty($row['ma_hang_cont']);
            });

            $isExists = XuatHangSua::where('so_to_khai_xuat', $request->so_to_khai_xuat)->exists();
            if (!$isExists) {
                $xuatHang = XuatHang::find($request->so_to_khai_xuat);
                $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $request->so_to_khai_xuat)->get();
                $xuatHangSua = XuatHangSua::create($xuatHang->toArray());
                foreach ($xuatHangConts as $xuatHangCont) {
                    $data = $xuatHangCont->toArray();
                    $data['ma_yeu_cau'] = $xuatHangSua->ma_yeu_cau;
                    XuatHangChiTietSua::create($data);
                }
                // $ptvts = PTVTXuatCanhCuaPhieu::where('so_to_khai_xuat', $request->so_to_khai_xuat)->get();
                // foreach ($ptvts as $ptvt) {
                //     $data = $ptvt->toArray();
                //     $data['ma_yeu_cau'] = $xuatHangSua->ma_yeu_cau;
                //     PTVTXuatCanhCuaPhieu::create($data);
                // }
            }

            $rowsData = array_values($rowsData);
            $ptvtRowsData = json_decode($request->ptvt_rows_data, true);
            $xuatHang = XuatHang::find($request->so_to_khai_xuat);
            $suaXuatHang = $this->xuatHangService->themSuaXuatHang($request, $xuatHang);

            if (in_array($xuatHang->trang_thai, ['3', '4', '5', '6'])) {
                PTVTXuatCanhCuaPhieuSua::where('ma_yeu_cau', $suaXuatHang->ma_yeu_cau)->delete();
                XuatHangChiTietSua::where('ma_yeu_cau', $suaXuatHang->ma_yeu_cau)->delete();
            }
            $this->xuatHangService->themSuaPTVTCuaPhieu($suaXuatHang->ma_yeu_cau, $ptvtRowsData, $xuatHang);
            $this->xuatHangService->themChiTietSuaXuatHang($suaXuatHang, $rowsData);
            $this->xuatHangService->capNhatTrangThaiPhieuXuat($xuatHang);

            DB::commit();
            if ($xuatHang->trang_thai == 3) {
                $this->duyetYeuCauSua($suaXuatHang->ma_yeu_cau);
                $xuatHang->trang_thai = 1;
                $xuatHang->save();
            } else {
                $this->themTienTrinh($xuatHang, "yêu cầu sửa");
            }

            session()->flash('alert-success', 'Sửa phiếu xuất thành công!');
            return redirect()->route('xuat-hang.thong-tin-xuat-hang', ['so_to_khai_xuat' => $request->so_to_khai_xuat]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-success', 'Có lỗi xảy ra');
            Log::error('Error in suaToKhaiXuatSubmit: ' . $e->getMessage());
            return redirect()->back();
        }
    }



    public function thongTinXuatHang($so_to_khai_xuat)
    {
        $congChucs = CongChuc::where('is_chi_xem', 0)->get();
        if (XuatHang::find($so_to_khai_xuat)) {
            $xuatHang = XuatHang::find($so_to_khai_xuat);
            $hangHoaRows = $this->xuatHangService->getThongTinPhieuXuatHang($so_to_khai_xuat);
            $ptvts = PTVTXuatCanhCuaPhieu::where('so_to_khai_xuat', $so_to_khai_xuat)
                ->with('PTVTXuatCanh')
                ->get()
                ->pluck('PTVTXuatCanh.ten_phuong_tien_vt')
                ->filter()
                ->implode('; ');
        }
        $soLuongSum = $hangHoaRows->sum('so_luong_xuat');
        $triGiaSum = $hangHoaRows->sum('tri_gia');

        return view('xuat-hang.thong-tin-xuat-hang', compact('xuatHang', 'hangHoaRows', 'soLuongSum', 'triGiaSum', 'congChucs', 'ptvts')); // Pass data to the view
    }




    public function xemSuaXuatHangTheoLan($ma_yeu_cau)
    {
        $congChucs = CongChuc::where('is_chi_xem', 0)->get();
        $suaXuatHang = XuatHangSua::find($ma_yeu_cau);
        $xuatHang = XuatHangSua::where('so_to_khai_xuat', $suaXuatHang->so_to_khai_xuat)
            ->where('ma_yeu_cau', '<', $suaXuatHang->ma_yeu_cau)
            ->orderByDesc('ma_yeu_cau')
            ->first();
        $hangHoaRows = $this->xuatHangService->getChiTietThongTinSauSuaXuatHang($xuatHang->ma_yeu_cau);
        $ptvts = $this->xuatHangService->getPTVTXuatCanhCuaPhieuSauSua($xuatHang->ma_yeu_cau);

        $suaHangHoaRows = $this->xuatHangService->getChiTietThongTinSauSuaXuatHang($ma_yeu_cau);
        $suaPTVTs = $this->xuatHangService->getPTVTXuatCanhCuaPhieuSauSua($ma_yeu_cau);

        $soLuongSum = $hangHoaRows->sum('so_luong_xuat');
        $triGiaSum = $hangHoaRows->sum('tri_gia');

        $suaSoLuongSum = $suaHangHoaRows->sum('so_luong_xuat');
        $suaTriGiaSum = $suaHangHoaRows->sum('tri_gia');
        return view('xuat-hang.xem-yeu-cau-sua', compact(
            'xuatHang',
            'hangHoaRows',
            'soLuongSum',
            'triGiaSum',
            'suaXuatHang',
            'suaHangHoaRows',
            'suaSoLuongSum',
            'suaTriGiaSum',
            'ptvts',
            'suaPTVTs'
        ));
    }

    public function xemYeuCauSua($so_to_khai_xuat, $ma_yeu_cau)
    {
        $congChucs = CongChuc::where('is_chi_xem', 0)->get();
        $xuatHang = XuatHang::find($so_to_khai_xuat);

        $ma_yeu_cau = XuatHangSua::where('so_to_khai_xuat', $so_to_khai_xuat)->max('ma_yeu_cau');
        if ($xuatHang) {
            $hangHoaRows = $this->xuatHangService->getThongTinHangTrongPhieuXuatHienTai($so_to_khai_xuat);
        }
        $suaXuatHang = $this->xuatHangService->getThongTinSuaXuatHang($so_to_khai_xuat);
        $suaHangHoaRows = $this->xuatHangService->getChiTietThongTinSauSuaXuatHang($ma_yeu_cau);

        $ptvts = PTVTXuatCanhCuaPhieu::where('so_to_khai_xuat', $so_to_khai_xuat)
            ->with('PTVTXuatCanh')
            ->get();
        $suaPTVTs = $this->xuatHangService->getPTVTXuatCanhCuaPhieuSauSua($ma_yeu_cau);

        $soLuongSum = $hangHoaRows->sum('so_luong_xuat');
        $triGiaSum = $hangHoaRows->sum('tri_gia');
        $suaSoLuongSum = $suaHangHoaRows->sum('so_luong_xuat');
        $suaTriGiaSum = $suaHangHoaRows->sum('tri_gia');
        return view('xuat-hang.xem-yeu-cau-sua', compact(
            'xuatHang',
            'hangHoaRows',
            'soLuongSum',
            'triGiaSum',
            'suaXuatHang',
            'suaHangHoaRows',
            'suaSoLuongSum',
            'suaTriGiaSum',
            'ptvts',
            'suaPTVTs'
        ));
    }

    public function duyetYeuCauSua($ma_yeu_cau, Request $request = null)
    {
        try {
            DB::beginTransaction();
            $suaXuatHang = XuatHangSua::findOrFail($ma_yeu_cau);
            $xuatHang = XuatHang::findOrFail($suaXuatHang->so_to_khai_xuat);
            $ptvt = PTVTXuatCanhCuaPhieu::where('so_to_khai_xuat', $suaXuatHang->so_to_khai_xuat)->pluck('so_ptvt_xuat_canh')->toArray();
            $ptvtSua =  PTVTXuatCanhCuaPhieuSua::where('ma_yeu_cau', $ma_yeu_cau)->pluck('so_ptvt_xuat_canh')->toArray();
            $ptvtNotInSua = array_diff($ptvt, $ptvtSua);

            $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                ->select('so_to_khai_nhap')
                ->distinct()
                ->get();
            foreach ($xuatHangConts as $xuatHangCont) {
                $trang_thai = NhapHang::find($xuatHangCont->so_to_khai_nhap)->trang_thai;
                if ($xuatHang->trang_thai != 14) {
                    if ($trang_thai == '7') {
                        $xuatHang->trang_thai = 14;
                        $xuatHang->save();
                        DB::commit();
                        return redirect()->route('xuat-hang.thong-tin-xuat-hang', ['so_to_khai_xuat' => $xuatHang->so_to_khai_xuat]);
                    }
                }
            }

            $chiTietSuaXuatHangs = XuatHangChiTietSua::where('ma_yeu_cau', $ma_yeu_cau)->get();
            $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $suaXuatHang->so_to_khai_xuat)->get();

            $kiemTra = $this->xuatHangService->kiemTraSoLuongCoTheXuat($suaXuatHang, $xuatHang, $chiTietSuaXuatHangs);

            if (!$kiemTra) {
                return redirect()->back();
            }
            $this->xuatHangService->capNhatThongTinXuatHang($xuatHang, $suaXuatHang, $chiTietSuaXuatHangs, $xuatHangConts);

            foreach ($ptvtNotInSua as $ptvt) {
                XuatCanhChiTiet::join('xuat_canh', 'xuat_canh_chi_tiet.ma_xuat_canh', 'xuat_canh_chi_tiet.ma_xuat_canh')
                    ->where('xuat_canh.so_ptvt_xuat_canh', $ptvt)
                    ->where('xuat_canh_chi_tiet.so_to_khai_xuat', $suaXuatHang->so_to_khai_xuat)
                    ->delete();
            }

            TheoDoiHangHoa::where('cong_viec', 1)
                ->where('ma_yeu_cau', $xuatHang->so_to_khai_xuat)
                ->delete();
            TheoDoiTruLui::where('cong_viec', 1)
                ->where('ma_yeu_cau', $xuatHang->so_to_khai_xuat)
                ->delete();

            $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)->get();
            foreach ($xuatHangConts as $xuatHangCont) {
                $hangHoaXuat = $this->xuatHangService->getThongTinHangHoaXuat($xuatHangCont);
                $this->xuatHangService->themTheoDoi($xuatHang, $xuatHangCont, $hangHoaXuat, '');

                $xuatHang->tong_so_luong = $this->xuatHangService->getTongSoLuongHangXuat($xuatHang->so_to_khai_xuat);
                $xuatHang->ten_phuong_tien_vt = $this->xuatHangService->getPTVTXuatCanhCuaPhieu($xuatHang->so_to_khai_xuat);
                $xuatHang->save();
            }

            $this->themTienTrinh($xuatHang, "duyệt yêu cầu sửa", true);

            DB::commit();
            if (Auth::user()->loai_tai_khoan == 'Lãnh đạo') {
                return redirect()->route('lanh-dao.quan-ly-duyet-xuat-hang');
            }
            return redirect()->route('xuat-hang.thong-tin-xuat-hang', ['so_to_khai_xuat' => $xuatHang->so_to_khai_xuat]);
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetYeuCauSua: ' . $e->getMessage());
            return redirect()->back();
        }
    }



    public function huyYeuCauSua(Request $request, $ma_yeu_cau)
    {
        try {
            DB::beginTransaction();
            $suaXuatHang = XuatHangSua::find($ma_yeu_cau);
            $xuatHang = XuatHang::find($suaXuatHang->so_to_khai_xuat);
            if ($xuatHang) {
                $xuatHang->trang_thai = $suaXuatHang->trang_thai_phieu_xuat;
                $xuatHang->ghi_chu = $request->ghi_chu;
                $xuatHang->save();

                $this->themTienTrinh($xuatHang, "hủy yêu cầu sửa");

                $suaXuatHang->delete();
                XuatHangChiTietSua::where('ma_yeu_cau', $ma_yeu_cau)->delete();
                PTVTXuatCanhCuaPhieuSua::where('ma_yeu_cau', $ma_yeu_cau)->delete();
                session()->flash('alert-success', 'Hủy yêu cầu sửa thành công!');
            }
            DB::commit();
            return redirect()->route('xuat-hang.thong-tin-xuat-hang', ['so_to_khai_xuat' => $xuatHang->so_to_khai_xuat]);
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in huyYeuCauSua: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function updateDuyetToKhai(Request $request)
    {
        $xuatHang = XuatHang::find($request->so_to_khai_xuat);
        if ($xuatHang->trang_thai != "2") {
            try {
                DB::beginTransaction();
                $this->xuatHangService->xuLyDuyetPhieuXuat($request->ma_cong_chuc, $request->so_to_khai_xuat);

                DB::commit();
                session()->flash('alert-success', 'Duyệt phiếu xuất thành công!');
                return redirect()->route('xuat-hang.quan-ly-xuat-hang');
            } catch (\Exception $e) {
                session()->flash('alert-danger', 'Có lỗi xảy ra');
                Log::error('Error in updateDuyetToKhai: ' . $e->getMessage());
                return redirect()->back();
            }
        }
        session()->flash('alert-success', 'Duyệt phiếu xuất thành công!');
        return redirect()->route('xuat-hang.quan-ly-xuat-hang');
    }


    public function yeuCauHuyToKhai(Request $request)
    {
        $xuatHang = XuatHang::find($request->so_to_khai_xuat);
        $xuatHang->ghi_chu = $request->ghi_chu;
        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $this->duyetYeuCauHuy($xuatHang);
        } else {
            if ($xuatHang->trang_thai == 1) {
                $this->huyPhieu($xuatHang);
                return redirect()->back();
            } elseif ($xuatHang->trang_thai == 2) {
                $xuatHang->trang_thai = 8;
                $xuatHang->save();
                $this->themTienTrinh($xuatHang, "yêu cầu hủy");
            } elseif ($xuatHang->trang_thai == 11) {
                $xuatHang->trang_thai = 9;
                $xuatHang->save();
                $this->themTienTrinh($xuatHang, "yêu cầu hủy");
            } elseif ($xuatHang->trang_thai == 12) {
                $xuatHang->trang_thai = 10;
                $xuatHang->save();
                $this->themTienTrinh($xuatHang, "yêu cầu hủy");
            } else {
                $this->duyetYeuCauHuy($xuatHang);
            }


            session()->flash('alert-success', 'Yêu cầu hủy phiếu xuất thành công!');
        }
        return redirect()->back();
    }

    public function huyPhieu($xuatHang)
    {
        if ($xuatHang->trang_thai != '0') {
            $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)->get();
            foreach ($xuatHangConts as $xuatHangCont) {
                $hang_trong_cont = HangTrongCont::find($xuatHangCont->ma_hang_cont);
                $hang_trong_cont->so_luong += $xuatHangCont->so_luong_xuat;
                $hang_trong_cont->is_da_chuyen_cont = 0;
                $hang_trong_cont->save();
            }

            XuatCanhChiTiet::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)->delete();
            $xuatHang->trang_thai = '0';
            $xuatHang->ghi_chu = 'Doanh nghiệp hủy phiếu';
            $xuatHang->save();

            $this->themTienTrinh($xuatHang, "hủy", false, true);
        }

        session()->flash('alert-success', 'Hủy phiếu xuất thành công!');
        return redirect()->back();
    }
    public function duyetYeuCauHuy($xuatHang)
    {
        if ($xuatHang != '0') {
            $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)->get();
            foreach ($xuatHangConts as $xuatHangCont) {
                $hang_trong_cont = HangTrongCont::find($xuatHangCont->ma_hang_cont);
                $hang_trong_cont->so_luong += $xuatHangCont->so_luong_xuat;
                $hang_trong_cont->is_da_chuyen_cont = 0;
                $hang_trong_cont->save();
            }

            XuatCanhChiTiet::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)->delete();

            if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
                $xuatHang->ghi_chu = 'Công chức duyệt yêu cầu hủy: ' . $xuatHang->ghi_chu;
                $noi_dung = "Công chức duyệt yêu cầu hủy phiếu xuất số " . $xuatHang->so_to_khai_xuat;
            } else {
                $xuatHang->ghi_chu = 'Doanh nghiệp hủy: ' . $xuatHang->ghi_chu;
                $noi_dung = "Doanh nghiệp hủy phiếu xuất số " . $xuatHang->so_to_khai_xuat;
            }

            $xuatHang->trang_thai = '0';
            $xuatHang->save();

            $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                ->select('so_to_khai_nhap')
                ->distinct()
                ->get();
            foreach ($xuatHangConts as $xuatHangCont) {
                $nhapHang = NhapHang::find($xuatHangCont->so_to_khai_nhap);
                $nhapHang->trang_thai = '2';
                $nhapHang->save();
                $this->xuatHangService->themTienTrinh($xuatHangCont->so_to_khai_nhap, $noi_dung, $this->xuatHangService->getCongChucHienTai()->ma_cong_chuc);
            }
        }
        session()->flash('alert-success', 'Duyệt yêu cầu hủy phiếu xuất thành công!');
        return redirect()->back();
    }

    public function thuHoiYeuCauHuy(Request $request)
    {
        $xuatHang = XuatHang::find($request->so_to_khai_xuat);
        if ($xuatHang->trang_thai == "7") {
            $xuatHang->trang_thai = '1';
        } elseif ($xuatHang->trang_thai == "8") {
            $xuatHang->trang_thai = '2';
        } elseif ($xuatHang->trang_thai == "9") {
            $xuatHang->trang_thai = '11';
        } elseif ($xuatHang->trang_thai == "10") {
            $xuatHang->trang_thai = '12';
        }

        $this->themTienTrinh($xuatHang, "hủy yêu cầu hủy");
        $this->capNhatGhiChu($xuatHang, "hủy yêu cầu hủy");

        session()->flash('alert-success', 'Hủy yêu cầu hủy phiếu xuất thành công!');
        $xuatHang->save();
        return redirect()->back();
    }

    public function themTienTrinh($xuatHang, $noi_dung, $cong_chuc_only = false, $doi_trang_thai = false)
    {
        $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
            ->select('so_to_khai_nhap')
            ->distinct()
            ->get();

        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            foreach ($xuatHangConts as $xuatHangCont) {
                $this->xuatHangService->themTienTrinh($xuatHangCont->so_to_khai_nhap,  "Công chức " . $noi_dung . " phiếu xuất số " . $xuatHang->so_to_khai_xuat, $this->xuatHangService->getCongChucHienTai()->ma_cong_chuc ?? '');
            }
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            if ($cong_chuc_only) {
                return;
            }
            foreach ($xuatHangConts as $xuatHangCont) {
                $this->xuatHangService->themTienTrinh($xuatHangCont->so_to_khai_nhap, "Doanh nghiệp " . $noi_dung . " phiếu xuất số " . $xuatHang->so_to_khai_xuat, '');
            }
        }

        if ($doi_trang_thai) {
            foreach ($xuatHangConts as $xuatHangCont) {
                $nhapHang = NhapHang::find($xuatHangCont->so_to_khai_nhap);
                $nhapHang->trang_thai = '2';
                $nhapHang->save();
            }
        }
    }

    public function capNhatGhiChu($xuatHang, $noi_dung)
    {
        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $xuatHang->ghi_chu = 'Công chức ' . $noi_dung . " : " . $xuatHang->ghi_chu;
        } else {
            $xuatHang->ghi_chu = 'Doanh nghiệp ' . $noi_dung . " : " . $xuatHang->ghi_chu;
        }
        $xuatHang->save();
    }

    public function duyetNhanhThucXuat(Request $request)
    {
        $ptvtXuatCanhs = PTVTXuatCanh::where('trang_thai', '2')->get();
        $congChucs = CongChuc::where('is_chi_xem', 0)->get();
        $congChucHienTai = $this->xuatHangService->getCongChucHienTai();
        return view('xuat-hang.duyet-nhanh-thuc-xuat', data: compact('ptvtXuatCanhs', 'congChucs', 'congChucHienTai'));
    }

    public function duyetNhanhThucXuatSubmit(Request $request)
    {
        $rowsData = json_decode($request->rows_data, true);
        foreach ($rowsData as $row) {
            $this->xuatHangService->xuLyDuyetThucXuat($request->ma_cong_chuc, $row["so_to_khai_xuat"]);
        }
        session()->flash('alert-success', 'Duyệt thực xuất hàng thành công');
        return redirect()->route('xuat-hang.quan-ly-xuat-hang');
    }

    public function thayDoiCongChucXuat(Request $request)
    {
        XuatHang::find($request->so_to_khai_xuat)->update([
            'ma_cong_chuc' => $request->ma_cong_chuc
        ]);
        session()->flash('alert-success', 'Thay đổi công chức thành công');
        return redirect()->back();
    }

    public function exportToKhaiXuat(Request $request)
    {
        $xuatHang = XuatHang::find($request->so_to_khai_xuat);
        $so_to_khai_xuat = '';
        if ($xuatHang) {
            $so_to_khai_xuat = $xuatHang->so_to_khai_xuat;
        } else {
            session()->flash('alert-danger', 'Không tìm thấy phiếu xuất hàng');
            return redirect()->back();
        }

        $fileName = 'Phiếu xuất số ' . $request->so_to_khai_xuat . ' tàu ' . $xuatHang->ten_phuong_tien_vt . '.xlsx';
        
        return Excel::download(new ToKhaiXuatExport($so_to_khai_xuat,$request->ma_yeu_cau), $fileName);
    }

    public function kiemTraQuaHan(Request $request)
    {
        try {
            $nhapHang = NhapHang::find($request->so_to_khai_nhap);
            if ($nhapHang->ma_loai_hinh == 'G21') {
                $ngay_dang_ky = $nhapHang->ngay_dang_ky;
                $so_ngay_gia_han = $nhapHang->so_ngay_gia_han;

                $ngayDangKy = Carbon::parse($ngay_dang_ky);
                $ngayGiaHan = $ngayDangKy->addDays($so_ngay_gia_han);

                $now = Carbon::now();
                $isMoreThan60Days = $ngayGiaHan->diffInDays($now, false) > 60;
            } else {
                $isMoreThan60Days = false;
            }
            return response()->json(['data' => $isMoreThan60Days]);
        } catch (\Exception $e) {
            Log::error('Error in kiemTraQuaHan: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json(['message' => 'An error occurred'], 500);
        }
    }
    public function getPhieuXuatChoDuyetCuaPTVT(Request $request)
    {
        $xuatHangs = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', 'xuat_hang_cont.so_to_khai_xuat')
            ->join('doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep', 'xuat_hang.ma_doanh_nghiep')
            ->join('ptvt_xuat_canh_cua_phieu', 'ptvt_xuat_canh_cua_phieu.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
            ->when(!empty($request->so_ptvt_xuat_canh), function ($query) use ($request) {
                return $query->where('ptvt_xuat_canh_cua_phieu.so_ptvt_xuat_canh', $request->so_ptvt_xuat_canh);
            })
            ->when(!empty($request->so_to_khai_nhap), function ($query) use ($request) {
                return $query->where('xuat_hang_cont.so_to_khai_nhap', $request->so_to_khai_nhap);
            })
            ->where('xuat_hang.trang_thai', '1')
            ->select('xuat_hang.*', 'doanh_nghiep.ten_doanh_nghiep', DB::raw('SUM(xuat_hang_cont.so_luong_xuat) as tong_so_luong_xuat'))
            ->groupBy(
                'doanh_nghiep.ten_doanh_nghiep',
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang.ma_loai_hinh',
                'xuat_hang.ngay_xuat_canh',
                'xuat_hang.trang_thai',
                'xuat_hang.updated_at',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ten_doan_tau',
                'xuat_hang.ghi_chu',
                'xuat_hang.ma_cong_chuc',
                'xuat_hang.ma_doanh_nghiep',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang.tong_so_luong',
                'xuat_hang.created_at',
                'xuat_hang.phuong_tien_vt_nhap',
            )
            ->get();
        foreach ($xuatHangs as $export) {
            $export->ptvts = $this->xuatHangService->getPTVTXuatCanhCuaPhieu($export->so_to_khai_xuat);
        }
        return response()->json(['xuatHangs' => $xuatHangs]);
    }
    public function getPhieuXuatDaXuatHangCuaPTVT(Request $request)
    {
        $congChuc = $this->xuatHangService->getCongChucHienTai();
        $xuatHangs = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', 'xuat_hang_cont.so_to_khai_xuat')
            ->join('doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep', 'xuat_hang.ma_doanh_nghiep')
            ->join('ptvt_xuat_canh_cua_phieu', 'ptvt_xuat_canh_cua_phieu.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
            ->where('xuat_hang.trang_thai', '12')
            ->where('xuat_hang.ma_cong_chuc', $congChuc->ma_cong_chuc)
            ->select('xuat_hang.*', 'doanh_nghiep.ten_doanh_nghiep', DB::raw('SUM(xuat_hang_cont.so_luong_xuat) as tong_so_luong_xuat'))
            ->groupBy(
                'doanh_nghiep.ten_doanh_nghiep',
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang.ma_loai_hinh',
                'xuat_hang.ngay_xuat_canh',
                'xuat_hang.trang_thai',
                'xuat_hang.updated_at',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ten_doan_tau',
                'xuat_hang.ghi_chu',
                'xuat_hang.ma_cong_chuc',
                'xuat_hang.ma_doanh_nghiep',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang.tong_so_luong',
                'xuat_hang.created_at',
                'xuat_hang.phuong_tien_vt_nhap',
            )
            ->get();

        return response()->json(['xuatHangs' => $xuatHangs]);
    }

    public function getXuatHangDaDuyets(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $statuses = [
            '2',
            '11',
            '12',
            '13',
            '14',
        ];

        $user = Auth::user();

        $query = XuatHang::query()
            ->select([
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang.ma_loai_hinh',
                'xuat_hang.trang_thai',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang.tong_so_luong',
                'doanh_nghiep.ten_doanh_nghiep'
            ])
            ->join('doanh_nghiep', 'xuat_hang.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->whereIn('xuat_hang.trang_thai', $statuses)
            ->groupBy(
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang.ma_loai_hinh',
                'xuat_hang.trang_thai',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang.tong_so_luong',
                'doanh_nghiep.ten_doanh_nghiep'
            );
        if ($user->loai_tai_khoan === "Doanh nghiệp") {
            $query->where('xuat_hang.ma_doanh_nghiep', function ($subquery) use ($user) {
                $subquery->select('ma_doanh_nghiep')
                    ->from('doanh_nghiep')
                    ->where('ma_tai_khoan', $user->ma_tai_khoan)
                    ->limit(1);
            });
        }
        $query->orderBy('xuat_hang.so_to_khai_xuat', 'desc');

        return DataTables::eloquent($query)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->search['value'])) {
                    $search = $request->search['value'];

                    $query->where(function ($q) use ($search) {
                        $q->orWhere('xuat_hang.so_to_khai_xuat', 'LIKE', "%{$search}%")
                            ->orWhereRaw("DATE_FORMAT(xuat_hang.ngay_dang_ky, '%d-%m-%Y') LIKE ?", ["%{$search}%"])
                            ->orWhere('doanh_nghiep.ten_doanh_nghiep', 'LIKE', "%{$search}%")
                            ->orWhere('xuat_hang.ten_phuong_tien_vt', 'LIKE', "%{$search}%")
                            ->orWhere('xuat_hang.so_to_khai_xuat', 'LIKE', "%{$search}%");
                    });
                }
            })
            ->addIndexColumn()
            ->addColumn('DT_RowIndex', function ($xuatHang) {
                return '';
            })
            ->editColumn('ngay_dang_ky', function ($xuatHang) {
                return Carbon::parse($xuatHang->ngay_dang_ky)->format('d-m-Y');
            })
            ->addColumn('ten_doanh_nghiep', function ($xuatHang) {
                return $xuatHang->ten_doanh_nghiep ?? 'N/A';
            })
            ->editColumn('trang_thai', function ($xuatHang) {
                $status = trim($xuatHang->trang_thai);

                $statusLabels = [
                    '2' => ['text' => 'Đã duyệt', 'class' => 'text-success'],
                    '11' => ['text' => 'Đã chọn phương tiện xuất cảnh', 'class' => 'text-success'],
                    '12' => ['text' => 'Đã duyệt xuất hàng', 'class' => 'text-success'],
                    '13' => ['text' => 'Đã thực xuất hàng', 'class' => 'text-success'],
                    '14' => ['text' => 'Đã duyệt sửa lần 1', 'class' => 'text-warning'],
                ];

                return isset($statusLabels[$status])
                    ? "<span class='{$statusLabels[$status]['class']}'>{$statusLabels[$status]['text']}</span>"
                    : '<span class="text-muted">Trạng thái không xác định</span>';
            })
            ->rawColumns(['trang_thai', 'action'])
            ->make(true);
    }


    public function uploadFileXuatAjax(Request $request)
    {
        $file = $request->file('hys_file');

        $requiredColumns = ['số tờ khai', 'tên hàng', 'số lượng  đăng ký xuất'];
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



        $headerRowIndex = -1;
        $foundColumns = [];

        foreach ($csvData as $index => $row) {
            $normalizedRow = array_map('mb_strtolower', array_map('trim', $row));

            foreach ($requiredColumns as $column) {
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

        foreach ($requiredColumns as $column) {
            $mappedColumns[$column] = collect($header)->search(fn($col) => is_string($col) && str_contains($col, $column));
        }

        // Prepare HangHoa records
        $data = [];
        $lastSoToKhaiNhap = '';
        foreach (array_slice($csvData, $headerRowIndex + 1) as $row) {
            if (!$row[$mappedColumns['tên hàng']]) {
                return response()->json(['data' => $data]);
            }
            if ($row[$mappedColumns['số tờ khai']]) {
                $lastSoToKhaiNhap = $row[$mappedColumns['số tờ khai']];
            }

            $ten_hang = $row[$mappedColumns['tên hàng']];
            $so_luong_xuat = (int) ($row[$mappedColumns['số lượng  đăng ký xuất']] ?? 0);

            $nhapHang = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', 'nhap_hang.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', 'hang_hoa.ma_hang')
                ->where('nhap_hang.so_to_khai_nhap', $lastSoToKhaiNhap)
                ->where('hang_hoa.ten_hang', trim($ten_hang))
                ->first();
            $soLuongKho = $nhapHang->so_luong;
            if ($request->has('so_to_khai_xuat')) {
                $soLuongDangXuat = XuatHangCont::where('so_to_khai_xuat', $request->so_to_khai_xuat)
                    ->where('ma_hang_cont', $nhapHang->ma_hang_cont)
                    ->first()
                    ->so_luong_xuat ?? 0;
                $soLuongKho = $soLuongKho + $soLuongDangXuat;
            }

            if (!$nhapHang) {
                return response("Không tìm thấy hàng hóa {$ten_hang} trong tờ khai {$lastSoToKhaiNhap}");
            }
            if ($soLuongKho < $so_luong_xuat) {
                return response("Số lượng xuất của {$ten_hang} là {$so_luong_xuat} lớn hơn số lượng tồn {$nhapHang->so_luong}");
            }

            $data[] = [
                'ma_hang_cont'   => $nhapHang->ma_hang_cont,
                'so_to_khai_nhap'   => $nhapHang->so_to_khai_nhap,
                'ten_hang'   => $nhapHang->ten_hang,
                'xuat_xu'   => $nhapHang->xuat_xu,
                'don_vi_tinh'   => $nhapHang->don_vi_tinh,
                'don_gia'   => $nhapHang->don_gia,
                'so_container'   => $nhapHang->so_container,
                'so_luong_ton'   => $soLuongKho,
                'so_luong_xuat'   => $so_luong_xuat,
            ];
        }
        return response()->json(['data' => $data]);
    }
}
