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
    $allCompleted = ($baoCaos->count() >= 5 && isset($baoCaos[5]) && $baoCaos[5]->TrangThai === 'Đạt');
    $bcHienTai = $baoCaos[$mocHienTai] ?? null;
@endphp

<!-- Milestone Stepper Ngang 5 Mốc Liên Hoàn -->
<div class="card card-premium shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <div class="stepper-horizontal-container">
            <div class="row g-2 text-center position-relative">
                <!-- Đường nối ngang giữa các mốc -->
                <div class="position-absolute top-50 start-0 translate-middle-y w-100 d-none d-md-block" style="height: 3px; background: #e2e8f0; z-index: 1; margin-top: -15px;">
                    @php
                        $completedCount = 0;
                        for ($i = 1; $i <= 5; $i++) {
                            if (isset($baoCaos[$i]) && $baoCaos[$i]->TrangThai === 'Đạt') {
                                $completedCount++;
                            }
                        }
                        $progressPercent = ($completedCount / 5) * 100;
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
                        <!-- Mốc hiện tại đang active -->
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
                                CHỜ DUYỆT
                            </span>
                        @elseif($isResubmit)
                            <span class="badge bg-info text-dark rounded-pill small fw-bold" style="font-size: 0.68rem;">
                                NỘP LẠI
                            </span>
                        @else
                            <span class="badge bg-primary text-white rounded-pill small fw-bold" style="font-size: 0.68rem;">
                                ĐANG MỞ
                            </span>
                        @endif
                    @else
                        <!-- Mốc tương lai chưa mở -->
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
<!-- Trạng thái hoàn thành tất cả 5 mốc -->
<div class="card card-premium shadow-sm border-0 mb-4 bg-success bg-opacity-10 border-success">
    <div class="card-body p-4 text-center">
        <i class="fa-solid fa-trophy fa-3x text-success mb-3"></i>
        <h4 class="fw-bold text-success">Chúc mừng nhóm của bạn!</h4>
        <p class="text-muted mb-3">
            Nhóm đã hoàn thành xuất sắc và được Giảng viên hướng dẫn đánh giá <strong>"Đạt"</strong> ở toàn bộ 5 mốc tiến độ.
        </p>
        <a href="{{ route('sinhvien.hoso.index') }}" class="btn btn-success rounded-pill px-4 fw-bold">
            <i class="fa-solid fa-file-shield me-2"></i>Chuyển đến Nộp Hồ Sơ Bảo Vệ (Turnitin)
        </a>
    </div>
</div>
@else

