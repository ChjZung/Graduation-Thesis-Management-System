<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ThongBao', function (Blueprint $table) {
            if (!Schema::hasColumn('ThongBao', 'MaGV')) {
                $table->string('MaGV', 20)->nullable()->after('MaGVu');
                $table->foreign('MaGV')->references('MaGV')->on('GiangVien')->onDelete('set null');
            }
            if (!Schema::hasColumn('ThongBao', 'FileDinhKem')) {
                $table->string('FileDinhKem', 500)->nullable()->after('MaGV');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ThongBao', function (Blueprint $table) {
            if (Schema::hasColumn('ThongBao', 'MaGV')) {
                $table->dropForeign(['MaGV']);
                $table->dropColumn('MaGV');
            }
            if (Schema::hasColumn('ThongBao', 'FileDinhKem')) {
                $table->dropColumn('FileDinhKem');
            }
        });
    }
};
