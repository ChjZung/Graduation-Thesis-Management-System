@extends('layouts.truongkhoa')

@section('page_title', 'Phê Duyệt Đề Tài Cấp Khoa')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-stamp me-2 text-danger"></i>
                Phê Duyệt Đề Tài Khóa Luận Cấp Khoa – {{ $khoa->TenKhoa ?? 'Khoa' }}
            </h4>
            <p class="text-muted mb-0 small">
                Xem xét và phê duyệt danh mục đề tài đã qua đánh giá phản biện và phê duyệt cấp Bộ môn.
            </p>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-2">
            <ul class="nav nav-pills flex-column flex-sm-row gap-1">
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ !request()->filled('TrangThai') || request('TrangThai') === 'Chờ duyệt cấp Khoa' ? 'active bg-danger text-white' : '' }}" 
                       href="{{ route('truongkhoa.duyet_detai.index', ['TrangThai' => 'Chờ duyệt cấp Khoa']) }}">
                        <i class="fa-solid fa-clock me-1"></i> Chờ Khoa Duyệt 
                        <span class="badge bg-white text-danger ms-1">{{ $counts['cho_duyet_khoa'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('TrangThai') === 'Trưởng khoa đã duyệt' ? 'active bg-success' : '' }}" 
                       href="{{ route('truongkhoa.duyet_detai.index', ['TrangThai' => 'Trưởng khoa đã duyệt']) }}">
                        Trưởng khoa đã duyệt <span class="badge bg-secondary ms-1">{{ $counts['truong_khoa_da_duyet'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('TrangThai') === 'Đã công bố' ? 'active' : '' }}" 
                       href="{{ route('truongkhoa.duyet_detai.index', ['TrangThai' => 'Đã công bố']) }}">
                        Đã công bố <span class="badge bg-secondary ms-1">{{ $counts['da_cong_bo'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('TrangThai') === 'Chờ duyệt cấp Bộ môn' ? 'active' : '' }}" 
                       href="{{ route('truongkhoa.duyet_detai.index', ['TrangThai' => 'Chờ duyệt cấp Bộ môn']) }}">
                        Chờ duyệt BM <span class="badge bg-secondary ms-1">{{ $counts['cho_duyet_bm'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('TrangThai') === 'Đang phản biện đề cương' ? 'active' : '' }}" 
                       href="{{ route('truongkhoa.duyet_detai.index', ['TrangThai' => 'Đang phản biện đề cương']) }}">
                        Đang phản biện <span class="badge bg-secondary ms-1">{{ $counts['dang_phan_bien'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('TrangThai') === 'Yêu cầu chỉnh sửa' ? 'active' : '' }}" 
                       href="{{ route('truongkhoa.duyet_detai.index', ['TrangThai' => 'Yêu cầu chỉnh sửa']) }}">
                        Yêu cầu sửa <span class="badge bg-secondary ms-1">{{ $counts['yeu_cau_sua'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('TrangThai') === 'ALL' ? 'active' : '' }}" 
                       href="{{ route('truongkhoa.duyet_detai.index', ['TrangThai' => 'ALL']) }}">
                        Tất cả <span class="badge bg-secondary ms-1">{{ $counts['total'] }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body">
            <form method="GET" action="{{ route('truongkhoa.duyet_detai.index') }}" class="row g-2 align-items-center">
                @if(request()->filled('TrangThai'))
                    <input type="hidden" name="TrangThai" value="{{ request('TrangThai') }}">
                @endif
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Tìm kiếm theo mã, tên đề tài hoặc GV..." value="{{ request('search') }}">
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
                <div class="col-md-3">
                    <select name="MaHocKy" class="form-select">
                        <option value="">-- Tất cả học kỳ --</option>
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-filter me-1"></i> Lọc
                    </button>
                    @if(request()->anyFilled(['search', 'MaBoMon', 'MaHocKy', 'TrangThai']))
                        <a href="{{ route('truongkhoa.duyet_detai.index') }}" class="btn btn-outline-secondary" title="Đặt lại bộ lọc">
                            <i class="fa-solid fa-rotate-right"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Topics Table -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body p-0">
            @if($detais->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary"></i>
                    <h6>Không tìm thấy đề tài nào theo điều kiện tìm kiếm.</h6>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 90px;">Mã ĐT</th>
                                <th style="min-width: 250px;">Tên Đề Tài & Chuyên Ngành</th>
                                <th style="min-width: 140px;">Bộ Môn & GV Hướng Dẫn</th>
                                <th style="min-width: 130px;">File Đề Cương</th>
                                <th style="min-width: 180px;">Đánh Giá Phản Biện</th>
                                <th style="min-width: 140px;">Trạng Thái</th>
                                <th class="text-end" style="width: 130px;">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detais as $dt)
                                @php
                                    $pb = $dt->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
                                @endphp
                                <tr>
                                    <td class="fw-bold text-danger">{{ $dt->MaDeTai }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $dt->TenDeTai }}</div>
                                        <div class="small text-muted">
                                            <span><i class="fa-solid fa-tag me-1"></i>{{ $dt->nganh->TenNganh ?? 'Chung' }}</span>
                                            <span class="ms-2"><i class="fa-regular fa-calendar me-1"></i>{{ $dt->hocKy->TenHocKy ?? $dt->MaHocKy }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $dt->giangVien->HoTen ?? 'N/A' }}</div>
                                        <div class="small text-muted"><i class="fa-solid fa-building-columns me-1"></i>{{ $dt->giangVien->boMon->TenBoMon ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        @if($dt->FileDeCuong)
                                            <a href="{{ asset('storage/' . $dt->FileDeCuong) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="fa-solid fa-file-pdf me-1"></i> Xem đề cương
                                            </a>
                                        @else
                                            <span class="text-muted small">Chưa có file</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pb)
                                            <div class="small fw-semibold">{{ $pb->giangVien->HoTen ?? 'N/A' }}</div>
                                            <div class="small mt-1">
                                                @if($pb->KetQua === 'Đạt')
                                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Đạt</span>
                                                @elseif($pb->KetQua === 'Yêu cầu chỉnh sửa')
                                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-pen me-1"></i> Yêu cầu sửa</span>
                                                @elseif($pb->KetQua === 'Không đạt')
                                                    <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> Không đạt</span>
                                                @else
                                                    <span class="badge bg-light text-muted border">Chưa đánh giá</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted small">Chưa ghi nhận</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($dt->TrangThai === 'Chờ duyệt cấp Khoa')
                                            <span class="badge bg-danger"><i class="fa-solid fa-clock me-1"></i> Chờ Khoa duyệt</span>
                                        @elseif($dt->TrangThai === 'Trưởng khoa đã duyệt')
                                            <span class="badge bg-success"><i class="fa-solid fa-stamp me-1"></i> TK đã duyệt</span>
                                        @elseif($dt->TrangThai === 'Đã công bố')
                                            <span class="badge bg-primary"><i class="fa-solid fa-bullhorn me-1"></i> Đã công bố</span>
                                        @elseif($dt->TrangThai === 'Chờ duyệt cấp Bộ môn')
                                            <span class="badge bg-warning text-dark">Chờ duyệt BM</span>
                                        @elseif($dt->TrangThai === 'Đang phản biện đề cương')
                                            <span class="badge bg-info text-dark">Đang phản biện</span>
                                        @elseif($dt->TrangThai === 'Yêu cầu chỉnh sửa')
                                            <span class="badge bg-warning text-dark">Yêu cầu sửa</span>
                                        @elseif($dt->TrangThai === 'Từ chối')
                                            <span class="badge bg-secondary">Từ chối</span>
                                        @else
                                            <span class="badge bg-light text-muted border">{{ $dt->TrangThai }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('truongkhoa.duyet_detai.show', $dt->MaDeTai) }}" class="btn btn-sm btn-danger">
                                            <i class="fa-solid fa-eye me-1"></i> Chi tiết
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($detais->hasPages())
                    <div class="p-3 border-top d-flex justify-content-end">
                        {{ $detais->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
