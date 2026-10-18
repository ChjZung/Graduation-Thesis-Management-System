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
        // 1. Bảng Giai đoạn thực hiện khóa luận do Giảng viên hướng dẫn thiết lập
        if (!Schema::hasTable('giaidoanthuchien')) {
            Schema::create('giaidoanthuchien', function (Blueprint $table) {
                $table->bigIncrements('MaGiaiDoan');
                $table->string('MaDeTai', 50)->index();
                $table->string('TenGiaiDoan', 255);
                $table->text('MoTa')->nullable();
                $table->text('NoiDungCongViec')->nullable();
                $table->text('KetQuaDauRa')->nullable();
                $table->date('NgayBatDau')->nullable();
                $table->date('NgayKetThuc')->nullable();
                $table->text('YeuCauCanDat')->nullable();
                $table->integer('ThuTu')->default(1);
                $table->string('TrangThai', 50)->default('CHUA_BAT_DAU'); // CHUA_BAT_DAU, DANG_THUC_HIEN, HOAN_THANH
                $table->timestamps();
            });
        }

        // 2. Bảng Các đợt nộp báo cáo trong giai đoạn do Giảng viên thiết lập
        if (!Schema::hasTable('dotnopbaocao')) {
            Schema::create('dotnopbaocao', function (Blueprint $table) {
                $table->bigIncrements('MaDotNop');
                $table->unsignedBigInteger('MaGiaiDoan')->index();
                $table->string('MaDeTai', 50)->index();
                $table->string('TenDotNop', 255);
                $table->text('MoTa')->nullable();
                $table->text('NoiDungYeuCau')->nullable();
                $table->dateTime('NgayMoNop')->nullable();
                $table->dateTime('HanNop')->nullable();
                $table->string('DinhDangChoPhep', 100)->default('pdf');
                $table->boolean('YeuCauSourceCode')->default(true);
                $table->integer('SoLanNopToiDa')->nullable()->default(null);
                $table->string('TrangThai', 50)->default('DANG_MO'); // DANG_MO, TAM_DONG, DA_DONG, HOAN_THANH
                $table->integer('ThuTu')->default(1);
                $table->timestamps();
            });
        }

        // 3. Mở rộng khoibaocaotiendo (Phiên bản nộp) để liên kết với đợt nộp và giai đoạn
        if (Schema::hasTable('khoibaocaotiendo')) {
            Schema::table('khoibaocaotiendo', function (Blueprint $table) {
                if (!Schema::hasColumn('khoibaocaotiendo', 'MaDotNop')) {
                    $table->unsignedBigInteger('MaDotNop')->nullable()->after('MaBaoCao')->index();
                }
                if (!Schema::hasColumn('khoibaocaotiendo', 'MaGiaiDoan')) {
                    $table->unsignedBigInteger('MaGiaiDoan')->nullable()->after('MaDotNop')->index();
                }
                if (!Schema::hasColumn('khoibaocaotiendo', 'CongViecChuaHoanThanh')) {
                    $table->text('CongViecChuaHoanThanh')->nullable()->after('KhoKhan');
                }
            });
        }

        // 4. Bảng Tiến độ nghiệp vụ khóa luận do Giáo vụ quản lý (6 giai đoạn nghiệp vụ)
        if (!Schema::hasTable('tiendonghiepvukhoaluan')) {
            Schema::create('tiendonghiepvukhoaluan', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('MaNhom', 50)->index();
                $table->string('MaDeTai', 50)->nullable()->index();
                $table->string('MaHocKy', 50)->nullable()->index();
                $table->integer('GiaiDoanHienTai')->default(1);
                $table->string('TrangThaiGiaiDoan', 50)->default('DANG_THUC_HIEN'); // CHUA_BAT_DAU, DANG_THUC_HIEN, CHO_XU_LY, HOAN_THANH, QUA_HAN, KHONG_DAT
                $table->json('DieuKienDat')->nullable();
                $table->dateTime('NgayBatDau')->nullable();
                $table->dateTime('NgayHoanThanh')->nullable();
                $table->text('GhiChu')->nullable();
                $table->timestamps();
            });
        }

        // 5. Bảng Lịch sử chuyển giai đoạn nghiệp vụ của Giáo vụ
        if (!Schema::hasTable('lichsuchuyengiaidoan')) {
            Schema::create('lichsuchuyengiaidoan', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('MaNhom', 50)->index();
                $table->integer('GiaiDoanTruoc')->nullable();
                $table->integer('GiaiDoanSau');
                $table->string('TrangThaiTruoc', 50)->nullable();
                $table->string('TrangThaiSau', 50);
                $table->string('HanhDong', 100);
                $table->string('NguoiThucHien', 100);
                $table->text('LyDo')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lichsuchuyengiaidoan');
        Schema::dropIfExists('tiendonghiepvukhoaluan');
        if (Schema::hasTable('khoibaocaotiendo')) {
            Schema::table('khoibaocaotiendo', function (Blueprint $table) {
                if (Schema::hasColumn('khoibaocaotiendo', 'MaDotNop')) {
                    $table->dropColumn('MaDotNop');
                }
                if (Schema::hasColumn('khoibaocaotiendo', 'MaGiaiDoan')) {
                    $table->dropColumn('MaGiaiDoan');
                }
                if (Schema::hasColumn('khoibaocaotiendo', 'CongViecChuaHoanThanh')) {
                    $table->dropColumn('CongViecChuaHoanThanh');
                }
            });
        }
        Schema::dropIfExists('dotnopbaocao');
        Schema::dropIfExists('giaidoanthuchien');
    }
};
