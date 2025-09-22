<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\YeuCauGiaHanChiTiet;
use App\Models\CongChuc;
use App\Models\DoanhNghiep;
use App\Models\NhapHang;
use App\Models\TienTrinh;
use App\Models\YeuCauGiaHan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class YeuCauGiaHanController extends Controller
{
    public function danhSachYeuCauGiaHan()
    {
        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $data = YeuCauGiaHan::join('doanh_nghiep', 'yeu_cau_gia_han.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
                ->join('yeu_cau_gia_han_chi_tiet', 'yeu_cau_gia_han.ma_yeu_cau', 'yeu_cau_gia_han_chi_tiet.ma_yeu_cau')
                ->select(
                    'doanh_nghiep.*',
                    'yeu_cau_gia_han.*',
                    DB::raw('GROUP_CONCAT(DISTINCT yeu_cau_gia_han_chi_tiet.so_to_khai_nhap ORDER BY yeu_cau_gia_han_chi_tiet.so_to_khai_nhap ASC SEPARATOR ", ") as so_to_khai_nhap_list')
                )
                ->groupBy('yeu_cau_gia_han.ma_yeu_cau')
                ->orderBy('ma_yeu_cau', 'desc')
                ->get();
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $maDoanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep;
            $data = YeuCauGiaHan::join('doanh_nghiep', 'yeu_cau_gia_han.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
                ->join('yeu_cau_gia_han_chi_tiet', 'yeu_cau_gia_han.ma_yeu_cau', 'yeu_cau_gia_han_chi_tiet.ma_yeu_cau')
                ->where('yeu_cau_gia_han.ma_doanh_nghiep', $maDoanhNghiep)
                ->select(
                    'doanh_nghiep.*',
                    'yeu_cau_gia_han.*',
                    DB::raw('GROUP_CONCAT(DISTINCT yeu_cau_gia_han_chi_tiet.so_to_khai_nhap ORDER BY yeu_cau_gia_han_chi_tiet.so_to_khai_nhap ASC SEPARATOR ", ") as so_to_khai_nhap_list')
                )
                ->groupBy('yeu_cau_gia_han.ma_yeu_cau')
                ->orderBy('ma_yeu_cau', 'desc')
                ->get();
        }
        return view('quan-ly-kho.yeu-cau-gia-han.danh-sach-yeu-cau-gia-han', data: compact(var_name: 'data'));
    }

    public function themYeuCauGiaHan()
    {
        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            $toKhaiDangXuLys = YeuCauGiaHanChiTiet::join('nhap_hang', 'yeu_cau_gia_han_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('yeu_cau_gia_han', 'yeu_cau_gia_han_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_gia_han.ma_yeu_cau')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->where('yeu_cau_gia_han.trang_thai', "1")
                ->pluck('yeu_cau_gia_han_chi_tiet.so_to_khai_nhap');

            $toKhaiNhaps = NhapHang::with('hangHoa')
                ->where('nhap_hang.trang_thai', '2')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->where('nhap_hang.ma_loai_hinh', 'G21')
                ->whereNotIn('nhap_hang.so_to_khai_nhap', $toKhaiDangXuLys)
                ->get();

            return view('quan-ly-kho.yeu-cau-gia-han.them-yeu-cau-gia-han', data: compact('toKhaiNhaps', 'doanhNghiep'));
        }
        return redirect()->back();
    }

    public function themYeuCauGiaHanSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();

            $yeuCau = YeuCauGiaHan::create([
                'ma_doanh_nghiep' => $doanhNghiep->ma_doanh_nghiep,
                'trang_thai' => '1',
                'ngay_yeu_cau' => now()
            ]);

            // Decode the JSON data from the form
            $rowsData = json_decode($request->rows_data, true);
            foreach ($rowsData as $row) {
                $ten_hang = '';
                $nhapHang = NhapHang::with('hangHoa.hangTrongCont')->find($row['so_to_khai_nhap']);

                $nhapHang = NhapHang::with('hangHoa.hangTrongCont')->find($row['so_to_khai_nhap']);
                foreach ($nhapHang->hangHoa as $hangHoa) {
                    foreach ($hangHoa->hangTrongCont as $hangTrongCont) {
                        $ten_hang .= $hangHoa->ten_hang . ' - Số lượng: ' . $hangTrongCont->so_luong . "<br>";
                    }
                }
                $containers = $nhapHang->hangHoa
                    ->flatMap(
                        fn($hangHoa) =>
                        $hangHoa->hangTrongCont
                            ->filter(fn($cont) => $cont->is_da_chuyen_cont == 0 || $cont->so_luong != 0)
                            ->pluck('so_container')
                    )
                    ->unique()
                    ->implode(';');

                YeuCauGiaHanChiTiet::insert([
                    'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                    'so_tau' => $nhapHang->phuong_tien_vt_nhap,
                    'ngay_dang_ky' => $nhapHang->ngay_dang_ky,
                    'ten_hang' => $ten_hang,
                    'so_container' => $containers,
                    'ma_yeu_cau' => $yeuCau->ma_yeu_cau
                ]);
                $this->themTienTrinh($row['so_to_khai_nhap'], "Doanh nghiệp đã yêu cầu gia hạn tờ khai số " . $yeuCau->ma_yeu_cau, '');
            }
            if ($request->file('file')) {
                $this->luuFile($request, $yeuCau);
            }
            DB::commit();
            session()->flash('alert-success', 'Thêm yêu cầu thành công!');
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-gia-han', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in ThemGiaHan: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function thongTinYeuCauGiaHan($ma_yeu_cau)
    {
        $yeuCau = YeuCauGiaHan::where('ma_yeu_cau', $ma_yeu_cau)
            ->leftJoin('cong_chuc', 'yeu_cau_gia_han.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->first();
        $doanhNghiep = DoanhNghiep::find($yeuCau->ma_doanh_nghiep);

        $chiTiets = YeuCauGiaHan::join('yeu_cau_gia_han_chi_tiet', 'yeu_cau_gia_han.ma_yeu_cau', '=', 'yeu_cau_gia_han_chi_tiet.ma_yeu_cau')
            ->join('nhap_hang', 'yeu_cau_gia_han_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
            ->where('yeu_cau_gia_han.ma_yeu_cau', $ma_yeu_cau)
            ->pluck('yeu_cau_gia_han_chi_tiet.so_to_khai_nhap');

        $nhapHangs = NhapHang::whereIn('so_to_khai_nhap', $chiTiets)->get();

        $congChucs = CongChuc::where('is_chi_xem', 0)->where('status', 1)->get();
        $chiTiets = YeuCauGiaHanChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->get();
        return view('quan-ly-kho.yeu-cau-gia-han.thong-tin-yeu-cau-gia-han', compact('yeuCau', 'nhapHangs', 'doanhNghiep', 'congChucs', 'chiTiets')); // Pass data to the view
    }
    public function duyetYeuCauGiaHan(Request $request)
    {
        try {
            DB::beginTransaction();
            $yeuCau = YeuCauGiaHan::find($request->ma_yeu_cau);
            if ($yeuCau) {
                $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
                $chiTietYeuCaus = YeuCauGiaHanChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();

                $rowsData = json_decode($request->rows_data, true);
                foreach ($rowsData as $row) {
                    $nhapHang = NhapHang::find($row['so_to_khai_nhap']);
                    $nhapHang->so_ngay_gia_han = ($nhapHang->so_ngay_gia_han ?? 0) + $row['so_ngay_gia_han'];
                    $nhapHang->save();
                }

                foreach ($chiTietYeuCaus as $chiTietYeuCau) {
                    foreach ($rowsData as $row) {
                        if ($chiTietYeuCau->so_to_khai_nhap == $row['so_to_khai_nhap']) {
                            $chiTietYeuCau->so_ngay_gia_han = $row['so_ngay_gia_han'];
                            $chiTietYeuCau->save();
                        }
                    }
                    $this->themTienTrinh($chiTietYeuCau->so_to_khai_nhap, "Cán bộ công chức đã duyệt yêu cầu gia hạn tờ khai số " . $request->ma_yeu_cau, $congChuc->ma_cong_chuc);
                }


                $yeuCau->ma_cong_chuc = $request->ma_cong_chuc;
                $yeuCau->ngay_hoan_thanh = now();
                $yeuCau->trang_thai = '2';
                $yeuCau->save();
                session()->flash('alert-success', 'Duyệt yêu cầu thành công!');
            }

            DB::commit();
            // return redirect()->back();
            return redirect()->route('quan-ly-kho.danh-sach-yeu-cau-gia-han');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetYeuCauGiaHan: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function huyYeuCauGiaHan(Request $request)
    {
        try {
            DB::beginTransaction();

            if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
                $this->huyYeuCauGiaHanFunc($request->ma_yeu_cau, $request->ghi_chu, "Cán bộ công chức", '');
            } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                $this->huyYeuCauGiaHanFunc($request->ma_yeu_cau, $request->ghi_chu, "Doanh nghiệp", '');
            }

            session()->flash('alert-success', 'Hủy yêu cầu thành công!');
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in huyYeuCauGiaHan: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function huyYeuCauGiaHanFunc($ma_yeu_cau, $ghi_chu, $user, $ly_do)
    {
        $yeuCau = YeuCauGiaHan::find($ma_yeu_cau);
        if ($yeuCau) {
            if ($yeuCau->trang_thai == "1") {

                $soToKhaiNhaps = YeuCauGiaHanChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->pluck('so_to_khai_nhap');

                if ($user == "Cán bộ công chức") {
                    $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
                    foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                        $this->themTienTrinh($soToKhaiNhap, "Cán bộ công chức đã hủy yêu cầu gia hạn tờ khai số " . $ma_yeu_cau . $ly_do, $congChuc->ma_cong_chuc);
                    }
                } elseif ($user == "Doanh nghiệp") {
                    foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                        $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đã hủy yêu cầu gia hạn tờ khai số " . $ma_yeu_cau . $ly_do, '');
                    }
                } elseif ($user == "Hệ thống") {
                    foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                        $this->themTienTrinh($soToKhaiNhap, "Hệ thống đã hủy yêu cầu gia hạn tờ khai số " . $ma_yeu_cau . $ly_do, '');
                    }
                }
                $yeuCau->trang_thai = '0';
                $yeuCau->ghi_chu = $ghi_chu;
                $yeuCau->save();
            }
        }
    }



    public function suaYeuCauGiaHan($ma_yeu_cau)
    {
        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            $toKhaiDangXuLys = YeuCauGiaHanChiTiet::join('nhap_hang', 'yeu_cau_gia_han_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('yeu_cau_gia_han', 'yeu_cau_gia_han_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_gia_han.ma_yeu_cau')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->where('yeu_cau_gia_han.trang_thai', "1")
                ->pluck('yeu_cau_gia_han_chi_tiet.so_to_khai_nhap');

            $toKhaiTrongPhieu = YeuCauGiaHanChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->pluck('so_to_khai_nhap');
            $toKhaiDangXuLys = $toKhaiDangXuLys->diff($toKhaiTrongPhieu);
            $toKhaiNhaps = NhapHang::with('hangHoa')
                ->where('nhap_hang.trang_thai', '2')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->where('nhap_hang.ma_loai_hinh', 'G21')
                ->whereNotIn('nhap_hang.so_to_khai_nhap', $toKhaiDangXuLys)
                ->get();
            $chiTiets = NhapHang::with('hangHoa')
                ->where('nhap_hang.trang_thai', '2')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->whereIn('nhap_hang.so_to_khai_nhap', $toKhaiTrongPhieu)
                ->get();
            $yeuCau = YeuCauGiaHan::find( $ma_yeu_cau);
            return view('quan-ly-kho.yeu-cau-gia-han.sua-yeu-cau-gia-han', data: compact('toKhaiNhaps', 'doanhNghiep', 'chiTiets', 'ma_yeu_cau', 'yeuCau'));
        }
        return redirect()->back();
    }

    public function suaYeuCauGiaHanSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            // Decode the JSON data from the form
            $rowsData = json_decode($request->rows_data, true);
            YeuCauGiaHanChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->delete();
            foreach ($rowsData as $row) {
                $ten_hang = '';
                $nhapHang = NhapHang::with('hangHoa.hangTrongCont')->find($row['so_to_khai_nhap']);
                foreach ($nhapHang->hangHoa as $hangHoa) {
                    foreach ($hangHoa->hangTrongCont as $hangTrongCont) {
                        $ten_hang .= $hangHoa->ten_hang . ' - Số lượng: ' . $hangTrongCont->so_luong . "<br>";
                    }
                }
                $containers = $nhapHang->hangHoa
                    ->flatMap(
                        fn($hangHoa) =>
                        $hangHoa->hangTrongCont
                            ->filter(fn($cont) => $cont->is_da_chuyen_cont == 0 || $cont->so_luong != 0)
                            ->pluck('so_container')
                    )
                    ->unique()
                    ->implode(';');
                YeuCauGiaHanChiTiet::insert([
                    'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                    'so_tau' => $nhapHang->phuong_tien_vt_nhap,
                    'ngay_dang_ky' => $nhapHang->ngay_dang_ky,
                    'ten_hang' => $ten_hang,
                    'so_container' => $containers,
                    'ma_yeu_cau' => $request->ma_yeu_cau
                ]);
            }

            DB::commit();
            session()->flash('alert-success', 'Sửa yêu cầu thành công!');
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-gia-han', ['ma_yeu_cau' => $request->ma_yeu_cau]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in SuaGiaHan: ' . $e->getMessage());
            return redirect()->back();
        }
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

        while (Storage::exists('public/yeu_cau_gia_han/' . $fileName)) {
            $fileInfo = pathinfo(path: $fileName);
            $fileName = $fileInfo['filename'] . '_' . time() . '.' . $fileInfo['extension'];
        }

        $filePath = $file->storeAs('yeu_cau_gia_han', $fileName, 'public');

        $yeuCau->file_name = $fileName;
        $yeuCau->file_path = $filePath;
        $yeuCau->save();
    }
    public function downloadFile($maYeuCau, $xemSua = false)
    {
        if ($xemSua) {
            // $yeuCau = YeuCauGiaHanSua::findOrFail($maYeuCau);
        } else {
            $yeuCau = YeuCauGiaHan::findOrFail($maYeuCau);
        }

        if (!$yeuCau->file_name) {
            session()->flash('alert-danger', 'Không tìm thấy file trong hệ thống');
            return redirect()->back();
        }

        $filePath = storage_path('app/public/' . $yeuCau->file_path);
        return response()->download($filePath, $yeuCau->file_name);
    }
}
