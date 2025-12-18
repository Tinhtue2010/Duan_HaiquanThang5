<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NiemPhong extends Model
{
    protected $connection = 'mysql';
    protected $table = 'niem_phong';
    protected $primaryKey = 'ma_niem_phong';
    public $timestamps = false; // Disable automatic timestamps

    protected $fillable = [
        'ma_niem_phong',
        'phuong_tien_vt_nhap',
        'so_seal',
        'ten_doan_tau',
        'ngay_niem_phong',
        'so_container',
        'ma_cong_chuc',
        'is_go_seal',
    ];
    public function seal()
    {
        return $this->hasOne(Seal::class, 'so_seal', 'so_seal');
    }
    public function container()
    {
        return $this->belongsTo(Container::class, 'so_container', 'so_container');
    }
}
