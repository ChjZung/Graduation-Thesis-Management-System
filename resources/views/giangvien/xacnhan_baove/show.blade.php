@extends('layouts.giangvien')

@section('page_title', 'Kiểm Tra Hồ Sơ Bảo Vệ Khóa Luận')

@section('content')
<div class="container-fluid px-0">
    <!-- Top Bar Navigation -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('giangvien.xacnhan_baove.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
            <div>
                <h4 class="fw-bold mb-0" style="color: var(--huit-blue-dark);">
                    Kiểm Tra Hồ Sơ Bảo Vệ Khóa Luận
                </h4>
                <div class="text-muted small">
                    Nhóm: <strong>{{ $hoSo->nhom->TenNhom ?? 'N/A' }}</strong> &bull; Đề tài: <strong>{{ $hoSo->deTai->TenDeTai ?? 'N/A' }}</strong>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @php
                $statusMap = match($hoSo->TrangThai) {
                    'Đã phê duyệt', 'Đủ điều kiện bảo vệ' => ['badge' => 'bg-success text-white', 'icon' => 'fa-circle-check', 'text' => 'Đã Phê Duyệt – Đủ Điều Kiện'],
                    'Yêu cầu chỉnh sửa' => ['badge' => 'bg-warning text-dark', 'icon' => 'fa-triangle-exclamation', 'text' => 'Yêu Cầu Chỉnh Sửa'],
                    'Không duyệt', 'Không đủ điều kiện' => ['badge' => 'bg-danger text-white', 'icon' => 'fa-circle-xmark', 'text' => 'Không Đồng Ý Cho Bảo Vệ'],
                    'Đã nộp lại - Chờ kiểm tra' => ['badge' => 'bg-info text-dark', 'icon' => 'fa-rotate', 'text' => 'Đã Nộp Lại – Chờ Kiểm Tra'],
                    'Đang được kiểm tra' => ['badge' => 'bg-info text-white', 'icon' => 'fa-magnifying-glass', 'text' => 'Đang Kiểm Tra'],
                    default => ['badge' => 'bg-warning text-dark', 'icon' => 'fa-clock', 'text' => 'Chờ GVHD Kiểm Tra'],
                };
            @endphp
            <span class="badge {{ $statusMap['badge'] }} px-3 py-2 rounded-pill fs-6 fw-semibold">
                <i class="fa-solid {{ $statusMap['icon'] }} me-1"></i> {{ $statusMap['text'] }}
            </span>
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fs-6 font-monospace">
                Phiên bản v{{ number_format($hoSo->PhienBanHienTai ?? 1, 1, '.', '') }}
            </span>
        </div>
    </div>

    <!-- Thông Tin Tổng Quan Đề Tài & Nhóm Sinh Viên -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-body p-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 small">
                            {{ $hoSo->nhom->hocPhan->TenHocPhan ?? 'Khóa luận tốt nghiệp' }}
                        </span>
                        <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">
                            Học kỳ {{ $hoSo->nhom->hocKy->TenHocKy ?? $hoSo->nhom->hocKy->HocKySo ?? 'Hiện tại' }}
                        </span>
                        <span class="badge bg-light text-secondary border rounded-pill px-2 py-1 small font-monospace">
                            Mã đề tài: {{ $hoSo->MaDeTai }}
                        </span>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">
                        {{ $hoSo->deTai->TenDeTai }}
                    </h5>
                    <div class="d-flex flex-wrap gap-3 text-secondary small">
                        <div>
                            <i class="fa-solid fa-users text-primary me-1"></i>
                            Nhóm: <strong>{{ $hoSo->nhom->TenNhom }}</strong>
                        </div>
                        <div>
                            <i class="fa-regular fa-calendar-days text-success me-1"></i>
                            Nộp gần nhất: <strong>{{ $hoSo->NgayNopGanNhat ? \Carbon\Carbon::parse($hoSo->NgayNopGanNhat)->format('d/m/Y H:i') : ($hoSo->NgayNop ? \Carbon\Carbon::parse($hoSo->NgayNop)->format('d/m/Y') : 'N/A') }}</strong>
                        </div>
                        <div>
                            <i class="fa-solid fa-shield-halved text-info me-1"></i>
                            Mã hồ sơ: <strong class="font-monospace">{{ $hoSo->MaHoSo }}</strong>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 border-top border-lg-top-0 pt-3 pt-lg-0 text-lg-end">
                    <div class="small text-muted mb-1">Thành viên nhóm thực hiện:</div>
                    <div class="d-flex flex-column align-items-lg-end gap-1">
                        @foreach($hoSo->nhom->thanhViens as $tv)
                            <div class="small text-dark">
                                <i class="fa-solid fa-user-graduate text-secondary me-1"></i>
                                <strong>{{ $tv->sinhVien->HoTen ?? 'Sinh viên' }}</strong>
                                <span class="text-muted font-monospace">({{ $tv->MaSV }})</span>
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

    <!-- Alert Thông Báo Yêu Cầu Sửa / Từ Chối Nếu Đang Có -->
    @if($hoSo->TrangThai === 'Yêu cầu chỉnh sửa')
        <div class="alert alert-warning border-warning shadow-sm mb-4" style="border-radius: 12px;">
            <div class="d-flex align-items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation fs-4 mt-1"></i>
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                        <strong class="text-dark">Hồ sơ đang ở trạng thái Yêu cầu sinh viên chỉnh sửa / bổ sung</strong>
                        @if($hoSo->HanNopLai)
                            <span class="badge bg-danger text-white rounded-pill px-3 py-1">
                                Hạn hoàn thành: {{ \Carbon\Carbon::parse($hoSo->HanNopLai)->format('d/m/Y H:i') }}
                            </span>
                        @endif
                    </div>
                    <div class="text-dark small mb-2">{!! nl2br(e($hoSo->NhanXetGVHD)) !!}</div>
                    @if(!empty($hoSo->ThanhPhanCanSua) && is_array($hoSo->ThanhPhanCanSua))
                        @php
                            $labels = [
                                'bao_cao'       => 'Báo cáo toàn văn (PDF)',
                                'source_code'   => 'Mã nguồn dự án (Git)',
                                'link_deploy'   => 'Đường dẫn triển khai (Deploy)',
                                'turnitin'      => 'Minh chứng Turnitin',
                                'tai_lieu_khac' => 'Tài liệu bổ sung',
                            ];
                        @endphp
                        <div class="d-flex flex-wrap gap-1">
                            <span class="small text-secondary me-1">Mục yêu cầu sửa:</span>
                            @foreach($hoSo->ThanhPhanCanSua as $tp)
                                <span class="badge bg-white text-danger border border-danger-subtle rounded-pill px-2 py-1 small">
                                    {{ $labels[$tp] ?? $tp }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- CỘT CHÍNH (TRÁI): NỘI DUNG 4 THÀNH PHẦN HỒ SƠ -->
        <div class="col-lg-8">
            <!-- 1. BÁO CÁO TOÀN VĂN PDF -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger text-white rounded-circle px-2">1</span>
                        <h6 class="fw-bold text-dark mb-0">Báo Cáo Khóa Luận Toàn Văn (PDF)</h6>
                    </div>
                    @if($hoSo->ToanVanKhoaLuan)
                        <div class="d-flex gap-2">
                            <a href="{{ asset('storage/' . $hoSo->ToanVanKhoaLuan) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Xem Toàn Màn Hình
                            </a>
                            <a href="{{ asset('storage/' . $hoSo->ToanVanKhoaLuan) }}" download class="btn btn-sm btn-danger rounded-pill px-3">
                                <i class="fa-solid fa-download me-1"></i> Tải Về PDF
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-body p-4">
                    @if($hoSo->ToanVanKhoaLuan)
                        @php
                            $latestPb = $hoSo->phienBans->first();
                        @endphp
                        <div class="p-3 bg-light rounded-3 border mb-3 small d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <i class="fa-solid fa-file-pdf text-danger me-2 fs-5"></i>
                                <strong>{{ $latestPb?->TenFileBaoCao ?: 'Báo cáo khóa luận toàn văn.pdf' }}</strong>
                                @if($latestPb?->DungLuongBaoCao)
                                    <span class="text-muted ms-2">({{ number_format($latestPb->DungLuongBaoCao / (1024 * 1024), 2) }} MB)</span>
                                @endif
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                Đã nộp thành công
                            </span>
                        </div>

                        <!-- Embedded PDF Viewer -->
                        <div class="border rounded-3 overflow-hidden bg-light" style="height: 520px;">
                            <iframe src="{{ asset('storage/' . $hoSo->ToanVanKhoaLuan) }}#toolbar=1" width="100%" height="100%" style="border: none;">
                                <div class="p-4 text-center">
                                    <p class="text-muted">Trình duyệt không hỗ trợ xem trước tệp PDF trực tiếp.</p>
                                    <a href="{{ asset('storage/' . $hoSo->ToanVanKhoaLuan) }}" target="_blank" class="btn btn-primary rounded-pill">
                                        Mở xem trong tab mới
                                    </a>
                                </div>
                            </iframe>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fa-solid fa-file-circle-xmark fa-2x text-danger mb-2"></i>
                            <div class="fw-bold">Chưa có tệp Báo cáo khóa luận toàn văn</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 2. MÃ NGUỒN DỰ ÁN (GIT REPOSITORY) -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-dark text-white rounded-circle px-2">2</span>
                        <h6 class="fw-bold text-dark mb-0">Mã Nguồn Dự Án (GitHub / GitLab / Bitbucket)</h6>
                    </div>
                    @if($hoSo->LinkSourceCode)
                        <a href="{{ $hoSo->LinkSourceCode }}" target="_blank" class="btn btn-sm btn-dark rounded-pill px-3">
                            <i class="fa-brands fa-github me-1"></i> Truy Cập Repository
                        </a>
                    @endif
                </div>
                <div class="card-body p-4">
                    @if($hoSo->LinkSourceCode)
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                <div class="text-muted small fw-semibold text-uppercase">Đường dẫn repository:</div>
                                <span class="badge bg-dark text-white rounded-pill px-3 py-1 font-monospace">Git Repo</span>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-code-branch"></i></span>
                                <input type="text" class="form-control bg-white font-monospace text-primary fw-semibold" value="{{ $hoSo->LinkSourceCode }}" readonly>
                                <a href="{{ $hoSo->LinkSourceCode }}" target="_blank" class="btn btn-outline-dark">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                            </div>
                        </div>

                        @if($hoSo->GhiChuSourceCode)
                            <div class="p-3 bg-white rounded-3 border">
                                <div class="fw-bold text-dark small mb-1">
                                    <i class="fa-solid fa-circle-info text-primary me-1"></i>Hướng dẫn cấu trúc thư mục &amp; tài khoản kiểm thử:
                                </div>
                                <div class="text-secondary small" style="line-height: 1.6;">
                                    {!! nl2br(e($hoSo->GhiChuSourceCode)) !!}
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fa-brands fa-github fa-2x text-secondary mb-2"></i>
                            <div class="fw-bold">Nhóm chưa cung cấp liên kết mã nguồn dự án</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. TRIỂN KHAI SẢN PHẨM TRỰC TUYẾN (DEPLOY) -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-info text-white rounded-circle px-2">3</span>
                        <h6 class="fw-bold text-dark mb-0">Triển Khai Trực Tuyến &amp; Trải Nghiệm Sản Phẩm (Deploy)</h6>
                    </div>
                    @if($hoSo->LinkDeploy)
                        <a href="{{ $hoSo->LinkDeploy }}" target="_blank" class="btn btn-sm btn-info text-white rounded-pill px-3">
                            <i class="fa-solid fa-globe me-1"></i> Mở Ứng Dụng Trực Tuyến
                        </a>
                    @endif
                </div>
                <div class="card-body p-4">
                    @if($hoSo->LinkDeploy)
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                <div class="text-muted small fw-semibold text-uppercase">Đường dẫn sản phẩm chạy thực tế:</div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                    <i class="fa-solid fa-circle-check me-1"></i> Live Production
                                </span>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-globe text-info"></i></span>
                                <input type="text" class="form-control bg-white font-monospace text-primary fw-semibold" value="{{ $hoSo->LinkDeploy }}" readonly>
                                <a href="{{ $hoSo->LinkDeploy }}" target="_blank" class="btn btn-outline-info">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                            </div>
                        </div>

                        @if($hoSo->GhiChuDeploy)
                            <div class="p-3 bg-white rounded-3 border">
                                <div class="fw-bold text-dark small mb-1">
                                    <i class="fa-solid fa-key text-warning me-1"></i>Tài khoản thử nghiệm &amp; Hướng dẫn đăng nhập:
                                </div>
                                <div class="text-secondary small" style="line-height: 1.6;">
                                    {!! nl2br(e($hoSo->GhiChuDeploy)) !!}
                                </div>
                            </div>
                        @endif
                    @elseif($hoSo->LyDoKhongDeploy)
                        <div class="alert alert-secondary bg-light border mb-0">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa-solid fa-circle-info text-secondary fs-5 mt-1"></i>
                                <div>
                                    <strong class="text-dark">Nhóm không triển khai sản phẩm trực tuyến</strong>
                                    <div class="text-muted small mt-1">Lý do giải trình từ nhóm sinh viên:</div>
                                    <div class="p-2 bg-white rounded border mt-2 small text-dark">
                                        {!! nl2br(e($hoSo->LyDoKhongDeploy)) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fa-solid fa-globe fa-2x text-secondary mb-2"></i>
                            <div class="fw-bold">Chưa có thông tin triển khai trực tuyến</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 4. TURNITIN & LIÊM CHÍNH HỌC THUẬT -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning text-dark rounded-circle px-2">4</span>
                        <h6 class="fw-bold text-dark mb-0">Kết Quả Kiểm Tra Đạo Văn (Turnitin)</h6>
                    </div>
                    @if($hoSo->MinhChungDaoVan)
                        <a href="{{ asset('storage/' . $hoSo->MinhChungDaoVan) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="fa-solid fa-file-shield me-1"></i> Xem Báo Cáo Turnitin (PDF)
                        </a>
                    @endif
                </div>
                <div class="card-body p-4">
                    @php
                        $turnitinScore = $hoSo->phienBans->first()?->TyLeTurnitin ?? $hoSo->TyLeTrungLap ?? 0;
                        $isPass = ($turnitinScore <= 20);
                    @endphp
                    <div class="row g-3 align-items-center">
                        <div class="col-md-5">
                            <div class="p-3 bg-light rounded-3 border text-center">
                                <div class="text-muted small mb-1">Tỷ lệ trùng lặp ghi nhận:</div>
                                <div class="display-5 fw-bold {{ $isPass ? 'text-primary' : 'text-danger' }}">
                                    {{ number_format($turnitinScore, 1) }}%
                                </div>
                                <div class="mt-2">
                                    @if($isPass)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-semibold small">
                                            <i class="fa-solid fa-circle-check me-1"></i> Hợp Lệ (&le; 20%)
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-semibold small">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Vượt Ngưỡng Cho Phép (&gt; 20%)
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="small text-secondary mb-2">
                                <strong>Quy định Khoa:</strong> Báo cáo khóa luận toàn văn phải có tỷ lệ tương đồng Turnitin không vượt quá <strong>20%</strong> mới đủ điều kiện bảo vệ chính thức.
                            </div>
                            @if($hoSo->MinhChungDaoVan)
                                <div class="p-2 bg-white rounded border small d-flex align-items-center justify-content-between">
                                    <div class="text-truncate">
                                        <i class="fa-solid fa-file-pdf text-danger me-2"></i>
                                        {{ $hoSo->phienBans->first()?->TenFileTurnitin ?: 'Minh chứng Turnitin.pdf' }}
                                    </div>
                                    <a href="{{ asset('storage/' . $hoSo->MinhChungDaoVan) }}" target="_blank" class="btn btn-sm btn-link text-primary p-0 ms-2">
                                        Xem file
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. TÀI LIỆU BỔ SUNG & DEMO -->
            @if(!empty($hoSo->LienKetKhac) && is_array($hoSo->LienKetKhac) && count($hoSo->LienKetKhac) > 0)
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary text-white rounded-circle px-2">5</span>
                            <h6 class="fw-bold text-dark mb-0">Tài Liệu Bổ Sung &amp; Liên Kết Khác</h6>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            @foreach($hoSo->LienKetKhac as $lk)
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between gap-2">
                                        <div class="text-truncate">
                                            <div class="fw-bold text-dark small text-truncate">
                                                <i class="fa-solid fa-link text-primary me-2"></i>{{ $lk['tieu_de'] ?? 'Tài liệu' }}
                                            </div>
                                            @if(!empty($lk['mo_ta']))
                                                <div class="text-muted small text-truncate">{{ $lk['mo_ta'] }}</div>
                                            @endif
                                        </div>
                                        @if(!empty($lk['url']))
                                            <a href="{{ $lk['url'] }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 flex-shrink-0">
                                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- LỊCH SỬ CÁC PHIÊN BẢN HỒ SƠ -->
            @if($hoSo->phienBans && $hoSo->phienBans->count() > 0)
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Lịch Sử Các Phiên Bản Hồ Sơ
                        </h6>
                        <span class="badge bg-light text-secondary border px-3 py-1">{{ $hoSo->phienBans->count() }} phiên bản</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 100px;">Phiên bản</th>
                                        <th style="min-width: 150px;">Thời gian nộp</th>
                                        <th style="min-width: 140px;">Trạng thái</th>
                                        <th style="min-width: 250px;">Đánh giá của GVHD</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hoSo->phienBans as $pb)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary px-3 py-1 rounded-pill font-monospace">
                                                    v{{ $pb->PhienBan }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $pb->NgayNop ? \Carbon\Carbon::parse($pb->NgayNop)->format('d/m/Y H:i') : '' }}</div>
                                                <div class="text-muted small">Người nộp: {{ $pb->NguoiNop }}</div>
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
                                            </td>
                                            <td>
                                                @if($pb->NhanXetGVHD)
                                                    <div class="text-dark small">{!! nl2br(e($pb->NhanXetGVHD)) !!}</div>
                                                @else
                                                    <span class="text-muted small">Chưa có nhận xét</span>
                                                @endif
                                                @if($pb->NgayDanhGia)
                                                    <div class="text-muted small mt-1">
                                                        <i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($pb->NgayDanhGia)->format('d/m/Y H:i') }}
                                                    </div>
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
        </div>

        <!-- CỘT BÊN PHẢI: BẢNG HÀNH ĐỘNG ĐÁNH GIÁ (STICKY ACTION PANEL) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="border-radius: 14px; top: 85px; z-index: 10;">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-gavel text-primary"></i>
                        Đánh Giá &amp; Xác Nhận Bảo Vệ
                    </h6>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-3">
                        Sau khi kiểm tra kỹ lưỡng 4 thành phần hồ sơ, Thầy/Cô vui lòng đưa ra kết luận đánh giá chính thức cho nhóm sinh viên:
                    </p>

                    <!-- NÚT 1: PHÊ DUYỆT ĐỦ ĐIỀU KIỆN BẢO VỆ -->
                    <button type="button" class="btn btn-success w-100 py-3 rounded-pill mb-3 d-flex align-items-center justify-content-center shadow-sm" data-bs-toggle="modal" data-bs-target="#modalPheDuyet">
                        <i class="fa-solid fa-circle-check fs-5 me-2"></i>
                        <div class="text-start">
                            <div class="fw-bold" style="font-size: 14.5px;">Phê Duyệt Đủ Điều Kiện</div>
                            <div style="font-size: 11.5px; opacity: 0.9;">Xác nhận cho nhóm ra bảo vệ trước Hội đồng</div>
                        </div>
                    </button>

                    <!-- NÚT 2: YÊU CẦU CHỈNH SỬA / BỔ SUNG -->
                    <button type="button" class="btn btn-warning w-100 py-3 rounded-pill mb-3 d-flex align-items-center justify-content-center text-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#modalYeuCauSua">
                        <i class="fa-solid fa-pen-to-square fs-5 me-2"></i>
                        <div class="text-start">
                            <div class="fw-bold" style="font-size: 14.5px;">Yêu Cầu Chỉnh Sửa / Bổ Sung</div>
                            <div style="font-size: 11.5px; opacity: 0.9;">Chỉ rõ thành phần cần sửa và hạn nộp lại</div>
                        </div>
                    </button>

                    <!-- NÚT 3: TỪ CHỐI CHO BẢO VỆ -->
                    <button type="button" class="btn btn-outline-danger w-100 py-2 rounded-pill d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTuChoi">
                        <i class="fa-solid fa-circle-xmark me-2"></i>
                        <span>Không Đồng Ý Cho Bảo Vệ</span>
                    </button>

                    <hr class="my-4">

                    <!-- Thông Tin Trạng Thái Hiện Tại -->
                    <div class="small">
                        <div class="text-muted fw-bold mb-2 text-uppercase" style="font-size: 11px;">Trạng thái ghi nhận:</div>
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="mb-2">
                                <span class="text-muted">Xác nhận GVHD:</span>
                                <strong>{{ $hoSo->XacNhanGVHD ?: 'Chưa xác nhận' }}</strong>
                            </div>
                            @if($hoSo->NgayXacNhan)
                                <div class="mb-2">
                                    <span class="text-muted">Ngày xác nhận:</span>
                                    <span>{{ \Carbon\Carbon::parse($hoSo->NgayXacNhan)->format('d/m/Y') }}</span>
                                </div>
                            @endif
                            @if($hoSo->NhanXetGVHD)
                                <div>
                                    <span class="text-muted">Nhận xét gần nhất:</span>
                                    <div class="mt-1 text-dark small">{!! nl2br(e($hoSo->NhanXetGVHD)) !!}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 1: PHÊ DUYỆT ĐỦ ĐIỀU KIỆN BẢO VỆ -->
