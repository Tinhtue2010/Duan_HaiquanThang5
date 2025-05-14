<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauChuyenContainer extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_chuyen_container';
    protected $primaryKey = 'ma_yeu_cau';
    public $timestamps = false; // Disable automatic timestamps
    protected $fillable = [
        'ma_doanh_nghiep',
        'ten_doan_tau',
        'trang_thai',
        'ngay_yeu_cau',
        'ngay_hoan_thanh',
        'ma_cong_chuc',
        'ghi_chu',
        'file_name',
        'file_path',
    ];



    public function doanhNghiep()
    {
        return $this->belongsTo(DoanhNghiep::class, 'ma_doanh_nghiep', 'ma_doanh_nghiep');
    }
    public function congChuc()
    {
        return $this->belongsTo(CongChuc::class, 'ma_cong_chuc', 'ma_cong_chuc');
    }
    public function yeuCauContainerChiTiet()
    {
        return $this->hasMany(YeuCauContainerChiTiet::class, 'ma_yeu_cau', 'ma_yeu_cau');
    }

}
