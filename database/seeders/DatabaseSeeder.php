<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Dataset: 10 BoMon, 10 Nganh, 10 Lop, 10 MonHoc,
     *          10 GiangVien (gv01-gv10), 50 SinhVien (sv01-sv50),
     *          50 DeTai, 20 Nhom, AuditLog, ThongBao mẫu.
     */
    public function run(): void
    {
        // ======================================================
        // 1. VAI TRÒ
        // ======================================================
        DB::table('vai_tros')->updateOrInsert(['MaVaiTro' => 1], ['TenVaiTro' => 'Admin']);
        DB::table('vai_tros')->updateOrInsert(['MaVaiTro' => 2], ['TenVaiTro' => 'Giảng viên']);
        DB::table('vai_tros')->updateOrInsert(['MaVaiTro' => 3], ['TenVaiTro' => 'Sinh viên']);

        // ======================================================
        // 2. 10 BỘ MÔN
        // ======================================================
        $boMonData = [
            ['TenBoMon' => 'Công Nghệ Phần Mềm',          'MoTa' => 'Bộ môn nghiên cứu phát triển phần mềm'],
            ['TenBoMon' => 'Hệ Thống Thông Tin',           'MoTa' => 'Bộ môn quản trị hệ thống thông tin'],
            ['TenBoMon' => 'Mạng Máy Tính & An Ninh',      'MoTa' => 'Bộ môn hạ tầng mạng và bảo mật'],
            ['TenBoMon' => 'Trí Tuệ Nhân Tạo',             'MoTa' => 'Bộ môn AI, Machine Learning, Deep Learning'],
            ['TenBoMon' => 'Khoa Học Dữ Liệu',             'MoTa' => 'Bộ môn phân tích & khoa học dữ liệu'],
            ['TenBoMon' => 'Kỹ Thuật Máy Tính',            'MoTa' => 'Bộ môn kiến trúc và phần cứng máy tính'],
            ['TenBoMon' => 'Công Nghệ Web & Di Động',      'MoTa' => 'Bộ môn phát triển web và ứng dụng di động'],
            ['TenBoMon' => 'Điện Toán Đám Mây',            'MoTa' => 'Bộ môn DevOps, Cloud & hạ tầng ảo hóa'],
            ['TenBoMon' => 'Đảm Bảo Chất Lượng PM',        'MoTa' => 'Bộ môn kiểm thử và đảm bảo chất lượng'],
            ['TenBoMon' => 'Toán Ứng Dụng & Tin Học',      'MoTa' => 'Bộ môn toán học nền tảng cho CNTT'],
        ];
        $bmIds = [];
        foreach ($boMonData as $bm) {
            $bmIds[] = DB::table('bo_mons')->insertGetId(array_merge($bm, ['created_at' => now(), 'updated_at' => now()]));
        }

        // ======================================================
        // 3. 10 NGÀNH
        // ======================================================
        $nganhData = [
            ['TenNganh' => 'Công Nghệ Thông Tin',          'MoTa' => 'Đào tạo kỹ sư CNTT toàn diện'],
            ['TenNganh' => 'Kỹ Thuật Phần Mềm',            'MoTa' => 'Chuyên ngành phát triển phần mềm'],
            ['TenNganh' => 'An Toàn Thông Tin',             'MoTa' => 'Chuyên ngành bảo mật hệ thống'],
            ['TenNganh' => 'Khoa Học Máy Tính',             'MoTa' => 'Nghiên cứu khoa học máy tính'],
            ['TenNganh' => 'Hệ Thống Thông Tin',           'MoTa' => 'Quản trị và phân tích hệ thống'],
            ['TenNganh' => 'Trí Tuệ Nhân Tạo',             'MoTa' => 'Ứng dụng AI và dữ liệu lớn'],
            ['TenNganh' => 'Công Nghệ Đa Phương Tiện',     'MoTa' => 'Thiết kế đồ hoạ và media số'],
            ['TenNganh' => 'Điện Tử Viễn Thông',           'MoTa' => 'Hạ tầng viễn thông và IoT'],
            ['TenNganh' => 'Kỹ Thuật Máy Tính',            'MoTa' => 'Phần cứng và nhúng hệ thống'],
            ['TenNganh' => 'Quản Trị Mạng Máy Tính',       'MoTa' => 'Quản trị mạng doanh nghiệp'],
        ];
        $nganhIds = [];
        foreach ($nganhData as $nganh) {
            $nganhIds[] = DB::table('nganhs')->insertGetId(array_merge($nganh, ['created_at' => now(), 'updated_at' => now()]));
        }

        // ======================================================
        // 4. HỌC KỲ
        // ======================================================
        $maHocKy = DB::table('hoc_kies')->insertGetId([
            'TenHocKy'    => 'Học Kỳ 1 (2025-2026)',
            'NamHoc'      => '2025-2026',
            'NgayBatDau'  => '2025-09-01',
            'NgayKetThuc' => '2026-01-15',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // ======================================================
        // 5. 10 LỚP (mỗi lớp 1 ngành)
        // ======================================================
        $lopData = [
            ['TenLop' => '21DTH01', 'MaNganh' => $nganhIds[0], 'KhoaHoc' => '2021-2025'],
            ['TenLop' => '21DTH02', 'MaNganh' => $nganhIds[0], 'KhoaHoc' => '2021-2025'],
            ['TenLop' => '21KPM01', 'MaNganh' => $nganhIds[1], 'KhoaHoc' => '2021-2025'],
            ['TenLop' => '21ATTT01','MaNganh' => $nganhIds[2], 'KhoaHoc' => '2021-2025'],
            ['TenLop' => '21KHMT01','MaNganh' => $nganhIds[3], 'KhoaHoc' => '2021-2025'],
            ['TenLop' => '21HTTT01','MaNganh' => $nganhIds[4], 'KhoaHoc' => '2021-2025'],
            ['TenLop' => '22DTH01', 'MaNganh' => $nganhIds[0], 'KhoaHoc' => '2022-2026'],
            ['TenLop' => '22KPM01', 'MaNganh' => $nganhIds[1], 'KhoaHoc' => '2022-2026'],
            ['TenLop' => '22ATTT01','MaNganh' => $nganhIds[2], 'KhoaHoc' => '2022-2026'],
            ['TenLop' => '22TTNT01','MaNganh' => $nganhIds[5], 'KhoaHoc' => '2022-2026'],
        ];
        $lopIds = [];
        foreach ($lopData as $lop) {
            $lopIds[] = DB::table('lops')->insertGetId(array_merge($lop, ['created_at' => now(), 'updated_at' => now()]));
        }

        // ======================================================
        // 6. 10 MÔN HỌC
        // ======================================================
        $monHocData = [
            ['TenMon' => 'Đồ Án Chuyên Ngành 1',   'MaBoMon' => $bmIds[0], 'SoTinChi' => 3],
            ['TenMon' => 'Đồ Án Chuyên Ngành 2',   'MaBoMon' => $bmIds[0], 'SoTinChi' => 3],
            ['TenMon' => 'Đồ Án Cơ Sở',            'MaBoMon' => $bmIds[1], 'SoTinChi' => 2],
            ['TenMon' => 'Đồ Án Tốt Nghiệp',       'MaBoMon' => $bmIds[1], 'SoTinChi' => 10],
            ['TenMon' => 'Đồ Án AI Ứng Dụng',      'MaBoMon' => $bmIds[3], 'SoTinChi' => 4],
            ['TenMon' => 'Đồ Án An Toàn TT',       'MaBoMon' => $bmIds[2], 'SoTinChi' => 3],
            ['TenMon' => 'Đồ Án Web & Di Động',    'MaBoMon' => $bmIds[6], 'SoTinChi' => 3],
            ['TenMon' => 'Đồ Án Khoa Học Dữ Liệu', 'MaBoMon' => $bmIds[4], 'SoTinChi' => 4],
            ['TenMon' => 'Đồ Án Điện Toán Đám Mây','MaBoMon' => $bmIds[7], 'SoTinChi' => 3],
            ['TenMon' => 'Đồ Án Kiểm Thử Phần Mềm','MaBoMon' => $bmIds[8], 'SoTinChi' => 2],
        ];
        $monIds = [];
        foreach ($monHocData as $mon) {
            $monIds[] = DB::table('mon_hocs')->insertGetId(array_merge($mon, ['created_at' => now(), 'updated_at' => now()]));
        }

        // ======================================================
        // 7. TÀI KHOẢN ADMIN
        // ======================================================
        $tkAdminId = DB::table('tai_khoans')->insertGetId([
            'TenDangNhap' => 'admin',
            'MatKhau'     => Hash::make('123456'),
            'MaVaiTro'    => 1,
            'TrangThai'   => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // ======================================================
        // 8. 10 GIẢNG VIÊN (gv01 - gv10)
        // ======================================================
        $gvProfiles = [
            ['name' => 'TS. Nguyễn Văn An',       'email' => 'gv01@edu.vn', 'phone' => '0901234561', 'degree' => 'Tiến sĩ',   'bomon' => $bmIds[0]],
            ['name' => 'ThS. Trần Thị Bình',      'email' => 'gv02@edu.vn', 'phone' => '0901234562', 'degree' => 'Thạc sĩ',   'bomon' => $bmIds[1]],
            ['name' => 'PGS.TS. Lê Hoàng Cường',  'email' => 'gv03@edu.vn', 'phone' => '0901234563', 'degree' => 'PGS.TS',    'bomon' => $bmIds[2]],
            ['name' => 'TS. Phạm Thị Dung',       'email' => 'gv04@edu.vn', 'phone' => '0901234564', 'degree' => 'Tiến sĩ',   'bomon' => $bmIds[3]],
            ['name' => 'ThS. Hoàng Minh Đức',     'email' => 'gv05@edu.vn', 'phone' => '0901234565', 'degree' => 'Thạc sĩ',   'bomon' => $bmIds[4]],
            ['name' => 'TS. Vũ Thị Hoa',          'email' => 'gv06@edu.vn', 'phone' => '0901234566', 'degree' => 'Tiến sĩ',   'bomon' => $bmIds[5]],
            ['name' => 'ThS. Đặng Quốc Hùng',     'email' => 'gv07@edu.vn', 'phone' => '0901234567', 'degree' => 'Thạc sĩ',   'bomon' => $bmIds[6]],
            ['name' => 'TS. Bùi Ngọc Khoa',       'email' => 'gv08@edu.vn', 'phone' => '0901234568', 'degree' => 'Tiến sĩ',   'bomon' => $bmIds[7]],
            ['name' => 'ThS. Đỗ Thị Lan',         'email' => 'gv09@edu.vn', 'phone' => '0901234569', 'degree' => 'Thạc sĩ',   'bomon' => $bmIds[8]],
            ['name' => 'GS.TS. Ngô Văn Minh',     'email' => 'gv10@edu.vn', 'phone' => '0901234570', 'degree' => 'GS.TS',     'bomon' => $bmIds[9]],
        ];
        $gvTkIds = [];
        $gvMaIds = [];
        foreach ($gvProfiles as $idx => $gv) {
            $username = sprintf('gv%02d', $idx + 1);
            $tkId = DB::table('tai_khoans')->insertGetId([
                'TenDangNhap' => $username,
                'MatKhau'     => Hash::make('123456'),
                'MaVaiTro'    => 2,
                'TrangThai'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $gvTkIds[] = $tkId;

            $gvId = DB::table('giang_viens')->insertGetId([
                'MaTK'        => $tkId,
                'MaBoMon'     => $gv['bomon'],
                'HoTen'       => $gv['name'],
                'Email'       => $gv['email'],
                'SoDienThoai' => $gv['phone'],
                'HocVi'       => $gv['degree'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $gvMaIds[] = $gvId;
        }

        // ======================================================
        // 9. 50 SINH VIÊN (sv01 - sv50), phân bổ đều vào 10 lớp
        // ======================================================
        $ho   = ['Nguyễn','Trần','Lê','Phạm','Hoàng','Huỳnh','Vũ','Đặng','Bùi','Đỗ'];
        $lot  = ['Văn','Thị','Minh','Đức','Anh','Hữu','Ngọc','Gia','Thành','Quốc'];
        $ten  = ['Bảo','Bình','Cường','Dũng','Hải','Hùng','Khoa','Linh','Long','Nam','Phong','Quân','Sơn','Tâm','Tuấn','Tùng','Vinh','Phương','Trang','Yến'];

        $svTkIds = []; // indexed 0-49
        for ($i = 1; $i <= 50; $i++) {
            $username = sprintf('sv%02d', $i);
            $fullName = $ho[($i - 1) % 10] . ' ' . $lot[($i - 1) % 10] . ' ' . $ten[($i - 1) % 20];
            $email    = "{$username}@st.edu.vn";
            $phone    = sprintf('098%07d', $i);
            $lopId    = $lopIds[($i - 1) % 10];

            $svTkId = DB::table('tai_khoans')->insertGetId([
                'TenDangNhap' => $username,
                'MatKhau'     => Hash::make('123456'),
                'MaVaiTro'    => 3,
                'TrangThai'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $svTkIds[] = $svTkId;

            DB::table('sinh_viens')->insert([
                'MaTK'        => $svTkId,
                'MaLop'       => $lopId,
                'HoTen'       => $fullName,
                'Email'       => $email,
                'SoDienThoai' => $phone,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // ======================================================
        // 10. PHÂN CÔNG HƯỚNG DẪN LỚP
        //     Mỗi GV hướng dẫn 1 lớp (10 GV - 10 Lớp)
        // ======================================================
        foreach ($lopIds as $idx => $lId) {
            DB::table('phan_cong_huong_dan_lops')->insert([
                'MaGV'         => $gvMaIds[$idx],
                'MaLop'        => $lId,
                'MaHocKy'      => $maHocKy,
                'NgayPhanCong' => now()->toDateString(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // ======================================================
        // 11. 50 ĐỀ TÀI (gv01-gv10 mỗi người 5 đề tài)
        //     Gán MaLop = lớp GV phụ trách, deadlines hợp lý
        // ======================================================
        $topicTemplates = [
            "Xây dựng Hệ thống Quản lý Đồ án Sinh viên trực tuyến",
            "Ứng dụng Thương mại Điện tử bán đồ công nghệ",
            "Hệ thống Điểm danh Sinh viên bằng Nhận diện Khuôn mặt AI",
            "Website Đặt vé Máy bay & Khách sạn trực tuyến",
            "Ứng dụng Quản lý Thư viện Số dùng Microservices",
            "Hệ thống Quản lý Bãi xe Thông minh kết hợp IoT",
            "Website Đánh giá & Giới thiệu Phim tích hợp AI Recommendation",
            "Ứng dụng Quản lý Khách sạn & Đặt phòng Online",
            "Hệ thống Chatbot Hỗ trợ Tư vấn Tuyển sinh tự động",
            "Phần mềm Quản lý Kho Hàng & Chuỗi Cung ứng",
            "Ứng dụng Học Tiếng Anh Gamification trên Mobile",
            "Hệ thống Theo dõi & Cảnh báo Sức khỏe Cá nhân",
            "Website Sàn Giao dịch Bất động sản trực tuyến",
            "Ứng dụng Quản lý Chuỗi Cửa hàng Cà phê",
            "Hệ thống Quản lý Lịch trình Tour Du lịch",
            "Phần mềm Quản lý Phòng Khám & Đặt lịch Khám bệnh",
            "Ứng dụng Ví Điện tử & Thanh toán Trực tuyến",
            "Hệ thống Quản lý Nhân sự & Tính lương Doanh nghiệp",
            "Website Chia sẻ & Đánh giá Khóa học Trực tuyến",
            "Ứng dụng Quản lý Chi tiêu Cá nhân thông minh",
            "Hệ thống Giám sát Mạng & Cảnh báo Sự cố real-time",
            "Nền tảng Học tập Trực tuyến E-Learning tích hợp Video",
            "Hệ thống Quản lý Dự án Agile theo chuẩn Scrum",
            "Ứng dụng Đặt đồ ăn trực tuyến với Real-time Tracking",
            "Hệ thống Phân tích Dữ liệu Bán hàng dùng BI Dashboard",
            "Nền tảng Kết nối Freelancer & Nhà tuyển dụng IT",
            "Hệ thống Nhận diện Biển số Xe tự động",
            "Ứng dụng Quản lý Sự kiện & Bán vé Online",
            "Hệ thống ERP nhỏ cho Doanh nghiệp SME",
            "Ứng dụng Tìm kiếm Việc làm IT tích hợp AI Matching",
            "Hệ thống Phân tích Cảm xúc Người dùng từ Review",
            "Nền tảng NFT & Thương mại Nghệ thuật Số",
            "Hệ thống Kiểm tra Đạo văn cho Báo cáo Học thuật",
            "Ứng dụng Quản lý Tài sản Cố định Doanh nghiệp",
            "Hệ thống Phân loại Rác thải tự động dùng Computer Vision",
            "Chatbot Hỗ trợ Khách hàng với NLP tiếng Việt",
            "Hệ thống Gợi ý Sản phẩm Cá nhân hóa dùng Collaborative Filtering",
            "Ứng dụng Học lập trình tương tác cho Học sinh THPT",
            "Hệ thống Quản lý Trung tâm Ngoại ngữ & Đăng ký Học",
            "Nền tảng Microlearning Video ngắn theo phong cách TikTok",
            "Ứng dụng Quản lý Nông trại Thông minh kết hợp IoT",
            "Hệ thống Dự báo Nhu cầu Điện năng dùng Machine Learning",
            "Nền tảng Crowdfunding cho Dự án Startup Công nghệ",
            "Ứng dụng Theo dõi Tiến độ & Thói quen Cá nhân",
            "Hệ thống Quản lý Thiết bị Y tế trong Bệnh viện",
            "Hệ thống Đặt lịch Sân thể thao trực tuyến",
            "Ứng dụng Tư vấn Dinh dưỡng & Lập kế hoạch Ăn uống AI",
            "Nền tảng Kết nối Mentor & Mentee trong Lĩnh vực Công nghệ",
            "Hệ thống Quản lý Hợp đồng Dịch vụ & Pháp lý",
            "Ứng dụng AR Hỗ trợ Tham quan Bảo tàng Ảo",
        ];

        $deTaiIds = [];
        for ($t = 0; $t < 50; $t++) {
            $gvIdx  = $t % 10; // GV phụ trách theo thứ tự vòng
            $lopId  = $lopIds[$gvIdx]; // Lớp GV phụ trách
            $monId  = $monIds[$t % 10];
            $gvTkId = $gvTkIds[$gvIdx];

            $hanDangKy    = now()->addDays(30)->toDateString();
            $hanBaoCao    = now()->addDays(60)->toDateString();
            $hanNopSanPham= now()->addDays(90)->toDateString();

            $deTaiIds[] = DB::table('de_tais')->insertGetId([
                'MaTK'           => $gvTkId,
                'MaMon'          => $monId,
                'MaHocKy'        => $maHocKy,
                'MaLop'          => $lopId,
                'TenDeTai'       => $topicTemplates[$t],
                'MoTa'           => "Nghiên cứu, phân tích và xây dựng sản phẩm hoàn chỉnh: {$topicTemplates[$t]}. Yêu cầu hoàn thiện tài liệu thiết kế, mã nguồn và báo cáo cuối kỳ.",
                'YeuCau'         => "Xây dựng cơ sở dữ liệu chuẩn hoá, thiết kế giao diện responsive, cài đặt RESTful API, viết unit test và tài liệu hướng dẫn sử dụng.",
                'TrangThai'      => 'Đang mở đăng ký',
                'NgayTao'        => now()->toDateString(),
                'HanDangKy'      => $hanDangKy,
                'HanBaoCao'      => $hanBaoCao,
                'HanNopSanPham'  => $hanNopSanPham,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        // ======================================================
        // 12. 20 NHÓM ĐỒ ÁN (2-5 SV mỗi nhóm từ cùng lớp)
        //     Mỗi nhóm đăng ký 1 đề tài
        // ======================================================
        // sv01-sv50 đã được gán vào lớp lopIds[($i-1)%10]
        // Lấy MaSinhVien theo MaTK
        $svRecords = DB::table('sinh_viens')->whereIn('MaTK', $svTkIds)->orderBy('MaSV')->get();
        // Nhóm sv theo lớp
        $svByLop = [];
        foreach ($svRecords as $sv) {
            $svByLop[$sv->MaLop][] = $sv;
        }

        $nhomCount = 0;
        foreach ($lopIds as $lopIdx => $lopId) {
            if ($nhomCount >= 20) break;
            $svsInLop = $svByLop[$lopId] ?? [];
            if (count($svsInLop) < 2) continue;

            // Tạo tối đa 2 nhóm mỗi lớp
            $nhomInLop = 0;
            $svOffset  = 0;
            while ($nhomCount < 20 && $nhomInLop < 2 && ($svOffset + 1) < count($svsInLop)) {
                $size = min(5, count($svsInLop) - $svOffset);
                if ($size < 2) break;

                $maNhom = DB::table('nhom_do_ans')->insertGetId([
                    'MaLop'      => $lopId,
                    'MaMon'      => $monIds[$lopIdx % 10],
                    'MaHocKy'    => $maHocKy,
                    'TenNhom'    => "Nhóm " . str_pad($nhomCount + 1, 2, '0', STR_PAD_LEFT),
                    'TrangThai'  => 'Đang hoạt động',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Chèn thành viên (first SV = trưởng nhóm)
                for ($s = 0; $s < $size; $s++) {
                    $sv = $svsInLop[$svOffset + $s];
                    DB::table('thanh_vien_nhoms')->insert([
                        'MaNhom'     => $maNhom,
                        'MaSV'       => $sv->MaSV,
                        'VaiTro'     => $s === 0 ? 'Trưởng nhóm' : 'Thành viên',
                        'TrangThai'  => 'da_tham_gia',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Gán đề tài cho nhóm (đề tài của GV hướng dẫn lớp này)
                $deTaiIdx = ($lopIdx * 5) + $nhomInLop; // chọn đề tài theo vị trí
                if (isset($deTaiIds[$deTaiIdx])) {
                    $deTai = DB::table('de_tais')->where('MaDeTai', $deTaiIds[$deTaiIdx])->first();
                    if ($deTai && $deTai->TrangThai === 'Đang mở đăng ký') {
                        DB::table('dang_ky_de_tais')->insert([
                            'MaNhom'    => $maNhom,
                            'MaDeTai'   => $deTaiIds[$deTaiIdx],
                            'TrangThai' => 'Chờ duyệt',
                            'NgayDangKy'=> now()->toDateString(),
                            'created_at'=> now(),
                            'updated_at'=> now(),
                        ]);
                        // Đánh dấu đề tài đã có nhóm đăng ký
                        DB::table('de_tais')->where('MaDeTai', $deTaiIds[$deTaiIdx])->update(['TrangThai' => 'Đã đăng ký']);
                    }
                }

                // Gán giảng viên hướng dẫn nhóm
                DB::table('huong_dans')->insert([
                    'MaGV'       => $gvMaIds[$lopIdx],
                    'MaNhom'     => $maNhom,
                    'NgayPhanCong'=> now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $svOffset  += $size;
                $nhomInLop++;
                $nhomCount++;
            }
        }

        // ======================================================
        // 13. THÔNG BÁO HỆ THỐNG MẪU
        // ======================================================
        DB::table('thong_baos')->insert([
            [
                'MaTK'      => $tkAdminId,
                'TieuDe'    => 'Kế hoạch triển khai Đồ án Học kỳ 1 (2025-2026)',
                'NoiDung'   => 'Toàn bộ sinh viên khối 21 nhanh chóng lập nhóm (tối đa 5 thành viên) và thực hiện đăng ký đề tài trước ngày 30/10/2025.',
                'NgayTao'   => now()->toDateString(),
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'MaTK'      => $gvTkIds[0],
                'TieuDe'    => 'Hướng dẫn nộp báo cáo tiến độ và sản phẩm đồ án',
                'NoiDung'   => 'Các nhóm lưu ý nộp báo cáo tiến độ định kỳ bằng link Google Drive hoặc file đính kèm dạng .ZIP/.PDF.',
                'NgayTao'   => now()->toDateString(),
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'MaTK'      => $gvTkIds[1],
                'TieuDe'    => 'Lịch báo cáo giữa kỳ đồ án chuyên ngành',
                'NoiDung'   => 'Lịch báo cáo giữa kỳ sẽ được tổ chức vào tuần thứ 8 của học kỳ. Các nhóm chuẩn bị slide trình bày và demo sản phẩm.',
                'NgayTao'   => now()->toDateString(),
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
        ]);

        $this->command->info('✅ Seeder hoàn thành!');
        $this->command->info("   - 10 Bộ môn, 10 Ngành, 10 Lớp, 10 Môn học");
        $this->command->info("   - 10 Giảng viên (gv01-gv10 / 123456)");
        $this->command->info("   - 50 Sinh viên  (sv01-sv50 / 123456)");
        $this->command->info("   - 50 Đề tài, {$nhomCount} Nhóm đồ án");
        $this->command->info("   - Admin: admin / 123456");
    }
}
