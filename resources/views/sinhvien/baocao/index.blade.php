@extends('layouts.sinhvien')

@section('page_title', 'Lộ Trình & Tiến Độ Khóa Luận Tốt Nghiệp')

@section('content')
@if(isset($error) && $error)
<div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $error }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Page Header (Theo chuẩn Mockup Figma) -->
<div class="mb-4">
    <h3 class="fw-bold mb-1 text-primary-custom">
        <i class="fa-solid fa-timeline me-2 text-primary"></i>Lộ Trình & Tiến Độ Khóa Luận Tốt Nghiệp
    </h3>
    <div class="text-muted small">
        Theo dõi lộ trình 5 mốc thực hiện và nộp báo cáo định kỳ cho Giảng viên hướng dẫn
    </div>
</div>

@if(isset($nhom) && $nhom && $nhom->deTai)
<!-- Thông tin Đề tài & GVHD Tóm tắt -->
<div class="card card-premium shadow-sm border-0 mb-4">
    <div class="card-body p-3 bg-light rounded-3 border">
        <div class="row g-2 align-items-center">
            <div class="col-md-8">
                <div class="fw-bold text-dark fs-6 mb-1">
                    <i class="fa-solid fa-folder-closed text-primary me-2"></i>{{ $nhom->TenNhom }} — {{ $nhom->deTai->TenDeTai }}
                </div>
                <div class="small text-muted d-flex flex-wrap gap-3">
                    <span><i class="fa-solid fa-chalkboard-user me-1 text-secondary"></i>GVHD: <strong>{{ $nhom->deTai->giangVien->HoTen ?? 'Chưa phân công' }}</strong></span>
                    @if($nhom->deTai->giangVien?->Email)
                        <span><i class="fa-regular fa-envelope me-1 text-secondary"></i>{{ $nhom->deTai->giangVien->Email }}</span>
                    @endif
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <span class="badge bg-success rounded-pill px-3 py-2 shadow-xs">
                    <i class="fa-solid fa-circle-check me-1"></i>Đề tài đã được duyệt
                </span>
            </div>
        </div>
    </div>
</div>

@php
    $allCompleted = ($baoCaos->count() >= 6 && isset($baoCaos[6]) && $baoCaos[6]->TrangThai === 'Đạt');
    $bcHienTai = $baoCaos[$mocHienTai] ?? null;
@endphp

