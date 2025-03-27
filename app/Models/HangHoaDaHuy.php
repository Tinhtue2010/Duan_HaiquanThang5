<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HangHoaDaHuy extends Model
{
    protected $connection = 'mysql';
    protected $table = 'hang_hoa_da_huy';
    protected $primaryKey = 'ma_hang'; 
    public $timestamps = false; // Disable automatic timestamps

    protected $fillable = [
        'id_huy',
        'ten_hang',
        'xuat_xu',
        'loai_hang',
        'so_luong_khai_bao',
        'don_gia',
        'tri_gia',
        'don_vi_tinh',
        'so_container_khai_bao',
    ];
    public function nhapHangDaHuy()
    {
        return $this->belongsTo(NhapHangDaHuy::class, 'id_huy', 'id_huy');
    }
}
