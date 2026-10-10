<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TruongBoMonPheDuyetDeTaiSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Đảm bảo tài khoản Trưởng Bộ Môn CNPM và ATTT hoạt động
        DB::table('TaiKhoan')->updateOrInsert(
            ['TenDangNhap' => 'TBM_BM_CNPM_GV00000001'],
            [
                'MaTK'              => 'TK_TBM_BM_CNPM_GV00000001',
                'MatKhau'           => Hash::make('123456'),
                'MaVaiTro'          => 'VT04',
                'TrangThai'         => true,
                'SoLanDangNhapSai'  => 0,
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau'  => 'ACTIVE',
                'updated_at'        => $now,
            ]
        );

        DB::table('TaiKhoan')->updateOrInsert(
            ['TenDangNhap' => 'TBM_BM_ATTT_GV00000005'],
            [
                'MaTK'              => 'TK_TBM_BM_ATTT_GV00000005',
                'MatKhau'           => Hash::make('123456'),
                'MaVaiTro'          => 'VT04',
                'TrangThai'         => true,
                'SoLanDangNhapSai'  => 0,
                'BatBuocDoiMatKhau' => false,
                'TrangThaiMatKhau'  => 'ACTIVE',
                'updated_at'        => $now,
            ]
        );

        // Đảm bảo giảng viên GV00000001 là TBM CNPM
        DB::table('BoMon')->where('MaBoMon', 'BM_CNPM')->update([
            'TruongBoMon' => 'PGS.TS. Nguyễn Văn Hùng',
        ]);

        // Đảm bảo giảng viên GV00000005 là TBM ATTT
        DB::table('BoMon')->where('MaBoMon', 'BM_ATTT')->update([
            'TruongBoMon' => 'TS. Lê Thị Mai',
        ]);

        // 2. Đảm bảo các giảng viên thuộc bộ môn tương ứng
        DB::table('GiangVien')->where('MaGV', 'GV00000001')->update(['MaBoMon' => 'BM_CNPM']);
        DB::table('GiangVien')->where('MaGV', 'GV00000004')->update(['MaBoMon' => 'BM_CNPM']);
        DB::table('GiangVien')->where('MaGV', 'GV00000007')->update(['MaBoMon' => 'BM_CNPM']);
        DB::table('GiangVien')->where('MaGV', 'GV00000002')->update(['MaBoMon' => 'BM_CNPM']);
        DB::table('GiangVien')->where('MaGV', 'GV00000005')->update(['MaBoMon' => 'BM_ATTT']);

        // 3. Xóa đề tài seed cũ nếu có để tránh trùng lặp
        $seedMaDeTais = [
            'DT_CNPM_CHO_01', 'DT_CNPM_CHO_02', 'DT_CNPM_SUA_01',
            'DT_CNPM_DUYET_01', 'DT_CNPM_TUCHOI_01', 'DT_CNPM_HK1_01',
            'DT_ATTT_CHO_01'
        ];
        DB::table('ChiTietDuyetDeTai')->whereIn('MaDeTai', $seedMaDeTais)->delete();
        DB::table('DeTai')->whereIn('MaDeTai', $seedMaDeTais)->delete();

        // 4. Tạo đề tài mẫu trong học kỳ HK2627_2 cho Bộ môn CNPM (đủ 4 trạng thái)
        
        // Đề tài 1: Chờ duyệt (GV00000007)
        DB::table('DeTai')->insert([
            'MaDeTai'              => 'DT_CNPM_CHO_01',
            'TenDeTai'             => 'Xây dựng ứng dụng quản lý tiến độ khóa luận thời gian thực với Laravel và WebSocket',
            'MoTa'                 => 'Nghiên cứu ứng dụng công nghệ realtime trong việc đồng bộ trạng thái duyệt và báo cáo khóa luận giữa sinh viên và giảng viên. Mục tiêu: Hoàn thiện hệ thống trao đổi thông báo thời gian thực và phân quyền duyệt đề tài nhiều cấp.',
            'YeuCau'               => 'Nắm vững kiến thức PHP/Laravel, cơ sở dữ liệu MySQL và kỹ năng lập trình hướng đối tượng.',
            'LinhVuc'              => 'Công nghệ phần mềm',
            'MaHocPhan'            => 'HP_KLTN_CNPM',
            'HocPhan'              => 'Khóa luận tốt nghiệp',
            'SoLuongSinhVienToiDa' => 3,
            'MaGV'                 => 'GV00000007',
            'MaHocKy'              => 'HK2627_2',
            'TrangThai'            => 'Chờ duyệt cấp Bộ môn',
            'NgayDeXuat'           => $now->copy()->subDays(2),
            'created_at'           => $now->copy()->subDays(2),
            'updated_at'           => $now->copy()->subDays(2),
        ]);

        // Đề tài 2: Chờ duyệt (GV00000004)
        DB::table('DeTai')->insert([
            'MaDeTai'              => 'DT_CNPM_CHO_02',
            'TenDeTai'             => 'Nghiên cứu kiến trúc Microservices và ứng dụng vào cổng đăng ký học phần đại học',
            'MoTa'                 => 'Thiết kế hệ thống chịu tải cao, phân tách dịch vụ đăng ký học phần và xử lý thanh toán học phí. Mục tiêu: Xây dựng bản thử nghiệm chịu tải tối thiểu 1.000 yêu cầu đồng thời.',
            'YeuCau'               => 'Có kiến thức về Docker, Message Queue (RabbitMQ/Kafka) và RESTful API.',
            'LinhVuc'              => 'Kiến trúc hệ thống',
            'MaHocPhan'            => 'HP_KLTN_CNPM',
            'HocPhan'              => 'Khóa luận tốt nghiệp',
            'SoLuongSinhVienToiDa' => 3,
            'MaGV'                 => 'GV00000004',
            'MaHocKy'              => 'HK2627_2',
            'TrangThai'            => 'Chờ duyệt cấp Bộ môn',
            'NgayDeXuat'           => $now->copy()->subDays(1),
            'created_at'           => $now->copy()->subDays(1),
            'updated_at'           => $now->copy()->subDays(1),
        ]);

        // Đề tài 3: Yêu cầu chỉnh sửa (GV00000007, có lịch sử)
        DB::table('DeTai')->insert([
            'MaDeTai'              => 'DT_CNPM_SUA_01',
            'TenDeTai'             => 'Ứng dụng AI phân tích cảm xúc phản hồi người dùng trong thương mại điện tử',
            'MoTa'                 => 'Sử dụng mô hình BERT và xử lý ngôn ngữ tự nhiên để đánh giá nhận xét của khách hàng trên sàn Shopee/Tiki. Mục tiêu: Huấn luyện mô hình đạt độ chính xác trên 85% với tập dữ liệu tiếng Việt.',
            'YeuCau'               => 'Nắm vững Python, PyTorch/Transformers và các kỹ thuật tiền xử lý dữ liệu tiếng Việt.',
            'LinhVuc'              => 'Trí tuệ nhân tạo',
            'MaHocPhan'            => 'HP_KLTN_CNPM',
            'HocPhan'              => 'Khóa luận tốt nghiệp',
            'SoLuongSinhVienToiDa' => 3,
            'MaGV'                 => 'GV00000007',
            'MaHocKy'              => 'HK2627_2',
            'TrangThai'            => 'Yêu cầu chỉnh sửa',
            'LyDoTuChoi'           => 'Cần làm rõ phương pháp thu thập dữ liệu và giới hạn phạm vi nghiên cứu để sinh viên hoàn thành đúng thời hạn.',
            'NgayDeXuat'           => $now->copy()->subDays(5),
            'created_at'           => $now->copy()->subDays(5),
            'updated_at'           => $now->copy()->subDays(3),
        ]);

        DB::table('ChiTietDuyetDeTai')->insert([
            'MaDuyet'       => 'CTD_SEED_01',
            'MaDeTai'       => 'DT_CNPM_SUA_01',
            'MaGV'          => 'GV00000001',
            'NguoiThucHien' => 'PGS.TS. Nguyễn Văn Hùng - Trưởng Bộ Môn',
            'HanhDong'      => 'Yêu cầu chỉnh sửa',
            'TrangThaiCu'   => 'Chờ duyệt cấp Bộ môn',
            'TrangThai'     => 'Yêu cầu chỉnh sửa',
            'LyDo'          => 'Cần làm rõ phương pháp thu thập dữ liệu và giới hạn phạm vi nghiên cứu để sinh viên hoàn thành đúng thời hạn.',
            'NgayDuyet'     => $now->copy()->subDays(3)->toDateString(),
            'created_at'    => $now->copy()->subDays(3),
            'updated_at'    => $now->copy()->subDays(3),
        ]);

        // Đề tài 4: Đã phê duyệt (GV00000007, có lịch sử)
        DB::table('DeTai')->insert([
            'MaDeTai'              => 'DT_CNPM_DUYET_01',
            'TenDeTai'             => 'Xây dựng hệ thống thi trắc nghiệm trực tuyến chống gian lận bằng nhận diện khuôn mặt',
            'MoTa'                 => 'Hệ thống giám sát quá trình làm bài của thí sinh qua webcam sử dụng OpenCV và MediaPipe. Mục tiêu: Phát hiện các hành vi quay đầu, mở tài liệu hoặc có người thứ hai trong khung hình.',
            'YeuCau'               => 'Thành thạo lập trình Web và xử lý ảnh cơ bản.',
            'LinhVuc'              => 'Thị giác máy tính',
            'MaHocPhan'            => 'HP_KLTN_CNPM',
            'HocPhan'              => 'Khóa luận tốt nghiệp',
            'SoLuongSinhVienToiDa' => 3,
            'MaGV'                 => 'GV00000007',
            'MaHocKy'              => 'HK2627_2',
            'TrangThai'            => 'Chờ duyệt cấp Khoa',
            'NgayDuyetBM'          => $now->copy()->subDays(2)->toDateString(),
            'NguoiDuyetBM'         => 'GV00000001',
            'NgayDeXuat'           => $now->copy()->subDays(6),
            'created_at'           => $now->copy()->subDays(6),
            'updated_at'           => $now->copy()->subDays(2),
        ]);

        DB::table('ChiTietDuyetDeTai')->insert([
            'MaDuyet'       => 'CTD_SEED_02',
            'MaDeTai'       => 'DT_CNPM_DUYET_01',
            'MaGV'          => 'GV00000001',
            'NguoiThucHien' => 'PGS.TS. Nguyễn Văn Hùng - Trưởng Bộ Môn',
            'HanhDong'      => 'Phê duyệt',
            'TrangThaiCu'   => 'Chờ duyệt cấp Bộ môn',
            'TrangThai'     => 'Chờ duyệt cấp Khoa',
            'LyDo'          => 'Đề tài có tính ứng dụng cao, đáp ứng đầy đủ yêu cầu chuyên môn của Bộ môn.',
            'NgayDuyet'     => $now->copy()->subDays(2)->toDateString(),
            'created_at'    => $now->copy()->subDays(2),
            'updated_at'    => $now->copy()->subDays(2),
        ]);

        // Đề tài 5: Bị từ chối (GV00000007, có lịch sử)
        DB::table('DeTai')->insert([
            'MaDeTai'              => 'DT_CNPM_TUCHOI_01',
            'TenDeTai'             => 'Xây dựng website bán hàng quần áo thời trang với WordPress và WooCommerce',
            'MoTa'                 => 'Cài đặt theme và plugin thương mại điện tử cơ bản. Mục tiêu: Tạo website bán hàng đơn giản.',
            'YeuCau'               => 'Sử dụng máy tính cơ bản.',
            'LinhVuc'              => 'Thương mại điện tử',
            'MaHocPhan'            => 'HP_KLTN_CNPM',
            'HocPhan'              => 'Khóa luận tốt nghiệp',
            'SoLuongSinhVienToiDa' => 3,
            'MaGV'                 => 'GV00000007',
            'MaHocKy'              => 'HK2627_2',
            'TrangThai'            => 'Từ chối',
            'LyDoTuChoi'           => 'Khối lượng công việc và hàm lượng kỹ thuật quá đơn giản, không đủ điều kiện làm Khóa luận tốt nghiệp đại học.',
            'NgayDeXuat'           => $now->copy()->subDays(7),
            'created_at'           => $now->copy()->subDays(7),
            'updated_at'           => $now->copy()->subDays(4),
        ]);

        DB::table('ChiTietDuyetDeTai')->insert([
            'MaDuyet'       => 'CTD_SEED_03',
            'MaDeTai'       => 'DT_CNPM_TUCHOI_01',
            'MaGV'          => 'GV00000001',
            'NguoiThucHien' => 'PGS.TS. Nguyễn Văn Hùng - Trưởng Bộ Môn',
            'HanhDong'      => 'Từ chối',
            'TrangThaiCu'   => 'Chờ duyệt cấp Bộ môn',
            'TrangThai'     => 'Từ chối',
            'LyDo'          => 'Khối lượng công việc và hàm lượng kỹ thuật quá đơn giản, không đủ điều kiện làm Khóa luận tốt nghiệp đại học.',
            'NgayDuyet'     => $now->copy()->subDays(4)->toDateString(),
            'created_at'    => $now->copy()->subDays(4),
            'updated_at'    => $now->copy()->subDays(4),
        ]);

        // Đề tài 6: Ở học kỳ khác (HK2425_1) để test đổi học kỳ -> số liệu thay đổi
        DB::table('DeTai')->insert([
            'MaDeTai'              => 'DT_CNPM_HK1_01',
            'TenDeTai'             => 'Nghiên cứu ứng dụng Blockchain trong xác thực văn bằng đại học HUIT',
            'MoTa'                 => 'Phát triển Smart Contract trên nền tảng Ethereum/Polygon để lưu trữ mã băm văn bằng sinh viên. Mục tiêu: Xây dựng giải pháp tra cứu chống làm giả văn bằng chứng chỉ.',
            'YeuCau'               => 'Nắm vững kiến thức Solidity, Web3 và Cryptography.',
            'LinhVuc'              => 'Công nghệ chuỗi khối',
            'MaHocPhan'            => 'HP_KLTN_CNPM',
            'HocPhan'              => 'Khóa luận tốt nghiệp',
            'SoLuongSinhVienToiDa' => 3,
            'MaGV'                 => 'GV00000007',
            'MaHocKy'              => 'HK2425_1',
            'TrangThai'            => 'Đã công bố',
            'NgayDeXuat'           => $now->copy()->subMonths(6),
            'created_at'           => $now->copy()->subMonths(6),
            'updated_at'           => $now->copy()->subMonths(5),
        ]);

        // Đề tài 7: Thuộc bộ môn khác (BM_ATTT - GV00000005) để test CHẶN TRUY CẬP CHÉO
        DB::table('DeTai')->insert([
            'MaDeTai'              => 'DT_ATTT_CHO_01',
            'TenDeTai'             => 'Phát hiện tấn công DDoS trên môi trường điện toán đám mây bằng Machine Learning',
            'MoTa'                 => 'Thu thập lưu lượng mạng và huấn luyện thuật toán Random Forest phát hiện lưu lượng bất thường. Mục tiêu: Cảnh báo sớm các cuộc tấn công SYN Flood và UDP Flood.',
            'YeuCau'               => 'Kiến thức an toàn thông tin và mạng máy tính chuyên sâu.',
            'LinhVuc'              => 'An toàn thông tin',
            'MaHocPhan'            => 'HP_KLTN_ATTT',
            'HocPhan'              => 'Khóa luận tốt nghiệp',
            'SoLuongSinhVienToiDa' => 3,
            'MaGV'                 => 'GV00000005', // Thuộc BM_ATTT
            'MaHocKy'              => 'HK2627_2',
            'TrangThai'            => 'Chờ duyệt cấp Bộ môn',
            'NgayDeXuat'           => $now->copy()->subDays(1),
            'created_at'           => $now->copy()->subDays(1),
            'updated_at'           => $now->copy()->subDays(1),
        ]);
    }
}
