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

                @if($gv && $gv->boMon)
                <div class="alert alert-info py-2 px-3 mb-4 d-flex align-items-center justify-content-between rounded-3 border">
                    <div>
                        <i class="fa-solid fa-building-user me-2 text-primary"></i>
                        Giảng viên: <strong>{{ $gv->HoTen }}</strong> — Trực thuộc: <span class="badge bg-primary fs-7">{{ $gv->boMon->TenBoMon }}</span>
                    </div>
                    <span class="text-muted small"><i class="fa-solid fa-circle-check text-success me-1"></i>Học phần được lọc theo bộ môn trực thuộc & khóa luận dùng chung</span>
                </div>
                @endif

                <form action="{{ route('giangvien.detai.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="TenDeTai" class="form-label fw-bold">Tên Đề Tài (Tiếng Việt) <span class="text-danger">*</span></label>
                        <input type="text" name="TenDeTai" id="TenDeTai" class="form-control" value="{{ old('TenDeTai') }}" placeholder="Nhập tên đề tài khóa luận..." required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="MaHocKy" class="form-label fw-bold">Học Kỳ Áp Dụng <span class="text-danger">*</span></label>
                            <select name="MaHocKy" id="MaHocKy" class="form-select" required>
                                <option value="">-- Chọn học kỳ --</option>
                                @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ old('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>{{ $hk->TenHocKy }} ({{ $hk->NamHoc }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="MaHocPhan" class="form-label fw-bold">Môn / Học Phần Áp Dụng <span class="text-danger">*</span></label>
                            <select name="MaHocPhan" id="MaHocPhan" class="form-select" required>
                                <option value="">-- Vui lòng chọn học kỳ trước --</option>
                            </select>
                            <div class="form-text text-muted small" id="MaHocPhan_help">
                                Chỉ hiển thị các môn được mở trong học kỳ đã chọn theo Bộ môn <strong>{{ $gv->boMon->TenBoMon ?? 'trực thuộc' }}</strong> & Khóa luận.
                            </div>
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

                    <div class="mb-4 p-3 bg-light rounded-3 border border-dashed">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <div class="fw-bold text-dark">
                                <i class="fa-solid fa-file-circle-exclamation text-primary me-1"></i> Đề Cương Chi Tiết (Chưa nộp ở bước này)
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-outline-primary btn-sm rounded-pill px-3 dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-download me-1"></i> Tải Biểu Mẫu Đề Cương (.docx)
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                    <li><h6 class="dropdown-header small fw-bold text-muted">Chọn biểu mẫu theo môn/học phần</h6></li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('giangvien.detai.download_template', ['type' => 'cu_nhan']) }}">
                                            <i class="fa-solid fa-graduation-cap text-primary me-2 fa-fw"></i>
                                            <div>
                                                <div class="fw-semibold">Khóa Luận Cử Nhân</div>
                                                <small class="text-muted">Biểu mẫu đề cương cử nhân ngành CNTT</small>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('giangvien.detai.download_template', ['type' => 'ky_su']) }}">
                                            <i class="fa-solid fa-gears text-success me-2 fa-fw"></i>
                                            <div>
                                                <div class="fw-semibold">Khóa Luận Kỹ Sư</div>
                                                <small class="text-muted">Biểu mẫu đề cương kỹ sư ngành CNTT</small>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('giangvien.detai.download_template', ['type' => 'do_an']) }}">
                                            <i class="fa-solid fa-folder-open text-warning me-2 fa-fw"></i>
                                            <div>
                                                <div class="fw-semibold">Đồ Án Tốt Nghiệp</div>
                                                <small class="text-muted">Biểu mẫu đề cương đồ án ngành CNTT</small>
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="text-muted small">
                            <i class="fa-solid fa-circle-info text-info me-1"></i>
                            <strong>Quy trình thực hiện:</strong> Ở bước đề xuất ban đầu, Giảng viên <strong>không nộp file đề cương</strong> mà chỉ khai báo tên, mục tiêu và yêu cầu đề tài. File Đề cương chi tiết sẽ được mở để nộp sau khi đề xuất được <strong>Trưởng Bộ Môn</strong> và <strong>Trưởng Khoa phê duyệt chủ trương</strong>.
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm fw-semibold">
                            <i class="fa-solid fa-paper-plane me-1"></i> Gửi Đề Xuất Lên Bộ Môn Duyệt
                        </button>
                        <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light border px-4 rounded-pill">Hủy Bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectHocKy = document.getElementById('MaHocKy');
    const selectHocPhan = document.getElementById('MaHocPhan');
    const helpHocPhan = document.getElementById('MaHocPhan_help');
    const allHocPhans = @json($hocPhans ?? []);
    const oldMaHocPhan = @json(old('MaHocPhan'));
    const boMonTen = @json($gv->boMon->TenBoMon ?? 'Bộ môn trực thuộc');

    function updateHocPhanOptions() {
        const selectedHk = selectHocKy.value;
        selectHocPhan.innerHTML = '';

        if (!selectedHk) {
            selectHocPhan.innerHTML = '<option value="">-- Vui lòng chọn học kỳ trước --</option>';
            selectHocPhan.disabled = true;
            return;
        }

        selectHocPhan.disabled = false;

        // Lọc các học phần được mở trong selectedHk
        const openedHocPhans = allHocPhans.filter(hp => {
            if (!hp.hoc_phan_hoc_kies || !Array.isArray(hp.hoc_phan_hoc_kies)) return false;
            return hp.hoc_phan_hoc_kies.some(hphk => hphk.MaHocKy === selectedHk && hphk.TrangThai === 'Đang mở');
        });

        if (openedHocPhans.length === 0) {
            selectHocPhan.innerHTML = '<option value="">-- Không có học phần nào mở trong học kỳ này --</option>';
            selectHocPhan.disabled = true;
            if (helpHocPhan) {
                helpHocPhan.innerHTML = '<span class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>Học kỳ này hiện chưa có học phần nào của Bộ môn hoặc Khóa luận được mở.</span>';
            }
            return;
        }

        if (helpHocPhan) {
            helpHocPhan.innerHTML = `<i class="fa-solid fa-circle-check text-success me-1"></i>Đã lọc <strong>${openedHocPhans.length}</strong> học phần đang mở trong kỳ này (Bộ môn <strong>${boMonTen}</strong> & Khóa luận).`;
        }

        selectHocPhan.innerHTML = '<option value="">-- Chọn môn / học phần --</option>';

        const dungChung = openedHocPhans.filter(hp => !hp.MaBoMon);
        const chuyenNganh = openedHocPhans.filter(hp => hp.MaBoMon);

        if (dungChung.length > 0) {
            const grp = document.createElement('optgroup');
            grp.label = '⭐ Học phần dùng chung toàn khoa';
            dungChung.forEach(hp => {
                const opt = document.createElement('option');
                opt.value = hp.MaHocPhan;
                opt.textContent = `${hp.TenHocPhan} (${hp.MaHocPhan})`;
                if (oldMaHocPhan === hp.MaHocPhan) opt.selected = true;
                grp.appendChild(opt);
            });
            selectHocPhan.appendChild(grp);
        }

        if (chuyenNganh.length > 0) {
            const grp = document.createElement('optgroup');
            grp.label = `🏢 Chuyên ngành: ${boMonTen}`;
            chuyenNganh.forEach(hp => {
                const opt = document.createElement('option');
                opt.value = hp.MaHocPhan;
                opt.textContent = `${hp.TenHocPhan} (${hp.MaHocPhan})`;
                if (oldMaHocPhan === hp.MaHocPhan) opt.selected = true;
                grp.appendChild(opt);
            });
            selectHocPhan.appendChild(grp);
        }
    }

    if (selectHocKy && selectHocPhan) {
        selectHocKy.addEventListener('change', updateHocPhanOptions);
        if (selectHocKy.value) {
            updateHocPhanOptions();
        } else {
            selectHocPhan.disabled = true;
        }
    }
});
</script>
@endpush