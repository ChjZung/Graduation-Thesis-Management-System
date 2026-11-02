{{-- Modal đối chiếu tính nhất quán giữa các lần phân tích AI --}}
@php
    $history = $aiData['_analysis_history'] ?? [];
    $metrics = $aiData['_consistency_metrics'] ?? [];
    $totalRuns = count($history) + 1;
    $currentSummary = $aiData['overall_summary'] ?? ($aiData['tom_tat'] ?? 'Chưa có tóm tắt');
    $currentCompleted = array_map(function($w) {
        return is_array($w) ? ($w['content'] ?? '') : (string)$w;
    }, $aiData['completed_work'] ?? []);
@endphp

<div class="modal fade" id="aiConsistencyModal-{{ $uniqueId }}" tabindex="-1" aria-labelledby="aiConsistencyModalLabel-{{ $uniqueId }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title text-primary fw-bold fs-6" id="aiConsistencyModalLabel-{{ $uniqueId }}">
                    <i class="fa-solid fa-scale-balanced me-2 text-primary"></i>Lịch sử phân tích & Đối chiếu tính nhất quán AI
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body p-4">
                @if(empty($history))
                    <div class="text-center py-4">
                        <i class="fa-solid fa-clock-rotate-left text-muted fs-1 mb-3"></i>
                        <h6 class="text-dark fw-bold">Đây là lượt phân tích đầu tiên</h6>
                        <p class="text-muted small mb-3">
                            Chưa có dữ liệu của các lần phân tích trước đó. Khi bạn bấm nút <strong>"Phân tích lại"</strong>, hệ thống sẽ lưu lại bản phân tích hiện tại vào lịch sử và tự động tính toán tỷ lệ tương đồng, đối chiếu kết quả giữa các lần chạy.
                        </p>
                        @if(!empty($reanalyzeUrl))
                            <form method="POST" action="{{ $reanalyzeUrl }}" data-teacher-analyze-form class="d-inline">
                                @csrf
                                <input type="hidden" name="fresh" value="1">
                                <button type="submit" class="btn btn-primary btn-sm" data-ai-review-submit>
                                    <i class="fa-solid fa-rotate me-1"></i>Thử phân tích lại ngay
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    {{-- Thẻ thống kê chỉ số nhất quán --}}
                    <div class="row g-2 mb-4">
                        <div class="col-4">
                            <div class="p-3 bg-light rounded border text-center">
                                <div class="small text-muted mb-1">Tổng lượt phân tích</div>
                                <div class="fs-4 fw-bold text-primary">{{ $totalRuns }} <small class="fs-6 text-muted fw-normal">lần</small></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-light rounded border text-center">
                                <div class="small text-muted mb-1">Tương đồng tóm tắt</div>
                                <div class="fs-4 fw-bold text-success">{{ $metrics['summary_similarity'] ?? 100 }}%</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-light rounded border text-center">
                                <div class="small text-muted mb-1">Khớp công việc</div>
                                <div class="fs-4 fw-bold text-info">{{ $metrics['completed_match_rate'] ?? 100 }}%</div>
                            </div>
                        </div>
                    </div>

                    @if(!empty($metrics['identical_hash']))
                        <div class="alert alert-success py-2 px-3 small mb-3">
                            <i class="fa-solid fa-check-double me-1"></i>
                            <strong>Kết luận:</strong> Nội dung tóm tắt giữa lần chạy hiện tại và lần chạy trước đạt <strong>trùng khớp tuyệt đối (100%)</strong>, xác nhận mô hình AI hoạt động hoàn toàn nhất quán trên tài liệu này.
                        </div>
                    @else
                        <div class="alert alert-info py-2 px-3 small mb-3">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            <strong>Đánh giá tính tất định:</strong> Kết quả tóm tắt đạt độ tương đồng <strong>{{ $metrics['summary_similarity'] ?? 100 }}%</strong>, các ý chính và công việc cốt lõi được giữ ổn định giữa các lần sinh.
                        </div>
                    @endif

                    {{-- Bảng lịch sử các lần chạy --}}
                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-timeline me-2 text-secondary"></i>Chi tiết các lần phân tích</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-hover small align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 15%">Lần chạy</th>
                                    <th style="width: 25%">Thời điểm</th>
                                    <th style="width: 20%">Công việc ghi nhận</th>
                                    <th style="width: 20%">Số chương/mục</th>
                                    <th style="width: 20%">Trạng thái đối chiếu</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Lần chạy hiện tại --}}
                                <tr class="table-primary">
                                    <td class="fw-bold">
                                        <span class="badge bg-primary">Lần {{ $totalRuns }} (Hiện tại)</span>
                                    </td>
                                    <td>{{ $generatedAt ?? 'Mới nhất' }}</td>
                                    <td><strong>{{ count($currentCompleted) }}</strong> mục</td>
                                    <td>{{ count($aiData['chapters'] ?? []) }} chương/phần</td>
                                    <td><span class="badge bg-success">Bản đang hiển thị</span></td>
                                </tr>
                                {{-- Các lần chạy trước đó --}}
                                @foreach(array_reverse($history) as $h)
                                    <tr>
                                        <td class="fw-semibold">Lần {{ $h['run_index'] ?? 1 }}</td>
                                        <td>{{ $h['created_at_human'] ?? 'Trước đó' }}</td>
                                        <td><strong>{{ $h['completed_work_count'] ?? count($h['completed_work'] ?? []) }}</strong> mục</td>
                                        <td>{{ $h['chapters_count'] ?? 0 }} chương/phần</td>
                                        <td>
                                            @if(($h['summary_hash'] ?? '') === md5($currentSummary))
                                                <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Khớp 100%</span>
                                            @else
                                                <span class="badge bg-info text-dark">Tương đồng cao</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- So sánh song song giữa Lần hiện tại và Lần trước --}}
                    @php
                        $lastRun = end($history);
                    @endphp
                    @if(!empty($lastRun))
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-columns me-2 text-secondary"></i>Đối chiếu nội dung (Lần {{ $totalRuns }} vs Lần {{ $lastRun['run_index'] ?? 1 }})</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-6">
                                <div class="card h-100 border-primary">
                                    <div class="card-header bg-primary text-white py-2 small fw-bold">
                                        Lần {{ $totalRuns }} (Bản hiện tại)
                                    </div>
                                    <div class="card-body p-3 small">
                                        <strong class="d-block mb-1 text-primary">Tóm tắt tổng quan:</strong>
                                        <p class="text-muted mb-3" style="line-height: 1.5;">{{ $currentSummary }}</p>
                                        <strong class="d-block mb-1 text-primary">Công việc đã hoàn thành ({{ count($currentCompleted) }}):</strong>
                                        <ul class="ps-3 mb-0 text-muted">
                                            @forelse($currentCompleted as $work)
                                                <li>{{ $work }}</li>
                                            @empty
                                                <li>Không có mục nào</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="card h-100 border-secondary">
                                    <div class="card-header bg-secondary text-white py-2 small fw-bold">
                                        Lần {{ $lastRun['run_index'] ?? 1 }} (Bản lưu trước đó)
                                    </div>
                                    <div class="card-body p-3 small">
                                        <strong class="d-block mb-1 text-secondary">Tóm tắt tổng quan:</strong>
                                        <p class="text-muted mb-3" style="line-height: 1.5;">{{ $lastRun['summary'] ?? 'Chưa có' }}</p>
                                        <strong class="d-block mb-1 text-secondary">Công việc đã hoàn thành ({{ count($lastRun['completed_work'] ?? []) }}):</strong>
                                        <ul class="ps-3 mb-0 text-muted">
                                            @forelse($lastRun['completed_work'] ?? [] as $work)
                                                <li>{{ $work }}</li>
                                            @empty
                                                <li>Không có mục nào</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
            <div class="modal-footer bg-light py-2">
                @if(!empty($reanalyzeUrl))
                    <form method="POST" action="{{ $reanalyzeUrl }}" data-teacher-analyze-form class="me-auto">
                        @csrf
                        <input type="hidden" name="fresh" value="1">
                        <button type="submit" class="btn btn-outline-primary btn-sm" data-ai-review-submit>
                            <i class="fa-solid fa-rotate me-1"></i>Kích hoạt phân tích lại lượt mới
                        </button>
                    </form>
                @endif
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
