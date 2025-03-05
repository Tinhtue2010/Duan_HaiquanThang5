<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XuatCanhChiTiet extends Model
{
    protected $connection = 'mysql';
    protected $table = 'xuat_canh_chi_tiet';
    protected $primaryKey = 'ma_chi_tiet';
    protected $fillable = [
        'ma_xuat_canh',
        'so_to_khai_xuat',
    ];
    public function xuatCanh()
    {
        return $this->belongsTo(XuatCanh::class, 'ma_xuat_canh', 'ma_xuat_canh');
    }
    public function xuatHang()
    {
        return $this->belongsTo(XuatHang::class, 'so_to_khai_xuat', 'so_to_khai_xuat');
    }
}
