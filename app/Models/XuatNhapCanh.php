<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XuatNhapCanh extends Model
{
    protected $connection = 'mysql';
    protected $table = 'xuat_nhap_canh';
    protected $primaryKey = 'ma_xnc';
    public $timestamps = false;

    protected $fillable = [
        'so_ptvt_xuat_canh',
        'ngay_them',
        'so_the',
        'is_hang_lanh',
        'is_hang_nong',
        'ma_chu_hang',
        'so_luong_may',
        'tong_trong_tai',
        'ma_cong_chuc',
        'thoi_gian_nhap_canh',
        'thoi_gian_xuat_canh',
        'ghi_chu',
        'trang_thai',
    ];

    public function chuHang()
    {
        return $this->belongsTo(ChuHang::class, 'ma_chu_hang', 'ma_chu_hang');
    }
    public function PTVTXuatCanh()
    {
        return $this->belongsTo(PTVTXuatCanh::class, 'so_ptvt_xuat_canh', 'so_ptvt_xuat_canh');
    }
}
