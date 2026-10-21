@extends('layouts.truongkhoa')

@section('page_title', 'Kế Hoạch Khóa Luận')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-calendar-check me-2 text-primary"></i>
                Kế Hoạch &amp; Tiến Độ Khóa Luận – {{ $khoa->TenKhoa ?? 'Khoa CNTT' }}
            </h4>
        </div>
    </div>

    <!-- ═══ MỐC THỜI GIAN SẮP TỚI / ĐANG DIỄN RA ═══ -->
    @if(isset($upcomingMocs) && $upcomingMocs->isNotEmpty())
        <style>
            .timeline-card-item {
                background: rgba(255, 255, 255, 0.12);
                border: 1px solid rgba(255, 255, 255, 0.22);
                transition: all 0.25s ease-in-out;
                min-height: 146px;
            }
            .timeline-card-item:hover {
                background: rgba(255, 255, 255, 0.18) !important;
                border-color: rgba(255, 255, 255, 0.38) !important;
                transform: translateY(-2px);
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
            }
            .timeline-card-title {
                font-size: 0.88rem;
                font-weight: 700;
                line-height: 1.38;
                color: #ffffff;
                height: 42px;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            .timeline-card-date {
                font-size: 0.78rem;
                color: rgba(255, 255, 255, 0.88);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        </style>

        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; background: linear-gradient(135deg, #003859 0%, #0072ce 100%); color: #fff;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h6 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-warning fs-5"></i>
                        <span>Các Mốc Thời Gian Quy Trình Đang Diễn Ra / Sắp Tới</span>
                    </h6>
                    <span class="badge bg-warning text-dark px-3 py-1 fw-bold rounded-pill shadow-xs">
                        <i class="fa-solid fa-bolt me-1"></i>Timeline quan trọng
                    </span>
                </div>

                <div class="row g-3 row-cols-1 row-cols-md-2 row-cols-lg-3 align-items-stretch">
                    @foreach($upcomingMocs as $moc)
                        @php
                            $now = now()->toDateString();
                            $isNow = $moc->NgayBatDau <= $now && $now <= $moc->NgayKetThuc;
                        @endphp
                        <div class="col d-flex">
                            <div class="p-3 rounded-3 w-100 d-flex flex-column justify-content-between timeline-card-item">
                                <!-- Phần thông tin tiêu đề và ngày (cố định chiều cao) -->
                                <div class="mb-2">
                                    <div class="timeline-card-title mb-2" title="{{ $moc->TenMoc }}">
                                        {{ $moc->TenMoc }}
                                    </div>
                                    <div class="timeline-card-date d-flex align-items-center gap-1">
                                        <i class="fa-regular fa-calendar-days text-warning me-1 flex-shrink-0"></i>
                                        <span>{{ \Carbon\Carbon::parse($moc->NgayBatDau)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($moc->NgayKetThuc)->format('d/m/Y') }}</span>
                                    </div>
                                </div>

                                <!-- Thanh trạng thái cố định ở đáy ô -->
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-2" style="border-top: 1px solid rgba(255, 255, 255, 0.16);">
                                    <span class="badge bg-white text-primary fw-semibold text-truncate px-2 py-1 shadow-xs" style="font-size: 0.68rem; max-width: 140px;" title="{{ $moc->keHoachKhoaLuan->hocKy->TenHocKy ?? 'Kế hoạch HK' }}">
                                        <i class="fa-solid fa-graduation-cap me-1"></i>{{ $moc->keHoachKhoaLuan->hocKy->TenHocKy ?? 'Kế hoạch HK' }}
                                    </span>
                                    @if($isNow)
                                        <span class="badge bg-danger text-white px-2 py-1 shadow-xs d-inline-flex align-items-center gap-1 rounded-pill" style="font-size: 0.68rem; font-weight: 700;">
                                            <span class="spinner-grow spinner-grow-sm" style="width: 5px; height: 5px;" role="status"></span>
                                            Đang diễn ra
                                        </span>
                                    @else
                                        <span class="badge bg-info text-white px-2 py-1 shadow-xs d-inline-flex align-items-center gap-1 rounded-pill" style="font-size: 0.68rem; font-weight: 600;">
                                            <i class="fa-regular fa-clock"></i>
                                            Sắp diễn ra
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- ═══ TOOLBAR LỌC HỌC KỲ & TÌM KIẾM ═══ -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body">
            <form method="GET" action="{{ route('truongkhoa.kehoach.index') }}" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Tìm kiếm theo tên kế hoạch hoặc nội dung..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="MaHocKy" class="form-select">
                        <option value="">-- Tất cả học kỳ --</option>
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold text-nowrap w-100 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-filter me-2"></i> Lọc
                    </button>
                    <a href="{{ route('truongkhoa.kehoach.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3" title="Đặt lại">
                        <i class="fa-solid fa-rotate"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══ DANH SÁCH KẾ HOẠCH KHÓA LUẬN ═══ -->
    @if($keHoachs->isEmpty())
        <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 12px;">
            <i class="fa-solid fa-calendar-xmark text-muted fa-3x mb-3"></i>
            <h6 class="text-muted">Chưa có kế hoạch khóa luận nào được công bố!</h6>
            <div class="small text-muted">Giáo vụ Khoa sẽ xây dựng và công bố kế hoạch cho các đợt khóa luận.</div>
        </div>
    @else
        <div class="row g-4">
            @foreach($keHoachs as $kh)
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <span class="badge bg-danger-subtle text-danger fw-bold me-2 px-2 py-1">
                                    {{ $kh->hocKy->TenHocKy ?? $kh->MaHocKy }}
                                </span>
                                <span class="fw-bold text-dark fs-6">{{ $kh->TenKeHoach }}</span>
                                <span class="text-muted small ms-2">(Mã KH: <strong>{{ $kh->MakeHoach }}</strong>)</span>
                            </div>
                            <div>
                                @if(in_array($kh->TrangThai, ['ĐÃ CÔNG BỐ', 'Đã công bố', 'Hoạt động']))
                                    <span class="badge bg-success px-3 py-2"><i class="fa-solid fa-circle-check me-2"></i> ĐÃ CÔNG BỐ</span>
                                @else
                                    <span class="badge bg-secondary px-3 py-2">{{ $kh->TrangThai ?? 'Dự thảo' }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-8">
                                    <div class="small text-muted fw-bold">Nội dung / Mô tả kế hoạch:</div>
                                    <div class="text-secondary small mt-1" style="white-space: pre-line;">{{ $kh->NoiDung ?? 'Chưa có mô tả chi tiết.' }}</div>
                                    @if($kh->FileDinhKem)
                                        <div class="mt-2">
                                            <a href="{{ asset('storage/' . $kh->FileDinhKem) }}" target="_blank" class="inline-flex align-items-center gap-1 badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill text-decoration-none px-3 py-2 shadow-xs">
                                                <i class="fa-solid fa-file-pdf me-2"></i> Văn bản thông báo chính thức đính kèm: {{ basename($kh->FileDinhKem) }} (Bấm để xem)
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-4 border-start-md">
                                    <div class="small text-muted">
                                        <div><i class="fa-solid fa-user-pen me-2 text-primary"></i> Người lập: <strong>{{ $kh->giaoVu->HoTen ?? ($kh->MaGVu ?? 'Quản trị viên Giáo vụ') }}</strong></div>
                                        <div class="mt-1"><i class="fa-regular fa-clock me-2 text-secondary"></i> Ngày lập: <strong>{{ $kh->NgayTao ? \Carbon\Carbon::parse($kh->NgayTao)->format('d/m/Y') : 'N/A' }}</strong></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mốc thời gian chi tiết của kế hoạch -->
                            @if($kh->mocThoiGians && $kh->mocThoiGians->isNotEmpty())
                                <div class="border-top pt-3 mt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                        <h6 class="fw-bold small text-dark mb-0 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-timeline text-danger me-1"></i>
                                            <span>Các Mốc Thời Gian Quy Trình (Chuẩn Thông Báo Khoa):</span>
                                        </h6>
                                        <span class="badge bg-light text-dark border rounded-pill px-3 py-1 fw-semibold">
                                            {{ $kh->mocThoiGians->count() }} Mốc quy trình
                                        </span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered align-middle mb-0" style="font-size: 0.8rem;">
                                            <thead class="table-light text-secondary text-uppercase fs-7 fw-bold">
                                                <tr>
                                                    <th style="width: 50px;" class="text-center">STT</th>
                                                    <th style="min-width: 250px;">Nội Dung Giai Đoạn / Mốc Quy Trình</th>
                                                    <th style="width: 140px;" class="text-center">Đối Tượng</th>
                                                    <th style="width: 210px;" class="text-center">Thời Gian Thực Hiện</th>
                                                    <th style="width: 135px;" class="text-center">Trạng Thái Mở</th>
                                                    <th>Ghi Chú / Yêu Cầu</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($kh->mocThoiGians->sortBy('NgayBatDau')->values() as $mIdx => $mItem)
                                                    @php
                                                        $statusInfo = \App\Services\PlanPhaseService::getPhaseStatus($mItem);
                                                    @endphp
                                                    <tr class="{{ $statusInfo['is_open'] ? 'table-success bg-opacity-25' : '' }}">
                                                        <td class="text-center fw-bold text-muted">{{ $loop->iteration }}</td>
                                                        <td>
                                                            <div class="fw-bold text-primary">{{ $mItem->TenMoc }}</div>
                                                            @if($mItem->LoaiGiaiDoan)
                                                                <span class="badge bg-light text-secondary border small mt-1">{{ $mItem->LoaiGiaiDoan }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1">
                                                                {{ $mItem->DoiTuongThucHien ?? 'Tất cả' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="fw-semibold text-dark">
                                                                <i class="fa-regular fa-calendar-days text-primary me-1"></i>
                                                                {{ $mItem->NgayBatDau ? \Carbon\Carbon::parse($mItem->NgayBatDau)->format('d/m/Y') : '-' }}
                                                                <i class="fa-solid fa-arrow-right text-muted mx-1"></i>
                                                                {{ $mItem->NgayKetThuc ? \Carbon\Carbon::parse($mItem->NgayKetThuc)->format('d/m/Y') : '-' }}
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge {{ $statusInfo['badge'] }} rounded-pill px-3 py-1">
                                                                {{ $statusInfo['label'] }}
                                                            </span>
                                                            @if($statusInfo['is_open'])
                                                                <div class="small text-success fw-bold mt-1">
                                                                    <i class="fa-solid fa-clock me-1"></i>Còn {{ $statusInfo['days_left'] }} ngày
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="text-muted small">
                                                            {{ preg_replace('/\[[A-Z0-9_]+\]/', '', $mItem->MoTa ?? '') ?: '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            <!-- Quy định đính kèm -->
                            @if($kh->quyDinhs && $kh->quyDinhs->isNotEmpty())
                                <div class="border-top pt-3 mt-3">
                                    <h6 class="fw-bold small text-dark mb-2">
                                        <i class="fa-solid fa-book-bookmark text-primary me-2"></i>
                                        Quy Định & Hướng Dẫn:
                                    </h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($kh->quyDinhs as $qd)
                                            <span class="badge bg-light text-dark border p-2">
                                                <i class="fa-solid fa-file-lines me-2 text-primary"></i>
                                                {{ $qd->TenQuyDinh ?? 'Quy định' }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($keHoachs->hasPages())
            <div class="mt-4">
                {{ $keHoachs->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
