@extends('layouts.sinhvien')

@section('page_title', 'Chi Tiết Đợt Báo Cáo — ' . $dotNop->TenDotNop)

@section('content')
<div class="container-fluid px-2 px-md-4 py-3">

    {{-- HEADER ĐIỀU HƯỚNG & THÔNG TIN ĐỢT NỘP --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4 transition-all hover-shadow" style="border: 1px solid #e2e8f0 !important;">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 d-inline-flex align-items-center transition-all hover-scale">
                    <i class="fa-solid fa-arrow-left me-2"></i>Về danh sách tiến độ
                </a>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold">
                            {{ $dotNop->giaiDoan->TenGiaiDoan ?? 'Giai đoạn khóa luận' }}
                        </span>
                        <h5 class="fw-bold text-dark mb-0">
                            {{ $dotNop->TenDotNop }}
                        </h5>
                        @php $tt = $dotNop->getTrangThaiHienThi(); @endphp
                        <span class="badge {{ $tt['badge'] }} rounded-pill px-3 py-1.5 fw-bold">
                            {{ $tt['label'] }}
                        </span>
                    </div>
                    <div class="text-muted small mt-1 py-1">
                        <i class="fa-solid fa-chalkboard-user text-primary me-2"></i>GVHD: <strong>{{ $dotNop->deTai->giangVien?->HoTen ?? 'Chưa rõ' }}</strong> • 
                        <i class="fa-regular fa-clock text-danger ms-2 me-2"></i>Hạn nộp: 
                        <strong class="text-danger">{{ $dotNop->HanNop ? \Carbon\Carbon::parse($dotNop->HanNop)->format('H:i d/m/Y') : 'Không giới hạn' }}</strong>
                    </div>
                </div>
            </div>

            <div>
                <span class="badge bg-light text-secondary border rounded-pill px-3 py-2 small shadow-sm">
                    <i class="fa-solid fa-code-branch me-2 text-primary"></i>Đã nộp {{ $phienBans->count() }} phiên bản
                </span>
            </div>
        </div>
    </div>

    @if($dotNop->NoiDungYeuCau)
        <div class="alert alert-info bg-info bg-opacity-10 border border-info border-opacity-25 rounded-4 p-4 mb-4 small text-dark shadow-sm transition-all">
            <div class="fw-bold mb-2 d-flex align-items-center gap-2 text-primary fs-6">
                <i class="fa-solid fa-circle-info me-1"></i>
                <span>Yêu Cầu Từ Giảng Viên Hướng Dẫn:</span>
            </div>
            <div class="whitespace-pre-line ps-1" style="line-height: 1.6;">{{ $dotNop->NoiDungYeuCau }}</div>
        </div>
    @endif

    {{-- BỐ CỤC 2 CỘT: XEM CÁC PHIÊN BẢN (TRÁI) & BIỂU MẪU NỘP BÁO CÁO (PHẢI) --}}
    <div class="row g-4 align-items-start">
        
        {{-- CỘT TRÁI: DANH SÁCH & NỘI DUNG CÁC PHIÊN BẢN ĐÃ NỘP --}}
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden" style="border: 1px solid #e2e8f0 !important;">
                
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold text-dark text-uppercase small tracking-wide">
                            <i class="fa-solid fa-timeline text-primary me-2"></i>Lịch Sử Các Phiên Bản Nộp
                        </span>
                        <span class="text-muted small" style="font-size: 11px;">Mỗi lần nộp lưu thành một phiên bản riêng biệt</span>
                    </div>

                    @if($phienBans->isNotEmpty())
                        <ul class="nav nav-pills gap-2 pt-1" id="pills-tab-sv" role="tablist">
                            @foreach($phienBans as $index => $pb)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $loop->first ? 'active' : '' }} rounded-pill py-2 px-3.5 small fw-bold transition-all" 
                                            id="pills-sv-v{{ $pb->LanNop }}-tab" 
                                            data-bs-toggle="pill" 
                                            data-bs-target="#pills-sv-v{{ $pb->LanNop }}" 
                                            type="button" role="tab">
                                        Phiên bản v{{ $pb->LanNop }}
                                        @if($pb->TrangThai === 'Đạt')
                                            <i class="fa-solid fa-check text-success ms-1"></i>
                                        @elseif($pb->TrangThai === 'Yêu cầu nộp lại')
                                            <i class="fa-solid fa-rotate-left text-warning ms-1"></i>
                                        @else
                                            <i class="fa-solid fa-hourglass-half ms-1"></i>
                                        @endif
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="card-body p-4 p-md-4">
                    @if($phienBans->isEmpty())
                        <div class="text-center py-5 text-muted px-3">
                            <i class="fa-regular fa-folder-open fs-1 mb-3 d-block opacity-50"></i>
                            <span class="d-block mb-1 fw-medium text-dark">Chưa có bài nộp nào</span>
                            <span class="small">Nhóm của bạn chưa nộp báo cáo cho đợt này. Hãy hoàn thiện nội dung và nộp phiên bản đầu tiên ở form bên phải.</span>
                        </div>
                    @else
                        <div class="tab-content" id="pills-tabContent-sv">
                            @foreach($phienBans as $index => $pb)
                                @php
                                    $aiJson = $pb->TomTatJson ?? [];
                                    $congViec = $aiJson['cong_viec_hoan_thanh'] ?? ($aiJson['data']['completed_work'] ?? []);
                                    $khoKhan = $aiJson['kho_khan'] ?? ($aiJson['data']['difficulties'] ?? []);
                                    $keHoach = $aiJson['ke_hoach_tiep_theo'] ?? ($aiJson['data']['next_steps'] ?? []);
                                    $canhBao = $aiJson['canh_bao'] ?? [];
                                @endphp

                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="pills-sv-v{{ $pb->LanNop }}" role="tabpanel">
                                    
                                    {{-- Thông tin phiên bản --}}
                                    <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                                        <div>
                                            <div class="fw-bold text-dark fs-5 mb-1">{{ $pb->TieuDe }}</div>
                                            <div class="text-muted small">
                                                <i class="fa-regular fa-clock me-1 text-primary"></i>Nộp lúc: <strong>{{ \Carbon\Carbon::parse($pb->NgayNop)->format('H:i d/m/Y') }}</strong> •
                                                Tự đánh giá: <strong class="text-primary">{{ $pb->PhanTramHoanThanh }}%</strong>
                                            </div>
                                        </div>
                                        <div>
                                            @if($pb->TrangThai === 'Đạt')
                                                <span class="badge bg-success rounded-pill px-3 py-2 fw-bold shadow-sm">✓ GVHD đã duyệt Đạt</span>
                                            @elseif($pb->TrangThai === 'Yêu cầu nộp lại')
                                                <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold shadow-sm">GVHD yêu cầu nộp lại</span>
                                            @else
                                                <span class="badge bg-info text-dark rounded-pill px-3 py-2 fw-bold shadow-sm">Đang chờ GVHD nhận xét</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- File đính kèm & Git repository --}}
                                    <div class="row g-3 mb-4">
                                        <div class="col-12 col-md-6">
                                            <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between transition-all hover-shadow-sm">
                                                <div class="d-flex align-items-center gap-3 text-truncate me-2">
                                                    <i class="fa-solid fa-file-pdf text-danger fs-3"></i>
                                                    <div class="text-truncate">
                                                        <div class="fw-bold text-dark text-truncate small">{{ $pb->TenFile ?? 'baocao.pdf' }}</div>
                                                        <span class="text-muted small" style="font-size: 11px;">Tệp PDF đã nộp</span>
                                                    </div>
                                                </div>
                                                @if($pb->DuongDanFile)
                                                    <a href="{{ asset('storage/' . $pb->DuongDanFile) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 transition-all">
                                                        <i class="fa-solid fa-eye me-1"></i>Xem
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between transition-all hover-shadow-sm">
                                                <div class="d-flex align-items-center gap-3 text-truncate me-2">
                                                    <i class="fa-brands fa-github text-dark fs-3"></i>
                                                    <div class="text-truncate">
                                                        <div class="fw-bold text-dark text-truncate small">Mã Nguồn Git</div>
                                                        <span class="text-muted small" style="font-size: 11px;">Repository liên kết</span>
                                                    </div>
                                                </div>
                                                @if($pb->LinkCode)
                                                    <a href="{{ $pb->LinkCode }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1.5 transition-all">
                                                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Mở
                                                    </a>
                                                @else
                                                    <span class="text-muted small fst-italic">Không có link</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Nhận xét của Giảng viên hướng dẫn --}}
                                    @if($pb->NhanXetGV)
                                        <div class="p-3.5 rounded-3 border border-warning bg-warning bg-opacity-10 mb-4 shadow-sm">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold text-dark small">
                                                    <i class="fa-solid fa-comment-dots text-warning me-2"></i>Nhận Xét Của GVHD ({{ $pb->NguoiNhanXet }})[cite: 1]:
                                                </span>
                                                <span class="text-muted small" style="font-size: 11px;">
                                                    {{ \Carbon\Carbon::parse($pb->NgayNhanXet)->format('H:i d/m/Y') }}
                                                </span>
                                            </div>
                                            <div class="text-dark small whitespace-pre-line" style="line-height: 1.6;">{{ $pb->NhanXetGV }}</div>
                                        </div>
                                    @endif

                                    {{-- KẾT QUẢ THẨM ĐỊNH AI 11 MỤC --}}
                                    <div class="mb-4">
                                        @include('partials.ai_summary_11_sections', ['pb' => $pb])
                                    </div>

                                    {{-- Ghi chú bổ sung của sinh viên (nếu có nhập riêng) --}}
                                    @if(!empty($pb->NoiDungBaoCao) && $pb->NoiDungBaoCao !== $pb->TomTatAI)
                                        <div class="accordion mb-3 shadow-sm rounded-3 overflow-hidden" id="accStudentNote-{{ $pb->LanNop }}">
                                            <div class="accordion-item border-0">
                                                <h2 class="accordion-header" id="headingNote-{{ $pb->LanNop }}">
                                                    <button class="accordion-button collapsed py-2.5 px-3 bg-light fw-bold text-dark small transition-all" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNote-{{ $pb->LanNop }}">
                                                        <i class="fa-solid fa-note-sticky text-primary me-2"></i>Ghi Chú Kèm Theo Của Sinh Viên
                                                    </button>
                                                </h2>
                                                <div id="collapseNote-{{ $pb->LanNop }}" class="accordion-collapse collapse">
                                                    <div class="accordion-body p-3.5 small text-dark bg-white" style="font-size: 13px; line-height: 1.65;">
                                                        {!! \App\Helpers\HtmlHelper::clean($pb->NoiDungBaoCao) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- CỘT PHẢI: BIỂU MẪU NỘP BÁO CÁO (TẠO PHIÊN BẢN MỚI) --}}
        <div class="col-12 col-lg-5">
            @php
                $canSubmit = $dotNop->isMoNop() || ($latestPhienBan && ($latestPhienBan->TrangThai === 'Yêu cầu nộp lại' || $latestPhienBan->TrangThai === 'Cần bổ sung'));
                $nextVersionNumber = ($latestPhienBan ? $latestPhienBan->LanNop : 0) + 1;
            @endphp

            @if($latestPhienBan && $latestPhienBan->TrangThai === 'Đạt')
                <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center transition-all" style="border: 1px solid #e2e8f0 !important;">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success p-4 mb-3 shadow-sm" style="width: 72px; height: 72px;">
                        <i class="fa-solid fa-circle-check fa-2x"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Đợt Báo Cáo Đã Hoàn Thành</h5>
                    <p class="text-muted small mb-0 px-2" style="line-height: 1.6;">Giảng viên hướng dẫn đã đánh giá Đạt cho phiên bản v{{ $latestPhienBan->LanNop }}. Bạn không cần nộp thêm cho đợt này.</p>
                </div>
            @elseif(!$canSubmit)
                <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center transition-all" style="border: 1px solid #e2e8f0 !important;">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary bg-opacity-10 text-secondary p-4 mb-3 shadow-sm" style="width: 72px; height: 72px;">
                        <i class="fa-solid fa-lock fa-2x"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Đợt Nộp Đang Đóng</h5>
                    <p class="text-muted small mb-0 px-2" style="line-height: 1.6;">Thời hạn nộp bài đã kết thúc hoặc đợt nộp đã được Giảng viên hướng dẫn tạm khóa.</p>
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-4 sticky-under-header transition-all" style="border: 1px solid #e2e8f0 !important;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="fa-solid fa-file-arrow-up text-primary me-2"></i>Nộp Báo Cáo Phiên Bản v{{ $nextVersionNumber }}
                        </h6>
                        <span class="badge bg-primary rounded-pill px-3 py-1.5 shadow-sm">v{{ $nextVersionNumber }}</span>
                    </div>
                    <span class="text-muted small mb-3.5 d-block">
                        {{ $latestPhienBan ? 'Nộp lại theo yêu cầu chỉnh sửa của Giảng viên hướng dẫn' : 'Nộp bài báo cáo lần đầu cho đợt này' }}
                    </span>

                    {{-- Thông báo nghiệp vụ: AI tự động phân tích không cần nhập thủ công --}}
                    <div class="alert alert-primary bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3 p-3.5 mb-4 small text-dark shadow-sm">
                        <div class="fw-bold text-primary mb-1.5 d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Tự Động Phân Tích Bằng AI:
                        </div>
                        <div style="font-size: 12px; line-height: 1.6;">
                            Hệ thống tự động sử dụng AI đọc hiểu tài liệu PDF, phân tích đầy đủ 11 mục chuyên môn (công việc hoàn thành, kết quả, mã nguồn, khó khăn, kế hoạch). 
                            <strong class="text-primary">Sinh viên không cần phải nhập thủ công lại các thông tin đã có trong file PDF.</strong>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('sinhvien.baocao.dot_nop.nop', $dotNop->MaDotNop) }}" enctype="multipart/form-data">
                        @csrf

                        {{-- Tệp PDF Báo Cáo --}}
                        <div class="mb-3.5">
                            <label class="form-label fw-bold small text-dark">
                                Tệp báo cáo PDF @if(!$latestPhienBan)<span class="text-danger">*</span>@endif
                            </label>
                            <input type="file" name="FilePdf" class="form-control form-control-sm py-2 px-3 rounded-3 transition-all" accept=".pdf" {{ $latestPhienBan ? '' : 'required' }}>
                            <div class="form-text small mt-1" style="font-size: 11px;">
                                @if($latestPhienBan)
                                    Đã có file PDF từ phiên bản trước. Chỉ cần tải lên nếu bạn có bản cập nhật mới.
                                @else
                                    Tệp PDF báo cáo tiến độ (tối đa 25MB). AI sẽ tự động đọc và lập tóm tắt.
                                @endif
                            </div>
                        </div>

                        {{-- Đường dẫn mã nguồn (Git) --}}
                        <div class="mb-3.5">
                            <label class="form-label fw-bold small text-dark">
                                Đường dẫn mã nguồn (GitHub / GitLab / Bitbucket) 
                                @if($dotNop->YeuCauSourceCode)<span class="text-danger">*</span>@else<span class="text-muted fw-normal">(tùy chọn)</span>@endif
                            </label>
                            <input type="url" name="LinkCode" class="form-control form-control-sm py-2 px-3 rounded-3 transition-all" 
                                   placeholder="https://github.com/username/repository" 
                                   value="{{ old('LinkCode', $latestPhienBan->LinkCode ?? '') }}"
                                   {{ $dotNop->YeuCauSourceCode ? 'required' : '' }}>
                            <div class="form-text small mt-1" style="font-size: 11px;">Đường dẫn repository mã nguồn của nhóm tại thời điểm nộp phiên bản này.</div>
                        </div>

                        {{-- Ghi chú chi tiết cho GVHD (Trình soạn thảo văn bản có định dạng) --}}
                        <div class="mb-3.5">
                            @include('partials.rich_text_editor', [
                                'name'         => 'NoiDungBaoCao',
                                'id'           => 'NoiDungBaoCao',
                                'value'        => old('NoiDungBaoCao', ''),
                                'label'        => 'Ghi chú & mô tả chi tiết gửi GVHD',
                                'placeholder'  => 'Nhập lời nhắn, mô tả chi tiết tiến độ hoặc các điểm lưu ý gửi GVHD (có thể định dạng in đậm, danh sách gạch đầu dòng)...',
                                'required'     => false,
                                'minHeight'    => '140px',
                                'compact'      => true,
                                'showTemplate' => false,
                                'helpText'     => 'Không bắt buộc. Nếu để trống, hệ thống sẽ tự động dùng bản tóm tắt trích xuất từ AI.',
                            ])
                        </div>

                        {{-- Tỷ lệ tự đánh giá (%) --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-dark">
                                Tỷ lệ hoàn thành tự đánh giá (%) <span class="text-muted fw-normal">(tùy chọn)</span>
                            </label>
                            <div class="input-group input-group-sm shadow-sm rounded-3 overflow-hidden">
                                <input type="number" name="PhanTramHoanThanh" class="form-control form-control-sm py-2 px-3" min="0" max="100" placeholder="Để trống để AI tự ước lượng theo tài liệu" value="{{ old('PhanTramHoanThanh') }}">
                                <span class="input-group-text bg-light px-3 fw-bold">%</span>
                            </div>
                            <div class="form-text small mt-1" style="font-size: 11px;">Nếu để trống, Trợ lý AI sẽ tự động ước tính dựa trên khối lượng công việc và số trang báo cáo.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2.5 fw-bold shadow-sm d-flex align-items-center justify-content-center transition-all hover-scale">
                            <i class="fa-solid fa-cloud-arrow-up me-2 fs-5"></i>Gửi Nộp &amp; Kích Hoạt AI Thẩm Định
                        </button>
                    </form>
                </div>
            @endif
        </div>

    </div>

</div>

{{-- Bổ sung một vài hiệu ứng CSS nhẹ nhàng, hiện đại --}}
<style>
    .transition-all {
        transition: all 0.3s ease-in-out;
    }
    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08) !important;
    }
    .hover-shadow-sm:hover {
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05) !important;
        background-color: #f8fafc !important;
    }
    .hover-scale:hover {
        transform: translateY(-1px);
    }
    .tracking-wide {
        letter-spacing: 0.5px;
    }
</style>
@endsection