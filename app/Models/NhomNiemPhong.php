<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhomNiemPhong extends Model
{
    protected $connection = 'mysql';
    protected $table = 'nhom_niem_phong';
    protected $primaryKey = 'ma_nhom';
    public $timestamps = false; // Disable automatic timestamps

    protected $fillable = [
        'moc_dau',
        'moc_cuoi',
        'tiep_ngu',
        'ngay_them',
    ];

}