<!-- Card Chi Tiết Mốc Hiện Tại: Nộp Báo Cáo -->
<div class="card card-premium shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-pen-ruler text-primary me-2"></i>{{ $mocs[$mocHienTai]['ten'] }}
            </h5>
        </div>
        <div class="d-flex gap-2 align-items-center">
            @if($bcHienTai && $bcHienTai->TrangThai === 'Chờ duyệt')
                <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold">
                    <i class="fa-solid fa-hourglass-half me-1"></i>Đang chờ GVHD đánh giá
                </span>
            @elseif($bcHienTai && $bcHienTai->TrangThai === 'Yêu cầu nộp lại')
                <span class="badge bg-info text-dark rounded-pill px-3 py-2 fw-bold">
                    <i class="fa-solid fa-rotate-left me-1"></i>GVHD yêu cầu nộp lại
                </span>
            @else
                <span class="badge bg-primary rounded-pill px-3 py-2 fw-bold">
                    <i class="fa-solid fa-lock-open me-1"></i>Đang mở nộp bài
                </span>
            @endif

            @if(isset($mocDeadlines[$mocHienTai]))
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
                <i class="fa-solid fa-circle-info me-1"></i>Yêu cầu & Quy định mốc này:
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

        @if($bcHienTai && $bcHienTai->TrangThai === 'Chờ duyệt')
            <!-- Thông báo khi bài đang chờ duyệt -->
            <div class="alert alert-info border-info-subtle d-flex align-items-center gap-3 mb-0">
                <i class="fa-solid fa-circle-notch fa-spin fa-2x text-info"></i>
                <div>
                    <div class="fw-bold">Bài nộp Mốc {{ $mocHienTai }} đang trong quá trình đánh giá!</div>
                    <div class="small text-muted">
                        Bạn đã nộp thành công bài báo cáo vào ngày {{ \Carbon\Carbon::parse($bcHienTai->NgayNop)->format('d/m/Y') }}. 
                        Giảng viên hướng dẫn sẽ xem xét và phản hồi sớm nhất. Vui lòng theo dõi trạng thái ở bảng Lịch sử bên dưới.
                    </div>
                </div>
            </div>
        @else
            <!-- Form Nộp Báo Cáo (Cho mốc mới hoặc mốc cần nộp lại) -->
            @if($bcHienTai && $bcHienTai->TrangThai === 'Yêu cầu nộp lại')
                <div class="alert alert-warning border-warning-subtle mb-3">
                    <div class="fw-bold text-dark"><i class="fa-solid fa-comment-dots text-warning me-1"></i>Nhận xét yêu cầu nộp lại từ GVHD:</div>
                    <div class="small text-dark mt-1"><em>"{{ $bcHienTai->NhanXet }}"</em></div>
                </div>
            @endif

            <form action="{{ route('sinhvien.baocao.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="LanBaoCao" value="{{ $mocHienTai }}">

                <div class="row g-3 mb-3">
                    <!-- Tiêu đề báo cáo -->
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-dark">
                            Tiêu đề báo cáo <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="TieuDe" class="form-control form-control-sm" 
                               value="{{ $bcHienTai?->TieuDe ?? ('Báo cáo tiến độ ' . $mocs[$mocHienTai]['ten']) }}" required>
                    </div>

                    <!-- Tệp báo cáo PDF nếu loại mốc yêu cầu -->
                    @if(in_array($mocs[$mocHienTai]['loai'], ['pdf', 'pdf_git']))
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">
                            Tệp tài liệu báo cáo (PDF) <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="FileBaoCao" class="form-control form-control-sm" accept=".pdf" required>
                        <div class="form-text" style="font-size: 0.72rem;">Định dạng: .pdf (Dung lượng tối đa 20MB)</div>
                    </div>
                    @endif

                    <!-- Link Git nếu loại mốc yêu cầu -->
                    @if(in_array($mocs[$mocHienTai]['loai'], ['git', 'pdf_git']))
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-dark">
                            Link Source Code (GitHub / GitLab) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted"><i class="fa-brands fa-github"></i></span>
                            <input type="url" name="LinkCode" class="form-control form-control-sm" 
                                   placeholder="https://github.com/username/repository" 
                                   value="{{ $bcHienTai?->LinkCode ?? '' }}" required>
                        </div>
                        <div class="form-text" style="font-size: 0.72rem;">Đường dẫn repository công khai hoặc branch đồ án</div>
                    </div>
                    @endif

                    <!-- Ghi chú cho GVHD -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-dark">
                            Ghi chú & Tóm tắt gửi Giảng viên hướng dẫn
                        </label>
                        <textarea name="NoiDungBaoCao" rows="3" class="form-control form-control-sm" 
                                  placeholder="Mô tả tóm tắt các kết quả đạt được, khó khăn hoặc câu hỏi dành cho thầy/cô hướng dẫn...">{{ $bcHienTai?->NoiDungBaoCao ?? '' }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold d-inline-flex align-items-center gap-2"
                            style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%); border: none;">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>{{ $bcHienTai && $bcHienTai->TrangThai === 'Yêu cầu nộp lại' ? 'Nộp Lại Báo Cáo Mốc ' . $mocHienTai : 'Nộp Báo Cáo Mốc ' . $mocHienTai }}</span>
                    </button>
                </div>
            </form>
        @endif
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
                    <th class="ps-4" width="15%">Mốc nộp</th>
                    <th width="12%">Ngày nộp</th>
                    <th width="28%">Tệp đính kèm & Mã nguồn</th>
                    <th width="25%">Đánh giá của GVHD</th>
                    <th width="10%">Tóm tắt AI</th>
                    <th width="10%" class="text-end pe-4">Trạng thái</th>
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
                        @if($bc->tomTatBaoCao)
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 small d-inline-flex align-items-center gap-1"
                                    data-bs-toggle="modal" data-bs-target="#modalAi{{ $bc->MaBaoCao }}">
                                <i class="fa-solid fa-wand-magic-sparkles text-warning"></i>
                                <span>Xem AI</span>
                            </button>
                        @else
                            <span class="text-muted small">Đang tạo...</span>
                        @endif
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
