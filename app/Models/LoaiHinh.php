<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoaiHinh extends Model
{
    protected $connection = 'mysql';
    protected $table = 'loai_hinh';
    protected $primaryKey = 'ma_loai_hinh';
    public $timestamps = false; // Disable automatic timestamps
    public $incrementing = false; // Ensure this is false if your primary key is not auto-incrementing
    protected $keyType = 'string'; // This is important if the key is a string like '61PA'}
    protected $fillable = [
        'ma_loai_hinh',
        'ten_loai_hinh',
        'loai'
    ];
    public function xuatHang()
    {
        return $this->hasMany(XuatHang::class, 'ma_loai_hinh', 'ma_loai_hinh');
    }
}
