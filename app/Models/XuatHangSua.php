<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XuatHangSua extends Model
{
    protected $connection = 'mysql';
    protected $table = 'xuat_hang_sua';
    protected $primaryKey = 'ma_yeu_cau'; // Assuming 'ma_hai_quan' is the primary key
    public $timestamps = false; // Disable automatic timestamps

    protected $fillable = [
        'so_to_khai_xuat',
        'ma_doanh_nghiep',
        'ngay_tao',
        'ma_loai_hinh',
        'ma_cong_chuc',
        'ten_doan_tau',
        'trang_thai',
        'trang_thai_phieu_xuat',
    ];
    public function chiTietSuaXuatHang()
    {
        return $this->hasMany(XuatHangChiTietSua::class, 'so_to_khai_xuat', 'so_to_khai_xuat');
    }
    public function congChuc()
    {
        return $this->belongsTo(CongChuc::class, 'ma_cong_chuc', 'ma_cong_chuc');
    }
    public function doanhNghiep()
    {
        return $this->belongsTo(DoanhNghiep::class, 'ma_doanh_nghiep', 'ma_doanh_nghiep');
    }
    public function xuatHang()
    {
        return $this->belongsTo(XuatHang::class, 'so_to_khai_xuat', 'so_to_khai_xuat');
    }
    public function loaiHinh()
    {
        return $this->belongsTo(LoaiHinh::class, 'ma_loai_hinh', 'ma_loai_hinh');
    }
}
