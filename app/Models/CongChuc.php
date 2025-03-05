<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CongChuc extends Model
{
    protected $connection = 'mysql';
    protected $table = 'cong_chuc';
    protected $primaryKey = 'ma_cong_chuc';
    public $incrementing = false; // Ensure this is false if your primary key is not auto-incrementing
    protected $keyType = 'string'; // This is important if the key is a string like '61PA'}
    protected $fillable = [
        'ma_cong_chuc',
        'ten_cong_chuc',
        'ma_tai_khoan',
        'is_nhap_hang',
        'is_xuat_hang',
        'is_xuat_canh',
        'is_yeu_cau',
        'is_ban_giao',
        'is_chi_xem',
    ];
    public function tienTrinh()
    {
        return $this->hasMany(TienTrinh::class, 'ma_cong_chuc', 'ma_cong_chuc');
    }
    public function yeuCauHangVeKho()
    {
        return $this->hasMany(YeuCauHangVeKho::class, 'ma_cong_chuc', 'ma_cong_chuc');
    }

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'ma_tai_khoan', 'ma_tai_khoan');
    }
}
