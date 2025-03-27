<?php

namespace App\Http\Controllers;

use App\Models\YeuCauHangVeKhoChiTiet;
use App\Models\CongChuc;
use App\Models\PTVTXuatCanh;
use App\Models\DoanhNghiep;
use App\Models\HangTrongCont;
use App\Models\TheoDoiTruLui;
use App\Models\NhapHang;
use App\Models\XuatHang;
use App\Models\XuatHangCont;
use App\Models\NiemPhong;
use App\Models\TienTrinh;
use App\Models\YeuCauHangVeKho;
use App\Models\TheoDoiHangHoa;
use App\Models\TheoDoiTruLuiChiTiet;
use App\Models\YeuCauHangVeKhoChiTietSua;
use App\Models\YeuCauSua;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\XuatHangService;
use Illuminate\Support\Facades\Storage;

class YeuCauHangVeKhoController extends Controller
{
    public function danhSachYeuCauHangVeKho()
    {
        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $data = YeuCauHangVeKho::join('doanh_nghiep', 'yeu_cau_hang_ve_kho.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
                ->join('yeu_cau_hang_ve_kho_chi_tiet', 'yeu_cau_hang_ve_kho.ma_yeu_cau', 'yeu_cau_hang_ve_kho_chi_tiet.ma_yeu_cau')
                ->select(
                    'doanh_nghiep.*',
                    'yeu_cau_hang_ve_kho.*',
                    DB::raw('GROUP_CONCAT(DISTINCT yeu_cau_hang_ve_kho_chi_tiet.so_to_khai_nhap ORDER BY yeu_cau_hang_ve_kho_chi_tiet.so_to_khai_nhap ASC SEPARATOR ", ") as so_to_khai_nhap_list')

                )
                ->groupBy('yeu_cau_hang_ve_kho.ma_yeu_cau')
                ->orderBy('ma_yeu_cau', 'desc')
                ->get();
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $maDoanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep;
            $data = YeuCauHangVeKho::join('doanh_nghiep', 'yeu_cau_hang_ve_kho.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
                ->join('yeu_cau_hang_ve_kho_chi_tiet', 'yeu_cau_hang_ve_kho.ma_yeu_cau', 'yeu_cau_hang_ve_kho_chi_tiet.ma_yeu_cau')
                ->where('yeu_cau_hang_ve_kho.ma_doanh_nghiep', $maDoanhNghiep)
                ->select(
                    'doanh_nghiep.*',
                    'yeu_cau_hang_ve_kho.*',
                    DB::raw('GROUP_CONCAT(DISTINCT yeu_cau_hang_ve_kho_chi_tiet.so_to_khai_nhap ORDER BY yeu_cau_hang_ve_kho_chi_tiet.so_to_khai_nhap ASC SEPARATOR ", ") as so_to_khai_nhap_list')

                )
                ->groupBy('yeu_cau_hang_ve_kho.ma_yeu_cau')
                ->orderBy('ma_yeu_cau', 'desc')
                ->get();
        }
        return view('quan-ly-kho.yeu-cau-hang-ve-kho.danh-sach-yeu-cau-hang-ve-kho', data: compact(var_name: 'data'));
    }

    public function themYeuCauHangVeKho()
    {
        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            $toKhaiDangXuLys = YeuCauHangVeKhoChiTiet::join('nhap_hang', 'yeu_cau_hang_ve_kho_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('yeu_cau_hang_ve_kho', 'yeu_cau_hang_ve_kho_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_hang_ve_kho.ma_yeu_cau')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->where('yeu_cau_hang_ve_kho.trang_thai', "1")
                ->pluck('yeu_cau_hang_ve_kho_chi_tiet.so_to_khai_nhap');

            $toKhaiNhaps = NhapHang::with('hangHoa')
                ->where('nhap_hang.trang_thai', '2')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->whereNotIn('nhap_hang.so_to_khai_nhap', $toKhaiDangXuLys)
                ->get();

            return view('quan-ly-kho.yeu-cau-hang-ve-kho.them-yeu-cau-hang-ve-kho', data: compact('toKhaiNhaps', 'doanhNghiep'));
        }
        return redirect()->back();
    }

    public function themYeuCauHangVeKhoSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();

            $yeuCau = YeuCauHangVeKho::create([
                'ma_doanh_nghiep' => $doanhNghiep->ma_doanh_nghiep,
                'trang_thai' => '1',
                'ngay_yeu_cau' => now(),
            ]);

            // Decode the JSON data from the form
            $rowsData = json_decode($request->rows_data, true);
            foreach ($rowsData as $row) {
                $nhapHang = NhapHang::find($row['so_to_khai_nhap']);
                $firstResult = $this->getThongTinTenHang($row);

                YeuCauHangVeKhoChiTiet::insert([
                    'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                    'so_tau' => $nhapHang->phuong_tien_vt_nhap,
                    'ngay_dang_ky' => $nhapHang->ngay_dang_ky,
                    'ten_hang' => $firstResult['hang_hoa'] ?? '',
                    'so_container' => $row['so_container'],
                    'ten_phuong_tien_vt' => $row['ten_phuong_tien_vt'],
                    'ma_yeu_cau' => $yeuCau->ma_yeu_cau
                ]);
                $this->themTheoDoiTruLui($row['so_to_khai_nhap'], $yeuCau);
                $this->themTienTrinh($row['so_to_khai_nhap'], "Doanh nghiệp đã yêu cầu đưa hàng trở lại kho ban đầu số " . $yeuCau->ma_yeu_cau, '');
            }

            DB::commit();
            session()->flash('alert-success', 'Thêm yêu cầu thành công!');
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-hang-ve-kho', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in ThemHangVeKho: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function getThongTinTenHang($row)
    {
        $data = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
            ->where('nhap_hang.so_to_khai_nhap', $row['so_to_khai_nhap'])
            ->where('hang_trong_cont.so_container', $row['so_container'])
            ->select(
                'hang_trong_cont.so_container',
                'hang_hoa.ten_hang',
                'hang_trong_cont.so_luong'
            )
            ->get()
            ->groupBy('so_container');

        $result = $data->map(function ($items, $so_container) {
            $hang_hoa_info = $items->map(function ($item) {
                return "{$item->ten_hang} - Số lượng: {$item->so_luong}";
            })->implode('<br>');

            return [
                'hang_hoa' => $hang_hoa_info,
            ];
        });
        return $result->first();
    }

    public function thongTinYeuCauHangVeKho($ma_yeu_cau)
    {

        $yeuCau = YeuCauHangVeKho::find($ma_yeu_cau);
        $doanhNghiep = DoanhNghiep::find($yeuCau->ma_doanh_nghiep);
        $chiTiets = YeuCauHangVeKho::join('yeu_cau_hang_ve_kho_chi_tiet', 'yeu_cau_hang_ve_kho.ma_yeu_cau', '=', 'yeu_cau_hang_ve_kho_chi_tiet.ma_yeu_cau')
            ->join('nhap_hang', 'yeu_cau_hang_ve_kho_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
            ->where('yeu_cau_hang_ve_kho.ma_yeu_cau', $ma_yeu_cau)
            ->pluck('yeu_cau_hang_ve_kho_chi_tiet.so_to_khai_nhap');

        $nhapHangs = NhapHang::whereIn('nhap_hang.so_to_khai_nhap', $chiTiets)
            ->join('yeu_cau_hang_ve_kho_chi_tiet', 'nhap_hang.so_to_khai_nhap', '=', 'yeu_cau_hang_ve_kho_chi_tiet.so_to_khai_nhap')
            ->where('yeu_cau_hang_ve_kho_chi_tiet.ma_yeu_cau', $ma_yeu_cau)
            ->select('nhap_hang.*')
            ->get();
        $congChucs = CongChuc::where('is_chi_xem', 0)->get();
        $chiTiets = YeuCauHangVeKhoChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->get();
        return view('quan-ly-kho.yeu-cau-hang-ve-kho.thong-tin-yeu-cau-hang-ve-kho', compact('yeuCau', 'nhapHangs', 'doanhNghiep', 'congChucs', 'chiTiets')); // Pass data to the view
    }
    public function duyetYeuCauHangVeKho(Request $request)
    {
        try {
            DB::beginTransaction();
            $yeuCau = YeuCauHangVeKho::find($request->ma_yeu_cau);
            if ($yeuCau) {
                $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
                $congChucPhuTrach = CongChuc::find($request->ma_cong_chuc);

                $yeuCau->ma_cong_chuc = $congChucPhuTrach->ma_cong_chuc;
                $yeuCau->ngay_hoan_thanh = now();
                $yeuCau->trang_thai = '2';
                $yeuCau->save();

                $chiTietYeuCaus = YeuCauHangVeKhoChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
                foreach ($chiTietYeuCaus as $chiTietYeuCau) {
                    // $count = XuatHang::where('so_to_khai_nhap', $chiTietYeuCau->so_to_khai_nhap)->count();
                    $hangTrongConts = HangTrongCont::join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                        ->where('hang_hoa.so_to_khai_nhap', $chiTietYeuCau->so_to_khai_nhap)
                        ->select('hang_trong_cont.*', 'hang_hoa.ma_hang')
                        ->get();
                    foreach ($hangTrongConts as $row) {
                        $so_seal = NiemPhong::where('so_container', $row->so_container)->first()->so_seal ?? '';
                        TheoDoiHangHoa::insert([
                            'so_to_khai_nhap' => $chiTietYeuCau->so_to_khai_nhap,
                            'ma_hang'  => $row->ma_hang,
                            'thoi_gian'  => now(),
                            'so_luong_xuat'  => $row->so_luong,
                            'so_luong_ton'  => $row->so_luong,
                            'phuong_tien_cho_hang' => $chiTietYeuCau->ten_phuong_tien_vt,
                            'cong_viec' => 5,
                            'phuong_tien_nhan_hang' => '',
                            'so_container' => $row->so_container,
                            'so_seal' => $so_seal,
                            'ma_cong_chuc' => $congChucPhuTrach->ma_cong_chuc,
                            'ma_yeu_cau' => $yeuCau->ma_yeu_cau,

                        ]);
                    }

                    $nhapHang = NhapHang::find($chiTietYeuCau->so_to_khai_nhap);
                    $nhapHang->update([
                        'trang_thai' => '6'
                    ]);
                    // $xuatHang = XuatHang::create([
                    //     'so_to_khai_nhap' => $nhapHang->so_to_khai_nhap,
                    //     'ma_loai_hinh' => "KNQ",
                    //     'lan_xuat_canh' => $count + 1,
                    //     'ngay_dang_ky' => $yeuCau->ngay_yeu_cau,
                    //     'ngay_thong_quan' => now(),
                    //     'ngay_xuat_canh' => now(),
                    //     'so_container' => $nhapHang->so_container,
                    //     'ma_doanh_nghiep' => $nhapHang->ma_doanh_nghiep,
                    //     'trang_thai' => 'KNQ',
                    //     'ma_cong_chuc' => $congChuc->ma_cong_chuc,
                    // ]);
                    // foreach ($nhapHang->hangHoa as $hangHoa) {
                    //     $hangTrongCont = $hangHoa->hangTrongCont;
                    //     if ($hangTrongCont) {
                    //         XuatHangCont::insert([
                    //             'so_to_khai_xuat' => $xuatHang->so_to_khai_xuat,
                    //             'ma_hang_cont' => $hangTrongCont->ma_hang_cont,
                    //             'so_luong_xuat' => $hangTrongCont->so_luong,
                    //             'so_luong_ton' => 0,
                    //             'so_luong_hien_tai' => $hangTrongCont->so_luong,
                    //             'so_container' => $hangTrongCont->so_container,
                    //             'tri_gia' => $hangTrongCont->so_luong * $hangHoa->don_gia, // Calculate tri_gia
                    //         ]);
                    //         // $so_seal = NiemPhong::where('so_container', $hangTrongCont->so_container)->first()->so_seal;

                    //     }
                    // }

                    // foreach ($nhapHang->hangHoa as $hangHoa) {
                    //     $hangHoa->hangTrongCont->update([
                    //         'so_luong' => 0
                    //     ]);
                    // }

                    // $service = new XuatHangService();
                    // $ly_do = " do tờ khai nhập " . $chiTietYeuCau->so_to_khai_nhap . " đã quay về kho ban đầu";
                    // $service->huyPhieuXuats($chiTietYeuCau->so_to_khai_nhap, $ly_do);
                    // $service->huyYeuCauCuaToKhai($chiTietYeuCau->so_to_khai_nhap, $ly_do);
                    // $service->moveDatabase($xuatHang->so_to_khai_nhap);

                    $this->themTheoDoiTruLui($chiTietYeuCau->so_to_khai_nhap, $yeuCau);
                    $this->themTienTrinh($chiTietYeuCau->so_to_khai_nhap, "Cán bộ công chức đã duyệt yêu cầu đưa hàng trở lại kho ban đầu số " . $request->ma_yeu_cau . ", cán bộ công chức phụ trách: " . $congChucPhuTrach->ten_cong_chuc, $congChuc->ma_cong_chuc);
                }

                session()->flash('alert-success', 'Duyệt yêu cầu thành công!');
            }
            DB::commit();
            // return redirect()->back();
            return redirect()->route('quan-ly-kho.danh-sach-yeu-cau-hang-ve-kho');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in duyetYeuCauHangVeKho: ' . $e->getMessage());
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            return redirect()->back();
        }
    }

    public function huyYeuCauHangVeKho(Request $request)
    {
        try {
            DB::beginTransaction();
            $yeuCau = YeuCauHangVeKho::find($request->ma_yeu_cau);
            if ($yeuCau->trang_thai == "1") {
                if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
                    $this->huyYeuCauHangVeKhoFunc($request->ma_yeu_cau, $request->ghi_chu, "Cán bộ công chức", '');
                } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                    $this->huyYeuCauHangVeKhoFunc($request->ma_yeu_cau, $request->ghi_chu, "Doanh nghiệp", '');
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
            Log::error('Error in huyYeuCauHangVeKho: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function huyHuyYeuCau(Request $request)
    {
        $yeuCau = YeuCauHangVeKho::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = '2';

        $soToKhaiNhaps = YeuCauHangVeKhoChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_to_khai_nhap');
        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $yeuCau->ghi_chu = "Công chức từ chối đề nghị hủy: " . $request->ghi_chu;
                $this->themTienTrinh($soToKhaiNhap, "Cán bộ công chức đã từ chối hủy yêu cầu đưa hàng về kho số " . $request->ma_yeu_cau, $congChuc->ma_cong_chuc);
            }
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $yeuCau->ghi_chu = "Doanh nghiệp hủy đề nghị hủy: " . $request->ghi_chu;
                $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đã hủy yêu cầu đưa hàng về kho số " . $request->ma_yeu_cau, '');
            }
        }
        $yeuCau->save();
        session()->flash('alert-success', 'Hủy đề nghị hủy thành công');
        return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-hang-ve-kho', ['ma_yeu_cau' => $request->ma_yeu_cau]);
    }
    public function huyYeuCauDaDuyet(Request $request)
    {
        $yeuCau = YeuCauHangVeKho::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = '4';
        $yeuCau->ghi_chu = $request->ghi_chu;
        $yeuCau->save();

        $soToKhaiNhaps = YeuCauHangVeKhoChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_to_khai_nhap');
        foreach ($soToKhaiNhaps as $soToKhaiNhap) {
            $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đề nghị hủy yêu cầu đưa hàng về kho hàng số " . $request->ma_yeu_cau, '');
        }
    }
    public function duyetHuyYeuCau(Request $request)
    {
        $yeuCau = YeuCauHangVeKho::find($request->ma_yeu_cau);
        $soToKhaiNhaps = YeuCauHangVeKhoChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_to_khai_nhap');

        $this->quayNguocYeuCau($soToKhaiNhaps, $yeuCau);

        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $this->themTienTrinh($soToKhaiNhap, "Cán bộ công chức đã duyệt đề nghị hủy yêu cầu tiêu hàng hủy số " . $request->ma_yeu_cau, $congChuc->ma_cong_chuc);
            }
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đã hủy đề nghị hủy yêu cầu đưa hàng về kho hàng số " . $request->ma_yeu_cau, '');
            }
        }
        $yeuCau->trang_thai = '0';
        $yeuCau->ghi_chu = "Công chức duyệt đề nghị hủy: " . $request->ghi_chu;
        $yeuCau->save();
    }

    public function huyYeuCauHangVeKhoFunc($ma_yeu_cau, $ghi_chu, $user, $ly_do)
    {
        $yeuCau = YeuCauHangVeKho::find($ma_yeu_cau);
        if ($yeuCau) {
            if ($yeuCau->trang_thai == "1") {

                $soToKhaiNhaps = YeuCauHangVeKhoChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->pluck('so_to_khai_nhap');

                if ($user == "Cán bộ công chức") {
                    $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
                    foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                        $this->themTienTrinh($soToKhaiNhap, "Cán bộ công chức đã hủy yêu cầu đưa hàng trở lại kho ban đầu số " . $ma_yeu_cau . $ly_do, $congChuc->ma_cong_chuc);
                    }
                } elseif ($user == "Doanh nghiệp") {
                    foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                        $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đã hủy yêu cầu đưa hàng trở lại kho ban đầu số " . $ma_yeu_cau . $ly_do, '');
                    }
                } elseif ($user == "Hệ thống") {
                    foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                        $this->themTienTrinh($soToKhaiNhap, "Hệ thống đã hủy yêu cầu đưa hàng trở lại kho ban đầu số " . $ma_yeu_cau . $ly_do, '');
                    }
                }
                $yeuCau->trang_thai = '0';
                $yeuCau->ghi_chu = $ghi_chu;
                $yeuCau->save();
            }
        }
    }
    public function suaYeuCauHangVeKho($ma_yeu_cau)
    {
        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            $toKhaiDangXuLys = YeuCauHangVeKhoChiTiet::join('nhap_hang', 'yeu_cau_hang_ve_kho_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('yeu_cau_hang_ve_kho', 'yeu_cau_hang_ve_kho_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_hang_ve_kho.ma_yeu_cau')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->where('yeu_cau_hang_ve_kho.trang_thai', "1")
                ->pluck('yeu_cau_hang_ve_kho_chi_tiet.so_to_khai_nhap');

            $toKhaiTrongPhieu = YeuCauHangVeKhoChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->pluck('so_to_khai_nhap');
            $toKhaiDangXuLys = $toKhaiDangXuLys->diff($toKhaiTrongPhieu);
            $toKhaiNhaps = NhapHang::with('hangHoa')
                ->where('nhap_hang.trang_thai', '2')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->whereNotIn('nhap_hang.so_to_khai_nhap', $toKhaiDangXuLys)
                ->get();
            $chiTiets = YeuCauHangVeKhoChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->get();
            $yeuCau = YeuCauHangVeKho::find($ma_yeu_cau);
            return view('quan-ly-kho.yeu-cau-hang-ve-kho.sua-yeu-cau-hang-ve-kho', data: compact('toKhaiNhaps', 'doanhNghiep', 'chiTiets', 'yeuCau'));
        }
        return redirect()->back();
    }

    public function suaYeuCauHangVeKhoSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            // Decode the JSON data from the form
            $yeuCau = YeuCauHangVeKho::find($request->ma_yeu_cau);

            if ($yeuCau->trang_thai == '1') {
                $this->suaYeuCauDangChoDuyet($request, $yeuCau);
            } else {
                $this->suaYeuCauDaDuyet($request, $yeuCau);
            }

            DB::commit();
            session()->flash('alert-success', 'Sửa yêu cầu thành công!');
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-hang-ve-kho', ['ma_yeu_cau' => $request->ma_yeu_cau]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in SuaHangVeKho: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function suaYeuCauDangChoDuyet($request, $yeuCau)
    {
        $rowsData = json_decode($request->rows_data, true);
        YeuCauHangVeKhoChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->delete();
        foreach ($rowsData as $row) {
            $nhapHang = NhapHang::find($row['so_to_khai_nhap']);
            $firstResult = $this->getThongTinTenHang($row);

            YeuCauHangVeKhoChiTiet::insert([
                'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                'so_tau' => $nhapHang->phuong_tien_vt_nhap,
                'ngay_dang_ky' => $nhapHang->ngay_dang_ky,
                'ten_hang' => $firstResult['hang_hoa'] ?? '',
                'so_container' => $row['so_container'],
                'ten_phuong_tien_vt' => $row['ten_phuong_tien_vt'],
                'ma_yeu_cau' => $request->ma_yeu_cau
            ]);
        }
        if ($request->file('file')) {
            $this->luuFile($request, $yeuCau);
        }
    }
    public function suaYeuCauDaDuyet($request, $yeuCau)
    {
        $yeuCau->trang_thai = '3';
        $yeuCau->save();
        $suaYeuCau = YeuCauSua::create([
            'ten_doan_tau' => $request->ten_doan_tau,
            'ma_yeu_cau' => $request->ma_yeu_cau,
            'loai_yeu_cau' => 5,
        ]);
        if ($request->file('file')) {
            $this->luuFile($request, $suaYeuCau);
        }

        $rowsData = json_decode($request->rows_data, true);
        foreach ($rowsData as $row) {
            $nhapHang = NhapHang::find($row['so_to_khai_nhap']);
            $firstResult = $this->getThongTinTenHang($row);
            YeuCauHangVeKhoChiTietSua::insert([
                'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                'so_container' => $row['so_container'],
                'so_tau' => $nhapHang->phuong_tien_vt_nhap,
                'ten_hang' => $firstResult['hang_hoa'] ?? '',
                'ngay_dang_ky' => $nhapHang->ngay_dang_ky,
                'ma_sua_yeu_cau' => $suaYeuCau->ma_sua_yeu_cau,
                'ten_phuong_tien_vt' => $row['ten_phuong_tien_vt']
            ]);
            $this->themTienTrinh($row['so_to_khai_nhap'], "Doanh nghiệp đã yêu cầu sửa yêu cầu đưa hàng về kho số " . $yeuCau->ma_yeu_cau, '');
        }
    }
    public function xemSuaYeuCau(Request $request)
    {
        $yeuCau = YeuCauHangVeKho::where('ma_yeu_cau', $request->ma_yeu_cau)
            ->leftJoin('cong_chuc', 'yeu_cau_hang_ve_kho.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->first();
        $chiTiets = YeuCauHangVeKhoChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();

        $suaYeuCau = YeuCauSua::where('ma_yeu_cau', $request->ma_yeu_cau)
            ->where('loai_yeu_cau', 5)
            ->first();
        $chiTietSuas = YeuCauHangVeKhoChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->get();
        $doanhNghiep = DoanhNghiep::find($yeuCau->ma_doanh_nghiep);
        return view('quan-ly-kho.yeu-cau-hang-ve-kho.xem-sua-yeu-cau-hang-ve-kho', compact('yeuCau', 'chiTiets', 'suaYeuCau', 'chiTietSuas', 'doanhNghiep'));
    }

    public function duyetSuaYeuCau(Request $request)
    {
        try {
            DB::beginTransaction();
            $suaYeuCau = YeuCauSua::find($request->ma_sua_yeu_cau);
            $yeuCau = YeuCauHangVeKho::find($request->ma_yeu_cau);

            $chiTietSuaYeuCaus = YeuCauHangVeKhoChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->get();
            $soToKhaiSauSuas = $chiTietSuaYeuCaus->pluck('so_to_khai_nhap')->toArray();

            $chiTietYeuCaus = YeuCauHangVeKhoChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
            $soToKhaiTruocSuas = $chiTietYeuCaus->pluck('so_to_khai_nhap')->toArray();

            $soToKhaiCanQuayNguoc = array_diff($soToKhaiTruocSuas, $soToKhaiSauSuas);
            $soToKhaiCanXuLy =  $soToKhaiSauSuas;

            $this->quayNguocYeuCau($soToKhaiCanQuayNguoc, $yeuCau);
            YeuCauHangVeKhoChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->delete();
            $this->xuLySuaYeuCau($chiTietSuaYeuCaus, $soToKhaiCanXuLy, $yeuCau);

            $yeuCau->trang_thai = '2';
            if ($yeuCau->file_name && $suaYeuCau->file_name) {
                $yeuCau->file_name = $suaYeuCau->file_name;
                $yeuCau->file_path = $suaYeuCau->file_path;
            }
            $yeuCau->save();

            YeuCauHangVeKhoChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->delete();
            YeuCauSua::find($request->ma_sua_yeu_cau)->delete();
            DB::commit();
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-hang-ve-kho', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]);
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetSuaYeuCauHangVeKho: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function quayNguocYeuCau($soToKhaiCanQuayNguoc, $yeuCau)
    {
        foreach ($soToKhaiCanQuayNguoc as $soToKhai) {
            TheoDoiHangHoa::where('so_to_khai_nhap', $soToKhai)
                ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->where('cong_viec', 5)
                ->delete();
            NhapHang::where('so_to_khai_nhap', $soToKhai)->update([
                'trang_thai' => "2",
            ]);
        }
    }

    public function xuLySuaYeuCau($chiTietSuaYeuCaus, $soToKhaiCanXuLy, $yeuCau)
    {
        $this->xoaTheoDoiTruLui($yeuCau);
        foreach ($chiTietSuaYeuCaus as $chiTietYeuCau) {
            if (in_array($chiTietYeuCau->so_to_khai_nhap, $soToKhaiCanXuLy)) {

                NhapHang::where('so_to_khai_nhap', $chiTietYeuCau->so_to_khai_nhap)->update([
                    'trang_thai' => "6",
                ]);
                $this->themTheoDoiTruLui($chiTietYeuCau->so_to_khai_nhap, $yeuCau);
                $this->themTienTrinh($chiTietYeuCau->so_to_khai_nhap, "Đã sửa yêu cầu đưa hàng về kho ban đầu số " . $yeuCau->ma_yeu_cau  . ", cán bộ công chức phụ trách: " . $yeuCau->congChuc->ten_cong_chuc, $yeuCau->congChuc->ma_cong_chuc);
            }
            YeuCauHangVeKhoChiTiet::insert([
                'so_to_khai_nhap' => $chiTietYeuCau->so_to_khai_nhap,
                'so_container' => $chiTietYeuCau->so_container,
                'so_tau' => $chiTietYeuCau->so_tau,
                'ngay_dang_ky' => $chiTietYeuCau->ngay_dang_ky,
                'ten_hang' => $chiTietYeuCau->ten_hang,
                'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
                'ten_phuong_tien_vt' => $chiTietYeuCau->ten_phuong_tien_vt
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
            'cong_viec' => 5,
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

    public function duyetHoanThanh(Request $request)
    {
        $yeuCau = YeuCauHangVeKho::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = "Đã hoàn thành";
        $yeuCau->save();
        session()->flash('alert-success', 'Duyệt hoàn thành yêu cầu thành công');
        return redirect()->back();
    }

    public function xoaTheoDoiTruLui($yeuCau)
    {
        TheoDoiTruLuiChiTiet::whereIn('ma_theo_doi', function ($query) use ($yeuCau) {
            $query->select('ma_theo_doi')
                ->from('theo_doi_tru_lui')
                ->where('cong_viec', 5)
                ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau);
        })->delete();

        TheoDoiTruLui::where('cong_viec', 5)
            ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
            ->delete();
    }
    public function huySuaYeuCau(Request $request)
    {
        $yeuCau = YeuCauHangVeKho::find($request->ma_yeu_cau);

        $suaYeuCau = YeuCauSua::find($request->ma_sua_yeu_cau);
        YeuCauHangVeKhoChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->delete();
        $suaYeuCau->delete();

        $chiTiets = YeuCauHangVeKhoChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
        if ($yeuCau->trang_thai = '3') {
            foreach ($chiTiets as $chiTiet) {
                if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                    $yeuCau->ghi_chu = "Doanh nghiệp hủy đề nghị sửa: " . $request->ghi_chu;
                    $this->themTienTrinh($chiTiet->so_to_khai_nhap, "Doanh nghiệp hủy đề nghị sửa yêu cầu đưa hàng về kho số " . $yeuCau->ma_yeu_cau, '');
                } else {
                    $yeuCau->ghi_chu = "Công chức từ chối đề nghị sửa: " . $request->ghi_chu;
                    $this->themTienTrinh($chiTiet->so_to_khai_nhap, "Công chức từ chối đề nghị sửa yêu cầu đưa hàng về kho số " . $yeuCau->ma_yeu_cau, $this->getCongChucHienTai()->ma_cong_chuc);
                }
            }
        } else {
            foreach ($chiTiets as $chiTiet) {
                if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                    $yeuCau->ghi_chu = "Doanh nghiệp hủy đề nghị hủy: " . $request->ghi_chu;
                    $this->themTienTrinh($chiTiet->so_to_khai_nhap, "Doanh nghiệp hủy đề nghị hủy yêu cầu đưa hàng về kho số " . $yeuCau->ma_yeu_cau, '');
                } else {
                    $yeuCau->ghi_chu = "Công chức từ chối đề nghị hủy: " . $request->ghi_chu;
                    $this->themTienTrinh($chiTiet->so_to_khai_nhap, "Công chức từ chối đề nghị hủy yêu cầu đưa hàng về kho số " . $yeuCau->ma_yeu_cau, $this->getCongChucHienTai()->ma_cong_chuc);
                }
            }
        }

        $yeuCau->trang_thai = '2';
        $yeuCau->save();

        session()->flash('alert-success', 'Hủy yêu cầu sửa thành công!');
        return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-hang-ve-kho', ['ma_yeu_cau' => $request->ma_yeu_cau]);
    }
    public function luuFile($request, $yeuCau)
    {
        if ($yeuCau->file_name) {
            Storage::delete('public/' . $yeuCau->file->path);
        }

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();

        while (Storage::exists('public/yeu_cau_hang_ve_kho/' . $fileName)) {
            $fileInfo = pathinfo(path: $fileName);
            $fileName = $fileInfo['filename'] . '_' . time() . '.' . $fileInfo['extension'];
        }

        $filePath = $file->storeAs('yeu_cau_hang_ve_kho', $fileName, 'public');

        $yeuCau->file_name = $fileName;
        $yeuCau->file_path = $filePath;
        $yeuCau->save();
    }
    public function downloadFile($maYeuCau, $xemSua = false)
    {
        if ($xemSua) {
            $yeuCau = YeuCauSua::findOrFail($maYeuCau);
        } else {
            $yeuCau = YeuCauHangVeKho::findOrFail($maYeuCau);
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
}
