<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XuatHangCont extends Model
{
    protected $connection = 'mysql';
    protected $table = 'xuat_hang_cont';
    protected $primaryKey = 'ma_xuat_hang_cont'; // Assuming 'ma_hai_quan' is the primary key
    public $timestamps = false; // Disable automatic timestamps
    protected $casts = [
        'so_to_khai_nhap' => 'string'
    ];
    protected $fillable = [
        'so_to_khai_xuat',
        'so_to_khai_nhap',
        'ma_hang_cont',
        'so_luong_xuat',
        'so_luong_ton',
        'so_container',
        'phuong_tien_vt_nhap',
        'so_seal_cuoi_ngay',
        'tri_gia'
    ];
    
    public function nhapHang()
    {
        return $this->belongsTo(NhapHang::class, 'so_to_khai_nhap', 'so_to_khai_nhap');
    }
    public function xuatHang()
    {
        return $this->belongsTo(XuatHang::class, 'so_to_khai_xuat', 'so_to_khai_xuat');
    }
    public function hangTrongCont()
    {
        return $this->belongsTo(HangTrongCont::class, 'ma_hang_cont', 'ma_hang_cont');
    }
    public function PTVTXuatCanh()
    {
        return $this->belongsTo(PTVTXuatCanh::class, 'so_ptvt_xuat_canh', 'so_ptvt_xuat_canh');
    }
}
