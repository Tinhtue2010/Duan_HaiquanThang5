<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauHangVeKhoChiTiet extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_hang_ve_kho_chi_tiet';
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
        'ten_hang',
        'ma_yeu_cau',
        'ten_phuong_tien_vt'
    ];

    public function nhapHang()
    {
        return $this->belongsTo(NhapHang::class, 'so_to_khai_nhap', 'so_to_khai_nhap');
    }
    // public function PTVTXuatCanh()
    // {
    //     return $this->belongsTo(PTVTXuatCanh::class, 'so_ptvt_xuat_canh', 'so_ptvt_xuat_canh');
    // }
    public function yeuCauHangVeKho()
    {
        return $this->belongsTo(YeuCauHangVeKho::class, 'ma_yeu_cau', 'ma_yeu_cau');
    }
}
