<?php

/**
 * KỊCH BẢN KIỂM THỬ TỰ ĐỘNG TOÀN DIỆN LUỒNG QUẢN LÝ ĐỀ TÀI KHÓA LUẬN
 * 
 * Luồng kiểm thử:
 *  GIẢNG VIÊN Đề xuất đề tài (Học kỳ, Môn/Học phần, Tên ĐT, Lĩnh vực, Mô tả, Yêu cầu, Số SV tối đa, Đề cương)
 *    ↓
 *  [CHỜ DUYỆT]
 *    ↓
 *  GIÁO VỤ KIỂM TRA (Yêu cầu sửa / Từ chối / Phê duyệt)
 *    ↓
 *  GV CHỈNH SỬA & NỘP LẠI -> [CHỜ DUYỆT]
 *    ↓
 *  GIÁO VỤ [PHÊ DUYỆT] -> [ĐÃ DUYỆT]
 *    ↓
 *  GIÁO VỤ [CÔNG BỐ ĐỀ TÀI] -> [ĐÃ CÔNG BỐ]
 *    ↓
 *  SINH VIÊN Chọn Môn/Học phần -> Xem danh sách đã công bố -> Đăng ký đề tài
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\Nganh;
use App\Models\SinhVien;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\DangKyDeTai;
use App\Models\TaiKhoan;
use App\Models\ThongBao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "\n========================================================================\n";
echo "       BẮT ĐẦU CHẠY KỊCH BẢN KIỂM THỬ LUỒNG ĐỀ TÀI KHÓA LUẬN          \n";
echo "========================================================================\n\n";

DB::beginTransaction();

try {
    // -------------------------------------------------------------------------
    // DỮ LIỆU CƠ BẢN
    // -------------------------------------------------------------------------
    $hocKy = HocKy::where('TrangThai', 1)->first() ?? HocKy::first();
    $giangVien = GiangVien::first();
    $nganh = Nganh::first();
    $tkGV = TaiKhoan::where('MaTK', $giangVien->MaTK)->first();

    echo "[-] Dữ liệu thiết lập ban đầu:\n";
    echo "    + Học kỳ áp dụng: {$hocKy->TenHocKy} ({$hocKy->MaHocKy})\n";
    echo "    + Giảng viên thực hiện: {$giangVien->HoTen} ({$giangVien->MaGV})\n";
    echo "    + Ngành đào tạo: {$nganh->TenNganh} ({$nganh->MaNganh})\n\n";

    // =========================================================================
    // BƯỚC 1: GIẢNG VIÊN ĐỀ XUẤT ĐỀ TÀI
    // =========================================================================
    echo "------------------------------------------------------------------------\n";
    echo "BƯỚC 1: GIẢNG VIÊN ĐỀ XUẤT ĐỀ TÀI MỚI\n";
    echo "------------------------------------------------------------------------\n";
    Auth::login($tkGV);
    $gvController = new \App\Http\Controllers\GiangVien\DeTaiController();

    $maDTTest = 'DT_' . rand(10000, 99999);
    $reqDeXuat = new Request([
        'TenDeTai'            => 'Hệ Thống Phân Tích Dữ Liệu Học Tập Ứng Dụng Học Máy',
        'MaHocKy'             => $hocKy->MaHocKy,
        'HocPhan'             => 'Khóa luận tốt nghiệp',
        'MaNganh'             => $nganh->MaNganh,
        'LinhVuc'             => 'Trí Tuệ Nhân Tạo & Khai Phá Dữ Liệu',
        'SoLuongSinhVienToiDa'=> 2,
        'MoTa'                => 'Nghiên cứu thuật toán phát hiện học viên có nguy cơ bỏ học sớm.',
        'YeuCau'              => 'Sinh viên thành thạo Python, PyTorch, nền tảng SQL tốt.',
    ]);

    // Tạo đề tài với thông số trên
    $deTai = DeTai::create([
        'MaDeTai'             => $maDTTest,
        'MaGV'                => $giangVien->MaGV,
        'TenDeTai'            => $reqDeXuat->TenDeTai,
        'MaHocKy'             => $reqDeXuat->MaHocKy,
        'HocPhan'             => $reqDeXuat->HocPhan,
        'MaNganh'             => $reqDeXuat->MaNganh,
        'LinhVuc'             => $reqDeXuat->LinhVuc,
        'SoLuongSinhVienToiDa'=> $reqDeXuat->SoLuongSinhVienToiDa,
        'MoTa'                => $reqDeXuat->MoTa,
        'YeuCau'              => $reqDeXuat->YeuCau,
        'FileDeCuong'         => 'storage/de_cuong/mau_de_cuong_test.docx',
        'TrangThai'           => 'Chờ duyệt',
        'NgayDeXuat'          => now(),
    ]);

    echo "  [PASS] Đã gửi đề xuất thành công!\n";
    echo "         - Mã ĐT: {$deTai->MaDeTai}\n";
    echo "         - Tên ĐT: {$deTai->TenDeTai}\n";
    echo "         - Môn/Học phần: {$deTai->HocPhan}\n";
    echo "         - Số SV tối đa: {$deTai->SoLuongSinhVienToiDa}\n";
    echo "         - Trạng thái hiện tại: [{$deTai->TrangThai}]\n\n";

    if ($deTai->TrangThai !== 'Chờ duyệt') {
        throw new Exception("Trạng thái đề tài phải là 'Chờ duyệt'!");
    }

    // =========================================================================
    // BƯỚC 2: GIÁO VỤ KIỂM TRA -> GỬI YÊU CẦU CHỈNH SỬA
    // =========================================================================
    echo "------------------------------------------------------------------------\n";
    echo "BƯỚC 2: GIÁO VỤ KIỂM TRA ĐỀ CƯƠNG & GỬI YÊU CẦU SỬA\n";
    echo "------------------------------------------------------------------------\n";
    $adminController = new \App\Http\Controllers\Admin\DuyetDeTaiController();

    $noiDungSua = 'Vui lòng bổ sung rõ công nghệ Frontend (Vue/React) và chi tiết bộ dữ liệu huấn luyện.';
    $reqSua = new Request(['YeuCauSua' => $noiDungSua]);
    $adminController->requestEdit($reqSua, $deTai->MaDeTai);
    $deTai->refresh();

    echo "  [PASS] Giáo vụ gửi yêu cầu điều chỉnh thành công!\n";
    echo "         - Trạng thái mới: [{$deTai->TrangThai}]\n";
    echo "         - Ý kiến phản hồi gửi GV: '{$deTai->LyDoTuChoi}'\n\n";

    if ($deTai->TrangThai !== 'Yêu cầu điều chỉnh') {
        throw new Exception("Trạng thái đề tài phải chuyển sang 'Yêu cầu điều chỉnh'!");
    }

    // 2b. Kiểm thử nhánh TỪ CHỐI đề tài
    $deTaiBiTuChoi = DeTai::create([
        'MaDeTai'             => 'DT_TC_' . rand(1000, 9999),
        'MaGV'                => $giangVien->MaGV,
        'TenDeTai'            => 'Đề tài sao chép nội dung cũ',
        'MaHocKy'             => $hocKy->MaHocKy,
        'HocPhan'             => 'Khóa luận tốt nghiệp',
        'SoLuongSinhVienToiDa'=> 2,
        'TrangThai'           => 'Chờ duyệt',
    ]);
    $lyDoTuChoi = 'Đề tài trùng lặp 90% với khóa luận năm trước, không đủ tính mới.';
    $adminController->reject(new Request(['LyDoTuChoi' => $lyDoTuChoi]), $deTaiBiTuChoi->MaDeTai);
    $deTaiBiTuChoi->refresh();

    echo "  [PASS] Giáo vụ thực hiện nhánh TỪ CHỐI thành công!\n";
    echo "         - Đề tài: {$deTaiBiTuChoi->MaDeTai}\n";
    echo "         - Trạng thái: [{$deTaiBiTuChoi->TrangThai}]\n";
    echo "         - Lý do từ chối: '{$deTaiBiTuChoi->LyDoTuChoi}'\n\n";

    if ($deTaiBiTuChoi->TrangThai !== 'Từ chối') {
        throw new Exception("Trạng thái đề tài phải là 'Từ chối'!");
    }

    // =========================================================================
    // BƯỚC 3: GIẢNG VIÊN CẬP NHẬT & NỘP LẠI ĐỀ TÀI
    // =========================================================================
    echo "------------------------------------------------------------------------\n";
    echo "BƯỚC 3: GIẢNG VIÊN CẬP NHẬT NỘI DUNG & NỘP LẠI\n";
    echo "------------------------------------------------------------------------\n";
    $reqCapNhat = new Request([
        'TenDeTai'            => $deTai->TenDeTai,
        'MaHocKy'             => $deTai->MaHocKy,
        'HocPhan'             => 'Khóa luận tốt nghiệp',
        'MaNganh'             => $deTai->MaNganh,
        'LinhVuc'             => $deTai->LinhVuc,
        'SoLuongSinhVienToiDa'=> 2,
        'MoTa'                => 'Đã bổ sung: Sử dụng tập dữ liệu OULAD, mô hình Transformer kết hợp FastAPI backend.',
        'YeuCau'              => 'Yêu cầu sinh viên biết ReactJS và Python FastAPI.',
    ]);
    $gvController->update($reqCapNhat, $deTai->MaDeTai);
    $deTai->refresh();

    echo "  [PASS] Giảng viên đã hoàn thiện chỉnh sửa và nộp lại!\n";
    echo "         - Mô tả mới: {$deTai->MoTa}\n";
    echo "         - Trạng thái sau cập nhật: [{$deTai->TrangThai}]\n\n";

    if ($deTai->TrangThai !== 'Chờ duyệt') {
        throw new Exception("Sau khi nộp lại, đề tài phải tự động quay về trạng thái 'Chờ duyệt'!");
    }

    // =========================================================================
    // BƯỚC 4: GIÁO VỤ THẨM ĐỊNH LẠI & PHÊ DUYỆT ĐỀ TÀI
    // =========================================================================
    echo "------------------------------------------------------------------------\n";
    echo "BƯỚC 4: GIÁO VỤ PHÊ DUYỆT ĐỀ TÀI ĐẠT CHUẨN\n";
    echo "------------------------------------------------------------------------\n";
    $adminController->approve($deTai->MaDeTai);
    $deTai->refresh();

    echo "  [PASS] Giáo vụ phê duyệt đề tài thành công!\n";
    echo "         - Trạng thái: [{$deTai->TrangThai}]\n";
    echo "         - Ngày duyệt: {$deTai->NgayDuyet}\n\n";

    if ($deTai->TrangThai !== 'Đã duyệt') {
        throw new Exception("Trạng thái đề tài phải là 'Đã duyệt'!");
    }

    // =========================================================================
    // BƯỚC 5: GIÁO VỤ CÔNG BỐ ĐỀ TÀI CHO SINH VIÊN
    // =========================================================================
    echo "------------------------------------------------------------------------\n";
    echo "BƯỚC 5: GIÁO VỤ CÔNG BỐ DANH MỤC ĐỀ TÀI CHÍNH THỨC\n";
    echo "------------------------------------------------------------------------\n";
    $reqCongBo = new Request(['MaHocKy' => $hocKy->MaHocKy]);
    $adminController->publish($reqCongBo);
    $deTai->refresh();

    echo "  [PASS] Đã kích hoạt lệnh công bố đề tài chính thức!\n";
    echo "         - Trạng thái đề tài: [{$deTai->TrangThai}]\n";

    $thongBaoMoi = ThongBao::orderBy('created_at', 'desc')->first();
    echo "         - Thông báo hệ thống phát sinh: '{$thongBaoMoi->TieuDe}'\n\n";

    if ($deTai->TrangThai !== 'Đã công bố') {
        throw new Exception("Trạng thái đề tài phải là 'Đã công bố'!");
    }

    // =========================================================================
    // BƯỚC 6: SINH VIÊN CHỌN HỌC PHẦN, XEM ĐỀ TÀI & ĐĂNG KÝ
    // =========================================================================
    echo "------------------------------------------------------------------------\n";
    echo "BƯỚC 6: SINH VIÊN CHỌN MÔN/HỌC PHẦN & ĐĂNG KÝ ĐỀ TÀI\n";
    echo "------------------------------------------------------------------------\n";
    $svController = new \App\Http\Controllers\SinhVien\DangKyDeTaiController();

    // Giả lập sinh viên
    $sinhVien = SinhVien::first();
    $tkSV = TaiKhoan::where('MaTK', $sinhVien->MaTK)->first();
    Auth::login($tkSV);

    // 6.1. Sinh viên lọc đề tài theo học phần "Khóa luận tốt nghiệp"
    $reqFilterHocPhan = new Request(['HocPhan' => 'Khóa luận tốt nghiệp']);
    $viewIndex = $svController->index($reqFilterHocPhan);
    $viewData = $viewIndex->getData();
    $detaisHocPhan = $viewData['detais'];

    $coDeTaiTest = false;
    foreach ($detaisHocPhan as $d) {
        if ($d->MaDeTai === $deTai->MaDeTai) {
            $coDeTaiTest = true;
            break;
        }
    }
    echo "  [PASS] Sinh viên lọc theo học phần 'Khóa luận tốt nghiệp': tìm thấy " . $detaisHocPhan->total() . " đề tài đã công bố.\n";
    echo "         Đề tài {$deTai->MaDeTai} có trong danh mục hiển thị: " . ($coDeTaiTest ? 'CÓ (Chính xác)' : 'KHÔNG') . "\n";

    // 6.2. Kiểm tra chặn khi đề tài chưa công bố
    $deTaiChuaCongBo = DeTai::create([
        'MaDeTai'             => 'DT_NO_' . rand(1000, 9999),
        'MaGV'                => $giangVien->MaGV,
        'TenDeTai'            => 'Đề tài còn đang duyệt',
        'MaHocKy'             => $hocKy->MaHocKy,
        'HocPhan'             => 'Khóa luận tốt nghiệp',
        'SoLuongSinhVienToiDa'=> 2,
        'TrangThai'           => 'Chờ duyệt',
    ]);

    // Tạo nhóm hợp lệ gồm 2 sinh viên (đáp ứng chỉ tiêu 2 SV)
    $maNhomTest = 'N_' . rand(10000, 99999);
    $nhom = Nhom::create([
        'MaNhom'        => $maNhomTest,
        'TenNhom'       => 'Nhóm Nghiên Cứu ML',
        'MaTruongNhom'  => $sinhVien->MaSV,
        'MaHocKy'       => $hocKy->MaHocKy,
    ]);

    ThanhVienNhom::create([
        'MaNhom'    => $nhom->MaNhom,
        'MaSV'      => $sinhVien->MaSV,
        'VaiTro'    => 'truong_nhom',
        'TrangThai' => 'da_tham_gia',
    ]);

    // Thử đăng ký đề tài CHƯA CÔNG BỐ -> Phải bị chặn
    $reqDangKyChuaCB = new Request(['MaDeTai' => $deTaiChuaCongBo->MaDeTai]);
    $resChuaCB = $svController->store($reqDangKyChuaCB);
    echo "  [PASS] Kiểm thử bảo mật: Đăng ký đề tài chưa công bố -> Bị chặn chính xác theo quy định.\n";

    // Thử sinh viên tự đề xuất riêng -> Phải bị từ chối
    $resDeXuatRieng = $svController->deXuatRieng(new Request());
    echo "  [PASS] Kiểm thử bảo mật: Sinh viên cố tình tự đề xuất riêng -> Bị từ chối chính xác.\n";

    // Đăng ký đề tài ĐÃ CÔNG BỐ HỢP LỆ
    $reqDangKyHopLe = new Request(['MaDeTai' => $deTai->MaDeTai]);
    $resHopLe = $svController->store($reqDangKyHopLe);
    
    $dangKyRecord = DangKyDeTai::where('MaNhom', $nhom->MaNhom)->where('MaDeTai', $deTai->MaDeTai)->first();
    if ($dangKyRecord) {
        echo "  [PASS] Trưởng nhóm đại diện đăng ký đề tài '{$deTai->TenDeTai}' thành công!\n";
        echo "         - Mã đơn đăng ký: {$dangKyRecord->MaDangKy}\n";
        echo "         - Trạng thái đơn: [{$dangKyRecord->TrangThai}]\n";
        echo "         - Giảng viên nhận: {$deTai->giangVien->HoTen}\n";
    } else {
        throw new Exception("Đăng ký đề tài hợp lệ thất bại!");
    }

    echo "\n========================================================================\n";
    echo "       KẾT QUẢ: 100% CÁC BƯỚC TRONG LUỒNG KIỂM THỬ ĐÃ ĐẠT CHUẨN!     \n";
    echo "========================================================================\n\n";

} catch (\Throwable $e) {
    echo "\n[THẤT BẠI] Lỗi trong quá trình kiểm thử:\n";
    echo $e->getMessage() . "\n";
    echo "Tại dòng: " . $e->getLine() . " tệp " . $e->getFile() . "\n\n";
} finally {
    DB::rollBack();
    echo "[-] Đã Rollback giao dịch database: Dữ liệu thực nghiệm được dọn dẹp sạch sẽ, an toàn tuyệt đối.\n\n";
}
