<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // =========================================================================
        // 1. CƠ CẤU TỔ CHỨC & ĐÀO TẠO
        // =========================================================================

        // 1. Khoa
        Schema::create('Khoa', function (Blueprint $table) {
            $table->string('MaKhoa', 20)->primary();
            $table->string('TenKhoa', 200);
            $table->timestamps();
        });

        // 2. BoMon
        Schema::create('BoMon', function (Blueprint $table) {
            $table->string('MaBoMon', 20)->primary();
            $table->string('TenBoMon', 200);
            $table->string('MaKhoa', 20);
            $table->foreign('MaKhoa')->references('MaKhoa')->on('Khoa')->onDelete('cascade');
            $table->timestamps();
        });

        // 3. Nganh
        Schema::create('Nganh', function (Blueprint $table) {
            $table->string('MaNganh', 20)->primary();
            $table->string('TenNganh', 200);
            $table->string('MaKhoa', 20)->nullable();
            $table->foreign('MaKhoa')->references('MaKhoa')->on('Khoa')->onDelete('set null');
            $table->timestamps();
        });

        // 4. ChuyenNganh
        Schema::create('ChuyenNganh', function (Blueprint $table) {
            $table->string('MaChuyenNganh', 20)->primary();
            $table->string('TenChuyenNganh', 200);
            $table->string('TrangThai', 50)->nullable()->default('Đang hoạt động');
            $table->string('MaNganh', 20)->nullable();
            $table->foreign('MaNganh')->references('MaNganh')->on('Nganh')->onDelete('set null');
            $table->timestamps();
        });

        // 5. Lop
        Schema::create('Lop', function (Blueprint $table) {
            $table->string('MaLop', 20)->primary();
            $table->string('TenLop', 100);
            $table->string('KhoaHoc', 50)->nullable();
            $table->string('MaNganh', 20)->nullable();
            $table->string('MaKhoa', 20)->nullable();
            $table->foreign('MaNganh')->references('MaNganh')->on('Nganh')->onDelete('set null');
            $table->foreign('MaKhoa')->references('MaKhoa')->on('Khoa')->onDelete('set null');
            $table->timestamps();
        });

        // =========================================================================
        // 2. NGƯỜI DÙNG & PHÂN QUYỀN
        // =========================================================================

        // 6. VaiTro
        Schema::create('VaiTro', function (Blueprint $table) {
            $table->string('MaVaiTro', 20)->primary();
            $table->string('TenVaiTro', 50);
            $table->timestamps();
        });

        // 7. TaiKhoan (Chuẩn Tiếng Việt 100% theo quy tắc BR01, BR02)
        Schema::create('TaiKhoan', function (Blueprint $table) {
            $table->string('MaTK', 50)->primary();
            $table->string('TenDangNhap', 50)->unique();
            $table->string('MatKhau', 255);
            $table->string('MaVaiTro', 20);
            $table->boolean('TrangThai')->default(true); // BR01: 1 = Hoạt động, 0 = Khóa
            $table->integer('SoLanDangNhapSai')->default(0); // BR01: khóa sau 5 lần sai
            $table->boolean('BatBuocDoiMatKhau')->default(true); // BR02: Bắt buộc đổi mật khẩu
            $table->string('TrangThaiMatKhau', 20)->default('INITIAL'); // INITIAL, ACTIVE, EXPIRED
            $table->dateTime('LanDangNhapDau')->nullable();
            $table->dateTime('NgayDoiMatKhau')->nullable();
            $table->dateTime('LanDangNhapCuoi')->nullable();
            $table->dateTime('NgayKhoa')->nullable(); // BR01
            $table->rememberToken();
            $table->foreign('MaVaiTro')->references('MaVaiTro')->on('VaiTro')->onDelete('restrict');
            $table->timestamps();
        });

        // 8. GiaoVu
        Schema::create('GiaoVu', function (Blueprint $table) {
            $table->string('MaGVu', 20)->primary();
            $table->string('MaTK', 50)->nullable();
            $table->string('HoTen', 100);
            $table->string('Email', 100)->nullable();
            $table->string('SoDienThoai', 20)->nullable();
            $table->string('ChucVu', 100)->nullable();
            $table->string('MaKhoa', 20)->nullable();
            $table->foreign('MaTK')->references('MaTK')->on('TaiKhoan')->onDelete('set null');
            $table->foreign('MaKhoa')->references('MaKhoa')->on('Khoa')->onDelete('set null');
            $table->timestamps();
        });

        // 9. GiangVien
        Schema::create('GiangVien', function (Blueprint $table) {
            $table->string('MaGV', 20)->primary();
            $table->string('MaTK', 50)->nullable();
            $table->string('HoTen', 100);
            $table->date('NgaySinh')->nullable();
            $table->string('GioiTinh', 10)->nullable();
            $table->string('Email', 100)->nullable();
            $table->string('SoDienThoai', 20)->nullable();
            $table->string('HocHam', 50)->nullable();
            $table->string('HocVi', 50)->nullable();
            $table->string('TrangThai', 50)->default('Đang công tác');
            $table->string('MaBoMon', 20)->nullable();
            $table->foreign('MaTK')->references('MaTK')->on('TaiKhoan')->onDelete('set null');
            $table->foreign('MaBoMon')->references('MaBoMon')->on('BoMon')->onDelete('set null');
            $table->timestamps();
        });

        // 10. SinhVien
        Schema::create('SinhVien', function (Blueprint $table) {
            $table->string('MaSV', 20)->primary();
            $table->string('MaTK', 50)->nullable();
            $table->string('HoTen', 100);
            $table->date('NgaySinh')->nullable();
            $table->string('GioiTinh', 10)->nullable();
            $table->string('Email', 100)->nullable();
            $table->string('SoDienThoai', 20)->nullable();
            $table->date('NgayNhapHoc')->nullable();
            $table->string('KhoaHoc', 50)->nullable();
            $table->integer('SoTinChiTichLuy')->default(0);
            $table->decimal('DiemTichLuy', 4, 2)->default(0.00);
            $table->string('TrangThai', 50)->default('Đang học');
            $table->string('MaKhoa', 20)->nullable();
            $table->string('MaNganh', 20)->nullable();
            $table->string('MaLop', 20)->nullable();
            $table->foreign('MaTK')->references('MaTK')->on('TaiKhoan')->onDelete('set null');
            $table->foreign('MaKhoa')->references('MaKhoa')->on('Khoa')->onDelete('set null');
            $table->foreign('MaNganh')->references('MaNganh')->on('Nganh')->onDelete('set null');
            $table->foreign('MaLop')->references('MaLop')->on('Lop')->onDelete('set null');
            $table->timestamps();
        });

        // =========================================================================
        // 3. HỌC KỲ, KẾ HOẠCH & QUY ĐỊNH
        // =========================================================================

        // 11. HocKy
        Schema::create('HocKy', function (Blueprint $table) {
            $table->string('MaHocKy', 20)->primary();
            $table->string('TenHocKy', 100);
            $table->string('NamHoc', 50);
            $table->date('NgayDiHoc')->nullable();
            $table->date('NgayKetThuc')->nullable();
            $table->string('TrangThai', 50)->default('Đang diễn ra');
            $table->timestamps();
        });

        // 12. DanhSachSVDuDieuKien (BR03)
        Schema::create('DanhSachSVDuDieuKien', function (Blueprint $table) {
            $table->string('MaDSDK', 20)->primary();
            $table->date('NgayXetDuyet')->nullable();
            $table->string('TrangThai', 50)->default('Đủ điều kiện');
            $table->string('GhiChu', 255)->nullable();
            $table->string('DieuKien', 255)->nullable();
            $table->string('MaSV', 20);
            $table->string('MaHocKy', 20);
            $table->foreign('MaSV')->references('MaSV')->on('SinhVien')->onDelete('cascade');
            $table->foreign('MaHocKy')->references('MaHocKy')->on('HocKy')->onDelete('cascade');
            $table->unique(['MaSV', 'MaHocKy']);
            $table->timestamps();
        });

        // 13. ChiTieuHuongDan (BR06)
        Schema::create('ChiTieuHuongDan', function (Blueprint $table) {
            $table->string('MaChiTieu', 20)->primary();
            $table->integer('SoNhomToiDa')->default(5);
            $table->date('NgayPhanBo')->nullable();
            $table->string('MaHocKy', 20);
            $table->string('MaGV', 20);
            $table->foreign('MaHocKy')->references('MaHocKy')->on('HocKy')->onDelete('cascade');
            $table->foreign('MaGV')->references('MaGV')->on('GiangVien')->onDelete('cascade');
            $table->unique(['MaGV', 'MaHocKy']);
            $table->timestamps();
        });

        // 14. KeHoachKhoaLuan
        Schema::create('KeHoachKhoaLuan', function (Blueprint $table) {
            $table->string('MakeHoach', 20)->primary();
            $table->string('TenKeHoach', 200);
            $table->longText('NoiDung')->nullable();
            $table->string('TrangThai', 50)->default('Dự thảo');
            $table->date('NgayTao')->nullable();
            $table->string('MaHocKy', 20);
            $table->string('MaGVu', 20)->nullable();
            $table->foreign('MaHocKy')->references('MaHocKy')->on('HocKy')->onDelete('cascade');
            $table->foreign('MaGVu')->references('MaGVu')->on('GiaoVu')->onDelete('set null');
            $table->timestamps();
        });

        // 15. MocThoiGianKhoaLuan
        Schema::create('MocThoiGianKhoaLuan', function (Blueprint $table) {
            $table->string('MaMoc', 20)->primary();
            $table->string('TenMoc', 200);
            $table->date('NgayBatDau');
            $table->date('NgayKetThuc');
            $table->longText('MoTa')->nullable();
            $table->string('MakeHoach', 20);
            $table->foreign('MakeHoach')->references('MakeHoach')->on('KeHoachKhoaLuan')->onDelete('cascade');
            $table->timestamps();
        });

        // 16. QuyDinhKhoaLuan
        Schema::create('QuyDinhKhoaLuan', function (Blueprint $table) {
            $table->string('MaQuyDinh', 20)->primary();
            $table->string('TenQuyDinh', 200);
            $table->string('GiaTri', 255)->nullable();
            $table->longText('MoTa')->nullable();
            $table->string('MakeHoach', 20);
            $table->foreign('MakeHoach')->references('MakeHoach')->on('KeHoachKhoaLuan')->onDelete('cascade');
            $table->timestamps();
        });

        // 17. BieuMau
        Schema::create('BieuMau', function (Blueprint $table) {
            $table->string('MaBieuMau', 20)->primary();
            $table->string('TenBieuMau', 200);
            $table->string('DuongDanFile', 500);
            $table->string('MakeHoach', 20);
            $table->foreign('MakeHoach')->references('MakeHoach')->on('KeHoachKhoaLuan')->onDelete('cascade');
            $table->timestamps();
        });

        // 18. ThongBao
        Schema::create('ThongBao', function (Blueprint $table) {
            $table->string('MaThongBao', 20)->primary();
            $table->string('TieuDe', 200);
            $table->longText('NoiDung');
            $table->string('LoaiThongBao', 50)->nullable();
            $table->string('DoiTuongNhan', 100)->nullable();
            $table->date('NgayTao')->nullable();
            $table->string('TrangThai', 50)->default('Đã phát hành');
            $table->string('MaGVu', 20)->nullable();
            $table->foreign('MaGVu')->references('MaGVu')->on('GiaoVu')->onDelete('set null');
            $table->timestamps();
        });

        // =========================================================================
        // 4. ĐỀ TÀI & ĐĂNG KÝ NHÓM
        // =========================================================================

        // 19. DeTai
        Schema::create('DeTai', function (Blueprint $table) {
            $table->string('MaDeTai', 20)->primary();
            $table->string('TenDeTai', 300);
            $table->longText('MoTa')->nullable();
            $table->longText('YeuCau')->nullable();
            $table->string('TrangThai', 50)->default('Chờ duyệt');
            $table->date('NgayDeXuat')->nullable();
            $table->string('MaGV', 20);
            $table->string('MaHocKy', 20);
            $table->foreign('MaGV')->references('MaGV')->on('GiangVien')->onDelete('cascade');
            $table->foreign('MaHocKy')->references('MaHocKy')->on('HocKy')->onDelete('cascade');
            $table->timestamps();
        });

        // 20. ChiTietDuyetDeTai
        Schema::create('ChiTietDuyetDeTai', function (Blueprint $table) {
            $table->string('MaDuyet', 20)->primary();
            $table->date('NgayDuyet')->nullable();
            $table->string('TrangThai', 50);
            $table->longText('LyDo')->nullable();
            $table->string('MaGV', 20);
            $table->string('MaDeTai', 20);
            $table->foreign('MaGV')->references('MaGV')->on('GiangVien')->onDelete('cascade');
            $table->foreign('MaDeTai')->references('MaDeTai')->on('DeTai')->onDelete('cascade');
            $table->timestamps();
        });

        // 21. PhanCongPhanBien (BR07: GVHD không được làm GVPB)
        Schema::create('PhanCongPhanBien', function (Blueprint $table) {
            $table->string('MaPhanCong', 20)->primary();
            $table->string('VaiTro', 50)->default('Phản biện');
            $table->date('NgayPhanCong')->nullable();
            $table->string('TrangThai', 50)->default('Đã phân công');
            $table->string('MaGV', 20);
            $table->string('MaDeTai', 20);
            $table->foreign('MaGV')->references('MaGV')->on('GiangVien')->onDelete('cascade');
            $table->foreign('MaDeTai')->references('MaDeTai')->on('DeTai')->onDelete('cascade');
            $table->timestamps();
        });

        // 22. Nhom
        Schema::create('Nhom', function (Blueprint $table) {
            $table->string('MaNhom', 20)->primary();
            $table->string('TenNhom', 100);
            $table->string('TrangThai', 50)->default('Đang hoạt động');
            $table->date('NgayTao')->nullable();
            $table->timestamps();
        });

        // 23. ThanhVienNhom (BR05: nhóm tối đa 3 SV)
        Schema::create('ThanhVienNhom', function (Blueprint $table) {
            $table->string('MaNhom', 20);
            $table->string('MaSV', 20);
            $table->string('VaiTro', 50)->default('Thành viên'); // 'Nhóm trưởng' hoặc 'Thành viên'
            $table->string('TrangThai', 50)->default('da_tham_gia'); // 'da_tham_gia', 'xin_gia_nhap', 'cho_xac_nhan'
            $table->date('NgayThamGia')->nullable();
            $table->primary(['MaNhom', 'MaSV']);
            $table->foreign('MaNhom')->references('MaNhom')->on('Nhom')->onDelete('cascade');
            $table->foreign('MaSV')->references('MaSV')->on('SinhVien')->onDelete('cascade');
            $table->timestamps();
        });

        // 24. PhieuDangKy (Thay thế DangKyDeTai cũ)
        Schema::create('PhieuDangKy', function (Blueprint $table) {
            $table->string('MaDangKy', 20)->primary();
            $table->date('NgayDangKy')->nullable();
            $table->string('TrangThai', 50)->default('Chờ duyệt');
            $table->date('NgayDuyet')->nullable();
            $table->string('LyDoTuChoi', 255)->nullable();
            $table->string('MaNhom', 20);
            $table->string('MaDeTai', 20);
            $table->foreign('MaNhom')->references('MaNhom')->on('Nhom')->onDelete('cascade');
            $table->foreign('MaDeTai')->references('MaDeTai')->on('DeTai')->onDelete('cascade');
            $table->timestamps();
        });

        // 25. LichGapHuongDan
        Schema::create('LichGapHuongDan', function (Blueprint $table) {
            $table->string('MaLichGap', 20)->primary();
            $table->dateTime('ThoiGianBatDau');
            $table->dateTime('ThoiGianKetThuc');
            $table->string('DiaDiem', 200)->nullable();
            $table->longText('NoiDung')->nullable();
            $table->string('TrangThai', 50)->default('Đã lên lịch');
            $table->date('NgayTao')->nullable();
            $table->string('MaNhom', 20);
            $table->string('MaGV', 20);
            $table->foreign('MaNhom')->references('MaNhom')->on('Nhom')->onDelete('cascade');
            $table->foreign('MaGV')->references('MaGV')->on('GiangVien')->onDelete('cascade');
            $table->timestamps();
        });

        // =========================================================================
        // 5. TIẾN ĐỘ & BÁO CÁO (TÍCH HỢP AI)
        // =========================================================================

        // 26. BaoCaoTienDo
        Schema::create('BaoCaoTienDo', function (Blueprint $table) {
            $table->string('MaBaoCao', 20)->primary();
            $table->integer('LanBaoCao');
            $table->string('TieuDe', 200);
            $table->longText('NoiDungBaoCao')->nullable();
            $table->date('NgayNop')->nullable();
            $table->string('TrangThai', 50)->default('Chờ nhận xét');
            $table->string('TenFile', 255)->nullable();
            $table->string('DuongDanFile', 500)->nullable();
            $table->longText('NhanXet')->nullable();
            $table->string('MaMoc', 20)->nullable();
            $table->string('MaDeTai', 20);
            $table->foreign('MaMoc')->references('MaMoc')->on('MocThoiGianKhoaLuan')->onDelete('set null');
            $table->foreign('MaDeTai')->references('MaDeTai')->on('DeTai')->onDelete('cascade');
            $table->timestamps();
        });

        // 27. TomTatBaoCao (AI/LLM)
        Schema::create('TomTatBaoCao', function (Blueprint $table) {
            $table->string('MaTomTat', 20)->primary();
            $table->longText('CongViecDaHoanThanh')->nullable();
            $table->longText('KhoKhan')->nullable();
            $table->longText('KeHoachTuanToi')->nullable();
            $table->longText('NoiDungAI')->nullable();
            $table->string('TrangThai', 50)->default('Đã tạo');
            $table->date('NgayTomTat')->nullable();
            $table->string('MaBaoCao', 20)->unique();
            $table->foreign('MaBaoCao')->references('MaBaoCao')->on('BaoCaoTienDo')->onDelete('cascade');
            $table->timestamps();
        });

        // 28. TaiLieuNop (BR12: Quản lý phiên bản tài liệu)
        Schema::create('TaiLieuNop', function (Blueprint $table) {
            $table->string('MaTaiLieu', 20)->primary();
            $table->string('TenTaiLieu', 255);
            $table->string('DuongDan', 500);
            $table->string('LoaiTaiLieu', 50)->nullable();
            $table->integer('LanNop')->default(1);
            $table->date('NgayNop')->nullable();
            $table->string('GhiChu', 255)->nullable();
            $table->string('MaDeTai', 20);
            $table->foreign('MaDeTai')->references('MaDeTai')->on('DeTai')->onDelete('cascade');
            $table->timestamps();
        });

        // =========================================================================
        // 6. HỘI ĐỒNG, HỒ SƠ BẢO VỆ & KẾT QUẢ
        // =========================================================================

        // 29. HoiDong (BR08, BR09)
        Schema::create('HoiDong', function (Blueprint $table) {
            $table->string('MaHoiDong', 20)->primary();
            $table->string('TenHoiDong', 200);
            $table->dateTime('ThoiGianBatDau')->nullable();
            $table->dateTime('ThoiGianKetThuc')->nullable();
            $table->string('DiaDiem', 200)->nullable();
            $table->string('TrangThai', 50)->default('Chưa diễn ra');
            $table->string('GhiChu', 255)->nullable();
            $table->date('NgayBaoVe')->nullable();
            $table->string('MaDeTai', 20)->nullable();
            $table->string('MaGV', 20)->nullable();
            $table->string('MaHocKy', 20)->nullable();
            $table->foreign('MaDeTai')->references('MaDeTai')->on('DeTai')->onDelete('set null');
            $table->foreign('MaGV')->references('MaGV')->on('GiangVien')->onDelete('set null');
            $table->foreign('MaHocKy')->references('MaHocKy')->on('HocKy')->onDelete('set null');
            $table->timestamps();
        });

        // 30. ThanhVienHoiDong (BR08: tối thiểu 3 thành viên)
        Schema::create('ThanhVienHoiDong', function (Blueprint $table) {
            $table->string('MaHoiDong', 20);
            $table->string('MaGV', 20);
            $table->string('VaiTro', 50); // Chủ tịch, Thư ký, Ủy viên
            $table->primary(['MaHoiDong', 'MaGV']);
            $table->foreign('MaHoiDong')->references('MaHoiDong')->on('HoiDong')->onDelete('cascade');
            $table->foreign('MaGV')->references('MaGV')->on('GiangVien')->onDelete('cascade');
            $table->timestamps();
        });

        // 31. HoSoBaoVe (BR10)
        Schema::create('HoSoBaoVe', function (Blueprint $table) {
            $table->string('MaHoSo', 20)->primary();
            $table->date('NgayLap')->nullable();
            $table->date('NgayNop')->nullable();
            $table->date('NgayXacNhan')->nullable();
            $table->string('XacNhanGVHD', 50)->default('Chưa duyệt'); // 'Đã duyệt' theo BR10
            $table->dateTime('ThoiGianBaoVe')->nullable();
            $table->string('PhongBaoVe', 100)->nullable();
            $table->string('TrangThai', 50)->default('Chờ xác nhận');
            $table->string('GhiChu', 255)->nullable();
            $table->string('MaGVu', 20)->nullable();
            $table->string('MaHoiDong', 20)->nullable();
            $table->string('MaNhom', 20)->nullable();
            $table->string('MaDeTai', 20)->nullable();
            $table->foreign('MaGVu')->references('MaGVu')->on('GiaoVu')->onDelete('set null');
            $table->foreign('MaHoiDong')->references('MaHoiDong')->on('HoiDong')->onDelete('set null');
            $table->foreign('MaNhom')->references('MaNhom')->on('Nhom')->onDelete('set null');
            $table->foreign('MaDeTai')->references('MaDeTai')->on('DeTai')->onDelete('set null');
            $table->timestamps();
        });

        // 32. TepHoSoBaoVe
        Schema::create('TepHoSoBaoVe', function (Blueprint $table) {
            $table->string('MaTep', 20)->primary();
            $table->string('TenTep', 255);
            $table->string('LoaiTep', 50)->nullable();
            $table->string('DuongDanFile', 500);
            $table->string('PhienBan', 20)->nullable();
            $table->date('NgayNop')->nullable();
            $table->string('TrangThai', 50)->nullable();
            $table->string('GhiChu', 255)->nullable();
            $table->string('MaHoSo', 20);
            $table->foreign('MaHoSo')->references('MaHoSo')->on('HoSoBaoVe')->onDelete('cascade');
            $table->timestamps();
        });

        // 33. PhieuChamDiem (BR11)
        Schema::create('PhieuChamDiem', function (Blueprint $table) {
            $table->string('MaPhieuChamDiem', 20)->primary();
            $table->decimal('Diem', 4, 2);
            $table->date('NgayCham')->nullable();
            $table->longText('NhanXet')->nullable();
            $table->string('MaDeTai', 20);
            $table->string('MaHoiDong', 20)->nullable();
            $table->string('MaGV', 20);
            $table->string('MaHoSo', 20)->nullable();
            $table->string('MaHocKy', 20)->nullable();
            $table->foreign('MaDeTai')->references('MaDeTai')->on('DeTai')->onDelete('cascade');
            $table->foreign('MaHoiDong')->references('MaHoiDong')->on('HoiDong')->onDelete('set null');
            $table->foreign('MaGV')->references('MaGV')->on('GiangVien')->onDelete('cascade');
            $table->foreign('MaHoSo')->references('MaHoSo')->on('HoSoBaoVe')->onDelete('set null');
            $table->foreign('MaHocKy')->references('MaHocKy')->on('HocKy')->onDelete('set null');
            $table->timestamps();
        });

        // 34. KetQuaSinhVien
        Schema::create('KetQuaSinhVien', function (Blueprint $table) {
            $table->string('MaKetQua', 20)->primary();
            $table->decimal('DiemPhanBien', 4, 2)->nullable();
            $table->decimal('DiemHoiDongTB', 4, 2)->nullable();
            $table->decimal('DiemTongKet', 4, 2)->nullable();
            $table->string('KetQua', 50)->nullable();
            $table->longText('NhanXetChung')->nullable();
            $table->date('NgayCham')->nullable();
            $table->string('MaSV', 20);
            $table->string('MaHoSo', 20)->nullable();
            $table->string('MaHocKy', 20)->nullable();
            $table->foreign('MaSV')->references('MaSV')->on('SinhVien')->onDelete('cascade');
            $table->foreign('MaHoSo')->references('MaHoSo')->on('HoSoBaoVe')->onDelete('set null');
            $table->foreign('MaHocKy')->references('MaHocKy')->on('HocKy')->onDelete('set null');
            $table->timestamps();
        });

        // 35. sessions (Laravel standard)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('KetQuaSinhVien');
        Schema::dropIfExists('PhieuChamDiem');
        Schema::dropIfExists('TepHoSoBaoVe');
        Schema::dropIfExists('HoSoBaoVe');
        Schema::dropIfExists('ThanhVienHoiDong');
        Schema::dropIfExists('HoiDong');
        Schema::dropIfExists('TaiLieuNop');
        Schema::dropIfExists('TomTatBaoCao');
        Schema::dropIfExists('BaoCaoTienDo');
        Schema::dropIfExists('LichGapHuongDan');
        Schema::dropIfExists('PhieuDangKy');
        Schema::dropIfExists('ThanhVienNhom');
        Schema::dropIfExists('Nhom');
        Schema::dropIfExists('PhanCongPhanBien');
        Schema::dropIfExists('ChiTietDuyetDeTai');
        Schema::dropIfExists('DeTai');
        Schema::dropIfExists('ThongBao');
        Schema::dropIfExists('BieuMau');
        Schema::dropIfExists('QuyDinhKhoaLuan');
        Schema::dropIfExists('MocThoiGianKhoaLuan');
        Schema::dropIfExists('KeHoachKhoaLuan');
        Schema::dropIfExists('ChiTieuHuongDan');
        Schema::dropIfExists('DanhSachSVDuDieuKien');
        Schema::dropIfExists('HocKy');
        Schema::dropIfExists('SinhVien');
        Schema::dropIfExists('GiangVien');
        Schema::dropIfExists('GiaoVu');
        Schema::dropIfExists('TaiKhoan');
        Schema::dropIfExists('VaiTro');
        Schema::dropIfExists('Lop');
        Schema::dropIfExists('ChuyenNganh');
        Schema::dropIfExists('Nganh');
        Schema::dropIfExists('BoMon');
        Schema::dropIfExists('Khoa');
    }
};
