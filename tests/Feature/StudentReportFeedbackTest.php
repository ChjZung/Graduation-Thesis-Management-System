<?php
namespace Tests\Feature;
use Tests\TestCase;
class StudentReportFeedbackTest extends TestCase
{
    private function renderFeedback(string $status='Yêu cầu nộp lại',bool $leader=true,string $comment='Bổ sung phần kiểm thử.'): string
    {
        return view('partials.student_report_feedback',[
            'latestPhienBan'=>(object)['TrangThai'=>$status,'LanNop'=>2,'NhanXetGV'=>$comment,'NguoiNhanXet'=>'GV hướng dẫn','NgayNhanXet'=>'2026-11-03 18:30:00'],
            'isLeader'=>$leader,
        ])->render();
    }
    public function test_resubmission_feedback_is_visible_and_links_to_new_version_form(): void
    {
        $html=$this->renderFeedback();
        $this->assertStringContainsString('data-student-resubmission-notice',$html);
        $this->assertStringContainsString('yêu cầu nộp lại báo cáo v2',$html);
        $this->assertStringContainsString('Bổ sung phần kiểm thử.',$html);
        $this->assertStringContainsString('href="#student-report-resubmission-form"',$html);
        $this->assertStringContainsString('phiên bản v3',$html);
    }
    public function test_latest_resubmission_or_approved_report_does_not_keep_old_warning(): void
    {
        foreach(['Chờ duyệt','Đạt'] as $status)$this->assertStringNotContainsString('data-student-resubmission-notice',$this->renderFeedback($status));
        $this->assertStringNotContainsString('data-student-resubmission-notice',view('partials.student_report_feedback',['latestPhienBan'=>null])->render());
    }
    public function test_group_member_sees_feedback_without_leader_submission_action(): void
    {
        $html=$this->renderFeedback('Cần bổ sung',false);
        $this->assertStringContainsString('Bổ sung phần kiểm thử.',$html);
        $this->assertStringContainsString('Nhóm trưởng thực hiện',$html);
        $this->assertStringNotContainsString('href="#student-report-resubmission-form"',$html);
    }
    public function test_comment_is_escaped_and_empty_comment_has_clear_fallback(): void
    {
        $html=$this->renderFeedback('Yêu cầu nộp lại',true,'<script>alert(1)</script>');
        $this->assertStringNotContainsString('<script>alert(1)</script>',$html);
        $this->assertStringContainsString('&lt;script&gt;',$html);
        $this->assertStringContainsString('liên hệ giảng viên',$this->renderFeedback('Yêu cầu nộp lại',true,''));
    }
}
