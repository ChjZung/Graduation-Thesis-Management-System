{{-- =========================================================================
     THÀNH PHẦN HIỂN THỊ 6 GIAI ĐOẠN TIẾN ĐỘ KHÓA LUẬN VỚI THANH TIẾN ĐỘ & PHẦN TRĂM
     Sử dụng: @include('partials.stage_progress_bars', ['stages' => $nhom->stage_breakdown ?? $stages, 'showOverall' => true])
     ========================================================================= --}}
@php
    $rawStages = $stages ?? ($nhom->stage_breakdown ?? []);
    if (isset($rawStages['stages']) && is_array($rawStages['stages'])) {
        $stagesList = $rawStages['stages'];
        $overall = $overallPercent ?? ($rawStages['overall_percent'] ?? ($nhom->progress_percent ?? 0));
    } else {
        $stagesList = is_array($rawStages) ? $rawStages : [];
        $overall = $overallPercent ?? ($nhom->progress_percent ?? 0);
    }
    // Filter to ensure only valid stage array items are iterated
    $stagesList = array_filter($stagesList, fn($item) => is_array($item));
    $showOverallCard = $showOverall ?? true;
@endphp

@if(!empty($stagesList))
    @if($showOverallCard)
        {{-- Thẻ Tiến Độ Tổng Thể Khóa Luận --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #002855 0%, #004a8f 100%); color: #ffffff;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="text-white-50 text-uppercase fw-semibold" style="font-size: 11.5px; letter-spacing: 0.8px;">
                            <i class="fa-solid fa-flag-checkered me-1"></i> Tổng quan tiến độ khóa luận
                        </div>
                        <h4 class="fw-bold mb-1 text-white mt-1">
                            Tiến Độ Thực Hiện Khóa Luận Tốt Nghiệp
                        </h4>
                        <div class="text-white-50 small">
                            Đã đáp ứng <strong>{{ collect($stagesList)->where('is_completed', true)->count() }}/6</strong> giai đoạn nghiệp vụ theo quy chuẩn Nhà trường
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="display-6 fw-bold text-white mb-0">{{ $overall }}%</div>
                        <span class="badge bg-white text-primary px-3 py-1 rounded-pill fw-semibold mt-1" style="font-size: 12px;">
                            {{ $overall >= 100 ? 'Đã hoàn thành' : ($overall >= 60 ? 'Tiến độ tốt' : 'Đang thực hiện') }}
                        </span>
                    </div>
                </div>
                <div class="progress mt-3 bg-white bg-opacity-25" style="height: 10px; border-radius: 8px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" 
                         role="progressbar" 
                         style="width: {{ $overall }}%;" 
                         aria-valuenow="{{ $overall }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Danh Sách 6 Giai Đoạn Chi Tiết --}}
    <div class="row g-3">
        @foreach($stagesList as $stNum => $stage)
            @php
                $isDone = $stage['is_completed'] ?? false;
                $isCurrent = $stage['is_current'] ?? false;
                $isFuture = $stage['is_future'] ?? false;
                $pct = (int)($stage['percent'] ?? 0);
                $statusColor = $stage['color'] ?? '#94a3b8';
            @endphp
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 transition-all stage-box-item {{ $isCurrent ? 'ring-2 border-primary border-2' : '' }}"
                     style="background: #ffffff; border: 1px solid {{ $isCurrent ? '#0072ce' : '#e2e8f0' }} !important;">
                    <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                        
                        {{-- Header giai đoạn: Tên + Icon + Badge trạng thái --}}
                        <div>
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-3 d-inline-flex align-items-center justify-content-center text-white fw-bold"
                                          style="width: 36px; height: 36px; font-size: 14px; background: {{ $statusColor }}; flex-shrink: 0;">
                                        <i class="fa-solid {{ $stage['icon'] ?? 'fa-circle' }}"></i>
                                    </span>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 14.5px;">
                                            {{ $stage['name'] ?? ($stage['ten'] ?? ('Giai đoạn ' . $stNum)) }}
                                        </h6>
                                        <span class="text-muted" style="font-size: 11.5px;">
                                            GĐ {{ $stNum }}/6
                                        </span>
                                    </div>
                                </div>
                                <span class="badge rounded-pill px-2.5 py-1 fw-semibold {{ $stage['badge_class'] ?? 'bg-secondary text-white' }}" style="font-size: 11.5px;">
                                    @if($isDone)
                                        <i class="fa-solid fa-circle-check me-1"></i>
                                    @elseif($isCurrent)
                                        <i class="fa-solid fa-spinner fa-spin me-1"></i>
                                    @endif
                                    {{ $stage['status_label'] ?? 'Đang thực hiện' }}
                                </span>
                            </div>

                            {{-- Mô tả nội dung thực tế --}}
                            <p class="text-secondary mb-3 mt-2" style="font-size: 12.5px; line-height: 1.5; min-height: 38px;">
                                {{ $stage['description'] ?? ($stage['mo_ta'] ?? '') }}
                            </p>
                        </div>

                        {{-- Footer giai đoạn: Thanh tiến độ + Phần trăm + Thời hạn --}}
                        <div class="mt-2 pt-2 border-top border-light">
                            <div class="d-flex justify-content-between align-items-center mb-1.5">
                                <span class="text-muted fw-semibold" style="font-size: 11.5px;">
                                    Tiến độ giai đoạn:
                                </span>
                                <span class="fw-bold" style="font-size: 13.5px; color: {{ $statusColor }};">
                                    @if($isFuture && $pct === 0)
                                        <span class="text-muted small fw-normal">Chưa đến đợt</span>
                                    @else
                                        {{ $pct }}%
                                    @endif
                                </span>
                            </div>
                            
                            {{-- Thanh tiến độ --}}
                            <div class="progress mb-2" style="height: 8px; border-radius: 6px; background-color: #f1f5f9;">
                                <div class="progress-bar {{ $isCurrent ? 'progress-bar-striped progress-bar-animated' : '' }}" 
                                     role="progressbar" 
                                     style="width: {{ $pct }}%; background-color: {{ $statusColor }}; border-radius: 6px;" 
                                     aria-valuenow="{{ $pct }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>

                            {{-- Metadata: Hạn nộp & Cập nhật gần nhất --}}
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-1 text-muted" style="font-size: 11px;">
                                <div>
                                    <i class="fa-regular fa-clock me-1 text-primary"></i>
                                    Hạn chót: <strong class="text-dark">{{ $stage['deadline'] ?? 'Theo quy định' }}</strong>
                                </div>
                                @if(!empty($stage['last_updated']))
                                    <div>
                                        <i class="fa-solid fa-rotate me-1 text-secondary"></i>
                                        Cập nhật: <span class="text-dark">{{ $stage['last_updated'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
