<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Bảng VaiTro
        Schema::create('vai_tros', function (Blueprint $table) {
            $table->string('MaVaiTro', 10)->primary();
            $table->string('TenVaiTro', 50)->unique();
            $table->timestamps();
        });

        // 2. Bảng TaiKhoan
        Schema::create('tai_khoans', function (Blueprint $table) {
            $table->string('MaTK', 10)->primary();
            $table->string('MaVaiTro', 10);
            $table->string('TenDangNhap', 50)->unique();
            $table->string('MatKhau', 255);
            $table->boolean('TrangThai')->default(true);
            $table->integer('SoLanDangNhapSai')->default(0);
            $table->boolean('BatBuocDoiMatKhau')->default(true);
            $table->timestamp('LanDangNhapCuoi')->nullable();
            $table->timestamp('NgayKhoa')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('MaVaiTro')->references('MaVaiTro')->on('vai_tros')->onUpdate('cascade')->onDelete('restrict');
        });

        // 3. Bảng Khoa
        Schema::create('khoas', function (Blueprint $table) {
            $table->string('MaKhoa', 10)->primary();
            $table->string('TenKhoa', 100)->unique();
            $table->timestamps();
        });

        // 4. Bảng BoMon
        Schema::create('bo_mons', function (Blueprint $table) {
            $table->string('MaBoMon', 10)->primary();
            $table->string('TenBoMon', 100);
            $table->string('MaKhoa', 10);
            $table->timestamps();

            $table->foreign('MaKhoa')->references('MaKhoa')->on('khoas')->onUpdate('cascade')->onDelete('restrict');
        });

        // 5. Bảng Nganh
        Schema::create('nganhs', function (Blueprint $table) {
            $table->string('MaNganh', 10)->primary();
            $table->string('TenNganh', 100);
            $table->string('MaKhoa', 10);
            $table->timestamps();

            $table->foreign('MaKhoa')->references('MaKhoa')->on('khoas')->onUpdate('cascade')->onDelete('restrict');
        });

        // 6. Bảng Lop
        Schema::create('lops', function (Blueprint $table) {
            $table->string('MaLop', 10)->primary();
            $table->string('TenLop', 100);
            $table->string('MaNganh', 10);
            $table->string('KhoaHoc', 20);
            $table->timestamps();

            $table->foreign('MaNganh')->references('MaNganh')->on('nganhs')->onUpdate('cascade')->onDelete('restrict');
        });

        // 7. Bảng HocKy
        Schema::create('hoc_kies', function (Blueprint $table) {
            $table->string('MaHocKy', 10)->primary();
            $table->string('TenHocKy', 50);
            $table->string('NamHoc', 20);
            $table->date('NgayBatDau');
            $table->date('NgayKetThuc');
            $table->boolean('TrangThai')->default(true);
            $table->timestamps();
        });

        // 8. Bảng GiaoVu
        Schema::create('giao_vus', function (Blueprint $table) {
            $table->string('MaGVu', 10)->primary();
            $table->string('MaTK', 10)->unique();
            $table->string('MaKhoa', 10);
            $table->string('HoTen', 100);
            $table->string('Email', 100)->unique()->nullable();
            $table->string('SoDienThoai', 15)->nullable();
            $table->string('ChucVu', 50)->nullable();
            $table->timestamps();

            $table->foreign('MaTK')->references('MaTK')->on('tai_khoans')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('MaKhoa')->references('MaKhoa')->on('khoas')->onUpdate('cascade')->onDelete('restrict');
        });

        // 9. Bảng GiangVien
        Schema::create('giang_viens', function (Blueprint $table) {
            $table->string('MaGV', 10)->primary();
            $table->string('MaTK', 10)->unique();
            $table->string('MaBoMon', 10);
            $table->string('MaSoCanBo', 20)->unique()->nullable();
            $table->string('HoTen', 100);
            $table->date('NgaySinh')->nullable();
            $table->boolean('GioiTinh')->nullable();
            $table->string('Email', 100)->unique()->nullable();
            $table->string('SoDienThoai', 15)->nullable();
            $table->string('HocHam', 50)->nullable();
            $table->string('HocVi', 50)->nullable();
            $table->string('ChuyenNganh', 100)->nullable();
            $table->boolean('TrangThai')->default(true);
            $table->timestamps();

            $table->foreign('MaTK')->references('MaTK')->on('tai_khoans')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('MaBoMon')->references('MaBoMon')->on('bo_mons')->onUpdate('cascade')->onDelete('restrict');
        });

        // 10. Bảng SinhVien
        Schema::create('sinh_viens', function (Blueprint $table) {
            $table->string('MaSV', 10)->primary();
            $table->string('MaTK', 10)->unique();
            $table->string('MaLop', 10);
            $table->string('MaSoSinhVien', 20)->unique()->nullable();
            $table->string('HoTen', 100);
            $table->date('NgaySinh')->nullable();
            $table->boolean('GioiTinh')->nullable();
            $table->string('Email', 100)->unique()->nullable();
            $table->string('SoDienThoai', 15)->nullable();
            $table->date('NgayNhapHoc')->nullable();
            $table->string('KhoaHoc', 20)->nullable();
            $table->integer('SoTinChiTichLuy')->default(0);
            $table->decimal('DiemTichLuy', 3, 2)->default(0.00);
            $table->string('TrangThai', 50)->default('Đang học');
            $table->timestamps();

            $table->foreign('MaTK')->references('MaTK')->on('tai_khoans')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('MaLop')->references('MaLop')->on('lops')->onUpdate('cascade')->onDelete('restrict');
        });

        // 11. Bảng DanhSachSVDuDieuKien
        Schema::create('danh_sach_sv_du_dieu_kiens', function (Blueprint $table) {
            $table->string('MaSV', 10);
            $table->string('MaHocKy', 10);
            $table->date('NgayXetDuyet')->nullable();
            $table->string('TrangThai', 50)->default('Đủ điều kiện');
            $table->string('GhiChu', 255)->nullable();
            $table->timestamps();

            $table->primary(['MaSV', 'MaHocKy']);
            $table->foreign('MaSV')->references('MaSV')->on('sinh_viens')->onDelete('cascade');
            $table->foreign('MaHocKy')->references('MaHocKy')->on('hoc_kies')->onDelete('cascade');
        });

        // 12. Bảng ChiTieuHuongDan
        Schema::create('chi_tieu_huong_dans', function (Blueprint $table) {
            $table->string('MaGV', 10);
            $table->string('MaHocKy', 10);
            $table->integer('SoNhomToiDa')->default(3);
            $table->date('NgayPhanBo')->nullable();
            $table->timestamps();

            $table->primary(['MaGV', 'MaHocKy']);
            $table->foreign('MaGV')->references('MaGV')->on('giang_viens')->onDelete('cascade');
            $table->foreign('MaHocKy')->references('MaHocKy')->on('hoc_kies')->onDelete('cascade');
        });

        // 13. Bảng KeHoachKhoaLuan
        Schema::create('ke_hoach_khoa_luans', function (Blueprint $table) {
            $table->string('MaKeHoach', 10)->primary();
            $table->string('MaHocKy', 10);
            $table->string('MaGVu', 10);
            $table->string('TenKeHoach', 200);
            $table->text('NoiDung')->nullable();
            $table->string('TrangThai', 50)->default('Nháp');
            $table->date('NgayTao')->nullable();
            $table->date('NgayCongBo')->nullable();
            $table->timestamps();

            $table->foreign('MaHocKy')->references('MaHocKy')->on('hoc_kies')->onDelete('restrict');
            $table->foreign('MaGVu')->references('MaGVu')->on('giao_vus')->onDelete('restrict');
        });

        // 14. Bảng MocThoiGianKhoaLuan
        Schema::create('moc_thoi_gian_khoa_luans', function (Blueprint $table) {
            $table->string('MaMoc', 10)->primary();
            $table->string('MaKeHoach', 10);
            $table->string('TenMoc', 100);
            $table->date('NgayBatDau');
            $table->date('NgayKetThuc');
            $table->string('MoTa', 255)->nullable();
            $table->timestamps();

            $table->foreign('MaKeHoach')->references('MaKeHoach')->on('ke_hoach_khoa_luans')->onDelete('cascade');
        });

        // 15. Bảng QuyDinhKhoaLuan
        Schema::create('quy_dinh_khoa_luans', function (Blueprint $table) {
            $table->string('MaQuyDinh', 10)->primary();
            $table->string('MaKeHoach', 10);
            $table->string('TenQuyDinh', 200);
            $table->string('GiaTri', 100);
            $table->text('MoTa')->nullable();
            $table->timestamps();

            $table->foreign('MaKeHoach')->references('MaKeHoach')->on('ke_hoach_khoa_luans')->onDelete('cascade');
        });

        // 16. Bảng BieuMau
        Schema::create('bieu_maus', function (Blueprint $table) {
            $table->string('MaBieuMau', 10)->primary();
            $table->string('MaKeHoach', 10);
            $table->string('TenBieuMau', 200);
            $table->string('DuongDanFile', 255);
            $table->timestamps();

            $table->foreign('MaKeHoach')->references('MaKeHoach')->on('ke_hoach_khoa_luans')->onDelete('cascade');
        });

        // 17. Bảng DeTai
        Schema::create('de_tais', function (Blueprint $table) {
            $table->string('MaDeTai', 10)->primary();
            $table->string('MaGV', 10);
            $table->string('TenDeTai', 200);
            $table->text('MoTa')->nullable();
            $table->text('YeuCau')->nullable();
            $table->string('LinhVuc', 100)->nullable();
            $table->integer('SoLuongSinhVienToiDa')->default(1);
            $table->string('MaHocKy', 10);
            $table->string('TrangThai', 50)->default('Chờ duyệt');
            $table->date('NgayDeXuat')->nullable();
            $table->date('NgayDuyet')->nullable();
            $table->string('LyDoTuChoi', 255)->nullable();
            $table->timestamps();

            $table->foreign('MaGV')->references('MaGV')->on('giang_viens')->onDelete('cascade');
            $table->foreign('MaHocKy')->references('MaHocKy')->on('hoc_kies')->onDelete('restrict');
        });

        // 18. Bảng Nhom
        Schema::create('nhoms', function (Blueprint $table) {
            $table->string('MaNhom', 10)->primary();
            $table->string('MaDeTai', 10)->nullable();
            $table->string('MaTruongNhom', 10);
            $table->string('TenNhom', 100)->nullable();
            $table->string('TrangThai', 50)->default('Đang tạo');
            $table->date('NgayTao')->nullable();
            $table->timestamps();

            $table->foreign('MaDeTai')->references('MaDeTai')->on('de_tais')->onDelete('set null');
            $table->foreign('MaTruongNhom')->references('MaSV')->on('sinh_viens')->onDelete('cascade');
        });

        // 19. Bảng ThanhVienNhom
        Schema::create('thanh_vien_nhoms', function (Blueprint $table) {
            $table->string('MaNhom', 10);
            $table->string('MaSV', 10);
            $table->string('VaiTro', 50)->default('Thành viên');
            $table->string('TrangThai', 50)->default('da_tham_gia');
            $table->date('NgayThamGia')->nullable();
            $table->timestamps();

            $table->primary(['MaNhom', 'MaSV']);
            $table->foreign('MaNhom')->references('MaNhom')->on('nhoms')->onDelete('cascade');
            $table->foreign('MaSV')->references('MaSV')->on('sinh_viens')->onDelete('cascade');
        });


        // 20. Bảng DangKyDeTai
        Schema::create('dang_ky_de_tais', function (Blueprint $table) {
            $table->string('MaDangKy', 10)->primary();
            $table->string('MaNhom', 10);
            $table->string('MaDeTai', 10);
            $table->string('MaGVHuongDan', 10);
            $table->date('NgayDangKy')->nullable();
            $table->string('TrangThai', 50)->default('Chờ duyệt');
            $table->date('NgayDuyet')->nullable();
            $table->string('GhiChu', 255)->nullable();
            $table->string('LyDoTuChoi', 255)->nullable();
            $table->timestamps();

            $table->foreign('MaNhom')->references('MaNhom')->on('nhoms')->onDelete('cascade');
            $table->foreign('MaDeTai')->references('MaDeTai')->on('de_tais')->onDelete('cascade');
            $table->foreign('MaGVHuongDan')->references('MaGV')->on('giang_viens')->onDelete('cascade');
        });

        // 21. Bảng BaoCaoTienDo
        Schema::create('bao_cao_tien_dos', function (Blueprint $table) {
            $table->string('MaBaoCao', 10)->primary();
            $table->string('MaNhom', 10);
            $table->integer('LanBaoCao');
            $table->string('TieuDe', 200);
            $table->text('NoiDungBaoCao')->nullable();
            $table->date('NgayNop')->nullable();
            $table->string('TenFile', 255)->nullable();
            $table->string('DuongDanFile', 500)->nullable();
            $table->string('TrangThai', 50)->default('Chờ duyệt');
            $table->timestamps();

            $table->foreign('MaNhom')->references('MaNhom')->on('nhoms')->onDelete('cascade');
        });

        // 22. Bảng TomTatBaoCao (AI / LLM Summary)
        Schema::create('tom_tat_bao_caos', function (Blueprint $table) {
            $table->string('MaTomTat', 10)->primary();
            $table->string('MaBaoCao', 10);
            $table->text('CongViecDaHoanThanh')->nullable();
            $table->text('KhoKhan')->nullable();
            $table->text('KeHoachTuanToi')->nullable();
            $table->text('NoiDungAI')->nullable();
            $table->decimal('DoTinCayAI', 5, 2)->nullable();
            $table->timestamp('NgayTomTat')->nullable();
            $table->string('TrangThai', 50)->default('Đã tạo');
            $table->timestamps();

            $table->foreign('MaBaoCao')->references('MaBaoCao')->on('bao_cao_tien_dos')->onDelete('cascade');
        });

        // 23. Bảng NhanXet
        Schema::create('nhan_xets', function (Blueprint $table) {
            $table->string('MaNhanXet', 10)->primary();
            $table->string('MaBaoCao', 10);
            $table->string('MaGV', 10);
            $table->text('NoiDung');
            $table->string('LoaiNhanXet', 50)->nullable();
            $table->timestamp('NgayNhanXet')->nullable();
            $table->string('TrangThai', 50)->default('Đã nhận xét');
            $table->timestamps();

            $table->foreign('MaBaoCao')->references('MaBaoCao')->on('bao_cao_tien_dos')->onDelete('cascade');
            $table->foreign('MaGV')->references('MaGV')->on('giang_viens')->onDelete('cascade');
        });

        // 24. Bảng TienDo
        Schema::create('tien_dos', function (Blueprint $table) {
            $table->string('MaTienDo', 10)->primary();
            $table->string('MaNhom', 10);
            $table->string('MaMoc', 10);
            $table->integer('TyLeHoanThanh')->default(0);
            $table->string('TrangThai', 50)->nullable();
            $table->timestamp('NgayCapNhat')->nullable();
            $table->text('GhiChu')->nullable();
            $table->timestamps();

            $table->foreign('MaNhom')->references('MaNhom')->on('nhoms')->onDelete('cascade');
            $table->foreign('MaMoc')->references('MaMoc')->on('moc_thoi_gian_khoa_luans')->onDelete('cascade');
        });

        // 25. Bảng HoiDong
        Schema::create('hoi_dongs', function (Blueprint $table) {
            $table->string('MaHoiDong', 10)->primary();
            $table->string('TenHoiDong', 200);
            $table->timestamp('ThoiGianBatDau');
            $table->timestamp('ThoiGianKetThuc');
            $table->string('DiaDiem', 200)->nullable();
            $table->string('TrangThai', 50)->default('Chưa diễn ra');
            $table->string('GhiChu', 255)->nullable();
            $table->timestamps();
        });

        // 26. Bảng ThanhVienHoiDong
        Schema::create('thanh_vien_hoi_dongs', function (Blueprint $table) {
            $table->string('MaHoiDong', 10);
            $table->string('MaGV', 10);
            $table->string('VaiTro', 50);
            $table->timestamps();

            $table->primary(['MaHoiDong', 'MaGV']);
            $table->foreign('MaHoiDong')->references('MaHoiDong')->on('hoi_dongs')->onDelete('cascade');
            $table->foreign('MaGV')->references('MaGV')->on('giang_viens')->onDelete('cascade');
        });

        // 27. Bảng HoSoBaoVe (Turnitin & GVPB)
        Schema::create('ho_so_bao_ves', function (Blueprint $table) {
            $table->string('MaHoSo', 10)->primary();
            $table->string('MaNhom', 10);
            $table->string('MaHoiDong', 10)->nullable();
            $table->string('MaGVPhanBien', 10)->nullable();
            $table->boolean('XacNhanGVHD')->default(false);
            $table->date('NgayXacNhanGVHD')->nullable();
            $table->decimal('TyLeTrungLap', 5, 2)->nullable();
            $table->string('MinhChungDaoVan', 255)->nullable();
            $table->string('TrangThai', 50)->default('Chờ xác nhận');
            $table->date('NgayNop')->nullable();
            $table->date('NgayXacNhan')->nullable();
            $table->string('NguoiXacNhan', 10)->nullable();
            $table->string('GhiChu', 255)->nullable();
            $table->timestamps();

            $table->foreign('MaNhom')->references('MaNhom')->on('nhoms')->onDelete('cascade');
            $table->foreign('MaHoiDong')->references('MaHoiDong')->on('hoi_dongs')->onDelete('set null');
            $table->foreign('MaGVPhanBien')->references('MaGV')->on('giang_viens')->onDelete('set null');
            $table->foreign('NguoiXacNhan')->references('MaGVu')->on('giao_vus')->onDelete('set null');
        });

        // 28. Bảng ChiTietDiemHoiDong
        Schema::create('chi_tiet_diem_hoi_dongs', function (Blueprint $table) {
            $table->string('MaHoiDong', 10);
            $table->string('MaGV', 10);
            $table->string('MaSV', 10);
            $table->decimal('Diem', 4, 2);
            $table->text('NhanXet')->nullable();
            $table->timestamp('NgayCham')->nullable();
            $table->timestamps();

            $table->primary(['MaHoiDong', 'MaGV', 'MaSV']);
            $table->foreign('MaHoiDong')->references('MaHoiDong')->on('hoi_dongs')->onDelete('cascade');
            $table->foreign('MaGV')->references('MaGV')->on('giang_viens')->onDelete('cascade');
            $table->foreign('MaSV')->references('MaSV')->on('sinh_viens')->onDelete('cascade');
        });

        // 29. Bảng KetQuaSinhVien
        Schema::create('ket_qua_sinh_viens', function (Blueprint $table) {
            $table->string('MaKetQua', 10)->primary();
            $table->string('MaSV', 10);
            $table->string('MaHocKy', 10);
            $table->decimal('DiemHuongDan', 4, 2)->nullable();
            $table->decimal('DiemPhanBien', 4, 2)->nullable();
            $table->decimal('DiemHoiDongTB', 4, 2)->nullable();
            $table->decimal('DiemTongKet', 4, 2)->nullable();
            $table->string('KetQua', 50)->nullable();
            $table->text('NhanXetChung')->nullable();
            $table->date('NgayCham')->nullable();
            $table->timestamps();

            $table->foreign('MaSV')->references('MaSV')->on('sinh_viens')->onDelete('cascade');
            $table->foreign('MaHocKy')->references('MaHocKy')->on('hoc_kies')->onDelete('cascade');
        });

        // 30. Bảng ThongBao
        Schema::create('thong_baos', function (Blueprint $table) {
            $table->string('MaThongBao', 10)->primary();
            $table->string('MaGVu', 10)->nullable();
            $table->string('TieuDe', 200);
            $table->text('NoiDung');
            $table->string('LoaiThongBao', 50)->nullable();
            $table->string('DoiTuongNhan', 50)->default('Tất cả');
            $table->timestamp('NgayTao')->nullable();
            $table->string('TrangThai', 50)->default('Đã tạo');
            $table->timestamps();

            $table->foreign('MaGVu')->references('MaGVu')->on('giao_vus')->onDelete('set null');
        });

        // 31. Bảng NguoiNhanThongBao
        Schema::create('nguoi_nhan_thong_baos', function (Blueprint $table) {
            $table->string('MaThongBao', 10);
            $table->string('MaTK', 10);
            $table->boolean('DaDoc')->default(false);
            $table->timestamp('NgayDoc')->nullable();
            $table->timestamps();

            $table->primary(['MaThongBao', 'MaTK']);
            $table->foreign('MaThongBao')->references('MaThongBao')->on('thong_baos')->onDelete('cascade');
            $table->foreign('MaTK')->references('MaTK')->on('tai_khoans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nguoi_nhan_thong_baos');


        Schema::dropIfExists('thong_baos');
        Schema::dropIfExists('ket_qua_sinh_viens');
        Schema::dropIfExists('chi_tiet_diem_hoi_dongs');
        Schema::dropIfExists('ho_so_bao_ves');
        Schema::dropIfExists('thanh_vien_hoi_dongs');
        Schema::dropIfExists('hoi_dongs');
        Schema::dropIfExists('tien_dos');
        Schema::dropIfExists('nhan_xets');
        Schema::dropIfExists('tom_tat_bao_caos');
        Schema::dropIfExists('bao_cao_tien_dos');
        Schema::dropIfExists('dang_ky_de_tais');
        Schema::dropIfExists('thanh_vien_nhoms');
        Schema::dropIfExists('nhoms');
        Schema::dropIfExists('de_tais');
        Schema::dropIfExists('bieu_maus');
        Schema::dropIfExists('quy_dinh_khoa_luans');
        Schema::dropIfExists('moc_thoi_gian_khoa_luans');
        Schema::dropIfExists('ke_hoach_khoa_luans');
        Schema::dropIfExists('chi_tieu_huong_dans');
        Schema::dropIfExists('danh_sach_sv_du_dieu_kiens');
        Schema::dropIfExists('sinh_viens');
        Schema::dropIfExists('giang_viens');
        Schema::dropIfExists('giao_vus');
        Schema::dropIfExists('hoc_kies');
        Schema::dropIfExists('lops');
        Schema::dropIfExists('nganhs');
        Schema::dropIfExists('bo_mons');
        Schema::dropIfExists('khoas');
        Schema::dropIfExists('tai_khoans');
        Schema::dropIfExists('vai_tros');
    }
};
