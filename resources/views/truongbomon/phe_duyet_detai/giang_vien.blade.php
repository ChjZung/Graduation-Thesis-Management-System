@extends('layouts.truongbomon')

@section('page_title', 'Danh sách đề tài của giảng viên ' . ($giangVien->HoTen ?? ''))

@push('styles')
<style>
    .badge-code {
        background: #f1f5f9;
        color: #0f172a;
        font-family: monospace;
        font-weight: 700;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
    }
    .badge-status-choduyet {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    .badge-status-yeucausua {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }
    .badge-status-daduyet {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .badge-status-tuchoi {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    .nav-filter-pill {
        border-radius: 50rem;
        padding: 0.45rem 1rem;
        font-weight: 600;
        font-size: 0.825rem;
        transition: all 0.15s ease;
        text-decoration: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3 px-3 px-md-4">
    <!-- Nút Quay lại & Breadcrumb -->
    <div class="mb-3">
        <a href="{{ route('truongbomon.phe_duyet_detai.index', ['hocKy' => $selectedHocKy]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách giảng viên (Học kỳ {{ $selectedHocKy }})
        </a>
    </div>

    <!-- Header Thông Tin Giảng Viên -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 58px; height: 58px; font-size: 1.5rem;">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge-code">{{ $giangVien->MaGV }}</span>
                            <span class="badge bg-light text-secondary border rounded-pill">{{ $giangVien->HocVi ?? 'Giảng viên' }}</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                                {{ $giangVien->boMon->TenBoMon ?? $boMon->TenBoMon }}
                            </span>
                        </div>
                        <h4 class="fw-bold text-dark mb-1">Danh sách đề tài của giảng viên: {{ $giangVien->HoTen }}</h4>
                        <div class="text-muted small d-flex flex-wrap align-items-center gap-3">
                            <span><i class="fa-regular fa-envelope me-1"></i>{{ $giangVien->Email }}</span>
                            @if($giangVien->SoDienThoai)
                                <span><i class="fa-solid fa-phone me-1"></i>{{ $giangVien->SoDienThoai }}</span>
                            @endif
                            <span><i class="fa-solid fa-calendar-check me-1 text-primary"></i>Học kỳ đang xem: <strong>{{ $currentHocKyModel->TenHocKy ?? $selectedHocKy }}</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Đổi Học Kỳ Nhanh -->
                <div class="d-flex align-items-center gap-2 bg-light p-2 rounded-3 border">
                    <label class="small fw-bold text-secondary mb-0 text-nowrap"><i class="fa-solid fa-filter me-1"></i>Học kỳ:</label>
                    <form method="GET" action="{{ route('truongbomon.phe_duyet_detai.giang_vien', $giangVien->MaGV) }}" class="d-inline-block">
                        @if(request('trang_thai'))
                            <input type="hidden" name="trang_thai" value="{{ request('trang_thai') }}">
                        @endif
                        <select name="hocKy" class="form-select form-select-sm fw-semibold border-0 bg-white" onchange="this.form.submit()">
                            @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                    {{ $hk->TenHocKy }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bộ Lọc Trạng Thái Đề Tài (Pills) -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <a href="{{ route('truongbomon.phe_duyet_detai.giang_vien', ['maGV' => $giangVien->MaGV, 'hocKy' => $selectedHocKy, 'trang_thai' => 'ALL']) }}" 
           class="nav-filter-pill {{ $selectedStatus === 'ALL' ? 'bg-primary text-white shadow-xs' : 'bg-white text-secondary border' }}">
            Tất cả đề tài ({{ $statFilter['total'] }})
        </a>
        <a href="{{ route('truongbomon.phe_duyet_detai.giang_vien', ['maGV' => $giangVien->MaGV, 'hocKy' => $selectedHocKy, 'trang_thai' => 'cho_duyet']) }}" 
           class="nav-filter-pill {{ $selectedStatus === 'cho_duyet' ? 'bg-warning text-dark shadow-xs' : 'bg-white text-secondary border' }}">
            <i class="fa-solid fa-clock me-1 text-warning"></i> Chờ duyệt ({{ $statFilter['cho_duyet'] }})
        </a>
        <a href="{{ route('truongbomon.phe_duyet_detai.giang_vien', ['maGV' => $giangVien->MaGV, 'hocKy' => $selectedHocKy, 'trang_thai' => 'yeu_cau_sua']) }}" 
           class="nav-filter-pill {{ $selectedStatus === 'yeu_cau_sua' ? 'bg-info text-white shadow-xs' : 'bg-white text-secondary border' }}">
            <i class="fa-solid fa-pen-ruler me-1"></i> Yêu cầu chỉnh sửa ({{ $statFilter['yeu_cau_sua'] }})
        </a>
        <a href="{{ route('truongbomon.phe_duyet_detai.giang_vien', ['maGV' => $giangVien->MaGV, 'hocKy' => $selectedHocKy, 'trang_thai' => 'da_duyet']) }}" 
           class="nav-filter-pill {{ $selectedStatus === 'da_duyet' ? 'bg-success text-white shadow-xs' : 'bg-white text-secondary border' }}">
            <i class="fa-solid fa-circle-check me-1"></i> Đã phê duyệt ({{ $statFilter['da_duyet'] }})
        </a>
        <a href="{{ route('truongbomon.phe_duyet_detai.giang_vien', ['maGV' => $giangVien->MaGV, 'hocKy' => $selectedHocKy, 'trang_thai' => 'tu_choi']) }}" 
           class="nav-filter-pill {{ $selectedStatus === 'tu_choi' ? 'bg-danger text-white shadow-xs' : 'bg-white text-secondary border' }}">
            <i class="fa-solid fa-ban me-1"></i> Từ chối ({{ $statFilter['tu_choi'] }})
        </a>
    </div>

    <!-- Bảng Danh Sách Đề Tài Của Giảng Viên -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark fs-6">
                <i class="fa-solid fa-book-bookmark text-primary me-2"></i> Danh Sách Đề Tài Đề Xuất ({{ $detais->count() }} đề tài)
            </span>
            <span class="small text-muted">
                Ưu tiên hiển thị: <strong>Chờ duyệt lên đầu</strong> &rarr; Ngày đề xuất mới nhất
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: #f8fafc; color: #334155; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th class="ps-4 py-3 text-center" width="5%">STT</th>
                        <th class="py-3" width="13%">MÃ ĐỀ TÀI</th>
                        <th class="py-3" width="34%">TÊN ĐỀ TÀI &amp; MÔN HỌC</th>
                        <th class="py-3" width="16%">LĨNH VỰC</th>
                        <th class="py-3 text-center" width="12%">NGÀY ĐỀ XUẤT</th>
                        <th class="py-3 text-center" width="12%">TRẠNG THÁI</th>
                        <th class="pe-4 py-3 text-center" width="8%">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($detais as $idx => $dt)
                    @php
                        $statusClass = 'badge-status-choduyet';
                        $statusLabel = 'Chờ duyệt';
                        $iconStatus = 'fa-clock';

                        if (in_array($dt->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt'])) {
                            $statusClass = 'badge-status-choduyet';
                            $statusLabel = 'Chờ duyệt';
                            $iconStatus = 'fa-clock';
                        } elseif (in_array($dt->TrangThai, ['Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương'])) {
                            $statusClass = 'badge-status-yeucausua';
                            $statusLabel = 'Yêu cầu chỉnh sửa';
                            $iconStatus = 'fa-pen-ruler';
                        } elseif (in_array($dt->TrangThai, ['Chờ duyệt cấp Khoa', 'Đã phê duyệt', 'Trưởng khoa đã duyệt', 'Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã công bố', 'Đã đăng ký', 'Hoàn thành'])) {
                            $statusClass = 'badge-status-daduyet';
                            $statusLabel = 'Đã phê duyệt';
                            $iconStatus = 'fa-circle-check';
                        } elseif (in_array($dt->TrangThai, ['Từ chối', 'Không đạt phản biện'])) {
                            $statusClass = 'badge-status-tuchoi';
                            $statusLabel = 'Từ chối';
                            $iconStatus = 'fa-ban';
                        }
                    @endphp
                    <tr class="{{ in_array($dt->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt']) ? 'table-warning bg-opacity-10' : '' }}">
                        <td class="ps-4 py-3 text-center text-muted fw-semibold">{{ $idx + 1 }}</td>
                        <td class="py-3">
                            <span class="badge-code">{{ $dt->MaDeTai }}</span>
                        </td>
                        <td class="py-3">
                            <div>
                                <a href="{{ route('truongbomon.phe_duyet_detai.show', ['maDeTai' => $dt->MaDeTai, 'hocKy' => $selectedHocKy]) }}" 
                                   class="fw-bold text-dark text-decoration-none hover-primary d-block">
                                    {{ $dt->TenDeTai }}
                                </a>
                                <div class="small text-muted mt-1 d-flex align-items-center gap-2">
                                    <span><i class="fa-solid fa-graduation-cap text-secondary me-1"></i>{{ $dt->hocPhanRef->TenHocPhan ?? ($dt->HocPhan ?? 'Khóa luận tốt nghiệp') }}</span>
                                    <span>&bull;</span>
                                    <span>Tối đa: <strong>{{ $dt->SoLuongSinhVienToiDa ?? 3 }} SV</strong></span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-light text-secondary border fw-medium rounded-pill px-2.5 py-1">
                                {{ $dt->LinhVuc ?? 'Công Nghệ Thông Tin' }}
                            </span>
                        </td>
                        <td class="py-3 text-center text-muted small">
                            {{ $dt->NgayDeXuat ? \Carbon\Carbon::parse($dt->NgayDeXuat)->format('d/m/Y') : ($dt->created_at ? $dt->created_at->format('d/m/Y') : '—') }}
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge rounded-pill fw-semibold px-2.5 py-1.5 {{ $statusClass }}">
                                <i class="fa-solid {{ $iconStatus }} me-1"></i> {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="pe-4 py-3 text-center">
                            <a href="{{ route('truongbomon.phe_duyet_detai.show', ['maDeTai' => $dt->MaDeTai, 'hocKy' => $selectedHocKy]) }}" 
                               class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold text-nowrap">
                                <i class="fa-solid fa-eye me-1"></i> Xem chi tiết
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="py-4">
                                <i class="fa-regular fa-folder-open text-muted mb-3" style="font-size: 2.5rem;"></i>
                                <h6 class="fw-bold text-secondary">Giảng viên chưa đề xuất đề tài nào trong học kỳ này</h6>
                                <p class="small text-muted mb-0">Học kỳ: <strong>{{ $selectedHocKy }}</strong> &bull; Vui lòng kiểm tra lại bộ lọc hoặc chọn học kỳ khác.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
            <span class="small text-muted">
                Tổng cộng <strong>{{ $detais->count() }}</strong> đề tài hiển thị cho giảng viên <strong>{{ $giangVien->HoTen }}</strong> trong học kỳ <strong>{{ $selectedHocKy }}</strong>.
            </span>
            <a href="{{ route('truongbomon.phe_duyet_detai.index', ['hocKy' => $selectedHocKy]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Trở về danh sách giảng viên
            </a>
        </div>
    </div>
</div>
@endsection
