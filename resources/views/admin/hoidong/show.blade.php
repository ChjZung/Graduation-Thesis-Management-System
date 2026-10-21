@extends('layouts.admin')

@section('page_title', 'Chi Tiết Hội Đồng Bảo Vệ — ' . $hoiDong->TenHoiDong)

@section('content')
<div class="container-fluid px-0">

    <!-- Header Section -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-light text-primary border font-monospace fw-bold">{{ $hoiDong->MaHoiDong }}</span>
                @if($hoiDong->TrangThaiCongBo === 'Đã công bố')
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-semibold">
                        <i class="fa-solid fa-circle-check me-1"></i> Đã công bố chính thức
                    </span>
                @elseif($hoiDong->TrangThaiCongBo === 'Đã thu hồi')
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-semibold">
                        <i class="fa-solid fa-arrow-rotate-left me-1"></i> Đã thu hồi công bố
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 fw-semibold">
                        <i class="fa-solid fa-pen-ruler me-1"></i> Bản nháp (Chưa công bố)
                    </span>
                @endif
                <span class="badge bg-light text-dark border">{{ $hoiDong->hocKy?->TenHocKy ?? $hoiDong->MaHocKy }} ({{ $hoiDong->DotBaoVe ?? 'Đợt 1' }})</span>
            </div>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-landmark text-primary me-2"></i>
                <span>{{ $hoiDong->TenHoiDong }}</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('admin.hoidong.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs d-inline-flex align-items-center">
                <i class="fa-solid fa-arrow-left me-2"></i> Danh Sách Hội Đồng
            </a>

            <!-- Nút xem trước PDF -->
            <a href="{{ route('admin.hoidong.previewPdf', $hoiDong->MaHoiDong) }}" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-xs d-inline-flex align-items-center">
                <i class="fa-solid fa-file-pdf me-2"></i> Xem Văn Bản (PDF)
            </a>

            <!-- Nút tải PDF -->
            <a href="{{ route('admin.hoidong.downloadPdf', $hoiDong->MaHoiDong) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs fw-semibold d-inline-flex align-items-center">
                <i class="fa-solid fa-download me-2"></i> Tải Quyết Định PDF
            </a>
        </div>
    </div>

    <!-- Thông báo phân quyền Giáo vụ -->
    <div class="alert alert-info py-2 px-3 small rounded-3 mb-4 border-0 bg-info-subtle text-info-emphasis d-flex align-items-center">
        <i class="fa-solid fa-circle-info fs-5 me-2"></i>
        <div>
            <strong>Chế độ Giáo vụ:</strong> Bạn có quyền tra cứu thông tin chi tiết, xem danh sách sinh viên, thành viên hội đồng và tải văn bản quyết định công bố. Việc thành lập, phân công và công bố hội đồng thuộc thẩm quyền của <strong>Trưởng khoa</strong>.
        </div>
    </div>

    <!-- 4 KPI Cards thống kê nhanh của Hội đồng -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Trạng Thái HĐ</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #e0f2fe; color: #0284c7;">
                        <i class="fa-solid fa-circle-dot fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-5 fw-bold text-dark lh-1">{{ $hoiDong->TrangThai }}</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Thành Viên HĐ</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #dcfce7; color: #16a34a;">
                        <i class="fa-solid fa-users fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-success lh-1">{{ $hoiDong->thanhViens->count() }}</span>
                    <span class="small text-muted fw-medium">giảng viên</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Đề Tài / Nhóm SV</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #fef3c7; color: #d97706;">
                        <i class="fa-solid fa-graduation-cap fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-3 fw-bold text-dark lh-1">{{ $hoiDong->hoSoBaoVes->count() }}</span>
                    <span class="small text-muted fw-medium">đề tài bảo vệ</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-light-subtle rounded-3 shadow-sm bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.75rem; letter-spacing: 0.5px;">Địa Điểm / Phòng</span>
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #ede9fe; color: #7c3aed;">
                        <i class="fa-solid fa-location-dot fs-5"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                    <span class="fs-6 fw-bold text-primary lh-1 text-truncate" title="{{ $hoiDong->DiaDiem ?? 'Chưa xếp' }}">{{ $hoiDong->DiaDiem ?? 'Chưa xếp' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Cột trái: Thông tin Hội đồng & Thành viên -->
        <div class="col-lg-5">
            <!-- Card 1: Thông tin cơ bản & Quyết định -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <span class="fw-bold text-dark"><i class="fa-solid fa-circle-info me-2 text-primary"></i> Thông Tin Hội Đồng &amp; Quyết Định</span>
                </div>
                <div class="card-body p-3">
                    <table class="table table-sm table-borderless mb-0 align-middle">
                        <tr>
                            <td class="text-muted fw-semibold" width="35%">Mã HĐ:</td>
                            <td><span class="badge-code">{{ $hoiDong->MaHoiDong }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Học kỳ / Đợt:</td>
                            <td><strong>{{ $hoiDong->hocKy?->TenHocKy ?? $hoiDong->MaHocKy }}</strong> ({{ $hoiDong->DotBaoVe ?? 'Đợt 1' }})</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Ngày bảo vệ:</td>
                            <td>
                                <div><i class="fa-regular fa-calendar text-primary me-2"></i>{{ $hoiDong->NgayBaoVe ? \Carbon\Carbon::parse($hoiDong->NgayBaoVe)->format('d/m/Y') : (\Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('d/m/Y')) }}</div>
                                <div class="small text-muted mt-1">
                                    <i class="fa-regular fa-clock me-1"></i>
                                    {{ \Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('H:i') }} đến {{ \Carbon\Carbon::parse($hoiDong->ThoiGianKetThuc)->format('H:i') }}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Địa điểm:</td>
                            <td><strong class="text-danger"><i class="fa-solid fa-location-dot me-1"></i>{{ $hoiDong->DiaDiem ?? 'Chưa xác định' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Số Quyết định:</td>
                            <td><span class="badge bg-light text-dark border">{{ $hoiDong->SoQuyetDinh ?? 'Chưa có' }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Người ký QĐ:</td>
                            <td>{{ $hoiDong->NguoiKy ?? 'Hiệu trưởng' }} ({{ $hoiDong->ChucVuNguoiKy ?? 'HIỆU TRƯỞNG' }})</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Trạng thái công bố:</td>
                            <td>
                                @if($hoiDong->TrangThaiCongBo === 'Đã công bố')
                                    <span class="text-success fw-bold"><i class="fa-solid fa-circle-check me-1"></i> Đã công bố</span>
                                @elseif($hoiDong->TrangThaiCongBo === 'Đã thu hồi')
                                    <span class="text-danger fw-bold"><i class="fa-solid fa-arrow-rotate-left me-1"></i> Đã thu hồi</span>
                                @else
                                    <span class="text-warning fw-bold"><i class="fa-solid fa-pen-ruler me-1"></i> Bản nháp</span>
                                @endif
                            </td>
                        </tr>
                        @if($hoiDong->GhiChu)
                        <tr>
                            <td class="text-muted fw-semibold">Ghi chú:</td>
                            <td class="small text-muted">{{ $hoiDong->GhiChu }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Card 2: Thành viên Hội đồng (Read-only) -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <span class="fw-bold text-dark"><i class="fa-solid fa-users me-2 text-primary"></i> Thành Viên Hội Đồng ({{ $hoiDong->thanhViens->count() }})</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 py-2">GIẢNG VIÊN</th>
                                    <th class="pe-3 py-2 text-center" width="40%">VAI TRÒ</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse($hoiDong->thanhViens as $tv)
                                <tr>
                                    <td class="ps-3 py-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; font-weight: 700; font-size: 0.85rem;">
                                                {{ mb_substr($tv->giangVien->HoTen ?? 'GV', 0, 1) }}
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <span class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $tv->giangVien->HoTen ?? '—' }}</span>
                                                    <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.68rem;">{{ $tv->MaGV }}</span>
                                                </div>
                                                <div class="small text-muted d-flex align-items-center gap-1 flex-wrap mt-1" style="font-size: 0.75rem;">
                                                    <span class="text-secondary fw-semibold">{{ $tv->giangVien->HocVi ?? ($tv->giangVien->HocHam ?? 'Giảng viên') }}</span>
                                                    <span>&bull;</span>
                                                    <span>{{ $tv->giangVien->boMon->TenBoMon ?? 'Khoa CNTT' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="pe-3 text-center py-2">
                                        @php
                                            $badgeStyle = match($tv->VaiTro) {
                                                'Chủ tịch' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                                'Thư ký', 'Ủy viên thư ký' => 'bg-info-subtle text-info border border-info-subtle',
                                                'Phản biện', 'Ủy viên phản biện' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                                default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeStyle }} rounded-pill px-3 py-1" style="font-size: 0.75rem;">{{ $tv->VaiTro }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">Chưa có thành viên nào.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Danh sách đề tài & nhóm sinh viên bảo vệ (Read-only) -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Đề Tài &amp; Sinh Viên Bảo Vệ Thuộc Hội Đồng ({{ $hoiDong->hoSoBaoVes->count() }} nhóm)</span>
                    <span class="small text-muted">
                        <i class="fa-solid fa-circle-info text-primary me-1"></i> Tên đề tài &amp; Cán bộ hướng dẫn gộp chung ô theo nhóm
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0" style="border-color: #e2e8f0;">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 py-3 text-center" style="width: 45px;">STT</th>
                                    <th class="py-3 text-center" style="width: 105px;">MSSV</th>
                                    <th class="py-3" style="width: 160px;">HỌ VÀ TÊN</th>
                                    <th class="py-3 text-center" style="width: 85px;">LỚP</th>
                                    <th class="py-3">TÊN ĐỀ TÀI KHÓA LUẬN</th>
                                    <th class="py-3" style="width: 175px;">CÁN BỘ HƯỚNG DẪN</th>
                                    <th class="pe-3 py-3 text-center" style="width: 85px;">TURNITIN</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @php $currentStt = 1; @endphp
                                @forelse($hoiDong->hoSoBaoVes as $hoSo)
                                @php
                                    $nhom = $hoSo->nhom;
                                    $deTai = $nhom?->deTai;
                                    $thanhViens = $nhom?->thanhVienNhoms ?? collect();
                                    $soSV = max($thanhViens->count(), 1);
                                @endphp
                                @if($thanhViens->isNotEmpty())
                                    @foreach($thanhViens as $svIndex => $tvn)
                                    <tr>
                                        <td class="ps-3 py-2 text-center text-muted small" style="vertical-align: middle;">{{ $currentStt++ }}</td>
                                        <td class="py-2 text-center font-monospace fw-bold text-dark" style="vertical-align: middle;">
                                            {{ $tvn->sinhVien?->MaSV ?? '—' }}
                                        </td>
                                        <td class="py-2" style="vertical-align: middle;">
                                            <div class="fw-semibold text-dark">{{ $tvn->sinhVien?->HoTen ?? '—' }}</div>
                                            @if($tvn->VaiTroTrongNhom === 'Trưởng nhóm' || $nhom?->MaTruongNhom === $tvn->MaSV)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.65rem;">Trưởng nhóm</span>
                                            @endif
                                        </td>
                                        <td class="py-2 text-center text-muted small" style="vertical-align: middle;">
                                            {{ $tvn->sinhVien?->lop?->TenLop ?? ($tvn->sinhVien?->MaLop ?? '—') }}
                                        </td>

                                        {{-- Tên đề tài & Cán bộ hướng dẫn: Hiển thị CHUNG Ô TRÊN BẢNG nếu chung 1 nhóm --}}
                                        @if($svIndex === 0)
                                            <td rowspan="{{ $soSV }}" class="py-3 bg-white" style="vertical-align: middle;">
                                                <div class="fw-bold text-primary">{{ $deTai?->TenDeTai ?? 'Chưa có tên đề tài' }}</div>
                                                <div class="d-flex flex-wrap align-items-center gap-1 mt-1 small text-muted">
                                                    <span class="badge bg-light text-dark border">Nhóm {{ $nhom?->MaNhom }}</span>
                                                    @if($deTai?->MaDeTai)
                                                        <span class="badge bg-secondary-subtle text-secondary border font-monospace">{{ $deTai->MaDeTai }}</span>
                                                    @endif
                                                    <span class="badge bg-info-subtle text-info border">{{ $soSV }} SV</span>
                                                </div>
                                            </td>
                                            <td rowspan="{{ $soSV }}" class="py-3 bg-white" style="vertical-align: middle;">
                                                <div class="fw-semibold text-dark">{{ $deTai?->giangVien?->HoTen ?? 'Chưa phân công' }}</div>
                                                <div class="small text-muted">{{ $deTai?->giangVien?->boMon?->TenBoMon ?? 'Bộ môn' }}</div>
                                            </td>
                                            <td rowspan="{{ $soSV }}" class="pe-3 text-center py-3 bg-white" style="vertical-align: middle;">
                                                @php $pct = (float)$hoSo->TyLeTrungLap; @endphp
                                                <span class="fw-bold {{ $pct <= 20 ? 'text-success' : 'text-danger' }}">{{ number_format($pct, 2) }}%</span>
                                                @if($hoSo->MinhChungDaoVan)
                                                <div>
                                                    <a href="{{ asset('storage/' . $hoSo->MinhChungDaoVan) }}" target="_blank" class="badge bg-danger-subtle text-danger border border-danger-subtle text-decoration-none" style="font-size: 0.68rem;">
                                                        Minh chứng
                                                    </a>
                                                </div>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="ps-3 py-2 text-center text-muted small">{{ $currentStt++ }}</td>
                                        <td class="py-2 text-center text-muted">—</td>
                                        <td class="py-2 text-muted fst-italic">Chưa có sinh viên</td>
                                        <td class="py-2 text-center text-muted">—</td>
                                        <td class="py-3 bg-white">
                                            <div class="fw-bold text-primary">{{ $deTai?->TenDeTai ?? 'Chưa có tên đề tài' }}</div>
                                            <div class="small text-muted mt-1">
                                                <span class="badge bg-light text-dark border">Nhóm {{ $nhom?->MaNhom }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 bg-white">
                                            <div class="fw-semibold text-dark">{{ $deTai?->giangVien?->HoTen ?? 'Chưa phân công' }}</div>
                                        </td>
                                        <td class="pe-3 text-center py-3 bg-white">—</td>
                                    </tr>
                                @endif
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-folder-open fa-2x mb-2 d-block opacity-50 me-2"></i>
                                        Chưa có nhóm sinh viên nào được phân công vào Hội đồng này.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
