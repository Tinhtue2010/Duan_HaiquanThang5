<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $connection = 'mysql';
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
            'password' => 'hashed',
        ];
    }

    public function chuHang()
    {
        return $this->hasOne(ChuHang::class, 'ma_tai_khoan', 'ma_tai_khoan');
    }

    public function congChuc()
    {
        return $this->hasOne(CongChuc::class, 'ma_tai_khoan', 'ma_tai_khoan');
    }

    public function isCongChuc(): bool
    {
        return $this->loai_tai_khoan === 'Cán bộ công chức';
    }
}
