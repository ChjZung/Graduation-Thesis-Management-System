@extends('layouts.truongkhoa')

@section('page_title', 'Chi Tiết & Phê Duyệt Đề Tài ' . $detai->MaDeTai)

@push('styles')
<style>
    .badge-status-choduyet {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    .badge-status-daduyet {
        background-color: #e8f8f2;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    .badge-status-yeucausua {
        background-color: #fff7ed;
        color: #9a3412;
        border: 1px solid #fed7aa;
    }
    .badge-status-tuchoi {
        background-color: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    .badge-code {
        font-family: monospace;
        font-size: 13px;
        background-color: #f1f5f9;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        color: #334155;
    }
    .timeline-wrap {
        position: relative;
        padding-left: 20px;
    }
    .timeline-wrap::before {
        content: '';
        position: absolute;
        top: 8px;
        bottom: 8px;
        left: 6px;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    .timeline-point {
        position: absolute;
        left: -20px;
        top: 2px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 8px;
    }
    .timeline-point.point-success { border-color: #16a34a; background: #22c55e; color: #fff; }
    .timeline-point.point-warning { border-color: #d97706; background: #f59e0b; color: #fff; }
    .timeline-point.point-danger  { border-color: #dc2626; background: #ef4444; color: #fff; }
    .timeline-point.point-info    { border-color: #0284c7; background: #0ea5e9; color: #fff; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3 px-3 px-md-4">
    <!-- NÚT QUAY LẠI & TIÊU ĐỀ -->
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <a href="{{ request('return_url', route('truongkhoa.duyet_detai.bomon', ['maBoMon' => $detai->giangVien?->MaBoMon, 'MaHocKy' => $detai->MaHocKy])) }}" 
           class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại danh sách đề tài bộ môn
        </a>

        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border rounded-pill px-3 py-2 font-monospace">
                Mã ĐT: <strong class="text-dark">{{ $detai->MaDeTai }}</strong>
            </span>
            <span class="badge bg-light text-secondary border rounded-pill px-3 py-2">
                Học kỳ: <strong class="text-dark">{{ $detai->hocKy->TenHocKy ?? $detai->MaHocKy }}</strong>
            </span>
        </div>
    </div>

    @php
        $statusClass = 'badge-status-choduyet';
        $statusLabel = 'Chờ duyệt cấp Khoa';
        $statusBadgeIcon = 'fa-clock';

        if ($detai->TrangThai === 'Chờ duyệt cấp Khoa') {
            $statusClass = 'badge-status-choduyet';
            $statusLabel = 'Chờ duyệt cấp Khoa';
            $statusBadgeIcon = 'fa-clock';
        } elseif (in_array($detai->TrangThai, ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương'])) {
            $statusClass = 'badge-status-yeucausua';
            $statusLabel = 'Yêu cầu chỉnh sửa';
            $statusBadgeIcon = 'fa-pen-ruler';
        } elseif (in_array($detai->TrangThai, ['Đã công bố', 'Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã duyệt', 'Hoàn thành'])) {
            $statusClass = 'badge-status-daduyet';
            $statusLabel = 'Đã phê duyệt (' . $detai->TrangThai . ')';
            $statusBadgeIcon = 'fa-circle-check';
        } elseif (in_array($detai->TrangThai, ['Từ chối', 'Không đạt phản biện'])) {
            $statusClass = 'badge-status-tuchoi';
            $statusLabel = 'Bị từ chối';
            $statusBadgeIcon = 'fa-ban';
        } else {
            $statusClass = 'badge-status-choduyet';
            $statusLabel = $detai->TrangThai;
            $statusBadgeIcon = 'fa-circle-info';
        }

        $isChoDuyetKhoa = ($detai->TrangThai === 'Chờ duyệt cấp Khoa');
        $isKhoaApprovedFinal = in_array($detai->TrangThai, [
            'Đã công bố',
            'Trưởng khoa đã duyệt',
            'Trưởng khoa đã duyệt - Chờ nộp đề cương',
            'Đã nộp đề cương - Chờ phân công PB',
            'Đang phản biện đề cương',
            'Đã cập nhật đề cương - Chờ phản biện lại',
            'Đã phản biện - Chờ duyệt BM',
            'Đã phản biện - Chờ TBM duyệt đề cương',
            'Hoàn thành'
        ]) || !empty($detai->NgayDuyetKhoa);
    @endphp

    <div class="row g-4">
        <!-- CỘT TRÁI: THÔNG TIN VÀ NỘI DUNG ĐỀ TÀI (8 CỘT) -->
        <div class="col-lg-8">
            <!-- Card 1: Thông Tin Chung Đề Tài -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark fs-6">
                        <i class="fa-solid fa-file-lines text-primary me-2"></i> Thông Tin Chung Đề Tài
                    </span>
                    <span class="badge rounded-pill px-3 py-2 fw-semibold {{ $statusClass }}">
                        <i class="fa-solid {{ $statusBadgeIcon }} me-2"></i> {{ $statusLabel }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <h4 class="fw-bold text-dark mb-3 text-break break-words" style="word-break: break-word; overflow-wrap: anywhere;" title="{{ $detai->TenDeTai }}">{{ $detai->TenDeTai }}</h4>

                    @if($detai->TenDeTaiTiengAnh)
                    <div class="text-secondary fst-italic mb-3">
                        <i class="fa-solid fa-language me-2 text-muted"></i> {{ $detai->TenDeTaiTiengAnh }}
                    </div>
                    @endif

                    <div class="row g-3 py-3 border-top border-bottom bg-light bg-opacity-50 rounded-3 px-2 mb-3">
                        <div class="col-12 col-md-6 min-w-0">
                            <div class="text-muted small">Giảng viên đề xuất:</div>
                            <div class="fw-bold text-dark fs-6 mt-1 text-break break-words" style="word-break: break-word; overflow-wrap: anywhere;">
                                {{ $detai->giangVien->HoTen ?? 'N/A' }} 
                                <span class="badge-code ms-1">{{ $detai->MaGV }}</span>
                            </div>
                            <div class="small text-muted mt-1 text-break break-words" style="word-break: break-word; overflow-wrap: anywhere;">
                                <i class="fa-solid fa-building-columns me-2 text-secondary"></i> {{ $detai->giangVien->boMon->TenBoMon ?? 'Bộ môn' }}
                            </div>
                        </div>

                        <div class="col-12 col-md-6 min-w-0">
                            <div class="text-muted small">Môn học / Học kỳ:</div>
                            <div class="fw-bold text-dark mt-1 text-break break-words" style="word-break: break-word; overflow-wrap: anywhere;">
                                {{ $detai->hocPhanRef->TenHocPhan ?? ($detai->HocPhan ?? 'Khóa luận tốt nghiệp') }}
                            </div>
                            <div class="small text-muted mt-1 text-break break-words" style="word-break: break-word; overflow-wrap: anywhere;">
                                <i class="fa-solid fa-calendar me-2 text-secondary"></i> {{ $detai->hocKy->TenHocKy ?? $detai->MaHocKy }}
                                &bull; Ngày đề xuất: <strong>{{ $detai->NgayDeXuat ? \Carbon\Carbon::parse($detai->NgayDeXuat)->format('d/m/Y') : ($detai->created_at ? $detai->created_at->format('d/m/Y') : '—') }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6 min-w-0">
                            <span class="text-muted small d-block mb-1">Lĩnh vực nghiên cứu:</span>
                            <div class="inline-flex items-start gap-2 max-w-full min-w-0 badge bg-light text-dark border px-3 py-2 rounded-pill text-wrap text-start d-inline-flex align-items-start" 
                                 style="white-space: normal !important; overflow-wrap: anywhere; word-break: break-word; max-width: 100%; min-width: 0; line-height: 1.4;"
                                 title="{{ $detai->LinhVuc ?? 'Công nghệ thông tin' }}">
                                <i class="fa-solid fa-tag text-primary shrink-0 flex-shrink-0 mt-1"></i>
                                <span class="min-w-0 break-words" style="min-width: 0; overflow-wrap: anywhere; word-break: break-word;">
                                    {{ $detai->LinhVuc ?? 'Công nghệ thông tin' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 min-w-0">
                            <span class="text-muted small d-block mb-1">Quy mô nhóm sinh viên:</span>
                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-medium shrink-0 flex-shrink-0 text-nowrap d-inline-flex align-items-center gap-2" style="white-space: nowrap !important;">
                                <i class="fa-solid fa-users text-primary shrink-0 flex-shrink-0"></i>
                                <span>{{ $detai->SoLuongSinhVienToiDa ?? 3 }} Sinh viên / nhóm</span>
                            </span>
                        </div>
                    </div>

                    @php
                        // 1. Trạng thái đề tài
                        $dtStatusLabel = in_array($detai->TrangThai, ['Đã công bố', 'Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã duyệt', 'Hoàn thành']) || !empty($detai->NgayDuyetKhoa)
                            ? 'Trưởng khoa đã duyệt' 
                            : ($detai->TrangThai === 'Từ chối' ? 'Bị từ chối' : 'Chờ duyệt cấp Khoa');
                        
                        // 2. Trạng thái đề cương
                        $dcStatusLabel = 'Chưa nộp đề cương';
                        if ($detai->TrangThai === 'Đã công bố') {
                            $dcStatusLabel = 'Đã duyệt đề cương';
                        } elseif ($detai->TrangThai === 'Yêu cầu chỉnh sửa đề cương') {
                            $dcStatusLabel = 'Yêu cầu chỉnh sửa đề cương';
                        } elseif ($detai->TrangThai === 'Đã cập nhật đề cương - Chờ phản biện lại') {
                            $dcStatusLabel = 'Đã nộp lại đề cương';
                        } elseif ($detai->FileDeCuong) {
                            $dcStatusLabel = 'Đã nộp đề cương';
                        }

                        // 3. Trạng thái phản biện đề cương
                        $pbKhoa = $detai->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
                        if (!$detai->FileDeCuong) {
                            $pbStatusLabel = 'Chưa phản biện (Chưa nộp ĐC)';
                            $pbStatusClass = 'bg-secondary-subtle text-secondary border';
                        } elseif (!$pbKhoa) {
                            $pbStatusLabel = 'Chưa phản biện (Chờ phân công)';
                            $pbStatusClass = 'bg-secondary-subtle text-secondary border';
                        } elseif (empty($pbKhoa->KetQua) || $pbKhoa->TrangThai === 'Đang phản biện') {
                            $pbStatusLabel = 'Chưa phản biện (Đang thẩm định)';
                            $pbStatusClass = 'bg-info-subtle text-info border';
                        } elseif ($pbKhoa->KetQua === 'Đạt') {
                            $pbStatusLabel = 'Phản biện Đạt yêu cầu';
                            $pbStatusClass = 'bg-success-subtle text-success border';
                        } elseif ($pbKhoa->KetQua === 'Yêu cầu chỉnh sửa') {
                            $pbStatusLabel = 'Yêu cầu chỉnh sửa';
                            $pbStatusClass = 'bg-warning-subtle text-warning-emphasis border';
                        } elseif ($pbKhoa->KetQua === 'Không đạt') {
                            $pbStatusLabel = 'Không đạt phản biện';
                            $pbStatusClass = 'bg-danger-subtle text-danger border';
                        } else {
                            $pbStatusLabel = 'Chưa phản biện';
                            $pbStatusClass = 'bg-secondary-subtle text-secondary border';
                        }
                    @endphp
                    <div class="row g-2 p-3 bg-light rounded-3 mb-3 border">
                        <div class="col-12 col-md-4 min-w-0">
                            <span class="text-muted small d-block mb-1">1. Trạng thái đề tài:</span>
                            <span class="badge bg-success-subtle text-success border px-3 py-1 rounded-pill mt-1 fw-semibold text-wrap text-start d-inline-flex align-items-center gap-2" style="white-space: normal !important; overflow-wrap: anywhere; word-break: break-word; max-width: 100%;">
                                <i class="fa-solid fa-check shrink-0 flex-shrink-0"></i>
                                <span class="min-w-0 break-words">{{ $dtStatusLabel }}</span>
                            </span>
                        </div>
                        <div class="col-12 col-md-4 min-w-0">
                            <span class="text-muted small d-block mb-1">2. Trạng thái đề cương:</span>
                            <span class="badge {{ $dcStatusLabel === 'Đã duyệt đề cương' ? 'bg-success text-white' : ($dcStatusLabel === 'Yêu cầu chỉnh sửa đề cương' ? 'bg-warning-subtle text-warning-emphasis border' : 'bg-primary-subtle text-primary border') }} px-3 py-1 rounded-pill mt-1 fw-semibold text-wrap text-start d-inline-flex align-items-center gap-2" style="white-space: normal !important; overflow-wrap: anywhere; word-break: break-word; max-width: 100%;">
                                <i class="fa-solid fa-file-lines shrink-0 flex-shrink-0"></i>
                                <span class="min-w-0 break-words">{{ $dcStatusLabel }}</span>
                            </span>
                        </div>
                        <div class="col-12 col-md-4 min-w-0">
                            <span class="text-muted small d-block mb-1">3. Phản biện đề cương:</span>
                            <span class="badge {{ $pbStatusClass }} px-3 py-1 rounded-pill mt-1 fw-semibold text-wrap text-start d-inline-flex align-items-center gap-2" style="white-space: normal !important; overflow-wrap: anywhere; word-break: break-word; max-width: 100%;">
                                <i class="fa-solid fa-user-check shrink-0 flex-shrink-0"></i>
                                <span class="min-w-0 break-words">{{ $pbStatusLabel }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- File Đề Cương / Tài Liệu Đính Kèm -->
                    @if($detai->FileDeCuong)
                        @php
                            $filePath = Str::startsWith($detai->FileDeCuong, ['http', 'storage/']) ? asset($detai->FileDeCuong) : asset('storage/' . $detai->FileDeCuong);
                            $fileName = basename($detai->FileDeCuong);
                        @endphp
                        <div class="alert alert-light border rounded-3 p-3 d-flex align-items-center justify-content-between mb-0 mt-3 flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <i class="fa-solid fa-file-pdf text-danger fs-3 shrink-0 flex-shrink-0"></i>
                                <div class="min-w-0">
                                    <div class="fw-bold text-dark">File Đề Cương Chi Tiết Đính Kèm</div>
                                    <div class="small text-muted text-break break-words" style="overflow-wrap: anywhere; word-break: break-word;">Tệp: {{ $fileName }}</div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 shrink-0 flex-shrink-0">
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold shadow-xs d-inline-flex align-items-center" 
                                        onclick="quickPreviewOutline('{{ $filePath }}', '{{ $fileName }}', '{{ addslashes($detai->TenDeTai) }}')">
                                    <i class="fa-solid fa-eye me-2"></i> Xem Nhanh
                                </button>
                                <a href="{{ $filePath }}" download="{{ $fileName }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center">
                                    <i class="fa-solid fa-download me-2"></i> Tải Về
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Card 2: Nội Dung & Yêu Cầu Đề Tài -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <span class="fw-bold text-dark fs-6">
                        <i class="fa-solid fa-align-left text-primary me-2"></i> Nội Dung &amp; Yêu Cầu Đề Tài
                    </span>
                </div>
                <div class="card-body p-4">
                    <!-- Mô tả đề tài -->
                    <div class="mb-4 min-w-0">
                        <h6 class="fw-bold text-secondary text-uppercase small" style="letter-spacing: 0.5px;">1. Mô Tả Đề Tài:</h6>
                        <div class="p-3 bg-light rounded-3 text-dark mt-2 text-break break-words" style="line-height: 1.6; overflow-wrap: anywhere; word-break: break-word;">
                            {!! \App\Helpers\HtmlHelper::clean($detai->MoTa) ?: 'Chưa có thông tin mô tả chi tiết.' !!}
                        </div>
                    </div>

                    <!-- Yêu cầu đối với sinh viên -->
                    <div class="mb-0 min-w-0">
                        <h6 class="fw-bold text-secondary text-uppercase small" style="letter-spacing: 0.5px;">2. Yêu Cầu Đối Với Sinh Viên Thực Hiện:</h6>
                        <div class="p-3 bg-light rounded-3 text-dark mt-2 text-break break-words" style="line-height: 1.6; overflow-wrap: anywhere; word-break: break-word;">
                            {!! \App\Helpers\HtmlHelper::clean($detai->YeuCau) ?: 'Kiến thức chuyên ngành phù hợp, tinh thần tự giác, hoàn thành đúng tiến độ theo quy định của Khoa & Bộ môn.' !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: BẢNG ĐIỀU KHIỂN THAO TÁC DUYỆT & LỊCH SỬ (4 CỘT) -->
        <div class="col-lg-4">
            <!-- HỘP THAO TÁC PHÊ DUYỆT CỦA TRƯỞNG KHOA -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 {{ $isChoDuyetKhoa ? 'border-primary border-opacity-50' : '' }}">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark fs-6">
                        <i class="fa-solid fa-stamp text-primary me-2"></i> Quyết Định Của Trưởng Khoa
                    </span>
                    @if($isChoDuyetKhoa)
                        <span class="badge bg-danger rounded-pill px-3 py-1">Cần Khoa xử lý</span>
                    @endif
                </div>
                <div class="card-body p-3 p-md-4">
                    @if($isChoDuyetKhoa)
                        <div class="alert alert-warning py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-clock text-warning fs-5"></i>
                            <div>Đề tài đang ở trạng thái <strong>Chờ duyệt cấp Khoa</strong>. Vui lòng xem xét nội dung và chọn một trong các quyết định dưới đây:</div>
                        </div>

                        <div class="d-flex flex-column gap-3 flex-col">
                            <!-- NÚT 1: PHÊ DUYỆT -->
                            <form id="formPheDuyetKhoa" action="{{ route('truongkhoa.duyet_detai.duyet', $detai->MaDeTai) }}" method="POST" class="m-0 p-0 w-100" data-custom-confirm="true" data-title="{{ $detai->TenDeTai }}">
                                @csrf
                                <input type="hidden" name="return_url" value="{{ request('return_url') }}">
                                <button type="submit" id="btnPheDuyetKhoa" class="w-full h-11 rounded-lg border font-semibold text-sm inline-flex items-center justify-center gap-2 transition bg-green-600 border-green-600 text-white hover:bg-green-700 btn-decision btn-decision-approve">
                                    <i class="fa-solid fa-stamp w-4 h-4"></i> Phê Duyệt
                                </button>
                            </form>

                            <!-- NÚT 2: YÊU CẦU CHỈNH SỬA -->
                            <button type="button" class="w-full h-11 rounded-lg border font-semibold text-sm inline-flex items-center justify-center gap-2 transition bg-white border-orange-400 text-orange-600 hover:bg-orange-50 btn-decision btn-decision-edit" data-bs-toggle="modal" data-bs-target="#modalTKYeuCauSua">
                                <i class="fa-solid fa-pen-ruler w-4 h-4"></i> Yêu Cầu Chỉnh Sửa
                            </button>

                            <!-- NÚT 3: TỪ CHỐI -->
                            <button type="button" class="w-full h-11 rounded-lg border font-semibold text-sm inline-flex items-center justify-center gap-2 transition bg-white border-red-400 text-red-600 hover:bg-red-50 btn-decision btn-decision-reject" data-bs-toggle="modal" data-bs-target="#modalTKTuChoi">
                                <i class="fa-solid fa-ban w-4 h-4"></i> Từ Chối Đề Tài
                            </button>
                        </div>
                    @elseif($isKhoaApprovedFinal)
                        <!-- ĐÃ PHÊ DUYỆT HOÀN TẤT -->
                        <div class="text-center mb-3">
                            <span class="badge bg-success text-white px-3 py-2 fs-6 rounded-pill shadow-xs">
                                <i class="fa-solid fa-stamp me-2"></i> Đã Phê Duyệt &amp; Công Bố Chính Thức
                            </span>
                            <div class="text-muted small mt-2">
                                Đề tài đã được Trưởng khoa phê duyệt chủ trương và chính thức công bố cho sinh viên.
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded-3 mb-3 small">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted"><i class="fa-solid fa-calendar-check text-success me-2"></i> Thời gian duyệt:</span>
                                <strong class="text-dark">{{ $detai->NgayDuyetKhoa ? \Carbon\Carbon::parse($detai->NgayDuyetKhoa)->format('d/m/Y H:i') : 'Đã duyệt' }}</strong>
                            </div>
                            @if($detai->NguoiDuyetKhoa)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted"><i class="fa-solid fa-user-check text-secondary me-2"></i> Cán bộ duyệt:</span>
                                <strong class="text-dark">{{ $detai->NguoiDuyetKhoa }}</strong>
                            </div>
                            @endif
                            @if($detai->NgayCongBo)
                            <div class="d-flex justify-content-between align-items-center py-2">
                                <span class="text-muted"><i class="fa-solid fa-bullhorn text-info me-2"></i> Ngày công bố:</span>
                                <strong class="text-dark">{{ \Carbon\Carbon::parse($detai->NgayCongBo)->format('d/m/Y H:i') }}</strong>
                            </div>
                            @endif
                        </div>

                        <div class="p-2 bg-success-subtle text-success rounded-3 text-center small fw-semibold">
                            <i class="fa-solid fa-circle-check me-2"></i> Quyết định phê duyệt cấp Khoa đã hoàn tất và có hiệu lực thi hành!
                        </div>
                    @elseif($detai->TrangThai === 'Từ chối')
                        <div class="p-3 bg-light rounded-3 text-center mb-3">
                            <div class="fs-4 mb-1 text-danger">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </div>
                            <div class="fw-bold text-danger fs-6">Đã Từ Chối Đề Tài</div>
                            <div class="small text-muted mt-1">Đề tài đã bị từ chối phê duyệt cấp Khoa.</div>
                        </div>

                        @if($detai->LyDoTuChoi)
                        <div class="border rounded-3 p-3 mb-3 bg-white max-w-full overflow-hidden text-break break-words" style="overflow-wrap: anywhere; word-break: break-word;">
                            <div class="small fw-bold text-danger mb-1">
                                <i class="fa-solid fa-message me-2"></i> Lý do từ chối:
                            </div>
                            <div class="small text-dark fst-italic text-break break-words whitespace-pre-wrap max-h-24 overflow-y-auto" style="word-break: break-word; overflow-wrap: anywhere; max-height: 6rem; overflow-y: auto;">
                                {!! \App\Helpers\HtmlHelper::clean($detai->LyDoTuChoi) !!}
                            </div>
                        </div>
                        @endif
                    @else
                        <div class="p-3 bg-light rounded-3 text-center mb-3">
                            <div class="fs-4 mb-1 text-warning">
                                <i class="fa-solid fa-circle-info"></i>
                            </div>
                            <div class="fw-bold text-dark fs-6">{{ $detai->TrangThai }}</div>
                            <div class="small text-muted mt-1">Đề tài hiện đang ở trạng thái này. Không cần thao tác duyệt cấp Khoa lúc này.</div>
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
                            <div class="border rounded-3 p-2 bg-light bg-opacity-50">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark small">
                                        {{ $log->HanhDong ?: $log->TrangThai }}
                                    </span>
                                    <span class="small text-muted" style="font-size: 11px;">
                                        {{ $log->created_at ? $log->created_at->format('H:i d/m/Y') : ($log->NgayDuyet ? \Carbon\Carbon::parse($log->NgayDuyet)->format('d/m/Y') : '—') }}
                                    </span>
                                </div>
                                <div class="small text-secondary mb-1">
                                    <i class="fa-solid fa-user-circle me-2"></i> {{ $log->NguoiThucHien ?? ($log->giangVien->HoTen ?? 'Người xử lý') }}
                                </div>
                                @if($log->TrangThaiCu)
                                    <div class="small text-muted mb-1" style="font-size: 11px;">
                                        Trạng thái: <code>{{ $log->TrangThaiCu }}</code> &rarr; <span class="fw-bold text-dark">{{ $log->TrangThai }}</span>
                                    </div>
                                @endif
                                @if($log->LyDo)
                                    <div class="small text-dark fst-italic p-2 bg-white rounded border">
                                        &ldquo;{!! \App\Helpers\HtmlHelper::clean($log->LyDo) !!}&rdquo;
                                    </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="timeline-item">
                            <div class="timeline-point point-info">
                                <i class="fa-solid fa-paper-plane"></i>
                            </div>
                            <div class="border rounded-3 p-2 bg-light bg-opacity-50">
                                <div class="fw-bold text-dark small">Khởi tạo đề xuất đề tài</div>
                                <div class="small text-muted">
                                    {{ $detai->NgayDeXuat ? \Carbon\Carbon::parse($detai->NgayDeXuat)->format('d/m/Y') : ($detai->created_at ? $detai->created_at->format('d/m/Y') : '—') }}
                                    &bull; Giảng viên: {{ $detai->giangVien->HoTen ?? 'GV' }}
                                </div>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Yêu Cầu Chỉnh Sửa -->
<div class="modal fade" id="modalTKYeuCauSua" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px;">
            <form id="formYeuCauSuaKhoa" action="{{ route('truongkhoa.duyet_detai.yeuCauChinhSua', $detai->MaDeTai) }}" method="POST" data-custom-confirm="true" data-title="{{ $detai->TenDeTai }}">
                @csrf
                <input type="hidden" name="return_url" value="{{ request('return_url') }}">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-pen-to-square text-warning me-2"></i>
                        Ý Kiến Yêu Cầu Chỉnh Sửa Của Trưởng Khoa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        @include('partials.rich_text_editor', [
                            'name' => 'YeuCauSua',
                            'id' => 'YeuCauSua',
                            'label' => 'Nội dung ý kiến chỉ đạo / Yêu cầu điều chỉnh',
                            'required' => true,
                            'minHeight' => '130px',
                            'compact' => true,
                            'placeholder' => 'Ghi rõ nội dung cần giảng viên và bộ môn điều chỉnh lại (ví dụ: giới hạn lại phạm vi nghiên cứu, làm rõ sản phẩm bàn giao)...'
                        ])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" id="btnYeuCauSuaKhoa" class="btn btn-warning fw-bold d-inline-flex align-items-center">
                        <i class="fa-solid fa-paper-plane me-2"></i> Gửi Yêu Cầu Chỉnh Sửa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Từ Chối -->
<div class="modal fade" id="modalTKTuChoi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px;">
            <form id="formTuChoiKhoa" action="{{ route('truongkhoa.duyet_detai.tuChoi', $detai->MaDeTai) }}" method="POST" data-custom-confirm="true" data-title="{{ $detai->TenDeTai }}">
                @csrf
                <input type="hidden" name="return_url" value="{{ request('return_url') }}">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="fa-solid fa-circle-xmark me-2"></i>
                        Từ Chối Phê Duyệt Đề Tài Cấp Khoa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        @include('partials.rich_text_editor', [
                            'name' => 'LyDoTuChoi',
                            'id' => 'LyDoTuChoi',
                            'label' => 'Lý do từ chối phê duyệt',
                            'required' => true,
                            'minHeight' => '130px',
                            'compact' => true,
                            'placeholder' => 'Nhập lý do không chấp thuận phê duyệt đề tài này (ví dụ: trùng lặp hướng nghiên cứu đã nghiệm thu, không phù hợp định hướng đào tạo)...'
                        ])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" id="btnTuChoiKhoa" class="btn btn-danger fw-bold d-inline-flex align-items-center">
                        <i class="fa-solid fa-ban me-2"></i> Xác Nhận Từ Chối
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function getShortTitle(title, maxLen = 80) {
        if (!title) return '';
        const clean = title.trim();
        return clean.length > maxLen ? clean.substring(0, maxLen) + '...' : clean;
    }

    // 1. Phê duyệt & Công bố form
    const formDuyet = document.getElementById('formPheDuyetKhoa');
    if (formDuyet) {
        formDuyet.addEventListener('submit', function (e) {
            const rawTitle = formDuyet.dataset.title || '';
            const shortTitle = getShortTitle(rawTitle, 80);
            const msg = `Xác nhận PHÊ DUYỆT đề xuất đề tài "${shortTitle}" cấp Khoa?\nSau khi phê duyệt, đề tài sẽ được công bố chính thức để sinh viên đăng ký theo kế hoạch.`;

            if (!confirm(msg)) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }

            const btn = document.getElementById('btnPheDuyetKhoa') || formDuyet.querySelector('button[type="submit"]');
            if (btn) {
                if (!btn.dataset.originalHtml) btn.dataset.originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.classList.add('opacity-60', 'cursor-not-allowed');
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin w-4 h-4"></i> Đang phê duyệt...';
            }
        });
    }

    // 2. Yêu cầu chỉnh sửa modal form
    const formYeuCauSua = document.getElementById('formYeuCauSuaKhoa');
    if (formYeuCauSua) {
        formYeuCauSua.addEventListener('submit', function (e) {
            const rawTitle = formYeuCauSua.dataset.title || '';
            const shortTitle = getShortTitle(rawTitle, 80);
            const msg = `Xác nhận gửi YÊU CẦU CHỈNH SỬA cho đề tài "${shortTitle}"?`;

            if (!confirm(msg)) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }

            const btn = document.getElementById('btnYeuCauSuaKhoa') || formYeuCauSua.querySelector('button[type="submit"]');
            if (btn) {
                if (!btn.dataset.originalHtml) btn.dataset.originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.classList.add('opacity-60', 'cursor-not-allowed');
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Đang gửi...';
            }
        });
    }

    // 3. Từ chối modal form
    const formTuChoi = document.getElementById('formTuChoiKhoa');
    if (formTuChoi) {
        formTuChoi.addEventListener('submit', function (e) {
            const rawTitle = formTuChoi.dataset.title || '';
            const shortTitle = getShortTitle(rawTitle, 80);
            const msg = `Bạn có chắc chắn muốn TỪ CHỐI đề tài "${shortTitle}" cấp Khoa?`;

            if (!confirm(msg)) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }

            const btn = document.getElementById('btnTuChoiKhoa') || formTuChoi.querySelector('button[type="submit"]');
            if (btn) {
                if (!btn.dataset.originalHtml) btn.dataset.originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.classList.add('opacity-60', 'cursor-not-allowed');
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Đang từ chối...';
            }
        });
    }

    // Reset trạng thái nút khi phục hồi trang từ bfcache (nút Back)
    window.addEventListener('pageshow', function (event) {
        document.querySelectorAll('button[type="submit"]').forEach(function (btn) {
            btn.disabled = false;
            btn.classList.remove('opacity-60', 'cursor-not-allowed');
            if (btn.dataset.originalHtml) {
                btn.innerHTML = btn.dataset.originalHtml;
            }
        });
    });
});
</script>
@endpush
