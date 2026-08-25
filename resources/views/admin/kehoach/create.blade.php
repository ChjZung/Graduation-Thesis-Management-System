@extends('layouts.admin')

@section('page_title', 'Lập Kế Hoạch Khóa Luận Mới')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.kehoach.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
    </a>
</div>

<div class="card card-premium">
    <div class="card-header-premium">
        <span><i class="fa-solid fa-calendar-plus text-primary me-2"></i> Lập Kế Hoạch Khóa Luận Tốt Nghiệp Mới & Thiết Lập 12 Giai Đoạn</span>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.kehoach.store') }}">
            @csrf
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tên Kế Hoạch Khóa Luận <span class="text-danger">*</span></label>
                    <input type="text" name="TenKeHoach" class="form-control" value="{{ old('TenKeHoach', 'Kế hoạch Khóa luận HK1 2026-2027') }}" required placeholder="VD: Kế hoạch Khóa luận Tốt nghiệp HK1 2026-2027">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Học Kỳ <span class="text-danger">*</span></label>
                    <select name="MaHocKy" class="form-select" required>
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ old('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>{{ $hk->TenHocKy }} ({{ $hk->NamHoc }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Năm Học <span class="text-danger">*</span></label>
                    <input type="text" name="NamHoc" class="form-control" value="{{ old('NamHoc', '2026-2027') }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Khoa Quản Lý</label>
                    <select name="MaKhoa" class="form-select">
                        <option value="">-- Toàn Trường / Khoa CNTT --</option>
                        @foreach($khoas as $k)
                            <option value="{{ $k->MaKhoa }}">{{ $k->TenKhoa }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Bộ Môn Trực Thuộc</label>
                    <select name="MaBoMon" class="form-select">
                        <option value="">-- Tất Cả Bộ Môn --</option>
                        @foreach($boMons as $bm)
                            <option value="{{ $bm->MaBoMon }}">{{ $bm->TenBoMon }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Ngày Bắt Đầu Kế Hoạch <span class="text-danger">*</span></label>
                    <input type="date" name="NgayBatDau" class="form-control" value="{{ old('NgayBatDau', '2026-09-01') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Ngày Kết Thúc Kế Hoạch <span class="text-danger">*</span></label>
                    <input type="date" name="NgayKetThuc" class="form-control" value="{{ old('NgayKetThuc', '2026-12-10') }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Mô Tả / Nội Dung Kế Hoạch</label>
                    <textarea name="NoiDung" class="form-control" rows="2" placeholder="Nhập ghi chú hoặc quy định chung của kế hoạch...">Kế hoạch quản lý và thực hiện Khóa luận tốt nghiệp Học kỳ 1 Năm học 2026-2027 cho sinh viên ngành Công nghệ Thông tin.</textarea>
                </div>
            </div>

            <hr class="my-4">

            <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-clock-rotate-left me-2"></i>Thiết Lập 12 Giai Đoạn / Mốc Thời Gian Quy Trình</h5>

            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="4%">#</th>
                            <th width="28%">Tên Giai Đoạn / Mốc Thời Gian</th>
                            <th width="15%">Đối Tượng</th>
                            <th width="18%">Ngày Bắt Đầu</th>
                            <th width="18%">Ngày Kết Thúc</th>
                            <th width="17%">Loại Giai Đoạn</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($defaultPhases as $idx => $phase)
                        <tr>
                            <td class="fw-bold text-center text-muted">{{ $idx + 1 }}</td>
                            <td>
                                <input type="text" name="mocs[{{ $idx }}][TenMoc]" class="form-control form-control-sm font-weight-bold" value="{{ $phase['TenMoc'] }}" required>
                                <input type="hidden" name="mocs[{{ $idx }}][LoaiGiaiDoan]" value="{{ $phase['LoaiGiaiDoan'] }}">
                            </td>
                            <td>
                                <input type="text" name="mocs[{{ $idx }}][DoiTuongThucHien]" class="form-control form-control-sm" value="{{ $phase['DoiTuongThucHien'] }}">
                            </td>
                            <td>
                                <input type="date" name="mocs[{{ $idx }}][NgayBatDau]" class="form-control form-control-sm" value="{{ $phase['NgayBatDau'] }}" required>
                            </td>
                            <td>
                                <input type="date" name="mocs[{{ $idx }}][NgayKetThuc]" class="form-control form-control-sm" value="{{ $phase['NgayKetThuc'] }}" required>
                            </td>
                            <td>
                                <span class="badge bg-light text-primary border small">{{ $phase['LoaiGiaiDoan'] }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="submit" name="action" value="draft" class="btn btn-light rounded-pill px-4">Lưu Bản Nháp</button>
                <button type="submit" name="action" value="publish" class="btn btn-success rounded-pill px-4"><i class="fa-solid fa-bullhorn me-1"></i>Công Bố Kế Hoạch Ngay</button>
            </div>
        </form>
    </div>
</div>
@endsection
