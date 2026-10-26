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
        'FileBanChinhSua',
        'NgayNopBanChinhSua',
        'TrangThai',
        'GhiChu',
        'LinkSourceCode',
        'GhiChuSourceCode',
        'LinkDeploy',
        'GhiChuDeploy',
        'LyDoKhongDeploy',
        'LienKetKhac',
        'PhienBanHienTai',
        'NhanXetGVHD',
        'ThanhPhanCanSua',
        'HanNopLai',
        'NguoiDuyet',
        'NgayNopGanNhat',
        'MaGVu',
        'MaHoiDong',
        'MaNhom',
        'MaDeTai',
        'LoaiKhoaLuan',
        'TieuChiChucNang',
        'TrangThaiChuanBiPhieu',
        'NguoiChuanBiPhieu',
        'NgayChuanBiPhieu',
        'NoiDungDeCuongJson',
        'TrangThaiKetQua',
        'YeuCauChinhSuaSauBaoVe',
        'HanNopBaoCaoSauBaoVe',
        'NguoiKiemTraChinhSua',
        'TrangThaiChinhSuaSauBaoVe',
        'NgayXacNhanHoanThanhSauBaoVe',
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'NgayLap'                      => 'date',
            'NgayNop'                      => 'date',
            'NgayXacNhan'                  => 'date',
            'ThoiGianBaoVe'                => 'datetime',
            'HanNopLai'                    => 'datetime',
            'NgayNopGanNhat'               => 'datetime',
            'HanNopBaoCaoSauBaoVe'         => 'datetime',
            'NgayXacNhanHoanThanhSauBaoVe' => 'datetime',
            'NgayChuanBiPhieu'             => 'datetime',
            'NoiDungDeCuongJson'           => 'array',
            'LienKetKhac'                  => 'array',
            'ThanhPhanCanSua'              => 'array',
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

    public function ketQuaSinhViens()
    {
        return $this->hasMany(KetQuaSinhVien::class, 'MaHoSo', 'MaHoSo');
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

    public function phienBans()
    {
        return $this->hasMany(PhienBanHoSoBaoVe::class, 'MaHoSo', 'MaHoSo')->orderBy('id', 'desc');
    }

    public function phienBanMoiNhat()
    {
        return $this->hasOne(PhienBanHoSoBaoVe::class, 'MaHoSo', 'MaHoSo')->latestOfMany('id');
    }

    public function getToanVanKhoaLuanAttribute()
    {
        if ($this->relationLoaded('phienBanMoiNhat') && $this->phienBanMoiNhat?->DuongDanBaoCao) {
            return $this->phienBanMoiNhat->DuongDanBaoCao;
        }

        return $this->tepHoSoBaoVes->first(function ($tep) {
            $loai = mb_strtolower($tep->LoaiTep ?? '');
            $ten = mb_strtolower($tep->TenTep ?? '');
            return str_contains($loai, 'toàn văn') || str_contains($loai, 'báo cáo') || str_contains($ten, 'toanvan');
        })?->DuongDanFile;
    }

    public function baoCaoSauBaoVes()
    {
        return $this->hasMany(BaoCaoSauBaoVe::class, 'MaHoSo', 'MaHoSo')->orderBy('LanChinhSua', 'desc');
    }

    public function baoCaoSauBaoVeMoiNhat()
    {
        return $this->hasOne(BaoCaoSauBaoVe::class, 'MaHoSo', 'MaHoSo')->latestOfMany('id');
    }

    public function nguoiChuanBi()
    {
        return $this->belongsTo(GiangVien::class, 'NguoiChuanBiPhieu', 'MaGV');
    }

    public function getNganhAttribute(): ?Nganh
    {
        if ($this->nhom?->deTai?->nganh) {
            return $this->nhom->deTai->nganh;
        }
        if ($this->deTai?->nganh) {
            return $this->deTai->nganh;
        }
        if ($this->nhom?->truongNhom?->nganh) {
            return $this->nhom->truongNhom->nganh;
        }
        $firstMember = $this->nhom?->thanhVienNhoms?->firstWhere('TrangThai', 'da_tham_gia')?->sinhVien;
        if ($firstMember?->nganh) {
            return $firstMember->nganh;
        }
        if ($firstMember?->lop?->nganh) {
            return $firstMember->lop->nganh;
        }
        return null;
    }

    public function getMaNganhAttribute(): ?string
    {
        return $this->nganh?->MaNganh ?? $this->nhom?->deTai?->MaNganh ?? $this->nhom?->truongNhom?->MaNganh ?? null;
    }

    public function getTenNganhAttribute(): string
    {
        return $this->nganh?->TenNganh ?? 'Chưa xác định ngành';
    }

    public function xacDinhLoaiKhoaLuan(): ?string
    {
        if (!empty($this->LoaiKhoaLuan)) {
            return $this->LoaiKhoaLuan;
        }

        $tenDeTai = mb_strtolower($this->nhom?->deTai?->TenDeTai ?? $this->deTai?->TenDeTai ?? '');
        $maHP = mb_strtolower($this->nhom?->deTai?->MaHocPhan ?? $this->deTai?->MaHocPhan ?? '');
        $tenHP = mb_strtolower($this->nhom?->deTai?->hocPhanRef?->TenHocPhan ?? $this->deTai?->hocPhanRef?->TenHocPhan ?? '');

        if (str_contains($tenHP, 'cử nhân') || str_contains($maHP, 'klcn') || str_contains($tenDeTai, 'cử nhân') || str_contains($tenDeTai, 'cu nhan')) {
            return 'KLCN';
        }

        if (str_contains($tenHP, 'kỹ sư') || str_contains($maHP, 'klks') || str_contains($tenDeTai, 'kỹ sư') || str_contains($tenDeTai, 'ky su')) {
            return 'KLKS';
        }

        $firstMember = $this->nhom?->thanhViens?->first()?->sinhVien;
        if ($firstMember && $firstMember->MaNganh === '7480103') {
            return 'KLKS';
        }

        if (str_contains($maHP, 'kltn') || str_contains($tenHP, 'tốt nghiệp')) {
            return 'KLCN';
        }

        return null;
    }
}

