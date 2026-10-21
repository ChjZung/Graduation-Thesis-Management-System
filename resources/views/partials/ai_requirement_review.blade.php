@php
    $currentBrief = $reviewRound->nguCanhDoiChieuAi();
    $reviewStale = is_array($requirementsReview) && ($requirementsReview['context_hash'] ?? '') !== $reviewRound->maNguCanhDoiChieuAi();
    $reviewProcessingFailed = ($requirementsReview['status'] ?? '') === 'service_error' || ($requirementsReview['decision_status'] ?? '') === 'failed';
    $reviewInProgress = in_array($aiData['_processing']['status'] ?? '', ['queued','running','waiting']);
    $reviewStatusLabels = ['met' => 'Đã đáp ứng', 'partial' => 'Đáp ứng một phần', 'not_found' => 'Chưa tìm thấy trong PDF', 'insufficient_evidence' => 'Chưa đủ bằng chứng'];
@endphp
<div class="ai-review-block">
    <div class="ai-review-heading">
        <div><h3>Đối chiếu yêu cầu của giảng viên</h3><p>Kết quả hỗ trợ đối chiếu tài liệu; giảng viên quyết định đánh giá cuối cùng.</p></div>
        @if($reviewUrl)
            <form method="POST" action="{{ $reviewUrl }}" class="ai-review-submit">
                @csrf
                <button type="submit" class="ai-button ai-button-primary" data-ai-review-submit><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>{{ $requirementsReview ? 'Đối chiếu lại' : 'Đối chiếu yêu cầu' }}</button>
            </form>
        @endif
    </div>
    <div class="ai-review-brief">
        <dl><dt>Giai đoạn</dt><dd>{{ $currentBrief['stage_title'] ?: 'Chưa cập nhật' }}</dd><dt>Đợt nộp</dt><dd>{{ $currentBrief['round_title'] }}</dd></dl>
        @if($currentBrief['round_description'])<strong>Mô tả đợt nộp</strong><p>{{ $currentBrief['round_description'] }}</p>@endif
        <strong>Nội dung yêu cầu</strong><p>{{ $currentBrief['submission_requirements'] ?: ($currentBrief['stage_requirements'] ?: 'Giảng viên chưa nhập yêu cầu cụ thể.') }}</p>
    </div>
    @if($reviewStale)
        <div class="ai-review-note">Yêu cầu hoặc tên giai đoạn/đợt nộp đã thay đổi. Kết quả dưới đây dựa trên yêu cầu cũ; hãy chọn Đối chiếu lại trước khi sử dụng.</div>
    @endif
    @if(is_array($requirementsReview))
        @if(($requirementsReview['method'] ?? '') !== 'document_structure' && ($requirementsReview['schema_version'] ?? 1) < 2)
            <div class="ai-review-note">Kết quả này được tạo trước khi có bước kiểm chứng mức độ liên quan của trích dẫn. Chọn Đối chiếu lại để lọc bằng chứng trực tiếp cho từng yêu cầu.</div>
        @endif
        @if(($requirementsReview['method'] ?? '') === 'document_structure')
            <div class="ai-review-note">Đối chiếu cấu trúc tài liệu: tìm đúng phần đặc tả và kiểm tra từng mục bằng trích dẫn nguyên văn. Kết quả xác nhận nội dung có trong tài liệu, không đánh giá phần mềm chạy đúng.</div>
        @elseif(isset($requirementsReview['coverage']['batches']))
            <div class="ai-review-note">Dịch vụ AI đã xử lý {{ $requirementsReview['coverage']['analyzed_batches'] ?? 0 }}/{{ $requirementsReview['coverage']['batches'] }} phần văn bản.</div>
        @endif
        @if(isset($requirementsReview['coverage']['verified_candidates']))
            <div class="ai-review-note">Đã tìm {{ $requirementsReview['coverage']['verified_candidates'] }} đoạn trích khớp tài liệu. Bước kết luận đã trả kết quả cho {{ $requirementsReview['coverage']['decision_items'] ?? 0 }}/{{ count($requirementsReview['items'] ?? []) }} yêu cầu.</div>
        @endif
        @if(in_array($requirementsReview['citation_audit']['status'] ?? '', ['failed', 'partial']))
            <div class="ai-review-note">Bước kiểm chứng trích dẫn chưa hoàn tất cho một số yêu cầu. Các mục đó chưa được kết luận đáp ứng. {{ $reviewInProgress ? 'Hệ thống sẽ tự tiếp tục sau thời gian chờ.' : 'Chọn Đối chiếu lại để thử lại.' }} Đây không phải kết luận báo cáo thiếu nội dung.</div>
        @elseif(($requirementsReview['decision_status'] ?? '') === 'failed')
            <div class="ai-review-note">Đã tìm các đoạn PDF liên quan nhưng bước kết luận của dịch vụ AI chưa trả kết quả hợp lệ. Đây là lỗi xử lý, không phải kết luận báo cáo thiếu nội dung. Bạn có thể xem các đoạn đã tìm được bên dưới và chọn Đối chiếu lại.</div>
        @elseif(($requirementsReview['status'] ?? '') !== 'completed')
            <div class="ai-review-note">Việc đối chiếu chưa hoàn tất hoặc chưa đủ độ bao phủ. Những mục chưa có bằng chứng không đồng nghĩa sinh viên chưa thực hiện.</div>
        @endif
        @foreach(collect(array_merge($requirementsReview['errors'] ?? [], array_filter([$requirementsReview['citation_audit']['error'] ?? null])))->unique(fn ($e) => ($e['stage'] ?? '').'|'.($e['code'] ?? '')) as $reviewError)
            <div class="ai-review-note">{{ ($reviewError['stage'] ?? '') === 'citation_audit' ? 'Kiểm chứng trích dẫn: ' : '' }}{{ $reviewError['message'] ?? 'Dịch vụ AI chưa đối chiếu hoàn tất.' }}</div>
        @endforeach
        <p class="ai-review-summary">{{ $requirementsReview['summary'] ?? '' }}</p>
        <div class="ai-review-list">
            @foreach(($requirementsReview['items'] ?? []) as $requirement)
                @php $assessmentStatus = array_key_exists($requirement['status'] ?? '', $reviewStatusLabels) ? $requirement['status'] : 'insufficient_evidence'; @endphp
                <article class="ai-review-item">
                    <div class="ai-review-result"><h4>{{ $requirement['id'] ?? $loop->iteration }}. {{ $requirement['requirement'] ?? '' }}</h4><span class="ai-review-status ai-review-status-{{ $assessmentStatus }}" data-review-result="{{ $assessmentStatus }}">{{ $reviewInProgress && $assessmentStatus === 'insufficient_evidence' ? 'Đang chờ đối chiếu' : ($reviewProcessingFailed && $assessmentStatus === 'insufficient_evidence' ? 'Chưa đánh giá được · Lỗi xử lý' : $reviewStatusLabels[$assessmentStatus]) }}</span></div>
                    <p>{{ $requirement['explanation'] ?? '' }}</p>
                    @foreach(array_merge($requirement['evidence'] ?? [], $requirement['related_evidence'] ?? []) as $evidence)
                        @if(!empty($evidence['verified']))
                            @php $evidencePart = ['actor' => 'Tác nhân', 'precondition' => 'Tiền điều kiện', 'main_flow' => 'Luồng hoạt động chính'][$evidence['part'] ?? ''] ?? (!empty($evidence['relevance_verified']) ? 'Bằng chứng đã kiểm chứng' : 'Đoạn liên quan cần kiểm tra'); @endphp
                            <div class="ai-citation ai-review-evidence">
                                <div class="ai-citation-head">
                                    <strong>{{ $evidencePart }}</strong>
                                    @include('partials.ai_citation_button', ['citationPage' => $evidence['page'] ?? 0, 'citationQuote' => $evidence['quote'] ?? '', 'citationProminent' => true, 'citationContext' => ($requirement['requirement'] ?? '') . ' · ' . $evidencePart])
                                </div>
                                @if(!empty($evidence['relevance_verified']))
                                    <p class="ai-evidence-excerpt">“{{ \Illuminate\Support\Str::limit(preg_replace('/\s+/u', ' ', trim($evidence['quote'] ?? '')), 420) }}”</p>
                                @endif
                                @if(empty($evidence['relevance_verified']) && ($requirementsReview['method'] ?? '') !== 'document_structure')
                                    <p class="ai-evidence-hint">Đoạn này khớp văn bản nguồn nhưng chưa được xác nhận đủ để chứng minh yêu cầu.</p>
                                @endif
                                <p class="ai-evidence-hint">Bấm để mở trang nguồn; hệ thống chỉ tô vàng khi khớp chính xác đoạn trích.</p>
                                <details class="ai-evidence-transcript">
                                    <summary>Xem trích dẫn nguyên văn</summary>
                                    <blockquote>{{ $evidence['quote'] ?? '' }}</blockquote>
                                </details>
                            </div>
                        @endif
                    @endforeach
                </article>
            @endforeach
        </div>
    @else
        <div class="ai-empty">Chưa có kết quả đối chiếu cho phiên bản này. Chọn Đối chiếu yêu cầu để AI đọc PDF theo yêu cầu giảng viên; bản tóm tắt phía trên vẫn được giữ nguyên.</div>
    @endif
</div>
