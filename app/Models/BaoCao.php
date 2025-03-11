<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaoCao extends Model
{
    protected $connection = 'mysql';
    protected $table = 'bao_cao';
    protected $primaryKey = 'ma_bao_cao';
    protected $fillable = [
        'ten_bao_cao',
    ];
}
