<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('dang_ky_de_tais', function (Blueprint $table) {
            if (!Schema::hasColumn('dang_ky_de_tais', 'LyDoTuChoi')) {
                $table->text('LyDoTuChoi')->nullable()->after('NgayDuyet');
            }
        });
    }

    public function down(): void {
        Schema::table('dang_ky_de_tais', function (Blueprint $table) {
            $table->dropColumn(['LyDoTuChoi']);
        });
    }
};
