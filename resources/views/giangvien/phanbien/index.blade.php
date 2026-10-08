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

    <!-- Semester Filter Bar -->
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('giangvien.phanbien.index') }}" class="row g-2 align-items-center">
                @if(request('KetQua'))
                    <input type="hidden" name="KetQua" value="{{ request('KetQua') }}">
                @endif
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted fw-bold mb-1">
                        <i class="fa-regular fa-calendar me-1 text-primary"></i>Học Kỳ:
                    </label>
                    <select name="MaHocKy" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="ALL" {{ $selectedHocKy === 'ALL' ? 'selected' : '' }}>-- Tất cả học kỳ --</option>
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }} {{ $hk->TrangThai == 'Đang diễn ra' ? '★' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-8 d-flex align-items-end justify-content-md-end gap-2 mt-2 mt-md-0">
                    @php
                        $curHkObj = $hocKies->firstWhere('MaHocKy', $selectedHocKy);
                    @endphp
                    <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small">
                        Hiển thị: <strong>{{ $curHkObj?->TenHocKy ?? ($selectedHocKy === 'ALL' ? 'Tất cả học kỳ' : $selectedHocKy) }}</strong> &bull; Tổng <strong>{{ $phanCongs->total() }}</strong> đề tài
                    </span>
                    @if(request('MaHocKy') || request('KetQua'))
                        <a href="{{ route('giangvien.phanbien.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Đặt lại bộ lọc">
                            <i class="fa-solid fa-rotate-left me-1"></i>Đặt lại
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-2">
            <ul class="nav nav-pills flex-column flex-sm-row gap-1">
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ !request()->filled('KetQua') ? 'active' : '' }}" 
                       href="{{ route('giangvien.phanbien.index', array_filter(['MaHocKy' => $selectedHocKy])) }}">
                        Tất cả <span class="badge bg-secondary ms-1">{{ $counts['total'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('KetQua') === 'chua_danh_gia' ? 'active bg-warning text-dark' : '' }}" 
                       href="{{ route('giangvien.phanbien.index', array_filter(['MaHocKy' => $selectedHocKy, 'KetQua' => 'chua_danh_gia'])) }}">
                        <i class="fa-solid fa-clock me-1"></i> Chưa Đánh Giá 
                        <span class="badge bg-dark ms-1">{{ $counts['chua_danh_gia'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('KetQua') === 'da_nop_lai' ? 'active bg-primary' : '' }}" 
                       href="{{ route('giangvien.phanbien.index', array_filter(['MaHocKy' => $selectedHocKy, 'KetQua' => 'da_nop_lai'])) }}">
                        <i class="fa-solid fa-rotate me-1"></i> Đã nộp lại đề cương 
                        <span class="badge {{ $counts['da_nop_lai'] > 0 ? 'bg-light text-primary' : 'bg-secondary' }} ms-1">{{ $counts['da_nop_lai'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('KetQua') === 'Đạt' ? 'active bg-success' : '' }}" 
                       href="{{ route('giangvien.phanbien.index', array_filter(['MaHocKy' => $selectedHocKy, 'KetQua' => 'Đạt'])) }}">
                        Đạt <span class="badge bg-secondary ms-1">{{ $counts['dat'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('KetQua') === 'Yêu cầu chỉnh sửa' ? 'active bg-warning text-dark' : '' }}" 
                       href="{{ route('giangvien.phanbien.index', array_filter(['MaHocKy' => $selectedHocKy, 'KetQua' => 'Yêu cầu chỉnh sửa'])) }}">
                        Yêu cầu sửa <span class="badge bg-secondary ms-1">{{ $counts['yeu_cau_sua'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('KetQua') === 'Không đạt' ? 'active bg-danger' : '' }}" 
                       href="{{ route('giangvien.phanbien.index', array_filter(['MaHocKy' => $selectedHocKy, 'KetQua' => 'Không đạt'])) }}">
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
                    <h6>Hiện tại quý Thầy/Cô không có đề cương nào được phân công phản biện trong học kỳ này.</h6>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 120px;">Mã ĐT</th>
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
                                    <td>
                                        <span class="fw-bold text-primary">{{ $pc->MaDeTai }}</span>
                                        <div class="small text-muted mt-1" style="font-size: 0.74rem;">{{ $pc->deTai->hocKy->TenHocKy ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $pc->deTai->TenDeTai ?? 'N/A' }}</div>
                                        <div class="small text-muted mt-1 d-flex flex-wrap gap-1 align-items-center">
                                            <span><i class="fa-solid fa-tag me-1"></i>{{ $pc->deTai->nganh->TenNganh ?? 'Toàn ngành' }}</span>
                                            <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5 ms-1" style="font-size: 0.72rem;">{{ $pc->deTai->HocPhan ?? 'Khóa luận cử nhân' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $pc->deTai->giangVien->HoTen ?? 'N/A' }}</div>
                                        <div class="small text-muted">{{ $pc->deTai->giangVien->boMon->TenBoMon ?? '' }}</div>
                                    </td>
                                    <td>
                                        @if($pc->deTai && $pc->deTai->FileDeCuong)
                                            @php
                                                $filePath = Str::startsWith($pc->deTai->FileDeCuong, ['http', 'storage/']) ? asset($pc->deTai->FileDeCuong) : asset('storage/' . $pc->deTai->FileDeCuong);
                                                $fileName = basename($pc->deTai->FileDeCuong);
                                            @endphp
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 shadow-sm"
                                                    onclick="quickPreviewOutline('{{ $filePath }}', '{{ $fileName }}', '{{ addslashes($pc->deTai->TenDeTai) }}')"
                                                    title="Xem nhanh đề cương trực tiếp trên web">
                                                <i class="fa-solid fa-eye me-1"></i> Xem đề cương
                                            </button>
                                        @else
                                            <span class="text-muted small">Chưa có file</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        {{ $pc->NgayPhanCong ? \Carbon\Carbon::parse($pc->NgayPhanCong)->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td>
                                        @php
                                            $isNopLai = $pc->KetQua === 'Đã nộp lại' 
                                                || $pc->TrangThai === 'Đã nộp lại đề cương' 
                                                || ($pc->deTai && $pc->deTai->TrangThai === 'Đã cập nhật đề cương - Chờ phản biện lại');
                                        @endphp
                                        @if($pc->KetQua === 'Đạt')
                                            <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Đạt</span>
                                        @elseif($isNopLai)
                                            <span class="badge bg-primary px-2.5 py-1.5 shadow-sm text-wrap text-start">
                                                <i class="fa-solid fa-rotate me-1"></i> Đã nộp lại đề cương
                                            </span>
                                            <div class="small text-primary fw-bold mt-1">
                                                <i class="fa-solid fa-bell me-1"></i>Chờ phản biện lại
                                            </div>
                                        @elseif($pc->KetQua === 'Yêu cầu chỉnh sửa')
                                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-pen me-1"></i> Yêu cầu sửa</span>
                                            <div class="small text-muted mt-0.5">(Chờ GV sửa)</div>
                                        @elseif($pc->KetQua === 'Không đạt')
                                            <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> Không đạt</span>
                                        @else
                                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Chưa đánh giá</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($isNopLai)
                                            <a href="{{ route('giangvien.phanbien.show', $pc->MaPhanCong) }}" class="btn btn-sm btn-primary fw-bold shadow-sm">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Phản biện lại
                                            </a>
                                        @elseif($pc->KetQua)
                                            <a href="{{ route('giangvien.phanbien.show', $pc->MaPhanCong) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fa-solid fa-eye me-1"></i> Xem lại
                                            </a>
                                        @else
                                            <a href="{{ route('giangvien.phanbien.show', $pc->MaPhanCong) }}" class="btn btn-sm btn-primary">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Đánh giá
                                            </a>
                                        @endif
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
