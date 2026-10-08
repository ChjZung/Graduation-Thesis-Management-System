@extends('layouts.admin')

@section('page_title', 'Preview & Kiểm Tra Dữ Liệu Kế Hoạch Đã Trích Xuất')

@php
    $previewData = $previewData ?? $data ?? session('parsed_doc_data') ?? [];
@endphp

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.kehoach.importDocument') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Upload file khác
    </a>
    
    @if(!empty($previewData))
    <form action="{{ route('admin.kehoach.confirmImport') }}" method="POST" class="d-inline">
        @csrf
        <input type="hidden" name="MaHocKy" value="{{ $previewData['ma_hoc_ky'] ?? '' }}">
        <button type="submit" class="btn btn-success btn-lg rounded-pill px-5 shadow-sm fw-bold">
            <i class="fa-solid fa-check-circle me-1"></i> XÁC NHẬN CẬP NHẬT KẾ HOẠCH & SINH LỊCH
        </button>
    </form>
    @endif
</div>

<!-- Header Notification Banner -->
<div class="alert alert-warning border-0 shadow-sm p-4 rounded-4 mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="fs-1 text-warning"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div>
            <h5 class="fw-bold text-dark mb-1">Dữ Liệu Đang Ở Chế Độ Xem Trước (PREVIEW)</h5>
            <p class="mb-0 text-secondary">CSDL chưa bị thay đổi. Vui lòng rà soát lại thông tin trích xuất bên dưới. Khi bấm nút <strong>[XÁC NHẬN CẬP NHẬT]</strong>, hệ thống mới ghi vào Database, tự động tạo Lịch quy trình và Gửi thông báo kèm file gốc cho Sinh viên/Giảng viên.</p>
        </div>
    </div>
</div>

