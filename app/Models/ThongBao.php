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
        'MaGV',
        'FileDinhKem',
    ];

    public $timestamps = true;

    public function scopeAttachmentFilter(\Illuminate\Database\Eloquent\Builder $query, mixed $filter): \Illuminate\Database\Eloquent\Builder
    {
        if ($filter === 'with') {
            $query->whereNotNull('FileDinhKem')->whereRaw("TRIM(FileDinhKem) <> ''");
        } elseif ($filter === 'without') {
            $query->where(fn ($q) => $q->whereNull('FileDinhKem')->orWhereRaw("TRIM(FileDinhKem) = ''"));
        }
        return $query;
    }

    public function giaoVu()
    {
        return $this->belongsTo(GiaoVu::class, 'MaGVu', 'MaGVu');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }
}

