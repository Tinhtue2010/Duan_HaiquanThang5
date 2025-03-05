<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\Access\Authorizable;             // <-- add import

class TaiKhoan extends Authenticatable
{
    use HasFactory, Notifiable, Authorizable;
    
    protected $connection = 'mysql';
    protected $table = 'tai_khoan';
    protected $primaryKey = 'ma_tai_khoan';
    protected $fillable = [
        'ten_dang_nhap',
        'mat_khau',
        'loai_tai_khoan',
    ];
    protected $hidden = [
        'mat_khau',
        'remember_token',
    ];
    protected function casts(): array
{
    return [
        'mat_khau' => 'hashed',
    ];
}
    public function getAuthPassword()
    {
        return $this->mat_khau;
    }
    public function chuHang()
    {
        return $this->hasOne(ChuHang::class, 'ma_tai_khoan', 'ma_tai_khoan');
    }
    public function congChuc()
    {
        return $this->hasOne(CongChuc::class, 'ma_tai_khoan', 'ma_tai_khoan');
    }
    public function thuKho()
    {
        return $this->hasOne(ThuKho::class, 'ma_tai_khoan', 'ma_tai_khoan');
    }
    public function doanhNghiep()
    {
        return $this->hasOne(DoanhNghiep::class, 'ma_tai_khoan', 'ma_tai_khoan');
    }
    public function isCongChuc(): bool
    {
        return $this->loai_tai_khoan === 'Cán bộ công chức';
    }
}
