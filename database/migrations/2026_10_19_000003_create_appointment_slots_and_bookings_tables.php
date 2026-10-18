<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Nâng cấp bảng LichGapHuongDan để làm Khung giờ rảnh tiếp sinh viên của Giảng viên
        if (Schema::hasTable('LichGapHuongDan')) {
            // Cho phép MaNhom nullable (vì khung giờ do GV tạo ban đầu chưa có nhóm nào đặt)
            try {
                DB::statement('ALTER TABLE LichGapHuongDan MODIFY MaNhom VARCHAR(20) NULL');
            } catch (\Throwable $e) {
                // Fallback nếu không dùng statement trực tiếp
            }

            Schema::table('LichGapHuongDan', function (Blueprint $table) {
                if (!Schema::hasColumn('LichGapHuongDan', 'HinhThuc')) {
                    $table->string('HinhThuc', 30)->default('TrucTiep')->after('NoiDung'); // 'TrucTiep', 'TrucTuyen'
                }
                if (!Schema::hasColumn('LichGapHuongDan', 'LinkCuocHop')) {
                    $table->string('LinkCuocHop', 500)->nullable()->after('DiaDiem');
                }
                if (!Schema::hasColumn('LichGapHuongDan', 'SoNhomToiDa')) {
                    $table->integer('SoNhomToiDa')->default(1)->after('LinkCuocHop');
                }
                if (!Schema::hasColumn('LichGapHuongDan', 'SoNhomDaDangKy')) {
                    $table->integer('SoNhomDaDangKy')->default(0)->after('SoNhomToiDa');
                }
                if (!Schema::hasColumn('LichGapHuongDan', 'LyDoHuy')) {
                    $table->text('LyDoHuy')->nullable()->after('TrangThai');
                }
            });
        }

        // 2. Tạo bảng dang_ky_lich_huong_dan để quản lý từng lượt nhóm đặt khung giờ
        if (!Schema::hasTable('dang_ky_lich_huong_dan')) {
            Schema::create('dang_ky_lich_huong_dan', function (Blueprint $table) {
                $table->id();
                $table->string('MaLichGap', 20);
                $table->string('MaNhom', 20);
                $table->string('MaSV', 20);
                $table->dateTime('NgayDangKy');
                $table->text('GhiChu')->nullable();
                $table->string('TrangThai', 50)->default('DaXacNhan'); // 'DaXacNhan', 'DaHuy', 'DaHoanThanh'
                $table->text('LyDoHuy')->nullable();
                $table->string('NguoiHuy', 50)->nullable(); // 'SinhVien', 'GiangVien'
                $table->dateTime('ThoiGianHuy')->nullable();
                $table->text('KetQuaGap')->nullable();
                $table->timestamps();

                $table->foreign('MaLichGap')->references('MaLichGap')->on('LichGapHuongDan')->onDelete('cascade');
                $table->foreign('MaNhom')->references('MaNhom')->on('Nhom')->onDelete('cascade');
                $table->foreign('MaSV')->references('MaSV')->on('SinhVien')->onDelete('cascade');

                $table->index(['MaLichGap', 'TrangThai']);
                $table->index(['MaNhom', 'TrangThai']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dang_ky_lich_huong_dan');

        if (Schema::hasTable('LichGapHuongDan')) {
            Schema::table('LichGapHuongDan', function (Blueprint $table) {
                $cols = ['HinhThuc', 'LinkCuocHop', 'SoNhomToiDa', 'SoNhomDaDangKy', 'LyDoHuy'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('LichGapHuongDan', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
