<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MocThoiGianKhoaLuan extends Model
{
    use HasFactory;

    protected $table = 'MocThoiGianKhoaLuan';
    protected $primaryKey = 'MaMoc';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaMoc',
        'TenMoc',
        'NgayBatDau',
        'NgayKetThuc',
        'MoTa',
        'MakeHoach',
    ];

    public $timestamps = true;

    protected $appends = [
        'LoaiGiaiDoan',
        'DoiTuongThucHien',
    ];

    public function getLoaiGiaiDoanAttribute(): string
    {
        if (isset($this->attributes['LoaiGiaiDoan']) && !empty($this->attributes['LoaiGiaiDoan'])) {
            return $this->attributes['LoaiGiaiDoan'];
        }

        // 1. Kiểm tra mã giai đoạn gắn trong MoTa: e.g. [BAO_CAO_TIEN_DO_1]
        if (!empty($this->MoTa) && preg_match('/\[([A-Z0-9_]+)\]/', $this->MoTa, $m)) {
            return $m[1];
        }

        // 2. Nhận diện tự động dựa theo từ khóa trong TenMoc
        $nd = mb_strtolower($this->TenMoc ?? '');
        if (str_contains($nd, 'tạo nhóm') || str_contains($nd, 'lập nhóm')) return 'TAO_NHOM';
        if (str_contains($nd, 'đăng ký đề tài')) return 'DANG_KY_DE_TAI';
        if (str_contains($nd, 'ngoại lệ') || str_contains($nd, 'xử lý')) return 'XU_LY_NGOAI_LE';
        if (str_contains($nd, 'công bố') && (str_contains($nd, 'gvhd') || str_contains($nd, 'danh sách'))) return 'CONG_BO_GVHD';
        if (str_contains($nd, 'liên hệ gvhd') || str_contains($nd, 'gặp gvhd')) return 'LIEN_HE_GVHD';
        if (str_contains($nd, 'đợt 1') || str_contains($nd, 'tiến độ 1') || str_contains($nd, 'lần 1')) return 'BAO_CAO_TIEN_DO_1';
        if (str_contains($nd, 'đợt 2') || str_contains($nd, 'tiến độ 2') || str_contains($nd, 'lần 2')) return 'BAO_CAO_TIEN_DO_2';
        if (str_contains($nd, 'đợt 3') || str_contains($nd, 'tiến độ 3') || str_contains($nd, 'lần 3')) return 'BAO_CAO_TIEN_DO_3';
        if (str_contains($nd, 'turnitin') || str_contains($nd, 'đạo văn')) return 'DAO_VAN';
        if (str_contains($nd, 'xác nhận') && (str_contains($nd, 'gvhd') || str_contains($nd, 'hướng dẫn'))) return 'GVHD_XAC_NHAN';
        if (str_contains($nd, 'bắt đầu thực hiện') || str_contains($nd, 'đề cương')) return 'THUC_HIEN';
        if (str_contains($nd, 'thực hiện')) return 'THUC_HIEN';
        if (str_contains($nd, 'nộp báo cáo') || str_contains($nd, 'nộp đồ án') || str_contains($nd, 'nộp khóa luận') || str_contains($nd, 'hồ sơ')) return 'NOP_BAO_CAO';
        if (str_contains($nd, 'hội đồng') || str_contains($nd, 'bảo vệ')) return 'BAO_VE';

        return 'THUC_HIEN';
    }

    public function getDoiTuongThucHienAttribute(): string
    {
        if (isset($this->attributes['DoiTuongThucHien']) && !empty($this->attributes['DoiTuongThucHien'])) {
            return $this->attributes['DoiTuongThucHien'];
        }

        $nd = mb_strtolower($this->TenMoc ?? '');
        if (str_contains($nd, 'sinh viên') && (str_contains($nd, 'gvhd') || str_contains($nd, 'giảng viên'))) {
            return 'Sinh viên & GVHD';
        }
        if (str_contains($nd, 'trưởng nhóm') || str_contains($nd, 'nhóm trưởng')) {
            return 'Nhóm trưởng';
        }
        if (str_contains($nd, 'sinh viên') || str_contains($nd, 'tạo nhóm') || str_contains($nd, 'đăng ký đề tài')) {
            return 'Sinh viên';
        }
        if (str_contains($nd, 'giảng viên') || str_contains($nd, 'gvhd') || str_contains($nd, 'xác nhận')) {
            return 'Giảng viên hướng dẫn';
        }
        if (str_contains($nd, 'hội đồng') || str_contains($nd, 'bảo vệ')) {
            return 'Hội đồng & Sinh viên';
        }

        return 'Khoa / Giáo vụ';
    }

    public function keHoachKhoaLuan()
    {
        return $this->belongsTo(KeHoachKhoaLuan::class, 'MakeHoach', 'MakeHoach');
    }

    public function keHoach()
    {
        return $this->keHoachKhoaLuan();
    }

    public function baoCaoTienDos()
    {
        return $this->hasMany(BaoCaoTienDo::class, 'MaMoc', 'MaMoc');
    }
}
