@extends('layouts.truongbomon')

@section('page_title', 'Theo Dõi Tiến Độ Bộ Môn')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-list-check me-2 text-warning"></i>
                Theo Dõi Tiến Độ Thực Hiện Khóa Luận – {{ $boMon->TenBoMon ?? 'Bộ Môn' }}
            </h4>
            <p class="text-muted mb-0 small">
                Giám sát việc thực hiện đề tài của các nhóm sinh viên và giảng viên hướng dẫn trong bộ môn.
            </p>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #0d6efd !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold">Tổng Nhóm Thực Hiện</div>
                    <h3 class="fw-bold my-1 text-primary">{{ $stats['total_nhom'] }}</h3>
                    <div class="small text-muted">Nhóm sinh viên Bộ môn</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #198754 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold">Đề Tài Đã Công Bố</div>
                    <h3 class="fw-bold my-1 text-success">{{ $stats['detai_cong_bo'] }}</h3>
                    <div class="small text-muted">Trên tổng số {{ $stats['total_detai'] }} đề tài</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #ffc107 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold">Giảng Viên Tham Gia</div>
                    <h3 class="fw-bold my-1 text-warning">{{ $stats['total_gv'] }}</h3>
                    <div class="small text-muted">Cán bộ hướng dẫn</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #20c997 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold">Nhóm Đã Hoàn Thành</div>
                    <h3 class="fw-bold my-1 text-teal">{{ $stats['nhom_hoan_thanh'] }}</h3>
                    <div class="small text-muted">Bảo vệ & nộp bản chính thức</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter and Search -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body">
            <form method="GET" action="{{ route('truongbomon.theodoi.index') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Tìm theo tên nhóm, đề tài, tên hoặc MSSV trưởng nhóm..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="MaGV" class="form-select">
                        <option value="">-- Tất cả Giảng viên hướng dẫn --</option>
                        @foreach($giangViens as $gv)
                            <option value="{{ $gv->MaGV }}" {{ request('MaGV') == $gv->MaGV ? 'selected' : '' }}>
                                {{ $gv->HoTen }} ({{ $gv->de_tais_count }} đề tài)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-filter me-1"></i> Lọc
                    </button>
                    @if(request()->anyFilled(['search', 'MaGV']))
                        <a href="{{ route('truongbomon.theodoi.index') }}" class="btn btn-outline-secondary" title="Đặt lại bộ lọc">
                            <i class="fa-solid fa-rotate-right"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Groups Tracking Table -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body p-0">
            @if($nhoms->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-users fa-3x mb-3 text-secondary"></i>
                    <h6>Không có nhóm sinh viên nào theo điều kiện tìm kiếm.</h6>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">Mã Nhóm</th>
                                <th style="min-width: 250px;">Đề Tài & GV Hướng Dẫn</th>
                                <th style="min-width: 200px;">Thành Viên Nhóm</th>
                                <th style="min-width: 130px;">Tiến Độ Báo Cáo</th>
                                <th style="min-width: 160px;">Hồ Sơ Bảo Vệ</th>
                                <th style="width: 130px;">Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($nhoms as $nhom)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-primary">{{ $nhom->MaNhom }}</div>
                                        <div class="small text-muted">{{ $nhom->TenNhom }}</div>
                                    </td>
                                    <td>
                                        @if($nhom->deTai)
                                            <div class="fw-bold text-dark">{{ $nhom->deTai->TenDeTai }}</div>
                                            <div class="small text-muted">
                                                <i class="fa-solid fa-chalkboard-user me-1 text-primary"></i>
                                                GVHD: <strong>{{ $nhom->deTai->giangVien->HoTen ?? 'N/A' }}</strong>
                                            </div>
                                        @else
                                            <span class="text-muted small">Chưa đăng ký đề tài</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">
                                            <span class="badge bg-primary-subtle text-primary border me-1">Trưởng nhóm</span>
                                            <strong>{{ $nhom->truongNhom->HoTen ?? 'N/A' }}</strong> ({{ $nhom->MaTruongNhom }})
                                        </div>
                                        @foreach($nhom->thanhViens as $tv)
                                            @if($tv->MaSV !== $nhom->MaTruongNhom)
                                                <div class="small text-muted mt-1 ps-2">
                                                    • {{ $tv->sinhVien->HoTen ?? $tv->MaSV }} ({{ $tv->MaSV }})
                                                </div>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>
                                        @php
                                            $soBaoCao = $nhom->baoCaos->count();
                                        @endphp
                                        <div class="fw-semibold text-dark">
                                            <i class="fa-solid fa-clipboard-check text-info me-1"></i> {{ $soBaoCao }} báo cáo
                                        </div>
                                    </td>
                                    <td>
                                        @if($nhom->hoSoBaoVe)
                                            <div class="small">
                                                @if($nhom->hoSoBaoVe->TrangThai === 'Đã duyệt' || $nhom->hoSoBaoVe->TrangThai === 'Đã phê duyệt')
                                                    <span class="badge bg-success">Đủ ĐK Bảo Vệ</span>
                                                @elseif($nhom->hoSoBaoVe->TrangThai === 'GVHD đã xác nhận')
                                                    <span class="badge bg-info text-dark">GVHD đã xác nhận</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">{{ $nhom->hoSoBaoVe->TrangThai }}</span>
                                                @endif
                                            </div>
                                            @if($nhom->hoSoBaoVe->hoiDong)
                                                <div class="small text-muted mt-1">
                                                    <i class="fa-solid fa-users-rectangle me-1"></i> HĐ: {{ $nhom->hoSoBaoVe->hoiDong->TenHoiDong }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="badge bg-light text-muted border small">Chưa nộp hồ sơ</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($nhom->TrangThai === 'Hoàn thành')
                                            <span class="badge bg-success">Hoàn thành</span>
                                        @elseif($nhom->TrangThai === 'Đang thực hiện')
                                            <span class="badge bg-info text-dark">Đang thực hiện</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $nhom->TrangThai }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($nhoms->hasPages())
                    <div class="p-3 border-top d-flex justify-content-end">
                        {{ $nhoms->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
