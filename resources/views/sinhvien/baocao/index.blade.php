@extends('layouts.sinhvien')

@section('page_title', 'Báo Cáo Tiến Độ Khóa Luận')

@section('content')
@if(isset($error) && $error)
<div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
    <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $error }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Tiêu đề trang -->
<div class="mb-4">
    <h4 class="fw-bold mb-0 text-dark" style="font-size: 1.5rem;">
        <i class="fa-solid fa-timeline me-2 text-primary"></i>Báo Cáo Tiến Độ Khóa Luận
    </h4>
</div>

@if(isset($nhom) && $nhom && $nhom->deTai)
<!-- Tóm tắt Đề tài & Giảng viên hướng dẫn -->
<div class="card mb-4 bg-light border-0 shadow-xs">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="fw-bold text-dark fs-6 mb-1">
                    <i class="fa-solid fa-book-bookmark text-primary me-2"></i>{{ $nhom->TenNhom }} — {{ $nhom->deTai->TenDeTai }}
                </div>
                <div class="text-muted d-flex flex-wrap gap-3" style="font-size: 13px;">
                    <span><i class="fa-solid fa-chalkboard-user me-1 text-secondary"></i>GVHD: <strong>{{ $nhom->deTai->giangVien->HoTen ?? 'Chưa phân công' }}</strong></span>
                    @if($nhom->deTai->giangVien?->Email)
                        <span><i class="fa-regular fa-envelope me-1 text-secondary"></i>{{ $nhom->deTai->giangVien->Email }}</span>
                    @endif
                </div>
            </div>
            <span class="badge bg-success text-white rounded-pill px-3 py-1.5">
                <i class="fa-solid fa-circle-check me-1"></i>Đề tài đã phê duyệt
            </span>
        </div>
    </div>
</div>

@php
    $allCompleted = ($baoCaos->count() >= 6 && isset($baoCaos[6]) && $baoCaos[6]->TrangThai === 'Đạt');
    $bcHienTai = $baoCaos[$mocHienTai] ?? null;
@endphp

<!-- Stepper Lộ Trình 6 Giai Đoạn -->
<div class="card mb-4">
    <div class="card-body p-4">
        <div class="row g-2 text-center position-relative">
            <!-- Thanh nối ngang giữa các mốc -->
            <div class="position-absolute top-50 start-0 translate-middle-y w-100 d-none d-md-block" style="height: 3px; background: #e2e8f0; z-index: 1; margin-top: -16px;">
                @php
                    $completedCount = 0;
                    for ($i = 1; $i <= 6; $i++) {
                        if (isset($baoCaos[$i]) && $baoCaos[$i]->TrangThai === 'Đạt') {
                            $completedCount++;
                        }
                    }
                    $progressPercent = ($completedCount / 6) * 100;
                @endphp
                <div style="height: 100%; width: {{ $progressPercent }}%; background: #004E64; transition: width 0.4s ease;"></div>
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
                    <div class="mx-auto rounded-circle text-white mb-2 shadow-xs"
                         style="width: 40px; height: 40px; background-color: #25A18E; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-check fw-bold fs-6"></i>
                    </div>
                    <div class="fw-bold text-dark" style="font-size: 13px;">{{ $moc['ten'] }}</div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill" style="font-size: 11.5px;">
                        Đạt
                    </span>
                @elseif($isCurrent)
                    <div class="mx-auto rounded-circle text-white mb-2 shadow-sm"
                         style="width: 42px; height: 42px; background-color: {{ $isPending ? '#f59e0b' : ($isResubmit ? '#00838C' : '#004E64') }}; margin-top: -1px; display: flex; align-items: center; justify-content: center;">
                        @if($isPending)
                            <i class="fa-solid fa-hourglass-half fs-6"></i>
                        @elseif($isResubmit)
                            <i class="fa-solid fa-rotate-left fs-6"></i>
                        @else
                            <span class="fw-bold fs-6">{{ $soMoc }}</span>
                        @endif
                    </div>
                    <div class="fw-bold text-primary" style="font-size: 13px;">{{ $moc['ten'] }}</div>
                    @if($isPending)
                        <span class="badge bg-warning text-dark rounded-pill" style="font-size: 11.5px;">
                            Chờ duyệt
                        </span>
                    @elseif($isResubmit)
                        <span class="badge bg-info text-dark rounded-pill" style="font-size: 11.5px;">
                            Cần nộp lại
                        </span>
                    @elseif(isset($timeCheckHienTai) && !$timeCheckHienTai['is_open'])
                        <span class="badge bg-secondary text-white rounded-pill" style="font-size: 11.5px;">
                            Chưa mở
                        </span>
                    @else
                        <span class="badge bg-primary text-white rounded-pill" style="font-size: 11.5px;">
                            Đang mở
                        </span>
                    @endif
                @else
                    <div class="mx-auto rounded-circle text-secondary border mb-2 bg-white"
                         style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <span class="fw-bold text-muted" style="font-size: 13px;">{{ $soMoc }}</span>
                    </div>
                    <div class="fw-semibold text-muted" style="font-size: 13px;">{{ $moc['ten'] }}</div>
                    <span class="badge bg-light text-muted border rounded-pill" style="font-size: 11.5px;">
                        Chưa mở
                    </span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

