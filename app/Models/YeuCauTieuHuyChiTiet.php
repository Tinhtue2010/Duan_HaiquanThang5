<?php

namespace App\Models;

use App\Models\SecondDB\NhapHangSecond;
use Illuminate\Database\Eloquent\Model;

class YeuCauTieuHuyChiTiet extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_tieu_huy_chi_tiet';
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
        'ma_yeu_cau',
    ];

    public function nhapHang()
    {
        return $this->belongsTo(NhapHang::class, 'so_to_khai_nhap', 'so_to_khai_nhap');
    }
    public function yeuCauTieuHuy()
    {
        return $this->belongsTo(YeuCauTieuHuy::class, 'ma_yeu_cau', 'ma_yeu_cau');
    }
}
