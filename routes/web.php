<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        /** @var \App\Models\TaiKhoan $user */
        $user = Auth::user();
        $user->loadMissing('vaiTro');
        $role = $user->vaiTro->TenVaiTro ?? '';

        if (in_array($role, ['Admin', 'Giáo vụ'])) return redirect()->route('admin.dashboard');
        if ($role === 'Giảng viên') return redirect()->route('giangvien.dashboard');
        if ($role === 'Sinh viên') return redirect()->route('sinhvien.dashboard');
    }
    return redirect()->route('login');
});

Auth::routes(['register' => false]);

// Custom: Quên mật khẩu
Route::get('/password/reset-request', [\App\Http\Controllers\Auth\QuenMatKhauController::class, 'showForm'])->name('password.request');
Route::post('/password/reset-request', [\App\Http\Controllers\Auth\QuenMatKhauController::class, 'sendRequest'])->name('password.send_request');

// Thiết lập mật khẩu lần đầu (Onboarding cho tài khoản INITIAL)
Route::middleware(['auth'])->group(function () {
    Route::get('/setup-password', [\App\Http\Controllers\Auth\PasswordSetupController::class, 'showSetupForm'])->name('password.setup');
    Route::post('/setup-password', [\App\Http\Controllers\Auth\PasswordSetupController::class, 'setupPassword'])->name('password.setup.post');
});


// ==========================================
// API ENDPOINTS (RESTful JSON)
// ==========================================
Route::prefix('api')->group(function () {
    // Đề tài - Full CRUD
    Route::get('/detais', [\App\Http\Controllers\ApiController::class, 'getDeTais']);
    Route::get('/detais/{id}', [\App\Http\Controllers\ApiController::class, 'getDeTaiDetail']);
    Route::post('/detais', [\App\Http\Controllers\ApiController::class, 'storeDeTai']);
    Route::put('/detais/{id}', [\App\Http\Controllers\ApiController::class, 'updateDeTai']);
    Route::delete('/detais/{id}', [\App\Http\Controllers\ApiController::class, 'destroyDeTai']);

    // Nhóm
    Route::get('/nhoms', [\App\Http\Controllers\ApiController::class, 'getNhoms']);
    Route::get('/nhoms/{id}', [\App\Http\Controllers\ApiController::class, 'getNhomDetail']);

    // Danh mục (Read-Only)
    Route::get('/sinhviens', [\App\Http\Controllers\ApiController::class, 'getSinhViens']);
    Route::get('/giangviens', [\App\Http\Controllers\ApiController::class, 'getGiangViens']);
    Route::get('/lops', [\App\Http\Controllers\ApiController::class, 'getLops']);
    Route::get('/hockys', [\App\Http\Controllers\ApiController::class, 'getHocKys']);
    Route::get('/bomons', [\App\Http\Controllers\ApiController::class, 'getBoMons']);
    Route::get('/nganhs', [\App\Http\Controllers\ApiController::class, 'getNganhs']);
    Route::get('/thongbaos', [\App\Http\Controllers\ApiController::class, 'getThongBaos']);
    Route::get('/thongke', [\App\Http\Controllers\ApiController::class, 'getThongKe']);
});

// ==========================================
// PROFILE ROUTES (all auth users)
// ==========================================
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'showProfile'])->name('profile.show');
    Route::get('/password/change', [\App\Http\Controllers\ProfileController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/password/change', [\App\Http\Controllers\ProfileController::class, 'changePassword'])->name('password.change.post');
});

