@extends('layouts.sinhvien')
@section('page_title', 'Hồ Sơ Bảo Vệ Khóa Luận')
@section('content')





@if(!$nhom || !$nhom->MaDeTai)
<div class="card card-premium shadow-sm border-0">
    <div class="card-body text-center py-5">
        <div class="mb-3">
            <span class="avatar-lg bg-light-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                <i class="fa-solid fa-lock fa-3x text-muted"></i>
            </span>
        </div>
        <h4 class="fw-bold text-dark mb-2">Chưa đủ điều kiện nộp hồ sơ</h4>
        <p class="text-muted mx-auto" style="max-width: 500px;">
            Nhóm của bạn cần có nhóm hoàn chỉnh và đề tài khóa luận đã được phê duyệt trước khi có thể nộp hồ sơ bảo vệ.
        </p>
        <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fa-solid fa-users me-2"></i>Quản lý nhóm
        </a>
    </div>
</div>

@elseif(!$moc5Dat)
<div class="card card-premium shadow-sm border-0">
    <div class="card-body text-center py-5">
        <div class="mb-3">
            <span class="avatar-lg bg-light-warning rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                <i class="fa-solid fa-hourglass-half fa-3x text-warning"></i>
            </span>
        </div>
        <h4 class="fw-bold text-dark mb-2">Chưa hoàn thành 5 Mốc Báo Cáo Tiến Độ</h4>
        <p class="text-muted mx-auto mb-4" style="max-width: 550px;">
            Theo quy định, nhóm khóa luận phải hoàn thành và được Giảng viên hướng dẫn đánh giá 
            <strong class="text-success">"Đạt"</strong> ở cả 5 Mốc báo cáo tiến độ mới đủ điều kiện nộp hồ sơ Turnitin và xin bảo vệ.
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-primary rounded-pill px-4">
                <i class="fa-solid fa-file-invoice me-2"></i>Đến Báo Cáo Tiến Độ
            </a>
            <a href="{{ route('sinhvien.dashboard') }}" class="btn btn-light rounded-pill px-4">
                Về Dashboard
            </a>
        </div>
    </div>
</div>

