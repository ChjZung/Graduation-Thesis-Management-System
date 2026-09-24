@extends('layouts.giangvien')

@section('page_title', 'Chỉnh Sửa Đề Tài')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-premium">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Chỉnh Sửa Đề Tài Đề Xuất</span>
                <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light border btn-sm rounded-pill px-3">Quay Lại</a>
            </div>
            <div class="card-body p-4">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">@foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
                </div>
                @endif

                @if(in_array($detai->TrangThai, ['Yêu cầu điều chỉnh', 'Từ chối']) && $detai->LyDoTuChoi)
                <div class="alert alert-warning border-warning d-flex align-items-start mb-4 rounded-3 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation text-warning fs-4 me-3 mt-1"></i>
                    <div>
                        <div class="fw-bold text-dark">Ý kiến phản hồi / Yêu cầu điều chỉnh từ Giáo vụ:</div>
                        <div class="text-danger mt-1 fw-semibold">{{ $detai->LyDoTuChoi }}</div>
                        <div class="small text-muted mt-2">
                            <i class="fa-solid fa-circle-info me-1"></i>Sau khi quý Thầy/Cô chỉnh sửa và bấm <strong>"Cập Nhật & Nộp Lại Đề Tài"</strong>, đề tài sẽ tự động chuyển về trạng thái <strong>"Chờ duyệt"</strong> để Giáo vụ xem xét lại.
                        </div>
                    </div>
                </div>
                @endif

                <form action="{{ route('giangvien.detai.update', $detai->MaDeTai) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="TenDeTai" class="form-label fw-bold">Tên Đề Tài (Tiếng Việt) <span class="text-danger">*</span></label>
                        <input type="text" name="TenDeTai" id="TenDeTai" class="form-control" value="{{ old('TenDeTai', $detai->TenDeTai) }}" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label for="MaHocKy" class="form-label fw-bold">Học Kỳ Áp Dụng <span class="text-danger">*</span></label>
                            <select name="MaHocKy" id="MaHocKy" class="form-select" required>
                                @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ old('MaHocKy', $detai->MaHocKy) == $hk->MaHocKy ? 'selected' : '' }}>{{ $hk->TenHocKy }} ({{ $hk->NamHoc }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="HocPhan" class="form-label fw-bold">Môn / Học Phần <span class="text-danger">*</span></label>
                            <select name="HocPhan" id="HocPhan" class="form-select" required>
                                <option value="Khóa luận tốt nghiệp" {{ old('HocPhan', $detai->HocPhan ?? 'Khóa luận tốt nghiệp') == 'Khóa luận tốt nghiệp' ? 'selected' : '' }}>Khóa luận tốt nghiệp</option>
                                <option value="Đồ án tốt nghiệp" {{ old('HocPhan', $detai->HocPhan) == 'Đồ án tốt nghiệp' ? 'selected' : '' }}>Đồ án tốt nghiệp</option>
                                <option value="Đồ án chuyên ngành" {{ old('HocPhan', $detai->HocPhan) == 'Đồ án chuyên ngành' ? 'selected' : '' }}>Đồ án chuyên ngành</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="MaNganh" class="form-label fw-bold">Ngành Đào Tạo Phù Hợp</label>
                            <select name="MaNganh" id="MaNganh" class="form-select">
                                <option value="">-- Áp dụng toàn khoa --</option>
                                @foreach($nganhs as $ng)
                                <option value="{{ $ng->MaNganh }}" {{ old('MaNganh', $detai->MaNganh) == $ng->MaNganh ? 'selected' : '' }}>{{ $ng->TenNganh }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="SoLuongSinhVienToiDa" class="form-label fw-bold">Số SV Tối Đa <span class="text-danger">*</span></label>
                            <select name="SoLuongSinhVienToiDa" id="SoLuongSinhVienToiDa" class="form-select" required>
                                <option value="1" {{ old('SoLuongSinhVienToiDa', $detai->SoLuongSinhVienToiDa) == 1 ? 'selected' : '' }}>1 Sinh viên</option>
                                <option value="2" {{ old('SoLuongSinhVienToiDa', $detai->SoLuongSinhVienToiDa) == 2 ? 'selected' : '' }}>2 Sinh viên (Khuyến nghị)</option>
                                <option value="3" {{ old('SoLuongSinhVienToiDa', $detai->SoLuongSinhVienToiDa) == 3 ? 'selected' : '' }}>3 Sinh viên</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="LinhVuc" class="form-label fw-bold">Lĩnh Vực Nghiên Cứu / Hướng Đề Tài</label>
                        <input type="text" name="LinhVuc" id="LinhVuc" class="form-control" value="{{ old('LinhVuc', $detai->LinhVuc) }}" placeholder="Ví dụ: AI, Web, Mobile...">
                    </div>

                    <div class="mb-3">
                        <label for="MoTa" class="form-label fw-bold">Mô Tả Đề Tài & Mục Tiêu Nghiên Cứu</label>
                        <textarea name="MoTa" id="MoTa" class="form-control" rows="3">{{ old('MoTa', $detai->MoTa) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="YeuCau" class="form-label fw-bold">Yêu Cầu Năng Lực Đối Với Sinh Viên</label>
                        <textarea name="YeuCau" id="YeuCau" class="form-control" rows="3">{{ old('YeuCau', $detai->YeuCau) }}</textarea>
                    </div>

                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="FileDeCuong" class="form-label fw-bold mb-0">
                                <i class="fa-solid fa-paperclip text-primary me-1"></i> Tệp Đề Cương Chi Tiết
                            </label>
                            <a href="{{ route('giangvien.detai.download_template') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="fa-solid fa-download me-1"></i> Tải Biểu Mẫu Đề Cương (.docx)
                            </a>
                        </div>
                        @if($detai->FileDeCuong)
                        <div class="mb-2 p-2 bg-white rounded border d-flex align-items-center justify-content-between">
                            <div class="small text-success">
                                <i class="fa-solid fa-file-check me-1"></i>
                                Đã nộp: <strong>{{ basename($detai->FileDeCuong) }}</strong>
                            </div>
                            <a href="{{ asset($detai->FileDeCuong) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill px-3">
                                <i class="fa-solid fa-eye me-1"></i> Xem / Tải Tệp
                            </a>
                        </div>
                        @endif
                        <input type="file" name="FileDeCuong" id="FileDeCuong" class="form-control" accept=".pdf,.doc,.docx">
                        <div class="form-text text-muted">
                            Chọn tệp mới nếu muốn thay thế đề cương hiện tại (Hỗ trợ: .pdf, .doc, .docx - Tối đa 10MB).
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm fw-semibold">
                            <i class="fa-solid fa-save me-1"></i> {{ in_array($detai->TrangThai, ['Yêu cầu điều chỉnh', 'Từ chối']) ? 'Cập Nhật & Nộp Lại Đề Tài' : 'Cập Nhật Đề Tài' }}
                        </button>
                        <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light border px-4 rounded-pill">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection