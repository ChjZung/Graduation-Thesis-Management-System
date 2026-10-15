@php
    if (empty($layout)) {
        $uRole = Auth::user()?->vaiTro?->TenVaiTro ?? '';
        $layout = match($uRole) {
            'Sinh viên'     => 'layouts.sinhvien',
            'Giảng viên'    => 'layouts.giangvien',
            'Trưởng bộ môn' => 'layouts.truongbomon',
            'Trưởng khoa'   => 'layouts.truongkhoa',
            default         => 'layouts.admin',
        };
    }
@endphp
@extends($layout)

@section('page_title', 'Chi Tiết Thông Báo & Xem Văn Bản Công Văn')

@push('styles')
<style>
    .doc-viewer-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        height: calc(100vh - 170px);
        min-height: 680px;
    }
    .doc-viewer-header {
        background: #1e293b;
        color: #f8fafc;
        padding: 10px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-shrink: 0;
    }
    .doc-viewer-body {
        flex-grow: 1;
        position: relative;
        background: #525659;
        overflow: hidden;
    }
    .doc-viewer-body iframe {
        width: 100%;
        height: 100%;
        border: none;
        display: block;
    }
    .noti-detail-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        height: calc(100vh - 170px);
        min-height: 680px;
    }
    .noti-detail-scroll {
        overflow-y: auto;
        padding: 20px;
        flex-grow: 1;
    }
    /* Fullscreen mode for doc viewer */
    .doc-fullscreen-active {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 99999 !important;
        border-radius: 0 !important;
        margin: 0 !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    {{-- Thanh điều hướng & Action bar --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ $backUrl ?? route('thongbao.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách thông báo
            </a>
            <span class="text-muted small">|</span>
            <span class="badge bg-light text-primary border rounded-pill px-3 py-1 font-monospace">{{ $thongBao->MaThongBao }}</span>
            <span class="badge {{ in_array($thongBao->TrangThai, ['ĐÃ GỬI', 'Đã phát hành']) ? 'bg-success' : 'bg-primary' }} rounded-pill px-3 py-1">
                {{ $thongBao->TrangThai }}
            </span>
        </div>
        
        <div class="d-flex gap-2">
            @if(!empty($fileUrl))
                @if(($fileType ?? '') === 'excel')
                    <a href="{{ asset($fileUrl) }}" download class="btn btn-success btn-sm rounded-pill px-3 shadow-sm fw-semibold text-white">
                        <i class="fa-solid fa-file-excel me-1"></i> Tải Danh Sách Excel (.xlsx)
                    </a>
                @else
                    <a href="{{ asset($fileUrl) }}" download class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold">
                        <i class="fa-solid fa-download me-1"></i> Tải Công Văn Gốc
                    </a>
                @endif
            @endif
            @if(in_array($thongBao->TrangThai, ['NHÁP', 'ĐÃ LÊN LỊCH']) && Route::has('thongbao.edit') && (!isset($layout) || $layout === 'layouts.admin'))
                <a href="{{ route('thongbao.edit', $thongBao->MaThongBao) }}" class="btn btn-warning btn-sm rounded-pill px-3 shadow-sm fw-semibold">
                    <i class="fa-solid fa-pen me-1"></i> Chỉnh Sửa
                </a>
            @endif
        </div>
    </div>

    {{-- GIAO DIỆN SONG SONG 2 BÊN (SPLIT VIEW): 1 BÊN CÔNG VĂN - 1 BÊN THÔNG TIN THÔNG BÁO --}}
    <div class="row g-3">
        {{-- CỘT TRÁI: KHUNG XEM TRỰC TIẾP FILE CÔNG VĂN / ẢNH (58%) --}}
        <div class="col-12 col-xl-7">
            <div class="doc-viewer-card" id="docViewerCard">
                <div class="doc-viewer-header">
                    <div class="d-flex align-items-center gap-2 text-truncate me-2">
                        @if(($fileType ?? '') === 'image')
                            <i class="fa-solid fa-file-image text-warning fs-5"></i>
                        @elseif(($fileType ?? '') === 'excel')
                            <i class="fa-solid fa-file-excel text-success fs-5"></i>
                        @else
                            <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
                        @endif
                        <span class="fw-semibold text-truncate small" title="{{ !empty($fileUrl) ? basename($fileUrl) : 'VanBan_ThongBao.pdf' }}">
                            {{ !empty($fileUrl) ? basename($fileUrl) : 'Văn Bản Công Văn Thông Báo (Khoa CNTT)' }}
                        </span>
                        <span class="badge {{ ($fileType ?? '') === 'excel' ? 'bg-success' : (($fileType ?? '') === 'image' ? 'bg-warning text-dark' : 'bg-secondary') }} rounded-pill px-2 small" style="font-size: 0.7rem;">
                            {{ strtoupper($fileType ?? 'PDF') }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        @if(!empty($fileUrl))
                            <a href="{{ asset($fileUrl) }}" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1" style="font-size: 0.78rem;" title="Mở trong cửa sổ riêng">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở tab mới
                            </a>
                            <a href="{{ asset($fileUrl) }}" download class="btn btn-sm btn-light text-dark rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;" title="Tải file về máy">
                                <i class="fa-solid fa-download me-1"></i> Tải về (.{{ strtoupper(pathinfo($fileUrl, PATHINFO_EXTENSION)) }})
                            </a>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-light rounded-circle p-1" style="width: 32px; height: 32px;" onclick="toggleDocFullscreen()" title="Toàn màn hình">
                            <i class="fa-solid fa-expand" id="fullscreenIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="doc-viewer-body">
                    @if(!empty($fileUrl))
                        @if(($fileType ?? '') === 'image')
                            {{-- Hiển thị trực tiếp Ảnh công văn --}}
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center p-3 overflow-auto" style="background: #334155;">
                                <img src="{{ asset($fileUrl) }}" alt="Ảnh công văn thông báo" class="img-fluid rounded shadow" style="max-height: 100%; object-fit: contain;">
                            </div>
                        @elseif(($fileType ?? '') === 'excel')
                            {{-- Hiển thị trực tiếp Bảng tính Excel / Danh sách sinh viên đủ điều kiện --}}
                            <div class="w-100 h-100 d-flex flex-column bg-white">
                                <!-- Excel Control Bar -->
                                <div class="p-2 px-3 border-bottom bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-file-excel text-success fs-5"></i>
                                        <span class="fw-bold small text-dark">
                                            @if(isset($duDieuKienStats))
                                                Danh Sách Sinh Viên Đủ Điều Kiện - {{ $duDieuKienStats['tenHocKy'] }}
                                            @else
                                                Bảng Tính Dữ Liệu Đính Kèm (.xlsx)
                                            @endif
                                        </span>
                                        @if(isset($duDieuKienStats))
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small px-2">
                                                <i class="fa-solid fa-check-double me-1"></i> {{ $duDieuKienStats['eligible'] }}/{{ $duDieuKienStats['total'] }} SV Đủ điều kiện
                                            </span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if(isset($duDieuKienList) && $duDieuKienList->isNotEmpty())
                                            <div class="input-group input-group-sm" style="max-width: 200px;">
                                                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted small"></i></span>
                                                <input type="text" id="excelSearchInput" class="form-control form-control-sm border-start-0 ps-0" placeholder="Tìm MSSV, họ tên..." onkeyup="filterExcelTable()">
                                            </div>
                                        @endif
                                        <a href="{{ asset($fileUrl) }}" download class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-semibold text-white shadow-sm" style="font-size: 0.78rem;">
                                            <i class="fa-solid fa-file-excel me-1"></i> Tải File Excel (.xlsx)
                                        </a>
                                    </div>
                                </div>

                                <!-- Excel Table Container -->
                                <div class="flex-grow-1 overflow-auto p-0" style="max-height: calc(100vh - 280px); min-height: 380px;">
                                    @if(isset($duDieuKienList) && $duDieuKienList->isNotEmpty())
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered table-sm align-middle mb-0 text-nowrap" id="excelTable" style="font-size: 0.82rem;">
                                                <thead class="table-light sticky-top" style="z-index: 5;">
                                                    <tr class="text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                                        <th class="text-center" style="width: 45px;">STT</th>
                                                        <th class="text-center" style="width: 105px;">Mã Sinh Viên</th>
                                                        <th>Họ Và Tên</th>
                                                        <th class="text-center">Lớp</th>
                                                        <th>Ngành Học</th>
                                                        <th class="text-center" style="width: 80px;">Tín Chỉ TL</th>
                                                        <th class="text-center" style="width: 80px;">ĐTB TL</th>
                                                        <th class="text-center" style="width: 110px;">Trạng Thái</th>
                                                        <th>Tiêu Chí &amp; Ghi Chú</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($duDieuKienList as $idx => $item)
                                                        @php
                                                            $sv = $item->sinhVien;
                                                            $isEligible = ($item->TrangThai === 'Đủ điều kiện');
                                                        @endphp
                                                        <tr class="excel-row">
                                                            <td class="text-center text-muted fw-semibold">{{ $idx + 1 }}</td>
                                                            <td class="text-center fw-bold text-primary">{{ $item->MaSV }}</td>
                                                            <td class="fw-semibold text-dark">{{ $sv->HoTen ?? 'N/A' }}</td>
                                                            <td class="text-center">{{ $sv->lop->TenLop ?? '' }}</td>
                                                            <td>{{ $sv->lop->nganh->TenNganh ?? '' }}</td>
                                                            <td class="text-center fw-semibold">{{ $sv->SoTinChiTichLuy ?? 0 }}</td>
                                                            <td class="text-center fw-semibold {{ (float)($sv->DiemTichLuy ?? 0) >= 2.0 ? 'text-success' : 'text-danger' }}">
                                                                {{ number_format((float)($sv->DiemTichLuy ?? 0), 2) }}
                                                            </td>
                                                            <td class="text-center">
                                                                @if($isEligible)
                                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                                                        <i class="fa-solid fa-circle-check me-1"></i> Đủ điều kiện
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
                                                                        <i class="fa-solid fa-circle-xmark me-1"></i> Chưa đủ ĐK
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td class="small text-muted text-truncate" style="max-width: 220px;" title="{{ $item->DieuKien }} {{ $item->GhiChu }}">
                                                                {{ $item->DieuKien }} ({{ $item->GhiChu }})
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @elseif(!empty($excelRows))
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered table-sm align-middle mb-0 text-nowrap" style="font-size: 0.82rem;">
                                                @foreach($excelRows as $rIdx => $row)
                                                    @if($rIdx === 0)
                                                        <thead class="table-light sticky-top">
                                                            <tr class="text-secondary text-uppercase" style="font-size: 0.72rem;">
                                                                @foreach($row as $cell)
                                                                    <th>{{ $cell }}</th>
                                                                @endforeach
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                    @else
                                                        <tr>
                                                            @foreach($row as $cell)
                                                                <td>{{ $cell }}</td>
                                                            @endforeach
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="h-100 d-flex flex-column align-items-center justify-content-center p-4 text-center">
                                            <i class="fa-solid fa-file-excel text-success display-4 mb-3"></i>
                                            <h6 class="fw-bold text-dark mb-2">Tệp Bảng Tính Đính Kèm (.XLSX)</h6>
                                            <p class="text-muted small mb-3">Tệp danh sách đã sẵn sàng để tải về và mở trên Microsoft Excel.</p>
                                            <a href="{{ asset($fileUrl) }}" download class="btn btn-success rounded-pill px-4 py-2 fw-semibold shadow-sm">
                                                <i class="fa-solid fa-download me-1"></i> Tải Danh Sách Excel (.xlsx)
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            {{-- Nhúng trực tiếp PDF Viewer native của trình duyệt --}}
                            <iframe id="docViewerIframe" src="{{ asset($fileUrl) }}#toolbar=1&navpanes=0"></iframe>
                        @endif
                    @else
                        {{-- Giao diện văn bản công văn chính thức khi chưa upload file PDF --}}
                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center p-4" style="background: #f8fafc;">
                            <div class="rounded-circle bg-primary-subtle text-primary p-4 mb-3">
                                <i class="fa-solid fa-file-circle-check fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">Văn Bản Thông Báo Chính Thức</h5>
                            <p class="text-muted small max-w-md mb-3" style="max-width: 420px;">
                                Văn bản thông báo số <strong>{{ $thongBao->keHoach->NoiDung ?? '27/TB-KCNTT' }}</strong> đã được lưu hành chính thức trên toàn hệ thống học vụ.
                            </p>
                            @if($thongBao->keHoach && auth()->check() && auth()->user()->MaVaiTro === 'VT01')
                                <a href="{{ route('admin.kehoach.show', $thongBao->keHoach->MaKeHoach) }}" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-semibold">
                                    <i class="fa-solid fa-calendar-check me-1"></i> Xem Chi Tiết Kế Hoạch &amp; Mốc Thời Gian
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- CỘT PHẢI: BẢNG THÔNG TIN THÔNG BÁO & CHI TIẾT QUY TRÌNH (42%) --}}
        <div class="col-12 col-xl-5">
            <div class="noti-detail-card">
                <div class="noti-detail-scroll">
                    <!-- Badge phân loại & Trạng thái -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-semibold">
                            <i class="fa-solid fa-bullhorn me-1"></i> {{ $thongBao->LoaiThongBao ?? 'Thông báo Khóa luận' }}
                        </span>
                        <span class="small text-muted">
                            <i class="fa-regular fa-calendar-check me-1"></i> {{ date('d/m/Y H:i', strtotime($thongBao->NgayTao)) }}
                        </span>
                    </div>

                    <!-- Tiêu đề thông báo -->
                    <h5 class="fw-bold text-dark mb-3" style="line-height: 1.45;">
                        {{ $thongBao->TieuDe }}
                    </h5>

                    <!-- Thẻ Metadata Tóm Tắt -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="row g-2 small">
                            <div class="col-6">
                                <div class="text-muted">Cơ quan ban hành:</div>
                                <div class="fw-bold text-dark mt-1">
                                    <i class="fa-solid fa-building-columns text-primary me-1"></i> Khoa CNTT
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted">Đối tượng tiếp nhận:</div>
                                <div class="fw-bold text-dark mt-1">
                                    <i class="fa-solid fa-users text-primary me-1"></i> {{ $thongBao->DoiTuongNhan ?? 'Toàn thể hệ thống' }}
                                </div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="text-muted">Người phát hành:</div>
                                <div class="fw-bold text-dark mt-1">
                                    <i class="fa-solid fa-user-check text-primary me-1"></i> {{ $thongBao->giaoVu->HoTen ?? $thongBao->MaGVu ?? 'Ban Giáo vụ' }}
                                </div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="text-muted">Trạng thái công bố:</div>
                                <div class="fw-bold text-success mt-1">
                                    <i class="fa-solid fa-circle-check text-success me-1"></i> Có hiệu lực ngay
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nội dung thông báo chi tiết -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-align-left text-primary"></i>
                            <span>Nội Dung Thông Báo Chính Thức</span>
                        </h6>
                        <div class="p-3 rounded-3 border bg-white text-dark shadow-xs" style="white-space: pre-line; font-size: 0.92rem; line-height: 1.75; border-left: 4px solid #0072ce !important;">
                            {{ $thongBao->NoiDung }}
                        </div>
                    </div>

                    <!-- Kế hoạch & Học kỳ liên quan nếu có -->
                    @if($thongBao->keHoach)
                    <div class="p-3 rounded-3 bg-primary-subtle border border-primary-subtle mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small text-muted fw-semibold">Kế hoạch Khóa luận liên kết:</div>
                                <div class="fw-bold text-primary">{{ $thongBao->keHoach->TenKeHoach }}</div>
                                <div class="small text-muted mt-1">Học kỳ: {{ $thongBao->keHoach->hocKy->TenHocKy ?? $thongBao->keHoach->MaHocKy }}</div>
                            </div>
                            <a href="{{ route('admin.kehoach.show', $thongBao->keHoach->MaKeHoach) }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                                Xem Kế Hoạch <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Thống kê phân phối người nhận (Chỉ hiển thị cho Quản trị viên/Giáo vụ) -->
                    @if(isset($totalSent) && (!isset($layout) || $layout === 'layouts.admin'))
                    <div class="mt-auto pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted fw-bold text-uppercase" style="letter-spacing: 0.5px;">Phân phối thông báo:</span>
                            <span class="badge bg-light text-primary border rounded-pill px-3">{{ $totalSent ?? 0 }} Tài khoản tiếp nhận</span>
                        </div>
                        <div class="row g-2 text-center small">
                            <div class="col-4">
                                <div class="p-2 bg-light rounded-3 border">
                                    <div class="fw-bold text-primary fs-6">{{ $totalSent ?? 0 }}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Đã gửi</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-success-subtle text-success rounded-3 border border-success-subtle">
                                    <div class="fw-bold fs-6">{{ $readCount ?? 0 }}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Đã xem</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-warning-subtle text-warning-emphasis rounded-3 border border-warning-subtle">
                                    <div class="fw-bold fs-6">{{ $unreadCount ?? 0 }}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Chờ xem</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleDocFullscreen() {
        const card = document.getElementById('docViewerCard');
        const icon = document.getElementById('fullscreenIcon');
        if (card.classList.contains('doc-fullscreen-active')) {
            card.classList.remove('doc-fullscreen-active');
            icon.classList.remove('fa-compress');
            icon.classList.add('fa-expand');
        } else {
            card.classList.add('doc-fullscreen-active');
            icon.classList.remove('fa-expand');
            icon.classList.add('fa-compress');
        }
    }

    // Thoát fullscreen khi nhấn ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const card = document.getElementById('docViewerCard');
            if (card && card.classList.contains('doc-fullscreen-active')) {
                toggleDocFullscreen();
            }
        }
    });

    // Lọc bảng danh sách Excel trực tiếp
    function filterExcelTable() {
        const input = document.getElementById('excelSearchInput');
        if (!input) return;
        const query = input.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#excelTable tbody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    }
</script>
@endpush
@endsection
