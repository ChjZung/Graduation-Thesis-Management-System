@extends('layouts.admin')

@section('page_title', 'Lập Kế Hoạch & Cấu Hình 5 Mốc Báo Cáo')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card card-premium">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-calendar-plus text-success me-2"></i>Lập Kế Hoạch Khóa Luận & Cấu Hình 5 Mốc Trình Tự Chuẩn</span>
                <a href="{{ route('admin.kehoach.index') }}" class="btn btn-light border btn-sm rounded-pill px-3">Quay Lại</a>
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">@foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
                </div>
                @endif

                <form action="{{ route('admin.kehoach.store') }}" method="POST">
                    @csrf

                    <h5 class="fw-bold text-primary-custom mb-3">1. THÔNG TIN KẾ HOẠCH HỌC KỲ</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label for="TenKeHoach" class="form-label fw-bold">Tên Kế Hoạch Khóa Luận <span class="text-danger">*</span></label>
                            <input type="text" name="TenKeHoach" id="TenKeHoach" class="form-control" value="{{ old('TenKeHoach', 'Kế Hoạch Khóa Luận Tốt Nghiệp HK1 (2025 - 2026)') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="MaHocKy" class="form-label fw-bold">Học Kỳ Áp Dụng <span class="text-danger">*</span></label>
                            <select name="MaHocKy" id="MaHocKy" class="form-select" required>
                                <option value="">-- Chọn học kỳ --</option>
                                @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}">{{ $hk->TenHocKy }} ({{ $hk->NamHoc }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="NoiDung" class="form-label fw-bold">Mô Tả & Quy Định Chung</label>
                            <textarea name="NoiDung" id="NoiDung" class="form-control" rows="2" placeholder="Nhập quy định hoặc mô tả chung cho khóa luận học kỳ này...">Yêu cầu tuân thủ đúng 5 mốc nộp bài trình tự cố định. Nộp file PDF hoặc Link Git theo đúng quy định từng mốc.</textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="fw-bold text-primary-custom mb-3">
                        <i class="fa-solid fa-timeline me-2 text-warning"></i>2. CẤU HÌNH 5 MỐC THỜI GIAN TRÌNH TỰ CHUẨN
                    </h5>

                    @php
                        $defaultMocs = [
                            ['TenMoc' => 'Mốc 1: Phân tích Nghiệp vụ', 'MoTa' => 'Yêu cầu nộp File PDF báo cáo khảo sát nghiệp vụ và đề xuất giải pháp.'],
                            ['TenMoc' => 'Mốc 2: Phân tích Hệ thống', 'MoTa' => 'Yêu cầu nộp File PDF sơ đồ Use-Case, Activity, Sequence Diagram.'],
                            ['TenMoc' => 'Mốc 3: Thiết kế Cơ sở Dữ liệu', 'MoTa' => 'Yêu cầu nộp File PDF mô hình ERD, sơ đồ CSDL quan hệ và tả bảng.'],
                            ['TenMoc' => 'Mốc 4: Triển khai Code & Demo', 'MoTa' => 'Yêu cầu nộp URL Link Source Code GitHub/GitLab hoạt động.'],
                            ['TenMoc' => 'Mốc 5: Báo cáo Hoàn thành & Bổ sung', 'MoTa' => 'Yêu cầu nộp Cuốn báo cáo hoàn chỉnh (PDF) và Link Git chính thức.'],
                        ];
                    @endphp

                    <div class="accordion mb-4" id="mocsAccordion">
                        @foreach($defaultMocs as $idx => $dm)
                        <div class="accordion-item border rounded-3 mb-3 overflow-hidden shadow-sm">
                            <h2 class="accordion-header" id="heading{{ $idx }}">
                                <button class="accordion-button {{ $idx == 0 ? '' : 'collapsed' }} bg-light fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $idx }}">
                                    <span class="badge bg-primary me-2">Mốc {{ $idx + 1 }}</span> {{ $dm['TenMoc'] }}
                                </button>
                            </h2>
                            <div id="collapse{{ $idx }}" class="accordion-collapse collapse {{ $idx == 0 ? 'show' : '' }}" data-bs-parent="#mocsAccordion">
                                <div class="accordion-body p-3">
                                    <input type="hidden" name="mocs[{{ $idx }}][TenMoc]" value="{{ $dm['TenMoc'] }}">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Ngày Bắt Đầu</label>
                                            <input type="date" name="mocs[{{ $idx }}][NgayBatDau]" class="form-control" value="{{ date('Y-m-d', strtotime('+' . ($idx * 14) . ' days')) }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Ngày Hạn Nộp (Kết thúc)</label>
                                            <input type="date" name="mocs[{{ $idx }}][NgayKetThuc]" class="form-control" value="{{ date('Y-m-d', strtotime('+' . (($idx + 1) * 14) . ' days')) }}" required>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold small">Quy Định & Mô Tả Nộp Bài</label>
                                            <input type="text" name="mocs[{{ $idx }}][MoTa]" class="form-control" value="{{ $dm['MoTa'] }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success px-5 rounded-pill shadow-sm">
                            <i class="fa-solid fa-check-circle me-1"></i> Lưu & Công Bố Kế Hoạch 5 Mốc
                        </button>
                        <a href="{{ route('admin.kehoach.index') }}" class="btn btn-light border px-4 rounded-pill">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
