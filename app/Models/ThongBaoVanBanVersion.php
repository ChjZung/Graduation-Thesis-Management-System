<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongBaoVanBanVersion extends Model
{
    use HasFactory;

    protected $table = 'thong_bao_van_ban_versions';

    protected $fillable = [
        'MaVanBan',
        'PhienBan',
        'FileGocPath',
        'NoiDungTrichXuatJson',
        'MocThoiGianJson',
        'ThayDoiSoVoiTruocJson',
        'LyDoThayDoi',
        'NguoiThayDoi',
    ];

    protected $casts = [
        'NoiDungTrichXuatJson'  => 'array',
        'MocThoiGianJson'        => 'array',
        'ThayDoiSoVoiTruocJson'  => 'array',
    ];

    public function vanBan()
    {
        return $this->belongsTo(ThongBaoVanBan::class, 'MaVanBan', 'MaVanBan');
    }
}
