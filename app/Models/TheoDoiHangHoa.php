<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TheoDoiHangHoa extends Model
{
    protected $connection = 'mysql';
    protected $table = 'theo_doi_hang_hoa';
    protected $primaryKey = 'ma_theo_doi';
    public $timestamps = false; // Disable automatic timestamps
    protected $fillable = [
        'so_to_khai_nhap',
        'ma_hang',
        'thoi_gian',
        'so_luong_xuat',
        'so_luong_ton',
        'phuong_tien_cho_hang',
        'cong_viec',
        'phuong_tien_nhan_hang',
        'so_container',
        'so_seal',
        'ma_cong_chuc',
        'ghi_chu',
        'ma_yeu_cau',
    ];
    public function congChuc()
    {
        return $this->belongsTo(CongChuc::class, 'ma_cong_chuc', 'ma_cong_chuc');
    }
}
