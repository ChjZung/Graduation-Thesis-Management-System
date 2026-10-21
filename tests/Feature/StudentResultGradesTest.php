<?php
namespace Tests\Feature;

use App\Http\Controllers\SinhVien\KetQuaController;
use App\Models\{HoSoBaoVe, KetQuaSinhVien, PhieuChamDiem, SinhVien, TaiKhoan};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StudentResultGradesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_each_student_only_sees_their_own_four_council_grades_and_comments(): void
    {
        $dossier = HoSoBaoVe::findOrFail('HSBV_N09');
        $dossier->update(['TrangThaiKetQua' => 'DaCongBo']);
        $ids = ['2001210088', '2001210089', '2001210090'];
        // Force the page to calculate the fallback average from individual grade sheets.
        KetQuaSinhVien::whereIn('MaSV', $ids)->delete();
        foreach ($ids as $position => $id) {
            PhieuChamDiem::where('MaHoSo', $dossier->MaHoSo)->where('MaSV', $id)
                ->update(['Diem' => 6.0 + $position, 'NhanXet' => 'PRIVATE_REVIEW_'.$id,
                    'TrangThai' => 'Đã hoàn thành']);
        }
        foreach ($ids as $position => $id) {
            $student = SinhVien::findOrFail($id);
            $this->actingAs(TaiKhoan::findOrFail($student->MaTK));
            $view = app(KetQuaController::class)->index();
            $grades = $view->getData()['phieuChamDiems'];
            $this->assertCount(4, $grades);
            $this->assertSame([$id], $grades->pluck('MaSV')->unique()->values()->all());
            $this->assertCount(4, $grades->pluck('MaGV')->unique());
            $this->assertEquals(6.0 + $position, $grades->avg('Diem'));
            $html = $view->render();
            $this->assertSame(4, substr_count($html, 'PRIVATE_REVIEW_'.$id));
            foreach (array_diff($ids, [$id]) as $otherId) {
                $this->assertStringNotContainsString('PRIVATE_REVIEW_'.$otherId, $html);
            }
        }
    }

    public function test_unpublished_results_do_not_expose_grade_sheets(): void
    {
        $dossier = HoSoBaoVe::findOrFail('HSBV_N09');
        $dossier->update(['TrangThaiKetQua' => 'ChuaDuPhieu']);
        $student = SinhVien::findOrFail('2001210088');
        KetQuaSinhVien::where('MaSV', $student->MaSV)->delete();
        $this->actingAs(TaiKhoan::findOrFail($student->MaTK));
        $data = app(KetQuaController::class)->index()->getData();
        $this->assertFalse($data['daCongBoChinhThuc']);
        $this->assertCount(0, $data['phieuChamDiems']);
    }
}
