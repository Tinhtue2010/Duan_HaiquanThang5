<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XuatHang extends Model
{
    protected $connection = 'mysql';
    protected $table = 'xuat_hang';
    protected $primaryKey = 'so_to_khai_xuat'; // Assuming 'ma_hai_quan' is the primary key
    protected $fillable = [
        'so_to_khai_xuat',
        'ma_loai_hinh',
        'ma_doanh_nghiep',
        'ngay_dang_ky',
        'ngay_xuat_canh',
        'ten_doan_tau',
        'trang_thai',
        'ghi_chu',
        'ma_cong_chuc',
        'phuong_tien_vt_nhap',
        'ten_phuong_tien_vt',
        'tong_so_luong',
    ];


    public function PTVTXuatCanhCuaPhieu()
    {
        return $this->hasMany(PTVTXuatCanhCuaPhieu::class, 'so_to_khai_xuat', 'so_to_khai_xuat');
    }
    public function chiTietXuatCanh()
    {
        return $this->belongsTo(XuatCanhChiTiet::class, 'so_to_khai_xuat', 'so_to_khai_xuat');
    }
    public function PTVTXuatCanh()
    {
        return $this->belongsTo(PTVTXuatCanh::class, 'so_ptvt_xuat_canh', 'so_ptvt_xuat_canh');
    }
    public function suaXuatHang()
    {
        return $this->hasMany(XuatHangSua::class, 'so_to_khai_xuat', 'so_to_khai_xuat');
    }
    public function xuatHangCont()
    {
        return $this->hasMany(XuatHangCont::class, 'so_to_khai_xuat', 'so_to_khai_xuat');
    }
    public function loaiHinh()
    {
        return $this->belongsTo(LoaiHinh::class, 'ma_loai_hinh', 'ma_loai_hinh');
    }
    public function congChuc()
    {
        return $this->belongsTo(CongChuc::class, 'ma_cong_chuc', 'ma_cong_chuc');
    }
    public function doanhNghiep()
    {
        return $this->belongsTo(DoanhNghiep::class, 'ma_doanh_nghiep', 'ma_doanh_nghiep');
    }
}
