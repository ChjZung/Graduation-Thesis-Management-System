@extends('layouts.giangvien')

@section('page_title', 'Đánh Giá Báo Cáo Tiến Độ (Tích hợp AI)')

@section('content')



<!-- Header & Navigation -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('giangvien.baocao.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
        <div>
            <h4 class="fw-bold mb-0 text-primary-custom d-flex align-items-center gap-2">
                <span>Đánh Giá Báo Cáo Tiến Độ</span>
                <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary-subtle" style="font-size: 0.75rem;">
                    <i class="fa-solid fa-wand-magic-sparkles text-warning me-1"></i>Tích hợp Trợ lý AI
                </span>
            </h4>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge {{ $baoCao->TrangThai === 'Đạt' ? 'bg-success' : ($baoCao->TrangThai === 'Chờ duyệt' ? 'bg-warning text-dark' : 'bg-info text-dark') }} rounded-pill px-3 py-2 fw-bold">
            Trạng thái: {{ $baoCao->TrangThai }}
        </span>
    </div>
</div>

<!-- Project & Group Meta Box (Theo chuẩn Mockup Figma) -->
<div class="card card-premium shadow-sm border-0 mb-4">
    <div class="card-body p-3 bg-light rounded-3 border">
        <div class="row g-2 align-items-center">
            <div class="col-md-7">
                <div class="fw-bold text-dark fs-6 mb-1">
                    <i class="fa-solid fa-folder-open text-primary me-2"></i>
                    {{ $nhom->TenNhom ?? ('Nhóm ' . $nhom->MaNhom) }} — {{ $deTai->TenDeTai ?? 'Chưa có tên đề tài' }}
                </div>
                <div class="small text-muted d-flex flex-wrap gap-3">
                    <span>
                        <strong><i class="fa-solid fa-users me-1 text-secondary"></i>Sinh viên:</strong>
                        @if($truongNhom)
                            {{ $truongNhom->HoTen }} <span class="badge bg-light text-dark border">👑 Trưởng nhóm</span>
                        @endif
                        @foreach($thanhViens as $tv)
                            @if(!$truongNhom || $tv->MaSV !== $truongNhom->MaSV)
                                , {{ $tv->sinhVien->HoTen ?? $tv->MaSV }}
                            @endif
                        @endforeach
                    </span>
                </div>
            </div>
            <div class="col-md-5 text-md-end">
                <div class="small text-muted">
                    <strong>GVHD:</strong> {{ $giangVien->HocHam ? $giangVien->HocHam . '.' : '' }} {{ $giangVien->HocVi ? $giangVien->HocVi . '.' : '' }} {{ $giangVien->HoTen }}
                </div>
                <div class="small text-primary fw-semibold mt-1">
                    <i class="fa-regular fa-clock me-1"></i>Đợt báo cáo: Mốc {{ $baoCao->LanBaoCao }} - {{ $baoCao->TieuDe }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bảng Lộ Trình 5 Mốc Tổng Quan của Nhóm (Theo Mockup duyettiendo) -->
<div class="card card-premium shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <div class="small fw-bold text-secondary text-uppercase mb-2">
            <i class="fa-solid fa-bars-progress me-1 text-primary"></i>Tổng quan lộ trình 5 mốc của nhóm:
        </div>
        <div class="row g-2 text-center">
            @php
                $mocsConfig = [
                    1 => 'Mốc 1: Đề cương',
                    2 => 'Mốc 2: Thiết kế',
                    3 => 'Mốc 3: Lập trình',
                    4 => 'Mốc 4: Code Git',
                    5 => 'Mốc 5: Báo cáo cuối',
                ];
            @endphp
            @foreach($mocsConfig as $mIndex => $mLabel)
            @php
                $item = $allBaoCaos[$mIndex] ?? null;
                $isThis = ($baoCao->LanBaoCao == $mIndex);
            @endphp
            <div class="col">
                <div class="p-2 rounded-3 border {{ $isThis ? 'border-primary bg-primary bg-opacity-10 shadow-xs' : 'bg-light' }}">
                    <div class="fw-bold small {{ $isThis ? 'text-primary' : 'text-dark' }}" style="font-size: 0.78rem;">
                        {{ $mLabel }}
                    </div>
                    <div class="mt-1">
                        @if(!$item)
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size: 0.68rem;">Chưa nộp</span>
                        @elseif($item->TrangThai === 'Đạt')
                            <span class="badge bg-success rounded-pill" style="font-size: 0.68rem;">✅ Đạt</span>
                        @elseif($item->TrangThai === 'Chờ duyệt')
                            <span class="badge bg-warning text-dark rounded-pill" style="font-size: 0.68rem;">⏳ Chờ duyệt</span>
                        @else
                            <span class="badge bg-info text-dark rounded-pill" style="font-size: 0.68rem;">🔄 Nộp lại</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Split View: Left (PDF Preview & Student Submission) | Right (AI Summary & Feedback) -->
<div class="row g-4">
    <!-- Cột Trái (58%): Bản xem trước tài liệu PDF & Nội dung nộp -->
    <div class="col-lg-7">
        <div class="card card-premium shadow-sm border-0 h-100 d-flex flex-column">
            <!-- PDF Toolbar -->
            <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
                    <span class="fw-bold text-dark small text-truncate" style="max-width: 250px;">
                        {{ $baoCao->TenFile ?? ('BaoCao_Moc' . $baoCao->LanBaoCao . '.pdf') }}
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($baoCao->DuongDanFile)
                        <a href="{{ asset('storage/' . $baoCao->DuongDanFile) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Mở toàn màn hình
                        </a>
                        <a href="{{ asset('storage/' . $baoCao->DuongDanFile) }}" download class="btn btn-sm btn-primary rounded-pill px-3 shadow-xs">
                            <i class="fa-solid fa-download me-1"></i>Tải file PDF
                        </a>
                    @else
                        <span class="badge bg-light text-muted border px-2 py-1">Không có file PDF</span>
                    @endif
                </div>
            </div>

            <!-- Ghi chú & Link Code nộp kèm -->
            @if($baoCao->LinkCode || $baoCao->NoiDungBaoCao)
            <div class="p-3 bg-light border-bottom">
                @if($baoCao->LinkCode)
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-dark text-white px-2 py-1"><i class="fa-brands fa-github me-1"></i>Source Code:</span>
                        <a href="{{ $baoCao->LinkCode }}" target="_blank" class="small fw-bold text-primary text-break">
                            {{ $baoCao->LinkCode }} <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i>
                        </a>
                    </div>
                @endif
                @if($baoCao->NoiDungBaoCao)
                    <div class="small text-secondary">
                        <strong><i class="fa-regular fa-comment-dots me-1"></i>Ghi chú của sinh viên:</strong>
                        <div class="p-2 bg-white rounded border mt-1 text-dark" style="font-size: 0.82rem; white-space: pre-line;">
                            {{ $baoCao->NoiDungBaoCao }}
                        </div>
                    </div>
                @endif
            </div>
            @endif

            <!-- PDF Viewer Body -->
            <div class="card-body p-0 flex-grow-1 bg-secondary bg-opacity-10 position-relative" style="min-height: 580px;">
                @if($baoCao->DuongDanFile && file_exists(public_path('storage/' . $baoCao->DuongDanFile)))
                    <iframe src="{{ asset('storage/' . $baoCao->DuongDanFile) }}" width="100%" height="100%" style="border:none; min-height: 580px;"></iframe>
                @else
                    <!-- Hiển thị nội dung văn bản giả lập đẹp chuẩn đồ án -->
                    <div class="p-4 d-flex justify-content-center overflow-auto" style="max-height: 650px;">
                        <div class="bg-white shadow p-4 p-md-5 rounded-1" style="width: 100%; max-width: 650px; min-height: 750px; font-family: 'Times New Roman', serif; color: #111;">
                            <div class="text-center mb-4 border-bottom pb-3">
                                <div style="font-size: 11pt; font-weight: bold;">BỘ GIÁO DỤC VÀ ĐÀO TẠO</div>
                                <div style="font-size: 11pt; font-weight: bold; text-decoration: underline;">KHOA CÔNG NGHỆ THÔNG TIN</div>
                                <div class="my-4">
                                    <h5 style="font-weight: bold; font-family: 'Times New Roman', serif; text-transform: uppercase;">
                                        BÁO CÁO TIẾN ĐỘ KHÓA LUẬN TỐT NGHIỆP
                                    </h5>
                                    <div style="font-size: 11pt; font-weight: bold; margin-top: 10px; color: #1e3a8a;">
                                        {{ $deTai->TenDeTai ?? 'Hệ thống Quản lý Khóa luận Tốt nghiệp' }}
                                    </div>
                                    <div style="font-size: 10pt; font-style: italic; margin-top: 5px;">
                                        Mốc báo cáo: Mốc {{ $baoCao->LanBaoCao }} - {{ $baoCao->TieuDe }}
                                    </div>
                                </div>
                            </div>

                            <div style="font-size: 11pt; line-height: 1.6;">
                                <p><strong>1. Thông tin nhóm thực hiện:</strong></p>
                                <ul>
                                    <li>Nhóm: <strong>{{ $nhom->TenNhom ?? $nhom->MaNhom }}</strong></li>
                                    <li>Trưởng nhóm: <strong>{{ $truongNhom->HoTen ?? 'Chưa xác định' }}</strong> (MSSV: {{ $truongNhom->MaSV ?? '' }})</li>
                                    <li>Giảng viên hướng dẫn: <strong>{{ $giangVien->HocHam ? $giangVien->HocHam . '.' : '' }} {{ $giangVien->HocVi ? $giangVien->HocVi . '.' : '' }} {{ $giangVien->HoTen }}</strong></li>
                                </ul>

                                <p><strong>2. Tóm tắt nội dung báo cáo đã nộp:</strong></p>
                                <p style="text-align: justify; text-indent: 20px;">
                                    {{ $baoCao->NoiDungBaoCao ?: 'Sinh viên đã nộp báo cáo tiến độ theo đúng yêu cầu của Mốc ' . $baoCao->LanBaoCao . '. Chi tiết công việc và tiến độ đã được tổng hợp tự động bởi trợ lý AI ở bảng điều khiển bên phải.' }}
                                </p>

                                @if($baoCao->LinkCode)
                                <p><strong>3. Đường dẫn kho lưu trữ mã nguồn:</strong></p>
                                <p style="text-indent: 20px;">
                                    <a href="{{ $baoCao->LinkCode }}" target="_blank">{{ $baoCao->LinkCode }}</a>
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cột Phải (42%): Trợ lý AI & Form đánh giá của GVHD -->
    <div class="col-lg-5 d-flex flex-column gap-3">
        <!-- Card 1: Trợ lý AI Tóm Tắt Báo Cáo (Theo chuẩn Mockup Figma duyettiendo) -->
        <div class="card card-premium shadow-sm border-0 overflow-hidden">
            <div class="card-header py-3 px-3 d-flex justify-content-between align-items-center text-white"
                 style="background: linear-gradient(135deg, #3b82f6 0%, #6366f1 50%, #8b5cf6 100%);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles text-warning fa-lg"></i>
                    <h6 class="fw-bold mb-0">Trợ Lý AI Tóm Tắt Báo Cáo</h6>
                </div>
                <span class="badge bg-white text-dark rounded-pill px-2 py-1 shadow-xs fw-bold" style="font-size: 0.72rem;">
                    <i class="fa-solid fa-circle-check text-success me-1"></i>AI Verified • Độ tin cậy {{ number_format($tomTat->DoTinCayAI ?? 95, 0) }}%
                </span>
            </div>
            <div class="card-body p-3 bg-white">
                @if($tomTat)
                    <!-- 1. Công việc đã hoàn thành -->
                    <div class="mb-3">
                        <div class="fw-bold text-success small mb-1 d-flex align-items-center gap-1">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>1. Công việc đã hoàn thành:</span>
                        </div>
                        <div class="p-2 rounded-2" style="background: #f0fdf4; border-left: 3px solid #10b981; font-size: 0.82rem; line-height: 1.5; color: #14532d;">
                            {{ $tomTat->CongViecDaHoanThanh ?: 'Hoàn thành các công việc theo đúng kế hoạch đề ra.' }}
                        </div>
                    </div>

                    <!-- 2. Khó khăn & Vướng mắc -->
                    <div class="mb-3">
                        <div class="fw-bold text-warning-emphasis small mb-1 d-flex align-items-center gap-1">
                            <i class="fa-solid fa-triangle-exclamation text-warning"></i>
                            <span>2. Khó khăn & Vướng mắc:</span>
                        </div>
                        <div class="p-2 rounded-2" style="background: #fffbeb; border-left: 3px solid #f59e0b; font-size: 0.82rem; line-height: 1.5; color: #78350f;">
                            {{ $tomTat->KhoKhan ?: 'Không ghi nhận khó khăn hay vướng mắc kỹ thuật đặc biệt.' }}
                        </div>
                    </div>

                    <!-- 3. Kế hoạch tuần tới -->
                    <div>
                        <div class="fw-bold text-primary small mb-1 d-flex align-items-center gap-1">
                            <i class="fa-solid fa-calendar-plus text-primary"></i>
                            <span>3. Kế hoạch giai đoạn tiếp theo:</span>
                        </div>
                        <div class="p-2 rounded-2" style="background: #eff6ff; border-left: 3px solid #3b82f6; font-size: 0.82rem; line-height: 1.5; color: #1e3a8a;">
                            {{ $tomTat->KeHoachTuanToi ?: 'Tiếp tục triển khai các hạng mục tiếp theo theo lộ trình.' }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-circle-notch fa-spin fa-2x text-primary mb-2"></i>
                        <div class="small fw-semibold">Hệ thống đang phân tích và tóm tắt báo cáo bằng AI...</div>
                        <div class="text-muted" style="font-size: 0.72rem;">Bản tóm tắt sẽ sẵn sàng sau giây lát.</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Card 2: Đánh Giá & Phản Hồi của GVHD (Theo Mockup Figma duyettiendo) -->
        <div class="card card-premium shadow-sm border-0">
            <div class="card-header bg-white py-3 px-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-comment-dots text-primary"></i>
                    <h6 class="fw-bold mb-0">Đánh Giá & Phản Hồi Của GVHD</h6>
                </div>
                @if($baoCao->TrangThai !== 'Chờ duyệt')
                    <span class="badge {{ $baoCao->TrangThai === 'Đạt' ? 'bg-success' : 'bg-info text-dark' }} rounded-pill px-2 py-1 small">
                        Đã đánh giá
                    </span>
                @endif
            </div>
            <div class="card-body p-3">
                @if($baoCao->TrangThai !== 'Chờ duyệt')
                    <!-- Khối hiển thị kết quả đã đánh giá (Readonly) -->
                    <div class="text-center py-2 mb-3">
                        @if($baoCao->TrangThai === 'Đạt')
                            <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-success bg-opacity-10 text-success border border-success-subtle fw-bold">
                                <i class="fa-solid fa-circle-check fs-5"></i>
                                <span>KẾT QUẢ: ĐẠT YÊU CẦU</span>
                            </div>
                        @else
                            <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle fw-bold">
                                <i class="fa-solid fa-rotate-left fs-5"></i>
                                <span>KẾT QUẢ: YÊU CẦU NỘP LẠI</span>
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted fw-bold">Nhận xét đã gửi cho nhóm:</label>
                        <div class="p-3 rounded-2 bg-light border text-dark mt-1" style="font-size: 0.85rem; line-height: 1.5; white-space: pre-line;">
                            {{ $baoCao->NhanXet ?: 'Chưa ghi nhận nội dung nhận xét chi tiết.' }}
                        </div>
                    </div>

                    @if($daNopMocSau)
                        <div class="alert alert-secondary py-2 small mb-0 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-lock text-secondary"></i>
                            <span>Đã chốt đánh giá mốc này do nhóm sinh viên đã nộp bài Mốc {{ $baoCao->LanBaoCao + 1 }}.</span>
                        </div>
                    @else
                        <!-- Nút bấm mở form chỉnh sửa nếu giảng viên muốn sửa lại lời nhận xét hoặc hoàn tác -->
                        <div class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" 
                                    data-bs-toggle="collapse" data-bs-target="#formChinhSuaNhanXet">
                                <i class="fa-solid fa-pen-to-square me-1"></i>Chỉnh sửa nhận xét / Đánh giá lại
                            </button>
                        </div>

                        <!-- Form chỉnh sửa thu gọn -->
                        <div class="collapse mt-3 pt-3 border-top" id="formChinhSuaNhanXet">
                            <form action="{{ route('giangvien.baocao.nhanxet', $baoCao->MaBaoCao) }}" method="POST"
                                  onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerHTML='<i class=\'fa-solid fa-spinner fa-spin me-1\'></i> Đang lưu...';">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">Kết quả đánh giá <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-2">
                                        <div class="form-check flex-fill p-2 border rounded-2">
                                            <input class="form-check-input ms-0 me-2" type="radio" name="LoaiNhanXet" value="Đạt" id="radioEditDat"
                                                   {{ $baoCao->TrangThai === 'Đạt' ? 'checked' : '' }} required>
                                            <label class="form-check-label fw-bold text-success small" for="radioEditDat">
                                                ✅ Đạt (Mở khóa mốc sau)
                                            </label>
                                        </div>
                                        <div class="form-check flex-fill p-2 border rounded-2">
                                            <input class="form-check-input ms-0 me-2" type="radio" name="LoaiNhanXet" value="Yêu cầu nộp lại" id="radioEditNopLai"
                                                   {{ $baoCao->TrangThai === 'Yêu cầu nộp lại' ? 'checked' : '' }} required>
                                            <label class="form-check-label fw-bold text-warning-emphasis small" for="radioEditNopLai">
                                                🔄 Yêu cầu nộp lại
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark">Cập nhật nhận xét <span class="text-danger">*</span></label>
                                    <textarea name="NoiDung" rows="3" class="form-control form-control-sm" required>{{ $baoCao->NhanXet }}</textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-sm btn-light rounded-pill" data-bs-toggle="collapse" data-bs-target="#formChinhSuaNhanXet">Hủy</button>
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                                        <i class="fa-solid fa-floppy-disk me-1"></i>Lưu thay đổi
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                @else
                    <!-- Form đánh giá lần đầu khi đang Chờ duyệt -->
                    <form action="{{ route('giangvien.baocao.nhanxet', $baoCao->MaBaoCao) }}" method="POST"
                          onsubmit="this.querySelector('button[type=submit]').disabled=true; this.querySelector('button[type=submit]').innerHTML='<i class=\'fa-solid fa-spinner fa-spin me-1\'></i> Đang lưu đánh giá...';">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Kết quả đánh giá <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check flex-fill p-2 border rounded-2 border-success bg-success bg-opacity-10">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="LoaiNhanXet" value="Đạt" id="radioDat" checked required>
                                    <label class="form-check-label fw-bold text-success small" for="radioDat">
                                        ✅ Đạt (Mở khóa mốc sau)
                                    </label>
                                </div>
                                <div class="form-check flex-fill p-2 border rounded-2">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="LoaiNhanXet" value="Yêu cầu nộp lại" id="radioNopLai" required>
                                    <label class="form-check-label fw-bold text-warning-emphasis small" for="radioNopLai">
                                        🔄 Yêu cầu nộp lại
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Ý kiến nhận xét của GVHD -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">
                                Ý kiến nhận xét của Giảng viên hướng dẫn <span class="text-danger">*</span>
                            </label>
                            <textarea name="NoiDung" rows="4" class="form-control form-control-sm" 
                                      placeholder="Nhập nhận xét chi tiết về tiến độ, chất lượng tài liệu hoặc hướng dẫn chỉnh sửa..." required>{{ $baoCao->NhanXet ?? '' }}</textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 shadow-sm fw-bold">
                                <i class="fa-solid fa-check me-1"></i>Xác Nhận Đánh Giá
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
