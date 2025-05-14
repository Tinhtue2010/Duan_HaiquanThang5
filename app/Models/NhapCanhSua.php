<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhapCanhSua extends Model
{
    protected $connection = 'mysql';
    protected $table = 'nhap_canh_sua';
    protected $primaryKey = 'ma_yeu_cau';
    public $timestamps = false;

    protected $fillable = [
        'ma_nhap_canh',
        'ma_doanh_nghiep',
        'ma_cong_chuc',
        'so_ptvt_xuat_canh',
        'so_luong',
        'loai_hang',
        'don_vi_tinh',
        'trong_luong',
        'ten_hang_hoa',
        'ten_chu_hang',
        'dia_chi_chu_hang',
        'ngay_dang_ky',
        'ngay_duyet',
        'trang_thai',
        'ghi_chu',
        'ten_thuyen_truong',
        'is_khong_hang',
    ];

    public function congChuc()
    {
        return $this->belongsTo(CongChuc::class, 'ma_cong_chuc', 'ma_cong_chuc');
    }
    public function doanhNghiep()
    {
        return $this->belongsTo(DoanhNghiep::class, 'ma_doanh_nghiep', 'ma_doanh_nghiep');
    }
    public function PTVTXuatCanh()
    {
        return $this->belongsTo(PTVTXuatCanh::class, 'so_ptvt_xuat_canh', 'so_ptvt_xuat_canh');
    }
}
