<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bảng thong_bao_van_bans (Quản lý File Văn Bản Thông Báo Gốc)
        Schema::create('thong_bao_van_bans', function (Blueprint $table) {
            $table->string('MaVanBan', 20)->primary();
            $table->string('MaKeHoach', 10)->nullable();
            $table->string('TenVanBan');
            $table->string('SoThongBao')->nullable();
            $table->date('NgayBanHanh')->nullable();
            $table->string('FileGocPath');
            $table->string('FileType', 10);
            $table->integer('FileSize')->default(0);
            $table->integer('PhienBanHienTai')->default(1);
            $table->string('NguoiUpload', 20)->nullable();
            $table->timestamps();

            $table->foreign('MaKeHoach')->references('MaKeHoach')->on('ke_hoach_khoa_luans')->onDelete('set null');
        });

        // 2. Bảng thong_bao_van_ban_versions (Lịch Sử Phiên Bản & Diff)
        Schema::create('thong_bao_van_ban_versions', function (Blueprint $table) {
            $table->id();
            $table->string('MaVanBan', 20);
            $table->integer('PhienBan');
            $table->string('FileGocPath');
            $table->longText('NoiDungTrichXuatJson')->nullable();
            $table->longText('MocThoiGianJson')->nullable();
            $table->longText('ThayDoiSoVoiTruocJson')->nullable();
            $table->text('LyDoThayDoi')->nullable();
            $table->string('NguoiThayDoi', 20)->nullable();
            $table->timestamps();

            $table->foreign('MaVanBan')->references('MaVanBan')->on('thong_bao_van_bans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thong_bao_van_ban_versions');
        Schema::dropIfExists('thong_bao_van_bans');
    }
};
