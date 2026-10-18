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
        // 1. Nâng cấp bảng PhieuChamDiem: Trạng thái (Nháp / Hoàn thành), Điểm khuyến khích, Khóa phiếu
        Schema::table('PhieuChamDiem', function (Blueprint $table) {
            if (!Schema::hasColumn('PhieuChamDiem', 'TrangThai')) {
                $table->string('TrangThai', 50)->default('Lưu nháp')->after('LoaiKhoaLuan');
            }
            if (!Schema::hasColumn('PhieuChamDiem', 'DiemKhuyenKhich')) {
                $table->decimal('DiemKhuyenKhich', 4, 2)->default(0.00)->after('Diem');
            }
            if (!Schema::hasColumn('PhieuChamDiem', 'NgayXacNhan')) {
                $table->dateTime('NgayXacNhan')->nullable()->after('NgayCham');
            }
            if (!Schema::hasColumn('PhieuChamDiem', 'GhiChuMoKhoa')) {
                $table->text('GhiChuMoKhoa')->nullable()->after('NhanXet');
            }
        });

        // 2. Nâng cấp bảng KetQuaSinhVien: Phân định Điểm sơ bộ vs Điểm chính thức, Trạng thái công bố
        Schema::table('KetQuaSinhVien', function (Blueprint $table) {
            if (!Schema::hasColumn('KetQuaSinhVien', 'DiemChinhThuc')) {
                $table->decimal('DiemChinhThuc', 4, 2)->nullable()->after('DiemTongKet');
            }
            if (!Schema::hasColumn('KetQuaSinhVien', 'DiemKhuyenKhich')) {
                $table->decimal('DiemKhuyenKhich', 4, 2)->default(0.00)->after('DiemHoiDongTB');
            }
            if (!Schema::hasColumn('KetQuaSinhVien', 'TrangThai')) {
                $table->string('TrangThai', 50)->default('Chờ xác nhận')->after('KetQua');
            }
            if (!Schema::hasColumn('KetQuaSinhVien', 'NguoiXacNhan')) {
                $table->string('NguoiXacNhan', 100)->nullable()->after('TrangThai');
            }
            if (!Schema::hasColumn('KetQuaSinhVien', 'NgayXacNhan')) {
                $table->dateTime('NgayXacNhan')->nullable()->after('NguoiXacNhan');
            }
            if (!Schema::hasColumn('KetQuaSinhVien', 'NguoiCongBo')) {
                $table->string('NguoiCongBo', 100)->nullable()->after('NgayXacNhan');
            }
            if (!Schema::hasColumn('KetQuaSinhVien', 'NgayCongBo')) {
                $table->dateTime('NgayCongBo')->nullable()->after('NguoiCongBo');
            }
            if (!Schema::hasColumn('KetQuaSinhVien', 'GhiChuDieuChinh')) {
                $table->text('GhiChuDieuChinh')->nullable()->after('NhanXetChung');
            }
            if (!Schema::hasColumn('KetQuaSinhVien', 'LichSuDieuChinh')) {
                $table->json('LichSuDieuChinh')->nullable()->after('GhiChuDieuChinh');
            }
        });

        // 3. Nâng cấp bảng HoSoBaoVe: Loại khóa luận, Trạng thái kết quả, Yêu cầu chỉnh sửa sau bảo vệ
        Schema::table('HoSoBaoVe', function (Blueprint $table) {
            if (!Schema::hasColumn('HoSoBaoVe', 'LoaiKhoaLuan')) {
                $table->string('LoaiKhoaLuan', 50)->nullable()->after('PhongBaoVe');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'TrangThaiKetQua')) {
                $table->string('TrangThaiKetQua', 50)->default('DangNhapDiem')->after('TrangThai');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'YeuCauChinhSuaSauBaoVe')) {
                $table->text('YeuCauChinhSuaSauBaoVe')->nullable()->after('TrangThaiKetQua');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'HanNopBaoCaoSauBaoVe')) {
                $table->dateTime('HanNopBaoCaoSauBaoVe')->nullable()->after('YeuCauChinhSuaSauBaoVe');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'NguoiKiemTraChinhSua')) {
                $table->string('NguoiKiemTraChinhSua', 100)->nullable()->after('HanNopBaoCaoSauBaoVe');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'TrangThaiChinhSuaSauBaoVe')) {
                $table->string('TrangThaiChinhSuaSauBaoVe', 50)->nullable()->after('NguoiKiemTraChinhSua');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'NgayXacNhanHoanThanhSauBaoVe')) {
                $table->dateTime('NgayXacNhanHoanThanhSauBaoVe')->nullable()->after('TrangThaiChinhSuaSauBaoVe');
            }
        });

        // 4. Bảng riêng biệt quản lý Lịch sử nộp báo cáo hoàn thiện sau bảo vệ
        if (!Schema::hasTable('bao_cao_sau_bao_ve')) {
            Schema::create('bao_cao_sau_bao_ve', function (Blueprint $table) {
                $table->id();
                $table->string('MaHoSo', 50)->index();
                $table->integer('LanChinhSua')->default(1);
                $table->string('DuongDanFile', 500);
                $table->string('TenFile', 255);
                $table->bigInteger('DungLuong')->nullable();
                $table->text('TomTatChinhSua')->nullable();
                $table->dateTime('NgayNop');
                $table->string('NguoiNop', 50)->nullable();
                $table->string('TrangThai', 50)->default('Đã nộp'); // Đã nộp, Đạt, Yêu cầu sửa tiếp
                $table->text('NhanXetGV')->nullable();
                $table->dateTime('NgayKiemTra')->nullable();
                $table->string('NguoiKiemTra', 100)->nullable();
                $table->timestamps();

                $table->foreign('MaHoSo')
                    ->references('MaHoSo')
                    ->on('HoSoBaoVe')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bao_cao_sau_bao_ve');

        Schema::table('HoSoBaoVe', function (Blueprint $table) {
            $cols = [
                'LoaiKhoaLuan',
                'TrangThaiKetQua',
                'YeuCauChinhSuaSauBaoVe',
                'HanNopBaoCaoSauBaoVe',
                'NguoiKiemTraChinhSua',
                'TrangThaiChinhSuaSauBaoVe',
                'NgayXacNhanHoanThanhSauBaoVe',
            ];
            foreach ($cols as $c) {
                if (Schema::hasColumn('HoSoBaoVe', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('KetQuaSinhVien', function (Blueprint $table) {
            $cols = [
                'DiemChinhThuc',
                'DiemKhuyenKhich',
                'TrangThai',
                'NguoiXacNhan',
                'NgayXacNhan',
                'NguoiCongBo',
                'NgayCongBo',
                'GhiChuDieuChinh',
                'LichSuDieuChinh',
            ];
            foreach ($cols as $c) {
                if (Schema::hasColumn('KetQuaSinhVien', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('PhieuChamDiem', function (Blueprint $table) {
            $cols = ['TrangThai', 'DiemKhuyenKhich', 'NgayXacNhan', 'GhiChuMoKhoa'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('PhieuChamDiem', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
