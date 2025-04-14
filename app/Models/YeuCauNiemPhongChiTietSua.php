<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauNiemPhongChiTietSua extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_niem_phong_chi_tiet_sua';
    protected $primaryKey = 'ma_chi_tiet';
    public $timestamps = false; // Disable automatic timestamps
    protected $fillable = [
        'so_container',
        'so_seal_cu',
        'so_seal_moi',
        'ma_yeu_cau',
    ];
}
