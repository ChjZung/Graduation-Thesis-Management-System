@extends('layouts.truongkhoa')

@section('page_title', 'Kế Hoạch Khóa Luận')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-calendar-check me-2 text-primary"></i>
                Kế Hoạch & Tiến Độ Khóa Luận – {{ $khoa->TenKhoa ?? 'Khoa CNTT' }}
            </h4>
            <p class="text-muted mb-0 small">
                Theo dõi các kế hoạch khóa luận tốt nghiệp chính thức, lịch trình các mốc thời gian và quy định đào tạo của Khoa.
            </p>
        </div>
    </div>

    <!-- ═══ MỐC THỜI GIAN SẮP TỚI / ĐANG DIỄN RA ═══ -->
    @if(isset($upcomingMocs) && $upcomingMocs->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #003859 0%, #0072ce 100%); color: #fff;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                    <h6 class="fw-bold mb-0 text-white">
                        <i class="fa-solid fa-bell me-2 text-warning"></i>
                        Các Mốc Thời Gian Quy Trình Đang Diễn Ra / Sắp Tới
                    </h6>
                    <span class="badge bg-warning text-dark px-3 py-1">Timeline quan trọng</span>
                </div>
                <div class="row g-3">
                    @foreach($upcomingMocs as $moc)
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.2);">
                                <div class="fw-bold text-white small mb-1">{{ $moc->TenMoc }}</div>
                                <div class="small opacity-75 mb-2">
                                    <i class="fa-regular fa-calendar-days me-1"></i>
                                    {{ \Carbon\Carbon::parse($moc->NgayBatDau)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($moc->NgayKetThuc)->format('d/m/Y') }}
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-white text-primary" style="font-size: 0.65rem;">
                                        {{ $moc->keHoachKhoaLuan->hocKy->TenHocKy ?? 'Kế hoạch HK' }}
                                    </span>
                                    @php
                                        $now = now()->toDateString();
                                        $isNow = $moc->NgayBatDau <= $now && $now <= $moc->NgayKetThuc;
                                    @endphp
                                    @if($isNow)
                                        <span class="badge bg-danger text-white" style="font-size: 0.65rem;">Đang diễn ra</span>
                                    @else
                                        <span class="badge bg-info text-white" style="font-size: 0.65rem;">Sắp diễn ra</span>
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
                                {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold text-nowrap w-100">
                        <i class="fa-solid fa-filter me-1"></i> Lọc
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
                                    <span class="badge bg-success px-3 py-2"><i class="fa-solid fa-circle-check me-1"></i> ĐÃ CÔNG BỐ</span>
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
                                </div>
                                <div class="col-md-4 border-start-md">
                                    <div class="small text-muted">
                                        <div><i class="fa-solid fa-user-pen me-1 text-primary"></i> Người lập: <strong>{{ $kh->giaoVu->HoTen ?? ($kh->MaGVu ?? 'Giáo vụ Khoa') }}</strong></div>
                                        <div class="mt-1"><i class="fa-regular fa-clock me-1 text-secondary"></i> Ngày lập: <strong>{{ $kh->NgayTao ? \Carbon\Carbon::parse($kh->NgayTao)->format('d/m/Y') : 'N/A' }}</strong></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mốc thời gian chi tiết của kế hoạch -->
                            @if($kh->mocThoiGians && $kh->mocThoiGians->isNotEmpty())
                                <div class="border-top pt-3 mt-3">
                                    <h6 class="fw-bold small text-dark mb-2">
                                        <i class="fa-solid fa-timeline text-danger me-1"></i>
                                        Các Mốc Thời Gian Quy Trình:
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.8rem;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 50px;" class="text-center">STT</th>
                                                    <th>Nội Dung Giai Đoạn / Mốc Quy Trình</th>
                                                    <th style="width: 140px;" class="text-center">Ngày Bắt Đầu</th>
                                                    <th style="width: 140px;" class="text-center">Ngày Kết Thúc</th>
                                                    <th>Ghi Chú / Yêu Cầu</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($kh->mocThoiGians->sortBy('NgayBatDau') as $mIdx => $mItem)
                                                    <tr>
                                                        <td class="text-center fw-bold">{{ $mIdx + 1 }}</td>
                                                        <td class="fw-semibold text-dark">{{ $mItem->TenMoc }}</td>
                                                        <td class="text-center text-primary fw-semibold">
                                                            {{ $mItem->NgayBatDau ? \Carbon\Carbon::parse($mItem->NgayBatDau)->format('d/m/Y') : '-' }}
                                                        </td>
                                                        <td class="text-center text-danger fw-semibold">
                                                            {{ $mItem->NgayKetThuc ? \Carbon\Carbon::parse($mItem->NgayKetThuc)->format('d/m/Y') : '-' }}
                                                        </td>
                                                        <td class="text-muted small">{{ $mItem->MoTa ?? '-' }}</td>
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
                                        <i class="fa-solid fa-book-bookmark text-primary me-1"></i>
                                        Quy Định & Hướng Dẫn:
                                    </h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($kh->quyDinhs as $qd)
                                            <span class="badge bg-light text-dark border p-2">
                                                <i class="fa-solid fa-file-lines me-1 text-primary"></i>
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
