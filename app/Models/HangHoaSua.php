<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HangHoaSua extends Model
{
    protected $connection = 'mysql';
    protected $table = 'hang_hoa_sua';
    protected $primaryKey = 'ma_hang'; 
    protected $casts = [
        'so_to_khai_nhap' => 'string',
    ];
    public $timestamps = false; // Disable automatic timestamps

    protected $fillable = [
        'so_to_khai_nhap',
        'ten_hang',
        'xuat_xu',
        'loai_hang',
        'so_luong_khai_bao',
        'don_gia',
        'tri_gia',
        'don_vi_tinh',
        'so_container_khai_bao',
    ];
    public function nhapHang()
    {
        return $this->belongsTo(NhapHang::class, 'so_to_khai_nhap', 'so_to_khai_nhap');
    }
}
