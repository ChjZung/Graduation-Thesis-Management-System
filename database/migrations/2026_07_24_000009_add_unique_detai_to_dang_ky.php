<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('dang_ky_de_tais', function (Blueprint $table) {
            $table->unique('MaDeTai', 'unique_detai_dangky');
        });
    }

    public function down(): void {
        Schema::table('dang_ky_de_tais', function (Blueprint $table) {
            $table->dropUnique('unique_detai_dangky');
        });
    }
};
