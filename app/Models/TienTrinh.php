<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TienTrinh extends Model
{
    protected $connection = 'mysql';
    protected $table = 'tien_trinh';
    protected $primaryKey = 'ma_tien_trinh';
    protected $casts = [
        'so_to_khai_nhap' => 'string',
    ];
    protected $fillable = [
        'so_to_khai_nhap',
        'ngay_thuc_hien',
        'ten_cong_viec',
        'ma_cong_chuc',
    ];
    public function nhapHang()
    {
        return $this->belongsTo(NhapHang::class, 'so_to_khai_nhap', 'so_to_khai_nhap');
    }
    public function congChuc()
    {
        return $this->belongsTo(CongChuc::class, 'ma_cong_chuc', 'ma_cong_chuc');
    }
}
