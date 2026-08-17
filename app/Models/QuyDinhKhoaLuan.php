<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuyDinhKhoaLuan extends Model
{
    use HasFactory;

    protected $table = 'quy_dinh_khoa_luans';
    protected $primaryKey = 'MaQuyDinh';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaQuyDinh', 'MaKeHoach', 'TenQuyDinh', 'GiaTri', 'MoTa'
    ];

    public $timestamps = true;
}
