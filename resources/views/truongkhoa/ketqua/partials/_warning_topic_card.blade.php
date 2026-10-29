<div class="card border border-warning-subtle rounded-3 bg-white w-100 shadow-none mb-0 warning-topic-card"
     style="min-width: 0; max-width: 100%; box-sizing: border-box; padding: 8px 12px; background-color: #ffffff; border-color: rgba(245, 158, 11, 0.35) !important;">
    <!-- Dòng 1: Tên đề tài (đậm, cho phép xuống dòng, chống tràn) -->
    <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem; line-height: 1.45; white-space: normal; overflow-wrap: anywhere; word-break: break-word; font-family: inherit;">
        <i class="fa-solid fa-book-bookmark text-warning me-1 flex-shrink-0"></i>
        <span>{{ $itemW['ten_de_tai'] ?? 'Chưa xác định tên đề tài' }}</span>
        @if(!empty($itemW['ma_ho_so']))
            <span class="badge bg-light text-secondary border font-monospace ms-1 fw-normal" style="font-size: 0.72rem; vertical-align: middle;">
                #{{ $itemW['ma_ho_so'] }}
            </span>
        @endif
    </div>

    <!-- Dòng 2: Nhóm & Hội đồng (chữ nhỏ, xám) -->
    <div class="small text-secondary mb-2" style="font-size: 0.8rem; line-height: 1.4; white-space: normal; overflow-wrap: anywhere; word-break: break-word; font-family: inherit;">
        <span><i class="fa-solid fa-users text-muted me-1"></i>{{ $itemW['ten_nhom'] ?? 'Chưa có nhóm' }}</span>
        <span class="text-secondary mx-1">&middot;</span>
        <span><i class="fa-solid fa-layer-group text-muted me-1"></i>{{ $itemW['ten_hoi_dong'] ?? 'Chưa phân công hội đồng' }}</span>
    </div>

    <!-- Dòng 3: Danh sách giám khảo chưa nộp phiếu -->
    @php
        $gvChuaNop = $itemW['gv_chua_hoan_thanh'] ?? [];
        $soLuongChuaNop = count($gvChuaNop);
    @endphp
    <div class="d-flex align-items-baseline flex-wrap gap-2 pt-1 border-top border-light-subtle" style="min-width: 0; width: 100%;">
        <span class="small fw-semibold text-danger flex-shrink-0" style="font-size: 0.78rem; font-family: inherit;">
            <i class="fa-solid fa-clock-rotate-left me-1"></i>
            Còn {{ $soLuongChuaNop > 0 ? $soLuongChuaNop : 1 }} giám khảo chưa nộp phiếu:
        </span>
        <div class="d-flex flex-wrap align-items-center" style="gap: 6px; min-width: 0; flex: 1 1 auto;">
            @forelse($gvChuaNop as $gv)
                @php
                    $rawSt = $gv['TrangThai'] ?? 'Chưa chấm';
                    $isLuuNhap = str_contains($rawSt, 'Lưu nháp');
                    $labelSt = $isLuuNhap ? 'Lưu nháp' : ($rawSt === 'Chưa chấm' ? 'Chưa chấm' : $rawSt);
                    $badgeClass = $isLuuNhap 
                        ? 'bg-warning-subtle text-warning-emphasis border-warning-subtle' 
                        : 'bg-danger-subtle text-danger-emphasis border-danger-subtle';
                    $iconClass = $isLuuNhap ? 'fa-pen-to-square text-warning' : 'fa-hourglass-start text-danger';
                @endphp
                <span class="badge {{ $badgeClass }} border px-2 py-1 rounded-2 fw-medium text-start d-inline-flex align-items-center" 
                      style="font-size: 0.75rem; white-space: normal; overflow-wrap: anywhere; word-break: break-word; line-height: 1.35; font-family: inherit; max-width: 100%;">
                    <i class="fa-solid {{ $iconClass }} me-1 flex-shrink-0" style="font-size: 0.7rem;"></i>
                    <span style="overflow-wrap: anywhere; word-break: break-word;">{{ $gv['HoTen'] }} ({{ $labelSt }})</span>
                </span>
            @empty
                <span class="text-danger small" style="white-space: normal; overflow-wrap: anywhere; word-break: break-word; font-family: inherit;">
                    {{ $itemW['ly_do'] ?? 'Chưa hoàn thành nộp phiếu' }}
                </span>
            @endforelse
        </div>
    </div>
</div>
