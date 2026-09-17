<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bao_cao_tien_dos', function (Blueprint $table) {
            $table->string('LinkCode', 500)->nullable()->after('DuongDanFile');
        });
    }

    public function down(): void
    {
        Schema::table('bao_cao_tien_dos', function (Blueprint $table) {
            $table->dropColumn('LinkCode');
        });
    }
};
