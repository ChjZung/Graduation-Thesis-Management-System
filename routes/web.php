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

// Custom: Quên mật khẩu (gửi yêu cầu thay vì email trực tiếp)
Route::get('/password/reset-request', [\App\Http\Controllers\Auth\QuenMatKhauController::class, 'showForm'])->name('password.request');
Route::post('/password/reset-request', [\App\Http\Controllers\Auth\QuenMatKhauController::class, 'sendRequest'])->name('password.send_request');

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

    // Báo cáo tiến độ
    Route::get('/baocao', [\App\Http\Controllers\GiangVien\DuyetBaoCaoController::class, 'index'])->name('giangvien.baocao.index');
    Route::post('/baocao/{maBaoCao}/nhanxet', [\App\Http\Controllers\GiangVien\DuyetBaoCaoController::class, 'storeNhanXet'])->name('giangvien.baocao.nhanxet');

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
    Route::resource('dangky', \App\Http\Controllers\SinhVien\DangKyDeTaiController::class)->names('sinhvien.dangky')->only(['index', 'store', 'destroy']);

    // Báo cáo tiến độ
    Route::get('/baocao', [\App\Http\Controllers\SinhVien\BaoCaoController::class, 'index'])->name('sinhvien.baocao.index');
    Route::post('/baocao', [\App\Http\Controllers\SinhVien\BaoCaoController::class, 'store'])->name('sinhvien.baocao.store');

    // Thông báo
    Route::get('/thongbao', [\App\Http\Controllers\SinhVien\ThongBaoController::class, 'index'])->name('sinhvien.thongbao.index');
    Route::post('/thongbao/{id}/read', [\App\Http\Controllers\SinhVien\ThongBaoController::class, 'markRead'])->name('sinhvien.thongbao.read');
    Route::post('/thongbao/read-all', [\App\Http\Controllers\SinhVien\ThongBaoController::class, 'markAllRead'])->name('sinhvien.thongbao.readAll');
});
