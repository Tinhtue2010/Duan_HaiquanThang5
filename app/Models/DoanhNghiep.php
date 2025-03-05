<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoanhNghiep extends Model
{
    protected $connection = 'mysql';
    protected $table = 'doanh_nghiep';
    protected $primaryKey = 'ma_doanh_nghiep';
    public $incrementing = false; // Ensure this is false if your primary key is not auto-incrementing
    protected $keyType = 'string'; // This is important if the key is a string like '61PA'
    protected $fillable = [
        'ma_doanh_nghiep',
        'ten_doanh_nghiep',
        'dia_chi',
        'ma_chu_hang',
        'ma_tai_khoan',
    ];
    
    public function nhapHang()
    {
        return $this->hasOne(NhapHang::class, 'ma_doanh_nghiep', 'ma_doanh_nghiep');
    }
    public function doanhNghiepQL()
    {
        return $this->hasMany(DoanhNghiepQL::class, 'ma_doanh_nghiep_ql', 'ma_doanh_nghiep');
    }
    public function doanhNghiepKhac()
    {
        return $this->hasMany(DoanhNghiepQL::class, 'ma_doanh_nghiep_khac', 'ma_doanh_nghiep');
    }
    public function PTVTXuatCanh()
    {
        return $this->hasMany(PTVTXuatCanh::class, 'ma_doanh_nghiep', 'ma_doanh_nghiep');
    }
    public function chuHang()
    {
        return $this->belongsTo(ChuHang::class, 'ma_chu_hang', 'ma_chu_hang');
    }
    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'ma_tai_khoan', 'ma_tai_khoan');
    }

}
