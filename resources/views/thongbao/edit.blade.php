@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Thông Báo')

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
        <span><i class="fa-solid fa-pen-to-square me-2 text-primary"></i> Chỉnh Sửa Thông Báo [{{ $thongBao->MaThongBao }}]</span>
        <span class="badge bg-secondary rounded-pill px-3 py-1">{{ $thongBao->TrangThai }}</span>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('thongbao.update', $thongBao->MaThongBao) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Tiêu Đề Thông Báo <span class="text-danger">*</span></label>
                    <input type="text" name="TieuDe" class="form-control" value="{{ old('TieuDe', $thongBao->TieuDe) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Loại Thông Báo <span class="text-danger">*</span></label>
                    <select name="LoaiThongBao" class="form-select" required>
                        <option value="Kế hoạch" {{ old('LoaiThongBao', $thongBao->LoaiThongBao) == 'Kế hoạch' ? 'selected' : '' }}>Kế hoạch</option>
                        <option value="Hệ thống" {{ old('LoaiThongBao', $thongBao->LoaiThongBao) == 'Hệ thống' ? 'selected' : '' }}>Hệ thống</option>
                        <option value="Báo cáo" {{ old('LoaiThongBao', $thongBao->LoaiThongBao) == 'Báo cáo' ? 'selected' : '' }}>Báo cáo</option>
                        <option value="Hội đồng" {{ old('LoaiThongBao', $thongBao->LoaiThongBao) == 'Hội đồng' ? 'selected' : '' }}>Hội đồng</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Đối Tượng Nhận <span class="text-danger">*</span></label>
                    <select name="DoiTuongNhan" class="form-select" required>
                        <option value="Tất cả" {{ old('DoiTuongNhan', $thongBao->DoiTuongNhan) == 'Tất cả' ? 'selected' : '' }}>Toàn thể (Sinh viên &amp; Giảng viên)</option>
                        <option value="Sinh viên" {{ old('DoiTuongNhan', $thongBao->DoiTuongNhan) == 'Sinh viên' ? 'selected' : '' }}>Chỉ Sinh viên</option>
                        <option value="Giảng viên" {{ old('DoiTuongNhan', $thongBao->DoiTuongNhan) == 'Giảng viên' ? 'selected' : '' }}>Chỉ Giảng viên</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Liên Kết Kế Hoạch (Nếu có)</label>
                    <select name="MaKeHoach" class="form-select">
                        <option value="">-- Không liên kết kế hoạch --</option>
                        @foreach($keHoachs as $kh)
                            <option value="{{ $kh->MaKeHoach }}" {{ old('MaKeHoach', $thongBao->MaKeHoach) == $kh->MaKeHoach ? 'selected' : '' }}>{{ $kh->TenKeHoach }} ({{ $kh->MaHocKy }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Nội Dung Chi Tiết Thông Báo <span class="text-danger">*</span></label>
                <textarea name="NoiDung" class="form-control" rows="6" required>{{ old('NoiDung', $thongBao->NoiDung) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold"><i class="fa-solid fa-paperclip me-1 text-primary"></i> File Đính Kèm Hiện Tại</label>
                @if($thongBao->FileDinhKem)
                    <div class="mb-2">
                        <a href="{{ asset($thongBao->FileDinhKem) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="fa-solid fa-file-pdf me-1"></i> Xem File Hiện Tại
                        </a>
                    </div>
                @endif
                <input type="file" name="FileDinhKem" class="form-control" accept=".pdf,.docx,.doc,.zip,.rar,.png,.jpg,.jpeg">
                <span class="small text-muted">Chọn file mới để thay thế file hiện tại (nếu cần).</span>
            </div>

            <div class="p-3 bg-light rounded-3 border mb-4">
                <h6 class="fw-bold mb-2"><i class="fa-solid fa-clock me-1 text-primary"></i> Tùy Chọn Phát Hành</h6>
                <div class="d-flex gap-4 align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="action_type" id="act_draft" value="save_draft" checked>
                        <label class="form-check-label fw-bold text-secondary" for="act_draft">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Lưu Thay Đổi
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="action_type" id="act_send_now" value="send_now">
                        <label class="form-check-label fw-bold text-success" for="act_send_now">
                            <i class="fa-solid fa-paper-plane me-1"></i> Lưu &amp; Phát Hành Ngay
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('thongbao.index') }}" class="btn btn-light rounded-pill px-4">Hủy</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                    <i class="fa-solid fa-check me-1"></i> Lưu Cập Nhật
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
