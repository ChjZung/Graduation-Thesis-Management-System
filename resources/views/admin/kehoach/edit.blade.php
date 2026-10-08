@extends('layouts.admin')

@section('page_title', 'Chỉnh Sửa Kế Hoạch Khóa Luận')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-11">
        <div class="card card-premium mb-4">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Chỉnh Sửa Kế Hoạch Khóa Luận & 13 Mốc Thời Gian Quy Trình</span>
                <a href="{{ route('admin.kehoach.index') }}" class="btn btn-light border btn-sm rounded-pill px-3">Quay Lại Danh Sách</a>
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">@foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
                </div>
                @endif

                <!-- Upload File Thay Thế Thông Báo Kế Hoạch (Nếu có) -->
                <div class="card border border-2 border-dashed bg-light-subtle rounded-3 p-3 mb-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-white border text-primary" style="width: 48px; height: 48px; font-size: 1.25rem;">
                                <i class="fa-solid fa-file-arrow-up"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Cập nhật hoặc Thay thế Văn bản Kế hoạch (PDF/Word)</h6>
                                <p class="small text-muted mb-0">Bạn có thể tải lên lại file thông báo mới nếu Khoa có điều chỉnh mốc thời gian quy trình.</p>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('admin.kehoach.importDocument') }}?ma_hoc_ky={{ $keHoach->MaHocKy }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
                                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload File Mới Thay Thế
                            </a>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.kehoach.update', $keHoach->MaKeHoach) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <label for="TenKeHoach" class="form-label fw-bold">Tên Kế Hoạch <span class="text-danger">*</span></label>
                            <input type="text" name="TenKeHoach" id="TenKeHoach" class="form-control" value="{{ old('TenKeHoach', $keHoach->TenKeHoach) }}" required>
                        </div>
                        <div class="col-md-5">
                            <label for="MaHocKy" class="form-label fw-bold">Học Kỳ Áp Dụng <span class="text-danger">*</span></label>
                            <select name="MaHocKy" id="MaHocKy" class="form-select" required>
                                @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ $keHoach->MaHocKy == $hk->MaHocKy ? 'selected' : '' }}>{{ $hk->TenHocKy }} (Năm học: {{ $hk->NamHoc }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="NoiDung" class="form-label fw-bold">Mô Tả / Nội Dung Kế Hoạch</label>
                            <textarea name="NoiDung" id="NoiDung" class="form-control" rows="2">{{ old('NoiDung', $keHoach->NoiDung) }}</textarea>
                        </div>
                    </div>

                    <h5 class="fw-bold text-primary mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="fa-solid fa-clock-rotate-left me-2"></i>Danh Sách Mốc Thời Gian Quy Trình ({{ $keHoach->mocThoiGians->count() }} Mốc)</span>
                    </h5>

                    <div class="table-responsive border rounded-3 mb-4">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="4%" class="text-center">#</th>
                                    <th width="32%">Tên Mốc Quy Trình</th>
                                    <th width="16%">Đối Tượng</th>
                                    <th width="18%">Ngày Bắt Đầu</th>
                                    <th width="18%">Ngày Kết Thúc</th>
                                    <th width="12%" class="text-center">Mã Giai Đoạn</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($keHoach->mocThoiGians as $idx => $moc)
                                <tr>
                                    <td class="text-center fw-bold text-muted">{{ $idx + 1 }}</td>
                                    <td>
                                        <input type="text" name="mocs[{{ $moc->MaMoc }}][TenMoc]" class="form-control form-control-sm fw-semibold" value="{{ $moc->TenMoc }}" required>
                                        @if($moc->MoTa)
                                            <div class="small text-muted mt-1">{{ Str::limit($moc->MoTa, 60) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="text" name="mocs[{{ $moc->MaMoc }}][DoiTuongThucHien]" class="form-control form-control-sm" value="{{ $moc->DoiTuongThucHien }}">
                                    </td>
                                    <td>
                                        <input type="date" name="mocs[{{ $moc->MaMoc }}][NgayBatDau]" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime($moc->NgayBatDau)) }}" required>
                                    </td>
                                    <td>
                                        <input type="date" name="mocs[{{ $moc->MaMoc }}][NgayKetThuc]" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime($moc->NgayKetThuc)) }}" required>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-primary border small font-monospace">{{ $moc->LoaiGiaiDoan }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.kehoach.index') }}" class="btn btn-light border px-4 rounded-pill">Hủy Bỏ</a>
                        <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm fw-bold" style="background: #00305a; border-color: #00305a;">
                            <i class="fa-solid fa-save me-1"></i> Lưu Thay Đổi Kế Hoạch
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
