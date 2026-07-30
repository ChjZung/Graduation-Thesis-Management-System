<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonHoc extends Model
{
    protected $table = 'mon_hocs';
    protected $primaryKey = 'MaMon';
    protected $fillable = ['TenMon', 'MaBoMon', 'SoTinChi'];

    public function boMon()
    {
        return $this->belongsTo(BoMon::class, 'MaBoMon', 'MaBoMon');
    }
}
