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
        Schema::table('PhieuChamDiem', function (Blueprint $table) {
            if (!Schema::hasColumn('PhieuChamDiem', 'MaSV')) {
                $table->string('MaSV', 20)->nullable()->after('MaGV');
                $table->foreign('MaSV')->references('MaSV')->on('SinhVien')->onDelete('cascade');
            }
            if (!Schema::hasColumn('PhieuChamDiem', 'LoaiKhoaLuan')) {
                $table->string('LoaiKhoaLuan', 50)->default('KLCN')->after('Diem');
            }
            if (!Schema::hasColumn('PhieuChamDiem', 'ChiTietDiem')) {
                $table->longText('ChiTietDiem')->nullable()->after('NhanXet');
            }
        });

        Schema::table('KetQuaSinhVien', function (Blueprint $table) {
            if (!Schema::hasColumn('KetQuaSinhVien', 'LoaiKhoaLuan')) {
                $table->string('LoaiKhoaLuan', 50)->default('KLCN')->nullable()->after('KetQua');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('PhieuChamDiem', function (Blueprint $table) {
            if (Schema::hasColumn('PhieuChamDiem', 'MaSV')) {
                $table->dropForeign(['MaSV']);
                $table->dropColumn('MaSV');
            }
            if (Schema::hasColumn('PhieuChamDiem', 'LoaiKhoaLuan')) {
                $table->dropColumn('LoaiKhoaLuan');
            }
            if (Schema::hasColumn('PhieuChamDiem', 'ChiTietDiem')) {
                $table->dropColumn('ChiTietDiem');
            }
        });

        Schema::table('KetQuaSinhVien', function (Blueprint $table) {
            if (Schema::hasColumn('KetQuaSinhVien', 'LoaiKhoaLuan')) {
                $table->dropColumn('LoaiKhoaLuan');
            }
        });
    }
};
