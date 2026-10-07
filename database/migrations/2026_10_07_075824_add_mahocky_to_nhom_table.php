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
        Schema::table('Nhom', function (Blueprint $table) {
            $table->string('MaHocKy', 20)->nullable();
            $table->foreign('MaHocKy')->references('MaHocKy')->on('HocKy')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Nhom', function (Blueprint $table) {
            $table->dropForeign(['MaHocKy']);
            $table->dropColumn('MaHocKy');
        });
    }
};
