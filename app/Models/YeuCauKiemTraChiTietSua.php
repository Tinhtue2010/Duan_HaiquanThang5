<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauKiemTraChiTietSua extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_kiem_tra_chi_tiet_sua';
    protected $primaryKey = 'ma_chi_tiet';
    public $timestamps = false; // Disable automatic timestamps
    protected $casts = [
        'so_to_khai_nhap' => 'string',
    ];
    protected $fillable = [
        'so_to_khai_nhap',
        'so_container',
        'so_luong',
        'so_tau',
        'so_luong',
        'ten_hang',
        'ngay_dang_ky',
        'ma_sua_yeu_cau',
    ];
}
