<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhapHang extends Model
{
    protected $connection = 'mysql';
    protected $table = 'nhap_hang';
    protected $primaryKey = 'so_to_khai_nhap';
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
    public function chuHang()
    {
        return $this->belongsTo(ChuHang::class, 'ma_chu_hang', 'ma_chu_hang');
    }
    public function congChuc()
    {
        return $this->belongsTo(CongChuc::class, 'ma_cong_chuc', 'ma_cong_chuc');
    }
    public function congChucBanGiao()
    {
        return $this->belongsTo(CongChuc::class, 'ma_cong_chuc_ban_giao', 'ma_cong_chuc');
    }
    public function hangHoa()
    {
        return $this->hasMany(HangHoa::class, 'so_to_khai_nhap', 'so_to_khai_nhap');
    }
    public function xuatHang()
    {
        return $this->hasMany(XuatHang::class, 'so_to_khai_nhap', 'so_to_khai_nhap');
    }
    public function tienTrinh()
    {
        return $this->hasMany(TienTrinh::class, 'so_to_khai_nhap', 'so_to_khai_nhap');
    }
    public function haiQuan()
    {
        return $this->belongsTo(HaiQuan::class, 'ma_hai_quan', 'ma_hai_quan');
    }
    public function doanhNghiep()
    {
        return $this->belongsTo(DoanhNghiep::class, 'ma_doanh_nghiep', 'ma_doanh_nghiep');
    }
    public function container()
    {
        return $this->belongsTo(Container::class, 'container_ban_dau', 'so_container');
    }
    public function chiTietYeuCauKiemTra()
    {
        return $this->hasMany(YeuCauKiemTraChiTiet::class, 'so_to_khai_nhap', 'so_to_khai_nhap');
    }
    public function chiTietYeuCauHangVeKho()
    {
        return $this->hasMany(YeuCauHangVeKhoChiTiet::class, 'so_to_khai_nhap', 'so_to_khai_nhap');
    }
}
