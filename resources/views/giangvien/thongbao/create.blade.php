@extends('layouts.giangvien')

@section('page_title', 'Tạo Thông Báo Cho Nhóm Hướng Dẫn')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-3">
        <a href="{{ route('giangvien.thongbao.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách thông báo
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <i class="fa-solid fa-circle-exclamation me-2"></i><strong>Vui lòng kiểm tra lại:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card card-premium shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title fw-bold text-primary-custom mb-0">
                <i class="fa-solid fa-paper-plane text-primary me-2"></i>Soạn Thông Báo Gửi Đến Nhóm Sinh Viên
            </h5>
            <small class="text-muted">Thông báo sẽ được gửi tức thì đến các thành viên trong nhóm và hiển thị trên bảng tin của sinh viên.</small>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('giangvien.thongbao.store') }}" enctype="multipart/form-data">
                @csrf
                
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Chọn Nhóm Nhận Thông Báo <span class="text-danger">*</span></label>
                        <select name="DoiTuong" class="form-select select2-enable" required>
                            @if($nhoms->isNotEmpty())
                                <option value="ALL" {{ old('DoiTuong') === 'ALL' ? 'selected' : '' }}>
                                    📢 [TẤT CẢ] Gửi đồng loạt cho toàn bộ {{ $nhoms->count() }} nhóm đang hướng dẫn
                                </option>
                                <optgroup label="── Gửi riêng cho từng nhóm cụ thể ──">
                                    @foreach($nhoms as $nhom)
                                        <option value="{{ $nhom->MaNhom }}" {{ old('DoiTuong') === $nhom->MaNhom ? 'selected' : '' }}>
                                            Nhóm {{ $nhom->MaNhom }} - Đề tài: {{ \Illuminate\Support\Str::limit($nhom->deTai->TenDeTai ?? 'Chưa gán đề tài', 45) }} (TN: {{ $nhom->truongNhom->HoTen ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @else
                                <option value="ALL">Tất cả các nhóm hướng dẫn (Hiện chưa có nhóm nào được phân công)</option>
                            @endif
                        </select>
                        <div class="form-text">Bạn có thể chọn gửi thông báo chung cho tất cả các nhóm hoặc gửi riêng cho một nhóm cụ thể.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Loại Thông Báo <span class="text-danger">*</span></label>
                        <select name="LoaiThongBao" class="form-select" required>
                            <option value="Nhắc nhở" {{ old('LoaiThongBao') === 'Nhắc nhở' ? 'selected' : '' }}>⏰ Nhắc nhở nộp tiến độ</option>
                            <option value="Lịch hẹn" {{ old('LoaiThongBao') === 'Lịch hẹn' ? 'selected' : '' }}>📅 Lịch hẹn gặp / Họp hướng dẫn</option>
                            <option value="Góp ý đề tài" {{ old('LoaiThongBao') === 'Góp ý đề tài' ? 'selected' : '' }}>📝 Góp ý chỉnh sửa đề cương / đồ án</option>
                            <option value="Khác" {{ old('LoaiThongBao') === 'Khác' ? 'selected' : '' }}>📌 Thông báo chung khác</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Tiêu Đề Thông Báo <span class="text-danger">*</span></label>
                    <input type="text" name="TieuDe" class="form-control form-control-lg fs-6" 
                           placeholder="Ví dụ: Nhắc nhở hạn nộp Báo cáo tiến độ Lần 1 vào ngày 15/10..." 
                           value="{{ old('TieuDe') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Nội Dung Chi Tiết <span class="text-danger">*</span></label>
                    <textarea name="NoiDung" class="form-control" rows="6" 
                              placeholder="Nhập nội dung nhắc nhở, hướng dẫn, đường link họp online Google Meet/Zoom, yêu cầu nộp file..." required>{{ old('NoiDung') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold"><i class="fa-solid fa-paperclip me-1"></i>Tệp Đính Kèm (Nếu có)</label>
                    <input type="file" name="FileDinhKem" class="form-control" accept=".pdf,.doc,.docx,.zip,.rar,.png,.jpg,.jpeg">
                    <div class="form-text">Hỗ trợ các định dạng PDF, DOCX, ZIP, RAR, PNG, JPG (Tối đa 10MB).</div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('giangvien.thongbao.index') }}" class="btn btn-light border px-4 rounded-pill">
                        Hủy
                    </a>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm fw-semibold">
                        <i class="fa-solid fa-paper-plane me-2"></i>Phát Hành Thông Báo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
