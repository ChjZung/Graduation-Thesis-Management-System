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
        // 1. Bổ sung thời hạn chỉnh sửa điểm và trạng thái mở khóa trên bảng HoiDong
        Schema::table('HoiDong', function (Blueprint $table) {
            if (!Schema::hasColumn('HoiDong', 'ThoiGianBatDauSuaDiem')) {
                $table->dateTime('ThoiGianBatDauSuaDiem')->nullable()->after('TrangThaiCongBo')->comment('Thời gian bắt đầu cho phép giảng viên chỉnh sửa điểm');
            }
            if (!Schema::hasColumn('HoiDong', 'ThoiGianKetThucSuaDiem')) {
                $table->dateTime('ThoiGianKetThucSuaDiem')->nullable()->after('ThoiGianBatDauSuaDiem')->comment('Thời gian kết thúc cho phép giảng viên chỉnh sửa điểm');
            }
            if (!Schema::hasColumn('HoiDong', 'TrangThaiMoKhoaSuaDiem')) {
                $table->boolean('TrangThaiMoKhoaSuaDiem')->default(false)->after('ThoiGianKetThucSuaDiem')->comment('Cờ mở khóa đặc biệt từ Quản trị viên/Trưởng khoa');
            }
            if (!Schema::hasColumn('HoiDong', 'LyDoMoKhoaSuaDiem')) {
                $table->text('LyDoMoKhoaSuaDiem')->nullable()->after('TrangThaiMoKhoaSuaDiem')->comment('Lý do mở khóa sửa điểm từ Quản trị viên');
            }
        });

        // 2. Tạo bảng lưu trữ lịch sử chỉnh sửa điểm (Immutable Audit Log)
        if (!Schema::hasTable('lich_su_chinh_sua_diem')) {
            Schema::create('lich_su_chinh_sua_diem', function (Blueprint $table) {
                $table->id();
                $table->string('MaPhieuChamDiem', 50)->index();
                $table->string('MaHoiDong', 50)->index();
                $table->string('MaHoSo', 50)->nullable()->index();
                $table->string('MaSV', 50)->index();
                $table->string('MaGV', 50)->index();
                $table->string('NguoiThucHien', 150);
                $table->string('MaCLO', 50)->nullable()->index();
                $table->text('TenTieuChi')->nullable();
                $table->decimal('DiemCu', 5, 2)->nullable();
                $table->decimal('DiemMoi', 5, 2)->nullable();
                $table->decimal('DiemTongCu', 5, 2)->nullable();
                $table->decimal('DiemTongMoi', 5, 2)->nullable();
                $table->json('ChiTietThayDoi')->nullable();
                $table->text('LyDo')->nullable();
                $table->timestamp('ThoiGian')->useCurrent();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lich_su_chinh_sua_diem');

        Schema::table('HoiDong', function (Blueprint $table) {
            if (Schema::hasColumn('HoiDong', 'LyDoMoKhoaSuaDiem')) {
                $table->dropColumn('LyDoMoKhoaSuaDiem');
            }
            if (Schema::hasColumn('HoiDong', 'TrangThaiMoKhoaSuaDiem')) {
                $table->dropColumn('TrangThaiMoKhoaSuaDiem');
            }
            if (Schema::hasColumn('HoiDong', 'ThoiGianKetThucSuaDiem')) {
                $table->dropColumn('ThoiGianKetThucSuaDiem');
            }
            if (Schema::hasColumn('HoiDong', 'ThoiGianBatDauSuaDiem')) {
                $table->dropColumn('ThoiGianBatDauSuaDiem');
            }
        });
    }
};
