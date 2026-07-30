<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoMon extends Model
{
    protected $table = 'bo_mons';
    protected $primaryKey = 'MaBoMon';
    protected $fillable = ['TenBoMon', 'MoTa'];
}
