@extends('layouts.sinhvien')

@section('page_title', 'Công Việc & Tiến Độ Của Tôi')

@section('content')
<style>
    /* Colors inherit the shared admin palette. */
    .task-container {
        font-size: 0.85rem;
        color: var(--admin-body-text);
    }

    /* Common Card Panels */
    .task-card-panel {
        background-color: var(--admin-surface);
        border: 1px solid var(--admin-border);
        border-radius: 12px;
        box-shadow: 0 1px 3px var(--shadow-sm);
        overflow: hidden;
    }

    .panel-header-compact {
        padding: 14px 20px;
        background-color: var(--admin-surface);
        border-bottom: 1px solid var(--admin-border-soft);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .panel-body-compact {
        padding: 18px 20px;
    }

    /* Custom Badges & Buttons */
    .badge-neutral {
        background-color: var(--admin-border-soft);
        color: var(--admin-text-secondary);
        border: 1px solid var(--admin-border);
    }

    .btn-cyan {
        background-color: var(--admin-primary);
        border-color: var(--admin-primary);
        color: var(--admin-surface);
    }
    .btn-cyan:hover, .btn-cyan:focus {
        background-color: var(--admin-primary-hover);
        border-color: var(--admin-primary-hover);
        color: var(--admin-surface);
    }

    .btn-outline-cyan {
        border-color: var(--admin-primary);
        color: var(--admin-primary);
    }
    .btn-outline-cyan:hover {
        background-color: var(--admin-primary);
        color: var(--admin-surface);
    }

    /* Table Styling */
    .task-table {
        margin-bottom: 0;
    }
    .task-table thead th {
        background-color: var(--admin-secondary-button-bg);
        color: var(--admin-text-secondary);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1px solid var(--admin-border);
        padding: 12px 16px;
    }
    .task-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        border-bottom: 1px solid var(--admin-border-soft);
    }
    .task-table tbody tr:last-child td {
        border-bottom: none;
    }
    .task-table tbody tr:hover {
        background-color: var(--admin-secondary-button-bg);
    }

    .kpi-summary-box {
        background-color: var(--admin-secondary-button-bg);
        border: 1px solid var(--admin-border);
        border-radius: 10px;
        padding: 12px 16px;
    }
</style>

