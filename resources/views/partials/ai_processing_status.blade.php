@php
    $processingState = $aiData['_processing'] ?? [];
    $reviewHasResults=(int)($aiData['requirements_review']['processing']['completed_checks']??0)>0;
    $processingActive = in_array($processingState['status'] ?? '', ['queued','running','waiting']);
    $processingUrl = $citationAvailable ? route('baocao.ai.trang_thai', $pb->MaKhoi) : '';
    $stepLabels = ['summary_part'=>'Đọc từng phần báo cáo','summary_audit'=>'Kiểm chứng ý tóm tắt','completion_audit'=>'Kiểm tra căn cứ công việc hoàn thành','summary_merge'=>'Tổng hợp chương và mục','review_part'=>'Tìm bằng chứng theo yêu cầu','review_repair'=>'Kiểm tra lại tiêu chí lỗi','review_decision'=>'Đối chiếu yêu cầu','citation_audit'=>'Kiểm chứng trích dẫn'];
@endphp
@if($teacherReportUi ?? false)
    @if($processingUrl)
    <div class="ai-processing-status" role="status" aria-live="polite" data-result-revision="{{ app(\App\Services\AiReportProcessingService::class)->resultRevision($aiData) ?? '' }}" data-ai-processing-url="{{ $processingUrl }}" data-processing-active="{{ $processingActive ? '1' : '0' }}" data-processing-started="{{ $processingState['queued_at'] ?? '' }}" data-processing-status="{{ $processingState['status'] ?? ($analysisComplete ? 'completed' : 'idle') }}" data-summary-status="{{ $aiData['analysis']['status'] ?? '' }}" data-review-status="{{ $aiData['requirements_review']['status'] ?? '' }}" data-review-has-results="{{ $reviewHasResults ? '1' : '0' }}" data-processing-updated="{{ $processingState['updated_at'] ?? 0 }}">
        <strong data-processing-title>{{ $processingActive ? 'Đang phân tích báo cáo' : (($processingState['status'] ?? '') === 'completed' ? 'Đã có kết quả phân tích' : 'Trạng thái phân tích') }}</strong>
        <p data-processing-message>{{ $processingActive ? 'Bạn có thể tiếp tục sử dụng trang trong khi chờ.' : 'Kết quả đã lưu được hiển thị bên dưới.' }}</p>
        <div class="ai-result-status-lines">
            <p data-processing-summary-result><strong>Tóm tắt báo cáo:</strong> {{ ($aiData['analysis']['status']??'')==='completed' ? 'Đã hoàn thành.' : 'Chưa hoàn tất.' }}</p>
            <p data-processing-review-result><strong>Đối chiếu yêu cầu:</strong> {{ ($aiData['requirements_review']['status']??'')==='completed' ? 'Đã có kết quả — giảng viên cần kiểm tra lại.' : (($aiData['requirements_review']['status']??'')==='partial' ? ($processingActive ? 'Đang đối chiếu yêu cầu.' : ($reviewHasResults ? 'Có kết quả tham khảo — giảng viên vui lòng kiểm tra dẫn chứng.' : 'Chưa có kết quả để đối chiếu.')) : 'Chưa có kết quả hoàn tất.') }}</p>
        </div>
        <p data-processing-step></p>
        <small class="ai-processing-wait-note">Thời gian chờ tóm tắt có thể kéo dài từ 3 đến 5 phút hoặc lâu hơn, tùy thuộc vào kích thước của file.</small>
        <button type="button" class="ai-button" data-open-ai-progress @if(!$processingActive) hidden @endif>Xem tiến độ</button>
        <button type="button" class="ai-button" data-ai-refresh-result hidden>Xem kết quả mới</button>
    </div>
    @endif
