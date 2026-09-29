@extends('layouts.giangvien')

@section('page_title', 'Phản Biện Đề Cương Đề Tài')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-file-pen me-2 text-primary"></i>
                Danh Sách Đề Tài Được Phân Công Phản Biện Đề Cương
            </h4>
            <p class="text-muted mb-0 small">
                Nhiệm vụ chuyên môn được Trưởng bộ môn phân công: Tải đề cương, đọc thẩm tra và đưa ra nhận xét, đánh giá kết quả (Đạt / Yêu cầu chỉnh sửa / Không đạt).
            </p>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-2">
            <ul class="nav nav-pills flex-column flex-sm-row gap-1">
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ !request()->filled('KetQua') ? 'active' : '' }}" 
                       href="{{ route('giangvien.phanbien.index') }}">
                        Tất cả <span class="badge bg-secondary ms-1">{{ $counts['total'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('KetQua') === 'chua_danh_gia' ? 'active bg-warning text-dark' : '' }}" 
                       href="{{ route('giangvien.phanbien.index', ['KetQua' => 'chua_danh_gia']) }}">
                        <i class="fa-solid fa-clock me-1"></i> Chưa Đánh Giá 
                        <span class="badge bg-dark ms-1">{{ $counts['chua_danh_gia'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('KetQua') === 'Đạt' ? 'active bg-success' : '' }}" 
                       href="{{ route('giangvien.phanbien.index', ['KetQua' => 'Đạt']) }}">
                        Đạt <span class="badge bg-secondary ms-1">{{ $counts['dat'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('KetQua') === 'Yêu cầu chỉnh sửa' ? 'active bg-warning text-dark' : '' }}" 
                       href="{{ route('giangvien.phanbien.index', ['KetQua' => 'Yêu cầu chỉnh sửa']) }}">
                        Yêu cầu sửa <span class="badge bg-secondary ms-1">{{ $counts['yeu_cau_sua'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('KetQua') === 'Không đạt' ? 'active bg-danger' : '' }}" 
                       href="{{ route('giangvien.phanbien.index', ['KetQua' => 'Không đạt']) }}">
                        Không đạt <span class="badge bg-secondary ms-1">{{ $counts['khong_dat'] }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Assignments Table -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body p-0">
            @if($phanCongs->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary"></i>
                    <h6>Hiện tại quý Thầy/Cô không có đề cương nào được phân công phản biện.</h6>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">Mã ĐT</th>
                                <th style="min-width: 250px;">Tên Đề Tài & Ngành</th>
                                <th style="min-width: 150px;">GV Đề Xuất</th>
                                <th style="min-width: 130px;">File Đề Cương</th>
                                <th style="min-width: 130px;">Ngày Phân Công</th>
                                <th style="min-width: 140px;">Kết Quả Đánh Giá</th>
                                <th class="text-end" style="width: 130px;">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($phanCongs as $pc)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $pc->MaDeTai }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $pc->deTai->TenDeTai ?? 'N/A' }}</div>
                                        <div class="small text-muted">
                                            <i class="fa-solid fa-tag me-1"></i>{{ $pc->deTai->nganh->TenNganh ?? 'Chung' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $pc->deTai->giangVien->HoTen ?? 'N/A' }}</div>
                                        <div class="small text-muted">{{ $pc->deTai->giangVien->boMon->TenBoMon ?? '' }}</div>
                                    </td>
                                    <td>
                                        @if($pc->deTai && $pc->deTai->FileDeCuong)
                                            <a href="{{ asset('storage/' . $pc->deTai->FileDeCuong) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="fa-solid fa-file-pdf me-1"></i> Đề cương
                                            </a>
                                        @else
                                            <span class="text-muted small">Chưa có file</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        {{ $pc->NgayPhanCong ? \Carbon\Carbon::parse($pc->NgayPhanCong)->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td>
                                        @if($pc->KetQua === 'Đạt')
                                            <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Đạt</span>
                                        @elseif($pc->KetQua === 'Yêu cầu chỉnh sửa')
                                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-pen me-1"></i> Yêu cầu sửa</span>
                                        @elseif($pc->KetQua === 'Không đạt')
                                            <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> Không đạt</span>
                                        @else
                                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Chưa đánh giá</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('giangvien.phanbien.show', $pc->MaPhanCong) }}" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> Đánh giá
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($phanCongs->hasPages())
                    <div class="p-3 border-top d-flex justify-content-end">
                        {{ $phanCongs->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
