<?php

namespace App\Services;

use App\Models\XuatCanhChiTiet;
use App\Models\YeuCauTauContChiTiet;
use App\Models\YeuCauContainerChiTiet;
use App\Models\YeuCauChuyenTauChiTiet;
use App\Models\YeuCauKiemTraChiTiet;
use App\Models\YeuCauTieuHuyChiTiet;
use App\Models\YeuCauGiaHanChiTiet;
use App\Models\YeuCauHangVeKhoChiTiet;
use App\Models\XuatHangChiTietSua;
use App\Models\XuatHangChiTietTruocSua;
use App\Models\CongChuc;
use App\Models\HangTrongCont;
use App\Models\NhapHang;
use App\Models\TienTrinh;
use App\Models\XuatHang;
use App\Models\HangHoa;
use App\Models\XuatHangCont;
use App\Models\XuatHangSua;
use App\Models\TheoDoiHangHoa;
use App\Models\TheoDoiTruLui;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\YeuCauTauContController;
use App\Http\Controllers\YeuCauContainerController;
use App\Http\Controllers\YeuCauChuyenTauController;
use App\Http\Controllers\YeuCauKiemTraController;
use App\Http\Controllers\YeuCauTieuHuyController;
use App\Http\Controllers\YeuCauHangVeKhoController;
use App\Http\Controllers\YeuCauGiaHanController;
use App\Models\DoanhNghiep;
use App\Models\PTVTXuatCanhCuaPhieu;
use App\Models\PTVTXuatCanhCuaPhieuSua;
use App\Models\PTVTXuatCanhCuaPhieuTruocSua;
use App\Models\XuatCanh;

class XuatCanhService
{
    public function xuLyDuyetPhieuXuat($xuatHang, $request)
    {
        if ($xuatHang->trang_thai == "Đã chọn phương tiện xuất cảnh") {
            $xuatHang->trang_thai = 'Đã duyệt xuất hàng';
        } elseif ($xuatHang->trang_thai == "Doanh nghiệp yêu cầu hủy phiếu đã chọn PTXC") {
            $xuatHang->trang_thai = 'Doanh nghiệp yêu cầu hủy phiếu đã duyệt xuất hàng';
        } elseif ($xuatHang->trang_thai == "Doanh nghiệp yêu cầu sửa phiếu đã chọn PTXC") {
            $xuatHang->trang_thai = 'Doanh nghiệp yêu cầu sửa phiếu đã duyệt xuất hàng';
        }
        $xuatHang->save();
        session()->flash('alert-success', 'Trạng thái đã được cập nhật thành công!');
        return $xuatHang;
    }
    public function xuLyDuyetThucXuat($xuatHang, $request)
    {
        if($xuatHang->trang_thai != "Đã thực xuất hàng") {
            if ($xuatHang->trang_thai == 'Doanh nghiệp yêu cầu sửa phiếu đã duyệt xuất hàng' || $xuatHang->trang_thai == 'Doanh nghiệp yêu cầu sửa phiếu đã chọn PTXC') {
                $suaXuatHang = XuatHangSua::where('so_to_khai_xuat', $xuatHang->so_to_khai_xuat)->first();
                XuatHangChiTietSua::where('ma_yeu_cau', $suaXuatHang->ma_yeu_cau)->delete();
                PTVTXuatCanhCuaPhieuSua::where('ma_yeu_cau', $suaXuatHang->ma_yeu_cau)->delete();
                PTVTXuatCanhCuaPhieuTruocSua::where('ma_yeu_cau', $suaXuatHang->ma_yeu_cau)->delete();
                $suaXuatHang->delete();
                $this->themTienTrinh($xuatHang->so_to_khai_nhap, "Phiếu xuất " . $xuatHang->so_to_khai_xuat . " đã hủy sửa do cán bộ công chức đã duyệt thực xuất tờ khai xuất cảnh", $this->getCongChucHienTai()->ma_cong_chuc);
            }
            $xuatHang->trang_thai = "Đã thực xuất hàng";
            $xuatHang->save();
            return $xuatHang;
        }
    }

