@extends('layouts.sinhvien')

@section('page_title', 'Công Việc & Tiến Độ Của Tôi')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold text-primary-custom mb-1"><i class="fa-solid fa-list-check me-2"></i>Công Việc & Lịch Khóa Luận Của Tôi</h4>
    <p class="text-muted small mb-0">Hệ thống nhắc nhở công việc, mốc thời gian và hạn nộp báo cáo khóa luận cá nhân/nhóm.</p>
</div>

@if($nhom)
<!-- Group & Topic Header -->
<div class="card card-premium mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span class="badge bg-primary fs-6 px-3 py-1 rounded-pill mb-2">{{ $nhom->MaNhom }} - {{ $nhom->TenNhom }}</span>
                <h4 class="fw-bold text-primary mb-2">{{ $nhom->deTai->TenDeTai ?? 'Chưa được phân công đề tài' }}</h4>
                <p class="text-muted mb-0"><i class="fa-solid fa-user-tie me-1"></i><strong>GVHD:</strong> {{ $nhom->dangKyDeTai->giangVienHuongDan->HoTen ?? 'Chưa phân công' }} | <strong>Trưởng nhóm:</strong> {{ $nhom->truongNhom->HoTen ?? '' }}</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="small text-muted mb-1">Tiến độ báo cáo tiến độ:</div>
                    <div class="fw-bold fs-4 text-success">{{ $nhom->baoCaos->count() }} <span class="fs-6 text-muted">/ 3 lần</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Task Checklist & Deadlines -->
<div class="card card-premium mb-4">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Danh Sách Mốc Thời Gian & Công Việc Cần Thực Hiện</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="30%">Tên Công Việc / Mốc Kế Hoạch</th>
                        <th width="20%">Thời Gian Bắt Đầu - Hạn Nộp</th>
                        <th width="20%">Trạng Thái Thực Tế</th>
                        <th width="25%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taskList as $idx => $task)
                    <tr>
                        <td class="fw-bold text-muted">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $task['moc']->TenMoc }}</div>
                            <small class="text-muted">{{ $task['moc']->LoaiGiaiDoan }}</small>
                        </td>
                        <td>
                            <div class="small fw-semibold text-primary"><i class="fa-regular fa-calendar-days me-1"></i>{{ date('d/m/Y', strtotime($task['moc']->NgayBatDau)) }}</div>
                            <div class="small text-muted"><i class="fa-solid fa-arrow-right me-1"></i>{{ date('d/m/Y', strtotime($task['moc']->NgayKetThuc)) }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $task['badge_class'] }} rounded-pill px-3 py-2">
                                {{ $task['status_label'] }}
                            </span>
                            @if($task['warning'])
                                <div class="small text-danger fw-bold mt-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $task['warning'] }}</div>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(str_contains($task['moc']->LoaiGiaiDoan, 'BAO_CAO_TIEN_DO'))
                                <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                    <i class="fa-solid fa-upload me-1"></i> Nộp báo cáo
                                </a>
                            @elseif($task['moc']->LoaiGiaiDoan === 'DAO_VAN')
                                <a href="{{ route('sinhvien.hosoBaoVe.index') }}" class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-sm">
                                    <i class="fa-solid fa-file-check me-1"></i> Nộp Turnitin
                                </a>
                            @else
                                <span class="text-muted small">---</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Chưa có mốc thời gian kế hoạch nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="alert alert-warning p-4 rounded-3 text-center">
    <i class="fa-solid fa-users-slash fs-1 mb-2"></i>
    <h5>Bạn chưa tham gia nhóm đồ án nào!</h5>
    <p class="mb-3 text-muted">Vui lòng tạo nhóm hoặc tham gia nhóm để xem danh sách công việc và mốc nộp báo cáo.</p>
    <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-primary rounded-pill px-4">Đến trang Quản lý nhóm</a>
</div>
@endif
@endsection
