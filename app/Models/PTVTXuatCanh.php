<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PTVTXuatCanh extends Model
{
    protected $connection = 'mysql';
    protected $table = 'ptvt_xuat_canh';
    protected $primaryKey = 'so_ptvt_xuat_canh';
    public $timestamps = false;

    protected $fillable = [
        'ten_phuong_tien_vt',
        'quoc_tich_tau',
        'cang_den',
        'ten_thuyen_truong',
        'so_giay_chung_nhan',
        'trang_thai',
        'draft',
        'dwt',
        'loa',
        'breadth',
    ];
    public function PTVTXuatCanhCuaPhieu()
    {
        return $this->hasMany(PTVTXuatCanhCuaPhieu::class, 'so_ptvt_xuat_canh', 'so_ptvt_xuat_canh');
    }
    public function xuatHangCont()
    {
        return $this->hasMany(XuatHangCont::class, 'so_ptvt_xuat_canh', 'so_ptvt_xuat_canh');
    }
    public function chiTietYeuCauHangVeKho()
    {
        return $this->hasMany(YeuCauHangVeKhoChiTiet::class, 'so_ptvt_xuat_canh', 'so_ptvt_xuat_canh');
    }
    public function xuatCanh()
    {
        return $this->hasMany(XuatCanh::class, 'so_ptvt_xuat_canh', 'so_ptvt_xuat_canh');
    }
}