<!-- Milestone Stepper Ngang 6 Giai Đoạn Liên Hoàn -->
<div class="card card-premium shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <div class="stepper-horizontal-container">
            <div class="row g-2 text-center position-relative">
                <!-- Đường nối ngang giữa các giai đoạn -->
                <div class="position-absolute top-50 start-0 translate-middle-y w-100 d-none d-md-block" style="height: 3px; background: #e2e8f0; z-index: 1; margin-top: -15px;">
                    @php
                        $completedCount = 0;
                        for ($i = 1; $i <= 6; $i++) {
                            if (isset($baoCaos[$i]) && $baoCaos[$i]->TrangThai === 'Đạt') {
                                $completedCount++;
                            }
                        }
                        $progressPercent = ($completedCount / 6) * 100;
                    @endphp
                    <div style="height: 100%; width: {{ $progressPercent }}%; background: linear-gradient(90deg, #10b981 0%, #2563eb 100%); transition: width 0.4s ease;"></div>
                </div>

                @foreach($mocs as $soMoc => $moc)
                @php
                    $bc = $baoCaos[$soMoc] ?? null;
                    $isDone = ($bc && $bc->TrangThai === 'Đạt');
                    $isCurrent = ($soMoc === $mocHienTai && !$allCompleted);
                    $isPending = ($bc && $bc->TrangThai === 'Chờ duyệt');
                    $isResubmit = ($bc && $bc->TrangThai === 'Yêu cầu nộp lại');
                @endphp
                <div class="col position-relative" style="z-index: 2;">
                    @if($isDone)
                        <!-- Đã đạt -->
                        <div class="mx-auto rounded-circle text-white shadow-sm mb-2"
                             style="width: 44px; height: 44px; background-color: #10b981; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-check fw-bold fs-5"></i>
                        </div>
                        <div class="fw-bold small text-dark" style="font-size: 0.78rem;">{{ $moc['ten'] }}</div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small" style="font-size: 0.68rem;">
                            Đã Đạt
                        </span>
                    @elseif($isCurrent)
                        <!-- Giai đoạn hiện tại đang active -->
                        <div class="mx-auto rounded-circle text-white shadow-lg mb-2"
                             style="width: 48px; height: 48px; background-color: {{ $isPending ? '#f59e0b' : ($isResubmit ? '#0284c7' : '#2563eb') }}; box-shadow: 0 0 15px rgba(37, 99, 235, 0.4) !important; margin-top: -2px; display: flex; align-items: center; justify-content: center;">
                            @if($isPending)
                                <i class="fa-solid fa-hourglass-half fs-5"></i>
                            @elseif($isResubmit)
                                <i class="fa-solid fa-rotate-left fs-5"></i>
                            @else
                                <span class="fw-bold fs-5">{{ $soMoc }}</span>
                            @endif
                        </div>
                        <div class="fw-bold small text-primary" style="font-size: 0.8rem;">{{ $moc['ten'] }}</div>
                        @if($isPending)
                            <span class="badge bg-warning text-dark rounded-pill small fw-bold" style="font-size: 0.68rem;">
                                CHỜ DUYỆT (CHO SỬA)
                            </span>
                        @elseif($isResubmit)
                            <span class="badge bg-info text-dark rounded-pill small fw-bold" style="font-size: 0.68rem;">
                                CẦN NỘP LẠI
                            </span>
                        @elseif(isset($timeCheckHienTai) && !$timeCheckHienTai['is_open'])
                            <span class="badge bg-secondary text-white rounded-pill small fw-bold" style="font-size: 0.68rem;">
                                CHƯA MỞ
                            </span>
                        @else
                            <span class="badge bg-primary text-white rounded-pill small fw-bold" style="font-size: 0.68rem;">
                                ĐANG MỞ
                            </span>
                        @endif
                    @else
                        <!-- Giai đoạn tương lai chưa mở -->
                        <div class="mx-auto rounded-circle text-secondary border mb-2"
                             style="width: 44px; height: 44px; background-color: #f8fafc; display: flex; align-items: center; justify-content: center;">
                            <span class="fw-bold text-muted">{{ $soMoc }}</span>
                        </div>
                        <div class="fw-semibold small text-muted" style="font-size: 0.78rem;">{{ $moc['ten'] }}</div>
                        <span class="badge bg-light text-muted border rounded-pill small" style="font-size: 0.68rem;">
                            Chưa mở
                        </span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@if($allCompleted)
<!-- Trạng thái hoàn thành tất cả 6 giai đoạn -->
<div class="card card-premium shadow-sm border-0 mb-4 bg-success bg-opacity-10 border-success">
    <div class="card-body p-4 text-center">
        <i class="fa-solid fa-trophy fa-3x text-success mb-3"></i>
        <h4 class="fw-bold text-success">Chúc mừng nhóm của bạn!</h4>
        <p class="text-muted mb-3">
            Nhóm đã hoàn thành xuất sắc toàn bộ 6 giai đoạn khóa luận tốt nghiệp và được nghiệm thu kết quả chính thức.
        </p>
        <a href="{{ route('sinhvien.ketqua.index') }}" class="btn btn-success rounded-pill px-4 fw-bold">
            <i class="fa-solid fa-graduation-cap me-2"></i>Xem Bảng Điểm & Kết Quả Khóa Luận
        </a>
    </div>
</div>
@else

