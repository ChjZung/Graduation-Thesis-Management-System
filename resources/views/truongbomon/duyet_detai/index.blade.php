@extends('layouts.truongbomon')

@section('page_title', 'Duyệt Đề Tài & Phản Biện Đề Cương')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-clipboard-check me-2 text-warning"></i>
                Phê Duyệt Đề Tài & Phân Công Phản Biện – {{ $boMon->TenBoMon ?? 'Bộ Môn' }}
            </h4>
            <p class="text-muted mb-0 small">
                Tiếp nhận đề tài từ Giảng viên, phân công phản biện đề cương, đánh giá và trình phê duyệt cấp Khoa.
            </p>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-2">
            <ul class="nav nav-pills flex-column flex-sm-row gap-1">
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ !request()->filled('TrangThai') ? 'active bg-warning text-dark' : '' }}" 
                       href="{{ route('truongbomon.duyet_detai.index') }}">
                        <i class="fa-solid fa-hourglass-half me-1"></i> Cần Xử Lý 
                        <span class="badge bg-dark ms-1">{{ $counts['cho_duyet_bm'] + $counts['da_phan_bien'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('TrangThai') === 'Chờ duyệt cấp Bộ môn' ? 'active' : '' }}" 
                       href="{{ route('truongbomon.duyet_detai.index', ['TrangThai' => 'Chờ duyệt cấp Bộ môn']) }}">
                        Chờ duyệt BM <span class="badge bg-secondary ms-1">{{ $counts['cho_duyet_bm'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('TrangThai') === 'Đang phản biện đề cương' ? 'active' : '' }}" 
                       href="{{ route('truongbomon.duyet_detai.index', ['TrangThai' => 'Đang phản biện đề cương']) }}">
                        Đang phản biện <span class="badge bg-secondary ms-1">{{ $counts['dang_phan_bien'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('TrangThai') === 'Đã phản biện - Chờ duyệt BM' ? 'active' : '' }}" 
                       href="{{ route('truongbomon.duyet_detai.index', ['TrangThai' => 'Đã phản biện - Chờ duyệt BM']) }}">
                        Đã phản biện <span class="badge bg-secondary ms-1">{{ $counts['da_phan_bien'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('TrangThai') === 'Chờ duyệt cấp Khoa' ? 'active' : '' }}" 
                       href="{{ route('truongbomon.duyet_detai.index', ['TrangThai' => 'Chờ duyệt cấp Khoa']) }}">
                        Chờ Khoa duyệt <span class="badge bg-secondary ms-1">{{ $counts['cho_duyet_khoa'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('TrangThai') === 'Trưởng khoa đã duyệt' ? 'active' : '' }}" 
                       href="{{ route('truongbomon.duyet_detai.index', ['TrangThai' => 'Trưởng khoa đã duyệt']) }}">
                        TK đã duyệt <span class="badge bg-secondary ms-1">{{ $counts['da_duyet_khoa'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('TrangThai') === 'Đã công bố' ? 'active' : '' }}" 
                       href="{{ route('truongbomon.duyet_detai.index', ['TrangThai' => 'Đã công bố']) }}">
                        Đã công bố <span class="badge bg-secondary ms-1">{{ $counts['da_cong_bo'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('TrangThai') === 'ALL' ? 'active' : '' }}" 
                       href="{{ route('truongbomon.duyet_detai.index', ['TrangThai' => 'ALL']) }}">
                        Tất cả <span class="badge bg-secondary ms-1">{{ $counts['total'] }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body">
            <form method="GET" action="{{ route('truongbomon.duyet_detai.index') }}" class="row g-2 align-items-center">
                @if(request()->filled('TrangThai'))
                    <input type="hidden" name="TrangThai" value="{{ request('TrangThai') }}">
                @endif
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Tìm kiếm theo mã, tên đề tài hoặc GV đề xuất..." value="{{ request('search') }}">
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
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-filter me-1"></i> Lọc dữ liệu
                    </button>
                    @if(request()->anyFilled(['search', 'MaHocKy', 'TrangThai']))
                        <a href="{{ route('truongbomon.duyet_detai.index') }}" class="btn btn-outline-secondary" title="Đặt lại bộ lọc">
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
                    <h6>Không tìm thấy đề tài nào phù hợp với bộ lọc hiện tại.</h6>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 90px;">Mã ĐT</th>
                                <th style="min-width: 250px;">Tên Đề Tài & Ngành</th>
                                <th style="min-width: 140px;">GV Đề Xuất</th>
                                <th style="min-width: 130px;">File Đề Cương</th>
                                <th style="min-width: 180px;">GV Phản Biện Đề Cương</th>
                                <th style="min-width: 150px;">Trạng Thái</th>
                                <th class="text-end" style="width: 130px;">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detais as $dt)
                                @php
                                    $pb = $dt->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
                                @endphp
                                <tr>
                                    <td class="fw-bold text-primary">{{ $dt->MaDeTai }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $dt->TenDeTai }}</div>
                                        <div class="small text-muted">
                                            <span><i class="fa-solid fa-tag me-1"></i>{{ $dt->nganh->TenNganh ?? 'Chung' }}</span>
                                            <span class="ms-2"><i class="fa-regular fa-calendar me-1"></i>{{ $dt->hocKy->TenHocKy ?? $dt->MaHocKy }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $dt->giangVien->HoTen ?? 'N/A' }}</div>
                                        <div class="small text-muted">{{ $dt->giangVien->MaGV ?? '' }}</div>
                                    </td>
                                    <td>
                                        @if($dt->FileDeCuong)
                                            <a href="{{ asset('storage/' . $dt->FileDeCuong) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="fa-solid fa-file-pdf me-1"></i> Xem đề cương
                                            </a>
                                        @else
                                            <span class="text-muted small"><i class="fa-solid fa-circle-minus me-1"></i> Chưa đính kèm</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pb)
                                            <div class="fw-semibold text-dark">{{ $pb->giangVien->HoTen ?? 'N/A' }}</div>
                                            <div class="small mt-1">
                                                @if($pb->KetQua === 'Đạt')
                                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Đạt</span>
                                                @elseif($pb->KetQua === 'Yêu cầu chỉnh sửa')
                                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-pen me-1"></i> Cần chỉnh sửa</span>
                                                @elseif($pb->KetQua === 'Không đạt')
                                                    <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> Không đạt</span>
                                                @else
                                                    <span class="badge bg-light text-muted border"><i class="fa-solid fa-hourglass-start me-1"></i> Đang chờ nhận xét</span>
                                                @endif
                                            </div>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalPhanCong{{ $dt->MaDeTai }}">
                                                <i class="fa-solid fa-user-plus me-1"></i> Phân công PB
                                            </button>
                                        @endif
                                    </td>
                                    <td>
                                        @if($dt->TrangThai === 'Chờ duyệt cấp Bộ môn')
                                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Chờ duyệt BM</span>
                                        @elseif($dt->TrangThai === 'Đang phản biện đề cương')
                                            <span class="badge bg-info text-dark"><i class="fa-solid fa-spinner me-1"></i> Đang phản biện</span>
                                        @elseif($dt->TrangThai === 'Đã phản biện - Chờ duyệt BM')
                                            <span class="badge bg-primary"><i class="fa-solid fa-check-double me-1"></i> Đã phản biện</span>
                                        @elseif($dt->TrangThai === 'Chờ duyệt cấp Khoa')
                                            <span class="badge bg-secondary"><i class="fa-solid fa-paper-plane me-1"></i> Chờ Khoa duyệt</span>
                                        @elseif($dt->TrangThai === 'Trưởng khoa đã duyệt')
                                            <span class="badge bg-success"><i class="fa-solid fa-stamp me-1"></i> TK đã duyệt</span>
                                        @elseif($dt->TrangThai === 'Đã công bố')
                                            <span class="badge bg-success"><i class="fa-solid fa-bullhorn me-1"></i> Đã công bố</span>
                                        @elseif($dt->TrangThai === 'Yêu cầu chỉnh sửa')
                                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-triangle-exclamation me-1"></i> Yêu cầu sửa</span>
                                        @elseif($dt->TrangThai === 'Từ chối')
                                            <span class="badge bg-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Từ chối</span>
                                        @else
                                            <span class="badge bg-light text-muted border">{{ $dt->TrangThai }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('truongbomon.duyet_detai.show', $dt->MaDeTai) }}" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-eye me-1"></i> Chi tiết
                                        </a>
                                    </td>
                                </tr>

                                <!-- Modal Phân Công Phản Biện Nhanh -->
                                <div class="modal fade" id="modalPhanCong{{ $dt->MaDeTai }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('truongbomon.duyet_detai.phanCongPhanBien', $dt->MaDeTai) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">
                                                        <i class="fa-solid fa-user-plus text-warning me-2"></i>
                                                        Phân Công Phản Biện Đề Cương
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted small">Đề tài:</label>
                                                        <div class="fw-bold text-dark">{{ $dt->TenDeTai }}</div>
                                                        <div class="small text-muted">GV Đề xuất: {{ $dt->giangVien->HoTen ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Chọn Giảng viên phản biện <span class="text-danger">*</span></label>
                                                        <select name="MaGVPhanBien" class="form-select" required>
                                                            <option value="">-- Chọn Giảng viên phản biện --</option>
                                                            @foreach($allGiangViens as $gvOption)
                                                                @if($gvOption->MaGV !== $dt->MaGV)
                                                                    <option value="{{ $gvOption->MaGV }}">
                                                                        {{ $gvOption->HoTen }} ({{ $gvOption->MaGV }} - {{ $gvOption->boMon->TenBoMon ?? 'N/A' }})
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                        <div class="form-text text-muted">
                                                            <i class="fa-solid fa-circle-info me-1"></i> Theo quy định, GV đề xuất đề tài không được làm phản biện cho đề tài đó.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fa-solid fa-check me-1"></i> Xác nhận phân công
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
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
