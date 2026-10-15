<?php

/**
 * Script reset toàn bộ cơ sở dữ liệu và khởi tạo lại dữ liệu mẫu chuẩn theo yêu cầu:
 * 1. Làm trống toàn bộ các bảng dữ liệu (nghiệp vụ, người dùng, kế hoạch, đề tài...)
 * 2. Khởi tạo 5 vai trò hệ thống chuẩn (VT01 - VT05)
 * 3. Import lại toàn bộ dữ liệu mẫu chuẩn từ sample_imports (*.csv)
 * 4. Thiết lập chuẩn danh sách tài khoản theo quy định:
 *    - Admin: admin / Admin@123
 *    - Trưởng khoa: TK_CNTT_GV001 (và TK_CNTT_GV00000001) / Huit@2026
 *    - Trưởng bộ môn CNPM: TBM_CNTT_CNPM_GV002 (và TBM_CNTT_CNPM_GV00000002) / Huit@2026
 *    - Trưởng bộ môn ATTT: TBM_CNTT_ATTT_GV004 (và TBM_CNTT_ATTT_GV00000004) / Huit@2026
 *    - Các Trưởng bộ môn khác (HTTT, MMT, KHMT, KHDL) / Huit@2026
 *    - Giảng viên: GV00000002, GV00000023 và tất cả giảng viên / Huit@2026
 *    - Sinh viên: 2001210001, 2001210002, 2001210003, 2001210004 và tất cả sinh viên / Huit@2026
 *    - Mật khẩu chung: Huit@2026 (trừ admin: Admin@123)
 *    - Trạng thái tài khoản: ACTIVE, BatBuocDoiMatKhau = 0 để sẵn sàng kiểm thử / đăng nhập ngay.
 * 5. Khởi tạo Kế hoạch khóa luận HK2627_1, Mốc thời gian quy trình, Chỉ tiêu phản biện, Nhóm làm đồ án mẫu & Hội đồng bảo vệ.
 */

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BaoCaoTienDo;
use App\Models\BoMon;
use App\Models\ChiTietDuyetDeTai;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\GiaoVu;
use App\Models\HocKy;
use App\Models\HocPhan;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\KeHoachKhoaLuan;
use App\Models\Khoa;
use App\Models\Nhom;
use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\ThanhVienHoiDong;
use App\Models\ThanhVienNhom;
use App\Models\VaiTro;
use App\Services\ExcelImportService;
use App\Services\PlanPhaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

