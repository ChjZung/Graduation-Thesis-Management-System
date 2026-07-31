@extends('layouts.giangvien')
@section('page_title', 'Danh Sách Lớp Học Phần Phụ Trách')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-graduation-cap text-primary me-2"></i>Danh Sách Lớp Học Phần Phụ Trách</h4>
        <p class="text-muted small mb-0">Danh sách các Lớp Học Phần (Lớp Tín Chỉ) do Ban Quản trị phân công bạn phụ trách và hướng dẫn đồ án.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-12 mb-4">
        <div class="card card-premium">
            <div class="card-header-premium d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="fa-solid fa-list-check text-primary me-2"></i>Các Lớp Học Phần Được Phân Công</span>
                
                <form action="{{ route('giangvien.lop.index') }}" method="GET" class="d-flex align-items-center gap-2 m-0">
                    <label class="small text-muted fw-bold text-nowrap mb-0"><i class="fa-solid fa-filter me-1"></i>Lọc Học kỳ:</label>
                    <select name="ma_hoc_ky" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()" style="min-width: 220px;">
                        <option value="">-- Tất cả các Học Kỳ --</option>
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ request('ma_hoc_ky') == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                            </option>
                        @endforeach
                    </select>
                    @if(request('ma_hoc_ky'))
                        <a href="{{ route('giangvien.lop.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 text-nowrap" title="Bỏ lọc">
                            <i class="fa-solid fa-rotate-left me-1"></i>Xóa lọc
                        </a>
                    @endif
                    <span class="badge bg-primary rounded-pill px-3 py-2 ms-1">{{ count($lopHocPhans) }} Lớp HP</span>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Mã HP</th>
                                <th>Tên Lớp Học Phần</th>
                                <th>Môn Học</th>
                                <th>Học Kỳ</th>
                                <th class="text-center">Sĩ Số</th>
                                <th class="text-end px-4">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lopHocPhans as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-bold text-muted">{{ $item->MaLopHP }}</td>
                                    <td class="fw-bold">
                                        <a href="{{ route('giangvien.lop.show', $item->MaLopHP) }}" class="text-primary text-decoration-none">
                                            <i class="fa-solid fa-graduation-cap me-1"></i>{{ $item->TenLopHP }}
                                        </a>
                                    </td>
                                    <td>{{ $item->monHoc->TenMon ?? '—' }}</td>
                                    <td><span class="badge bg-info text-dark">{{ $item->hocKy->TenHocKy ?? '—' }} ({{ $item->hocKy->NamHoc ?? '' }})</span></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">{{ $item->sinhVienLopHocPhans->count() }} / {{ $item->SiSoToiDa ?? 40 }} SV</span></td>
                                    <td class="text-end px-4">
                                        <a href="{{ route('giangvien.lop.show', $item->MaLopHP) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="fa-solid fa-eye me-1"></i>Xem Chi Tiết & Nhóm
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                                        Bạn chưa được Ban Quản trị phân công phụ trách Lớp Học Phần nào.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
