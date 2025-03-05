<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    protected $connection = 'mysql';
    public $timestamps = false; // Disable automatic timestamps

    protected $table = 'container';
    protected $primaryKey = 'so_container'; // Assuming 'ma_hai_quan' is the primary key
    public $incrementing = false; // Ensure this is false if your primary key is not auto-incrementing
    protected $keyType = 'string'; // This is important if the key is a string like '61PA'}
    protected $fillable = [
        'so_container',
    ];
    public function niemPhong()
    {
        return $this->hasOne(NiemPhong::class, 'so_container', 'so_container');
    }
    public function nhapHang()
    {
        return $this->hasMany(NhapHang::class, 'so_container', 'so_container');
    }

}