<?php

namespace App\Http\Controllers;

use App\Models\ChuHang;
use Illuminate\Http\Request;

class ChuHangController extends Controller
{
    public function danhSachChuHang()
    {
        $data = ChuHang::all();
        return view('quan-ly-khac.danh-sach-chu-hang', data: compact('data'));
    }

    public function themChuHang(Request $request)
    {
        if (ChuHang::find($request->ma_chu_hang)) {
            session()->flash('alert-danger', 'Mã đại lý này đã tồn tại.');
            return redirect()->back();
        }

        ChuHang::create([
            'ma_chu_hang' => $request->ma_chu_hang,
            'ten_chu_hang' => $request->ten_chu_hang,
        ]);
        session()->flash('alert-success', 'Thêm đại lý mới thành công');
        return redirect()->back();
    }

    public function xoaChuHang(Request $request)
    {
        ChuHang::find($request->ma_chu_hang)->delete();
        session()->flash('alert-success', 'Xóa đại lý thành công');
        return redirect()->back();
    }

    public function updateChuHang(Request $request)
    {
        if (ChuHang::find($request->ma_chu_hang)) {
            ChuHang::find($request->ma_chu_hang)->update(['ten_chu_hang' => $request->ten_chu_hang]);;
            session()->flash('alert-success', 'Cập nhật thành công');
            return redirect()->back();
        }
        session()->flash('alert-danger', 'Có lỗi xảy ra');
        return redirect()->back();
    }
}
