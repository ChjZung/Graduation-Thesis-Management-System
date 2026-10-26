<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Models\DanhSachSVDuDieuKien;
use Illuminate\Support\Facades\Hash;

echo "=== KIEM TRA 3 SINH VIEN MOI ===\n";
$ids = ['2001210076', '2001210077', '2001210078'];

foreach ($ids as $id) {
    $sv = SinhVien::with(['lop', 'nganh', 'khoa', 'taiKhoan'])->find($id);
    if (!$sv) {
        echo " [FAIL] Khong tim thay SinhVien: {$id}\n";
        continue;
    }

    $tk = $sv->taiKhoan;
    $passOk = $tk && Hash::check('Huit@2026', $tk->MatKhau);
    $dk = DanhSachSVDuDieuKien::where('MaSV', $id)->first();

    echo " [PASS] MSSV: {$sv->MaSV}\n";
    echo "        Họ tên: {$sv->HoTen} ({$sv->GioiTinh})\n";
    echo "        Lớp: {$sv->MaLop} ({$sv->lop?->TenLop})\n";
    echo "        Ngành: {$sv->MaNganh} - {$sv->nganh?->TenNganh}\n";
    echo "        Khoa: {$sv->MaKhoa} - {$sv->khoa?->TenKhoa}\n";
    echo "        Email: {$sv->Email} | SĐT: {$sv->SoDienThoai}\n";
    echo "        Tín chỉ: {$sv->SoTinChiTichLuy} | GPA: {$sv->DiemTichLuy}\n";
    echo "        Tài khoản: {$tk?->TenDangNhap} | Vai trò: {$tk?->MaVaiTro} | Mật khẩu Huit@2026: " . ($passOk ? 'ĐÚNG' : 'SAI') . "\n";
    echo "        Đủ điều kiện KLTN: " . ($dk ? "CÓ ({$dk->MaHocKy} - {$dk->TrangThai})" : "KHÔNG") . "\n";
    echo "---------------------------------------------------------\n";
}
