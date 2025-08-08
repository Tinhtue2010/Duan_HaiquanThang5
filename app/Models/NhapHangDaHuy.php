<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhapHangDaHuy extends Model
{
    protected $connection = 'mysql';
    protected $table = 'nhap_hang_da_huy';
    protected $primaryKey = 'id_huy'; // Specify your custom primary key
    protected $casts = [
        'so_to_khai_nhap' => 'string',
    ];
    public $timestamps = false; // Disable automatic timestamps

    protected $fillable = [
        'so_to_khai_nhap',
        'ma_hai_quan',
        'ma_doanh_nghiep',
        'ma_chu_hang',
        'ma_loai_hinh',
        'ngay_dang_ky',
        'ngay_thong_quan',
        'ngay_thong_quan',
        'ngay_xuat_het',
        'trang_thai',
        'ghi_chu',
        'container_ban_dau',
        'trong_luong',
        'phuong_tien_vt_nhap',
        'ptvt_ban_dau',
        'ten_doan_tau',
        'ma_cong_chuc',
    ];
    public function hangHoaDaHuy()
    {
        return $this->hasMany(HangHoaDaHuy::class, 'id_huy', 'id_huy');
    }
    public function haiQuan()
    {
        return $this->belongsTo(HaiQuan::class, 'ma_hai_quan', 'ma_hai_quan');
    }
    public function doanhNghiep()
    {
        return $this->belongsTo(DoanhNghiep::class, 'ma_doanh_nghiep', 'ma_doanh_nghiep');
    }
    public function chuHang()
    {
        return $this->belongsTo(ChuHang::class, 'ma_chu_hang', 'ma_chu_hang');
    }

}