@else
@if($processingUrl && $processingState)
<div class="ai-processing-status" role="status" aria-live="polite" data-result-revision="{{ app(\App\Services\AiReportProcessingService::class)->resultRevision($aiData) ?? '' }}" data-ai-processing-url="{{ $processingUrl }}" data-processing-active="{{ $processingActive ? '1' : '0' }}" data-summary-status="{{ $aiData['analysis']['status'] ?? '' }}" data-review-status="{{ $aiData['requirements_review']['status'] ?? '' }}" data-review-has-results="{{ $reviewHasResults ? '1' : '0' }}" data-processing-updated="{{ $processingState['updated_at'] ?? 0 }}">
    <strong data-processing-title>{{ ['queued'=>'Đang chờ xử lý','running'=>'Đang phân tích','waiting'=>'Đang chờ thử tiếp','completed'=>'Đã hoàn tất','partial'=>'Hoàn tất một phần'][$processingState['status'] ?? ''] ?? 'Trạng thái AI' }}</strong>
    <p data-processing-message>{{ $processingState['message'] ?? '' }}</p>
    <p data-processing-step></p>
        <small class="ai-processing-wait-note">Thời gian chờ tóm tắt có thể kéo dài từ 3 đến 5 phút hoặc lâu hơn, tùy thuộc vào kích thước của file.</small>
    <p data-processing-retry>@if(!empty($processingState['next_retry_at']))Thử tiếp sau: {{ date('H:i:s d/m/Y', $processingState['next_retry_at']) }} (giờ máy chủ).@endif</p>
    <ul data-processing-errors>
        @foreach(collect($processingState['errors'] ?? [])->unique(fn ($e) => ($e['stage'] ?? '').'|'.($e['code'] ?? '')) as $error)
            <li>{{ $stepLabels[$error['stage'] ?? ''] ?? 'Xử lý AI' }}: {{ $error['message'] ?? 'Chưa hoàn tất.' }}</li>
        @endforeach
    </ul>
    <button type="button" class="ai-button" data-ai-refresh-result hidden>Cập nhật kết quả mới</button>
    <small>Bài nộp đã được lưu. Lỗi API không phải kết luận báo cáo thiếu nội dung.</small>
</div>
@endif
@endif

