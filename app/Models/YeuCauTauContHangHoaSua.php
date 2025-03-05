<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauTauContHangHoaSua extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_tau_cont_hang_hoa_sua';
    protected $primaryKey = 'ma_yeu_cau_hang_hoa ';
    public $timestamps = false; // Disable automatic timestamps
    protected $fillable = [
        'ma_hang_cont',
        'ten_hang',
        'so_container_cu',
        'so_container_moi',
        'so_luong',
        'ma_chi_tiet',
    ];
    public function yeuCauTauContChiTiet()
    {
        return $this->belongsTo(YeuCauTauContChiTietSua::class, 'ma_chi_tiet', 'ma_chi_tiet');
    }
}
