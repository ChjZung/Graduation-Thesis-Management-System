@extends('layouts.giangvien')

@section('page_title', 'Quản Lý & Phê Duyệt Tiến Độ Báo Cáo')

@push('styles')
<style>
    :root {
        --gv-border: #e2e8f0;
        --gv-bg-subtle: #f8fafc;
        --huit-primary: #00305a;
        --huit-accent: #0284c7;
    }

    .card-dashboard {
        background: #ffffff;
        border: 1px solid var(--gv-border);
        border-radius: 1rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }

    /* Thẻ thống kê KPI */
    .stat-card-kpi {
        background: #ffffff;
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
        border: 1px solid var(--gv-border);
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02);
        transition: all 0.2s ease-in-out;
    }
    .stat-card-kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.05);
    }
    .stat-card-kpi.border-l-primary { border-left: 4px solid #2563eb; }
    .stat-card-kpi.border-l-warning { border-left: 4px solid #f59e0b; }
    .stat-card-kpi.border-l-success { border-left: 4px solid #10b981; }
    .stat-card-kpi.border-l-danger  { border-left: 4px solid #ef4444; }

    /* Cột danh sách nhóm bên trái */
    .group-selector-container {
        max-height: 750px;
        overflow-y: auto;
        padding-right: 4px;
    }
    .group-selector-container::-webkit-scrollbar {
        width: 5px;
    }
    .group-selector-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .group-item-card {
        text-align: left;
        border: 1.5px solid var(--gv-border);
        background: #ffffff;
        border-radius: 0.85rem;
        padding: 1.1rem;
        transition: all 0.2s ease-in-out;
        width: 100%;
        cursor: pointer;
    }
    .group-item-card:hover {
        border-color: #cbd5e1;
        background-color: #f8fafc;
        transform: translateY(-1px);
    }
    .group-item-card.active {
        background-color: #f0f7ff;
        border-color: var(--huit-accent);
        box-shadow: 0 8px 16px -3px rgba(2, 132, 199, 0.15);
    }

    /* Thẻ giai đoạn bên phải */
    .stage-card-item {
        border: 1px solid var(--gv-border);
        border-radius: 0.85rem;
        background: #ffffff;
        transition: all 0.2s ease;
    }
    .stage-card-item:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.04);
    }
    .stage-card-item.is-pending {
        border-left: 4px solid #f59e0b;
        background: #fffdfa;
    }
    .stage-card-item.is-done {
        border-left: 4px solid #10b981;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">

    <!-- PAGE HEADER -->
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-primary"></i>
                <span>Quản Lý &amp; Phê Duyệt Tiến Độ Báo Cáo</span>
            </h4>
            <p class="text-muted small mb-0">
                Nhấn chọn nhóm ở cột trái để xem chi tiết tiến độ 6 giai đoạn và mở không gian chấm điểm AI Map-Reduce ở cột phải.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('giangvien.my_tasks') }}" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 shadow-sm fw-medium small">
                <i class="fa-solid fa-table-list me-1.5 text-primary"></i> Bảng Công Việc &amp; Turnitin
            </a>
            <a href="{{ route('giangvien.calendar') }}" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 shadow-sm fw-medium small">
                <i class="fa-regular fa-calendar-days me-1.5 text-primary"></i> Lịch Công Việc
            </a>
        </div>
    </div>

    <!-- 4 THẺ KPI THỐNG KÊ (ĐỒNG BỘ 4 CỘT) -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-kpi border-l-primary d-flex justify-content-between align-items-center h-100">
                <div>
                    <span class="text-muted small fw-medium d-block mb-1">Tổng Số Nhóm</span>
                    <h3 class="fw-bold text-primary mb-0">{{ $tongSoNhom }}</h3>
                </div>
                <div class="text-primary fs-2 opacity-25">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-kpi border-l-warning d-flex justify-content-between align-items-center h-100">
                <div>
                    <span class="text-muted small fw-medium d-block mb-1">Báo Cáo Chờ Duyệt</span>
                    <h3 class="fw-bold text-warning mb-0">{{ $baocaoChoDuyet }}</h3>
                </div>
                <div class="text-warning fs-2 opacity-25">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-kpi border-l-success d-flex justify-content-between align-items-center h-100">
                <div>
                    <span class="text-muted small fw-medium d-block mb-1">Giai Đoạn Đã Đạt</span>
                    <h3 class="fw-bold text-success mb-0">{{ $baocaoDaDat }}</h3>
                </div>
                <div class="text-success fs-2 opacity-25">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-kpi border-l-danger d-flex justify-content-between align-items-center h-100">
                <div>
                    <span class="text-muted small fw-medium d-block mb-1">Cần Nhắc Nhở / Quá Hạn</span>
                    <h3 class="fw-bold text-danger mb-0">{{ $nhomCanNhacNho }}</h3>
                </div>
                <div class="text-danger fs-2 opacity-25">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
        </div>
    </div>

    @if($nhoms->isEmpty())
    <div class="card-dashboard p-5 text-center">
        <div class="py-4">
            <i class="fa-solid fa-inbox fa-3x text-secondary mb-3 opacity-50"></i>
            <h5 class="text-dark fw-bold mb-1">Chưa có nhóm nào đăng ký đề tài hướng dẫn</h5>
            <p class="text-muted small mb-0">Danh sách nhóm hướng dẫn sẽ hiển thị tự động tại đây khi có phân công chính thức.</p>
        </div>
    </div>
    @else

    <!-- BỐ CỤC 2 CỘT TƯƠNG TÁC THÔNG MINH (CLICK LÀ CHUYỂN NGAY TỨC THÌ) -->
    <div class="row g-4">
        
        <!-- CỘT TRÁI (35%): DANH SÁCH NHÓM & THANH TIẾN ĐỘ % -->
        <div class="col-lg-4">
            <div class="card-dashboard h-100">
                <div class="bg-white border-bottom p-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-users text-primary me-2"></i>Nhóm Hướng Dẫn ({{ $nhoms->count() }})
                    </h6>
                </div>

                {{-- Ô tìm kiếm nhanh nhóm --}}
                <div class="p-3 border-bottom bg-light">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" id="searchGroupInput" class="form-control border-start-0" 
                               placeholder="Tìm mã nhóm, đề tài, sinh viên...">
                    </div>
                </div>

                {{-- Danh sách các nhóm cuộn độc lập --}}
                <div class="p-3 group-selector-container d-flex flex-column gap-2.5" id="groupListContainer">
                    @foreach($nhoms as $index => $nhom)
                    @php
                        $prog = $nhom->progress_percent ?? 0;
                        $hasPending = $nhom->baoCaos->contains(function($b) {
                            return $b->TrangThai === 'Chờ duyệt' || ($b->khoiBaoCaos && $b->khoiBaoCaos->where('TrangThai', 'Chờ duyệt')->isNotEmpty());
                        });
                        $searchData = mb_strtolower($nhom->MaNhom . ' ' . $nhom->TenNhom . ' ' . ($nhom->deTai->TenDeTai ?? '') . ' ' . ($nhom->truongNhom->HoTen ?? ''));
                    @endphp
                    <div class="group-item-card {{ $loop->first ? 'active' : '' }}"
                         data-group-id="{{ $nhom->MaNhom }}"
                         data-search="{{ $searchData }}"
                         onclick="selectGroup('{{ $nhom->MaNhom }}')">
                        
                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                            <span class="badge bg-light text-primary border font-monospace px-2 py-1 fw-bold" style="font-size: 11px;">
                                {{ $nhom->MaNhom }}
                            </span>
                            @if($hasPending)
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 small fw-bold">
                                    <i class="fa-solid fa-bell me-1"></i>Có bài mới
                                </span>
                            @endif
                        </div>

                        <div class="fw-bold text-dark line-clamp-2 mb-1" style="font-size: 13px; line-height: 1.4;" title="{{ $nhom->deTai->TenDeTai ?? 'Chưa gán đề tài' }}">
                            {{ $nhom->deTai->TenDeTai ?? 'Chưa gán đề tài chính thức' }}
                        </div>

                        <div class="small text-muted mb-2.5 d-flex align-items-center gap-1" style="font-size: 11.5px;">
                            <i class="fa-solid fa-crown text-warning"></i>
                            <span>{{ $nhom->truongNhom->HoTen ?? 'Chưa xác định' }}</span>
                            <span>•</span>
                            <span>{{ $nhom->thanhViens->count() }} SV</span>
                        </div>

                        {{-- Thanh tiến độ % trực quan --}}
                        <div class="d-flex align-items-center gap-2 pt-2 border-top">
                            <div class="progress flex-grow-1" style="height: 6px; border-radius: 4px; background: #e2e8f0;">
                                <div class="progress-bar {{ $prog >= 100 ? 'bg-success' : ($prog >= 50 ? 'bg-primary' : 'bg-info') }}" 
                                     role="progressbar" 
                                     style="width: {{ $prog }}%; border-radius: 4px;" 
                                     aria-valuenow="{{ $prog }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="fw-bold text-dark font-monospace" style="font-size: 11.5px;">
                                {{ $prog }}%
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI (65%): CHI TIẾT 6 GIAI ĐOẠN CỦA NHÓM ĐƯỢC CHỌN -->
        <div class="col-lg-8" id="groupDetailContainer">
            @foreach($nhoms as $index => $nhom)
            @php
                $baoCaos = $nhom->baoCaos->keyBy('LanBaoCao');
                $stagesMeta = [
                    1 => ['name' => 'GĐ 1: Nhận đề cương & Thiết kế CSDL', 'deadline' => '18/09/2026', 'icon' => 'fa-database'],
                    2 => ['name' => 'GĐ 2: Thiết kế hệ thống & Xây dựng chức năng', 'deadline' => '16/10/2026', 'icon' => 'fa-code'],
                    3 => ['name' => 'GĐ 3: Kiểm thử hoàn thiện & Báo cáo dự thảo', 'deadline' => '06/11/2026', 'icon' => 'fa-vial-circle-check'],
                    4 => ['name' => 'GĐ 4: Hoàn thiện hồ sơ bảo vệ & Turnitin', 'deadline' => '27/11/2026', 'icon' => 'fa-file-shield'],
                    5 => ['name' => 'GĐ 5: Tiến hành bảo vệ trước Hội đồng', 'deadline' => '18/12/2026', 'icon' => 'fa-chalkboard-user'],
                    6 => ['name' => 'GĐ 6: Nhận kết quả & Nộp lại kết quả hoàn chỉnh', 'deadline' => '05/01/2027', 'icon' => 'fa-award'],
                ];
                $groupProgress = $nhom->progress_percent ?? 0;
            @endphp

            <div class="card-dashboard mb-0 h-100 group-detail-panel {{ $loop->first ? '' : 'd-none' }}" 
                 id="panel-group-{{ $nhom->MaNhom }}">
                
                {{-- Header nhóm bên phải --}}
                <div class="bg-white border-bottom p-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-2">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1.5">
                                <span class="badge bg-primary text-white fw-bold px-2.5 py-1 font-monospace" style="font-size: 11px;">
                                    {{ $nhom->MaNhom }}
                                </span>
                                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 11px;">
                                    {{ $nhom->deTai->MaDeTai ?? 'Chưa có mã ĐT' }}
                                </span>
                            </div>
                            <h5 class="fw-bold text-dark mb-1" style="font-size: 1.1rem; line-height: 1.4;">
                                {{ $nhom->deTai->TenDeTai ?? 'Chưa gán đề tài chính thức' }}
                            </h5>
                        </div>

                        {{-- Khối tiến độ tổng quan --}}
                        <div class="text-end" style="min-width: 140px;">
                            <span class="text-muted small fw-medium d-block mb-1">Tiến Độ Toàn Khóa</span>
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                <div class="progress" style="width: 80px; height: 7px; border-radius: 4px; background: #e2e8f0;">
                                    <div class="progress-bar {{ $groupProgress >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                         style="width: {{ $groupProgress }}%; border-radius: 4px;"></div>
                                </div>
                                <span class="fw-bold text-dark font-monospace" style="font-size: 13px;">{{ $groupProgress }}%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Danh sách thành viên nhóm --}}
                    <div class="d-flex align-items-center gap-2 flex-wrap pt-2 border-top text-muted small" style="font-size: 12px;">
                        <span><i class="fa-solid fa-users me-1 text-primary"></i>Thành viên:</span>
                        @foreach($nhom->thanhViens as $tv)
                            <span class="badge bg-light text-dark border px-2.5 py-1">
                                {{ $tv->sinhVien->HoTen ?? $tv->MaSV }}
                                @if(str_contains(mb_strtolower($tv->VaiTro ?? ''), 'trưởng'))
                                    <i class="fa-solid fa-crown text-warning ms-1"></i>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Thân: Danh sách 6 Giai đoạn tiến độ --}}
                <div class="p-4 flex-grow-1 overflow-auto d-flex flex-column gap-3" style="max-height: 680px;">
                    @foreach($stagesMeta as $stNum => $stData)
                    @php
                        $bc = $baoCaos[$stNum] ?? null;
                        $isPending = ($bc && ($bc->TrangThai === 'Chờ duyệt' || $bc->TrangThai === 'Đã nộp'));
                        $isDone = ($bc && $bc->TrangThai === 'Đạt');
                        $isResubmit = ($bc && $bc->TrangThai === 'Yêu cầu nộp lại');
                        $hasKhois = ($bc && $bc->khoiBaoCaos && $bc->khoiBaoCaos->count() > 0);
                        $ttAi = $bc?->tomTatBaoCao;
                    @endphp

                    <div class="stage-card-item p-3 {{ $isPending ? 'is-pending' : ($isDone ? 'is-done' : '') }}">
                        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-2">
                            <div class="d-flex align-items-center gap-2.5">
                                <span class="badge rounded-circle d-flex align-items-center justify-content-center {{ $isDone ? 'bg-success text-white' : ($isPending ? 'bg-warning text-dark' : 'bg-light text-secondary border') }}" 
                                      style="width: 28px; height: 28px; font-size: 12px; font-weight: bold;">
                                    @if($isDone)
                                        <i class="fa-solid fa-check" style="font-size: 11px;"></i>
                                    @else
                                        {{ $stNum }}
                                    @endif
                                </span>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">
                                        {{ $stData['name'] }}
                                    </h6>
                                    <span class="text-muted" style="font-size: 11.5px;">
                                        <i class="fa-regular fa-calendar me-1"></i>Hạn chót: {{ $stData['deadline'] }}
                                    </span>
                                </div>
                            </div>

                            {{-- Badge trạng thái mốc --}}
                            <div>
                                @if(!$bc)
                                    <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                        Chưa nộp bài
                                    </span>
                                @elseif($isDone)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px;">
                                        <i class="fa-solid fa-check-circle me-1"></i>Đã nghiệm thu (Đạt)
                                    </span>
                                @elseif($isPending)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px;">
                                        <i class="fa-solid fa-clock-rotate-left me-1"></i>Chờ GVHD duyệt
                                    </span>
                                @elseif($isResubmit)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px;">
                                        <i class="fa-solid fa-rotate-left me-1"></i>Yêu cầu nộp lại
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Tóm tắt AI Map-Reduce nếu có --}}
                        @if($ttAi && !empty($ttAi->CongViecDaHoanThanh))
                            <div class="p-2.5 rounded-3 bg-light border mb-2.5" style="font-size: 12px; line-height: 1.45;">
                                <div class="fw-bold text-primary mb-1 d-flex align-items-center gap-1.5">
                                    <i class="fa-solid fa-wand-magic-sparkles text-warning"></i>
                                    <span>Trợ Lý AI Map-Reduce Trích Xuất:</span>
                                </div>
                                <div class="text-secondary">
                                    {{ Str::limit($ttAi->CongViecDaHoanThanh, 160) }}
                                </div>
                            </div>
                        @endif

                        {{-- Thanh công cụ tài liệu & Nút Phê Duyệt Chi Tiết --}}
                        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap pt-2 border-top">
                            <div class="d-flex align-items-center gap-2">
                                @if($bc && $bc->TenFile)
                                    <a href="{{ asset('storage/' . $bc->DuongDanFile) }}" target="_blank" 
                                       class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1" 
                                       style="font-size: 11.5px;" title="Xem file báo cáo PDF">
                                        <i class="fa-solid fa-file-pdf"></i>
                                        <span>PDF Báo Cáo</span>
                                    </a>
                                @endif

                                @if($bc && $bc->link_code)
                                    <a href="{{ $bc->link_code }}" target="_blank" 
                                       class="btn btn-sm btn-outline-dark rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1" 
                                       style="font-size: 11.5px;" title="Kho mã nguồn">
                                        <i class="fa-brands fa-github"></i>
                                        <span>Source Code</span>
                                    </a>
                                @endif

                                @if($hasKhois)
                                    <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 11px;">
                                        {{ $bc->khoiBaoCaos->count() }} khối nộp bài
                                    </span>
                                @endif
                            </div>

                            <div>
                                @if($bc)
                                    <a href="{{ route('giangvien.baocao.show', $bc->MaBaoCao) }}" 
                                       class="btn btn-sm {{ $isPending ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" 
                                       style="font-size: 12px;">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span>{{ $isPending ? 'Phê Duyệt & Chấm AI' : 'Xem Chi Tiết & Lịch Sử' }}</span>
                                    </a>
                                @else
                                    <span class="text-muted small fst-italic" style="font-size: 11.5px;">
                                        Chưa có bài nộp từ sinh viên
                                    </span>
                                @endif
                            </div>
                        </div>

                    </div>
                    @endforeach
                </div>

            </div>
            @endforeach
        </div>

    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
