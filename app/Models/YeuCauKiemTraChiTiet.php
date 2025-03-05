<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauKiemTraChiTiet extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_kiem_tra_chi_tiet';
    protected $primaryKey = 'ma_chi_tiet';
    public $timestamps = false; // Disable automatic timestamps
    protected $casts = [
        'so_to_khai_nhap' => 'string',
    ];
    protected $fillable = [
        'so_to_khai_nhap',
        'so_container',
        'so_tau',
        'so_luong',
        'ten_hang',
        'ngay_dang_ky',
        'ma_yeu_cau',
    ];

    public function nhapHang()
    {
        return $this->belongsTo(NhapHang::class, 'so_to_khai_nhap', 'so_to_khai_nhap');
    }
    public function yeuCauKiemTra()
    {
        return $this->belongsTo(YeuCauKiemTra::class, 'ma_yeu_cau', 'ma_yeu_cau');
    }
}
