@once('ai-report-mobile-styles')
<link rel="stylesheet" href="{{ asset('css/ai_report_mobile.css') }}?v={{ filemtime(public_path('css/ai_report_mobile.css')) }}">
@endonce
@php
    $coverageData=$aiData;
    if(isset($reviewRound) && $reviewRound && ($aiData['summary_scope']['context_hash']??'')!==$reviewRound->maNguCanhDoiChieuAi()) $coverageData['requirements_binding_status']='stale';
    $coverage=\App\Support\AiRequirementCoverage::calculate($coverageData);
    $hasVerifiedCoverage = $coverage['available'] && $coverage['found'] > 0;
    $coverageLabel = $hasVerifiedCoverage ? number_format($coverage['percent'], 2, ',', '.').'%' : ($coverage['available'] ? 'Chưa xác minh được dẫn chứng' : 'Chưa có kết quả');
@endphp
@php
    $coverageKey=$coverageReportKey ?? ($pb->MaKhoi ?? '');
    $coverageVersionNumber=$coverageVersion ?? ($pb->LanNop ?? null);
    $coverageSidebar=$coverageSidebar ?? false;
    $coverageMetric=$coverageMetric??false;
    $coverageTemplateOnly=($coverageSourceOnly??false) || (!$coverageSidebar && !$coverageMetric && request()->routeIs('sinhvien.baocao.dot_nop.show','sinhvien.baocao.show_dot'));
