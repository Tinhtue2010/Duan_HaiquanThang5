<?php

namespace App\Http\Controllers;

use App\Models\YeuCauTieuHuyChiTiet;
use App\Models\CongChuc;
use App\Models\DoanhNghiep;
use App\Models\HangTrongCont;
use App\Models\NhapHang;
use App\Models\NiemPhong;
use App\Models\TienTrinh;
use App\Models\YeuCauTieuHuy;
use App\Models\TheoDoiHangHoa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PTVanTaiController;
use App\Models\TheoDoiTruLui;
use App\Models\TheoDoiTruLuiChiTiet;
use App\Models\YeuCauSua;
use App\Models\YeuCauTieuHuyChiTietSua;
use Illuminate\Support\Facades\Log;
use App\Services\XuatHangService;
use Illuminate\Support\Facades\Storage;

class YeuCauTieuHuyController extends Controller
{
    public function danhSachYeuCauTieuHuy()
    {
        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $data = YeuCauTieuHuy::join('doanh_nghiep', 'yeu_cau_tieu_huy.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
                ->select(
                    'doanh_nghiep.*',
                    'yeu_cau_tieu_huy.*',
                )
                ->distinct()  // Ensure unique rows
                ->orderBy('ma_yeu_cau', 'desc')
                ->get();
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $maDoanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep;
            $data = YeuCauTieuHuy::join('doanh_nghiep', 'yeu_cau_tieu_huy.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
                ->where('yeu_cau_tieu_huy.ma_doanh_nghiep', $maDoanhNghiep)
                ->select(
                    'doanh_nghiep.*',
                    'yeu_cau_tieu_huy.*',
                )
                ->distinct()  // Ensure unique rows
                ->orderBy('ma_yeu_cau', 'desc')
                ->get();
        }
        return view('quan-ly-kho.yeu-cau-tieu-huy.danh-sach-yeu-cau-tieu-huy', data: compact(var_name: 'data'));
    }

    public function themYeuCauTieuHuy()
    {
        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            $toKhaiDangXuLys = YeuCauTieuHuyChiTiet::join('nhap_hang', 'yeu_cau_tieu_huy_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('yeu_cau_tieu_huy', 'yeu_cau_tieu_huy_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_tieu_huy.ma_yeu_cau')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->where('yeu_cau_tieu_huy.trang_thai', "Đang chờ duyệt")
                ->pluck('yeu_cau_tieu_huy_chi_tiet.so_to_khai_nhap');

            $toKhaiNhaps = NhapHang::with('hangHoa')
                ->where('nhap_hang.trang_thai', 'Đã nhập hàng')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->whereNotIn('nhap_hang.so_to_khai_nhap', $toKhaiDangXuLys)
                ->get();

            return view('quan-ly-kho.yeu-cau-tieu-huy.them-yeu-cau-tieu-huy', data: compact('toKhaiNhaps', 'doanhNghiep'));
        }
        return redirect()->back();
    }

    public function themYeuCauTieuHuySubmit(Request $request)
    {
        try {
            DB::beginTransaction();

            $doanhNghiep = $this->getDoanhNghiepHienTai();
            $yeuCau = $this->taoYeuCauTieuHuy($doanhNghiep);
            $this->xuLyThemChiTietTieuHuy($request, $yeuCau);
            if ($request->file('file')) {
                $this->luuFile($request, $yeuCau);
            }
            DB::commit();
            session()->flash('alert-success', 'Thêm yêu cầu thành công!');
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-tieu-huy', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in ThemTieuHuy: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    private function getDoanhNghiepHienTai()
    {
        return DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
    }

    private function taoYeuCauTieuHuy($doanhNghiep)
    {
        return YeuCauTieuHuy::create([
            'ma_doanh_nghiep' => $doanhNghiep->ma_doanh_nghiep,
            'trang_thai' => 'Đang chờ duyệt',
            'ngay_yeu_cau' => now()
        ]);
    }

    private function xuLyThemChiTietTieuHuy(Request $request, $yeuCau)
    {
        $rowsData = json_decode($request->rows_data, true);
        foreach ($rowsData as $row) {
            $ten_hang = '';
            $nhapHang = NhapHang::with('hangHoa.hangTrongCont')->find($row['so_to_khai_nhap']);
            foreach ($nhapHang->hangHoa as $hangHoa) {
                foreach ($hangHoa->hangTrongCont as $hangTrongCont) {
                    $ten_hang .= $hangHoa->ten_hang . ' - Số lượng: ' . $hangTrongCont->so_luong . "<br>";
                }
            }
            $this->themChiTietTieuHuy($nhapHang, $yeuCau, $ten_hang);
            $this->themTienTrinh($row['so_to_khai_nhap'], "Doanh nghiệp thêm yêu cầu tiêu hủy hàng số " . $yeuCau->ma_yeu_cau, '');
        }
    }

    private function themChiTietTieuHuy($nhapHang, $yeuCau, $tenHang)
    {
        $containers = $nhapHang->hangHoa
            ->flatMap(
                fn($hangHoa) =>
                $hangHoa->hangTrongCont
                    ->filter(fn($cont) => $cont->is_da_chuyen_cont == 0 || $cont->so_luong != 0)
                    ->pluck('so_container')
            )
            ->unique()
            ->implode(';');
        YeuCauTieuHuyChiTiet::insert([
            'so_to_khai_nhap' => $nhapHang->so_to_khai_nhap,
            'so_container' => $containers,
            'so_tau' => $nhapHang->phuong_tien_vt_nhap,
            'ten_hang' => $tenHang,
            'ngay_dang_ky' => $nhapHang->ngay_dang_ky,
            'ma_yeu_cau' => $yeuCau->ma_yeu_cau
        ]);
    }
    public function thongTinYeuCauTieuHuy($ma_yeu_cau)
    {
        $yeuCau = YeuCauTieuHuy::where('ma_yeu_cau', $ma_yeu_cau)
            ->leftJoin('cong_chuc', 'yeu_cau_tieu_huy.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->first();
        $doanhNghiep = DoanhNghiep::find($yeuCau->ma_doanh_nghiep);

        $chiTiets = YeuCauTieuHuy::join('yeu_cau_tieu_huy_chi_tiet', 'yeu_cau_tieu_huy.ma_yeu_cau', '=', 'yeu_cau_tieu_huy_chi_tiet.ma_yeu_cau')
            ->join('nhap_hang', 'yeu_cau_tieu_huy_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
            ->where('yeu_cau_tieu_huy.ma_yeu_cau', $ma_yeu_cau)
            ->pluck('yeu_cau_tieu_huy_chi_tiet.so_to_khai_nhap');

        $nhapHangs = NhapHang::whereIn('so_to_khai_nhap', $chiTiets)->get();

        $congChucs = CongChuc::where('is_chi_xem',0)->get();
        $chiTiets = YeuCauTieuHuyChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->get();
        return view('quan-ly-kho.yeu-cau-tieu-huy.thong-tin-yeu-cau-tieu-huy', compact('yeuCau', 'nhapHangs', 'doanhNghiep', 'congChucs', 'chiTiets')); // Pass data to the view
    }
    public function duyetYeuCauTieuHuy(Request $request)
    {
        try {
            DB::beginTransaction();
            $yeuCau = YeuCauTieuHuy::find($request->ma_yeu_cau);
            if ($yeuCau) {
                $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
                $congChucPhuTrach = CongChuc::find($request->ma_cong_chuc);
                $chiTietYeuCaus = YeuCauTieuHuyChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();

                $yeuCau->ma_cong_chuc = $congChucPhuTrach->ma_cong_chuc;
                $yeuCau->ngay_hoan_thanh = now();
                $yeuCau->trang_thai = 'Đã duyệt';
                $yeuCau->save();

                foreach ($chiTietYeuCaus as $chiTietYeuCau) {
                    $nhapHang = NhapHang::find($chiTietYeuCau->so_to_khai_nhap);

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
                            'cong_viec' => 6,
                            'phuong_tien_nhan_hang' => '',
                            'so_container' => $row->so_container,
                            'so_seal' => $so_seal,
                            'ma_cong_chuc' => $congChucPhuTrach->ma_cong_chuc,
                            'ma_yeu_cau' => $yeuCau->ma_yeu_cau,

                        ]);
                    }

                    $nhapHang->update([
                        'trang_thai' => 'Đã tiêu hủy'
                    ]);


                    // $service = new XuatHangService();
                    // $ly_do = " do tờ khai nhập " . $chiTietYeuCau->so_to_khai_nhap . " đã tiêu hủy";
                    // $service->huyPhieuXuats($chiTietYeuCau->so_to_khai_nhap, $ly_do);
                    // $service->huyYeuCauCuaToKhai($chiTietYeuCau->so_to_khai_nhap, $ly_do);


                    $this->themTheoDoiTruLui($chiTietYeuCau->so_to_khai_nhap, $yeuCau);
                    $this->themTienTrinh($chiTietYeuCau->so_to_khai_nhap, "Đã duyệt yêu cầu tiêu hủy hàng số " . $request->ma_yeu_cau . ", cán bộ công chức phụ trách: " . $congChucPhuTrach->ten_cong_chuc, $congChucPhuTrach->ma_cong_chuc);
                }

                session()->flash('alert-success', 'Duyệt yêu cầu thành công!');
            }

            DB::commit();
            // return redirect()->back();
            return redirect()->route('quan-ly-kho.danh-sach-yeu-cau-tieu-huy');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in duyetYeuCauTieuHuy: ' . $e->getMessage());
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            return redirect()->back();
        }
    }



    public function huyYeuCauTieuHuy(Request $request)
    {
        try {
            DB::beginTransaction();
            $yeuCau = YeuCauTieuHuy::find($request->ma_yeu_cau);
            if ($yeuCau->trang_thai == "Đang chờ duyệt") {
                if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
                    $this->huyYeuCauTieuHuyFunc($request->ma_yeu_cau, $request->ghi_chu, "Cán bộ công chức", '');
                } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                    $this->huyYeuCauTieuHuyFunc($request->ma_yeu_cau, $request->ghi_chu, "Doanh nghiệp", '');
                }
            } elseif ($yeuCau->trang_thai == "Đã duyệt") {
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

    public function huyYeuCauTieuHuyFunc($ma_yeu_cau, $ghi_chu, $user, $ly_do)
    {
        $yeuCau = YeuCauTieuHuy::find($ma_yeu_cau);
        $soToKhaiNhaps = YeuCauTieuHuyChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->pluck('so_to_khai_nhap');

        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $this->themTienTrinh($soToKhaiNhap, "Cán bộ công chức đã hủy yêu cầu tiêu hủy hàng số " . $ma_yeu_cau, $congChuc->ma_cong_chuc);
            }
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đã hủy yêu cầu tiêu hủy hàng số " . $ma_yeu_cau, '');
            }
        } elseif ($user == "Hệ thống") {
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $this->themTienTrinh($soToKhaiNhap, "Hệ thống đã hủy yêu cầu tiêu hủy hàng số " . $ma_yeu_cau . $ly_do, '');
            }
        }
        $yeuCau->trang_thai = 'Đã hủy';
        $yeuCau->ghi_chu = $ghi_chu;
        $yeuCau->save();
    }

    public function huyHuyYeuCau(Request $request)
    {
        $yeuCau = YeuCauTieuHuy::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = 'Đã duyệt';

        $soToKhaiNhaps = YeuCauTieuHuyChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_to_khai_nhap');
        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $yeuCau->ghi_chu = "Công chức từ chối đề nghị hủy: " . $request->ghi_chu;
                $this->themTienTrinh($soToKhaiNhap, "Cán bộ công chức đã hủy yêu cầu tiêu hủy hàng số " . $request->ma_yeu_cau, $congChuc->ma_cong_chuc);
            }
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $yeuCau->ghi_chu = "Doanh nghiệp hủy đề nghị hủy: " . $request->ghi_chu;
                $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đã hủy yêu cầu tiêu hủy hàng số " . $request->ma_yeu_cau, '');
            }
        }
        $yeuCau->save();
        session()->flash('alert-success', 'Hủy đề nghị hủy thành công');
        return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-tieu-huy', ['ma_yeu_cau' => $request->ma_yeu_cau]);
    }
    public function huyYeuCauDaDuyet(Request $request)
    {
        $yeuCau = YeuCauTieuHuy::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = 'Doanh nghiệp đề nghị hủy yêu cầu';
        $yeuCau->ghi_chu = $request->ghi_chu;
        $yeuCau->save();

        $soToKhaiNhaps = YeuCauTieuHuyChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_to_khai_nhap');
        foreach ($soToKhaiNhaps as $soToKhaiNhap) {
            $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đề nghị hủy yêu cầu tiêu hủy hàng số " . $request->ma_yeu_cau, '');
        }
    }

    public function duyetHuyYeuCau(Request $request)
    {
        $yeuCau = YeuCauTieuHuy::find($request->ma_yeu_cau);
        $soToKhaiNhaps = YeuCauTieuHuyChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_to_khai_nhap');

        $this->quayNguocYeuCau($soToKhaiNhaps, $yeuCau);

        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $this->themTienTrinh($soToKhaiNhap, "Cán bộ công chức đã duyệt đề nghị hủy yêu cầu tiêu hủy hàng số " . $request->ma_yeu_cau, $congChuc->ma_cong_chuc);
            }
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            foreach ($soToKhaiNhaps as $soToKhaiNhap) {
                $this->themTienTrinh($soToKhaiNhap, "Doanh nghiệp đã hủy đề nghị hủy yêu cầu tiêu hủy hàng số " . $request->ma_yeu_cau, '');
            }
        }
        $yeuCau->trang_thai = 'Đã hủy';
        $yeuCau->ghi_chu = "Công chức duyệt đề nghị hủy: " . $request->ghi_chu;
        $yeuCau->save();
    }

    public function suaYeuCauTieuHuy($ma_yeu_cau)
    {
        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            $toKhaiDangXuLys = YeuCauTieuHuyChiTiet::join('nhap_hang', 'yeu_cau_tieu_huy_chi_tiet.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->join('yeu_cau_tieu_huy', 'yeu_cau_tieu_huy_chi_tiet.ma_yeu_cau', '=', 'yeu_cau_tieu_huy.ma_yeu_cau')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->where('yeu_cau_tieu_huy.trang_thai', '!=', "Đã hủy")
                ->pluck('yeu_cau_tieu_huy_chi_tiet.so_to_khai_nhap');

            $toKhaiTrongPhieu = YeuCauTieuHuyChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->pluck('so_to_khai_nhap');
            $toKhaiDangXuLys = $toKhaiDangXuLys->diff($toKhaiTrongPhieu);
            $toKhaiNhaps = NhapHang::with('hangHoa')
                ->where('nhap_hang.trang_thai', 'Đã nhập hàng')
                ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->whereNotIn('nhap_hang.so_to_khai_nhap', $toKhaiDangXuLys)
                ->get();
            $toKhaiTrongPhieu = YeuCauTieuHuyChiTiet::where('ma_yeu_cau', $ma_yeu_cau)->pluck('so_to_khai_nhap');
            $chiTiets = NhapHang::with('hangHoa')
                // ->where('nhap_hang.trang_thai', 'Đã nhập hàng')
                // ->where('nhap_hang.ma_doanh_nghiep', $doanhNghiep->ma_doanh_nghiep)
                ->whereIn('nhap_hang.so_to_khai_nhap', $toKhaiTrongPhieu)
                ->get();
            $yeuCau = YeuCauTieuHuy::find($ma_yeu_cau);
            return view('quan-ly-kho.yeu-cau-tieu-huy.sua-yeu-cau-tieu-huy', data: compact('toKhaiNhaps', 'doanhNghiep', 'chiTiets', 'ma_yeu_cau', 'yeuCau'));
        }
        return redirect()->back();
    }

    public function suaYeuCauTieuHuySubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $yeuCau = YeuCauTieuHuy::find($request->ma_yeu_cau);

            if ($yeuCau->trang_thai == 'Đang chờ duyệt') {
                $this->suaYeuCauDangChoDuyet($request, $yeuCau);
            } else {
                $this->suaYeuCauDaDuyet($request, $yeuCau);
            }
            DB::commit();
            session()->flash('alert-success', 'Sửa yêu cầu thành công!');
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-tieu-huy', ['ma_yeu_cau' => $request->ma_yeu_cau]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in ThemTieuHuy: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function suaYeuCauDangChoDuyet($request, $yeuCau)
    {
        $rowsData = json_decode($request->rows_data, true);
        YeuCauTieuHuyChiTiet::where('ma_yeu_cau', operator: $request->ma_yeu_cau)->delete();
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
            YeuCauTieuHuyChiTiet::insert([
                'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                'so_container' => $containers,
                'so_tau' => $nhapHang->phuong_tien_vt_nhap,
                'ten_hang' => $ten_hang,
                'ngay_dang_ky' => $nhapHang->ngay_dang_ky,
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
            'loai_yeu_cau' => 6,
        ]);
        if ($request->file('file')) {
            $this->luuFile($request, $suaYeuCau);
        }

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
            YeuCauTieuHuyChiTietSua::insert([
                'so_to_khai_nhap' => $row['so_to_khai_nhap'],
                'so_container' => $containers,
                'so_tau' => $nhapHang->phuong_tien_vt_nhap,
                'ten_hang' => $ten_hang,
                'ngay_dang_ky' => $nhapHang->ngay_dang_ky,
                'ma_sua_yeu_cau' => $suaYeuCau->ma_sua_yeu_cau
            ]);
            $this->themTienTrinh($row['so_to_khai_nhap'], "Doanh nghiệp đã yêu cầu sửa yêu cầu tiêu hủy số " . $yeuCau->ma_yeu_cau, '');
        }
    }
    public function xemSuaYeuCau(Request $request)
    {
        $yeuCau = YeuCauTieuHuy::where('ma_yeu_cau', $request->ma_yeu_cau)
            ->leftJoin('cong_chuc', 'yeu_cau_tieu_huy.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->first();
        $chiTiets = YeuCauTieuHuyChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();

        $suaYeuCau = YeuCauSua::where('ma_yeu_cau', $request->ma_yeu_cau)
            ->where('loai_yeu_cau', 6)
            ->first();
        $chiTietSuas = YeuCauTieuHuyChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->get();
        $doanhNghiep = DoanhNghiep::find($yeuCau->ma_doanh_nghiep);
        return view('quan-ly-kho.yeu-cau-tieu-huy.xem-sua-yeu-cau-tieu-huy', compact('yeuCau', 'chiTiets', 'suaYeuCau', 'chiTietSuas', 'doanhNghiep'));
    }
    public function duyetSuaYeuCau(Request $request)
    {
        try {
            DB::beginTransaction();
            $suaYeuCau = YeuCauSua::find($request->ma_sua_yeu_cau);
            $yeuCau = YeuCauTieuHuy::find($request->ma_yeu_cau);

            $chiTietSuaYeuCaus = YeuCauTieuHuyChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->get();
            $soToKhaiSauSuas = $chiTietSuaYeuCaus->pluck('so_to_khai_nhap')->toArray();

            $chiTietYeuCaus = YeuCauTieuHuyChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
            $soToKhaiTruocSuas = $chiTietYeuCaus->pluck('so_to_khai_nhap')->toArray();

            $soToKhaiCanQuayNguoc = array_diff($soToKhaiTruocSuas, $soToKhaiSauSuas);
            $soToKhaiCanXuLy =  $soToKhaiSauSuas;

            $this->quayNguocYeuCau($soToKhaiCanQuayNguoc, $yeuCau);
            YeuCauTieuHuyChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->delete();
            $this->xuLySuaYeuCau($chiTietSuaYeuCaus, $soToKhaiCanXuLy, $yeuCau);

            $yeuCau->trang_thai = 'Đã duyệt';
            if ($yeuCau->file_name && $suaYeuCau->file_name) {
                $yeuCau->file_name = $suaYeuCau->file_name;
                $yeuCau->file_path = $suaYeuCau->file_path;
            }
            $yeuCau->save();

            YeuCauTieuHuyChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->delete();
            YeuCauSua::find($request->ma_sua_yeu_cau)->delete();
            DB::commit();
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-tieu-huy', ['ma_yeu_cau' => $yeuCau->ma_yeu_cau]);
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetSuaYeuCauTieuHuy: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function quayNguocYeuCau($soToKhaiCanQuayNguoc, $yeuCau)
    {
        foreach ($soToKhaiCanQuayNguoc as $soToKhai) {
            $chiTiet = YeuCauTieuHuyChiTiet::where('so_to_khai_nhap', $soToKhai)
                ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->first();
            TheoDoiHangHoa::where('so_to_khai_nhap', $soToKhai)
                ->where('ma_yeu_cau', $chiTiet->ma_yeu_cau)
                ->where('cong_viec', 6)
                ->delete();
            NhapHang::where('so_to_khai_nhap', $chiTiet->so_to_khai_nhap)->update([
                'trang_thai' => 'Đã nhập hàng',
            ]);
        }
    }
    public function xuLySuaYeuCau($chiTietSuaYeuCaus, $soToKhaiCanXuLy, $yeuCau)
    {
        $this->xoaTheoDoiTruLui($yeuCau);
        foreach ($chiTietSuaYeuCaus as $chiTietYeuCau) {
            if (in_array($chiTietYeuCau->so_to_khai_nhap, $soToKhaiCanXuLy)) {

                NhapHang::where('so_to_khai_nhap', $chiTietYeuCau->so_to_khai_nhap)->update([
                    'trang_thai' => 'Đã tiêu hủy',
                ]);
                $this->themTheoDoiTruLui($chiTietYeuCau->so_to_khai_nhap, $yeuCau);
                $this->themTienTrinh($chiTietYeuCau->so_to_khai_nhap, "Đã sửa yêu cầu tiêu hủy số " . $yeuCau->ma_yeu_cau  . ", cán bộ công chức phụ trách: " . $yeuCau->congChuc->ten_cong_chuc, $yeuCau->congChuc->ma_cong_chuc);
            }
            YeuCauTieuHuyChiTiet::insert([
                'so_to_khai_nhap' => $chiTietYeuCau->so_to_khai_nhap,
                'so_container' => $chiTietYeuCau->so_container,
                'so_tau' => $chiTietYeuCau->so_tau,
                'ten_hang' => $chiTietYeuCau->ten_hang,
                'ngay_dang_ky' => $chiTietYeuCau->ngay_dang_ky,
                'ma_yeu_cau' => $yeuCau->ma_yeu_cau,
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
            'cong_viec' => 6,
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
    public function huySuaYeuCau(Request $request)
    {
        $yeuCau = YeuCauTieuHuy::find($request->ma_yeu_cau);

        $suaYeuCau = YeuCauSua::find($request->ma_sua_yeu_cau);
        YeuCauTieuHuyChiTietSua::where('ma_sua_yeu_cau', $suaYeuCau->ma_sua_yeu_cau)->delete();
        $suaYeuCau->delete();

        $chiTiets = YeuCauTieuHuyChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();
        if ($yeuCau->trang_thai = 'Doanh nghiệp đề nghị sửa yêu cầu') {
            foreach ($chiTiets as $chiTiet) {
                if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                    $yeuCau->ghi_chu = "Doanh nghiệp hủy đề nghị sửa: " . $request->ghi_chu;
                    $this->themTienTrinh($chiTiet->so_to_khai_nhap, "Doanh nghiệp hủy đề nghị sửa yêu cầu tiêu hủy số " . $yeuCau->ma_yeu_cau, '');
                } else {
                    $yeuCau->ghi_chu = "Công chức từ chối đề nghị sửa: " . $request->ghi_chu;
                    $this->themTienTrinh($chiTiet->so_to_khai_nhap, "Công chức từ chối đề nghị sửa yêu cầu tiêu hủy số " . $yeuCau->ma_yeu_cau, $this->getCongChucHienTai()->ma_cong_chuc);
                }
            }
        } else {
            foreach ($chiTiets as $chiTiet) {
                if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
                    $yeuCau->ghi_chu = "Doanh nghiệp hủy đề nghị hủy: " . $request->ghi_chu;
                    $this->themTienTrinh($chiTiet->so_to_khai_nhap, "Doanh nghiệp hủy đề nghị hủy yêu cầu tiêu hủy số " . $yeuCau->ma_yeu_cau, '');
                } else {
                    $yeuCau->ghi_chu = "Công chức từ chối đề nghị hủy: " . $request->ghi_chu;
                    $this->themTienTrinh($chiTiet->so_to_khai_nhap, "Công chức từ chối đề nghị hủy yêu cầu tiêu hủy số " . $yeuCau->ma_yeu_cau, $this->getCongChucHienTai()->ma_cong_chuc);
                }
            }
        }

        $yeuCau->trang_thai = 'Đã duyệt';
        $yeuCau->save();

        session()->flash('alert-success', 'Hủy yêu cầu sửa thành công!');
        return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-tieu-huy', ['ma_yeu_cau' => $request->ma_yeu_cau]);
    }

    public function duyetHoanThanh(Request $request)
    {
        $yeuCau = YeuCauTieuHuy::find($request->ma_yeu_cau);
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
                ->where('cong_viec', 6)
                ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau);
        })->delete();

        TheoDoiTruLui::where('cong_viec', 6)
            ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
            ->delete();
    }
    private function getCongChucHienTai()
    {
        return CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
    }

    public function luuFile($request, $yeuCau)
    {
        if ($yeuCau->file_name) {
            Storage::delete('public/' . $yeuCau->file->path);
        }

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();

        while (Storage::exists('public/yeu_cau_tieu_huy/' . $fileName)) {
            $fileInfo = pathinfo(path: $fileName);
            $fileName = $fileInfo['filename'] . '_' . time() . '.' . $fileInfo['extension'];
        }

        $filePath = $file->storeAs('yeu_cau_tieu_huy', $fileName, 'public');

        $yeuCau->file_name = $fileName;
        $yeuCau->file_path = $filePath;
        $yeuCau->save();
    }
    public function downloadFile($maYeuCau, $xemSua = false)
    {
        if ($xemSua) {
            $yeuCau = YeuCauSua::findOrFail($maYeuCau);
        } else {
            $yeuCau = YeuCauTieuHuy::findOrFail($maYeuCau);
        }

        if (!$yeuCau->file_name) {
            session()->flash('alert-danger', 'Không tìm thấy file trong hệ thống');
            return redirect()->back();
        }

        $filePath = storage_path('app/public/' . $yeuCau->file_path);
        return response()->download($filePath, $yeuCau->file_name);
    }
}
