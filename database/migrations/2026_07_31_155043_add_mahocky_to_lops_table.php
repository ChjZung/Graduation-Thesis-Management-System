<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lops', function (Blueprint $table) {
            if (!Schema::hasColumn('lops', 'MaHocKy')) {
                $table->bigInteger('MaHocKy')->unsigned()->nullable()->after('MaNganh');
                $table->foreign('MaHocKy')->references('MaHocKy')->on('hoc_kies')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lops', function (Blueprint $table) {
            if (Schema::hasColumn('lops', 'MaHocKy')) {
                $table->dropForeign(['MaHocKy']);
                $table->dropColumn('MaHocKy');
            }
        });
    }
};
