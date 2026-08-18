@extends('layouts.sinhvien')
@section('page_title', 'Hồ Sơ Bảo Vệ Khóa Luận')
@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-3">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3">
    <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(!$nhom || !$nhom->MaDeTai)
<div class="card card-premium">
    <div class="card-body text-center py-5">
        <i class="fa-solid fa-lock fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Bạn chưa đủ điều kiện nộp hồ sơ bảo vệ</h5>
        <p class="text-muted">Bạn cần có nhóm và đề tài được duyệt trước.</p>
    </div>
</div>
@elseif(!$moc5Dat)
<div class="card card-premium">
    <div class="card-body text-center py-5">
        <i class="fa-solid fa-hourglass-half fa-3x text-warning mb-3"></i>
        <h5 class="text-muted">Chưa hoàn thành 5 Mốc Tiến Độ</h5>
        <p class="text-muted mb-4">Bạn cần hoàn thành và được Giảng viên đánh giá <strong>"Đạt"</strong> cho tất cả 5 Mốc báo cáo trước khi nộp hồ sơ bảo vệ.</p>
        <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fa-solid fa-file-invoice me-2"></i>Đến Báo Cáo Tiến Độ
        </a>
    </div>
</div>
@elseif($hoSo)
<!-- Đã nộp hồ sơ -->
<div class="card card-premium mb-4">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-folder-check me-2 text-primary"></i>Hồ Sơ Bảo Vệ — {{ $nhom->TenNhom }}</span>
        @php
            $bc = match($hoSo->TrangThai) {
                'Đã xác nhận', 'Đã phân công' => 'bg-success',
                default => 'bg-warning text-dark',
            };
        @endphp
        <span class="badge {{ $bc }} rounded-pill px-3">{{ $hoSo->TrangThai }}</span>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <h6 class="fw-bold mb-3">📋 Thông Tin Hồ Sơ</h6>
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">Ngày nộp:</td><td><strong>{{ $hoSo->NgayNop }}</strong></td></tr>
                    <tr>
                        <td class="text-muted">Turnitin:</td>
                        <td>
                            @php $pct = (float)$hoSo->TyLeTrungLap; @endphp
                            <strong class="fs-5 {{ $pct > 30 ? 'text-danger' : ($pct > 15 ? 'text-warning' : 'text-success') }}">{{ $pct }}%</strong>
                            @if($pct <= 30) <i class="fa-solid fa-check-circle text-success ms-1"></i>
                            @else <i class="fa-solid fa-triangle-exclamation text-danger ms-1"></i> @endif
                        </td>
                    </tr>
                    @if($hoSo->MinhChungDaoVan)
                    <tr>
                        <td class="text-muted">Báo cáo Turnitin:</td>
                        <td><a href="{{ asset('storage/' . $hoSo->MinhChungDaoVan) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill">Xem PDF</a></td>
                    </tr>
                    @endif
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold mb-3">🏛️ Phân Công Hội Đồng</h6>
                @if($hoSo->hoiDong)
                <div class="p-3 rounded-3" style="background: #f0f7ff; border: 1px solid #cce5ff;">
                    <div class="fw-bold text-primary">{{ $hoSo->hoiDong->TenHoiDong }}</div>
                    <div class="small text-muted mt-1">
                        <i class="fa-solid fa-clock me-1"></i>{{ \Carbon\Carbon::parse($hoSo->hoiDong->ThoiGianBatDau)->format('d/m/Y H:i') }}
                    </div>
                    @if($hoSo->hoiDong->DiaDiem)
                    <div class="small text-muted"><i class="fa-solid fa-location-dot me-1"></i>{{ $hoSo->hoiDong->DiaDiem }}</div>
                    @endif
                </div>
                @else
                <p class="text-muted">Giáo vụ chưa phân công Hội đồng. Vui lòng chờ thông báo.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@else
<!-- Chưa nộp hồ sơ -->
<div class="card card-premium">
    <div class="card-header-premium">
        <i class="fa-solid fa-file-circle-plus me-2 text-primary"></i>Nộp Hồ Sơ Bảo Vệ Khóa Luận
    </div>
    <div class="card-body">
        <div class="alert alert-success rounded-3 mb-4">
            <i class="fa-solid fa-check-circle me-2"></i>
            <strong>Chúc mừng!</strong> Bạn đã hoàn thành cả 5 Mốc báo cáo tiến độ. Hãy nộp hồ sơ bảo vệ để tiến hành buổi bảo vệ khóa luận.
        </div>

        <form action="{{ route('sinhvien.hoso.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tỷ Lệ Trùng Lặp Turnitin (%) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="TyLeTrungLap" class="form-control" step="0.01" min="0" max="100"
                            placeholder="VD: 12.5" value="{{ old('TyLeTrungLap') }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="form-text text-muted">Nhập tỷ lệ trùng lặp từ báo cáo Turnitin. Yêu cầu dưới 30%.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Báo Cáo Turnitin (PDF) <span class="text-muted">(tùy chọn)</span></label>
                    <input type="file" name="MinhChungDaoVan" class="form-control" accept=".pdf">
                    <div class="form-text text-muted">Upload file PDF báo cáo Turnitin làm minh chứng.</div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Ghi Chú Thêm</label>
                    <textarea name="GhiChu" class="form-control" rows="2" placeholder="Ghi chú thêm cho Giáo vụ Khoa...">{{ old('GhiChu') }}</textarea>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">
                    <i class="fa-solid fa-paper-plane me-2"></i>Nộp Hồ Sơ Bảo Vệ
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection
