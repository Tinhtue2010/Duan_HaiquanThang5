<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThuKho extends Model
{
    protected $connection = 'mysql';
    protected $table = 'thu_kho';
    protected $primaryKey = 'ma_thu_kho';
    public $incrementing = true; // Ensure this is false if your primary key is not auto-incrementing
    protected $keyType = 'string'; // This is important if the key is a string like '61PA'}
    protected $fillable = [
        'ma_thu_kho',
        'ten_thu_kho',
        'ma_tai_khoan',
        'status',
    ];
    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'ma_tai_khoan', 'ma_tai_khoan');
    }
}
