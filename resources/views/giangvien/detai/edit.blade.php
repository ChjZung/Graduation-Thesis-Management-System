@extends('layouts.giangvien')

@section('page_title', 'Chỉnh Sửa Đề Tài')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-premium">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Chỉnh Sửa Đề Tài Đề Xuất</span>
                <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light border btn-sm rounded-pill px-3">Quay Lại</a>
            </div>
            <div class="card-body p-4">
                @if(isset($errors) && $errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">@foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
                </div>
                @endif

                <!-- Trạng Thái Hiện Tại Của Đề Tài (Đồng bộ, nổi bật, chuyên nghiệp) -->
                @php
                    $tt = trim($detai->TrangThai ?? 'Chờ duyệt');
                    $statusConfig = [
                        'Chờ duyệt cấp Bộ môn' => ['class' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle', 'icon' => 'fa-solid fa-clock', 'desc' => 'Đang chờ Trưởng bộ môn thẩm định nội dung.'],
                        'Chờ duyệt cấp Khoa'   => ['class' => 'bg-info-subtle text-info border-info-subtle', 'icon' => 'fa-solid fa-paper-plane', 'desc' => 'Bộ môn đã thông qua, đang chờ Ban chủ nhiệm Khoa phê duyệt.'],
                        'Trưởng khoa đã duyệt - Chờ nộp đề cương' => ['class' => 'bg-primary-subtle text-primary border-primary-subtle', 'icon' => 'fa-solid fa-file-arrow-up', 'desc' => 'Đề tài đã duyệt thông qua tên, vui lòng tải lên Đề cương chi tiết.'],
                        'Đã nộp đề cương - Chờ phân công PB' => ['class' => 'bg-info-subtle text-info border-info-subtle', 'icon' => 'fa-solid fa-file-shield', 'desc' => 'Đã nộp đề cương, đang chờ Trưởng bộ môn phân công CB phản biện.'],
                        'Đang phản biện đề cương' => ['class' => 'bg-primary-subtle text-primary border-primary-subtle', 'icon' => 'fa-solid fa-spinner fa-spin', 'desc' => 'Cán bộ phản biện đang tiến hành thẩm định đề cương.'],
                        'Đã cập nhật đề cương - Chờ phản biện lại' => ['class' => 'bg-primary text-white', 'icon' => 'fa-solid fa-rotate', 'desc' => 'Đã nộp lại đề cương chỉnh sửa, chờ phản biện lại.'],
                        'Yêu cầu chỉnh sửa đề cương' => ['class' => 'bg-warning text-dark border-warning', 'icon' => 'fa-solid fa-triangle-exclamation', 'desc' => 'Phản biện yêu cầu bổ sung/chỉnh sửa lại đề cương chi tiết.'],
                        'Yêu cầu điều chỉnh'   => ['class' => 'bg-warning text-dark border-warning', 'icon' => 'fa-solid fa-wrench', 'desc' => 'Cần điều chỉnh lại thông tin đề tài theo góp ý của Hội đồng / Bộ môn.'],
                        'Yêu cầu chỉnh sửa'    => ['class' => 'bg-warning text-dark border-warning', 'icon' => 'fa-solid fa-wrench', 'desc' => 'Cần điều chỉnh lại thông tin đề tài theo góp ý của Hội đồng / Bộ môn.'],
                        'Đã duyệt'             => ['class' => 'bg-success-subtle text-success border-success-subtle', 'icon' => 'fa-solid fa-circle-check', 'desc' => 'Đề tài đã được phê duyệt chính thức.'],
                        'Trưởng khoa đã duyệt' => ['class' => 'bg-success-subtle text-success border-success-subtle', 'icon' => 'fa-solid fa-circle-check', 'desc' => 'Đề tài đã được Trưởng khoa phê duyệt hoàn tất.'],
                        'Đã công bố'           => ['class' => 'bg-success text-white', 'icon' => 'fa-solid fa-bullhorn', 'desc' => 'Đề tài đã công bố để sinh viên đăng ký.'],
                        'Từ chối'              => ['class' => 'bg-danger text-white', 'icon' => 'fa-solid fa-circle-xmark', 'desc' => 'Đề tài không được duyệt hoặc đã bị từ chối.'],
                        'Không đạt phản biện'  => ['class' => 'bg-danger text-white', 'icon' => 'fa-solid fa-circle-xmark', 'desc' => 'Đề cương không đạt yêu cầu phản biện.'],
                    ];
                    $currentConfig = $statusConfig[$tt] ?? ['class' => 'bg-secondary text-white', 'icon' => 'fa-solid fa-circle-info', 'desc' => 'Trạng thái hiện tại của đề tài trong hệ thống.'];
                @endphp

                <div class="card border rounded-3 shadow-sm mb-4 bg-light-subtle">
                    <div class="card-body p-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-white border shadow-xs text-primary" style="width: 48px; height: 48px; font-size: 1.25rem;">
                                    <i class="fa-solid fa-lightbulb"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-secondary-subtle text-dark border px-2 py-1 font-monospace">{{ $detai->MaDeTai }}</span>
                                        <span class="small text-muted">Học kỳ: <strong>{{ $detai->hocKy->TenHocKy ?? $detai->MaHocKy }}</strong></span>
                                        @if($detai->NgayDeXuat)
                                            <span class="small text-muted">&bull; Ngày gửi: {{ \Carbon\Carbon::parse($detai->NgayDeXuat)->format('d/m/Y') }}</span>
                                        @endif
                                    </div>
                                    <div class="small text-secondary">{{ $currentConfig['desc'] }}</div>
                                </div>
                            </div>
                            <div>
                                <div class="text-end small text-muted mb-1 fw-semibold">Trạng thái đề tài:</div>
                                <span class="badge {{ $currentConfig['class'] }} border rounded-pill px-3 py-2 fs-6 shadow-xs">
                                    <i class="{{ $currentConfig['icon'] }} me-1"></i> {{ $tt }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                @if(in_array($detai->TrangThai, ['Yêu cầu điều chỉnh', 'Yêu cầu chỉnh sửa', 'Yêu cầu chỉnh sửa đề cương', 'Từ chối', 'Không đạt phản biện']) && $detai->LyDoTuChoi)
                <div class="alert alert-warning border-warning d-flex align-items-start mb-4 rounded-3 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation text-warning fs-4 me-3 mt-1"></i>
                    <div>
                        <div class="fw-bold text-dark">Ý kiến phản hồi / Lý do yêu cầu chỉnh sửa:</div>
                        <div class="text-danger mt-1 fw-semibold">{{ $detai->LyDoTuChoi }}</div>
                        <div class="small text-muted mt-2">
                            <i class="fa-solid fa-circle-info me-1"></i>Sau khi quý Thầy/Cô chỉnh sửa và bấm <strong>"Cập Nhật & Nộp Lại Đề Tài"</strong>, đề tài sẽ tự động được gửi lại để xem xét phê duyệt.
                        </div>
                    </div>
                </div>
                @endif

                @if($gv && $gv->boMon)
                <div class="alert alert-info py-2 px-3 mb-4 d-flex align-items-center justify-content-between rounded-3 border">
                    <div>
                        <i class="fa-solid fa-building-user me-2 text-primary"></i>
                        Giảng viên: <strong>{{ $gv->HoTen }}</strong> &mdash; Bộ môn: <span class="badge bg-primary fs-7">{{ $gv->boMon->TenBoMon }}</span>
                    </div>
                    <span class="text-muted small"><i class="fa-solid fa-circle-check text-success me-1"></i>Học phần tự động lọc theo bộ môn trực thuộc</span>
                </div>
                @endif

                <form action="{{ route('giangvien.detai.update', $detai->MaDeTai) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="TenDeTai" class="form-label fw-bold">Tên Đề Tài (Tiếng Việt) <span class="text-danger">*</span></label>
                        <input type="text" name="TenDeTai" id="TenDeTai" class="form-control" value="{{ old('TenDeTai', $detai->TenDeTai) }}" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="MaHocKy" class="form-label fw-bold">Học Kỳ Áp Dụng <span class="text-danger">*</span></label>
                            <select name="MaHocKy" id="MaHocKy" class="form-select" required>
                                @foreach($hocKies as $hk)
                                <option value="{{ $hk->MaHocKy }}" {{ old('MaHocKy', $detai->MaHocKy) == $hk->MaHocKy ? 'selected' : '' }}>{{ $hk->TenHocKy }}</option>
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
                        <input type="text" name="LinhVuc" id="LinhVuc" class="form-control" value="{{ old('LinhVuc', $detai->LinhVuc) }}" placeholder="Ví dụ: AI, Web, Mobile...">
                    </div>

                    <div class="mb-3">
                        <label for="MoTa" class="form-label fw-bold">Mô Tả Đề Tài & Mục Tiêu Nghiên Cứu</label>
                        <textarea name="MoTa" id="MoTa" class="form-control" rows="3">{{ old('MoTa', $detai->MoTa) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="YeuCau" class="form-label fw-bold">Yêu Cầu Năng Lực Đối Với Sinh Viên</label>
                        <textarea name="YeuCau" id="YeuCau" class="form-control" rows="3">{{ old('YeuCau', $detai->YeuCau) }}</textarea>
                    </div>

                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <div class="small text-secondary d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-info text-primary"></i>
                                <span>
                                    <strong>Đề cương chi tiết:</strong> Đề tài chưa được phê duyệt. Biểu mẫu và nộp đề cương sẽ mở tại trang <strong>Nộp đề cương</strong> sau khi đề tài được duyệt và có nhóm SV đăng ký.
                                </span>
                            </div>
                            @if($detai->FileDeCuong)
                                @php
                                    $filePathEdit = Str::startsWith($detai->FileDeCuong, ['http', 'storage/']) ? asset($detai->FileDeCuong) : asset('storage/' . $detai->FileDeCuong);
                                @endphp
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                        onclick="quickPreviewOutline('{{ $filePathEdit }}', '{{ basename($detai->FileDeCuong) }}', '{{ addslashes($detai->TenDeTai) }}')">
                                    <i class="fa-solid fa-eye me-1"></i> Xem đề cương hiện tại
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 align-items-center pt-2">
                        <button type="submit" name="action_type" value="draft" class="btn btn-outline-secondary px-4 py-2 rounded-pill fw-semibold">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Lưu bản nháp
                        </button>
                        <button type="submit" name="action_type" value="resubmit" class="btn btn-primary px-4 py-2 rounded-pill fw-semibold shadow-sm">
                            <i class="fa-solid fa-paper-plane me-1"></i> Nộp lại yêu cầu phê duyệt đề tài
                        </button>
                        <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light border px-4 py-2 rounded-pill text-muted">Hủy bỏ</a>
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
    const selectedMaHocPhan = @json(old('MaHocPhan', $detai->MaHocPhan ?? ($hocPhans->firstWhere('TenHocPhan', $detai->HocPhan)?->MaHocPhan)));
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
            if (selectedMaHocPhan === hp.MaHocPhan) opt.selected = true;
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