<!-- Card Chi Tiết Giai Đoạn Hiện Tại: Nộp Báo Cáo / Chỉnh Sửa Tiến Độ -->
<div class="card card-premium shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-pen-ruler text-primary me-2"></i>{{ $mocs[$mocHienTai]['ten'] }}
            </h5>
        </div>
        <div class="d-flex gap-2 align-items-center">
            @if(isset($timeCheckHienTai) && !$timeCheckHienTai['is_open'])
                <span class="badge bg-secondary text-white rounded-pill px-3 py-2 fw-bold" style="font-size: 13px;">
                    <i class="fa-solid fa-clock me-1"></i>Chưa mở nộp bài {{ !empty($timeCheckHienTai['start_date']) ? '(Từ ' . \Carbon\Carbon::parse($timeCheckHienTai['start_date'])->format('d/m/Y') . ')' : '' }}
                </span>
            @elseif($bcHienTai && $bcHienTai->TrangThai === 'Chờ duyệt')
                <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold" style="font-size: 13px;">
                    <i class="fa-solid fa-hourglass-half me-1"></i>Đang chờ GVHD đánh giá (Cho phép chỉnh sửa)
                </span>
            @elseif($bcHienTai && $bcHienTai->TrangThai === 'Yêu cầu nộp lại')
                <span class="badge bg-info text-dark rounded-pill px-3 py-2 fw-bold" style="font-size: 13px;">
                    <i class="fa-solid fa-rotate-left me-1"></i>GVHD yêu cầu nộp lại
                </span>
            @else
                <span class="badge bg-success rounded-pill px-3 py-2 fw-bold" style="font-size: 13px;">
                    <i class="fa-solid fa-lock-open me-1"></i>Đang mở nộp bài
                </span>
            @endif

            @if(!empty($timeCheckHienTai['end_date']))
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 small fw-bold">
                    <i class="fa-regular fa-clock me-1"></i>Hạn nộp: {{ \Carbon\Carbon::parse($timeCheckHienTai['end_date'])->format('d/m/Y') }}
                </span>
            @elseif(isset($timeCheckHienTai) && empty($timeCheckHienTai['end_date']) && !empty($timeCheckHienTai['start_date']))
                <span class="badge bg-light text-secondary border rounded-pill px-3 py-2 small fw-semibold">
                    <i class="fa-solid fa-infinity me-1"></i>Không giới hạn hạn chót
                </span>
            @elseif(isset($mocDeadlines[$mocHienTai]))
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 small fw-bold">
                    <i class="fa-regular fa-clock me-1"></i>Hạn nộp: {{ \Carbon\Carbon::parse($mocDeadlines[$mocHienTai])->format('d/m/Y') }}
                </span>
            @endif
        </div>
    </div>
    <div class="card-body p-4">
        <!-- Quy định & Hướng dẫn nộp bài -->
        <div class="p-3 rounded-3 bg-light border border-info-subtle mb-4">
            <div class="fw-bold text-primary small mb-1">
                <i class="fa-solid fa-circle-info me-1"></i>Nội dung & Yêu cầu giai đoạn này:
            </div>
            <div class="small text-secondary" style="line-height: 1.6;">
                {{ $mocs[$mocHienTai]['mo_ta'] }}
                <ul class="mb-0 mt-1 ps-3">
                    @if(in_array($mocs[$mocHienTai]['loai'], ['pdf', 'pdf_git']))
                        <li>Định dạng tệp đính kèm: <strong>PDF</strong> (Dung lượng tối đa <strong>20MB</strong>).</li>
                    @endif
                    @if(in_array($mocs[$mocHienTai]['loai'], ['git', 'pdf_git']))
                        <li>Đường dẫn mã nguồn: URL repository hợp lệ (<strong>https://github.com/...</strong> hoặc <strong>https://gitlab.com/...</strong>).</li>
                    @endif
                    <li>Hệ thống tự động sử dụng <strong>Trí tuệ Nhân tạo (AI)</strong> để tóm tắt các công việc hoàn thành và khó khăn giúp GVHD nắm bắt nhanh chóng.</li>
                </ul>
            </div>
        </div>

        @if(isset($timeCheckHienTai) && !$timeCheckHienTai['is_open'])
            <div class="alert alert-warning border mb-3" style="font-size: 13px;">
                <i class="fa-solid fa-calendar-xmark text-warning me-2 fs-6"></i>
                <strong>Thông báo mốc thời gian:</strong> {{ $timeCheckHienTai['message'] }}
            </div>
        @endif

        @if($bcHienTai && $bcHienTai->khoiBaoCaos && $bcHienTai->khoiBaoCaos->isNotEmpty())
            <!-- DÒNG THỜI GIAN CÁC LẦN NỘP (TIMELINE) CỦA GIAI ĐOẠN HIỆN TẠI -->
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-dark" style="font-size: 14px;">
                        <i class="fa-solid fa-timeline text-primary me-1"></i>Lịch Sử Các Lần Nộp (Timeline) — GĐ {{ $mocHienTai }}
                    </span>
                    <span class="badge bg-light text-primary border" style="font-size: 12px;">
                        Đã có {{ $bcHienTai->khoiBaoCaos->count() }} khối nộp bài
                    </span>
                </div>
                <div class="p-3 bg-light rounded-3 border">
                    <div class="position-relative ps-3" style="border-left: 2px solid #cbd5e1; margin-left: 10px;">
                        @foreach($bcHienTai->khoiBaoCaos as $khoi)
                        <div class="position-relative mb-3 pb-1">
                            <!-- Nút tròn timeline -->
                            <div class="position-absolute rounded-circle {{ $khoi->TrangThai === 'Đạt' ? 'bg-success' : ($khoi->TrangThai === 'Yêu cầu nộp lại' ? 'bg-warning' : 'bg-primary') }}" 
                                 style="width: 14px; height: 14px; left: -24px; top: 6px; border: 2px solid #fff;"></div>
                            <div class="card border bg-white p-3 rounded-2 shadow-none" style="font-size: 13px;">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary text-white fw-bold">Khối #{{ $khoi->LanNop }} (Lần nộp {{ $khoi->LanNop }})</span>
                                        <span class="text-muted small">
                                            <i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($khoi->NgayNop)->format('H:i - d/m/Y') }}
                                        </span>
                                    </div>
                                    <div>
                                        @if($khoi->TrangThai === 'Đạt')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">✅ Đạt</span>
                                        @elseif($khoi->TrangThai === 'Yêu cầu nộp lại')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-bold">🔄 Yêu cầu chỉnh sửa</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border fw-bold">⏳ Đang chờ GVHD đánh giá</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    @if($khoi->TenFile)
                                        <a href="{{ asset('storage/' . $khoi->DuongDanFile) }}" target="_blank" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 small">
                                            <i class="fa-solid fa-file-pdf me-1"></i>{{ $khoi->TenFile }}
                                        </a>
                                    @endif
                                    @if($khoi->LinkCode)
                                        <a href="{{ $khoi->LinkCode }}" target="_blank" class="btn btn-xs btn-outline-dark rounded-pill px-2 py-1 small">
                                            <i class="fa-brands fa-github me-1"></i>Link Code
                                        </a>
                                    @endif
                                </div>
                                @if($khoi->NoiDungBaoCao)
                                    <div class="small text-secondary mb-1">
                                        <strong>Ghi chú nộp bài:</strong> {{ $khoi->NoiDungBaoCao }}
                                    </div>
                                @endif
                                @if($khoi->NhanXetGV)
                                    <div class="mt-2 p-2 rounded bg-light border-start border-3 {{ $khoi->TrangThai === 'Đạt' ? 'border-success' : 'border-warning' }}">
                                        <div class="small fw-bold text-dark">
                                            <i class="fa-solid fa-comment-dots text-primary me-1"></i>Đánh giá từ {{ $khoi->NguoiNhanXet ?? 'GVHD' }} 
                                            @if($khoi->NgayNhanXet)
                                                <span class="text-muted fw-normal">({{ \Carbon\Carbon::parse($khoi->NgayNhanXet)->format('H:i d/m/Y') }})</span>
                                            @endif:
                                        </div>
                                        <div class="small text-dark mt-1"><em>"{{ $khoi->NhanXetGV }}"</em></div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($bcHienTai && $bcHienTai->TrangThai === 'Chờ duyệt')
            <div class="alert alert-info border-info-subtle d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-info fs-5"></i>
                    <div>
                        <div class="fw-bold text-dark">Bản nộp gần nhất đang chờ GVHD đánh giá</div>
                        <div class="small text-muted">Trong khi chờ đánh giá, bạn có thể chỉnh sửa nội dung, ghi chú hoặc cập nhật file mới nếu cần thiết.</div>
                    </div>
                </div>
                <span class="badge bg-light text-primary border">Có thể chỉnh sửa</span>
            </div>
        @elseif($bcHienTai && $bcHienTai->TrangThai === 'Yêu cầu nộp lại')
            <div class="alert alert-warning border mb-3" style="font-size: 13px;">
                <div class="fw-bold text-dark"><i class="fa-solid fa-circle-exclamation text-warning me-1"></i>Giảng viên yêu cầu chỉnh sửa báo cáo!</div>
                <div class="small text-dark mt-1">Hệ thống sẽ lưu lại khối nộp bài trước đó và tự động tạo <strong>Khối nộp bài mới (Lần nộp {{ $bcHienTai->khoiBaoCaos->max('LanNop') + 1 }})</strong> khi bạn nộp bản báo cáo cập nhật.</div>
            </div>
        @endif

        <form action="{{ route('sinhvien.baocao.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="LanBaoCao" value="{{ $mocHienTai }}">

            <div class="row g-3 mb-3">
                <!-- Tiêu đề báo cáo -->
                <div class="col-md-12">
                    <label class="form-label small fw-bold text-dark">
                        Tiêu đề tiến độ <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="TieuDe" class="form-control form-control-sm" 
                           value="{{ $bcHienTai?->TieuDe ?? ('Báo cáo tiến độ ' . $mocs[$mocHienTai]['ten']) }}" required>
                </div>

                <!-- Tệp báo cáo PDF nếu loại mốc yêu cầu -->
                @if(in_array($mocs[$mocHienTai]['loai'], ['pdf', 'pdf_git']))
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">
                        Tệp tài liệu báo cáo (PDF) 
                        @if(!$bcHienTai || !$bcHienTai->DuongDanFile) <span class="text-danger">*</span> @endif
                    </label>
                    <input type="file" name="FileBaoCao" class="form-control form-control-sm" accept=".pdf" {{ (!$bcHienTai || !$bcHienTai->DuongDanFile) ? 'required' : '' }}>
                    <div class="form-text" style="font-size: 0.72rem;">
                        @if($bcHienTai && $bcHienTai->TenFile)
                            <span class="text-success fw-semibold"><i class="fa-solid fa-file-pdf me-1"></i>Đã có: {{ $bcHienTai->TenFile }}</span> (Chọn file mới nếu muốn thay thế)
                        @else
                            Định dạng: .pdf (Dung lượng tối đa 20MB)
                        @endif
                    </div>
                </div>
                @endif

                <!-- Link Git nếu loại mốc yêu cầu -->
                @if(in_array($mocs[$mocHienTai]['loai'], ['git', 'pdf_git']))
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">
                        Link Source Code (GitHub / GitLab)
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="fa-brands fa-github"></i></span>
                        <input type="url" name="LinkCode" class="form-control form-control-sm" 
                                placeholder="https://github.com/username/repository" 
                                value="{{ $bcHienTai?->LinkCode ?? '' }}">
                    </div>
                    <div class="form-text" style="font-size: 0.72rem;">Đường dẫn repository công khai hoặc branch đồ án</div>
                </div>
                @endif

                <!-- Ghi chú cho GVHD -->
                <div class="col-12">
                    <label class="form-label small fw-bold text-dark">
                        Nội dung chi tiết & Ghi chú gửi Giảng viên hướng dẫn
                    </label>
                    <textarea name="NoiDungBaoCao" rows="3" class="form-control form-control-sm @error('NoiDungBaoCao') is-invalid @enderror" 
                              minlength="20" maxlength="5000"
                              placeholder="Mô tả tóm tắt các kết quả đạt được, khó khăn hoặc câu hỏi dành cho thầy/cô hướng dẫn (tối thiểu 20 ký tự)...">{{ old('NoiDungBaoCao', $bcHienTai?->NoiDungBaoCao ?? '') }}</textarea>
                    @error('NoiDungBaoCao')
                        <div class="invalid-feedback d-block fw-semibold" style="font-size: 13px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                @if(isset($timeCheckHienTai) && !$timeCheckHienTai['is_open'])
                    <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold" disabled style="font-size: 13px;" title="{{ $timeCheckHienTai['message'] }}">
                        <i class="fa-solid fa-lock me-1"></i>Chưa Đến Hạn Nộp {{ !empty($timeCheckHienTai['start_date']) ? '(Mở từ ' . \Carbon\Carbon::parse($timeCheckHienTai['start_date'])->format('d/m/Y') . ')' : '' }}
                    </button>
                @else
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold d-inline-flex align-items-center gap-2"
                            style="background: #2563eb; border: none; font-size: 13px;">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        @if($bcHienTai && $bcHienTai->TrangThai === 'Yêu cầu nộp lại')
                            <span>Nộp Lại Báo Cáo Tiến Độ (Tạo Khối Mới — Lần nộp {{ ($bcHienTai->khoiBaoCaos->max('LanNop') ?? 1) + 1 }})</span>
                        @elseif($bcHienTai)
                            <span>Lưu Thay Đổi & Cập Nhật Tiến Độ GĐ {{ $mocHienTai }}</span>
                        @else
                            <span>Nộp Báo Cáo Tiến Độ GĐ {{ $mocHienTai }}</span>
                        @endif
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
@endif

