<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToKhaiPhuongTienVT extends Model
{
    protected $connection = 'mysql';
    protected $table = 'to_khai_phuong_tien_vt';
    protected $primaryKey = 'so_to_khai_ptvt';
    public $timestamps = false;
    protected $fillable = [
        'so_to_khai_ptvt',
        'ngay_dang_ky',
        'ngay_thong_quan',
        'trang_thai',
        'so_ptvt_xuat_canh',
        'ma_doanh_nghiep',
        'updated_at'
    ];
    public function PTVTXuatCanh()
    {
        return $this->belongsTo(PTVTXuatCanh::class, 'so_ptvt_xuat_canh', 'so_ptvt_xuat_canh');
    }
    public function doanhNghiep()
    {
        return $this->belongsTo(DoanhNghiep::class, 'ma_doanh_nghiep', 'ma_doanh_nghiep');
    }
    
}
