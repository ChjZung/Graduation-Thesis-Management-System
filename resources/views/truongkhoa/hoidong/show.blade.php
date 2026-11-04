@extends('layouts.truongkhoa')

@section('page_title', 'Chi Tiết Hội Đồng — ' . $hoiDong->TenHoiDong)

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
            <a href="{{ route('truongkhoa.hoidong.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs d-inline-flex align-items-center">
                <i class="fa-solid fa-arrow-left me-2"></i> Danh Sách
            </a>


        </div>
    </div>

    <!-- BANNER CÔNG BỐ DANH SÁCH HỘI ĐỒNG & NHÓM BẢO VỆ -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 {{ $hoiDong->TrangThaiCongBo === 'Đã công bố' ? 'border-start border-success border-4' : 'border-start border-warning border-4' }} bg-white">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 {{ $hoiDong->TrangThaiCongBo === 'Đã công bố' ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning' }}" style="width: 52px; height: 52px; font-size: 22px;">
                        <i class="fa-solid {{ $hoiDong->TrangThaiCongBo === 'Đã công bố' ? 'fa-bullhorn' : 'fa-clipboard-check' }}"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h5 class="fw-bold mb-0 text-dark">Công Bố Danh Sách Hội Đồng, Đề Tài &amp; Nhóm Sinh Viên Bảo Vệ</h5>
                            @if($hoiDong->TrangThaiCongBo === 'Đã công bố')
                                <span class="badge bg-success text-white px-3 py-1 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i> ĐÃ CÔNG BỐ CHÍNH THỨC</span>
                            @elseif($hoiDong->TrangThaiCongBo === 'Đã thu hồi')
                                <span class="badge bg-danger text-white px-3 py-1 rounded-pill"><i class="fa-solid fa-arrow-rotate-left me-1"></i> ĐÃ THU HỒI CÔNG BỐ</span>
                            @else
                                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill"><i class="fa-solid fa-pen-ruler me-1"></i> BẢN NHÁP (CHƯA CÔNG BỐ)</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-0 lh-base">
                            @if($hoiDong->TrangThaiCongBo === 'Đã công bố')
                                Hội đồng đã công bố chính thức vào lúc <strong>{{ $hoiDong->NgayCongBo ? \Carbon\Carbon::parse($hoiDong->NgayCongBo)->format('H:i \n\gà\y d/m/Y') : 'vừa qua' }}</strong> bởi <strong>{{ $hoiDong->NguoiCongBo ?? 'Trưởng khoa' }}</strong>.
                                Tệp PDF đã phát hành được lưu trong thông báo công bố tương ứng.
                            @else
                                Hiện tại Hội đồng đang ở chế độ <strong>dự thảo (chưa công bố)</strong> gồm <strong>{{ $hoiDong->thanhViens->count() }} thành viên</strong> và <strong>{{ $hoiDong->hoSoBaoVes->count() }} nhóm đề tài</strong>.
                                Vui lòng kiểm tra kỹ danh sách thành viên và đề tài bảo vệ bên dưới, sau khi tạo đủ hội đồng, chọn <strong>"Xem trước quyết định"</strong> trên danh sách, xem trước quyết định rồi công bố toàn bộ hội đồng cùng học kỳ và đợt bảo vệ.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap flex-shrink-0">
                    <a href="{{ route('truongkhoa.hoidong.previewPdf', $hoiDong->MaHoiDong) }}" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold shadow-xs">
                        <i class="fa-solid fa-file-pdf me-1"></i> Xem quyết định chung của đợt
                    </a>

                </div>
            </div>
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
                    <span class="fs-5 fw-bold text-dark lh-1">{{ $hoiDong->ten_trang_thai_thoi_gian }}</span>
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
        <!-- Cột trái: Thông tin Hội đồng, Quyết định & Thành viên -->
        <div class="col-lg-5">
            <!-- Card 1: Thông tin cơ bản & Quyết định -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="fa-solid fa-circle-info me-2 text-primary"></i> Thông Tin Hội Đồng &amp; Quyết Định</span>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-2" data-bs-toggle="modal" data-bs-target="#modalSuaQuyetDinh" title="Sửa thông tin Quyết định">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                </div>
                <div class="card-body p-3">
                    <table class="table table-sm table-borderless mb-3 align-middle">
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
                        @if($hoiDong->GhiChu)
                        <tr>
                            <td class="text-muted fw-semibold">Ghi chú:</td>
                            <td class="small text-muted">{{ $hoiDong->GhiChu }}</td>
                        </tr>
                        @endif
                    </table>

                    <!-- Cập nhật trạng thái Hội đồng -->
                    <form action="{{ route('truongkhoa.hoidong.updateTrangThai', $hoiDong->MaHoiDong) }}" method="POST" class="d-flex gap-2 pt-2 border-top">
                        @csrf
                        <select name="TrangThai" class="form-select form-select-sm">
                            <option value="Chưa diễn ra" {{ $hoiDong->TrangThai === 'Chưa diễn ra' ? 'selected' : '' }}>Chưa diễn ra</option>
                            <option value="Đang diễn ra" {{ $hoiDong->TrangThai === 'Đang diễn ra' ? 'selected' : '' }}>Đang diễn ra</option>
                            <option value="Đã kết thúc" {{ $hoiDong->TrangThai === 'Đã kết thúc' ? 'selected' : '' }}>Đã kết thúc</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 flex-shrink-0 fw-semibold">Cập nhật</button>
                    </form>
                </div>
            </div>

            <!-- Card Quản lý thời hạn chỉnh sửa điểm & Kiểm toán -->
            @php
                $sysNow = \App\Models\HoiDong::getSystemNow();
                $hasStart = !empty($hoiDong->ThoiGianBatDauSuaDiem);
                $hasEnd = !empty($hoiDong->ThoiGianKetThucSuaDiem);
                $isPub = ($hoiDong->hoSoBaoVes->isNotEmpty() && $hoiDong->hoSoBaoVes->every(fn($h) => in_array($h->TrangThaiKetQua, ['DaCongBo', 'CoYeuCauChinhSua', 'DaHoanThanh'])));
                $isUnlocked = (bool)$hoiDong->TrangThaiMoKhoaSuaDiem;

                if ($isPub && !$isUnlocked) {
                    $hdStatusBadge = '<span class="badge bg-danger text-white rounded-pill px-3 py-1"><i class="fa-solid fa-lock me-1"></i> Đã khóa (Điểm đã công bố)</span>';
                    $hdStatusNote = 'Toàn bộ điểm số của 3 giảng viên đã được công bố chính thức và tự động khóa chỉnh sửa.';
                } elseif ($isUnlocked) {
                    $hdStatusBadge = '<span class="badge bg-warning text-dark rounded-pill px-3 py-1"><i class="fa-solid fa-lock-open me-1"></i> Đang mở khóa xử lý sai sót</span>';
                    $hdStatusNote = 'Quản trị viên đã mở khóa sửa điểm: ' . ($hoiDong->LyDoMoKhoaSuaDiem ?? 'Xử lý sai sót.');
                } elseif ($hasStart && $sysNow->lt($hoiDong->ThoiGianBatDauSuaDiem)) {
                    $hdStatusBadge = '<span class="badge bg-secondary text-white rounded-pill px-3 py-1"><i class="fa-solid fa-hourglass-start me-1"></i> Chưa đến hạn sửa</span>';
                    $hdStatusNote = 'Thời gian sửa điểm bắt đầu từ: ' . $hoiDong->ThoiGianBatDauSuaDiem->format('H:i d/m/Y');
                } elseif ($hasEnd && $sysNow->gt($hoiDong->ThoiGianKetThucSuaDiem)) {
                    $hdStatusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1"><i class="fa-solid fa-ban me-1"></i> Hết hạn chỉnh sửa (Tự động khóa)</span>';
                    $hdStatusNote = 'Hệ thống đã tự động khóa quyền sửa điểm vào lúc: ' . $hoiDong->ThoiGianKetThucSuaDiem->format('H:i d/m/Y');
                } else {
                    $hdStatusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1"><i class="fa-solid fa-circle-check me-1"></i> Đang trong thời hạn cho phép sửa</span>';
                    $hdStatusNote = $hasEnd ? ('Hạn chót chỉnh sửa đến: ' . $hoiDong->ThoiGianKetThucSuaDiem->format('H:i d/m/Y')) : 'Không giới hạn thời hạn kết thúc.';
                }
            @endphp
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">
                        <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Quản Lý Thời Hạn Sửa Điểm &amp; Audit Log
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalLichSuSuaDiem" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-receipt me-1"></i> Xem Lịch Sử ({{ $lichSuSuaDiems->count() }})
                    </button>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted fw-semibold">Trạng thái quyền sửa điểm:</span>
                            {!! $hdStatusBadge !!}
                        </div>
                        <div class="small text-muted fst-italic">{{ $hdStatusNote }}</div>
                    </div>

                    <table class="table table-sm table-borderless mb-3 align-middle bg-light rounded-3 p-2">
                        <tr>
                            <td class="text-muted fw-semibold ps-2" width="45%">Bắt đầu cho sửa:</td>
                            <td>
                                <strong>{{ $hoiDong->ThoiGianBatDauSuaDiem ? $hoiDong->ThoiGianBatDauSuaDiem->format('H:i d/m/Y') : 'Không giới hạn' }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold ps-2">Hạn chót kết thúc:</td>
                            <td>
                                <strong class="{{ ($hasEnd && $sysNow->gt($hoiDong->ThoiGianKetThucSuaDiem)) ? 'text-danger' : 'text-primary' }}">
                                    {{ $hoiDong->ThoiGianKetThucSuaDiem ? $hoiDong->ThoiGianKetThucSuaDiem->format('H:i d/m/Y') : 'Không giới hạn' }}
                                </strong>
                            </td>
                        </tr>
                        @if($hoiDong->TrangThaiMoKhoaSuaDiem)
                        <tr>
                            <td class="text-muted fw-semibold ps-2">Lý do mở khóa:</td>
                            <td class="text-danger small fw-semibold">{{ $hoiDong->LyDoMoKhoaSuaDiem }}</td>
                        </tr>
                        @endif
                    </table>

                    <div class="d-flex gap-2 flex-wrap pt-2 border-top">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-xs flex-grow-1" data-bs-toggle="modal" data-bs-target="#modalCauHinhSuaDiem">
                            <i class="fa-regular fa-clock me-1"></i> Thiết Lập Thời Hạn
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-semibold shadow-xs flex-grow-1 text-dark" data-bs-toggle="modal" data-bs-target="#modalMoKhoaSuaDiem">
                            <i class="fa-solid fa-lock-open me-1"></i> Mở Khóa Xử Lý Sai Sót
                        </button>
                    </div>
                </div>
            </div>

            <!-- Card 2: Thành viên Hội đồng -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="fa-solid fa-users me-2 text-primary"></i> Thành Viên Hội Đồng ({{ $hoiDong->thanhViens->count() }})</span>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalThemThanhVien" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-user-plus me-2"></i> Thêm TV
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3 py-2">GIẢNG VIÊN</th>
                                    <th class="py-2 text-center" width="35%">VAI TRÒ</th>
                                    <th class="pe-3 py-2 text-center" width="15%">THAO TÁC</th>
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
                                                <div class="small text-muted d-flex align-items-center gap-1 flex-wrap" style="font-size: 0.75rem;">
                                                    <span class="text-secondary fw-semibold">{{ $tv->giangVien->HocVi ?? ($tv->giangVien->HocHam ?? 'Giảng viên') }}</span>
                                                    <span>&bull;</span>
                                                    <span>{{ $tv->giangVien->boMon->TenBoMon ?? 'Khoa CNTT' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center py-2">
                                        <span class="badge {{ $tv->badge_class }} rounded-pill px-3 py-1" style="font-size: 0.75rem;">{{ $tv->short_vai_tro }}</span>
                                    </td>
                                    <td class="pe-3 text-center py-2">
                                        <div class="d-flex justify-content-center gap-1">
                                            <!-- Nút xem chi tiết giảng viên -->
                                            <button type="button" class="btn btn-sm btn-light border btn-action-circle d-inline-flex align-items-center text-info" 
                                                    title="Xem chi tiết giảng viên" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalChiTietGV{{ $tv->MaGV }}">
                                                <i class="fa-solid fa-id-card" style="font-size: 0.75rem;"></i>
                                            </button>

                                            <!-- Nút sửa vai trò -->
                                            <button type="button" class="btn btn-sm btn-light border btn-action-circle d-inline-flex align-items-center" 
                                                    title="Đổi vai trò" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalDoiVaiTro{{ $tv->MaGV }}">
                                                <i class="fa-solid fa-pen-to-square text-primary" style="font-size: 0.75rem;"></i>
                                            </button>

                                            <!-- Nút xóa thành viên -->
                                            <form action="{{ route('truongkhoa.hoidong.xoaThanhVien', [$hoiDong->MaHoiDong, $tv->MaGV]) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light border text-danger btn-action-circle d-inline-flex align-items-center" 
                                                        title="Xóa thành viên này" 
                                                        onclick="return confirm('Bạn có chắc chắn muốn xóa giảng viên {{ $tv->giangVien->HoTen ?? $tv->MaGV }} khỏi Hội đồng?');">
                                                    <i class="fa-solid fa-trash" style="font-size: 0.75rem;"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Modal Chi tiết Giảng viên -->
                                        <div class="modal fade text-start" id="modalChiTietGV{{ $tv->MaGV }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header py-3" style="background: linear-gradient(135deg, #003859 0%, #0072ce 100%); color: #fff;">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center shadow-xs" style="width: 44px; height: 44px; font-weight: 700; font-size: 1.1rem;">
                                                                {{ mb_substr($tv->giangVien->HoTen ?? 'GV', 0, 1) }}
                                                            </div>
                                                            <div>
                                                                <h6 class="modal-title fw-bold text-white mb-0">{{ $tv->giangVien->HoTen ?? $tv->MaGV }}</h6>
                                                                <span class="small text-white-50">{{ $tv->giangVien->HocVi ?? ($tv->giangVien->HocHam ?? 'Giảng viên') }} &bull; {{ $tv->giangVien->boMon->TenBoMon ?? 'Bộ môn' }}</span>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3 p-3 bg-light rounded-3 border">
                                                            <div class="row g-2 small">
                                                                <div class="col-6">
                                                                    <span class="text-muted d-block">Mã giảng viên:</span>
                                                                    <strong class="font-monospace text-dark">{{ $tv->MaGV }}</strong>
                                                                </div>
                                                                <div class="col-6">
                                                                    <span class="text-muted d-block">Vai trò trong Hội đồng:</span>
                                                                    <span class="badge {{ $tv->badge_class }} rounded-pill px-2 py-1">{{ $tv->VaiTro }}</span>
                                                                </div>
                                                                <div class="col-6 mt-2">
                                                                    <span class="text-muted d-block">Trạng thái công tác:</span>
                                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">{{ $tv->giangVien->TrangThai ?? 'Đang công tác' }}</span>
                                                                </div>
                                                                <div class="col-6 mt-2">
                                                                    <span class="text-muted d-block">Giới tính / Ngày sinh:</span>
                                                                    <strong class="text-dark">{{ $tv->giangVien->GioiTinh ?? '—' }} &bull; {{ $tv->giangVien->NgaySinh ? \Carbon\Carbon::parse($tv->giangVien->NgaySinh)->format('d/m/Y') : '—' }}</strong>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <h6 class="fw-bold small text-dark mb-2"><i class="fa-solid fa-address-book text-primary me-2"></i>Thông Tin Liên Hệ & Đơn Vị:</h6>
                                                        <ul class="list-group list-group-flush border rounded-3 mb-3 small">
                                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                                <span class="text-muted"><i class="fa-regular fa-envelope me-2 text-primary"></i>Email:</span>
                                                                <a href="mailto:{{ $tv->giangVien->Email }}" class="fw-semibold text-primary text-decoration-none">{{ $tv->giangVien->Email ?? 'Chưa cập nhật' }}</a>
                                                            </li>
                                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                                <span class="text-muted"><i class="fa-solid fa-phone me-2 text-success"></i>Số điện thoại:</span>
                                                                <a href="tel:{{ $tv->giangVien->SoDienThoai }}" class="fw-semibold text-dark text-decoration-none">{{ $tv->giangVien->SoDienThoai ?? 'Chưa cập nhật' }}</a>
                                                            </li>
                                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                                <span class="text-muted"><i class="fa-solid fa-sitemap me-2 text-info"></i>Bộ môn:</span>
                                                                <strong class="text-dark">{{ $tv->giangVien->boMon->TenBoMon ?? '—' }}</strong>
                                                            </li>
                                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                                <span class="text-muted"><i class="fa-solid fa-landmark me-2 text-secondary"></i>Khoa:</span>
                                                                <strong class="text-dark">{{ $tv->giangVien->boMon->khoa->TenKhoa ?? $khoa->TenKhoa ?? 'Khoa CNTT' }}</strong>
                                                            </li>
                                                        </ul>

                                                        <h6 class="fw-bold small text-dark mb-2"><i class="fa-solid fa-calendar-check text-warning me-2"></i>Lịch Làm Việc Hội Đồng:</h6>
                                                        <div class="p-3 bg-light-subtle border rounded-3 small">
                                                            <div><strong>Hội đồng:</strong> {{ $hoiDong->TenHoiDong }} ({{ $hoiDong->MaHoiDong }})</div>
                                                            <div class="mt-1">
                                                                <strong>Thời gian:</strong> 
                                                                {{ $hoiDong->NgayBaoVe ? \Carbon\Carbon::parse($hoiDong->NgayBaoVe)->format('d/m/Y') : ($hoiDong->ThoiGianBatDau ? \Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('d/m/Y') : '—') }}
                                                                @if($hoiDong->ThoiGianBatDau && $hoiDong->ThoiGianKetThuc)
                                                                    | {{ \Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('H:i') }} – {{ \Carbon\Carbon::parse($hoiDong->ThoiGianKetThuc)->format('H:i') }}
                                                                @endif
                                                            </div>
                                                            <div class="mt-1"><strong>Phòng bảo vệ:</strong> <span class="text-primary fw-bold">{{ $hoiDong->DiaDiem ?? 'Chưa xếp phòng' }}</span></div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer py-2 bg-light">
                                                        <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Đổi vai trò cho từng thành viên -->
                                        <div class="modal fade text-start" id="modalDoiVaiTro{{ $tv->MaGV }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header py-2 bg-light">
                                                        <h6 class="modal-title fw-bold text-dark"><i class="fa-solid fa-user-gear me-2 text-primary"></i> Đổi Vai Trò</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('truongkhoa.hoidong.doiVaiTroThanhVien', [$hoiDong->MaHoiDong, $tv->MaGV]) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body p-3">
                                                            <div class="mb-2">
                                                                <small class="text-muted d-block">Giảng viên:</small>
                                                                <span class="fw-bold text-dark">{{ $tv->giangVien->HoTen ?? $tv->MaGV }}</span>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small fw-semibold text-secondary">Vai trò mới:</label>
                                                                <select name="VaiTro" class="form-select form-select-sm" required>
                                                                    <option value="Chủ tịch" {{ \App\Enums\VaiTroHoiDong::isChuTich($tv->VaiTro) ? 'selected' : '' }}>Chủ tịch</option>
                                                                    <option value="Thư ký" {{ \App\Enums\VaiTroHoiDong::isThuKy($tv->VaiTro) ? 'selected' : '' }}>Thư ký</option>
                                                                    <option value="Phản biện" {{ \App\Enums\VaiTroHoiDong::isPhanBien($tv->VaiTro) ? 'selected' : '' }}>Phản biện</option>
                                                                    <option value="Ủy viên" {{ \App\Enums\VaiTroHoiDong::isUyVien($tv->VaiTro) ? 'selected' : '' }}>Ủy viên</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer py-2 bg-light">
                                                            <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                                                            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">Lưu thay đổi</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Chưa có thành viên nào.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Danh sách nhóm sinh viên bảo vệ -->
        <div class="col-lg-7">
            <!-- Card: Thêm đề tài mới vào Hội đồng -->
            @php
                $nhomChuaPhanCong = $nhomDuDieuKien->filter(function($h) use ($hoiDong) {
                    return $h->MaHoiDong !== $hoiDong->MaHoiDong;
                });
            @endphp

            @if($hoiDong->TrangThaiCongBo !== 'Đã công bố' && $nhomChuaPhanCong->isNotEmpty())
            <div class="card border-0 shadow-sm rounded-3 mb-4" style="background: #f8fafc; border: 1px dashed #cbd5e1 !important;">
                <div class="card-header bg-white border-bottom py-2 text-primary fw-bold">
                    <i class="fa-solid fa-plus me-2 text-primary"></i> Gắn Thêm Đề Tài / Nhóm Sinh Viên Vào Hội Đồng Này
                </div>
                <div class="card-body p-3">
                    <form action="{{ route('truongkhoa.hoidong.phanCongNhom', $hoiDong->MaHoiDong) }}" method="POST">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-7">
                                <label class="form-label small fw-bold text-dark">Chọn Nhóm Đề Tài (Đủ điều kiện)</label>
                                <select name="MaNhom" class="form-select form-select-sm" required>
                                    <option value="">— Chọn đề tài / nhóm sinh viên —</option>
                                    @foreach($nhomChuaPhanCong as $item)
                                        @php
                                            $gvhdMaGV = $item->nhom?->deTai?->MaGV;
                                            $isGVHDInCouncil = in_array($gvhdMaGV, $councilMemberMaGVs ?? []);
                                        @endphp
                                        <option value="{{ $item->MaNhom }}" {{ $isGVHDInCouncil ? 'disabled' : '' }}>
                                            Nhóm {{ $item->MaNhom }} — {{ Str::limit($item->nhom->deTai->TenDeTai ?? 'Chưa rõ', 40) }}
                                            @if($isGVHDInCouncil)
                                                (GVHD: {{ $item->nhom?->deTai?->giangVien?->HoTen }} đang là thành viên HĐ - Không được chọn)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-dark">Phòng bảo vệ</label>
                                <input type="text" name="PhongBaoVe" class="form-control form-control-sm" placeholder="VD: Phòng B.304" value="{{ $hoiDong->DiaDiem }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-dark">Thời gian bảo vệ</label>
                                <input type="datetime-local" name="ThoiGianBaoVe" class="form-control form-control-sm"
                                    value="{{ $hoiDong->ThoiGianBatDau ? \Carbon\Carbon::parse($hoiDong->ThoiGianBatDau)->format('Y-m-d\TH:i') : '' }}">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100 fw-bold shadow-xs d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-plus me-2"></i>Thêm Vào HĐ
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Card: Các đề tài đang trong Hội đồng (Phụ lục 2) -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-dark"><i class="fa-regular fa-folder-open me-2 text-primary"></i> Đề Tài &amp; Sinh Viên Thuộc Hội Đồng ({{ $hoiDong->hoSoBaoVes->count() }} nhóm)</span>
                        @if($hoiDong->TrangThaiCongBo === 'Đã công bố')
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.72rem;">
                                <i class="fa-solid fa-lock me-1"></i>Đã công bố - Danh sách cố định
                            </span>
                        @endif
                    </div>
                    <span class="small text-muted">
                        <i class="fa-solid fa-circle-info text-primary me-1"></i> Tên đề tài &amp; Cán bộ hướng dẫn gộp chung ô theo từng nhóm
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
                                    <th class="py-3 text-center" style="width: 85px;">TURNITIN</th>
                                    @if($hoiDong->TrangThaiCongBo !== 'Đã công bố')
                                        <th class="pe-3 py-3 text-center" style="width: 65px;">TÁC VỤ</th>
                                    @endif
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
                                                    <span class="badge bg-primary-subtle text-primary border" title="Ngành đào tạo">
                                                        <i class="fa-solid fa-graduation-cap me-1"></i>{{ $hoSo->ten_nganh }}
                                                    </span>
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
                                            <td rowspan="{{ $soSV }}" class="text-center py-3 bg-white" style="vertical-align: middle;">
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
                                            @if($hoiDong->TrangThaiCongBo !== 'Đã công bố')
                                            <td rowspan="{{ $soSV }}" class="pe-3 text-center py-3 bg-white" style="vertical-align: middle;">
                                                <form action="{{ route('truongkhoa.hoidong.huyPhanCongNhom', [$hoiDong->MaHoiDong, $hoSo->MaHoSo]) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-light text-danger btn-action-circle border" title="Hủy phân công nhóm này khỏi hội đồng" onclick="return confirm('Bạn có chắc chắn muốn loại nhóm này khỏi Hội đồng? Nhóm sẽ quay về trạng thái đủ điều kiện bảo vệ.');">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            @endif
                                        @endif
                                    </tr>
                                    @endforeach
                                @else
                                    {{-- Trường hợp nhóm chưa cập nhật thành viên --}}
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
                                        <td class="text-center py-3 bg-white">—</td>
                                        @if($hoiDong->TrangThaiCongBo !== 'Đã công bố')
                                        <td class="pe-3 text-center py-3 bg-white">
                                            <form action="{{ route('truongkhoa.hoidong.huyPhanCongNhom', [$hoiDong->MaHoiDong, $hoSo->MaHoSo]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-light text-danger btn-action-circle border" title="Hủy phân công" onclick="return confirm('Hủy phân công đề tài này?');">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                        @endif
                                    </tr>
                                @endif
                                @empty
                                <tr>
                                    <td colspan="{{ $hoiDong->TrangThaiCongBo === 'Đã công bố' ? 7 : 8 }}" class="text-center py-5 text-muted">
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

<!-- Modal Thêm thành viên Hội đồng (Mở rộng toàn Khoa) -->
<div class="modal fade" id="modalThemThanhVien" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-3 bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-user-plus me-2 text-primary"></i> Thêm Thành Viên Vào Hội Đồng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('truongkhoa.hoidong.themThanhVien', $hoiDong->MaHoiDong) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Chọn Giảng viên: <span class="text-danger">*</span></label>
                        @php
                            $existingMaGVs = $hoiDong->thanhViens->pluck('MaGV')->toArray();
                        @endphp
                        <select name="MaGV" class="form-select" required>
                            <option value="">-- Chọn giảng viên thuộc Khoa --</option>
                            @foreach($giangViens as $gv)
                                @if(!in_array($gv->MaGV, $existingMaGVs))
                                    @php
                                        $isGVHDOfCouncil = in_array($gv->MaGV, $assignedGvhdMaGVs ?? []);
                                        $isScheduleConflict = isset($conflictingScheduleMap[$gv->MaGV]);
                                        $stat = $facultyCouncilStats[$gv->MaGV] ?? null;
                                        $soHd = $stat ? (int)$stat['total'] : 0;
                                        $tooltip = $stat['tooltip'] ?? "Chưa tham gia hội đồng nào trong {$tenHocKyHoiDong}";
                                    @endphp
                                    <option value="{{ $gv->MaGV }}" 
                                            title="{{ $tooltip }}"
                                            {{ ($isGVHDOfCouncil || $isScheduleConflict) ? 'disabled' : '' }}>
                                        {{ $gv->danh_xung_option ?? ($gv->HoTen . ' (' . $gv->MaGV . ')') }} — [{{ $soHd }} HĐ]
                                        @if($isGVHDOfCouncil)
                                            — [Đang là GVHD nhóm trong HĐ - Không được chọn]
                                        @elseif($isScheduleConflict)
                                            — [{{ $conflictingScheduleMap[$gv->MaGV] }} - Không được chọn]
                                        @endif
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <div class="form-text small text-muted mt-1">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i>Số hội đồng đã tham gia trong <span class="fw-semibold text-primary">{{ $tenHocKyHoiDong }}</span> (không tính hội đồng này).
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Vai trò trong Hội đồng: <span class="text-danger">*</span></label>
                        <select name="VaiTro" class="form-select" required>
                            <option value="Ủy viên">Ủy viên</option>
                            <option value="Chủ tịch">Chủ tịch</option>
                            <option value="Thư ký">Thư ký</option>
                            <option value="Phản biện">Phản biện</option>
                        </select>
                    </div>
                    <div class="p-3 bg-light rounded-3 text-muted small mb-2">
                        <i class="fa-solid fa-circle-info text-primary me-2"></i> Trưởng khoa có thể phân công giảng viên từ bất kỳ Bộ môn nào trong Khoa.
                    </div>
                    <div class="p-2 px-3 rounded-3 bg-danger bg-opacity-10 text-danger small">
                        <i class="fa-solid fa-shield-halved me-1"></i> <strong>Quy định:</strong> Giảng viên hướng dẫn không được tham gia Hội đồng chấm nhóm do mình hướng dẫn.
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Xác nhận thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa thông tin Quyết định -->
<div class="modal fade" id="modalSuaQuyetDinh" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-3 bg-light">
                <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i> Sửa Thông Tin Quyết Định Ban Hành</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('truongkhoa.hoidong.updateQuyetDinh', $hoiDong->MaHoiDong) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Số Quyết định:</label>
                        <input type="text" name="SoQuyetDinh" class="form-control" value="{{ $hoiDong->SoQuyetDinh }}" placeholder="VD: 125/QĐ-ĐHCNTP">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Ngày Ban Hành:</label>
                        <input type="date" name="NgayBanHanh" class="form-control" value="{{ $hoiDong->NgayBanHanh ? \Carbon\Carbon::parse($hoiDong->NgayBanHanh)->format('Y-m-d') : '' }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Đợt Bảo Vệ:</label>
                        <input type="text" name="DotBaoVe" class="form-control" value="{{ $hoiDong->DotBaoVe }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Người Ký:</label>
                        <input type="text" name="NguoiKy" class="form-control" value="{{ $hoiDong->NguoiKy }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Chức Vụ Người Ký:</label>
                        <input type="text" name="ChucVuNguoiKy" class="form-control" value="{{ $hoiDong->ChucVuNguoiKy }}">
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Cập nhật Quyết định</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cấu hình thời hạn chỉnh sửa điểm -->
<div class="modal fade" id="modalCauHinhSuaDiem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-3 bg-light">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fa-regular fa-clock me-2 text-primary"></i> Thiết Lập Thời Hạn Chỉnh Sửa Điểm
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('truongkhoa.hoidong.cauhinh_suadiem', $hoiDong->MaHoiDong) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        <i class="fa-solid fa-circle-info me-1 text-primary"></i>
                        Giảng viên chỉ được phép chỉnh sửa điểm trong khoảng thời gian này. Khi hết hạn, hệ thống sẽ <strong>tự động khóa chức năng sửa điểm</strong> của cả 3 giảng viên.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Thời Gian Bắt Đầu Cho Phép Sửa:</label>
                        <input type="datetime-local" name="ThoiGianBatDauSuaDiem" class="form-control"
                               value="{{ $hoiDong->ThoiGianBatDauSuaDiem ? $hoiDong->ThoiGianBatDauSuaDiem->format('Y-m-d\TH:i') : '' }}">
                        <div class="form-text small text-muted">Bỏ trống nếu cho phép sửa ngay.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Thời Gian Kết Thúc (Hạn Chót):</label>
                        <input type="datetime-local" name="ThoiGianKetThucSuaDiem" class="form-control"
                               value="{{ $hoiDong->ThoiGianKetThucSuaDiem ? $hoiDong->ThoiGianKetThucSuaDiem->format('Y-m-d\TH:i') : '' }}">
                        <div class="form-text small text-muted">Sau thời điểm này, chức năng sửa điểm sẽ tự động bị khóa hoàn toàn.</div>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Lưu Cấu Hình Thời Hạn</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Mở khóa sửa điểm xử lý sai sót -->
<div class="modal fade" id="modalMoKhoaSuaDiem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-3 bg-light">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="fa-solid fa-lock-open me-2"></i> Mở Khóa Sửa Điểm (Xử Lý Sai Sót)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('truongkhoa.ketqua.mokhoaphieu') }}" method="POST">
                @csrf
                <input type="hidden" name="MaHoiDong" value="{{ $hoiDong->MaHoiDong }}">
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-warning-subtle small mb-3">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        <strong>Lưu ý nghiệp vụ:</strong> Chỉ mở khóa khi có sai sót cần đính chính. Theo quy định, <strong>bắt buộc phải ghi nhận rõ lý do mở khóa</strong> và hành động này sẽ được ghi vết vào Lịch sử kiểm toán không thể xóa.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">
                            Lý Do Mở Khóa <span class="text-danger">*</span> (Tối thiểu 5 ký tự):
                        </label>
                        <textarea name="ly_do" class="form-control" rows="3" required minlength="5"
                                  placeholder="Nhập lý do cụ thể vì sao mở khóa sửa điểm (VD: Giám khảo chấm nhầm điểm tiêu chí 3 của SV Nguyễn Văn A, cần chỉnh sửa lại theo biên bản...)">{{ old('ly_do', $hoiDong->LyDoMoKhoaSuaDiem) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Gia Hạn Chót Sửa Mới (Tùy chọn):</label>
                        <input type="datetime-local" name="han_sua_moi" class="form-control"
                               value="{{ $hoiDong->ThoiGianKetThucSuaDiem ? $hoiDong->ThoiGianKetThucSuaDiem->format('Y-m-d\TH:i') : '' }}">
                        <div class="form-text small text-muted">Thời hạn mới để giảng viên hoàn tất sửa điểm trước khi tự động khóa lại.</div>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold shadow-xs">
                        <i class="fa-solid fa-unlock me-1"></i> Xác Nhận Mở Khóa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Lịch sử chỉnh sửa điểm (Audit Log) -->
<div class="modal fade" id="modalLichSuSuaDiem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-3 bg-light border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-primary fs-5"></i>
                    <div>
                        <h5 class="modal-title fw-bold text-dark fs-6 mb-0">
                            Lịch Sử Chỉnh Sửa Điểm &amp; Kiểm Toán (Audit Log) — {{ $hoiDong->TenHoiDong }}
                        </h5>
                        <div class="small text-muted">Dữ liệu bất biến, ghi vết mọi thay đổi điểm tiêu chí/CLO và hành động mở khóa.</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                @if($lichSuSuaDiems->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-receipt fa-3x mb-3 text-secondary opacity-50"></i>
                        <h6>Chưa có lịch sử chỉnh sửa điểm nào được ghi nhận cho Hội đồng này.</h6>
                    </div>
                @else
                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 45px;">STT</th>
                                    <th style="min-width: 150px;">Giảng Viên Thực Hiện</th>
                                    <th style="min-width: 110px;">Sinh Viên</th>
                                    <th style="min-width: 130px;">CLO / Tiêu Chí</th>
                                    <th class="text-center" style="width: 90px;">Điểm Cũ</th>
                                    <th class="text-center" style="width: 90px;">Điểm Mới</th>
                                    <th style="min-width: 140px;">Thời Gian</th>
                                    <th style="min-width: 200px;">Ghi Chú / Lý Do</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lichSuSuaDiems as $idx => $ls)
                                    <tr>
                                        <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $ls->NguoiThucHien }}</div>
                                            <div class="small text-muted font-monospace">{{ $ls->MaGV }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border font-monospace">{{ $ls->MaSV }}</span>
                                        </td>
                                        <td>
                                            @if($ls->MaCLO === 'UNLOCK')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                                                    <i class="fa-solid fa-lock-open me-1"></i> MỞ KHÓA SỬA ĐIỂM
                                                </span>
                                            @else
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill me-1">
                                                    {{ $ls->MaCLO ?? 'CLO' }}
                                                </span>
                                                <span class="small text-secondary">{{ Str::limit($ls->TenTieuChi ?? $ls->MaTieuChi, 35) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center font-monospace">
                                            @if(!is_null($ls->DiemCu))
                                                <span class="text-muted text-decoration-line-through">{{ number_format($ls->DiemCu, 2) }}</span>
                                            @else
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </td>
                                        <td class="text-center font-monospace">
                                            @if(!is_null($ls->DiemMoi))
                                                <span class="fw-bold text-success">{{ number_format($ls->DiemMoi, 2) }}</span>
                                            @else
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </td>
                                        <td class="small text-muted">
                                            <div>{{ $ls->ThoiGian ? \Carbon\Carbon::parse($ls->ThoiGian)->format('d/m/Y') : ($ls->created_at ? $ls->created_at->format('d/m/Y') : '') }}</div>
                                            <div class="fw-bold text-dark">{{ $ls->ThoiGian ? \Carbon\Carbon::parse($ls->ThoiGian)->format('H:i:s') : ($ls->created_at ? $ls->created_at->format('H:i:s') : '') }}</div>
                                        </td>
                                        <td class="small" style="overflow-wrap: anywhere; word-break: break-word;">
                                            <div class="{{ $ls->MaCLO === 'UNLOCK' ? 'text-danger fw-semibold' : 'text-secondary' }}">
                                                {{ $ls->LyDo ?? 'Cập nhật điểm phiếu chấm' }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="modal-footer py-2 bg-light">
                <span class="small text-muted me-auto">
                    <i class="fa-solid fa-shield-halved text-success me-1"></i> Báo cáo kiểm toán bất biến &bull; Không cho phép sửa xóa dữ liệu lịch sử
                </span>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endsection
