<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YeuCauContainerHangHoa extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yeu_cau_container_hang_hoa';
    protected $primaryKey = 'ma_yeu_cau_hang_hoa ';
    public $timestamps = false; // Disable automatic timestamps
    protected $fillable = [
        'ma_hang_cont',
        'ten_hang',
        'so_container_cu',
        'so_container_moi',
        'so_luong',
        'ma_chi_tiet',
    ];
    public function yeuCauContainerChiTiet()
    {
        return $this->belongsTo(YeuCauChuyenContainer::class, 'ma_chi_tiet', 'ma_chi_tiet');
    }
}
