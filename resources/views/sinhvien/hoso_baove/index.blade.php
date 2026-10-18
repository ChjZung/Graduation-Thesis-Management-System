@extends('layouts.sinhvien')
@section('page_title', 'Hồ Sơ Bảo Vệ Khóa Luận')
@section('content')

<div class="container-fluid px-0">
    <!-- Header Trang -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-shield-halved text-primary me-2"></i>Hồ Sơ Bảo Vệ Khóa Luận Tốt Nghiệp
            </h4>
            <p class="text-muted mb-0" style="font-size: 13.5px;">
                Quản lý và nộp toàn bộ hồ sơ báo cáo, mã nguồn và sản phẩm thực hiện để Giảng viên hướng dẫn xác nhận đủ điều kiện bảo vệ.
            </p>
        </div>
        @if($hoSo)
            @php
                $sttConfig = match($hoSo->TrangThai) {
                    'Đã phê duyệt', 'Đủ điều kiện bảo vệ' => ['badge' => 'bg-success text-white', 'icon' => 'fa-circle-check', 'text' => 'Đã Phê Duyệt – Đủ Điều Kiện Bảo Vệ'],
                    'Đã phân công', 'Đã phân công hội đồng' => ['badge' => 'bg-primary text-white', 'icon' => 'fa-landmark', 'text' => 'Đã Phân Công Hội Đồng Bảo Vệ'],
                    'Yêu cầu chỉnh sửa' => ['badge' => 'bg-warning text-dark', 'icon' => 'fa-triangle-exclamation', 'text' => 'Yêu Cầu Chỉnh Sửa / Bổ Sung'],
                    'Không duyệt', 'Không đủ điều kiện' => ['badge' => 'bg-danger text-white', 'icon' => 'fa-circle-xmark', 'text' => 'Chưa Được Phê Duyệt'],
                    'Đã nộp lại - Chờ kiểm tra' => ['badge' => 'bg-info text-dark', 'icon' => 'fa-rotate', 'text' => 'Đã Nộp Lại – Chờ GVHD Kiểm Tra'],
                    'Đang được kiểm tra' => ['badge' => 'bg-info text-white', 'icon' => 'fa-magnifying-glass', 'text' => 'GVHD Đang Kiểm Tra'],
                    default => ['badge' => 'bg-warning text-dark', 'icon' => 'fa-clock', 'text' => 'Chờ GVHD Kiểm Tra'],
                };
            @endphp
            <div class="d-flex align-items-center gap-2">
                <span class="badge {{ $sttConfig['badge'] }} px-3 py-2 rounded-pill fs-6 fw-semibold shadow-sm">
                    <i class="fa-solid {{ $sttConfig['icon'] }} me-2"></i>{{ $sttConfig['text'] }}
                </span>
                @if($hoSo->PhienBanHienTai)
                    <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill fs-6 fw-semibold">
                        Phiên bản {{ number_format($hoSo->PhienBanHienTai, 1, '.', '') }}
                    </span>
                @endif
            </div>
        @endif
    </div>

    <!-- Bộ Chọn Nhóm / Học Phần Nếu Sinh Viên Tham Gia Nhiều Nhóm -->
    @if(isset($memberships) && $memberships->count() > 1)
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: linear-gradient(to right, #f8fafc, #edf2f7);">
            <div class="card-body p-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-layer-group text-primary fs-5"></i>
                        <div>
                            <span class="fw-bold text-dark" style="font-size: 14px;">Bạn đang tham gia nhiều học phần/khóa luận:</span>
                            <span class="text-muted small ms-1">(Chọn nhóm để xem hoặc nộp hồ sơ tương ứng)</span>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($memberships as $mb)
                            @php
                                $isSelected = ($nhom && $nhom->MaNhom === $mb->MaNhom);
                                $courseName = $mb->nhom->hocPhan->TenHocPhan ?? 'Khóa luận';
                                $topicName = $mb->nhom->deTai->TenDeTai ?? 'Chưa có đề tài';
                            @endphp
                            <a href="{{ route('sinhvien.hoso.index', ['nhom_id' => $mb->MaNhom]) }}" 
                               class="btn btn-sm {{ $isSelected ? 'btn-primary shadow-sm' : 'btn-outline-secondary bg-white' }} rounded-pill px-3 py-2 text-start">
                                <i class="fa-solid {{ $isSelected ? 'fa-circle-dot' : 'fa-circle' }} me-2"></i>
                                <strong>{{ $mb->nhom->TenNhom }}</strong>
                                <span class="badge {{ $isSelected ? 'bg-light text-primary' : 'bg-secondary-subtle text-secondary' }} ms-1">
                                    {{ Str::limit($courseName, 20) }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Trường hợp 1: Chưa có nhóm hoặc chưa có đề tài --}}
    @if(!$nhom || !$nhom->MaDeTai)
        <div class="card shadow-sm border-0" style="border-radius: 16px;">
            <div class="card-body text-center py-5 px-4">
                <div class="mb-3">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-light text-secondary" style="width: 76px; height: 76px;">
                        <i class="fa-solid fa-lock fa-2x"></i>
                    </span>
                </div>
                <h5 class="fw-bold text-dark mb-2">Chưa đủ điều kiện nộp hồ sơ bảo vệ</h5>
                <p class="text-muted mx-auto mb-4" style="max-width: 520px; font-size: 14px;">
                    Nhóm của bạn cần hoàn tất đăng ký và được Giảng viên hướng dẫn / Bộ môn phê duyệt đề tài khóa luận trước khi tiến hành nộp hồ sơ bảo vệ.
                </p>
                <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-primary rounded-pill px-4 py-2 d-inline-flex align-items-center">
                    <i class="fa-solid fa-users me-2"></i>Vào Quản lý nhóm &amp; Đề tài
                </a>
            </div>
        </div>

    {{-- Trường hợp 2: Chưa đủ điều kiện nộp hồ sơ bảo vệ --}}
    @elseif(!($duDieuKien['du_dieu_kien'] ?? $moc5Dat))
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
            <div class="card-body text-center py-5 px-4">
                <div class="mb-3">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning" style="width: 76px; height: 76px;">
                        <i class="fa-solid fa-hourglass-half fa-2x"></i>
                    </span>
                </div>
                <h5 class="fw-bold text-dark mb-2">Chưa đủ điều kiện nộp hồ sơ bảo vệ</h5>
                <p class="text-muted mx-auto mb-3" style="max-width: 600px; font-size: 14px;">
                    {{ $duDieuKien['ly_do'] ?? 'Theo quy định đào tạo, nhóm khóa luận cần hoàn thành các mốc báo cáo tiến độ và được Giảng viên hướng dẫn xác nhận hoàn thành giai đoạn thực hiện đề tài để mở cổng nộp hồ sơ bảo vệ chính thức.' }}
                </p>

                @if(!empty($duDieuKien['tong_dot']) && $duDieuKien['tong_dot'] > 0)
                    <div class="mx-auto mb-4" style="max-width: 420px;">
                        <div class="d-flex justify-content-between align-items-center mb-1 small text-secondary">
                            <span>Tiến độ hoàn thành: <strong>{{ $duDieuKien['dot_hoan_thanh'] }}/{{ $duDieuKien['tong_dot'] }} đợt</strong></span>
                            <span class="fw-bold text-primary">{{ $duDieuKien['ti_le_hoan_thanh'] }}%</span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $duDieuKien['ti_le_hoan_thanh'] }}%;"></div>
                        </div>
                    </div>
                @endif

                <div class="alert alert-light border d-inline-block text-start small text-muted mx-auto mb-4 px-4 py-2.5 rounded-3" style="max-width: 620px;">
                    <i class="fa-solid fa-lightbulb text-warning me-2"></i>
                    <strong>Gợi ý:</strong> Nếu nhóm đã hoàn thành tất cả các mốc báo cáo nhưng cổng nộp hồ sơ bảo vệ chưa mở, vui lòng liên hệ Giảng viên hướng dẫn (<strong>{{ $nhom->deTai->giangVien->HoTen ?? 'GVHD' }}</strong>) để thầy/cô bấm nút <em>"Xác Nhận Cho Phép Nộp Hồ Sơ Bảo Vệ"</em> trên hệ thống.
                </div>

                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-primary rounded-pill px-4 py-2 d-inline-flex align-items-center">
                        <i class="fa-solid fa-chart-line me-2"></i>Xem Báo Cáo Tiến Độ
                    </a>
                    <a href="{{ route('sinhvien.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                        Về Bảng Điều Khiển
                    </a>
                </div>
            </div>
        </div>

    {{-- Trường hợp 3 & 4: Đủ điều kiện và quản lý hồ sơ --}}
    @else
        <!-- Thông Tin Nhóm & Đề Tài Khóa Luận -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
            <div class="card-body p-4">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small">
                                {{ $nhom->hocPhan->TenHocPhan ?? 'Khóa luận tốt nghiệp' }}
                            </span>
                            <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small">
                                Học kỳ {{ $nhom->hocKy->TenHocKy ?? $nhom->hocKy->HocKySo ?? 'Hiện tại' }}
                            </span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">
                            {{ $nhom->deTai->TenDeTai }}
                        </h5>
                        <div class="d-flex flex-wrap align-items-center gap-3 text-secondary small">
                            <div>
                                <i class="fa-solid fa-users text-primary me-2"></i>
                                Nhóm: <strong>{{ $nhom->TenNhom }}</strong>
                            </div>
                            <div>
                                <i class="fa-solid fa-user-tie text-success me-2"></i>
                                GVHD: <strong>{{ $nhom->deTai->giangVien->HoTen ?? 'Chưa phân công' }}</strong>
                            </div>
                            <div>
                                <i class="fa-solid fa-circle-check text-info me-2"></i>
                                Tiến độ: <strong class="text-success">Đã hoàn thành 5/5 Mốc</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 text-lg-end pt-2 pt-lg-0 border-top border-lg-top-0">
                        <div class="small text-muted mb-1">Thành viên thực hiện:</div>
                        <div class="d-flex flex-column align-items-lg-end gap-1">
                            @foreach($nhom->thanhViens as $tv)
                                <div class="small text-dark">
                                    <i class="fa-solid fa-user-graduate text-secondary me-2"></i>
                                    {{ $tv->sinhVien->HoTen ?? 'Sinh viên' }} 
                                    <span class="font-monospace text-muted">({{ $tv->MaSV }})</span>
                                    @if(str_contains(mb_strtolower($tv->VaiTro ?? ''), 'trưởng'))
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill py-0 px-2" style="font-size: 11px;">Trưởng nhóm</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $isApproved = $hoSo && in_array($hoSo->TrangThai, ['Đã phê duyệt', 'Đủ điều kiện bảo vệ', 'Đã phân công', 'Đã phân công hội đồng', 'Đã lưu trữ']);
            $isRevisionRequested = $hoSo && $hoSo->TrangThai === 'Yêu cầu chỉnh sửa';
            $isRejected = $hoSo && in_array($hoSo->TrangThai, ['Không duyệt', 'Không đủ điều kiện']);
            $isPending = $hoSo && in_array($hoSo->TrangThai, ['Chờ GVHD kiểm tra', 'Đang được kiểm tra', 'Đã nộp lại - Chờ kiểm tra', 'Chờ thẩm định']);
            $hasSubmitted = (bool)$hoSo;
            $phienBanLatest = $hoSo?->phienBans->first();
        @endphp

        <!-- Thông báo Trạng thái Hồ sơ chi tiết -->
        @if($isRevisionRequested)
            <div class="card border-0 shadow-sm mb-4 border-start border-warning border-4" style="border-radius: 12px; background: #fffcf0;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <span class="rounded-circle bg-warning text-dark d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                        </span>
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <h6 class="fw-bold text-dark mb-0">
                                    Yêu Cầu Chỉnh Sửa / Bổ Sung Hồ Sơ Bảo Vệ Từ Giảng Viên Hướng Dẫn
                                </h6>
                                @if($hoSo->HanNopLai)
                                    <span class="badge bg-danger text-white px-3 py-2 rounded-pill small">
                                        <i class="fa-regular fa-clock me-2"></i>Hạn nộp lại: {{ \Carbon\Carbon::parse($hoSo->HanNopLai)->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Các thành phần cần sửa -->
                            @if(!empty($hoSo->ThanhPhanCanSua) && is_array($hoSo->ThanhPhanCanSua))
                                @php
                                    $labels = [
                                        'bao_cao'       => ['icon' => 'fa-file-pdf', 'label' => 'Báo cáo khóa luận toàn văn (PDF)'],
                                        'source_code'   => ['icon' => 'fa-code', 'label' => 'Mã nguồn dự án (GitHub/GitLab)'],
                                        'link_deploy'   => ['icon' => 'fa-globe', 'label' => 'Đường dẫn triển khai / Demo sản phẩm'],
                                        'turnitin'      => ['icon' => 'fa-shield-halved', 'label' => 'Minh chứng kiểm tra đạo văn Turnitin'],
                                        'tai_lieu_khac' => ['icon' => 'fa-paperclip', 'label' => 'Tài liệu hướng dẫn / Liên kết bổ sung'],
                                    ];
                                @endphp
                                <div class="mb-3">
                                    <div class="fw-bold text-secondary small mb-2">Các thành phần cần kiểm tra và cập nhật lại:</div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($hoSo->ThanhPhanCanSua as $tp)
                                            @if(isset($labels[$tp]))
                                                <span class="badge bg-white text-danger border border-danger-subtle px-3 py-2 rounded-pill shadow-xs">
                                                    <i class="fa-solid {{ $labels[$tp]['icon'] }} me-2"></i>{{ $labels[$tp]['label'] }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Nhận xét của GVHD -->
                            <div class="p-3 bg-white rounded-3 border">
                                <div class="fw-bold text-dark small mb-1">
                                    <i class="fa-solid fa-comment-dots text-warning me-2"></i>Ý kiến nhận xét &amp; hướng dẫn sửa đổi của GVHD:
                                </div>
                                <div class="text-secondary" style="font-size: 13.5px; line-height: 1.6;">
                                    {!! \App\Helpers\HtmlHelper::clean($hoSo->NhanXetGVHD ?: 'Vui lòng hoàn thiện các thành phần được đánh dấu và nộp lại hồ sơ.') !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($isRejected)
            <div class="card border-0 shadow-sm mb-4 border-start border-danger border-4" style="border-radius: 12px; background: #fff5f5;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <span class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fa-solid fa-circle-xmark fs-5"></i>
                        </span>
                        <div>
                            <h6 class="fw-bold text-danger mb-2">Hồ Sơ Chưa Được Phê Duyệt Bảo Vệ</h6>
                            <p class="text-secondary mb-2" style="font-size: 13.5px;">
                                Giảng viên hướng dẫn ghi nhận hồ sơ chưa đạt tiêu chuẩn để tham gia bảo vệ trước Hội đồng.
                            </p>
                            @if($hoSo->NhanXetGVHD)
                                <div class="p-3 bg-white rounded-3 border border-danger-subtle">
                                    <div class="fw-bold text-danger small mb-1">Lý do từ chối:</div>
                                    <div class="text-dark small">{!! \App\Helpers\HtmlHelper::clean($hoSo->NhanXetGVHD) !!}</div>
                                </div>
                            @endif
                            <div class="mt-3">
                                <a href="#formNopHoSo" class="btn btn-sm btn-danger rounded-pill px-3">
                                    <i class="fa-solid fa-rotate me-2"></i>Nộp Lại Hồ Sơ Sau Khi Khắc Phục
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($isPending)
            <div class="card border-0 shadow-sm mb-4 border-start border-info border-4" style="border-radius: 12px; background: #f0f9ff;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <span class="rounded-circle bg-info text-white d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fa-solid fa-hourglass-half fs-5"></i>
                        </span>
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                                <h6 class="fw-bold text-dark mb-0">Hồ Sơ Đã Được Tiếp Nhận – Đang Chờ GVHD Đánh Giá</h6>
                                <span class="badge bg-white text-info border border-info-subtle px-3 py-2 rounded-pill small">
                                    <i class="fa-regular fa-clock me-2"></i>Nộp lúc: {{ $hoSo->NgayNopGanNhat ? \Carbon\Carbon::parse($hoSo->NgayNopGanNhat)->format('d/m/Y H:i') : \Carbon\Carbon::parse($hoSo->NgayNop)->format('d/m/Y') }}
                                </span>
                            </div>
                            <p class="text-secondary mb-0" style="font-size: 13.5px;">
                                Hồ sơ bảo vệ của nhóm đã được gửi đến Giảng viên hướng dẫn <strong>{{ $nhom->deTai->giangVien->HoTen }}</strong>. Thầy/Cô sẽ kiểm tra báo cáo, mã nguồn và sản phẩm thực hiện để phê duyệt điều kiện bảo vệ.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- BẢNG TỔNG KẾT HỒ SƠ ĐÃ NỘP HIỆN TẠI (Hiển thị nếu đã nộp hồ sơ) --}}
        @if($hasSubmitted)
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-box-archive text-primary fs-5"></i>
                        <h6 class="fw-bold text-dark mb-0">Thành Phần Hồ Sơ Bảo Vệ Hiện Tại</h6>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark border px-3 py-1 font-monospace">Mã hồ sơ: {{ $hoSo->MaHoSo }}</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">Phiên bản {{ number_format($hoSo->PhienBanHienTai ?? 1, 1, '.', '') }}</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- 1. Báo cáo toàn văn PDF -->
                        <div class="col-md-6 col-xl-3">
                            <div class="p-3 bg-light rounded-3 h-100 border d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 small">
                                            <i class="fa-solid fa-file-pdf me-2"></i>Báo Cáo
                                        </span>
                                        <i class="fa-solid fa-circle-check text-success"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Báo Cáo Khóa Luận Toàn Văn</h6>
                                    <div class="text-truncate text-muted small mb-2" title="{{ $phienBanLatest?->TenFileBaoCao ?: 'BaoCaoKhoaLuan.pdf' }}">
                                        {{ $phienBanLatest?->TenFileBaoCao ?: 'Báo cáo toàn văn.pdf' }}
                                    </div>
                                    @if($phienBanLatest?->DungLuongBaoCao)
                                        <div class="text-secondary small mb-3">
                                            Dung lượng: {{ number_format($phienBanLatest->DungLuongBaoCao / (1024 * 1024), 2) }} MB
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    @if($hoSo->ToanVanKhoaLuan)
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger flex-grow-1 rounded-pill" onclick="openPdfModal('{{ asset('storage/' . $hoSo->ToanVanKhoaLuan) }}', 'Báo Cáo Khóa Luận Toàn Văn')">
                                                <i class="fa-solid fa-eye me-1"></i>Xem Báo Cáo
                                            </button>
                                            <a href="{{ asset('storage/' . $hoSo->ToanVanKhoaLuan) }}" download class="btn btn-sm btn-danger rounded-circle px-2.5 d-flex align-items-center justify-content-center" style="width: 31px; height: 31px;" title="Tải xuống tệp PDF">
                                                <i class="fa-solid fa-download"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary w-100 py-2">Chưa có tệp</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- 2. Mã nguồn dự án -->
                        <div class="col-md-6 col-xl-3">
                            <div class="p-3 bg-light rounded-3 h-100 border d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-dark-subtle text-dark border rounded-pill px-2 py-1 small">
                                            <i class="fa-brands fa-github me-2"></i>Source Code
                                        </span>
                                        @if($hoSo->LinkSourceCode)
                                            <i class="fa-solid fa-circle-check text-success"></i>
                                        @else
                                            <i class="fa-solid fa-circle-exclamation text-warning"></i>
                                        @endif
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Mã Nguồn Dự Án</h6>
                                    @if($hoSo->LinkSourceCode)
                                        <div class="text-truncate small mb-2">
                                            <a href="{{ $hoSo->LinkSourceCode }}" target="_blank" class="text-primary text-decoration-underline" title="{{ $hoSo->LinkSourceCode }}">
                                                {{ $hoSo->LinkSourceCode }}
                                            </a>
                                        </div>
                                        @if($hoSo->GhiChuSourceCode)
                                            <div class="text-secondary small mb-3 text-truncate" title="{{ $hoSo->GhiChuSourceCode }}">
                                                <i class="fa-solid fa-circle-info me-2"></i>{{ $hoSo->GhiChuSourceCode }}
                                            </div>
                                        @endif
                                    @else
                                        <p class="text-muted small mb-3">Chưa cung cấp liên kết mã nguồn</p>
                                    @endif
                                </div>
                                <div>
                                    @if($hoSo->LinkSourceCode)
                                        <a href="{{ $hoSo->LinkSourceCode }}" target="_blank" class="btn btn-sm btn-outline-dark w-100 rounded-pill">
                                            <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Mở Repository
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- 3. Triển khai sản phẩm (Deploy) -->
                        <div class="col-md-6 col-xl-3">
                            <div class="p-3 bg-light rounded-3 h-100 border d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1 small">
                                            <i class="fa-solid fa-globe me-2"></i>Deploy / Demo
                                        </span>
                                        @if($hoSo->LinkDeploy)
                                            <i class="fa-solid fa-circle-check text-success"></i>
                                        @else
                                            <i class="fa-solid fa-circle-info text-secondary"></i>
                                        @endif
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Triển Khai Trực Tuyến</h6>
                                    @if($hoSo->LinkDeploy)
                                        <div class="text-truncate small mb-2">
                                            <a href="{{ $hoSo->LinkDeploy }}" target="_blank" class="text-primary text-decoration-underline" title="{{ $hoSo->LinkDeploy }}">
                                                {{ $hoSo->LinkDeploy }}
                                            </a>
                                        </div>
                                        @if($hoSo->GhiChuDeploy)
                                            <div class="text-secondary small mb-3 text-truncate" title="{{ $hoSo->GhiChuDeploy }}">
                                                <i class="fa-solid fa-key me-2"></i>{{ $hoSo->GhiChuDeploy }}
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-muted small mb-3">
                                            <span class="badge bg-secondary-subtle text-secondary">Không triển khai online</span>
                                            @if($hoSo->LyDoKhongDeploy)
                                                <div class="mt-1 text-truncate" title="{{ $hoSo->LyDoKhongDeploy }}">
                                                    Lý do: {{ $hoSo->LyDoKhongDeploy }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    @if($hoSo->LinkDeploy)
                                        <a href="{{ $hoSo->LinkDeploy }}" target="_blank" class="btn btn-sm btn-outline-info w-100 rounded-pill">
                                            <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Truy Cập Ứng Dụng
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- 4. Minh chứng Turnitin -->
                        <div class="col-md-6 col-xl-3">
                            <div class="p-3 bg-light rounded-3 h-100 border d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-2 py-1 small">
                                            <i class="fa-solid fa-shield-halved me-2"></i>Turnitin
                                        </span>
                                        @php
                                            $turnitinScore = $phienBanLatest?->TyLeTurnitin ?? $hoSo->TyLeTrungLap ?? 0;
                                        @endphp
                                        @if($turnitinScore <= 20)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">
                                                &le; 20% Đạt
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 small">
                                                &gt; 20% Vượt ngưỡng
                                            </span>
                                        @endif
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Tỷ Lệ Trùng Lặp</h6>
                                    <div class="display-6 fw-bold mb-1 {{ $turnitinScore <= 20 ? 'text-primary' : 'text-danger' }}" style="font-size: 1.8rem;">
                                        {{ number_format($turnitinScore, 1) }}%
                                    </div>
                                    <div class="text-secondary small mb-3">
                                        Quy định: Không vượt quá 20%
                                    </div>
                                </div>
                                <div>
                                    @if($hoSo->MinhChungDaoVan)
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary flex-grow-1 rounded-pill" onclick="openPdfModal('{{ asset('storage/' . $hoSo->MinhChungDaoVan) }}', 'Minh Chứng Kết Quả Turnitin')">
                                                <i class="fa-solid fa-file-shield me-1"></i>Xem Minh Chứng
                                            </button>
                                            <a href="{{ asset('storage/' . $hoSo->MinhChungDaoVan) }}" download class="btn btn-sm btn-primary rounded-circle px-2.5 d-flex align-items-center justify-content-center" style="width: 31px; height: 31px;" title="Tải xuống tệp PDF">
                                                <i class="fa-solid fa-download"></i>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách tài liệu, slide & liên kết bổ sung nếu có -->
                    @if(!empty($hoSo->LienKetKhac) && is_array($hoSo->LienKetKhac) && count($hoSo->LienKetKhac) > 0)
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="fa-solid fa-paperclip text-primary me-2"></i>Tài Liệu, Slide &amp; Liên Kết Bổ Sung Đã Nộp:
                            </h6>
                            <div class="row g-2">
                                @foreach($hoSo->LienKetKhac as $lk)
                                    @php
                                        $isLkFile = ($lk['loai'] ?? '') === 'file' || !empty($lk['file_path']);
                                        $fileExt = $isLkFile ? strtolower(pathinfo($lk['ten_file'] ?? $lk['file_path'], PATHINFO_EXTENSION)) : '';
                                        $badgeIcon = 'fa-solid fa-link text-primary';
                                        if ($isLkFile) {
                                            if (in_array($fileExt, ['ppt', 'pptx'])) {
                                                $badgeIcon = 'fa-solid fa-file-powerpoint text-danger';
                                            } elseif ($fileExt === 'pdf') {
                                                $badgeIcon = 'fa-solid fa-file-pdf text-danger';
                                            } elseif (in_array($fileExt, ['doc', 'docx'])) {
                                                $badgeIcon = 'fa-solid fa-file-word text-primary';
                                            } elseif (in_array($fileExt, ['zip', 'rar', '7z'])) {
                                                $badgeIcon = 'fa-solid fa-file-zipper text-secondary';
                                            } elseif (in_array($fileExt, ['mp4', 'avi', 'mov', 'mkv'])) {
                                                $badgeIcon = 'fa-solid fa-file-video text-info';
                                            } else {
                                                $badgeIcon = 'fa-solid fa-file-lines text-secondary';
                                            }
                                        }
                                    @endphp
                                    <div class="col-md-6 col-lg-4">
                                        <div class="p-3 bg-white rounded-3 border d-flex align-items-center justify-content-between gap-2 shadow-xs h-100">
                                            <div class="text-truncate">
                                                <div class="fw-bold text-dark small text-truncate d-flex align-items-center gap-2">
                                                    <i class="{{ $badgeIcon }}"></i>
                                                    <span title="{{ $lk['tieu_de'] ?? 'Tài liệu' }}">{{ $lk['tieu_de'] ?? 'Tài liệu' }}</span>
                                                </div>
                                                @if($isLkFile && !empty($lk['ten_file']))
                                                    <div class="text-muted small text-truncate" style="font-size: 11.5px;">
                                                        <i class="fa-solid fa-paperclip me-1"></i>{{ $lk['ten_file'] }}
                                                    </div>
                                                @endif
                                                @if(!empty($lk['mo_ta']))
                                                    <div class="text-muted small text-truncate" title="{{ $lk['mo_ta'] }}">{{ $lk['mo_ta'] }}</div>
                                                @endif
                                            </div>
                                            @if($isLkFile && !empty($lk['file_path']))
                                                @if($fileExt === 'pdf')
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill flex-shrink-0 px-3" onclick="openPdfModal('{{ asset('storage/' . $lk['file_path']) }}', '{{ addslashes($lk['tieu_de'] ?? 'Tài liệu PDF') }}')">
                                                        <i class="fa-solid fa-eye me-1"></i> Xem file
                                                    </button>
                                                @else
                                                    <a href="{{ asset('storage/' . $lk['file_path']) }}" download class="btn btn-sm btn-outline-success rounded-pill flex-shrink-0 px-3" title="Tải xuống tệp">
                                                        <i class="fa-solid fa-download me-1"></i> Tải file
                                                    </a>
                                                @endif
                                            @elseif(!empty($lk['url']))
                                                <a href="{{ $lk['url'] }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill flex-shrink-0 px-3" title="Mở liên kết">
                                                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở link
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- BẢN XEM TRỰC TIẾP BÁO CÁO KHÓA LUẬN TOÀN VĂN TRÊN MÀN HÌNH (KHÔNG QUA TAB MỚI) --}}
            @if($hoSo->ToanVanKhoaLuan)
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
                            Bản Xem Trực Tiếp Báo Cáo Khóa Luận Toàn Văn (PDF)
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="togglePdfInlineViewer()">
                                <i class="fa-solid fa-arrows-up-down me-1"></i> <span id="togglePdfText">Thu gọn</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="openPdfModal('{{ asset('storage/' . $hoSo->ToanVanKhoaLuan) }}', 'Báo Cáo Khóa Luận Toàn Văn')">
                                <i class="fa-solid fa-maximize me-1"></i> Phóng to màn hình
                            </button>
                            <a href="{{ asset('storage/' . $hoSo->ToanVanKhoaLuan) }}" download class="btn btn-sm btn-danger rounded-pill px-3">
                                <i class="fa-solid fa-download me-1"></i> Tải Về PDF
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0" id="pdfInlineContainer">
                        <div style="height: 650px; width: 100%; background: #f8fafc;">
                            <iframe src="{{ asset('storage/' . $hoSo->ToanVanKhoaLuan) }}#toolbar=1" width="100%" height="100%" style="border: none;">
                                <div class="p-4 text-center">
                                    <p class="text-muted">Trình duyệt không hỗ trợ nhúng tệp PDF trực tiếp.</p>
                                    <button type="button" class="btn btn-primary rounded-pill" onclick="openPdfModal('{{ asset('storage/' . $hoSo->ToanVanKhoaLuan) }}', 'Báo Cáo Khóa Luận Toàn Văn')">
                                        Xem Báo Cáo Trên Màn Hình
                                    </button>
                                </div>
                            </iframe>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        {{-- CHI TIẾT KHI ĐÃ ĐƯỢC PHÊ DUYỆT ĐỦ ĐIỀU KIỆN HOẶC ĐÃ CÓ LỊCH BẢO VỆ --}}
        @if($isApproved)
            <div class="row g-4 mb-4">
                <!-- Cột trái: Thông tin phê duyệt của GVHD -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                        <div class="card-header bg-white py-3 px-4 border-bottom">
                            <h6 class="fw-bold text-success mb-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-check fs-5"></i>
                                Giảng Viên Hướng Dẫn Xác Nhận Đủ Điều Kiện Bảo Vệ
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-success bg-success-subtle border-success-subtle mb-3">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="fa-solid fa-award text-success fs-5"></i>
                                    <strong class="text-dark">Hồ sơ đã được phê duyệt chính thức!</strong>
                                </div>
                                <div class="text-secondary small">
                                    Giảng viên hướng dẫn đã kiểm tra toàn bộ báo cáo, mã nguồn và sản phẩm thực hiện, xác nhận nhóm đạt chuẩn tham gia bảo vệ trước Hội đồng.
                                </div>
                            </div>

                            <div class="border rounded-3 p-3 bg-light-subtle mb-3" style="font-size: 13.5px;">
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span class="text-muted">Giảng viên phê duyệt:</span>
                                    <span class="fw-bold text-dark text-end">{{ $nhom->deTai->giangVien->HoTen ?? 'GVHD' }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span class="text-muted">Ngày xác nhận:</span>
                                    <span class="fw-semibold text-dark text-end">{{ $hoSo->NgayXacNhan ? \Carbon\Carbon::parse($hoSo->NgayXacNhan)->format('d/m/Y') : 'Đã duyệt' }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2">
                                    <span class="text-muted">Trạng thái hồ sơ:</span>
                                    <span class="badge bg-success rounded-pill px-3 py-1">Đủ điều kiện bảo vệ</span>
                                </div>
                            </div>

                            @if($hoSo->NhanXetGVHD)
                                <div class="p-3 bg-light rounded-3 border" style="font-size: 13.5px;">
                                    <div class="text-muted fw-bold mb-1 d-flex align-items-center gap-1.5">
                                        <i class="fa-solid fa-comment-dots text-success"></i>
                                        <span>Ý kiến nhận xét &amp; đánh giá của Giảng viên:</span>
                                    </div>
                                    <div class="text-dark bg-white p-2.5 rounded border mt-1" style="word-break: break-word; overflow-wrap: anywhere; white-space: normal;">
                                        {!! \App\Helpers\HtmlHelper::clean($hoSo->NhanXetGVHD) !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Cột phải: Thông tin Hội đồng & Lịch bảo vệ nếu có -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                        <div class="card-header bg-white py-3 px-4 border-bottom">
                            <h6 class="fw-bold text-primary mb-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-landmark fs-5"></i>
                                Hội Đồng Bảo Vệ Khóa Luận Tốt Nghiệp
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            @if($hoSo->hoiDong && $hoSo->hoiDong->TrangThaiCongBo === 'Đã công bố')
                                <div class="p-3 bg-light rounded-3 border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="fw-bold text-primary fs-6">
                                            <i class="fa-solid fa-building-columns me-2"></i>{{ $hoSo->hoiDong->TenHoiDong }}
                                        </div>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                            <i class="fa-solid fa-circle-check me-1"></i> Đã công bố chính thức
                                        </span>
                                    </div>
                                    <div class="row g-2 text-secondary small">
                                        <div class="col-sm-6">
                                            <i class="fa-regular fa-calendar-days text-primary me-2"></i>
                                            <strong>Thời gian:</strong>
                                            {{ \Carbon\Carbon::parse($hoSo->ThoiGianBaoVe ?? $hoSo->hoiDong->ThoiGianBatDau)->format('d/m/Y H:i') }}
                                        </div>
                                        <div class="col-sm-6">
                                            <i class="fa-solid fa-location-dot text-danger me-2"></i>
                                            <strong>Phòng bảo vệ:</strong>
                                            {{ $hoSo->PhongBaoVe ?? $hoSo->hoiDong->DiaDiem ?? 'Theo thông báo' }}
                                        </div>
                                    </div>
                                    @if($hoSo->hoiDong->FileQuyetDinh)
                                    <div class="mt-2 pt-2 border-top">
                                        <a href="{{ asset($hoSo->hoiDong->FileQuyetDinh) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-file-pdf me-1"></i> Tải Quyết định &amp; Danh sách bảo vệ (PDF)
                                        </a>
                                    </div>
                                    @endif
                                </div>

                                <div class="mb-2">
                                    <div class="fw-bold text-muted small mb-2 text-uppercase">Thành viên Hội đồng:</div>
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($hoSo->hoiDong->thanhViens as $tv)
                                            <div class="d-flex justify-content-between align-items-center bg-white p-2 px-3 rounded-3 border small">
                                                <div>
                                                    <i class="fa-solid fa-user-tie text-secondary me-2"></i>
                                                    <strong>{{ $tv->giangVien->HoTen ?? 'Giảng viên' }}</strong>
                                                    @if(!empty($tv->giangVien->HocVi))
                                                        <span class="text-muted">({{ $tv->giangVien->HocVi }})</span>
                                                    @endif
                                                </div>
                                                <span class="badge bg-light text-dark border rounded-pill px-3 py-1">{{ $tv->VaiTro }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif($hoSo->hoiDong)
                                <div class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-clock-rotate-left fa-2x text-warning mb-2"></i>
                                    <div class="fw-bold text-dark">Lịch bảo vệ đang được Trưởng khoa chuẩn bị &amp; phê duyệt</div>
                                    <p class="mb-0 mt-1 small">
                                        Hội đồng bảo vệ của nhóm đang trong quá trình hoàn thiện quyết định thành lập. Thông tin hội đồng, thời gian và địa điểm chính thức sẽ được công bố ngay sau khi Trưởng khoa ký ban hành văn bản.
                                    </p>
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-hourglass-start fa-2x text-warning mb-2"></i>
                                    <div class="fw-bold text-dark">Hồ sơ đã sẵn sàng để xếp lịch bảo vệ</div>
                                    <p class="mb-0 mt-1 small">
                                        Khoa đang tổng hợp danh sách các nhóm đủ điều kiện để phân công Hội đồng và công bố lịch bảo vệ chính thức.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- FORM NỘP / CẬP NHẬT HỒ SƠ BẢO VỆ (Hiển thị khi chưa nộp, hoặc khi GVHD yêu cầu sửa, hoặc từ chối) --}}
        @if(!$isApproved)
            @if(!$isLeader)
                <!-- KHU VỰC THÔNG BÁO CHO THÀNH VIÊN KHÔNG PHẢI NHÓM TRƯỞNG -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-user-lock text-warning"></i>
                            Khu Vực Nộp Hồ Sơ Bảo Vệ
                        </h6>
                    </div>
                    <div class="card-body p-4 text-center py-5">
                        <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                            <i class="fa-solid fa-user-shield fs-2"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Quyền Nộp Hồ Sơ Dành Riêng Cho Nhóm Trưởng</h5>
                        <p class="text-secondary mb-3 mx-auto" style="max-width: 580px;">
                            Theo quy định, chỉ <strong>Nhóm trưởng ({{ $nhom->truongNhom?->sinhVien?->HoTen ?? 'Trưởng nhóm' }})</strong> mới có quyền đại diện nhóm nộp hoặc cập nhật các tài liệu trong Hồ sơ bảo vệ. Các thành viên khác có quyền theo dõi tiến độ và xem lại tài liệu trực tiếp trên hệ thống.
                        </p>
                        <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                            <i class="fa-solid fa-info-circle me-1"></i> Bạn đang đăng nhập với vai trò thành viên nhóm
                        </span>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm mb-4" id="formNopHoSo" style="border-radius: 14px;">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-cloud-arrow-up text-primary fs-5"></i>
                            <h6 class="fw-bold text-dark mb-0">
                                {{ $hasSubmitted ? 'Cập Nhật / Nộp Lại Hồ Sơ Bảo Vệ' : 'Nộp Hồ Sơ Bảo Vệ Khóa Luận Tốt Nghiệp' }}
                            </h6>
                        </div>
                        @if($hasSubmitted)
                            <span class="badge bg-warning-subtle text-dark border px-3 py-1">
                                Sẽ tạo Phiên bản {{ number_format(($hoSo->PhienBanHienTai ?? 1) + 1, 1, '.', '') }}
                            </span>
                        @endif
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('sinhvien.hoso.store') }}" method="POST" enctype="multipart/form-data" id="dossierForm">
                        @csrf
                        <input type="hidden" name="MaNhom" value="{{ $nhom->MaNhom }}">

                        <!-- KHỐI 1: BÁO CÁO TOÀN VĂN & TURNITIN -->
                        <div class="p-3 bg-light rounded-3 mb-4 border">
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                <span class="badge bg-primary text-white rounded-circle px-2">1</span>
                                Báo Cáo Khóa Luận Toàn Văn &amp; Kết Quả Turnitin
                            </h6>

                            <div class="row g-3">
                                <!-- File báo cáo PDF -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">
                                        Tệp Báo Cáo Khóa Luận Toàn Văn (PDF) 
                                        @if(!$hasSubmitted) <span class="text-danger">*</span> @endif
                                    </label>
                                    <input type="file" name="FileToanVan" class="form-control" accept=".pdf" {{ !$hasSubmitted ? 'required' : '' }}>
                                    <div class="form-text text-muted small">
                                        @if($hoSo && $hoSo->ToanVanKhoaLuan)
                                            <span class="text-success"><i class="fa-solid fa-circle-check me-2"></i>Đã có tệp hiện tại. Tải tệp mới nếu muốn thay thế.</span>
                                        @else
                                            Định dạng PDF hoàn chỉnh, dung lượng tối đa 50MB.
                                        @endif
                                    </div>
                                </div>

                                <!-- Tỷ lệ Turnitin -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">
                                        Tỷ Lệ Trùng Lặp Turnitin (%) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" name="TyLeTrungLap" id="inputTyLeTurnitin" class="form-control" step="0.01" min="0" max="100"
                                            placeholder="VD: 12.5" 
                                            value="{{ old('TyLeTrungLap', $phienBanLatest?->TyLeTurnitin ?? $hoSo?->TyLeTrungLap ?? '') }}" required>
                                        <span class="input-group-text bg-white fw-bold">%</span>
                                    </div>
                                    <div class="form-text small" id="turnitinNotice">
                                        Quy định: Tỷ lệ phải <strong>&le; 20%</strong> để được công nhận hợp lệ.
                                    </div>
                                </div>

                                <!-- Minh chứng Turnitin PDF -->
                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-dark small">
                                        Minh Chứng Kết Quả Kiểm Tra Đạo Văn Turnitin (PDF)
                                        @if(!$hasSubmitted) <span class="text-danger">*</span> @endif
                                    </label>
                                    <input type="file" name="MinhChungTurnitin" class="form-control" accept=".pdf" {{ !$hasSubmitted ? 'required' : '' }}>
                                    <div class="form-text text-muted small">
                                        @if($hoSo && $hoSo->MinhChungDaoVan)
                                            <span class="text-success"><i class="fa-solid fa-circle-check me-2"></i>Đã có minh chứng hiện tại. Tải tệp mới nếu cập nhật kết quả kiểm tra.</span>
                                        @else
                                            Tệp PDF báo cáo chi tiết Similarity Report xuất trực tiếp từ hệ thống Turnitin (tối đa 30MB).
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KHỐI 2: MÃ NGUỒN DỰ ÁN (GIT REPOSITORY) -->
                        <div class="p-3 bg-light rounded-3 mb-4 border">
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                <span class="badge bg-dark text-white rounded-circle px-2">2</span>
                                Mã Nguồn Dự Án (GitHub / GitLab / Bitbucket)
                            </h6>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-dark small">
                                        Đường Dẫn Repository Mã Nguồn <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fa-brands fa-github"></i></span>
                                        <input type="url" name="LinkSourceCode" id="inputLinkSourceCode" class="form-control" 
                                            placeholder="https://github.com/tai-khoan/du-an-khoa-luan" 
                                            value="{{ old('LinkSourceCode', $hoSo->LinkSourceCode ?? '') }}" required>
                                    </div>
                                    <div class="form-text text-muted small">
                                        Đường dẫn công khai (Public) hoặc cung cấp quyền truy cập để GVHD kiểm tra commit history và cấu trúc code.
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-dark small">
                                        Hướng Dẫn Cấu Trúc Mã Nguồn &amp; Tài Khoản Kiểm Thử (Nếu có)
                                    </label>
                                    <textarea name="GhiChuSourceCode" class="form-control" rows="2" 
                                        placeholder="Ví dụ: Nhánh chính: main | Thư mục backend: /server, frontend: /client | Hướng dẫn cài đặt xem README.md...">{{ old('GhiChuSourceCode', $hoSo->GhiChuSourceCode ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- KHỐI 3: ĐƯỜNG DẪN TRIỂN KHAI SẢN PHẨM (DEPLOY) -->
                        <div class="p-3 bg-light rounded-3 mb-4 border">
                            <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                <span class="badge bg-info text-white rounded-circle px-2">3</span>
                                Triển Khai Trực Tuyến &amp; Trải Nghiệm Sản Phẩm (Deploy)
                            </h6>

                            @php
                                $hasDeployVal = old('CoTrienKhai', ($hoSo && $hoSo->LyDoKhongDeploy && !$hoSo->LinkDeploy) ? 'khong' : 'co');
                            @endphp

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small d-block">Trạng thái triển khai trực tuyến <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="CoTrienKhai" id="deploy_co" value="co" {{ $hasDeployVal === 'co' ? 'checked' : '' }} onchange="toggleDeploySection()">
                                    <label class="form-check-label fw-semibold text-dark small" for="deploy_co">
                                        <i class="fa-solid fa-cloud text-success me-2"></i>Có triển khai trực tuyến (Web / App / Cloud Service)
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="CoTrienKhai" id="deploy_khong" value="khong" {{ $hasDeployVal === 'khong' ? 'checked' : '' }} onchange="toggleDeploySection()">
                                    <label class="form-check-label fw-semibold text-dark small" for="deploy_khong">
                                        <i class="fa-solid fa-ban text-secondary me-2"></i>Không thể triển khai trực tuyến (IoT, phần cứng, mạng nội bộ...)
                                    </label>
                                </div>
                            </div>

                            <!-- Vùng Nhập Link Deploy -->
                            <div id="sectionDeployCo" class="row g-3" style="{{ $hasDeployVal === 'khong' ? 'display: none;' : '' }}">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-dark small">
                                        Đường Dẫn Triển Khai (Deploy URL) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fa-solid fa-globe"></i></span>
                                        <input type="url" name="LinkDeploy" id="inputLinkDeploy" class="form-control" 
                                            placeholder="https://du-an-khoa-luan.vercel.app hoặc http://ten-mien-du-an.com" 
                                            value="{{ old('LinkDeploy', $hoSo->LinkDeploy ?? '') }}">
                                    </div>
                                    <div class="form-text text-muted small">Đường dẫn sản phẩm chạy thực tế để GVHD và Hội đồng trực tiếp trải nghiệm.</div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-dark small">
                                        Tài Khoản Thử Nghiệm &amp; Hướng Dẫn Truy Cập
                                    </label>
                                    <textarea name="GhiChuDeploy" class="form-control" rows="2" 
                                        placeholder="Ví dụ: Tài khoản Admin: admin@demo.com / Pass: 123456 | Tài khoản Người dùng: user@demo.com / Pass: 123456">{{ old('GhiChuDeploy', $hoSo->GhiChuDeploy ?? '') }}</textarea>
                                </div>
                            </div>

                            <!-- Vùng Nêu Lý Do Không Deploy -->
                            <div id="sectionDeployKhong" class="row g-3" style="{{ $hasDeployVal === 'co' ? 'display: none;' : '' }}">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-dark small">
                                        Lý Do Không Thể Triển Khai Trực Tuyến <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="LyDoKhongDeploy" id="inputLyDoKhongDeploy" class="form-control" rows="3" 
                                        placeholder="Ví dụ: Đề tài là mô hình thiết bị nhúng IoT kết nối mạng cục bộ vi điều khiển ESP32, hoặc giải pháp mạng nội bộ Enterprise không public IP...">{{ old('LyDoKhongDeploy', $hoSo->LyDoKhongDeploy ?? '') }}</textarea>
                                    <div class="form-text text-muted small">Cần giải trình rõ lý do kỹ thuật để GVHD xem xét và phê duyệt.</div>
                                </div>
                            </div>
                        </div>

                        <!-- KHỐI 4: LIÊN KẾT BỔ SUNG & TÀI LIỆU DEMO (DYNAMIC REPEATER) -->
                        <div class="p-3 bg-light rounded-3 mb-4 border">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary text-white rounded-circle px-2">4</span>
                                    Tài Liệu Bổ Sung, Video Demo &amp; Slide Thuyết Trình
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="addLienKetRow()">
                                    <i class="fa-solid fa-plus me-2"></i>Thêm Mục (Link / File)
                                </button>
                            </div>
                            <p class="text-muted small mb-3">
                                Bạn có thể nộp <strong>Đường dẫn liên kết (URL)</strong> (YouTube, Google Drive, Swagger API...) hoặc <strong>Tải lên tệp đính kèm (File)</strong> (Slide thuyết trình PowerPoint <code>.ppt, .pptx</code>, Hướng dẫn sử dụng <code>.pdf, .docx</code>, Video offline <code>.mp4</code>, tệp nén <code>.zip</code>).
                            </p>

                            <div id="lienKetContainer" class="d-flex flex-column gap-3">
                                @php
                                    $existingLinks = old('LienKetKhac', $hoSo->LienKetKhac ?? []);
                                    if (empty($existingLinks)) {
                                        $existingLinks = [
                                            ['tieu_de' => 'Slide Thuyết Trình Bảo Vệ', 'loai' => 'file', 'url' => '', 'mo_ta' => 'Slide trình bày PowerPoint khóa luận'],
                                        ];
                                    }
                                @endphp

                                @foreach($existingLinks as $index => $item)
                                    @php
                                        $isTypeFile = ($item['loai'] ?? '') === 'file' || (!empty($item['file_path']) && empty($item['url']));
                                    @endphp
                                    <div class="card p-3 border bg-white rounded-3 lien-ket-row shadow-xs">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-2">
                                                <select name="LienKetKhac[{{ $index }}][loai]" class="form-select form-select-sm fw-semibold" onchange="toggleLienKetType(this)">
                                                    <option value="link" {{ !$isTypeFile ? 'selected' : '' }}>🔗 Link URL</option>
                                                    <option value="file" {{ $isTypeFile ? 'selected' : '' }}>📁 Tệp File</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" name="LienKetKhac[{{ $index }}][tieu_de]" class="form-control form-control-sm" 
                                                    placeholder="Tiêu đề (VD: Slide, User Manual...)" value="{{ $item['tieu_de'] ?? '' }}">
                                            </div>
                                            <div class="col-md-4">
                                                <!-- Link Box -->
                                                <div class="input-url-box {{ $isTypeFile ? 'd-none' : '' }}">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-link"></i></span>
                                                        <input type="url" name="LienKetKhac[{{ $index }}][url]" class="form-control form-control-sm" 
                                                            placeholder="https://..." value="{{ $item['url'] ?? '' }}">
                                                    </div>
                                                </div>
                                                <!-- File Box -->
                                                <div class="input-file-box {{ !$isTypeFile ? 'd-none' : '' }}">
                                                    <input type="file" name="LienKetKhac[{{ $index }}][file]" class="form-control form-control-sm" 
                                                        accept=".ppt,.pptx,.pdf,.doc,.docx,.zip,.rar,.mp4,.png,.jpg,.jpeg">
                                                    @if(!empty($item['file_path']))
                                                        <input type="hidden" name="LienKetKhac[{{ $index }}][file_path]" value="{{ $item['file_path'] }}">
                                                        <input type="hidden" name="LienKetKhac[{{ $index }}][ten_file]" value="{{ $item['ten_file'] ?? '' }}">
                                                        <input type="hidden" name="LienKetKhac[{{ $index }}][dung_luong]" value="{{ $item['dung_luong'] ?? '' }}">
                                                        <div class="d-flex align-items-center gap-1 mt-1 small">
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle text-truncate" style="max-width: 220px;" title="{{ $item['ten_file'] ?? basename($item['file_path']) }}">
                                                                <i class="fa-solid fa-file-circle-check me-1"></i>{{ $item['ten_file'] ?? basename($item['file_path']) }}
                                                            </span>
                                                            <a href="{{ asset('storage/' . $item['file_path']) }}" target="_blank" download class="text-primary small text-decoration-none ms-1">
                                                                <i class="fa-solid fa-download"></i> Tải
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="text" name="LienKetKhac[{{ $index }}][mo_ta]" class="form-control form-control-sm" 
                                                    placeholder="Mô tả ngắn" value="{{ $item['mo_ta'] ?? '' }}">
                                            </div>
                                            <div class="col-md-1 text-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle" onclick="removeLienKetRow(this)" title="Xóa mục này">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- KHỐI 5: GHI CHÚ GỬI GVHD & NÚT NỘP -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">
                                <i class="fa-regular fa-comment-dots text-primary me-2"></i>Lời Nhắn / Ghi Chú Gửi Đến GVHD
                            </label>
                            <textarea name="GhiChuSinhVien" class="form-control" rows="2" 
                                placeholder="Ghi chú thêm gửi đến Thầy/Cô hướng dẫn về nội dung cập nhật hoặc lưu ý khi kiểm tra hồ sơ...">{{ old('GhiChuSinhVien') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('sinhvien.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4">Hủy Bỏ</a>
                            <button type="button" class="btn btn-primary rounded-pill px-4 py-2 d-inline-flex align-items-center shadow-sm" onclick="openConfirmModal()">
                                <i class="fa-solid fa-paper-plane me-2"></i>
                                {{ $hasSubmitted ? 'Xác Nhận Nộp Lại Hồ Sơ' : 'Xác Nhận & Nộp Hồ Sơ Bảo Vệ' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        @endif

        {{-- LỊCH SỬ CÁC PHIÊN BẢN HỒ SƠ ĐÃ NỘP (AUDIT TRAIL) --}}
        @if($hoSo && $hoSo->phienBans && $hoSo->phienBans->count() > 0)
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>
                        Lịch Sử Các Phiên Bản Hồ Sơ Bảo Vệ
                    </h6>
                    <span class="badge bg-light text-secondary border px-3 py-1">{{ $hoSo->phienBans->count() }} phiên bản ghi nhận</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 120px;">Phiên Bản</th>
                                    <th style="min-width: 150px;">Thời Gian Nộp</th>
                                    <th style="min-width: 250px;">Tóm Tắt Hồ Sơ Đã Nộp</th>
                                    <th style="min-width: 150px;">Trạng Thái Đánh Giá</th>
                                    <th style="min-width: 220px;">Nhận Xét Của GVHD</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hoSo->phienBans as $pb)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary px-3 py-1 rounded-pill font-monospace">
                                                v{{ $pb->PhienBan }}
                                            </span>
                                            @if($loop->first)
                                                <div class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0 mt-1" style="font-size: 11px;">Mới nhất</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $pb->NgayNop ? \Carbon\Carbon::parse($pb->NgayNop)->format('d/m/Y H:i') : '' }}</div>
                                            <div class="text-muted small">Người nộp: {{ $pb->NguoiNop }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                @if($pb->DuongDanBaoCao)
                                                    <button type="button" class="btn btn-link text-danger p-0 text-start text-truncate text-decoration-none" style="max-width: 240px;" onclick="openPdfModal('{{ asset('storage/' . $pb->DuongDanBaoCao) }}', 'Báo Cáo Khóa Luận Toàn Văn - Phiên Bản v{{ $pb->PhienBan }}')">
                                                        <i class="fa-solid fa-file-pdf me-2"></i>{{ $pb->TenFileBaoCao ?: 'Báo cáo toàn văn' }}
                                                    </button>
                                                @endif
                                                @if($pb->LinkSourceCode)
                                                    <a href="{{ $pb->LinkSourceCode }}" target="_blank" class="text-dark text-truncate" style="max-width: 240px;">
                                                        <i class="fa-brands fa-github me-2"></i>{{ $pb->LinkSourceCode }}
                                                    </a>
                                                @endif
                                                @if($pb->LinkDeploy)
                                                    <a href="{{ $pb->LinkDeploy }}" target="_blank" class="text-info text-truncate" style="max-width: 240px;">
                                                        <i class="fa-solid fa-globe me-2"></i>{{ $pb->LinkDeploy }}
                                                    </a>
                                                @elseif($pb->LyDoKhongDeploy)
                                                    <span class="text-muted small"><i class="fa-solid fa-ban me-2"></i>Không deploy: {{ Str::limit($pb->LyDoKhongDeploy, 30) }}</span>
                                                @endif
                                                @if($pb->TyLeTurnitin !== null)
                                                    <div class="d-flex align-items-center gap-1">
                                                        <span class="small {{ $pb->TyLeTurnitin <= 20 ? 'text-success' : 'text-danger' }}">
                                                            <i class="fa-solid fa-shield-halved me-1"></i>Turnitin: <strong>{{ number_format($pb->TyLeTurnitin, 1) }}%</strong>
                                                        </span>
                                                        @if($pb->DuongDanTurnitin)
                                                            <button type="button" class="btn btn-link text-primary p-0 text-decoration-none small" onclick="openPdfModal('{{ asset('storage/' . $pb->DuongDanTurnitin) }}', 'Minh Chứng Turnitin - Phiên Bản v{{ $pb->PhienBan }}')" title="Xem minh chứng Turnitin">
                                                                (Xem)
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $pbBadge = match($pb->TrangThai) {
                                                    'Đã phê duyệt' => 'bg-success text-white',
                                                    'Yêu cầu chỉnh sửa' => 'bg-warning text-dark',
                                                    'Từ chối' => 'bg-danger text-white',
                                                    default => 'bg-secondary text-white',
                                                };
                                            @endphp
                                            <span class="badge {{ $pbBadge }} rounded-pill px-3 py-1">
                                                {{ $pb->TrangThai }}
                                            </span>
                                            @if($pb->NgayDanhGia)
                                                <div class="text-muted small mt-1">
                                                    {{ \Carbon\Carbon::parse($pb->NgayDanhGia)->format('d/m/Y H:i') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($pb->NhanXetGVHD)
                                                <div class="text-dark small">{!! \App\Helpers\HtmlHelper::clean($pb->NhanXetGVHD) !!}</div>
                                            @else
                                                <span class="text-muted small">Chưa có nhận xét</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    @endif
</div>

<!-- Modal Xác Nhận Nộp Hồ Sơ -->
<div class="modal fade" id="modalConfirmSubmit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-question text-primary"></i>
                    Xác Nhận Nộp Hồ Sơ Bảo Vệ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-secondary small mb-3">
                    Vui lòng kiểm tra kỹ các thông tin trước khi gửi lên hệ thống. Sau khi nộp, Giảng viên hướng dẫn sẽ nhận được thông báo để kiểm tra và đánh giá điều kiện bảo vệ của nhóm.
                </p>
                <div class="p-3 bg-light rounded-3 border mb-3 small">
                    <div class="mb-2"><strong>Nhóm:</strong> {{ $nhom->TenNhom ?? '' }}</div>
                    <div class="mb-2"><strong>Đề tài:</strong> {{ $nhom->deTai->TenDeTai ?? '' }}</div>
                    <div class="mb-2"><strong>Mã nguồn (Repo):</strong> <span id="confirmSourceCode" class="text-break font-monospace text-primary"></span></div>
                    <div class="mb-2"><strong>Triển khai (Deploy):</strong> <span id="confirmDeploy" class="text-break text-dark"></span></div>
                    <div><strong>Tỷ lệ Turnitin:</strong> <span id="confirmTurnitin" class="fw-bold"></span></div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="checkCamKet" required>
                    <label class="form-check-label text-dark small" for="checkCamKet">
                        Nhóm cam kết toàn bộ báo cáo, mã nguồn và kết quả kiểm tra đạo văn là trung thực, tuân thủ nghiêm ngặt quy chế đào tạo và liêm chính học thuật.
                    </label>
                </div>
            </div>
            <div class="modal-footer border-top py-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Kiểm tra lại</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="btnThucHienNop" disabled onclick="submitDossierForm()">
                    <i class="fa-solid fa-paper-plane me-2"></i>Đồng ý nộp
                </button>
            </div>
        </div>
    <!-- MODAL XEM BÁO CÁO TRỰC TIẾP TRÊN MÀN HÌNH (KHÔNG CHUYỂN TAB) -->
<div class="modal fade" id="modalPdfPreview" tabindex="-1" aria-hidden="true" style="backdrop-filter: blur(4px);">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 95vw; height: 92vh;">
        <div class="modal-content h-100 shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-dark text-white py-2.5 px-4">
                <div class="d-flex align-items-center gap-2 text-truncate">
                    <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
                    <h6 class="modal-title fw-bold mb-0 text-truncate" id="pdfPreviewTitle">Xem Báo Cáo Khóa Luận</h6>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="btnDownloadPdfModal" download class="btn btn-sm btn-outline-light rounded-pill px-3">
                        <i class="fa-solid fa-download me-1"></i> Tải về máy
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 bg-secondary bg-opacity-10" style="height: calc(100% - 58px);">
                <iframe id="pdfPreviewIframe" src="" width="100%" height="100%" style="border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openPdfModal(url, title = 'Xem Báo Cáo Khóa Luận') {
        const titleEl = document.getElementById('pdfPreviewTitle');
        const iframeEl = document.getElementById('pdfPreviewIframe');
        const dlBtn = document.getElementById('btnDownloadPdfModal');
        
        if (titleEl) titleEl.textContent = title;
        if (iframeEl) iframeEl.src = url + '#toolbar=1';
        if (dlBtn) dlBtn.href = url;

        const modalEl = document.getElementById('modalPdfPreview');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    function togglePdfInlineViewer() {
        const container = document.getElementById('pdfInlineContainer');
        const textSpan = document.getElementById('togglePdfText');
        if (!container) return;
        if (container.style.display === 'none') {
            container.style.display = 'block';
            if (textSpan) textSpan.textContent = 'Thu gọn';
        } else {
            container.style.display = 'none';
            if (textSpan) textSpan.textContent = 'Mở rộng';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('modalPdfPreview');
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function () {
                const iframeEl = document.getElementById('pdfPreviewIframe');
                if (iframeEl) iframeEl.src = '';
            });
        }
    });
    function toggleDeploySection() {
        const isCo = document.getElementById('deploy_co').checked;
        const sectionCo = document.getElementById('sectionDeployCo');
        const sectionKhong = document.getElementById('sectionDeployKhong');
        const inputLinkDeploy = document.getElementById('inputLinkDeploy');
        const inputLyDoKhongDeploy = document.getElementById('inputLyDoKhongDeploy');

        if (isCo) {
            sectionCo.style.display = 'flex';
            sectionKhong.style.display = 'none';
            inputLinkDeploy.setAttribute('required', 'required');
            inputLyDoKhongDeploy.removeAttribute('required');
        } else {
            sectionCo.style.display = 'none';
            sectionKhong.style.display = 'flex';
            inputLinkDeploy.removeAttribute('required');
            inputLyDoKhongDeploy.setAttribute('required', 'required');
        }
    }

    let linkIndex = {{ isset($existingLinks) ? count($existingLinks) : 1 }};
    function addLienKetRow() {
        const container = document.getElementById('lienKetContainer');
        const div = document.createElement('div');
        div.className = 'card p-3 border bg-white rounded-3 lien-ket-row shadow-xs';
        div.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-md-2">
                    <select name="LienKetKhac[${linkIndex}][loai]" class="form-select form-select-sm fw-semibold" onchange="toggleLienKetType(this)">
                        <option value="link">🔗 Link URL</option>
                        <option value="file">📁 Tệp File</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="LienKetKhac[${linkIndex}][tieu_de]" class="form-control form-control-sm" placeholder="Tiêu đề (VD: Slide, User Manual...)">
                </div>
                <div class="col-md-4">
                    <div class="input-url-box">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-link"></i></span>
                            <input type="url" name="LienKetKhac[${linkIndex}][url]" class="form-control form-control-sm" placeholder="https://...">
                        </div>
                    </div>
                    <div class="input-file-box d-none">
                        <input type="file" name="LienKetKhac[${linkIndex}][file]" class="form-control form-control-sm" accept=".ppt,.pptx,.pdf,.doc,.docx,.zip,.rar,.mp4,.png,.jpg,.jpeg">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="text" name="LienKetKhac[${linkIndex}][mo_ta]" class="form-control form-control-sm" placeholder="Mô tả ngắn">
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle" onclick="removeLienKetRow(this)" title="Xóa mục này">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(div);
        linkIndex++;
    }

    function toggleLienKetType(select) {
        const row = select.closest('.lien-ket-row');
        if (!row) return;
        const urlBox = row.querySelector('.input-url-box');
        const fileBox = row.querySelector('.input-file-box');
        if (select.value === 'file') {
            urlBox.classList.add('d-none');
            fileBox.classList.remove('d-none');
        } else {
            fileBox.classList.add('d-none');
            urlBox.classList.remove('d-none');
        }
    }

    function removeLienKetRow(btn) {
        const row = btn.closest('.lien-ket-row');
        if (row) {
            row.remove();
        }
    }

    function openConfirmModal() {
        const form = document.getElementById('dossierForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const sourceCode = document.getElementById('inputLinkSourceCode').value;
        const isCo = document.getElementById('deploy_co').checked;
        const deployVal = isCo 
            ? (document.getElementById('inputLinkDeploy').value || 'Chưa nhập') 
            : ('Không deploy: ' + (document.getElementById('inputLyDoKhongDeploy').value || 'Không có'));
        const turnitin = document.getElementById('inputTyLeTurnitin').value;

        document.getElementById('confirmSourceCode').textContent = sourceCode;
        document.getElementById('confirmDeploy').textContent = deployVal;
        
        const turnitinEl = document.getElementById('confirmTurnitin');
        turnitinEl.textContent = turnitin + '%';
        if (parseFloat(turnitin) <= 20) {
            turnitinEl.className = 'fw-bold text-success';
        } else {
            turnitinEl.className = 'fw-bold text-danger';
        }

        const camKetCheck = document.getElementById('checkCamKet');
        const submitBtn = document.getElementById('btnThucHienNop');
        camKetCheck.checked = false;
        submitBtn.disabled = true;

        camKetCheck.onchange = function() {
            submitBtn.disabled = !this.checked;
        };

        const modal = new bootstrap.Modal(document.getElementById('modalConfirmSubmit'));
        modal.show();
    }

    function submitDossierForm() {
        const form = document.getElementById('dossierForm');
        form.submit();
    }
</script>
@endpush

@endsection
