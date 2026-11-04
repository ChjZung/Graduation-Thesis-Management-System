<?php
namespace Tests\Feature;

use App\Models\{HoiDong, HocKy, TaiKhoan};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminCouncilExportTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $user = TaiKhoan::whereHas('vaiTro', fn ($q) => $q->whereIn('TenVaiTro', ['Admin', 'Giáo vụ']))->firstOrFail();
        $user->update(['TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->actingAs($user);
    }

    public function test_styled_xlsx_is_complete_and_uses_the_same_filters_as_index(): void
    {
        $semester = HocKy::firstOrFail()->replicate();
        $semester->MaHocKy = 'HK_CSV_TEST';
        $semester->save();
        $ids = [];
        for ($i = 0; $i < 8; $i++) {
            $row = HoiDong::firstOrFail()->replicate();
            $row->forceFill(['MaHoiDong' => 'HD_CSV_'.bin2hex(random_bytes(4)), 'MaHocKy' => $semester->MaHocKy,
                'DotBaoVe' => 'Đợt CSV', 'TenHoiDong' => $i === 0 ? '=HYPERLINK("test")' : 'Hội đồng tiếng Việt '.str_repeat('Tên dài kiểm thử ', 5).$i,
                'TrangThaiCongBo' => $i === 7 ? 'Đã công bố' : 'Chưa công bố', 'TrangThai' => 'Chưa diễn ra',
                'NgayBaoVe' => '2026-11-10', 'DiaDiem' => "Phòng A, lầu 2\nKhu B"])->save();
            $ids[] = $row->MaHoiDong;
        }
        $scope = ['MaHocKy' => $semester->MaHocKy, 'DotBaoVe' => 'Đợt CSV', 'TrangThaiCongBo' => 'Chưa công bố', 'NgayBaoVe' => '2026-11-10', 'TrangThai' => 'Chưa diễn ra'];
        $index = $this->get(route('admin.hoidong.index', $scope))->assertOk()->assertSee('Xuất báo cáo Excel');
        $this->assertSame(7, $index->viewData('hoiDongs')->total());
        $response = $this->get(route('admin.hoidong.exportReport', $scope))->assertOk()->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', $response->headers->get('Content-Disposition'));
        $bytes = $response->streamedContent();
        $this->assertStringStartsWith('PK', $bytes);
        $rows = app(\App\Http\Controllers\Admin\HoiDongController::class);
        $councils = HoiDong::whereIn('MaHoiDong', array_slice($ids, 0, 7))->with(['thanhViens.giangVien', 'hoSoBaoVes.nhom.thanhVienNhoms', 'hocKy'])->orderBy('MaHoiDong')->get();
        $book = app(\App\Services\CouncilListExcelService::class)->build($councils, 'Học kỳ kiểm thử', 'Đợt CSV');
        $sheet = $book->getActiveSheet();
        $this->assertSame('STT', $sheet->getCell('A8')->getValue());
        $this->assertSame(7, $councils->count());
        $this->assertSame('003366', $sheet->getStyle('A8')->getFill()->getStartColor()->getRGB());
        $this->assertSame('D9', $sheet->getFreezePane());
        foreach ($councils as $i => $council) {
            $row = $i + 9;
            $this->assertSame($council->TenHoiDong, $sheet->getCell('C'.$row)->getValue());
            $this->assertSame('s', $sheet->getCell('C'.$row)->getDataType());
            $this->assertSame("Phòng A, lầu 2\nKhu B", $sheet->getCell('H'.$row)->getValue());
            $this->assertTrue($sheet->getStyle('C'.$row)->getAlignment()->getWrapText());
            $this->assertGreaterThanOrEqual(57, $sheet->getRowDimension($row)->getRowHeight());
        }
        file_put_contents('C:/Users/hochidung/Documents/Codex/2026-10-25/b-n-v-o-c-laragon/work/admin-council-export.xlsx', $bytes);
        $search = $this->get(route('admin.hoidong.exportReport', $scope + ['search' => $ids[2]]))->assertOk()->streamedContent();
        $this->assertStringStartsWith('PK', $search);
    }
}
