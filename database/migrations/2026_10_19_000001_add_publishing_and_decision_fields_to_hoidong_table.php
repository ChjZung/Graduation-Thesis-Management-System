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
        Schema::table('HoiDong', function (Blueprint $table) {
            if (!Schema::hasColumn('HoiDong', 'MaKhoa')) {
                $table->string('MaKhoa', 20)->nullable()->after('MaHocKy');
                $table->foreign('MaKhoa')->references('MaKhoa')->on('Khoa')->onDelete('set null');
            }
            if (!Schema::hasColumn('HoiDong', 'MaNganh')) {
                $table->string('MaNganh', 20)->nullable()->after('MaKhoa');
                $table->foreign('MaNganh')->references('MaNganh')->on('Nganh')->onDelete('set null');
            }
            if (!Schema::hasColumn('HoiDong', 'DotBaoVe')) {
                $table->string('DotBaoVe', 100)->nullable()->after('MaNganh');
            }
            if (!Schema::hasColumn('HoiDong', 'SoQuyetDinh')) {
                $table->string('SoQuyetDinh', 100)->nullable()->after('DotBaoVe');
            }
            if (!Schema::hasColumn('HoiDong', 'NgayBanHanh')) {
                $table->date('NgayBanHanh')->nullable()->after('SoQuyetDinh');
            }
            if (!Schema::hasColumn('HoiDong', 'NguoiKy')) {
                $table->string('NguoiKy', 150)->nullable()->after('NgayBanHanh');
            }
            if (!Schema::hasColumn('HoiDong', 'ChucVuNguoiKy')) {
                $table->string('ChucVuNguoiKy', 150)->nullable()->after('NguoiKy');
            }
            if (!Schema::hasColumn('HoiDong', 'FileQuyetDinh')) {
                $table->string('FileQuyetDinh', 255)->nullable()->after('ChucVuNguoiKy');
            }
            if (!Schema::hasColumn('HoiDong', 'TrangThaiCongBo')) {
                $table->string('TrangThaiCongBo', 50)->default('Chưa công bố')->after('FileQuyetDinh');
            }
            if (!Schema::hasColumn('HoiDong', 'NgayCongBo')) {
                $table->dateTime('NgayCongBo')->nullable()->after('TrangThaiCongBo');
            }
            if (!Schema::hasColumn('HoiDong', 'NguoiCongBo')) {
                $table->string('NguoiCongBo', 100)->nullable()->after('NgayCongBo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('HoiDong', function (Blueprint $table) {
            $cols = [
                'MaKhoa', 'MaNganh', 'DotBaoVe', 'SoQuyetDinh', 'NgayBanHanh',
                'NguoiKy', 'ChucVuNguoiKy', 'FileQuyetDinh', 'TrangThaiCongBo',
                'NgayCongBo', 'NguoiCongBo'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('HoiDong', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
