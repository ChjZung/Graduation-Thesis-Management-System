@extends('layouts.giangvien')

@section('page_title', 'Đề Xuất Đề Tài Mới')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-premium">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-plus-circle text-success me-2"></i>Đề Xuất Đề Tài Khóa Luận Mới</span>
                <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light border btn-sm rounded-pill px-3">Quay Lại</a>
            </div>
            <div class="card-body p-4">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">@foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
                </div>
                @endif

                <form action="{{ route('giangvien.detai.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="TenDeTai" class="form-label fw-bold">Tên Đề Tài (Tiếng Việt) <span class="text-danger">*</span></label>
                        <input type="text" name="TenDeTai" id="TenDeTai" class="form-control" value="{{ old('TenDeTai') }}" placeholder="Nhập tên đề tài khóa luận..." required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label for="MaHocKy" class="form-label fw-bold">Học Kỳ Áp Dụng <span class="text-danger">*</span></label>
                            <select name="MaHocKy" id="MaHocKy" class="form-select" required>
                                <option value="">-- Chọn học kỳ --</option>
                                @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ old('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>{{ $hk->TenHocKy }} ({{ $hk->NamHoc }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="HocPhan" class="form-label fw-bold">Môn / Học Phần <span class="text-danger">*</span></label>
                            <select name="HocPhan" id="HocPhan" class="form-select" required>
                                <option value="Khóa luận tốt nghiệp" {{ old('HocPhan', 'Khóa luận tốt nghiệp') == 'Khóa luận tốt nghiệp' ? 'selected' : '' }}>Khóa luận tốt nghiệp</option>
                                <option value="Đồ án tốt nghiệp" {{ old('HocPhan') == 'Đồ án tốt nghiệp' ? 'selected' : '' }}>Đồ án tốt nghiệp</option>
                                <option value="Đồ án chuyên ngành" {{ old('HocPhan') == 'Đồ án chuyên ngành' ? 'selected' : '' }}>Đồ án chuyên ngành</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="MaNganh" class="form-label fw-bold">Ngành Đào Tạo Phù Hợp</label>
                            <select name="MaNganh" id="MaNganh" class="form-select">
                                <option value="">-- Áp dụng toàn khoa --</option>
                                @foreach($nganhs as $ng)
                                <option value="{{ $ng->MaNganh }}" {{ old('MaNganh') == $ng->MaNganh ? 'selected' : '' }}>{{ $ng->TenNganh }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="SoLuongSinhVienToiDa" class="form-label fw-bold">Số SV Tối Đa <span class="text-danger">*</span></label>
                            <select name="SoLuongSinhVienToiDa" id="SoLuongSinhVienToiDa" class="form-select" required>
                                <option value="1" {{ old('SoLuongSinhVienToiDa') == 1 ? 'selected' : '' }}>1 Sinh viên</option>
                                <option value="2" {{ old('SoLuongSinhVienToiDa', 2) == 2 ? 'selected' : '' }}>2 Sinh viên (Khuyến nghị)</option>
                                <option value="3" {{ old('SoLuongSinhVienToiDa') == 3 ? 'selected' : '' }}>3 Sinh viên</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="LinhVuc" class="form-label fw-bold">Lĩnh Vực Nghiên Cứu / Hướng Đề Tài</label>
                        <input type="text" name="LinhVuc" id="LinhVuc" class="form-control" value="{{ old('LinhVuc', 'Công Nghệ Phần Mềm & Trí Tuệ Nhân Tạo') }}" placeholder="Ví dụ: Hệ thống phân tán, Trí tuệ nhân tạo, Thị giác máy tính, Web app...">
                    </div>

                    <div class="mb-3">
                        <label for="MoTa" class="form-label fw-bold">Mô Tả Đề Tài & Mục Tiêu Nghiên Cứu</label>
                        <textarea name="MoTa" id="MoTa" class="form-control" rows="3" placeholder="Nhập mô tả chi tiết bài toán, phạm vi nghiên cứu, kết quả kỳ vọng...">{{ old('MoTa') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="YeuCau" class="form-label fw-bold">Yêu Cầu Năng Lực Đối Với Sinh Viên</label>
                        <textarea name="YeuCau" id="YeuCau" class="form-control" rows="3" placeholder="Ví dụ: Thành thạo Laravel/Vue, kiến thức cơ sở dữ liệu vững vàng, có tinh thần làm việc nhóm...">{{ old('YeuCau') }}</textarea>
                    </div>

                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="FileDeCuong" class="form-label fw-bold mb-0">
                                <i class="fa-solid fa-paperclip text-primary me-1"></i> Tệp Đề Cương Chi Tiết (Word / PDF)
                            </label>
                            <a href="{{ route('giangvien.detai.download_template') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="fa-solid fa-download me-1"></i> Tải Biểu Mẫu Đề Cương (.docx)
                            </a>
                        </div>
                        <input type="file" name="FileDeCuong" id="FileDeCuong" class="form-control" accept=".pdf,.doc,.docx">
                        <div class="form-text text-muted">
                            Định dạng hỗ trợ: .pdf, .doc, .docx (Dung lượng tối đa: 10MB). Đề cương giúp Giáo vụ duyệt đề tài nhanh chóng hơn.
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success px-5 rounded-pill shadow-sm fw-semibold">
                            <i class="fa-solid fa-paper-plane me-1"></i> Gửi Đề Xuất Cho Giáo Vụ Duyệt
                        </button>
                        <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light border px-4 rounded-pill">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection