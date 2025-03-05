<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BanGiaoHoSoChiTiet extends Model
{
    protected $connection = 'mysql';
    protected $table = 'ban_giao_ho_so_chi_tiet';
    protected $primaryKey = 'ma_chi_tiet'; // Specify your custom primary key
    protected $casts = [
        'so_to_khai_nhap' => 'string'
    ];
    protected $fillable = [
        'so_to_khai_nhap',
        'ma_ban_giao'
    ];
    public function nhapHang()
    {
        return $this->belongsTo(NhapHang::class, 'so_to_khai_nhap', 'so_to_khai_nhap');
    }
}
