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
            if (!Schema::hasColumn('PhieuChamDiem', 'ChuKy')) {
                $table->longText('ChuKy')->nullable()->after('NhanXet');
            }
            if (!Schema::hasColumn('PhieuChamDiem', 'NgayKy')) {
                $table->dateTime('NgayKy')->nullable()->after('ChuKy');
            }
        });

        Schema::table('GiangVien', function (Blueprint $table) {
            if (!Schema::hasColumn('GiangVien', 'ChuKy')) {
                $table->longText('ChuKy')->nullable()->after('HocVi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('PhieuChamDiem', function (Blueprint $table) {
            if (Schema::hasColumn('PhieuChamDiem', 'ChuKy')) {
                $table->dropColumn('ChuKy');
            }
            if (Schema::hasColumn('PhieuChamDiem', 'NgayKy')) {
                $table->dropColumn('NgayKy');
            }
        });

        Schema::table('GiangVien', function (Blueprint $table) {
            if (Schema::hasColumn('GiangVien', 'ChuKy')) {
                $table->dropColumn('ChuKy');
            }
        });
    }
};
