<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\GiangVien;
use App\Models\DeTai;
use App\Models\HocKy;

class TruongKhoaDuyetDeTaiTest extends TestCase
{
    /**
     * P3.2: Render 5 thẻ thống kê và danh sách bộ môn chuẩn admin
     */
    public function test_p32_render_5_the_thong_ke_va_bang_bo_mon(): void
    {
        $userTK = TaiKhoan::where('TenDangNhap', 'TK_CNTT_GV00000002')->first();
        if (!$userTK) {
            $this->markTestSkipped('TK_CNTT_GV00000002 not found in database');
        }

        $res = $this->actingAs($userTK)->get(route('truongkhoa.duyet_detai.index'));
        $res->assertStatus(200);

        // 1. Kiểm tra 5 thẻ thống kê P3.2
        $res->assertSee('Đề tài chờ duyệt cấp khoa');
        $res->assertSee('Tổng số bộ môn');
        $res->assertSee('Giảng viên đề xuất');
        $res->assertSee('Đề tài đã phê duyệt');
        $res->assertSee('Cần xem xét');

        // 2. Kiểm tra các cột bảng danh sách bộ môn
        $res->assertSee('Danh Sách Bộ Môn Trực Thuộc Khoa');
        $res->assertSee('Tên Bộ Môn');
        $res->assertSee('Giảng Viên Đề Xuất');
        $res->assertSee('Tổng Số Đề Tài');
        $res->assertSee('Chờ Duyệt Cấp Khoa');
        $res->assertSee('Tình Trạng Chỉ Tiêu');
        $res->assertSee('Danh sách đề tài');

        // 3. Kiểm tra các bộ môn thuộc CNTT xuất hiện
        $res->assertSee('Công nghệ phần mềm');
        $res->assertSee('An toàn thông tin');
        $res->assertSee('Hệ thống thông tin');
        $res->assertSee('Khoa học máy tính');
    }

    /**
     * P3.2: Tìm kiếm theo tên bộ môn hoặc mã bộ môn
     */
    public function test_p32_tim_kiem_theo_ten_bo_mon(): void
    {
        $userTK = TaiKhoan::where('TenDangNhap', 'TK_CNTT_GV00000002')->first();
        if (!$userTK) {
            $this->markTestSkipped('TK_CNTT_GV00000002 not found');
        }

        // Tìm theo tên "Công nghệ phần mềm"
        $res = $this->actingAs($userTK)->get(route('truongkhoa.duyet_detai.index', ['search' => 'phần mềm']));
        $res->assertStatus(200);
        $res->assertSee('Công nghệ phần mềm');
        $res->assertViewHas('filteredBoMonRows', function ($rows) {
            return $rows->count() === 1 && $rows->first()['MaBoMon'] === 'BM_CNPM';
        });

        // Tìm theo mã "BM_HTTT"
        $res2 = $this->actingAs($userTK)->get(route('truongkhoa.duyet_detai.index', ['search' => 'BM_HTTT']));
        $res2->assertStatus(200);
        $res2->assertSee('Hệ thống thông tin');
        $res2->assertViewHas('filteredBoMonRows', function ($rows) {
            return $rows->count() === 1 && $rows->first()['MaBoMon'] === 'BM_HTTT';
        });
    }

    /**
     * P3.2: Lọc theo tình trạng chỉ tiêu hướng dẫn
     */
    public function test_p32_loc_theo_tinh_trang_chi_tieu(): void
    {
        $userTK = TaiKhoan::where('TenDangNhap', 'TK_CNTT_GV00000002')->first();
        if (!$userTK) {
            $this->markTestSkipped('TK_CNTT_GV00000002 not found');
        }

        $res = $this->actingAs($userTK)->get(route('truongkhoa.duyet_detai.index', ['tinh_trang_chi_tieu' => 'con_chi_tieu']));
        $res->assertStatus(200);
        $res->assertSee('Còn chỉ tiêu');
    }

    /**
     * P3.2 -> P3.3: Nút danh sách đề tài mở trang đề tài bộ môn, chặn bộ môn ngoài khoa 403
     */
    public function test_p32_chuyen_trang_de_tai_bo_mon_va_chan_ngoai_khoa(): void
    {
        $userTK = TaiKhoan::where('TenDangNhap', 'TK_CNTT_GV00000002')->first();
        if (!$userTK) {
            $this->markTestSkipped('TK_CNTT_GV00000002 not found');
        }

        // Truy cập bộ môn của khoa mình: 200 OK
        $res = $this->actingAs($userTK)->get(route('truongkhoa.duyet_detai.bomon', 'BM_CNPM'));
        $res->assertStatus(200);
        $res->assertSee('Bộ Môn Công nghệ phần mềm');
        $res->assertSee('Quay lại danh sách bộ môn');

        // Giả lập truy cập bộ môn của khoa khác (nếu có)
        // Tạo hoặc tìm 1 bộ môn thuộc khoa khác
        $khoaKhac = Khoa::where('MaKhoa', '!=', 'CNTT')->first();
        if ($khoaKhac) {
            $bmKhac = BoMon::where('MaKhoa', $khoaKhac->MaKhoa)->first();
            if ($bmKhac) {
                $res403 = $this->actingAs($userTK)->get(route('truongkhoa.duyet_detai.bomon', $bmKhac->MaBoMon));
                $res403->assertStatus(403);
            }
        }
    }

