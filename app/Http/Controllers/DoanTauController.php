<?php

namespace App\Http\Controllers;

use App\Models\ChuHang;
use App\Models\NhapHang;
use Illuminate\Http\Request;

class DoanTauController extends Controller
{
    public function danhSachDoanTau()
    {
        $data = NhapHang::where('trang_thai', 2)
            ->where('ten_doan_tau', '!=', null)
            ->where('ten_doan_tau', '!=', '')
            ->orderBy('ten_doan_tau', 'asc')
            ->groupBy('phuong_tien_vt_nhap')
            ->get();
        return view('quan-ly-khac.danh-sach-doan-tau', data: compact('data'));
    }

    public function updateDoanTau(Request $request)
    {
        NhapHang::where('trang_thai', 2)
            ->where('phuong_tien_vt_nhap', $request->phuong_tien_vt_nhap)
            ->update(['ten_doan_tau' => $request->ten_doan_tau]);
        session()->flash('alert-success', 'Cập nhật thành công');
        return redirect()->back();
    }
}
