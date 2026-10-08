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
        if ($role === 'Trưởng khoa') return redirect()->route('truongkhoa.dashboard');
        if ($role === 'Trưởng bộ môn') return redirect()->route('truongbomon.dashboard');
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
// PROFILE & SCHEDULE MATRIX ROUTES (all auth users)
// ==========================================
Route::middleware(['auth'])->group(function () {
    Route::get('/lich-quy-trinh-matrix', [\App\Http\Controllers\CalendarController::class, 'scheduleMatrix'])->name('calendar.matrix');
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'showProfile'])->name('profile.show');
    Route::post('/profile', [\App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::get('/password/change', [\App\Http\Controllers\ProfileController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/password/change', [\App\Http\Controllers\ProfileController::class, 'changePassword'])->name('password.change.post');

    // Route aliases cho Đổi mật khẩu (tránh lỗi 404)
    Route::get('/doi-mat-khau', [\App\Http\Controllers\ProfileController::class, 'showChangePasswordForm'])->name('password.doi');
    Route::post('/doi-mat-khau', [\App\Http\Controllers\ProfileController::class, 'changePassword']);
    Route::get('/change-password', fn() => redirect()->route('password.change'));
    Route::post('/change-password', [\App\Http\Controllers\ProfileController::class, 'changePassword']);
});

// ==========================================
// ADMIN / GIÁO VỤ ROUTES
// ==========================================
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->group(function () {

    // Dashboard
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/taikhoan/{id}/toggle-lock', [\App\Http\Controllers\Admin\TaiKhoanController::class, 'toggleLock'])->name('admin.taikhoan.toggleLock');
    Route::post('/taikhoan/{id}/reset-password', [\App\Http\Controllers\Admin\TaiKhoanController::class, 'resetPassword'])->name('admin.taikhoan.resetPassword');
    Route::post('/taikhoan/can-bo', [\App\Http\Controllers\Admin\TaiKhoanController::class, 'storeCanBo'])->name('admin.taikhoan.store_canbo');
    Route::post('/taikhoan/import', [\App\Http\Controllers\Admin\TaiKhoanController::class, 'importExcel'])->name('admin.taikhoan.import');
    Route::resource('taikhoan', \App\Http\Controllers\Admin\TaiKhoanController::class)->names('admin.taikhoan');

    // Danh mục cơ bản
    Route::resource('khoa', \App\Http\Controllers\KhoaController::class);
    Route::resource('bomon', \App\Http\Controllers\BoMonController::class);
    Route::post('/bomon/{id}/assign-lecturer', [\App\Http\Controllers\BoMonController::class, 'assignLecturer'])->name('admin.bomon.assign_lecturer');
    Route::post('/bomon/{id}/remove-lecturer/{magv}', [\App\Http\Controllers\BoMonController::class, 'removeLecturer'])->name('admin.bomon.remove_lecturer');
    Route::post('/bomon/{id}/import-lecturers', [\App\Http\Controllers\BoMonController::class, 'importLecturers'])->name('admin.bomon.import_lecturers');
    Route::resource('nganh', \App\Http\Controllers\NganhController::class);
    Route::resource('lop', \App\Http\Controllers\LopController::class);
    Route::resource('hocky', \App\Http\Controllers\HocKyController::class);
    Route::resource('hocphan', \App\Http\Controllers\HocPhanController::class);
    Route::resource('giangvien', \App\Http\Controllers\GiangVienController::class);
    Route::get('sinhvien/du-dieu-kien', [\App\Http\Controllers\SinhVienController::class, 'dieuKienIndex'])->name('admin.sinhvien.dieu_kien');
    Route::post('sinhvien/du-dieu-kien/ra-soat', [\App\Http\Controllers\SinhVienController::class, 'dieuKienRaSoat'])->name('admin.sinhvien.ra_soat');
    Route::post('sinhvien/du-dieu-kien/cap-nhat', [\App\Http\Controllers\SinhVienController::class, 'dieuKienCapNhat'])->name('admin.sinhvien.cap_nhat');
    Route::post('sinhvien/du-dieu-kien/cong-bo', [\App\Http\Controllers\SinhVienController::class, 'dieuKienCongBo'])->name('admin.sinhvien.cong_bo');
    Route::get('sinhvien/du-dieu-kien/export', [\App\Http\Controllers\SinhVienController::class, 'dieuKienExport'])->name('admin.sinhvien.export_du_dk');

    Route::resource('sinhvien', \App\Http\Controllers\SinhVienController::class);

    // Yêu cầu đổi mật khẩu
    Route::get('yeu-cau-doi-mat-khau', [\App\Http\Controllers\Admin\YeuCauDoiMatKhauController::class, 'index'])->name('admin.yeucau.index');
    Route::post('yeu-cau-doi-mat-khau/{id}/duyet', [\App\Http\Controllers\Admin\YeuCauDoiMatKhauController::class, 'approve'])->name('admin.yeucau.approve');
    Route::post('yeu-cau-doi-mat-khau/{id}/tu-choi', [\App\Http\Controllers\Admin\YeuCauDoiMatKhauController::class, 'reject'])->name('admin.yeucau.reject');

    // Kế hoạch Khóa luận & Upload Văn bản thông báo
    Route::get('/kehoach/import-document', [\App\Http\Controllers\Admin\DocumentPlanController::class, 'importForm'])->name('admin.kehoach.importDocument');
    Route::post('/kehoach/process-parse', [\App\Http\Controllers\Admin\DocumentPlanController::class, 'processParse'])->name('admin.kehoach.processParse');
    Route::get('/kehoach/preview-document', [\App\Http\Controllers\Admin\DocumentPlanController::class, 'previewDocument'])->name('admin.kehoach.previewDocument');
    Route::post('/kehoach/confirm-import', [\App\Http\Controllers\Admin\DocumentPlanController::class, 'confirmImport'])->name('admin.kehoach.confirmImport');
    Route::get('/kehoach/document-history/{maVanBan}', [\App\Http\Controllers\Admin\DocumentPlanController::class, 'history'])->name('admin.kehoach.documentHistory');
    Route::post('/kehoach/document-rollback/{maVanBan}/{versionId}', [\App\Http\Controllers\Admin\DocumentPlanController::class, 'rollbackVersion'])->name('admin.kehoach.documentRollback');

    Route::resource('kehoach', \App\Http\Controllers\Admin\KeHoachKhoaLuanController::class)->names('admin.kehoach');
    Route::post('/kehoach/{id}/status', [\App\Http\Controllers\Admin\KeHoachKhoaLuanController::class, 'updateStatus'])->name('admin.kehoach.updateStatus');

    // Quy định Khóa luận tốt nghiệp
    Route::post('/quydinh/init-defaults', [\App\Http\Controllers\Admin\QuyDinhKhoaLuanController::class, 'initDefaults'])->name('admin.quydinh.initDefaults');
    Route::resource('quydinh', \App\Http\Controllers\Admin\QuyDinhKhoaLuanController::class)->names('admin.quydinh');

    // Biểu mẫu Khóa luận
    Route::resource('bieumau', \App\Http\Controllers\Admin\BieuMauController::class)->names('admin.bieumau');

    Route::get('/calendar', [\App\Http\Controllers\CalendarController::class, 'adminCalendar'])->name('admin.calendar');

    // Theo dõi Đồ án / Khóa luận
    Route::get('/theo-doi-do-an', [\App\Http\Controllers\Admin\TheoDoiDoAnController::class, 'index'])->name('admin.theodoi.index');
    Route::get('/theo-doi-do-an/{id}', [\App\Http\Controllers\Admin\TheoDoiDoAnController::class, 'show'])->name('admin.theodoi.show');
    Route::post('/theo-doi-do-an/{id}/remind', [\App\Http\Controllers\Admin\TheoDoiDoAnController::class, 'remindGroup'])->name('admin.theodoi.remind');

    // Quản lý, Theo dõi & Công bố Đề tài (Giáo vụ không tự ý thay thế quyết định chuyên môn của TBM/TK)
    // Quản lý & Công bố Đề tài (Giáo vụ theo dõi và công bố đề tài đã duyệt)
    Route::get('/duyet-detai/export', [\App\Http\Controllers\Admin\DuyetDeTaiController::class, 'export'])->name('admin.duyet_detai.export');
    Route::get('/duyet-detai', [\App\Http\Controllers\Admin\DuyetDeTaiController::class, 'index'])->name('admin.duyet_detai.index');
    Route::post('/duyet-detai/cong-bo', [\App\Http\Controllers\Admin\DuyetDeTaiController::class, 'publish'])->name('admin.duyet_detai.publish');

    // Quản lý & Theo dõi Đăng ký Đề tài của Nhóm Sinh viên (GVHD phê duyệt, Giáo vụ theo dõi/tra cứu)
    Route::get('/duyet-dangky-detai', [\App\Http\Controllers\Admin\DuyetDangKyDeTaiController::class, 'index'])->name('admin.duyet_dangky.index');
    Route::get('/duyet-dangky-detai/export', [\App\Http\Controllers\Admin\DuyetDangKyDeTaiController::class, 'export'])->name('admin.duyet_dangky.export');
    Route::post('/duyet-dangky-detai/gan-ngoai-le', [\App\Http\Controllers\Admin\DuyetDangKyDeTaiController::class, 'assignTopicForNgoaiLe'])->name('admin.duyet_dangky.assignTopicForNgoaiLe');
    Route::post('/duyet-dangky-detai/cong-bo-chinh-thuc', [\App\Http\Controllers\Admin\DuyetDangKyDeTaiController::class, 'publishOfficialList'])->name('admin.duyet_dangky.publishOfficialList');

    // ── GĐ6: Hội Đồng & Hồ Sơ Bảo Vệ ──
    Route::get('/hoi-dong', [\App\Http\Controllers\Admin\HoiDongController::class, 'index'])->name('admin.hoidong.index');
    Route::get('/hoi-dong/create', [\App\Http\Controllers\Admin\HoiDongController::class, 'create'])->name('admin.hoidong.create');
    Route::post('/hoi-dong', [\App\Http\Controllers\Admin\HoiDongController::class, 'store'])->name('admin.hoidong.store');
    Route::get('/hoi-dong/{id}', [\App\Http\Controllers\Admin\HoiDongController::class, 'show'])->name('admin.hoidong.show');
    Route::post('/hoi-dong/{id}/trang-thai', [\App\Http\Controllers\Admin\HoiDongController::class, 'updateTrangThai'])->name('admin.hoidong.updateTrangThai');
    Route::post('/hoi-dong/{id}/phan-cong-nhom', [\App\Http\Controllers\Admin\HoiDongController::class, 'phanCongNhom'])->name('admin.hoidong.phanCongNhom');
    Route::post('/hoi-dong/{id}/huy-phan-cong/{maHoSo}', [\App\Http\Controllers\Admin\HoiDongController::class, 'huyPhanCongNhom'])->name('admin.hoidong.huyPhanCongNhom');

    Route::get('/ho-so-bao-ve', [\App\Http\Controllers\Admin\HoSoBaoVeController::class, 'index'])->name('admin.hosoBaoVe.index');
    Route::post('/ho-so-bao-ve/{id}/phan-cong', [\App\Http\Controllers\Admin\HoSoBaoVeController::class, 'phanCong'])->name('admin.hosoBaoVe.phanCong');
    Route::post('/ho-so-bao-ve/{id}/xac-nhan', [\App\Http\Controllers\Admin\HoSoBaoVeController::class, 'xacNhan'])->name('admin.hosoBaoVe.xacNhan');
    Route::post('/ho-so-bao-ve/{id}/luu-tru', [\App\Http\Controllers\Admin\HoSoBaoVeController::class, 'luuTru'])->name('admin.hosoBaoVe.luuTru');

    // Kết quả & Xuất Bảng Điểm Excel
    Route::get('/ketqua', [\App\Http\Controllers\Admin\KetQuaController::class, 'index'])->name('admin.ketqua.index');
    Route::get('/ketqua/export', [\App\Http\Controllers\Admin\KetQuaController::class, 'exportExcel'])->name('admin.ketqua.export');

    // Excel Import & Templates
    Route::get('/import/template/{type}', [\App\Http\Controllers\Admin\ImportTemplateController::class, 'downloadTemplate'])->name('admin.import.template');
    Route::get('/import/error-log/{filename}', [\App\Http\Controllers\Admin\ImportTemplateController::class, 'downloadErrorLog'])->name('admin.import.errorLog');

    Route::post('/khoa/import', [\App\Http\Controllers\KhoaController::class, 'importExcel'])->name('admin.khoa.import');
    Route::post('/sinhvien/import', [\App\Http\Controllers\SinhVienController::class, 'importExcel'])->name('admin.sinhvien.import');
    Route::post('/giangvien/import', [\App\Http\Controllers\GiangVienController::class, 'importExcel'])->name('admin.giangvien.import');
    Route::post('/bomon/import', [\App\Http\Controllers\BoMonController::class, 'importExcel'])->name('admin.bomon.import');
    Route::post('/nganh/import', [\App\Http\Controllers\NganhController::class, 'importExcel'])->name('admin.nganh.import');
    Route::post('/lop/import', [\App\Http\Controllers\LopController::class, 'importExcel'])->name('admin.lop.import');
    Route::post('/hocky/import', [\App\Http\Controllers\HocKyController::class, 'importExcel'])->name('admin.hocky.import');
    Route::post('/hocphan/import', [\App\Http\Controllers\HocPhanController::class, 'importExcel'])->name('admin.hocphan.import');

    // Thông báo
    Route::post('/thongbao/{id}/send-now', [\App\Http\Controllers\ThongBaoController::class, 'sendNow'])->name('thongbao.sendNow');
    Route::resource('thongbao', \App\Http\Controllers\ThongBaoController::class);

    // Thống kê Đề tài Khóa luận
    Route::get('/thongke/detai', [\App\Http\Controllers\ThongKeDeTaiController::class, 'index'])->name('admin.thongke.detai');
    Route::get('/thongke/detai/export', [\App\Http\Controllers\ThongKeDeTaiController::class, 'exportExcel'])->name('admin.thongke.detai.export');

    // Bổ nhiệm & Import tài khoản Trưởng khoa / Trưởng bộ môn
    Route::post('/giangvien/{id}/bo-nhiem', [\App\Http\Controllers\GiangVienController::class, 'boNhiem'])->name('admin.giangvien.bo_nhiem');
    Route::post('/giangvien/import-tbm-tk', [\App\Http\Controllers\GiangVienController::class, 'importTaiKhoanTBM_TK'])->name('admin.giangvien.import_tbm_tk');
});



// ==========================================
// GIẢNG VIÊN ROUTES
// ==========================================
Route::middleware(['auth', 'role:Giảng viên'])->prefix('giangvien')->group(function () {
    Route::get('/', [\App\Http\Controllers\GiangVien\DashboardController::class, 'index'])->name('giangvien.dashboard');

    // Lịch Calendar Giảng viên
    Route::get('/calendar', [\App\Http\Controllers\CalendarController::class, 'giangVienCalendar'])->name('giangvien.calendar');
    Route::get('/cong-viec-huong-dan', [\App\Http\Controllers\GiangVien\DeTaiController::class, 'myTasks'])->name('giangvien.my_tasks');

    // Đề tài
    Route::get('detai/bieu-mau-de-cuong', [\App\Http\Controllers\GiangVien\DeTaiController::class, 'downloadTemplate'])->name('giangvien.detai.download_template');
    Route::post('detai/{id}/nop-de-cuong', [\App\Http\Controllers\GiangVien\DeTaiController::class, 'nopDeCuong'])->name('giangvien.detai.nopDeCuong');
    Route::resource('detai', \App\Http\Controllers\GiangVien\DeTaiController::class)->names('giangvien.detai');
    Route::post('detai/{id}/gan-nhom', [\App\Http\Controllers\GiangVien\DeTaiController::class, 'ganNhom'])->name('giangvien.detai.ganNhom');

    // Báo cáo tiến độ
    Route::get('/baocao', [\App\Http\Controllers\GiangVien\DuyetBaoCaoController::class, 'index'])->name('giangvien.baocao.index');
    Route::get('/baocao/{id}', [\App\Http\Controllers\GiangVien\DuyetBaoCaoController::class, 'show'])->name('giangvien.baocao.show');
    Route::post('/baocao/{maBaoCao}/nhanxet', [\App\Http\Controllers\GiangVien\DuyetBaoCaoController::class, 'storeNhanXet'])->name('giangvien.baocao.nhanxet');

    // ── GĐ6: Chấm Điểm Hội Đồng ──
    Route::get('/chamdiem', [\App\Http\Controllers\GiangVien\ChamDiemController::class, 'index'])->name('giangvien.chamdiem.index');
    Route::post('/chamdiem', [\App\Http\Controllers\GiangVien\ChamDiemController::class, 'store'])->name('giangvien.chamdiem.store');

    // Phê duyệt đăng ký đề tài của Sinh viên / Nhóm
    Route::get('/duyet-dangky-detai', [\App\Http\Controllers\GiangVien\DuyetDeTaiController::class, 'index'])->name('giangvien.duyet_dangky.index');
    Route::post('/duyet-dangky-detai/{id}', [\App\Http\Controllers\GiangVien\DuyetDeTaiController::class, 'update'])->name('giangvien.duyet_dangky.update');
    Route::get('/duyet-detai', [\App\Http\Controllers\GiangVien\DuyetDeTaiController::class, 'index']);
    Route::post('/duyet-detai/{id}/duyet', [\App\Http\Controllers\GiangVien\DuyetDeTaiController::class, 'update']);

    // Phản biện đề cương
    Route::get('/phan-bien-de-cuong', [\App\Http\Controllers\GiangVien\PhanBienDeCuongController::class, 'index'])->name('giangvien.phanbien.index');
    Route::get('/phan-bien-de-cuong/{id}', [\App\Http\Controllers\GiangVien\PhanBienDeCuongController::class, 'show'])->name('giangvien.phanbien.show');
    Route::post('/phan-bien-de-cuong/{id}', [\App\Http\Controllers\GiangVien\PhanBienDeCuongController::class, 'store'])->name('giangvien.phanbien.store');

    // Xác nhận hồ sơ bảo vệ (GVHD)
    Route::get('/xac-nhan-bao-ve', [\App\Http\Controllers\GiangVien\XacNhanBaoVeController::class, 'index'])->name('giangvien.xacnhan_baove.index');
    Route::post('/xac-nhan-bao-ve/{id}', [\App\Http\Controllers\GiangVien\XacNhanBaoVeController::class, 'xacNhan'])->name('giangvien.xacnhan_baove.xacNhan');

    // Thông báo cho Giảng viên (xem, soạn và gửi đến nhóm hướng dẫn)
    Route::get('/thongbao', [\App\Http\Controllers\GiangVien\ThongBaoController::class, 'index'])->name('giangvien.thongbao.index');
    Route::get('/thongbao/create', [\App\Http\Controllers\GiangVien\ThongBaoController::class, 'create'])->name('giangvien.thongbao.create');
    Route::post('/thongbao', [\App\Http\Controllers\GiangVien\ThongBaoController::class, 'store'])->name('giangvien.thongbao.store');
    Route::get('/thongbao/{id}', [\App\Http\Controllers\GiangVien\ThongBaoController::class, 'show'])->name('giangvien.thongbao.show');

    // Thống kê Đề tài Khóa luận (Chỉ đề tài của Giảng viên)
    Route::get('/thongke/detai', [\App\Http\Controllers\ThongKeDeTaiController::class, 'index'])->name('giangvien.thongke.detai');
    Route::get('/thongke/detai/export', [\App\Http\Controllers\ThongKeDeTaiController::class, 'exportExcel'])->name('giangvien.thongke.detai.export');

    // Đổi mật khẩu alias
    Route::get('/doi-mat-khau', fn() => redirect()->route('password.change'));
    Route::get('/change-password', fn() => redirect()->route('password.change'));
});


// ==========================================
// SINH VIÊN ROUTES
// ==========================================
Route::middleware(['auth', 'role:Sinh viên'])->prefix('sinhvien')->group(function () {
    Route::get('/', [\App\Http\Controllers\SinhVien\DashboardController::class, 'index'])->name('sinhvien.dashboard');

    // Lịch Calendar Sinh viên
    Route::get('/calendar', [\App\Http\Controllers\CalendarController::class, 'sinhVienCalendar'])->name('sinhvien.calendar');
    Route::get('/cong-viec-cua-toi', [\App\Http\Controllers\SinhVien\NhomController::class, 'myTasks'])->name('sinhvien.my_tasks');

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
    Route::post('/ho-so-bao-ve/nop-ban-hoan-chinh', [\App\Http\Controllers\SinhVien\HoSoBaoVeController::class, 'nopBanHoanChinh'])->name('sinhvien.hoso.nopBanHoanChinh');
    Route::get('/ket-qua', [\App\Http\Controllers\SinhVien\KetQuaController::class, 'index'])->name('sinhvien.ketqua.index');

    // Thông báo
    Route::get('/thongbao', [\App\Http\Controllers\SinhVien\ThongBaoController::class, 'index'])->name('sinhvien.thongbao.index');
    Route::post('/thongbao/{id}/read', [\App\Http\Controllers\SinhVien\ThongBaoController::class, 'markRead'])->name('sinhvien.thongbao.read');
    Route::post('/thongbao/read-all', [\App\Http\Controllers\SinhVien\ThongBaoController::class, 'markAllRead'])->name('sinhvien.thongbao.readAll');

    // Đổi mật khẩu alias
    Route::get('/doi-mat-khau', fn() => redirect()->route('password.change'));
    Route::get('/change-password', fn() => redirect()->route('password.change'));
});


// ==========================================
// TRƯỞNG BỘ MÔN ROUTES
// ==========================================
Route::middleware(['auth', 'role:Trưởng bộ môn'])->prefix('truongbomon')->group(function () {
    Route::get('/', [\App\Http\Controllers\TruongBoMon\DashboardController::class, 'index'])->name('truongbomon.dashboard');
    Route::get('/duyet-detai/export', [\App\Http\Controllers\TruongBoMon\DuyetDeTaiController::class, 'export'])->name('truongbomon.duyet_detai.export');
    Route::get('/duyet-detai', [\App\Http\Controllers\TruongBoMon\DuyetDeTaiController::class, 'index'])->name('truongbomon.duyet_detai.index');
    Route::get('/duyet-detai/{id}', [\App\Http\Controllers\TruongBoMon\DuyetDeTaiController::class, 'show'])->name('truongbomon.duyet_detai.show');
    Route::post('/duyet-detai/{id}/phan-cong-phan-bien', [\App\Http\Controllers\TruongBoMon\DuyetDeTaiController::class, 'phanCongPhanBien'])->name('truongbomon.duyet_detai.phanCongPhanBien');
    Route::post('/duyet-detai/{id}/duyet', [\App\Http\Controllers\TruongBoMon\DuyetDeTaiController::class, 'approve'])->name('truongbomon.duyet_detai.duyet');
    Route::post('/duyet-detai/{id}/duyet-va-cong-bo', [\App\Http\Controllers\TruongBoMon\DuyetDeTaiController::class, 'duyetVaCongBo'])->name('truongbomon.duyet_detai.duyetVaCongBo');
    Route::post('/duyet-detai/{id}/yeu-cau-chinh-sua', [\App\Http\Controllers\TruongBoMon\DuyetDeTaiController::class, 'requestEdit'])->name('truongbomon.duyet_detai.yeuCauChinhSua');
    Route::post('/duyet-detai/{id}/request-edit', [\App\Http\Controllers\TruongBoMon\DuyetDeTaiController::class, 'requestEdit'])->name('truongbomon.duyet_detai.requestEdit');
    Route::post('/duyet-detai/{id}/tu-choi', [\App\Http\Controllers\TruongBoMon\DuyetDeTaiController::class, 'reject'])->name('truongbomon.duyet_detai.tuChoi');

    // Phân công phản biện đề cương riêng
    Route::get('/phan-cong-phan-bien', [\App\Http\Controllers\TruongBoMon\PhanCongPhanBienController::class, 'index'])->name('truongbomon.phancong.index');
    Route::post('/phan-cong-phan-bien/{id}', [\App\Http\Controllers\TruongBoMon\PhanCongPhanBienController::class, 'store'])->name('truongbomon.phancong.store');

    // Duyệt đề cương riêng biệt
    Route::get('/duyet-de-cuong', [\App\Http\Controllers\TruongBoMon\DuyetDeCuongController::class, 'index'])->name('truongbomon.duyet_decuong.index');
    Route::post('/duyet-de-cuong/{id}/duyet-va-cong-bo', [\App\Http\Controllers\TruongBoMon\DuyetDeCuongController::class, 'duyetVaCongBo'])->name('truongbomon.duyet_decuong.duyetVaCongBo');
    Route::post('/duyet-de-cuong/{id}/yeu-cau-chinh-sua', [\App\Http\Controllers\TruongBoMon\DuyetDeCuongController::class, 'requestEdit'])->name('truongbomon.duyet_decuong.yeuCauChinhSua');

    Route::get('/theo-doi', [\App\Http\Controllers\TruongBoMon\TheoDoiController::class, 'index'])->name('truongbomon.theodoi.index');

    // Thống kê Đề tài Khóa luận (Chuyển tab trong Duyệt đề tài)
    Route::get('/thongke/detai', fn() => redirect()->route('truongbomon.duyet_detai.index', ['view' => 'thongke']))->name('truongbomon.thongke.detai');
    Route::get('/thongke/detai/export', [\App\Http\Controllers\ThongKeDeTaiController::class, 'exportExcel'])->name('truongbomon.thongke.detai.export');

    // Đổi mật khẩu alias
    Route::get('/doi-mat-khau', fn() => redirect()->route('password.change'));
    Route::get('/change-password', fn() => redirect()->route('password.change'));
});


// ==========================================
// TRƯỞNG KHOA ROUTES
// ==========================================
Route::middleware(['auth', 'role:Trưởng khoa'])->prefix('truongkhoa')->group(function () {
    Route::get('/', [\App\Http\Controllers\TruongKhoa\DashboardController::class, 'index'])->name('truongkhoa.dashboard');
    Route::get('/duyet-detai/export', [\App\Http\Controllers\TruongKhoa\DuyetDeTaiController::class, 'export'])->name('truongkhoa.duyet_detai.export');
    Route::get('/duyet-detai', [\App\Http\Controllers\TruongKhoa\DuyetDeTaiController::class, 'index'])->name('truongkhoa.duyet_detai.index');
    Route::get('/duyet-detai/{id}', [\App\Http\Controllers\TruongKhoa\DuyetDeTaiController::class, 'show'])->name('truongkhoa.duyet_detai.show');
    Route::post('/duyet-detai/{id}/duyet', [\App\Http\Controllers\TruongKhoa\DuyetDeTaiController::class, 'approve'])->name('truongkhoa.duyet_detai.duyet');
    Route::post('/duyet-detai/{id}/yeu-cau-chinh-sua', [\App\Http\Controllers\TruongKhoa\DuyetDeTaiController::class, 'requestEdit'])->name('truongkhoa.duyet_detai.yeuCauChinhSua');
    Route::post('/duyet-detai/{id}/tu-choi', [\App\Http\Controllers\TruongKhoa\DuyetDeTaiController::class, 'reject'])->name('truongkhoa.duyet_detai.tuChoi');
    Route::get('/theo-doi', [\App\Http\Controllers\TruongKhoa\TheoDoiController::class, 'index'])->name('truongkhoa.theodoi.index');

    // Kế hoạch khóa luận cấp Khoa
    Route::get('/ke-hoach', [\App\Http\Controllers\TruongKhoa\KeHoachController::class, 'index'])->name('truongkhoa.kehoach.index');

    // Kết quả khóa luận & Bảng điểm
    Route::get('/ket-qua', [\App\Http\Controllers\TruongKhoa\KetQuaController::class, 'index'])->name('truongkhoa.ketqua.index');
    Route::get('/ket-qua/export', [\App\Http\Controllers\TruongKhoa\KetQuaController::class, 'exportExcel'])->name('truongkhoa.ketqua.export');

    // Thống kê Đề tài Khóa luận (Chuyển tab trong Duyệt đề tài)
    Route::get('/thongke/detai', fn() => redirect()->route('truongkhoa.duyet_detai.index', ['view' => 'thongke']))->name('truongkhoa.thongke.detai');
    Route::get('/thongke/detai/export', [\App\Http\Controllers\ThongKeDeTaiController::class, 'exportExcel'])->name('truongkhoa.thongke.detai.export');

    // Đổi mật khẩu alias
    Route::get('/doi-mat-khau', fn() => redirect()->route('password.change'));
    Route::get('/change-password', fn() => redirect()->route('password.change'));
});

// ==========================================
// API ROUTES (AJAX Cascading Dropdowns)
// ==========================================
Route::get('/api/khoa/{maKhoa}/bomons', [\App\Http\Controllers\ThongKeDeTaiController::class, 'getBoMonsByKhoa'])->name('api.khoa.bomons');

// ==========================================
// TIME MACHINE ROUTES (Mô phỏng thời gian ảo phục vụ Test)
// ==========================================
Route::post('/dev/mock-time', [\App\Http\Controllers\TimeMachineController::class, 'setMockTime'])->name('dev.mock-time');
Route::post('/dev/reset-mock-time', [\App\Http\Controllers\TimeMachineController::class, 'resetMockTime'])->name('dev.reset-mock-time');



