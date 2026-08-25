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

<!-- CẢNH BÁO NẾU NHÓM CHƯA ĐỦ 3 THÀNH VIÊN -->
@if(isset($nhom) && $nhom && $soThanhVien < 3)
<div class="alert alert-warning border-warning shadow-sm mb-4 d-flex justify-content-between align-items-center">
    <div>
        <i class="fa-solid fa-triangle-exclamation fs-5 me-2 text-warning"></i>
        <strong>Quy định bắt buộc:</strong> Nhóm của bạn hiện có <strong>{{ $soThanhVien }}/3 thành viên chính thức</strong>. Cần tuyển đủ <strong>3 thành viên</strong> để mở khóa chức năng Đăng ký Đề tài!
    </div>
    <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold text-dark text-nowrap">
        <i class="fa-solid fa-user-plus me-1"></i>Mời Thêm Thành Viên
    </a>
</div>
@endif

<!-- TÌNH TRẠNG ĐĂNG KÝ CỦA NHÓM -->
@if(isset($dangKyCurrent) && $dangKyCurrent)
<div class="card card-premium mb-4 border-start border-4 {{ $dangKyCurrent->TrangThai === 'Đã duyệt' ? 'border-success' : ($dangKyCurrent->TrangThai === 'Từ chối' ? 'border-danger' : 'border-warning') }}">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-2">
            <div>
                <div class="small text-muted text-uppercase fw-bold mb-1">Đơn Đăng Ký Hiện Tại Của Nhóm</div>
                <h5 class="fw-bold text-primary mb-1">{{ $dangKyCurrent->deTai->TenDeTai ?? 'Chưa rõ đề tài' }}</h5>
                <div class="small text-secondary">
                    <i class="fa-solid fa-chalkboard-user me-1"></i>Giảng viên hướng dẫn: <strong>{{ $dangKyCurrent->deTai->giangVien->HoTen ?? 'Chưa rõ' }}</strong>
                </div>
            </div>
            <div class="text-end">
                @if($dangKyCurrent->TrangThai === 'Đã duyệt')
                    <span class="badge bg-success rounded-pill fs-6 px-3 py-2"><i class="fa-solid fa-check-circle me-1"></i>Đã Duyệt Chính Thức</span>
                @elseif($dangKyCurrent->TrangThai === 'Từ chối')
                    <span class="badge bg-danger rounded-pill fs-6 px-3 py-2"><i class="fa-solid fa-circle-xmark me-1"></i>Đã Từ Chối</span>
                @else
                    <span class="badge bg-warning text-dark rounded-pill fs-6 px-3 py-2"><i class="fa-solid fa-clock me-1"></i>Đang Chờ Giáo Vụ Duyệt</span>
                @endif
            </div>
        </div>

        @if($dangKyCurrent->TrangThai === 'Từ chối')
            <div class="alert alert-danger bg-danger-subtle border-0 rounded-3 p-3 mt-3 mb-0">
                <div class="fw-bold text-danger mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i>Lý do từ chối:</div>
                <div class="text-dark small">{{ $dangKyCurrent->LyDoTuChoi ?? 'Đề tài không phù hợp hoặc đã được phân công cho nhóm khác.' }}</div>
                <div class="small text-danger fw-semibold mt-2">
                    <i class="fa-solid fa-arrow-down me-1"></i>Nhóm của bạn có thể lựa chọn và đăng ký một đề tài khác trong danh sách bên dưới.
                </div>
            </div>
        @elseif($dangKyCurrent->TrangThai === 'Chờ duyệt')
            @if(isset($nhom) && $nhom && $nhom->MaTruongNhom === $sinhVien->MaSV)
            <div class="mt-3 pt-2 border-top">
                <form action="{{ route('sinhvien.dangky.destroy', $dangKyCurrent->MaDangKy) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn đăng ký đề tài này để chọn đề tài khác?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                        <i class="fa-solid fa-trash-can me-1"></i>Hủy Đơn Đăng Ký Đang Chờ
                    </button>
                </form>
            </div>
            @endif
        @endif
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
                        <th width="18%" class="text-center">Trạng Thái / Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detais as $dt)
                    @php
                        // Kiểm tra xem nhóm mình có đăng ký đề tài này không
                        $myGroupRegThis = (isset($dangKyCurrent) && $dangKyCurrent && $dangKyCurrent->MaDeTai === $dt->MaDeTai) ? $dangKyCurrent : null;

                        // Đề tài đã có nhóm khác đăng ký (Chờ duyệt hoặc Đã duyệt)
                        $isTakenByOther = isset($deTaiDaDangKys[$dt->MaDeTai]) && (!isset($nhom) || $deTaiDaDangKys[$dt->MaDeTai]->MaNhom !== $nhom->MaNhom);

                        // Nhóm đang có 1 đơn ACTIVE (Chờ duyệt hoặc Đã duyệt)
                        $hasActiveRegistration = isset($dangKyCurrent) && $dangKyCurrent && in_array($dangKyCurrent->TrangThai, ['Chờ duyệt', 'Đã duyệt']);
                    @endphp
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
                            @if($myGroupRegThis)
                                @if($myGroupRegThis->TrangThai === 'Đã duyệt')
                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                        <i class="fa-solid fa-check-circle me-1"></i>Đã Duyệt Chính Thức
                                    </span>
                                @elseif($myGroupRegThis->TrangThai === 'Từ chối')
                                    <span class="badge bg-danger rounded-pill px-3 py-2" title="{{ $myGroupRegThis->LyDoTuChoi }}">
                                        <i class="fa-solid fa-circle-xmark me-1"></i>Đã Bị Từ Chối
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                        <i class="fa-solid fa-clock me-1"></i>Đang Chờ Duyệt
                                    </span>
                                @endif
                            @elseif($isTakenByOther)
                                <span class="badge bg-secondary rounded-pill px-3 py-2" title="Đề tài này đã có nhóm khác đăng ký">
                                    <i class="fa-solid fa-lock me-1"></i>Đã Có Nhóm Đăng Ký
                                </span>
                            @elseif(isset($nhom) && $nhom)
                                @if($nhom->MaTruongNhom !== $sinhVien->MaSV)
                                    <span class="text-muted small">Chỉ Trưởng nhóm mới có quyền đăng ký</span>
                                @elseif($soThanhVien < 3)
                                    <button class="btn btn-sm btn-secondary rounded-pill px-3" disabled title="Nhóm cần đủ 3 thành viên để mở khóa đăng ký">
                                        <i class="fa-solid fa-lock me-1"></i>Cần 3 Thành Viên
                                    </button>
                                @elseif($hasActiveRegistration)
                                    <button class="btn btn-sm btn-secondary rounded-pill px-3" disabled title="Nhóm đang có đơn đăng ký đề tài khác chưa xử lý">
                                        <i class="fa-solid fa-lock me-1"></i>Đã Đăng Ký Đề Tài Khác
                                    </button>
                                @else
                                    <form action="{{ route('sinhvien.dangky.store') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="MaDeTai" value="{{ $dt->MaDeTai }}">
                                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-sm" onclick="return confirm('Bạn có chắc chắn muốn đại diện nhóm đăng ký đề tài {{ $dt->TenDeTai }}?');">
                                            <i class="fa-solid fa-pen-to-square me-1"></i>Đăng Ký
                                        </button>
                                    </form>
                                @endif
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