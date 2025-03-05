<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauContainerChiTietSua extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_container_chi_tiet_sua';
    protected $primaryKey = 'ma_chi_tiet';
    public $timestamps = false; // Disable automatic timestamps
    protected $casts = [
        'so_to_khai_cont_moi' => 'string',
        'so_to_khai_nhap' => 'string',
    ];
    protected $fillable = [
        'so_to_khai_nhap',
        'tau_goc',
        'so_container_goc',
        'so_container_dich',
        'so_luong_ton_cont_moi',
        'so_to_khai_cont_moi',
        'so_luong_chuyen',
        'ma_sua_yeu_cau',
    ];
    public function yeuCauContainerHangHoa()
    {
        return $this->hasMany(YeuCauContainerHangHoaSua::class, 'ma_chi_tiet', 'ma_chi_tiet');
    }
}
