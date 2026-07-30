<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhanXet extends Model
{
    protected $table = 'nhan_xets';
    protected $primaryKey = 'MaNhanXet';
    public $timestamps = false;

    protected $fillable = [
        'MaBaoCao',
        'MaGV',
        'NoiDung',
        'NgayNhanXet'
    ];

    public function baoCao()
    {
        return $this->belongsTo(BaoCaoTienDo::class, 'MaBaoCao', 'MaBaoCao');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }
}