@endphp
@if($coverageTemplateOnly)<template data-ai-coverage-widget-source>@endif
<div class="{{ $coverageMetric ? 'card h-100 ai-metric-requirement-rate' : '' }}" data-ai-coverage-widget @if($coverageMetric) data-ai-requirement-coverage @endif data-report-key="{{ $coverageKey }}" data-report-version="{{ $coverageVersionNumber }}">
    @if($coverageMetric)
        <div class="card-body">
            <i class="fa-solid fa-percent" aria-hidden="true"></i>
            <h4>Tỷ lệ yêu cầu có dẫn chứng @if($coverageVersionNumber)(v{{ $coverageVersionNumber }})@endif</h4>
            <strong data-ai-coverage-percent>{{ $coverageLabel }}</strong>
            <p>{{ $coverage['available'] ? $coverage['found'].'/'.$coverage['leaves'].' mục có nguồn đã xác minh.' : 'Chưa đủ dữ liệu để tính tỷ lệ.' }}</p>
            <button type="button" class="ai-metric-action" data-ai-open-requirements @if(!$coverageKey) disabled @endif>Xem yêu cầu được đối chiếu</button>
            <button type="button" class="ai-metric-action" data-ai-coverage-rules>Thể lệ ước tính tỷ lệ <i class="fa-solid fa-circle-info" aria-hidden="true"></i></button>
        </div>
    @else
    <section class="info-callout info-callout-neutral mb-2" data-ai-requirement-coverage>
        <div class="callout-title text-primary fw-bold"><i class="fa-solid fa-calculator me-1" aria-hidden="true"></i> Tỷ lệ yêu cầu có dẫn chứng được AI tìm thấy @if($coverageVersionNumber) <span class="ai-coverage-version">(v{{ $coverageVersionNumber }})</span>@endif:</div>
        <div class="callout-body ai-coverage-body">
            @if($coverage['available'])
                <div class="ai-coverage-value-row">
                    <strong class="text-primary" style="font-size:2rem" data-ai-coverage-percent>{{ $coverageLabel }}</strong>
                    @if($coverage['provisional'])
                        <span class="ai-coverage-status" title="Một số tiêu chí chưa xác minh được nguồn. Xem chi tiết trong thể lệ."><span class="ai-coverage-status-dot" aria-hidden="true"></span>{{ in_array($coverageData['_processing']['status']??'', ['queued','running','waiting']) ? 'Đang cập nhật' : 'Cần đối chiếu lại với báo cáo gốc' }}</span>
                    @endif
                </div>
            @else
                <div class="ai-coverage-empty" data-ai-coverage-percent>Chưa có kết quả</div>
            @endif
            <button type="button" class="btn btn-outline-primary ai-coverage-requirements-button" data-ai-open-requirements @if(!$coverageKey) disabled @endif><i class="fa-solid fa-arrow-up-right-from-square me-1" aria-hidden="true"></i><span>Xem yêu cầu của giảng viên được đối chiếu</span></button>
        </div>
    </section>
    <button type="button" class="btn btn-link btn-sm px-0 mb-3" data-ai-coverage-rules><i class="fa-solid fa-circle-info me-1" aria-hidden="true"></i>Thể lệ ước tính tỷ lệ</button>
    @endif
    <template data-ai-coverage-rules-content>
        @if($coverageVersionNumber)<p class="small text-muted">Phiên bản báo cáo v{{ $coverageVersionNumber }}</p>@endif
        @if($coverage['available'])
            <p><strong>{{ $coverageLabel }}</strong> · {{ $coverage['found'] }}/{{ $coverage['leaves'] }} mục có nguồn đã kiểm chứng vị trí.</p>
            @if($coverage['provisional'])<p class="text-warning-emphasis">Một số tiêu chí chưa xác minh được nguồn. Tỷ lệ này có thể thay đổi khi đối chiếu bổ sung.</p>@endif
        <p class="small mb-2">Chia đều cho {{ $coverage['roots'] }} yêu cầu lớn; mục cha chia đều trọng số cho các mục con ghi rõ trong yêu cầu gốc. Không cộng trùng mục cha.</p>

            <p class="small text-muted d-md-none">Vuốt ngang bảng để xem đầy đủ các cột.</p>
            <div class="table-responsive mt-2" tabindex="0" role="region" aria-label="Bảng trọng số yêu cầu; có thể cuộn ngang"><table class="table table-sm mb-0"><thead><tr><th>Yêu cầu</th><th>Trọng số</th><th>Dẫn chứng</th><th>Đóng góp</th></tr></thead><tbody>
            @foreach($coverage['rows'] as $row)
                <tr><td style="padding-left:{{ min($row['depth'],6)*16+8 }}px">{{ $row['label'] }}</td><td>{{ number_format($row['weight'],2,',','.') }}%</td><td>{{ $row['found']===null?'Tổng hợp từ mục con':($row['found']?($row['caption_only']?'Có tên hình/bảng — cần xem PDF':'Có dẫn chứng'):'Chưa tìm được / chưa liên kết được') }}</td><td>{{ $row['found']===null?'—':($row['found'] ? number_format($row['weight'],2,',','.') .'%' : '—') }}</td></tr>
            @endforeach
            </tbody></table></div>

        @if($coverage['caption_only'])<p class="small mt-2 mb-0">Có {{ $coverage['caption_only'] }} mục chỉ tìm được tên hình/bảng; giảng viên cần kiểm chứng nội dung trong PDF gốc.</p>@endif
        @else
            <p>Chưa đủ dữ liệu đúng ngữ cảnh để tính tỷ lệ. Hãy hoàn tất đối chiếu yêu cầu hiện tại.</p>
            <p>Các yêu cầu lớn chia đều 100%; mục cha chia đều trọng số cho các mục con ghi rõ trong yêu cầu gốc. Chỉ cộng phần đóng góp của mục cuối cùng, không cộng trùng mục cha.</p>
        @endif
    <p class="small mt-2 mb-0"><strong>Lưu ý:</strong> Đây là tỷ lệ tìm thấy dẫn chứng, không phải tỷ lệ hoàn thành công việc, điểm đánh giá hoặc độ chính xác AI. Không tìm thấy dẫn chứng không đồng nghĩa sinh viên chưa thực hiện. Các mục con chỉ được tính khi liên kết đúng tên mục; AI không tự chia trọng số theo các ý do mô hình sinh ra.</p>
    </template>
</div>
@if($coverageTemplateOnly)</template>@endif