@if(!empty($previewData['is_version_update']))
<!-- Version Diff Comparison Banner -->
<div class="card card-premium border-start border-4 border-warning mb-4">
    <div class="card-header-premium bg-warning bg-opacity-10 text-dark">
        <span><i class="fa-solid fa-code-compare me-2 text-warning"></i> PHÁT HIỆN THAY ĐỔI VỚI PHIÊN BẢN CŨ (Phiên bản {{ $previewData['next_version_number'] ?? 2 }})</span>
    </div>
    <div class="card-body p-3">
        <p class="small text-muted mb-2">Thông báo mới sẽ cập nhật thay đổi ngày nộp cho các mốc quy trình đã ban hành trước đó:</p>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tên Mốc Quy Trình</th>
                        <th>Hạn Cũ (Version Trước)</th>
                        <th>Hạn Mới (Theo Văn Bản Mới)</th>
                        <th>Loại Thay Đổi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($previewData['diffs'] ?? []) as $diff)
                    <tr>
                        <td class="fw-bold">{{ $diff['TenMoc'] ?? $diff['phase_name'] ?? '' }}</td>
                        <td class="text-danger text-decoration-line-through">{{ $diff['Truoc'] ?? $diff['old_range'] ?? '' }}</td>
                        <td class="text-success fw-bold">{{ $diff['Sau'] ?? $diff['new_range'] ?? '' }}</td>
                        <td><span class="badge bg-warning text-dark rounded-pill">{{ $diff['Loai'] ?? 'Dời lịch' }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-muted text-center">Không có thay đổi về deadline so với phiên bản trước.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Plan Metadata Header -->
<div class="card card-premium mb-4">
    <div class="card-header-premium">
        <span><i class="fa-solid fa-circle-info text-primary me-2"></i> Thông Tin Kế Hoạch Trích Xuất Từ Văn Bản Gốc</span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="small text-muted fw-bold">Tên Kế Hoạch:</label>
                <div class="fw-bold text-primary fs-6">{{ $previewData['header']['TenKeHoach'] ?? 'Kế hoạch Khóa luận tốt nghiệp' }}</div>
            </div>
            <div class="col-md-3">
                <label class="small text-muted fw-bold">Số Thông Báo:</label>
                <div class="fw-bold text-dark">{{ $previewData['header']['SoThongBao'] ?? 'TB-KCNTT' }}</div>
            </div>
            <div class="col-md-3">
                <label class="small text-muted fw-bold">File Gốc Upload:</label>
                <div class="fw-bold text-success"><i class="fa-solid fa-paperclip me-1"></i>{{ $previewData['original_name'] ?? 'Văn bản đính kèm' }} ({{ strtoupper($previewData['ext'] ?? 'DOCX') }})</div>
            </div>

            <div class="col-md-3">
                <label class="small text-muted fw-bold">Học Kỳ Áp Dụng (Đã chọn):</label>
                <div class="fw-bold text-success"><i class="fa-solid fa-calendar-check me-1"></i>{{ $previewData['ten_hoc_ky'] ?? $previewData['header']['HocKy'] ?? '' }}</div>
            </div>
            <div class="col-md-3">
                <label class="small text-muted fw-bold">Khóa Sinh Viên:</label>
                <div><span class="badge bg-light text-dark border">{{ $previewData['header']['KhoaSinhVien'] ?? 'Đại học chính quy' }}</span></div>
            </div>
            <div class="col-md-6">
                <label class="small text-muted fw-bold">Phạm Vi Ngành Áp Dụng:</label>
                <div>{{ $previewData['header']['Nganh'] ?? 'Tất cả các ngành' }}</div>
            </div>

            <div class="col-md-6">
                <label class="small text-muted fw-bold">Mã Học Phần & Tín Chỉ:</label>
                <div>{{ $previewData['header']['MaHocPhan'] ?? '0101000' }} ({{ $previewData['header']['SoTinChi'] ?? 10 }} tín chỉ)</div>
            </div>
            <div class="col-md-6">
                <label class="small text-muted fw-bold">Tổng Thời Gian Thực Hiện:</label>
                <div class="fw-bold text-dark">
                    {{ !empty($previewData['header']['NgayBatDau']) ? date('d/m/Y', strtotime($previewData['header']['NgayBatDau'])) : '' }}
                    <i class="fa-solid fa-arrow-right mx-1 text-muted"></i>
                    {{ !empty($previewData['header']['NgayKetThuc']) ? date('d/m/Y', strtotime($previewData['header']['NgayKetThuc'])) : '' }}
                    @if(!empty($previewData['header']['TongSoTuan']))
                        ({{ $previewData['header']['TongSoTuan'] }} tuần)
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Extracted Milestones Table -->
<div class="card card-premium mb-4">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-list-check text-primary me-2"></i> Danh Sách Mốc Thời Gian Quy Trình Nhận Diện Được ({{ count($previewData['milestones'] ?? []) }} Mốc)</span>
        <span class="badge bg-success rounded-pill px-3">Tất cả mốc đã Hợp lệ</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="4%">#</th>
                        <th width="28%">Tên Mốc Quy Trình / Công Việc</th>
                        <th width="15%">Đối Tượng</th>
                        <th width="20%">Thời Gian Bắt Đầu - Kết Thúc</th>
                        <th width="15%">Khung Giờ (nếu có)</th>
                        <th width="18%">Trạng Thái Nhận Diện</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($previewData['milestones'] ?? []) as $idx => $m)
                    <tr>
                        <td class="fw-bold text-muted">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $m['TenMoc'] ?? '' }}</div>
                            @if(!empty($m['LoaiGiaiDoan']))
                                <span class="badge bg-light text-primary border small">{{ $m['LoaiGiaiDoan'] }}</span>
                            @endif
                        </td>
                        <td><span class="badge bg-light text-dark border rounded-pill px-3">{{ $m['DoiTuongThucHien'] ?? 'Tất cả' }}</span></td>
                        <td>
                            <div class="small fw-semibold text-primary"><i class="fa-regular fa-calendar-days me-1"></i>{{ !empty($m['NgayBatDau']) ? date('d/m/Y', strtotime($m['NgayBatDau'])) : '' }}</div>
                            <div class="small text-muted"><i class="fa-solid fa-arrow-right me-1"></i>{{ !empty($m['NgayKetThuc']) ? date('d/m/Y', strtotime($m['NgayKetThuc'])) : '' }}</div>
                        </td>
                        <td>
                            @if(!empty($m['GioBatDau']))
                                <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1"><i class="fa-regular fa-clock me-1"></i>{{ $m['GioBatDau'] }} - {{ $m['GioKetThuc'] ?? '' }}</span>
                            @else
                                <span class="text-muted small">Cả ngày</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-success-subtle text-success border border-success rounded-pill px-3 py-1">
                                {{ $m['StatusBadge'] ?? 'Hợp lệ' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Chưa có mốc thời gian nào được nhận diện.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Extracted Regulations Card -->
@if(!empty($previewData['regulations']))
<div class="card card-premium mb-4">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-scale-balanced text-primary me-2"></i> Quy Định &amp; Tiêu Chuẩn Khóa Luận Trích Xuất Từ Văn Bản Kế Hoạch ({{ count($previewData['regulations']) }} Quy định)</span>
        <span class="badge bg-primary-subtle text-primary border border-primary rounded-pill px-3">Tự động thiết lập khi xác nhận</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="ps-3">#</th>
                        <th width="32%">Tên Quy Định / Tiêu Chuẩn</th>
                        <th width="20%">Giá Trị Tham Số</th>
                        <th width="43%" class="pe-3">Mô Tả &amp; Yêu Cầu Chi Tiết</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($previewData['regulations'] as $idx => $r)
                    <tr>
                        <td class="fw-bold text-muted ps-3">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $r['TenQuyDinh'] }}</div>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-1 fs-7 fw-semibold">
                                {{ $r['GiaTri'] }}
                            </span>
                        </td>
                        <td class="text-secondary small pe-3">{{ $r['MoTa'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection

