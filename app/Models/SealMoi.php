<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SealMoi extends Model
{
    protected $connection = 'mysql';
    protected $table = 'seal_moi';
    protected $primaryKey = 'ma_seal_moi';
    public $timestamps = false; // Disable automatic timestamps
    protected $fillable = [
        'ma_yeu_cau',
        'so_container',
        'so_seal_moi',
    ];
    public function yeuCauChuyenContainer()
    {
        return $this->belongsTo(YeuCauChuyenContainer::class, 'ma_yeu_cau', 'ma_yeu_cau');
    }
}