    public function getXuatHangDaDuyet($so_ptvt_xuat_canh)
    {
        return XuatHang::join('doanh_nghiep', 'xuat_hang.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
            ->join('ptvt_xuat_canh_cua_phieu', 'ptvt_xuat_canh_cua_phieu.so_to_khai_xuat', '=', 'xuat_hang.so_to_khai_xuat')
            ->where('ptvt_xuat_canh_cua_phieu.so_ptvt_xuat_canh', $so_ptvt_xuat_canh)
            ->whereDate('xuat_hang.ngay_dang_ky', today())
            ->where('xuat_hang.trang_thai', 'Đã duyệt')
            ->select(
                'xuat_hang.*',
                'doanh_nghiep.ten_doanh_nghiep',
                DB::raw('SUM(xuat_hang_cont.so_luong_xuat) as tong_so_luong_xuat')
            )
            ->groupBy(
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang.ma_loai_hinh',
                'xuat_hang.lan_xuat_canh',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ngay_xuat_canh',
                'xuat_hang.ten_doan_tau',
                'xuat_hang.trang_thai',
                'xuat_hang.ghi_chu',
                'xuat_hang.ma_cong_chuc',
                'xuat_hang.so_seal_cuoi_ngay',
                'xuat_hang.ma_doanh_nghiep',
                'xuat_hang.phuong_tien_vt_nhap',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang.tong_so_luong',
                'xuat_hang.created_at',
                'xuat_hang.updated_at',
                'doanh_nghiep.ten_doanh_nghiep'
            ) // Nhóm theo khóa chính của bảng xuat_hang
            ->get();
    }
    // public function getXuatHangDaChonPTXC($so_ptvt_xuat_canh, $ma_doanh_nghiep)
    // {
    //     return XuatHang::join('nhap_hang', 'nhap_hang.so_to_khai_nhap', 'xuat_hang.so_to_khai_nhap')
    //         ->join('ptvt_xuat_canh_cua_phieu', 'ptvt_xuat_canh_cua_phieu.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
    //         ->where('ptvt_xuat_canh_cua_phieu.so_ptvt_xuat_canh', $so_ptvt_xuat_canh)
    //         ->where('xuat_hang.trang_thai', 'Đã chọn phương tiện xuất cảnh')
    //         ->get();
    // }
    // public function getXuatHangDaXuatHang($so_ptvt_xuat_canh, $ma_doanh_nghiep)
    // {
    //     return XuatHang::join('nhap_hang', 'nhap_hang.so_to_khai_nhap', 'xuat_hang.so_to_khai_nhap')
    //         ->join('ptvt_xuat_canh_cua_phieu', 'ptvt_xuat_canh_cua_phieu.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
    //         ->where('ptvt_xuat_canh_cua_phieu.so_ptvt_xuat_canh', $so_ptvt_xuat_canh)
    //         ->where('xuat_hang.trang_thai', 'Đã duyệt xuất hàng')
    //         ->get();
    // }
    public function themXuatCanh($request)
    {
        $doanh_nghiep = $this->getDoanhNghiepHienTai();
        return XuatCanh::create([
            'ma_doanh_nghiep' => $doanh_nghiep->ma_doanh_nghiep,
            'so_ptvt_xuat_canh' => $request->so_ptvt_xuat_canh,
            'ma_doanh_nghiep_chon' => $request->ma_doanh_nghiep_chon,
            'ten_thuyen_truong' => $request->ten_thuyen_truong,
            'ngay_dang_ky' => now(),
            'trang_thai' => "Đang chờ duyệt",
        ]);
    }
    public function themChiTietXuatCanh($xuatCanh, $xuatHang)
    {
        XuatCanhChiTiet::insert([
            'ma_xuat_canh' => $xuatCanh->ma_xuat_canh,
            'so_to_khai_xuat' => $xuatHang->so_to_khai_xuat,
        ]);
    }




    public function getCongChucHienTai()
    {
        return CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
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
    public function getThongTinXuatCanh($ma_xuat_canh, $loai)
    {
        if ($loai == 'primary') {
            return XuatCanhChiTiet::where('ma_xuat_canh', $ma_xuat_canh)->get();
        } else {
            return XuatCanhChiTiet::where('ma_xuat_canh', $ma_xuat_canh)->get();
        }
    }
    public function huyXuatCanhFunc($ma_xuat_canh, $ghi_chu, $user, $ly_do)
    {
        $xuatCanh = XuatCanh::find($ma_xuat_canh);
        if ($xuatCanh) {
            $congChuc = CongChuc::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
            $so_to_khai_nhaps = XuatHang::join('xuat_canh_chi_tiet', 'xuat_canh_chi_tiet.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
                ->join('xuat_canh', 'xuat_canh.ma_xuat_canh', 'xuat_canh_chi_tiet.ma_xuat_canh')
                ->join('xuat_hang_cont', 'xuat_hang_cont.so_to_khai_xuat', 'xuat_hang.so_to_khai_xuat')
                ->where('xuat_canh.ma_xuat_canh', $xuatCanh->ma_xuat_canh)
                ->pluck('so_to_khai_nhap')->unique()->values();

            if ($xuatCanh->trang_thai == "Đang chờ duyệt") {
                foreach ($so_to_khai_nhaps as $so_to_khai_nhap) {
                    $this->themTienTrinh($so_to_khai_nhap, "Cán bộ công chức đã hủy tờ khai xuất cảnh số " . $ma_xuat_canh, $congChuc->ma_cong_chuc);
                }
                $xuatCanh->trang_thai = 'Đã hủy';
                $xuatCanh->ghi_chu = "Công chức đã hủy xuất cảnh: " . $ghi_chu;
            } elseif ($xuatCanh->trang_thai == 'Doanh nghiệp xin hủy (Chờ duyệt)') {
                foreach ($so_to_khai_nhaps as $so_to_khai_nhap) {
                    $this->themTienTrinh($so_to_khai_nhap, "Cán bộ công chức đã duyệt yêu cầu xin hủy tờ khai xuất cảnh số " . $ma_xuat_canh, $congChuc->ma_cong_chuc);
                }
                $xuatCanh->trang_thai = 'Chấp nhận hủy';
                $xuatCanh->ghi_chu = "Công chức duyệt yêu cầu hủy xuất cảnh: " . $ghi_chu;
            } elseif ($xuatCanh->trang_thai == 'Doanh nghiệp xin hủy (Đã duyệt)') {
                foreach ($so_to_khai_nhaps as $so_to_khai_nhap) {
                    $this->themTienTrinh($so_to_khai_nhap, "Cán bộ công chức đã duyệt yêu cầu xin hủy tờ khai xuất cảnh số " . $ma_xuat_canh, $congChuc->ma_cong_chuc);
                }
                $xuatCanh->trang_thai = 'Chấp nhận hủy';
                $xuatCanh->ghi_chu = "Công chức duyệt yêu cầu hủy xuất cảnh: " . $ghi_chu;
            }

            $xuatHangs = XuatHang::join('xuat_canh_chi_tiet', 'xuat_hang.so_to_khai_xuat', 'xuat_canh_chi_tiet.so_to_khai_xuat')
                ->where('ma_xuat_canh', $xuatCanh->ma_xuat_canh)
                ->select('xuat_hang.*')
                ->get();
            foreach ($xuatHangs as $xuatHang) {
                if ($xuatHang->trang_thai == 'Doanh nghiệp yêu cầu hủy phiếu đã chọn PTXC' || $xuatHang->trang_thai == 'Doanh nghiệp yêu cầu hủy phiếu đã duyệt xuất hàng') {
                    $xuatHang->trang_thai = 'Doanh nghiệp yêu cầu hủy phiếu đã duyệt';
                } elseif ($xuatHang->trang_thai == 'Doanh nghiệp yêu cầu sửa phiếu đã chọn PTXC' || $xuatHang->trang_thai == 'Doanh nghiệp yêu cầu sửa phiếu đã duyệt xuất hàng') {
                    $xuatHang->trang_thai = 'Doanh nghiệp yêu cầu sửa phiếu đã duyệt';
                } else {
                    $xuatHang->trang_thai = 'Đã duyệt';
                }
                $xuatHang->save();
            }

            $xuatCanh->save();
        }
    }
    public function getDoanhNghiepHienTai()
    {
        return DoanhNghiep::where('ma_tai_khoan', Auth::user()->ma_tai_khoan)->first();
    }
    public function kiemTraXuatHetHang($so_to_khai_nhap)
    {
        $allZero = !HangTrongCont::whereHas('hangHoa', function ($query) use ($so_to_khai_nhap) {
            $query->where('so_to_khai_nhap', $so_to_khai_nhap);
        })->where('so_luong', '!=', 0)->exists();
        if ($allZero) {
            $this->capNhatXuatHetHang($so_to_khai_nhap);
        }
    }
    public function capNhatXuatHetHang($so_to_khai_nhap)
    {
        $maCongChuc = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
            ->where('xuat_hang_cont.so_to_khai_nhap', $so_to_khai_nhap)
            ->where('xuat_hang.trang_thai', 'Đã thực xuất hàng')
            ->orderBy('xuat_hang.updated_at', 'desc')
            ->select('xuat_hang.ma_cong_chuc')
            ->first()?->ma_cong_chuc;

        NhapHang::find($so_to_khai_nhap)
            ->update([
                'ngay_xuat_het' => now(),
                'trang_thai' => 'Đã xuất hết',
                'ma_cong_chuc_ban_giao' => $maCongChuc
            ]);

        // $ly_do = " do tờ khai " . $xuatHang->so_to_khai_nhap . " đã xuất hết hàng";
        // $this->huyYeuCauCuaToKhai($xuatHang->so_to_khai_nhap, $ly_do);

        // $this->moveDatabase($xuatHang->so_to_khai_nhap);
    }
    public function huyYeuCauCuaToKhai($so_to_khai_nhap, $ly_do)
    {
        $ghi_chu =  "Hệ thống tự động hủy yêu cầu" . $ly_do;
        $yeuCauTauCont = new YeuCauTauContController();
        $yeuCauContainer = new YeuCauContainerController();
        $yeuCauChuyenTau = new YeuCauChuyenTauController();
        $yeuCauKiemTra = new YeuCauKiemTraController();
        $yeuCauTieuHuy = new YeuCauTieuHuyController();
        $yeuCauHangVeKho = new YeuCauHangVeKhoController();
        $yeuCauGiaHan = new YeuCauGiaHanController();

        $ma_yeu_cau = YeuCauTauContChiTiet::join('yeu_cau_tau_cont', 'yeu_cau_tau_cont_chi_tiet.ma_yeu_cau', 'yeu_cau_tau_cont.ma_yeu_cau')
            ->where('trang_thai', 'Đang chờ duyệt')
            ->where('so_to_khai_nhap', $so_to_khai_nhap)
            ->first()
            ->ma_yeu_cau ?? '';
        $yeuCauTauCont->huyYeuCauTauContFunc($ma_yeu_cau, $ghi_chu, "Hệ thống", $ly_do);

        $ma_yeu_cau = YeuCauContainerChiTiet::join('yeu_cau_chuyen_container', 'yeu_cau_container_chi_tiet.ma_yeu_cau', 'yeu_cau_chuyen_container.ma_yeu_cau')
            ->where('so_to_khai_nhap', $so_to_khai_nhap)
            ->where('trang_thai', 'Đang chờ duyệt')
            ->first()->ma_yeu_cau ?? '';
        $yeuCauContainer->huyYeuCauContainerFunc($ma_yeu_cau, $ghi_chu, "Hệ thống", $ly_do);

        $ma_yeu_cau = YeuCauChuyenTauChiTiet::join('yeu_cau_chuyen_tau', 'yeu_cau_chuyen_tau_chi_tiet.ma_yeu_cau', 'yeu_cau_chuyen_tau.ma_yeu_cau')
            ->where('so_to_khai_nhap', $so_to_khai_nhap)
            ->where('trang_thai', 'Đang chờ duyệt')
            ->first()->ma_yeu_cau ?? '';
        $yeuCauChuyenTau->huyYeuCauChuyenTauFunc($ma_yeu_cau, $ghi_chu, "Hệ thống", $ly_do);

        $ma_yeu_cau = YeuCauKiemTraChiTiet::join('yeu_cau_kiem_tra', 'yeu_cau_kiem_tra_chi_tiet.ma_yeu_cau', 'yeu_cau_kiem_tra.ma_yeu_cau')
            ->where('so_to_khai_nhap', $so_to_khai_nhap)
            ->where('trang_thai', 'Đang chờ duyệt')
            ->first()->ma_yeu_cau ?? '';
        $yeuCauKiemTra->huyYeuCauKiemTraFunc($ma_yeu_cau, $ghi_chu, "Hệ thống", $ly_do);

        $ma_yeu_cau = YeuCauTieuHuyChiTiet::join('yeu_cau_tieu_huy', 'yeu_cau_tieu_huy_chi_tiet.ma_yeu_cau', 'yeu_cau_tieu_huy.ma_yeu_cau')
            ->where('so_to_khai_nhap', $so_to_khai_nhap)
            ->where('trang_thai', 'Đang chờ duyệt')
            ->first()->ma_yeu_cau ?? '';
        $yeuCauTieuHuy->huyYeuCauTieuHuyFunc($ma_yeu_cau, $ghi_chu, "Hệ thống", $ly_do);

        $ma_yeu_cau = YeuCauHangVeKhoChiTiet::join('yeu_cau_hang_ve_kho', 'yeu_cau_hang_ve_kho_chi_tiet.ma_yeu_cau', 'yeu_cau_hang_ve_kho.ma_yeu_cau')
            ->where('so_to_khai_nhap', $so_to_khai_nhap)
            ->where('trang_thai', 'Đang chờ duyệt')
            ->first()->ma_yeu_cau ?? '';
        $yeuCauHangVeKho->huyYeuCauHangVeKhoFunc($ma_yeu_cau, $ghi_chu, "Hệ thống", $ly_do);

        $ma_yeu_cau = YeuCauGiaHanChiTiet::join('yeu_cau_gia_han', 'yeu_cau_gia_han_chi_tiet.ma_yeu_cau', 'yeu_cau_gia_han.ma_yeu_cau')
            ->where('so_to_khai_nhap', $so_to_khai_nhap)
            ->where('trang_thai', 'Đang chờ duyệt')
            ->first()->ma_yeu_cau ?? '';
        $yeuCauGiaHan->huyYeuCauGiaHanFunc($ma_yeu_cau, $ghi_chu, "Hệ thống", $ly_do);
    }
}
