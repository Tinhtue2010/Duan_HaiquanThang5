<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauGoSealChiTiet extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_go_seal_chi_tiet';
    protected $primaryKey = 'ma_chi_tiet';
    public $timestamps = false; // Disable automatic timestamps
    protected $fillable = [
        'so_container',
        'phuong_tien_vt_nhap',
        'so_seal_cu',
        'so_seal_moi',
        'ma_yeu_cau',
    ];
}
