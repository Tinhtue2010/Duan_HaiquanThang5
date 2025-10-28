<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChoTKQuaHan extends Model
{
    protected $connection = 'mysql';
    protected $table = 'cho_tk_qua_han';
    protected $primaryKey = 'so_to_khai_nhap';
    public $timestamps = false; // Disable automatic timestamps

    protected $fillable = [
        'so_to_khai_nhap',
    ];
}