function runResetAndSeed($dbName = null)
{
    if ($dbName) {
        config(['database.connections.mysql.database' => $dbName]);
        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    $currentDb = DB::connection()->getDatabaseName();
    echo "\n=================================================================\n";
    echo ">>> BẮT ĐẦU RESET & KHỞI TẠO CƠ SỞ DỮ LIỆU: [ {$currentDb} ] <<<\n";
    echo "=================================================================\n";

    // -------------------------------------------------------------
    // BƯỚC 1: XÓA SẠCH DỮ LIỆU CÁC BẢNG (TRUNCATE)
    // -------------------------------------------------------------
    echo "\n--- BƯỚC 1: Làm trống toàn bộ dữ liệu bảng nghiệp vụ & hệ thống ---\n";
    DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

    $tables = [
        'baocaotiendo',
        'bieumau',
        'chitietduyetdetai',
        'chitieuhuongdan',
        'chitieuphanbien',
        'danhsachsvdudieukien',
        'detai',
        'hoidong',
        'hosobaove',
        'ketquasinhvien',
        'lichgaphuongdan',
        'mocthoigiankhoaluan',
        'phancongphanbien',
        'phieuchamdiem',
        'phieudangky',
        'quydinhkhoaluan',
        'kehoachkhoaluan',
        'tailieunop',
        'tephosobaove',
        'thanhvienhoidong',
        'thanhviennhom',
        'thongbao',
        'tomtatbaocao',
        'yeucaudoimatkhau',
        'nhom',
        'sinhvien',
        'giangvien',
        'giaovu',
        'taikhoan',
        'lop',
        'chuyennganh',
        'nganh',
        'bomon',
        'khoa',
        'hocphan_hocky',
        'hocphan',
        'hocky',
        'khoibaocaotiendo',
        'sessions',
        'jobs',
        'failed_jobs'
    ];

    foreach ($tables as $t) {
        if (Schema::hasTable($t)) {
            DB::table($t)->truncate();
            echo " [TRUNCATE] {$t}\n";
        }
    }

    // -------------------------------------------------------------
    // BƯỚC 2: KHỞI TẠO 5 VAI TRÒ CHUẨN
    // -------------------------------------------------------------
    echo "\n--- BƯỚC 2: Khởi tạo 5 Vai trò hệ thống chuẩn (VT01 - VT05) ---\n";
    $roles = [
        ['MaVaiTro' => 'VT01', 'TenVaiTro' => 'Giáo vụ'],
        ['MaVaiTro' => 'VT02', 'TenVaiTro' => 'Giảng viên'],
        ['MaVaiTro' => 'VT03', 'TenVaiTro' => 'Sinh viên'],
        ['MaVaiTro' => 'VT04', 'TenVaiTro' => 'Trưởng bộ môn'],
        ['MaVaiTro' => 'VT05', 'TenVaiTro' => 'Trưởng khoa'],
    ];
    foreach ($roles as $r) {
        DB::table('vaitro')->updateOrInsert(['MaVaiTro' => $r['MaVaiTro']], $r);
    }
    echo " [OK] Đã bảo đảm 5 vai trò trong bảng vaitro.\n";

    DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

    // -------------------------------------------------------------
    // BƯỚC 3: IMPORT TOÀN BỘ FILE MẪU CHUẨN TỪ sample_imports
    // -------------------------------------------------------------
    echo "\n--- BƯỚC 3: Import dữ liệu chuẩn hóa từ thư mục sample_imports/ ---\n";
    $sampleDir = base_path('sample_imports');
    $service = new ExcelImportService();

    $steps = [
        ['name' => '1. Danh mục Khoa', 'file' => '01_Khoa_Mau.csv', 'method' => 'importKhoa'],
        ['name' => '2. Danh mục Bộ Môn', 'file' => '02_BoMon_Mau.csv', 'method' => 'importBoMon'],
        ['name' => '3. Danh mục Ngành', 'file' => '03_Nganh_Mau.csv', 'method' => 'importNganh'],
        ['name' => '4. Danh mục Lớp', 'file' => '04_Lop_Mau.csv', 'method' => 'importLop'],
        ['name' => '5. Danh mục Học kỳ', 'file' => '05_HocKy_Mau.csv', 'method' => 'importHocKy'],
        ['name' => '6. Danh sách Giảng viên', 'file' => '06_GiangVien_Mau.csv', 'method' => 'importGiangVien'],
        ['name' => '7. Danh sách Sinh viên', 'file' => '07_SinhVien_Mau.csv', 'method' => 'importSinhVien'],
        ['name' => '8. Sinh viên đủ điều kiện', 'file' => '09_SinhVien_TheoHocKy_Mau.csv', 'method' => 'importSinhVienHocKy'],
        ['name' => '9. 12 Học phần Khóa luận', 'file' => '10_HocPhan_KhoaLuan_Mau.csv', 'method' => 'importHocPhan'],
        ['name' => '10. Danh mục Đề tài mẫu', 'file' => '11_DeTai_KhoaLuan_Mau.csv', 'method' => 'importDeTai'],
    ];

    foreach ($steps as $st) {
        $filePath = $sampleDir . '/' . $st['file'];
        if (!file_exists($filePath)) {
            $filePath = str_replace('.csv', '.xlsx', $filePath);
        }
        if (!file_exists($filePath)) {
            echo " [BỎ QUA] Không tìm thấy tệp: {$st['file']}\n";
            continue;
        }

        echo " >> Đang nhập: {$st['name']} ({$st['file']})... ";
        try {
            $res = $service->{$st['method']}($filePath);
            $suc = $res['success_count'] ?? ($res['success'] ?? 0);
            $err = $res['error_count'] ?? 0;
            echo "Thành công: {$suc} | Lỗi: {$err}\n";
        } catch (\Throwable $e) {
            echo "LỖI EXCEPTION: " . $e->getMessage() . "\n";
        }
    }

    // -------------------------------------------------------------
    // BƯỚC 4: THIẾT LẬP TOÀN DIỆN DANH SÁCH TÀI KHOẢN & MẬT KHẨU
    // -------------------------------------------------------------
    echo "\n--- BƯỚC 4: Khởi tạo & Đồng bộ tài khoản theo quy định người dùng ---\n";
    $passAdmin = Hash::make('Admin@123');
    $passChung = Hash::make('Huit@2026');
    $now = now();

    // 4.1. Tài khoản Giáo vụ Quản trị (Admin)
    TaiKhoan::updateOrCreate(
        ['TenDangNhap' => 'admin'],
        [
            'MaTK'              => 'TK_ADMIN',
            'MatKhau'           => $passAdmin,
            'MaVaiTro'          => 'VT01',
            'TrangThai'         => true,
            'TrangThaiMatKhau'  => 'ACTIVE',
            'BatBuocDoiMatKhau' => false,
            'SoLanDangNhapSai'  => 0,
            'LanDangNhapDau'    => $now,
            'NgayDoiMatKhau'    => $now,
            'updated_at'        => $now,
        ]
    );

    GiaoVu::updateOrCreate(
        ['MaGVu' => 'GVU01'],
        [
            'MaTK'        => 'TK_ADMIN',
            'HoTen'       => 'Quản trị viên Admin',
            'Email'       => 'admin@huit.edu.vn',
            'SoDienThoai' => '0908123456',
            'ChucVu'      => 'Quản trị viên Hệ thống (Admin)',
            'MaKhoa'      => 'CNTT',
            'updated_at'  => $now,
        ]
    );
    echo " [OK] Admin: admin / Admin@123 (Role VT01 - GiaoVu GVU01)\n";

    // 4.2. Cập nhật mật khẩu Huit@2026 & BẮT BUỘC ĐỔI MẬT KHẨU LẦN ĐẦU cho toàn bộ Giảng viên (VT02)
    $gvData = [
        'MatKhau'           => $passChung,
        'TrangThai'         => true,
        'TrangThaiMatKhau'  => 'INITIAL',
        'BatBuocDoiMatKhau' => true,
        'SoLanDangNhapSai'  => 0,
        'LanDangNhapDau'    => null,
        'NgayDoiMatKhau'    => null,
        'updated_at'        => $now,
    ];
    if (Schema::hasColumn('taikhoan', 'MaGV')) {
        $gvData['MaGV'] = DB::raw('TenDangNhap');
    }
    DB::table('taikhoan')->where('MaVaiTro', 'VT02')->update($gvData);
    echo " [OK] Toàn bộ tài khoản Giảng viên (VT02) đã đặt mật khẩu: Huit@2026 (Bắt buộc đổi mật khẩu lần đầu: INITIAL)\n";

    // 4.3. Cập nhật mật khẩu Huit@2026 & BẮT BUỘC ĐỔI MẬT KHẨU LẦN ĐẦU cho toàn bộ Sinh viên (VT03)
    DB::table('taikhoan')->where('MaVaiTro', 'VT03')->update([
        'MatKhau'           => $passChung,
        'TrangThai'         => true,
        'TrangThaiMatKhau'  => 'INITIAL',
        'BatBuocDoiMatKhau' => true,
        'SoLanDangNhapSai'  => 0,
        'LanDangNhapDau'    => null,
        'NgayDoiMatKhau'    => null,
        'updated_at'        => $now,
    ]);
    echo " [OK] Toàn bộ tài khoản Sinh viên (VT03) đã đặt mật khẩu: Huit@2026 (Bắt buộc đổi mật khẩu lần đầu: INITIAL)\n";

    // 4.4. Tài khoản Trưởng Khoa (VT05)
    // - Yêu cầu người dùng: TK_CNTT_GV001
    // - Alias mở rộng: TK_CNTT_GV00000001
    // - Hỗ trợ Security Test: TK_ATTT_GV00000004
    $tkAccounts = [
        ['username' => 'TK_CNTT_GV001',      'magv' => 'GV00000001', 'name' => 'PGS.TS. Nguyễn Văn Hùng (Trưởng Khoa CNTT)'],
        ['username' => 'TK_CNTT_GV00000001', 'magv' => 'GV00000001', 'name' => 'PGS.TS. Nguyễn Văn Hùng (Trưởng Khoa CNTT - 10 ký tự)'],
        ['username' => 'TK_ATTT_GV00000004', 'magv' => 'GV00000004', 'name' => 'TS. Trịnh Văn Thành (Trưởng Khoa ATTT - Security Test)'],
    ];

    foreach ($tkAccounts as $acc) {
        TaiKhoan::updateOrCreate(
            ['TenDangNhap' => $acc['username']],
            [
                'MaTK'              => $acc['username'],
                'MaGV'              => $acc['magv'],
                'MatKhau'           => $passChung,
                'MaVaiTro'          => 'VT05',
                'TrangThai'         => true,
                'TrangThaiMatKhau'  => 'INITIAL',
                'BatBuocDoiMatKhau' => true,
                'SoLanDangNhapSai'  => 0,
                'LanDangNhapDau'    => null,
                'NgayDoiMatKhau'    => null,
                'updated_at'        => $now,
            ]
        );
        echo " [OK] Trưởng khoa: {$acc['username']} -> {$acc['magv']} | Huit@2026 (Bắt buộc đổi mật khẩu)\n";
    }

    Khoa::where('MaKhoa', 'CNTT')->update(['TruongKhoa' => 'PGS.TS. Nguyễn Văn Hùng']);
    Khoa::updateOrCreate(
        ['MaKhoa' => 'ATTT'],
        ['TenKhoa' => 'Khoa An Toàn Thông Tin', 'TruongKhoa' => 'TS. Trịnh Văn Thành', 'MoTa' => 'Khoa An Toàn Thông Tin', 'created_at' => $now, 'updated_at' => $now]
    );

    // 4.5. Tài khoản Trưởng Bộ Môn (VT04)
    // - Yêu cầu người dùng: TBM_CNTT_CNPM_GV002, TBM_CNTT_ATTT_GV004
    // - Alias & các bộ môn khác: HTTT, KHMT, MMT, KHDL
    $tbmAccounts = [
        ['username' => 'TBM_CNTT_CNPM_GV002',      'magv' => 'GV00000002', 'mabm' => 'CNPM', 'name' => 'TS. Trần Anh Dũng (Trưởng BM CNPM)'],
        ['username' => 'TBM_CNTT_CNPM_GV00000002', 'magv' => 'GV00000002', 'mabm' => 'CNPM', 'name' => 'TS. Trần Anh Dũng (Trưởng BM CNPM - 10 ký tự)'],
        ['username' => 'TBM_BM_CNPM_GV00000001',   'magv' => 'GV00000001', 'mabm' => 'CNPM', 'name' => 'Trưởng BM CNPM (Alias Security Test)'],
        ['username' => 'TBM_CNTT_ATTT_GV004',      'magv' => 'GV00000004', 'mabm' => 'ATTT', 'name' => 'TS. Trịnh Văn Thành (Trưởng BM ATTT)'],
        ['username' => 'TBM_CNTT_ATTT_GV00000004', 'magv' => 'GV00000004', 'mabm' => 'ATTT', 'name' => 'TS. Trịnh Văn Thành (Trưởng BM ATTT - 10 ký tự)'],
        ['username' => 'TBM_CNTT_HTTT_GV00000003', 'magv' => 'GV00000003', 'mabm' => 'HTTT', 'name' => 'TS. Phạm Minh Tuấn (Trưởng BM HTTT)'],
        ['username' => 'TBM_BM_HTTT_GV00000003',   'magv' => 'GV00000003', 'mabm' => 'HTTT', 'name' => 'TS. Phạm Minh Tuấn (Trưởng BM HTTT - Security Test)'],
        ['username' => 'TBM_CNTT_MMT_GV00000005',  'magv' => 'GV00000005', 'mabm' => 'MMT',  'name' => 'TS. Lê Hải Đăng (Trưởng BM MMT)'],
        ['username' => 'TBM_CNTT_KHMT_GV00000006', 'magv' => 'GV00000006', 'mabm' => 'KHMT', 'name' => 'PGS.TS. Võ Quốc Phong (Trưởng BM KHMT)'],
        ['username' => 'TBM_CNTT_KHDL_GV00000007', 'magv' => 'GV00000007', 'mabm' => 'KHDL', 'name' => 'TS. Đỗ Anh Minh (Trưởng BM KHDL)'],
    ];

    foreach ($tbmAccounts as $acc) {
        TaiKhoan::updateOrCreate(
            ['TenDangNhap' => $acc['username']],
            [
                'MaTK'              => $acc['username'],
                'MaGV'              => $acc['magv'],
                'MatKhau'           => $passChung,
                'MaVaiTro'          => 'VT04',
                'TrangThai'         => true,
                'TrangThaiMatKhau'  => 'INITIAL',
                'BatBuocDoiMatKhau' => true,
                'SoLanDangNhapSai'  => 0,
                'LanDangNhapDau'    => null,
                'NgayDoiMatKhau'    => null,
                'updated_at'        => $now,
            ]
        );
        BoMon::where('MaBoMon', $acc['mabm'])->update(['TruongBoMon' => $acc['name']]);
        echo " [OK] Trưởng bộ môn: {$acc['username']} -> {$acc['magv']} ({$acc['mabm']}) | Huit@2026 (Bắt buộc đổi mật khẩu)\n";
    }

    // -------------------------------------------------------------
    // BƯỚC 5: KHỞI TẠO KẾ HOẠCH, CHỈ TIÊU & DỮ LIỆU ĐỒ ÁN MẪU
    // -------------------------------------------------------------
    echo "\n--- BƯỚC 5: Khởi tạo Kế hoạch khóa luận, Mốc quy trình & Chỉ tiêu phản biện ---\n";
    $hocKy = HocKy::where('MaHocKy', 'HK2627_1')->first() ?? HocKy::first();
    $maHK = $hocKy ? $hocKy->MaHocKy : 'HK2627_1';

    // 5.1. Kế hoạch khóa luận HK2627_1
    DB::table('kehoachkhoaluan')->updateOrInsert(
        ['MakeHoach' => 'KH_HK2627_1'],
        [
            'TenKeHoach'  => 'Kế hoạch Khóa luận tốt nghiệp Học kỳ 1 Năm học 2026-2027',
            'MaHocKy'     => $maHK,
            'MaGVu'       => 'GVU01',
            'TrangThai'   => 'ĐÃ CÔNG BỐ',
            'NgayTao'     => '2026-08-01',
            'NoiDung'     => 'Kế hoạch chính thức công tác khóa luận tốt nghiệp cho sinh viên khóa 2021-2025 Khoa CNTT.',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]
    );

    // 5.2. Mốc thời gian quy trình (Phases)
    $phases = PlanPhaseService::getDefaultPhases('2026-08-01');
    foreach ($phases as $idx => $ph) {
        DB::table('mocthoigiankhoaluan')->updateOrInsert(
            ['MaMoc' => 'MOC_' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT)],
            [
                'MakeHoach'        => 'KH_HK2627_1',
                'TenMoc'           => $ph['TenMoc'],
                'DoiTuongThucHien' => $ph['DoiTuongThucHien'],
                'NgayBatDau'       => $ph['NgayBatDau'],
                'NgayKetThuc'      => $ph['NgayKetThuc'],
                'MoTa'             => $ph['MoTa'] ?? '',
                'created_at'       => $now,
                'updated_at'       => $now,
            ]
        );
    }
    echo " [OK] Đã thiết lập 12 mốc thời gian quy trình khóa luận cho kế hoạch KH_HK2627_1.\n";

    // 5.3. Liên kết 12 học phần với học kỳ HK2627_1
    $allHocPhans = HocPhan::all();
    foreach ($allHocPhans as $hp) {
        DB::table('hocphan_hocky')->updateOrInsert(
            ['MaHocPhan' => $hp->MaHocPhan, 'MaHocKy' => $maHK],
            ['TrangThai' => 'Đang mở', 'created_at' => $now, 'updated_at' => $now]
        );
    }
    echo " [OK] Đã liên kết " . count($allHocPhans) . " học phần khóa luận với học kỳ {$maHK}.\n";

    // 5.4. Chỉ tiêu phản biện (chitieuphanbien) cho giảng viên
    $allGvs = GiangVien::all();
    foreach ($allGvs as $idx => $gv) {
        DB::table('chitieuphanbien')->updateOrInsert(
            ['MaGV' => $gv->MaGV, 'MaHocKy' => $maHK],
            [
                'MaChiTieuPB'   => 'CTPB_' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT),
                'SoDeTaiToiDa'  => 5,
                'GhiChu'        => 'Chỉ tiêu phản biện tiêu chuẩn học kỳ',
                'created_at'    => $now,
                'updated_at'    => $now
            ]
        );
    }
    echo " [OK] Đã cập nhật chỉ tiêu phản biện (5 đề tài/GV) cho " . count($allGvs) . " giảng viên.\n";

    // 5.5. Trạng thái Đề tài mẫu & Nhóm sinh viên thực hiện
    // Cập nhật DT01 và DT26 sang 'Đã công bố'
    DeTai::where('MaDeTai', 'DT01')->update(['TrangThai' => 'Đã công bố', 'MaHocKy' => $maHK]);
    DeTai::where('MaDeTai', 'DT26')->update(['TrangThai' => 'Đã công bố', 'MaHocKy' => $maHK]);

    // Tạo Nhóm N01 làm đề tài DT26 (GVHD: GV00000023 - TS. Lê Hoàng Cường)
    $nhomN01 = Nhom::updateOrCreate(
        ['MaNhom' => 'N01'],
        [
            'TenNhom'        => 'Nhóm N01 - Khóa luận tốt nghiệp',
            'MaHocKy'        => $maHK,
            'MaHocPhan'      => 'HP_KLCN_CNPM',
            'TrangThai'      => 'Đang hoạt động',
            'NgayTao'        => $now,
            'updated_at'     => $now,
        ]
    );

    // Phiếu đăng ký đề tài DT26 cho Nhóm N01
    DB::table('phieudangky')->updateOrInsert(
        ['MaNhom' => 'N01', 'MaDeTai' => 'DT26'],
        [
            'MaDangKy'   => 'PDK_N01_DT26',
            'NgayDangKy' => $now,
            'TrangThai'  => 'Đã duyệt',
            'NgayDuyet'  => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]
    );

    ThanhVienNhom::updateOrCreate(
        ['MaNhom' => $nhomN01->MaNhom, 'MaSV' => '2001210001'],
        ['VaiTro' => 'Trưởng nhóm', 'TrangThai' => 'da_tham_gia', 'NgayThamGia' => $now, 'updated_at' => $now]
    );
    ThanhVienNhom::updateOrCreate(
        ['MaNhom' => $nhomN01->MaNhom, 'MaSV' => '2001210002'],
        ['VaiTro' => 'Thành viên', 'TrangThai' => 'da_tham_gia', 'NgayThamGia' => $now, 'updated_at' => $now]
    );
    ThanhVienNhom::updateOrCreate(
        ['MaNhom' => $nhomN01->MaNhom, 'MaSV' => '2001210003'],
        ['VaiTro' => 'Thành viên', 'TrangThai' => 'da_tham_gia', 'NgayThamGia' => $now, 'updated_at' => $now]
    );
    echo " [OK] Đã thiết lập Nhóm N01 (2001210001 Trưởng nhóm, 2001210002, 2001210003) làm đề tài DT26.\n";

    // 5.6. Tạo dữ liệu mẫu cho Hội đồng bảo vệ & Báo cáo tiến độ (phục vụ Security Authorization Test)
    $hoiDong = HoiDong::updateOrCreate(
        ['MaHoiDong' => 'HD01'],
        [
            'TenHoiDong'      => 'Hội đồng Bảo vệ Khóa luận Tốt nghiệp số 01 - Chuyên ngành CNPM',
            'MaHocKy'         => $maHK,
            'NgayBaoVe'       => '2026-12-15',
            'ThoiGianBatDau'  => '2026-12-15 08:00:00',
            'ThoiGianKetThuc' => '2026-12-15 11:30:00',
            'DiaDiem'         => 'Phòng Hội đồng B.401',
            'TrangThai'       => 'Đang hoạt động',
            'MaDeTai'         => 'DT01',
            'MaGV'            => 'GV00000003',
            'updated_at'      => $now,
        ]
    );

    ThanhVienHoiDong::updateOrCreate(
        ['MaHoiDong' => $hoiDong->MaHoiDong, 'MaGV' => 'GV00000003'],
        ['VaiTro' => 'Chủ tịch', 'updated_at' => $now]
    );
    ThanhVienHoiDong::updateOrCreate(
        ['MaHoiDong' => $hoiDong->MaHoiDong, 'MaGV' => 'GV00000004'],
        ['VaiTro' => 'Thư ký', 'updated_at' => $now]
    );
    ThanhVienHoiDong::updateOrCreate(
        ['MaHoiDong' => $hoiDong->MaHoiDong, 'MaGV' => 'GV00000002'],
        ['VaiTro' => 'Ủy viên', 'updated_at' => $now]
    );

    HoSoBaoVe::updateOrCreate(
        ['MaHoSo' => 'HS01'],
        [
            'MaHoiDong'     => $hoiDong->MaHoiDong,
            'MaNhom'        => $nhomN01->MaNhom,
            'MaDeTai'       => 'DT01',
            'MaGVu'         => 'GVU01',
            'NgayLap'       => '2026-12-01',
            'NgayNop'       => '2026-12-05',
            'XacNhanGVHD'   => 'Đã duyệt',
            'TrangThai'     => 'Đã nộp',
            'ThoiGianBaoVe' => '2026-12-15 08:30:00',
            'PhongBaoVe'    => 'Phòng B.401',
            'updated_at'    => $now,
        ]
    );

    BaoCaoTienDo::updateOrCreate(
        ['MaBaoCao' => 'BC01'],
        [
            'MaDeTai'       => 'DT01',
            'LanBaoCao'     => 1,
            'TieuDe'        => 'Báo cáo tiến độ đề tài DT01 Đợt 1',
            'NoiDungBaoCao' => 'Hoàn thành đặc tả yêu cầu và thiết kế kiến trúc hệ thống.',
            'DuongDanFile'  => 'baocao/BaoCaoTienDo_DT01_Lan1.pdf',
            'NgayNop'       => $now,
            'TrangThai'     => 'Đã nộp',
            'MaMoc'         => 'MOC_06',
            'updated_at'    => $now,
        ]
    );
    echo " [OK] Đã thiết lập Hội đồng HD01, Hồ sơ bảo vệ HS01 và Báo cáo tiến độ BC01.\n";

    echo "\n=================================================================\n";
    echo ">>> HOÀN TẤT RESET & KHỞI TẠO DỮ LIỆU CHO: [ {$currentDb} ] <<<\n";
    echo "=================================================================\n\n";
}

// 1. Chạy trên CSDL chính ql_khoaluan_totnghiep
runResetAndSeed('ql_khoaluan_totnghiep');

// 2. Chạy trên CSDL quanly_doan để đồng bộ nếu DB này tồn tại
try {
    $hasQLDoAn = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'quanly_doan'");
    if (!empty($hasQLDoAn)) {
        runResetAndSeed('quanly_doan');
    }
} catch (\Throwable $e) {
    echo "Lưu ý: Không thể đồng bộ quanly_doan: " . $e->getMessage() . "\n";
}
