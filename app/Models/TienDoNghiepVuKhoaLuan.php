<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TienDoNghiepVuKhoaLuan extends Model
{
    use HasFactory;

    protected $table = 'tiendonghiepvukhoaluan';

    // 6 Giai đoạn nghiệp vụ chuẩn của Giáo vụ theo quy chế Khoa
    public const GIAI_DOAN_DE_XUAT_DE_TAI       = 1;
    public const GIAI_DOAN_DANG_KY_DE_TAI       = 2;
    public const GIAI_DOAN_THUC_HIEN_DE_CUONG   = 3;
    public const GIAI_DOAN_PHE_DUYET_DE_CUONG   = 4;
    public const GIAI_DOAN_THUC_HIEN_KHOA_LUAN  = 5;
    public const GIAI_DOAN_HOAN_THIEN_VA_BAO_VE = 6;

    public const DANH_SACH_GIAI_DOAN = [
        self::GIAI_DOAN_DE_XUAT_DE_TAI => [
            'ten' => 'Giai đoạn 1: Đề xuất đề tài',
            'short' => 'Đề xuất đề tài',
            'mo_ta' => 'Giảng viên đề xuất đề tài; Trưởng Bộ môn và Ban Chủ nhiệm Khoa duyệt và công bố danh mục đề tài.',
            'dieu_kien_bat_dau' => 'Kế hoạch học kỳ mở đợt đề xuất đề tài.',
            'dieu_kien_hoan_thanh' => 'Đề tài có trạng thái "Đã công bố" hoặc "Đã duyệt".',
            'icon' => 'fa-lightbulb',
            'color' => '#0072ce',
        ],
        self::GIAI_DOAN_DANG_KY_DE_TAI => [
            'ten' => 'Giai đoạn 2: Đăng ký đề tài',
            'short' => 'Đăng ký đề tài',
            'mo_ta' => 'Nhóm sinh viên đủ điều kiện làm khóa luận đăng ký đề tài; GVHD và Giáo vụ xác nhận phân công.',
            'dieu_kien_bat_dau' => 'Sinh viên có tên trong danh sách đủ điều kiện và đề tài đã được công bố.',
            'dieu_kien_hoan_thanh' => 'Phiếu đăng ký đề tài được duyệt, nhóm gắn chính thức với đề tài và GVHD.',
            'icon' => 'fa-clipboard-check',
            'color' => '#10b981',
        ],
        self::GIAI_DOAN_THUC_HIEN_DE_CUONG => [
            'ten' => 'Giai đoạn 3: Thực hiện đề cương',
            'short' => 'Thực hiện đề cương',
            'mo_ta' => 'Nhóm sinh viên xây dựng đề cương chi tiết dưới sự hướng dẫn của GVHD và nộp hồ sơ đề cương.',
            'dieu_kien_bat_dau' => 'Hoàn tất phân công đề tài.',
            'dieu_kien_hoan_thanh' => 'Nhóm nộp đầy đủ file đề cương chi tiết (PDF) có chữ ký xác nhận của GVHD.',
            'icon' => 'fa-file-lines',
            'color' => '#f59e0b',
        ],
        self::GIAI_DOAN_PHE_DUYET_DE_CUONG => [
            'ten' => 'Giai đoạn 4: Phê duyệt đề cương',
            'short' => 'Phê duyệt đề cương',
            'mo_ta' => 'Trưởng Bộ môn phân công cán bộ phản biện; cán bộ phản biện đánh giá và Bộ môn phê duyệt đề cương.',
            'dieu_kien_bat_dau' => 'File đề cương chi tiết đã được nộp.',
            'dieu_kien_hoan_thanh' => 'Cán bộ phản biện đánh giá đạt và Bộ môn ra quyết định phê duyệt đề cương.',
            'icon' => 'fa-user-check',
            'color' => '#8b5cf6',
        ],
        self::GIAI_DOAN_THUC_HIEN_KHOA_LUAN => [
            'ten' => 'Giai đoạn 5: Thực hiện khóa luận',
            'short' => 'Thực hiện khóa luận',
            'mo_ta' => 'Nhóm triển khai đồ án, phân tích, thiết kế, lập trình, kiểm thử và nộp báo cáo định kỳ theo các đợt do GVHD thiết lập.',
            'dieu_kien_bat_dau' => 'Đề cương được thông qua.',
            'dieu_kien_hoan_thanh' => 'GVHD xác nhận nhóm đã hoàn thành tất cả các mốc báo cáo kỹ thuật và đủ khối lượng khóa luận.',
            'icon' => 'fa-laptop-code',
            'color' => '#06b6d4',
        ],
        self::GIAI_DOAN_HOAN_THIEN_VA_BAO_VE => [
            'ten' => 'Giai đoạn 6: Hoàn thiện và bảo vệ',
            'short' => 'Hoàn thiện & Bảo vệ',
            'mo_ta' => 'Nộp hồ sơ bảo vệ, kiểm tra đạo văn (Turnitin), bảo vệ trước Hội đồng chấm và nộp quyển khóa luận hoàn chỉnh sau bảo vệ.',
            'dieu_kien_bat_dau' => 'GVHD ký xác nhận đồng ý cho sinh viên bảo vệ.',
            'dieu_kien_hoan_thanh' => 'Hội đồng chấm điểm đạt, nhóm nộp bản khóa luận hoàn chỉnh sau chỉnh sửa góp ý của Hội đồng.',
            'icon' => 'fa-award',
            'color' => '#ec4899',
        ],
    ];

    protected $fillable = [
        'MaNhom',
        'MaDeTai',
        'MaHocKy',
        'GiaiDoanHienTai',
        'TrangThaiGiaiDoan', // CHUA_BAT_DAU, DANG_THUC_HIEN, CHO_XU_LY, HOAN_THANH, QUA_HAN, KHONG_DAT
        'DieuKienDat',
        'NgayBatDau',
        'NgayHoanThanh',
        'GhiChu',
    ];

    protected function casts(): array
    {
        return [
            'GiaiDoanHienTai' => 'integer',
            'DieuKienDat' => 'array',
            'NgayBatDau' => 'datetime',
            'NgayHoanThanh' => 'datetime',
        ];
    }

    public function nhom()
    {
        return $this->belongsTo(Nhom::class, 'MaNhom', 'MaNhom');
    }

    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }

    public function lichSu()
    {
        return $this->hasMany(LichSuChuyenGiaiDoan::class, 'MaNhom', 'MaNhom')->orderBy('created_at', 'desc');
    }

    public function getMetaGiaiDoanAttribute(): array
    {
        return self::DANH_SACH_GIAI_DOAN[$this->GiaiDoanHienTai] ?? [
            'ten' => 'Giai đoạn ' . $this->GiaiDoanHienTai,
            'short' => 'Giai đoạn ' . $this->GiaiDoanHienTai,
            'mo_ta' => '',
            'dieu_kien_bat_dau' => '',
            'dieu_kien_hoan_thanh' => '',
            'icon' => 'fa-circle-dot',
            'color' => '#64748b',
        ];
    }
}
