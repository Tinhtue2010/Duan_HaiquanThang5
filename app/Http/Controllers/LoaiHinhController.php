<?php

namespace App\Http\Controllers;

use App\Models\BaoCao;
use App\Models\CongChuc;
use App\Models\DoanhNghiep;
use App\Models\HangHoa;
use App\Models\HangTrongCont;
use Illuminate\Http\Request;
use App\Models\LoaiHinh;
use App\Models\NhapHang;
use App\Models\NhapHangDaHuy;
use App\Models\NhapHangSua;
use App\Models\PhanQuyenBaoCao;
use App\Models\PTVTXuatCanhCuaPhieu;
use App\Models\TaiKhoan;
use App\Models\TheoDoiHangHoa;
use App\Models\TheoDoiTruLui;
use App\Models\TheoDoiTruLuiChiTiet;
use App\Models\XuatCanh;
use App\Models\XuatHang;
use App\Models\XuatHangCont;
use App\Models\YCContainerMaHangContMoi;
use App\Models\YeuCauChuyenContainer;
use App\Models\YeuCauChuyenTau;
use App\Models\YeuCauChuyenTauChiTiet;
use App\Models\YeuCauContainerChiTiet;
use App\Models\YeuCauContainerHangHoa;
use App\Models\YeuCauGiaHan;
use App\Models\YeuCauHangVeKho;
use App\Models\YeuCauKiemTra;
use App\Models\YeuCauKiemTraChiTiet;
use App\Models\YeuCauNiemPhong;
use App\Models\YeuCauNiemPhongChiTiet;
use App\Models\YeuCauTauCont;
use App\Models\YeuCauTauContChiTiet;
use App\Models\YeuCauTieuHuy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoaiHinhController extends Controller
{
    public function danhSachLoaiHinh()
    {
        $data = LoaiHinh::all();
        return view('quan-ly-khac.danh-sach-loai-hinh', data: compact(var_name: 'data'));
    }
    public function ttest()
    {
        return view('quan-ly-khac.test');
    }

    public function themLoaiHinh(Request $request)
    {
        if (LoaiHinh::find($request->ma_loai_hinh)) {
            session()->flash('alert-danger', 'Mã loại hình này đã tồn tại.');
            return redirect()->back();
        }
        LoaiHinh::create([
            'ma_loai_hinh' => $request->ma_loai_hinh,
            'ten_loai_hinh' => $request->ten_loai_hinh,
            'loai' => $request->loai,
        ]);
        session()->flash('alert-success', 'Thêm loại hình mới thành công');
        return redirect()->back();
    }

    public function xoaLoaiHinh(Request $request)
    {
        LoaiHinh::find($request->ma_loai_hinh)->delete();
        session()->flash('alert-success', 'Xóa loại hình thành công');
        return redirect()->back();
    }
    public function xoaTheoDoiHang(Request $request)
    {
        // $chiTietYeuCaus = YeuCauNiemPhong::join('yeu_cau_niem_phong_chi_tiet', 'yeu_cau_niem_phong.ma_yeu_cau', '=', 'yeu_cau_niem_phong_chi_tiet.ma_yeu_cau')
        //     ->where(function ($query) {
        //         if (now()->hour < 10) {
        //             $query->whereDate('ngay_yeu_cau', today())
        //                 ->orWhereDate('ngay_yeu_cau', today()->subDay());
        //         } else {
        //             $query->whereDate('ngay_yeu_cau', today());
        //         }
        //     })->get();
        // foreach ($chiTietYeuCaus as $chiTietYeuCau) {
        //     // $this->capNhatSealTruLui($chiTietYeuCau->so_container, $chiTietYeuCau->so_seal_moi);
        //     $this->capNhatSealXuatHang($chiTietYeuCau->so_container, $chiTietYeuCau->so_seal_moi);
        //     // $this->capNhatSealTheoDoi($chiTietYeuCau->so_container, $chiTietYeuCau->so_seal_moi);
        // }

        // $this->xuatHet();
        // $this->fixNgayXuatHet();
        // $this->fixCCXuatHet();
        $this->fixSoContKhaiBao();
        return redirect()->back();
    }
    public function fixSoContKhaiBao(){
        $nhapHangs = NhapHang::all();
        foreach($nhapHangs as $nhapHang){
            HangHoa::where('so_to_khai_nhap', $nhapHang->so_to_khai_nhap)->update(['so_container_khai_bao' => $nhapHang->container_ban_dau]);
        }
    }



    public function quayNguocYeuCau($yeuCau)
    {
        $chiTietYeuCaus = YeuCauContainerChiTiet::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)->get();
        foreach ($chiTietYeuCaus as $chiTietYeuCau) {
            $nhapHangs = HangTrongCont::join('hang_hoa', 'hang_trong_cont.ma_hang', '=', 'hang_hoa.ma_hang')
                ->join('nhap_hang', 'hang_hoa.so_to_khai_nhap', '=', 'nhap_hang.so_to_khai_nhap')
                ->where('nhap_hang.so_to_khai_nhap', $chiTietYeuCau->so_to_khai_nhap)
                ->get();

            foreach ($nhapHangs as $nhapHang) {
                $nhapHang->so_container = $chiTietYeuCau->so_container_goc;
                $nhapHang->save();
            }
        }
    }

    public function xoaTheoDoi()
    {
        // $theoDoiHangHoas = TheoDoiHangHoa::whereDate('thoi_gian', today()->subDay())
        //     ->where('cong_viec', 1)
        //     ->get();

        // foreach ($theoDoiHangHoas as $theoDoiHangHoa) {
        //     $xuatHang = XuatHang::join('xuat_hang_cont', 'xuat_hang_cont.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
        //         ->join('hang_trong_cont', 'xuat_hang_cont.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
        //         ->whereDate('ngay_dang_ky', today()->subDay())
        //         ->where('xuat_hang.so_to_khai_xuat', $theoDoiHangHoa->ma_yeu_cau)
        //         ->where('hang_trong_cont.ma_hang', $theoDoiHangHoa->ma_hang)
        //         ->exists();
        //     if (!$xuatHang) {
        //         $theoDoiHangHoa->delete();
        //     }
        // }



        // $xuatHangs = XuatHang::where('trang_thai', 0)
        //     ->whereDate('ngay_dang_ky', today()->subDay())
        //     ->get();
        // $total = 0;
        // foreach ($xuatHangs as $xuatHang) {
        //     $theoDois = TheoDoiHangHoa::where('cong_viec', 1)
        //         ->whereDate('thoi_gian', today()->subDay())
        //         ->where('ma_yeu_cau', $xuatHang->so_to_khai_xuat)
        //         ->exists();
        //     if ($theoDois) {
        //         $total++;
        //     }
        // }
        // dd($total);
    }


    public function fixTheoDoi()
    {
        $xuatHangs = XuatHang::join('xuat_hang_cont', 'xuat_hang_cont.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
            ->join('hang_trong_cont', 'xuat_hang_cont.ma_hang_cont', '=', 'hang_trong_cont.ma_hang_cont')
            ->whereDate('ngay_dang_ky', today()->subDay())
            ->where('xuat_hang.trang_thai', '!=', 0)
            ->select(
                'xuat_hang_cont.so_to_khai_nhap',
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang_cont.so_container',
                'xuat_hang_cont.so_luong_xuat',
                'xuat_hang_cont.so_seal_cuoi_ngay',
                'hang_trong_cont.ma_hang',
                'hang_trong_cont.so_luong',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang.ma_cong_chuc',
            )
            ->groupBy('xuat_hang_cont.ma_xuat_hang_cont')
            ->get();
        $total = 0;
        foreach ($xuatHangs as $xuatHang) {
            $theoDoiTruLui = TheoDoiTruLui::where('so_to_khai_nhap', $xuatHang->so_to_khai_nhap)
                ->where('cong_viec', 1)
                ->where('ma_yeu_cau', $xuatHang->so_to_khai_xuat)
                ->exists();
            if (!$theoDoiTruLui) {
                TheoDoiTruLui::insert([
                    'so_to_khai_nhap' => $xuatHang->so_to_khai_nhap,
                    'so_ptvt_nuoc_ngoai' => $xuatHang->ten_phuong_tien_vt,
                    'phuong_tien_vt_nhap' => '',
                    'ngay_them' => now()->subDay(),
                    'cong_viec' => 1,
                    'ma_yeu_cau' => $xuatHang->so_to_khai_xuat,
                ]);
            }
            // $theoDoiHangHoa = TheoDoiHangHoa::where('so_to_khai_nhap', $xuatHang->so_to_khai_nhap)
            //     ->where('ma_yeu_cau', $xuatHang->so_to_khai_xuat)
            //     ->where('cong_viec', 1)
            //     ->where('ma_hang', $xuatHang->ma_hang)
            //     ->exists();
            // if (!$theoDoiHangHoa) {
            //     TheoDoiHangHoa::insert(
            //         [
            //             'so_to_khai_nhap' => $xuatHang->so_to_khai_nhap,
            //             'ma_hang' => $xuatHang->ma_hang,
            //             'thoi_gian' => now(),
            //             'so_luong_xuat' => $xuatHang->so_luong_xuat,
            //             'so_luong_ton' => $xuatHang->so_luong - $xuatHang->so_luong_xuat,
            //             'phuong_tien_cho_hang' => '',
            //             'cong_viec' => 1,
            //             'phuong_tien_nhan_hang' => $xuatHang->ten_phuong_tien_vt,
            //             'so_container' => $xuatHang->so_container,
            //             'so_seal' => $xuatHang->so_seal_cuoi_ngay ?? '',
            //             'ma_cong_chuc' => $xuatHang->ma_cong_chuc ?? '',
            //             'ma_yeu_cau' => $xuatHang->so_to_khai_xuat,
            //         ]
            //     );
            // }
        }
    }

    public function capNhatSealTheoDoi($so_container, $so_seal)
    {
        $so_container_no_space = str_replace(' ', '', $so_container); // Remove spaces
        $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

        TheoDoiHangHoa::whereIn('so_container', [$so_container_no_space, $so_container_with_space])
            ->whereDate('thoi_gian', today()->subDay())
            ->update(['so_seal' => $so_seal]);
    }
    public function capNhatSealXuatHang($so_container, $so_seal)
    {
        $so_container_no_space = str_replace(' ', '', $so_container); // Remove spaces
        $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

        XuatHang::whereDate('ngay_dang_ky', today()->subDay())
            ->join('xuat_hang_cont', 'xuat_hang_cont.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->whereIn('xuat_hang_cont.so_container',  [$so_container_no_space, $so_container_with_space])
            ->update(['xuat_hang_cont.so_seal_cuoi_ngay' => $so_seal]);
    }
    public function capNhatSealTruLui($so_container, $so_seal): void
    {
        $so_container_no_space = str_replace(' ', '', $so_container); // Remove spaces
        $so_container_with_space = substr($so_container_no_space, 0, 4) . ' ' . substr($so_container_no_space, 4);

        TheoDoiTruLui::join('theo_doi_tru_lui_chi_tiet', 'theo_doi_tru_lui_chi_tiet.ma_theo_doi', 'theo_doi_tru_lui.ma_theo_doi')
            ->whereIn('theo_doi_tru_lui_chi_tiet.so_container', [$so_container_no_space, $so_container_with_space])
            ->whereDate('ngay_them', today()->subDay())
            ->update(['theo_doi_tru_lui_chi_tiet.so_seal' => $so_seal]);
    }

    public function xoaTheoDoiTruLui2($yeuCau)
    {
        TheoDoiTruLuiChiTiet::whereIn('ma_theo_doi', function ($query) use ($yeuCau) {
            $query->select('ma_theo_doi')
                ->from('theo_doi_tru_lui')
                ->where('cong_viec', 2)
                ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau);
        })->delete();

        TheoDoiTruLui::where('cong_viec', 2)
            ->where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
            ->delete();
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
            'phuong_tien_vt_nhap' => $nhapHang->phuong_tien_vt_nhap ?? '',
            'ngay_them' => now(),
            'cong_viec' => 2,
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





    public function fixNgayXuatHet()
    {
        try {
            DB::beginTransaction();

            $xuatHet = NhapHang::where('trang_thai', '4')
                ->whereNull('ma_cong_chuc_ban_giao')
                ->get();

            foreach ($xuatHet as $nhapHang) {
                $ngay_xuat_canh = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                    ->where('xuat_hang_cont.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                    ->where('xuat_hang.trang_thai', '2')
                    ->orderBy('xuat_hang.updated_at', 'desc')
                    ->select('xuat_hang.ngay_xuat_canh')
                    ->first()?->ngay_xuat_canh;
                $nhapHang->ngay_xuat_het = $ngay_xuat_canh;
                $nhapHang->save();
            }
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('alert-danger', 'Có lỗi xảy ra');
            Log::error('Error in fix: ' . $e->getMessage());
            return redirect()->back();
        }
    }


    public function thayDoiMaDoanhNghiep(Request $request)
    {
        $maDNCu = "4900216802";
        $maDNMoi = "5901982917MT";

        NhapHang::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        NhapHangDaHuy::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        NhapHangSua::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        XuatCanh::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        XuatCanh::where('ma_doanh_nghiep_chon', $maDNCu)->update([
            'ma_doanh_nghiep_chon' => $maDNMoi,
        ]);
        YeuCauGiaHan::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        YeuCauTauCont::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        YeuCauKiemTra::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        YeuCauChuyenContainer::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        YeuCauChuyenTau::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        YeuCauHangVeKho::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        YeuCauTieuHuy::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        YeuCauNiemPhong::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        DoanhNghiep::where('ma_doanh_nghiep', $maDNCu)->update([
            'ma_doanh_nghiep' => $maDNMoi,
        ]);
        TaiKhoan::where('ten_dang_nhap', $maDNCu)->update([
            'ten_dang_nhap' => $maDNMoi,
        ]);
    }
    public function xoaTheoDoiTruLui(Request $request)
    {
        $this->checkLechSoLuong($request);
    }

    public function thayPTVT(Request $request)
    {
        $xuatHangs = XuatHang::all();
        foreach ($xuatHangs as $xuatHang) {
            $ptvts = PTVTXuatCanhCuaPhieu::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)
                ->with('PTVTXuatCanh')
                ->get()
                ->pluck('PTVTXuatCanh.ten_phuong_tien_vt')
                ->filter()
                ->implode('; ');
            $xuatHang->ten_phuong_tien_vt = $ptvts ?? '';
            $xuatHang->save();
        }
    }
    public function xuatHet()
    {
        $allNhapHangs = NhapHang::where('trang_thai', '2')->get();
        $arr = [];
        foreach ($allNhapHangs as $nhapHang) {
            $soLuongTon = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('nhap_hang.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->sum('hang_trong_cont.so_luong');
            if ($soLuongTon == 0) {
                array_push($arr, $soLuongTon, $nhapHang->so_to_khai_nhap);
                $nhapHang->trang_thai = "4";
                $nhapHang->save();
            }
        }
    }

    public function fixCCXuatHet()
    {
        $allNhapHangs = NhapHang::where('trang_thai', '4')
            ->whereNull('ma_cong_chuc_ban_giao')
            ->get();
        foreach ($allNhapHangs as $nhapHang) {
            $maCongChuc = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                ->where('xuat_hang_cont.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->where('xuat_hang.trang_thai', '2')
                ->orderBy('xuat_hang.updated_at', 'desc')
                ->select('xuat_hang.ma_cong_chuc')
                ->first()?->ma_cong_chuc;
            $nhapHang->ma_cong_chuc_ban_giao = $maCongChuc;
            $nhapHang->save();
        }
    }

    public function containerTauGoc()
    {
        $yeuCaus = YeuCauChuyenContainer::where('trang_thai', '2')->get();
        foreach ($yeuCaus as $yeuCau) {
            $phuong_tien_cho_hang = TheoDoiHangHoa::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->where('cong_viec', 3)
                ->first()->phuong_tien_cho_hang;
            YeuCauContainerChiTiet::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)->update([
                'tau_goc' => $phuong_tien_cho_hang
            ]);
        }
    }
    public function ycKiemTraSoLuong()
    {
        $yeuCaus = YeuCauKiemTra::where('trang_thai', '2')->get();
        foreach ($yeuCaus as $yeuCau) {
            $so_luong_ton = TheoDoiHangHoa::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->where('cong_viec', 7)
                ->sum('so_luong_ton');
            YeuCauKiemTraChiTiet::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)->update([
                'so_luong' => $so_luong_ton
            ]);
        }
    }
    public function ycChuyenTauSoLuong()
    {
        $yeuCaus = YeuCauChuyenTau::all();
        foreach ($yeuCaus as $yeuCau) {
            $so_luong_ton = TheoDoiHangHoa::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)
                ->where('cong_viec', 4)
                ->sum('so_luong_ton');
            YeuCauChuyenTauChiTiet::where('ma_yeu_cau', $yeuCau->ma_yeu_cau)->update([
                'so_luong' => $so_luong_ton
            ]);
        }
    }

    public function doiMatKhau(Request $request)
    {
        $taiKhoan = TaiKhoan::where('ten_dang_nhap', $request->ten_dang_nhap)->first();

        if (!$taiKhoan) {
            session()->flash('alert-danger', 'Không tìm thấy');
            return redirect()->back();
        }

        if ($request->mat_khau != '') {
            $taiKhoan->update([
                'mat_khau' => Hash::make($request->mat_khau)
            ]);
        }

        session()->flash('alert-success', 'OK');
        return redirect()->back();
    }

    public function checkLechSoLuong(Request $request)
    {
        $allNhapHangs = NhapHang::where('trang_thai', '!=', '1')->get();

        $arr = [];
        foreach ($allNhapHangs as $nhapHang) {
            $slKhaiBao = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', 'hang_hoa.so_to_khai_nhap')
                ->where('nhap_hang.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->sum('hang_hoa.so_luong_khai_bao');

            $slDaXuat = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', 'xuat_hang_cont.so_to_khai_xuat')
                ->where('xuat_hang.trang_thai', '!=', '0')
                ->where('xuat_hang_cont.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->sum('xuat_hang_cont.so_luong_xuat');

            $soLuongTon = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('nhap_hang.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->sum('hang_trong_cont.so_luong');

            if ($slKhaiBao - $slDaXuat != $soLuongTon) {
                array_push($arr, $slKhaiBao, $slDaXuat, $soLuongTon, $nhapHang->so_to_khai_nhap);
            }
        }
        $excludeValues = []; // Add more if needed

        $arr = array_filter($arr, function ($value) use ($excludeValues) {
            return !in_array($value, $excludeValues);
        });

        $arr = array_values($arr);

        dd($arr);
    }
    public function checkXuatHetHang(Request $request)
    {
        $allNhapHangs = NhapHang::where('trang_thai', '4')->get();
        $arr = [];
        foreach ($allNhapHangs as $nhapHang) {
            $soLuongTon = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('nhap_hang.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->sum('hang_trong_cont.so_luong');
            if ($soLuongTon != 0) {
                array_push($arr, $soLuongTon, $nhapHang->so_to_khai_nhap);
            }
        }
        dd($arr);
    }
    public function fixPhanQuyenBaoCao(Request $request)
    {
        $congChucs = CongChuc::all();
        foreach ($congChucs as $congChuc) {
            for ($i = 1; $i <= 20; $i++) {
                $check = PhanQuyenBaoCao::where('ma_cong_chuc', $congChuc->ma_cong_chuc)
                    ->where('ma_bao_cao', $i)
                    ->exists();
                if (!$check) {
                    PhanQuyenBaoCao::insert([
                        'ma_cong_chuc' => $congChuc->ma_cong_chuc,
                        'ma_bao_cao' => $i,
                    ]);
                }
            }
        }
    }

}
