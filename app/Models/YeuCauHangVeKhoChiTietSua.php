<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauHangVeKhoChiTietSua extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_hang_ve_kho_chi_tiet_sua';
    protected $primaryKey = 'ma_chi_tiet';
    public $timestamps = false; // Disable automatic timestamps
    protected $casts = [
        'so_to_khai_nhap' => 'string',
    ];
    protected $fillable = [
        'so_to_khai_nhap',
        'so_to_khai_moi',
        'ma_hai_quan',
        'so_container',
        'so_tau',
        'ngay_dang_ky',
        'ten_hang',
        'ma_sua_yeu_cau',
        'ten_phuong_tien_vt',
        'so_seal_dinh_vi'
    ];
}
