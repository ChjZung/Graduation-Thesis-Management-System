<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\KeHoachKhoaLuan;
use App\Models\MocThoiGianKhoaLuan;
use App\Models\DeTai;
use App\Models\PhanCongPhanBien;
use App\Models\SinhVien;
use App\Models\KetQuaSinhVien;
use App\Models\HocKy;
use App\Models\GiaoVu;
use App\Models\GiangVien;
use Illuminate\Support\Facades\DB;

echo "=== SEEDING DEMO DATA FOR TRƯỞNG KHOA ===\n";

// 1. Kế hoạch khóa luận & Mốc thời gian
$makeHoach = 'KH_CNTT_2526_01';
$hk = HocKy::where('MaHocKy', 'HK2526_1')->first() ?? HocKy::first();
$gv = GiaoVu::first();

KeHoachKhoaLuan::updateOrCreate(
    ['MakeHoach' => $makeHoach],
    [
        'TenKeHoach' => 'Kế hoạch Khóa luận Tốt nghiệp Học kỳ 1 Năm học 2025-2026',
        'NoiDung'    => "Kế hoạch chính thức triển khai công tác khóa luận tốt nghiệp cho sinh viên đại học chính quy Khoa Công nghệ Thông tin.\nCác bộ môn và giảng viên tuân thủ nghiêm ngặt khung thời gian quy định.",
        'TrangThai'  => 'ĐÃ CÔNG BỐ',
        'NgayTao'    => '2025-08-15',
        'MaHocKy'    => $hk ? $hk->MaHocKy : 'HK2526_1',
        'MaGVu'      => $gv ? $gv->MaGVu : 'GVU01',
    ]
);
echo "✓ Đã tạo/cập nhật Kế hoạch khóa luận: $makeHoach\n";

// Mốc thời gian
$mocs = [
    [
        'MaMoc'       => 'MOC_01',
        'TenMoc'      => 'Giảng viên đề xuất danh mục đề tài khóa luận',
        'NgayBatDau'  => '2025-09-01',
        'NgayKetThuc' => '2025-09-15',
        'MoTa'        => 'Giảng viên gửi đề cương chi tiết lên hệ thống để Bộ môn xét duyệt.',
    ],
    [
        'MaMoc'       => 'MOC_02',
        'TenMoc'      => 'Phân công phản biện đề cương & Trưởng bộ môn duyệt',
        'NgayBatDau'  => '2025-09-16',
        'NgayKetThuc' => '2025-09-25',
        'MoTa'        => 'Trưởng BM phân công GV phản biện đề cương và hoàn tất xét duyệt cấp Bộ môn.',
    ],
    [
        'MaMoc'       => 'MOC_03',
        'TenMoc'      => 'Trưởng khoa phê duyệt & Công bố danh mục đề tài',
        'NgayBatDau'  => '2025-09-26',
        'NgayKetThuc' => '2025-09-30',
        'MoTa'        => 'Trưởng khoa xem xét phê duyệt, Giáo vụ chính thức công bố danh mục đề tài.',
    ],
    [
        'MaMoc'       => 'MOC_04',
        'TenMoc'      => 'Sinh viên đăng ký đề tài & Giáo vụ phân nhóm',
        'NgayBatDau'  => '2025-10-01',
        'NgayKetThuc' => '2025-10-10',
        'MoTa'        => 'Sinh viên đủ điều kiện nộp đơn đăng ký nguyện vọng và ghép nhóm.',
    ],
    [
        'MaMoc'       => 'MOC_05',
        'TenMoc'      => 'Thực hiện đề tài & Nộp báo cáo định kỳ các đợt',
        'NgayBatDau'  => '2025-10-11',
        'NgayKetThuc' => '2025-12-15',
        'MoTa'        => 'Sinh viên thực hiện đề tài dưới sự hướng dẫn của GVHD, nộp báo cáo tiến độ.',
    ],
    [
        'MaMoc'       => 'MOC_06',
        'TenMoc'      => 'Nộp hồ sơ bảo vệ & Chấm điểm Hội đồng tốt nghiệp',
        'NgayBatDau'  => '2025-12-16',
        'NgayKetThuc' => '2025-12-30',
        'MoTa'        => 'Bảo vệ trước Hội đồng chấm khóa luận tốt nghiệp cấp Khoa.',
    ],
];

foreach ($mocs as $m) {
    MocThoiGianKhoaLuan::updateOrCreate(
        ['MaMoc' => $m['MaMoc']],
        [
            'TenMoc'      => $m['TenMoc'],
            'NgayBatDau'  => $m['NgayBatDau'],
            'NgayKetThuc' => $m['NgayKetThuc'],
            'MoTa'        => $m['MoTa'],
            'MakeHoach'   => $makeHoach,
        ]
    );
}
echo "✓ Đã tạo/cập nhật " . count($mocs) . " mốc thời gian quy trình\n";

