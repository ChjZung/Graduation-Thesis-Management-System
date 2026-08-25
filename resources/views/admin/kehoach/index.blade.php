@extends('layouts.admin')

@section('page_title', 'Quản Lý Kế Hoạch Khóa Luận')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-premium mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.kehoach.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm theo tên hoặc mã kế hoạch..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="TrangThai" class="form-select form-select-sm">
                    <option value="">-- Tất cả Trạng thái --</option>
                    <option value="NHÁP" {{ request('TrangThai') == 'NHÁP' ? 'selected' : '' }}>NHÁP</option>
                    <option value="CHỜ DUYỆT" {{ request('TrangThai') == 'CHỜ DUYỆT' ? 'selected' : '' }}>CHỜ DUYỆT</option>
                    <option value="ĐÃ DUYỆT" {{ request('TrangThai') == 'ĐÃ DUYỆT' ? 'selected' : '' }}>ĐÃ DUYỆT</option>
                    <option value="ĐÃ CÔNG BỐ" {{ request('TrangThai') == 'ĐÃ CÔNG BỐ' ? 'selected' : '' }}>ĐÃ CÔNG BỐ</option>
                    <option value="ĐANG THỰC HIỆN" {{ request('TrangThai') == 'ĐANG THỰC HIỆN' ? 'selected' : '' }}>ĐANG THỰC HIỆN</option>
                    <option value="HOÀN THÀNH" {{ request('TrangThai') == 'HOÀN THÀNH' ? 'selected' : '' }}>HOÀN THÀNH</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="MaHocKy" class="form-select form-select-sm">
                    <option value="">-- Tất cả Học kỳ --</option>
                    @foreach($hocKies as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>{{ $hk->TenHocKy }} ({{ $hk->NamHoc }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 w-100"><i class="fa-solid fa-filter me-1"></i>Lọc</button>
                <a href="{{ route('admin.kehoach.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2" title="Xóa lọc"><i class="fa-solid fa-rotate"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card card-premium">
    <div class="card-header-premium">
        <span><i class="fa-solid fa-calendar-check me-2"></i> Danh Sách Kế Hoạch Khóa Luận Tốt Nghiệp</span>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.kehoach.importDocument') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-file-pdf me-1"></i> Upload Thông Báo Kế Hoạch (PDF/Word)
            </a>
            <a href="{{ route('admin.kehoach.create') }}" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Lập Kế Hoạch Mới
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="12%">Mã KH</th>
                        <th width="28%">Tên Kế Hoạch / Học Kỳ</th>
                        <th width="18%">Thời Gian Thực Hiện</th>
                        <th width="12%">Mốc Thời Gian</th>
                        <th width="15%">Trạng Thái</th>
                        <th width="15%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($keHoachs as $kh)
                    @php
                        $badgeClass = match($kh->TrangThai) {
                            'NHÁP'          => 'bg-secondary',
                            'CHỜ DUYỆT'     => 'bg-warning text-dark',
                            'ĐÃ DUYỆT'      => 'bg-info text-white',
                            'ĐÃ CÔNG BỐ'    => 'bg-primary',
                            'ĐANG THỰC HIỆN'=> 'bg-success',
                            'HOÀN THÀNH'     => 'bg-dark',
                            default         => 'bg-secondary',
                        };
                    @endphp
                    <tr>
                        <td><span class="badge-code">{{ $kh->MaKeHoach }}</span></td>
                        <td>
                            <div class="fw-bold text-primary fs-6">{{ $kh->TenKeHoach }}</div>
                            <div class="small text-muted"><i class="fa-solid fa-graduation-cap me-1"></i>{{ $kh->hocKy->TenHocKy ?? $kh->MaHocKy }} ({{ $kh->NamHoc ?? '2026-2027' }})</div>
                        </td>
                        <td>
                            <div class="small fw-semibold text-dark"><i class="fa-regular fa-calendar me-1"></i>{{ $kh->NgayBatDau ? date('d/m/Y', strtotime($kh->NgayBatDau)) : '---' }}</div>
                            <div class="small text-muted"><i class="fa-solid fa-arrow-right me-1"></i>{{ $kh->NgayKetThuc ? date('d/m/Y', strtotime($kh->NgayKetThuc)) : '---' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border rounded-pill px-3">{{ $kh->mocThoiGians->count() }} mốc</span>
                        </td>
                        <td>
                            <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2">{{ $kh->TrangThai }}</span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('admin.kehoach.show', $kh->MaKeHoach) }}" class="btn btn-sm btn-light text-info btn-action-circle" title="Xem Chi Tiết & Timeline 12 Mốc">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @if(!in_array($kh->TrangThai, ['ĐÃ CÔNG BỐ', 'ĐANG THỰC HIỆN', 'HOÀN THÀNH']))
                                <a href="{{ route('admin.kehoach.edit', $kh->MaKeHoach) }}" class="btn btn-sm btn-light text-primary btn-action-circle" title="Chỉnh sửa">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form action="{{ route('admin.kehoach.destroy', $kh->MaKeHoach) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa kế hoạch này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light text-danger btn-action-circle" title="Xóa">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state-box">
                                <i class="fa-solid fa-calendar-check"></i>
                                <h6>Chưa có Kế hoạch Khóa luận nào</h6>
                                <p class="small text-muted mb-3">Vui lòng lập kế hoạch mới hoặc upload từ văn bản thông báo.</p>
                                <a href="{{ route('admin.kehoach.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">Lập Kế Hoạch Mới</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($keHoachs->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $keHoachs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
