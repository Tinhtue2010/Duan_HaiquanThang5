<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauGiaHanChiTiet extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_gia_han_chi_tiet';
    protected $primaryKey = 'ma_chi_tiet';
    public $timestamps = false; // Disable automatic timestamps
    protected $casts = [
        'so_to_khai_nhap' => 'string',
    ];
    protected $fillable = [
        'so_to_khai_nhap',
        'so_ngay_gia_han',
        'ma_yeu_cau',
        'so_container',
        'so_tau',
        'ten_hang',
        'ngay_dang_ky',
    ];

    public function nhapHang()
    {
        return $this->belongsTo(NhapHang::class, 'so_to_khai_nhap', 'so_to_khai_nhap');
    }
    public function yeuCauGiaHan()
    {
        return $this->belongsTo(YeuCauGiaHan::class, 'ma_yeu_cau', 'ma_yeu_cau');
    }
}
