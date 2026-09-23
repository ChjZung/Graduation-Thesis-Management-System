@extends('layouts.admin')

@section('page_title', 'Thêm Mới Học Kỳ Đào Tạo')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card card-create-huit">
            <div class="card-header-huit">
                <div class="card-title-huit">
                    <i class="fa-solid fa-plus text-white me-1"></i> Thêm Mới Học Kỳ Đào Tạo
                </div>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-4">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('hocky.store') }}">
                    @csrf
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <label class="form-label-huit">Mã Học Kỳ <span class="text-danger">*</span></label>
                            <input type="text" name="MaHocKy" class="form-control form-control-huit" value="{{ old('MaHocKy') }}" placeholder="HK2_2627" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-huit">Tên Học Kỳ & Niên Khóa <span class="text-danger">*</span></label>
                            <input type="text" name="TenHocKy" class="form-control form-control-huit" value="{{ old('TenHocKy') }}" placeholder="Học kỳ 2 — Năm học 2026–2027" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-huit">Năm Học <span class="text-danger">*</span></label>
                            <input type="text" name="NamHoc" class="form-control form-control-huit" value="{{ old('NamHoc', '2026-2027') }}" placeholder="2026-2027" required>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-7">
                            <label class="form-label-huit">Thời Gian Bắt Đầu – Kết Thúc <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="date" name="NgayBatDau" class="form-control form-control-huit" value="{{ old('NgayBatDau', date('Y-m-d')) }}" required>
                                <span class="input-group-text bg-white border-0 text-muted px-2">&ndash;</span>
                                <input type="date" name="NgayKetThuc" class="form-control form-control-huit" value="{{ old('NgayKetThuc', date('Y-m-d', strtotime('+5 months'))) }}" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-huit">Trạng Thái Cổng Đăng Ký</label>
                            <select name="TrangThaiCong" class="form-select form-select-huit">
                                <option value="1">Mở đăng ký đề tài</option>
                                <option value="0">Đóng cổng đăng ký</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-huit">Trạng Thái Học Kỳ Hệ Thống</label>
                        <select name="TrangThai" class="form-select form-select-huit">
                            <option value="UPCOMING">🟢 Chuẩn bị kích hoạt (Upcoming)</option>
                            <option value="1" selected>🟢 Đang diễn ra (Active)</option>
                            <option value="0">⚪ Đã kết thúc (Completed)</option>
                        </select>
                    </div>

                    <div class="card-footer-huit mx-n4 mb-n4 mt-5">
                        <button type="submit" class="btn btn-save-huit">
                            <i class="fa-solid fa-check me-1"></i> Lưu & Thêm Học Kỳ
                        </button>
                        <a href="{{ route('hocky.index') }}" class="btn btn-cancel-huit">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection