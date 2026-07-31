<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\ImportTemplateController;

Route::get('/', function () {
    if (Auth::check()) {
        /** @var \App\Models\TaiKhoan $user */
        $user = Auth::user();
        $user->loadMissing('vaiTro');
        $role = $user->vaiTro->TenVaiTro ?? '';
        
        if ($role === 'Admin') return redirect()->route('admin.dashboard');
        if ($role === 'Giảng viên') return redirect()->route('giangvien.dashboard');
        if ($role === 'Sinh viên') return redirect()->route('sinhvien.dashboard');
    }
    return redirect()->route('login');
});

Auth::routes();

// Quên mật khẩu gửi Admin duyệt (Custom Flow)
Route::get('/password/reset-request', [\App\Http\Controllers\Auth\QuenMatKhauController::class, 'showForm'])->name('password.request');
Route::post('/password/reset-request', [\App\Http\Controllers\Auth\QuenMatKhauController::class, 'sendRequest'])->name('password.send_request');

// ==========================================
// API ENDPOINTS (RESTful JSON)
// Tổng cộng: 16 endpoints chuẩn REST
// ==========================================
Route::prefix('api')->group(function () {
    // Đề tài - Full CRUD
    Route::get('/detais', [\App\Http\Controllers\ApiController::class, 'getDeTais']);
    Route::get('/detais/{id}', [\App\Http\Controllers\ApiController::class, 'getDeTaiDetail']);
    Route::post('/detais', [\App\Http\Controllers\ApiController::class, 'storeDeTai']);
    Route::put('/detais/{id}', [\App\Http\Controllers\ApiController::class, 'updateDeTai']);
    Route::delete('/detais/{id}', [\App\Http\Controllers\ApiController::class, 'destroyDeTai']);

    // Nhóm đồ án
    Route::get('/nhoms', [\App\Http\Controllers\ApiController::class, 'getNhoms']);
    Route::get('/nhoms/{id}', [\App\Http\Controllers\ApiController::class, 'getNhomDetail']);

    // Danh mục (Read-Only)
    Route::get('/sinhviens', [\App\Http\Controllers\ApiController::class, 'getSinhViens']);
    Route::get('/giangviens', [\App\Http\Controllers\ApiController::class, 'getGiangViens']);
    Route::get('/monhocs', [\App\Http\Controllers\ApiController::class, 'getMonHocs']);
    Route::get('/lops', [\App\Http\Controllers\ApiController::class, 'getLops']);
    Route::get('/hockys', [\App\Http\Controllers\ApiController::class, 'getHocKys']);
    Route::get('/bomons', [\App\Http\Controllers\ApiController::class, 'getBoMons']);
    Route::get('/nganhs', [\App\Http\Controllers\ApiController::class, 'getNganhs']);
    Route::get('/thongbaos', [\App\Http\Controllers\ApiController::class, 'getThongBaos']);

    // Thống kê tổng quan
    Route::get('/thongke', [\App\Http\Controllers\ApiController::class, 'getThongKe']);
});

// Download sample Excel templates for import
Route::get('import/template/{type}', [ImportTemplateController::class, 'downloadTemplate'])->name('admin.import.template');
Route::get('import/error-log/{filename}', [ImportTemplateController::class, 'downloadErrorLog'])->name('import.error.download');

// Profile Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'showProfile'])->name('profile.show');
    Route::get('/password/change', [\App\Http\Controllers\ProfileController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/password/change', [\App\Http\Controllers\ProfileController::class, 'changePassword'])->name('password.change.post');
});

// Admin Routes
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/taikhoan/{id}/toggle-lock', [\App\Http\Controllers\Admin\DashboardController::class, 'toggleLockAccount'])->name('admin.taikhoan.toggleLock');
    
    Route::post('bomon/import', [\App\Http\Controllers\BoMonController::class, 'importExcel'])->name('admin.bomon.import');
    Route::resource('bomon', App\Http\Controllers\BoMonController::class);

    Route::post('nganh/import', [\App\Http\Controllers\NganhController::class, 'importExcel'])->name('admin.nganh.import');
    Route::resource('nganh', \App\Http\Controllers\NganhController::class);
    
    Route::post('lop/import', [\App\Http\Controllers\LopController::class, 'importExcel'])->name('admin.lop.import');
    Route::resource('lop', \App\Http\Controllers\LopController::class);

    Route::post('monhoc/import', [\App\Http\Controllers\MonHocController::class, 'importExcel'])->name('admin.monhoc.import');
    Route::resource('monhoc', \App\Http\Controllers\MonHocController::class);
    
    Route::post('giangvien/import', [\App\Http\Controllers\GiangVienController::class, 'importExcel'])->name('admin.giangvien.import');
    Route::resource('giangvien', \App\Http\Controllers\GiangVienController::class);

    Route::post('sinhvien/import', [\App\Http\Controllers\SinhVienController::class, 'importExcel'])->name('admin.sinhvien.import');
    Route::resource('sinhvien', \App\Http\Controllers\SinhVienController::class);

    Route::post('hocky/import', [\App\Http\Controllers\HocKyController::class, 'importExcel'])->name('admin.hocky.import');
    Route::resource('hocky', \App\Http\Controllers\HocKyController::class);

    Route::post('phancong/import', [\App\Http\Controllers\PhanCongController::class, 'importExcel'])->name('admin.phancong.import');
    Route::delete('phancong/lhp/{id}', [\App\Http\Controllers\PhanCongController::class, 'unassignLhp'])->name('admin.phancong.unassign_lhp');
    Route::resource('phancong', \App\Http\Controllers\PhanCongController::class)->only(['index', 'store', 'destroy']);
    

    Route::resource('thongbao', \App\Http\Controllers\ThongBaoController::class)->only(['index', 'store', 'destroy']);
    Route::get('sanpham', [\App\Http\Controllers\Admin\SanPhamController::class, 'index'])->name('admin.sanpham.index');
    Route::get('ketqua', [\App\Http\Controllers\Admin\KetQuaController::class, 'index'])->name('admin.ketqua.index');
    
    // Duyệt Quên Mật Khẩu
    Route::get('yeu-cau-doi-mat-khau', [\App\Http\Controllers\Admin\YeuCauDoiMatKhauController::class, 'index'])->name('admin.yeucau.index');
    Route::post('yeu-cau-doi-mat-khau/{id}/duyet', [\App\Http\Controllers\Admin\YeuCauDoiMatKhauController::class, 'approve'])->name('admin.yeucau.approve');
    Route::post('yeu-cau-doi-mat-khau/{id}/tu-choi', [\App\Http\Controllers\Admin\YeuCauDoiMatKhauController::class, 'reject'])->name('admin.yeucau.reject');

    // Quản Lý Lớp Học Phần
    Route::post('lop-hoc-phan/import', [\App\Http\Controllers\Admin\LopHocPhanController::class, 'import'])->name('admin.lophocphan.import');
    Route::post('lop-hoc-phan/{id}/import-students', [\App\Http\Controllers\Admin\LopHocPhanController::class, 'importStudents'])->name('admin.lophocphan.importStudents');
    Route::resource('lop-hoc-phan', \App\Http\Controllers\Admin\LopHocPhanController::class)->names('admin.lophocphan');
    Route::post('lop-hoc-phan/{id}/add-sv', [\App\Http\Controllers\Admin\LopHocPhanController::class, 'addStudent'])->name('admin.lophocphan.addStudent');
    Route::delete('lop-hoc-phan/{id}/remove-sv/{maSV}', [\App\Http\Controllers\Admin\LopHocPhanController::class, 'removeStudent'])->name('admin.lophocphan.removeStudent');
});

