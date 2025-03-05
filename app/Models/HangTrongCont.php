<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HangTrongCont extends Model
{
    protected $connection = 'mysql';
    protected $table = 'hang_trong_cont';
    protected $primaryKey = 'ma_hang_cont';
    public $timestamps = false; // Disable automatic timestamps
    protected $fillable = [
        'ma_hang',
        'so_container',
        'so_luong',
        'is_da_chuyen_cont',
    ];
    public function hangHoa()
    {
        return $this->belongsTo(HangHoa::class, 'ma_hang', 'ma_hang');
    }
    public function xuatHangCont()
    {
        return $this->hasOne(XuatHangCont::class, 'ma_hang_cont', 'ma_hang_cont');
    }
}
