<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhapHangSua extends Model
{
    protected $connection = 'mysql';
    protected $table = 'nhap_hang_sua';
    protected $primaryKey = 'so_to_khai_nhap'; // Specify your custom primary key
    protected $casts = [
        'so_to_khai_nhap' => 'string'
    ];
    protected $fillable = [
        'so_to_khai_nhap',
        'ma_chu_hang',
        'ma_hai_quan',
        'ma_doanh_nghiep',
        'ma_loai_hinh',
        'ngay_thong_quan',
        'ngay_dang_ky',
        'ngay_xuat_het',
        'so_ngay_gia_han',
        'trang_thai',
        'ghi_chu',
        'container_ban_dau',
        'phuong_tien_vt_nhap',
        'trong_luong',
        'ptvt_ban_dau',
        'ma_cong_chuc',
        'ma_cong_chuc_ban_giao',
        'created_at',
    ];
    public function hangHoa()
    {
        return $this->hasMany(HangHoa::class, 'so_to_khai_nhap', 'so_to_khai_nhap');
    }
}
