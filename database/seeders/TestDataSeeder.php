<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        
        // ============================================
        // 1. TẠO 50 SINH VIÊN
        // ============================================
        $sinhViens = [];
        $taiKhoans = [];
        $dsdk = [];

        $hoArray = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng'];
        $demArray = ['Văn', 'Thị', 'Đình', 'Minh', 'Ngọc', 'Hải', 'Thành', 'Quốc', 'Gia', 'Hoàng'];
        $tenArray = ['An', 'Bình', 'Châu', 'Dũng', 'Em', 'Giang', 'Hưng', 'Khánh', 'Long', 'Minh', 'Nam', 'Phúc', 'Quân', 'Sơn', 'Tài', 'Tú', 'Vinh', 'Xuân', 'Yến', 'Khoa'];

        $lopCodes = ['12DHTH01', '12DHTH02', '12DKTPM01', '12DATTT01'];

        $passwordHash = Hash::make('123456');

        for ($i = 51; $i <= 100; $i++) {
            $code = str_pad($i, 2, '0', STR_PAD_LEFT);
            $maSV = '200123' . str_pad(100 + $i, 4, '0', STR_PAD_LEFT);
            $ho = $hoArray[$i % count($hoArray)];
            $dem = $demArray[$i % count($demArray)];
            $ten = $tenArray[$i % count($tenArray)];
            $hoTen = "$ho $dem $ten";
            $lop = $lopCodes[$i % count($lopCodes)];

            $maTK = 'TK_' . $maSV;

            // Insert TaiKhoan
            $taiKhoans[] = [
                'MaTK' => $maTK,
                'TenDangNhap' => $maSV,
                'MatKhau' => $passwordHash,
                'MaVaiTro' => 'VT03', // Sinh viên
                'TrangThai' => 'Đang hoạt động',
                'created_at' => $now,
                'updated_at' => $now
            ];

            // Insert SinhVien
            $sinhViens[] = [
                'MaSV' => $maSV,
                'MaTK' => $maTK,
                'HoTen' => $hoTen,
                'NgaySinh' => '2004-05-15',
                'GioiTinh' => ($i % 3 == 0) ? 'Nữ' : 'Nam',
                'Email' => $maSV . '@huit.edu.vn',
                'SoDienThoai' => '0987' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'NgayNhapHoc' => '2022-09-05',
                'KhoaHoc' => '2022-2026',
                'SoTinChiTichLuy' => rand(125, 145),
                'DiemTichLuy' => round(rand(260, 390) / 100, 2),
                'TrangThai' => 'Đang học',
                'MaKhoa' => 'CNTT',
                'MaNganh' => ($lop === '12DKTPM01') ? '7480103' : (($lop === '12DATTT01') ? '7480202' : '7480201'),
                'MaLop' => $lop,
                'created_at' => $now,
                'updated_at' => $now
            ];

            // Insert DSDK
            $dsdk[] = [
                'MaDSDK' => 'DSDK_NEW_' . $code,
                'NgayXetDuyet' => '2026-01-10',
                'TrangThai' => 'Đủ điều kiện',
                'GhiChu' => 'Tích lũy >= 120 tín chỉ, CPA >= 2.50',
                'DieuKien' => 'Đạt chuẩn đầu ra KLTN',
                'MaSV' => $maSV,
                'MaHocKy' => 'HK2526_2',
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        DB::table('TaiKhoan')->insertOrIgnore($taiKhoans);
        DB::table('SinhVien')->insertOrIgnore($sinhViens);
        DB::table('DanhSachSVDuDieuKien')->insertOrIgnore($dsdk);

        // ============================================
        // 2. TẠO 50 ĐỀ TÀI PHÂN BỔ CÁC BỘ MÔN (GIẢNG VIÊN)
        // ============================================
        
        $deTais = [];
        $linhVucs = [
            'CNTT' => ['Web', 'Mobile App', 'IoT', 'AI', 'Machine Learning', 'Blockchain'],
            'HTTT' => ['ERP', 'Hệ thống quản trị', 'Phân tích dữ liệu', 'BI', 'Chuyển đổi số'],
            'KHM' => ['Thị giác máy tính', 'Xử lý ngôn ngữ tự nhiên', 'Mạng Neural', 'Tối ưu hóa'],
            'MMT' => ['Bảo mật mạng', 'Điện toán đám mây', 'Quản trị mạng', 'Mạng không dây']
        ];

        // Lấy danh sách 10 giảng viên (GV01 - GV10)
        $giangViens = DB::table('GiangVien')->whereIn('MaGV', [
            'GV01', 'GV02', 'GV03', 'GV04', 'GV05', 'GV06', 'GV07', 'GV08', 'GV09', 'GV10'
        ])->get();

        foreach ($giangViens as $index => $gv) {
            $maBoMon = $gv->MaBoMon ?? 'CNTT'; // Fallback to CNTT
            $lvList = $linhVucs[$maBoMon] ?? $linhVucs['CNTT'];
            
            // Mỗi GV tạo 5 đề tài
            for ($k = 1; $k <= 5; $k++) {
                $stt = ($index * 5) + $k;
                $maDeTai = 'DT_NEW_' . str_pad($stt, 3, '0', STR_PAD_LEFT);
                $lv = $lvList[$k % count($lvList)];
                
                $deTais[] = [
                    'MaDeTai' => $maDeTai,
                    'TenDeTai' => "Nghiên cứu và ứng dụng $lv trong thực tế (Phiên bản $stt)",
                    'MoTa' => "Đề tài tập trung nghiên cứu chuyên sâu về $lv, đề xuất giải pháp và xây dựng ứng dụng thực tiễn.",
                    'YeuCau' => 'Có kiến thức cơ bản về lập trình. Thái độ nghiêm túc.',
                    'SoLuongSinhVienToiDa' => 3,
                    'TrangThai' => 'Đã duyệt', // Cho sẵn trạng thái đã duyệt để SV có thể đăng ký
                    'LinhVuc' => $lv,
                    'MaGV' => $gv->MaGV,
                    'MaHocKy' => 'HK2526_2',
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
        }

        DB::table('DeTai')->insertOrIgnore($deTais);
    }
}