@if($allCompleted)
<!-- Trạng thái hoàn thành tất cả các mốc -->
<div class="card mb-4 bg-success bg-opacity-10 border-success">
    <div class="card-body p-4 text-center">
        <i class="fa-solid fa-award fa-3x text-success mb-2"></i>
        <h5 class="fw-bold text-success mb-1">Chúc mừng nhóm của bạn!</h5>
        <p class="text-muted mb-3" style="font-size: 13.5px;">
            Nhóm đã hoàn thành xuất sắc toàn bộ các mốc báo cáo tiến độ và đủ điều kiện nghiệm thu.
        </p>
        <a href="{{ route('sinhvien.ketqua.index') }}" class="btn btn-success rounded-pill px-4 fw-bold">
            <i class="fa-solid fa-graduation-cap me-2"></i>Xem Kết Quả Khóa Luận
        </a>
    </div>
</div>
@else

<!-- Khung Nộp Báo Cáo Giai Đoạn Hiện Tại -->
<div class="card mb-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-pen-ruler text-primary"></i>
            <h6 class="fw-bold mb-0 text-dark">{{ $mocs[$mocHienTai]['ten'] }}</h6>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            @if(isset($timeCheckHienTai) && !$timeCheckHienTai['is_open'])
                <span class="badge bg-secondary text-white rounded-pill px-3 py-1">
                    <i class="fa-solid fa-clock me-1"></i>Chưa mở nộp {{ !empty($timeCheckHienTai['start_date']) ? '(Từ ' . \Carbon\Carbon::parse($timeCheckHienTai['start_date'])->format('d/m/Y') . ')' : '' }}
                </span>
            @elseif($bcHienTai && $bcHienTai->TrangThai === 'Chờ duyệt')
                <span class="badge bg-warning text-dark rounded-pill px-3 py-1">
                    <i class="fa-solid fa-hourglass-half me-1"></i>Đang chờ GVHD đánh giá (Có thể cập nhật)
                </span>
            @elseif($bcHienTai && $bcHienTai->TrangThai === 'Yêu cầu nộp lại')
                <span class="badge bg-info text-dark rounded-pill px-3 py-1">
                    <i class="fa-solid fa-rotate-left me-1"></i>GVHD yêu cầu nộp lại
                </span>
            @else
                <span class="badge bg-success rounded-pill px-3 py-1">
                    <i class="fa-solid fa-lock-open me-1"></i>Đang mở nộp bài
                </span>
            @endif

            @if(!empty($timeCheckHienTai['end_date']))
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">
                    <i class="fa-regular fa-clock me-1"></i>Hạn chót: {{ \Carbon\Carbon::parse($timeCheckHienTai['end_date'])->format('d/m/Y') }}
                </span>
            @elseif(isset($mocDeadlines[$mocHienTai]))
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">
                    <i class="fa-regular fa-clock me-1"></i>Hạn chót: {{ \Carbon\Carbon::parse($mocDeadlines[$mocHienTai])->format('d/m/Y') }}
                </span>
            @endif
        </div>
    </div>
    <div class="card-body p-4">
        <!-- Hướng dẫn nộp ngắn gọn -->
        <div class="p-3 rounded-3 bg-light border mb-4" style="font-size: 13px;">
            <div class="fw-semibold text-dark mb-1">
                <i class="fa-solid fa-circle-info text-primary me-1"></i>Yêu cầu nộp bài:
            </div>
            <div class="text-secondary">
                {{ $mocs[$mocHienTai]['mo_ta'] }} 
                (Định dạng tệp: <strong>PDF</strong> tối đa 20MB @if(in_array($mocs[$mocHienTai]['loai'], ['git', 'pdf_git'])), kèm đường dẫn <strong>GitHub/GitLab</strong> @endif).
            </div>
        </div>

        @if(isset($timeCheckHienTai) && !$timeCheckHienTai['is_open'])
            <div class="alert alert-warning border mb-3" style="font-size: 13.5px;">
                <i class="fa-solid fa-calendar-xmark text-warning me-2"></i>{{ $timeCheckHienTai['message'] }}
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <span class="fw-bold text-dark fs-6">
                <i class="fa-solid fa-layer-group text-primary me-1"></i>Các Báo Cáo Tiến Độ Nhỏ Trong Giai Đoạn {{ $mocHienTai }}
            </span>
            <a href="{{ route('sinhvien.baocao.create_khoi', $mocHienTai) }}" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 fw-bold shadow-xs">
                <i class="fa-solid fa-wand-magic-sparkles me-1"></i>+ Tạo Báo Cáo Nhỏ Mới (Tích hợp AI)
            </a>
        </div>

        @if($bcHienTai && $bcHienTai->khoiBaoCaos && $bcHienTai->khoiBaoCaos->isNotEmpty())
            <!-- Timeline các lần nộp bài của mốc hiện tại -->
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-dark" style="font-size: 13.5px;">
                        <i class="fa-solid fa-timeline text-primary me-1"></i>Lịch Sử Các Báo Cáo Nhỏ — GĐ {{ $mocHienTai }}
                    </span>
                    <span class="badge bg-light text-primary border">
                        {{ $bcHienTai->khoiBaoCaos->count() }} báo cáo
                    </span>
                </div>
                <div class="p-3 bg-light rounded-3 border">
                    <div class="position-relative ps-3" style="border-left: 2px solid #cbd5e1; margin-left: 10px;">
                        @foreach($bcHienTai->khoiBaoCaos as $khoi)
                        <div class="position-relative mb-3 pb-1">
                            <div class="position-absolute rounded-circle {{ $khoi->TrangThai === 'Đạt' ? 'bg-success' : ($khoi->TrangThai === 'Yêu cầu nộp lại' ? 'bg-warning' : 'bg-primary') }}" 
                                 style="width: 14px; height: 14px; left: -24px; top: 6px; border: 2px solid #fff;"></div>
                            <div class="card border bg-white p-3 rounded-2 shadow-none" style="font-size: 13px;">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary text-white fw-semibold">Đợt nộp #{{ $khoi->LanNop }}</span>
                                        <span class="fw-bold text-dark">{{ $khoi->TieuDe }}</span>
                                        <span class="text-muted" style="font-size: 12px;">
                                            <i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($khoi->NgayNop)->format('H:i - d/m/Y') }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($khoi->PhanTramHoanThanh)
                                            <span class="badge bg-light text-dark border">{{ $khoi->PhanTramHoanThanh }}%</span>
                                        @endif
                                        @if($khoi->TrangThai === 'Đạt')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">✅ Đạt</span>
                                        @elseif($khoi->TrangThai === 'Yêu cầu nộp lại')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">🔄 Yêu cầu chỉnh sửa</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border">⏳ Đang chờ đánh giá</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                    <div class="d-flex flex-wrap gap-2">
                                        @if($khoi->TenFile)
                                            <a href="{{ asset('storage/' . $khoi->DuongDanFile) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-0.5" style="font-size: 12px;">
                                                <i class="fa-solid fa-file-pdf me-1"></i>{{ $khoi->TenFile }}
                                            </a>
                                        @endif
                                        @if($khoi->LinkCode)
                                            <a href="{{ $khoi->LinkCode }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-0.5" style="font-size: 12px;">
                                                <i class="fa-brands fa-github me-1"></i>Source Code
                                            </a>
                                        @endif
                                        @if($khoi->TomTatJson || $khoi->TomTatAI)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill py-1">
                                                <i class="fa-solid fa-wand-magic-sparkles me-1"></i>AI Trích Xuất
                                            </span>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('sinhvien.baocao.show_khoi', $khoi->MaKhoi) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5" style="font-size: 12px;">
                                            <i class="fa-solid fa-eye me-1"></i>Xem chi tiết
                                        </a>
                                        @if($khoi->TrangThai !== 'Đạt')
                                            <a href="{{ route('sinhvien.baocao.edit_khoi', $khoi->MaKhoi) }}" class="btn btn-sm btn-outline-warning rounded-pill px-2.5 py-0.5" style="font-size: 12px;">
                                                <i class="fa-solid fa-pen me-1"></i>Sửa
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                @if($khoi->KetQuaDatDuoc || $khoi->NoiDungBaoCao)
                                    <div class="text-secondary mb-1" style="font-size: 12.5px;">
                                        <strong>Kết quả:</strong> {{ Str::limit($khoi->KetQuaDatDuoc ?: $khoi->NoiDungBaoCao, 160) }}
                                    </div>
                                @endif
                                @if($khoi->NhanXetGV)
                                    <div class="mt-2 p-2 rounded bg-light border-start border-3 {{ $khoi->TrangThai === 'Đạt' ? 'border-success' : 'border-warning' }}">
                                        <div class="fw-bold text-dark">
                                            <i class="fa-solid fa-comment-dots text-primary me-1"></i>Nhận xét từ {{ $khoi->NguoiNhanXet ?? 'GVHD' }}:
                                        </div>
                                        <div class="text-dark mt-1"><em>"{{ $khoi->NhanXetGV }}"</em></div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('sinhvien.baocao.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="LanBaoCao" value="{{ $mocHienTai }}">

            <div class="row g-3 mb-3">
                <div class="col-md-12">
                    <label class="form-label fw-bold text-dark" style="font-size: 13.5px;">
                        Tiêu đề tiến độ <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="TieuDe" class="form-control" 
                           value="{{ $bcHienTai?->TieuDe ?? ('Báo cáo tiến độ ' . $mocs[$mocHienTai]['ten']) }}" required>
                </div>

                @if(in_array($mocs[$mocHienTai]['loai'], ['pdf', 'pdf_git']))
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark" style="font-size: 13.5px;">
                        Tệp báo cáo (PDF) 
                        @if(!$bcHienTai || !$bcHienTai->DuongDanFile) <span class="text-danger">*</span> @endif
                    </label>
                    <input type="file" name="FileBaoCao" class="form-control" accept=".pdf" {{ (!$bcHienTai || !$bcHienTai->DuongDanFile) ? 'required' : '' }}>
                    <div class="form-text text-muted" style="font-size: 12.5px;">
                        @if($bcHienTai && $bcHienTai->TenFile)
                            <span class="text-success fw-semibold"><i class="fa-solid fa-file-pdf me-1"></i>Đã có: {{ $bcHienTai->TenFile }}</span> (Chọn file mới nếu muốn cập nhật)
                        @else
                            Định dạng: .pdf (Dung lượng tối đa 20MB)
                        @endif
                    </div>
                </div>
                @endif

                @if(in_array($mocs[$mocHienTai]['loai'], ['git', 'pdf_git']))
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark" style="font-size: 13.5px;">
                        Đường dẫn Source Code (GitHub / GitLab)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-brands fa-github"></i></span>
                        <input type="url" name="LinkCode" class="form-control" 
                               placeholder="https://github.com/username/repository" 
                               value="{{ $bcHienTai?->LinkCode ?? '' }}">
                    </div>
                    <div class="form-text text-muted" style="font-size: 12.5px;">Đường dẫn repository mã nguồn công khai</div>
                </div>
                @endif

                <div class="col-12">
                    <label class="form-label fw-bold text-dark" style="font-size: 13.5px;">
                        Nội dung công việc &amp; Ghi chú gửi Giảng viên hướng dẫn
                    </label>
                    <textarea name="NoiDungBaoCao" rows="3" class="form-control @error('NoiDungBaoCao') is-invalid @enderror" 
                              minlength="20" maxlength="5000"
                              placeholder="Mô tả tóm tắt các công việc đã hoàn thành, khó khăn gặp phải (tối thiểu 20 ký tự)...">{{ old('NoiDungBaoCao', $bcHienTai?->NoiDungBaoCao ?? '') }}</textarea>
                    @error('NoiDungBaoCao')
                        <div class="invalid-feedback d-block fw-semibold" style="font-size: 13px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                @if(isset($timeCheckHienTai) && !$timeCheckHienTai['is_open'])
                    <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold" disabled>
                        <i class="fa-solid fa-lock me-1"></i>Chưa Đến Hạn Nộp
                    </button>
                @else
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i>
                        @if($bcHienTai && $bcHienTai->TrangThai === 'Yêu cầu nộp lại')
                            Nộp Lại Báo Cáo (Lần nộp {{ ($bcHienTai->khoiBaoCaos->max('LanNop') ?? 1) + 1 }})
                        @elseif($bcHienTai)
                            Cập Nhật Báo Cáo Mốc {{ $mocHienTai }}
                        @else
                            Nộp Báo Cáo Tiến Độ Mốc {{ $mocHienTai }}
                        @endif
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
@endif

<!-- Bảng Lịch Sử Các Báo Cáo Đã Nộp -->
<div class="card">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left text-primary"></i>
        <h6 class="fw-bold mb-0 text-dark">Lịch Sử Các Mốc Báo Cáo Đã Nộp</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th width="18%">Mốc Nộp</th>
                    <th width="12%">Ngày Nộp</th>
                    <th width="24%">Tệp &amp; Source Code</th>
                    <th width="24%">Đánh Giá Của GVHD</th>
                    <th width="12%">Timeline</th>
                    <th width="10%" class="text-end pe-4">Trạng Thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse($baoCaos as $bc)
                <tr>
                    <td>
                        <span class="badge bg-secondary-subtle text-secondary-emphasis fw-bold border">
                            Mốc {{ $bc->LanBaoCao }}
                        </span>
                        <div class="fw-bold text-dark mt-1">{{ $bc->TieuDe }}</div>
                    </td>
                    <td class="text-muted" style="font-size: 13px;">
                        <i class="fa-regular fa-calendar me-1"></i>{{ $bc->NgayNop ? \Carbon\Carbon::parse($bc->NgayNop)->format('d/m/Y') : '—' }}
                    </td>
                    <td>
                        @if($bc->TenFile)
                            <div class="d-inline-block me-1 mb-1">
                                <a href="{{ asset('storage/' . $bc->DuongDanFile) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1">
                                    <i class="fa-solid fa-file-pdf me-1"></i>{{ Str::limit($bc->TenFile, 18) }}
                                </a>
                            </div>
                        @endif
                        @if($bc->LinkCode)
                            <div class="d-inline-block mb-1">
                                <a href="{{ $bc->LinkCode }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-2.5 py-1">
                                    <i class="fa-brands fa-github me-1"></i>GitHub
                                </a>
                            </div>
                        @endif
                        @if(!$bc->TenFile && !$bc->LinkCode)
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($bc->NhanXet)
                            <div class="text-dark fst-italic" style="font-size: 13px; line-height: 1.4;">
                                "{{ $bc->NhanXet }}"
                            </div>
                        @else
                            <span class="text-muted fst-italic" style="font-size: 13px;">Đang chờ GVHD đánh giá...</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5"
                                    data-bs-toggle="modal" data-bs-target="#modalTimeline{{ $bc->MaBaoCao }}" title="Xem toàn bộ các khối nộp bài">
                                <i class="fa-solid fa-timeline me-1"></i>Timeline ({{ $bc->khoiBaoCaos->count() }})
                            </button>
                            @if($bc->tomTatBaoCao)
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1.5"
                                        data-bs-toggle="modal" data-bs-target="#modalAi{{ $bc->MaBaoCao }}" title="Xem trích yếu nội dung báo cáo">
                                    <i class="fa-solid fa-file-lines text-primary"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        @if($bc->TrangThai === 'Đạt')
                            <span class="badge bg-success rounded-pill px-3 py-1">Đạt</span>
                        @elseif($bc->TrangThai === 'Chờ duyệt')
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1">Chờ duyệt</span>
                        @else
                            <span class="badge bg-info text-dark rounded-pill px-3 py-1">Nộp lại</span>
                        @endif
                    </td>
                </tr>

                <!-- Modal Timeline cho từng Mốc Báo Cáo -->
                <div class="modal fade" id="modalTimeline{{ $bc->MaBaoCao }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                            <div class="modal-header bg-primary text-white py-3 px-4">
                                <h6 class="modal-title fw-bold d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-timeline"></i>
                                    <span>Lịch Sử Các Lần Nộp — Mốc {{ $bc->LanBaoCao }}</span>
                                </h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                                                    <span class="badge bg-primary text-white fw-bold">Lần nộp #{{ $khoi->LanNop }}</span>
                                                    <span class="text-muted ms-2">
                                                        <i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($khoi->NgayNop)->format('H:i - d/m/Y') }}
                                                    </span>
                                                </div>
                                                <div>
                                                    @if($khoi->TrangThai === 'Đạt')
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">✅ Đạt</span>
                                                    @elseif($khoi->TrangThai === 'Yêu cầu nộp lại')
                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-bold">🔄 Yêu cầu chỉnh sửa</span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary border fw-bold">⏳ Đang chờ đánh giá</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2 mb-2">
                                                @if($khoi->TenFile)
                                                    <a href="{{ asset('storage/' . $khoi->DuongDanFile) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                        <i class="fa-solid fa-file-pdf me-1"></i>{{ $khoi->TenFile }}
                                                    </a>
                                                @endif
                                                @if($khoi->LinkCode)
                                                    <a href="{{ $khoi->LinkCode }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                                        <i class="fa-brands fa-github me-1"></i>Source Code
                                                    </a>
                                                @endif
                                            </div>
                                            @if($khoi->NoiDungBaoCao)
                                                <div class="text-secondary mb-1">
                                                    <strong>Ghi chú:</strong> {{ $khoi->NoiDungBaoCao }}
                                                </div>
                                            @endif
                                            @if($khoi->NhanXetGV)
                                                <div class="mt-2 p-2 rounded bg-light border-start border-3 {{ $khoi->TrangThai === 'Đạt' ? 'border-success' : 'border-warning' }}">
                                                    <div class="fw-bold text-dark">
                                                        <i class="fa-solid fa-comment-dots text-primary me-1"></i>Đánh giá từ {{ $khoi->NguoiNhanXet ?? 'GVHD' }}:
                                                    </div>
                                                    <div class="text-dark mt-1"><em>"{{ $khoi->NhanXetGV }}"</em></div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-muted" style="font-size: 13px;">Chưa có khối nộp bài nào.</div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="modal-footer py-2 bg-white">
                                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Trích Yếu Báo Cáo cho sinh viên -->
                @if($bc->tomTatBaoCao)
                @php $tt = $bc->tomTatBaoCao; @endphp
                <div class="modal fade" id="modalAi{{ $bc->MaBaoCao }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                            <div class="modal-header text-white py-3 px-4" style="background-color: #004E64;">
                                <h6 class="modal-title fw-bold">
                                    <i class="fa-solid fa-file-lines me-2"></i>Trích Yếu Báo Cáo Tiến Độ — Mốc {{ $bc->LanBaoCao }}
                                </h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted" style="font-size: 13px;">
                                        <i class="fa-regular fa-clock me-1"></i>Trích xuất lúc: {{ \Carbon\Carbon::parse($tt->NgayTomTat)->format('d/m/Y H:i') }}
                                    </span>
                                    <span class="badge bg-light text-primary border rounded-pill px-3 py-1">
                                        Mức độ hoàn thiện: {{ number_format($tt->DoTinCayAI, 0) }}%
                                    </span>
                                </div>
                                <div class="row g-3" style="font-size: 14px;">
                                    <div class="col-12">
                                        <div class="p-3.5 rounded-3 border-start border-success border-4" style="background: #f0fdf4; padding: 18px 20px;">
                                            <div class="fw-bold text-success mb-1.5"><i class="fa-solid fa-circle-check me-1"></i>1. Nội dung đã thực hiện:</div>
                                            <div class="text-dark">{{ $tt->CongViecDaHoanThanh }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3.5 rounded-3 border-start border-warning border-4" style="background: #fffbeb; padding: 18px 20px;">
                                            <div class="fw-bold text-warning-emphasis mb-1.5"><i class="fa-solid fa-triangle-exclamation me-1"></i>2. Khó khăn &amp; Vướng mắc:</div>
                                            <div class="text-dark">{{ $tt->KhoKhan }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3.5 rounded-3 border-start border-primary border-4" style="background: #eff6ff; padding: 18px 20px;">
                                            <div class="fw-bold text-primary mb-1.5"><i class="fa-solid fa-calendar-check me-1"></i>3. Kế hoạch giai đoạn tiếp theo:</div>
                                            <div class="text-dark">{{ $tt->KeHoachTuanToi }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer px-4 py-3 bg-light border-top">
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-folder-open fs-2 mb-2 text-muted opacity-50 d-block"></i>
                        <div>Nhóm của bạn chưa nộp bài báo cáo tiến độ nào.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fa-solid fa-user-group fs-1 text-muted mb-3 d-block opacity-50"></i>
        <h5 class="fw-bold text-dark mb-2">Chưa Đủ Điều Kiện Báo Cáo Tiến Độ</h5>
        <p class="text-muted mb-4" style="font-size: 13.5px; max-width: 500px; margin: 0 auto;">
            Để nộp báo cáo tiến độ, bạn cần có Nhóm và Đề tài khóa luận được phê duyệt chính thức.
        </p>
        <a href="{{ route('sinhvien.nhom.index') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fa-solid fa-users me-1"></i>Quản lý Nhóm &amp; Đề tài
        </a>
    </div>
</div>
@endif

@endsection