@elseif($hoSo && $hoSo->TrangThai !== 'Không đủ điều kiện')
<!-- Đã nộp hồ sơ hợp lệ / đang chờ thẩm định / đã duyệt -->
<div class="card card-premium shadow-sm border-0 mb-4">
    <div class="card-header-premium d-flex justify-content-between align-items-center py-3">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-shield-halved text-primary fs-5"></i>
            <h5 class="mb-0 fw-bold">Hồ Sơ Bảo Vệ Khóa Luận — {{ $nhom->TenNhom }}</h5>
        </div>
        @php
            $statusBadge = match($hoSo->TrangThai) {
                'Đã phân công', 'Đã phân công hội đồng' => ['class' => 'bg-success text-white', 'icon' => 'fa-circle-check', 'label' => 'Đã phân công Hội đồng'],
                'Đủ điều kiện bảo vệ' => ['class' => 'bg-primary text-white', 'icon' => 'fa-badge-check', 'label' => 'Đủ điều kiện bảo vệ'],
                'Chờ thẩm định' => ['class' => 'bg-warning text-dark', 'icon' => 'fa-hourglass-start', 'label' => 'Chờ Giáo vụ thẩm định'],
                default => ['class' => 'bg-secondary text-white', 'icon' => 'fa-info-circle', 'label' => $hoSo->TrangThai],
            };
        @endphp
        <span class="badge {{ $statusBadge['class'] }} rounded-pill px-3 py-2 fs-6 fw-semibold">
            <i class="fa-solid {{ $statusBadge['icon'] }} me-1"></i>{{ $statusBadge['label'] }}
        </span>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <!-- Cột trái: Thông tin Hồ sơ & Turnitin -->
            <div class="col-lg-6">
                <div class="p-3 bg-light rounded-3 h-100 border">
                    <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-file-shield text-primary me-2"></i>Kết Quả Thẩm Định Turnitin
                    </h6>

                    <div class="mb-3 d-flex align-items-center justify-content-between p-3 bg-white rounded-3 border">
                        <div>
                            <div class="text-muted small">Tỷ lệ trùng lặp Turnitin:</div>
                            @php $pct = (float)$hoSo->TyLeTrungLap; @endphp
                            <div class="display-6 fw-bold {{ $pct <= 20 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($pct, 2) }}%
                            </div>
                        </div>
                        <div class="text-end">
                            @if($pct <= 20)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
                                    <i class="fa-solid fa-check-circle me-1"></i>Hợp lệ (&le; 20%)
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-semibold">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>Vượt ngưỡng (&gt; 20%)
                                </span>
                            @endif
                        </div>
                    </div>

                    <table class="table table-sm table-borderless mb-3">
                        <tr>
                            <td class="text-muted" width="40%">Mã hồ sơ:</td>
                            <td><span class="badge bg-secondary font-monospace">{{ $hoSo->MaHoSo }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Ngày nộp:</td>
                            <td><strong>{{ \Carbon\Carbon::parse($hoSo->NgayNop)->format('d/m/Y') }}</strong></td>
                        </tr>
                        @if($hoSo->NgayXacNhan)
                        <tr>
                            <td class="text-muted">Ngày duyệt:</td>
                            <td><strong>{{ \Carbon\Carbon::parse($hoSo->NgayXacNhan)->format('d/m/Y') }}</strong></td>
                        </tr>
                        @endif
                        @if($hoSo->GhiChu)
                        <tr>
                            <td class="text-muted">Ghi chú:</td>
                            <td class="small">{{ $hoSo->GhiChu }}</td>
                        </tr>
                        @endif
                    </table>

                    <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                        @if($hoSo->ToanVanKhoaLuan)
                            <a href="{{ asset('storage/' . $hoSo->ToanVanKhoaLuan) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="fa-solid fa-file-pdf me-1"></i>Xem Khóa Luận Toàn Văn
                            </a>
                        @endif
                        @if($hoSo->MinhChungDaoVan)
                            <a href="{{ asset('storage/' . $hoSo->MinhChungDaoVan) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                <i class="fa-solid fa-file-shield me-1"></i>Xem Minh Chứng Turnitin
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Cột phải: Lịch bảo vệ & Hội đồng -->
            <div class="col-lg-6">
                <div class="p-3 bg-light rounded-3 h-100 border">
                    <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-landmark text-primary me-2"></i>Hội Đồng Bảo Vệ & Phản Biện
                    </h6>

                    @if($hoSo->hoiDong)
                        <div class="p-3 bg-white rounded-3 border mb-3">
                            <div class="fw-bold text-primary fs-6 mb-2">
                                <i class="fa-solid fa-building-columns me-2"></i>{{ $hoSo->hoiDong->TenHoiDong }}
                            </div>
                            <div class="row g-2 small text-secondary">
                                <div class="col-sm-6">
                                    <i class="fa-regular fa-calendar-days text-primary me-1"></i>
                                    <strong>Thời gian:</strong>
                                    {{ \Carbon\Carbon::parse($hoSo->ThoiGianBaoVe ?? $hoSo->hoiDong->ThoiGianBatDau)->format('d/m/Y H:i') }}
                                </div>
                                <div class="col-sm-6">
                                    <i class="fa-solid fa-location-dot text-danger me-1"></i>
                                    <strong>Phòng:</strong>
                                    {{ $hoSo->PhongBaoVe ?? $hoSo->hoiDong->DiaDiem ?? 'Theo thông báo' }}
                                </div>
                            </div>
                        </div>

                        <!-- Thành viên hội đồng -->
                        <div class="mb-3">
                            <div class="small fw-bold text-muted mb-2 text-uppercase">Thành viên Hội đồng:</div>
                            <div class="d-flex flex-column gap-1">
                                @foreach($hoSo->hoiDong->thanhViens as $tv)
                                <div class="d-flex justify-content-between align-items-center bg-white p-2 rounded border small">
                                    <div>
                                        <i class="fa-solid fa-user-tie text-secondary me-2"></i>
                                        <strong>{{ $tv->giangVien->HoTen ?? 'Giảng viên' }}</strong>
                                        <span class="text-muted">({{ $tv->giangVien->HocVi ?? '' }})</span>
                                    </div>
                                    @php
                                        $tvBadge = match($tv->VaiTro) {
                                            'Chủ tịch' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                            'Thư ký'   => 'bg-info-subtle text-info border border-info-subtle',
                                            default    => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                        };
                                    @endphp
                                    <span class="badge {{ $tvBadge }} rounded-pill">{{ $tv->VaiTro }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Giảng viên phản biện -->
                        <div class="p-2 bg-white rounded border d-flex justify-content-between align-items-center small">
                            <div>
                                <i class="fa-solid fa-user-check text-success me-2"></i>
                                <span class="text-muted">Giảng viên Phản biện:</span>
                                <strong>{{ $hoSo->giangVienPhanBien->HoTen ?? 'Chưa phân công' }}</strong>
                            </div>
                            @if($hoSo->giangVienPhanBien)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">GVPB</span>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fa-solid fa-hourglass-start fa-2x text-warning mb-2"></i>
                            <div class="fw-bold">Hồ sơ đã được tiếp nhận</div>
                            <p class="small mb-0">Giáo vụ Khoa đang tiến hành thẩm định và xếp lịch bảo vệ. Vui lòng theo dõi thông báo.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@else
<!-- Form nộp hồ sơ (Chưa nộp hoặc bị từ chối) -->
<div class="card card-premium shadow-sm border-0 mb-4">
    <div class="card-header-premium py-3">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-cloud-arrow-up text-primary fs-5"></i>
            <h5 class="mb-0 fw-bold">Nộp Hồ Sơ Bảo Vệ Khóa Luận (Turnitin & Toàn Văn)</h5>
        </div>
    </div>
    <div class="card-body p-4">
        @if($hoSo && $hoSo->TrangThai === 'Không đủ điều kiện')
        <div class="alert alert-danger mb-4 rounded-3 border-danger-subtle">
            <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
            <strong>Hồ sơ trước đó bị từ chối:</strong> {{ $hoSo->GhiChu }}
            <div class="mt-1 small">Vui lòng kiểm tra lại tỷ lệ đạo văn Turnitin (&le; 20%) và nộp lại hồ sơ đầy đủ dưới đây.</div>
        </div>
        @else
        <div class="alert alert-info mb-4 rounded-3 border-info-subtle">
            <div class="d-flex align-items-center mb-1">
                <i class="fa-solid fa-circle-check text-success me-2 fs-5"></i>
                <strong class="text-dark">Chúc mừng! Nhóm đã hoàn thành xuất sắc 5 Mốc Báo Cáo Tiến Độ.</strong>
            </div>
            <div class="small text-muted ps-4">
                Vui lòng nộp bản Báo cáo khóa luận toàn văn (PDF) và kết quả kiểm tra đạo văn Turnitin hợp lệ (Tỷ lệ &le; 20%) để Giáo vụ Khoa thẩm định và xếp lịch bảo vệ.
            </div>
        </div>
        @endif

        <form action="{{ route('sinhvien.hoso.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-4">
                <!-- Tỷ lệ Turnitin -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">
                        Tỷ Lệ Trùng Lặp Turnitin (%) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="number" name="TyLeTrungLap" class="form-control" step="0.01" min="0" max="100"
                            placeholder="VD: 14.5" value="{{ old('TyLeTrungLap') }}" required>
                        <span class="input-group-text bg-light fw-bold">%</span>
                    </div>
                    <div class="form-text text-muted">
                        <i class="fa-solid fa-circle-info me-1"></i>Quy định của Khoa: Tỷ lệ đạo văn Turnitin phải <strong>&le; 20%</strong> mới đủ điều kiện bảo vệ.
                    </div>
                </div>

                <!-- Báo cáo toàn văn PDF -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">
                        Báo Cáo Khóa Luận Toàn Văn (PDF) <span class="text-danger">*</span>
                    </label>
                    <input type="file" name="FileToanVan" class="form-control" accept=".pdf" required>
                    <div class="form-text text-muted">
                        <i class="fa-solid fa-file-pdf me-1"></i>Bản hoàn chỉnh toàn văn khóa luận (PDF, tối đa 30MB).
                    </div>
                </div>

                <!-- Minh chứng Turnitin PDF -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">
                        Minh Chứng Kiểm Tra Đạo Văn Turnitin (PDF) <span class="text-danger">*</span>
                    </label>
                    <input type="file" name="MinhChungTurnitin" class="form-control" accept=".pdf" required>
                    <div class="form-text text-muted">
                        <i class="fa-solid fa-file-shield me-1"></i>Tệp PDF báo cáo chi tiết xuất từ Turnitin (PDF, tối đa 20MB).
                    </div>
                </div>

                <!-- Ghi chú thêm -->
                <div class="col-md-6">
                    <label class="form-label fw-bold">Ghi Chú Thêm (Nếu có)</label>
                    <textarea name="GhiChu" class="form-control" rows="2" placeholder="Ghi chú thêm gửi đến Giáo vụ Khoa...">{{ old('GhiChu') }}</textarea>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                <a href="{{ route('sinhvien.dashboard') }}" class="btn btn-light rounded-pill px-4">Hủy</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                    <i class="fa-solid fa-paper-plane me-2"></i>Nộp Hồ Sơ Bảo Vệ
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

