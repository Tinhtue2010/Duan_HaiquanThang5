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
        $ptvtXuatCanhs = PTVTXuatCanh::all();
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

            $uniqueSoToKhaiNhap = array_unique(array_column($rowsData, 'so_to_khai_nhap'));
            $uniqueSoToKhaiNhap = array_values($uniqueSoToKhaiNhap);
            foreach ($uniqueSoToKhaiNhap as $soToKhaiNhap) {
                $this->xuatHangService->themTienTrinh($soToKhaiNhap, "Doanh nghiệp tạo phiếu xuất hàng số " . $xuatHang->so_to_khai_xuat, '');
            }
            $xuatHang->tong_so_luong = $this->xuatHangService->getTongSoLuongHangXuat($xuatHang->so_to_khai_xuat);
            $xuatHang->ten_phuong_tien_vt = $this->xuatHangService->getPTVTXuatCanhCuaPhieu($xuatHang->so_to_khai_xuat);
            $xuatHang->save();
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
        $ptvtXuatCanhs = PTVTXuatCanh::all();
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


    public function lichSuSuaPhieu($so_to_khai_nhap)
    {
        $nhapHang = NhapHang::find($so_to_khai_nhap);
        if ($nhapHang) {
            $suaToKhais = XuatHangSua::where('so_to_khai_nhap', $so_to_khai_nhap)
                ->orderBy('ma_yeu_cau', 'desc')
                ->get();
        }
        return view('xuat-hang.lich-su-sua-phieu', data: compact('suaToKhais', 'nhapHang'));
    }


    //////
    public function suaToKhaiXuat($so_to_khai_xuat)
    {
        $xuatHang = XuatHang::find($so_to_khai_xuat);
        // $containers = $this->xuatHangService->getThongTinHangHoaHienTaiChoDuyet($xuatHang);


        $loaiHinhs = LoaiHinh::all();
        $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
        $ptvtXuatCanhs = PTVTXuatCanh::where('trang_thai', 1)->get();
        $ptvts = PTVTXuatCanhCuaPhieu::where('so_to_khai_xuat', $so_to_khai_xuat)
            ->with('PTVTXuatCanh')
            ->get();
        $PTVTcount = $ptvts->count();
        $nhapHangs = NhapHang::where('ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
            ->where('trang_thai', '2')
            ->get();
        $containers = $this->xuatHangService->getThongTinHangHoaHienTai();
        $xuatHangConts = XuatHangCont::join('hang_trong_cont', 'hang_trong_cont.ma_hang_cont', '=', 'xuat_hang_cont.ma_hang_cont')
            ->join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
            ->where('so_to_khai_xuat', $so_to_khai_xuat)
            ->get();
        $xuatHangContMap = $xuatHangConts->pluck('so_luong_xuat', 'ma_hang_cont');

        foreach ($containers as $container) {
            $container->so_luong_xuat = $xuatHangContMap[$container->ma_hang_cont] ?? 0; // Default to 0 if not found
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
            $rowsData = array_values($rowsData);

            $ptvtRowsData = json_decode($request->ptvt_rows_data, true);
            $xuatHang = XuatHang::find($request->so_to_khai_xuat);
            $suaXuatHang = $this->xuatHangService->themSuaXuatHang($request, $xuatHang);
            $this->xuatHangService->themSuaPTVTCuaPhieu($suaXuatHang->ma_yeu_cau, $ptvtRowsData);
            $this->xuatHangService->themChiTietSuaXuatHang($suaXuatHang, $rowsData);
            $this->xuatHangService->capNhatTrangThaiPhieuXuat($xuatHang);
            $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                ->select('so_to_khai_nhap')
                ->distinct()
                ->get();

            foreach ($xuatHangConts as $xuatHangCont) {
                $this->xuatHangService->themTienTrinh($xuatHangCont->so_to_khai_nhap, "Doanh nghiệp yêu cầu sửa phiếu xuất hàng số " . $request->so_to_khai_xuat, '');
            }
            DB::commit();
            if ($xuatHang->trang_thai == 3) {
                $this->duyetYeuCauSua($suaXuatHang->ma_yeu_cau);
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
            }

            $xuatHang->tong_so_luong = $this->xuatHangService->getTongSoLuongHangXuat($xuatHang->so_to_khai_xuat);
            $xuatHang->ten_phuong_tien_vt = $this->xuatHangService->getPTVTXuatCanhCuaPhieu($xuatHang->so_to_khai_xuat);
            $xuatHang->save();


            XuatHangSua::findOrFail($ma_yeu_cau)->delete();
            XuatHangChiTietSua::where('ma_yeu_cau', $ma_yeu_cau)->delete();
            PTVTXuatCanhCuaPhieuSua::where('ma_yeu_cau', $ma_yeu_cau)->delete();
            PTVTXuatCanhCuaPhieuTruocSua::where('ma_yeu_cau', $ma_yeu_cau)->delete();

            DB::commit();
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

                $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                    ->select('so_to_khai_nhap')
                    ->distinct()
                    ->get();

                if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
                    $congChuc = $this->xuatHangService->getCongChucHienTai();
                    foreach ($xuatHangConts as $xuatHangCont) {
                        $this->xuatHangService->themTienTrinh($xuatHangCont->so_to_khai_nhap, "Cán bộ công chức đã hủy yêu cầu sửa phiếu xuất số " . $xuatHang->so_to_khai_xuat, $congChuc->ma_cong_chuc);
                    }
                } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                    foreach ($xuatHangConts as $xuatHangCont) {
                        $this->xuatHangService->themTienTrinh($xuatHangCont->so_to_khai_nhap, "Doanh nghiệp đã hủy yêu cầu sửa phiếu xuất số " . $xuatHang->so_to_khai_xuat, '');
                    }
                }
                $suaXuatHang->delete();
                XuatHangChiTietSua::where('ma_yeu_cau', $ma_yeu_cau)->delete();
                PTVTXuatCanhCuaPhieuSua::where('ma_yeu_cau', $ma_yeu_cau)->delete();
                PTVTXuatCanhCuaPhieuTruocSua::where('ma_yeu_cau', $ma_yeu_cau)->delete();
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

                // return redirect()->back();
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
            } elseif ($xuatHang->trang_thai == 2) {
                $xuatHang->trang_thai = 8;
                $xuatHang->save();
            } elseif ($xuatHang->trang_thai == 11) {
                $xuatHang->trang_thai = 9;
                $xuatHang->save();
            } elseif ($xuatHang->trang_thai == 12) {
                $xuatHang->trang_thai = 10;
                $xuatHang->save();
            } else {
                $this->duyetYeuCauHuy($xuatHang);
            }

            $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                ->select('so_to_khai_nhap')
                ->distinct()
                ->get();
            foreach ($xuatHangConts as $xuatHangCont) {
                $this->xuatHangService->themTienTrinh($xuatHangCont->so_to_khai_nhap, "Doanh nghiệp yêu cầu hủy phiếu xuất số " . $request->so_to_khai_xuat, '');
            }
            session()->flash('alert-success', 'Yêu cầu hủy phiếu xuất thành công!');
        }
        return redirect()->back();
    }

    public function huyPhieu($xuatHang)
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
            TheoDoiHangHoa::where('so_to_khai_nhap', $xuatHang->so_to_khai_nhap)
                ->where('cong_viec', 1)
                ->where('ma_yeu_cau', $xuatHang->so_to_khai_xuat)
                ->delete();

            $xuatHang->trang_thai = '0';
            $xuatHang->ghi_chu = 'Doanh nghiệp hủy phiếu';
            $xuatHang->save();

            $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                ->select('so_to_khai_nhap')
                ->distinct()
                ->get();
            foreach ($xuatHangConts as $xuatHangCont) {
                $nhapHang = NhapHang::find($xuatHangCont->so_to_khai_nhap);
                $nhapHang->trang_thai = '2';
                $nhapHang->save();
                $this->xuatHangService->themTienTrinh($xuatHangCont->so_to_khai_nhap, "Doanh nghiệp hủy phiếu xuất số " . $xuatHang->so_to_khai_xuat, '');
            }
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
            TheoDoiHangHoa::where('so_to_khai_nhap', $xuatHang->so_to_khai_nhap)
                ->where('cong_viec', 1)
                ->where('ma_yeu_cau', $xuatHang->so_to_khai_xuat)
                ->delete();
            TheoDoiTruLui::where('so_to_khai_nhap', $xuatHang->so_to_khai_nhap)
                ->where('ma_yeu_cau', $xuatHang->so_to_khai_xuat)
                ->where('cong_viec', 1)
                ->delete();


            $xuatHang->trang_thai = '0';
            $xuatHang->ghi_chu = 'Công chức duyệt yêu cầu hủy';
            $xuatHang->save();

            $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                ->select('so_to_khai_nhap')
                ->distinct()
                ->get();
            foreach ($xuatHangConts as $xuatHangCont) {
                $nhapHang = NhapHang::find($xuatHangCont->so_to_khai_nhap);
                $nhapHang->trang_thai = '2';
                $nhapHang->save();
                $this->xuatHangService->themTienTrinh($xuatHangCont->so_to_khai_nhap, "Công chức duyệt yêu cầu hủy phiếu xuất số " . $xuatHang->so_to_khai_xuat, $this->xuatHangService->getCongChucHienTai()->ma_cong_chuc);
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


        $xuatHangConts = XuatHangCont::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
            ->select('so_to_khai_nhap')
            ->distinct()
            ->get();
        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $xuatHang->ghi_chu = 'Công chức từ chối yêu cầu hủy: ' . $xuatHang->ghi_chu;
            foreach ($xuatHangConts as $xuatHangCont) {
                $this->xuatHangService->themTienTrinh($xuatHangCont->so_to_khai_nhap, "Công chức từ chối yêu cầu hủy phiếu xuất số " . $request->so_to_khai_xuat, $this->xuatHangService->getCongChucHienTai()->ma_cong_chuc);
            }
            session()->flash('alert-success', 'Từ chối yêu cầu hủy phiếu xuất thành công!');
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $xuatHang->ghi_chu = 'Doanh nghiệp thu hồi yêu cầu hủy: ' . $xuatHang->ghi_chu;
            foreach ($xuatHangConts as $xuatHangCont) {
                $this->xuatHangService->themTienTrinh($xuatHangCont->so_to_khai_nhap, "Doanh nghiệp thu hồi yêu cầu hủy phiếu xuất số " . $request->so_to_khai_xuat, '');
            }
            session()->flash('alert-success', 'Thu hồi yêu cầu hủy phiếu xuất thành công!');
        }
        $xuatHang->save();
        return redirect()->back();
    }

    // public function huyToKhai(Request $request)
    // {
    //     if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
    //         $this->xuatHangService->huyPhieuXuatFunc($request->so_to_khai_xuat, $request->ghi_chu, "Cán bộ công chức", '');
    //     } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
    //         $this->xuatHangService->huyPhieuXuatFunc($request->so_to_khai_xuat, $request->ghi_chu, "Doanh nghiệp", '');
    //     }
    //     session()->flash('alert-success', 'Hủy phiếu xuất thành công!');
    //     return redirect()->back();


    public function duyetNhanhThucXuat(Request $request)
    {
        $ptvtXuatCanhs = PTVTXuatCanh::all();
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
        return Excel::download(new ToKhaiXuatExport($so_to_khai_xuat), $fileName);
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
                $isMoreThan60Days = $ngayGiaHan->diffInDays($now, false) > 90;
            } else {
                $isMoreThan60Days = false;
            }
            return response()->json(['data' => $isMoreThan60Days]);
        } catch (\Exception $e) {
            // Log the exception details
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
                'xuat_hang.lan_xuat_canh',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ten_doan_tau',
                'xuat_hang.ghi_chu',
                'xuat_hang.ma_cong_chuc',
                'xuat_hang.so_seal_cuoi_ngay',
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
            // ->when(!empty($request->so_ptvt_xuat_canh), function ($query) use ($request) {
            //     return $query->where('ptvt_xuat_canh_cua_phieu.so_ptvt_xuat_canh', $request->so_ptvt_xuat_canh);
            // })
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
                'xuat_hang.lan_xuat_canh',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ten_doan_tau',
                'xuat_hang.ghi_chu',
                'xuat_hang.ma_cong_chuc',
                'xuat_hang.so_seal_cuoi_ngay',
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

        return DataTables::of($query)
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
                ];

                return isset($statusLabels[$status])
                    ? "<span class='{$statusLabels[$status]['class']}'>{$statusLabels[$status]['text']}</span>"
                    : '<span class="text-muted">Trạng thái không xác định</span>';
            })
            ->rawColumns(['trang_thai', 'action'])
            ->make(true);
    }
}
