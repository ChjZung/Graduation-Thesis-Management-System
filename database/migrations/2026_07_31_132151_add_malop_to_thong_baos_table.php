<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thong_baos', function (Blueprint $table) {
            if (!Schema::hasColumn('thong_baos', 'MaLop')) {
                $table->bigInteger('MaLop')->unsigned()->nullable()->after('MaTK');
                $table->foreign('MaLop')->references('MaLop')->on('lops')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('thong_baos', function (Blueprint $table) {
            if (Schema::hasColumn('thong_baos', 'MaLop')) {
                $table->dropForeign(['MaLop']);
                $table->dropColumn('MaLop');
            }
        });
    }
};
