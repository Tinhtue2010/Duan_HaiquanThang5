<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoaiHang extends Model
{
    protected $connection = 'mysql';
    public $timestamps = false; // Disable automatic timestamps
    protected $table = 'loai_hang';
    protected $primaryKey = 'ma_loai_hang'; // Assuming 'ma_hai_quan' is the primary key
    protected $fillable = [
        'ten_loai_hang',
        'don_vi_tinh',
    ];
}
