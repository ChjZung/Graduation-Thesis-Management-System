@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Học Kỳ')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="card card-create-huit">
            <div class="card-header-huit">
                <div class="card-title-huit">
                    <i class="fa-solid fa-pen-to-square text-white me-1"></i> Chỉnh Sửa Thông Tin Học Kỳ: {{ $hocky->TenHocKy }} ({{ $hocky->NamHoc }})
                </div>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger p-3 rounded-3 mb-4">
                    <ul class="mb-0 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('hocky.update', $hocky->MaHocKy) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-5">
                            <label class="form-label-huit">Mã Học Kỳ (Hệ Thống)</label>
                            <input type="text" class="form-control form-control-huit bg-light" value="{{ $hocky->MaHocKy }}" readonly>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label-huit">Tên Học Kỳ Tổ Chức <span class="text-danger">*</span></label>
                            <input type="text" name="TenHocKy" class="form-control form-control-huit" value="{{ old('TenHocKy', $hocky->TenHocKy) }}" placeholder="Học kỳ 1 năm học 2025-2026" required>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <label class="form-label-huit">Năm Học <span class="text-danger">*</span></label>
                            <input type="text" name="NamHoc" class="form-control form-control-huit" value="{{ old('NamHoc', $hocky->NamHoc) }}" placeholder="2025-2026" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-huit">Thời Gian Bắt Đầu <span class="text-danger">*</span></label>
                            <input type="date" name="NgayBatDau" class="form-control form-control-huit" value="{{ old('NgayBatDau', $hocky->NgayBatDau ? \Carbon\Carbon::parse($hocky->NgayBatDau)->format('Y-m-d') : '') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-huit">Thời Gian Kết Thúc <span class="text-danger">*</span></label>
                            <input type="date" name="NgayKetThuc" class="form-control form-control-huit" value="{{ old('NgayKetThuc', $hocky->NgayKetThuc ? \Carbon\Carbon::parse($hocky->NgayKetThuc)->format('Y-m-d') : '') }}" required>
                        </div>
                    </div>

                    <div class="card-footer-huit mx-n4 mb-n4 mt-5">
                        <button type="submit" class="btn btn-save-huit">
                            <i class="fa-solid fa-check me-1"></i> Cập Nhật Học Kỳ
                        </button>
                        <a href="{{ route('hocky.index') }}" class="btn btn-cancel-huit">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection