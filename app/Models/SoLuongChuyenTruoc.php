<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoLuongChuyenTruoc extends Model
{
    protected $connection = 'mysql';
    protected $table = 'so_luong_chuyen_truoc';
    protected $primaryKey = 'ma_chuyen';
    protected $fillable = [
        'ma_yeu_cau',
        'ma_chi_tiet',
        'cong_viec',
    ];
}
