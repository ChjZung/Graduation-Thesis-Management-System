<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\Khoa;
use App\Models\HocKy;
use App\Models\HoiDong;
use App\Models\KetQuaSinhVien;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TruongKhoaKetQuaFilterTest extends TestCase
{
    use DatabaseTransactions;

    protected $userTruongKhoa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userTruongKhoa = TaiKhoan::where('MaVaiTro', 'VT05')->first();
        if (!$this->userTruongKhoa) {
            $this->userTruongKhoa = TaiKhoan::whereHas('vaiTro', function($q) {
                $q->where('TenVaiTro', 'like', '%Trưởng khoa%');
            })->first();
        }
    }

    public function test_giao_dien_tab_hoidong_khong_con_nut_dat_lai_va_co_day_du_thanh_phan()
    {
        if (!$this->userTruongKhoa) {
            $this->markTestSkipped('Không tìm thấy tài khoản Trưởng khoa.');
        }

        $response = $this->actingAs($this->userTruongKhoa)->get(route('truongkhoa.ketqua.index', ['view' => 'hoidong']));
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Tuyệt đối không còn nút đặt lại hay icon fa-rotate
        $this->assertStringNotContainsString('fa-rotate', $content);
        $this->assertStringNotContainsString('title="Đặt lại"', $content);

        // 2. Có form flex-wrap không gây tràn mép
        $this->assertStringContainsString('d-flex flex-wrap gap-2 align-items-center', $content);
        $this->assertStringContainsString('id="filter-form"', $content);
        $this->assertStringContainsString('id="input-search"', $content);
        $this->assertStringNotContainsString('btn-clear-search', $content);
        $this->assertStringNotContainsString('fa-circle-xmark', $content);
        $this->assertStringContainsString('name="search_hd"', $content);
        $this->assertStringContainsString('name="MaHocKy"', $content);
        $this->assertStringContainsString('id="ketqua-result-container"', $content);
    }

    public function test_giao_dien_tab_toankhoa_khong_con_nut_dat_lai_va_co_day_du_thanh_phan()
    {
        if (!$this->userTruongKhoa) {
            $this->markTestSkipped('Không tìm thấy tài khoản Trưởng khoa.');
        }

        $response = $this->actingAs($this->userTruongKhoa)->get(route('truongkhoa.ketqua.index', ['view' => 'toankhoa']));
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Tuyệt đối không còn nút đặt lại hay icon fa-rotate
        $this->assertStringNotContainsString('fa-rotate', $content);
        $this->assertStringNotContainsString('title="Đặt lại"', $content);

        // 2. Có các trường lọc của tab toàn khoa
        $this->assertStringContainsString('name="search"', $content);
        $this->assertStringContainsString('name="MaBoMon"', $content);
        $this->assertStringContainsString('name="MaHocKy"', $content);
        $this->assertStringContainsString('name="xep_loai"', $content);
        $this->assertStringContainsString('id="ketqua-result-container"', $content);
    }

    public function test_ajax_filter_tab_hoidong_tra_ve_json_voi_html_va_stats()
    {
        if (!$this->userTruongKhoa) {
            $this->markTestSkipped('Không tìm thấy tài khoản Trưởng khoa.');
        }

        $response = $this->actingAs($this->userTruongKhoa)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('truongkhoa.ketqua.index', ['view' => 'hoidong', 'search_hd' => 'Hội đồng']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'viewMode',
            'html',
            'total',
            'stats' => ['total', 'xuat_sac', 'gioi', 'kha', 'trung_binh', 'khong_dat']
        ]);
        $this->assertEquals('hoidong', $response->json('viewMode'));
    }

    public function test_ajax_filter_tab_toankhoa_tra_ve_json_voi_html_va_stats()
    {
        if (!$this->userTruongKhoa) {
            $this->markTestSkipped('Không tìm thấy tài khoản Trưởng khoa.');
        }

        $response = $this->actingAs($this->userTruongKhoa)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('truongkhoa.ketqua.index', ['view' => 'toankhoa', 'search' => 'SV']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'viewMode',
            'html',
            'total',
            'stats' => ['total', 'xuat_sac', 'gioi', 'kha', 'trung_binh', 'khong_dat']
        ]);
        $this->assertEquals('toankhoa', $response->json('viewMode'));
    }

    public function test_empty_state_khi_khong_tim_thay_ket_qua()
    {
        if (!$this->userTruongKhoa) {
            $this->markTestSkipped('Không tìm thấy tài khoản Trưởng khoa.');
        }

        // Tab Hội Đồng
        $resHD = $this->actingAs($this->userTruongKhoa)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('truongkhoa.ketqua.index', [
                'view' => 'hoidong',
                'search_hd' => 'KhongTheNaoTimThay_XYZ_9999'
            ]));
        $resHD->assertStatus(200);
        $this->assertStringContainsString('Không tìm thấy kết quả phù hợp', $resHD->json('html'));

        // Tab Toàn Khoa
        $resTK = $this->actingAs($this->userTruongKhoa)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('truongkhoa.ketqua.index', [
                'view' => 'toankhoa',
                'search' => 'KhongTheNaoTimThay_XYZ_9999'
            ]));
        $resTK->assertStatus(200);
        $this->assertStringContainsString('Không tìm thấy kết quả phù hợp', $resTK->json('html'));
    }

    public function test_tim_kiem_chuoi_dai_va_ky_tu_dac_biet_like()
    {
        if (!$this->userTruongKhoa) {
            $this->markTestSkipped('Không tìm thấy tài khoản Trưởng khoa.');
        }

        $longStringWithWildcards = str_repeat('abc%_123', 20); // 160 ký tự có % và _
        $response = $this->actingAs($this->userTruongKhoa)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('truongkhoa.ketqua.index', [
                'view' => 'toankhoa',
                'search' => $longStringWithWildcards
            ]));

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
    }

    public function test_chuyen_tab_giu_lai_ma_hoc_ky_hop_le()
    {
        if (!$this->userTruongKhoa) {
            $this->markTestSkipped('Không tìm thấy tài khoản Trưởng khoa.');
        }

        $response = $this->actingAs($this->userTruongKhoa)
            ->get(route('truongkhoa.ketqua.index', [
                'view' => 'hoidong',
                'MaHocKy' => 'HK2627_1',
                'search_hd' => 'HoiDong1'
            ]));

        $content = $response->getContent();
        // Kiểm tra liên kết chuyển sang Tab 2 có giữ MaHocKy nhưng không mang search_hd
        $this->assertStringContainsString('view=toankhoa', $content);
        $this->assertStringContainsString('MaHocKy=HK2627_1', $content);
        $this->assertStringNotContainsString('view=toankhoa&amp;search_hd=HoiDong1', $content);
    }

    public function test_o_tim_kiem_chua_cho_cho_icon_kinh_lup_va_khong_con_nut_x()
    {
        if (!$this->userTruongKhoa) {
            $this->markTestSkipped('Không tìm thấy tài khoản Trưởng khoa.');
        }

        $response = $this->actingAs($this->userTruongKhoa)->get(route('truongkhoa.ketqua.index', ['view' => 'hoidong']));
        $content = $response->getContent();

        // 1. Icon kính lúp có pointer-events: none và position absolute
        $this->assertStringContainsString('fa-magnifying-glass', $content);
        $this->assertStringContainsString('pointer-events: none', $content);

        // 2. Ô input có padding-left 2.75rem chống đè chữ
        $this->assertStringContainsString('padding-left: 2.75rem !important', $content);

        // 3. Tuyệt đối không còn nút x custom nào
        $this->assertStringNotContainsString('btn-clear-search', $content);
        $this->assertStringNotContainsString('fa-circle-xmark', $content);

        // 4. Đồng bộ chiều cao 42px giữa input, select và nút lọc
        $this->assertStringContainsString('min-height: 42px !important', $content);
    }
}
