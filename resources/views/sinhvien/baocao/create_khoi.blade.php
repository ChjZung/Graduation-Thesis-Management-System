@extends('layouts.sinhvien')

@section('page_title', 'Thêm Báo Cáo Tiến Độ Nhỏ - Giai Đoạn ' . $lan)

@section('content')
<div class="container-fluid px-2 px-md-4 py-3">
    <!-- Breadcrumb & Tiêu đề -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2" style="font-size: 13px;">
                    <li class="breadcrumb-item"><a href="{{ route('sinhvien.baocao.index') }}" class="text-decoration-none text-muted transition-all hover-primary">Báo Cáo Tiến Độ</a></li>
                    <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Giai Đoạn {{ $lan }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-file-circle-plus text-primary me-2"></i>Tạo Báo Cáo Nhỏ (Đợt {{ $lanNop }}) — Giai Đoạn {{ $lan }}
            </h4>
        </div>
        <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-outline-secondary rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center transition-all hover-scale" style="font-size: 13px;">
            <i class="fa-solid fa-arrow-left me-2"></i>Quay lại danh sách
        </a>
    </div>

    <!-- Thông tin đề tài & Giai đoạn -->
    <div class="card border-0 shadow-sm mb-4 transition-all hover-shadow" style="background: linear-gradient(135deg, #f0f7ff 0%, #e6f0fa 100%); border-left: 5px solid var(--huit-blue, #002244) !important; border-radius: 12px;">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-md-8">
                    <span class="badge bg-primary rounded-pill px-3 py-1.5 mb-2 shadow-xs" style="font-size: 12px;">Giai Đoạn {{ $lan }}/6: {{$mocInfo['ten'] }}</span>
                    <div class="fw-bold text-dark fs-5 mb-1">{{ $nhom->deTai->TenDeTai }}</div>
                    <div class="text-muted" style="font-size: 13px;">
                        <span><i class="fa-solid fa-chalkboard-user me-2 text-primary"></i>GVHD: <strong>{{ $nhom->deTai->giangVien->HoTen ?? 'Chưa phân công' }}</strong></span>
                        <span class="ms-3"><i class="fa-solid fa-users me-2 text-primary"></i>Nhóm: <strong>{{ $nhom->TenNhom }}</strong></span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                    <div class="text-secondary p-2 rounded-3 bg-white bg-opacity-60 border" style="font-size: 12.5px; line-height: 1.5;">{{ $mocInfo['mo_ta'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- KHỐI 1: TRỢ LÝ AI MAP-REDUCE PHÂN TÍCH TÀI LIỆU PDF -->
    <div class="card mb-4 border border-primary border-opacity-25 shadow-sm rounded-4 overflow-hidden transition-all hover-shadow" style="border-radius: 14px;">
        <div class="card-header bg-white py-3.5 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-primary text-white p-2.5 rounded-circle shadow-xs">
                    <i class="fa-solid fa-wand-magic-sparkles fs-6"></i>
                </span>
                <div>
                    <h6 class="fw-bold text-dark mb-1">Trợ Lý AI HUIT — Phân Tích &amp; Tóm Tắt PDF Tự Động (Map-Reduce)</h6>
                    <small class="text-muted" style="font-size: 12px;">Hỗ trợ đọc tài liệu từ ngắn đến hàng trăm trang, trích xuất chính xác công việc, khó khăn, kế hoạch kèm số trang dẫn chứng.</small>
                </div>
            </div>
            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1.5 shadow-xs" style="font-size: 12px;">
                <i class="fa-solid fa-bolt me-1.5"></i>Map-Reduce Pipeline
            </span>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label fw-bold text-dark mb-2" style="font-size: 13px;">
                        Chọn tệp báo cáo PDF để AI phân tích:
                    </label>
                    <div class="input-group shadow-xs rounded-3 overflow-hidden">
                        <input type="file" id="aiPdfFile" class="form-control py-2 px-3" accept=".pdf">
                        <button type="button" id="btnRunAi" class="btn btn-primary fw-bold px-4 d-inline-flex align-items-center transition-all hover-scale" onclick="runAiExtraction()">
                            <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Trích Xuất Bằng AI
                        </button>
                    </div>
                    <div class="form-text text-muted mt-1.5" style="font-size: 12px;">
                        Tệp PDF báo cáo tuần, tài liệu thiết kế hoặc dự thảo tiến độ (tối đa 25MB).
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="text-muted d-inline-block p-2" style="font-size: 12px;">
                        <i class="fa-solid fa-shield-halved text-primary me-2"></i>Bảo tồn số trang trích dẫn &amp; bằng chứng học thuật
                    </span>
                </div>
            </div>

            <!-- AI Progress / Spinner Tracker -->
            <div id="aiLoadingSection" class="mt-4 p-4 rounded-3 bg-light border text-center d-none shadow-xs">
                <div class="spinner-border text-primary mb-3" style="width: 2.5rem; height: 2.5rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6 class="fw-bold text-primary mb-1" id="aiLoadingTitle">Đang xử lý tài liệu PDF với AI Map-Reduce...</h6>
                <p class="text-muted mb-0" id="aiLoadingDesc" style="font-size: 13px;">Trích xuất văn bản từng trang → Chạy Map chunking → Hợp nhất Reduce dữ liệu...</p>
            </div>

            <!-- AI Extracted Result Preview Card -->
            <div id="aiResultCard" class="mt-4 p-4 rounded-3 bg-light border border-primary border-opacity-50 d-none shadow-sm">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-primary text-white rounded-pill px-3 py-1.5 shadow-xs">
                            <i class="fa-solid fa-check me-2"></i>Phân tích AI thành công
                        </span>
                        <span class="text-muted" style="font-size: 12.5px;">
                            Số trang: <strong id="resTotalPages" class="text-dark">0</strong> trang | Thời gian: <strong id="resTime" class="text-dark">0</strong>s
                        </span>
                    </div>
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3.5 py-2 fw-bold shadow-xs d-inline-flex align-items-center transition-all hover-scale" onclick="applyAiToForm()">
                        <i class="fa-solid fa-arrow-down me-2"></i>1-Click Áp Dụng Vào Biểu Mẫu Dưới
                    </button>
                </div>

                <!-- Tóm tắt tổng quan -->
                <div class="alert alert-info p-3 mb-3 border-0 rounded-3 shadow-xs" style="font-size: 13px; line-height: 1.6;">
                    <strong><i class="fa-solid fa-circle-info me-2"></i>Tóm tắt tổng quan:</strong>
                    <span id="resOverallSummary" class="text-dark"></span>
                </div>

                <!-- Danh sách công việc trích xuất -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3.5 bg-white rounded-3 border h-100 shadow-xs">
                            <h6 class="fw-bold text-primary mb-2.5" style="font-size: 13px;">
                                <i class="fa-solid fa-circle-check me-2"></i>Công việc đã hoàn thành:
                            </h6>
                            <div id="resCompletedList" class="small text-secondary" style="max-height: 200px; overflow-y: auto;"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3.5 bg-white rounded-3 border h-100 shadow-xs">
                            <h6 class="fw-bold text-warning-emphasis mb-2.5" style="font-size: 13px;">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>Khó khăn &amp; Hướng giải quyết:
                            </h6>
                            <div id="resDifficultiesList" class="small text-secondary" style="max-height: 200px; overflow-y: auto;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KHỐI 2: FORM NỘP BÁO CÁO NHỎ CHÍNH THỨC -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden transition-all hover-shadow" style="border: 1px solid #e2e8f0 !important; border-radius: 14px;">
        <div class="card-header bg-white py-3.5 px-4 border-bottom">
            <h6 class="fw-bold text-dark mb-0">
                <i class="fa-solid fa-file-pen text-primary me-2"></i>Nội Dung Báo Cáo Tiến Độ Nhỏ (Đợt {{ $lanNop }})
            </h6>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('sinhvien.baocao.store_khoi') }}" method="POST" enctype="multipart/form-data" id="subReportForm">
                @csrf
                <input type="hidden" name="LanBaoCao" value="{{ $lan }}">
                <input type="hidden" name="TomTatAI" id="formTomTatAI" value="">
                <input type="hidden" name="TomTatJson" id="formTomTatJson" value="">
                <input type="hidden" name="ExistingFileUrl" id="formExistingFileUrl" value="">
                <input type="hidden" name="ExistingFileName" id="formExistingFileName" value="">

                <div class="row g-4">
                    <!-- Tiêu đề & % Hoàn thành -->
                    <div class="col-md-8">
                        <label class="form-label fw-bold text-dark mb-2" style="font-size: 13px;">
                            Tiêu đề báo cáo nhỏ <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="TieuDe" id="formTieuDe" class="form-control py-2 px-3 rounded-3 transition-all" 
                               value="Báo cáo tiến độ đợt {{ $lanNop }} - Giai đoạn {{ $lan }}" required>
                        <div class="form-text text-muted mt-1.5" style="font-size: 12px;">Tóm lược nội dung công việc báo cáo của tuần/đợt này.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-dark mb-2" style="font-size: 13px;">
                            % Tiến độ giai đoạn đạt được:
                        </label>
                        <div class="input-group shadow-xs rounded-3 overflow-hidden">
                            <input type="number" name="PhanTramHoanThanh" id="formPhanTram" class="form-control text-center fw-bold py-2" 
                                   min="0" max="100" value="{{ min(100, $lanNop * 25) }}">
                            <span class="input-group-text bg-light fw-bold px-3">%</span>
                        </div>
                    </div>

                    <!-- Kết quả đạt được / Công việc hoàn thành -->
                    <div class="col-12">
                        @include('partials.rich_text_editor', [
                            'name' => 'KetQuaDatDuoc',
                            'id' => 'formKetQua',
                            'label' => '1. Kết quả đạt được & Công việc đã hoàn thành',
                            'required' => true,
                            'minHeight' => '130px',
                            'compact' => true,
                            'placeholder' => 'Mô tả cụ thể các module, tính năng, hoặc tài liệu đã hoàn thiện (kèm số trang dẫn chứng nếu có)...'
                        ])
                    </div>

                    <!-- Khó khăn & Hướng giải quyết -->
                    <div class="col-md-6">
                        @include('partials.rich_text_editor', [
                            'name' => 'KhoKhan',
                            'id' => 'formKhoKhan',
                            'label' => '2. Khó khăn & Rào cản gặp phải',
                            'minHeight' => '110px',
                            'compact' => true,
                            'placeholder' => 'Mô tả các vấn đề kỹ thuật, tích hợp hoặc dữ liệu gặp trở ngại...'
                        ])
                    </div>
                    <div class="col-md-6">
                        @include('partials.rich_text_editor', [
                            'name' => 'HuongGiaiQuyet',
                            'id' => 'formHuongGiaiQuyet',
                            'label' => '3. Hướng giải quyết đề xuất',
                            'minHeight' => '110px',
                            'compact' => true,
                            'placeholder' => 'Phương án khắc phục, tìm giải pháp thay thế hoặc tham khảo ý kiến GVHD...'
                        ])
                    </div>

                    <!-- Kế hoạch tiếp theo -->
                    <div class="col-12">
                        @include('partials.rich_text_editor', [
                            'name' => 'KeHoachTiepTheo',
                            'id' => 'formKeHoach',
                            'label' => '4. Kế hoạch giai đoạn tiếp theo',
                            'minHeight' => '110px',
                            'compact' => true,
                            'placeholder' => 'Các công việc cụ thể nhóm dự kiến triển khai trong tuần tới / đợt tiếp theo...'
                        ])
                    </div>

                    <!-- File đính kèm & Git Link -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark mb-2" style="font-size: 13px;">
                            Tệp đính kèm (PDF)
                        </label>
                        <input type="file" name="FileBaoCao" id="formFileBaoCao" class="form-control py-2 px-3 rounded-3 transition-all" accept=".pdf">
                        <div class="form-text text-muted mt-1.5" id="fileNotice" style="font-size: 12px;">
                            Có thể sử dụng tệp PDF đã phân tích ở trên hoặc tải tệp mới.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark mb-2" style="font-size: 13px;">
                            Đường dẫn Source Code (GitHub / GitLab):
                        </label>
                        <div class="input-group shadow-xs rounded-3 overflow-hidden">
                            <span class="input-group-text bg-light text-muted px-3"><i class="fa-brands fa-github fs-6"></i></span>
                            <input type="url" name="LinkCode" class="form-control py-2 px-3" placeholder="https://github.com/..." 
                                   value="{{ $baoCao?->LinkCode ?? '' }}">
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="d-flex justify-content-end gap-2 pt-4 mt-4 border-top">
                    <a href="{{ route('sinhvien.baocao.index') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2.5 fw-semibold transition-all hover-scale">
                        Hủy
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-xs d-inline-flex align-items-center transition-all hover-scale">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i>Nộp Báo Cáo Nhỏ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Bổ sung lớp CSS tùy chỉnh hiệu ứng & khoảng cách --}}
<style>
    .transition-all {
        transition: all 0.25s ease-in-out;
    }
    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.08) !important;
    }
    .hover-scale:hover {
        transform: translateY(-1px);
    }
    .hover-primary:hover {
        color: var(--bs-primary) !important;
    }