// Giảng Viên Routes
Route::middleware(['auth', 'role:Giảng viên'])->prefix('giangvien')->group(function () {
    Route::get('/', function() {
        return redirect()->route('giangvien.detai.index');
    })->name('giangvien.dashboard');
    
    Route::post('detai/import', [\App\Http\Controllers\GiangVien\DeTaiController::class, 'importExcel'])->name('giangvien.detai.import');
    Route::post('detai/{id}/upload-tai-lieu', [\App\Http\Controllers\GiangVien\DeTaiController::class, 'uploadTaiLieu'])->name('giangvien.detai.uploadTaiLieu');
    Route::get('detai/{id}/download-tai-lieu', [\App\Http\Controllers\GiangVien\DeTaiController::class, 'downloadTaiLieu'])->name('giangvien.detai.downloadTaiLieu');
    Route::delete('detai/{id}/delete-tai-lieu', [\App\Http\Controllers\GiangVien\DeTaiController::class, 'deleteTaiLieu'])->name('giangvien.detai.deleteTaiLieu');

    Route::resource('detai', App\Http\Controllers\GiangVien\DeTaiController::class)->names('giangvien.detai');
    Route::get('/lop', [\App\Http\Controllers\GiangVien\LopController::class, 'index'])->name('giangvien.lop.index');
    Route::get('/lop/{id}', [\App\Http\Controllers\GiangVien\LopController::class, 'show'])->name('giangvien.lop.show');
    Route::resource('duyet', App\Http\Controllers\GiangVien\DuyetDeTaiController::class)->names('giangvien.duyet')->only(['index', 'update']);
    Route::get('/baocao', [\App\Http\Controllers\GiangVien\DuyetBaoCaoController::class, 'index'])->name('giangvien.baocao.index');
    Route::post('/baocao/{maBaoCao}/nhanxet', [\App\Http\Controllers\GiangVien\DuyetBaoCaoController::class, 'storeNhanXet'])->name('giangvien.baocao.nhanxet');
    
    Route::get('/sanpham', [\App\Http\Controllers\GiangVien\SanPhamController::class, 'index'])->name('giangvien.sanpham.index');
    Route::get('/chamdiem', [\App\Http\Controllers\GiangVien\ChamDiemController::class, 'index'])->name('giangvien.chamdiem.index');
    Route::post('/chamdiem/{maNhom}', [\App\Http\Controllers\GiangVien\ChamDiemController::class, 'store'])->name('giangvien.chamdiem.store');
    
    Route::resource('thongbao', \App\Http\Controllers\ThongBaoController::class)->names('giangvien.thongbao')->only(['index', 'store', 'destroy']);
});

// Sinh Viên Routes
Route::middleware(['auth', 'role:Sinh viên'])->prefix('sinhvien')->group(function () {
    Route::get('/', function() {
        return redirect()->route('sinhvien.nhom.index');
    })->name('sinhvien.dashboard');
    
    Route::get('nhom', [App\Http\Controllers\SinhVien\NhomController::class, 'index'])->name('sinhvien.nhom.index');
    Route::post('nhom', [App\Http\Controllers\SinhVien\NhomController::class, 'store'])->name('sinhvien.nhom.store');
    Route::get('nhom/search-sv', [App\Http\Controllers\SinhVien\NhomController::class, 'searchSV'])->name('sinhvien.nhom.searchSV');
    Route::post('nhom/moi', [App\Http\Controllers\SinhVien\NhomController::class, 'moiThanhVien'])->name('sinhvien.nhom.moiThanhVien');
    Route::post('nhom/moi/{id}/xac-nhan', [App\Http\Controllers\SinhVien\NhomController::class, 'xacNhanLoiMoi'])->name('sinhvien.nhom.xacNhan');
    Route::post('nhom/moi/{id}/tu-choi', [App\Http\Controllers\SinhVien\NhomController::class, 'tuChoiLoiMoi'])->name('sinhvien.nhom.tuChoi');
    
    Route::resource('dangky', App\Http\Controllers\SinhVien\DangKyDeTaiController::class)->names('sinhvien.dangky')->only(['index', 'store', 'destroy']);

    Route::get('/baocao', [\App\Http\Controllers\SinhVien\BaoCaoController::class, 'index'])->name('sinhvien.baocao.index');
    Route::post('/baocao', [\App\Http\Controllers\SinhVien\BaoCaoController::class, 'store'])->name('sinhvien.baocao.store');
    
    Route::get('/sanpham', [\App\Http\Controllers\SinhVien\SanPhamController::class, 'index'])->name('sinhvien.sanpham.index');
    Route::post('/sanpham', [\App\Http\Controllers\SinhVien\SanPhamController::class, 'store'])->name('sinhvien.sanpham.store');
    Route::put('/sanpham/{id}', [\App\Http\Controllers\SinhVien\SanPhamController::class, 'update'])->name('sinhvien.sanpham.update');
    
    Route::get('/thongbao', [\App\Http\Controllers\SinhVien\ThongBaoController::class, 'index'])->name('sinhvien.thongbao.index');
    Route::post('/thongbao/{id}/read', [\App\Http\Controllers\SinhVien\ThongBaoController::class, 'markRead'])->name('sinhvien.thongbao.read');
    Route::post('/thongbao/read-all', [\App\Http\Controllers\SinhVien\ThongBaoController::class, 'markAllRead'])->name('sinhvien.thongbao.readAll');
});
