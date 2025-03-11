<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhanQuyenBaoCao extends Model
{
    protected $connection = 'mysql';
    protected $table = 'phan_quyen_bao_cao';
    protected $primaryKey = 'ma_phan_quyen';
    public $timestamps = false; // Disable automatic timestamps

    protected $fillable = [
        'ma_cong_chuc',
        'ma_bao_cao',
        'phan_quyen',
    ];
}