// 2. Đề tài demo cho Trưởng khoa duyệt
$gv1 = GiangVien::where('MaBoMon', 'ATTT')->first() ?? GiangVien::first();
$gv2 = GiangVien::where('MaBoMon', 'CNPM')->first() ?? GiangVien::skip(1)->first();

$dt1 = DeTai::updateOrCreate(
    ['MaDeTai' => 'DT_TK_01'],
    [
        'TenDeTai'           => 'Nghiên cứu và xây dựng hệ thống phát hiện tấn công mạng bằng Deep Learning',
        'TenDeTaiTiengAnh'   => 'Research and development of deep learning based intrusion detection system',
        'MucTieu'            => "1. Khảo sát các mô hình học sâu CNN, LSTM, Transformer trong phát hiện xâm nhập.\n2. Huấn luyện mô hình đạt F1-Score > 95% trên tập dữ liệu chuẩn CICIDS2017.\n3. Xây dựng dashboard cảnh báo tấn công mạng thời gian thực.",
        'MoTa'               => 'Hệ thống giám sát luồng dữ liệu mạng, trích xuất đặc trưng và tự động nhận diện các cuộc tấn công DDoS, Port Scan, Brute Force bằng mô hình Deep Learning.',
        'YeuCau'             => 'Sinh viên có kiến thức mạng máy tính tốt, lập trình Python, am hiểu PyTorch/TensorFlow.',
        'SoLuongSV'          => 2,
        'SoLuongSinhVienToiDa' => 2,
        'LinhVuc'            => 'An toàn thông tin & AI',
        'TrangThai'          => 'Chờ duyệt cấp Khoa',
        'MaGV'               => $gv1 ? $gv1->MaGV : 'GV001',
        'MaHocKy'            => $hk ? $hk->MaHocKy : 'HK2526_1',
        'NgayDuyetBM'        => now()->subDays(1),
        'NguoiDuyetBM'       => $gv1 ? $gv1->MaGV : 'GV001',
    ]
);

// Tạo phân công phản biện đề cương cho DT_TK_01
if ($gv2) {
    PhanCongPhanBien::updateOrCreate(
        ['MaPhanCong' => 'PC_DT_TK_01'],
        [
            'MaDeTai'  => 'DT_TK_01',
            'MaGV'     => $gv2->MaGV,
            'VaiTro'   => 'Phản biện đề cương',
            'KetQua'   => 'Đạt',
            'NhanXet'  => "Đề tài có tính cấp thiết cao, phương pháp nghiên cứu rõ ràng và khả thi.\nĐề cương đã được chỉnh sửa hoàn thiện theo góp ý của Hội đồng bộ môn.\nKính chuyển Trưởng khoa phê duyệt chính thức.",
        ]
    );
}

// Đề tài thứ 2 đã duyệt cấp Khoa
DeTai::updateOrCreate(
    ['MaDeTai' => 'DT_TK_02'],
    [
        'TenDeTai'           => 'Xây dựng nền tảng học trực tuyến thích ứng áp dụng giải thuật gợi ý cá nhân hóa',
        'TenDeTaiTiengAnh'   => 'Adaptive e-learning platform using personalized recommendation algorithm',
        'MucTieu'            => 'Xây dựng LMS thông minh có khả năng cá nhân hóa lộ trình học theo năng lực người học.',
        'MoTa'               => 'Nền tảng tích hợp thuật toán lọc cộng tác (Collaborative Filtering) để gợi ý bài tập và bài giảng.',
        'YeuCau'             => 'Thành thạo Laravel, Vue.js, cơ bản về Machine Learning.',
        'SoLuongSV'          => 3,
        'SoLuongSinhVienToiDa' => 3,
        'LinhVuc'            => 'Công nghệ phần mềm',
        'TrangThai'          => 'Trưởng khoa đã duyệt',
        'MaGV'               => $gv2 ? $gv2->MaGV : 'GV002',
        'MaHocKy'            => $hk ? $hk->MaHocKy : 'HK2526_1',
        'NgayDuyetBM'        => now()->subDays(3),
        'NguoiDuyetBM'       => $gv2 ? $gv2->MaGV : 'GV002',
        'NgayDuyetKhoa'      => now()->subDays(1),
        'NguoiDuyetKhoa'     => $gv1 ? $gv1->MaGV : 'GV001',
    ]
);

