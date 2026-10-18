@extends('layouts.giangvien')

@section('page_title', 'Phản Biện Đề Cương Đề Tài')

@push('styles')
<style>
    :root {
        --gv-border: #e2e8f0;
        --gv-bg-subtle: #f8fafc;
        --huit-primary: #00305a;
        --huit-accent: #0284c7;
    }

    .card-dashboard {
        background: #ffffff;
        border: 1px solid var(--gv-border);
        border-radius: 1rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }

    .table-custom th {
        background: var(--gv-bg-subtle);
        font-size: 0.75rem;
        font-weight: 700;
        color: #475569;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        border-bottom: 1px solid var(--gv-border);
        padding: 1.25rem 1.5rem;
        white-space: nowrap;
    }

    .table-custom td {
        padding: 1.5rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    .table-custom tbody tr {
        transition: background-color 0.15s ease;
    }

    .table-custom tbody tr:hover {
        background-color: #f8fafc;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <!-- HEADER TITLE -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-2">
                <i class="fa-solid fa-file-pen me-2 text-primary"></i>
                Danh Sách Đề Tài Được Phân Công Phản Biện Đề Cương
            </h4>
            <p class="text-muted small mb-0">
                Quản lý, xem nhanh đề cương và thực hiện đánh giá các đề tài được phân công.
            </p>
        </div>
    </div>

    <!-- SEMESTER FILTER BAR -->
    <div class="card-dashboard mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('giangvien.phanbien.index') }}" class="row g-3 align-items-center">
                @if(request('KetQua'))
                    <input type="hidden" name="KetQua" value="{{ request('KetQua') }}">
                @endif
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted fw-bold mb-2">
                        <i class="fa-regular fa-calendar me-2 text-primary"></i>Học Kỳ:
                    </label>
                    <select name="MaHocKy" class="form-select form-select-sm rounded-pill px-3 py-2" onchange="this.form.submit()">
                        <option value="ALL" {{ $selectedHocKy === 'ALL' ? 'selected' : '' }}>-- Tất cả học kỳ --</option>
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }} {{ $hk->TrangThai == 'Đang diễn ra' ? '★' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-8 d-flex align-items-center justify-content-md-end gap-3 mt-3 mt-md-0 flex-wrap">
                    @php
                        $curHkObj = $hocKies->firstWhere('MaHocKy', $selectedHocKy);
                    @endphp
                    <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small fw-medium">
                        Hiển thị: <strong class="text-dark">{{ $curHkObj?->TenHocKy ?? ($selectedHocKy === 'ALL' ? 'Tất cả học kỳ' : $selectedHocKy) }}</strong> &bull; Tổng <strong class="text-dark">{{ $phanCongs->total() }}</strong> đề tài
                    </span>
                    @if(request('MaHocKy') || request('KetQua'))
                        <a href="{{ route('giangvien.phanbien.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 fw-medium d-inline-flex align-items-center" title="Đặt lại bộ lọc">
                            <i class="fa-solid fa-rotate-left me-2"></i> Đặt lại
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- STATUS TABS -->
    <div class="card-dashboard mb-4">
        <div class="card-body p-3">
            <ul class="nav nav-pills flex-column flex-sm-row gap-2">
                <li class="nav-item flex-fill text-center">
                    <a class="nav-link py-2 px-3 rounded-pill fw-semibold small {{ !request()->filled('KetQua') ? 'active bg-primary text-white shadow-xs' : 'text-dark bg-light' }}" 
                       href="{{ route('giangvien.phanbien.index', array_filter(['MaHocKy' => $selectedHocKy])) }}">
                        Tất cả <span class="badge {{ !request()->filled('KetQua') ? 'bg-white text-primary' : 'bg-secondary text-white' }} ms-2">{{ $counts['total'] }}</span>
                    </a>
                </li>
                <li class="nav-item flex-fill text-center">
                    <a class="nav-link py-2 px-3 rounded-pill fw-semibold small {{ request('KetQua') === 'chua_danh_gia' ? 'active bg-warning text-dark shadow-xs' : 'text-dark bg-light' }}" 
                       href="{{ route('giangvien.phanbien.index', array_filter(['MaHocKy' => $selectedHocKy, 'KetQua' => 'chua_danh_gia'])) }}">
                        <i class="fa-solid fa-clock me-2"></i> Chưa Đánh Giá 
                        <span class="badge {{ request('KetQua') === 'chua_danh_gia' ? 'bg-dark text-white' : 'bg-secondary text-white' }} ms-2">{{ $counts['chua_danh_gia'] }}</span>
                    </a>
                </li>
                <li class="nav-item flex-fill text-center">
                    <a class="nav-link py-2 px-3 rounded-pill fw-semibold small {{ request('KetQua') === 'da_nop_lai' ? 'active bg-primary text-white shadow-xs' : 'text-dark bg-light' }}" 
                       href="{{ route('giangvien.phanbien.index', array_filter(['MaHocKy' => $selectedHocKy, 'KetQua' => 'da_nop_lai'])) }}">
                        <i class="fa-solid fa-rotate me-2"></i> Đã nộp lại 
                        <span class="badge {{ request('KetQua') === 'da_nop_lai' ? 'bg-white text-primary' : 'bg-secondary text-white' }} ms-2">{{ $counts['da_nop_lai'] }}</span>
                    </a>
                </li>
                <li class="nav-item flex-fill text-center">
                    <a class="nav-link py-2 px-3 rounded-pill fw-semibold small {{ request('KetQua') === 'Đạt' ? 'active bg-success text-white shadow-xs' : 'text-dark bg-light' }}" 
                       href="{{ route('giangvien.phanbien.index', array_filter(['MaHocKy' => $selectedHocKy, 'KetQua' => 'Đạt'])) }}">
                        Đạt <span class="badge {{ request('KetQua') === 'Đạt' ? 'bg-white text-success' : 'bg-secondary text-white' }} ms-2">{{ $counts['dat'] }}</span>
                    </a>
                </li>
                <li class="nav-item flex-fill text-center">
                    <a class="nav-link py-2 px-3 rounded-pill fw-semibold small {{ request('KetQua') === 'Yêu cầu chỉnh sửa' ? 'active bg-warning text-dark shadow-xs' : 'text-dark bg-light' }}" 
                       href="{{ route('giangvien.phanbien.index', array_filter(['MaHocKy' => $selectedHocKy, 'KetQua' => 'Yêu cầu chỉnh sửa'])) }}">
                        Yêu cầu sửa <span class="badge {{ request('KetQua') === 'Yêu cầu chỉnh sửa' ? 'bg-dark text-white' : 'bg-secondary text-white' }} ms-2">{{ $counts['yeu_cau_sua'] }}</span>
                    </a>
                </li>
                <li class="nav-item flex-fill text-center">
                    <a class="nav-link py-2 px-3 rounded-pill fw-semibold small {{ request('KetQua') === 'Không đạt' ? 'active bg-danger text-white shadow-xs' : 'text-dark bg-light' }}" 
                       href="{{ route('giangvien.phanbien.index', array_filter(['MaHocKy' => $selectedHocKy, 'KetQua' => 'Không đạt'])) }}">
                        Không đạt <span class="badge {{ request('KetQua') === 'Không đạt' ? 'bg-white text-danger' : 'bg-secondary text-white' }} ms-2">{{ $counts['khong_dat'] }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- ASSIGNMENTS TABLE -->
    <div class="card-dashboard">
        <div class="card-body p-0">
            @if($phanCongs->isEmpty())
                <div class="text-center py-5 text-muted">
                    <div class="py-5">
                        <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                        <h6 class="fw-medium text-secondary mb-0">Hiện tại quý Thầy/Cô không có đề cương nào được phân công phản biện trong học kỳ này.</h6>
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th width="12%">Mã ĐT</th>
                                <th width="28%">Tên Đề Tài &amp; Ngành</th>
                                <th width="16%">GV Đề Xuất</th>
                                <th width="12%">File Đề Cương</th>
                                <th width="12%">Ngày Phân Công</th>
                                <th width="12%">Kết Quả Đánh Giá</th>
                                <th width="8%" class="text-end pe-4">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($phanCongs as $pc)
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark fw-bold border px-3 py-2 font-monospace">
                                            {{ $pc->MaDeTai }}
                                        </span>
                                        <div class="small text-muted mt-2" style="font-size: 0.76rem;">{{ $pc->deTai->hocKy->TenHocKy ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark line-clamp-2 small mb-2" title="{{ $pc->deTai->TenDeTai ?? 'N/A' }}" style="line-height: 1.5;">
                                            {{ $pc->deTai->TenDeTai ?? 'N/A' }}
                                        </div>
                                        <div class="small text-muted d-flex flex-wrap gap-2 align-items-center mt-1" style="font-size: 0.8rem;">
                                            <span><i class="fa-solid fa-tag me-2 text-primary"></i>{{ $pc->deTai->nganh->TenNganh ?? 'Toàn ngành' }}</span>
                                            <span class="badge bg-light text-muted border rounded-pill px-3 py-1" style="font-size: 0.72rem;">{{ $pc->deTai->HocPhan ?? 'Khóa luận cử nhân' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark small mb-1">{{ $pc->deTai->giangVien->HoTen ?? 'N/A' }}</div>
                                        <div class="text-muted" style="font-size: 0.8rem;">{{ $pc->deTai->giangVien->boMon->TenBoMon ?? '' }}</div>
                                    </td>
                                    <td>
                                        @if($pc->deTai && $pc->deTai->FileDeCuong)
                                            @php
                                                $filePath = Str::startsWith($pc->deTai->FileDeCuong, ['http', 'storage/']) ? asset($pc->deTai->FileDeCuong) : asset('storage/' . $pc->deTai->FileDeCuong);
                                                $fileName = basename($pc->deTai->FileDeCuong);
                                            @endphp
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-2 shadow-xs fw-medium small d-inline-flex align-items-center"
                                                    onclick="quickPreviewOutline('{{ $filePath }}', '{{ $fileName }}', '{{ addslashes($pc->deTai->TenDeTai) }}')"
                                                    title="Xem nhanh đề cương trực tiếp trên web">
                                                <i class="fa-solid fa-eye me-2"></i> Xem đề cương
                                            </button>
                                        @else
                                            <span class="badge bg-light text-muted border rounded-pill px-3 py-2 small fw-normal">Chưa có file</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted fw-medium">
                                        {{ $pc->NgayPhanCong ? \Carbon\Carbon::parse($pc->NgayPhanCong)->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td>
                                        @php
                                            $isNopLai = $pc->KetQua === 'Đã nộp lại' 
                                                || $pc->TrangThai === 'Đã nộp lại đề cương' 
                                                || ($pc->deTai && $pc->deTai->TrangThai === 'Đã cập nhật đề cương - Chờ phản biện lại');
                                        @endphp
                                        @if($pc->KetQua === 'Đạt')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold small">
                                                <i class="fa-solid fa-circle-check me-2"></i> Đạt
                                            </span>
                                        @elseif($isNopLai)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold small d-inline-block text-truncate" style="max-width: 150px;" title="Đã nộp lại đề cương">
                                                <i class="fa-solid fa-rotate me-2"></i> Đã nộp lại
                                            </span>
                                            <div class="small text-primary fw-bold mt-2" style="font-size: 0.76rem;">
                                                <i class="fa-solid fa-bell me-2"></i>Chờ phản biện lại
                                            </div>
                                        @elseif($pc->KetQua === 'Yêu cầu chỉnh sửa')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2 fw-semibold small">
                                                <i class="fa-solid fa-pen me-2"></i> Yêu cầu sửa
                                            </span>
                                            <div class="small text-muted mt-2" style="font-size: 0.76rem;">(Chờ GV sửa)</div>
                                        @elseif($pc->KetQua === 'Không đạt')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 fw-semibold small">
                                                <i class="fa-solid fa-circle-xmark me-2"></i> Không đạt
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2 fw-semibold small">
                                                <i class="fa-solid fa-clock me-2"></i> Chưa đánh giá
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        @if($isNopLai)
                                            <a href="{{ route('giangvien.phanbien.show', $pc->MaPhanCong) }}" class="btn btn-sm btn-primary rounded-pill px-3 py-2 shadow-xs fw-semibold small d-inline-flex align-items-center justify-content-center">
                                                <i class="fa-solid fa-pen-to-square me-2"></i> Phản biện lại
                                            </a>
                                        @elseif($pc->KetQua)
                                            <a href="{{ route('giangvien.phanbien.show', $pc->MaPhanCong) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 shadow-xs fw-medium small d-inline-flex align-items-center justify-content-center">
                                                <i class="fa-solid fa-eye me-2"></i> Xem lại
                                            </a>
                                        @else
                                            <a href="{{ route('giangvien.phanbien.show', $pc->MaPhanCong) }}" class="btn btn-sm btn-primary rounded-pill px-4 py-2 shadow-xs fw-semibold small d-inline-flex align-items-center justify-content-center">
                                                <i class="fa-solid fa-pen-to-square me-2"></i> Đánh giá
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($phanCongs->hasPages())
                    <div class="p-4 border-top d-flex justify-content-end bg-white">
                        {{ $phanCongs->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@include('partials.modal_preview_decuong')
@endsection