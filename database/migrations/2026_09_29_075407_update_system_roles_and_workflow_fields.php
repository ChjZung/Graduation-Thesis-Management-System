<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Thêm / Chuẩn hóa 5 Vai trò cố định trong bảng VaiTro
        // 1. GIÁO VỤ, 2. GIẢNG VIÊN, 3. SINH VIÊN, 4. TRƯỞNG BỘ MÔN, 5. TRƯỞNG KHOA
        DB::table('VaiTro')->where('MaVaiTro', 'VT01')->update(['TenVaiTro' => 'Giáo vụ']);
        
        $roles = [
            ['MaVaiTro' => 'VT01', 'TenVaiTro' => 'Giáo vụ', 'created_at' => now(), 'updated_at' => now()],
            ['MaVaiTro' => 'VT02', 'TenVaiTro' => 'Giảng viên', 'created_at' => now(), 'updated_at' => now()],
            ['MaVaiTro' => 'VT03', 'TenVaiTro' => 'Sinh viên', 'created_at' => now(), 'updated_at' => now()],
            ['MaVaiTro' => 'VT04', 'TenVaiTro' => 'Trưởng bộ môn', 'created_at' => now(), 'updated_at' => now()],
            ['MaVaiTro' => 'VT05', 'TenVaiTro' => 'Trưởng khoa', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($roles as $role) {
            DB::table('VaiTro')->updateOrInsert(
                ['MaVaiTro' => $role['MaVaiTro']],
                ['TenVaiTro' => $role['TenVaiTro'], 'updated_at' => now()]
            );
        }

        // 2. Bổ sung trường quản lý luồng duyệt Bộ môn, Khoa và Công bố vào bảng DeTai
        Schema::table('DeTai', function (Blueprint $table) {
            if (!Schema::hasColumn('DeTai', 'NgayDuyetBM')) {
                $table->dateTime('NgayDuyetBM')->nullable()->after('NgayDeXuat');
            }
            if (!Schema::hasColumn('DeTai', 'NguoiDuyetBM')) {
                $table->string('NguoiDuyetBM', 20)->nullable()->after('NgayDuyetBM');
            }
            if (!Schema::hasColumn('DeTai', 'NgayDuyetKhoa')) {
                $table->dateTime('NgayDuyetKhoa')->nullable()->after('NguoiDuyetBM');
            }
            if (!Schema::hasColumn('DeTai', 'NguoiDuyetKhoa')) {
                $table->string('NguoiDuyetKhoa', 20)->nullable()->after('NgayDuyetKhoa');
            }
            if (!Schema::hasColumn('DeTai', 'NgayCongBo')) {
                $table->dateTime('NgayCongBo')->nullable()->after('NguoiDuyetKhoa');
            }
        });

        // 3. Bổ sung trường đánh giá phản biện đề cương vào bảng PhanCongPhanBien
        Schema::table('PhanCongPhanBien', function (Blueprint $table) {
            if (!Schema::hasColumn('PhanCongPhanBien', 'NhanXet')) {
                $table->longText('NhanXet')->nullable()->after('TrangThai');
            }
            if (!Schema::hasColumn('PhanCongPhanBien', 'KetQua')) {
                $table->string('KetQua', 50)->nullable()->after('NhanXet'); // 'Đạt', 'Yêu cầu chỉnh sửa', 'Không đạt'
            }
            if (!Schema::hasColumn('PhanCongPhanBien', 'NgayDanhGia')) {
                $table->dateTime('NgayDanhGia')->nullable()->after('KetQua');
            }
        });

        // 4. Bổ sung trường bản chỉnh sửa sau bảo vệ vào HoSoBaoVe
        Schema::table('HoSoBaoVe', function (Blueprint $table) {
            if (!Schema::hasColumn('HoSoBaoVe', 'FileBanChinhSua')) {
                $table->string('FileBanChinhSua', 500)->nullable()->after('PhongBaoVe');
            }
            if (!Schema::hasColumn('HoSoBaoVe', 'NgayNopBanChinhSua')) {
                $table->dateTime('NgayNopBanChinhSua')->nullable()->after('FileBanChinhSua');
            }
        });

        // 5. Chuẩn hóa trạng thái đề tài cũ sang luồng mới
        DB::table('DeTai')->where('TrangThai', 'Chờ duyệt')->update(['TrangThai' => 'Chờ duyệt cấp Bộ môn']);
        DB::table('DeTai')->where('TrangThai', 'Đã duyệt')->update(['TrangThai' => 'Trưởng khoa đã duyệt']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('HoSoBaoVe', function (Blueprint $table) {
            if (Schema::hasColumn('HoSoBaoVe', 'FileBanChinhSua')) {
                $table->dropColumn(['FileBanChinhSua', 'NgayNopBanChinhSua']);
            }
        });

        Schema::table('PhanCongPhanBien', function (Blueprint $table) {
            if (Schema::hasColumn('PhanCongPhanBien', 'NhanXet')) {
                $table->dropColumn(['NhanXet', 'KetQua', 'NgayDanhGia']);
            }
        });

        Schema::table('DeTai', function (Blueprint $table) {
            $cols = ['NgayDuyetBM', 'NguoiDuyetBM', 'NgayDuyetKhoa', 'NguoiDuyetKhoa', 'NgayCongBo'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('DeTai', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
