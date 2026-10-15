@extends('layouts.sinhvien')

@section('page_title', 'Công Việc & Tiến Độ Của Tôi')

@section('content')

<!-- Header Trang -->
<div class="mb-4">
    <h4 class="fw-bold text-dark mb-0">
        <i class="fa-solid fa-list-check text-primary me-2"></i>Công Việc & Tiến Độ Khóa Luận
    </h4>
</div>

@if($nhom)
<!-- Thông Tin Nhóm & Đề Tài -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary text-white rounded-pill px-3 py-1 font-monospace">
                        {{ $nhom->MaNhom }}
                    </span>
                    <span class="fw-bold text-dark fs-6">{{ $nhom->TenNhom }}</span>
                </div>
                <h5 class="fw-bold text-primary mb-2">
                    {{ $nhom->deTai->TenDeTai ?? 'Chưa được phân công đề tài' }}
                </h5>
                <div class="text-muted d-flex flex-wrap gap-3" style="font-size: 13px;">
                    <span><i class="fa-solid fa-user-tie text-secondary me-1"></i>GVHD: <strong class="text-dark">{{ $nhom->dangKyDeTai->giangVienHuongDan->HoTen ?? 'Chưa phân công' }}</strong></span>
                    <span><i class="fa-solid fa-crown text-warning me-1"></i>Trưởng nhóm: <strong class="text-dark">{{ $nhom->truongNhom->HoTen ?? '' }}</strong></span>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="p-3 bg-light rounded-3 border d-inline-block text-start w-100" style="max-width: 280px;">
                    <div class="text-muted mb-1" style="font-size: 13px;">Báo cáo tiến độ:</div>
                    <div class="d-flex align-items-baseline gap-2">
                        <span class="fs-4 fw-bold text-success">{{ $nhom->baoCaos->count() }}</span>
                        <span class="text-muted" style="font-size: 13px;">/ 5 mốc theo quy định</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Danh Sách Mốc Thời Gian & Công Việc -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left text-primary fs-5"></i>
        <h5 class="mb-0 fw-bold text-dark fs-6">Kế Hoạch &amp; Hạn Nộp Báo Cáo</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th width="32%">Công Việc / Mốc Kế Hoạch</th>
                        <th width="25%">Thời Gian Thực Hiện</th>
                        <th width="20%">Trạng Thái</th>
                        <th width="18%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taskList as $idx => $task)
                    <tr>
                        <td class="text-center fw-bold text-muted" style="font-size: 13px;">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $task['moc']->TenMoc }}</div>
                            <span class="badge bg-light text-secondary border rounded-pill mt-1" style="font-size: 12px;">
                                {{ $task['moc']->LoaiGiaiDoan }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold text-primary" style="font-size: 13px;">
                                <i class="fa-regular fa-calendar-days me-1"></i>Từ: {{ date('d/m/Y', strtotime($task['moc']->NgayBatDau)) }}
                            </div>
                            <div class="text-danger mt-0.5" style="font-size: 13px;">
                                <i class="fa-regular fa-clock me-1"></i>Đến: {{ date('d/m/Y', strtotime($task['moc']->NgayKetThuc)) }}
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $task['badge_class'] }} rounded-pill px-3 py-1.5 fw-semibold">
                                {{ $task['status_label'] }}
                            </span>
                            @if($task['warning'])
                                <div class="text-danger fw-semibold mt-1" style="font-size: 12.5px;">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $task['warning'] }}
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(str_contains($task['moc']->LoaiGiaiDoan, 'BAO_CAO_TIEN_DO'))
                                <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                                    <i class="fa-solid fa-upload me-1"></i>Nộp báo cáo
                                </a>
                            @elseif($task['moc']->LoaiGiaiDoan === 'DAO_VAN')
                                <a href="{{ route('sinhvien.hosoBaoVe.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="fa-solid fa-file-shield me-1"></i>Nộp Turnitin
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fa-regular fa-calendar-xmark fa-2x mb-2 d-block opacity-50"></i>
                            Chưa có mốc thời gian kế hoạch nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
{{-- Chưa có nhóm --}}
<div class="card shadow-sm border-0">
    <div class="card-body text-center py-5 px-4">
        <div class="mb-3">
            <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning" style="width: 72px; height: 72px;">
                <i class="fa-solid fa-users-slash fa-2x"></i>
            </span>
        </div>
        <h5 class="fw-bold text-dark mb-2">Bạn chưa tham gia nhóm đồ án nào</h5>
        <p class="text-muted mx-auto mb-4" style="max-width: 500px; font-size: 13.5px;">
            Vui lòng tạo nhóm hoặc gia nhập nhóm khóa luận để theo dõi lịch trình và nộp báo cáo theo quy định.
        </p>
        <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fa-solid fa-users me-1"></i>Đến Quản lý nhóm
        </a>
    </div>
</div>
@endif

@endsection
