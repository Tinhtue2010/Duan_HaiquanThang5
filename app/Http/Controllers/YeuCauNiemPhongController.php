<?php

namespace App\Http\Controllers;

use App\Models\CongChuc;
use App\Models\Container;
use App\Models\DoanhNghiep;
use App\Models\NiemPhong;
use App\Models\Seal;
use App\Models\TheoDoiHangHoa;
use App\Models\TheoDoiTruLui;
use App\Models\TheoDoiTruLuiChiTiet;
use App\Models\YeuCauNiemPhong;
use App\Models\YeuCauNiemPhongChiTiet;
use App\Models\HangTrongCont;
use App\Models\NhapHang;
use App\Models\XuatHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class YeuCauNiemPhongController extends Controller
{
    public function danhSachYeuCauNiemPhong()
    {
        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $data = YeuCauNiemPhong::join('doanh_nghiep', 'yeu_cau_niem_phong.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
                ->select(
                    'doanh_nghiep.*',
                    'yeu_cau_niem_phong.*',
                )
                ->distinct()  // Ensure unique rows
                ->orderBy('ma_yeu_cau', 'desc')
                ->get();
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $maDoanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first()->ma_doanh_nghiep;
            $data = YeuCauNiemPhong::join('doanh_nghiep', 'yeu_cau_niem_phong.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
                ->where('yeu_cau_niem_phong.ma_doanh_nghiep', $maDoanhNghiep)
                ->select(
                    'doanh_nghiep.*',
                    'yeu_cau_niem_phong.*',
                )
                ->distinct()  // Ensure unique rows
                ->orderBy('ma_yeu_cau', 'desc')
                ->get();
        }
        return view('quan-ly-kho.yeu-cau-niem-phong.danh-sach-yeu-cau-niem-phong', data: compact(var_name: 'data'));
    }

    public function themYeuCauNiemPhong()
    {
        $soContainers = Container::all();
        if (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            return view('quan-ly-kho.yeu-cau-niem-phong.them-yeu-cau-niem-phong', data: compact('doanhNghiep', 'soContainers'));
        }
        return redirect()->back();
    }

    public function themYeuCauNiemPhongSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            $doanhNghiep = DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();

            $yeuCau = YeuCauNiemPhong::create([
                'ma_doanh_nghiep' => $doanhNghiep->ma_doanh_nghiep,
                'trang_thai' => 'Đang chờ duyệt',
                'ngay_yeu_cau' => now()
            ]);

            // Decode the JSON data from the form
            $rowsData = json_decode($request->rows_data, true);
            foreach ($rowsData as $row) {
                $niemPhong = NiemPhong::where('so_container', $row['so_container'])->first();
                YeuCauNiemPhongChiTiet::insert([
                    'so_container' => $row['so_container'],
                    'so_seal_cu' => $niemPhong->so_seal ?? '',
                    'so_seal_moi' => '',
                    'ma_yeu_cau' => $yeuCau->ma_yeu_cau
                ]);
            }

            DB::commit();
            session()->flash('alert-success', 'Thêm yêu cầu niêm phong thành công');
            return redirect('/danh-sach-yeu-cau-niem-phong');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra, có thể là số container không tồn tại trong hệ thống');
            return redirect()->back();
        }
    }

    public function thongTinYeuCauNiemPhong($ma_yeu_cau)
    {
        $yeuCau = YeuCauNiemPhong::where('ma_yeu_cau', $ma_yeu_cau)
            ->leftJoin('cong_chuc', 'yeu_cau_niem_phong.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->first();
        $doanhNghiep = DoanhNghiep::find($yeuCau->ma_doanh_nghiep);
        $chiTiets = YeuCauNiemPhongChiTiet::where('yeu_cau_niem_phong_chi_tiet.ma_yeu_cau', $ma_yeu_cau)
            ->get();
        $congChucHienTai = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
        $seals = Seal::where('seal.trang_thai', 0)->get();
        $congChucs = CongChuc::where('is_chi_xem', 0)->get();
        return view('quan-ly-kho.yeu-cau-niem-phong.thong-tin-yeu-cau-niem-phong', compact('yeuCau', 'chiTiets', 'doanhNghiep', 'congChucs', 'seals')); // Pass data to the view
    }

    public function suaYeuCauNiemPhong($ma_yeu_cau)
    {
        $yeuCau = YeuCauNiemPhong::where('ma_yeu_cau', $ma_yeu_cau)
            ->leftJoin('cong_chuc', 'yeu_cau_niem_phong.ma_cong_chuc', '=', 'cong_chuc.ma_cong_chuc')
            ->first();
        $soContainers = Container::all();
        $doanhNghiep = DoanhNghiep::find($yeuCau->ma_doanh_nghiep);
        $chiTiets = YeuCauNiemPhongChiTiet::where('yeu_cau_niem_phong_chi_tiet.ma_yeu_cau', $ma_yeu_cau)
            ->get();
        return view('quan-ly-kho.yeu-cau-niem-phong.sua-yeu-cau-niem-phong', compact('yeuCau', 'chiTiets', 'soContainers')); // Pass data to the view
    }

    public function suaYeuCauNiemPhongSubmit(Request $request)
    {
        try {
            DB::beginTransaction();
            YeuCauNiemPhongChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->delete();
            // Decode the JSON data from the form
            $rowsData = json_decode($request->rows_data, true);
            foreach ($rowsData as $row) {
                $niemPhong = NiemPhong::where('so_container', $row['so_container'])->first();
                YeuCauNiemPhongChiTiet::insert([
                    'so_container' => $row['so_container'],
                    'so_seal_cu' => $niemPhong->so_seal ?? '',
                    'so_seal_moi' => '',
                    'ma_yeu_cau' => $request->ma_yeu_cau,
                ]);
            }

            DB::commit();
            session()->flash('alert-success', 'Sửa yêu cầu niêm phong thành công');
            return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-niem-phong', ['ma_yeu_cau' => $request->ma_yeu_cau]);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('alert-danger', 'Có lỗi xảy ra, có thể là số container không tồn tại trong hệ thống');
            Log::error('Error in suaYeuCauNiemPhong: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function duyetYeuCauNiemPhong(Request $request)
    {
        $yeuCau = YeuCauNiemPhong::find($request->ma_yeu_cau);

        $rowsData = json_decode($request->rows_data, true);
        foreach ($rowsData as $row) {
            if ($row['loai_seal'] == "5" && $row['so_seal'] == null) {
                session()->flash('alert-danger', 'Chưa chọn số seal cho seal định vị điện tử');
                return redirect()->back();
            }
        }
        $counts = collect($rowsData)->countBy('loai_seal');


        foreach ($counts as $loai_seal => $count) {
            if ($loai_seal == 1) {
                $tenSeal = 'Seal dây cáp đồng';
            } elseif ($loai_seal == 2) {
                $tenSeal = 'Seal dây cáp thép';
            } elseif ($loai_seal == 3) {
                $tenSeal = 'Seal dây cáp container';
            } elseif ($loai_seal == 4) {
                $tenSeal = 'Seal dây nhựa dẹt';
            } elseif ($loai_seal == 5) {
                $tenSeal = 'Seal định vị điện tử';
            }
            $availableSeals = $this->getSealNhoNhat($loai_seal, $request->ma_cong_chuc, $count);
            if (!$availableSeals) {
                session()->flash('alert-danger', 'Không đủ số ' . $tenSeal . ' để cấp cho yêu cầu này');
                return redirect()->back();
            }
        }

        try {
            DB::beginTransaction();
            if ($yeuCau) {
                $chiTietYeuCaus = YeuCauNiemPhongChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->get();

                foreach ($chiTietYeuCaus as $chiTietYeuCau) {
                    foreach ($rowsData as $row) {
                        if ($row['so_container'] === $chiTietYeuCau->so_container) {
                            if ($row['loai_seal'] == "5") {
                                $so_seal_moi = $row['so_seal'];
                            } else {
                                $availableSeals = $this->getSealNhoNhat($row['loai_seal'], $request->ma_cong_chuc);
                                $so_seal_moi = $availableSeals->shift();
                            }
                            break;
                        }
                    }
                    $chiTietYeuCau->so_seal_moi = $so_seal_moi;
                    $chiTietYeuCau->save();

                    $this->themContainerMoi($chiTietYeuCau->so_container);
                    $suDungSeal = $this->suDungSeal($so_seal_moi, $request->so_container, $yeuCau->ma_cong_chuc);
                    if (!$suDungSeal) {
                        session()->flash('alert-danger', 'Seal này đã được sử dụng');
                        return redirect()->back();
                    }
                    $this->updateNiemPhong($so_seal_moi, $chiTietYeuCau->so_container, $request->ma_cong_chuc);
                    $this->capNhatSealXuatHang($chiTietYeuCau->so_container, $so_seal_moi);
                    $this->capNhatSealTruLui($chiTietYeuCau->so_container, $so_seal_moi);
                    $this->capNhatSealTheoDoi($chiTietYeuCau->so_container, $so_seal_moi);
                }

                $yeuCau->ma_cong_chuc = $request->ma_cong_chuc;
                $yeuCau->ngay_hoan_thanh = now();
                $yeuCau->trang_thai = 'Đã duyệt';
                $yeuCau->save();
                session()->flash('alert-success', 'Duyệt yêu cầu thành công!');
            }

            DB::commit();
            // return redirect()->back();
            return redirect()->route('quan-ly-kho.danh-sach-yeu-cau-niem-phong');
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in duyetYeuCauNiemPhong: ' . $e->getMessage());
            return redirect()->back();
        }
    }
    public function capNhatSealXuatHang($so_container, $so_seal)
    {
        XuatHang::where('xuat_hang.ngay_dang_ky', today())
            ->join('xuat_hang_cont', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->where('xuat_hang_cont.so_container', '=', $so_container)
            ->where('xuat_hang.ngay_dang_ky', today())
            ->update(['xuat_hang.so_seal_cuoi_ngay' => $so_seal]);
    }
    public function capNhatSealTruLui($so_container, $so_seal)
    {
        TheoDoiTruLui::join('theo_doi_tru_lui_chi_tiet', 'theo_doi_tru_lui_chi_tiet.ma_theo_doi', 'theo_doi_tru_lui.ma_theo_doi')
            ->where('theo_doi_tru_lui_chi_tiet.so_container', '=', $so_container)
            ->where('ngay_them', today())
            ->update(['theo_doi_tru_lui_chi_tiet.so_seal' => $so_seal]);
    }
    public function capNhatSealTheoDoi($so_container, $so_seal)
    {
        TheoDoiHangHoa::where('so_container', '=', $so_container)
            ->whereDate('thoi_gian', today())
            ->update(['so_seal' => $so_seal]);
    }

    public function huyYeuCauNiemPhong(Request $request)
    {
        try {
            DB::beginTransaction();
            $yeuCau = YeuCauNiemPhong::find($request->ma_yeu_cau);
            if ($yeuCau->trang_thai == "Đang chờ duyệt") {
                $yeuCau->trang_thai = 'Đã hủy';
                $yeuCau->ghi_chu = $request->ghi_chu;
                $yeuCau->save();
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
            Log::error('Error in huyYeuCauNiemPhong: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function huyYeuCauDaDuyet(Request $request)
    {
        $yeuCau = YeuCauNiemPhong::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = 'Doanh nghiệp đề nghị hủy yêu cầu';
        $yeuCau->ghi_chu = $request->ghi_chu;
        $yeuCau->save();
    }
    public function huyHuyYeuCau(Request $request)
    {
        $yeuCau = YeuCauNiemPhong::find($request->ma_yeu_cau);
        $yeuCau->trang_thai = 'Đã duyệt';

        if (Auth::user()->loai_tai_khoan == "Cán bộ công chức") {
            $yeuCau->ghi_chu = "Công chức từ chối đề nghị hủy: " . $request->ghi_chu;
        } elseif (Auth::user()->loai_tai_khoan == "Doanh nghiệp") {
            $yeuCau->ghi_chu = "Doanh nghiệp hủy đề nghị hủy: " . $request->ghi_chu;
        }

        $yeuCau->save();
        session()->flash('alert-success', 'Hủy đề nghị hủy thành công');
        return redirect()->route('quan-ly-kho.thong-tin-yeu-cau-niem-phong', ['ma_yeu_cau' => $request->ma_yeu_cau]);
    }
    public function quayNguocYeuCau($soContainerCanQuayNguoc, $yeuCau)
    {
        foreach ($soContainerCanQuayNguoc as $soContainer) {
            $chiTiet = YeuCauNiemPhongChiTiet::where('so_container', $soContainer)
                ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->first();
            $this->capNhatSealXuatHang($soContainer, $chiTiet->so_seal_cu);
            $this->capNhatSealTruLui($soContainer, $chiTiet->so_seal_cu);
            $this->capNhatSealTheoDoi($soContainer, $chiTiet->so_seal_cu);
            $this->updateNiemPhong($chiTiet->so_seal_cu, $soContainer, $yeuCau->ma_cong_chuc);
        }
    }

    public function duyetHuyYeuCau(Request $request)
    {
        $yeuCau = YeuCauNiemPhong::find($request->ma_yeu_cau);
        $soContainers = YeuCauNiemPhongChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)->pluck('so_container');
        $this->quayNguocYeuCau($soContainers, $yeuCau);

        $yeuCau->trang_thai = 'Đã hủy';
        $yeuCau->ghi_chu = "Công chức duyệt đề nghị hủy: " . $request->ghi_chu;
        $yeuCau->save();
    }

    public function suaSealNiemPhong(Request $request)
    {
        try {
            DB::beginTransaction();

            $yeuCau = YeuCauNiemPhong::find($request->ma_yeu_cau);
            $sealMoi = YeuCauNiemPhongChiTiet::where('ma_yeu_cau', $request->ma_yeu_cau)
                ->where('so_container', $request->so_container)
                ->first();

            if ($request->loai_seal == 5 && $request->so_seal == null) {
                session()->flash('alert-danger', 'Chưa lựa chọn số chì cho seal loại seal định vị điện tử');
                return redirect()->back();
            }
            if ($request->loai_seal == 5) {
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

            $so_seal = NiemPhong::where('so_container', $request->so_container)->first()->so_seal;

            $seal = Seal::find($so_seal);
            if ($seal->loai_seal != 5) {
                $seal->update(['trang_thai' => 2]);
            }

            $suDungSeal = $this->suDungSeal($so_seal_moi, $request->so_container, $yeuCau->ma_cong_chuc);
            if (!$suDungSeal) {
                session()->flash('alert-danger', 'Seal này đã được sử dụng');
                return redirect()->back();
            }
            $this->updateNiemPhong($so_seal_moi, $request->so_container, $yeuCau->ma_cong_chuc);
            $this->capNhatSealXuatHang($request->so_container, $so_seal_moi);
            $this->capNhatSealTruLui($request->so_container, $so_seal_moi);
            $this->capNhatSealTheoDoi($request->so_container, $so_seal_moi);

            session()->flash('alert-success', 'Sửa seal niêm phong thành công');
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in suaSealNiemPhong: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function themContainerMoi($so_container)
    {
        if (!Container::find($so_container)) {
            Container::insert([
                'so_container' => $so_container,
            ]);
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

            if ($seal->loai_seal == 5) {
                $seal->update([
                    'ngay_su_dung' => now(),
                    'so_container' => $so_container,
                ]);
            } else {
                $seal->update([
                    'trang_thai' => 1,
                    'ngay_su_dung' => now(),
                    'so_container' => $so_container,
                ]);
            }
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
}
