<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoanhNghiepQL extends Model
{
    protected $connection = 'mysql';
    protected $table = 'doanh_nghiep_ql';
    protected $primaryKey = 'ma_quan_ly';
    protected $fillable = [
        'ma_doanh_nghiep_ql',
        'ma_doanh_nghiep_khac',
    ];
    public function doanhNghiepQL()
    {
        return $this->belongsTo(DoanhNghiep::class, 'ma_doanh_nghiep_ql', 'ma_doanh_nghiep');
    }
    public function doanhNghiepKhac()
    {
        return $this->belongsTo(DoanhNghiep::class, 'ma_doanh_nghiep_khac', 'ma_doanh_nghiep');
    }
}
