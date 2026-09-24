<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('DeTai', function (Blueprint $table) {
            if (!Schema::hasColumn('DeTai', 'HocPhan')) {
                $table->string('HocPhan', 150)->default('Khóa luận tốt nghiệp')->after('MaHocKy');
            }
        });
    }

    public function down(): void
    {
        Schema::table('DeTai', function (Blueprint $table) {
            if (Schema::hasColumn('DeTai', 'HocPhan')) {
                $table->dropColumn('HocPhan');
            }
        });
    }
};
