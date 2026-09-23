@extends('layouts.admin')

@section('page_title', 'Upload Văn Bản Thông Báo Tự Động Trích Xuất Kế Hoạch')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.kehoach.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách kế hoạch
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="admin-table-card">
    <div class="admin-table-header">
        <h5 class="admin-table-header-title">
            <i class="fa-solid fa-file-pdf text-danger"></i>
            Lập Kế Hoạch Tự Động Từ File Thông Báo Chính Thức (PDF / Word)
        </h5>
    </div>
    <div class="card-body p-4">
        <!-- 4-Step Wizard Progress -->
        <div class="mb-4 p-3 bg-light rounded-3 border">
            <div class="row text-center small fw-bold text-muted g-2">
                <div class="col-md-3 text-primary"><i class="fa-solid fa-circle-1 me-1"></i>1. Upload Văn bản</div>
                <div class="col-md-3"><i class="fa-solid fa-circle-2 me-1"></i>2. Phân tích AI/OCR</div>
                <div class="col-md-3"><i class="fa-solid fa-circle-3 me-1"></i>3. Preview Đối chiếu</div>
                <div class="col-md-3"><i class="fa-solid fa-circle-4 me-1"></i>4. Xác nhận &amp; Sinh lịch</div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.kehoach.processParse') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="row justify-content-center my-4">
                <div class="col-md-8 text-center">
                    <div class="p-5 border border-2 border-dashed rounded-4 bg-light shadow-sm">
                        <i class="fa-solid fa-cloud-arrow-up fs-1 text-primary mb-3"></i>
                        <h5 class="fw-bold text-dark mb-2">Kéo thả hoặc Chọn File Văn Bản Thông Báo Khóa Luận</h5>
                        <p class="text-muted small mb-4">Hỗ trợ định dạng <strong>.PDF</strong> hoặc <strong>.DOCX</strong> (Dung lượng tối đa 10MB). Hệ thống sẽ tự động đọc bảng mốc thời gian, học kỳ và mã học phần.</p>
                        
                        <input type="file" name="document_file" id="document_file" class="form-control form-control-lg d-none" accept=".pdf,.docx" onchange="updateFileName(this)" required>
                        <button type="button" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm" onclick="document.getElementById('document_file').click()">
                            <i class="fa-solid fa-file-import me-2"></i>Chọn File Văn Bản
                        </button>
                        
                        <div id="file-name-display" class="mt-3 fw-bold text-success fs-6 d-none"></div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info border-0 shadow-sm p-3 rounded-3">
                <h6 class="fw-bold"><i class="fa-solid fa-lightbulb me-2"></i> Hướng Dẫn Nghiệp Vụ:</h6>
                <ul class="mb-0 small text-secondary">
                    <li>Khoa <strong>không cần nhập thủ công từng mốc thời gian</strong>. Chỉ cần upload file thông báo của Khoa (chứa danh sách quy trình, ngày bắt đầu, deadline).</li>
                    <li>Sau khi upload, hệ thống sẽ <strong>đọc dữ liệu và hiển thị Preview</strong> để Khoa kiểm tra lại trước khi cập nhật CSDL.</li>
                    <li>Khi xác nhận, hệ thống sẽ tự động cập nhật Database, sinh Lịch Quy Trình, cài đặt nhắc nhở Deadline và <strong>tự động gửi Thông Báo có đính kèm file gốc</strong> tới tất cả Sinh viên/Giảng viên thuộc phạm vi.</li>
                </ul>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="submit" class="btn btn-success rounded-pill px-5 font-weight-bold shadow-sm">
                    <i class="fa-solid fa-microchip me-1"></i> Bắt Đầu Phân Tích & Preview
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function updateFileName(input) {
        if (input.files && input.files[0]) {
            const display = document.getElementById('file-name-display');
            display.innerText = '📄 Đã chọn file: ' + input.files[0].name + ' (' + (input.files[0].size / 1024 / 1024).toFixed(2) + ' MB)';
            display.classList.remove('d-none');
        }
    }
</script>
@endpush
@endsection
