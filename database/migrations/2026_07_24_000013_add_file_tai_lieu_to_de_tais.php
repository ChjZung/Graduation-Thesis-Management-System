<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('de_tais', function (Blueprint $table) {
            if (!Schema::hasColumn('de_tais', 'FileTaiLieu')) {
                $table->string('FileTaiLieu', 255)->nullable()->after('YeuCau');
            }
        });
    }

    public function down(): void {
        Schema::table('de_tais', function (Blueprint $table) {
            if (Schema::hasColumn('de_tais', 'FileTaiLieu')) {
                $table->dropColumn('FileTaiLieu');
            }
        });
    }
};
