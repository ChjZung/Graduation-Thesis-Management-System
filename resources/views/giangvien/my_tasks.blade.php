@extends('layouts.giangvien')

@section('page_title', 'Công Việc Hướng Dẫn & Tiến Độ Nhóm')

@push('styles')
<style>
    :root {
        --gv-border: #e2e8f0;
        --gv-bg-subtle: #f8fafc;
    }

    .stat-card-widget {
        background: #ffffff;
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
        border: 1px solid var(--gv-border);
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02);
        transition: all 0.2s ease-in-out;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card-widget:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.05);
    }

    .card-dashboard {
        background: #ffffff;
        border: 1px solid var(--gv-border);
        border-radius: 1rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }

    .table-tasks th {
        background: var(--gv-bg-subtle);
        font-size: 0.72rem;
        font-weight: 700;
        color: #475569;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        border-bottom: 1px solid var(--gv-border);
        padding: 1rem 1.25rem;
        white-space: nowrap;
    }

    .table-tasks td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    .table-tasks tbody tr {
        transition: background-color 0.15s ease;
    }

    .table-tasks tbody tr:hover {
        background-color: var(--gv-bg-subtle);
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

    <!-- HEADER TITLE & ACTION BUTTON -->
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-primary"></i>
                <span>Công Việc Hướng Dẫn &amp; Tiến Độ Các Nhóm</span>
            </h4>
            <p class="text-muted small mb-0">
                Theo dõi tiến độ thực hiện khóa luận của các nhóm sinh viên và kiểm tra điều kiện bảo vệ.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('giangvien.calendar') }}" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 shadow-sm fw-medium small">
                <i class="fa-regular fa-calendar-days me-1.5 text-primary"></i> Lịch Công Việc &amp; Hội Đồng
            </a>
            <a href="{{ route('giangvien.baocao.index') }}" class="btn btn-primary rounded-pill px-3.5 py-1.5 shadow-sm fw-medium small">
                <i class="fa-solid fa-clipboard-check me-1.5"></i> Duyệt Báo Cáo Chi Tiết
            </a>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card-widget d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-medium d-block mb-1">Nhóm Đang Hướng Dẫn</span>
                    <h3 class="fw-bold text-primary mb-0">{{ $stats['tong_nhom'] ?? $nhoms->count() }}</h3>
                </div>
                <div class="text-primary fs-2 opacity-25">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card-widget d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-medium d-block mb-1">Báo Cáo Sắp Đến Hạn</span>
                    <h3 class="fw-bold text-warning mb-0">{{ $stats['sap_den_han'] ?? 0 }}</h3>
                </div>
                <div class="text-warning fs-2 opacity-25">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card-widget d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-medium d-block mb-1">Nhóm Quá Hạn Báo Cáo</span>
                    <h3 class="fw-bold text-danger mb-0">{{ $stats['qua_han'] ?? 0 }}</h3>
                </div>
                <div class="text-danger fs-2 opacity-25">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card-widget d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-medium d-block mb-1">Báo Cáo Chờ Nhận Xét</span>
                    <h3 class="fw-bold text-success mb-0">{{ $stats['cho_nhan_xet'] ?? 0 }}</h3>
                </div>
                <div class="text-success fs-2 opacity-25">
                    <i class="fa-solid fa-file-circle-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- LIST OF MANAGED GROUPS -->
    <div class="card-dashboard table-tasks mb-4">
        <div class="bg-white p-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-users text-primary"></i>
                <h6 class="fw-bold text-dark mb-0">Danh Sách Nhóm Đồ Án Hướng Dẫn &amp; Tiến Độ</h6>
            </div>
            <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill small fw-medium">
                Tổng cộng: <strong>{{ $nhoms->count() }}</strong> nhóm
            </span>
        </div>
        
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th width="12%" class="ps-4">Mã Nhóm</th>
                        <th width="34%">Tên Đề Tài &amp; Trưởng Nhóm</th>
                        <th width="24%">Tiến Độ</th>
                        <th width="16%">Hồ Sơ Đạo Văn</th>
                        <th width="14%" class="text-center pe-4">Thao Tác GVHD</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nhoms as $nhom)
                    @php
                        // Tính % tiến độ
                        $progress = $nhom->progress_percent ?? null;
                        if ($progress === null) {
                            $soMoc = 0;
                            for($m = 1; $m <= 6; $m++) {
                                if($nhom->baoCaos->where('LanBaoCao', $m)->count() > 0) {
                                    $soMoc++;
                                }
                            }
                            $progress = (int) round(($soMoc / 6) * 100);
                        }

                        $barColor = match(true) {
                            $progress >= 100 => 'bg-success',
                            $progress >= 50  => 'bg-primary',
                            $progress > 0    => 'bg-info',
                            default          => 'bg-secondary'
                        };

                        // Tìm báo cáo đang thực hiện để tạo link duyệt trực tiếp
                        $bcCurrent = $nhom->baoCaos->where('LanBaoCao', $nhom->current_stage ?? 1)->first() 
                                    ?? $nhom->baoCaos->sortByDesc('LanBaoCao')->first();
                        $targetUrl = $bcCurrent ? route('giangvien.baocao.show', $bcCurrent->MaBaoCao) : route('giangvien.baocao.index');
                    @endphp
                    <tr>
                        {{-- 1. Mã nhóm --}}
                        <td class="ps-4">
                            <span class="badge bg-light text-dark fw-bold border px-2.5 py-1.5 font-monospace">
                                {{ $nhom->MaNhom }}
                            </span>
                        </td>

                        {{-- 2. Tên đề tài & Trưởng nhóm --}}
                        <td>
                            <div class="fw-bold text-dark line-clamp-2" title="{{ $nhom->deTai->TenDeTai ?? 'Chưa gán đề tài' }}">
                                {{ $nhom->deTai->TenDeTai ?? 'Chưa gán đề tài' }}
                            </div>
                            <div class="small text-muted mt-1 d-flex align-items-center gap-1">
                                <i class="fa-solid fa-crown text-warning" style="font-size: 11px;"></i>
                                <span>Trưởng nhóm: <strong>{{ $nhom->truongNhom->HoTen ?? 'Chưa xác định' }}</strong></span>
                            </div>
                        </td>

                        {{-- 3. Tiến độ (Thanh tiến độ % trực quan, không mô tả chữ rườm rà) --}}
                        <td>
                            <div class="d-flex align-items-center gap-2" style="min-width: 140px;">
                                <div class="progress flex-grow-1" style="height: 8px; border-radius: 6px; background: #e2e8f0;">
                                    <div class="progress-bar {{ $barColor }}" role="progressbar" 
                                         style="width: {{ $progress }}%; border-radius: 6px; transition: width 0.3s ease;" 
                                         aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="fw-bold text-dark font-monospace" style="font-size: 12px; min-width: 38px; text-align: right;">
                                    {{ $progress }}%
                                </span>
                            </div>
                        </td>

                        {{-- 4. Hồ sơ đạo văn Turnitin --}}
                        <td>
                            @if($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->TyLeTrungLap !== null)
                                @php
                                    $tl = (float) $nhom->hoSoBaoVe->TyLeTrungLap;
                                    $turnitinBadge = $tl <= 20 ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle';
                                @endphp
                                <span class="badge {{ $turnitinBadge }} border rounded-pill px-2.5 py-1 fw-bold">
                                    <i class="fa-solid fa-shield-halved me-1"></i>Đạo văn: {{ $tl }}%
                                </span>
                            @else
                                <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small fw-normal">
                                    Chưa kiểm tra
                                </span>
                            @endif
                        </td>

                        {{-- 5. Thao tác GVHD --}}
                        <td class="text-center pe-4">
                            <a href="{{ $targetUrl }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 shadow-sm fw-medium text-nowrap">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Nhận xét
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <div class="py-3">
                                <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                                <p class="mb-0 fw-medium">Hiện tại bạn chưa được phân công hướng dẫn nhóm đồ án nào trong hệ thống.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection