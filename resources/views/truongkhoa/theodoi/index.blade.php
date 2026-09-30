@extends('layouts.truongkhoa')

@section('page_title', 'Theo Dõi Tiến Độ Toàn Khoa')

@push('styles')
<style>
    #theoDoiMainTabs {
        background: #f1f5f9;
        padding: 4px;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
    }
    #theoDoiMainTabs .nav-link {
        color: #475569;
        font-weight: 700;
        font-size: 0.88rem;
        padding: 8px 24px;
        border-radius: 999px;
        border: none;
        transition: all 0.2s ease;
    }
    #theoDoiMainTabs .nav-link:hover {
        color: #002855;
        background: rgba(255, 255, 255, 0.6);
    }
    #theoDoiMainTabs .nav-link.active {
        background: #002855;
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(0, 40, 85, 0.2);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-chart-line me-2 text-primary"></i>
                Giám Sát Tiến Độ & Tình Hình Bảo Vệ – {{ $khoa->TenKhoa ?? 'Khoa CNTT' }}
            </h4>
            <p class="text-muted mb-0 small">
                Theo dõi tiến độ thực hiện đề tài của các nhóm, tình hình nộp báo cáo định kỳ, xác nhận hồ sơ và hội đồng bảo vệ toàn khoa.
            </p>
        </div>
    </div>

    <!-- ═══ SUMMARY KPI CARDS ═══ -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #0d6efd !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Tổng Nhóm Khóa Luận</div>
                    <h3 class="fw-bold my-1 text-primary">{{ $stats['total_nhom'] }}</h3>
                    <div class="small text-muted">{{ $stats['total_sv'] }} sinh viên tham gia</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #198754 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Đề Tài Đã Công Bố</div>
                    <h3 class="fw-bold my-1 text-success">{{ $stats['detai_cong_bo'] }}</h3>
                    <div class="small text-muted">Trên tổng {{ $stats['total_detai'] }} đề tài của Khoa</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #ffc107 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Báo Cáo Tiến Độ</div>
                    <h3 class="fw-bold my-1 text-warning">{{ $stats['total_baocao'] }}</h3>
                    <div class="small text-muted">Đã nộp qua các giai đoạn</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 4px solid #dc3545 !important;">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase">Hồ Sơ Đủ ĐK Bảo Vệ</div>
                    <h3 class="fw-bold my-1 text-danger">{{ $stats['hoso_du_dieu_kien'] }} / {{ $stats['total_hoso'] }}</h3>
                    <div class="small text-muted">{{ $stats['total_hoidong'] }} hội đồng bảo vệ</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ NAVIGATION TABS ═══ -->
    <div class="mb-4">
        <ul class="nav nav-pills d-inline-flex" id="theoDoiMainTabs">
            <li class="nav-item">
                <a class="nav-link {{ ($tab ?? 'tien_do') === 'tien_do' ? 'active' : '' }}" 
                   href="{{ route('truongkhoa.theodoi.index', ['tab' => 'tien_do']) }}">
                    <i class="fa-solid fa-list-check me-2"></i> Tiến Độ Nhóm &amp; Báo Cáo Định Kỳ
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ ($tab ?? 'tien_do') === 'bao_ve' ? 'active' : '' }}" 
                   href="{{ route('truongkhoa.theodoi.index', ['tab' => 'bao_ve']) }}">
                    <i class="fa-solid fa-shield-halved me-2"></i> Tình Hình Bảo Vệ &amp; Hội Đồng Chấm
                </a>
            </li>
        </ul>
    </div>

    @if(($tab ?? 'tien_do') === 'tien_do')
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- TAB 1: TIẾN ĐỘ NHÓM & BÁO CÁO ĐỊNH KỲ -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body">
                <form method="GET" action="{{ route('truongkhoa.theodoi.index') }}" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="tien_do">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" 
                                   placeholder="Tìm kiếm theo mã nhóm, tên nhóm, tên đề tài hoặc GVHD..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="MaBoMon" class="form-select">
                            <option value="">-- Tất cả Bộ môn --</option>
                            @foreach($boMons as $bm)
                                <option value="{{ $bm->MaBoMon }}" {{ request('MaBoMon') == $bm->MaBoMon ? 'selected' : '' }}>
                                    {{ $bm->TenBoMon }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="MaHocKy" class="form-select">
                            <option value="">-- Tất cả học kỳ --</option>
                            @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold text-nowrap w-100">
                            <i class="fa-solid fa-filter me-1"></i> Lọc
                        </button>
                        <a href="{{ route('truongkhoa.theodoi.index', ['tab' => 'tien_do']) }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3" title="Đặt lại">
                            <i class="fa-solid fa-rotate"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-users text-primary me-2"></i>
                    Danh Sách Nhóm Khóa Luận & Tiến Độ Thực Hiện ({{ $nhoms->total() }} nhóm)
                </h6>
                <div class="small text-muted">Trang {{ $nhoms->currentPage() }}/{{ $nhoms->lastPage() }}</div>
            </div>
            <div class="card-body p-0">
                @if($nhoms->isEmpty())
                    <div class="text-center py-5">
                        <i class="fa-solid fa-inbox text-muted fa-3x mb-3"></i>
                        <h6 class="text-muted">Không tìm thấy nhóm khóa luận nào phù hợp!</h6>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Mã Nhóm</th>
                                    <th>Tên Đề Tài & Bộ Môn</th>
                                    <th>Thành Viên Nhóm</th>
                                    <th>GV Hướng Dẫn</th>
                                    <th>Báo Cáo Định Kỳ</th>
                                    <th>Hội Đồng Bảo Vệ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nhoms as $nhom)
                                    @php
                                        $gvhd = $nhom->dangKyDeTai->giangVienHuongDan ?? ($nhom->deTai->giangVien ?? null);
                                        $totalBc = $nhom->baoCaos ? $nhom->baoCaos->count() : 0;
                                        $approvedBc = $nhom->baoCaos ? $nhom->baoCaos->where('TrangThai', 'Đạt')->count() : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-danger">{{ $nhom->MaNhom }}</div>
                                            <div class="small text-muted">{{ $nhom->TenNhom }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $nhom->deTai->TenDeTai ?? 'Chưa gán đề tài' }}</div>
                                            <div class="small text-muted">
                                                <i class="fa-solid fa-building-columns me-1"></i>
                                                {{ $nhom->deTai->giangVien->boMon->TenBoMon ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($nhom->truongNhom)
                                                <div class="small fw-bold text-dark">
                                                    <i class="fa-solid fa-star text-warning me-1" title="Trưởng nhóm"></i>
                                                    {{ $nhom->truongNhom->HoTen }} ({{ $nhom->truongNhom->MaSV }})
                                                </div>
                                            @endif
                                            @foreach($nhom->thanhViens as $tv)
                                                @if($tv->sinhVien && (!$nhom->truongNhom || $tv->sinhVien->MaSV !== $nhom->truongNhom->MaSV))
                                                    <div class="small text-muted">
                                                        <i class="fa-solid fa-user me-1"></i>
                                                        {{ $tv->sinhVien->HoTen }} ({{ $tv->sinhVien->MaSV }})
                                                    </div>
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $gvhd->HoTen ?? 'Chưa phân công' }}</div>
                                            <div class="small text-muted">{{ $gvhd->HocVi ?? '' }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge {{ $totalBc > 0 ? 'bg-primary' : 'bg-light text-muted border' }}">
                                                    {{ $totalBc }} báo cáo
                                                </span>
                                                @if($approvedBc > 0)
                                                    <span class="badge bg-success" title="Báo cáo đạt yêu cầu">{{ $approvedBc }} đạt</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->hoiDong)
                                                <span class="badge bg-info text-dark">
                                                    <i class="fa-solid fa-users-viewfinder me-1"></i>
                                                    {{ $nhom->hoSoBaoVe->hoiDong->TenHoiDong ?? 'Hội đồng' }}
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted border">Chưa xếp HĐ</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            @if($nhoms->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ $nhoms->links() }}
                </div>
            @endif
        </div>

    @else
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- TAB 2: TÌNH HÌNH BẢO VỆ & HỘI ĐỒNG CHẤM -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body">
                <form method="GET" action="{{ route('truongkhoa.theodoi.index') }}" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="bao_ve">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                            <input type="text" name="search_baove" class="form-control border-start-0" 
                                   placeholder="Tìm kiếm theo mã hồ sơ, tên nhóm hoặc tên đề tài..." value="{{ request('search_baove') }}">
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
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold text-nowrap w-100">
                            <i class="fa-solid fa-filter me-1"></i> Lọc
                        </button>
                        <a href="{{ route('truongkhoa.theodoi.index', ['tab' => 'bao_ve']) }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3" title="Đặt lại">
                            <i class="fa-solid fa-rotate"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-shield-halved text-primary me-2"></i>
                    Danh Sách Hồ Sơ Bảo Vệ & Hội Đồng Chấm ({{ $hoSoBaoVes->total() }} hồ sơ)
                </h6>
                <div class="small text-muted">Trang {{ $hoSoBaoVes->currentPage() }}/{{ $hoSoBaoVes->lastPage() }}</div>
            </div>
            <div class="card-body p-0">
                @if($hoSoBaoVes->isEmpty())
                    <div class="text-center py-5">
                        <i class="fa-solid fa-folder-open text-muted fa-3x mb-3"></i>
                        <h6 class="text-muted">Chưa có hồ sơ bảo vệ nào được ghi nhận!</h6>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 110px;">Mã Hồ Sơ</th>
                                    <th>Nhóm & Đề Tài Khóa Luận</th>
                                    <th>Bộ Môn</th>
                                    <th>Xác Nhận GVHD</th>
                                    <th>Hội Đồng Bảo Vệ</th>
                                    <th>Thời Gian & Phòng</th>
                                    <th>Trạng Thái Hồ Sơ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hoSoBaoVes as $hs)
                                    <tr>
                                        <td class="fw-bold text-danger">{{ $hs->MaHoSo }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $hs->deTai->TenDeTai ?? 'Chưa xác định' }}</div>
                                            <div class="small text-muted">
                                                <i class="fa-solid fa-users me-1"></i>Nhóm: {{ $hs->nhom->TenNhom ?? $hs->MaNhom }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="small fw-semibold text-secondary">
                                                {{ $hs->deTai->giangVien->boMon->TenBoMon ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($hs->XacNhanGVHD == 1)
                                                <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Đã đồng ý</span>
                                                <div class="small text-muted mt-1">{{ $hs->NgayXacNhan ? \Carbon\Carbon::parse($hs->NgayXacNhan)->format('d/m/Y') : '' }}</div>
                                            @elseif($hs->XacNhanGVHD === 0)
                                                <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> Chưa đạt</span>
                                            @else
                                                <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Chờ GVHD duyệt</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($hs->hoiDong)
                                                <div class="fw-bold text-primary">{{ $hs->hoiDong->TenHoiDong }}</div>
                                                <div class="small text-muted">Chủ tịch: {{ $hs->hoiDong->chuTich->HoTen ?? 'Chưa phân công' }}</div>
                                            @else
                                                <span class="badge bg-light text-muted border">Chưa phân công HĐ</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($hs->ThoiGianBaoVe)
                                                <div class="small fw-bold text-dark">
                                                    <i class="fa-regular fa-clock me-1 text-primary"></i>
                                                    {{ \Carbon\Carbon::parse($hs->ThoiGianBaoVe)->format('d/m/Y H:i') }}
                                                </div>
                                                <div class="small text-muted">Phòng: <strong>{{ $hs->PhongBaoVe ?? 'Chưa xếp' }}</strong></div>
                                            @else
                                                <span class="text-muted small">Chưa xếp lịch</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $hs->TrangThai ?? 'Chờ bảo vệ' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            @if($hoSoBaoVes->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ $hoSoBaoVes->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
