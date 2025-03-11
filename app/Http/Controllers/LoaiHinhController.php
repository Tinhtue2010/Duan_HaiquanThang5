<?php

namespace App\Http\Controllers;

use App\Models\BaoCao;
use App\Models\CongChuc;
use App\Models\DoanhNghiep;
use App\Models\HangHoa;
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
use App\Models\XuatCanh;
use App\Models\XuatHang;
use App\Models\YeuCauChuyenContainer;
use App\Models\YeuCauChuyenTau;
use App\Models\YeuCauChuyenTauChiTiet;
use App\Models\YeuCauContainerChiTiet;
use App\Models\YeuCauGiaHan;
use App\Models\YeuCauHangVeKho;
use App\Models\YeuCauKiemTra;
use App\Models\YeuCauKiemTraChiTiet;
use App\Models\YeuCauNiemPhong;
use App\Models\YeuCauTauCont;
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
        // $hangHoa = HangHoa::join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
        //     ->where('so_to_khai_nhap', $request->so_to_khai_nhap)->get();
        // dd($hangHoa);
        $congChucs = CongChuc::all();
        $baoCaos = BaoCao::all();
        foreach($congChucs as $congChuc){
            foreach( $baoCaos as $baoCao){
                PhanQuyenBaoCao::insert([
                    'ma_cong_chuc' => $congChuc->ma_cong_chuc,
                    'ma_bao_cao' => $baoCao->ma_bao_cao,
                ]);
            }
        }

        return redirect()->back();
    }
    public function fixNgayXuatHet()
    {
        try {
            DB::beginTransaction();

            $xuatHet = NhapHang::where('trang_thai', 'Đã xuất hết')
                ->where('ngay_xuat_het', null)
                ->get();

            foreach ($xuatHet as $nhapHang) {
                $ngay_xuat_canh = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                    ->where('xuat_hang_cont.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                    ->where('xuat_hang.trang_thai', 'Đã thực xuất hàng')
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
        $allNhapHangs = NhapHang::where('trang_thai', 'Đã nhập hàng')->get();
        $arr = [];
        foreach ($allNhapHangs as $nhapHang) {
            $soLuongTon = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', '=', 'hang_hoa.so_to_khai_nhap')
                ->join('hang_trong_cont', 'hang_hoa.ma_hang', '=', 'hang_trong_cont.ma_hang')
                ->where('nhap_hang.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->sum('hang_trong_cont.so_luong');
            if ($soLuongTon == 0) {
                array_push($arr, $soLuongTon, $nhapHang->so_to_khai_nhap);
                $nhapHang->trang_thai = "Đã xuất hết";
                $nhapHang->save();
            }
        }
    }

    public function fixCCXuatHet()
    {
        $allNhapHangs = NhapHang::where('trang_thai', 'Đã xuất hết')->get();
        foreach ($allNhapHangs as $nhapHang) {
            $maCongChuc = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', '=', 'xuat_hang_cont.so_to_khai_xuat')
                ->where('xuat_hang_cont.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->where('xuat_hang.trang_thai', 'Đã thực xuất hàng')
                ->orderBy('xuat_hang.updated_at', 'desc')
                ->select('xuat_hang.ma_cong_chuc')
                ->first()?->ma_cong_chuc;
            $nhapHang->ma_cong_chuc_ban_giao = $maCongChuc;
            $nhapHang->save();
        }
    }

    public function containerTauGoc()
    {
        $yeuCaus = YeuCauChuyenContainer::where('trang_thai', 'Đã duyệt')->get();
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
        $yeuCaus = YeuCauKiemTra::where('trang_thai', 'Đã duyệt')->get();
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
        $allNhapHangs = NhapHang::where('trang_thai', '!=', 'Đang chờ duyệt')->get();

        $arr = [];
        foreach ($allNhapHangs as $nhapHang) {
            $slKhaiBao = NhapHang::join('hang_hoa', 'nhap_hang.so_to_khai_nhap', 'hang_hoa.so_to_khai_nhap')
                ->where('nhap_hang.so_to_khai_nhap', $nhapHang->so_to_khai_nhap)
                ->sum('hang_hoa.so_luong_khai_bao');

            $slDaXuat = XuatHang::join('xuat_hang_cont', 'xuat_hang.so_to_khai_xuat', 'xuat_hang_cont.so_to_khai_xuat')
                ->where('xuat_hang.trang_thai', '!=', 'Đã hủy')
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
        $excludeValues = [500479933260, 500492636050, 500502320810, 500503039340, 500509914260, 500512470660, 500512788831, 500512795240, 500512795500, 500513909860, 500514188350, 500516161760]; // Add more if needed

        $arr = array_filter($arr, function ($value) use ($excludeValues) {
            return !in_array($value, $excludeValues);
        });

        $arr = array_values($arr);

        dd($arr);
    }
    public function checkXuatHetHang(Request $request)
    {
        $allNhapHangs = NhapHang::where('trang_thai', 'Đã xuất hết')->get();
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
}
