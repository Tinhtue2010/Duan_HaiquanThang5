<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauChuyenTauChiTiet extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_chuyen_tau_chi_tiet';
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
        'ma_yeu_cau',
    ];
    public function yeuCauChuyenTau()
    {
        return $this->belongsTo(YeuCauChuyenTau::class, 'ma_yeu_cau', 'ma_yeu_cau');
    }
}