<!-- Lịch Sử Các Báo Cáo Đã Nộp -->
<div class="card card-premium shadow-sm border-0">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left text-primary"></i>
        <h6 class="fw-bold mb-0">Lịch Sử Các Báo Cáo Đã Nộp</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small text-secondary">
                <tr>
                    <th class="ps-4" width="18%">Mốc nộp</th>
                    <th width="12%">Ngày nộp</th>
                    <th width="24%">Tệp đính kèm & Mã nguồn</th>
                    <th width="22%">Đánh giá của GVHD</th>
                    <th width="12%">Khối / Timeline</th>
                    <th width="12%" class="text-end pe-4">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse($baoCaos as $bc)
                <tr>
                    <td class="ps-4">
                        <span class="badge bg-secondary-subtle text-secondary-emphasis fw-bold border">
                            Mốc {{ $bc->LanBaoCao }}
                        </span>
                        <div class="small fw-semibold text-dark mt-1">{{ $bc->TieuDe }}</div>
                    </td>
                    <td class="small text-muted">
                        <i class="fa-regular fa-calendar me-1"></i>{{ $bc->NgayNop ? \Carbon\Carbon::parse($bc->NgayNop)->format('d/m/Y') : '—' }}
                    </td>
                    <td>
                        @if($bc->TenFile)
                            <div class="d-inline-block me-2 mb-1">
                                <a href="{{ asset('storage/' . $bc->DuongDanFile) }}" target="_blank" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 small">
                                    <i class="fa-solid fa-file-pdf me-1"></i>{{ Str::limit($bc->TenFile, 20) }}
                                </a>
                            </div>
                        @endif
                        @if($bc->LinkCode)
                            <div class="d-inline-block mb-1">
                                <a href="{{ $bc->LinkCode }}" target="_blank" class="btn btn-xs btn-outline-dark rounded-pill px-2 py-1 small">
                                    <i class="fa-brands fa-github me-1"></i>Source Code
                                </a>
                            </div>
                        @endif
                        @if(!$bc->TenFile && !$bc->LinkCode)
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @if($bc->NhanXet)
                            <div class="small text-dark" style="font-size: 0.82rem; line-height: 1.4;">
                                <em>"{{ $bc->NhanXet }}"</em>
                            </div>
                        @else
                            <span class="text-muted small"><em>Đang chờ giảng viên nhận xét...</em></span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-1 small d-inline-flex align-items-center gap-1"
                                    data-bs-toggle="modal" data-bs-target="#modalTimeline{{ $bc->MaBaoCao }}" title="Xem toàn bộ các khối nộp bài">
                                <i class="fa-solid fa-timeline"></i>
                                <span>Timeline ({{ $bc->khoiBaoCaos->count() }})</span>
                            </button>
                            @if($bc->tomTatBaoCao)
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1 small"
                                        data-bs-toggle="modal" data-bs-target="#modalAi{{ $bc->MaBaoCao }}" title="Xem AI tóm tắt">
                                    <i class="fa-solid fa-wand-magic-sparkles text-warning"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        @if($bc->TrangThai === 'Đạt')
                            <span class="badge bg-success rounded-pill px-3 py-1">✅ Đạt</span>
                        @elseif($bc->TrangThai === 'Chờ duyệt')
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1">⏳ Chờ duyệt</span>
                        @else
                            <span class="badge bg-info text-dark rounded-pill px-3 py-1">🔄 Nộp lại</span>
                        @endif
                    </td>
                </tr>

                <!-- Modal Timeline cho từng Mốc Báo Cáo -->
                <div class="modal fade" id="modalTimeline{{ $bc->MaBaoCao }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content border-0">
                            <div class="modal-header bg-white border-bottom py-3">
                                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-timeline text-primary"></i>
                                    <span>Lịch Sử Các Khối Nộp Bài (Timeline) — Mốc {{ $bc->LanBaoCao }}</span>
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4 bg-light">
                                <div class="position-relative ps-3" style="border-left: 2px solid #cbd5e1; margin-left: 10px;">
                                    @forelse($bc->khoiBaoCaos as $khoi)
                                    <div class="position-relative mb-3 pb-1">
                                        <div class="position-absolute rounded-circle {{ $khoi->TrangThai === 'Đạt' ? 'bg-success' : ($khoi->TrangThai === 'Yêu cầu nộp lại' ? 'bg-warning' : 'bg-primary') }}" 
                                             style="width: 14px; height: 14px; left: -24px; top: 6px; border: 2px solid #fff;"></div>
                                        <div class="card border bg-white p-3 rounded-2 shadow-none" style="font-size: 13px;">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                                <div>
                                                    <span class="badge bg-primary text-white fw-bold">Khối #{{ $khoi->LanNop }} (Lần nộp {{ $khoi->LanNop }})</span>
                                                    <span class="text-muted small ms-2">
                                                        <i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($khoi->NgayNop)->format('H:i - d/m/Y') }}
                                                    </span>
                                                </div>
                                                <div>
                                                    @if($khoi->TrangThai === 'Đạt')
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">✅ Đạt</span>
                                                    @elseif($khoi->TrangThai === 'Yêu cầu nộp lại')
                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-bold">🔄 Yêu cầu chỉnh sửa</span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary border fw-bold">⏳ Đang chờ GVHD đánh giá</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2 mb-2">
                                                @if($khoi->TenFile)
                                                    <a href="{{ asset('storage/' . $khoi->DuongDanFile) }}" target="_blank" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 small">
                                                        <i class="fa-solid fa-file-pdf me-1"></i>{{ $khoi->TenFile }}
                                                    </a>
                                                @endif
                                                @if($khoi->LinkCode)
                                                    <a href="{{ $khoi->LinkCode }}" target="_blank" class="btn btn-xs btn-outline-dark rounded-pill px-2 py-1 small">
                                                        <i class="fa-brands fa-github me-1"></i>Source Code
                                                    </a>
                                                @endif
                                            </div>
                                            @if($khoi->NoiDungBaoCao)
                                                <div class="small text-secondary mb-1">
                                                    <strong>Ghi chú nộp:</strong> {{ $khoi->NoiDungBaoCao }}
                                                </div>
                                            @endif
                                            @if($khoi->NhanXetGV)
                                                <div class="mt-2 p-2 rounded bg-light border-start border-3 {{ $khoi->TrangThai === 'Đạt' ? 'border-success' : 'border-warning' }}">
                                                    <div class="small fw-bold text-dark">
                                                        <i class="fa-solid fa-comment-dots text-primary me-1"></i>Đánh giá từ {{ $khoi->NguoiNhanXet ?? 'GVHD' }} 
                                                        @if($khoi->NgayNhanXet)
                                                            <span class="text-muted fw-normal">({{ \Carbon\Carbon::parse($khoi->NgayNhanXet)->format('H:i d/m/Y') }})</span>
                                                        @endif:
                                                    </div>
                                                    <div class="small text-dark mt-1"><em>"{{ $khoi->NhanXetGV }}"</em></div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-muted small">Chưa có khối nộp bài nào.</div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="modal-footer py-2 bg-white">
                                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Tóm Tắt AI cho sinh viên -->
                @if($bc->tomTatBaoCao)
                @php $tt = $bc->tomTatBaoCao; @endphp
                <div class="modal fade" id="modalAi{{ $bc->MaBaoCao }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header text-white" style="background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);">
                                <h6 class="modal-title fw-bold">
                                    <i class="fa-solid fa-robot me-2"></i>Trợ Lý AI Tóm Tắt — Mốc {{ $bc->LanBaoCao }}
                                </h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="small text-muted">
                                        <i class="fa-regular fa-clock me-1"></i>Tóm tắt lúc: {{ \Carbon\Carbon::parse($tt->NgayTomTat)->format('d/m/Y H:i') }}
                                    </span>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                                        Độ tin cậy: {{ number_format($tt->DoTinCayAI, 0) }}%
                                    </span>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="p-3 rounded-3 border-start border-success border-4" style="background: #f0fdf4;">
                                            <div class="fw-bold text-success mb-1"><i class="fa-solid fa-circle-check me-1"></i>1. Công việc đã hoàn thành:</div>
                                            <div class="small text-dark" style="line-height: 1.5;">{{ $tt->CongViecDaHoanThanh }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3 rounded-3 border-start border-warning border-4" style="background: #fffbeb;">
                                            <div class="fw-bold text-warning-emphasis mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>2. Khó khăn & Vướng mắc:</div>
                                            <div class="small text-dark" style="line-height: 1.5;">{{ $tt->KhoKhan }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3 rounded-3 border-start border-primary border-4" style="background: #eff6ff;">
                                            <div class="fw-bold text-primary mb-1"><i class="fa-solid fa-calendar-plus me-1"></i>3. Kế hoạch tuần tới:</div>
                                            <div class="small text-dark" style="line-height: 1.5;">{{ $tt->KeHoachTuanToi }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="fa-solid fa-folder-open fa-2x mb-2 text-secondary"></i>
                        <div>Nhóm của bạn chưa nộp bài báo cáo tiến độ nào.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@else
<!-- Trường hợp sinh viên chưa có nhóm hoặc chưa có đề tài được duyệt -->
<div class="card card-premium shadow-sm border-0">
    <div class="card-body text-center py-5">
        <i class="fa-solid fa-user-group fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Chưa đủ điều kiện nộp báo cáo tiến độ</h5>
        <p class="text-muted">
            Để nộp báo cáo tiến độ 5 mốc, bạn cần có Nhóm và Đề tài khóa luận được Giáo vụ Khoa phê duyệt chính thức.
        </p>
        <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fa-solid fa-users me-1"></i>Quản lý Nhóm & Đăng ký đề tài
        </a>
    </div>
</div>
@endif

@endsection
