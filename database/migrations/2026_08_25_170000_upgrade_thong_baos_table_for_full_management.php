<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thong_baos', function (Blueprint $table) {
            if (!Schema::hasColumn('thong_baos', 'FileDinhKem')) {
                $table->string('FileDinhKem')->nullable()->after('DoiTuongNhan');
            }
            if (!Schema::hasColumn('thong_baos', 'MaKeHoach')) {
                $table->string('MaKeHoach', 10)->nullable()->after('FileDinhKem');
            }
            if (!Schema::hasColumn('thong_baos', 'MaMoc')) {
                $table->string('MaMoc', 10)->nullable()->after('MaKeHoach');
            }
            if (!Schema::hasColumn('thong_baos', 'NgayGui')) {
                $table->dateTime('NgayGui')->nullable()->after('NgayTao');
            }
            if (!Schema::hasColumn('thong_baos', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('thong_baos', function (Blueprint $table) {
            $table->dropColumn(['FileDinhKem', 'MaKeHoach', 'MaMoc', 'NgayGui', 'deleted_at']);
        });
    }
};
