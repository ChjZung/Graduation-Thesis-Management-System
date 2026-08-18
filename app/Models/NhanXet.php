<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NhanXet extends Model
{
    use HasFactory;

    protected $table = 'nhan_xets';
    protected $primaryKey = 'MaNhanXet';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaNhanXet', 'MaBaoCao', 'MaGV',
        'NoiDung', 'LoaiNhanXet', 'NgayNhanXet', 'TrangThai',
    ];

    protected $casts = [
        'NgayNhanXet' => 'datetime',
    ];

    public $timestamps = true;

    public function baoCao()
    {
        return $this->belongsTo(BaoCaoTienDo::class, 'MaBaoCao', 'MaBaoCao');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }
}
