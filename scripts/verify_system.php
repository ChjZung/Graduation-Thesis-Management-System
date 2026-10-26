<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "========================================================\n";
echo "KIỂM THỬ TOÀN DIỆN HỆ THỐNG QUẢN LÝ KHÓA LUẬN TỐT NGHIỆP\n";
echo "========================================================\n";

$pass = 0;
$fail = 0;

function test($description, $callback) {
    global $pass, $fail;
    try {
        $result = $callback();
        if ($result) {
            echo " [PASS] $description\n";
            $pass++;
        } else {
            echo " [FAIL] $description\n";
            $fail++;
        }
    } catch (\Throwable $e) {
        echo " [ERROR] $description: " . $e->getMessage() . " on line " . $e->getLine() . " in " . basename($e->getFile()) . "\n";
        $fail++;
    }
}

// 1. Kiểm tra CSDL các bảng và trường mới
test("1. CSDL: Bảng khoibaocaotiendo có PhanTramHoanThanh nullable", function() {
    $rows = DB::select("SHOW COLUMNS FROM khoibaocaotiendo WHERE Field = 'PhanTramHoanThanh'");
    return !empty($rows) && strtolower($rows[0]->Null) === 'yes';
});

test("2. CSDL: Bảng tomtatbaocao có các trường AI đánh giá tiến độ", function() {
    return Schema::hasColumns('tomtatbaocao', [
        'TyLeHoanThanhAI', 'CanCuDanhGia', 'ThoiDiemDanhGia', 'TrangThaiDanhGia'
    ]);
});

test("3. CSDL: Bảng hosobaove có các trường chuẩn bị phiếu của Thư ký", function() {
    return Schema::hasColumns('hosobaove', [
        'TrangThaiChuanBiPhieu', 'NguoiChuanBiPhieu', 'NgayChuanBiPhieu', 'NoiDungDeCuongJson'
    ]);
});

test("4. CSDL: Bảng bao_cao_sau_bao_ve có các trường đính kèm giải trình & minh chứng", function() {
    return Schema::hasColumns('bao_cao_sau_bao_ve', [
        'FileGiaiTrinh', 'TenFileGiaiTrinh', 'FileMinhChung', 'TenFileMinhChung'
    ]);
});

// 2. Kiểm tra Model quan hệ
test("5. Model HoSoBaoVe: accessor ten_nganh & ma_nganh hoạt động chuẩn xác", function() {
    $hs = \App\Models\HoSoBaoVe::first();
    if (!$hs) return true;
    $nganh = $hs->ten_nganh;
    return is_string($nganh) && !empty($nganh);
});

test("6. Model Nhom: accessor ten_nganh giải quyết Ngành chính xác từ sinh viên", function() {
    $nhom = \App\Models\Nhom::first();
    if (!$nhom) return true;
    return !empty($nhom->ten_nganh);
});

// 3. Kiểm tra CouncilAssignmentService
test("7. Service CouncilAssignmentService: thongKeTruocPhanCong hoạt động", function() {
    $service = new \App\Services\CouncilAssignmentService();
    $stats = $service->thongKeTruocPhanCong();
    return isset($stats['tong_giang_vien_du_dieu_kien'], $stats['tong_hoi_dong'], $stats['so_gv_da_phan_cong'], $stats['so_gv_chua_phan_cong']);
});

test("8. Service CouncilAssignmentService: deXuatThanhVienHoiDong chạy thành công", function() {
    $service = new \App\Services\CouncilAssignmentService();
    $goiY = $service->deXuatThanhVienHoiDong(
        '2026-11-20 08:00:00',
        '2026-11-20 12:00:00',
        ['HS01'],
        'HK251',
        'CNTT'
    );
    return is_array($goiY) && isset($goiY['success'], $goiY['thanh_viens'], $goiY['da_loai_gvhd']);
});

test("9. Service CouncilAssignmentService: thongKeGiangVienHoiDong hoạt động", function() {
    $service = new \App\Services\CouncilAssignmentService();
    $gv = \App\Models\GiangVien::first();
    if (!$gv) return true;
    $stats = $service->thongKeGiangVienHoiDong($gv->MaGV);
    return isset($stats['tong_so_hoidong'], $stats['sap_dien_ra'], $stats['da_hoan_thanh'], $stats['theo_vai_tro']);
});

// 4. Kiểm tra Routes
test("10. Route GiangVien Hội đồng: giangvien.hoidong.index & show", function() {
    $r1 = Route::has('giangvien.hoidong.index');
    $r2 = Route::has('giangvien.hoidong.show');
    return $r1 && $r2;
});

test("11. Route SinhVien Hội đồng: sinhvien.hoidong.index", function() {
    return Route::has('sinhvien.hoidong.index');
});

test("12. Route Thư ký xem đề cương & chuẩn bị phiếu", function() {
    $r1 = Route::has('giangvien.chamdiem.deCuong');
    $r2 = Route::has('giangvien.chamdiem.chuanBiPhieu');
    return $r1 && $r2;
});

test("13. Route Nộp báo cáo sau bảo vệ: sinhvien.ketqua.nopBaoCaoChinhSua", function() {
    return Route::has('sinhvien.ketqua.nopBaoCaoChinhSua');
});

// 5. Kiểm tra AI Summarizer Python & Version Comparison
test("14. Python Map-Reduce ai_summarizer: compare_report_versions hoạt động đúng quy tắc Lần 1 vs Lần 2", function() {
    $pyBin = 'C:\Users\ASUS\AppData\Local\Programs\Python\Python313\python.exe';
    $output = shell_exec("$pyBin scripts/test_python_compare.py 2>&1");
    return str_contains($output, 'ALL PYTHON TESTS PASSED');
});

echo "========================================================\n";
echo "KẾT QUẢ: $pass / " . ($pass + $fail) . " tiêu chí ĐẠT.\n";
echo "========================================================\n";
