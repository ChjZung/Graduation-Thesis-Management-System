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
        // 1. Cho phép PhanTramHoanThanh là null để phản ánh trạng thái "Chưa đánh giá"
        Schema::table('khoibaocaotiendo', function (Blueprint $table) {
            $table->integer('PhanTramHoanThanh')->nullable()->default(null)->change();
        });

        Schema::table('baocaotiendo', function (Blueprint $table) {
            $table->integer('PhanTramHoanThanh')->nullable()->default(null)->change();
        });

        // 2. Mở rộng tomtatbaocao để lưu kết quả đánh giá AI có căn cứ & thời điểm đánh giá
        Schema::table('tomtatbaocao', function (Blueprint $table) {
            if (!Schema::hasColumn('tomtatbaocao', 'TyLeHoanThanhAI')) {
                $table->integer('TyLeHoanThanhAI')->nullable()->after('SoTrang');
            }
            if (!Schema::hasColumn('tomtatbaocao', 'CanCuDanhGia')) {
                $table->longText('CanCuDanhGia')->nullable()->after('TyLeHoanThanhAI');
            }
            if (!Schema::hasColumn('tomtatbaocao', 'ThoiDiemDanhGia')) {
                $table->dateTime('ThoiDiemDanhGia')->nullable()->after('CanCuDanhGia');
            }
            if (!Schema::hasColumn('tomtatbaocao', 'TrangThaiDanhGia')) {
                $table->string('TrangThaiDanhGia', 50)->default('chua_danh_gia')->after('ThoiDiemDanhGia');
            }
        });

        // 3. Mở rộng HoSoBaoVe để Thư ký hội đồng theo dõi trạng thái chuẩn bị phiếu chấm
        Schema::table('HoSoBaoVe', function (Blueprint $table) {
            if (!Schema::hasColumn('HoSoBaoVe', 'TrangThaiChuanBiPhieu')) {
                $table->string('TrangThaiChuanBiPhieu', 50)->default('ChuaChuanBi')->after('TieuChiChucNang');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'NguoiChuanBiPhieu')) {
                $table->string('NguoiChuanBiPhieu', 50)->nullable()->after('TrangThaiChuanBiPhieu');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'NgayChuanBiPhieu')) {
                $table->dateTime('NgayChuanBiPhieu')->nullable()->after('NguoiChuanBiPhieu');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'NoiDungDeCuongJson')) {
                $table->longText('NoiDungDeCuongJson')->nullable()->after('NgayChuanBiPhieu');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('HoSoBaoVe', function (Blueprint $table) {
            if (Schema::hasColumn('HoSoBaoVe', 'NoiDungDeCuongJson')) {
                $table->dropColumn('NoiDungDeCuongJson');
            }
            if (Schema::hasColumn('HoSoBaoVe', 'NgayChuanBiPhieu')) {
                $table->dropColumn('NgayChuanBiPhieu');
            }
            if (Schema::hasColumn('HoSoBaoVe', 'NguoiChuanBiPhieu')) {
                $table->dropColumn('NguoiChuanBiPhieu');
            }
            if (Schema::hasColumn('HoSoBaoVe', 'TrangThaiChuanBiPhieu')) {
                $table->dropColumn('TrangThaiChuanBiPhieu');
            }
        });

        Schema::table('tomtatbaocao', function (Blueprint $table) {
            if (Schema::hasColumn('tomtatbaocao', 'TrangThaiDanhGia')) {
                $table->dropColumn('TrangThaiDanhGia');
            }
            if (Schema::hasColumn('tomtatbaocao', 'ThoiDiemDanhGia')) {
                $table->dropColumn('ThoiDiemDanhGia');
            }
            if (Schema::hasColumn('tomtatbaocao', 'CanCuDanhGia')) {
                $table->dropColumn('CanCuDanhGia');
            }
            if (Schema::hasColumn('tomtatbaocao', 'TyLeHoanThanhAI')) {
                $table->dropColumn('TyLeHoanThanhAI');
            }
        });

        Schema::table('baocaotiendo', function (Blueprint $table) {
            $table->integer('PhanTramHoanThanh')->default(0)->change();
        });

        Schema::table('khoibaocaotiendo', function (Blueprint $table) {
            $table->integer('PhanTramHoanThanh')->default(0)->change();
        });
    }
};
