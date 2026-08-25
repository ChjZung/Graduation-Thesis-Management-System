@extends('layouts.admin')

@section('page_title', 'Tạo Thông Báo Mới')

@section('content')
<div class="mb-3">
    <a href="{{ route('thongbao.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách thông báo
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-premium">
    <div class="card-header-premium">
        <span><i class="fa-solid fa-plus-circle me-2 text-primary"></i> Khởi Tạo Thông Báo Hệ Thống</span>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('thongbao.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Tiêu Đề Thông Báo <span class="text-danger">*</span></label>
                    <input type="text" name="TieuDe" class="form-control" placeholder="Nhập tiêu đề thông báo..." value="{{ old('TieuDe') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Loại Thông Báo <span class="text-danger">*</span></label>
                    <select name="LoaiThongBao" class="form-select" required>
                        <option value="Kế hoạch" {{ old('LoaiThongBao') == 'Kế hoạch' ? 'selected' : '' }}>Kế hoạch</option>
                        <option value="Hệ thống" {{ old('LoaiThongBao') == 'Hệ thống' ? 'selected' : '' }}>Hệ thống</option>
                        <option value="Báo cáo" {{ old('LoaiThongBao') == 'Báo cáo' ? 'selected' : '' }}>Báo cáo</option>
                        <option value="Hội đồng" {{ old('LoaiThongBao') == 'Hội đồng' ? 'selected' : '' }}>Hội đồng</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Đối Tượng Nhận <span class="text-danger">*</span></label>
                    <select name="DoiTuongNhan" class="form-select" required>
                        <option value="Tất cả" {{ old('DoiTuongNhan') == 'Tất cả' ? 'selected' : '' }}>Toàn thể (Sinh viên &amp; Giảng viên)</option>
                        <option value="Sinh viên" {{ old('DoiTuongNhan') == 'Sinh viên' ? 'selected' : '' }}>Chỉ Sinh viên</option>
                        <option value="Giảng viên" {{ old('DoiTuongNhan') == 'Giảng viên' ? 'selected' : '' }}>Chỉ Giảng viên</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Liên Kết Kế Hoạch (Nếu có)</label>
                    <select name="MaKeHoach" class="form-select">
                        <option value="">-- Không liên kết kế hoạch --</option>
                        @foreach($keHoachs as $kh)
                            <option value="{{ $kh->MaKeHoach }}" {{ old('MaKeHoach') == $kh->MaKeHoach ? 'selected' : '' }}>{{ $kh->TenKeHoach }} ({{ $kh->MaHocKy }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Nội Dung Chi Tiết Thông Báo <span class="text-danger">*</span></label>
                <textarea name="NoiDung" class="form-control" rows="6" placeholder="Nhập nội dung chi tiết thông báo..." required>{{ old('NoiDung') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold"><i class="fa-solid fa-paperclip me-1 text-primary"></i> File Văn Bản / Tài Liệu Đính Kèm (PDF / Word / Zip)</label>
                <input type="file" name="FileDinhKem" class="form-control" accept=".pdf,.docx,.doc,.zip,.rar,.png,.jpg,.jpeg">
                <span class="small text-muted">Hỗ trợ các định dạng file PDF, DOCX, ZIP, RAR hoặc Hình ảnh (tối đa 10MB).</span>
            </div>

            <div class="p-3 bg-light rounded-3 border mb-4">
                <h6 class="fw-bold mb-2"><i class="fa-solid fa-clock me-1 text-primary"></i> Tùy Chọn Phát Hành / Lên Lịch</h6>
                <div class="d-flex gap-4 align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="action_type" id="act_send_now" value="send_now" checked onchange="toggleSchedule(false)">
                        <label class="form-check-label fw-bold text-success" for="act_send_now">
                            <i class="fa-solid fa-paper-plane me-1"></i> Phát Hành &amp; Gửi Ngay
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="action_type" id="act_draft" value="draft" onchange="toggleSchedule(false)">
                        <label class="form-check-label fw-bold text-secondary" for="act_draft">
                            <i class="fa-solid fa-file-signature me-1"></i> Lưu Thành Bản Nháp
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="action_type" id="act_schedule" value="schedule" onchange="toggleSchedule(true)">
                        <label class="form-check-label fw-bold text-warning" for="act_schedule">
                            <i class="fa-regular fa-clock me-1"></i> Đặt Lịch Phát Hành
                        </label>
                    </div>
                </div>

                <div id="schedule-input-group" class="mt-3 d-none col-md-4">
                    <label class="form-label small fw-bold">Thời Gian Phát Hành Dự Kiến:</label>
                    <input type="datetime-local" name="NgayGuiScheduled" class="form-control form-control-sm" value="{{ date('Y-m-d\TH:i') }}">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('thongbao.index') }}" class="btn btn-light rounded-pill px-4">Hủy</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                    <i class="fa-solid fa-check me-1"></i> Hoàn Tất &amp; Thực Thi
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function toggleSchedule(isSchedule) {
        const group = document.getElementById('schedule-input-group');
        if (isSchedule) {
            group.classList.remove('d-none');
        } else {
            group.classList.add('d-none');
        }
    }
</script>
@endpush
@endsection
