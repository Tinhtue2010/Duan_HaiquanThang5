<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LienHe extends Model
{
    protected $connection = 'mysql';
    protected $table = 'lien_he';
    protected $primaryKey = 'ma_lien_he'; 
    protected $fillable = [
        'ten_ca_nhan',
        'email',
        'loi_nhan',
        'ngay_tao',
    ];
}