echo "✓ Đã tạo đề tài DT_TK_01 (Chờ duyệt cấp Khoa) & DT_TK_02 (Trưởng khoa đã duyệt)\n";

// 3. Tạo Kết quả sinh viên mẫu (KetQuaSinhVien) cho sinh viên CNTT
$svList = SinhVien::where('MaKhoa', 'CNTT')->limit(12)->get();
$sampleScores = [
    ['diemHD' => 9.2, 'diemPB' => 9.0, 'diemHDTB' => 9.5, 'tongKet' => 9.3, 'xepLoai' => 'Xuất sắc', 'ghiChu' => 'Khóa luận xuất sắc, có bài báo khoa học.'],
    ['diemHD' => 9.0, 'diemPB' => 9.0, 'diemHDTB' => 9.0, 'tongKet' => 9.0, 'xepLoai' => 'Xuất sắc', 'ghiChu' => 'Sản phẩm demo hoàn chỉnh.'],
    ['diemHD' => 8.5, 'diemPB' => 8.5, 'diemHDTB' => 8.8, 'tongKet' => 8.6, 'xepLoai' => 'Giỏi', 'ghiChu' => 'Đáp ứng tốt các yêu cầu.'],
    ['diemHD' => 8.0, 'diemPB' => 8.2, 'diemHDTB' => 8.4, 'tongKet' => 8.2, 'xepLoai' => 'Giỏi', 'ghiChu' => 'Báo cáo lưu loát, bảo vệ tự tin.'],
    ['diemHD' => 8.2, 'diemPB' => 8.0, 'diemHDTB' => 8.0, 'tongKet' => 8.1, 'xepLoai' => 'Giỏi', 'ghiChu' => 'Hoàn thành tốt nhiệm vụ.'],
    ['diemHD' => 7.8, 'diemPB' => 7.5, 'diemHDTB' => 7.8, 'tongKet' => 7.7, 'xepLoai' => 'Khá', 'ghiChu' => 'Nội dung đạt yêu cầu.'],
    ['diemHD' => 7.5, 'diemPB' => 7.0, 'diemHDTB' => 7.6, 'tongKet' => 7.4, 'xepLoai' => 'Khá', 'ghiChu' => 'Cần bổ sung thêm tài liệu tham khảo.'],
    ['diemHD' => 7.0, 'diemPB' => 7.2, 'diemHDTB' => 7.0, 'tongKet' => 7.1, 'xepLoai' => 'Khá', 'ghiChu' => 'Đạt chuẩn đầu ra.'],
    ['diemHD' => 8.8, 'diemPB' => 8.6, 'diemHDTB' => 8.9, 'tongKet' => 8.8, 'xepLoai' => 'Giỏi', 'ghiChu' => 'Giao diện ứng dụng đẹp, mượt mà.'],
    ['diemHD' => 6.5, 'diemPB' => 6.0, 'diemHDTB' => 6.8, 'tongKet' => 6.5, 'xepLoai' => 'Trung bình', 'ghiChu' => 'Cần cải thiện chất lượng mã nguồn.'],
    ['diemHD' => 8.4, 'diemPB' => 8.2, 'diemHDTB' => 8.5, 'tongKet' => 8.4, 'xepLoai' => 'Giỏi', 'ghiChu' => 'Kiểm thử kỹ lưỡng.'],
    ['diemHD' => 9.5, 'diemPB' => 9.2, 'diemHDTB' => 9.6, 'tongKet' => 9.5, 'xepLoai' => 'Xuất sắc', 'ghiChu' => 'Ý tưởng đột phá, tính ứng dụng thực tế cao.'],
];

foreach ($svList as $i => $sv) {
    $sc = $sampleScores[$i % count($sampleScores)];
    KetQuaSinhVien::updateOrCreate(
        ['MaSV' => $sv->MaSV],
        [
            'MaKetQua'       => 'KQ_' . $sv->MaSV,
            'DiemPhanBien'   => $sc['diemPB'],
            'DiemHoiDongTB'  => $sc['diemHDTB'],
            'DiemTongKet'    => $sc['tongKet'],
            'KetQua'         => $sc['xepLoai'],
            'NhanXetChung'   => $sc['ghiChu'],
            'NgayCham'       => '2025-12-28',
            'MaHocKy'        => $hk ? $hk->MaHocKy : 'HK2526_1',
        ]
    );
}

echo "✓ Đã tạo " . count($svList) . " bản ghi kết quả sinh viên (KetQuaSinhVien) cho Khoa CNTT\n";
echo "=== SEEDING COMPLETED SUCCESSFULLY ===\n";
