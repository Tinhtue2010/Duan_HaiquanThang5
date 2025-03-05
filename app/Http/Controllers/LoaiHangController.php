<?php

namespace App\Http\Controllers;
use App\Models\LoaiHang;
use Illuminate\Http\Request;

class LoaiHangController extends Controller
{
    public function danhSachLoaiHang()
    {
        $data = LoaiHang::all();
        return view('quan-ly-khac.danh-sach-loai-hang', data: compact(var_name: 'data'));
    }

    public function themLoaiHang(Request $request)
    {
        LoaiHang::create([
            'ten_loai_hang' => $request->ten_loai_hang,
            'don_vi_tinh' => $request->don_vi_tinh,
        ]);
        session()->flash('alert-success', 'Thêm loại hàng mới thành công');
        return redirect()->back();
    }

    public function xoaLoaiHang(Request $request)
    {
        LoaiHang::find($request->ma_loai_hang)->delete();
        session()->flash('alert-success', 'Xóa loại hàng thành công');
        return redirect()->back();
    }

}
