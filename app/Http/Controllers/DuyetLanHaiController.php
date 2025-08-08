<?php

namespace App\Http\Controllers;

use App\Models\NhapHang;
use App\Models\XuatHang;
use Yajra\DataTables\Facades\DataTables;

class DuyetLanHaiController extends Controller
{
    public function quanLyDuyetNhapHang()
    {
        $nhapHangs = NhapHang::join('doanh_nghiep', 'nhap_hang.ma_doanh_nghiep', '=', 'doanh_nghiep.ma_doanh_nghiep')
            ->where('nhap_hang.trang_thai', '8')
            ->get();
        return view('lanh-dao.quan-ly-duyet-nhap-hang', compact('nhapHangs'));
    }
    public function quanLyDuyetXuatHang()
    {
        $xuatHangs = XuatHang::whereIn('xuat_hang.trang_thai', [14,15])
            ->join('doanh_nghiep', 'xuat_hang.ma_doanh_nghiep', 'doanh_nghiep.ma_doanh_nghiep')
            ->select(
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang.ma_doanh_nghiep',
                'xuat_hang.ma_loai_hinh',
                'xuat_hang.trang_thai',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang.tong_so_luong',
                'doanh_nghiep.ten_doanh_nghiep'
            )
            ->groupBy(
                'xuat_hang.so_to_khai_xuat',
                'xuat_hang.ma_doanh_nghiep',
                'xuat_hang.ma_loai_hinh',
                'xuat_hang.trang_thai',
                'xuat_hang.ngay_dang_ky',
                'xuat_hang.ten_phuong_tien_vt',
                'xuat_hang.tong_so_luong',
                'doanh_nghiep.ten_doanh_nghiep'
            )
            ->orderBy('xuat_hang.so_to_khai_xuat', 'desc')
            ->get();

        return view('lanh-dao.quan-ly-duyet-xuat-hang', compact('xuatHangs'));
    }
}
