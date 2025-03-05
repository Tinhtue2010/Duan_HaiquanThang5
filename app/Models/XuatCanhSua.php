<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XuatCanhSua extends Model
{
    protected $connection = 'mysql';
    protected $table = 'xuat_canh_sua';
    protected $primaryKey = 'ma_yeu_cau';
    public $timestamps = false; // Disable automatic timestamps

    protected $fillable = [
        'ma_doanh_nghiep',
        'ma_cong_chuc',
        'so_ptvt_xuat_canh',
        'ngay_dang_ky',
        'ngay_duyet',
        'trang_thai',
        'ghi_chu',
        'ma_doanh_nghiep_chon',
        'ten_thuyen_truong',
        'ma_xuat_canh',
    ];

    public function congChuc()
    {
        return $this->belongsTo(CongChuc::class, 'ma_cong_chuc', 'ma_cong_chuc');
    }
    public function doanhNghiep()
    {
        return $this->belongsTo(DoanhNghiep::class, 'ma_doanh_nghiep', 'ma_doanh_nghiep');
    }
    public function doanhNghiepChon()
    {
        return $this->belongsTo(DoanhNghiep::class, 'ma_doanh_nghiep_chon', 'ma_doanh_nghiep');
    }
    public function PTVTXuatCanh()
    {
        return $this->belongsTo(PTVTXuatCanh::class, 'so_ptvt_xuat_canh', 'so_ptvt_xuat_canh');
    }
    public function XuatCanhChiTiet()
    {
        return $this->hasMany(XuatCanhChiTiet::class, 'ma_xuat_canh', 'ma_xuat_canh');
    }
}
