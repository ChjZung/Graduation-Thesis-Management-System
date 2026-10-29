@extends('layouts.sinhvien')
@section('page_title', 'Kết Quả Khóa Luận & Nộp Lưu Trữ Khoa')
@section('content')

@php
    $isDaCongBo = ($trangThaiHienThi === 'da_cong_bo');
    $diem = null;

    if ($isDaCongBo && $ketQua) {
        $diem = (float) ($ketQua->DiemTongKet ?? $ketQua->DiemChinhThuc ?? $ketQua->DiemHoiDongTB ?? 0);
    }

    $xepLoai = ($isDaCongBo && $diem !== null) ? ($ketQua?->KetQua ?? \App\Models\KetQuaSinhVien::xepLoai($diem)) : 'Chưa có';
    
    $cardBgColor = match(true) {
        !$isDaCongBo && $trangThaiHienThi === 'chua_cong_bo' => '#0f766e', // Xanh Teal trang trọng chờ công bố
        !$isDaCongBo                                       => '#475569', // Xám Slate chưa có điểm
        $diem >= 8.5                                       => '#00529b', // Xanh Navy HUIT
        $diem >= 7.0                                       => '#0d9488', // Xanh Ngọc
        $diem >= 5.0                                       => '#b45309', // Hổ phách
        default                                            => '#991b1b', // Đỏ
    };

    $diemChu = match(true) {
        !$isDaCongBo || $diem === null => null,
        $ketQua?->diem_chu !== null     => $ketQua->diem_chu,
        $diem >= 9.0                   => 'A+',
        $diem >= 8.5                   => 'A',
        $diem >= 8.0                   => 'B+',
        $diem >= 7.0                   => 'B',
        $diem >= 6.5                   => 'C+',
        $diem >= 5.5                   => 'C',
        $diem >= 5.0                   => 'D+',
        $diem >= 4.0                   => 'D',
        default                        => 'F',
    };

    $diemHe4 = match(true) {
        !$isDaCongBo || $diem === null => null,
        $ketQua?->diem_he4 !== null     => $ketQua->diem_he4,
        $diem >= 8.5                   => 4.0,
        $diem >= 8.0                   => 3.5,
        $diem >= 7.0                   => 3.0,
        $diem >= 6.5                   => 2.5,
        $diem >= 5.5                   => 2.0,
        $diem >= 5.0                   => 1.5,
        $diem >= 4.0                   => 1.0,
        default                        => 0.0,
    };

    $loai = $ketQua?->LoaiKhoaLuan ?? (str_contains(mb_strtolower($nhom?->deTai?->TenDeTai ?? ''), 'kỹ sư') ? 'KLKS' : 'KLCN');
    $isDaLuuTru = ($hoSo?->TrangThai === 'Đã lưu trữ');

    // Sắp xếp thứ tự cố định thành viên hội đồng: Chủ tịch -> Thư ký -> Phản biện -> Ủy viên
    $roleOrder = ['Chủ tịch' => 1, 'Thư ký' => 2, 'Phản biện' => 3, 'Ủy viên' => 4];
    $sortedThanhViens = ($hoSo?->hoiDong?->thanhViens ?? collect())->sortBy(fn($tv) => $roleOrder[$tv->VaiTro] ?? 5);
@endphp

<!-- Header Trang -->
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="fa-solid fa-award text-primary"></i> Kết Quả Khóa Luận Tốt Nghiệp &amp; Nộp Lưu Trữ Khoa
        </h4>
        <p class="text-muted small mb-0">
            Xem điểm đánh giá chính thức từ Hội đồng bảo vệ và nộp lại toàn bộ các sản phẩm khóa luận hoàn thiện cuối cùng để lưu trữ Khoa.
        </p>
    </div>
    @if($isDaLuuTru)
        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-box-archive"></i> Đã Lưu Trữ Chính Thức Tại Khoa
        </span>
    @elseif($hoSo?->FileBanChinhSua)
        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left"></i> Đã Nộp Bản Hoàn Chỉnh &bull; Chờ Giáo Vụ Lưu Trữ
        </span>
    @endif
</div>

