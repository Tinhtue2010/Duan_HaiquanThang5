<?php

namespace App\Http\Controllers;

use App\Models\TaiKhoan;
use App\Models\ThuKho;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class ThuKhoController extends Controller
{
    public function danhSachThuKho()
    {
        $data = ThuKho::leftJoin('tai_khoan', 'thu_kho.ma_tai_khoan', '=', 'tai_khoan.ma_tai_khoan')
            ->get();

        $taiKhoans = TaiKhoan::where('loai_tai_khoan', 'Thủ kho')
            ->doesntHave('thuKho')
            ->get();
        return view('quan-ly-khac.danh-sach-thu-kho', data: compact('data', 'taiKhoans'));
    }
    public function themThuKho(Request $request)
    {
        if (ThuKho::find($request->ma_thu_kho)) {
            session()->flash('alert-danger', 'Mã thủ kho này đã tồn tại.');
            return redirect()->back();
        }
        $ma_tai_khoan = $this->taoTaiKhoan($request->ten_dang_nhap, $request->mat_khau, "Thủ kho");

        if (!$ma_tai_khoan) {
            session()->flash('alert-danger', 'Tên đăng nhập này đã được sử dụng.');
            return redirect()->back();
        }
        ThuKho::create([
            'ma_thu_kho' => $request->ma_thu_kho,
            'ten_thu_kho' => $request->ten_thu_kho,
            'ma_tai_khoan' => $ma_tai_khoan,
        ]);
        session()->flash('alert-success', 'Thêm thủ kho mới thành công');
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
    public function xoaThuKho(Request $request)
    {
        ThuKho::find($request->ma_thu_kho)->delete();
        session()->flash('alert-success', 'Xóa thủ kho thành công');
        return redirect()->back();
    }
    public function updateThuKho(Request $request)
    {
        $thuKho = ThuKho::find($request->ma_thu_kho);
        $ma_thu_kho = $request->ma_thu_kho;
        if ($thuKho) {
            if ($request->ma_thu_kho != $request->ma_thu_kho_moi) {
                if (ThuKho::find($request->ma_thu_kho_moi)) {
                    session()->flash('alert-danger', 'Mã thủ kho này đã tồn tại.');
                    return redirect()->back();
                }
                $ma_thu_kho = $request->ma_thu_kho_moi;
                $thuKho->update(['ma_thu_kho' => $request->ma_thu_kho_moi]);
            }
            $thuKho->update([
                'ten_thu_kho' => $request->ten_thu_kho,
                'status' => $request->status ?? 1,
            ]);
            if ($request->ma_tai_khoan != '') {
                $taiKhoan =  TaiKhoan::find($request->ma_tai_khoan);
                ThuKho::find($ma_thu_kho)->update(['ma_tai_khoan' => $taiKhoan->ma_tai_khoan]);;
            }
            session()->flash('alert-success', 'Cập nhật thành công');
            return redirect()->back();
        }
        session()->flash('alert-danger', 'Có lỗi xảy ra');
        return redirect()->back();
    }
}