    /**
     * P3.3: Trang danh sách đề tài bộ môn có đầy đủ các bộ lọc (TBM, TK, Chỉ tiêu, Tìm kiếm, Học kỳ)
     */
    public function test_p33_trang_de_tai_bo_mon_co_du_cac_bo_loc(): void
    {
        $userTK = TaiKhoan::where('TenDangNhap', 'TK_CNTT_GV00000002')->first();
        if (!$userTK) {
            $this->markTestSkipped('TK_CNTT_GV00000002 not found');
        }

        $res = $this->actingAs($userTK)->get(route('truongkhoa.duyet_detai.bomon', 'BM_CNPM'));
        $res->assertStatus(200);

        // Kiểm tra các trường bộ lọc P3.3
        $res->assertSee('name="search"', false);
        $res->assertSee('name="trang_thai_tbm"', false);
        $res->assertSee('name="trang_thai_tk"', false);
        $res->assertSee('name="tinh_trang_chi_tieu"', false);
        $res->assertSee('name="MaHocKy"', false);

        // Kiểm tra các cột bảng
        $res->assertSee('Mã Đề Tài');
        $res->assertSee('Tên Đề Tài &amp; Lĩnh Vực', false);
        $res->assertSee('Giảng Viên Hướng Dẫn');
        $res->assertSee('Phê Duyệt TBM');
        $res->assertSee('Phê Duyệt TK');
        $res->assertSee('Thao Tác');
        $res->assertSee('Quay lại danh sách bộ môn');
    }

    /**
     * P3.3: Lọc theo trạng thái phê duyệt của TBM và TK
     */
    public function test_p33_loc_theo_trang_thai_tbm_va_tk(): void
    {
        $userTK = TaiKhoan::where('TenDangNhap', 'TK_CNTT_GV00000002')->first();
        if (!$userTK) {
            $this->markTestSkipped('TK_CNTT_GV00000002 not found');
        }

        // Lọc trạng thái TK: Chờ duyệt cấp Khoa
        $res = $this->actingAs($userTK)->get(route('truongkhoa.duyet_detai.bomon', [
            'maBoMon' => 'BM_CNPM',
            'trang_thai_tk' => 'cho_duyet',
        ]));
        $res->assertStatus(200);
        $detais = $res->viewData('detais');
        foreach ($detais as $dt) {
            $this->assertEquals('Chờ duyệt cấp Khoa', $dt->TrangThai);
        }

        // Lọc trạng thái TBM: TBM đã duyệt
        $res2 = $this->actingAs($userTK)->get(route('truongkhoa.duyet_detai.bomon', [
            'maBoMon' => 'BM_CNPM',
            'trang_thai_tbm' => 'da_duyet',
        ]));
        $res2->assertStatus(200);
        $detais2 = $res2->viewData('detais');
        foreach ($detais2 as $dt) {
            $this->assertTrue(
                !empty($dt->NgayDuyetBM) || in_array($dt->TrangThai, ['Chờ duyệt cấp Khoa', 'Trưởng khoa đã duyệt', 'Đã công bố', 'Đã đăng ký', 'Hoàn thành'])
            );
        }
    }

    /**
     * P3.3: Duyệt đề tài xử lý xong chuyển hướng về đúng trang bộ môn và giữ bộ lọc qua return_url
     */
    public function test_p33_duyet_xu_ly_xong_chuyen_huong_ve_dung_trang_bo_mon(): void
    {
        $userTK = TaiKhoan::where('TenDangNhap', 'TK_CNTT_GV00000002')->first();
        if (!$userTK) {
            $this->markTestSkipped('TK_CNTT_GV00000002 not found');
        }

        // Lấy 1 đề tài đang Chờ duyệt cấp Khoa của BM_CNPM
        $detai = DeTai::whereHas('giangVien', fn($g) => $g->where('MaBoMon', 'BM_CNPM'))
            ->where('TrangThai', 'Chờ duyệt cấp Khoa')
            ->whereNotNull('NgayDuyetBM')
            ->first();

        if (!$detai) {
            $this->markTestSkipped('No topic ready for TK approval in BM_CNPM');
        }

        $origStatus = $detai->TrangThai;
        $returnUrl = route('truongkhoa.duyet_detai.bomon', [
            'maBoMon' => 'BM_CNPM',
            'trang_thai_tk' => 'cho_duyet',
            'search' => $detai->MaDeTai,
        ]);

        try {
            $res = $this->actingAs($userTK)->post(route('truongkhoa.duyet_detai.duyet', $detai->MaDeTai), [
                'return_url' => $returnUrl,
            ]);

            // Phải redirect về đúng $returnUrl kèm session success
            $res->assertRedirect($returnUrl);
            $res->assertSessionHas('success');
            $this->assertEquals('Đã công bố', $detai->fresh()->TrangThai);
        } finally {
            // Phục hồi lại trạng thái ban đầu của đề tài
            $detai->update([
                'TrangThai'      => $origStatus,
                'NgayDuyetKhoa'  => null,
                'NgayCongBo'     => null,
                'NguoiDuyetKhoa' => null,
            ]);
        }
    }
}
