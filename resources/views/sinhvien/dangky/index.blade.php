@extends('layouts.sinhvien')
@section('page_title', 'Đăng Ký Đề Tài')
@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($dangky)
<div class="alert alert-{{ $dangky->TrangThai == 'Đã duyệt' ? 'success' : ($dangky->TrangThai == 'Chờ duyệt' ? 'warning' : 'danger') }} border-0 shadow-sm p-4 rounded-4 mb-4">
    <h5 class="fw-bold"><i class="fa-solid fa-circle-info me-2"></i> Trạng thái đăng ký hiện tại: <span class="text-uppercase">{{ $dangky->TrangThai }}</span></h5>
    <hr>
    <p class="mb-1"><strong>Đề tài:</strong> {{ $dangky->deTai->TenDeTai ?? '' }}</p>
    <p class="mb-1"><strong>Giảng viên:</strong> {{ $dangky->deTai->giangVien->HoTen ?? 'Chưa xác định' }}</p>
    <p class="mb-1"><strong>Ngày đăng ký:</strong> {{ date('d/m/Y', strtotime($dangky->NgayDangKy)) }}</p>
    @if($dangky->TrangThai == 'Từ chối' && $dangky->LyDoTuChoi)
        <p class="mb-0 text-danger mt-2"><strong>Lý do từ chối:</strong> <i>"{{ $dangky->LyDoTuChoi }}"</i></p>
    @endif
</div>
@endif

<div class="card card-premium">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-list text-primary me-2"></i>Danh Sách Đề Tài Cho Lớp {{ $sinhVien->lop->TenLop ?? '' }}</span>
        <span class="badge bg-info text-dark">Chỉ hiện đề tài lớp mình</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4">Mã ĐT</th>
                    <th width="35%">Tên Đề Tài</th>
                    <th>Giảng Viên Phụ Trách</th>
                    <th>Hạn Đăng Ký</th>
                    <th class="text-center">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detais as $dt)
                @php
                    $isExpired = $dt->HanDangKy && date('Y-m-d') > $dt->HanDangKy;
                @endphp
                <tr>
                    <td class="px-4 fw-bold text-muted">{{ $dt->MaDeTai }}</td>
                    <td class="fw-bold text-primary">{{ $dt->TenDeTai }}</td>
                    <td>{{ $dt->giangVien->HoTen ?? 'Chưa xác định' }}</td>
                    <td>
                        @if($dt->HanDangKy)
                            <span class="badge {{ $isExpired ? 'bg-danger' : 'bg-success' }}">
                                <i class="fa-regular fa-clock me-1"></i>{{ date('d/m/Y', strtotime($dt->HanDangKy)) }}
                            </span>
                        @else
                            <span class="text-muted small">Không giới hạn</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($isExpired)
                            <span class="badge bg-secondary">Hết hạn đăng ký</span>
                        @elseif((!$dangky || $dangky->TrangThai == 'Từ chối') && $nhom && $nhom->TruongNhom == $sinhVien->MaSV)
                            <form action="{{ route('sinhvien.dangky.store') }}" method="POST" class="form-dangky">
                                @csrf
                                <input type="hidden" name="MaDeTai" value="{{ $dt->MaDeTai }}">
                                <button type="button" class="btn btn-sm btn-primary-custom rounded-pill px-3 btn-dangky">Đăng Ký</button>
                            </form>
                        @elseif(!$nhom)
                            <span class="text-muted small">Hãy tạo nhóm trước</span>
                        @elseif($nhom->TruongNhom != $sinhVien->MaSV)
                            <span class="text-muted small">Chỉ trưởng nhóm</span>
                        @else
                            @if($dangky && $dangky->MaDeTai == $dt->MaDeTai)
                                <button class="btn btn-sm btn-success rounded-pill px-3" disabled><i class="fa-solid fa-check me-1"></i>Đã chọn</button>
                            @else
                                <button class="btn btn-sm btn-secondary rounded-pill px-3 opacity-50" disabled><i class="fa-solid fa-lock me-1"></i>Đã khóa</button>
                            @endif
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Hiện không có đề tài nào mở đăng ký cho lớp của bạn.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">
    {{ $detais->links('pagination::bootstrap-5') }}
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-dangky').forEach(button => {
            button.addEventListener('click', function() {
                let form = this.closest('form');
                Swal.fire({
                    title: 'Xác nhận đăng ký?',
                    text: "Bạn đại diện cho nhóm đăng ký đề tài này?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3699ff',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Đồng ý Đăng Ký',
                    cancelButtonText: 'Hủy',
                    background: '#fff',
                    borderRadius: '1rem',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                })
            });
        });
    });
</script>
@endsection