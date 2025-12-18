<?php

namespace App\Http\Controllers;

use App\Models\YeuCauContainerChiTietSua;
use App\Models\YeuCauContainerChiTiet;
use App\Models\CongChuc;
use App\Models\Container;
use App\Models\DoanhNghiep;
use App\Models\HangHoa;
use App\Models\HangTrongCont;
use App\Models\NhapHang;
use App\Models\NiemPhong;
use App\Models\Seal;
use App\Models\TienTrinh;
use App\Models\YeuCauChuyenContainer;
use App\Models\TheoDoiHangHoa;
use App\Models\TheoDoiTruLui;
use App\Models\TheoDoiTruLuiChiTiet;
use App\Models\YCContainerMaHangContMoi;
use App\Models\YeuCauContainerHangHoa;
use App\Models\YeuCauContainerHangHoaSua;
use App\Models\YeuCauSua;
use App\Models\YeuCauTauCont;
use App\Models\YeuCauTauContChiTiet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class YeuCauContainerController extends Controller
{
    public function danhSachYeuCauContainer()
    {
        return view('quan-ly-kho.yeu-cau-container.danh-sach-yeu-cau-container');
    }


    public function themYeuCauContainer()
    {
        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $soContainers = Container::select('container.*')
                ->selectRaw('COALESCE(SUM(hang_trong_cont.so_luong), 0) as total_so_luong')
                ->join('hang_trong_cont', 'container.so_container', '=', 'hang_trong_cont.so_container')
                ->join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                ->join('nhap_hang', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->whereNotIn('nhap_hang.trang_thai', ['6', '5', '4', '7'])
                ->groupBy('container.so_container')
                ->get();

            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            $toKhaiDangXuLys = YeuCauContainerChiTiet::join('nhap_hang', 'yeu_cau_container_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('yeu_cau_chuyen_container', 'yeu_cau_container_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_chuyen_container.ma_yeu_cau')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->where('yeu_cau_chuyen_container.trang_thai', "1")
                ->pluck('yeu_cau_container_chi_tiet.so_to_khai_nhap');
            $toKhaiDangXuLy2s = YeuCauTauContChiTiet::join('nhap_hang', 'yeu_cau_tau_cont_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('yeu_cau_tau_cont', 'yeu_cau_tau_cont_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_tau_cont.ma_yeu_cau')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->where('yeu_cau_tau_cont.trang_thai', "1")
                ->pluck('yeu_cau_tau_cont_chi_tiet.so_to_khai_nhap');
            $toKhaiNhaps = NhapHang::where('nhap_hang.trang_thai', '2')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->whereNotIn('nhap_hang.so_to_khai_nhap', $toKhaiDangXuLys)
                ->whereNotIn('nhap_hang.so_to_khai_nhap', $toKhaiDangXuLy2s)
                ->get();

            return view('quan-ly-kho.yeu-cau-container.them-yeu-cau-container', data: compact('toKhaiNhaps', 'doanhNghiep', 'soContainers'));
        }
        return redirect()->back();
    }


    public function themYeuCauContainerSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();

            $yeuCauChuyenCont = YeuCauChuyenContainer::create([
                'ma_doanh_nghiep' => $doanhNghiep->ma_doanh_nghiep,
                'ten_doan_tau' => $request->ten_doan_tau,
                'trang_thai' => '1',
                'ngay_yeu_cau' => now()
            ]);


            if ($request->file('file')) {
                $this->luuFile($request, $yeuCauChuyenCont);
            }
            // Decode the JSON data from the form
            $rowsData = json_decode($request->rows_data, true);
            $groupedData = collect($rowsData)
                ->groupBy(function ($item) {
                    return $item['so_to_khai_nhap'] . '|' . $item['so_container_goc'] . '|' . $item['so_container_dich'];
                })
                ->map(function ($group) {
                    return [
                        'so_to_khai_nhap' => $group->first()['so_to_khai_nhap'],
                        'so_container_goc' => $group->first()['so_container_goc'],
                        'so_container_dich' => $group->first()['so_container_dich'],
                        'total_so_luong_chuyen' => $group->sum('so_luong_chuyen'),

                    ];
                })
                ->values();
            foreach ($groupedData as $row) {
                $so_luong_ton_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                    ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                    ->where('hang_trong_cont.so_container', $row['so_container_dich'])
                    ->whereIn('nhap_hang.trang_thai', ['2', '3'])
                    ->sum('hang_trong_cont.so_luong');

                $so_to_khai_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                    ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                    ->where('hang_trong_cont.so_container', $row['so_container_dich'])
                    ->whereIn('nhap_hang.trang_thai', ['2', '3'])
                    ->where('hang_trong_cont.so_luong', '!=', 0)
                    ->distinct()
                    ->pluck('nhap_hang.so_to_khai_nhap')
                    ->implode('</br>');
                $so_to_khai_cont_moi .= ($so_to_khai_cont_moi ? '</br>' : '') . $row['so_to_khai_nhap'];
                $tauGoc = NiemPhong::where('so_container', $row['so_container_goc'])->first()->phuong_tien_vt_nhap ?? "";
                $chiTietYeuCau = YeuCauContainerChiTiet::create([
                    'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                    'so_container_goc' => $row['so_container_goc'],
                    'so_container_dich' => $row['so_container_dich'],
                    'so_luong_chuyen' => $row['total_so_luong_chuyen'],
                    'so_luong_ton_cont_moi' => $so_luong_ton_cont_moi,
                    'so_to_khai_cont_moi' => $so_to_khai_cont_moi,
                    'tau_goc' => $tauGoc,
                    'ma_yeu_cau' => $yeuCauChuyenCont->ma_yeu_cau
                ]);
                foreach ($rowsData as $row2) {
                    if ($row2['so_to_khai_nhap'] == $row['so_to_khai_nhap'] && $row['so_container_goc'] == $row2['so_container_goc'] && $row['so_container_dich'] == $row2['so_container_dich']) {
                        YeuCauContainerHangHoa::insert([
                            'ma_hang_cont' => $row2['ma_hang_cont'],
                            'ten_hang' => $row2['ten_hang'],
                            'so_container_cu' => $row2['so_container_goc'],
                            'so_container_moi' => $row2['so_container_dich'],
                            'so_luong' => $row2['so_luong_chuyen'],
                            'ma_chi_tiet' => $chiTietYeuCau->ma_chi_tiet,
                        ]);
                    }
                }
                $this->themTienTrinh($row['so_to_khai_nhap'], "Doanh nghiệp thêm yêu cầu di chuyển hàng số " . $yeuCauChuyenCont->ma_yeu_cau, '');
            }

            DB::commit();
            $chiTietYeuCaus = YeuCauContainerChiTiet::where('ma_yeu_cau', $yeuCauChuyenCont->ma_yeu_cau)->get();
            $soToKhaiNhaps = $chiTietYeuCaus->pluck('so_to_khai_nhap')->unique()->values()->toArray();
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $this->themTheoDoiTruLui($soToKhaiNhap, $yeuCauChuyenCont);
            }
            session()->flash('alert-success', 'Thêm yêu cầu thành công!');
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau', ['ma_yeu_cau' => $yeuCauChuyenCont->ma_yeu_cau]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in ThemChuyenCont: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function kiemTraDaXuatHang($ma_yeu_cau)
    {
        $chiTiets = YeuCauChuyenContainer::join('yeu_cau_container_chi_tiet', 'yeu_cau_container_chi_tiet.ma_yeu_cau', 'yeu_cau_chuyen_container.ma_yeu_cau')
            ->where('yeu_cau_chuyen_container.ma_yeu_cau', '=', $ma_yeu_cau)
            ->get();
        foreach ($chiTiets as $chiTiet) {
            $tongSoLuong = YeuCauContainerHangHoa::where('ma_chi_tiet', $chiTiet->ma_chi_tiet)
                ->join('hang_trong_cont', 'hang_trong_cont.ma_hang_cont', 'yeu_cau_container_hang_hoa.ma_hang_cont')
                ->join('hang_hoa', 'hang_hoa.ma_hang', 'hang_trong_cont.ma_hang')
                ->first();
            if ($chiTiet->so_luong_chuyen != $tongSoLuong) {
                return false;
            }
        }
    }

    public function suaYeuCauContainer($ma_yeu_cau)
    {
        // if(!$this->kiemTraDaXuatHang($ma_yeu_cau)){
        //     session()->flash('alert-danger', 'Không thể sửa yêu cầu này do đã xuất hàng');
        //     return redirect()->back();
        // }
        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $soContainers = Container::select('container.*')
                ->selectRaw('COALESCE(SUM(hang_trong_cont.so_luong), 0) as total_so_luong')
                ->join('hang_trong_cont', 'container.so_container', '=', 'hang_trong_cont.so_container')
                ->join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                ->join('nhap_hang', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->whereNotIn('nhap_hang.trang_thai', ['6', '5', '4', '7'])
                ->groupBy('container.so_container')
                ->get();

            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            $toKhaiDangXuLys = YeuCauContainerChiTiet::join('nhap_hang', 'yeu_cau_container_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('yeu_cau_chuyen_container', 'yeu_cau_container_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_chuyen_container.ma_yeu_cau')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->where('yeu_cau_chuyen_container.trang_thai', "1")
                ->pluck('yeu_cau_container_chi_tiet.so_to_khai_nhap');
            $toKhaiDangXuLy2s = YeuCauTauContChiTiet::join('nhap_hang', 'yeu_cau_tau_cont_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('yeu_cau_tau_cont', 'yeu_cau_tau_cont_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_tau_cont.ma_yeu_cau')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->where('yeu_cau_tau_cont.trang_thai', "1")
                ->pluck('yeu_cau_tau_cont_chi_tiet.so_to_khai_nhap');

            $toKhaiTrongPhieu = YeuCauContainerChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->pluck('so_to_khai_nhap');
            $toKhaiDangXuLys = $toKhaiDangXuLys->diff($toKhaiTrongPhieu);

            $toKhaiNhaps = NhapHang::where('nhap_hang.trang_thai', '2')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->whereNotIn('nhap_hang.so_to_khai_nhap', $toKhaiDangXuLys)
                ->whereNotIn('nhap_hang.so_to_khai_nhap', $toKhaiDangXuLy2s)
                ->get();

            $chiTiets = YeuCauContainerChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->get();
            $chiTietHangHoas = YeuCauContainerHangHoa::join('yeu_cau_container_chi_tiet', 'yeu_cau_container_chi_tiet.ma_chi_tiet', '=', 'yeu_cau_container_hang_hoa.ma_chi_tiet')
                ->where('yeu_cau_container_chi_tiet.ma_yeu_cau', $ma_yeu_cau)
                ->select('yeu_cau_container_hang_hoa.*', 'yeu_cau_container_chi_tiet.so_to_khai_nhap')
                ->get()
                ->unique('ma_yeu_cau_hang_hoa')
                ->values();

            $yeuCau = YeuCauChuyenContainer::find($ma_yeu_cau);
            return view('quan-ly-kho.yeu-cau-container.sua-yeu-cau-container', data: compact('toKhaiNhaps', 'doanhNghiep', 'soContainers', 'chiTiets', 'ma_yeu_cau', 'yeuCau', 'chiTietHangHoas'));
        }
        return redirect()->back();
    }

    public function suaYeuCauContainerSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $yeuCau = YeuCauChuyenContainer::find($request->ma_yeu_cau);
            if ($yeuCau->trang_thai == '1') {
                $this->suaYeuCauDangChoDuyet($request, $yeuCau);
            } else {
                $this->suaYeuCauDaDuyet($request, $yeuCau);
            }
            DB::commit();
            session()->flash('alert-success', 'Sửa yêu cầu thành công!');
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau', ['ma_yeu_cau' => $request->ma_yeu_cau]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in SuaChuyenCont: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function suaYeuCauDaDuyet($request, $yeuCau)
    {
        $yeuCau->trang_thai = '3';
        $yeuCau->save();
        $suaYeuCau = YeuCauSua::create([
            'ten_doan_tau' => $request->ten_doan_tau,
            'ma_yeu_cau' => $request->ma_yeu_cau,
            'loai_yeu_cau' => 3,
        ]);
        if ($request->file('file')) {
            $this->luuFile($request, yeuCau: $suaYeuCau);
        }
        $this->xuLyThemChiTietYeuCau($request,  $suaYeuCau, $yeuCau);
    }

    public function suaYeuCauDangChoDuyet($request, $yeuCau)
    {
        $this->xoaTheoDoiTruLui($yeuCau);
        $yeuCau->ten_doan_tau = $request->ten_doan_tau;
        $yeuCau->save();
        YeuCauContainerChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->delete();
        $rowsData = json_decode($request->rows_data, true);

        $groupedData = collect($rowsData)
            ->groupBy(function ($item) {
                return $item['so_to_khai_nhap'] . '|' . $item['so_container_goc'] . '|' . $item['so_container_dich'];
            })
            ->map(function ($group) {
                return [
                    'so_to_khai_nhap' => $group->first()['so_to_khai_nhap'],
                    'so_container_goc' => $group->first()['so_container_goc'],
                    'so_container_dich' => $group->first()['so_container_dich'],
                    'total_so_luong_chuyen' => $group->sum('so_luong_chuyen')
                ];
            })
            ->values();


        foreach ($groupedData as $row) {
            $so_luong_ton_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('hang_trong_cont.so_container', $row['so_container_dich'])
                ->whereIn('nhap_hang.trang_thai', ['2', '3'])
                ->sum('hang_trong_cont.so_luong');

            $so_to_khai_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('hang_trong_cont.so_container', $row['so_container_dich'])
                ->whereIn('nhap_hang.trang_thai', ['2', '3'])
                ->where('hang_trong_cont.so_luong', '!=', 0)
                ->distinct()
                ->pluck('nhap_hang.so_to_khai_nhap')
                ->implode('</br>');
            $so_to_khai_cont_moi .= ($so_to_khai_cont_moi ? '</br>' : '') . $row['so_to_khai_nhap'];

            $tauGoc = NiemPhong::where('so_container', $row['so_container_goc'])->first()->phuong_tien_vt_nhap ?? "";
            $chiTietYeuCau = YeuCauContainerChiTiet::create([
                'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                'so_container_goc' => $row['so_container_goc'],
                'so_container_dich' => $row['so_container_dich'],
                'so_luong_chuyen' => $row['total_so_luong_chuyen'],
                'so_luong_ton_cont_moi' => $so_luong_ton_cont_moi,
                'so_to_khai_cont_moi' => $so_to_khai_cont_moi,
                'tau_goc' => $tauGoc,
                'ma_yeu_cau' => $request->ma_yeu_cau
            ]);

            foreach ($rowsData as $row2) {
                if ($row2['so_to_khai_nhap'] == $row['so_to_khai_nhap'] && $row['so_container_goc'] == $row2['so_container_goc'] && $row['so_container_dich'] == $row2['so_container_dich']) {
                    YeuCauContainerHangHoa::insert([
                        'ma_hang_cont' => $row2['ma_hang_cont'],
                        'ten_hang' => $row2['ten_hang'],
                        'so_container_cu' => $row2['so_container_goc'],
                        'so_container_moi' => $row2['so_container_dich'],
                        'so_luong' => $row2['so_luong_chuyen'],
                        'ma_chi_tiet' => $chiTietYeuCau->ma_chi_tiet,
                    ]);
                }
            }
        }
        if ($request->file('file')) {
            $this->luuFile($request, $yeuCau);
        }

        $chiTietYeuCaus = YeuCauContainerChiTiet::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)->get();
        $soToKhaiNhaps = $chiTietYeuCaus->pluck('so_to_khai_nhap')->unique()->values()->toArray();
        foreach ($soToKhaiNhaps as $soToKhaiNhap) {
            $this->themTheoDoiTruLui($soToKhaiNhap, $yeuCau);
        }
    }

    public function xuLyThemChiTietYeuCau($request, $suaYeuCau, $yeuCauCu)
    {
        $rowsData = json_decode($request->rows_data, true);
        $ma_yeu_cau = $yeuCauCu->ma_yeu_cau;

        $groupedData = collect($rowsData)
            ->groupBy(function ($item) {
                return $item['so_to_khai_nhap'] . '|' . $item['so_container_goc'] . '|' . $item['so_container_dich'];
            })
            ->map(function ($group) {
                return [
                    'so_to_khai_nhap' => $group->first()['so_to_khai_nhap'],
                    'so_container_goc' => $group->first()['so_container_goc'],
                    'so_container_dich' => $group->first()['so_container_dich'],
                    'total_so_luong_chuyen' => $group->sum('so_luong_chuyen')
                ];
            })
            ->values();
        foreach ($groupedData as $row) {
            $so_luong_ton_cont_moi = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('hang_trong_cont.so_container', $row['so_container_dich'])
                ->whereIn('nhap_hang.trang_thai', ['2', '3'])
                ->sum('hang_trong_cont.so_luong');

            $soLuongTrongDon = YeuCauContainerChiTiet::where('ma_yeu_cau', $ma_yeu_cau)
                ->where('so_container_dich', $row['so_container_dich'])
                ->where('so_container_goc', $row['so_container_goc'])
                ->sum('so_luong_chuyen');

            $soToKhaiList = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('hang_trong_cont.so_container', $row['so_container_dich'])
                ->whereIn('nhap_hang.trang_thai', ['2', '3'])
                ->distinct()
                ->pluck('nhap_hang.so_to_khai_nhap')
                ->toArray();

            // Check and add if not exists
            if (!in_array($row['so_to_khai_nhap'], $soToKhaiList)) {
                $soToKhaiList[] = $row['so_to_khai_nhap'];
            }

            $so_to_khai_cont_moi = implode('</br>', $soToKhaiList);
            $so_luong_ton_cont_moi -= $soLuongTrongDon;

            // $tauGoc = NhapHang::find($row['so_to_khai_nhap'])->phuong_tien_vt_nhap;
            $tauGoc = NiemPhong::where('so_container', $row['so_container_goc'])->first()->phuong_tien_vt_nhap ?? "";
            $chiTietYeuCau = YeuCauContainerChiTietSua::create([
                'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                'so_container_goc' => $row['so_container_goc'],
                'so_container_dich' => $row['so_container_dich'],
                'so_luong_chuyen' => $row['total_so_luong_chuyen'],
                'so_luong_ton_cont_moi' => $so_luong_ton_cont_moi,
                'tau_goc' => $tauGoc,
                'so_to_khai_cont_moi' => $so_to_khai_cont_moi,
                'ma_sua_yeu_cau' => $suaYeuCau->ma_sua_yeu_cau
            ]);

            foreach ($rowsData as $row2) {
                if ($row2['so_to_khai_nhap'] == $row['so_to_khai_nhap'] && $row['so_container_goc'] == $row2['so_container_goc'] && $row['so_container_dich'] == $row2['so_container_dich']) {
                    YeuCauContainerHangHoaSua::insert([
                        'ma_hang_cont' => $row2['ma_hang_cont'],
                        'ten_hang' => $row2['ten_hang'],
                        'so_container_cu' => $row2['so_container_goc'],
                        'so_container_moi' => $row2['so_container_dich'],
                        'so_luong' => $row2['so_luong_chuyen'],
                        'ma_chi_tiet' => $chiTietYeuCau->ma_chi_tiet,
                    ]);
                }
            }
            $this->themTienTrinh($row['so_to_khai_nhap'], "Doanh nghiệp đề nghị sửa yêu cầu chuyển container số " . $yeuCauCu->ma_yeu_cau, '');
        }
    }

    public function thongTinYeuCauContainer($ma_yeu_cau)
    {
        $yeuCau = YeuCauChuyenContainer::where('ma_yeu_cau', $ma_yeu_cau)
            ->leftJoin('cong_chuc', 'yeu_cau_chuyen_container.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->first();
        $doanhNghiep = DoanhNghiep::find($yeuCau->ma_doanh_nghiep);

        $soContainers = Container::select('container.so_container')
            ->selectRaw('COALESCE(SUM(hang_trong_cont.so_luong), 0) as total_so_luong')
            ->leftJoin('hang_trong_cont', 'container.so_container', '=', 'hang_trong_cont.so_container')
            ->groupBy('container.so_container');

        $chiTiets = YeuCauContainerChiTiet::with('yeuCauContainerHangHoa')->where('ma_yeu_cau', $ma_yeu_cau)->get();

        $seals = Seal::where('seal.trang_thai', 0)->get();
        $congChucs = CongChuc::where('is_chi_xem', 0)->where('status', 1)->get();
        return view('quan-ly-kho.yeu-cau-container.thong-tin-yeu-cau-container', compact('yeuCau', 'chiTiets', 'doanhNghiep', 'seals', 'congChucs')); // Pass data to the view
    }

    public function xemSuaYeuCau(Request $request)
    {
        $yeuCau = YeuCauChuyenContainer::find($request->ma_yeu_cau);
        $chiTietYeuCaus = YeuCauContainerChiTiet::with('yeuCauContainerHangHoa')->where('ma_yeu_cau', $request->ma_yeu_cau)->get();
        $suaYeuCau = YeuCauSua::where('ma_yeu_cau', $request->ma_yeu_cau)
            ->where('loai_yeu_cau', 3)
            ->first();
        $chiTietSuaYeuCaus = YeuCauContainerChiTietSua::with('yeuCauContainerHangHoa')->where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->get();
        $doanhNghiep = DoanhNghiep::find($yeuCau->ma_doanh_nghiep);
        return view('quan-ly-kho.yeu-cau-container.xem-sua-yeu-cau-container', compact('yeuCau', 'chiTietYeuCaus', 'suaYeuCau', 'chiTietSuaYeuCaus', 'doanhNghiep'));
    }
    public function duyetYeuCauContainer(Request $request)
    {
        try {
            DB::beginTransaction();

            $yeuCau = YeuCauChuyenContainer::find($request->ma_yeu_cau);
            if ($yeuCau) {
                $soContainerDich = YeuCauContainerChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_container_dich')->toArray();
                $soContainers = array_unique($soContainerDich);
                $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
                $congChucPhuTrach = CongChuc::find($request->ma_cong_chuc);

                foreach ($soContainers as $soContainer) {
                    $this->kiemTraContainer($soContainer);
                }

                $chiTietYeuCaus = YeuCauContainerChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
                foreach ($chiTietYeuCaus as $chiTietYeuCau) {
                    $yeuCauContainerHangHoas = YeuCauContainerHangHoa::where('ma_chi_tiet', $chiTietYeuCau->ma_chi_tiet)->get();
                    $this->xuLyContainer($chiTietYeuCau->so_container_dich, $chiTietYeuCau->tau_goc);

                    foreach ($yeuCauContainerHangHoas as $yeuCauContainerHangHoa) {
                        $hangTrongCont = HangTrongCont::find($yeuCauContainerHangHoa->ma_hang_cont);
                        $so_seal = NiemPhong::where('so_container', $hangTrongCont->so_container)->first()->so_seal ?? '';
                        $sumSoLuong = HangHoa::join('hang_trong_cont', 'hang_hoa.ma_hang', 'hang_trong_cont.ma_hang')
                            ->where('hang_hoa.ma_hang', $hangTrongCont->ma_hang)
                            ->sum('hang_trong_cont.so_luong');
                        $this->tienHanhChuyenCont($hangTrongCont, $yeuCauContainerHangHoa, $sumSoLuong);
                        // $ptvtChoHang = NiemPhong::where('so_container', $hangTrongCont->so_container)->first()->phuong_tien_vt_nhap ?? '';

                        // TheoDoiHangHoa::insert([
                        //     'so_to_khai_nhap' => $chiTietYeuCau->so_to_khai_nhap,
                        //     'ma_hang'  => $hangTrongCont->ma_hang,
                        //     'thoi_gian'  => now(),
                        //     'so_luong_xuat'  => $hangTrongContMoi->so_luong,
                        //     'so_luong_ton'  => $hangTrongContMoi->so_luong,
                        //     'phuong_tien_cho_hang' => $ptvtChoHang,
                        //     'cong_viec' => 3,
                        //     'phuong_tien_nhan_hang' => '',
                        //     'so_container' => $hangTrongContMoi->so_container,
                        //     'so_seal' => $so_seal,
                        //     'ma_cong_chuc' => $congChucPhuTrach->ma_cong_chuc,
                        //     'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
                        // ]);
                    }
                }


                $yeuCau->ma_cong_chuc = $congChucPhuTrach->ma_cong_chuc;
                $yeuCau->ngay_hoan_thanh = now();
                $yeuCau->trang_thai = '2';
                $yeuCau->save();

                $soToKhaiNhaps = YeuCauContainerChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)
                    ->pluck('so_to_khai_nhap')
                    ->unique()
                    ->values()
                    ->toArray();
                foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                    // $this->themTheoDoiTruLui($soToKhaiNhap, $yeuCau);
                    TheoDoiHangHoa::where('so_to_khai_nhap', $soToKhaiNhap)
                        ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                        ->where('cong_viec', 3)
                        ->update([
                            'ma_cong_chuc' => $request->ma_cong_chuc ?? '',
                        ]);
                    $this->themTienTrinh($soToKhaiNhap, "Đã duyệt yêu cầu di chuyển hàng số " . $request->ma_yeu_cau . " di chuyển hàng từ container " . $chiTietYeuCau->so_container_goc . " sang " . $chiTietYeuCau->so_container_dich . ", cán bộ công chức phụ trách: " . $congChucPhuTrach->ten_cong_chuc, $congChuc->ma_cong_chuc);
                }
                session()->flash('alert-success', 'Duyệt yêu cầu thành công!');
            }

            DB::commit();
            // return redirect()->back();
            return redirect()->route('quan-ly-kho.danh-sach-yeu-cau-container');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in duyetYeuCauContainer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
    public function tienHanhChuyenCont($hangTrongCont, $yeuCauContainerHangHoa, $sumSoLuong)
    {
        $isExisted = HangTrongCont::where('so_container', $yeuCauContainerHangHoa->so_container_moi)
            ->where('ma_hang', $hangTrongCont->ma_hang)
            ->exists();

        if ($hangTrongCont->so_luong == $yeuCauContainerHangHoa->so_luong && $hangTrongCont->so_luong == $sumSoLuong) {
            $hangTrongCont->so_container = $yeuCauContainerHangHoa->so_container_moi;
            $hangTrongContMoi = $hangTrongCont;
        } else if ($isExisted) {
            $hangTrongContMoi = HangTrongCont::where('so_container', $yeuCauContainerHangHoa->so_container_moi)
                ->where('ma_hang', operator: $hangTrongCont->ma_hang)
                ->first();
            $hangTrongCont->so_luong -= $yeuCauContainerHangHoa->so_luong;
            $hangTrongContMoi->so_luong += $yeuCauContainerHangHoa->so_luong;
            $hangTrongContMoi->is_da_chuyen_cont = 0;
            YCContainerMaHangContMoi::insert([
                'ma_yeu_cau_hang_hoa' => $yeuCauContainerHangHoa->ma_yeu_cau_hang_hoa,
                'ma_hang_cont' =>  $hangTrongContMoi->ma_hang_cont,
                'so_luong' => $yeuCauContainerHangHoa->so_luong,
                'loai_cont_moi' => 2,
            ]);
            $hangTrongContMoi->save();
        } else {
            $hangTrongCont->so_luong -= $yeuCauContainerHangHoa->so_luong;
            $hangTrongContMoi = HangTrongCont::create([
                'ma_hang' => $hangTrongCont->ma_hang,
                'so_container' => $yeuCauContainerHangHoa->so_container_moi,
                'so_luong' => $yeuCauContainerHangHoa->so_luong,
            ]);
            YCContainerMaHangContMoi::insert([
                'ma_yeu_cau_hang_hoa' => $yeuCauContainerHangHoa->ma_yeu_cau_hang_hoa,
                'ma_hang_cont' =>  $hangTrongContMoi->ma_hang_cont,
                'so_luong' => $yeuCauContainerHangHoa->so_luong,
                'loai_cont_moi' => 1,
            ]);
        }

        $hangTrongCont->save();
        if ($hangTrongCont->so_luong < 0) {
            $hangTrongCont->so_luong = 0;
        }
        if ($hangTrongCont->so_luong == 0) {
            $hangTrongCont->is_da_chuyen_cont = 1;
        }
        $hangTrongCont->save();
        return $hangTrongContMoi;
    }

    private function xuLyContainer($so_container, $phuong_tien_vt_nhap)
    {
        if (!Container::find($so_container)) {
            Container::insert([
                'so_container' => $so_container,
            ]);
        }

        $record = NiemPhong::where('so_container', $so_container)->first();

        if (!$record) {
            NiemPhong::insert([
                'so_container' => $so_container,
                'so_seal' => '',
                'ngay_niem_phong' => now(),
                'phuong_tien_vt_nhap' => $phuong_tien_vt_nhap,
            ]);
        } else {
            $record->update([
                'phuong_tien_vt_nhap' => $phuong_tien_vt_nhap,
            ]);
        }
    }

    public function duyetSuaYeuCau(Request $request)
    {
        try {
            DB::beginTransaction();
            $suaYeuCau = YeuCauSua::find($request->ma_sua_yeu_cau);
            $yeuCau = YeuCauChuyenContainer::find($request->ma_yeu_cau);

            $soContainerDich = YeuCauContainerChiTietSua::where('ma_sua_yeu_cau', $request->ma_yeu_cau)->pluck('so_container_dich')->toArray();
            $soContainers = array_unique($soContainerDich);
            foreach ($soContainers as $soContainer) {
                $this->kiemTraContainer($soContainer);
            }



            $chiTietSuaYeuCaus = YeuCauContainerChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->get();
            $soToKhaiSauSuas = $chiTietSuaYeuCaus->pluck('so_to_khai_nhap')->toArray();
            $soToKhaiCanXuLy = $soToKhaiSauSuas;

            $this->quayNguocYeuCau($yeuCau);


            $chiTiets = YeuCauContainerChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
            foreach ($chiTiets as $chiTiet) {
                YeuCauContainerHangHoa::where('ma_chi_tiet', $chiTiet->ma_chi_tiet)->delete();
                $chiTiet->delete();
            }
            $this->xuLySuaYeuCau($chiTietSuaYeuCaus, $soToKhaiCanXuLy, $yeuCau);

            $chiTietYeuCaus = YeuCauContainerChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
            foreach ($chiTietYeuCaus as $chiTietYeuCau) {
                $yeuCauContainerHangHoas = YeuCauContainerHangHoa::where('ma_chi_tiet', $chiTietYeuCau->ma_chi_tiet)->get();
                $this->xuLyContainer($chiTietYeuCau->so_container_dich, $chiTietYeuCau->tau_goc);

                foreach ($yeuCauContainerHangHoas as $yeuCauContainerHangHoa) {
                    $hangTrongCont = HangTrongCont::find($yeuCauContainerHangHoa->ma_hang_cont);
                    $sumSoLuong = HangHoa::join('hang_trong_cont', 'hang_hoa.ma_hang', 'hang_trong_cont.ma_hang')
                        ->where('hang_hoa.ma_hang', $hangTrongCont->ma_hang)
                        ->sum('hang_trong_cont.so_luong');
                    $this->tienHanhChuyenCont($hangTrongCont, $yeuCauContainerHangHoa, $sumSoLuong);
                }
            }
            $yeuCau->ten_doan_tau = $suaYeuCau->ten_doan_tau;
            $yeuCau->trang_thai = '2';
            if ($yeuCau->file_name && $suaYeuCau->file_name) {
                $yeuCau->file_name = $suaYeuCau->file_name;
                $yeuCau->file_path = $suaYeuCau->file_path;
            }
            $yeuCau->save();

            $chiTiets = YeuCauContainerChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->get();
            foreach ($chiTiets as $chiTiet) {
                YeuCauContainerHangHoaSua::where('ma_chi_tiet', $chiTiet->ma_chi_tiet)->delete();
                $chiTiet->delete();
            }
            YeuCauSua::find($request->ma_sua_yeu_cau)->delete();


            TheoDoiTruLui::where('ma_yeu_cau', $request->ma_yeu_cau)->where('cong_viec', 3)->delete();
            TheoDoiHangHoa::where('ma_yeu_cau', $request->ma_yeu_cau)->where('cong_viec', 3)->delete();
            $soToKhaiNhaps = YeuCauContainerChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)
                ->pluck('so_to_khai_nhap')
                ->unique()
                ->values()
                ->toArray();
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $this->themTheoDoiTruLui($soToKhaiNhap, $yeuCau);
            }





            DB::commit();


            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]);
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetSuaYeuCauCont: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function xuLySuaYeuCau($chiTietSuaYeuCaus, $soToKhaiCanXuLy, $yeuCau)
    {
        $this->xoaTheoDoiTruLui($yeuCau);
        foreach ($chiTietSuaYeuCaus as $chiTietYeuCau) {
            $chiTiet = YeuCauContainerChiTiet::create([
                'so_to_khai_nhap' => $chiTietYeuCau->so_to_khai_nhap,
                'so_container_goc' => $chiTietYeuCau->so_container_goc,
                'so_container_dich' => $chiTietYeuCau->so_container_dich,
                'so_luong_chuyen' => $chiTietYeuCau->so_luong_chuyen,
                'so_luong_ton_cont_moi' => $chiTietYeuCau->so_luong_ton_cont_moi,
                'so_to_khai_cont_moi' => $chiTietYeuCau->so_to_khai_cont_moi,
                'tau_goc' => $chiTietYeuCau->tau_goc,
                'tau_dich' => $chiTietYeuCau->tau_dich,
                'ma_yeu_cau' => $yeuCau->ma_yeu_cau
            ]);
            $yeuCauContainerHangHoas = YeuCauContainerHangHoaSua::where('ma_chi_tiet', $chiTietYeuCau->ma_chi_tiet)->get();
            foreach ($yeuCauContainerHangHoas as $yeuCauContainerHangHoa) {
                if ($yeuCauContainerHangHoa->so_container_moi == '') {
                    continue;
                }
                $hangTrongCont = HangTrongCont::find($yeuCauContainerHangHoa->ma_hang_cont);
                $hangTrongCont->so_container = $yeuCauContainerHangHoa->so_container_moi;
                $hangTrongCont->save();

                YeuCauContainerHangHoa::insert([
                    'ma_hang_cont' => $yeuCauContainerHangHoa->ma_hang_cont,
                    'ten_hang' => $yeuCauContainerHangHoa->ten_hang,
                    'so_container_cu' => $yeuCauContainerHangHoa->so_container_cu,
                    'so_container_moi' => $yeuCauContainerHangHoa->so_container_moi,
                    'so_luong' => $yeuCauContainerHangHoa->so_luong,
                    'ma_chi_tiet' => $chiTiet->ma_chi_tiet,
                ]);
            }
        }
        $soToKhaiNhaps = $chiTietSuaYeuCaus->pluck('so_to_khai_nhap')->unique()->values()->toArray();
        foreach ($soToKhaiNhaps as $soToKhaiNhap) {
            $this->themTheoDoiTruLui($soToKhaiNhap, $yeuCau);
            $this->themTienTrinh($soToKhaiNhap, "Đã sửa yêu cầu chuyển container số " . $yeuCau->ma_yeu_cau . ", cán bộ công chức phụ trách: " . $yeuCau->congChuc->ten_cong_chuc, $yeuCau->congChuc->ma_cong_chuc);
        }
    }


    public function quayNguocYeuCau($yeuCau)
    {
        $chiTietYeuCaus = YeuCauContainerChiTiet::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)->get();
        foreach ($chiTietYeuCaus as $chiTietYeuCau) {
            $yeuCauContainerHangHoas = YeuCauContainerHangHoa::where('ma_chi_tiet', $chiTietYeuCau->ma_chi_tiet)->get();

            foreach ($yeuCauContainerHangHoas as $yeuCauContainerHangHoa) {
                $hangTrongContMain = HangTrongCont::find($yeuCauContainerHangHoa->ma_hang_cont);

                $ycHangTrongContKhacs = YCContainerMaHangContMoi::where('ma_yeu_cau_hang_hoa', $yeuCauContainerHangHoa->ma_yeu_cau_hang_hoa)->get();
                $tongSoLuongDaChuyen = 0;

                if ($hangTrongContMain->so_luong == 0) {
                    $hangTrongContMain->is_da_chuyen_cont = 0;
                }

                if ($ycHangTrongContKhacs->isEmpty()) {
                    $hangTrongCont = HangTrongCont::find($yeuCauContainerHangHoa->ma_hang_cont);
                    $hangTrongCont->so_container = $chiTietYeuCau->so_container_goc;
                    $hangTrongCont->save();
                } else {
                    foreach ($ycHangTrongContKhacs as $ycHangTrongContKhac) {

                        $hangTrongContKhac = HangTrongCont::find($ycHangTrongContKhac->ma_hang_cont);
                        $tongSoLuongDaChuyen += $ycHangTrongContKhac->so_luong;

                        if ($ycHangTrongContKhac->loai_cont_moi == 1) {
                            $hangTrongContKhac->is_da_chuyen_cont = 1;
                        } else if ($hangTrongContKhac->so_luong - $ycHangTrongContKhac->so_luong == 0) {
                            $hangTrongContKhac->is_da_chuyen_cont = 1;
                        }
                        $hangTrongContKhac->so_luong -= $ycHangTrongContKhac->so_luong;
                        $hangTrongContKhac->save();
                    }
                    $hangTrongContMain->so_luong += $tongSoLuongDaChuyen;
                    $hangTrongContMain->save();
                }
            }
        }
    }



    public function huyYeuCauContainer(Request $request)
    {
        try {
            DB::beginTransaction();
            $yeuCau = YeuCauChuyenContainer::find($request->ma_yeu_cau);
            if ($yeuCau->trang_thai == "1") {
                if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
                    $this->huyYeuCauContainerFunc($request->ma_yeu_cau, $request->ghi_chu, "Cán bộ công chức", '');
                } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                    $this->huyYeuCauContainerFunc($request->ma_yeu_cau, $request->ghi_chu, "Doanh nghiệp", '');
                }
                $soToKhaiNhaps = YeuCauContainerChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_to_khai_nhap');
                foreach ($soToKhaiNhaps as $soToKhai) {
                    TheoDoiHangHoa::where('so_to_khai_nhap', $soToKhai)
                        ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                        ->where('cong_viec', 3)
                        ->delete();
                    TheoDoiTruLui::where('so_to_khai_nhap', $soToKhai)
                        ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                        ->where('cong_viec', 3)
                        ->delete();
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
            Log::error('Error in huyYeuCauContainer: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function huySuaYeuCau(Request $request)
    {
        $yeuCau = YeuCauChuyenContainer::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = '2';
        $yeuCau->save();
        $suaYeuCau = YeuCauSua::where('ma_sua_yeu_cau', $request->ma_sua_yeu_cau)->first();
        YeuCauContainerChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->delete();
        $suaYeuCau->delete();

        $chiTiets =  YeuCauContainerChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
        foreach ($chiTiets as $chiTiet) {
            if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                $yeuCau->ghi_chu = "Doanh nghiệp hủy đề nghị sửa: " . $request->ghi_chu;
                $this->themTienTrinh($chiTiet->so_to_khai_nhap, "Doanh nghiệp hủy đề nghị sửa yêu cầu chuyển container số " . $yeuCau->ma_yeu_cau, '');
            } else {
                $yeuCau->ghi_chu = "Công chức từ chối đề nghị sửa: " . $request->ghi_chu;
                $this->themTienTrinh($chiTiet->so_to_khai_nhap, "Công chức từ chối đề nghị sửa yêu cầu chuyển container số " . $yeuCau->ma_yeu_cau, $this->getCongChucHienTai()->ma_cong_chuc);
            }
        }
        $yeuCau->save();

        session()->flash('alert-success', 'Hủy yêu cầu sửa thành công!');
        return redirect()->route('quan-ly-kho.thong-tin-yeu-cau', ['ma_yeu_cau' => $request->ma_yeu_cau]);
    }

    public function huyYeuCauContainerFunc($ma_yeu_cau, $ghi_chu, $user, $ly_do)
    {
        $yeuCau = YeuCauChuyenContainer::find($ma_yeu_cau);
        if ($yeuCau) {
            if ($yeuCau->trang_thai == "1") {
                $soToKhaiNhaps = YeuCauContainerChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->pluck('so_to_khai_nhap');
                if ($user == "Cán bộ công chức") {
                    $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
                    foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                        $this->themTienTrinh($soToKhaiNhap, "Cán bộ công chức đã hủy yêu cầu di chuyển hàng số " . $ma_yeu_cau, $congChuc->ma_cong_chuc);
                    }
                } elseif ($user == "Doanh nghiệp") {
                    foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                        $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đã hủy yêu cầu di chuyển hàng số " . $ma_yeu_cau, '');
                    }
                } elseif ($user == "Hệ thống") {
                    foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                        $this->themTienTrinh($soToKhaiNhap, "Hệ thống đã hủy yêu cầu di chuyển hàng số " . $ma_yeu_cau . $ly_do, '');
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
        $yeuCau = YeuCauChuyenContainer::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = '2';

        $soToKhaiNhaps = YeuCauContainerChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_to_khai_nhap');
        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $yeuCau->ghi_chu = "Công chức từ chối đề nghị hủy: " . $request->ghi_chu;
                $this->themTienTrinh($soToKhaiNhap, "Cán bộ công chức đã hủy yêu cầu chuyển container số " . $request->ma_yeu_cau, $congChuc->ma_cong_chuc);
            }
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $yeuCau->ghi_chu = "Doanh nghiệp hủy đề nghị hủy: " . $request->ghi_chu;
                $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đã hủy yêu cầu chuyển container số " . $request->ma_yeu_cau, '');
            }
        }
        $yeuCau->save();
        session()->flash('alert-success', 'Hủy đề nghị hủy thành công');
        return redirect()->route('quan-ly-kho.thong-tin-yeu-cau', ['ma_yeu_cau' => $request->ma_yeu_cau]);
    }
    public function huyYeuCauDaDuyet(Request $request)
    {
        $yeuCau = YeuCauChuyenContainer::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = '4';
        $yeuCau->ghi_chu = $request->ghi_chu;
        $yeuCau->save();

        $soToKhaiNhaps = YeuCauContainerChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_to_khai_nhap');
        foreach ($soToKhaiNhaps as $soToKhaiNhap) {
            $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đề nghị hủy yêu cầu chuyển container số " . $request->ma_yeu_cau, '');
        }
    }
    public function thayDoiCongChucContainer(Request $request)
    {
        YeuCauChuyenContainer::find($request->ma_yeu_cau)->update([
            'ma_cong_chuc' => $request->ma_cong_chuc
        ]);
        TheoDoiHangHoa::where('cong_viec', 3)
            ->where('ma_yeu_cau', $request->ma_yeu_cau)
            ->update(['ma_cong_chuc' => $request->ma_cong_chuc]);
        session()->flash('alert-success', 'Thay đổi công chức thành công');
        return redirect()->back();
    }

    public function duyetHuyYeuCau(Request $request)
    {
        $yeuCau = YeuCauChuyenContainer::find($request->ma_yeu_cau);
        $soToKhaiNhaps = YeuCauContainerChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_to_khai_nhap');

        $this->quayNguocYeuCau($yeuCau);
        foreach ($soToKhaiNhaps as $soToKhai) {
            TheoDoiHangHoa::where('so_to_khai_nhap', $soToKhai)
                ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->where('cong_viec', 3)
                ->delete();
            TheoDoiTruLui::where('so_to_khai_nhap', $soToKhai)
                ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->where('cong_viec', 3)
                ->delete();
        }

        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $this->themTienTrinh($soToKhaiNhap, "Cán bộ công chức đã duyệt đề nghị hủy yêu cầu chuyển container số " . $request->ma_yeu_cau, $congChuc->ma_cong_chuc);
            }
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đã hủy đề nghị hủy yêu cầu chuyển container số " . $request->ma_yeu_cau, '');
            }
        }

        $yeuCau->trang_thai = '0';
        $yeuCau->ghi_chu = "Công chức duyệt đề nghị hủy: " . $request->ghi_chu;
        $yeuCau->save();
    }

    public function duyetHoanThanh(Request $request)
    {
        $yeuCau = YeuCauChuyenContainer::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = "Đã hoàn thành";
        $yeuCau->save();
        session()->flash('alert-success', 'Duyệt hoàn thành yêu cầu thành công');
        return redirect()->back();
    }

    public function getSealNhoNhat($loai_seal, $ma_cong_chuc, $count = 1)
    {
        $soSeals = Seal::where('seal.trang_thai', 0)
            ->where('ma_cong_chuc', $ma_cong_chuc)
            ->where('loai_seal', $loai_seal)
            ->pluck('so_seal');
        $availableSeals = collect($soSeals)->sort()->values();
        if ($availableSeals->count() < $count) {
            return false;
        }
        return $availableSeals;
    }

    public function suDungSeal($so_seal_moi, $so_container, $ma_cong_chuc)
    {
        $seal = Seal::find($so_seal_moi);
        if ($seal) {
            if ($seal->trang_thai == 1) {
                return false;
            }
            $seal->update([
                'trang_thai' => 1,
                'ngay_su_dung' => now(),
                'so_container' => $so_container,
                'ma_cong_chuc' => $ma_cong_chuc,
            ]);
        } else {
            $seal = Seal::create([
                'so_seal' => $so_seal_moi,
                'ngay_cap' => now(),
                'ngay_su_dung' => now(),
                'so_container' => $so_container,
                'ma_cong_chuc' => $ma_cong_chuc,
                'trang_thai' => 1,
            ]);
        }
        return true;
    }
    public function updateNiemPhong($so_seal, $so_container, $ma_cong_chuc)
    {
        $record = NiemPhong::where('so_container', $so_container)->first();
        if ($record) {
            $record->update([
                'so_seal' => $so_seal,
                'ngay_niem_phong' => now(),
                'ma_cong_chuc' => $ma_cong_chuc,
            ]);
        } else {
            NiemPhong::insert([
                'so_container' => $so_container,
                'so_seal' => $so_seal,
                'ngay_niem_phong' => now(),
                'ma_cong_chuc' => $ma_cong_chuc,
            ]);
        }
    }
    public function kiemTraContainer($so_container)
    {
        $container = Container::find($so_container);
        if (!$container) {
            Container::insert([
                'so_container' => $so_container,
            ]);
            NiemPhong::insert([
                'so_container' => $so_container,
                'so_seal' => '',
                'ngay_niem_phong' => now(),
                'ma_cong_chuc' => '',
            ]);
        }
    }
    public function themTheoDoiTruLui($so_to_khai_nhap, $yeuCau)
    {
        $ngayDangKy = Carbon::parse($yeuCau->ngay_yeu_cau);
        $thoiGian = $ngayDangKy->setTime(now()->hour, now()->minute);

        $hangHoas = YeuCauContainerChiTiet::join('yeu_cau_container_hang_hoa', 'yeu_cau_container_chi_tiet.ma_chi_tiet', 'yeu_cau_container_hang_hoa.ma_chi_tiet')
            ->where('yeu_cau_container_chi_tiet.so_to_khai_nhap', $so_to_khai_nhap)
            ->where('yeu_cau_container_chi_tiet.ma_yeu_cau', $yeuCau->ma_yeu_cau)
            ->get();

        $theoDoi = TheoDoiTruLui::create([
            'so_to_khai_nhap' => $so_to_khai_nhap,
            'so_ptvt_nuoc_ngoai' => '',
            'ngay_them' => $thoiGian,
            'cong_viec' => 3,
            'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
        ]);
        foreach ($hangHoas as $hangHoa) {
            $ma_hang = HangTrongCont::find( $hangHoa->ma_hang_cont)->ma_hang;

            TheoDoiHangHoa::insert([
                'so_to_khai_nhap' => $hangHoa->so_to_khai_nhap,
                'ma_hang'  => $ma_hang,
                'thoi_gian'  => now(),
                'so_luong_xuat'  => $hangHoa->so_luong,
                'so_luong_ton'  => $hangHoa->so_luong,
                'phuong_tien_cho_hang' => $hangHoa->tau_dich ?? '',
                'cong_viec' => 2,
                'phuong_tien_nhan_hang' => '',
                'so_container' => $hangHoa->so_container_moi ?? '',
                'so_seal' => '',
                'ma_cong_chuc' => $yeuCau->ma_cong_chuc ?? '',
                'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
            ]);
            TheoDoiTruLuiChiTiet::insert(
                [
                    'ten_hang' => $hangHoa->ten_hang,
                    'so_luong_xuat' => 0,
                    'so_luong_chua_xuat' => $hangHoa->so_luong,
                    'ma_theo_doi' => $theoDoi->ma_theo_doi,
                    'so_container' => $hangHoa->so_container_moi ?? '',
                    'so_seal' => '',
                    'phuong_tien_vt_nhap' => $hangHoa->tau_dich ?? NiemPhong::where('so_container', $hangHoa->so_container_moi)->first()->phuong_tien_vt_nhap ?? ''
                ]
            );
        }
    }
    public function xoaTheoDoiTruLui($yeuCau)
    {
        TheoDoiTruLuiChiTiet::whereIn('ma_theo_doi', function ($query) use ($yeuCau) {
            $query->select('ma_theo_doi')
                ->from('theo_doi_tru_lui')
                ->where('cong_viec', 3)
                ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau);
        })->delete();

        TheoDoiTruLui::where('cong_viec', 3)
            ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
            ->delete();
        TheoDoiHangHoa::where('cong_viec', operator: 3)
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
            $yeuCau->file->delete();
        }

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();

        while (Storage::exists('public/yeu_cau_chuyen_container/' . $fileName)) {
            $fileInfo = pathinfo(path: $fileName);
            $fileName = $fileInfo['filename'] . '_' . time() . '.' . $fileInfo['extension'];
        }

        $filePath = $file->storeAs('yeu_cau_chuyen_container', $fileName, 'public');

        $yeuCau->file_name = $fileName;
        $yeuCau->file_path = $filePath;
        $yeuCau->save();
    }
    public function downloadFile($maYeuCau, $xemSua = false)
    {
        $yeuCau = YeuCauChuyenContainer::findOrFail($maYeuCau);

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
    public function getYeuCauContainer(Request $request)
    {
        if ($request->ajax()) {
            if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
                $data = YeuCauChuyenContainer::with(['doanhNghiep', 'yeuCauContainerChiTiet'])
                    ->join('doanh_nghiep', 'yeu_cau_chuyen_container.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
                    ->join('yeu_cau_container_chi_tiet', 'yeu_cau_chuyen_container.ma_yeu_cau', '=', 'yeu_cau_container_chi_tiet.ma_yeu_cau')
                    ->select(
                        'doanh_nghiep.ten_doanh_nghiep',
                        'yeu_cau_chuyen_container.ma_yeu_cau',
                        'yeu_cau_chuyen_container.trang_thai',
                        'yeu_cau_chuyen_container.ngay_yeu_cau',
                        DB::raw('GROUP_CONCAT(DISTINCT yeu_cau_container_chi_tiet.so_to_khai_nhap ORDER BY yeu_cau_container_chi_tiet.so_to_khai_nhap ASC SEPARATOR ", ") as so_to_khai_nhap_list')
                    )
                    ->groupBy('yeu_cau_chuyen_container.ma_yeu_cau')
                    ->orderBy('ma_yeu_cau', 'desc')
                    ->get();
            } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                $maDoanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep;
                $data = YeuCauChuyenContainer::with(['doanhNghiep', 'yeuCauContainerChiTiet'])
                    ->join('doanh_nghiep', 'yeu_cau_chuyen_container.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
                    ->join('yeu_cau_container_chi_tiet', 'yeu_cau_chuyen_container.ma_yeu_cau', '=', 'yeu_cau_container_chi_tiet.ma_yeu_cau')
                    ->where('yeu_cau_chuyen_container.ma_doanh_nghiep', $maDoanhNghiep)
                    ->select(
                        'doanh_nghiep.ten_doanh_nghiep',
                        'yeu_cau_chuyen_container.ma_yeu_cau',
                        'yeu_cau_chuyen_container.trang_thai',
                        'yeu_cau_chuyen_container.ngay_yeu_cau',
                        DB::raw('GROUP_CONCAT(DISTINCT yeu_cau_container_chi_tiet.so_to_khai_nhap ORDER BY yeu_cau_container_chi_tiet.so_to_khai_nhap ASC SEPARATOR ", ") as so_to_khai_nhap_list')
                    )
                    ->groupBy('yeu_cau_chuyen_container.ma_yeu_cau')
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
