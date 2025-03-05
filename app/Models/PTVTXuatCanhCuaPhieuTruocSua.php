<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PTVTXuatCanhCuaPhieuTruocSua extends Model
{
    protected $connection = 'mysql';
    protected $table = 'ptvt_xuat_canh_cua_phieu_truoc_sua';
    protected $primaryKey = 'ma_ptvt_xuat_canh';
    public $timestamps = false;

    protected $fillable = [
        'so_ptvt_xuat_canh',
        'ma_yeu_cau',
    ];
    public function PTVTXuatCanh()
    {
        return $this->belongsTo(PTVTXuatCanh::class, 'so_ptvt_xuat_canh', 'so_ptvt_xuat_canh');
    }
}
