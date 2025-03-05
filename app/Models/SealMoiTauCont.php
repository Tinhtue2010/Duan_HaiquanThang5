<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SealMoiTauCont extends Model
{
    protected $connection = 'mysql';
    protected $table = 'seal_moi_tau_cont';
    protected $primaryKey = 'ma_seal_moi';
    public $timestamps = false; // Disable automatic timestamps
    protected $fillable = [
        'ma_yeu_cau',
        'so_container',
        'so_seal_moi',
    ];
}
