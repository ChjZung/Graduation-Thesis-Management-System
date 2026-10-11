<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\SinhVien;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\DeTai;
use App\Models\GiangVien;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SinhVienDangKyDeTaiFilterTest extends TestCase
{
    use DatabaseTransactions;

    protected $userNoGroup;
    protected $svNoGroup;
    protected $userWithGroup;
    protected $svWithGroup;
    protected $nhom;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. User without group
        $this->userNoGroup = TaiKhoan::where('TenDangNhap', '2001210001')->firstOrFail();
        $this->userNoGroup->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->svNoGroup = SinhVien::where('MaTK', $this->userNoGroup->MaTK)->firstOrFail();

        // Ensure userNoGroup has no group
        ThanhVienNhom::where('MaSV', $this->svNoGroup->MaSV)->delete();

        // 2. User with group
        $this->userWithGroup = TaiKhoan::where('TenDangNhap', '2001210044')->firstOrFail();
        $this->userWithGroup->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->svWithGroup = SinhVien::where('MaTK', $this->userWithGroup->MaTK)->firstOrFail();
        
        $tvRecord = ThanhVienNhom::where('MaSV', $this->svWithGroup->MaSV)->where('TrangThai', 'da_tham_gia')->first();
        if (!$tvRecord) {
            $this->nhom = Nhom::create([
                'MaNhom' => 'N_TEST_B2',
                'TenNhom' => 'Nhóm Test B2',
                'MaTruongNhom' => $this->svWithGroup->MaSV,
                'MaHocKy' => 'HK2627_2',
                'MaHocPhan' => 'HP_KLCN_CNPM',
                'TrangThai' => 'Đang hoạt động',
            ]);
            ThanhVienNhom::create([
                'MaNhom' => $this->nhom->MaNhom,
                'MaSV' => $this->svWithGroup->MaSV,
                'VaiTro' => 'Trưởng nhóm',
                'TrangThai' => 'da_tham_gia',
                'NgayGiaNhap' => now(),
            ]);
        } else {
            $this->nhom = Nhom::with(['hocPhan', 'hocKy'])->find($tvRecord->MaNhom);
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_student_without_group_can_freely_filter_semesters_and_courses()
    {
        $response = $this->actingAs($this->userNoGroup)
            ->get(route('sinhvien.dangky.index', [
                'HocKy' => 'HK2627_1',
                'HocPhan' => 'HP_KLTN_CNPM',
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('isGroupLocked', false);
        $response->assertViewHas('selectedHocKy', 'HK2627_1');
        $response->assertViewHas('selectedHocPhan', 'HP_KLTN_CNPM');
        $response->assertDontSee('Đã khóa bộ lọc theo nhóm:');
    }

    public function test_student_with_group_has_filters_locked_to_group_semester_and_course()
    {
        // Try passing different semester and course via query string
        $response = $this->actingAs($this->userWithGroup)
            ->get(route('sinhvien.dangky.index', [
                'HocKy' => 'HK2627_1',
                'HocPhan' => 'HP_KLTN_ATTT',
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('isGroupLocked', true);
        
        // Filter must remain locked to the group's semester and course!
        $response->assertViewHas('selectedHocKy', $this->nhom->MaHocKy);
        $response->assertViewHas('selectedHocPhan', $this->nhom->MaHocPhan);

        // UI indicators
        $response->assertSee('Đã khóa bộ lọc theo nhóm:');
        $response->assertSee($this->nhom->TenNhom);
        $response->assertSee('(Khóa theo nhóm)');
    }

    public function test_cannot_register_topic_of_incompatible_course_or_semester()
    {
        // Set time inside registration phase so registration window check passes
        Carbon::setTestNow('2026-08-11 10:00:00');

        // Ensure group has 3 members
        $currentCount = ThanhVienNhom::where('MaNhom', $this->nhom->MaNhom)->where('TrangThai', 'da_tham_gia')->count();
        if ($currentCount < 3) {
            $otherSvs = SinhVien::where('MaSV', '!=', $this->svWithGroup->MaSV)->take(3 - $currentCount)->get();
            foreach ($otherSvs as $osv) {
                ThanhVienNhom::firstOrCreate([
                    'MaNhom' => $this->nhom->MaNhom,
                    'MaSV' => $osv->MaSV,
                ], [
                    'VaiTro' => 'Thành viên',
                    'TrangThai' => 'da_tham_gia',
                    'NgayGiaNhap' => now(),
                ]);
            }
        }

        // Create a published topic for an incompatible course
        $gv = GiangVien::first();
        $incompatibleDeTai = DeTai::create([
            'MaDeTai' => 'DT_INCOMPAT_' . rand(1000, 9999),
            'TenDeTai' => 'Đề tài khác học phần B2',
            'MaHocKy' => $this->nhom->MaHocKy,
            'MaHocPhan' => 'HP_KLTN_ATTT', // Incompatible with HP_KLCN_CNPM
            'MaGV' => $gv?->MaGV,
            'TrangThai' => 'Đã công bố',
            'SoLuongSinhVienToiDa' => 3,
        ]);

        $response = $this->actingAs($this->userWithGroup)
            ->post(route('sinhvien.dangky.store'), [
                'MaDeTai' => $incompatibleDeTai->MaDeTai,
            ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('PhieuDangKy', [
            'MaNhom' => $this->nhom->MaNhom,
            'MaDeTai' => $incompatibleDeTai->MaDeTai,
        ]);
    }
}
