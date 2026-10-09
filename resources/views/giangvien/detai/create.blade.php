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
                    <span class="text-muted small"><i class="fa-solid fa-circle-check text-success me-1"></i>Học phần tự động lọc theo bộ môn trực thuộc & theo từng học kỳ</span>
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
                                <option value="{{ $hk->MaHocKy }}" {{ old('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>{{ $hk->TenHocKy }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="MaHocPhan" class="form-label fw-bold">Môn / Học Phần Áp Dụng <span class="text-danger">*</span></label>
                            <select name="MaHocPhan" id="MaHocPhan" class="form-select" required>
                                <option value="">-- Vui lòng chọn học kỳ trước --</option>
                            </select>
                            <div class="form-text text-muted small" id="MaHocPhan_help">
                                Chỉ hiển thị các môn được mở trong học kỳ đã chọn theo Bộ môn <strong>{{ $gv->boMon->TenBoMon ?? 'trực thuộc' }}</strong>.
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="SoLuongSinhVienToiDa" class="form-label fw-bold">Số SV Tối Đa <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light fw-semibold text-dark" value="3 Sinh viên" readonly>
                            <input type="hidden" name="SoLuongSinhVienToiDa" value="3">
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
                        <div class="small text-secondary d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-info text-primary"></i>
                            <span><strong>Đề cương chi tiết:</strong> Đề tài chưa được phê duyệt (Giảng viên sẽ nộp file đề cương chi tiết sau khi đề tài được phê duyệt).</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="fa-solid fa-paper-plane me-1"></i> Đề xuất đề tài
                        </button>
                        <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light border px-4">Hủy Bỏ</a>
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

        // Lọc các học phần của Bộ môn được mở trong selectedHk
        const openedHocPhans = allHocPhans.filter(hp => {
            if (hp.hoc_phan_hoc_kies && Array.isArray(hp.hoc_phan_hoc_kies) && hp.hoc_phan_hoc_kies.length > 0) {
                const hphk = hp.hoc_phan_hoc_kies.find(x => x.MaHocKy === selectedHk);
                if (hphk) {
                    return hphk.TrangThai === 'Đang mở';
                }
            }
            return false;
        });

        if (openedHocPhans.length === 0) {
            selectHocPhan.innerHTML = '<option value="">-- Không có học phần nào mở trong học kỳ này --</option>';
            selectHocPhan.disabled = true;
            if (helpHocPhan) {
                helpHocPhan.innerHTML = `<span class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>Học kỳ này hiện chưa có học phần nào của Bộ môn <strong>${boMonTen}</strong> được kích hoạt mở. Vui lòng liên hệ Giáo vụ/Admin để mở học phần trong tab Phân bổ học kỳ.</span>`;
            }
            return;
        }

        if (helpHocPhan) {
            helpHocPhan.innerHTML = '';
        }

        selectHocPhan.innerHTML = '<option value="">-- Chọn môn / học phần --</option>';

        openedHocPhans.forEach(hp => {
            const opt = document.createElement('option');
            opt.value = hp.MaHocPhan;
            opt.textContent = hp.TenHocPhan;
            if (oldMaHocPhan === hp.MaHocPhan || openedHocPhans.length === 1) opt.selected = true;
            selectHocPhan.appendChild(opt);
        });
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