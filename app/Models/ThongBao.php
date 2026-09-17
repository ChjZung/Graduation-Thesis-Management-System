<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongBao extends Model
{
    use HasFactory;

    protected $table = 'ThongBao';
    protected $primaryKey = 'MaThongBao';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaThongBao',
        'TieuDe',
        'NoiDung',
        'LoaiThongBao',
        'DoiTuongNhan',
        'NgayTao',
        'TrangThai',
        'MaGVu',
    ];

    public $timestamps = true;

    public function giaoVu()
    {
        return $this->belongsTo(GiaoVu::class, 'MaGVu', 'MaGVu');
    }
}
