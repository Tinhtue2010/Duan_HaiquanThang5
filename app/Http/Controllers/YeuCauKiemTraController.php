<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\YeuCauKiemTraChiTiet;
use App\Models\CongChuc;
use App\Models\DoanhNghiep;
use App\Models\HangHoa;
use App\Models\HangTrongCont;
use App\Models\NhapHang;
use App\Models\NiemPhong;
use App\Models\Seal;
use App\Models\SealMoiChoKiemTra;
use App\Models\TienTrinh;
use App\Models\YeuCauKiemTra;
use App\Models\TheoDoiHangHoa;
use App\Models\TheoDoiTruLui;
use App\Models\TheoDoiTruLuiChiTiet;
use App\Models\YeuCauKiemTraChiTietSua;
use App\Models\YeuCauSua;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class YeuCauKiemTraController extends Controller
{
    public function danhSachYeuCauKiemTra()
    {
        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $data = YeuCauKiemTra::join('doanh_nghiep', 'yeu_cau_kiem_tra.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
                ->select(
                    'doanh_nghiep.*',
                    'yeu_cau_kiem_tra.*',
                )
                ->distinct()  // Ensure unique rows
                ->orderBy('ma_yeu_cau', 'desc')
                ->get();
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $maDoanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep;
            $data = YeuCauKiemTra::join('doanh_nghiep', 'yeu_cau_kiem_tra.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
                ->where('yeu_cau_kiem_tra.ma_doanh_nghiep', $maDoanhNghiep)
                ->select(
                    'doanh_nghiep.*',
                    'yeu_cau_kiem_tra.*',
                )
                ->distinct()  // Ensure unique rows
                ->orderBy('ma_yeu_cau', 'desc')
                ->get();
        }
        return view('quan-ly-kho.yeu-cau-kiem-tra.danh-sach-yeu-cau-kiem-tra', data: compact(var_name: 'data'));
    }

    public function themYeuCauKiemTra()
    {
        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            $toKhaiDangXuLys = YeuCauKiemTraChiTiet::join('nhap_hang', 'yeu_cau_kiem_tra_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('yeu_cau_kiem_tra', 'yeu_cau_kiem_tra_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_kiem_tra.ma_yeu_cau')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->where('yeu_cau_kiem_tra.trang_thai', "Đang chờ duyệt")
                ->pluck('yeu_cau_kiem_tra_chi_tiet.so_to_khai_nhap');

            $toKhaiNhaps = NhapHang::with('hangHoa')
                ->where('nhap_hang.trang_thai', 'Đã nhập hàng')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->whereNotIn('nhap_hang.so_to_khai_nhap', $toKhaiDangXuLys)
                ->get();

            return view('quan-ly-kho.yeu-cau-kiem-tra.them-yeu-cau-kiem-tra', data: compact('toKhaiNhaps', 'doanhNghiep'));
        }
        return redirect()->back();
    }

    public function themYeuCauKiemTraSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();

            $yeuCau = YeuCauKiemTra::create([
                'ma_doanh_nghiep' => $doanhNghiep->ma_doanh_nghiep,
                'ten_doan_tau' => $request->ten_doan_tau,
                'trang_thai' => 'Đang chờ duyệt',
                'ngay_yeu_cau' => now()
            ]);

            // Decode the JSON data from the form
            $rowsData = json_decode($request->rows_data, true);
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


                    $sumSoLuong = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                    ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                    ->where('nhap_hang.so_to_khai_nhap', $row['so_to_khai_nhap'])
                    ->groupBy('hang_trong_cont.ma_hang_cont') 
                    ->selectRaw('SUM(hang_trong_cont.so_luong) AS total')
                    ->pluck('total')
                    ->sum(); 


                YeuCauKiemTraChiTiet::insert([
                    'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                    'so_tau' => $nhapHang->phuong_tien_vt_nhap,
                    'ngay_dang_ky' => $nhapHang->ngay_dang_ky,
                    'ten_hang' => $ten_hang,
                    'so_container' => $containers,
                    'so_luong' => $sumSoLuong,
                    'ma_yeu_cau' => $yeuCau->ma_yeu_cau
                ]);
                $this->themTienTrinh($row['so_to_khai_nhap'], "Doanh nghiệp thêm yêu cầu kiểm tra hàng số " . $yeuCau->ma_yeu_cau, '');
            }
            if ($request->file('file')) {
                $this->luuFile($request, $yeuCau);
            }
            DB::commit();
            session()->flash('alert-success', 'Thêm yêu cầu thành công!');
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-kiem-tra', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in ThemKiemTra: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function thongTinYeuCauKiemTra($ma_yeu_cau)
    {
        $yeuCau = YeuCauKiemTra::where('ma_yeu_cau', $ma_yeu_cau)
            ->leftJoin('cong_chuc', 'yeu_cau_kiem_tra.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->first();
        $doanhNghiep = DoanhNghiep::find($yeuCau->ma_doanh_nghiep);

        $chiTiets = YeuCauKiemTra::join('yeu_cau_kiem_tra_chi_tiet', 'yeu_cau_kiem_tra.ma_yeu_cau', '=', 'yeu_cau_kiem_tra_chi_tiet.ma_yeu_cau')
            ->join('nhap_hang', 'yeu_cau_kiem_tra_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
            ->where('yeu_cau_kiem_tra.ma_yeu_cau', $ma_yeu_cau)
            ->pluck('yeu_cau_kiem_tra_chi_tiet.so_to_khai_nhap');

        $nhapHangs = NhapHang::whereIn('so_to_khai_nhap', $chiTiets)->get();

        $sealMois = SealMoiChoKiemTra::where('ma_yeu_cau', $ma_yeu_cau)->get();
        $seals = Seal::where('seal.trang_thai', 0)->get();
        $congChucs = CongChuc::where('is_chi_xem',0)->get();
        $chiTiets = YeuCauKiemTraChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->get();
        return view('quan-ly-kho.yeu-cau-kiem-tra.thong-tin-yeu-cau-kiem-tra', compact('yeuCau', 'nhapHangs', 'doanhNghiep', 'congChucs', 'sealMois', 'seals', 'chiTiets')); // Pass data to the view
    }

    public function duyetYeuCauKiemTra(Request $request)
    {
        try {
            DB::beginTransaction();
            $yeuCau = YeuCauKiemTra::find($request->ma_yeu_cau);
            if ($yeuCau) {
                $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
                $congChucPhuTrach = CongChuc::find($request->ma_cong_chuc);
                $chiTietYeuCaus = YeuCauKiemTraChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
                $soContainers = [];

                foreach ($chiTietYeuCaus as $chiTietYeuCau) {
                    $container = HangHoa::join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                        ->where('hang_hoa.so_to_khai_nhap', $chiTietYeuCau->so_to_khai_nhap)
                        ->select('hang_trong_cont.so_container')
                        ->first();
                    $soContainers[] = $container->so_container; // Add each container number to the array.
                }
                // $soContainers = array_unique($soContainers);

                // $availableSeals = $this->getSealNhoNhat($request->loai_seal, $request->ma_cong_chuc,count($soContainers));
                // if (!$availableSeals) {
                //     session()->flash('alert-danger', 'Không đủ số seal niêm phong để cấp cho yêu cầu này');
                //     return redirect()->back();
                // }

                // foreach ($soContainers as $soContainer) {
                //     $so_seal_moi = $availableSeals->shift();
                //     SealMoiChoKiemTra::insert([
                //         'ma_yeu_cau' => $request->ma_yeu_cau,
                //         'so_container' => $soContainer,
                //         'so_seal_moi' => $so_seal_moi,
                //     ]);
                //     $this->suDungSeal($so_seal_moi, $soContainer, $request->ma_cong_chuc);
                //     $this->updateNiemPhong($so_seal_moi, $soContainer, $request->ma_cong_chuc);
                // }

                foreach ($chiTietYeuCaus as $chiTietYeuCau) {
                    //TheoDoiHangHoa
                    $hangTrongConts = HangTrongCont::join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                        ->where('hang_hoa.so_to_khai_nhap', $chiTietYeuCau->so_to_khai_nhap)
                        ->select('hang_trong_cont.*', 'hang_hoa.ma_hang')
                        ->get();

                    foreach ($hangTrongConts as $row) {
                        $ptvtNhanHang = NhapHang::find($chiTietYeuCau->so_to_khai_nhap)->phuong_tien_vt_nhap;
                        $so_seal = NiemPhong::where('so_container', $row->so_container)->first()->so_seal;
                        TheoDoiHangHoa::insert([
                            'so_to_khai_nhap' => $chiTietYeuCau->so_to_khai_nhap,
                            'ma_hang'  => $row->ma_hang,
                            'thoi_gian'  => now(),
                            'so_luong_xuat'  => $row->so_luong,
                            'so_luong_ton'  => $row->so_luong,
                            'phuong_tien_cho_hang' => $ptvtNhanHang,
                            'cong_viec' => 7,
                            'phuong_tien_nhan_hang' => '',
                            'so_container' => $row->so_container,
                            'so_seal' => $so_seal,
                            'ma_cong_chuc' => $congChucPhuTrach->ma_cong_chuc,
                            'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
                        ]);
                    }
                    $this->themTheoDoiTruLui($chiTietYeuCau->so_to_khai_nhap, $yeuCau);
                    $this->themTienTrinh($chiTietYeuCau->so_to_khai_nhap, "Đã duyệt yêu cầu kiểm tra hàng số " . $request->ma_yeu_cau . ", cán bộ công chức phụ trách: " . $congChucPhuTrach->ten_cong_chuc, $congChuc->ma_cong_chuc);
                }

                $yeuCau->ma_cong_chuc = $congChucPhuTrach->ma_cong_chuc;
                $yeuCau->ngay_hoan_thanh = now();
                $yeuCau->trang_thai = 'Đã duyệt';
                $yeuCau->save();
                session()->flash('alert-success', 'Duyệt yêu cầu thành công!');
            }

            DB::commit();
            // return redirect()->back();
            return redirect()->route('quan-ly-kho.danh-sach-yeu-cau-kiem-tra');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in duyetYeuCauKiemTra: ' . $e->getMessage());
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            return redirect()->back();
        }
    }

    public function huyYeuCauKiemTra(Request $request)
    {
        $yeuCau = YeuCauKiemTra::find($request->ma_yeu_cau);
        if ($yeuCau->trang_thai == "Đang chờ duyệt") {
            if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
                $this->huyYeuCauKiemTraFunc($request->ma_yeu_cau, $request->ghi_chu, "Cán bộ công chức", '');
            } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                $this->huyYeuCauKiemTraFunc($request->ma_yeu_cau, $request->ghi_chu, "Doanh nghiệp", '');
            }
        } elseif ($yeuCau->trang_thai == "Đã duyệt") {
            $this->huyYeuCauDaDuyet($request);
        } else {
            $this->duyetHuyYeuCau($request);
        }

        try {
            DB::beginTransaction();

            if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
                $this->huyYeuCauKiemTraFunc($request->ma_yeu_cau, $request->ghi_chu, "Cán bộ công chức", '');
            } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                $this->huyYeuCauKiemTraFunc($request->ma_yeu_cau, $request->ghi_chu, "Doanh nghiệp", '');
            }

            session()->flash('alert-success', 'Hủy yêu cầu thành công!');
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in huyYeuCauKiemTra: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function huyYeuCauKiemTraFunc($ma_yeu_cau, $ghi_chu, $user, $ly_do)
    {
        $yeuCau = YeuCauKiemTra::find($ma_yeu_cau);
        if ($yeuCau) {
            if ($yeuCau->trang_thai == "Đang chờ duyệt") {

                $soToKhaiNhaps = YeuCauKiemTraChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->pluck('so_to_khai_nhap');

                if ($user == "Cán bộ công chức") {
                    $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
                    foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                        $this->themTienTrinh($soToKhaiNhap, "Cán bộ công chức đã hủy yêu cầu kiểm tra hàng số " . $ma_yeu_cau, $congChuc->ma_cong_chuc);
                    }
                } elseif ($user == "Doanh nghiệp") {
                    foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                        $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đã hủy yêu cầu kiểm tra hàng số " . $ma_yeu_cau, '');
                    }
                } elseif ($user == "Hệ thống") {
                    foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                        $this->themTienTrinh($soToKhaiNhap, "Hệ thống đã hủy yêu cầu kiểm tra hàng số " . $ma_yeu_cau . $ly_do, '');
                    }
                }
                $yeuCau->trang_thai = 'Đã hủy';
                $yeuCau->ghi_chu = $ghi_chu;
                $yeuCau->save();
            }
        }
    }

    public function huyHuyYeuCau(Request $request)
    {
        $yeuCau = YeuCauKiemTra::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = 'Đã duyệt';

        $soToKhaiNhaps = YeuCauKiemTraChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_to_khai_nhap');
        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $yeuCau->ghi_chu = "Công chức từ chối đề nghị hủy: " . $request->ghi_chu;
                $this->themTienTrinh($soToKhaiNhap, "Cán bộ công chức đã hủy yêu cầu kiểm tra hàng số " . $request->ma_yeu_cau, $congChuc->ma_cong_chuc);
            }
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $yeuCau->ghi_chu = "Doanh nghiệp hủy đề nghị hủy: " . $request->ghi_chu;
                $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đã hủy yêu cầu kiểm tra hàng số " . $request->ma_yeu_cau, '');
            }
        }
        $yeuCau->save();
        session()->flash('alert-success', 'Hủy đề nghị hủy thành công');
        return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-tieu-huy', ['ma_yeu_cau' => $request->ma_yeu_cau]);
    }
    public function huyYeuCauDaDuyet(Request $request)
    {
        $yeuCau = YeuCauKiemTra::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = 'Doanh nghiệp đề nghị hủy yêu cầu';
        $yeuCau->ghi_chu = $request->ghi_chu;
        $yeuCau->save();

        $soToKhaiNhaps = YeuCauKiemTraChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_to_khai_nhap');
        foreach ($soToKhaiNhaps as $soToKhaiNhap) {
            $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đề nghị hủy yêu cầu kiểm tra hàng số " . $request->ma_yeu_cau, '');
        }
    }

    public function duyetHuyYeuCau(Request $request)
    {
        $yeuCau = YeuCauKiemTra::find($request->ma_yeu_cau);
        $soToKhaiNhaps = YeuCauKiemTraChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_to_khai_nhap');

        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $this->themTienTrinh($soToKhaiNhap, "Cán bộ công chức đã duyệt đề nghị hủy yêu cầu kiểm tra hàng số " . $request->ma_yeu_cau, $congChuc->ma_cong_chuc);
            }
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đã hủy đề nghị hủy yêu cầu kiểm tra hàng số " . $request->ma_yeu_cau, '');
            }
        }
        $yeuCau->trang_thai = 'Đã hủy';
        $yeuCau->ghi_chu = "Công chức duyệt đề nghị hủy: " . $request->ghi_chu;
        $yeuCau->save();
    }


    public function suaYeuCauKiemTra($ma_yeu_cau)
    {
        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            $toKhaiDangXuLys = YeuCauKiemTraChiTiet::join('nhap_hang', 'yeu_cau_kiem_tra_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('yeu_cau_kiem_tra', 'yeu_cau_kiem_tra_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_kiem_tra.ma_yeu_cau')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->where('yeu_cau_kiem_tra.trang_thai', '!=', "Đã hủy")
                ->pluck('yeu_cau_kiem_tra_chi_tiet.so_to_khai_nhap');

            $toKhaiTrongPhieu = YeuCauKiemTraChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->pluck('so_to_khai_nhap');
            $toKhaiDangXuLys = $toKhaiDangXuLys->diff($toKhaiTrongPhieu);
            $toKhaiNhaps = NhapHang::with('hangHoa')
                ->where('nhap_hang.trang_thai', 'Đã nhập hàng')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->whereNotIn('nhap_hang.so_to_khai_nhap', $toKhaiDangXuLys)
                ->get();
            $chiTiets = NhapHang::with('hangHoa')
                ->where('nhap_hang.trang_thai', 'Đã nhập hàng')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->whereIn('nhap_hang.so_to_khai_nhap', $toKhaiTrongPhieu)
                ->get();
            $yeuCau = YeuCauKiemTra::find($ma_yeu_cau);

            return view('quan-ly-kho.yeu-cau-kiem-tra.sua-yeu-cau-kiem-tra', data: compact('toKhaiNhaps', 'doanhNghiep', 'chiTiets', 'ma_yeu_cau', 'yeuCau'));
        }
        return redirect()->back();
    }

    public function suaYeuCauKiemTraSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $yeuCau = YeuCauKiemTra::find($request->ma_yeu_cau);
            if ($yeuCau->trang_thai == 'Đang chờ duyệt') {
                $this->suaYeuCauDangChoDuyet($request, $yeuCau);
            } else {
                $this->suaYeuCauDaDuyet($request, $yeuCau);
            }
            DB::commit();
            session()->flash('alert-success', 'Sửa yêu cầu thành công!');
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-kiem-tra', ['ma_yeu_cau' => $request->ma_yeu_cau]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in SuaKiemTra: ' . $e->getMessage());
            return redirect()->back();
        }
    }


    public function suaYeuCauDangChoDuyet($request, $yeuCau)
    {
        $yeuCau = YeuCauKiemTra::find($request->ma_yeu_cau);
        $yeuCau->ten_doan_tau = $request->ten_doan_tau;
        $yeuCau->save();
        // Decode the JSON data from the form
        $rowsData = json_decode($request->rows_data, true);
        YeuCauKiemTraChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->delete();
        foreach ($rowsData as $row) {
            $ten_hang = '';
            $nhapHang = NhapHang::find($row['so_to_khai_nhap']);
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

                $sumSoLuong = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                    ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                    ->where('nhap_hang.so_to_khai_nhap', $row['so_to_khai_nhap'])
                    ->groupBy('hang_trong_cont.ma_hang_cont') 
                    ->selectRaw('SUM(hang_trong_cont.so_luong) AS total')
                    ->pluck('total')
                    ->sum(); 
            YeuCauKiemTraChiTiet::insert([
                'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                'so_tau' => $nhapHang->phuong_tien_vt_nhap,
                'ngay_dang_ky' => $nhapHang->ngay_dang_ky,
                'ten_hang' => $ten_hang,
                'so_luong' => $sumSoLuong,
                'so_container' => $containers,
                'ma_yeu_cau' => $request->ma_yeu_cau
            ]);
        }
        if ($request->file('file')) {
            $this->luuFile($request, $yeuCau);
        }
    }


    public function suaYeuCauDaDuyet($request, $yeuCau)
    {
        $yeuCau->trang_thai = 'Doanh nghiệp đề nghị sửa yêu cầu';
        $yeuCau->save();
        $suaYeuCau = YeuCauSua::create([
            'ten_doan_tau' => $request->ten_doan_tau,
            'ma_yeu_cau' => $request->ma_yeu_cau,
            'loai_yeu_cau' => 7,
        ]);
        if ($request->file('file')) {
            $this->luuFile($request, yeuCau: $suaYeuCau);
        }

        $this->xuLyThemChiTietYeuCau($request,  "sua", $suaYeuCau, $yeuCau);
    }

    public function xuLyThemChiTietYeuCau($request, $action, $yeuCau, $yeuCauCu)
    {
        $this->xoaTheoDoiTruLui($yeuCau);
        $rowsData = json_decode($request->rows_data, true);
        foreach ($rowsData as $row) {
            $ten_hang = '';
            $nhapHang = NhapHang::find($row['so_to_khai_nhap']);
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

                $sumSoLuong = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                    ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                    ->where('nhap_hang.so_to_khai_nhap', $row['so_to_khai_nhap'])
                    ->groupBy('hang_trong_cont.ma_hang_cont') 
                    ->selectRaw('SUM(hang_trong_cont.so_luong) AS total')
                    ->pluck('total')
                    ->sum(); 
            YeuCauKiemTraChiTietSua::insert([
                'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                'so_tau' => $nhapHang->phuong_tien_vt_nhap,
                'ngay_dang_ky' => $nhapHang->ngay_dang_ky,
                'ten_hang' => $ten_hang,
                'so_container' => $containers,
                'so_luong' => $sumSoLuong,
                'ma_sua_yeu_cau' => $yeuCau->ma_sua_yeu_cau
            ]);
            $this->themTheoDoiTruLui($row['so_to_khai_nhap'], $yeuCau);
            $this->themTienTrinh($row['so_to_khai_nhap'], "Doanh nghiệp đề nghị sửa yêu cầu kiểm tra hàng số " . $yeuCauCu->ma_yeu_cau, '');
        }
    }
    public function xemSuaYeuCau(Request $request)
    {
        $yeuCau = YeuCauKiemTra::find($request->ma_yeu_cau);
        $chiTietYeuCaus = YeuCauKiemTraChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
        $suaYeuCau = YeuCauSua::where('ma_yeu_cau', $request->ma_yeu_cau)
            ->where('loai_yeu_cau', 7)
            ->first();
        $chiTietSuaYeuCaus = YeuCauKiemTraChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->get();
        $doanhNghiep = DoanhNghiep::find($yeuCau->ma_doanh_nghiep);
        return view('quan-ly-kho.yeu-cau-kiem-tra.xem-sua-yeu-cau-kiem-tra', compact('yeuCau', 'chiTietYeuCaus', 'suaYeuCau', 'chiTietSuaYeuCaus', 'doanhNghiep'));
    }

    public function duyetSuaYeuCau(Request $request)
    {
        try {
            DB::beginTransaction();
            $suaYeuCau = YeuCauSua::find($request->ma_sua_yeu_cau);
            $yeuCau = YeuCauKiemTra::find($request->ma_yeu_cau);
            $chiTietSuaYeuCaus = YeuCauKiemTraChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->get();

            YeuCauKiemTraChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->delete();
            $this->xuLySuaYeuCau($chiTietSuaYeuCaus, $yeuCau);

            $yeuCau->ten_doan_tau = $suaYeuCau->ten_doan_tau;
            $yeuCau->trang_thai = 'Đã duyệt';
            if ($yeuCau->file_name && $suaYeuCau->file_name) {
                $yeuCau->file_name = $suaYeuCau->file_name;
                $yeuCau->file_path = $suaYeuCau->file_path;
            }
            $yeuCau->save();

            YeuCauKiemTraChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->delete();
            YeuCauSua::find($request->ma_sua_yeu_cau)->delete();
            DB::commit();
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-kiem-tra', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]);
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetSuaYeuCauKT: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function xuLySuaYeuCau($chiTietSuaYeuCaus, $yeuCau)
    {
        foreach ($chiTietSuaYeuCaus as $chiTietYeuCau) {
            $this->themTienTrinh($chiTietYeuCau->so_to_khai_nhap, "Đã sửa yêu cầu chuyển container và tàu số " . $yeuCau->ma_yeu_cau . " di chuyển hàng từ container " . $chiTietYeuCau->so_container_goc . " (" . $chiTietYeuCau->tau_goc . ") sang " . $chiTietYeuCau->so_container_dich . " (" . $chiTietYeuCau->tau_dich . "), cán bộ công chức phụ trách: " . $yeuCau->congChuc->ten_cong_chuc, $yeuCau->congChuc->ma_cong_chuc);
            YeuCauKiemTraChiTiet::insert([
                'so_to_khai_nhap' => $chiTietYeuCau->so_to_khai_nhap,
                'so_tau' => $chiTietYeuCau->so_tau,
                'ngay_dang_ky' => $chiTietYeuCau->ngay_dang_ky,
                'ten_hang' => $chiTietYeuCau->ten_hang,
                'so_container' => $chiTietYeuCau->so_container,
                'so_luong' => $chiTietYeuCau->so_luong,
                'ma_yeu_cau' => $yeuCau->ma_yeu_cau
            ]);
        }
    }

    public function themTheoDoiHangHoa($chiTietYeuCau, $row, $ma_cong_chuc)
    {
        $ptvtNhanHang = NhapHang::find($chiTietYeuCau->so_to_khai_nhap)->phuong_tien_vt_nhap;
        $so_seal = NiemPhong::where('so_container', $row->so_container)->first()->so_seal ?? "";
        TheoDoiHangHoa::insert([
            'so_to_khai_nhap' => $chiTietYeuCau->so_to_khai_nhap,
            'ma_hang'  => $row->ma_hang,
            'thoi_gian'  => now(),
            'so_luong_xuat'  => $row->so_luong,
            'so_luong_ton'  => $row->so_luong,
            'phuong_tien_cho_hang' => $ptvtNhanHang,
            'cong_viec' => 7,
            'phuong_tien_nhan_hang' => '',
            'so_container' => $row->so_container,
            'so_seal' => $so_seal,
            'ma_cong_chuc' => $ma_cong_chuc,
            'ma_yeu_cau' => $chiTietYeuCau->ma_yeu_cau,
        ]);
    }

    public function huySuaYeuCau(Request $request)
    {
        $yeuCau = YeuCauKiemTra::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = 'Đã duyệt';
        $yeuCau->save();
        $suaYeuCau = YeuCauSua::where('ma_sua_yeu_cau', $request->ma_sua_yeu_cau)->first();
        YeuCauKiemTraChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->delete();
        $suaYeuCau->delete();

        $chiTiets =  YeuCauKiemTraChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
        foreach ($chiTiets as $chiTiet) {
            if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                $yeuCau->ghi_chu = "Doanh nghiệp hủy đề nghị sửa: " . $request->ghi_chu;
                $this->themTienTrinh($chiTiet->so_to_khai_nhap, "Doanh nghiệp hủy đề nghị sửa yêu cầu kiểm tra số " . $yeuCau->ma_yeu_cau, '');
            } else {
                $yeuCau->ghi_chu = "Công chức từ chối đề nghị sửa: " . $request->ghi_chu;
                $this->themTienTrinh($chiTiet->so_to_khai_nhap, "Công chức từ chối đề nghị sửa yêu cầu kiểm tra số " . $yeuCau->ma_yeu_cau, $this->getCongChucHienTai()->ma_cong_chuc);
            }
        }
        $yeuCau->save();

        session()->flash('alert-success', 'Hủy yêu cầu sửa thành công!');
        return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-kiem-tra', ['ma_yeu_cau' => $request->ma_yeu_cau]);
    }

    public function duyetHoanThanh(Request $request)
    {
        $yeuCau = YeuCauKiemTra::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = "Đã hoàn thành";
        $yeuCau->save();
        session()->flash('alert-success', 'Duyệt hoàn thành yêu cầu thành công');
        return redirect()->back();
    }

    private function getCongChucHienTai()
    {
        return CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
    }
    public function suaSealKiemTra(Request $request)
    {
        try {
            DB::beginTransaction();

            $yeuCau = YeuCauKiemTra::find($request->ma_yeu_cau);
            $sealMoi = SealMoiChoKiemTra::where('ma_yeu_cau', $request->ma_yeu_cau)
                ->where('so_container', $request->so_container)
                ->first();
            if ($request->so_seal) {
                $so_seal_moi = $request->so_seal;
            } else {
                $availableSeals = $this->getSealNhoNhat($request->loai_seal, $yeuCau->ma_cong_chuc);
                if (!$availableSeals) {
                    session()->flash('alert-danger', 'Không đủ số seal niêm phong để cấp cho yêu cầu này');
                    return redirect()->back();
                }
                $so_seal_moi = $availableSeals->shift();
            }
            $sealMoi->so_seal_moi = $so_seal_moi;
            $sealMoi->save();

            $suDungSeal = $this->suDungSeal($so_seal_moi, $request->so_container, $yeuCau->ma_cong_chuc);
            if (!$suDungSeal) {
                session()->flash('alert-danger', 'Seal này đã được sử dụng');
                return redirect()->back();
            }
            $this->updateNiemPhong($so_seal_moi, $request->so_container, $yeuCau->ma_cong_chuc);

            session()->flash('alert-success', 'Sửa seal niêm phong thành công');
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in suaSealKiemTra: ' . $e->getMessage());
            return redirect()->back();
        }
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

    public function themTienTrinh($so_to_khai_nhap, $ten_cong_viec, $ma_cong_chuc)
    {
        TienTrinh::insert([
            'so_to_khai_nhap' => $so_to_khai_nhap,
            'ten_cong_viec' => $ten_cong_viec,
            'ngay_thuc_hien' => now(),
            'ma_cong_chuc' => $ma_cong_chuc
        ]);
    }

    public function themTheoDoiTruLui($so_to_khai_nhap, $yeuCau)
    {
        $hangHoas = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.so_to_khai_nhap', $so_to_khai_nhap)
            ->get();
        $nhapHang = NhapHang::find($so_to_khai_nhap);

        $theoDoi = TheoDoiTruLui::create([
            'so_to_khai_nhap' => $so_to_khai_nhap,
            'so_ptvt_nuoc_ngoai' => '',
            'phuong_tien_vt_nhap' => $nhapHang->phuong_tien_vt_nhap,
            'ngay_them' => now(),
            'cong_viec' => 7,
            'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
        ]);
        foreach ($hangHoas as $hangHoa) {
            TheoDoiTruLuiChiTiet::insert(
                [
                    'ten_hang' => $hangHoa->ten_hang,
                    'so_luong_xuat' => 0,
                    'so_luong_chua_xuat' => $hangHoa->so_luong,
                    'ma_theo_doi' => $theoDoi->ma_theo_doi,
                    'so_container' => $hangHoa->so_container,
                    'so_seal' => '',


                ]
            );
        }
    }
    public function xoaTheoDoiTruLui($yeuCau)
    {
        TheoDoiTruLuiChiTiet::whereIn('ma_theo_doi', function ($query) use ($yeuCau) {
            $query->select('ma_theo_doi')
                ->from('theo_doi_tru_lui')
                ->where('cong_viec', 7)
                ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau);
        })->delete();

        TheoDoiTruLui::where('cong_viec', 7)
            ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
            ->delete();
    }

    public function luuFile($request, $yeuCau)
    {
        if ($yeuCau->file_name) {
            Storage::delete('public/' . $yeuCau->file->path);
            $yeuCau->file->delete();
        }

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();

        while (Storage::exists('public/yeu_cau_kiem_tra/' . $fileName)) {
            $fileInfo = pathinfo(path: $fileName);
            $fileName = $fileInfo['filename'] . '_' . time() . '.' . $fileInfo['extension'];
        }

        $filePath = $file->storeAs('yeu_cau_kiem_tra', $fileName, 'public');

        $yeuCau->file_name = $fileName;
        $yeuCau->file_path = $filePath;
        $yeuCau->save();
    }
    public function downloadFile($maYeuCau, $xemSua = false)
    {

        $yeuCau = YeuCauKiemTra::findOrFail($maYeuCau);


        if (!$yeuCau->file_name) {
            session()->flash('alert-danger', 'Không tìm thấy file trong hệ thống');
            return redirect()->back();
        }

        $filePath = storage_path('app/public/' . $yeuCau->file_path);
        return response()->download($filePath, $yeuCau->file_name);
    }
}
