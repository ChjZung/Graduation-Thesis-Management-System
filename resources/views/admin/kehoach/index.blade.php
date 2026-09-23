@extends('layouts.admin')

@section('page_title', 'Quản lý kế hoạch')

@section('content')

{{-- ── THANH TÌM KIẾM & BỘ LỌC DỮ LIỆU ── --}}
<div class="admin-filter-bar">
    <form method="GET" action="{{ route('admin.kehoach.index') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" class="form-control form-control-sm border-start-0" placeholder="Tìm theo tên hoặc mã kế hoạch..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="TrangThai" class="form-select form-select-sm">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="ĐÃ CÔNG BỐ" {{ request('TrangThai') == 'ĐÃ CÔNG BỐ' ? 'selected' : '' }}>Đã công bố</option>
                <option value="ĐANG THỰC HIỆN" {{ request('TrangThai') == 'ĐANG THỰC HIỆN' ? 'selected' : '' }}>Đang diễn ra</option>
                <option value="NHÁP" {{ request('TrangThai') == 'NHÁP' ? 'selected' : '' }}>Bản nháp</option>
                <option value="CHỜ DUYỆT" {{ request('TrangThai') == 'CHỜ DUYỆT' ? 'selected' : '' }}>Chờ duyệt</option>
                <option value="HOÀN THÀNH" {{ request('TrangThai') == 'HOÀN THÀNH' ? 'selected' : '' }}>Hoàn thành</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="MaHocKy" class="form-select form-select-sm">
                <option value="">-- Tất cả học kỳ --</option>
                @foreach($hocKies as $hk)
                    <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                        {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 flex-grow-1 fw-semibold" style="background: #0072ce; border-color: #0072ce;">
                <i class="fa-solid fa-bolt me-1"></i> Lọc Dữ Liệu
            </button>
            <a href="{{ route('admin.kehoach.index') }}" class="btn btn-sm btn-light border rounded-3 px-2 text-secondary" title="Tải lại">
                <i class="fa-solid fa-rotate-right"></i>
            </a>
        </div>
    </form>
</div>

{{-- ── BẢNG DANH SÁCH KẾ HOẠCH ── --}}
<div class="admin-table-card">
    <div class="admin-table-header">
        <h5 class="admin-table-header-title">
            <i class="fa-regular fa-file-lines text-primary"></i>
            Danh Sách Kế Hoạch Khóa Luận Tốt Nghiệp
        </h5>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.kehoach.importDocument') }}" class="btn btn-sm text-white rounded-3 px-3 fw-semibold shadow-sm" style="background: #0072ce;">
                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload Thông Báo Kế Hoạch (PDF)
            </a>
            <a href="{{ route('admin.kehoach.create') }}" class="btn btn-sm btn-success rounded-3 px-3 fw-semibold shadow-sm" style="background: #198754;">
                <i class="fa-solid fa-plus me-1"></i> Lập Kế Hoạch Mới
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width: 100px;">Mã KH</th>
                    <th style="min-width: 320px;">Tên Kế Hoạch / Học Kỳ Áp Dụng</th>
                    <th style="width: 220px;">Thời Gian Thực Hiện</th>
                    <th class="text-center" style="width: 130px;">Mốc Quy Trình</th>
                    <th class="text-center" style="width: 140px;">Trạng Thái</th>
                    <th class="text-center" style="width: 140px;">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($keHoachs as $kh)
                @php
                    $durationWeeks = 16;
                    if ($kh->NgayBatDau && $kh->NgayKetThuc) {
                        $start = \Carbon\Carbon::parse($kh->NgayBatDau);
                        $end = \Carbon\Carbon::parse($kh->NgayKetThuc);
                        $durationWeeks = max(1, $start->diffInWeeks($end));
                    }
                @endphp
                <tr>
                    {{-- Mã KH --}}
                    <td>
                        <span class="badge-code">{{ $kh->MaKeHoach }}</span>
                    </td>

                    {{-- Tên Kế hoạch & Học kỳ --}}
                    <td>
                        <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                            {{ $kh->TenKeHoach }}
                        </div>
                        <div class="text-primary mt-1" style="font-size: 0.78rem; font-weight: 600;">
                            <i class="fa-solid fa-graduation-cap me-1"></i>
                            {{ $kh->hocKy->TenHocKy ?? $kh->MaHocKy }} ({{ $kh->NamHoc ?? '2026–2027' }})
                            • Khoa Công nghệ Thông tin
                        </div>
                        <div class="text-muted mt-1" style="font-size: 0.74rem;">
                            @if(in_array($kh->TrangThai, ['NHÁP', 'CHỜ DUYỆT']))
                                <span class="text-warning fw-semibold">Đang chờ Ban Chủ nhiệm Khoa phê duyệt chỉ tiêu đề tài</span>
                            @else
                                <span>Đối tượng: Sinh viên năm cuối đạt chuẩn tích lũy tín chỉ</span>
                            @endif
                        </div>
                    </td>

                    {{-- Thời gian --}}
                    <td>
                        <div class="fw-semibold text-dark" style="font-size: 0.84rem;">
                            {{ $kh->NgayBatDau ? \Carbon\Carbon::parse($kh->NgayBatDau)->format('d/m/Y') : '01/09/2026' }}
                            <span class="text-muted mx-1">→</span>
                            {{ $kh->NgayKetThuc ? \Carbon\Carbon::parse($kh->NgayKetThuc)->format('d/m/Y') : '15/01/2027' }}
                        </div>
                        <div class="text-muted mt-1" style="font-size: 0.76rem;">
                            <i class="fa-regular fa-clock me-1 text-primary"></i> Thời lượng: {{ $durationWeeks }} tuần
                        </div>
                    </td>

                    {{-- Mốc quy trình --}}
                    <td class="text-center">
                        <div class="fw-bold text-dark" style="font-size: 0.85rem;">
                            {{ $kh->mocThoiGians->count() ?: 5 }} mốc
                        </div>
                        <a href="{{ route('admin.kehoach.show', $kh->MaKeHoach) }}" class="text-decoration-none text-primary fw-semibold" style="font-size: 0.75rem;">
                            Xem chi tiết <i class="fa-solid fa-caret-down"></i>
                        </a>
                    </td>

                    {{-- Trạng thái --}}
                    <td class="text-center">
                        @if($kh->TrangThai === 'ĐÃ CÔNG BỐ')
                            <span class="badge-status-green">Đã công bố</span>
                        @elseif($kh->TrangThai === 'ĐANG THỰC HIỆN')
                            <span class="badge-status-blue">Đang diễn ra</span>
                        @elseif($kh->TrangThai === 'NHÁP')
                            <span class="badge-status-amber">Bản nháp</span>
                        @elseif($kh->TrangThai === 'CHỜ DUYỆT')
                            <span class="badge-status-amber">Chờ duyệt</span>
                        @else
                            <span class="badge-status-green">{{ $kh->TrangThai }}</span>
                        @endif
                    </td>

                    {{-- Thao tác --}}
                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ route('admin.kehoach.show', $kh->MaKeHoach) }}" class="btn-action-icon btn-action-view" title="Xem chi tiết">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.kehoach.edit', $kh->MaKeHoach) }}" class="btn-action-icon btn-action-edit" title="Chỉnh sửa">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('admin.kehoach.destroy', $kh->MaKeHoach) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa kế hoạch này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action-icon btn-action-delete" title="Xóa">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="fa-regular fa-calendar-xmark fa-2x mb-2 d-block opacity-50"></i>
                        Chưa có kế hoạch khóa luận nào phù hợp với bộ lọc.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Phân trang & Đếm dòng --}}
    <div class="px-3 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #ffffff;">
        <div class="text-muted" style="font-size: 0.82rem;">
            Hiển thị <strong>{{ $keHoachs->firstItem() ?? 1 }}–{{ $keHoachs->lastItem() ?? $keHoachs->count() }}</strong> trên tổng số <strong>{{ $keHoachs->total() }}</strong> kế hoạch khóa luận
        </div>
        <div>
            {{ $keHoachs->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection
