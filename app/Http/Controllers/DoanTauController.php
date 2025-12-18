<?php

namespace App\Http\Controllers;

use App\Models\ChuHang;
use App\Models\NhapHang;
use App\Models\NiemPhong;
use Illuminate\Http\Request;

class DoanTauController extends Controller
{
    public function danhSachDoanTau()
    {
        $data = NhapHang::join('hang_hoa', 'hang_hoa.so_to_khai_nhap', 'nhap_hang.so_to_khai_nhap')
            ->join('hang_trong_cont', 'hang_trong_cont.ma_hang', 'hang_hoa.ma_hang')
            ->leftJoin('container', 'container.so_container', 'hang_trong_cont.so_container')
            ->leftJoin('niem_phong', 'container.so_container', '=', 'niem_phong.so_container')
            ->whereIn('nhap_hang.trang_thai', ['2', '4', '7'])
            ->where('niem_phong.ten_doan_tau', '!=', null)
            ->where('niem_phong.ten_doan_tau', '!=', '')
            ->select('niem_phong.ten_doan_tau', 'niem_phong.phuong_tien_vt_nhap')
            ->groupBy('phuong_tien_vt_nhap')
            ->orderBy('niem_phong.ten_doan_tau', 'asc')
            ->get();
        return view('quan-ly-khac.danh-sach-doan-tau', data: compact('data'));
    }

    public function updateDoanTau(Request $request)
    {
        NiemPhong::where('phuong_tien_vt_nhap', $request->phuong_tien_vt_nhap)
            ->update(['ten_doan_tau' => $request->ten_doan_tau]);
        session()->flash('alert-success', 'Cập nhật thành công');
        return redirect()->back();
    }
}
