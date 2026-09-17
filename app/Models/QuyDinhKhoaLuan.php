<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuyDinhKhoaLuan extends Model
{
    use HasFactory;

    protected $table = 'QuyDinhKhoaLuan';
    protected $primaryKey = 'MaQuyDinh';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaQuyDinh',
        'TenQuyDinh',
        'GiaTri',
        'MoTa',
        'MakeHoach',
    ];

    public $timestamps = true;

    public function keHoachKhoaLuan()
    {
        return $this->belongsTo(KeHoachKhoaLuan::class, 'MakeHoach', 'MakeHoach');
    }
}
