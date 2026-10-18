@extends('layouts.giangvien')

@section('page_title', 'Xác Nhận Hồ Sơ Bảo Vệ Khóa Luận')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-user-check me-2 text-primary"></i>
                Xác Nhận Điều Kiện Bảo Vệ Khóa Luận (Dành Cho GVHD)
            </h4>
            <p class="text-muted mb-0 small">
                Kiểm tra báo cáo toàn văn, mã nguồn dự án, sản phẩm triển khai và phê duyệt điều kiện bảo vệ cho các nhóm sinh viên do bạn hướng dẫn.
            </p>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
        <div class="card-body p-2">
            <ul class="nav nav-pills flex-column flex-sm-row gap-1">
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ !request()->filled('xac_nhan') ? 'active' : '' }}" 
                       href="{{ route('giangvien.xacnhan_baove.index', array_merge(request()->except('xac_nhan', 'page'))) }}">
                        Tất cả <span class="badge bg-secondary ms-1">{{ $counts['total'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('xac_nhan') === 'cho_xac_nhan' ? 'active bg-warning text-dark' : '' }}" 
                       href="{{ route('giangvien.xacnhan_baove.index', array_merge(request()->except('xac_nhan', 'page'), ['xac_nhan' => 'cho_xac_nhan'])) }}">
                        <i class="fa-solid fa-clock me-1"></i> Chờ Xác Nhận 
                        <span class="badge bg-dark ms-1">{{ $counts['cho_xac_nhan'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('xac_nhan') === 'yeu_cau_sua' ? 'active bg-warning text-dark' : '' }}" 
                       href="{{ route('giangvien.xacnhan_baove.index', array_merge(request()->except('xac_nhan', 'page'), ['xac_nhan' => 'yeu_cau_sua'])) }}">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Yêu Cầu Chỉnh Sửa 
                        <span class="badge bg-secondary ms-1">{{ $counts['yeu_cau_sua'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('xac_nhan') === 'da_xac_nhan' ? 'active bg-success' : '' }}" 
                       href="{{ route('giangvien.xacnhan_baove.index', array_merge(request()->except('xac_nhan', 'page'), ['xac_nhan' => 'da_xac_nhan'])) }}">
                        <i class="fa-solid fa-circle-check me-1"></i> Đã Duyệt Bảo Vệ 
                        <span class="badge bg-secondary ms-1">{{ $counts['da_xac_nhan'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('xac_nhan') === 'khong_dat' ? 'active bg-danger' : '' }}" 
                       href="{{ route('giangvien.xacnhan_baove.index', array_merge(request()->except('xac_nhan', 'page'), ['xac_nhan' => 'khong_dat'])) }}">
                        <i class="fa-solid fa-circle-xmark me-1"></i> Không Đồng Ý 
                        <span class="badge bg-secondary ms-1">{{ $counts['khong_dat'] }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Bộ lọc & Tìm kiếm -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <form action="{{ route('giangvien.xacnhan_baove.index') }}" method="GET" class="row g-2 align-items-center">
                @if(request()->filled('xac_nhan'))
                    <input type="hidden" name="xac_nhan" value="{{ request('xac_nhan') }}">
                @endif
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm theo tên nhóm, đề tài, tên hoặc mã sinh viên..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="hoc_ky" class="form-select">
                        <option value="">-- Tất cả học kỳ --</option>
                        @foreach($hocKys as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ request('hoc_ky') == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy ?? ("Học kỳ " . $hk->HocKySo . " - Năm học " . $hk->NamHoc) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 flex-grow-1">
                        <i class="fa-solid fa-filter me-1"></i> Lọc dữ liệu
                    </button>
                    @if(request()->filled('search') || request()->filled('hoc_ky') || request()->filled('xac_nhan'))
                        <a href="{{ route('giangvien.xacnhan_baove.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Xóa bộ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Dossiers Table -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body p-0">
            @if($hoSos->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary"></i>
                    <h6>Không có hồ sơ bảo vệ nào theo tiêu chí lọc.</h6>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 110px;">Mã / Phiên Bản</th>
                                <th style="min-width: 190px;">Nhóm Sinh Viên</th>
                                <th style="min-width: 250px;">Đề Tài Khóa Luận</th>
                                <th style="min-width: 200px;">Hồ Sơ Đã Nộp</th>
                                <th style="min-width: 160px;">Trạng Thái &amp; Xác Nhận</th>
                                <th class="text-end" style="width: 160px;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hoSos as $hs)
                                @php
                                    $phienBan = $hs->phienBans->first();
                                    $turnitinPct = $phienBan?->TyLeTurnitin ?? $hs->TyLeTrungLap;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-primary font-monospace">{{ $hs->MaHoSo }}</div>
                                        <span class="badge bg-light text-dark border rounded-pill font-monospace" style="font-size: 11px;">
                                            v{{ number_format($hs->PhienBanHienTai ?? 1, 1, '.', '') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $hs->nhom->TenNhom ?? 'N/A' }}</div>
                                        <div class="small text-muted">
                                            Trưởng nhóm: {{ $hs->nhom->truongNhom->HoTen ?? 'N/A' }} 
                                            <span class="font-monospace">({{ $hs->nhom->MaTruongNhom ?? '' }})</span>
                                        </div>
                                        <div class="small text-secondary">
                                            {{ $hs->nhom->hocPhan->TenHocPhan ?? '' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $hs->deTai->TenDeTai ?? 'N/A' }}</div>
                                        <div class="small text-muted">Mã ĐT: {{ $hs->MaDeTai }}</div>
                                        @if($hs->NgayNopGanNhat)
                                            <div class="small text-muted mt-1">
                                                <i class="fa-regular fa-clock me-1"></i>Nộp: {{ \Carbon\Carbon::parse($hs->NgayNopGanNhat)->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <!-- Báo cáo PDF -->
                                            @if($hs->ToanVanKhoaLuan)
                                                <button type="button" class="btn btn-link text-danger p-0 text-start small text-truncate text-decoration-none" style="max-width: 220px;" onclick="openPdfModal('{{ asset('storage/' . $hs->ToanVanKhoaLuan) }}', 'Báo Cáo Khóa Luận - Đề Tài {{ $hs->MaDeTai }}')">
                                                    <i class="fa-solid fa-file-pdf me-1"></i> Báo cáo toàn văn
                                                </button>
                                            @else
                                                <span class="text-muted small"><i class="fa-solid fa-file-pdf me-1"></i> Chưa có báo cáo</span>
                                            @endif

                                            <!-- Source code -->
                                            @if($hs->LinkSourceCode)
                                                <a href="{{ $hs->LinkSourceCode }}" target="_blank" class="text-dark small text-truncate" style="max-width: 220px;" title="{{ $hs->LinkSourceCode }}">
                                                    <i class="fa-brands fa-github me-1"></i> Mã nguồn (Git)
                                                </a>
                                            @endif

                                            <!-- Deploy -->
                                            @if($hs->LinkDeploy)
                                                <a href="{{ $hs->LinkDeploy }}" target="_blank" class="text-info small text-truncate" style="max-width: 220px;" title="{{ $hs->LinkDeploy }}">
                                                    <i class="fa-solid fa-globe me-1"></i> Deploy sản phẩm
                                                </a>
                                            @elseif($hs->LyDoKhongDeploy)
                                                <span class="text-muted small text-truncate" style="max-width: 220px;" title="Lý do không deploy: {{ $hs->LyDoKhongDeploy }}">
                                                    <i class="fa-solid fa-ban me-1"></i> Không deploy online
                                                </span>
                                            @endif

                                            <!-- Turnitin -->
                                            @if($turnitinPct !== null)
                                                <span class="small {{ $turnitinPct <= 20 ? 'text-success' : 'text-danger' }}">
                                                    <i class="fa-solid fa-shield-halved me-1"></i> Turnitin: <strong>{{ number_format($turnitinPct, 1) }}%</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($hs->XacNhanGVHD === 'Đã duyệt' || in_array($hs->TrangThai, ['Đã phê duyệt', 'Đủ điều kiện bảo vệ']))
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold">
                                                <i class="fa-solid fa-circle-check me-1"></i> Đã duyệt bảo vệ
                                            </span>
                                            <div class="small text-muted mt-1">{{ $hs->NgayXacNhan ? \Carbon\Carbon::parse($hs->NgayXacNhan)->format('d/m/Y') : '' }}</div>
                                        @elseif($hs->TrangThai === 'Yêu cầu chỉnh sửa')
                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1 rounded-pill fw-semibold">
                                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Yêu cầu chỉnh sửa
                                            </span>
                                            @if($hs->HanNopLai)
                                                <div class="small text-danger mt-1">Hạn: {{ \Carbon\Carbon::parse($hs->HanNopLai)->format('d/m/Y') }}</div>
                                            @endif
                                        @elseif($hs->XacNhanGVHD === 'Không duyệt' || in_array($hs->TrangThai, ['Không duyệt', 'Không đủ điều kiện']))
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill fw-semibold">
                                                <i class="fa-solid fa-circle-xmark me-1"></i> Không đồng ý
                                            </span>
                                        @else
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill fw-semibold">
                                                <i class="fa-solid fa-clock me-1"></i> Chờ GVHD kiểm tra
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('giangvien.xacnhan_baove.show', $hs->MaHoSo) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-xs d-inline-flex align-items-center">
                                            <i class="fa-solid fa-clipboard-check me-1"></i> Kiểm Tra
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($hoSos->hasPages())
                    <div class="p-3 border-top d-flex justify-content-end">
                        {{ $hoSos->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- MODAL XEM BÁO CÁO TRỰC TIẾP TRÊN MÀN HÌNH (KHÔNG CHUYỂN TAB) -->
<div class="modal fade" id="modalPdfPreview" tabindex="-1" aria-hidden="true" style="backdrop-filter: blur(4px);">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 95vw; height: 92vh;">
        <div class="modal-content h-100 shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-dark text-white py-2.5 px-4">
                <div class="d-flex align-items-center gap-2 text-truncate">
                    <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
                    <h6 class="modal-title fw-bold mb-0 text-truncate" id="pdfPreviewTitle">Xem Báo Cáo Khóa Luận</h6>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="btnDownloadPdfModal" download class="btn btn-sm btn-outline-light rounded-pill px-3">
                        <i class="fa-solid fa-download me-1"></i> Tải về máy
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 bg-secondary bg-opacity-10" style="height: calc(100% - 58px);">
                <iframe id="pdfPreviewIframe" src="" width="100%" height="100%" style="border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openPdfModal(url, title = 'Xem Báo Cáo Khóa Luận') {
        const titleEl = document.getElementById('pdfPreviewTitle');
        const iframeEl = document.getElementById('pdfPreviewIframe');
        const dlBtn = document.getElementById('btnDownloadPdfModal');
        
        if (titleEl) titleEl.textContent = title;
        if (iframeEl) iframeEl.src = url + '#toolbar=1';
        if (dlBtn) dlBtn.href = url;

        const modalEl = document.getElementById('modalPdfPreview');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('modalPdfPreview');
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function () {
                const iframeEl = document.getElementById('pdfPreviewIframe');
                if (iframeEl) iframeEl.src = '';
            });
        }
    });
</script>
@endpush

@endsection
