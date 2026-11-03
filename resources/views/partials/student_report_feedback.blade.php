@php
    $feedbackVersion=$latestPhienBan??null;
    $needsResubmission=$feedbackVersion && in_array($feedbackVersion->TrangThai,['Yêu cầu nộp lại','Cần bổ sung'],true);
@endphp
@if($needsResubmission)
    @once('ai-report-mobile-styles')
        <link rel="stylesheet" href="{{ asset('css/ai_report_mobile.css') }}?v={{ filemtime(public_path('css/ai_report_mobile.css')) }}">
    @endonce
    <section class="student-report-feedback mb-4" role="region" aria-label="Yêu cầu chỉnh sửa của giảng viên" data-student-resubmission-notice>
        <div class="student-report-feedback-heading">
            <span class="student-report-feedback-icon" aria-hidden="true"><i class="fa-solid fa-comment-dots"></i></span>
            <div class="flex-grow-1" style="min-width:0">
                <span class="student-report-feedback-label">CẦN CHỈNH SỬA</span>
                <h2>Giảng viên yêu cầu nộp lại báo cáo v{{ $feedbackVersion->LanNop }}</h2>
                <p class="student-report-feedback-meta">{{ $feedbackVersion->NguoiNhanXet ?: 'Giảng viên hướng dẫn' }}@if($feedbackVersion->NgayNhanXet) · {{ \Carbon\Carbon::parse($feedbackVersion->NgayNhanXet)->format('H:i d/m/Y') }}@endif</p>
            </div>
        </div>
        <div class="student-report-feedback-comment"><strong>Góp ý của giảng viên</strong><p>{{ trim((string)$feedbackVersion->NhanXetGV) !== '' ? $feedbackVersion->NhanXetGV : 'Vui lòng liên hệ giảng viên để làm rõ nội dung cần bổ sung trước khi nộp lại.' }}</p></div>
        <div class="student-report-feedback-actions">
            @if($isLeader ?? true)
                <a href="#student-report-resubmission-form" class="btn btn-primary"><i class="fa-solid fa-file-arrow-up me-2" aria-hidden="true"></i>Chỉnh sửa và nộp bản mới</a>
                <span>Bản mới sẽ được lưu thành phiên bản v{{ (int)$feedbackVersion->LanNop+1 }}.</span>
            @else
                <span>Nhóm trưởng thực hiện nộp phiên bản mới sau khi nhóm chỉnh sửa báo cáo.</span>
            @endif
        </div>
    </section>
@endif
