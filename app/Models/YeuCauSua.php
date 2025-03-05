<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauSua extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_sua';
    protected $primaryKey = 'ma_sua_yeu_cau';
    public $timestamps = false; // Disable automatic timestamps
    protected $fillable = [
        'ten_doan_tau',
        'ma_yeu_cau',
        'loai_yeu_cau',
        'file_name',
        'file_path',
    ];
}
