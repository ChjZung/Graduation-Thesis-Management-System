@extends('layouts.truongbomon')

@section('page_title', 'Chi tiết & Phê duyệt đề tài: ' . $detai->TenDeTai)

@push('styles')
<style>
    .badge-code {
        background: #f1f5f9;
        color: #0f172a;
        font-family: monospace;
        font-weight: 700;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
    }
    .badge-status-choduyet {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    .badge-status-yeucausua {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }
    .badge-status-daduyet {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .badge-status-tuchoi {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    .timeline-wrap {
        position: relative;
        padding-left: 2rem;
    }
    .timeline-wrap::before {
        content: '';
        position: absolute;
        left: 0.75rem;
        top: 0.5rem;
        bottom: 0.5rem;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .timeline-point {
        position: absolute;
        left: -2rem;
        top: 0.25rem;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        background: #ffffff;
        border: 2px solid #cbd5e1;
        color: #64748b;
    }
    .timeline-point.point-success {
        border-color: #22c55e;
        color: #22c55e;
        background: #f0fdf4;
    }
    .timeline-point.point-warning {
        border-color: #f59e0b;
        color: #f59e0b;
        background: #fffbeb;
    }
    .timeline-point.point-info {
        border-color: #0ea5e9;
        color: #0ea5e9;
        background: #f0f9ff;
    }
    .timeline-point.point-danger {
        border-color: #ef4444;
        color: #ef4444;
        background: #fef2f2;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3 px-3 px-md-4">
    <!-- Nút Quay lại -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <a href="{{ route('truongbomon.phe_duyet_detai.giang_vien', ['maGV' => $detai->MaGV, 'hocKy' => $selectedHocKy]) }}" 
           class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách đề tài của GV {{ $detai->giangVien->HoTen ?? '' }}
        </a>

        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5">
                Mã ĐT: <strong class="text-dark">{{ $detai->MaDeTai }}</strong>
            </span>
            <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5">
                Học kỳ: <strong class="text-dark">{{ $detai->hocKy->TenHocKy ?? $detai->MaHocKy }}</strong>
            </span>
        </div>
    </div>

    <!-- Thông Báo Kết Quả / Flash Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-xs mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-xs mb-4" role="alert">
            <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Đã xảy ra lỗi:</div>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $statusClass = 'badge-status-choduyet';
        $statusLabel = 'Chờ duyệt cấp Bộ môn';
        $statusBadgeIcon = 'fa-clock';

        if (in_array($detai->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt'])) {
            $statusClass = 'badge-status-choduyet';
            $statusLabel = 'Chờ duyệt (Cần xem xét)';
            $statusBadgeIcon = 'fa-clock';
        } elseif (in_array($detai->TrangThai, ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương'])) {
            $statusClass = 'badge-status-yeucausua';
            $statusLabel = 'Yêu cầu chỉnh sửa';
            $statusBadgeIcon = 'fa-pen-ruler';
        } elseif (in_array($detai->TrangThai, ['Chờ duyệt cấp Khoa', 'Đã phê duyệt', 'Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã công bố', 'Đã đăng ký', 'Hoàn thành'])) {
            $statusClass = 'badge-status-daduyet';
            $statusLabel = 'Đã phê duyệt (' . $detai->TrangThai . ')';
            $statusBadgeIcon = 'fa-circle-check';
        } elseif (in_array($detai->TrangThai, ['Từ chối', 'Không đạt phản biện'])) {
            $statusClass = 'badge-status-tuchoi';
            $statusLabel = 'Bị từ chối';
            $statusBadgeIcon = 'fa-ban';
        }
    @endphp

    <div class="row g-4">
        <!-- CỘT TRÁI: THÔNG TIN VÀ NỘI DUNG ĐỀ TÀI (8 CỘT) -->
        <div class="col-lg-8">
            <!-- Card Thông Tin Tổng Quan -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark fs-6">
                        <i class="fa-solid fa-file-lines text-primary me-2"></i> Thông Tin Chung Đề Tài
                    </span>
                    <span class="badge rounded-pill px-3 py-1.5 fw-semibold {{ $statusClass }}">
                        <i class="fa-solid {{ $statusBadgeIcon }} me-1"></i> {{ $statusLabel }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <h4 class="fw-bold text-dark mb-3">{{ $detai->TenDeTai }}</h4>

                    <div class="row g-3 py-3 border-top border-bottom bg-light bg-opacity-50 rounded-3 px-2 mb-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Giảng viên đề xuất:</div>
                            <div class="fw-bold text-dark fs-6 mt-0.5">
                                {{ $detai->giangVien->HoTen ?? 'N/A' }} 
                                <span class="badge-code ms-1">{{ $detai->MaGV }}</span>
                            </div>
                            <div class="small text-muted mt-1">
                                <i class="fa-solid fa-building-columns me-1 text-secondary"></i> {{ $detai->giangVien->boMon->TenBoMon ?? ($boMon->TenBoMon ?? 'Bộ môn') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted small">Môn học / Học kỳ:</div>
                            <div class="fw-bold text-dark mt-0.5">
                                {{ $detai->hocPhanRef->TenHocPhan ?? ($detai->HocPhan ?? 'Khóa luận tốt nghiệp') }}
                            </div>
                            <div class="small text-muted mt-1">
                                <i class="fa-solid fa-calendar me-1 text-secondary"></i> {{ $detai->hocKy->TenHocKy ?? $detai->MaHocKy }}
                                &bull; Ngày đề xuất: <strong>{{ $detai->NgayDeXuat ? \Carbon\Carbon::parse($detai->NgayDeXuat)->format('d/m/Y') : ($detai->created_at ? $detai->created_at->format('d/m/Y') : '—') }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Lĩnh vực nghiên cứu:</span>
                            <span class="badge bg-light text-dark border px-3 py-1.5 rounded-pill mt-1 fw-medium">
                                <i class="fa-solid fa-layer-group text-primary me-1"></i> {{ $detai->LinhVuc ?? 'Công Nghệ Thông Tin' }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Quy mô nhóm sinh viên:</span>
                            <span class="badge bg-light text-dark border px-3 py-1.5 rounded-pill mt-1 fw-medium">
                                <i class="fa-solid fa-users text-primary me-1"></i> Tối đa {{ $detai->SoLuongSinhVienToiDa ?? 3 }} sinh viên
                            </span>
                        </div>
                    </div>

                    <!-- File Đề Cương / Tài Liệu Đính Kèm (Ẩn nếu không có) -->
                    @if($detai->FileDeCuong)
                        <div class="alert alert-light border rounded-3 p-3 d-flex align-items-center justify-content-between mb-0">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-file-pdf text-danger fs-3"></i>
                                <div>
                                    <div class="fw-bold text-dark">File Đề Cương Chi Tiết Đính Kèm</div>
                                    <div class="small text-muted">Được tải lên bởi Giảng viên hướng dẫn</div>
                                </div>
                            </div>
                            <div>
                                <a href="{{ asset($detai->FileDeCuong) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                                    <i class="fa-solid fa-eye me-1"></i> Xem / Tải file
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Card Nội Dung Chi Tiết Đề Tài -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <span class="fw-bold text-dark fs-6">
                        <i class="fa-solid fa-align-left text-primary me-2"></i> Nội Dung &amp; Yêu Cầu Đề Tài
                    </span>
                </div>
                <div class="card-body p-4">
                    <!-- Mô tả đề tài -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-secondary text-uppercase small" style="letter-spacing: 0.5px;">1. Mô Tả Đề Tài:</h6>
                        <div class="p-3 bg-light rounded-3 text-dark mt-2" style="white-space: pre-line; line-height: 1.6;">
                            {{ $detai->MoTa ?: 'Chưa có thông tin mô tả chi tiết.' }}
                        </div>
                    </div>

                    <!-- Mục tiêu đề tài -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-secondary text-uppercase small" style="letter-spacing: 0.5px;">2. Mục Tiêu Đạt Được:</h6>
                        <div class="p-3 bg-light rounded-3 text-dark mt-2" style="white-space: pre-line; line-height: 1.6;">
                            {{ $detai->MucTieu ?: ($detai->MoTa ? 'Xem chi tiết trong phần mô tả đề tài.' : 'Chưa có thông tin mục tiêu.') }}
                        </div>
                    </div>

                    <!-- Yêu cầu đối với sinh viên -->
                    <div class="mb-0">
                        <h6 class="fw-bold text-secondary text-uppercase small" style="letter-spacing: 0.5px;">3. Yêu Cầu Đối Với Sinh Viên Thực Hiện:</h6>
                        <div class="p-3 bg-light rounded-3 text-dark mt-2" style="white-space: pre-line; line-height: 1.6;">
                            {{ $detai->YeuCau ?: 'Kiến thức chuyên ngành phù hợp, tinh thần tự giác, hoàn thành đúng tiến độ theo quy định của Khoa & Bộ môn.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: BẢNG ĐIỀU KHIỂN THAO TÁC DUYỆT & TIMELINE LỊCH SỬ (4 CỘT) -->
        <div class="col-lg-4">
            <!-- HỘP THAO TÁC PHÊ DUYỆT (CHỈ KHẢ DỤNG KHI TRẠNG THÁI = CHỜ DUYỆT) -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 {{ $isChoDuyet ? 'border-primary border-opacity-50' : '' }}">
                <div class="card-header bg-white border-bottom py-3">
                    <span class="fw-bold text-dark fs-6">
                        <i class="fa-solid fa-gavel text-primary me-2"></i> Thao Tác Phê Duyệt (Trưởng Bộ Môn)
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    @if($isChoDuyet)
                        <div class="alert alert-warning py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-clock text-warning fs-5"></i>
                            <div>Đề tài đang ở trạng thái <strong>Chờ duyệt</strong>. Vui lòng xem xét nội dung và chọn một trong các quyết định dưới đây:</div>
                        </div>

                        <div class="d-grid gap-2.5">
                            <!-- NÚT 1: PHÊ DUYỆT -->
                            <button type="button" class="btn btn-success fw-bold py-2 rounded-pill shadow-xs" data-bs-toggle="modal" data-bs-target="#modalPheDuyet">
                                <i class="fa-solid fa-circle-check me-1"></i> Phê Duyệt Đề Tài
                            </button>

                            <!-- NÚT 2: YÊU CẦU CHỈNH SỬA -->
                            <button type="button" class="btn btn-outline-info text-dark fw-bold py-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalYeuCauSua">
                                <i class="fa-solid fa-pen-ruler me-1 text-primary"></i> Yêu Cầu Chỉnh Sửa
                            </button>

                            <!-- NÚT 3: TỪ CHỐI -->
                            <button type="button" class="btn btn-outline-danger fw-bold py-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalTuChoi">
                                <i class="fa-solid fa-ban me-1"></i> Từ Chối Đề Tài
                            </button>
                        </div>
                    @else
                        <!-- CÁC TRẠNG THÁI KHÁC: CHỈ XEM, DISABLE NÚT VÀ HIỂN THỊ LÝ DO -->
                        <div class="p-3 bg-light rounded-3 text-center mb-3">
                            <div class="fs-4 mb-1">
                                @if(in_array($detai->TrangThai, ['Chờ duyệt cấp Khoa', 'Đã phê duyệt', 'Trưởng khoa đã duyệt', 'Đã công bố', 'Đã đăng ký', 'Hoàn thành']))
                                    <i class="fa-solid fa-circle-check text-success"></i>
                                @elseif($detai->TrangThai === 'Từ chối')
                                    <i class="fa-solid fa-circle-xmark text-danger"></i>
                                @else
                                    <i class="fa-solid fa-pen-ruler text-info"></i>
                                @endif
                            </div>
                            <div class="fw-bold text-dark fs-6">{{ $statusLabel }}</div>
                            <div class="small text-muted mt-1">Đề tài đã hoàn tất xử lý ở trạng thái này. Không thể thao tác lại.</div>
                        </div>

                        @if($detai->LyDoTuChoi)
                            <div class="border rounded-3 p-3 mb-3 bg-white">
                                <div class="small fw-bold text-secondary mb-1">
                                    <i class="fa-solid fa-message me-1 text-primary"></i> Nội dung phản hồi / Lý do ghi nhận:
                                </div>
                                <div class="small text-dark fst-italic" style="white-space: pre-line;">
                                    "{{ $detai->LyDoTuChoi }}"
                                </div>
                            </div>
                        @endif

                        <div class="d-grid gap-2">
                            <button class="btn btn-light rounded-pill py-2 fw-semibold text-muted border" disabled>
                                <i class="fa-solid fa-lock me-1"></i> Chức năng phê duyệt đã khóa
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- CARD LỊCH SỬ XỬ LÝ (TIMELINE) -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark fs-6">
                        <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Lịch Sử Xử Lý Đề Tài
                    </span>
                    <span class="badge bg-light text-secondary border rounded-pill">
                        {{ $detai->chiTietDuyetDeTais->count() + 1 }} sự kiện
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="timeline-wrap">
                        <!-- Danh sách các lần xử lý từ mới nhất đến cũ nhất -->
                        @forelse($detai->chiTietDuyetDeTais as $log)
                        @php
                            $pClass = 'point-info';
                            $pIcon = 'fa-pen';
                            if ($log->HanhDong === 'Phê duyệt' || in_array($log->TrangThai, ['Chờ duyệt cấp Khoa', 'Đã phê duyệt', 'Trưởng khoa đã duyệt'])) {
                                $pClass = 'point-success';
                                $pIcon = 'fa-check';
                            } elseif ($log->HanhDong === 'Từ chối' || $log->TrangThai === 'Từ chối') {
                                $pClass = 'point-danger';
                                $pIcon = 'fa-xmark';
                            } elseif ($log->HanhDong === 'Yêu cầu chỉnh sửa') {
                                $pClass = 'point-warning';
                                $pIcon = 'fa-pen-ruler';
                            } elseif ($log->HanhDong === 'Nộp lại đề tài' || $log->HanhDong === 'Gửi đề xuất mới') {
                                $pClass = 'point-info';
                                $pIcon = 'fa-paper-plane';
                            }
                        @endphp
                        <div class="timeline-item">
                            <div class="timeline-point {{ $pClass }}">
                                <i class="fa-solid {{ $pIcon }}"></i>
                            </div>
                            <div class="border rounded-3 p-2.5 bg-light bg-opacity-50">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark small">
                                        {{ $log->HanhDong ?: $log->TrangThai }}
                                    </span>
                                    <span class="small text-muted" style="font-size: 11px;">
                                        {{ $log->created_at ? $log->created_at->format('H:i d/m/Y') : ($log->NgayDuyet ? \Carbon\Carbon::parse($log->NgayDuyet)->format('d/m/Y') : '—') }}
                                    </span>
                                </div>
                                <div class="small text-secondary mb-1">
                                    <i class="fa-solid fa-user-circle me-1"></i> {{ $log->NguoiThucHien ?? ($log->giangVien->HoTen ?? 'Người xử lý') }}
                                </div>
                                @if($log->TrangThaiCu)
                                    <div class="small text-muted mb-1" style="font-size: 11px;">
                                        Trạng thái: <code>{{ $log->TrangThaiCu }}</code> &rarr; <span class="fw-bold text-dark">{{ $log->TrangThai }}</span>
                                    </div>
                                @endif
                                @if($log->LyDo)
                                    <div class="small text-dark bg-white border rounded p-2 mt-1 fst-italic">
                                        "{{ $log->LyDo }}"
                                    </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        @endforelse

                        <!-- Sự kiện gốc: Giảng viên gửi đề xuất ban đầu -->
                        <div class="timeline-item">
                            <div class="timeline-point point-info">
                                <i class="fa-solid fa-paper-plane"></i>
                            </div>
                            <div class="border rounded-3 p-2.5 bg-light bg-opacity-50">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark small">Khởi tạo &amp; Gửi đề xuất</span>
                                    <span class="small text-muted" style="font-size: 11px;">
                                        {{ $detai->NgayDeXuat ? \Carbon\Carbon::parse($detai->NgayDeXuat)->format('d/m/Y') : ($detai->created_at ? $detai->created_at->format('H:i d/m/Y') : 'Ban đầu') }}
                                    </span>
                                </div>
                                <div class="small text-secondary">
                                    <i class="fa-solid fa-user me-1"></i> Giảng viên: <strong>{{ $detai->giangVien->HoTen ?? $detai->MaGV }}</strong>
                                </div>
                                <div class="small text-muted mt-1" style="font-size: 11px;">
                                    Đề tài được gửi đến Bộ môn để chờ Trưởng bộ môn thẩm định phê duyệt.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 1: XÁC NHẬN PHÊ DUYỆT ĐỀ TÀI                             -->
<!-- ============================================================== -->
<div class="modal fade" id="modalPheDuyet" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('truongbomon.phe_duyet_detai.duyet', $detai->MaDeTai) }}">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-circle-check me-2"></i> Phê Duyệt Đề Xuất Đề Tài
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border rounded-3 p-3 mb-3">
                        <div class="small text-muted">Đề tài phê duyệt:</div>
                        <div class="fw-bold text-dark fs-6">{{ $detai->TenDeTai }}</div>
                        <div class="small text-muted mt-1">Giảng viên: {{ $detai->giangVien->HoTen ?? $detai->MaGV }} &bull; Mã: {{ $detai->MaDeTai }}</div>
                    </div>

                    <p class="text-dark small mb-3">
                        Sau khi được Trưởng bộ môn phê duyệt, đề tài sẽ được chuyển tiếp lên <strong>Ban Chủ Nhiệm Khoa</strong> để phê duyệt và công bố chính thức cho sinh viên đăng ký.
                    </p>

                    <div class="mb-0">
                        <label class="form-label fw-bold small text-secondary">Ghi chú phê duyệt (Tùy chọn):</label>
                        <textarea name="GhiChu" class="form-control" rows="3" placeholder="Nhập ghi chú hoặc ý kiến của Trưởng bộ môn nếu có..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-check me-1"></i> Xác Nhận Phê Duyệt
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 2: YÊU CẦU CHỈNH SỬA (BẮT BUỘC NHẬP NỘI DUNG GÓP Ý)      -->
<!-- ============================================================== -->
<div class="modal fade" id="modalYeuCauSua" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('truongbomon.phe_duyet_detai.yeu_cau_sua', $detai->MaDeTai) }}">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-info text-white rounded-top-4">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-pen-ruler me-2"></i> Yêu Cầu Chỉnh Sửa Đề Tài
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border rounded-3 p-2.5 mb-3">
                        <div class="small text-muted">Đề tài yêu cầu chỉnh sửa:</div>
                        <div class="fw-bold text-dark small">{{ $detai->TenDeTai }}</div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold text-dark">
                            Nội dung góp ý / Yêu cầu chỉnh sửa <span class="text-danger">*</span>
                        </label>
                        <textarea name="YeuCauSua" class="form-control" rows="4" 
                                  placeholder="Nhập chi tiết các nội dung cần giảng viên chỉnh sửa (ví dụ: mục tiêu chưa rõ ràng, cần bổ sung phạm vi nghiên cứu...)" 
                                  required minlength="5" maxlength="1000"></textarea>
                        <div class="form-text small text-muted">Nội dung góp ý bắt buộc từ 5 đến 1000 ký tự. Hệ thống sẽ gửi thông báo đến giảng viên đề xuất.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-paper-plane me-1"></i> Gửi Yêu Cầu Chỉnh Sửa
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 3: TỪ CHỐI ĐỀ TÀI (BẮT BUỘC NHẬP LÝ DO TỪ CHỐI)          -->
<!-- ============================================================== -->
<div class="modal fade" id="modalTuChoi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('truongbomon.phe_duyet_detai.tu_choi', $detai->MaDeTai) }}">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-danger text-white rounded-top-4">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-ban me-2"></i> Từ Chối Phê Duyệt Đề Tài
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger bg-danger-subtle border-0 rounded-3 p-3 mb-3 small text-danger-emphasis">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> <strong>Lưu ý:</strong> Khi từ chối, đề tài sẽ không được đưa vào danh sách đề tài của học kỳ này. Vui lòng nêu rõ lý do để giảng viên nắm thông tin.
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold text-dark">
                            Lý do từ chối đề tài <span class="text-danger">*</span>
                        </label>
                        <textarea name="LyDoTuChoi" class="form-control" rows="4" 
                                  placeholder="Nhập lý do từ chối đề xuất này (ví dụ: đề tài trùng lặp với đề tài năm trước, không phù hợp định hướng chuyên môn...)" 
                                  required minlength="5" maxlength="1000"></textarea>
                        <div class="form-text small text-muted">Bắt buộc nhập lý do (5 - 1000 ký tự).</div>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-ban me-1"></i> Xác Nhận Từ Chối
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
