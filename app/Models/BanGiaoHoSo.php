<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BanGiaoHoSo extends Model
{
    protected $connection = 'mysql';
    protected $table = 'ban_giao_ho_so';
    protected $primaryKey = 'ma_ban_giao';
    public $timestamps = false;

    protected $fillable = [
        'tu_ngay',
        'den_ngay',
        'ma_cong_chuc',
        'ngay_tao',
    ];
    public function congChuc()
    {
        return $this->belongsTo(CongChuc::class, 'ma_cong_chuc', 'ma_cong_chuc');
    }
}