</style>

@push('scripts')
<script>
let currentAiResult = null;

function runAiExtraction() {
    const fileInput = document.getElementById('aiPdfFile');
    if (!fileInput.files || fileInput.files.length === 0) {
        alert('Vui lòng chọn một tệp PDF trước khi bấm phân tích AI!');
        return;
    }

    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('FilePdf', file);
    formData.append('LanBaoCao', '{{ $lan }}');
    formData.append('_token', '{{ csrf_token() }}');

    // UI Feedback
    const btn = document.getElementById('btnRunAi');
    const loading = document.getElementById('aiLoadingSection');
    const resultCard = document.getElementById('aiResultCard');

    btn.disabled = true;
    loading.classList.remove('d-none');
    resultCard.classList.add('d-none');

    fetch('{{ route("sinhvien.baocao.ai_summary") }}', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        btn.disabled = false;
        loading.classList.add('d-none');

        if (res.success && res.data) {
            currentAiResult = res;
            renderAiResult(res);
        } else {
            alert('Lỗi phân tích AI: ' + (res.error || 'Không thể trích xuất nội dung từ PDF này.'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        loading.classList.add('d-none');
        alert('Lỗi kết nối khi gửi yêu cầu phân tích AI: ' + err.message);
    });
}

function renderAiResult(res) {
    const card = document.getElementById('aiResultCard');
    card.classList.remove('d-none');

    document.getElementById('resTotalPages').innerText = res.total_pages || 1;
    document.getElementById('resTime').innerText = res.processing_time || 0;
    document.getElementById('resOverallSummary').innerText = res.data.overall_summary || 'Đã phân tích xong báo cáo.';

    // Completed List
    const compBox = document.getElementById('resCompletedList');
    compBox.innerHTML = '';
    const completedItems = res.data.completed_work || [];
    if (completedItems.length > 0) {
        completedItems.forEach(item => {
            const pages = item.source_pages ? ` <span class="badge bg-secondary-subtle text-secondary border">[Trang ${item.source_pages.join(', ')}]</span>` : '';
            compBox.innerHTML += `<div class="mb-2 pb-1 border-bottom">• <strong>${item.content}</strong>${pages}<br><small class="text-muted"><em>"${item.evidence || ''}"</em></small></div>`;
        });
    } else {
        compBox.innerHTML = '<span class="text-muted">Chưa phát hiện danh mục rõ ràng.</span>';
    }

    // Difficulties List
    const diffBox = document.getElementById('resDifficultiesList');
    diffBox.innerHTML = '';
    const diffItems = res.data.difficulties || [];
    if (diffItems.length > 0) {
        diffItems.forEach(item => {
            const pages = item.source_pages ? ` <span class="badge bg-secondary-subtle text-secondary border">[Trang ${item.source_pages.join(', ')}]</span>` : '';
            diffBox.innerHTML += `<div class="mb-2 pb-1 border-bottom">• <strong>${item.content}</strong>${pages}</div>`;
        });
    } else {
        diffBox.innerHTML = '<span class="text-muted">Không phát hiện khó khăn nổi bật.</span>';
    }
}

function applyAiToForm() {
    if (!currentAiResult) return;

    const formatted = currentAiResult.formatted || {};
    const data = currentAiResult.data || {};

    function updateRichField(id, text) {
        if (!text) return;
        const input = document.getElementById(id);
        if (input) input.value = text;
        const editor = document.getElementById('editor_' + id);
        if (editor) {
            editor.innerHTML = text.includes('<') ? text : '<p>' + text.replace(/\n/g, '<br>') + '</p>';
        }
    }

    if (formatted.completed_work) {
        updateRichField('formKetQua', formatted.completed_work);
    }
    if (formatted.difficulties) {
        updateRichField('formKhoKhan', formatted.difficulties);
    }
    if (formatted.next_steps) {
        updateRichField('formKeHoach', formatted.next_steps);
    }
    if (data.completion_percentage) {
        document.getElementById('formPhanTram').value = data.completion_percentage;
    }

    // Pass temporary file metadata to hidden inputs so user doesn't need to re-upload
    if (currentAiResult.temp_file_url) {
        document.getElementById('formExistingFileUrl').value = currentAiResult.temp_file_url;
        document.getElementById('formExistingFileName').value = currentAiResult.temp_file_name || 'BaoCao.pdf';
        document.getElementById('fileNotice').innerHTML = `<span class="text-primary fw-bold"><i class="fa-solid fa-circle-check me-2"></i>Đã gắn file PDF vừa phân tích: ${currentAiResult.temp_file_name}</span>`;
    }

    // Save JSON & AI Summary
    document.getElementById('formTomTatAI').value = formatted.overall_summary || '';
    document.getElementById('formTomTatJson').value = JSON.stringify(currentAiResult);

    // Scroll smoothly to the form
    document.getElementById('subReportForm').scrollIntoView({ behavior: 'smooth' });
}
</script>
@endpush
@endsection