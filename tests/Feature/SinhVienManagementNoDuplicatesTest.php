<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\SinhVien;
use App\Models\HocKy;
use App\Models\DanhSachSVDuDieuKien;

class SinhVienManagementNoDuplicatesTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = TaiKhoan::where('TenDangNhap', 'admin')->first();
    }

    /**
     * 1. Tab 1 (Danh mục SV): Đảm bảo danh sách sinh viên phân trang không có bất kỳ MaSV nào bị trùng lặp.
     */
    public function test_admin_sinhvien_tab_danhmuc_has_no_duplicate_students()
    {
        $response = $this->actingAs($this->admin)->get(route('sinhvien.index', ['tab' => 'danh_muc']));
        $response->assertStatus(200);

        $sinhviens = $response->viewData('sinhviens');
        $this->assertNotNull($sinhviens);

        $maSVs = $sinhviens->pluck('MaSV')->toArray();
        $uniqueMaSVs = array_unique($maSVs);

        $this->assertCount(count($uniqueMaSVs), $maSVs, 'Tab Danh Mục Sinh Viên bị duplicate MaSV trong kết quả phân trang!');
    }

    /**
     * 2. Tab 1 với bộ lọc Học kỳ (MaHocKy_filter): Đảm bảo không trùng dòng khi SV có nhiều nhóm hoặc bản ghi xét duyệt.
     */
    public function test_admin_sinhvien_filter_by_semester_has_no_duplicate_students()
    {
        $hk = HocKy::first();
        $this->assertNotNull($hk, 'Cần có ít nhất 1 học kỳ để test.');

        $response = $this->actingAs($this->admin)->get(route('sinhvien.index', [
            'tab' => 'danh_muc',
            'MaHocKy_filter' => $hk->MaHocKy,
        ]));
        $response->assertStatus(200);

        $sinhviens = $response->viewData('sinhviens');
        $this->assertNotNull($sinhviens);

        $maSVs = $sinhviens->pluck('MaSV')->toArray();
        $uniqueMaSVs = array_unique($maSVs);

        $this->assertCount(count($uniqueMaSVs), $maSVs, 'Lọc theo học kỳ bị duplicate MaSV khi sinh viên có liên kết nhóm hoặc xét duyệt!');
    }

    /**
     * 3. Tab 2 (Rà soát điều kiện theo học kỳ): Đảm bảo mỗi sinh viên chỉ xuất hiện đúng 1 lần trong học kỳ được chọn.
     */
    public function test_admin_sinhvien_tab_dudk_has_no_duplicate_records_for_semester()
    {
        $hk = HocKy::first();
        $this->assertNotNull($hk);

        $response = $this->actingAs($this->admin)->get(route('sinhvien.index', [
            'tab' => 'du_dieu_kien',
            'MaHocKy' => $hk->MaHocKy,
        ]));
        $response->assertStatus(200);

        $dsDuDieuKien = $response->viewData('dsDuDieuKien');
        $this->assertNotNull($dsDuDieuKien);

        $maSVs = $dsDuDieuKien->pluck('MaSV')->toArray();
        $uniqueMaSVs = array_unique($maSVs);

        $this->assertCount(count($uniqueMaSVs), $maSVs, 'Tab Rà Soát Điều Kiện bị trùng lặp sinh viên trong cùng một học kỳ!');
    }

    /**
     * 4. Thống kê KPI: Đảm bảo số lượng thống kê tính theo DISTINCT sinh viên.
     */
    public function test_stats_counters_match_distinct_counts()
    {
        $hk = HocKy::first();
        $response = $this->actingAs($this->admin)->get(route('sinhvien.index', [
            'tab' => 'du_dieu_kien',
            'MaHocKy' => $hk->MaHocKy,
        ]));
        $response->assertStatus(200);

        $statsSv = $response->viewData('statsSv');
        $statsDk = $response->viewData('statsDk');

        $expectedTotalSv = SinhVien::distinct('MaSV')->count('MaSV');
        $this->assertEquals($expectedTotalSv, $statsSv['total'], 'Tổng số SV phải bằng distinct count của MaSV.');

        $expectedTotalDk = DanhSachSVDuDieuKien::where('MaHocKy', $hk->MaHocKy)->distinct('MaSV')->count('MaSV');
        $this->assertEquals($expectedTotalDk, $statsDk['total_evaluated'], 'Tổng số SV xét duyệt học kỳ phải bằng distinct count của MaSV.');
    }

    /**
     * 5. Xuất Excel/CSV danh sách đủ điều kiện: Không được xuất trùng sinh viên.
     */
    public function test_dieu_kien_export_has_no_duplicate_students()
    {
        $hk = HocKy::whereHas('danhSachSVDuDieuKiens')->first() ?? HocKy::first();
        $response = $this->actingAs($this->admin)->get(route('admin.sinhvien.export_du_dk', [
            'MaHocKy' => $hk->MaHocKy,
        ]));
        $response->assertStatus(200);

        $content = $response->streamedContent();
        $lines = explode("\n", trim($content));
        $this->assertGreaterThan(1, count($lines), 'File CSV phải có dòng tiêu đề và dữ liệu.');

        // Bỏ header
        array_shift($lines);

        $exportedMssvs = [];
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            $cols = str_getcsv($line);
            // Cột 1 là STT, Cột 2 là MSSV
            if (isset($cols[1])) {
                $exportedMssvs[] = trim($cols[1]);
            }
        }

        $uniqueMssvs = array_unique($exportedMssvs);
        $this->assertCount(count($uniqueMssvs), $exportedMssvs, 'File CSV xuất ra bị trùng lặp MSSV!');
    }
}