<!-- Alert Thông báo -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check fs-5 text-success"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-exclamation fs-5 text-danger"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(!empty($hoSo?->YeuCauChinhSuaSauBaoVe))
    <div class="alert alert-warning border-warning shadow-sm rounded-3 p-4 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-warning fs-3 mt-1"></i>
            <div style="min-width: 0; overflow-wrap: anywhere; word-break: break-word;">
                <div class="fw-bold text-warning-emphasis fs-6 mb-1">Hội Đồng Yêu Cầu Chỉnh Sửa Báo Cáo Sau Bảo Vệ</div>
                <div class="small text-secondary mb-1">{{ \App\Helpers\HtmlHelper::plainText($hoSo->YeuCauChinhSuaSauBaoVe, 250) }}</div>
                <div class="small text-muted">
                    <span>Hạn nộp: <strong>{{ \Carbon\Carbon::parse($hoSo->HanNopBaoCaoSauBaoVe)->format('d/m/Y H:i') }}</strong></span> &bull; 
                    <span>Trạng thái: <strong>{{ $hoSo->TrangThaiChinhSuaSauBaoVe ?? 'Chờ nộp bản sửa' }}</strong></span>
                </div>
            </div>
        </div>
        <a href="{{ route('sinhvien.ketqua.hoanthien') }}" class="btn btn-warning rounded-pill px-4 fw-bold shadow-xs">
            <i class="fa-solid fa-file-pen me-2"></i> Hoàn Thiện Báo Cáo Ngay
        </a>
    </div>
@endif

