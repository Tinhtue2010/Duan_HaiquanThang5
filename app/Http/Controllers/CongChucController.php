<?php

namespace App\Http\Controllers;

use App\Models\BaoCao;
use App\Models\CongChuc;
use App\Models\PhanQuyenBaoCao;
use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class CongChucController extends Controller
{
    public function danhSachCongChuc()
    {
        $data = CongChuc::leftJoin('tai_khoan', 'cong_chuc.ma_tai_khoan', '=', 'tai_khoan.ma_tai_khoan')
            ->orderBy('cong_chuc.status')
            ->get();

        $taiKhoans = TaiKhoan::where('loai_tai_khoan', 'Cán bộ công chức')
            ->doesntHave('congChuc')
            ->get();
        return view('quan-ly-khac.danh-sach-cong-chuc', data: compact('data', 'taiKhoans'));
    }

    public function themCongChuc(Request $request)
    {
        if (CongChuc::find($request->ma_cong_chuc)) {
            session()->flash('alert-danger', 'Mã cán bộ công chức này đã tồn tại.');
            return redirect()->back();
        }
        $ma_tai_khoan = $this->taoTaiKhoan($request->ten_dang_nhap, $request->mat_khau, "Cán bộ công chức");
        if (!$ma_tai_khoan) {
            session()->flash('alert-danger', 'Tên đăng nhập này đã được sử dụng.');
            return redirect()->back();
        }
        $congChuc = CongChuc::create([
            'ma_cong_chuc' => $request->ma_cong_chuc,
            'ten_cong_chuc' => $request->ten_cong_chuc,
            'ma_tai_khoan' => $ma_tai_khoan,
        ]);

        $baoCaos = BaoCao::all();
        foreach ($baoCaos as $baoCao) {
            $check = PhanQuyenBaoCao::where('ma_cong_chuc', $congChuc->ma_cong_chuc)
                ->where('ma_bao_cao', $baoCao->ma_bao_cao)
                ->exists();
            if (!$check) {
                PhanQuyenBaoCao::insert([
                    'ma_cong_chuc' => $congChuc->ma_cong_chuc,
                    'ma_bao_cao' => $baoCao->ma_bao_cao,
                ]);
            }
        }

        session()->flash('alert-success', 'Thêm cán bộ công chức mới thành công');
        return redirect()->back();
    }
    public function taoTaiKhoan($ten_dang_nhap, $mat_khau, $loai_tai_khoan)
    {
        if (!TaiKhoan::where('ten_dang_nhap', $ten_dang_nhap)->get()->isEmpty()) {
            return false;
        }
        $taiKhoan = TaiKhoan::create([
            'ten_dang_nhap' => $ten_dang_nhap,
            'mat_khau' => Hash::make($mat_khau),
            'loai_tai_khoan' => $loai_tai_khoan,
        ]);
        return $taiKhoan->ma_tai_khoan;
    }
    public function xoaCongChuc(Request $request)
    {
        $congChuc = CongChuc::find($request->ma_cong_chuc);
        TaiKhoan::find($congChuc->ma_tai_khoan)->delete();
        CongChuc::find($request->ma_cong_chuc)->delete();

        session()->flash('alert-success', 'Xóa công chức thành công');
        return redirect()->back();
    }

    public function updateCongChuc(Request $request)
    {
        $congChuc = CongChuc::find($request->ma_cong_chuc);
        $ma_cong_chuc = $request->ma_cong_chuc;
        if ($congChuc) {
            if ($request->ma_cong_chuc != $request->ma_cong_chuc_moi) {
                if (CongChuc::find($request->ma_cong_chuc_moi)) {
                    session()->flash('alert-danger', 'Mã cán bộ công chức này đã tồn tại.');
                    return redirect()->back();
                }
                $ma_cong_chuc = $request->ma_cong_chuc_moi;
                $congChuc->update(['ma_cong_chuc' => $request->ma_cong_chuc_moi]);
            }
            $congChuc->update(['ten_cong_chuc' => $request->ten_cong_chuc]);
            if ($request->ma_tai_khoan != '') {
                $taiKhoan =  TaiKhoan::find($request->ma_tai_khoan);
                CongChuc::find($ma_cong_chuc)->update(['ma_tai_khoan' => $taiKhoan->ma_tai_khoan]);;
            }
            $congChuc->is_nhap_hang = $request->is_nhap_hang ? 1 : 0;
            $congChuc->is_xuat_hang = $request->is_xuat_hang ? 1 : 0;
            $congChuc->is_xuat_canh = $request->is_xuat_canh ? 1 : 0;
            $congChuc->is_ban_giao = $request->is_ban_giao ? 1 : 0;
            $congChuc->is_yeu_cau = $request->is_yeu_cau ? 1 : 0;
            $congChuc->is_chi_xem = $request->is_chi_xem ? 1 : 0;
            $congChuc->status = $request->status ?? 1;
            $congChuc->save();
            session()->flash('alert-success', 'Cập nhật thành công');
            return redirect()->back();
        }
        session()->flash('alert-danger', 'Có lỗi xảy ra');
        return redirect()->back();
    }
    public function phanQuyenBaoCao(Request $request)
    {
        $ma_cong_chuc = $request->ma_cong_chuc;
        $congChuc = CongChuc::find($ma_cong_chuc);
        $phanQuyens = PhanQuyenBaoCao::where('ma_cong_chuc', $ma_cong_chuc)->get();
        foreach ($phanQuyens as $phanQuyen) {
            $phanQuyen->phan_quyen = $request->input($phanQuyen->ma_bao_cao) ? 1 : 0;
            $phanQuyen->save();
        }
        session()->flash('alert-success', 'Cập nhật thành công');
        return redirect()->back();
    }

    public function getPhanQuyenBaoCao(Request $request)
    {
        $phanQuyens = PhanQuyenBaoCao::where('ma_cong_chuc', $request->ma_cong_chuc)
            ->get();
        return response()->json($phanQuyens);
    }
}