@once
<style>
.ai-result-status-lines {margin:8px 0;}
.ai-processing-status .ai-result-status-lines p {display:block !important;margin:3px 0;font-size:.9rem;}
.ai-processing-status .ai-processing-wait-note {display:block;margin:6px 0 0;font-size:.85rem;line-height:1.5;color:#a35400;}
.ai-processing-status[data-processing-active="0"] .ai-processing-wait-note {display:none;}

.ai-processing-status { margin:16px 20px; padding:16px; border:1px solid #c8dff7; border-radius:8px; background:#f0f7ff; color:#173e63; font-size:14px; line-height:1.65; }
.ai-processing-status [data-ai-refresh-result][hidden] { display:none !important; }
.ai-processing-status[data-processing-active="1"] [data-processing-title]::before {content:'';display:inline-block;width:13px;height:13px;border:2px solid #bdd7ed;border-top-color:#1767a3;border-radius:50%;margin-right:8px;vertical-align:middle;animation:ai-queue-spin 1s linear infinite;} @keyframes ai-queue-spin {to {transform:rotate(360deg)}}
.ai-processing-status p { margin:4px 0; }.ai-processing-status p:empty,.ai-processing-status ul:empty { display:none; }
.ai-processing-status ul { margin:8px 0; padding-left:22px; }.ai-processing-status small { display:block; margin-top:8px; color:#52677e; }
.ai-summary-expanded .ai-processing-status { flex-shrink:0; max-height:30vh; overflow:auto; }
</style>
<script>
(() => {
    const stages = {extract:'Trích xuất tài liệu',map:'Đọc các phần báo cáo',chapter_reduce:'Tổng hợp theo chương',final_reduce:'Tổng hợp kết quả cuối',waiting:'Đang chờ giới hạn API',complete:'Hoàn tất xử lý',partial:'Đã lưu tiến độ',summary_part:'Đọc từng phần báo cáo',summary_audit:'Kiểm chứng ý tóm tắt',completion_audit:'Kiểm tra căn cứ công việc hoàn thành',summary_merge:'Tổng hợp chương và mục',review_part:'Tìm bằng chứng theo yêu cầu',review_repair:'Kiểm tra lại tiêu chí lỗi',review_decision:'Đối chiếu yêu cầu',citation_audit:'Kiểm chứng trích dẫn'};
    const labels = {queued:'Đang chờ xử lý',running:'Đang phân tích',waiting:'Đang chờ thử tiếp',completed:'Đã hoàn tất',partial:'Hoàn tất một phần'};
    function updateButtons(root, active) {
        root.closest('.ai-summary')?.querySelectorAll('[data-ai-review-submit]').forEach(button => {button.disabled=active;});
    }
    async function poll(node) {
        if (!node.isConnected) return;
        try {
            const response = await fetch(node.dataset.aiProcessingUrl,{headers:{Accept:'application/json'},cache:'no-store',credentials:'same-origin'});
            if (response.redirected) {
                node.querySelector('[data-processing-message]').textContent='Phiên đăng nhập đã thay đổi. Vui lòng tải lại trang để xem trạng thái.';
                return;
            }
            if (!response.ok) {
                if ([401,403,404].includes(response.status)) return;
                throw new Error('status-unavailable');
            }
            const data = await response.json();
            const active = ['queued','running','waiting'].includes(data.status);
            document.querySelectorAll('[data-ai-processing-url]').forEach(banner => {
                if (banner.dataset.aiProcessingUrl!==node.dataset.aiProcessingUrl) return;
                banner.querySelector('[data-processing-title]').textContent=labels[data.status] || 'Trạng thái AI';
                banner.querySelector('[data-processing-message]').textContent=data.message || '';
                const p=data.progress || {}, coverage=data.summary_coverage || {};
                banner.querySelector('[data-processing-step]').textContent=[p.stage ? (stages[p.stage] || (p.engine === 'pdf_lab' ? p.stage : 'Đang xử lý')) : '',p.map_total ? `Đã đọc ${p.map_done || 0}/${p.map_total} phần báo cáo` : (coverage.total ? `Tóm tắt: ${coverage.done}/${coverage.total} phần` : ''),p.chapter_total ? `Tổng hợp chương: ${p.chapter_done || 0}/${p.chapter_total}` : '',p.completed_calls ? (p.engine === 'pdf_lab' ? `${p.completed_calls} lượt sinh nội dung đã thử (gồm checkpoint)` : `${p.completed_calls} lượt AI thành công trong lần chạy này`) : '',p.started_at ? `Đã chạy khoảng ${Math.max(0,Math.floor(Date.now()/1000-p.started_at))} giây trong lượt này` : '',p.waiting_seconds ? `Chờ điều tiết API khoảng ${p.waiting_seconds} giây` : '',p.reused_calls ? `Đã dùng lại ${p.reused_calls} bước từ bộ nhớ đệm` : ''].filter(Boolean).join(' · ');
                banner.querySelector('[data-processing-retry]').textContent=data.next_retry_at ? 'Dự kiến thử tiếp: '+new Date(data.next_retry_at*1000).toLocaleString('vi-VN') : '';
                const list=banner.querySelector('[data-processing-errors]'); list.replaceChildren();
                const seen=new Set();
                for (const error of data.errors || []) {
                    const key=(error.stage || '')+'|'+error.code; if(seen.has(key)) continue; seen.add(key);
                    const item=document.createElement('li'); item.textContent=(stages[error.stage] || 'Xử lý AI')+': '+(error.message || 'Chưa hoàn tất.');list.append(item);
                }
                if (Number(data.updated_at)>Number(banner.dataset.processingUpdated) && ['waiting','completed','partial'].includes(data.status)) banner.querySelector('[data-ai-refresh-result]').hidden=false;
                banner.dataset.processingActive=active ? '1' : '0';
                updateButtons(banner,active);
                if (!active && Number(data.updated_at) > Number(banner.dataset.processingUpdated)) {
                    banner.closest('.ai-summary-card, .ai-summary')?.querySelectorAll('[data-review-result="insufficient_evidence"]').forEach(label => {
                        label.textContent = 'Đã xử lý · Cập nhật để xem kết quả';
                    });
                }
            });
            if (active) setTimeout(()=>poll(node),5000);
        } catch(error) {
            node.querySelector('[data-processing-step]').textContent='Chưa cập nhật được trạng thái. Hệ thống sẽ kiểm tra lại; bài nộp vẫn được giữ.';
            setTimeout(()=>poll(node),15000);
        }
    }
    function start() {
        document.querySelectorAll('.ai-summary-card [data-ai-processing-url]').forEach(node=> {
            if(node.closest('[data-teacher-ui="1"]')) return;
            const active=node.dataset.processingActive==='1'; updateButtons(node,active);
            if(active) poll(node);
        });
    }
    document.addEventListener('click',event=> {if(event.target.closest('[data-ai-refresh-result]') && !event.target.closest('[data-teacher-ui="1"]')) location.reload();});
    if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',start); else setTimeout(start,0);
})();
</script>
@endonce
