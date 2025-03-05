<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seal extends Model
{
    protected $connection = 'mysql';
    protected $table = 'seal';
    protected $primaryKey = 'so_seal';
    public $timestamps = false; // Disable automatic timestamps
    public $incrementing = false; // Ensure this is false if your primary key is not auto-incrementing
    protected $keyType = 'string'; // This is important if the key is a string like '61PA'}
    protected $fillable = [
        'so_seal',
        'ngay_cap',
        'ngay_su_dung',
        'so_container',
        'ma_cong_chuc',
        'loai_seal',
        'trang_thai',
    ];
    public function niemPhong()
    {
        return $this->hasOne(NiemPhong::class, 'so_seal', 'so_seal');
    }
    public function congChuc()
    {
        return $this->belongsTo(CongChuc::class, 'ma_cong_chuc', 'ma_cong_chuc');
    }

}
