<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. VaiTrố
        DB::table('vai_tros')->insertOrIgnore([
            ['MaVaiTro' => 'VT01', 'TenVaiTro' => 'Giáo vụ', 'created_at' => now(), 'updated_at' => now()],
            ['MaVaiTro' => 'VT02', 'TenVaiTro' => 'Giảng viên', 'created_at' => now(), 'updated_at' => now()],
            ['MaVaiTro' => 'VT03', 'TenVaiTro' => 'Sinh viên', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. TaiKhoan (Giáo vụ: giaovu01, Giảng viên: gv01 - gv10, Sinh viên: sv01 - sv50)
        $accounts = [
            [
                'MaTK' => 'TK01',
                'MaVaiTro' => 'VT01',
                'TenDangNhap' => 'giaovu01',
                'MatKhau' => Hash::make('123456'),
                'TrangThai' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'MaTK' => 'TK00',
                'MaVaiTro' => 'VT01',
                'TenDangNhap' => 'admin',
                'MatKhau' => Hash::make('123456'),
                'TrangThai' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // 10 Giảng viên
        for ($i = 1; $i <= 10; $i++) {
            $idStr = str_pad($i, 2, '0', STR_PAD_LEFT);
            $accounts[] = [
                'MaTK' => 'TK_GV' . $idStr,
                'MaVaiTro' => 'VT02',
                'TenDangNhap' => 'gv' . $idStr,
                'MatKhau' => Hash::make('123456'),
                'TrangThai' => true,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // 50 Sinh viên
        for ($i = 1; $i <= 50; $i++) {
            $idStr = str_pad($i, 2, '0', STR_PAD_LEFT);
            $accounts[] = [
                'MaTK' => 'TK_SV' . $idStr,
                'MaVaiTro' => 'VT03',
                'TenDangNhap' => 'sv' . $idStr,
                'MatKhau' => Hash::make('123456'),
                'TrangThai' => true,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('tai_khoans')->insertOrIgnore($accounts);

        // 3. Khoa
        DB::table('khoas')->insertOrIgnore([
            ['MaKhoa' => 'K01', 'TenKhoa' => 'Khoa Công Nghệ Thông Tin', 'created_at' => now(), 'updated_at' => now()]
        ]);

        // 4. BoMon
        DB::table('bo_mons')->insertOrIgnore([
            ['MaBoMon' => 'BM01', 'TenBoMon' => 'Công Nghệ Phần Mềm', 'MaKhoa' => 'K01', 'created_at' => now(), 'updated_at' => now()],
            ['MaBoMon' => 'BM02', 'TenBoMon' => 'Khoa Học Máy Tính', 'MaKhoa' => 'K01', 'created_at' => now(), 'updated_at' => now()],
            ['MaBoMon' => 'BM03', 'TenBoMon' => 'Mạng Máy Tính & An Ninh Thông Tin', 'MaKhoa' => 'K01', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 5. Nganh
        DB::table('nganhs')->insertOrIgnore([
            ['MaNganh' => 'NG01', 'TenNganh' => 'Công Nghệ Thông Tin', 'MaKhoa' => 'K01', 'created_at' => now(), 'updated_at' => now()],
            ['MaNganh' => 'NG02', 'TenNganh' => 'Khoa Học Máy Tính', 'MaKhoa' => 'K01', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 6. Lop
        DB::table('lops')->insertOrIgnore([
            ['MaLop' => 'L01', 'TenLop' => '14DHTH05', 'MaNganh' => 'NG01', 'KhoaHoc' => '2023-2027', 'created_at' => now(), 'updated_at' => now()],
            ['MaLop' => 'L02', 'TenLop' => '14DHTH11', 'MaNganh' => 'NG01', 'KhoaHoc' => '2023-2027', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 7. HocKy
        DB::table('hoc_kies')->insertOrIgnore([
            [
                'MaHocKy' => 'HK01',
                'TenHocKy' => 'Học Kỳ 1',
                'NamHoc' => '2025-2026',
                'NgayBatDau' => '2025-09-01',
                'NgayKetThuc' => '2026-01-15',
                'TrangThai' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 8. GiaoVu
        DB::table('giao_vus')->insertOrIgnore([
            [
                'MaGVu' => 'GVU01',
                'MaTK' => 'TK01',
                'MaKhoa' => 'K01',
                'HoTen' => 'ThS. Nguyễn Văn Lễ',
                'Email' => 'giaovu.cntt@huit.edu.vn',
                'SoDienThoai' => '0901112233',
                'ChucVu' => 'Giáo vụ Khoa',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 9. GiangVien (10 Giảng viên)
        $giangViens = [];
        $names = ['Nguyễn Thanh Truyền', 'Trần Văn Hùng', 'Lê Thị Mai', 'Phạm Hoàng Nam', 'Đặng Quốc Bảo', 'Vũ Thị Hồng', 'Bùi Anh Tuấn', 'Hoàng Minh Trí', 'Đỗ Ngọc Trinh', 'Ngô Quốc Cường'];
        for ($i = 1; $i <= 10; $i++) {
            $idStr = str_pad($i, 2, '0', STR_PAD_LEFT);
            $giangViens[] = [
                'MaGV' => 'GV' . $idStr,
                'MaTK' => 'TK_GV' . $idStr,
                'MaBoMon' => ($i % 3 == 1) ? 'BM01' : (($i % 3 == 2) ? 'BM02' : 'BM03'),
                'MaSoCanBo' => 'CB' . $idStr,
                'HoTen' => 'ThS. ' . $names[$i - 1],
                'Email' => 'gv' . $idStr . '@huit.edu.vn',
                'SoDienThoai' => '09030000' . $idStr,
                'HocHam' => ($i == 1) ? 'Phó Giáo sư' : 'Không',
                'HocVi' => ($i <= 3) ? 'Tiến sĩ' : 'Thạc sĩ',
                'ChuyenNganh' => 'Công Nghệ Phần Mềm',
                'TrangThai' => true,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('giang_viens')->insertOrIgnore($giangViens);

        // 10. SinhVien (50 Sinh viên)
        $sinhViens = [
            [
                'MaSV' => 'SV01',
                'MaTK' => 'TK_SV01',
                'MaLop' => 'L01',
                'MaSoSinhVien' => '2001230106',
                'HoTen' => 'Hồ Chí Dũng',
                'Email' => 'sv01@st.huit.edu.vn',
                'SoDienThoai' => '0987654321',
                'KhoaHoc' => '14DHTH',
                'SoTinChiTichLuy' => 130,
                'DiemTichLuy' => 3.45,
                'TrangThai' => 'Đang học',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'MaSV' => 'SV02',
                'MaTK' => 'TK_SV02',
                'MaLop' => 'L01',
                'MaSoSinhVien' => '2001230136',
                'HoTen' => 'Nguyễn Thị Thùy Dương',
                'Email' => 'sv02@st.huit.edu.vn',
                'SoDienThoai' => '0987654322',
                'KhoaHoc' => '14DHTH',
                'SoTinChiTichLuy' => 132,
                'DiemTichLuy' => 3.60,
                'TrangThai' => 'Đang học',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'MaSV' => 'SV03',
                'MaTK' => 'TK_SV03',
                'MaLop' => 'L02',
                'MaSoSinhVien' => '2001230634',
                'HoTen' => 'La Thuận Phát',
                'Email' => 'sv03@st.huit.edu.vn',
                'SoDienThoai' => '0987654323',
                'KhoaHoc' => '14DHTH',
                'SoTinChiTichLuy' => 128,
                'DiemTichLuy' => 3.30,
                'TrangThai' => 'Đang học',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        for ($i = 4; $i <= 50; $i++) {
            $idStr = str_pad($i, 2, '0', STR_PAD_LEFT);
            $sinhViens[] = [
                'MaSV' => 'SV' . $idStr,
                'MaTK' => 'TK_SV' . $idStr,
                'MaLop' => ($i % 2 == 0) ? 'L01' : 'L02',
                'MaSoSinhVien' => '200123' . str_pad($i + 100, 4, '0', STR_PAD_LEFT),
                'HoTen' => 'Sinh Viên ' . $i,
                'Email' => 'sv' . $idStr . '@st.huit.edu.vn',
                'SoDienThoai' => '09870000' . $idStr,
                'KhoaHoc' => '14DHTH',
                'SoTinChiTichLuy' => 125 + ($i % 10),
                'DiemTichLuy' => 3.0 + (($i % 10) * 0.08),
                'TrangThai' => 'Đang học',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('sinh_viens')->insertOrIgnore($sinhViens);

        // 11. DanhSachSVDuDieuKien
        $duDieuKien = [];
        for ($i = 1; $i <= 50; $i++) {
            $idStr = str_pad($i, 2, '0', STR_PAD_LEFT);
            $duDieuKien[] = [
                'MaSV' => 'SV' . $idStr,
                'MaHocKy' => 'HK01',
                'NgayXetDuyet' => '2025-09-05',
                'TrangThai' => 'Đủ điều kiện',
                'GhiChu' => 'Đạt chuẩn tín chỉ tích lũy >= 120',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('danh_sach_sv_du_dieu_kiens')->insertOrIgnore($duDieuKien);

        // 12. ChiTieuHuongDan
        $chiTieu = [];
        for ($i = 1; $i <= 10; $i++) {
            $idStr = str_pad($i, 2, '0', STR_PAD_LEFT);
            $chiTieu[] = [
                'MaGV' => 'GV' . $idStr,
                'MaHocKy' => 'HK01',
                'SoNhomToiDa' => 4,
                'NgayPhanBo' => '2025-09-05',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('chi_tieu_huong_dans')->insertOrIgnore($chiTieu);

        // 13. KeHoachKhoaLuan
        DB::table('ke_hoach_khoa_luans')->insertOrIgnore([
            [
                'MaKeHoach' => 'KH01',
                'MaHocKy' => 'HK01',
                'MaGVu' => 'GVU01',
                'TenKeHoach' => 'Kế hoạch Triển khai Khóa luận Tốt nghiệp Đại học Khóa 14DHTH',
                'NoiDung' => 'Kế hoạch triển khai công tác khóa luận tốt nghiệp HK1 2025-2026 cho Khoa CNTT HUIT.',
                'TrangThai' => 'Đã công bố',
                'NgayTao' => '2025-09-01',
                'NgayCongBo' => '2025-09-05',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 14. MocThoiGianKhoaLuan
        DB::table('moc_thoi_gian_khoa_luans')->insertOrIgnore([
            ['MaMoc' => 'MOC01', 'MaKeHoach' => 'KH01', 'TenMoc' => 'Đề xuất & Phê duyệt Đề tài', 'NgayBatDau' => '2025-09-05', 'NgayKetThuc' => '2025-09-15', 'MoTa' => 'Giảng viên đăng tải đề tài', 'created_at' => now(), 'updated_at' => now()],
            ['MaMoc' => 'MOC02', 'MaKeHoach' => 'KH01', 'TenMoc' => 'Đăng ký Đề tài Sinh viên', 'NgayBatDau' => '2025-09-16', 'NgayKetThuc' => '2025-09-25', 'MoTa' => 'Sinh viên lập nhóm và chọn đề tài', 'created_at' => now(), 'updated_at' => now()],
            ['MaMoc' => 'MOC03', 'MaKeHoach' => 'KH01', 'TenMoc' => 'Báo cáo Tiến độ Định kỳ', 'NgayBatDau' => '2025-10-01', 'NgayKetThuc' => '2025-12-15', 'MoTa' => 'Nộp báo cáo tuần & tóm tắt AI', 'created_at' => now(), 'updated_at' => now()],
            ['MaMoc' => 'MOC04', 'MaKeHoach' => 'KH01', 'TenMoc' => 'Nộp Hồ sơ Bảo vệ & Turnitin', 'NgayBatDau' => '2025-12-16', 'NgayKetThuc' => '2025-12-25', 'MoTa' => 'Kiểm tra tỷ lệ đạo văn < 20%', 'created_at' => now(), 'updated_at' => now()],
            ['MaMoc' => 'MOC05', 'MaKeHoach' => 'KH01', 'TenMoc' => 'Tổ chức Bảo vệ Hội đồng', 'NgayBatDau' => '2026-01-05', 'NgayKetThuc' => '2026-01-12', 'MoTa' => 'Chấm điểm Hội đồng & công bố kết quả', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 15. QuyDinhKhoaLuan
        DB::table('quy_dinh_khoa_luans')->insertOrIgnore([
            ['MaQuyDinh' => 'QD01', 'MaKeHoach' => 'KH01', 'TenQuyDinh' => 'Tỷ lệ trùng lặp đạo văn Turnitin tối đa', 'GiaTri' => '20%', 'MoTa' => 'Không vượt quá 20% tổng số từ', 'created_at' => now(), 'updated_at' => now()],
            ['MaQuyDinh' => 'QD02', 'MaKeHoach' => 'KH01', 'TenQuyDinh' => 'Số lượng sinh viên tối đa / nhóm', 'GiaTri' => '3 sinh viên', 'MoTa' => 'Mỗi nhóm từ 1 đến 3 thành viên', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 16. BieuMau
        DB::table('bieu_maus')->insertOrIgnore([
            ['MaBieuMau' => 'BM01', 'MaKeHoach' => 'KH01', 'TenBieuMau' => 'Mẫu Báo cáo Khóa luận Tốt nghiệp HUIT', 'DuongDanFile' => 'templates/mau_bao_cao_khoa_luan.docx', 'created_at' => now(), 'updated_at' => now()],
            ['MaBieuMau' => 'BM02', 'MaKeHoach' => 'KH01', 'TenBieuMau' => 'Phiếu Đăng ký Đề tài Khóa luận', 'DuongDanFile' => 'templates/phieu_dang_ky_de_tai.docx', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 17. DeTai (10 Đề tài mẫu)
        $deTais = [
            [
                'MaDeTai' => 'DT01',
                'MaGV' => 'GV01',
                'TenDeTai' => 'Xây dựng hệ thống quản lý công tác khóa luận tốt nghiệp tại Trường Đại học Công Thương TP.HCM',
                'MoTa' => 'Nghiên cứu ứng dụng Laravel & AI trong quản lý công tác khóa luận tốt nghiệp.',
                'YeuCau' => 'Thạo Laravel, MySQL, hiểu quy trình hội đồng bảo vệ.',
                'LinhVuc' => 'Công Nghệ Phần Mềm',
                'SoLuongSinhVienToiDa' => 3,
                'MaHocKy' => 'HK01',
                'TrangThai' => 'Đã duyệt',
                'NgayDeXuat' => '2025-09-06',
                'NgayDuyet' => '2025-09-10',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        for ($i = 2; $i <= 10; $i++) {
            $idStr = str_pad($i, 2, '0', STR_PAD_LEFT);
            $deTais[] = [
                'MaDeTai' => 'DT' . $idStr,
                'MaGV' => 'GV' . str_pad(($i % 10) + 1, 2, '0', STR_PAD_LEFT),
                'TenDeTai' => 'Đề tài ứng dụng Công nghệ phần mềm số ' . $i,
                'MoTa' => 'Mô tả chi tiết đề tài nghiên cứu số ' . $i,
                'YeuCau' => 'Yêu cầu kỹ thuật và công nghệ ứng dụng.',
                'LinhVuc' => 'Công Nghệ Phần Mềm',
                'SoLuongSinhVienToiDa' => 3,
                'MaHocKy' => 'HK01',
                'TrangThai' => 'Đã duyệt',
                'NgayDeXuat' => '2025-09-06',
                'NgayDuyet' => '2025-09-10',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('de_tais')->insertOrIgnore($deTais);

        // 18. Nhom (Nhóm N01: Hồ Chí Dũng, Nguyễn Thị Thùy Dương, La Thuận Phát)
        DB::table('nhoms')->insertOrIgnore([
            [
                'MaNhom' => 'N01',
                'MaDeTai' => 'DT01',
                'MaTruongNhom' => 'SV01',
                'TenNhom' => 'Nhóm Khóa Luận CNTT HUIT - N01',
                'TrangThai' => 'Đã duyệt',
                'NgayTao' => '2025-09-17',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 19. ThanhVienNhom
        DB::table('thanh_vien_nhoms')->insertOrIgnore([
            ['MaNhom' => 'N01', 'MaSV' => 'SV01', 'VaiTro' => 'Trưởng nhóm', 'NgayThamGia' => '2025-09-17', 'created_at' => now(), 'updated_at' => now()],
            ['MaNhom' => 'N01', 'MaSV' => 'SV02', 'VaiTro' => 'Thành viên', 'NgayThamGia' => '2025-09-17', 'created_at' => now(), 'updated_at' => now()],
            ['MaNhom' => 'N01', 'MaSV' => 'SV03', 'VaiTro' => 'Thành viên', 'NgayThamGia' => '2025-09-17', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 20. DangKyDeTai
        DB::table('dang_ky_de_tais')->insertOrIgnore([
            [
                'MaDangKy' => 'DK01',
                'MaNhom' => 'N01',
                'MaDeTai' => 'DT01',
                'MaGVHuongDan' => 'GV01',
                'NgayDangKy' => '2025-09-18',
                'TrangThai' => 'Đã duyệt',
                'NgayDuyet' => '2025-09-20',
                'GhiChu' => 'Hợp lệ và đủ điều kiện',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 21. BaoCaoTienDo
        DB::table('bao_cao_tien_dos')->insertOrIgnore([
            [
                'MaBaoCao' => 'BC01',
                'MaNhom' => 'N01',
                'LanBaoCao' => 1,
                'TieuDe' => 'Báo cáo Tiến độ Tuần 1 - Khảo sát hệ thống và Thiết kế CSDL 31 bảng',
                'NoiDungBaoCao' => 'Đã hoàn thành khảo sát thực trạng công tác khóa luận HUIT và thiết kế mô hình CSDL 31 bảng.',
                'NgayNop' => '2025-10-15',
                'TenFile' => 'BaoCaoTienDo_Tuan1_N01.docx',
                'DuongDanFile' => 'uploads/baocao/BaoCaoTienDo_Tuan1_N01.docx',
                'TrangThai' => 'Đã duyệt',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 22. TomTatBaoCao (AI / LLM Summary Integration)
        DB::table('tom_tat_bao_caos')->insertOrIgnore([
            [
                'MaTomTat' => 'TT01',
                'MaBaoCao' => 'BC01',
                'CongViecDaHoanThanh' => 'Khảo sát quy trình nghiệp vụ HUIT, hoàn thiện 31 bảng CSDL.',
                'KhoKhan' => 'Tối ưu hóa các mối quan hệ khóa ngoại phức tạp.',
                'KeHoachTuanToi' => 'Lập trình Controller, Route và giao diện chấm điểm Hội đồng.',
                'NoiDungAI' => '[AI Summary]: Nhóm N01 hoàn thành 100% mục tiêu Tuần 1 đúng tiến độ.',
                'DoTinCayAI' => 98.50,
                'NgayTomTat' => now(),
                'TrangThai' => 'Đã tạo',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 23. NhanXet
        DB::table('nhan_xets')->insertOrIgnore([
            [
                'MaNhanXet' => 'NX01',
                'MaBaoCao' => 'BC01',
                'MaGV' => 'GV01',
                'NoiDung' => 'Tiến độ làm việc rất tốt, mô hình CSDL thiết kế chuẩn xác.',
                'LoaiNhanXet' => 'Đạt',
                'NgayNhanXet' => now(),
                'TrangThai' => 'Đã nhận xét',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 24. TienDo
        DB::table('tien_dos')->insertOrIgnore([
            [
                'MaTienDo' => 'TD01',
                'MaNhom' => 'N01',
                'MaMoc' => 'MOC03',
                'TyLeHoanThanh' => 85,
                'TrangThai' => 'Đúng tiến độ',
                'NgayCapNhat' => now(),
                'GhiChu' => 'Chuẩn bị nộp hồ sơ bảo vệ Turnitin',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 25. HoiDong
        DB::table('hoi_dongs')->insertOrIgnore([
            [
                'MaHoiDong' => 'HD01',
                'TenHoiDong' => 'Hội đồng Bảo vệ Khóa luận Tốt nghiệp Số 01 - Khoa CNTT',
                'ThoiGianBatDau' => '2026-01-08 08:00:00',
                'ThoiGianKetThuc' => '2026-01-08 12:00:00',
                'DiaDiem' => 'Phòng Hội Thảo A301 - HUIT',
                'TrangThai' => 'Đã kết thúc',
                'GhiChu' => 'Chấm bảo vệ khóa luận cử nhân CNTT',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 26. ThanhVienHoiDong
        DB::table('thanh_vien_hoi_dongs')->insertOrIgnore([
            ['MaHoiDong' => 'HD01', 'MaGV' => 'GV01', 'VaiTro' => 'Chủ tịch', 'created_at' => now(), 'updated_at' => now()],
            ['MaHoiDong' => 'HD01', 'MaGV' => 'GV02', 'VaiTro' => 'Thư ký', 'created_at' => now(), 'updated_at' => now()],
            ['MaHoiDong' => 'HD01', 'MaGV' => 'GV03', 'VaiTro' => 'Phản biện 1', 'created_at' => now(), 'updated_at' => now()],
            ['MaHoiDong' => 'HD01', 'MaGV' => 'GV04', 'VaiTro' => 'Phản biện 2', 'created_at' => now(), 'updated_at' => now()],
            ['MaHoiDong' => 'HD01', 'MaGV' => 'GV05', 'VaiTro' => 'Ủy viên', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 27. HoSoBaoVe
        DB::table('ho_so_bao_ves')->insertOrIgnore([
            [
                'MaHoSo' => 'HS01',
                'MaNhom' => 'N01',
                'MaHoiDong' => 'HD01',
                'MaGVPhanBien' => 'GV03',
                'XacNhanGVHD' => true,
                'NgayXacNhanGVHD' => '2025-12-20',
                'TyLeTrungLap' => 12.50,
                'MinhChungDaoVan' => 'uploads/turnitin/Turnitin_Report_N01.pdf',
                'TrangThai' => 'Đã phê duyệt',
                'NgayNop' => '2025-12-18',
                'NgayXacNhan' => '2025-12-22',
                'NguoiXacNhan' => 'GVU01',
                'GhiChu' => 'Hồ sơ đầy đủ, Turnitin 12.5% đạt chuẩn <20%',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 28. ChiTietDiemHoiDong
        DB::table('chi_tiet_diem_hoi_dongs')->insertOrIgnore([
            ['MaHoiDong' => 'HD01', 'MaGV' => 'GV01', 'MaSV' => 'SV01', 'Diem' => 9.0, 'NhanXet' => 'Báo cáo thuyết phục, trả lời xuất sắc', 'NgayCham' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['MaHoiDong' => 'HD01', 'MaGV' => 'GV02', 'MaSV' => 'SV01', 'Diem' => 8.8, 'NhanXet' => 'Mô hình CSDL 31 bảng rất chuẩn', 'NgayCham' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['MaHoiDong' => 'HD01', 'MaGV' => 'GV03', 'MaSV' => 'SV01', 'Diem' => 9.2, 'NhanXet' => 'Đáp ứng đầy đủ yêu cầu bài toán HUIT', 'NgayCham' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 29. KetQuaSinhVien
        DB::table('ket_qua_sinh_viens')->insertOrIgnore([
            [
                'MaKetQua' => 'KQ01',
                'MaSV' => 'SV01',
                'MaHocKy' => 'HK01',
                'DiemHuongDan' => 9.0,
                'DiemPhanBien' => 8.5,
                'DiemHoiDongTB' => 9.0,
                'DiemTongKet' => 8.83,
                'KetQua' => 'Đạt (Xuất sắc)',
                'NhanXetChung' => 'Chúc mừng sinh viên Hồ Chí Dũng hoàn thành khóa luận xuất sắc.',
                'NgayCham' => '2026-01-08',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'MaKetQua' => 'KQ02',
                'MaSV' => 'SV02',
                'MaHocKy' => 'HK01',
                'DiemHuongDan' => 9.2,
                'DiemPhanBien' => 9.0,
                'DiemHoiDongTB' => 9.1,
                'DiemTongKet' => 9.10,
                'KetQua' => 'Đạt (Xuất sắc)',
                'NhanXetChung' => 'Chúc mừng sinh viên Nguyễn Thị Thùy Dương hoàn thành xuất sắc.',
                'NgayCham' => '2026-01-08',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'MaKetQua' => 'KQ03',
                'MaSV' => 'SV03',
                'MaHocKy' => 'HK01',
                'DiemHuongDan' => 8.5,
                'DiemPhanBien' => 8.5,
                'DiemHoiDongTB' => 8.6,
                'DiemTongKet' => 8.53,
                'KetQua' => 'Đạt (Giỏi)',
                'NhanXetChung' => 'Chúc mừng sinh viên La Thuận Phát hoàn thành khóa luận.',
                'NgayCham' => '2026-01-08',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 30. ThongBao
        DB::table('thong_baos')->insertOrIgnore([
            [
                'MaThongBao' => 'TB01',
                'MaGVu' => 'GVU01',
                'TieuDe' => 'Thông báo Triển khai Kế hoạch Khóa luận Tốt nghiệp Học kỳ 1 (2025-2026)',
                'NoiDung' => 'Giáo vụ Khoa CNTT thông báo kế hoạch làm khóa luận cho toàn thể sinh viên và giảng viên.',
                'LoaiThongBao' => 'Chung',
                'DoiTuongNhan' => 'Tất cả',
                'NgayTao' => now(),
                'TrangThai' => 'Đã công bố',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // 31. NguoiNhanThongBao
        DB::table('nguoi_nhan_thong_baos')->insertOrIgnore([
            ['MaThongBao' => 'TB01', 'MaTK' => 'TK_SV01', 'DaDoc' => true, 'NgayDoc' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['MaThongBao' => 'TB01', 'MaTK' => 'TK_SV02', 'DaDoc' => true, 'NgayDoc' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
