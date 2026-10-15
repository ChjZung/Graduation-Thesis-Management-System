@extends('layouts.sinhvien')
@section('page_title', 'Hồ Sơ Bảo Vệ Khóa Luận')
@section('content')

<!-- Header Trang -->
<div class="mb-4">
    <h4 class="fw-bold text-dark mb-1">
        <i class="fa-solid fa-shield-halved text-primary me-2"></i>Hồ Sơ Bảo Vệ Khóa Luận
    </h4>
    <p class="text-muted mb-0" style="font-size: 13.5px;">
        Quản lý hồ sơ thẩm định Turnitin, thông tin Hội đồng bảo vệ và nộp bản khóa luận hoàn chỉnh.
    </p>
</div>

@if(!$nhom || !$nhom->MaDeTai)
{{-- Trường hợp 1: Chưa có nhóm hoặc chưa có đề tài --}}
<div class="card shadow-sm border-0">
    <div class="card-body text-center py-5 px-4">
        <div class="mb-3">
            <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-light text-secondary" style="width: 72px; height: 72px;">
                <i class="fa-solid fa-lock fa-2x"></i>
            </span>
        </div>
        <h5 class="fw-bold text-dark mb-2">Chưa đủ điều kiện nộp hồ sơ</h5>
        <p class="text-muted mx-auto mb-4" style="max-width: 500px; font-size: 13.5px;">
            Nhóm cần hoàn tất đăng ký và được phê duyệt đề tài khóa luận trước khi thực hiện nộp hồ sơ bảo vệ.
        </p>
        <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fa-solid fa-users me-1"></i>Quản lý nhóm
        </a>
    </div>
</div>

@elseif(!$moc5Dat)
{{-- Trường hợp 2: Chưa đạt đủ 5 mốc tiến độ --}}
<div class="card shadow-sm border-0">
    <div class="card-body text-center py-5 px-4">
        <div class="mb-3">
            <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning" style="width: 72px; height: 72px;">
                <i class="fa-solid fa-hourglass-half fa-2x"></i>
            </span>
        </div>
        <h5 class="fw-bold text-dark mb-2">Chưa hoàn thành 5 mốc báo cáo tiến độ</h5>
        <p class="text-muted mx-auto mb-4" style="max-width: 540px; font-size: 13.5px;">
            Theo quy định, nhóm khóa luận cần hoàn thành và được Giảng viên hướng dẫn xác nhận 
            <strong class="text-success">"Đạt"</strong> ở cả 5 mốc báo cáo tiến độ để đủ điều kiện nộp hồ sơ bảo vệ.
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-primary rounded-pill px-4">
                <i class="fa-solid fa-chart-line me-1"></i>Đến Báo Cáo Tiến Độ
            </a>
            <a href="{{ route('sinhvien.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4">
                Về Trang Chủ
            </a>
        </div>
    </div>
</div>

