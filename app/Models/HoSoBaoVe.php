<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoSoBaoVe extends Model
{
    use HasFactory;

    protected $table = 'HoSoBaoVe';
    protected $primaryKey = 'MaHoSo';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaHoSo',
        'NgayLap',
        'NgayNop',
        'NgayXacNhan',
        'XacNhanGVHD',
        'ThoiGianBaoVe',
        'PhongBaoVe',
        'TrangThai',
        'GhiChu',
        'MaGVu',
        'MaHoiDong',
        'MaNhom',
        'MaDeTai',
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'NgayLap' => 'date',
            'NgayNop' => 'date',
            'NgayXacNhan' => 'date',
            'ThoiGianBaoVe' => 'datetime',
        ];
    }

    public function giaoVu()
    {
        return $this->belongsTo(GiaoVu::class, 'MaGVu', 'MaGVu');
    }

    public function hoiDong()
    {
        return $this->belongsTo(HoiDong::class, 'MaHoiDong', 'MaHoiDong');
    }

    public function nhom()
    {
        return $this->belongsTo(Nhom::class, 'MaNhom', 'MaNhom');
    }

    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }

    public function tepHoSoBaoVes()
    {
        return $this->hasMany(TepHoSoBaoVe::class, 'MaHoSo', 'MaHoSo');
    }

    public function phieuChamDiems()
    {
        return $this->hasMany(PhieuChamDiem::class, 'MaHoSo', 'MaHoSo');
    }

    public function phanCongPhanBien()
    {
        return $this->hasOne(PhanCongPhanBien::class, 'MaDeTai', 'MaDeTai');
    }

    public function giangVienPhanBien()
    {
        return $this->hasOneThrough(GiangVien::class, PhanCongPhanBien::class, 'MaDeTai', 'MaGV', 'MaDeTai', 'MaGV');
    }

    public function getMaGVPhanBienAttribute()
    {
        return $this->phanCongPhanBien?->MaGV;
    }

    public function getTyLeTrungLapAttribute()
    {
        if (preg_match('/(\d+(\.\d+)?)%/', $this->GhiChu ?? '', $matches)) {
            return (float)$matches[1];
        }
        return null;
    }

    public function getMinhChungDaoVanAttribute()
    {
        return $this->tepHoSoBaoVes->first(function ($tep) {
            $loai = mb_strtolower($tep->LoaiTep ?? '');
            $ten = mb_strtolower($tep->TenTep ?? '');
            return str_contains($loai, 'minh chứng') || str_contains($loai, 'turnitin') || str_contains($ten, 'turnitin');
        })?->DuongDanFile;
    }

    public function getToanVanKhoaLuanAttribute()
    {
        return $this->tepHoSoBaoVes->first(function ($tep) {
            $loai = mb_strtolower($tep->LoaiTep ?? '');
            $ten = mb_strtolower($tep->TenTep ?? '');
            return str_contains($loai, 'toàn văn') || str_contains($loai, 'báo cáo') || str_contains($ten, 'toanvan');
        })?->DuongDanFile;
    }
}
