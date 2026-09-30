<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TaiKhoan;
use App\Models\DeTai;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "=== TESTING TRƯỞNG KHOA ACTIONS ===\n";

$user = TaiKhoan::where('TenDangNhap', 'TK_CNTT_GV001')->first();
Auth::login($user);

$controller = new \App\Http\Controllers\TruongKhoa\DuyetDeTaiController();

// 1. Test Request Edit
echo "1. Testing Request Edit on DT_TK_01...\n";
$reqEdit = Request::create('/truongkhoa/duyet-detai/DT_TK_01/yeu-cau-chinh-sua', 'POST', [
    'YeuCauSua' => 'Đề nghị bổ sung thêm phần cứng thử nghiệm mô hình thực tế.'
]);
$resEdit = $controller->requestEdit($reqEdit, 'DT_TK_01');
$dtCheck1 = DeTai::find('DT_TK_01');
echo "   Status: {$dtCheck1->TrangThai}, LyDo: {$dtCheck1->LyDoTuChoi}\n";
assert($dtCheck1->TrangThai === 'Yêu cầu chỉnh sửa');

// 2. Test Approve
echo "2. Testing Approve on DT_TK_01...\n";
$reqApprove = Request::create('/truongkhoa/duyet-detai/DT_TK_01/duyet', 'POST');
$resApprove = $controller->approve($reqApprove, 'DT_TK_01');
$dtCheck2 = DeTai::find('DT_TK_01');
echo "   Status: {$dtCheck2->TrangThai}, NgayDuyetKhoa: {$dtCheck2->NgayDuyetKhoa}, NguoiDuyetKhoa: {$dtCheck2->NguoiDuyetKhoa}\n";
assert($dtCheck2->TrangThai === 'Trưởng khoa đã duyệt');

// 3. Test Reject
echo "3. Testing Reject on DT_TK_01...\n";
$reqReject = Request::create('/truongkhoa/duyet-detai/DT_TK_01/tu-choi', 'POST', [
    'LyDoTuChoi' => 'Đề tài trùng lặp với đề tài cấp trường năm 2024.'
]);
$resReject = $controller->reject($reqReject, 'DT_TK_01');
$dtCheck3 = DeTai::find('DT_TK_01');
echo "   Status: {$dtCheck3->TrangThai}, LyDo: {$dtCheck3->LyDoTuChoi}\n";
assert($dtCheck3->TrangThai === 'Từ chối');

// Reset DT_TK_01 back to 'Chờ duyệt cấp Khoa' so the user can test the UI buttons themselves
$dtCheck3->update([
    'TrangThai'       => 'Chờ duyệt cấp Khoa',
    'LyDoTuChoi'      => null,
    'NgayDuyetKhoa'   => null,
    'NguoiDuyetKhoa'  => null,
]);
echo "✓ Reset DT_TK_01 back to 'Chờ duyệt cấp Khoa' for user testing!\n";

// 4. Test Excel Export
echo "4. Testing Export Excel in KetQuaController...\n";
$kqController = new \App\Http\Controllers\TruongKhoa\KetQuaController();
$reqExport = Request::create('/truongkhoa/ket-qua/export', 'GET');
$resExport = $kqController->exportExcel($reqExport);
echo "   Response class: " . get_class($resExport) . ", Status: " . $resExport->getStatusCode() . "\n";
assert($resExport->getStatusCode() === 200);

echo "\n>>> ALL TRƯỞNG KHOA ACTIONS TESTED & VERIFIED 100%! <<<\n";
