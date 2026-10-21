@php
    $processingState = $aiData['_processing'] ?? [];
    $processingActive = in_array($processingState['status'] ?? '', ['queued','running','waiting']);
    $processingUrl = $citationAvailable ? route('baocao.ai.trang_thai', $pb->MaKhoi) : '';
    $stepLabels = ['summary_part'=>'Đọc từng phần báo cáo','summary_merge'=>'Tổng hợp nội dung','review_part'=>'Tìm bằng chứng theo yêu cầu','review_decision'=>'Đối chiếu yêu cầu','citation_audit'=>'Kiểm chứng trích dẫn'];
@endphp
@if($processingUrl && $processingState)
<div class="ai-processing-status" role="status" aria-live="polite" data-ai-processing-url="{{ $processingUrl }}" data-processing-active="{{ $processingActive ? '1' : '0' }}" data-processing-updated="{{ $processingState['updated_at'] ?? 0 }}">
    <strong data-processing-title>{{ ['queued'=>'Đang chờ xử lý','running'=>'Đang phân tích','waiting'=>'Đang chờ thử tiếp','completed'=>'Đã hoàn tất','partial'=>'Hoàn tất một phần'][$processingState['status'] ?? ''] ?? 'Trạng thái AI' }}</strong>
    <p data-processing-message>{{ $processingState['message'] ?? '' }}</p>
    <p data-processing-step></p>
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
@once
<style>
.ai-processing-status { margin:16px 20px; padding:16px; border:1px solid #c8dff7; border-radius:8px; background:#f0f7ff; color:#173e63; font-size:14px; line-height:1.65; }
.ai-processing-status [data-ai-refresh-result][hidden] { display:none !important; }
.ai-processing-status p { margin:4px 0; }.ai-processing-status p:empty,.ai-processing-status ul:empty { display:none; }
.ai-processing-status ul { margin:8px 0; padding-left:22px; }.ai-processing-status small { display:block; margin-top:8px; color:#52677e; }
.ai-summary-expanded .ai-processing-status { flex-shrink:0; max-height:30vh; overflow:auto; }
</style>
<script>
(() => {
    const stages = {summary_part:'Đọc từng phần báo cáo',summary_merge:'Tổng hợp nội dung',review_part:'Tìm bằng chứng theo yêu cầu',review_decision:'Đối chiếu yêu cầu',citation_audit:'Kiểm chứng trích dẫn'};
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
                banner.querySelector('[data-processing-step]').textContent=[p.stage ? (stages[p.stage] || 'Đang xử lý') : '',coverage.total ? `Tóm tắt: ${coverage.done}/${coverage.total} phần` : '',p.waiting_seconds ? `Chờ điều tiết API khoảng ${p.waiting_seconds} giây` : '',p.reused_calls ? `Đã dùng lại ${p.reused_calls} bước từ bộ nhớ đệm` : ''].filter(Boolean).join(' · ');
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
            });
            if (active) setTimeout(()=>poll(node),5000);
        } catch(error) {
            node.querySelector('[data-processing-step]').textContent='Chưa cập nhật được trạng thái. Hệ thống sẽ kiểm tra lại; bài nộp vẫn được giữ.';
            setTimeout(()=>poll(node),15000);
        }
    }
    function start() {
        document.querySelectorAll('.ai-summary-card [data-ai-processing-url]').forEach(node=> {
            const active=node.dataset.processingActive==='1'; updateButtons(node,active);
            if(active) poll(node);
        });
    }
    document.addEventListener('click',event=> {if(event.target.closest('[data-ai-refresh-result]')) location.reload();});
    if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',start); else setTimeout(start,0);
})();
</script>
@endonce
