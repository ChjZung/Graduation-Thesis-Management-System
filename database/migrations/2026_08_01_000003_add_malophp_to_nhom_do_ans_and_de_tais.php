<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nhom_do_ans', function (Blueprint $table) {
            $table->unsignedBigInteger('MaLopHP')->nullable()->after('MaMon');
            $table->foreign('MaLopHP')->references('MaLopHP')->on('lop_hoc_phans')->onDelete('set null');
        });

        Schema::table('de_tais', function (Blueprint $table) {
            $table->unsignedBigInteger('MaLopHP')->nullable()->after('MaMon');
            $table->foreign('MaLopHP')->references('MaLopHP')->on('lop_hoc_phans')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('nhom_do_ans', function (Blueprint $table) {
            $table->dropForeign(['MaLopHP']);
            $table->dropColumn('MaLopHP');
        });

        Schema::table('de_tais', function (Blueprint $table) {
            $table->dropForeign(['MaLopHP']);
            $table->dropColumn('MaLopHP');
        });
    }
};
