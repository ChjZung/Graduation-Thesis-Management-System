@extends('layouts.sinhvien')

@section('page_title', 'Đăng Ký Đề Tài Khóa Luận')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- TÌNH TRẠNG ĐĂNG KÝ CỦA NHÓM -->
@if(isset($dangKyCurrent) && $dangKyCurrent)
<div class="card card-premium mb-4 border-start border-4 border-primary">
    <div class="card-body p-4 d-flex justify-content-between align-items-center">
        <div>
            <div class="small text-muted text-uppercase fw-bold mb-1">Đơn Đăng Ký Hiện Tại Của Nhóm</div>
            <h5 class="fw-bold text-primary mb-1">{{ $dangKyCurrent->deTai->TenDeTai ?? '' }}</h5>
            <div class="small text-secondary">
                <i class="fa-solid fa-chalkboard-user me-1"></i>Giảng viên đề xuất: <strong>{{ $dangKyCurrent->deTai->giangVien->HoTen ?? '' }}</strong>
            </div>
        </div>
        <div class="text-end">
            @if($dangKyCurrent->TrangThai === 'Đã duyệt')
                <span class="badge bg-success rounded-pill fs-6 px-3 py-2"><i class="fa-solid fa-check-circle me-1"></i>Đã Duyệt Chính Thức</span>
            @elseif($dangKyCurrent->TrangThai === 'Từ chối')
                <span class="badge bg-danger rounded-pill fs-6 px-3 py-2">Đã Từ Chối</span>
            @else
                <span class="badge bg-warning text-dark rounded-pill fs-6 px-3 py-2"><i class="fa-solid fa-clock me-1"></i>Đang Chờ Giáo Vụ Duyệt</span>
                @if($nhom->MaTruongNhom === $sinhVien->MaSV)
                <form action="{{ route('sinhvien.dangky.destroy', $dangKyCurrent->MaDangKy) }}" method="POST" class="mt-2" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn đăng ký đề tài này?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Hủy Đơn Đăng Ký</button>
                </form>
                @endif
            @endif
        </div>
    </div>
</div>
@endif

<div class="card card-premium">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-clipboard-list text-primary me-2"></i>Danh Sách Đề Tài Đã Công Bố Đăng Ký</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th width="12%">Mã Đề Tài</th>
                        <th width="35%">Tên Đề Tài Khóa Luận</th>
                        <th width="20%">Giảng Viên Đề Xuất</th>
                        <th width="15%" class="text-center">Số SV Tối Đa</th>
                        <th width="18%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detais as $dt)
                    <tr>
                        <td><span class="badge bg-light text-dark fw-bold border">{{ $dt->MaDeTai }}</span></td>
                        <td>
                            <div class="fw-bold text-primary-custom">{{ $dt->TenDeTai }}</div>
                            <div class="small text-muted">{{ Str::limit($dt->MoTa, 90) }}</div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $dt->giangVien->HoTen ?? 'Chưa rõ' }}</div>
                            <div class="small text-muted">{{ $dt->giangVien->HocVi ?? '' }}</div>
                        </td>
                        <td class="text-center fw-bold">{{ $dt->SoLuongSinhVienToiDa }} SV</td>
                        <td class="text-center">
                            @if(isset($nhom) && $nhom && $nhom->MaTruongNhom === $sinhVien->MaSV)
                                @if(isset($dangKyCurrent) && $dangKyCurrent && $dangKyCurrent->MaDeTai === $dt->MaDeTai)
                                    <span class="badge bg-success rounded-pill px-3">Đã Đăng Ký</span>
                                @else
                                    <form action="{{ route('sinhvien.dangky.store') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="MaDeTai" value="{{ $dt->MaDeTai }}">
                                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" onclick="return confirm('Bạn có chắc chắn muốn đại diện nhóm đăng ký đề tài này?');">
                                            <i class="fa-solid fa-pen-to-square me-1"></i>Đăng Ký
                                        </button>
                                    </form>
                                @endif
                            @elseif(isset($nhom) && $nhom)
                                <span class="text-muted small">Chỉ Trưởng nhóm mới có quyền đăng ký</span>
                            @else
                                <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3">Cần tạo nhóm trước</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-clipboard-question fs-1 text-light mb-3 d-block"></i>
                            Hiện tại chưa có đề tài nào được công bố để đăng ký.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($detais->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $detais->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection