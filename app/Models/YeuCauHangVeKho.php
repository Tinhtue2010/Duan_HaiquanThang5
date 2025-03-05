<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauHangVeKho extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_hang_ve_kho';
    protected $primaryKey = 'ma_yeu_cau';
    public $timestamps = false; // Disable automatic timestamps
    protected $fillable = [
        'ma_doanh_nghiep',
        'trang_thai',
        'ngay_yeu_cau',
        'ngay_hoan_thanh',
        'ghi_chu',
        'ma_cong_chuc',
        'file_name',
        'file_path'
    ];
    public function chiTietYeuCau()
    {
        return $this->hasMany(YeuCauHangVeKhoChiTiet::class, 'ma_yeu_cau', 'ma_yeu_cau');
    }
    public function doanhNghiep()
    {
        return $this->belongsTo(DoanhNghiep::class, 'ma_doanh_nghiep', 'ma_doanh_nghiep');
    }
    public function congChuc()
    {
        return $this->belongsTo(CongChuc::class, 'ma_cong_chuc', 'ma_cong_chuc');
    }
    public function PTVTXuatCanh()
    {
        return $this->belongsTo(PTVTXuatCanh::class, 'so_ptvt_xuat_canh', 'so_ptvt_xuat_canh');
    }
}
