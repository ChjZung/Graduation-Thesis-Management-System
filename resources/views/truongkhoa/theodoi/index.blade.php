@extends('layouts.truongkhoa')

@section('page_title', 'Theo Dõi Toàn Khoa')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-chart-line me-2 text-danger"></i>
                Giám Sát Tiến Độ & Kết Quả Khóa Luận Toàn Khoa
            </h4>
            <p class="text-muted mb-0 small">
                Theo dõi tình hình thực hiện của các nhóm, hoạt động các hội đồng bảo vệ và tra cứu bảng điểm kết quả sinh viên.
            </p>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #0d6efd !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold">Tổng Nhóm Khóa Luận</div>
                    <h3 class="fw-bold my-1 text-primary">{{ $stats['total_nhom'] }}</h3>
                    <div class="small text-muted">Toàn bộ các bộ môn</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #198754 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold">Đề Tài Đã Công Bố</div>
                    <h3 class="fw-bold my-1 text-success">{{ $stats['detai_cong_bo'] }}</h3>
                    <div class="small text-muted">Trên tổng {{ $stats['total_detai'] }} đề tài</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #dc3545 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold">Hội Đồng Bảo Vệ</div>
                    <h3 class="fw-bold my-1 text-danger">{{ $stats['total_hoidong'] }}</h3>
                    <div class="small text-muted">Đã thành lập</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 4px solid #ffc107 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold">SV Đã Có Điểm Tổng Kết</div>
                    <h3 class="fw-bold my-1 text-warning">{{ $stats['total_sv_ketqua'] }}</h3>
                    <div class="small text-muted">Xuất sắc: {{ $stats['sv_xuat_sac'] }} | Giỏi: {{ $stats['sv_gioi'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-2">
            <ul class="nav nav-pills gap-2">
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ $tab === 'tien_do' ? 'active bg-danger text-white' : '' }}" 
                       href="{{ route('truongkhoa.theodoi.index', ['tab' => 'tien_do']) }}">
                        <i class="fa-solid fa-list-check me-1"></i> Theo Dõi Tiến Độ Nhóm & Hội Đồng
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ $tab === 'ket_qua' ? 'active bg-danger text-white' : '' }}" 
                       href="{{ route('truongkhoa.theodoi.index', ['tab' => 'ket_qua']) }}">
                        <i class="fa-solid fa-award me-1"></i> Kết Quả & Xếp Loại Khóa Luận
                    </a>
                </li>
            </ul>
        </div>
    </div>

    @if($tab === 'tien_do')
        <!-- Tab 1: Tiến độ nhóm -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body">
                <form method="GET" action="{{ route('truongkhoa.theodoi.index') }}" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="tien_do">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" 
                                   placeholder="Tìm kiếm theo mã nhóm, tên nhóm hoặc tên đề tài..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="MaBoMon" class="form-select">
                            <option value="">-- Tất cả Bộ môn --</option>
                            @foreach($boMons as $bm)
                                <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                                    {{ $bm->TenBoMon }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fa-solid fa-filter me-1"></i> Lọc
                        </button>
                        @if(request()->anyFilled(['search', 'MaBoMon']))
                            <a href="{{ route('truongkhoa.theodoi.index', ['tab' => 'tien_do']) }}" class="btn btn-outline-secondary" title="Đặt lại">
                                <i class="fa-solid fa-rotate-right"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-0">
                @if($nhoms->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-users fa-3x mb-3 text-secondary"></i>
                        <h6>Không tìm thấy nhóm sinh viên nào theo điều kiện lọc.</h6>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Mã Nhóm</th>
                                    <th style="min-width: 250px;">Đề Tài & GV Hướng Dẫn</th>
                                    <th style="min-width: 140px;">Bộ Môn</th>
                                    <th style="min-width: 180px;">Trưởng Nhóm & Thành Viên</th>
                                    <th style="min-width: 120px;">Báo Cáo</th>
                                    <th style="min-width: 160px;">Hội Đồng Bảo Vệ</th>
                                    <th style="width: 120px;">Trạng Thái</th>
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
                                                    {{ $nhom->deTai->giangVien->HoTen ?? 'N/A' }}
                                                </div>
                                            @else
                                                <span class="text-muted small">Chưa đăng ký đề tài</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $nhom->deTai->giangVien->boMon->TenBoMon ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold text-dark">
                                                {{ $nhom->truongNhom->HoTen ?? 'N/A' }} ({{ $nhom->MaTruongNhom }})
                                            </div>
                                            <div class="small text-muted">Tổng số SV: {{ $nhom->thanhViens->count() }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info border">
                                                {{ $nhom->baoCaos->count() }} báo cáo
                                            </span>
                                        </td>
                                        <td>
                                            @if($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->hoiDong)
                                                <div class="small fw-bold text-dark">{{ $nhom->hoSoBaoVe->hoiDong->TenHoiDong }}</div>
                                                <div class="small text-muted">{{ $nhom->hoSoBaoVe->hoiDong->DiaDiem ?? '' }}</div>
                                            @else
                                                <span class="text-muted small">Chưa phân công HĐ</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($nhom->TrangThai === 'Hoàn thành')
                                                <span class="badge bg-success">Hoàn thành</span>
                                            @elseif($nhom->TrangThai === 'Đang thực hiện')
                                                <span class="badge bg-primary">Đang thực hiện</span>
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
    @else
        <!-- Tab 2: Kết quả sinh viên -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body">
                <form method="GET" action="{{ route('truongkhoa.theodoi.index') }}" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="ket_qua">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                            <input type="text" name="search_kq" class="form-control border-start-0" 
                                   placeholder="Tìm kiếm theo MSSV hoặc họ tên sinh viên..." value="{{ request('search_kq') }}">
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
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fa-solid fa-filter me-1"></i> Lọc
                        </button>
                        @if(request()->anyFilled(['search_kq', 'MaHocKy']))
                            <a href="{{ route('truongkhoa.theodoi.index', ['tab' => 'ket_qua']) }}" class="btn btn-outline-secondary" title="Đặt lại">
                                <i class="fa-solid fa-rotate-right"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-0">
                @if($ketQuas->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-award fa-3x mb-3 text-secondary"></i>
                        <h6>Chưa có dữ liệu kết quả chấm điểm bảo vệ cho điều kiện tìm kiếm.</h6>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 110px;">MSSV</th>
                                    <th>Họ Và Tên</th>
                                    <th>Lớp / Ngành</th>
                                    <th>Điểm GVHD</th>
                                    <th>Điểm GVPB</th>
                                    <th>Điểm Hội Đồng</th>
                                    <th>Điểm Tổng Kết</th>
                                    <th>Xếp Loại</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ketQuas as $kq)
                                    <tr>
                                        <td class="fw-bold text-primary">{{ $kq->MaSV }}</td>
                                        <td class="fw-semibold text-dark">{{ $kq->sinhVien->HoTen ?? 'N/A' }}</td>
                                        <td>
                                            <div class="small">{{ $kq->sinhVien->lop->TenLop ?? 'N/A' }}</div>
                                            <div class="small text-muted">{{ $kq->sinhVien->lop->nganh->TenNganh ?? '' }}</div>
                                        </td>
                                        <td>{{ $kq->DiemHuongDan !== null ? number_format($kq->DiemHuongDan, 2) : '-' }}</td>
                                        <td>{{ $kq->DiemPhanBien !== null ? number_format($kq->DiemPhanBien, 2) : '-' }}</td>
                                        <td>{{ $kq->DiemHoiDong !== null ? number_format($kq->DiemHoiDong, 2) : '-' }}</td>
                                        <td class="fw-bold text-danger fs-6">
                                            {{ $kq->DiemTongKet !== null ? number_format($kq->DiemTongKet, 2) : '-' }}
                                        </td>
                                        <td>
                                            @if($kq->KetQua === 'Xuất sắc')
                                                <span class="badge bg-success">Xuất sắc</span>
                                            @elseif($kq->KetQua === 'Giỏi')
                                                <span class="badge bg-primary">Giỏi</span>
                                            @elseif($kq->KetQua === 'Khá')
                                                <span class="badge bg-info text-dark">Khá</span>
                                            @elseif($kq->KetQua === 'Trung bình')
                                                <span class="badge bg-warning text-dark">Trung bình</span>
                                            @elseif($kq->KetQua === 'Không đạt')
                                                <span class="badge bg-danger">Không đạt</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $kq->KetQua ?? '-' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($ketQuas->hasPages())
                        <div class="p-3 border-top d-flex justify-content-end">
                            {{ $ketQuas->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
