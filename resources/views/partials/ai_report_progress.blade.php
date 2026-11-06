@php
    $progress = $aiData['progress_v1'] ?? null;
    $hasProgress = \App\Services\AiResultMerger::validProgress($progress);
    $workGroups = ['Đã hoàn thành' => [], 'Chưa hoàn thành' => [], 'Chưa phân loại' => []];
    $progressDisplay = $aiData['progress_display'] ?? [];
    $progressRows = [];
    if ($hasProgress) {
        foreach ($progress as $key => $value) {
            $summaries = preg_split('/\R/u', $progressDisplay[$key] ?? '', -1, PREG_SPLIT_NO_EMPTY);
            foreach (preg_split('/\R/u', $value ?? '', -1, PREG_SPLIT_NO_EMPTY) as $index => $quote) {
                if ($key === 'ke_hoach' && preg_match('/tạo\s*tiền\s*đề\s*cho|trình\s*bày\s*ở.*chương\s*tiếp\s*theo/iu', $quote)) continue;
                $progressRows[$key][] = ['quote'=>$quote, 'summary'=>$summaries[$index] ?? ''];
            }
        }
        // Older results may have put both a limitation and a proposed next step in one saved quote.
        foreach ($progressRows['kho_khan'] ?? [] as $index => $finding) {
            if (!preg_match('/^\s*(?:Tuy nhiên,\s*)?do\s+(thời gian nghiên cứu còn hạn chế),/iu', $finding['quote'], $limitation)) continue;
            if (!$finding['summary']) {
                $progressRows['kho_khan'][$index]['summary'] = 'Thời gian nghiên cứu còn hạn chế.';
            }
            if (empty($progressRows['ke_hoach']) && preg_match(
                '/có thể được tiếp tục hoàn thiện trong tương lai,\s*chẳng hạn như\s+(.+?)(?:\s+nhằm\s+[^.!?]+)?[.!?]?$/iu',
                $finding['quote'], $proposal
            )) {
                $actions = strtr($proposal[1], ['mởrộng'=>'mở rộng', 'dữliệu'=>'dữ liệu', 'cơ chếsao'=>'cơ chế sao']);
                $progressRows['ke_hoach'][] = [
                    'quote'=>$finding['quote'],
                    'summary'=>'Hướng đề xuất: '.rtrim($actions, '.').'.',
                ];
            }
        }
        foreach ($progressRows['cong_viec'] ?? [] as $row) {
            $workLine = $row['quote'];
            if (preg_match('/^(Đã hoàn thành|Chưa hoàn thành):\s+(.+)$/u', trim($workLine), $workMatch)) {
                $row['quote'] = $workMatch[2];
                $row['summary'] = preg_replace('/^(Đã hoàn thành|Chưa hoàn thành):\s+/u', '', $row['summary']);
                $workGroups[$workMatch[1]][] = $row;
            } else {
                $workGroups['Chưa phân loại'][] = $row;
            }
        }
    }
@endphp
<section class="ai-section ai-section-wide mb-3">
    <h3 class="ai-section-title mb-2">Tiến độ theo báo cáo</h3>
    <p class="ai-review-note">AI phân loại thông tin nhóm nêu trong báo cáo. Giảng viên xác nhận kết quả ở mục bên dưới.</p>
    @if($hasProgress)
        @if(($aiData['progress_v1_status'] ?? '') !== 'complete')
            <p class="ai-review-note">Kết quả đang dựa trên phần văn bản đã đọc.</p>
        @endif
        <h4>Công việc</h4>
        @foreach($workGroups as $label => $lines)
            @continue($label === 'Chưa phân loại' && !$lines)
            <h5>{{ $label }}</h5>
            @if($label === 'Đã hoàn thành' && !array_filter(array_column($lines, 'summary')) && !empty($aiData['completed_work']))
                @include('partials.ai_summary_section', ['section'=>[
                    'key'=>'saved-reported-work', 'title'=>'Công việc tác giả báo cáo đã thực hiện · Có nguồn',
                    'icon'=>'fa-file-lines', 'tone'=>'primary', 'items'=>$aiData['completed_work'],
                ]])
                @continue
            @endif
            @include('partials.ai_progress_findings', ['findings'=>$lines, 'emptyFindingsText'=>$label === 'Chưa hoàn thành' ? 'Chưa tìm thấy công việc được báo cáo là còn dang dở.' : null])
        @endforeach
        @foreach(['kho_khan'=>'Khó khăn', 'ke_hoach'=>'Kế hoạch'] as $key => $label)
            <h4>{{ $label }}</h4>
            @include('partials.ai_progress_findings', ['findings'=>$progressRows[$key] ?? []])
        @endforeach
    @else
        <p class="ai-empty">Chưa có kết quả phân loại đủ ba trường cho phiên bản này.</p>
        @if(!empty($aiData['completed_work']))
            @include('partials.ai_summary_section', ['section'=>[
                'key'=>'saved-reported-work', 'title'=>'Công việc tác giả báo cáo đã thực hiện · Có nguồn',
                'icon'=>'fa-file-lines', 'tone'=>'primary', 'items'=>$aiData['completed_work'],
            ]])
        @endif
        <p class="ai-muted">Khó khăn và kế hoạch: chưa có kết quả phân loại.</p>
    @endif
</section>
<section class="ai-section ai-section-wide">
    <h3 class="ai-section-title mb-2">Đánh giá của giảng viên</h3>
    @if(!empty($pb->NhanXetGV))
        <p><strong>Trạng thái đã ghi nhận</strong> {{ $pb->TrangThai }}</p>
        <div>{!! \App\Helpers\HtmlHelper::clean($pb->NhanXetGV) !!}</div>
    @else
        <p class="ai-muted">Phiên bản này chưa có nhận xét của giảng viên.</p>
    @endif
</section>
