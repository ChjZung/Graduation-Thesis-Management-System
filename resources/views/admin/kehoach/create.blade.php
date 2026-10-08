@extends('layouts.admin')

@section('page_title', 'Lập Kế Hoạch Khóa Luận Mới')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.kehoach.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
    </a>
</div>

<div class="admin-table-card">
    <div class="admin-table-header">
        <h5 class="admin-table-header-title">
            <i class="fa-solid fa-calendar-plus text-primary"></i>
            Lập Kế Hoạch Khóa Luận Tốt Nghiệp Mới
        </h5>
    </div>
    <div class="card-body p-4">
        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger p-3 rounded-3 mb-4">
                <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Vui lòng kiểm tra lại thông tin:</div>
                <ul class="mb-0 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.kehoach.store') }}" id="formCreateKeHoach">
            @csrf
            
            {{-- 1. THÔNG TIN CHUNG KẾ HOẠCH --}}
            <h6 class="fw-bold text-primary mb-3">
                <i class="fa-solid fa-circle-info me-2"></i>1. Thông Tin Chung Kế Hoạch Khóa Luận
            </h6>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tên Kế Hoạch Khóa Luận <span class="text-danger">*</span></label>
                    <input type="text" name="TenKeHoach" class="form-control" value="{{ old('TenKeHoach', 'Kế hoạch Khóa luận HK1 2026-2027') }}" required placeholder="VD: Kế hoạch Khóa luận Tốt nghiệp HK1 2026-2027">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Học Kỳ <span class="text-danger">*</span></label>
                    <select name="MaHocKy" class="form-select" required>
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ old('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>{{ $hk->TenHocKy }}</option>
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
                    <input type="date" name="NgayBatDau" id="NgayBatDauKH" class="form-control" value="{{ old('NgayBatDau', '2026-08-10') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Ngày Kết Thúc Kế Hoạch <span class="text-danger">*</span></label>
                    <input type="date" name="NgayKetThuc" id="NgayKetThucKH" class="form-control" value="{{ old('NgayKetThuc', '2026-11-28') }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Mô Tả / Nội Dung Kế Hoạch</label>
                    <textarea name="NoiDung" class="form-control" rows="2" placeholder="Nhập ghi chú hoặc quy định chung của kế hoạch...">Kế hoạch quản lý và thực hiện Khóa luận tốt nghiệp Học kỳ 1 Năm học 2026-2027 cho sinh viên ngành Công nghệ Thông tin theo Thông báo số 27/TB-KCNTT.</textarea>
                </div>
            </div>

            <hr class="my-4">

            {{-- 2. THIẾT LẬP MỐC THỜI GIAN QUY TRÌNH --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-primary mb-0">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i>2. Thiết Lập Các Mốc Thời Gian Quy Trình (Không được để trống mốc thời gian)
                </h6>
                <span class="badge bg-light text-primary border small">{{ count($defaultPhases) }} Mốc quy trình chuẩn</span>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="4%" class="text-center">#</th>
                            <th width="32%">Tên Giai Đoạn / Mốc Quy Trình <span class="text-danger">*</span></th>
                            <th width="15%">Đối Tượng</th>
                            <th width="18%">Ngày Bắt Đầu <span class="text-danger">*</span></th>
                            <th width="18%">Ngày Kết Thúc <span class="text-danger">*</span></th>
                            <th width="13%" class="text-center">Mã Giai Đoạn</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($defaultPhases as $idx => $phase)
                        <tr>
                            <td class="fw-bold text-center text-muted">{{ $idx + 1 }}</td>
                            <td>
                                <input type="text" name="mocs[{{ $idx }}][TenMoc]" class="form-control form-control-sm fw-semibold moc-ten" value="{{ $phase['TenMoc'] }}" required>
                                <input type="hidden" name="mocs[{{ $idx }}][LoaiGiaiDoan]" value="{{ $phase['LoaiGiaiDoan'] }}">
                            </td>
                            <td>
                                <input type="text" name="mocs[{{ $idx }}][DoiTuongThucHien]" class="form-control form-control-sm" value="{{ $phase['DoiTuongThucHien'] }}">
                            </td>
                            <td>
                                <input type="date" name="mocs[{{ $idx }}][NgayBatDau]" class="form-control form-control-sm moc-start" value="{{ $phase['NgayBatDau'] }}" required>
                            </td>
                            <td>
                                <input type="date" name="mocs[{{ $idx }}][NgayKetThuc]" class="form-control form-control-sm moc-end" value="{{ $phase['NgayKetThuc'] }}" required>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-primary border small font-monospace">{{ $phase['LoaiGiaiDoan'] }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <hr class="my-4">

            {{-- 3. QUY ĐỊNH & TIÊU CHUẨN KHÓA LUẬN (CHO PHÉP CHỈNH SỬA) --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="fa-solid fa-scale-balanced me-2"></i>3. Quy Định &amp; Tiêu Chuẩn Khóa Luận (Có thể tùy chỉnh theo đợt)
                    </h6>
                    <div class="small text-muted mt-1">Giáo vụ có thể trực tiếp điều chỉnh các chỉ số điều kiện, tiêu chuẩn trước khi lưu kế hoạch.</div>
                </div>
                <span class="badge bg-success-subtle text-success border border-success rounded-pill px-3 fw-semibold">8 Quy định chuẩn</span>
            </div>

            <div class="table-responsive mb-4 border rounded-3">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="4%" class="text-center">#</th>
                            <th width="30%">Tên Quy Định / Tiêu Chuẩn <span class="text-danger">*</span></th>
                            <th width="20%">Giá Trị Tham Số <span class="text-danger">*</span></th>
                            <th width="46%">Mô Tả Chi Tiết &amp; Căn Cứ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($defaultRegulations as $rIdx => $reg)
                        <tr>
                            <td class="text-center fw-bold text-muted">{{ $rIdx + 1 }}</td>
                            <td>
                                <input type="text" name="regulations[{{ $rIdx }}][TenQuyDinh]" class="form-control form-control-sm fw-bold" value="{{ $reg['TenQuyDinh'] }}" required>
                            </td>
                            <td>
                                <input type="text" name="regulations[{{ $rIdx }}][GiaTri]" class="form-control form-control-sm fw-semibold text-primary" value="{{ $reg['GiaTri'] }}" required placeholder="VD: 115 Tín chỉ, 2.0 GPA...">
                            </td>
                            <td>
                                <input type="text" name="regulations[{{ $rIdx }}][MoTa]" class="form-control form-control-sm text-secondary" value="{{ $reg['MoTa'] }}" placeholder="Ghi chú điều khoản...">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <a href="{{ route('admin.kehoach.index') }}" class="btn btn-light border rounded-pill px-4">Hủy bỏ</a>
                <div class="d-flex gap-2">
                    <button type="submit" name="action" value="draft" class="btn btn-light border rounded-pill px-4 fw-semibold">Lưu Bản Nháp</button>
                    <button type="submit" name="action" value="publish" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-check me-1"></i> Lưu &amp; Công Bố Kế Hoạch
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('formCreateKeHoach').addEventListener('submit', function(e) {
        const startKH = document.getElementById('NgayBatDauKH').value;
        const endKH = document.getElementById('NgayKetThucKH').value;

        if (!startKH || !endKH) {
            alert('Vui lòng chọn ngày bắt đầu và kết thúc của kế hoạch!');
            e.preventDefault();
            return false;
        }

        if (endKH < startKH) {
            alert('Ngày kết thúc kế hoạch phải diễn ra sau ngày bắt đầu!');
            e.preventDefault();
            return false;
        }

        // Kiểm tra từng mốc: Tuyệt đối không được để trống mốc thời gian
        const startInputs = document.querySelectorAll('.moc-start');
        const endInputs = document.querySelectorAll('.moc-end');
        const tenInputs = document.querySelectorAll('.moc-ten');

        for (let i = 0; i < startInputs.length; i++) {
            const ten = tenInputs[i].value.trim();
            const startVal = startInputs[i].value;
            const endVal = endInputs[i].value;

            if (!startVal) {
                alert('Mốc "' + ten + '" đang bị để trống Ngày bắt đầu! Vui lòng nhập đầy đủ ngày tháng.');
                startInputs[i].focus();
                e.preventDefault();
                return false;
            }

            if (!endVal) {
                alert('Mốc "' + ten + '" đang bị để trống Ngày kết thúc! Vui lòng nhập đầy đủ ngày tháng.');
                endInputs[i].focus();
                e.preventDefault();
                return false;
            }

            if (endVal < startVal) {
                alert('Mốc "' + ten + '" có ngày kết thúc trước ngày bắt đầu!');
                endInputs[i].focus();
                e.preventDefault();
                return false;
            }
        }
    });
</script>
@endpush
@endsection
