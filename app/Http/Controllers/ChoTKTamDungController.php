<?php

namespace App\Http\Controllers;

use App\Models\ChoTKTamDung;
use App\Models\LoaiHang;
use Illuminate\Http\Request;

class ChoTKTamDungController extends Controller
{
    public function danhSachTKTamDung()
    {
        $data = ChoTKTamDung::leftJoin('nhap_hang', 'nhap_hang.so_to_khai_nhap', 'cho_tk_tam_dung.so_to_khai_nhap')
            ->leftJoin('doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep', 'nhap_hang.ma_doanh_nghiep')
            ->select('nhap_hang.*', 'cho_tk_tam_dung.so_to_khai_nhap', 'doanh_nghiep.ten_doanh_nghiep')
            ->get();
        return view('quan-ly-khac.danh-sach-tk-tam-dung', data: compact(var_name: 'data'));
    }

    public function themTKTamDung(Request $request)
    {
        if (ChoTKTamDung::find($request->so_to_khai_nhap)) {
            session()->flash('alert-danger', 'Đã có tờ khai này cho tạm dừng');
            return redirect()->back();
        }
        ChoTKTamDung::create([
            'so_to_khai_nhap' => $request->so_to_khai_nhap,
        ]);
        session()->flash('alert-success', 'Thêm tờ khai tạm dừng mới thành công');
        return redirect()->back();
    }

    public function xoaTKTamDung(Request $request)
    {
        ChoTKTamDung::find($request->so_to_khai_nhap)->delete();
        session()->flash('alert-success', 'Xóa tờ khai tạm dừng thành công');
        return redirect()->back();
    }
}