<!-- KHỐI 1: ĐIỂM SỐ & ĐÁNH GIÁ CỦA HỘI ĐỒNG BẢO VỆ -->
<div class="row g-4 align-items-stretch mb-4">
    <!-- Cột trái: Thẻ Điểm Tổng Kết Nổi Bật (3 trạng thái chuẩn tách bạch) -->
    <div class="col-lg-4">
        <div class="card border-0 text-white text-center shadow-sm h-100" style="background-color: {{ $cardBgColor }}; border-radius: 14px;">
            <div class="card-body p-4 d-flex flex-column justify-content-between align-items-center">

                {{-- TRẠNG THÁI 1: ĐÃ CÔNG BỐ CHÍNH THỨC --}}
                @if($isDaCongBo && $diem !== null)
                    <div class="my-auto py-3 w-100">
                        <div class="display-4 fw-bold mb-0" style="line-height: 1.1;">
                            {{ number_format($diem, 2) }}
                        </div>
                        <div class="text-white-50 fw-semibold mb-3" style="font-size: 13.5px;">/ 10.00 điểm (Điểm Chính Thức)</div>

                        <div class="mb-3">
                            <span class="badge bg-white text-dark rounded-pill px-4 py-2 fw-bold" style="font-size: 14px; letter-spacing: 0.3px;">
                                {{ $xepLoai }}
                            </span>
                        </div>

                        <div class="p-3 rounded-3 bg-white bg-opacity-10 text-white w-100" style="font-size: 13.5px;">
                            <div>Điểm chữ: <strong>{{ $diemChu }}</strong> &bull; Hệ 4: <strong>{{ number_format($diemHe4, 1) }}</strong></div>
                            <div class="text-white-50 mt-1" style="font-size: 12.5px;">
                                {{ $loai === 'KLKS' ? 'Khóa luận Kỹ sư (KLKS)' : 'Khóa luận Cử nhân (KLCN)' }}
                            </div>
                        </div>
                    </div>

                {{-- TRẠNG THÁI 2: ĐÃ CHẤM NHƯNG CHƯA CÔNG BỐ CHÍNH THỨC --}}
                @elseif($trangThaiHienThi === 'chua_cong_bo')
                    <div class="my-auto py-4 w-100">
                        <div class="mb-2">
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold">
                                <i class="fa-solid fa-hourglass-half me-1"></i> Đang Thẩm Định Kết Quả
                            </span>
                        </div>
                        <h5 class="fw-bold mb-2 text-white">Chưa Công Bố Chính Thức</h5>
                        <p class="text-white-50 small mb-0 px-2" style="line-height: 1.6;">
                            Phiên bảo vệ đã hoàn tất. Điểm số chính thức đang được Hội đồng &amp; Trưởng khoa thẩm định trước khi công bố công khai.
                        </p>
                    </div>

                {{-- TRẠNG THÁI 3: CHƯA CÓ ĐIỂM / CHƯA ĐỦ PHIẾU CHẤM --}}
                @else
                    <div class="my-auto py-4 w-100">
                        <h5 class="fw-bold mb-2 text-white">Chưa Có Điểm Khóa Luận</h5>
                        <p class="text-white-50 small mb-0 px-2" style="line-height: 1.6;">
                            Kết quả đánh giá và điểm số sẽ được ghi nhận sau khi tất cả các giám khảo trong Hội đồng hoàn tất đánh giá đề tài.
                        </p>
                    </div>
                @endif

                {{-- Thông tin thời gian và phòng bảo vệ --}}
                @if($hoSo && ($hoSo->ThoiGianBaoVe || $hoSo->PhongBaoVe))
                    <div class="mt-auto pt-3 border-top border-white border-opacity-25 w-100 text-start small">
                        @if($hoSo->ThoiGianBaoVe)
                            <div class="text-white-50">
                                <i class="fa-regular fa-calendar-check me-2"></i>Bảo vệ: <strong class="text-white">{{ \Carbon\Carbon::parse($hoSo->ThoiGianBaoVe)->format('H:i') }} &bull; {{ \Carbon\Carbon::parse($hoSo->ThoiGianBaoVe)->format('d/m/Y') }}</strong>
                            </div>
                        @endif
                        @if($hoSo->PhongBaoVe)
                            <div class="text-white-50 mt-1">
                                <i class="fa-solid fa-location-dot me-2"></i>Địa điểm: <strong class="text-white">{{ $hoSo->PhongBaoVe }}</strong>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cột phải: Chi Tiết Đề Tài, Thành Viên Hội Đồng & Nhóm Sinh Viên -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 14px;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-chart-column text-primary fs-5"></i>
                    <h5 class="mb-0 fw-bold text-dark fs-6">Chi Tiết Đánh Giá Hội Đồng Bảo Vệ</h5>
                </div>
                @if($hoSo?->hoiDong)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-semibold small">
                        {{ $hoSo->hoiDong->TenHoiDong }}
                    </span>
                @endif
            </div>
            <div class="card-body p-4">
                {{-- Thông tin đề tài --}}
                @if($nhom)
                <div class="mb-4 p-3 bg-light rounded-3 border" style="min-width: 0;">
                    <div class="text-muted mb-1" style="font-size: 12.5px;">Đề tài khóa luận tốt nghiệp:</div>
                    <div class="fw-bold text-primary fs-6" style="overflow-wrap: anywhere; word-break: break-word;">
                        {{ $nhom->deTai->TenDeTai ?? 'Chưa xác định' }}
                    </div>
                    @if(!empty($nhom->deTai?->TenDeTaiTiengAnh))
                        <div class="text-muted fst-italic small mt-1" style="overflow-wrap: anywhere; word-break: break-word;">
                            {{ $nhom->deTai->TenDeTaiTiengAnh }}
                        </div>
                    @endif
                    <div class="text-muted mt-2 d-flex flex-wrap gap-3" style="font-size: 13px;">
                        <span><i class="fa-solid fa-user-tie text-secondary me-1"></i>GVHD: <strong class="text-dark">{{ $nhom->deTai->giangVien->HoTen ?? 'N/A' }}</strong></span>
                        <span><i class="fa-solid fa-users text-secondary me-1"></i>Nhóm: <strong class="text-dark">{{ $nhom->TenNhom ?? '' }}</strong></span>
                        @if($nhom->truongNhom)
                            <span><i class="fa-solid fa-crown text-warning me-1"></i>Trưởng nhóm: <strong class="text-dark">{{ $nhom->truongNhom->HoTen }}</strong></span>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Bảng Thành Viên Hội Đồng (Dạng cột, sắp xếp cố định Chủ tịch -> Thư ký -> Phản biện -> Ủy viên) --}}
                <div class="mb-4">
                    <div class="fw-bold text-dark mb-2 small d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-people-group text-primary"></i>
                            <span>Thành viên Hội đồng đánh giá ({{ $sortedThanhViens->count() }} giảng viên):</span>
                        </span>
                        @if(!$isDaCongBo)
                            <span class="badge bg-secondary-subtle text-secondary small fw-normal">
                                <i class="fa-solid fa-lock me-1"></i> Điểm thành viên được bảo mật trước khi công bố
                            </span>
                        @endif
                    </div>

                    @if($sortedThanhViens->isEmpty())
                        <div class="text-muted small fst-italic p-3 bg-light rounded text-center">
                            Chưa có thông tin thành viên hội đồng.
                        </div>
                    @else
                        <div class="table-responsive rounded-3 border">
                            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 45px;" class="text-center">STT</th>
                                        <th style="width: 130px;">Vai Trò</th>
                                        <th>Giảng Viên Giám Khảo</th>
                                        <th>Bộ Môn / Đơn Vị</th>
                                        <th style="width: 140px;" class="text-center">Điểm Đánh Giá</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sortedThanhViens as $idx => $tvHd)
                                        @php
                                            $badgeRole = match($tvHd->VaiTro) {
                                                'Chủ tịch'  => 'bg-danger-subtle text-danger border border-danger-subtle',
                                                'Thư ký'    => 'bg-primary-subtle text-primary border border-primary-subtle',
                                                'Phản biện' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                                default     => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                            };
                                            $phieuOfGv = $phieuChamDiems->firstWhere('MaGV', $tvHd->MaGV);
                                        @endphp
                                        <tr>
                                            <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                            <td>
                                                <span class="badge {{ $badgeRole }} rounded-pill px-2 py-1 fw-bold" style="font-size: 11.5px;">
                                                    {{ $tvHd->VaiTro ?? 'Thành viên' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark" style="overflow-wrap: anywhere; word-break: break-word;">
                                                    {{ $tvHd->giangVien->HoTen ?? $tvHd->MaGV }}
                                                </div>
                                                <div class="text-muted font-monospace" style="font-size: 11px;">
                                                    {{ $tvHd->giangVien->HocVi ?? 'Giảng viên' }}
                                                </div>
                                            </td>
                                            <td class="text-secondary small" style="overflow-wrap: anywhere; word-break: break-word;">
                                                {{ $tvHd->giangVien->boMon->TenBoMon ?? 'Khoa CNTT' }}
                                            </td>
                                            <td class="text-center">
                                                @if($isDaCongBo && $phieuOfGv && $phieuOfGv->Diem !== null)
                                                    <span class="fw-bold text-primary fs-6">{{ number_format($phieuOfGv->Diem, 2) }}</span>
                                                    <span class="small text-muted">/10</span>
                                                @elseif($isDaCongBo)
                                                    <span class="badge bg-light text-muted border">Chưa nộp</span>
                                                @else
                                                    <span class="badge bg-light text-secondary border py-1 px-2" style="font-size: 11px;">
                                                        <i class="fa-solid fa-lock me-1"></i> Chưa công bố
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Bảng Danh Sách Sinh Viên Nhóm (Đầy đủ tất cả thành viên) --}}
                @if($nhom && $nhom->thanhViens->isNotEmpty())
                <div class="mb-4">
                    <div class="fw-bold text-dark mb-2 small d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-user-graduate text-primary"></i>
                            <span>Danh sách sinh viên thực hiện đề tài ({{ $nhom->thanhViens->count() }} sinh viên):</span>
                        </span>
                    </div>
                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 45px;" class="text-center">STT</th>
                                    <th style="width: 110px;">MSSV</th>
                                    <th>Họ và Tên</th>
                                    <th>Lớp</th>
                                    <th style="width: 120px;" class="text-center">Vai Trò</th>
                                    <th style="width: 120px;" class="text-center text-primary">Điểm Chính Thức</th>
                                    <th style="width: 90px;" class="text-center">Điểm Chữ</th>
                                    <th style="width: 110px;" class="text-center">Kết Quả</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nhom->thanhViens as $sIdx => $tvn)
                                    @php
                                        $sv = $tvn->sinhVien;
                                        $isMe = ($sv?->MaSV === $sinhVien->MaSV);
                                        $isLeaderMember = ($nhom->MaTruongNhom === $tvn->MaSV) || (mb_stripos($tvn->VaiTro ?? '', 'trưởng') !== false);
                                        $kqSv = $allKetQuaNhom->get($tvn->MaSV);
                                        $diemSv = ($isDaCongBo && $kqSv) ? (float)($kqSv->DiemChinhThuc ?? $kqSv->DiemTongKet ?? $kqSv->DiemHoiDongTB ?? 0) : null;
                                        $diemChuSv = ($diemSv !== null) ? ($kqSv->diem_chu ?? match(true) {
                                            $diemSv >= 9.0 => 'A+',
                                            $diemSv >= 8.5 => 'A',
                                            $diemSv >= 8.0 => 'B+',
                                            $diemSv >= 7.0 => 'B',
                                            $diemSv >= 6.5 => 'C+',
                                            $diemSv >= 5.5 => 'C',
                                            $diemSv >= 5.0 => 'D+',
                                            $diemSv >= 4.0 => 'D',
                                            default        => 'F',
                                        }) : null;
                                        $xepLoaiSv = ($diemSv !== null) ? ($kqSv->KetQua ?? \App\Models\KetQuaSinhVien::xepLoai($diemSv)) : null;
                                    @endphp
                                    <tr class="{{ $isMe ? 'table-primary table-opacity-10' : '' }}">
                                        <td class="text-center text-muted">{{ $sIdx + 1 }}</td>
                                        <td class="font-monospace fw-bold {{ $isMe ? 'text-primary' : 'text-dark' }}">
                                            {{ $tvn->MaSV }}
                                            @if($isMe)
                                                <span class="badge bg-primary text-white ms-1" style="font-size: 10px;">Bạn</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark" style="overflow-wrap: anywhere; word-break: break-word;">
                                                {{ $sv?->HoTen ?? $tvn->MaSV }}
                                            </div>
                                            <div class="text-muted small" style="overflow-wrap: anywhere; word-break: break-word;">
                                                {{ $sv?->Email ?? '' }}
                                            </div>
                                        </td>
                                        <td class="text-secondary small">{{ $sv?->lop?->TenLop ?? ($sv?->MaLop ?? 'N/A') }}</td>
                                        <td class="text-center">
                                            @if($isLeaderMember)
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1" style="font-size: 11px;">
                                                    <i class="fa-solid fa-crown me-1 text-warning"></i> Trưởng nhóm
                                                </span>
                                            @else
                                                <span class="badge bg-light text-secondary border rounded-pill px-2 py-1" style="font-size: 11px;">
                                                    Thành viên
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($isDaCongBo && $diemSv !== null)
                                                <span class="fw-bold text-primary fs-6">{{ number_format($diemSv, 2) }}</span>
                                            @else
                                                <span class="badge bg-light text-secondary border" style="font-size: 11px;">
                                                    <i class="fa-solid fa-lock me-1"></i> Chưa công bố
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($isDaCongBo && $diemChuSv)
                                                <span class="badge bg-light text-dark border fw-bold">{{ $diemChuSv }}</span>
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($isDaCongBo && $xepLoaiSv)
                                                @php
                                                    $isDat = ($diemSv >= 5.0);
                                                @endphp
                                                <span class="badge {{ $isDat ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} rounded-pill px-2 py-1 fw-bold" style="font-size: 11px;">
                                                    {{ $isDat ? 'Đạt' : 'Không đạt' }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1" style="font-size: 11px;">
                                                    Chờ công bố
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Nhận xét của Hội đồng --}}
                @if($isDaCongBo && ($ketQua?->NhanXetChung || $phieuChamDiems->whereNotNull('NhanXet')->isNotEmpty()))
                <div class="p-3 rounded-3 bg-light border mb-3" style="font-size: 13.5px; min-width: 0; overflow-wrap: anywhere; word-break: break-word;">
                    <div class="fw-bold text-primary mb-1">
                        <i class="fa-solid fa-comments me-2"></i>Ý kiến đóng góp &amp; Nhận xét của Hội đồng dành cho bạn:
                    </div>
                    @if($ketQua?->NhanXetChung)
                        <div class="text-dark mb-2">{!! \App\Helpers\HtmlHelper::clean($ketQua->NhanXetChung) !!}</div>
                    @endif
                    @foreach($phieuChamDiems->whereNotNull('NhanXet') as $pcd)
                        @if(!empty(trim($pcd->NhanXet)))
                            <div class="border-top pt-2 mt-2 small text-muted">
                                <strong class="text-dark">{{ $pcd->giangVien->HoTen ?? 'Giám khảo' }}:</strong>
                                <span class="text-secondary">{!! \App\Helpers\HtmlHelper::clean($pcd->NhanXet) !!}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
                @endif

                @if($hoSo?->TrangThaiChinhSuaSauBaoVe === 'YeuCauSua' || $hoSo?->YeuCauChinhSuaSauBaoVe)
                <div class="alert alert-warning border-0 p-3 rounded-3 mt-3 mb-0 small text-dark d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2" style="min-width: 0; overflow-wrap: anywhere; word-break: break-word;">
                        <i class="fa-solid fa-triangle-exclamation text-warning fs-5"></i>
                        <div>
                            <strong>Hội đồng có yêu cầu chỉnh sửa báo cáo sau bảo vệ:</strong>
                            <div class="text-muted mt-0">Vui lòng truy cập mục <strong>Hoàn thiện BC sau bảo vệ</strong> trên menu để tải lên báo cáo đã chỉnh sửa.</div>
                        </div>
                    </div>
                    <a href="{{ route('sinhvien.ketqua.hoanthien') }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold">
                        Đến trang hoàn thiện &rarr;
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
