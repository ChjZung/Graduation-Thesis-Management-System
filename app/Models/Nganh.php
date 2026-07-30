<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nganh extends Model
{
    protected $table = 'nganhs';
    protected $primaryKey = 'MaNganh';
    protected $fillable = ['TenNganh', 'MoTa'];
}
