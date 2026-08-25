<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongBaoVanBan extends Model
{
    use HasFactory;

    protected $table = 'thong_bao_van_bans';
    protected $primaryKey = 'MaVanBan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaVanBan',
        'MaKeHoach',
        'TenVanBan',
        'SoThongBao',
        'NgayBanHanh',
        'FileGocPath',
        'FileType',
        'FileSize',
        'PhienBanHienTai',
        'NguoiUpload',
    ];

    public function keHoach()
    {
        return $this->belongsTo(KeHoachKhoaLuan::class, 'MaKeHoach', 'MaKeHoach');
    }

    public function versions()
    {
        return $this->hasMany(ThongBaoVanBanVersion::class, 'MaVanBan', 'MaVanBan')->orderBy('PhienBan', 'desc');
    }
}
