@extends('layouts.admin')

@section('page_title', 'Quản Lý Hồ Sơ Bảo Vệ Khóa Luận')

@section('content')
<div class="container-fluid px-0">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 p-3 shadow-sm rounded-3 border-0" role="alert" style="border-left: 5px solid #198754 !important; background-color: #f0fdf4;">
            <i class="fa-solid fa-circle-check me-2 text-success"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 p-3 shadow-sm rounded-3 border-0" role="alert" style="border-left: 5px solid #dc3545 !important; background-color: #fef2f2;">
            <i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header Section (Đồng bộ chuẩn Học Kỳ) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: #00305a;">
                <i class="fa-solid fa-folder-check text-primary me-2"></i>Quản Lý Hồ Sơ Bảo Vệ &amp; Thẩm Định Turnitin
            </h4>
            <p class="text-muted small mb-0">Kiểm tra báo cáo toàn văn, kết quả quét trùng lặp Turnitin và phân bổ nhóm đủ điều kiện sang Hội đồng chấm bảo vệ.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.hoidong.index') }}" class="btn btn-outline-primary shadow-xs fw-semibold">
                <i class="fa-solid fa-landmark me-1"></i> Quản Lý Hội Đồng
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards: Cân đối, đồng đều, icon chuẩn, typography đồng nhất chuẩn Học Kỳ -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tổng Số Hồ Sơ</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #e0f2fe; color: #0284c7;">
                        <i class="fa-solid fa-folder-open fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $tongSoHoSo }}</span>
                    <span class="small text-muted fw-medium">hồ sơ đã nộp</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Chờ Thẩm Định</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fef3c7; color: #d97706;">
                        <i class="fa-solid fa-hourglass-half fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $choThamDinh }}</span>
                    <span class="small text-muted fw-medium">cần xét duyệt</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đủ Điều Kiện Bảo Vệ</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #dcfce7; color: #16a34a;">
                        <i class="fa-solid fa-clipboard-check fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-success lh-1">{{ $duDieuKien }}</span>
                    <span class="small text-muted fw-medium">Turnitin &le; 20%</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đã Xếp Hội Đồng</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #ede9fe; color: #7c3aed;">
                        <i class="fa-solid fa-landmark fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-primary lh-1">{{ $daPhanCong }}</span>
                    <span class="small text-muted fw-medium">sẵn sàng bảo vệ</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar (Đồng bộ chuẩn Học Kỳ) -->
    <div class="admin-filter-bar mb-4">
        <form method="GET" action="{{ route('admin.hosoBaoVe.index') }}" class="d-flex flex-wrap flex-xl-nowrap align-items-center justify-content-between gap-3 w-100">
            <div class="d-flex flex-grow-1 flex-wrap flex-md-nowrap align-items-center gap-2" style="min-width: 300px;">
                <div class="input-group" style="min-width: 240px; flex: 1;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Tìm theo mã hồ sơ, tên nhóm, tên đề tài...">
                </div>
                <select name="TrangThai" class="form-select" style="min-width: 210px;" onchange="this.form.submit()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="Chờ thẩm định" {{ request('TrangThai') === 'Chờ thẩm định' ? 'selected' : '' }}>🟠 Chờ thẩm định</option>
                    <option value="Đủ điều kiện bảo vệ" {{ request('TrangThai') === 'Đủ điều kiện bảo vệ' ? 'selected' : '' }}>🟢 Đủ điều kiện bảo vệ</option>
                    <option value="Đã phân công" {{ request('TrangThai') === 'Đã phân công' ? 'selected' : '' }}>🔵 Đã xếp Hội đồng</option>
                    <option value="Không đủ điều kiện" {{ request('TrangThai') === 'Không đủ điều kiện' ? 'selected' : '' }}>🔴 Không đủ điều kiện</option>
                    <option value="Đã lưu trữ" {{ request('TrangThai') === 'Đã lưu trữ' ? 'selected' : '' }}>⚪ Đã lưu trữ</option>
                </select>
                @if(request('search') || request('TrangThai'))
                    <a href="{{ route('admin.hosoBaoVe.index') }}" class="btn btn-outline-secondary px-3"><i class="fa-solid fa-xmark me-1"></i>Xóa lọc</a>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap ms-auto">
                <a href="{{ route('admin.hoidong.index') }}" class="btn btn-primary shadow-xs fw-semibold">
                    <i class="fa-solid fa-landmark me-1"></i> Quản Lý Hội Đồng
                </a>
            </div>
        </form>
    </div>

    <!-- Main Table Card (Đồng bộ chuẩn Học Kỳ) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Danh Sách Hồ Sơ Khóa Luận &amp; Thẩm Định Turnitin</span>
            <span class="small text-muted">Tổng số: <strong>{{ $hoSos->total() }}</strong> hồ sơ</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th class="ps-4 py-3" style="width: 130px;">MÃ HỒ SƠ</th>
                            <th class="py-3" style="min-width: 250px;">NHÓM / ĐỀ TÀI</th>
                            <th class="py-3" style="width: 170px;">KHÓA LUẬN / BẢN NỘP</th>
                            <th class="py-3 text-center" style="width: 140px;">TURNITIN %</th>
                            <th class="py-3 text-center" style="width: 160px;">TRẠNG THÁI</th>
                            <th class="py-3" style="min-width: 200px;">HỘI ĐỒNG BẢO VỆ</th>
                            <th class="pe-4 py-3 text-center" style="width: 130px;">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($hoSos as $hs)
                        <tr>
                            {{-- Mã HS --}}
                            <td class="ps-4 py-3">
                                <span class="badge-code">{{ $hs->MaHoSo }}</span>
                                <div class="small text-muted mt-1">{{ \Carbon\Carbon::parse($hs->NgayNop)->format('d/m/Y') }}</div>
                            </td>

                            {{-- Nhóm / Đề tài --}}
                            <td class="py-3">
                                <div class="fw-bold text-primary">{{ $hs->nhom->TenNhom ?? 'Chưa rõ nhóm' }}</div>
                                <div class="small text-dark fw-semibold mt-0.5">{{ Str::limit($hs->nhom->deTai->TenDeTai ?? 'Chưa có đề tài', 50) }}</div>
                                <div class="small text-muted mt-1">
                                    <span>TN: <strong>{{ $hs->nhom->truongNhom->HoTen ?? '—' }}</strong></span>
                                    @if($hs->nhom && $hs->nhom->deTai && $hs->nhom->deTai->giangVien)
                                        &bull; <span>GVHD: {{ $hs->nhom->deTai->giangVien->HoTen }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- File nộp --}}
                            <td class="py-3">
                                @if($hs->ToanVanKhoaLuan)
                                    <a href="{{ asset('storage/' . $hs->ToanVanKhoaLuan) }}" target="_blank" class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none px-2.5 py-1">
                                        <i class="fa-solid fa-file-pdf me-1"></i>Toàn văn (PDF)
                                    </a>
                                @else
                                    <span class="text-muted small fst-italic">Chưa có tệp</span>
                                @endif
                                @if($hs->FileBanChinhSua)
                                    <div class="mt-1">
                                        <a href="{{ asset('storage/' . $hs->FileBanChinhSua) }}" target="_blank" class="badge bg-info-subtle text-info border border-info-subtle text-decoration-none px-2 py-0.5" style="font-size: 0.72rem;">
                                            <i class="fa-solid fa-file-circle-check me-1"></i>Bản sau bảo vệ
                                        </a>
                                    </div>
                                @endif
                            </td>

                            {{-- Turnitin % --}}
                            <td class="text-center py-3">
                                @php $pct = (float)$hs->TyLeTrungLap; @endphp
                                <div class="fs-6 fw-bold {{ $pct <= 20 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($pct, 2) }}%
                                </div>
                                @if($pct <= 20)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">
                                        <i class="fa-solid fa-check me-1"></i>Hợp lệ (&le; 20%)
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">
                                        <i class="fa-solid fa-xmark me-1"></i>Vượt ngưỡng
                                    </span>
                                @endif

                                @if($hs->MinhChungDaoVan)
                                    <div class="mt-1">
                                        <a href="{{ asset('storage/' . $hs->MinhChungDaoVan) }}" target="_blank" class="badge bg-danger-subtle text-danger border border-danger-subtle text-decoration-none px-2 py-0.5" style="font-size: 0.68rem;">
                                            <i class="fa-solid fa-file-shield me-1"></i>Minh chứng
                                        </a>
                                    </div>
                                @endif
                            </td>

                            {{-- Trạng thái --}}
                            <td class="text-center py-3">
                                @php
                                    $st = $hs->TrangThai;
                                @endphp
                                @if(in_array($st, ['Đã phân công', 'Đã phân công hội đồng']))
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                        <i class="fa-solid fa-landmark me-1" style="font-size: 0.55rem;"></i> Đã xếp HĐ
                                    </span>
                                @elseif($st === 'Đủ điều kiện bảo vệ')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                        <i class="fa-solid fa-circle-check me-1" style="font-size: 0.55rem;"></i> Đủ điều kiện
                                    </span>
                                @elseif($st === 'Không đủ điều kiện')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                        <i class="fa-solid fa-xmark me-1" style="font-size: 0.55rem;"></i> Không đạt
                                    </span>
                                @elseif($st === 'Đã lưu trữ')
                                    <span class="badge bg-light text-muted border rounded-pill px-3 py-1.5 fw-medium">
                                        <i class="fa-solid fa-box-archive me-1"></i> Đã lưu trữ
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1.5 fw-semibold">
                                        <i class="fa-solid fa-clock me-1" style="font-size: 0.55rem;"></i> Chờ thẩm định
                                    </span>
                                @endif
                            </td>

                            {{-- Hội đồng bảo vệ --}}
                            <td class="py-3">
                                @if($hs->hoiDong)
                                    <div class="fw-bold text-dark small"><i class="fa-solid fa-landmark me-1 text-primary"></i>{{ $hs->hoiDong->TenHoiDong }}</div>
                                    @if($hs->ThoiGianBaoVe || $hs->hoiDong->ThoiGianBatDau)
                                        <div class="small text-muted"><i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($hs->ThoiGianBaoVe ?? $hs->hoiDong->ThoiGianBatDau)->format('H:i d/m/Y') }}</div>
                                    @endif
                                    @if($hs->PhongBaoVe || $hs->hoiDong->DiaDiem)
                                        <div class="small text-muted"><i class="fa-solid fa-location-dot me-1 text-danger"></i>{{ $hs->PhongBaoVe ?? $hs->hoiDong->DiaDiem }}</div>
                                    @endif
                                @else
                                    <span class="text-muted small fst-italic">Chưa xếp Hội đồng</span>
                                @endif
                            </td>

                            {{-- Thao tác --}}
                            <td class="pe-4 text-center py-3">
                                <div class="d-flex justify-content-center align-items-center gap-1">
                                    {{-- Thao tác Thẩm định nếu đang Chờ thẩm định hoặc Không đủ điều kiện --}}
                                    @if($hs->TrangThai === 'Chờ thẩm định' || $hs->TrangThai === 'Không đủ điều kiện')
                                        <form action="{{ route('admin.hosoBaoVe.xacNhan', $hs->MaHoSo) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-sm btn-light text-success btn-action-circle border" title="Duyệt đủ điều kiện bảo vệ" onclick="return confirm('Xác nhận duyệt hồ sơ này ĐỦ ĐIỀU KIỆN BẢO VỆ?')">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>

                                        @if($hs->TrangThai === 'Chờ thẩm định')
                                        <button type="button" class="btn btn-sm btn-light text-danger btn-action-circle border" title="Từ chối hồ sơ" data-bs-toggle="modal" data-bs-target="#modalTuChoi{{ $hs->MaHoSo }}">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                        @endif
                                    @endif

                                    {{-- Nút Phân công Hội đồng --}}
                                    @if(in_array($hs->TrangThai, ['Đủ điều kiện bảo vệ', 'Đã phân công', 'Đã phân công hội đồng']))
                                        <button type="button" class="btn btn-sm btn-light text-primary btn-action-circle border" title="{{ $hs->hoiDong ? 'Đổi Hội đồng' : 'Xếp Hội đồng bảo vệ' }}" data-bs-toggle="modal" data-bs-target="#modalPhanCong{{ $hs->MaHoSo }}">
                                            <i class="fa-solid fa-landmark"></i>
                                        </button>
                                    @endif

                                    {{-- Nút Lưu Trữ Hồ Sơ Sau Bảo Vệ --}}
                                    @if($hs->FileBanChinhSua && $hs->TrangThai !== 'Đã lưu trữ')
                                        <form action="{{ route('admin.hosoBaoVe.luuTru', $hs->MaHoSo) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light text-secondary btn-action-circle border" title="Lưu trữ hồ sơ sau bảo vệ" onclick="return confirm('Xác nhận hoàn tất và lưu trữ hồ sơ khóa luận này?')">
                                                <i class="fa-solid fa-box-archive"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <!-- Modal Từ chối Hồ sơ (Chuẩn HUIT Modal) -->
                                <div class="modal fade text-start" id="modalTuChoi{{ $hs->MaHoSo }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <form action="{{ route('admin.hosoBaoVe.xacNhan', $hs->MaHoSo) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="action" value="reject">
                                                <div class="modal-header border-bottom-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-danger">
                                                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Từ Chối Hồ Sơ — {{ $hs->MaHoSo }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body py-3">
                                                    <div class="small text-muted mb-3">Nhóm: <strong>{{ $hs->nhom->TenNhom ?? '' }}</strong> &bull; Tỷ lệ Turnitin: <strong>{{ $hs->TyLeTrungLap }}%</strong></div>
                                                    <div>
                                                        <label class="form-label fw-bold">Lý do từ chối <span class="text-danger">*</span></label>
                                                        <textarea name="GhiChu" class="form-control" rows="3" placeholder="Nhập lý do từ chối (VD: Tỷ lệ trùng lặp vượt quá 20%, thiếu minh chứng kiểm tra...)" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top-0 pt-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Xác Nhận Từ Chối</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Phân công Hội đồng (Chuẩn HUIT Modal) -->
                                <div class="modal fade text-start" id="modalPhanCong{{ $hs->MaHoSo }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <form action="{{ route('admin.hosoBaoVe.phanCong', $hs->MaHoSo) }}" method="POST">
                                                @csrf
                                                <div class="modal-header border-bottom-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-primary">
                                                        <i class="fa-solid fa-landmark me-2"></i>Xếp Hội Đồng Bảo Vệ — {{ $hs->MaHoSo }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body py-3">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Chọn Hội Đồng Bảo Vệ <span class="text-danger">*</span></label>
                                                        <select name="MaHoiDong" class="form-select" required>
                                                            <option value="">— Chọn Hội đồng chấm —</option>
                                                            @foreach($hoiDongs as $hd)
                                                                <option value="{{ $hd->MaHoiDong }}" {{ $hs->MaHoiDong === $hd->MaHoiDong ? 'selected' : '' }}>
                                                                    {{ $hd->TenHoiDong }} ({{ $hd->DiaDiem ?? 'Phòng chưa xếp' }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <label class="form-label fw-bold small">Ngày &amp; Giờ Bảo Vệ</label>
                                                            <input type="datetime-local" name="ThoiGianBaoVe" class="form-control form-control-sm" 
                                                                value="{{ $hs->ThoiGianBaoVe ? \Carbon\Carbon::parse($hs->ThoiGianBaoVe)->format('Y-m-d\TH:i') : '' }}">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label fw-bold small">Phòng Bảo Vệ</label>
                                                            <input type="text" name="PhongBaoVe" class="form-control form-control-sm" 
                                                                placeholder="VD: Phòng B.304" value="{{ $hs->PhongBaoVe }}">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top-0 pt-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Lưu Phân Công</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state-box">
                                    <i class="fa-solid fa-folder-open text-muted mb-3" style="font-size: 2.5rem;"></i>
                                    <h6 class="fw-bold text-secondary">Không có hồ sơ bảo vệ nào</h6>
                                    <p class="small text-muted mb-0">Hồ sơ sẽ hiển thị khi sinh viên nộp báo cáo toàn văn và kết quả Turnitin.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center">
            <span class="small text-muted">
                Hiển thị {{ $hoSos->firstItem() ?? 0 }}–{{ $hoSos->lastItem() ?? 0 }} trên tổng số {{ $hoSos->total() }} hồ sơ hệ thống
            </span>
            @if($hoSos->hasPages())
                <div>
                    {{ $hoSos->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
