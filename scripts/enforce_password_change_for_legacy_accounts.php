<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TaiKhoan;
use Illuminate\Support\Facades\Hash;

echo "=== KỊCH BẢN XỬ LÝ 103 TÀI KHOẢN MANG MẬT KHẨU MẶC ĐỊNH '123456' ===\n";
echo "LƯU Ý: Kịch bản này chỉ chạy khi được Quản trị viên / Người dùng phê duyệt.\n\n";

$accounts = TaiKhoan::all();
$affectedCount = 0;
$alreadyInitial = 0;

foreach ($accounts as $tk) {
    if (Hash::check('123456', $tk->MatKhau)) {
        if ($tk->BatBuocDoiMatKhau && $tk->TrangThaiMatKhau === 'INITIAL') {
            $alreadyInitial++;
        } else {
            $tk->update([
                'TrangThaiMatKhau'  => 'INITIAL',
                'BatBuocDoiMatKhau' => true,
            ]);
            $affectedCount++;
            echo "  + Đã chuyển tài khoản [{$tk->TenDangNhap}] sang trạng thái INITIAL & BatBuocDoiMatKhau = true\n";
        }
    }
}

echo "\nTổng kết:\n";
echo "- Số tài khoản đã được kích hoạt bắt buộc đổi mật khẩu mới: {$affectedCount}\n";
echo "- Số tài khoản đã ở sẵn trạng thái INITIAL từ trước: {$alreadyInitial}\n";
echo "- Hoàn tất chuyển đổi bảo mật cho tất cả tài khoản sử dụng mật khẩu cũ 123456!\n";