<div class="task-container">

    {{-- â”€â”€ 1. TIÃŠU Äá»€ TRANG â”€â”€ --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1 d-flex align-items-center gap-2" style="color: var(--admin-primary-dark);">
                <i class="fa-solid fa-list-check" style="color: var(--admin-primary);"></i>
                <span>CÃ´ng Viá»‡c &amp; Tiáº¿n Äá»™ KhÃ³a Luáº­n</span>
            </h5>
            <div class="text-secondary" style="font-size: 0.825rem;">
                Theo dÃµi káº¿ hoáº¡ch thá»±c hiá»‡n, lá»‹ch ná»™p bÃ¡o cÃ¡o tiáº¿n Ä‘á»™ vÃ  cÃ¡c má»‘c Ä‘Ã¡nh giÃ¡ quan trá»ng.
            </div>
        </div>
        @if($nhom)
        <div>
            <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-cyan btn-sm rounded-pill px-3">
                <i class="fa-solid fa-upload me-1"></i>Ná»™p bÃ¡o cÃ¡o
            </a>
        </div>
        @endif
    </div>

    @if($nhom)
    {{-- â”€â”€ 2. THÃ”NG TIN NHÃ“M & Äá»€ TÃ€I Tá»”NG QUAN â”€â”€ --}}
    <div class="task-card-panel mb-3">
        <div class="panel-body-compact">
            <div class="row align-items-center g-3">
                <div class="col-12 col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.75rem;">
                            {{ $nhom->MaNhom }}
                        </span>
                        <span class="fw-bold text-dark" style="font-size: 0.95rem;">{{ $nhom->TenNhom }}</span>
                    </div>
                    <h6 class="fw-bold mb-2 text-truncate-2" style="color: var(--admin-primary-dark); line-height: 1.4; font-size: 1rem;">
                        {{ $nhom->deTai->TenDeTai ?? 'ChÆ°a Ä‘Æ°á»£c phÃ¢n cÃ´ng Ä‘á» tÃ i' }}
                    </h6>
                    <div class="text-muted d-flex flex-wrap gap-3" style="font-size: 0.8rem;">
                        <span><i class="fa-solid fa-chalkboard-user me-1" style="color: var(--admin-primary);"></i>GVHD: <strong style="color: var(--admin-primary-dark);">{{ $nhom->dangKyDeTai->giangVienHuongDan->HoTen ?? 'ChÆ°a phÃ¢n cÃ´ng' }}</strong></span>
                        <span><i class="fa-solid fa-crown text-warning me-1"></i>TrÆ°á»Ÿng nhÃ³m: <strong style="color: var(--admin-primary-dark);">{{ $nhom->truongNhom->HoTen ?? 'ChÆ°a cÃ³' }}</strong></span>
                    </div>
                </div>
                <div class="col-12 col-lg-4 text-lg-end">
                    <div class="kpi-summary-box d-inline-block text-start w-100" style="max-width: 280px;">
                        <div class="text-muted mb-1 fw-semibold" style="font-size: 0.75rem; text-transform: uppercase;">
                            <i class="fa-solid fa-bars-progress me-1" style="color: var(--admin-primary);"></i>BÃ¡o cÃ¡o tiáº¿n Ä‘á»™
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="fs-4 fw-bold" style="color: var(--admin-success);">{{ $nhom->baoCaos->count() }}</span>
                            <span class="text-muted" style="font-size: 0.8rem;">/ 5 má»‘c theo quy Ä‘á»‹nh</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- â”€â”€ 3. DANH SÃCH Má»C THá»œI GIAN & CÃ”NG VIá»†C â”€â”€ --}}
    <div class="task-card-panel mb-4">
        <div class="panel-header-compact">
            <div class="fw-bold d-flex align-items-center gap-2" style="color: var(--admin-primary-dark); font-size: 0.875rem;">
                <i class="fa-solid fa-clock-rotate-left" style="color: var(--admin-primary);"></i>
                <span>Káº¿ Hoáº¡ch &amp; Háº¡n Ná»™p BÃ¡o CÃ¡o</span>
            </div>
            <span class="badge badge-neutral rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                Tá»•ng cá»™ng: {{ count($taskList) }} má»‘c
            </span>
        </div>
        <div class="p-0">
            <div class="table-responsive">
                <table class="table task-table align-middle">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th width="32%">CÃ´ng Viá»‡c / Má»‘c Káº¿ Hoáº¡ch</th>
                            <th width="25%">Thá»i Gian Thá»±c Hiá»‡n</th>
                            <th width="20%">Tráº¡ng ThÃ¡i</th>
                            <th width="18%" class="text-center">Thao TÃ¡c</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($taskList as $idx => $task)
                        <tr>
                            <td class="text-center fw-bold text-muted" style="font-size: 0.8rem;">{{ $idx + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark" style="font-size: 0.875rem;">{{ $task['moc']->TenMoc }}</div>
                                <span class="badge badge-neutral rounded-pill mt-1 py-0.5 px-2" style="font-size: 0.725rem;">
                                    {{ $task['moc']->LoaiGiaiDoan }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold" style="font-size: 0.8rem; color: var(--admin-primary-dark);">
                                    <i class="fa-regular fa-calendar-days me-1" style="color: var(--admin-primary);"></i>Tá»«: {{ date('d/m/Y', strtotime($task['moc']->NgayBatDau)) }}
                                </div>
                                <div class="text-danger mt-1" style="font-size: 0.8rem;">
                                    <i class="fa-regular fa-clock me-1"></i>Äáº¿n: {{ date('d/m/Y', strtotime($task['moc']->NgayKetThuc)) }}
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $task['badge_class'] }} rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 0.75rem;">
                                    {{ $task['status_label'] }}
                                </span>
                                @if($task['warning'])
                                    <div class="text-danger fw-semibold mt-1" style="font-size: 0.75rem;">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $task['warning'] }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(str_contains($task['moc']->LoaiGiaiDoan, 'BAO_CAO_TIEN_DO'))
                                    <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-sm btn-cyan rounded-pill px-3">
                                        <i class="fa-solid fa-upload me-1"></i>Ná»™p bÃ¡o cÃ¡o
                                    </a>
                                @elseif($task['moc']->LoaiGiaiDoan === 'DAO_VAN')
                                    <a href="{{ route('sinhvien.hosoBaoVe.index') }}" class="btn btn-sm btn-outline-cyan rounded-pill px-3">
                                        <i class="fa-solid fa-file-shield me-1"></i>Ná»™p Turnitin
                                    </a>
                                @else
                                    <span class="text-muted" style="font-size: 0.85rem;">â€”</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-regular fa-calendar-xmark fa-2x mb-2 d-block opacity-50"></i>
                                <span style="font-size: 0.85rem;">ChÆ°a cÃ³ má»‘c thá»i gian káº¿ hoáº¡ch nÃ o Ä‘Æ°á»£c ghi nháº­n.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @else
    {{-- â”€â”€ 4. TRáº NG THÃI CHÆ¯A CÃ“ NHÃ“M (EMPTY STATE) â”€â”€ --}}
    <div class="task-card-panel">
        <div class="panel-body-compact text-center py-5 px-4">
            <div class="mb-3">
                <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 72px; height: 72px; background-color: var(--admin-info-soft); color: var(--admin-primary);">
                    <i class="fa-solid fa-users-slash fa-2x"></i>
                </span>
            </div>
            <h6 class="fw-bold mb-2" style="color: var(--admin-primary-dark); font-size: 1.05rem;">Báº¡n chÆ°a tham gia nhÃ³m Ä‘á»“ Ã¡n nÃ o</h6>
            <p class="text-muted mx-auto mb-4" style="max-width: 480px; font-size: 0.825rem; line-height: 1.5;">
                Vui lÃ²ng táº¡o nhÃ³m má»›i hoáº·c gia nháº­p nhÃ³m khÃ³a luáº­n Ä‘á»ƒ theo dÃµi toÃ n bá»™ káº¿ hoáº¡ch, nháº­n xÃ©t cá»§a GVHD vÃ  ná»™p bÃ¡o cÃ¡o theo quy Ä‘á»‹nh.
            </p>
            <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-cyan rounded-pill px-4">
                <i class="fa-solid fa-users me-1"></i>Äáº¿n Quáº£n lÃ½ nhÃ³m
            </a>
        </div>
    </div>
    @endif

</div>
@endsection