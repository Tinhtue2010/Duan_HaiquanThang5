<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauGoSeal extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_go_seal';
    protected $primaryKey = 'ma_yeu_cau';
    public $timestamps = false; // Disable automatic timestamps
    protected $fillable = [
        'ma_doanh_nghiep',
        'trang_thai',
        'ngay_yeu_cau',
        'ngay_hoan_thanh',
        'ghi_chu',
        'ma_cong_chuc'
    ];

    public function congChuc()
    {
        return $this->belongsTo(CongChuc::class, 'ma_cong_chuc', 'ma_cong_chuc');
    }
}
