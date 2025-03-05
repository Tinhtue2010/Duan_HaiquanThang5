<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HaiQuan extends Model
{
    protected $connection = 'mysql';

    protected $table = 'hai_quan';
    protected $primaryKey = 'ma_hai_quan'; // Assuming 'ma_hai_quan' is the primary key
    public $incrementing = false; // Ensure this is false if your primary key is not auto-incrementing
    protected $keyType = 'string'; // This is important if the key is a string like '61PA'
    protected $fillable = [
        'ma_hai_quan',
        'ten_hai_quan',
    ];
    public function nhapHang()
    {
        return $this->hasOne(NhapHang::class, 'ma_hai_quan', 'ma_hai_quan');
    }
}
