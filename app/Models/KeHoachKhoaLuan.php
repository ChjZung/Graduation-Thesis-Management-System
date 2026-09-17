<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeHoachKhoaLuan extends Model
{
    use HasFactory;

    protected $table = 'KeHoachKhoaLuan';
    protected $primaryKey = 'MakeHoach';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MakeHoach',
        'TenKeHoach',
        'NoiDung',
        'TrangThai',
        'NgayTao',
        'MaHocKy',
        'MaGVu',
    ];

    public $timestamps = true;

    // Backward compatibility for MaKeHoach
    public function getMaKeHoachAttribute()
    {
        return $this->attributes['MakeHoach'] ?? null;
    }

    public function setMaKeHoachAttribute($value)
    {
        $this->attributes['MakeHoach'] = $value;
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }

    public function giaoVu()
    {
        return $this->belongsTo(GiaoVu::class, 'MaGVu', 'MaGVu');
    }

    public function mocThoiGians()
    {
        return $this->hasMany(MocThoiGianKhoaLuan::class, 'MakeHoach', 'MakeHoach');
    }

    public function quyDinhs()
    {
        return $this->hasMany(QuyDinhKhoaLuan::class, 'MakeHoach', 'MakeHoach');
    }

    public function bieuMaus()
    {
        return $this->hasMany(BieuMau::class, 'MakeHoach', 'MakeHoach');
    }
}
