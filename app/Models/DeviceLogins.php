<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceLogins extends Model
{
    protected $connection = 'mysql';
    protected $table = 'device_logins';
    protected $primaryKey = 'id';
    protected $fillable = [
        'ten_dang_nhap',
        'device',
        'ip_address',
        'platform',
        'browser',
    ];
    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'ten_dang_nhap', 'ten_dang_nhap');
    }

}