@elseif($hoSo && $hoSo->TrangThai !== 'Không đủ điều kiện')
{{-- Trường hợp 3: Đã nộp hồ sơ hợp lệ --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-3 px-4 border-bottom gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-check text-success fs-5"></i>
            <h5 class="mb-0 fw-bold text-dark fs-6">Hồ Sơ Bảo Vệ — {{ $nhom->TenNhom }}</h5>
        </div>
        @php
            $statusBadge = match($hoSo->TrangThai) {
                'Đã phân công', 'Đã phân công hội đồng' => ['class' => 'bg-success text-white', 'icon' => 'fa-circle-check', 'label' => 'Đã phân công Hội đồng'],
                'Đủ điều kiện bảo vệ' => ['class' => 'bg-primary text-white', 'icon' => 'fa-badge-check', 'label' => 'Đủ điều kiện bảo vệ'],
                'Chờ thẩm định' => ['class' => 'bg-warning text-dark', 'icon' => 'fa-hourglass-start', 'label' => 'Chờ thẩm định'],
                default => ['class' => 'bg-secondary text-white', 'icon' => 'fa-circle-info', 'label' => $hoSo->TrangThai],
            };
        @endphp
        <span class="badge {{ $statusBadge['class'] }} rounded-pill px-3 py-2 fw-semibold">
            <i class="fa-solid {{ $statusBadge['icon'] }} me-1"></i>{{ $statusBadge['label'] }}
        </span>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <!-- Cột trái: Kết Quả Thẩm Định Turnitin -->
            <div class="col-lg-6">
                <div class="p-4 bg-light rounded-3 h-100 border">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-shield text-primary"></i>Kết Quả Thẩm Định Turnitin
                    </h6>

                    <div class="mb-3 d-flex align-items-center justify-content-between p-3 bg-white rounded-3 border">
                        <div>
                            <div class="text-muted" style="font-size: 13px;">Tỷ lệ trùng lặp:</div>
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

                    <table class="table table-sm table-borderless mb-3" style="font-size: 13.5px;">
                        <tr>
                            <td class="text-muted" width="38%">Mã hồ sơ:</td>
                            <td><span class="badge bg-light text-dark border font-monospace">{{ $hoSo->MaHoSo }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Ngày nộp:</td>
                            <td class="fw-semibold">{{ \Carbon\Carbon::parse($hoSo->NgayNop)->format('d/m/Y') }}</td>
                        </tr>
                        @if($hoSo->NgayXacNhan)
                        <tr>
                            <td class="text-muted">Ngày duyệt:</td>
                            <td class="fw-semibold">{{ \Carbon\Carbon::parse($hoSo->NgayXacNhan)->format('d/m/Y') }}</td>
                        </tr>
                        @endif
                        @if($hoSo->GhiChu)
                        <tr>
                            <td class="text-muted">Ghi chú:</td>
                            <td class="text-dark">{{ $hoSo->GhiChu }}</td>
                        </tr>
                        @endif
                    </table>

                    <div class="d-flex flex-wrap gap-2 pt-3 border-top">
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

            <!-- Cột phải: Lịch Bảo Vệ & Hội Đồng -->
            <div class="col-lg-6">
                <div class="p-4 bg-light rounded-3 h-100 border">
                    <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="fa-solid fa-landmark text-primary"></i>Hội Đồng Bảo Vệ & Phản Biện
                    </h6>

                    @if($hoSo->hoiDong)
                        <div class="p-3 bg-white rounded-3 border mb-3">
                            <div class="fw-bold text-primary fs-6 mb-2">
                                <i class="fa-solid fa-building-columns me-2"></i>{{ $hoSo->hoiDong->TenHoiDong }}
                            </div>
                            <div class="row g-2 text-secondary" style="font-size: 13.5px;">
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

                        <!-- Danh sách thành viên hội đồng -->
                        <div class="mb-3">
                            <div class="fw-bold text-muted mb-2 text-uppercase" style="font-size: 13px;">Thành viên Hội đồng:</div>
                            <div class="d-flex flex-column gap-2">
                                @foreach($hoSo->hoiDong->thanhViens as $tv)
                                <div class="d-flex justify-content-between align-items-center bg-white p-2.5 px-3 rounded-3 border" style="font-size: 13.5px;">
                                    <div>
                                        <i class="fa-solid fa-user-tie text-secondary me-2"></i>
                                        <strong>{{ $tv->giangVien->HoTen ?? 'Giảng viên' }}</strong>
                                        @if(!empty($tv->giangVien->HocVi))
                                            <span class="text-muted">({{ $tv->giangVien->HocVi }})</span>
                                        @endif
                                    </div>
                                    @php
                                        $tvBadge = match($tv->VaiTro) {
                                            'Chủ tịch' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                            'Thư ký'   => 'bg-info-subtle text-info border border-info-subtle',
                                            default    => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                        };
                                    @endphp
                                    <span class="badge {{ $tvBadge }} rounded-pill px-2.5 py-1">{{ $tv->VaiTro }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Giảng viên phản biện -->
                        <div class="p-2.5 px-3 bg-white rounded-3 border d-flex justify-content-between align-items-center" style="font-size: 13.5px;">
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
                            <div class="fw-bold text-dark">Hồ sơ đã được tiếp nhận</div>
                            <p class="mb-0 mt-1" style="font-size: 13.5px;">Giáo vụ Khoa đang thẩm định và xếp lịch bảo vệ. Vui lòng theo dõi thông báo.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Nộp Bản Hoàn Chỉnh Sau Khi Bảo Vệ -->
        <div class="mt-4 pt-4 border-top">
            <h6 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-file-circle-check text-success me-2"></i>
                Nộp Bản Hoàn Chỉnh Khóa Luận (Sau Bảo Vệ)
            </h6>
            <p class="text-muted mb-3" style="font-size: 13.5px;">
                Nhóm tiếp thu ý kiến từ Hội đồng, hoàn thiện bản báo cáo cuối cùng để lưu trữ và xét cấp bằng tốt nghiệp.
            </p>

            @if($hoSo->FileBanChinhSua)
                <div class="alert alert-success d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <i class="fa-solid fa-circle-check me-2"></i>
                        <strong>Đã nộp bản hoàn chỉnh sau bảo vệ!</strong>
                        <span class="text-muted ms-2" style="font-size: 13px;">
                            ({{ $hoSo->NgayNopBanChinhSua ? \Carbon\Carbon::parse($hoSo->NgayNopBanChinhSua)->format('d/m/Y H:i') : '' }})
                        </span>
                    </div>
                    <a href="{{ asset('storage/' . $hoSo->FileBanChinhSua) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3">
                        <i class="fa-solid fa-download me-1"></i>Tải về bản hoàn chỉnh
                    </a>
                </div>
            @endif

            <form action="{{ route('sinhvien.hoso.nopBanHoanChinh') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-center">
                @csrf
                <div class="col-md-8">
                    <input type="file" name="FileBanChinhSua" class="form-control" required accept=".pdf,.doc,.docx,.zip,.rar">
                    <div class="form-text text-muted" style="font-size: 13px;">Định dạng: .pdf, .docx, .zip, .rar (Tối đa 50MB).</div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success rounded-pill w-100" onclick="return confirm('Xác nhận nộp bản báo cáo khóa luận hoàn chỉnh sau bảo vệ?');">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i> {{ $hoSo->FileBanChinhSua ? 'Cập Nhật Bản Mới' : 'Nộp Bản Hoàn Chỉnh' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@else
{{-- Trường hợp 4: Form nộp hồ sơ bảo vệ mới hoặc nộp lại sau khi bị từ chối --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 px-4 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-cloud-arrow-up text-primary fs-5"></i>
            <h5 class="mb-0 fw-bold text-dark fs-6">Nộp Hồ Sơ Bảo Vệ Khóa Luận (Turnitin & Toàn Văn)</h5>
        </div>
    </div>
    <div class="card-body p-4">
        @if($hoSo && $hoSo->TrangThai === 'Không đủ điều kiện')
        <div class="alert alert-danger mb-4">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                <strong>Hồ sơ trước đó chưa đạt: {{ $hoSo->GhiChu }}</strong>
            </div>
            <div class="text-muted" style="font-size: 13px;">Vui lòng kiểm tra lại tỷ lệ đạo văn Turnitin (&le; 20%) và nộp lại hồ sơ đầy đủ dưới đây.</div>
        </div>
        @else
        <div class="alert alert-primary bg-primary-subtle border-primary-subtle mb-4">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fa-solid fa-circle-check text-primary fs-5"></i>
                <strong class="text-dark">Nhóm đã hoàn thành 5 Mốc Báo Cáo Tiến Độ.</strong>
            </div>
            <div class="text-muted" style="font-size: 13px;">
                Vui lòng nộp Báo cáo khóa luận toàn văn (PDF) và kết quả kiểm tra Turnitin hợp lệ (&le; 20%) để Giáo vụ Khoa thẩm định và xếp lịch bảo vệ.
            </div>
        </div>
        @endif

        <form action="{{ route('sinhvien.hoso.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-4">
                <!-- Tỷ lệ Turnitin -->
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark" style="font-size: 13.5px;">
                        Tỷ Lệ Trùng Lặp Turnitin (%) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="number" name="TyLeTrungLap" class="form-control" step="0.01" min="0" max="100"
                            placeholder="VD: 14.5" value="{{ old('TyLeTrungLap') }}" required>
                        <span class="input-group-text bg-light fw-bold">%</span>
                    </div>
                    <div class="form-text text-muted" style="font-size: 13px;">
                        Quy định Khoa: Tỷ lệ trùng lặp phải <strong>&le; 20%</strong> mới đủ điều kiện bảo vệ.
                    </div>
                </div>

                <!-- Báo cáo toàn văn PDF -->
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark" style="font-size: 13.5px;">
                        Báo Cáo Khóa Luận Toàn Văn (PDF) <span class="text-danger">*</span>
                    </label>
                    <input type="file" name="FileToanVan" class="form-control" accept=".pdf" required>
                    <div class="form-text text-muted" style="font-size: 13px;">
                        Bản toàn văn khóa luận hoàn chỉnh định dạng PDF (tối đa 30MB).
                    </div>
                </div>

                <!-- Minh chứng Turnitin PDF -->
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark" style="font-size: 13.5px;">
                        Minh Chứng Kiểm Tra Đạo Văn Turnitin (PDF) <span class="text-danger">*</span>
                    </label>
                    <input type="file" name="MinhChungTurnitin" class="form-control" accept=".pdf" required>
                    <div class="form-text text-muted" style="font-size: 13px;">
                        Tệp PDF báo cáo chi tiết xuất từ hệ thống Turnitin (tối đa 20MB).
                    </div>
                </div>

                <!-- Ghi chú thêm -->
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark" style="font-size: 13.5px;">Ghi Chú Thêm (Nếu có)</label>
                    <textarea name="GhiChu" class="form-control" rows="2" placeholder="Ghi chú thêm gửi đến Giáo vụ Khoa...">{{ old('GhiChu') }}</textarea>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                <a href="{{ route('sinhvien.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4">Hủy</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">
                    <i class="fa-solid fa-paper-plane me-2"></i>Nộp Hồ Sơ Bảo Vệ
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection
