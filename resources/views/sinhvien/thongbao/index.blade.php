@extends('layouts.sinhvien')
@section('title', 'Thông Báo')
@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-primary-custom"><i class="fa-solid fa-bell me-2"></i>Bảng Thông Báo Cá Nhân</h4>
    <form action="{{ route('sinhvien.thongbao.readAll') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-outline-primary rounded-pill btn-sm px-3">
            <i class="fa-solid fa-check-double me-1"></i>Đánh Dấu Tất Cả Đã Đọc
        </button>
    </form>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-premium shadow-sm">
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($thongbaos as $tb)
                    <div class="list-group-item p-4 {{ !$tb->DaDoc ? 'bg-light border-start border-primary border-4' : '' }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h5 class="mb-0 text-primary fw-bold d-inline">{{ $tb->TieuDe }}</h5>
                                @if(!$tb->DaDoc)
                                    <span class="badge bg-warning text-dark ms-2">MỚI</span>
                                @endif
                                @if($tb->LoaiThongBao)
                                    <span class="badge bg-info text-dark ms-1">{{ $tb->LoaiThongBao }}</span>
                                @endif
                            </div>
                            <small class="text-muted"><i class="fa-regular fa-clock me-1"></i> {{ date('d/m/Y H:i', strtotime($tb->created_at ?? $tb->NgayTao)) }}</small>
                        </div>
                        <p class="mb-2 text-secondary" style="font-size: 1rem;">{{ $tb->NoiDung }}</p>
                        
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div>
                                @if(optional($tb->taiKhoan)->MaVaiTro == 1)
                                    <span class="badge bg-danger"><i class="fa-solid fa-shield-halved me-1"></i> Ban Quản Trị</span>
                                @else
                                    <span class="badge bg-success"><i class="fa-solid fa-chalkboard-user me-1"></i> Giảng Viên / Hệ Thống</span>
                                @endif
                            </div>

                            @if($tb->DuongDan)
                                <a href="{{ $tb->DuongDan }}" class="btn btn-sm btn-primary-custom rounded-pill px-3">
                                    Xem chi tiết <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="p-5 text-center text-muted">
                        <i class="fa-solid fa-bell-slash fs-1 opacity-25 mb-3"></i>
                        <p>Hiện không có thông báo nào dành cho bạn.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="mt-3">
            {{ $thongbaos->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
