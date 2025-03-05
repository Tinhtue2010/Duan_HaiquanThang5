<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XuatHangChiTietTruocSua extends Model
{
    protected $connection = 'mysql';
    protected $table = 'xuat_hang_chi_tiet_truoc_sua';
    protected $primaryKey = 'ma_chi_tiet'; // Assuming 'ma_hai_quan' is the primary key
    public $timestamps = false; // Disable automatic timestamps

    protected $fillable = [
        'so_to_khai_xuat',
        'ma_hang_cont',
        'so_luong_xuat',
        'so_luong_ton',
        'so_container',
        'tri_gia',
        'so_ptvt_xuat_canh',
        'ma_yeu_cau'
    ];
    public function suaXuatHang()
    {
        return $this->belongsTo(XuatHangSua::class, 'ma_yeu_cau', 'ma_yeu_cau');
    }
    public function hangTrongCont()
    {
        return $this->belongsTo(HangTrongCont::class, 'ma_hang_cont', 'ma_hang_cont');
    }
    public function PTVTXuatCanh()
    {
        return $this->belongsTo(PTVTXuatCanh::class, 'so_ptvt_xuat_canh', 'so_ptvt_xuat_canh');
    }
}