<div class="modal fade" id="modalPheDuyet" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <form action="{{ route('giangvien.xacnhan_baove.xacNhan', $hoSo->MaHoSo) }}" method="POST">
                @csrf
                <input type="hidden" name="HanhDong" value="dong_y">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-success d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-check fs-5"></i>
                        Xác Nhận Phê Duyệt Đủ Điều Kiện Bảo Vệ
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-success bg-success-subtle border-success-subtle mb-3 small">
                        <strong>Lưu ý:</strong> Khi Thầy/Cô phê duyệt, hệ thống sẽ xác nhận nhóm sinh viên đạt yêu cầu chuyên môn, hoàn thành báo cáo và sản phẩm, chính thức đủ điều kiện để Giáo vụ Khoa bố trí Hội đồng và xếp lịch bảo vệ.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Nhận xét &amp; Lời dặn dò của GVHD (Nếu có):</label>
                        <textarea name="NhanXet" class="form-control" rows="3" placeholder="Ví dụ: Báo cáo hoàn thiện tốt, mã nguồn rõ ràng, sản phẩm hoạt động ổn định. Đồng ý cho nhóm tham gia bảo vệ trước Hội đồng.">{{ old('NhanXet', 'Báo cáo và sản phẩm đạt yêu cầu. Đồng ý cho nhóm tham gia bảo vệ khóa luận trước Hội đồng.') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="fa-solid fa-check me-2"></i>Đồng Ý Phê Duyệt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: YÊU CẦU CHỈNH SỬA / BỔ SUNG HỒ SƠ -->
<div class="modal fade" id="modalYeuCauSua" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <form action="{{ route('giangvien.xacnhan_baove.yeuCauChinhSua', $hoSo->MaHoSo) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-warning d-flex align-items-center gap-2">
                        <i class="fa-solid fa-pen-to-square fs-5"></i>
                        Yêu Cầu Chỉnh Sửa / Bổ Sung Hồ Sơ Bảo Vệ
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Checkbox các thành phần cần sửa -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark small d-block">
                            1. Chọn các thành phần hồ sơ nhóm cần chỉnh sửa hoặc bổ sung: <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-check p-2 bg-light rounded border ps-4">
                                    <input class="form-check-input" type="checkbox" name="ThanhPhanCanSua[]" value="bao_cao" id="chk_bao_cao">
                                    <label class="form-check-label text-dark small fw-semibold" for="chk_bao_cao">
                                        <i class="fa-solid fa-file-pdf text-danger me-1"></i> Báo cáo khóa luận toàn văn (PDF)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check p-2 bg-light rounded border ps-4">
                                    <input class="form-check-input" type="checkbox" name="ThanhPhanCanSua[]" value="source_code" id="chk_source_code">
                                    <label class="form-check-label text-dark small fw-semibold" for="chk_source_code">
                                        <i class="fa-brands fa-github text-dark me-1"></i> Mã nguồn dự án (GitHub/GitLab)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check p-2 bg-light rounded border ps-4">
                                    <input class="form-check-input" type="checkbox" name="ThanhPhanCanSua[]" value="link_deploy" id="chk_link_deploy">
                                    <label class="form-check-label text-dark small fw-semibold" for="chk_link_deploy">
                                        <i class="fa-solid fa-globe text-info me-1"></i> Đường dẫn triển khai (Deploy) / Tài khoản test
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check p-2 bg-light rounded border ps-4">
                                    <input class="form-check-input" type="checkbox" name="ThanhPhanCanSua[]" value="turnitin" id="chk_turnitin">
                                    <label class="form-check-label text-dark small fw-semibold" for="chk_turnitin">
                                        <i class="fa-solid fa-shield-halved text-warning me-1"></i> Minh chứng Turnitin / Tỷ lệ trùng lặp
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check p-2 bg-light rounded border ps-4">
                                    <input class="form-check-input" type="checkbox" name="ThanhPhanCanSua[]" value="tai_lieu_khac" id="chk_tai_lieu_khac">
                                    <label class="form-check-label text-dark small fw-semibold" for="chk_tai_lieu_khac">
                                        <i class="fa-solid fa-paperclip text-secondary me-1"></i> Tài liệu hướng dẫn sử dụng / Video Demo sản phẩm
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ý kiến nhận xét chi tiết -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">
                            2. Nội dung nhận xét chi tiết &amp; Hướng dẫn chỉnh sửa: <span class="text-danger">*</span>
                        </label>
                        <textarea name="NhanXet" class="form-control" rows="4" required 
                            placeholder="Ghi rõ các nội dung chưa đạt trong báo cáo, lỗi chức năng mã nguồn hoặc yêu cầu bổ sung tài liệu để sinh viên khắc phục..."></textarea>
                    </div>

                    <!-- Thời hạn nộp lại -->
                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark small">
                            3. Thời hạn nộp lại hồ sơ (Deadline):
                        </label>
                        <input type="datetime-local" name="HanNopLai" class="form-control" 
                            min="{{ now()->format('Y-m-d\TH:i') }}" 
                            value="{{ now()->addDays(3)->format('Y-m-d\TH:i') }}">
                        <div class="form-text text-muted small">Sinh viên sẽ nhận được thông báo kèm thời hạn này để hoàn thiện hồ sơ.</div>
                    </div>
                </div>
                <div class="modal-footer border-top py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 text-dark">
                        <i class="fa-solid fa-paper-plane me-2"></i>Gửi Yêu Cầu Chỉnh Sửa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: TỪ CHỐI CHO RA BẢO VỆ -->
<div class="modal fade" id="modalTuChoi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <form action="{{ route('giangvien.xacnhan_baove.xacNhan', $hoSo->MaHoSo) }}" method="POST">
                @csrf
                <input type="hidden" name="HanhDong" value="khong_dong_y">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-danger d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-xmark fs-5"></i>
                        Không Đồng Ý Cho Ra Bảo Vệ
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">
                        Hành động này xác nhận nhóm khóa luận <strong>không đủ điều kiện</strong> tham gia Hội đồng bảo vệ khóa luận tốt nghiệp đợt này.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Lý do không đồng ý: <span class="text-danger">*</span></label>
                        <textarea name="NhanXet" class="form-control" rows="4" required 
                            placeholder="Ghi rõ lý do sản phẩm chưa hoàn thiện, tỷ lệ đạo văn vi phạm hoặc không đáp ứng chuẩn đầu ra khóa luận..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                        <i class="fa-solid fa-xmark me-2"></i>Xác Nhận Từ Chối
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
