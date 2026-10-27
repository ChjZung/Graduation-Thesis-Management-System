<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaiKhoan;
use App\Models\SinhVien;
use App\Models\GiangVien;
use App\Models\DeTai;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\PhieuDangKy;
use App\Models\HoSoBaoVe;
use App\Models\PhienBanHoSoBaoVe;
use App\Models\TepHoSoBaoVe;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\Nganh;
use App\Models\Lop;
use App\Models\HocKy;
use App\Models\HocPhan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class TestDefenseFlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maGV = 'GV00000023';
        $gv = GiangVien::find($maGV);

        if (!$gv) {
            $this->command->error("Không tìm thấy Giảng viên với mã {$maGV}. Vui lòng kiểm tra lại CSDL.");
            return;
        }

        // Lấy thông tin Khoa, Bộ môn, Học kỳ, Học phần, Ngành, Lớp
        $khoa = Khoa::where('MaKhoa', 'CNTT')->first() ?? Khoa::first();
        $boMon = BoMon::where('MaBoMon', 'CNPM')->first() ?? $gv->boMon ?? BoMon::first();
        $hocKy = HocKy::where('MaHocKy', 'HK2627_1')->first() ?? HocKy::first();
        $hocPhan = HocPhan::where('MaHocPhan', 'HP_KLCN_CNPM')->first() ?? HocPhan::first();
        $nganh = Nganh::where('MaKhoa', $khoa->MaKhoa)->first() ?? Nganh::first();
        $lop = Lop::first() ?? Lop::create([
            'MaLop'   => '12DHTH01',
            'TenLop'  => '12 Đại học Tin học 01',
            'MaKhoa'  => $khoa->MaKhoa,
            'MaNganh' => $nganh?->MaNganh,
            'KhoaHoc' => '2023-2027',
        ]);

        $this->command->info("--- Bắt đầu tạo 5 Đề tài, 5 Nhóm và 15 Sinh viên cho GV {$gv->HoTen} ({$maGV}) ---");

        // 15 Sinh viên (3 SV / nhóm)
        $studentsData = [
            // Nhóm 1 (Đã nộp báo cáo toàn văn PDF - Chờ GV duyệt)
            ['ma' => '2001230201', 'ten' => 'Nguyễn Văn An',   'user' => 'sv_test01', 'email' => 'sv_test01@huit.edu.vn', 'role' => 'Trưởng nhóm', 'group' => 1],
            ['ma' => '2001230202', 'ten' => 'Trần Thị Bích',   'user' => 'sv_test02', 'email' => 'sv_test02@huit.edu.vn', 'role' => 'Thành viên',  'group' => 1],
            ['ma' => '2001230203', 'ten' => 'Lê Hoàng Cảnh',   'user' => 'sv_test03', 'email' => 'sv_test03@huit.edu.vn', 'role' => 'Thành viên',  'group' => 1],

            // Nhóm 2 (Chưa nộp báo cáo toàn văn - Nút duyệt bị khóa)
            ['ma' => '2001230204', 'ten' => 'Phạm Minh Đức',   'user' => 'sv_test04', 'email' => 'sv_test04@huit.edu.vn', 'role' => 'Trưởng nhóm', 'group' => 2],
            ['ma' => '2001230205', 'ten' => 'Hoàng Thúy Hằng', 'user' => 'sv_test05', 'email' => 'sv_test05@huit.edu.vn', 'role' => 'Thành viên',  'group' => 2],
            ['ma' => '2001230206', 'ten' => 'Võ Quốc Huy',     'user' => 'sv_test06', 'email' => 'sv_test06@huit.edu.vn', 'role' => 'Thành viên',  'group' => 2],

            // Nhóm 3 (Đang Yêu cầu chỉnh sửa - Có thể test sinh viên nộp báo cáo)
            ['ma' => '2001230207', 'ten' => 'Đỗ Thanh Khang',  'user' => 'sv_test07', 'email' => 'sv_test07@huit.edu.vn', 'role' => 'Trưởng nhóm', 'group' => 3],
            ['ma' => '2001230208', 'ten' => 'Bùi Mỹ Linh',     'user' => 'sv_test08', 'email' => 'sv_test08@huit.edu.vn', 'role' => 'Thành viên',  'group' => 3],
            ['ma' => '2001230209', 'ten' => 'Ngô Trọng Nghĩa', 'user' => 'sv_test09', 'email' => 'sv_test09@huit.edu.vn', 'role' => 'Thành viên',  'group' => 3],

            // Nhóm 4 (Đã có báo cáo & Đã duyệt bảo vệ - Sẵn sàng xếp Hội đồng)
            ['ma' => '2001230210', 'ten' => 'Dương Thái Phong','user' => 'sv_test10', 'email' => 'sv_test10@huit.edu.vn', 'role' => 'Trưởng nhóm', 'group' => 4],
            ['ma' => '2001230211', 'ten' => 'Lý Ngọc Quyên',   'user' => 'sv_test11', 'email' => 'sv_test11@huit.edu.vn', 'role' => 'Thành viên',  'group' => 4],
            ['ma' => '2001230212', 'ten' => 'Trịnh Văn Sang',  'user' => 'sv_test12', 'email' => 'sv_test12@huit.edu.vn', 'role' => 'Thành viên',  'group' => 4],

            // Nhóm 5 (Chưa có báo cáo - Dùng để test chặn API / chặn gán Hội đồng)
            ['ma' => '2001230213', 'ten' => 'Vũ Phương Thảo',  'user' => 'sv_test13', 'email' => 'sv_test13@huit.edu.vn', 'role' => 'Trưởng nhóm', 'group' => 5],
            ['ma' => '2001230214', 'ten' => 'Đoàn Quốc Tuấn',  'user' => 'sv_test14', 'email' => 'sv_test14@huit.edu.vn', 'role' => 'Thành viên',  'group' => 5],
            ['ma' => '2001230215', 'ten' => 'Huỳnh Hải Yến',   'user' => 'sv_test15', 'email' => 'sv_test15@huit.edu.vn', 'role' => 'Thành viên',  'group' => 5],
        ];

        // Tạo tài khoản và hồ sơ sinh viên
        foreach ($studentsData as $svItem) {
            $maTK = 'TK_' . strtoupper($svItem['user']);
            
            TaiKhoan::updateOrCreate(
                ['TenDangNhap' => $svItem['user']],
                [
                    'MaTK'      => $maTK,
                    'MatKhau'   => bcrypt('123456'),
                    'MaVaiTro'  => 'VT03', // Sinh viên
                    'TrangThai' => true,
                ]
            );

            SinhVien::updateOrCreate(
                ['MaSV' => $svItem['ma']],
                [
                    'MaTK'        => $maTK,
                    'HoTen'       => $svItem['ten'],
                    'Email'       => $svItem['email'],
                    'MaKhoa'      => $khoa->MaKhoa,
                    'MaNganh'     => $nganh?->MaNganh,
                    'MaLop'       => $lop->MaLop,
                    'KhoaHoc'     => '2023-2027',
                    'TrangThai'   => 'Đang học',
                    'SoTinChiTichLuy' => 125,
                    'DiemTichLuy' => 3.25,
                ]
            );
        }

        // Thông tin 5 đề tài & kịch bản kiểm thử
        $topicsData = [
            1 => [
                'ma_dt'     => 'DT_TEST_01',
                'ten_dt'    => 'Xây dựng ứng dụng quản lý tài chính cá nhân thông minh với Flutter và AI',
                'ma_nhom'   => 'NH_TEST_01',
                'ten_nhom'  => 'Nhóm Test 01 - Smart Finance',
                'ma_hoso'   => 'HSBV_T01',
                'has_rep'   => true,
                'xac_nhan'  => 'Chờ xác nhận',
                'trang_thai'=> 'Chờ GVHD kiểm tra',
                'scenario'  => 'Đã nộp báo cáo PDF -> Giảng viên vào duyệt được thành công',
            ],
            2 => [
                'ma_dt'     => 'DT_TEST_02',
                'ten_dt'    => 'Hệ thống điểm danh sinh viên bằng nhận diện khuôn mặt kết hợp thiết bị IoT ESP32',
                'ma_nhom'   => 'NH_TEST_02',
                'ten_nhom'  => 'Nhóm Test 02 - IoT FaceID',
                'ma_hoso'   => 'HSBV_T02',
                'has_rep'   => false,
                'xac_nhan'  => 'Chờ xác nhận',
                'trang_thai'=> 'Chờ GVHD kiểm tra',
                'scenario'  => 'Chưa có báo cáo -> Nút duyệt bị khóa, giảng viên chọn yêu cầu sửa',
            ],
            3 => [
                'ma_dt'     => 'DT_TEST_03',
                'ten_dt'    => 'Nền tảng thương mại điện tử mua bán sách cũ trực tuyến tích hợp cổng thanh toán VNPay',
                'ma_nhom'   => 'NH_TEST_03',
                'ten_nhom'  => 'Nhóm Test 03 - BookStore VNPay',
                'ma_hoso'   => 'HSBV_T03',
                'has_rep'   => false,
                'xac_nhan'  => 'Yêu cầu chỉnh sửa',
                'trang_thai'=> 'Yêu cầu chỉnh sửa',
                'scenario'  => 'Đang yêu cầu sửa -> Test sinh viên sv_test07 nộp báo cáo toàn văn',
            ],
            4 => [
                'ma_dt'     => 'DT_TEST_04',
                'ten_dt'    => 'Ứng dụng phân tích dữ liệu mạng xã hội cảnh báo sớm thông tin sai lệch bằng Deep Learning',
                'ma_nhom'   => 'NH_TEST_04',
                'ten_nhom'  => 'Nhóm Test 04 - Social FakeNews',
                'ma_hoso'   => 'HSBV_T04',
                'has_rep'   => true,
                'xac_nhan'  => 'Đã duyệt',
                'trang_thai'=> 'Đã phê duyệt',
                'scenario'  => 'Đã có báo cáo & Đã duyệt -> Hiển thị ở bước 2 khi Trưởng khoa lập Hội đồng',
            ],
            5 => [
                'ma_dt'     => 'DT_TEST_05',
                'ten_dt'    => 'Hệ thống giám sát và điều khiển nhà thông minh Smart Home qua giao thức MQTT',
                'ma_nhom'   => 'NH_TEST_05',
                'ten_nhom'  => 'Nhóm Test 05 - Smart Home MQTT',
                'ma_hoso'   => 'HSBV_T05',
                'has_rep'   => false,
                'xac_nhan'  => 'Chờ xác nhận',
                'trang_thai'=> 'Chờ GVHD kiểm tra',
                'scenario'  => 'Chưa có báo cáo -> Test gọi thẳng POST request duyệt bị chặn với lỗi 422',
            ],
        ];

        // Chuẩn bị tệp PDF mẫu trong storage
        $samplePdfSource = storage_path('app/public/hoso/N04/eUWkVP68uHmeVIdoDCGf6TcnnaWDqcY9q4fb8o1y.pdf');
        $fallbackSample = storage_path('app/public/baocao/baocao_dot1_v1_1792412827.pdf');
        $validSourcePdf = file_exists($samplePdfSource) ? $samplePdfSource : (file_exists($fallbackSample) ? $fallbackSample : null);

        foreach ($topicsData as $gIdx => $gInfo) {
            // 1. Tạo Đề tài
            $deTai = DeTai::updateOrCreate(
                ['MaDeTai' => $gInfo['ma_dt']],
                [
                    'TenDeTai'              => $gInfo['ten_dt'],
                    'MoTa'                  => 'Mô tả chi tiết nội dung đề tài nghiên cứu và phát triển phần mềm.',
                    'YeuCau'                => 'Hoàn thiện mã nguồn, sản phẩm chạy thực tế và báo cáo theo chuẩn.',
                    'LinhVuc'               => 'Công nghệ phần mềm & Hệ thống thông tin',
                    'SoLuongSinhVienToiDa'  => 3,
                    'MaNganh'               => $nganh?->MaNganh,
                    'TrangThai'             => 'Đã công bố',
                    'MaGV'                  => $maGV,
                    'MaHocKy'               => $hocKy->MaHocKy,
                    'MaHocPhan'             => $hocPhan?->MaHocPhan,
                    'NgayCongBo'            => now()->subDays(20),
                    'NgayDuyet'             => now()->subDays(25),
                ]
            );

            // 2. Tìm danh sách sinh viên của nhóm
            $groupMembers = array_filter($studentsData, fn($s) => $s['group'] === $gIdx);
            $leader = collect($groupMembers)->firstWhere('role', 'Trưởng nhóm');

            // 3. Tạo Nhóm
            $nhom = Nhom::updateOrCreate(
                ['MaNhom' => $gInfo['ma_nhom']],
                [
                    'TenNhom'       => $gInfo['ten_nhom'],
                    'TrangThai'     => 'Đã duyệt',
                    'NgayTao'       => now()->subDays(20),
                    'MaHocKy'       => $hocKy->MaHocKy,
                    'MaHocPhan'     => $hocPhan?->MaHocPhan,
                ]
            );

            // 4. Tạo Phiếu Đăng Ký Đề Tài (gán Đề tài vào Nhóm)
            PhieuDangKy::updateOrCreate(
                ['MaNhom' => $nhom->MaNhom, 'MaDeTai' => $deTai->MaDeTai],
                [
                    'MaDangKy'    => 'PDK_' . $nhom->MaNhom,
                    'NgayDangKy'  => now()->subDays(20),
                    'TrangThai'   => 'Đã duyệt',
                    'NgayDuyet'   => now()->subDays(18),
                ]
            );

            // 5. Gán Thành viên vào Nhóm
            foreach ($groupMembers as $memberItem) {
                ThanhVienNhom::updateOrCreate(
                    ['MaNhom' => $nhom->MaNhom, 'MaSV' => $memberItem['ma']],
                    [
                        'VaiTro'        => $memberItem['role'],
                        'TrangThai'     => 'da_tham_gia',
                        'NgayThamGia'   => now()->subDays(20),
                    ]
                );
            }

            // 6. Tạo đường dẫn file báo cáo nếu kịch bản có báo cáo
            $relPathBaoCao = null;
            if ($gInfo['has_rep']) {
                $targetDir = storage_path("app/public/hoso/{$nhom->MaNhom}");
                if (!File::isDirectory($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true, true);
                }
                $targetFile = "{$targetDir}/BaoCao_ToanVan_KLTN.pdf";
                if ($validSourcePdf && !file_exists($targetFile)) {
                    File::copy($validSourcePdf, $targetFile);
                } elseif (!file_exists($targetFile)) {
                    // Tạo tệp PDF placeholder nếu chưa có nguồn
                    file_put_contents($targetFile, "%PDF-1.4\n1 0 obj\n<< /Title (Bao Cao Khoa Luan) >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF");
                }
                $relPathBaoCao = "hoso/{$nhom->MaNhom}/BaoCao_ToanVan_KLTN.pdf";
            }

            // 7. Tạo Hồ Sơ Bảo Vệ
            $hoSo = HoSoBaoVe::updateOrCreate(
                ['MaHoSo' => $gInfo['ma_hoso']],
                [
                    'MaNhom'           => $nhom->MaNhom,
                    'MaDeTai'          => $deTai->MaDeTai,
                    'NgayLap'          => now()->subDays(15),
                    'NgayNop'          => now()->subDays(5),
                    'NgayNopGanNhat'   => now()->subHours(8),
                    'NgayXacNhan'      => ($gInfo['xac_nhan'] === 'Đã duyệt') ? now()->subDay() : null,
                    'XacNhanGVHD'      => $gInfo['xac_nhan'],
                    'TrangThai'        => $gInfo['trang_thai'],
                    'PhienBanHienTai'  => 1.0,
                    'LinkSourceCode'   => "https://github.com/huit-kltn/{$gInfo['ma_nhom']}-source",
                    'GhiChuSourceCode' => 'Mã nguồn đầy đủ frontend & backend đã kiểm tra chạy tốt.',
                    'LinkDeploy'       => ($gIdx % 2 !== 0) ? "https://{$gInfo['ma_nhom']}.demo-huit.vn" : null,
                    'LyDoKhongDeploy'  => ($gIdx % 2 === 0) ? 'Mô hình hệ thống phần cứng cục bộ, không triển khai online.' : null,
                    'ThanhPhanCanSua'  => ($gInfo['xac_nhan'] === 'Yêu cầu chỉnh sửa') ? ['bao_cao'] : null,
                    'NhanXetGVHD'      => ($gInfo['xac_nhan'] === 'Yêu cầu chỉnh sửa') 
                        ? 'Yêu cầu nhóm bổ sung tệp Báo cáo khóa luận toàn văn (định dạng PDF) theo đúng quy định của Khoa.' 
                        : (($gInfo['xac_nhan'] === 'Đã duyệt') ? 'Báo cáo và sản phẩm đạt yêu cầu. Đồng ý cho nhóm bảo vệ trước Hội đồng.' : null),
                    'HanNopLai'        => ($gInfo['xac_nhan'] === 'Yêu cầu chỉnh sửa') ? now()->addDays(5) : null,
                    'NguoiDuyet'       => ($gInfo['xac_nhan'] === 'Đã duyệt') ? $maGV : null,
                ]
            );

            // 8. Tạo Snapshot Phiên bản hồ sơ bảo vệ
            PhienBanHoSoBaoVe::updateOrCreate(
                ['MaHoSo' => $hoSo->MaHoSo, 'PhienBan' => '1.0'],
                [
                    'TenFileBaoCao'    => $relPathBaoCao ? 'BaoCao_ToanVan_KLTN.pdf' : null,
                    'DuongDanBaoCao'   => $relPathBaoCao,
                    'DungLuongBaoCao'  => $relPathBaoCao ? 2048576 : null,
                    'LinkSourceCode'   => $hoSo->LinkSourceCode,
                    'LinkDeploy'       => $hoSo->LinkDeploy,
                    'LyDoKhongDeploy'  => $hoSo->LyDoKhongDeploy,
                    'TyLeTurnitin'     => 12.5,
                    'NgayNop'          => now()->subDays(5),
                    'NguoiNop'         => $leader['ma'],
                    'TrangThai'        => $gInfo['trang_thai'],
                    'NhanXetGVHD'      => $hoSo->NhanXetGVHD,
                ]
            );

            // 9. Tạo bản ghi TepHoSoBaoVe nếu có tệp báo cáo
            if ($relPathBaoCao) {
                TepHoSoBaoVe::updateOrCreate(
                    ['MaHoSo' => $hoSo->MaHoSo, 'LoaiTep' => 'Khóa luận toàn văn'],
                    [
                        'MaTep'        => 'TEP_' . $hoSo->MaHoSo . '_TV',
                        'TenTep'       => 'Báo cáo khóa luận toàn văn.pdf',
                        'DuongDanFile' => $relPathBaoCao,
                        'PhienBan'     => '1.0',
                        'NgayNop'      => now()->subDays(5)->toDateString(),
                        'TrangThai'    => 'Đã nộp',
                    ]
                );
            }

            $this->command->line("   [OK] Đã tạo Nhóm {$gIdx}: {$nhom->MaNhom} | Hồ sơ: {$hoSo->MaHoSo} ({$gInfo['scenario']})");
        }

        $this->command->info("================================================================================");
        $this->command->info("✅ ĐÃ KHỞI TẠO THÀNH CÔNG 5 ĐỀ TÀI, 5 NHÓM VÀ 15 SINH VIÊN!");
        $this->command->info("   - Giảng viên hướng dẫn: {$gv->HoTen} (Tài khoản: {$gv->MaGV} / Mật khẩu: 123456)");
        $this->command->info("   - Tài khoản sinh viên: sv_test01 đến sv_test15 (Mật khẩu: 123456)");
        $this->command->info("================================================================================");
    }
}
