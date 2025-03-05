<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThuyenTruong extends Model
{
    protected $connection = 'mysql';
    protected $table = 'thuyen_truong';
    protected $primaryKey = 'ma_thuyen_truong';
    public $incrementing = true; // Ensure this is false if your primary key is not auto-incrementing
    protected $fillable = [
        'ten_thuyen_truong',
    ];
}
