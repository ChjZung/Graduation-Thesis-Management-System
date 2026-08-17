@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Kế Hoạch 5 Mốc Báo Cáo')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card card-premium">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Chỉnh Sửa Kế Hoạch Khóa Luận & 5 Mốc Thời Gian</span>
                <a href="{{ route('admin.kehoach.index') }}" class="btn btn-light border btn-sm rounded-pill px-3">Quay Lại</a>
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">@foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
                </div>
                @endif

                <form action="{{ route('admin.kehoach.update', $keHoach->MaKeHoach) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label for="TenKeHoach" class="form-label fw-bold">Tên Kế Hoạch <span class="text-danger">*</span></label>
                            <input type="text" name="TenKeHoach" id="TenKeHoach" class="form-control" value="{{ old('TenKeHoach', $keHoach->TenKeHoach) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="MaHocKy" class="form-label fw-bold">Học Kỳ <span class="text-danger">*</span></label>
                            <select name="MaHocKy" id="MaHocKy" class="form-select" required>
                                @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ $keHoach->MaHocKy == $hk->MaHocKy ? 'selected' : '' }}>{{ $hk->TenHocKy }} ({{ $hk->NamHoc }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="NoiDung" class="form-label fw-bold">Mô Tả / Nội Dung</label>
                            <textarea name="NoiDung" id="NoiDung" class="form-control" rows="2">{{ old('NoiDung', $keHoach->NoiDung) }}</textarea>
                        </div>
                    </div>

                    <h5 class="fw-bold text-primary-custom mb-3"><i class="fa-solid fa-clock me-2"></i>CẬP NHẬT 5 MỐC THỜI GIAN</h5>
                    <div class="accordion mb-4" id="mocsEditAccordion">
                        @foreach($keHoach->mocThoiGians as $idx => $moc)
                        <div class="accordion-item border rounded-3 mb-3 overflow-hidden shadow-sm">
                            <h2 class="accordion-header" id="headingE{{ $idx }}">
                                <button class="accordion-button {{ $idx == 0 ? '' : 'collapsed' }} bg-light fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseE{{ $idx }}">
                                    <span class="badge bg-primary me-2">Mốc {{ $idx + 1 }}</span> {{ $moc->TenMoc }}
                                </button>
                            </h2>
                            <div id="collapseE{{ $idx }}" class="accordion-collapse collapse {{ $idx == 0 ? 'show' : '' }}" data-bs-parent="#mocsEditAccordion">
                                <div class="accordion-body p-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Ngày Bắt Đầu</label>
                                            <input type="date" name="mocs[{{ $moc->MaMoc }}][NgayBatDau]" class="form-control" value="{{ date('Y-m-d', strtotime($moc->NgayBatDau)) }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Ngày Hạn Nộp (Kết thúc)</label>
                                            <input type="date" name="mocs[{{ $moc->MaMoc }}][NgayKetThuc]" class="form-control" value="{{ date('Y-m-d', strtotime($moc->NgayKetThuc)) }}" required>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold small">Yêu Cầu / Mô Tả</label>
                                            <input type="text" name="mocs[{{ $moc->MaMoc }}][MoTa]" class="form-control" value="{{ $moc->MoTa }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm">
                            <i class="fa-solid fa-save me-1"></i> Cập Nhật Kế Hoạch
                        </button>
                        <a href="{{ route('admin.kehoach.index') }}" class="btn btn-light border px-4 rounded-pill">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
