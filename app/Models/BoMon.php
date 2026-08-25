<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoMon extends Model
{
    use HasFactory;

    protected $table = 'bo_mons';
    protected $primaryKey = 'MaBoMon';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaBoMon', 'TenBoMon', 'MaKhoa'
    ];

    public $timestamps = true;

    public function khoa()
    {
        return $this->belongsTo(Khoa::class, 'MaKhoa', 'MaKhoa');
    }

    public function giangViens()
    {
        return $this->hasMany(GiangVien::class, 'MaBoMon', 'MaBoMon');
    }
}
