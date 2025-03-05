<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YCTauContMaHangContMoi extends Model
{
    protected $connection = 'mysql';
    protected $table = 'yc_tau_cont_hang_trong_cont_moi';
    protected $primaryKey = 'ma_hang_cont_moi';
    public $timestamps = false;
    protected $fillable = [
        'ma_yeu_cau_hang_hoa',
        'ma_hang_cont',
        'so_luong',
        'loai_cont_moi',
    ];
    public function nhapHang()
    {
        return $this->belongsTo(NhapHang::class, 'so_to_khai_nhap', 'so_to_khai_nhap');
    }
    public function yeuCauChuyenContainer()
    {
        return $this->belongsTo(YeuCauChuyenContainer::class, 'ma_yeu_cau', 'ma_yeu_cau');
    }
    public function yeuCauContainerHangHoa()
    {
        return $this->hasMany(YeuCauContainerHangHoa::class, 'ma_chi_tiet', 'ma_chi_tiet');
    }
}
