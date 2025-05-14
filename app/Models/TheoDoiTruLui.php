<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TheoDoiTruLui extends Model
{
    protected $connection = 'mysql';
    protected $table = 'theo_doi_tru_lui';
    protected $primaryKey = 'ma_theo_doi';
    public $timestamps = false; // Disable automatic timestamps
    protected $casts = [
        'so_to_khai_nhap' => 'string',
    ];
    protected $fillable = [
        'so_to_khai_nhap',
        'so_ptvt_nuoc_ngoai',
        'ngay_them', 
        'ngay_duyet', 
        'cong_viec',
        'ma_yeu_cau',
    ];
    public function theoDoiChiTiet()
    {
        return $this->hasMany(TheoDoiTruLuiChiTiet::class, 'ma_theo_doi', 'ma_theo_doi');
    }
}
