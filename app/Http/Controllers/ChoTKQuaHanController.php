<?php

namespace App\Http\Controllers;

use App\Models\ChoTKQuaHan;
use App\Models\LoaiHang;
use Illuminate\Http\Request;

class ChoTKQuaHanController extends Controller
{
    public function danhSachTKQuaHan()
    {
        $data = ChoTKQuaHan::leftJoin('nhap_hang', 'nhap_hang.so_to_khai_nhap', 'cho_tk_qua_han.so_to_khai_nhap')
            ->leftJoin('doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep', 'nhap_hang.ma_doanh_nghiep')
            ->select('nhap_hang.*', 'cho_tk_qua_han.so_to_khai_nhap', 'doanh_nghiep.ten_doanh_nghiep')
            ->get();
        return view('quan-ly-khac.danh-sach-tk-qua-han', data: compact(var_name: 'data'));
    }

    public function themTKQuaHan(Request $request)
    {
        if (ChoTKQuaHan::find($request->so_to_khai_nhap)) {
            session()->flash('alert-danger', 'Đã có tờ khai này cho quá hạn');
            return redirect()->back();
        }
        ChoTKQuaHan::create([
            'so_to_khai_nhap' => $request->so_to_khai_nhap,
        ]);
        session()->flash('alert-success', 'Thêm tờ khai quá hạn mới thành công');
        return redirect()->back();
    }

    public function xoaTKQuaHan(Request $request)
    {
        ChoTKQuaHan::find($request->so_to_khai_nhap)->delete();
        session()->flash('alert-success', 'Xóa tờ khai quá hạn thành công');
        return redirect()->back();
    }
}
