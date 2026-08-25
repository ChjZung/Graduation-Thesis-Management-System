<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThongBao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'thong_baos';
    protected $primaryKey = 'MaThongBao';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaThongBao',
        'MaGVu',
        'TieuDe',
        'NoiDung',
        'LoaiThongBao',
        'DoiTuongNhan',
        'FileDinhKem',
        'MaKeHoach',
        'MaMoc',
        'NgayTao',
        'NgayGui',
        'TrangThai',
    ];

    public $timestamps = true;

    public function giaoVu()
    {
        return $this->belongsTo(GiaoVu::class, 'MaGVu', 'MaGVu');
    }

    public function keHoach()
    {
        return $this->belongsTo(KeHoachKhoaLuan::class, 'MaKeHoach', 'MaKeHoach')->withDefault();
    }

    public function mocThoiGian()
    {
        return $this->belongsTo(MocThoiGianKhoaLuan::class, 'MaMoc', 'MaMoc')->withDefault();
    }

    public function nguoiNhans()
    {
        return $this->hasMany(NguoiNhanThongBao::class, 'MaThongBao', 'MaThongBao');
    }

    public function lop()
    {
        return $this->belongsTo(Lop::class, 'MaLop', 'MaLop')->withDefault();
    }

    public function lopHocPhan()
    {
        return $this->belongsTo(LopHocPhan::class, 'MaLopHP', 'MaLopHP')->withDefault();
    }
}
