@extends('layouts.sinhvien')

@section('page_title', 'Đăng Ký Đề Tài Khóa Luận')

@section('content')


<!-- THÔNG TIN NHÓM SINH VIÊN -->
@if(isset($nhom) && $nhom)
<div class="alert alert-info border-info shadow-sm mb-4 d-flex justify-content-between align-items-center">
    <div>
        <i class="fa-solid fa-users fs-5 me-2 text-info"></i>
        <strong>Nhóm của bạn:</strong> <strong>{{ $nhom->TenNhom }}</strong> ({{ $soThanhVien }}/3 thành viên). Nhóm có từ 1 đến 3 thành viên được phép đăng ký đề tài.
    </div>
    @if($soThanhVien < 3)
    <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-user-plus me-1"></i>Mời thêm thành viên (Tối đa 3)
    </a>
    @endif
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
                @if($dangKyCurrent->TrangThai === 'Từ chối')
                    <span class="badge bg-danger rounded-pill fs-6 px-3 py-2"><i class="fa-solid fa-circle-xmark me-1"></i>Đã Từ Chối</span>
                @else
                    <span class="badge bg-success rounded-pill fs-6 px-3 py-2"><i class="fa-solid fa-check-circle me-1"></i>Đã Gán Chính Thức Cho Nhóm</span>
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
        @else
            @if(isset($nhom) && $nhom && $nhom->MaTruongNhom === $sinhVien->MaSV)
            <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                <span class="small text-success fw-medium"><i class="fa-solid fa-circle-check me-1"></i>Đề tài đã được gán trực tiếp cho nhóm của bạn.</span>
                <form action="{{ route('sinhvien.dangky.destroy', $dangKyCurrent->MaDangKy) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đăng ký đề tài này để chọn đề tài khác?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                        <i class="fa-solid fa-rotate-left me-1"></i>Hủy Đăng Ký Đề Tài Này
                    </button>
                </form>
            </div>
            @endif
        @endif
    </div>
</div>
@endif

<!-- BƯỚC 1: CHỌN MÔN / HỌC PHẦN -->
<div class="card border-0 shadow-sm rounded-4 mb-3 p-3 bg-light">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <span class="fw-bold text-dark"><i class="fa-solid fa-book-open text-primary me-2"></i>Bước 1: Chọn Môn / Học Phần:</span>
            <div class="small text-muted">Lọc danh sách đề tài chính thức theo học phần bạn đang học</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('sinhvien.dangky.index') }}" 
               class="btn btn-sm {{ !$selectedHocPhan ? 'btn-primary text-white' : 'btn-outline-secondary bg-white' }} rounded-pill px-3 fw-semibold">
                Tất Cả Học Phần
            </a>
            @foreach($hocPhans as $hp)
            @php
                $hpCode = is_object($hp) ? $hp->MaHocPhan : $hp;
                $hpName = is_object($hp) ? $hp->TenHocPhan : $hp;
                $isActive = ($selectedHocPhan === $hpCode || $selectedHocPhan === $hpName);
            @endphp
            <a href="{{ route('sinhvien.dangky.index', ['HocPhan' => $hpCode]) }}" 
               class="btn btn-sm {{ $isActive ? 'btn-primary text-white' : 'btn-outline-secondary bg-white' }} rounded-pill px-3 fw-semibold">
                {{ $hpName }}
            </a>
            @endforeach
        </div>
    </div>
</div>

<div class="card card-premium">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-clipboard-list text-primary me-2"></i>Bước 2: Danh Sách Đề Tài Đã Công Bố ({{ $detais->total() }} đề tài)</span>
        @if($selectedHocPhan)
            <span class="badge bg-primary rounded-pill px-3 py-1">{{ $selectedHocPhan }}</span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th width="12%">Mã Đề Tài</th>
                        <th width="38%">Tên Đề Tài & Học Phần</th>
                        <th width="20%">Giảng Viên Đề Xuất</th>
                        <th width="13%" class="text-center">Số SV Tối Đa</th>
                        <th width="17%" class="text-center">Trạng Thái / Thao Tác</th>
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
                            <div class="small text-muted mb-1">{{ Str::limit($dt->MoTa, 90) }}</div>
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.72rem;">
                                    {{ $dt->hocPhanRef->TenHocPhan ?? $dt->HocPhan ?? 'Khóa luận cử nhân' }}
                                </span>
                                @if($dt->LinhVuc)
                                    <span class="badge bg-light text-secondary border" style="font-size: 0.72rem;">{{ $dt->LinhVuc }}</span>
                                @endif
                                @if($dt->nganh)
                                    <span class="badge bg-light text-secondary border" style="font-size: 0.72rem;">{{ $dt->nganh->TenNganh }}</span>
                                @endif
                                @if($dt->FileDeCuong)
                                    <a href="{{ asset($dt->FileDeCuong) }}" target="_blank" class="badge bg-success-subtle text-success border text-decoration-none" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-file-lines me-1"></i> Xem đề cương
                                    </a>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $dt->giangVien->HoTen ?? 'Chưa rõ' }}</div>
                            <div class="small text-muted">{{ $dt->giangVien->HocVi ?? '' }}</div>
                        </td>
                        <td class="text-center fw-bold">{{ $dt->SoLuongSinhVienToiDa }} SV</td>
                        <td class="text-center">
                            @if($myGroupRegThis)
                                @if($myGroupRegThis->TrangThai === 'Từ chối')
                                    <span class="badge bg-danger rounded-pill px-3 py-2" title="{{ $myGroupRegThis->LyDoTuChoi }}">
                                        <i class="fa-solid fa-circle-xmark me-1"></i>Đã Bị Từ Chối
                                    </span>
                                @else
                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                        <i class="fa-solid fa-check-circle me-1"></i>Đã Gán Cho Nhóm Của Bạn
                                    </span>
                                @endif
                            @elseif($isTakenByOther)
                                <button class="btn btn-sm btn-secondary rounded-pill px-3" disabled title="Đề tài này đã có nhóm khác đăng ký">
                                    <i class="fa-solid fa-lock me-1"></i>Đã Có Nhóm Đăng Ký
                                </button>
                            @elseif(isset($nhom) && $nhom)
                                @if($nhom->MaTruongNhom !== $sinhVien->MaSV)
                                    <span class="text-muted small">Chỉ Trưởng nhóm mới có quyền đăng ký</span>
                                @elseif($hasActiveRegistration)
                                    <button class="btn btn-sm btn-secondary rounded-pill px-3" disabled title="Nhóm của bạn đã đăng ký một đề tài khác rồi">
                                        <i class="fa-solid fa-lock me-1"></i>Nhóm Đã Có Đề Tài
                                    </button>
                                @else
                                    <form action="{{ route('sinhvien.dangky.store') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="MaDeTai" value="{{ $dt->MaDeTai }}">
                                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-sm" onclick="return confirm('Bạn có chắc chắn muốn đại diện nhóm đăng ký đề tài {{ $dt->TenDeTai }}? Sau khi đăng ký, đề tài sẽ được gán trực tiếp cho nhóm.');">
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