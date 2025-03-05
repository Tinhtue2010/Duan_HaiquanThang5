<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TheoDoiTruLuiChiTiet extends Model
{
    protected $connection = 'mysql';
    protected $table = 'theo_doi_tru_lui_chi_tiet';
    protected $primaryKey = 'ma_chi_tiet';
    public $timestamps = false; // Disable automatic timestamps
    protected $fillable = [
        'ten_hang',
        'so_luong_xuat',
        'so_luong_chua_xuat',
        'ma_theo_doi',
        'so_container',
        'so_seal',
    ];
}
