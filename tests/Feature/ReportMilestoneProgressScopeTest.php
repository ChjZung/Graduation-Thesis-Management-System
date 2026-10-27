<?php

namespace Tests\Feature;

use App\Models\KhoiBaoCaoTienDo;
use App\Models\Nhom;
use App\Models\PhieuDangKy;
use App\Services\ReportMilestoneProgressService;
use App\Services\ThesisProgressService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReportMilestoneProgressScopeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_latest_version_and_group_semester_binding_control_completion(): void
    {
        $registration = PhieuDangKy::whereIn('TrangThai',['Đã duyệt','da_duyet'])->whereHas('nhom')->whereHas('deTai')->firstOrFail();
        $group = $registration->nhom;
        $topic = $registration->deTai;
        if ((string) $group->MaHocKy !== (string) $topic->MaHocKy) $this->markTestSkipped('Fixture semester differs.');
        $service = app(ThesisProgressService::class);
        $stage = $service->taoGiaiDoan($topic, ['TenGiaiDoan'=>'Synthetic scope test']);
        $round = $service->taoDotNop($stage, ['TenDotNop'=>'Synthetic round']);
        $source = KhoiBaoCaoTienDo::firstOrFail();
        $make = function ($version, $status, $groupId, $semester) use ($source,$round,$topic,$stage) {
            $copy = $source->replicate(['id']);
            $copy->fill(['MaKhoi'=>'TEST_SCOPE_'.bin2hex(random_bytes(5)), 'MaDotNop'=>$round->MaDotNop,
                'MaGiaiDoan'=>$stage->MaGiaiDoan, 'MaDeTai'=>$topic->MaDeTai, 'LanNop'=>$version,
                'TrangThai'=>$status, 'NguoiNhanXet'=>'Fixture reviewer', 'NgayNhanXet'=>now(),
                'TomTatJson'=>['_submission_identity'=>['group_id'=>$groupId,'semester_id'=>$semester]]]);
            $copy->save();
            return $copy;
        };
        $make(1,'Đạt',$group->MaNhom,$group->MaHocKy);
        $new = $make(2,'Yêu cầu nộp lại',$group->MaNhom,$group->MaHocKy);
        $make(10,'Đạt','OTHER_GROUP',$group->MaHocKy);
        $make(11,'Đạt',$group->MaNhom,'OTHER_SEMESTER');
        $read = fn () => collect(app(ReportMilestoneProgressService::class)->forGroup($group,$topic)['stages'])
            ->firstWhere('id',$stage->MaGiaiDoan)['rounds'][0];
        $this->assertFalse($read()['completed']);
        $this->assertSame($new->MaKhoi,$read()['version_id']);
        $new->update(['TrangThai'=>'Đạt']);
        $this->assertTrue($read()['completed']);
        $this->assertSame(2,$read()['version']);
        $new->update(['NguoiNhanXet'=>null,'NgayNhanXet'=>null]);
        $this->assertFalse($read()['completed']);
    }
}