// ==========================================
// ADMIN / GIÁO VỤ ROUTES
// ==========================================
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->group(function () {

    // Dashboard
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/taikhoan/{id}/toggle-lock', [\App\Http\Controllers\Admin\DashboardController::class, 'toggleLockAccount'])->name('admin.taikhoan.toggleLock');

    // Danh mục cơ bản
    Route::resource('khoa', \App\Http\Controllers\KhoaController::class);
    Route::resource('bomon', \App\Http\Controllers\BoMonController::class);
    Route::resource('nganh', \App\Http\Controllers\NganhController::class);
    Route::resource('lop', \App\Http\Controllers\LopController::class);
    Route::resource('hocky', \App\Http\Controllers\HocKyController::class);
    Route::resource('giangvien', \App\Http\Controllers\GiangVienController::class);
    Route::resource('sinhvien', \App\Http\Controllers\SinhVienController::class);

    // Yêu cầu đổi mật khẩu
    Route::get('yeu-cau-doi-mat-khau', [\App\Http\Controllers\Admin\YeuCauDoiMatKhauController::class, 'index'])->name('admin.yeucau.index');
    Route::post('yeu-cau-doi-mat-khau/{id}/duyet', [\App\Http\Controllers\Admin\YeuCauDoiMatKhauController::class, 'approve'])->name('admin.yeucau.approve');
    Route::post('yeu-cau-doi-mat-khau/{id}/tu-choi', [\App\Http\Controllers\Admin\YeuCauDoiMatKhauController::class, 'reject'])->name('admin.yeucau.reject');

    // Kế hoạch Khóa luận & Lịch Calendar
    Route::resource('kehoach', \App\Http\Controllers\Admin\KeHoachKhoaLuanController::class)->names('admin.kehoach');
    Route::get('/calendar', [\App\Http\Controllers\CalendarController::class, 'adminCalendar'])->name('admin.calendar');

    // Phê duyệt Đề tài do GV đề xuất
    Route::get('/duyet-detai', [\App\Http\Controllers\Admin\DuyetDeTaiController::class, 'index'])->name('admin.duyet_detai.index');
    Route::post('/duyet-detai/{id}/duyet', [\App\Http\Controllers\Admin\DuyetDeTaiController::class, 'approve'])->name('admin.duyet_detai.approve');
    Route::post('/duyet-detai/{id}/tu-choi', [\App\Http\Controllers\Admin\DuyetDeTaiController::class, 'reject'])->name('admin.duyet_detai.reject');

    // Phê duyệt Đơn đăng ký Đề tài của Nhóm Sinh viên
    Route::get('/duyet-dangky-detai', [\App\Http\Controllers\Admin\DuyetDangKyDeTaiController::class, 'index'])->name('admin.duyet_dangky.index');
    Route::post('/duyet-dangky-detai/{id}/duyet', [\App\Http\Controllers\Admin\DuyetDangKyDeTaiController::class, 'approve'])->name('admin.duyet_dangky.approve');
    Route::post('/duyet-dangky-detai/{id}/tu-choi', [\App\Http\Controllers\Admin\DuyetDangKyDeTaiController::class, 'reject'])->name('admin.duyet_dangky.reject');

    // ── GĐ6: Hội Đồng & Hồ Sơ Bảo Vệ ──
    Route::get('/hoi-dong', [\App\Http\Controllers\Admin\HoiDongController::class, 'index'])->name('admin.hoidong.index');
    Route::get('/hoi-dong/create', [\App\Http\Controllers\Admin\HoiDongController::class, 'create'])->name('admin.hoidong.create');
    Route::post('/hoi-dong', [\App\Http\Controllers\Admin\HoiDongController::class, 'store'])->name('admin.hoidong.store');
    Route::get('/hoi-dong/{id}', [\App\Http\Controllers\Admin\HoiDongController::class, 'show'])->name('admin.hoidong.show');
    Route::post('/hoi-dong/{id}/trang-thai', [\App\Http\Controllers\Admin\HoiDongController::class, 'updateTrangThai'])->name('admin.hoidong.updateTrangThai');
    Route::post('/hoi-dong/{id}/phan-cong-nhom', [\App\Http\Controllers\Admin\HoiDongController::class, 'phanCongNhom'])->name('admin.hoidong.phanCongNhom');

    Route::get('/ho-so-bao-ve', [\App\Http\Controllers\Admin\HoSoBaoVeController::class, 'index'])->name('admin.hosoBaoVe.index');
    Route::post('/ho-so-bao-ve/{id}/phan-cong', [\App\Http\Controllers\Admin\HoSoBaoVeController::class, 'phanCong'])->name('admin.hosoBaoVe.phanCong');
    Route::post('/ho-so-bao-ve/{id}/xac-nhan', [\App\Http\Controllers\Admin\HoSoBaoVeController::class, 'xacNhan'])->name('admin.hosoBaoVe.xacNhan');

    // Excel Import & Templates
    Route::get('/import/template/{type}', [\App\Http\Controllers\Admin\ImportTemplateController::class, 'downloadTemplate'])->name('admin.import.template');
    Route::get('/import/error-log/{filename}', [\App\Http\Controllers\Admin\ImportTemplateController::class, 'downloadErrorLog'])->name('admin.import.errorLog');

    Route::post('/sinhvien/import', [\App\Http\Controllers\SinhVienController::class, 'importExcel'])->name('admin.sinhvien.import');
    Route::post('/giangvien/import', [\App\Http\Controllers\GiangVienController::class, 'importExcel'])->name('admin.giangvien.import');
    Route::post('/bomon/import', [\App\Http\Controllers\BoMonController::class, 'importExcel'])->name('admin.bomon.import');
    Route::post('/nganh/import', [\App\Http\Controllers\NganhController::class, 'importExcel'])->name('admin.nganh.import');
    Route::post('/lop/import', [\App\Http\Controllers\LopController::class, 'importExcel'])->name('admin.lop.import');
    Route::post('/hocky/import', [\App\Http\Controllers\HocKyController::class, 'importExcel'])->name('admin.hocky.import');

    // Thông báo
    Route::resource('thongbao', \App\Http\Controllers\ThongBaoController::class)->only(['index', 'store', 'destroy']);
});



