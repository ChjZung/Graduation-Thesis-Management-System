<?php

namespace Tests\Feature;

use App\Models\{DeTai, GiangVien, HocKy, TaiKhoan};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DeanTopicApprovalBadgeTest extends TestCase
{
    use DatabaseTransactions;

    private string $faculty;
    private string $semester;
    private string $otherSemester;
    private GiangVien $teacher;
    private DeTai $template;

    protected function setUp(): void
    {
        parent::setUp();
        $user = TaiKhoan::whereHas('vaiTro', fn ($q) => $q->where('TenVaiTro', 'Trưởng khoa'))->firstOrFail();
        $user->update(['TenDangNhap' => 'TK_CNTT_BADGE_TEST', 'TrangThaiMatKhau' => 'ACTIVE', 'BatBuocDoiMatKhau' => false]);
        $this->actingAs($user);
        $this->faculty = 'CNTT';
        $this->teacher = GiangVien::whereHas('boMon', fn ($q) => $q->where('MaKhoa', $this->faculty))->firstOrFail();
        $this->template = DeTai::firstOrFail();
        $this->semester = 'HK_BADGE_A';
        $this->otherSemester = 'HK_BADGE_B';
        foreach ([$this->semester, $this->otherSemester] as $id) {
            $semester = HocKy::firstOrFail()->replicate();
            $semester->MaHocKy = $id;
            $semester->TrangThai = 'Đã kết thúc';
            $semester->save();
        }
    }

    private function topic(string $id, array $overrides = []): DeTai
    {
        $topic = $this->template->replicate();
        $topic->forceFill(array_merge([
            'MaDeTai' => $id, 'MaGV' => $this->teacher->MaGV,
            'MaHocKy' => $this->semester, 'TrangThai' => 'Chờ duyệt cấp Khoa',
            'NgayDuyetBM' => now(), 'NgayDuyetKhoa' => null,
        ], $overrides));
        $topic->save();
        return $topic;
    }

    private function badgeCount(string $semester, string $parameter = 'MaHocKy'): int
    {
        $response = $this->get(route('truongkhoa.duyet_detai.index', [$parameter => $semester]));
        $response->assertOk();
        $kpi = (int) $response->viewData('summaryKPIs')['cho_duyet_khoa'];
        $rows = collect($response->viewData('filteredBoMonRows'))->sum('cho_duyet_khoa');
        $this->assertSame($kpi, $rows);
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
        $xpath = new \DOMXPath($dom);
        $badges = $xpath->query('//a[contains(@href,"/truongkhoa/duyet-detai") and contains(.,"Phê duyệt đề tài")]//span[contains(@class,"badge")]');
        $count = $badges->length ? (int) trim($badges->item(0)->textContent) : 0;
        $this->assertSame($kpi, $count);
        return $count;
    }

    public function test_empty_queue_ignores_pending_topics_in_other_semesters(): void
    {
        $this->topic('BADGE_OTHER_HK', ['MaHocKy' => $this->otherSemester]);
        $this->assertSame(0, $this->badgeCount($this->semester));
        $this->assertSame(1, $this->badgeCount($this->otherSemester, 'hocKy'));
    }

    public function test_counts_only_department_approved_pending_topics_in_own_faculty(): void
    {
        $this->topic('BADGE_VALID_1');
        $this->topic('BADGE_VALID_2');
        $this->topic('BADGE_NO_BM', ['NgayDuyetBM' => null]);
        $this->topic('BADGE_PUBLISHED', ['TrangThai' => 'Đã công bố']);
        $other = GiangVien::whereHas('boMon', fn ($q) => $q->where('MaKhoa', '!=', $this->faculty))->firstOrFail();
        $this->topic('BADGE_OTHER_KHOA', ['MaGV' => $other->MaGV]);
        $this->assertSame(2, $this->badgeCount($this->semester));
    }

    public function test_badge_refreshes_when_topics_leave_the_pending_queue(): void
    {
        $approved = $this->topic('BADGE_APPROVE');
        $rejected = $this->topic('BADGE_REJECT');
        $edit = $this->topic('BADGE_EDIT');
        $this->assertSame(3, $this->badgeCount($this->semester));
        $approved->update(['TrangThai' => 'Đã công bố']);
        $this->assertSame(2, $this->badgeCount($this->semester));
        $rejected->update(['TrangThai' => 'Từ chối']);
        $this->assertSame(1, $this->badgeCount($this->semester));
        $edit->update(['TrangThai' => 'Yêu cầu chỉnh sửa']);
        $this->assertSame(0, $this->badgeCount($this->semester));
    }
}
