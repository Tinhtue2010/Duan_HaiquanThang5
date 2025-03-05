<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChuHang extends Model
{
    protected $connection = 'mysql';
    protected $table = 'chu_hang';
    protected $primaryKey = 'ma_chu_hang';
    public $incrementing = true; // Ensure this is false if your primary key is not auto-incrementing
    protected $keyType = 'string'; // This is important if the key is a string like '61PA'}
    protected $fillable = [
        'ma_chu_hang',
        'ten_chu_hang',
        'ten_rut_gon',
        'ten_day_du',
        'dia_chi',
    ];

    public function doanhNghiep()
    {
        return $this->hasMany(DoanhNghiep::class, 'ma_chu_hang', 'ma_chu_hang');
    }
}
