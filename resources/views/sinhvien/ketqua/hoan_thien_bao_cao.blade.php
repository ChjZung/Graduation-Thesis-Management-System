@extends('layouts.sinhvien')

@section('page_title', 'Hoàn Thiện Báo Cáo Sau Bảo Vệ')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-file-circle-check me-2 text-primary"></i>
                Hoàn Thiện Báo Cáo Sau Bảo Vệ (Lưu Trữ Khoa)
            </h4>
            <div class="text-muted small">
                Tiếp thu ý kiến góp ý của Hội đồng, chỉnh sửa toàn văn báo cáo và nộp lại bản hoàn chỉnh để xét cấp bằng tốt nghiệp.
            </div>
        </div>
        <div>
            <a href="{{ route('sinhvien.ketqua.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                <i class="fa-solid fa-award me-1"></i> Xem Kết Quả &amp; Bảng Điểm
            </a>
        </div>
    </div>

    <!-- Thông Tin Hội Đồng & Đề Tài -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-12">
                    <span class="badge bg-light text-secondary border font-monospace mb-1">Mã hồ sơ: {{ $hoSo->MaHoSo }}</span>
                    <h5 class="fw-bold text-primary mb-1">{{ $nhom->deTai->TenDeTai ?? 'Chưa cập nhật tên đề tài' }}</h5>
                    <div class="small text-muted">
                        <span>Nhóm: <strong>{{ $nhom->TenNhom }}</strong></span> &bull; 
                        <span>GVHD: <strong>{{ $nhom->deTai->giangVien->HoTen ?? 'Chưa phân công' }}</strong></span> &bull; 
                        <span>Hội đồng: <strong>{{ $hoSo->hoiDong->TenHoiDong ?? 'Chưa có' }}</strong> ({{ $hoSo->PhongBaoVe ?? ($hoSo->hoiDong->DiaDiem ?? 'Phòng B.304') }})</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(empty($hoSo->YeuCauChinhSuaSauBaoVe))
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center bg-white mb-4">
        <div class="mb-3">
            <i class="fa-solid fa-circle-check text-success fa-3x"></i>
        </div>
        <h5 class="fw-bold text-success mb-2">Đề tài đạt yêu cầu — Không có yêu cầu chỉnh sửa sau bảo vệ</h5>
        <p class="text-muted small mb-0 max-w-600 mx-auto">
            Hội đồng bảo vệ đã đánh giá quyển báo cáo toàn văn của nhóm đạt chuẩn. Nhóm có thể theo dõi kết quả bảng điểm chính thức tại mục <a href="{{ route('sinhvien.ketqua.index') }}">Kết quả khóa luận</a>.
        </p>
    </div>
    @else
    <!-- Card Kết Luận & Yêu Cầu Chỉnh Sửa Của Hội Đồng -->
    @php
        $hanNop = $hoSo->HanNopBaoCaoSauBaoVe ? \Carbon\Carbon::parse($hoSo->HanNopBaoCaoSauBaoVe) : null;
        $isQuaHan = $hanNop && $hanNop->isPast();
        $trangThaiSua = $hoSo->TrangThaiChinhSuaSauBaoVe ?? 'ChoSinhVienNop';
    @endphp
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white" style="border-left: 5px solid {{ $trangThaiSua === 'DaHoanThanh' ? '#198754' : ($isQuaHan ? '#dc3545' : '#0072ce') }} !important;">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-clipboard-question text-primary me-2"></i>Kết Luận &amp; Yêu Cầu Chỉnh Sửa Từ Hội Đồng Bảo Vệ
            </span>
            <div>
                @if($trangThaiSua === 'DaHoanThanh')
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold">
                        <i class="fa-solid fa-circle-check me-1"></i> Báo Cáo Đã Đạt Chuẩn Lưu Trữ
                    </span>
                @elseif($trangThaiSua === 'DaNop')
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-3 py-2 fw-semibold">
                        <i class="fa-solid fa-hourglass-half me-1"></i> Đã Nộp — Chờ Giảng Viên Kiểm Tra
                    </span>
                @elseif($trangThaiSua === 'YeuCauSuaTiep')
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 fw-semibold">
                        <i class="fa-solid fa-rotate-right me-1"></i> Yêu Cầu Chỉnh Sửa Lại
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2 fw-semibold">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Chờ Nhóm Nộp Bản Chỉnh Sửa
                    </span>
                @endif
            </div>
        </div>
        <div class="card-body p-4">
            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary text-uppercase">Nội dung yêu cầu chỉnh sửa từ Hội đồng:</label>
                <div class="p-3 rounded-3 bg-light border text-dark" style="white-space: pre-line; font-size: 0.92rem; line-height: 1.6;">{{ $hoSo->YeuCauChinhSuaSauBaoVe }}</div>
            </div>

            <div class="row g-3 pt-2 border-top">
                <div class="col-md-6">
                    <span class="text-muted small">Thời hạn hoàn thành nộp lại:</span>
                    @if($hanNop)
                        <div class="fw-bold {{ $isQuaHan ? 'text-danger' : 'text-primary' }} fs-6 mt-1">
                            <i class="fa-regular fa-clock me-1"></i> {{ $hanNop->format('d/m/Y H:i') }}
                            @if($isQuaHan)
                                <span class="badge bg-danger ms-1">Đã hết hạn</span>
                            @else
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1 font-monospace">Còn {{ $hanNop->diffForHumans() }}</span>
                            @endif
                        </div>
                    @else
                        <div class="fw-semibold text-secondary mt-1">Theo thông báo từ Hội đồng</div>
                    @endif
                </div>

                <div class="col-md-6">
                    <span class="text-muted small">Người phụ trách kiểm tra, xác nhận:</span>
                    <div class="fw-bold text-dark fs-6 mt-1">
                        <i class="fa-solid fa-user-check text-success me-1"></i> {{ $hoSo->NguoiKiemTraChinhSua ?? ($nhom->deTai->giangVien->HoTen ?? 'Giảng viên hướng dẫn') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Nộp Lại Báo Cáo Chỉnh Sửa (Chỉ dành cho Nhóm trưởng và khi chưa hoàn thành) -->
    @if($trangThaiSua !== 'DaHoanThanh')
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-header bg-white border-bottom py-3">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i>Nộp Bản Báo Cáo Toàn Văn Đã Chỉnh Sửa
            </span>
        </div>
        <div class="card-body p-4">
            @if(!$isLeader)
                <div class="alert alert-warning border-warning rounded-3 py-2 px-3 small mb-0">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> <strong>Lưu ý:</strong> Chỉ có <strong>Nhóm trưởng ({{ $nhom->truongNhom->HoTen ?? 'Nhóm trưởng' }})</strong> mới có quyền đại diện nhóm nộp tệp Báo cáo chỉnh sửa sau bảo vệ.
                </div>
            @else
                <form action="{{ route('sinhvien.ketqua.nopBaoCaoChinhSua') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="MaHoSo" value="{{ $hoSo->MaHoSo }}">

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-dark">
                                1. Tệp Báo Cáo Toàn Văn Đã Chỉnh Sửa (PDF Chuẩn) <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="FileBaoCaoChinhSua" class="form-control" accept=".pdf" required>
                            <div class="form-text small text-muted">
                                Bắt buộc định dạng .PDF (tối đa 50MB). Bản báo cáo hoàn thiện phải bao gồm đầy đủ trang bìa, chữ ký xác nhận của GVHD và chỉnh sửa đầy đủ theo ý kiến Hội đồng.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                2. Bản Giải Trình Tiếp Thu Ý Kiến Của Hội Đồng (PDF / Word)
                            </label>
                            <input type="file" name="FileGiaiTrinh" class="form-control" accept=".pdf,.doc,.docx">
                            <div class="form-text small text-muted">
                                Đính kèm bảng giải trình chi tiết từng ý kiến góp ý của Hội đồng và vị trí nội dung đã chỉnh sửa (.pdf, .doc, .docx - tối đa 50MB).
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">
                                3. Tài Liệu Minh Chứng Kèm Theo (Nếu có)
                            </label>
                            <input type="file" name="FileMinhChung" class="form-control" accept=".zip,.rar,.pdf,.doc,.docx">
                            <div class="form-text small text-muted">
                                Ảnh chụp kết quả thực nghiệm, dữ liệu test, xác nhận cơ quan hoặc tệp nén đính kèm (.zip, .rar, .pdf - tối đa 50MB).
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-dark">
                                4. Tóm Tắt &amp; Ghi Chú Các Nội Dung Nhóm Đã Tiếp Thu &amp; Chỉnh Sửa <span class="text-danger">*</span>
                            </label>
                            <textarea name="TomTatChinhSua" rows="4" class="form-control" required
                                placeholder="Ghi rõ: Nhóm đã tiếp thu góp ý của Hội đồng và sửa đổi tại các trang/mục nào... Ví dụ:
- Mục 3.2: Đã bổ sung biểu đồ kiến trúc triển khai hệ thống (trang 45).
- Mục 5.1: Đã chuẩn hóa danh mục tài liệu tham khảo theo IEEE (trang 78).
- Đã kiểm tra lại kết quả thực nghiệm và sửa lỗi chính tả theo góp ý của Thư ký HĐ."></textarea>
                        </div>

                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" style="background: #0072ce; border-color: #0072ce;">
                                <i class="fa-solid fa-paper-plane me-2"></i> Nộp Báo Cáo Chỉnh Sửa Cho Giảng Viên Kiểm Tra
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
    @endif
    @endif

    <!-- Lịch Sử Các Lần Nộp Lại Báo Cáo Hoàn Thiện -->
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-header bg-white border-bottom py-3">
            <span class="fw-bold text-dark">
                <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Lịch Sử Các Lần Nộp Lại Báo Cáo Sau Bảo Vệ ({{ $lichSuNop->count() }} lần)
            </span>
        </div>
        <div class="card-body p-0">
            @if($lichSuNop->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                    <h6>Chưa có bản báo cáo chỉnh sửa nào được nộp.</h6>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.86rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;" class="text-center">Lần nộp</th>
                                <th>Hồ sơ nộp (Báo cáo &amp; Đính kèm)</th>
                                <th style="min-width: 250px;">Tóm tắt nội dung tiếp thu &amp; sửa đổi</th>
                                <th style="width: 150px;">Thời gian nộp</th>
                                <th class="text-center" style="width: 140px;">Trạng thái</th>
                                <th style="min-width: 200px;">Nhận xét của Giảng viên kiểm tra</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lichSuNop as $item)
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-primary text-white rounded-pill px-3 py-1">Lần {{ $item->LanChinhSua }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <i class="fa-solid fa-file-pdf text-danger me-1"></i>{{ $item->TenFile }}
                                    </div>
                                    <div class="text-muted small">
                                        {{ $item->DungLuong ? round($item->DungLuong / 1024 / 1024, 2) . ' MB' : '' }}
                                    </div>
                                    <a href="{{ asset('storage/' . $item->DuongDanFile) }}" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0 mt-1 d-inline-flex align-items-center" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-download me-1"></i> Báo cáo PDF
                                    </a>

                                    @if($item->FileGiaiTrinh)
                                    <div class="mt-2 pt-1 border-top">
                                        <div class="small fw-semibold text-secondary text-truncate" style="max-width: 220px;" title="{{ $item->TenFileGiaiTrinh }}">
                                            <i class="fa-solid fa-file-lines text-info me-1"></i>{{ $item->TenFileGiaiTrinh ?? 'Bản giải trình' }}
                                        </div>
                                        <a href="{{ asset('storage/' . $item->FileGiaiTrinh) }}" target="_blank" class="btn btn-xs btn-outline-info rounded-pill px-2 py-0 mt-1 d-inline-flex align-items-center" style="font-size: 0.72rem;">
                                            <i class="fa-solid fa-file-arrow-down me-1"></i> Giải trình
                                        </a>
                                    </div>
                                    @endif

                                    @if($item->FileMinhChung)
                                    <div class="mt-2 pt-1 border-top">
                                        <div class="small fw-semibold text-secondary text-truncate" style="max-width: 220px;" title="{{ $item->TenFileMinhChung }}">
                                            <i class="fa-solid fa-paperclip text-success me-1"></i>{{ $item->TenFileMinhChung ?? 'Minh chứng' }}
                                        </div>
                                        <a href="{{ asset('storage/' . $item->FileMinhChung) }}" target="_blank" class="btn btn-xs btn-outline-success rounded-pill px-2 py-0 mt-1 d-inline-flex align-items-center" style="font-size: 0.72rem;">
                                            <i class="fa-solid fa-paperclip me-1"></i> Minh chứng
                                        </a>
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-secondary" style="white-space: pre-line; max-width: 320px;">{{ $item->TomTatChinhSua }}</div>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark">{{ \Carbon\Carbon::parse($item->NgayNop)->format('d/m/Y H:i') }}</div>
                                    <div class="small text-muted">Người nộp: {{ $item->sinhVienNop->HoTen ?? $item->NguoiNop }}</div>
                                </td>
                                <td class="text-center">
                                    @if($item->TrangThai === 'Đạt')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                            <i class="fa-solid fa-check me-1"></i> ĐẠT YÊU CẦU
                                        </span>
                                    @elseif($item->TrangThai === 'Yêu cầu sửa tiếp')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">
                                            <i class="fa-solid fa-rotate me-1"></i> SỬA LẠI
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1">
                                            <i class="fa-solid fa-hourglass-half me-1"></i> ĐÃ NỘP - CHỜ KIỂM TRA
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($item->NhanXetGV))
                                        <div class="text-dark small fw-semibold">{{ $item->NhanXetGV }}</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">
                                            {{ $item->NgayKiemTra ? \Carbon\Carbon::parse($item->NgayKiemTra)->format('d/m/Y H:i') : '' }} &bull; Bởi: {{ $item->NguoiKiemTra }}
                                        </div>
                                    @else
                                        <span class="text-muted small fst-italic">Chưa có đánh giá</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