// ==========================================
// GIẢNG VIÊN ROUTES
// ==========================================
Route::middleware(['auth', 'role:Giảng viên'])->prefix('giangvien')->group(function () {
    Route::get('/', function () {
        return redirect()->route('giangvien.calendar');
    })->name('giangvien.dashboard');

    // Lịch Calendar Giảng viên
    Route::get('/calendar', [\App\Http\Controllers\CalendarController::class, 'giangVienCalendar'])->name('giangvien.calendar');

    // Đề tài
    Route::resource('detai', \App\Http\Controllers\GiangVien\DeTaiController::class)->names('giangvien.detai');
    Route::post('detai/{id}/gan-nhom', [\App\Http\Controllers\GiangVien\DeTaiController::class, 'ganNhom'])->name('giangvien.detai.ganNhom');

    // Báo cáo tiến độ
    Route::get('/baocao', [\App\Http\Controllers\GiangVien\DuyetBaoCaoController::class, 'index'])->name('giangvien.baocao.index');
    Route::post('/baocao/{maBaoCao}/nhanxet', [\App\Http\Controllers\GiangVien\DuyetBaoCaoController::class, 'storeNhanXet'])->name('giangvien.baocao.nhanxet');

    // ── GĐ6: Chấm Điểm Hội Đồng ──
    Route::get('/chamdiem', [\App\Http\Controllers\GiangVien\ChamDiemController::class, 'index'])->name('giangvien.chamdiem.index');
    Route::post('/chamdiem', [\App\Http\Controllers\GiangVien\ChamDiemController::class, 'store'])->name('giangvien.chamdiem.store');

    // Thông báo
    Route::get('/thongbao', [\App\Http\Controllers\ThongBaoController::class, 'index'])->name('giangvien.thongbao.index');
});


// ==========================================
// SINH VIÊN ROUTES
// ==========================================
Route::middleware(['auth', 'role:Sinh viên'])->prefix('sinhvien')->group(function () {
    Route::get('/', function () {
        return redirect()->route('sinhvien.calendar');
    })->name('sinhvien.dashboard');

    // Lịch Calendar Sinh viên
    Route::get('/calendar', [\App\Http\Controllers\CalendarController::class, 'sinhVienCalendar'])->name('sinhvien.calendar');

    // Nhóm & Đăng ký đề tài
    Route::get('nhom', [\App\Http\Controllers\SinhVien\NhomController::class, 'index'])->name('sinhvien.nhom.index');
    Route::post('nhom', [\App\Http\Controllers\SinhVien\NhomController::class, 'store'])->name('sinhvien.nhom.store');
    Route::get('nhom/tra-cuu-sinh-vien', [\App\Http\Controllers\SinhVien\NhomController::class, 'traCuuSinhVien'])->name('sinhvien.nhom.traCuuSinhVien');
    Route::get('nhom/{id}/chi-tiet', [\App\Http\Controllers\SinhVien\NhomController::class, 'chiTietNhom'])->name('sinhvien.nhom.chiTiet');
    Route::post('nhom/moi', [\App\Http\Controllers\SinhVien\NhomController::class, 'moiThanhVien'])->name('sinhvien.nhom.moiThanhVien');

    Route::post('nhom/loi-moi/{id}/chap-nhan', [\App\Http\Controllers\SinhVien\NhomController::class, 'xacNhanLoiMoi'])->name('sinhvien.nhom.xacNhanLoiMoi');
    Route::post('nhom/loi-moi/{id}/tu-choi', [\App\Http\Controllers\SinhVien\NhomController::class, 'tuChoiLoiMoi'])->name('sinhvien.nhom.tuChoiLoiMoi');
    Route::post('nhom/{id}/loi-moi/{maSV}/thu-hoi', [\App\Http\Controllers\SinhVien\NhomController::class, 'huyLoiMoiDaGui'])->name('sinhvien.nhom.huyLoiMoiDaGui');
    Route::post('nhom/{id}/khai-tru/{maSV}', [\App\Http\Controllers\SinhVien\NhomController::class, 'khaiTruThanhVien'])->name('sinhvien.nhom.khaiTru');
    Route::post('nhom/{id}/xin-gia-nhap', [\App\Http\Controllers\SinhVien\NhomController::class, 'xinGiaNhap'])->name('sinhvien.nhom.xinGiaNhap');

    Route::post('nhom/{id}/huy-xin-gia-nhap', [\App\Http\Controllers\SinhVien\NhomController::class, 'huyXinGiaNhap'])->name('sinhvien.nhom.huyXinGiaNhap');
    Route::post('nhom/{id}/yeu-cau/{maSV}/duyet', [\App\Http\Controllers\SinhVien\NhomController::class, 'duyetYeuCauXinVao'])->name('sinhvien.nhom.duyetYeuCau');
    Route::post('nhom/{id}/yeu-cau/{maSV}/tu-choi', [\App\Http\Controllers\SinhVien\NhomController::class, 'tuChoiYeuCauXinVao'])->name('sinhvien.nhom.tuChoiYeuCau');
    Route::resource('dangky', \App\Http\Controllers\SinhVien\DangKyDeTaiController::class)->names('sinhvien.dangky')->only(['index', 'store', 'destroy']);


    // Báo cáo tiến độ
    Route::get('/baocao', [\App\Http\Controllers\SinhVien\BaoCaoController::class, 'index'])->name('sinhvien.baocao.index');
    Route::post('/baocao', [\App\Http\Controllers\SinhVien\BaoCaoController::class, 'store'])->name('sinhvien.baocao.store');

    // ── GĐ6: Hồ Sơ Bảo Vệ & Kết Quả ──
    Route::get('/ho-so-bao-ve', [\App\Http\Controllers\SinhVien\HoSoBaoVeController::class, 'index'])->name('sinhvien.hoso.index');
    Route::post('/ho-so-bao-ve', [\App\Http\Controllers\SinhVien\HoSoBaoVeController::class, 'store'])->name('sinhvien.hoso.store');
    Route::get('/ket-qua', [\App\Http\Controllers\SinhVien\KetQuaController::class, 'index'])->name('sinhvien.ketqua.index');

    // Thông báo
    Route::get('/thongbao', [\App\Http\Controllers\SinhVien\ThongBaoController::class, 'index'])->name('sinhvien.thongbao.index');
    Route::post('/thongbao/{id}/read', [\App\Http\Controllers\SinhVien\ThongBaoController::class, 'markRead'])->name('sinhvien.thongbao.read');
    Route::post('/thongbao/read-all', [\App\Http\Controllers\SinhVien\ThongBaoController::class, 'markAllRead'])->name('sinhvien.thongbao.readAll');
});


