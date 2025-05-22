<?php

namespace App\Http\Controllers;

use App\Models\YeuCauChuyenTauChiTietSua;
use App\Models\YeuCauSua;
use Illuminate\Http\Request;
use App\Models\YeuCauChuyenTauChiTiet;
use App\Models\CongChuc;
use App\Models\DoanhNghiep;
use App\Models\HangTrongCont;
use App\Models\NhapHang;
use App\Models\NiemPhong;
use App\Models\TienTrinh;
use App\Models\YeuCauChuyenTau;
use App\Models\TheoDoiHangHoa;
use App\Models\TheoDoiTruLui;
use App\Models\TheoDoiTruLuiChiTiet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class YeuCauChuyenTauController extends Controller
{
    public function danhSachYeuCauChuyenTau()
    {
        return view('quan-ly-kho.yeu-cau-chuyen-tau.danh-sach-yeu-cau-chuyen-tau');
    }

    public function themYeuCauChuyenTau()
    {
        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            $toKhaiDangXuLys = YeuCauChuyenTauChiTiet::join('nhap_hang', 'yeu_cau_chuyen_tau_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('yeu_cau_chuyen_tau', 'yeu_cau_chuyen_tau_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_chuyen_tau.ma_yeu_cau')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->where('yeu_cau_chuyen_tau.trang_thai', "1")
                ->pluck('yeu_cau_chuyen_tau_chi_tiet.so_to_khai_nhap');

            $toKhaiNhaps = NhapHang::where('nhap_hang.trang_thai', '2')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->whereNotIn('nhap_hang.so_to_khai_nhap', $toKhaiDangXuLys)
                ->get();

            return view('quan-ly-kho.yeu-cau-chuyen-tau.them-yeu-cau-chuyen-tau', data: compact('toKhaiNhaps', 'doanhNghiep'));
        }
        return redirect()->back();
    }

    public function themYeuCauChuyenTauSubmit(Request $request)
    {
        $rowsData = json_decode($request->rows_data, true);
        foreach ($rowsData as $row) {
        }
        try {
            DB::beginTransaction();
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();

            $yeuCauChuyenCont = YeuCauChuyenTau::create([
                'ma_doanh_nghiep' => $doanhNghiep->ma_doanh_nghiep,
                'ten_doan_tau' => $request->ten_doan_tau,
                'trang_thai' => '1',
                'ngay_yeu_cau' => now()
            ]);

            $sumSoLuong = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                ->where('nhap_hang.so_to_khai_nhap', $row['so_to_khai_nhap'])
                ->groupBy('hang_trong_cont.ma_hang_cont')
                ->selectRaw('SUM(hang_trong_cont.so_luong) AS total')
                ->pluck('total')
                ->sum();
            foreach ($rowsData as $row) {
                YeuCauChuyenTauChiTiet::insert([
                    'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                    'so_container' => $row['so_container'],
                    'tau_goc' => $row['tau_cu'],
                    'tau_dich' => $row['tau_moi'],
                    'so_luong' => $sumSoLuong,
                    'ma_yeu_cau' => $yeuCauChuyenCont->ma_yeu_cau
                ]);
                $this->themTienTrinh($row['so_to_khai_nhap'], "Doanh nghiệp đã yêu cầu di chuyển tàu số " . $yeuCauChuyenCont->ma_yeu_cau . " di chuyển từ tàu " . $row['tau_cu'] . " sang " . $row['tau_moi'], '');
            }
            if ($request->file('file')) {
                $this->luuFile($request, $yeuCauChuyenCont);
            }
            DB::commit();
            session()->flash('alert-success', 'Thêm yêu cầu thành công!');
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-chuyen-tau', ['ma_yeu_cau' => $yeuCauChuyenCont->ma_yeu_cau]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in ThemChuyenTau: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function suaYeuCauChuyenTau($ma_yeu_cau)
    {
        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            $toKhaiDangXuLys = YeuCauChuyenTauChiTiet::join('nhap_hang', 'yeu_cau_chuyen_tau_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('yeu_cau_chuyen_tau', 'yeu_cau_chuyen_tau_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_chuyen_tau.ma_yeu_cau')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->where('yeu_cau_chuyen_tau.trang_thai', "1")
                ->pluck('yeu_cau_chuyen_tau_chi_tiet.so_to_khai_nhap');

            $toKhaiTrongPhieu = YeuCauChuyenTauChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->pluck('so_to_khai_nhap');
            $toKhaiDangXuLys = $toKhaiDangXuLys->diff($toKhaiTrongPhieu);

            $toKhaiNhaps = NhapHang::where('nhap_hang.trang_thai', '2')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->whereNotIn('nhap_hang.so_to_khai_nhap', $toKhaiDangXuLys)
                ->get();
            $chiTiets = YeuCauChuyenTauChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->get();
            $yeuCau = YeuCauChuyenTau::find($ma_yeu_cau);

            return view('quan-ly-kho.yeu-cau-chuyen-tau.sua-yeu-cau-chuyen-tau', data: compact('toKhaiNhaps', 'doanhNghiep', 'chiTiets', 'ma_yeu_cau', 'yeuCau'));
        }
        return redirect()->back();
    }

    public function suaYeuCauChuyenTauSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $yeuCauChuyenTau = YeuCauChuyenTau::find($request->ma_yeu_cau);

            if ($yeuCauChuyenTau->trang_thai == '1') {
                $this->suaYeuCauDangChoDuyet($request, $yeuCauChuyenTau);
            } else {
                $this->suaYeuCauDaDuyet($request, $yeuCauChuyenTau);
            }
            DB::commit();
            session()->flash('alert-success', 'Sửa yêu cầu thành công!');
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-chuyen-tau', ['ma_yeu_cau' => $request->ma_yeu_cau]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in SuaChuyenTau: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function suaYeuCauDangChoDuyet($request, $yeuCauChuyenTau)
    {
        $rowsData = json_decode($request->rows_data, true);
        $yeuCauChuyenTau->ten_doan_tau = $request->ten_doan_tau;
        $yeuCauChuyenTau->save();
        YeuCauChuyenTauChitiet::where('ma_yeu_cau', $request->ma_yeu_cau)->delete();
        foreach ($rowsData as $row) {
            $sumSoLuong = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                ->where('nhap_hang.so_to_khai_nhap', $row['so_to_khai_nhap'])
                ->groupBy('hang_trong_cont.ma_hang_cont')
                ->selectRaw('SUM(hang_trong_cont.so_luong) AS total')
                ->pluck('total')
                ->sum();

            YeuCauChuyenTauChiTiet::insert([
                'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                'so_container' => $row['so_container'],
                'tau_goc' => $row['tau_cu'],
                'tau_dich' => $row['tau_moi'],
                'so_luong' => $sumSoLuong,
                'ma_yeu_cau' => $request->ma_yeu_cau
            ]);
        }
        if ($request->file('file')) {
            $this->luuFile($request, $yeuCauChuyenTau);
        }
    }
    public function suaYeuCauDaDuyet($request, $yeuCau)
    {
        $yeuCau->trang_thai = '3';
        $yeuCau->save();
        $suaYeuCau = YeuCauSua::create([
            'ten_doan_tau' => $request->ten_doan_tau,
            'ma_yeu_cau' => $request->ma_yeu_cau,
            'loai_yeu_cau' => 4,
        ]);
        if ($request->file('file')) {
            $this->luuFile($request, $suaYeuCau);
        }

        $rowsData = json_decode($request->rows_data, associative: true);
        foreach ($rowsData as $row) {
            $sumSoLuong = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                ->where('nhap_hang.so_to_khai_nhap', $row['so_to_khai_nhap'])
                ->groupBy('hang_trong_cont.ma_hang_cont')
                ->selectRaw('SUM(hang_trong_cont.so_luong) AS total')
                ->pluck('total')
                ->sum();

            YeuCauChuyenTauChiTietSua::insert([
                'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                'so_container' => $row['so_container'],
                'tau_goc' => $row['tau_cu'],
                'tau_dich' => $row['tau_moi'],
                'so_luong' => $sumSoLuong,
                'ma_sua_yeu_cau' => $suaYeuCau->ma_sua_yeu_cau
            ]);
            $this->themTienTrinh($row['so_to_khai_nhap'], "Doanh nghiệp đã yêu cầu sửa yêu cầu di chuyển tàu số " . $yeuCau->ma_yeu_cau . " di chuyển từ tàu " . $row['tau_cu'] . " sang " . $row['tau_moi'], '');
        }
    }
    public function xemSuaYeuCau(Request $request)
    {
        $yeuCau = YeuCauChuyenTau::find($request->ma_yeu_cau);
        $chiTietYeuCaus = YeuCauChuyenTauChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
        $suaYeuCau = YeuCauSua::where('ma_yeu_cau', $request->ma_yeu_cau)
            ->where('loai_yeu_cau', 4)
            ->first();
        $chiTietSuaYeuCaus = YeuCauChuyenTauChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->get();
        $doanhNghiep = DoanhNghiep::find($yeuCau->ma_doanh_nghiep);
        return view('quan-ly-kho.yeu-cau-chuyen-tau.xem-sua-yeu-cau-chuyen-tau', compact('yeuCau', 'chiTietYeuCaus', 'suaYeuCau', 'chiTietSuaYeuCaus', 'doanhNghiep'));
    }
    public function duyetSuaYeuCau(Request $request)
    {
        try {
            DB::beginTransaction();
            $suaYeuCau = YeuCauSua::find($request->ma_sua_yeu_cau);
            $yeuCau = YeuCauChuyenTau::find($request->ma_yeu_cau);

            $chiTietSuaYeuCaus = YeuCauChuyenTauChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->get();
            $soToKhaiSauSuas = $chiTietSuaYeuCaus->pluck('so_to_khai_nhap')->toArray();

            $chiTietYeuCaus = YeuCauChuyenTauChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
            $soToKhaiTruocSuas = $chiTietYeuCaus->pluck('so_to_khai_nhap')->toArray();

            $soToKhaiCanQuayNguoc = array_diff($soToKhaiTruocSuas, $soToKhaiSauSuas);
            $soToKhaiCanXuLy =  $soToKhaiSauSuas;

            $this->quayNguocYeuCau($soToKhaiCanQuayNguoc, $yeuCau);
            YeuCauChuyenTauChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->delete();
            $this->xuLySuaYeuCau($chiTietSuaYeuCaus, $soToKhaiCanXuLy, $yeuCau);

            $yeuCau->ten_doan_tau = $suaYeuCau->ten_doan_tau;
            $yeuCau->trang_thai = '2';
            if ($yeuCau->file_name && $suaYeuCau->file_name) {
                $yeuCau->file_name = $suaYeuCau->file_name;
                $yeuCau->file_path = $suaYeuCau->file_path;
            }
            $yeuCau->save();

            YeuCauChuyenTauChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->delete();
            YeuCauSua::find($request->ma_sua_yeu_cau)->delete();
            DB::commit();
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-chuyen-tau', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]);
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetSuaYeuCauTau: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function quayNguocYeuCau($soToKhaiCanQuayNguoc, $yeuCau)
    {
        foreach ($soToKhaiCanQuayNguoc as $soToKhai) {
            $chiTiet = YeuCauChuyenTauChiTiet::where('so_to_khai_nhap', $soToKhai)
                ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->first();
            TheoDoiHangHoa::where('so_to_khai_nhap', $soToKhai)
                ->where('ma_yeu_cau', $chiTiet->ma_yeu_cau)
                ->where('cong_viec', 4)
                ->delete();
            NhapHang::where('so_to_khai_nhap', $chiTiet->so_to_khai_nhap)->update([
                'phuong_tien_vt_nhap' => $chiTiet->tau_goc,
            ]);
            NiemPhong::where('so_container', $chiTiet->so_container)->update([
                'phuong_tien_vt_nhap' => $chiTiet->tau_goc,
            ]);
        }
    }

    public function xuLySuaYeuCau($chiTietSuaYeuCaus, $soToKhaiCanXuLy, $yeuCau)
    {
        $this->xoaTheoDoiTruLui($yeuCau);
        foreach ($chiTietSuaYeuCaus as $chiTietYeuCau) {
            if (in_array($chiTietYeuCau->so_to_khai_nhap, $soToKhaiCanXuLy)) {

                NhapHang::where('so_to_khai_nhap', $chiTietYeuCau->so_to_khai_nhap)->update([
                    'phuong_tien_vt_nhap' => $chiTietYeuCau->tau_dich,
                ]);
                NiemPhong::where('so_container', $chiTietYeuCau->so_container)->update([
                    'phuong_tien_vt_nhap' => $chiTietYeuCau->tau_dich,
                ]);
                $hangTrongConts = HangTrongCont::join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                    ->where('hang_hoa.so_to_khai_nhap', $chiTietYeuCau->so_to_khai_nhap)
                    ->where('hang_trong_cont.so_container', $chiTietYeuCau->so_container)
                    ->select('hang_trong_cont.*', 'hang_hoa.ma_hang')
                    ->get();
                foreach ($hangTrongConts as $hangTrongCont) {
                    $this->themTheoDoiTruLui($chiTietYeuCau->so_to_khai_nhap, $yeuCau, $hangTrongCont->ma_hang);
                }
                $this->themTienTrinh($chiTietYeuCau->so_to_khai_nhap, "Đã sửa yêu cầu chuyển tàu số " . $yeuCau->ma_yeu_cau  . ", cán bộ công chức phụ trách: " . $yeuCau->congChuc->ten_cong_chuc, $yeuCau->congChuc->ma_cong_chuc);
            }
            YeuCauChuyenTauChiTiet::insert([
                'so_to_khai_nhap' => $chiTietYeuCau->so_to_khai_nhap,
                'so_container' => $chiTietYeuCau->so_container,
                'tau_goc' => $chiTietYeuCau->tau_goc,
                'tau_dich' => $chiTietYeuCau->tau_dich,
                'so_luong' => $chiTietYeuCau->so_luong,
                'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
            ]);
        }
    }
    public function huySuaYeuCau(Request $request)
    {
        $yeuCau = YeuCauChuyenTau::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = '2';
        $yeuCau->save();
        $suaYeuCau = YeuCauSua::find($request->ma_sua_yeu_cau);
        YeuCauChuyenTauChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->delete();
        $suaYeuCau->delete();

        $chiTiets = YeuCauChuyenTauChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
        foreach ($chiTiets as $chiTiet) {
            if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                $yeuCau->ghi_chu = "Doanh nghiệp hủy đề nghị sửa: " . $request->ghi_chu;
                $this->themTienTrinh($chiTiet->so_to_khai_nhap, "Doanh nghiệp hủy đề nghị sửa yêu cầu chuyển tàu số " . $yeuCau->ma_yeu_cau, '');
            } else {
                $yeuCau->ghi_chu = "Công chức từ chối đề nghị sửa: " . $request->ghi_chu;
                $this->themTienTrinh($chiTiet->so_to_khai_nhap, "Công chức từ chối đề nghị sửa yêu cầu chuyển tàu số " . $yeuCau->ma_yeu_cau, $this->getCongChucHienTai()->ma_cong_chuc);
            }
        }
        $yeuCau->save();

        session()->flash('alert-success', 'Hủy yêu cầu sửa thành công!');
        return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-chuyen-tau', ['ma_yeu_cau' => $request->ma_yeu_cau]);
    }



    public function thongTinYeuCauChuyenTau($ma_yeu_cau)
    {
        $yeuCau = YeuCauChuyenTau::where('ma_yeu_cau', $ma_yeu_cau)
            ->leftJoin('cong_chuc', 'yeu_cau_chuyen_tau.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->first();
        $doanhNghiep = DoanhNghiep::find($yeuCau->ma_doanh_nghiep);

        $chiTiets = YeuCauChuyenTau::join('yeu_cau_chuyen_tau_chi_tiet', 'yeu_cau_chuyen_tau.ma_yeu_cau', '=', 'yeu_cau_chuyen_tau_chi_tiet.ma_yeu_cau')
            ->where('yeu_cau_chuyen_tau.ma_yeu_cau', $ma_yeu_cau)
            ->get();

        $congChucs = CongChuc::where('is_chi_xem', 0)->get();
        return view('quan-ly-kho.yeu-cau-chuyen-tau.thong-tin-yeu-cau-chuyen-tau', compact('yeuCau', 'chiTiets', 'doanhNghiep', 'congChucs')); // Pass data to the view
    }
    public function duyetYeuCauChuyenTau(Request $request)
    {
        $yeuCau = YeuCauChuyenTau::find($request->ma_yeu_cau);

        try {
            DB::beginTransaction();
            if ($yeuCau) {
                $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
                $congChucPhuTrach = CongChuc::find($request->ma_cong_chuc);
                $chiTietYeuCaus = YeuCauChuyenTauChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
                foreach ($chiTietYeuCaus as $chiTietYeuCau) {
                    NhapHang::where('so_to_khai_nhap', $chiTietYeuCau->so_to_khai_nhap)->update([
                        'phuong_tien_vt_nhap' => $chiTietYeuCau->tau_dich,
                    ]);
                    NiemPhong::where('so_container', $chiTietYeuCau->so_container)->update([
                        'phuong_tien_vt_nhap' => $chiTietYeuCau->tau_dich,
                    ]);
                    $hangTrongConts = HangTrongCont::join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                        ->where('hang_hoa.so_to_khai_nhap', $chiTietYeuCau->so_to_khai_nhap)
                        ->where('hang_trong_cont.so_container', $chiTietYeuCau->so_container)
                        ->select('hang_trong_cont.*', 'hang_hoa.ma_hang')
                        ->get();
                    foreach ($hangTrongConts as $row) {
                        $ptvtChoHang = NiemPhong::where('so_container', $row->so_container)->first()->phuong_tien_vt_nhap ?? '';
                        $so_seal = NiemPhong::where('so_container', $row->so_container)->first()->so_seal ?? '';
                        TheoDoiHangHoa::insert([
                            'so_to_khai_nhap' => $chiTietYeuCau->so_to_khai_nhap,
                            'ma_hang'  => $row->ma_hang,
                            'thoi_gian'  => now(),
                            'so_luong_xuat'  => $row->so_luong,
                            'so_luong_ton'  => $row->so_luong,
                            'phuong_tien_cho_hang' => $ptvtChoHang,
                            'cong_viec' => 4,
                            'phuong_tien_nhan_hang' => '',
                            'so_container' => $row->so_container,
                            'so_seal' => $so_seal,
                            'ma_cong_chuc' => $congChucPhuTrach->ma_cong_chuc,
                            'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
                        ]);
                        $this->themTheoDoiTruLui($chiTietYeuCau->so_to_khai_nhap, $yeuCau, $row->ma_hang);
                    }
                    $this->themTienTrinh($chiTietYeuCau->so_to_khai_nhap, "Đã duyệt yêu cầu di chuyển tàu số " . $request->ma_yeu_cau . " di chuyển từ tàu " . $chiTietYeuCau->tau_goc . " sang " . $chiTietYeuCau->tau_dich . ", cán bộ công chức phụ trách: " . $congChucPhuTrach->ten_cong_chuc, $congChuc->ma_cong_chuc);
                }
                $yeuCau->ma_cong_chuc = $congChucPhuTrach->ma_cong_chuc;
                $yeuCau->ngay_hoan_thanh = now();
                $yeuCau->trang_thai = '2';
                $yeuCau->save();
                session()->flash('alert-success', 'Duyệt yêu cầu thành công!');
            }

            DB::commit();
            return redirect()->route('quan-ly-kho.danh-sach-yeu-cau-chuyen-tau');

            // return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetYeuCauChuyenTau: ' . $e->getMessage());
            return redirect()->back();
        }
    }



    public function huyYeuCauChuyenTau(Request $request)
    {
        try {
            DB::beginTransaction();
            $yeuCau = YeuCauChuyenTau::find($request->ma_yeu_cau);
            if ($yeuCau->trang_thai == "1") {
                if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
                    $this->huyYeuCauChuyenTauFunc($request->ma_yeu_cau, $request->ghi_chu, "Cán bộ công chức", '');
                } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                    $this->huyYeuCauChuyenTauFunc($request->ma_yeu_cau, $request->ghi_chu, "Doanh nghiệp", '');
                }
            } elseif ($yeuCau->trang_thai == "2") {
                $this->huyYeuCauDaDuyet($request);
            } else {
                $this->duyetHuyYeuCau($request);
            }
            session()->flash('alert-success', 'Hủy yêu cầu thành công!');
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in huyYeuCauChuyenTau: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function huyYeuCauChuyenTauFunc($ma_yeu_cau, $ghi_chu, $user, $ly_do)
    {
        $yeuCau = YeuCauChuyenTau::find($ma_yeu_cau);
        if ($yeuCau) {
            if ($yeuCau->trang_thai == "1") {

                $soToKhaiNhaps = YeuCauChuyenTauChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->pluck('so_to_khai_nhap');
                if ($user == "Cán bộ công chức") {
                    $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
                    foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                        $this->themTienTrinh($soToKhaiNhap, "Cán bộ công chức đã hủy yêu cầu di chuyển tàu số " . $ma_yeu_cau, $congChuc->ma_cong_chuc);
                    }
                } elseif ($user == "Doanh nghiệp") {
                    foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                        $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đã hủy yêu cầu di chuyển tàu số " . $ma_yeu_cau, '');
                    }
                } elseif ($user == "Hệ thống") {
                    foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                        $this->themTienTrinh($soToKhaiNhap, "Hệ thống đã hủy yêu cầu di chuyển tàu số " . $ma_yeu_cau . $ly_do, '');
                    }
                }

                $yeuCau->trang_thai = '0';
                $yeuCau->ghi_chu = $ghi_chu;
                $yeuCau->save();
            }
        }
    }

    public function huyHuyYeuCau(Request $request)
    {
        $yeuCau = YeuCauChuyenTau::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = '2';

        $soToKhaiNhaps = YeuCauChuyenTauChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_to_khai_nhap');
        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $yeuCau->ghi_chu = "Công chức từ chối đề nghị hủy: " . $request->ghi_chu;
                $this->themTienTrinh($soToKhaiNhap, "Cán bộ công chức đã hủy yêu cầu chuyển tàu số " . $request->ma_yeu_cau, $congChuc->ma_cong_chuc);
            }
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $yeuCau->ghi_chu = "Doanh nghiệp hủy đề nghị hủy: " . $request->ghi_chu;
                $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đã hủy yêu cầu chuyển tàu số " . $request->ma_yeu_cau, '');
            }
        }
        $yeuCau->save();
        session()->flash('alert-success', 'Hủy đề nghị hủy thành công');
        return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-tieu-huy', ['ma_yeu_cau' => $request->ma_yeu_cau]);
    }
    public function huyYeuCauDaDuyet(Request $request)
    {
        $yeuCau = YeuCauChuyenTau::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = '4';
        $yeuCau->ghi_chu = $request->ghi_chu;
        $yeuCau->save();

        $soToKhaiNhaps = YeuCauChuyenTauChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_to_khai_nhap');
        foreach ($soToKhaiNhaps as $soToKhaiNhap) {
            $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đề nghị hủy yêu cầu chuyển tàu số " . $request->ma_yeu_cau, '');
        }
    }

    public function duyetHuyYeuCau(Request $request)
    {
        $yeuCau = YeuCauChuyenTau::find($request->ma_yeu_cau);
        $soToKhaiNhaps = YeuCauChuyenTauChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_to_khai_nhap');

        $this->quayNguocYeuCau($soToKhaiNhaps, $yeuCau);
        foreach ($soToKhaiNhaps as $soToKhaiNhap) {
            TheoDoiHangHoa::where('so_to_khai_nhap', $soToKhaiNhap)
                ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->where('cong_viec', 2)
                ->delete();
            TheoDoiTruLui::where('so_to_khai_nhap', $soToKhaiNhap)
                ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->where('cong_viec', 2)
                ->delete();
        }
        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $this->themTienTrinh($soToKhaiNhap, "Cán bộ công chức đã duyệt đề nghị hủy yêu cầu chuyển tàu số " . $request->ma_yeu_cau, $congChuc->ma_cong_chuc);
            }
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đã hủy đề nghị hủy yêu cầu chuyển tàu số " . $request->ma_yeu_cau, '');
            }
        }
        $yeuCau->trang_thai = '0';
        $yeuCau->ghi_chu = "Công chức duyệt đề nghị hủy: " . $request->ghi_chu;
        $yeuCau->save();
    }

    public function duyetHoanThanh(Request $request)
    {
        $yeuCau = YeuCauChuyenTau::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = "Đã hoàn thành";
        $yeuCau->save();
        session()->flash('alert-success', 'Duyệt hoàn thành yêu cầu thành công');
        return redirect()->back();
    }
    public function thayDoiCongChucChuyenTau(Request $request)
    {
        YeuCauChuyenTau::find($request->ma_yeu_cau)->update([
            'ma_cong_chuc' => $request->ma_cong_chuc
        ]);
        session()->flash('alert-success', 'Thay đổi công chức thành công');
        return redirect()->back();
    }

    public function themTheoDoiTruLui($so_to_khai_nhap, $yeuCau, $ma_hang)
    {
        $hangHoas = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('hang_hoa.ma_hang', $ma_hang)
            ->get();
        $nhapHang = NhapHang::find($so_to_khai_nhap);
        $theoDoi = TheoDoiTruLui::where('cong_viec', 4)->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)->where('so_to_khai_nhap', $so_to_khai_nhap)->first();
        if (!$theoDoi) {
            $theoDoi = TheoDoiTruLui::create([
                'so_to_khai_nhap' => $so_to_khai_nhap,
                'so_ptvt_nuoc_ngoai' => '',
                'ngay_them' => now(),
                'cong_viec' => 4,
                'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
            ]);
        }
        foreach ($hangHoas as $hangHoa) {
            TheoDoiTruLuiChiTiet::insert(
                [
                    'ten_hang' => $hangHoa->ten_hang,
                    'so_luong_xuat' => 0,
                    'so_luong_chua_xuat' => $hangHoa->so_luong,
                    'ma_theo_doi' => $theoDoi->ma_theo_doi,
                    'so_container' => $hangHoa->so_container,
                    'so_seal' => '',
                    'phuong_tien_vt_nhap' => NiemPhong::where('so_container', $hangHoa->so_container)->first()->phuong_tien_vt_nhap ?? "",
                ]
            );
        }
    }
    public function xoaTheoDoiTruLui($yeuCau)
    {
        TheoDoiTruLuiChiTiet::whereIn('ma_theo_doi', function ($query) use ($yeuCau) {
            $query->select('ma_theo_doi')
                ->from('theo_doi_tru_lui')
                ->where('cong_viec', 4)
                ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau);
        })->delete();

        TheoDoiTruLui::where('cong_viec', 4)
            ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
            ->delete();
    }
    public function themTienTrinh($so_to_khai_nhap, $ten_cong_viec, $ma_cong_chuc)
    {
        TienTrinh::insert([
            'so_to_khai_nhap' => $so_to_khai_nhap,
            'ten_cong_viec' => $ten_cong_viec,
            'ngay_thuc_hien' => now(),
            'ma_cong_chuc' => $ma_cong_chuc
        ]);
    }
    public function luuFile($request, $yeuCau)
    {
        if ($yeuCau->file_name) {
            Storage::delete('public/' . $yeuCau->file->path);
        }

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();

        while (Storage::exists('public/yeu_cau_chuyen_tau/' . $fileName)) {
            $fileInfo = pathinfo(path: $fileName);
            $fileName = $fileInfo['filename'] . '_' . time() . '.' . $fileInfo['extension'];
        }

        $filePath = $file->storeAs('yeu_cau_chuyen_tau', $fileName, 'public');

        $yeuCau->file_name = $fileName;
        $yeuCau->file_path = $filePath;
        $yeuCau->save();
    }
    public function downloadFile($maYeuCau, $xemSua = false)
    {
        if ($xemSua) {
            $yeuCau = YeuCauSua::findOrFail($maYeuCau);
        } else {
            $yeuCau = YeuCauChuyenTau::findOrFail($maYeuCau);
        }

        if (!$yeuCau->file_name) {
            session()->flash('alert-danger', 'Không tìm thấy file trong hệ thống');
            return redirect()->back();
        }

        $filePath = storage_path('app/public/' . $yeuCau->file_path);
        return response()->download($filePath, $yeuCau->file_name);
    }
    private function getCongChucHienTai()
    {
        return CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
    }

    public function getYeuCauChuyenTau(Request $request)
    {
        if ($request->ajax()) {
            if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
                $data = YeuCauChuyenTau::with(['doanhNghiep', 'yeuCauChuyenTauChiTiet'])
                    ->join('doanh_nghiep', 'yeu_cau_chuyen_tau.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
                    ->join('yeu_cau_chuyen_tau_chi_tiet', 'yeu_cau_chuyen_tau_chi_tiet.ma_yeu_cau', 'yeu_cau_chuyen_tau.ma_yeu_cau')
                    ->select(
                        'doanh_nghiep.ten_doanh_nghiep',
                        'yeu_cau_chuyen_tau.ma_yeu_cau',
                        'yeu_cau_chuyen_tau.trang_thai',
                        'yeu_cau_chuyen_tau.ngay_yeu_cau',
                        DB::raw('GROUP_CONCAT(DISTINCT yeu_cau_chuyen_tau_chi_tiet.so_to_khai_nhap ORDER BY yeu_cau_chuyen_tau_chi_tiet.so_to_khai_nhap ASC SEPARATOR ", ") as so_to_khai_nhap_list')
                    )
                    ->groupBy('yeu_cau_chuyen_tau.ma_yeu_cau')
                    ->orderBy('ma_yeu_cau', 'desc')
                    ->get();
            } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                $maDoanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep;
                $data = YeuCauChuyenTau::with(['doanhNghiep', 'yeuCauChuyenTauChiTiet'])
                    ->join('doanh_nghiep', 'yeu_cau_chuyen_tau.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
                    ->join('yeu_cau_chuyen_tau_chi_tiet', 'yeu_cau_chuyen_tau_chi_tiet.ma_yeu_cau', 'yeu_cau_chuyen_tau.ma_yeu_cau')
                    ->where('yeu_cau_chuyen_tau.ma_doanh_nghiep', $maDoanhNghiep)
                    ->select(
                        'doanh_nghiep.ten_doanh_nghiep',
                        'yeu_cau_chuyen_tau.ma_yeu_cau',
                        'yeu_cau_chuyen_tau.trang_thai',
                        'yeu_cau_chuyen_tau.ngay_yeu_cau',
                        DB::raw('GROUP_CONCAT(DISTINCT yeu_cau_chuyen_tau_chi_tiet.so_to_khai_nhap ORDER BY yeu_cau_chuyen_tau_chi_tiet.so_to_khai_nhap ASC SEPARATOR ", ") as so_to_khai_nhap_list')
                    )
                    ->groupBy('yeu_cau_chuyen_tau.ma_yeu_cau')
                    ->orderBy('ma_yeu_cau', 'desc')
                    ->get();
            }

            return DataTables::of($data)
                ->addIndexColumn() // Adds auto-incrementing index
                ->editColumn('ngay_yeu_cau', function ($yeuCau) {
                    return Carbon::parse($yeuCau->ngay_yeu_cau)->format('d-m-Y');
                })
                ->addColumn('ten_doanh_nghiep', function ($yeuCau) {
                    return $yeuCau->ten_doanh_nghiep ?? 'N/A';
                })
                ->addColumn('so_to_khai_nhap_list', function ($yeuCau) {
                    return $yeuCau->so_to_khai_nhap_list ?? 'N/A';
                })
                ->editColumn('trang_thai', function ($yeuCau) {
                    $status = trim($yeuCau->trang_thai);

                    $statusLabels = [
                        '1' => ['text' => 'Đang chờ duyệt', 'class' => 'text-primary'],
                        '2' => ['text' => 'Đã duyệt', 'class' => 'text-success'],
                        '3' => ['text' => 'Doanh nghiệp đề nghị sửa yêu cầu', 'class' => 'text-warning'],
                        '4' => ['text' => 'Doanh nghiệp đề nghị hủy yêu cầu', 'class' => 'text-danger'],
                        '0' => ['text' => 'Đã hủy', 'class' => 'text-danger'],
                    ];
                    return isset($statusLabels[$status])
                        ? "<span class='{$statusLabels[$status]['class']}'>{$statusLabels[$status]['text']}</span>"
                        : '<span class="text-muted">Trạng thái không xác định</span>';
                })
                ->rawColumns(['trang_thai', 'action']) // Allows HTML in status & action columns
                ->make(true);
        }
    }
}