/**
 * Chuyển đổi hiển thị nhóm chi tiết khi người dùng click vào thẻ nhóm bên trái
 */
function selectGroup(groupId) {
    if (!groupId) return;

    // 1. Cập nhật class active cho thẻ nhóm bên cột trái
    const cards = document.querySelectorAll('.group-item-card');
    cards.forEach(card => {
        if (card.getAttribute('data-group-id') === groupId) {
            card.classList.add('active');
        } else {
            card.classList.remove('active');
        }
    });

    // 2. Ẩn tất cả các panel bên cột phải, chỉ hiển thị panel của nhóm được chọn
    const panels = document.querySelectorAll('.group-detail-panel');
    panels.forEach(panel => {
        if (panel.id === 'panel-group-' + groupId) {
            panel.classList.remove('d-none');
        } else {
            panel.classList.add('d-none');
        }
    });
}

// Tìm kiếm nhanh nhóm & kiểm tra tham số URL
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchGroupInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.group-item-card');
            cards.forEach(card => {
                const text = card.getAttribute('data-search') || '';
                if (!query || text.includes(query)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // Nếu trên URL có param ?nhom=N01 thì tự động chọn nhóm đó
    const urlParams = new URLSearchParams(window.location.search);
    const targetGroup = urlParams.get('nhom');
    if (targetGroup) {
        selectGroup(targetGroup);
    }
});
</script>
@endpush