<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhieuChamDiem extends Model
{
    use HasFactory;

    protected $table = 'PhieuChamDiem';
    protected $primaryKey = 'MaPhieuChamDiem';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaPhieuChamDiem',
        'Diem',
        'LoaiKhoaLuan',
        'NgayCham',
        'NhanXet',
        'ChiTietDiem',
        'MaDeTai',
        'MaHoiDong',
        'MaGV',
        'MaSV',
        'MaHoSo',
        'MaHocKy',
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'Diem' => 'float',
            'NgayCham' => 'date',
            'ChiTietDiem' => 'array',
        ];
    }

    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }

    public function hoiDong()
    {
        return $this->belongsTo(HoiDong::class, 'MaHoiDong', 'MaHoiDong');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class, 'MaSV', 'MaSV');
    }

    public function hoSoBaoVe()
    {
        return $this->belongsTo(HoSoBaoVe::class, 'MaHoSo', 'MaHoSo');
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }

    /**
     * Bảng Tiêu chí chấm điểm Khóa luận Cử nhân (KLCN - Hướng ứng dụng)
     * Trích xuất nguyên bản từ biểu mẫu chính thức Khoa CNTT - ĐH Công Thương TP.HCM
     */
    public static function getRubricKLCN(): array
    {
        return [
            [
                'id' => 'tc1',
                'stt' => 1,
                'noi_dung' => 'Khảo sát nghiệp vụ, mô hình hóa nghiệp vụ: Xác định lý do, mục tiêu đề tài (0.25đ); Khảo sát hiện trạng: Cơ cấu tổ chức, Qui trình, biểu mẫu (0.50đ)',
                'clo' => 'CLO1.1',
                'max' => 0.75,
            ],
            [
                'id' => 'tc2',
                'stt' => 2,
                'noi_dung' => 'Lập kế hoạch và phân công thực hiện công việc',
                'clo' => 'CLO6',
                'max' => 0.25,
            ],
            [
                'id' => 'tc3',
                'stt' => 3,
                'noi_dung' => 'Phân tích hệ thống: Lập sơ đồ Use-Case nghiệp vụ và đặc tả (0.25đ); Lập sơ đồ Use-Case hệ thống và đặc tả (0.25đ); Sơ đồ lớp phân tích (0.25đ)',
                'clo' => 'CLO1.2',
                'max' => 0.75,
            ],
            [
                'id' => 'tc4',
                'stt' => 4,
                'noi_dung' => 'Thiết kế hệ thống: Sơ đồ lớp thiết kế (0.25đ); Mô hình dữ liệu (0.25đ)',
                'clo' => 'CLO2.1',
                'max' => 0.50,
            ],
            [
                'id' => 'tc5',
                'stt' => 5,
                'noi_dung' => 'Thiết kế giao diện',
                'clo' => 'CLO2.2',
                'max' => 0.50,
            ],
            [
                'id' => 'tc6',
                'stt' => 6,
                'noi_dung' => 'Xây dựng API hệ thống & Phân tích các chức năng trên các nền tảng: Xây dựng API (0.25đ); Phân tích các chức năng trên các nền tảng (0.25đ)',
                'clo' => 'CLO1.1',
                'max' => 0.50,
            ],
            [
                'id' => 'tc7',
                'stt' => 7,
                'noi_dung' => 'Xây dựng các chức năng cơ bản của ứng dụng (đăng nhập, đăng xuất, backup & restore,...) (0.5đ); Cài đặt chức năng theo yêu cầu bài toán (3.5đ)',
                'clo' => 'CLO3',
                'max' => 4.00,
            ],
            [
                'id' => 'tc8',
                'stt' => 8,
                'noi_dung' => 'Kiểm thử và triển khai hệ thống',
                'clo' => 'CLO4',
                'max' => 0.75,
            ],
            [
                'id' => 'tc9',
                'stt' => 9,
                'noi_dung' => 'Nội dung kiến thức trình bày trong quyển báo cáo',
                'clo' => 'CLO5.1',
                'max' => 0.50,
            ],
            [
                'id' => 'tc10',
                'stt' => 10,
                'noi_dung' => 'Hình thức, định dạng quyển báo cáo',
                'clo' => 'CLO5.1',
                'max' => 0.50,
            ],
            [
                'id' => 'tc11',
                'stt' => 11,
                'noi_dung' => 'Thái độ, tác phong làm việc',
                'clo' => 'CLO6',
                'max' => 0.50,
            ],
            [
                'id' => 'tc12',
                'stt' => 12,
                'noi_dung' => 'Phong cách báo cáo, Slide',
                'clo' => 'CLO5.2',
                'max' => 0.50,
            ],
            [
                'id' => 'tc13',
                'stt' => 13,
                'noi_dung' => 'Cộng điểm khuyến khích NCKH liên quan nội dung đề tài: Giải cấp Khoa/bài báo Khoa (0.5đ); Giải cấp Trường/tạp chí (1.0đ). Tổng không quá 10 điểm',
                'clo' => 'CLO3',
                'max' => 1.00,
                'is_bonus' => true,
            ],
        ];
    }

    /**
     * Bảng Tiêu chí chấm điểm Khóa luận Kỹ sư (KLKS - Hướng ứng dụng)
     * Trích xuất nguyên bản từ biểu mẫu chính thức Khoa CNTT - ĐH Công Thương TP.HCM
     */
    public static function getRubricKLKS(): array
    {
        return [
            [
                'id' => 'tc1',
                'stt' => 1,
                'noi_dung' => 'Khảo sát nghiệp vụ, mô hình hóa nghiệp vụ: Xác định lý do, mục tiêu đề tài (0.25đ); Khảo sát hiện trạng: Cơ cấu tổ chức, Qui trình, biểu mẫu (0.25đ)',
                'clo' => 'CLO1.1',
                'max' => 0.50,
            ],
            [
                'id' => 'tc2',
                'stt' => 2,
                'noi_dung' => 'Lập kế hoạch và phân công thực hiện công việc',
                'clo' => 'CLO6',
                'max' => 0.25,
            ],
            [
                'id' => 'tc3',
                'stt' => 3,
                'noi_dung' => 'Phân tích hệ thống: Sơ đồ Use-Case nghiệp vụ và đặc tả (0.25đ); Sơ đồ Use-Case hệ thống và đặc tả (0.25đ); Sơ đồ lớp phân tích (0.25đ)',
                'clo' => 'CLO1.2',
                'max' => 0.75,
            ],
            [
                'id' => 'tc4',
                'stt' => 4,
                'noi_dung' => 'Thiết kế hệ thống: Sơ đồ lớp thiết kế (0.25đ); Mô hình dữ liệu (0.25đ)',
                'clo' => 'CLO2.1',
                'max' => 0.50,
            ],
            [
                'id' => 'tc5',
                'stt' => 5,
                'noi_dung' => 'Thiết kế giao diện: Giao diện nền tảng 1 (0.50đ); Giao diện nền tảng 2 (0.50đ)',
                'clo' => 'CLO2.2',
                'max' => 1.00,
            ],
            [
                'id' => 'tc6',
                'stt' => 6,
                'noi_dung' => 'Xây dựng API hệ thống & Phân tích các chức năng trên các nền tảng: Xây dựng API (0.25đ); Phân tích các chức năng trên các nền tảng (0.25đ)',
                'clo' => 'CLO1.1',
                'max' => 0.50,
            ],
            [
                'id' => 'tc7',
                'stt' => 7,
                'noi_dung' => 'Xây dựng ứng dụng: Chức năng cơ bản (đăng nhập, phân quyền...); Cài đặt chức năng nghiệp vụ trên Web & Mobile',
                'clo' => 'CLO3',
                'max' => 4.00,
            ],
            [
                'id' => 'tc8',
                'stt' => 8,
                'noi_dung' => 'Kiểm thử và triển khai hệ thống',
                'clo' => 'CLO4',
                'max' => 0.25,
            ],
            [
                'id' => 'tc9',
                'stt' => 9,
                'noi_dung' => 'Ứng dụng thuật toán hoặc trí tuệ nhân tạo (AI) nhằm nâng cao hiệu quả của ứng dụng',
                'clo' => 'CLO3',
                'max' => 1.25,
            ],
            [
                'id' => 'tc10',
                'stt' => 10,
                'noi_dung' => 'Nội dung kiến thức trình bày trong quyển báo cáo',
                'clo' => 'CLO5.1',
                'max' => 0.25,
            ],
            [
                'id' => 'tc11',
                'stt' => 11,
                'noi_dung' => 'Hình thức, định dạng quyển báo cáo',
                'clo' => 'CLO5.1',
                'max' => 0.25,
            ],
            [
                'id' => 'tc12',
                'stt' => 12,
                'noi_dung' => 'Thái độ, tác phong làm việc',
                'clo' => 'CLO6',
                'max' => 0.25,
            ],
            [
                'id' => 'tc13',
                'stt' => 13,
                'noi_dung' => 'Phong cách báo cáo, Slide',
                'clo' => 'CLO5.2',
                'max' => 0.25,
            ],
            [
                'id' => 'tc14',
                'stt' => 14,
                'noi_dung' => 'Cộng điểm khuyến khích NCKH liên quan nội dung đề tài: Giải cấp Khoa/bài báo Khoa (0.5đ); Giải cấp Trường/tạp chí (1.0đ). Tổng không quá 10 điểm',
                'clo' => 'CLO3',
                'max' => 1.00,
                'is_bonus' => true,
            ],
        ];
    }
}
