<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauChuyenTauChiTietSua extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_chuyen_tau_chi_tiet_sua';
    protected $primaryKey = 'ma_chi_tiet';
    public $timestamps = false; // Disable automatic timestamps
    protected $casts = [
        'so_to_khai_nhap' => 'string',
    ];
    protected $fillable = [
        'so_to_khai_nhap',
        'so_luong',
        'so_container',
        'tau_goc',
        'tau_dich',
        'ma_sua_yeu_cau',
    ];
}
