<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PTVTXuatCanhCuaPhieuSua extends Model
{
    protected $connection = 'mysql';
    protected $table = 'ptvt_xuat_canh_cua_phieu_sua';
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
    public function xuatHang()
    {
        return $this->belongsTo(XuatHang::class, 'so_to_khai_xuat', 'so_to_khai_xuat');
    }
}
