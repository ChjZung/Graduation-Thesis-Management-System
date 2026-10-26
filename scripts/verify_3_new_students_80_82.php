<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\DanhSachSVDuDieuKien;
use Illuminate\Support\Facades\Hash;

echo "=== KIEM TRA 3 TAI KHOAN SINH VIEN MOI (80, 81, 82) ===\n";
$ids = ['2001210080', '2001210081', '2001210082'];

foreach ($ids as $id) {
    $sv = SinhVien::with(['lop', 'nganh', 'khoa', 'taiKhoan', 'thanhVienNhoms'])->find($id);
    if (!$sv) {
        echo " [FAIL] Khong tim thay SinhVien: {$id}\n";
        continue;
    }

    $tk = $sv->taiKhoan;
    $passOk = $tk && Hash::check('Huit@2026', $tk->MatKhau);
    $dks = DanhSachSVDuDieuKien::where('MaSV', $id)->pluck('MaHocKy')->toArray();
    $soNhom = $sv->thanhVienNhoms->count();

    echo " [PASS] MSSV: {$sv->MaSV}\n";
    echo "        Họ tên: {$sv->HoTen} ({$sv->GioiTinh}) | Lớp: {$sv->MaLop} | Ngành: {$sv->MaNganh} - {$sv->nganh?->TenNganh}\n";
    echo "        Email: {$sv->Email} | SĐT: {$sv->SoDienThoai}\n";
    echo "        Tài khoản: {$tk?->TenDangNhap} | Mật khẩu Huit@2026: " . ($passOk ? 'ĐÚNG' : 'SAI') . "\n";
    echo "        Trạng thái TK: {$tk?->TrangThaiMatKhau} | Đủ điều kiện các HK: " . implode(', ', $dks) . "\n";
    echo "        Số nhóm hiện tại: {$soNhom} (Sẵn sàng test tạo nhóm mới từ đầu)\n";
    echo "---------------------------------------------------------\n";
}
