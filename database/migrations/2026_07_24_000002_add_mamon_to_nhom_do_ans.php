<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('nhom_do_ans', function (Blueprint $table) {
            if (!Schema::hasColumn('nhom_do_ans', 'MaMon')) {
                $table->unsignedBigInteger('MaMon')->nullable()->after('MaHocKy');
                $table->foreign('MaMon')->references('MaMon')->on('mon_hocs')->onUpdate('cascade')->onDelete('set null');
            }
        });
    }

    public function down(): void {
        Schema::table('nhom_do_ans', function (Blueprint $table) {
            $table->dropForeign(['MaMon']);
            $table->dropColumn(['MaMon']);
        });
    }
};
