<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauTieuHuyChiTietSua extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_tieu_huy_chi_tiet_sua';
    protected $primaryKey = 'ma_chi_tiet';
    public $timestamps = false; // Disable automatic timestamps
    protected $casts = [
        'so_to_khai_nhap' => 'string',
    ];
    protected $fillable = [
        'so_to_khai_nhap',
        'so_container',
        'so_tau',
        'ngay_dang_ky',
        'ma_sua_yeu_cau',
    ];
}
