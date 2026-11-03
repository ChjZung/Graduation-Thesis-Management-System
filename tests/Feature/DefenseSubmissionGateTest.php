<?php

namespace Tests\Feature;

use App\Http\Controllers\GiangVien\DuyetBaoCaoController;
use App\Models\{DotNopBaoCao,Nhom,TaiKhoan};
use App\Services\ReportMilestoneProgressService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DefenseSubmissionGateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_direct_confirmation_is_blocked_before_any_records_change(): void
    {
        $this->assertStringStartsWith('ai_stability_', DB::connection()->getDatabaseName());
        $round = DotNopBaoCao::with('deTai.giangVien')->findOrFail(223);
        $group = Nhom::findOrFail('N_TEST737475');
        $this->actingAs(TaiKhoan::where('MaGV', $round->deTai->MaGV)->firstOrFail());
        $service = \Mockery::mock(ReportMilestoneProgressService::class);
        $service->shouldReceive('forGroup')->once()->andReturn(['can_open_defense_submission'=>false]);
        $this->app->instance(ReportMilestoneProgressService::class, $service);
        $before = $round->fresh()->getAttributes();
        try {
            app(DuyetBaoCaoController::class)->choPhepNopHoSoBaoVe(Request::create('/', 'POST'), $group->MaNhom);
            $this->fail('An incomplete group must not open defense submission.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('bao_cao', $exception->errors());
        }
        $this->assertSame($before, $round->fresh()->getAttributes());
    }
}
