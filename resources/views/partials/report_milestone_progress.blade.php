@if(isset($reportMilestoneProgress))
@php $milestones = $reportMilestoneProgress; @endphp
<section class="card border shadow-sm mb-3" data-report-milestone-progress>
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
            <h3 class="h6 fw-bold text-primary mb-0">{{ $milestones['label'] }}</h3>
            <strong>{{ number_format($milestones['percentage'], 2) }}%</strong>
        </div>
        <div class="progress mb-2" style="height:8px" role="progressbar" aria-label="Tiến độ mốc báo cáo" aria-valuenow="{{ $milestones['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar" style="width:{{ $milestones['percentage'] }}%"></div>
        </div>
        <p class="small text-muted mb-2">{{ $milestones['stage_count'] }} giai đoạn · {{ $milestones['completed_rounds'] }}/{{ $milestones['round_count'] }} đợt được GVHD xác nhận đạt. Mỗi giai đoạn có trọng số bằng nhau; các đợt trong cùng giai đoạn chia đều trọng số đó.</p>
        @if(!$milestones['stage_count'])<p class="small text-muted mb-0">Chưa có giai đoạn báo cáo được thiết lập.</p>@endif
        @if($milestones['configuration_incomplete'])<p class="small text-warning">Có giai đoạn chưa có đợt nộp; phần trọng số đó chưa được tính hoàn thành.</p>@endif
        @if(!empty($milestones['scope']['legacy_ownership_ambiguous']))<p class="small text-warning">Bài nộp cũ chưa xác định được nhóm sẽ không được tự tính vào tiến độ.</p>@endif
        <details>
            <summary class="small text-primary fw-semibold" style="cursor:pointer">Xem trọng số và kết quả từng đợt</summary>
            <div class="table-responsive mt-2">
                <table class="table table-sm align-middle small mb-0">
                    <thead><tr><th>Giai đoạn / Đợt</th><th>Trạng thái</th><th>Trọng số</th><th>Đã đạt</th><th>Lũy kế</th></tr></thead>
                    <tbody>
                    @foreach($milestones['stages'] as $milestoneStage)
                        <tr class="table-light"><th colspan="2">{{ $milestoneStage['title'] }}</th><td>{{ number_format($milestoneStage['weight'], 2) }}%</td><td>{{ number_format($milestoneStage['earned'], 2) }}%</td><td></td></tr>
                        @foreach($milestoneStage['rounds'] as $milestoneRound)
                            <tr><td>{{ $milestoneRound['title'] }}</td><td>{{ $milestoneRound['status'] }}</td><td>{{ number_format($milestoneRound['weight'], 2) }}%</td><td>{{ number_format($milestoneRound['earned'], 2) }}%</td><td>{{ number_format($milestoneRound['cumulative'], 2) }}%</td></tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    </div>
</section>
@endif
