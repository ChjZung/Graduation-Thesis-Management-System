<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thong_baos', function (Blueprint $table) {
            if (!Schema::hasColumn('thong_baos', 'MaLopHP')) {
                $table->bigInteger('MaLopHP')->unsigned()->nullable()->after('MaLop');
                $table->foreign('MaLopHP')->references('MaLopHP')->on('lop_hoc_phans')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('thong_baos', function (Blueprint $table) {
            if (Schema::hasColumn('thong_baos', 'MaLopHP')) {
                $table->dropForeign(['MaLopHP']);
                $table->dropColumn('MaLopHP');
            }
        });
    }
};
