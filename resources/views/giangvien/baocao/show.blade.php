@extends('layouts.giangvien')

@section('page_title', 'Phê Duyệt Chi Tiết Tiến Độ — GĐ ' . $baoCao->LanBaoCao . ' (' . ($nhom->TenNhom ?? $nhom->MaNhom) . ')')

@section('content')
<div class="gv-review-container">

    {{-- ── 1. HEADER TOP: ĐIỀU HƯỚNG & METADATA ── --}}
    <div class="card border-0 shadow-sm rounded-3 bg-white p-3 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('giangvien.baocao.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
                    <i class="fa-solid fa-arrow-left me-1"></i>Danh sách nhóm
                </a>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 fw-bold font-monospace">
                            {{ $nhom->MaNhom }}
                        </span>
                        <span class="fw-bold fs-6 text-dark">
                            {{ $nhom->TenNhom ?: 'Nhóm Khóa Luận' }}
                        </span>
                        <span class="badge {{ $baoCao->TrangThai === 'Đạt' ? 'bg-success' : ($baoCao->TrangThai === 'Chờ duyệt' ? 'bg-warning text-dark' : 'bg-info text-dark') }} rounded-pill px-2.5 py-1 fw-bold">
                            {{ $baoCao->TrangThai }}
                        </span>
                    </div>
                    <div class="text-primary fw-semibold small mt-0.5">
                        <i class="fa-solid fa-folder-open me-1"></i>{{ $deTai->TenDeTai ?? 'Chưa có tên đề tài' }}
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="text-end d-none d-md-block small" style="font-size: 12px;">
                    <div><i class="fa-solid fa-crown text-warning me-1"></i>Trưởng nhóm: <strong>{{ $truongNhom->HoTen ?? 'Chưa xác định' }}</strong> ({{ $truongNhom->MaSV ?? '' }})</div>
                    <div class="text-muted"><i class="fa-solid fa-users me-1"></i>Thành viên: {{ $thanhViens->count() }} sinh viên</div>
                </div>

                {{-- Nút nhảy nhanh tới Form Nghiệm thu --}}
                @if($baoCao->TrangThai !== 'Đạt')
                    <a href="#section-xacnhan-giaidoan" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-bold shadow-xs">
                        <i class="fa-solid fa-certificate me-1"></i>Nghiệm Thu Giai Đoạn {{ $baoCao->LanBaoCao }}
                    </a>
                @endif
            </div>
        </div>

        {{-- ── MINI STEPPER: LỘ TRÌNH 6 GIAI ĐOẠN ── --}}
        <div class="border-top mt-3 pt-2.5">
            <div class="d-flex justify-content-between align-items-center mb-1.5">
                <span class="text-muted small fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-timeline text-primary me-1"></i>Lộ trình 6 giai đoạn tiến độ:
                </span>
                <span class="text-secondary small" style="font-size: 11px;">
                    Đang xem: <strong>Giai đoạn {{ $baoCao->LanBaoCao }}</strong>
                </span>
            </div>

            @php
                $stageNames = [
                    1 => 'Đề cương & CSDL',
                    2 => 'HT & Chức năng',
                    3 => 'Kiểm thử & Dự thảo',
                    4 => 'Hồ sơ bảo vệ',
                    5 => 'Bảo vệ Hội đồng',
                    6 => 'Kết quả & Nộp lại',
                ];
            @endphp

            <div class="row g-1 text-center">
                @foreach($stageNames as $stNum => $stName)
                    @php
                        $stBc = $allBaoCaos[$stNum] ?? null;
                        $isCurrent = ($baoCao->LanBaoCao == $stNum);
                        $isPassed = ($stBc && $stBc->TrangThai === 'Đạt');
                    @endphp
                    <div class="col-2">
                        <div class="p-1.5 rounded-2 border {{ $isCurrent ? 'border-primary bg-primary bg-opacity-10 shadow-xs' : ($isPassed ? 'border-success-subtle bg-success-subtle bg-opacity-25' : 'bg-light') }}">
                            <div class="fw-bold text-truncate {{ $isCurrent ? 'text-primary' : ($isPassed ? 'text-success' : 'text-secondary') }}" style="font-size: 11px;">
                                GĐ {{ $stNum }}: {{ $stName }}
                            </div>
                            <div class="mt-0.5">
                                @if($isPassed)
                                    <span class="badge bg-success rounded-pill" style="font-size: 9.5px;">✓ Đạt</span>
                                @elseif($isCurrent)
                                    <span class="badge bg-primary text-white rounded-pill" style="font-size: 9.5px;">Đang duyệt</span>
                                @elseif($stBc)
                                    <span class="badge bg-warning text-dark rounded-pill" style="font-size: 9.5px;">{{ $stBc->TrangThai }}</span>
                                @else
                                    <span class="badge bg-light text-secondary border rounded-pill" style="font-size: 9.5px;">Chưa nộp</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── 2. THÂN MÀN HÌNH SPLIT-LAYOUT (2 CỘT CHUYÊN NGHIỆP) ── --}}
    <div class="row g-3">

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- ── CỘT TRÁI (62%): DANH SÁCH BÁO CÁO NHỎ & FORM DUYỆT CHI TIẾT ── --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        <div class="col-lg-7 col-xl-8">

            {{-- Thông báo session --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3 rounded-3 shadow-xs" role="alert" style="font-size: 13px;">
                    <i class="fa-solid fa-circle-check me-1.5"></i>{{ session('success') }}
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3 rounded-3 shadow-xs" role="alert" style="font-size: 13px;">
                    <i class="fa-solid fa-circle-exclamation me-1.5"></i>{{ session('error') }}
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- DANH SÁCH CÁC KHỐI NỘP BÀI (SUB-REPORTS) --}}
            @if($baoCao->khoiBaoCaos && $baoCao->khoiBaoCaos->isNotEmpty())
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-layer-group text-primary"></i>
                        <span>Chi Tiết Các Báo Cáo Nhỏ ({{ $baoCao->khoiBaoCaos->count() }} đợt nộp)</span>
                    </h6>
                    <span class="text-muted small" style="font-size: 11.5px;">
                        Mỗi đợt nộp có thể đánh giá và nhận xét độc lập
                    </span>
                </div>

                @foreach($baoCao->khoiBaoCaos as $index => $khoi)
                    @php
                        $isLatest = ($khoi->LanNop === $baoCao->khoiBaoCaos->max('LanNop'));
                        $isPassedKhoi = ($khoi->TrangThai === 'Đạt');
                    @endphp
                    <div class="card border shadow-sm rounded-3 bg-white mb-3 overflow-hidden {{ $isLatest ? 'border-primary' : '' }}">
                        {{-- Card Header khối --}}
                        <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $isLatest ? 'bg-primary' : 'bg-secondary' }} text-white rounded-pill px-2.5 py-1 fw-bold">
                                    Đợt nộp #{{ $khoi->LanNop }}
                                </span>
                                <span class="fw-bold text-dark fs-6">{{ $khoi->TieuDe }}</span>
                                @if($isLatest)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill" style="font-size: 10px;">
                                        Mới nhất
                                    </span>
                                @endif
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small" style="font-size: 11px;">
                                    <i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($khoi->NgayNop)->format('H:i - d/m/Y') }}
                                </span>
                                <span class="badge {{ $isPassedKhoi ? 'bg-success' : ($khoi->TrangThai === 'Chờ duyệt' ? 'bg-warning text-dark' : 'bg-danger text-white') }} rounded-pill px-2.5 py-1 fw-bold">
                                    {{ $khoi->TrangThai }}
                                </span>
                            </div>
                        </div>

                        {{-- Card Body nội dung khối --}}
                        <div class="card-body p-3">
                            {{-- 4 mục nghiệp vụ chuẩn --}}
                            <div class="row g-2 mb-3">
                                <div class="col-12">
                                    <div class="p-2.5 rounded-2 bg-light border">
                                        <div class="fw-bold text-success mb-1" style="font-size: 12.5px;">
                                            <i class="fa-solid fa-circle-check me-1"></i>1. Kết quả đạt được &amp; Công việc đã làm:
                                        </div>
                                        <div class="text-dark small" style="white-space: pre-line; line-height: 1.5;">
                                            {{ $khoi->KetQuaDatDuoc ?: ($khoi->NoiDungBaoCao ?: 'Chưa cập nhật nội dung') }}
                                        </div>
                                    </div>
                                </div>

                                @if($khoi->KhoKhan)
                                <div class="col-md-6">
                                    <div class="p-2.5 rounded-2 bg-light border h-100">
                                        <div class="fw-bold text-warning-emphasis mb-1" style="font-size: 12.5px;">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i>2. Khó khăn gặp phải:
                                        </div>
                                        <div class="text-dark small" style="white-space: pre-line;">{{ $khoi->KhoKhan }}</div>
                                    </div>
                                </div>
                                @endif

                                @if($khoi->HuongGiaiQuyet)
                                <div class="col-md-6">
                                    <div class="p-2.5 rounded-2 bg-light border h-100">
                                        <div class="fw-bold text-info mb-1" style="font-size: 12.5px;">
                                            <i class="fa-solid fa-lightbulb me-1"></i>3. Hướng giải quyết đề xuất:
                                        </div>
                                        <div class="text-dark small" style="white-space: pre-line;">{{ $khoi->HuongGiaiQuyet }}</div>
                                    </div>
                                </div>
                                @endif

                                @if($khoi->KeHoachTiepTheo)
                                <div class="col-12">
                                    <div class="p-2.5 rounded-2 bg-light border">
                                        <div class="fw-bold text-primary mb-1" style="font-size: 12.5px;">
                                            <i class="fa-solid fa-calendar-check me-1"></i>4. Kế hoạch giai đoạn tiếp theo:
                                        </div>
                                        <div class="text-dark small" style="white-space: pre-line;">{{ $khoi->KeHoachTiepTheo }}</div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            {{-- Minh chứng file đính kèm & Source code --}}
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-3 p-2 bg-light rounded-2 border">
                                <span class="text-muted small fw-semibold" style="font-size: 11.5px;">
                                    <i class="fa-solid fa-paperclip me-1"></i>Minh chứng:
                                </span>
                                @if($khoi->DuongDanFile)
                                    <a href="{{ asset('storage/' . $khoi->DuongDanFile) }}" target="_blank" class="btn btn-xs btn-outline-danger rounded-pill px-2.5 py-1">
                                        <i class="fa-solid fa-file-pdf me-1"></i>{{ $khoi->TenFile ?: 'BaoCao_TienDo.pdf' }} (Mở xem)
                                    </a>
                                    <a href="{{ asset('storage/' . $khoi->DuongDanFile) }}" download class="btn btn-xs btn-light border rounded-pill px-2 py-1 text-secondary">
                                        <i class="fa-solid fa-download me-1"></i>Tải về
                                    </a>
                                @else
                                    <span class="text-muted small fst-italic">Không đính kèm file PDF.</span>
                                @endif

                                @if($khoi->LinkCode)
                                    <a href="{{ $khoi->LinkCode }}" target="_blank" class="btn btn-xs btn-outline-dark rounded-pill px-2.5 py-1">
                                        <i class="fa-brands fa-github me-1"></i>Source Code
                                    </a>
                                @endif
                            </div>

                            {{-- Đánh giá trước đó nếu có --}}
                            @if($khoi->NhanXetGV)
                                <div class="p-2.5 rounded-2 bg-light border-start border-3 {{ $isPassedKhoi ? 'border-success' : 'border-warning' }} mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark small" style="font-size: 12px;">
                                            <i class="fa-solid fa-comment-dots text-primary me-1"></i>Nhận xét của bạn:
                                        </span>
                                        <span class="text-muted" style="font-size: 11px;">
                                            {{ $khoi->NgayNhanXet ? \Carbon\Carbon::parse($khoi->NgayNhanXet)->format('H:i - d/m/Y') : '' }}
                                        </span>
                                    </div>
                                    <div class="text-dark small" style="white-space: pre-line;"><em>"{{ $khoi->NhanXetGV }}"</em></div>
                                </div>
                            @endif

                            {{-- ── FORM ĐÁNH GIÁ TRỰC TIẾP TẠI CHỖ CHO KHỐI NÀY ── --}}
                            <div class="p-3 rounded-3 bg-white border border-primary border-opacity-25 shadow-xs">
                                <form action="{{ route('giangvien.baocao.nhanxet_khoi', $khoi->MaKhoi) }}" method="POST">
                                    @csrf
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                        <label class="form-label fw-bold text-dark mb-0 small" style="font-size: 12.5px;">
                                            <i class="fa-solid fa-pen-to-square text-primary me-1"></i>Đánh giá đợt nộp #{{ $khoi->LanNop }}:
                                        </label>

                                        {{-- Nút chọn Đạt / Yêu cầu nộp lại --}}
                                        <div class="btn-group btn-group-sm" role="group">
                                            <input type="radio" class="btn-check" name="LoaiNhanXet" id="opt_dat_{{ $khoi->MaKhoi }}" value="Đạt" {{ $khoi->TrangThai === 'Đạt' || !$khoi->NhanXetGV ? 'checked' : '' }}>
                                            <label class="btn btn-outline-success px-3 fw-bold" for="opt_dat_{{ $khoi->MaKhoi }}">
                                                ✓ Đạt yêu cầu
                                            </label>

                                            <input type="radio" class="btn-check" name="LoaiNhanXet" id="opt_nop_lai_{{ $khoi->MaKhoi }}" value="Yêu cầu nộp lại" {{ $khoi->TrangThai === 'Yêu cầu nộp lại' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-warning text-dark px-3 fw-bold" for="opt_nop_lai_{{ $khoi->MaKhoi }}">
                                                🔄 Yêu cầu nộp lại
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <textarea name="NoiDung" class="form-control form-control-sm" rows="2" 
                                                  placeholder="Nhập nhận xét chuyên môn, góp ý sửa đổi hoặc hướng dẫn tiếp theo cho sinh viên..." required>{{ old('NoiDung', $khoi->NhanXetGV) }}</textarea>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 py-1 fw-bold shadow-xs">
                                            <i class="fa-solid fa-floppy-disk me-1"></i>Lưu Đánh Giá Đợt #{{ $khoi->LanNop }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                @endforeach
            @else
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4 text-center text-muted mb-3">
                    <i class="fa-regular fa-folder-open fa-3x mb-2 text-secondary"></i>
                    <h6 class="fw-bold">Chưa có bản nộp báo cáo nhỏ nào trong giai đoạn này</h6>
                    <p class="small text-muted mb-0">Khi sinh viên tạo và nộp các báo cáo nhỏ, danh sách chi tiết sẽ hiển thị ở đây để bạn phê duyệt.</p>
                </div>
            @endif

            {{-- ── FORM NGHIỆM THU TOÀN BỘ GIAI ĐOẠN (XÁC NHẬN MỞ KHÓA GIAI ĐOẠN TIẾP THEO) ── --}}
            <div class="card border-0 shadow-sm rounded-3 bg-white mb-4 overflow-hidden" id="section-xacnhan-giaidoan">
                <div class="card-header py-3 px-3 text-white" style="background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-certificate fa-lg"></i>
                            <h6 class="fw-bold mb-0">Xác Nhận Nghiệm Thu Toàn Bộ Giai Đoạn {{ $baoCao->LanBaoCao }}</h6>
                        </div>
                        <span class="badge bg-white text-success rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px;">
                            Điều kiện tiên quyết GĐ {{ $baoCao->LanBaoCao + 1 }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    @if($baoCao->TrangThai === 'Đạt')
                        <div class="alert alert-success d-flex align-items-center gap-3 py-3 mb-0 rounded-3">
                            <i class="fa-solid fa-circle-check fa-2x text-success"></i>
                            <div>
                                <div class="fw-bold fs-6">Giai đoạn {{ $baoCao->LanBaoCao }} đã được nghiệm thu ĐẠT 100%!</div>
                                <div class="small mt-0.5">
                                    Xác nhận bởi: <strong>{{ $baoCao->NguoiXacNhan ?: $giangVien->HoTen }}</strong> 
                                    @if($baoCao->NgayXacNhan)
                                        vào lúc {{ \Carbon\Carbon::parse($baoCao->NgayXacNhan)->format('H:i - d/m/Y') }}
                                    @endif
                                </div>
                                <div class="text-secondary small mt-1"><em>"{{ $baoCao->NhanXet ?: 'Hoàn thành đạt yêu cầu giai đoạn.' }}"</em></div>
                            </div>
                        </div>
                    @else
                        <form action="{{ route('giangvien.baocao.xacnhan_giaidoan', $baoCao->MaBaoCao) }}" method="POST"
                              onsubmit="return confirm('Bạn có chắc chắn muốn nghiệm thu Đạt giai đoạn {{ $baoCao->LanBaoCao }}? Nhóm sinh viên sẽ đủ điều kiện chuyển sang giai đoạn tiếp theo.');">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small">
                                    Tiến độ hoàn thành giai đoạn (%):
                                </label>
                                <div class="input-group input-group-sm" style="max-width: 160px;">
                                    <input type="number" name="PhanTramHoanThanh" class="form-control fw-bold text-primary" value="100" min="50" max="100" required>
                                    <span class="input-group-text bg-light fw-bold">%</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small">
                                    Nhận xét tổng kết nghiệm thu Giai đoạn {{ $baoCao->LanBaoCao }}:
                                </label>
                                <textarea name="NhanXetTongQuat" class="form-control form-control-sm" rows="3" 
                                          placeholder="Đánh giá tổng quan về chất lượng đồ án, tiến độ hoàn thành và đồng ý cho nhóm chuyển sang giai đoạn tiếp theo..." required>Giảng viên hướng dẫn xác nhận nhóm hoàn thành đạt yêu cầu giai đoạn {{ $baoCao->LanBaoCao }}. Cho phép nhóm chuyển sang giai đoạn tiếp theo.</textarea>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="text-muted small" style="font-size: 12px;">
                                    <i class="fa-solid fa-shield-halved text-success me-1"></i>Hệ thống sẽ lưu vết Giảng viên xác nhận và thời điểm phê duyệt chính thức.
                                </span>
                                <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 py-1.5 fw-bold shadow-xs">
                                    <i class="fa-solid fa-check-double me-1.5"></i>Xác Nhận Nghiệm Thu GĐ {{ $baoCao->LanBaoCao }}
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

        </div>

        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        {{-- ── CỘT PHẢI (38%): TRỢ LÝ AI ĐỐI CHIẾU & TỰ ĐỘNG TỔNG HỢP ── --}}
        {{-- ═════════════════════════════════════════════════════════════════════ --}}
        <div class="col-lg-5 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white sticky-top" style="top: 80px; z-index: 10;">
                <div class="card-header py-3 px-3 text-white d-flex justify-content-between align-items-center"
                     style="background: linear-gradient(135deg, #1e3a8a 0%, #0072ce 100%);">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-wand-magic-sparkles text-warning fa-lg"></i>
                        <h6 class="fw-bold mb-0">Trợ Lý AI Map-Reduce</h6>
                    </div>
                    <span class="badge bg-white text-dark rounded-pill px-2.5 py-1 fw-bold shadow-xs" style="font-size: 11px;">
                        <i class="fa-solid fa-circle-check text-success me-1"></i>{{ $tomTat->DoTinCayAI ?? 95 }}% tin cậy
                    </span>
                </div>

                <div class="card-body p-3" style="max-height: calc(100vh - 160px); overflow-y: auto;">
                    @php
                        $tomTatData = $tomTat?->TomTatJson ?? null;
                    @endphp

                    {{-- 1. Tổng quan phân tích --}}
                    <div class="mb-3">
                        <div class="fw-bold text-primary small mb-1.5 d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span>1. Tổng quan phân tích:</span>
                        </div>
                        <div class="p-2.5 rounded-2 bg-light border text-dark small" style="line-height: 1.5; font-size: 12.5px;">
                            {{ $tomTat?->NoiDungAI ?: ($tomTat?->CongViecDaHoanThanh ?: 'AI đã tự động trích xuất các hạng mục công việc chính của nhóm từ báo cáo tiến độ.') }}
                        </div>
                    </div>

                    {{-- 2. Hạng mục công việc tìm thấy trong tài liệu --}}
                    <div class="mb-3">
                        <div class="fw-bold text-success small mb-1.5 d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>2. Công việc hoàn thành (kèm trích dẫn):</span>
                        </div>
                        @if(!empty($tomTatData['data']['completed_work']))
                            <div class="list-group list-group-flush border rounded-2 bg-white" style="max-height: 220px; overflow-y: auto;">
                                @foreach($tomTatData['data']['completed_work'] as $item)
                                    <div class="list-group-item p-2" style="font-size: 12px;">
                                        <div class="d-flex justify-content-between align-items-start gap-1">
                                            <span class="fw-semibold text-dark">{{ $item['content'] ?? '' }}</span>
                                            @if(!empty($item['source_pages']))
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill text-nowrap" style="font-size: 10px;">
                                                    Trang {{ implode(', ', (array)$item['source_pages']) }}
                                                </span>
                                            @endif
                                        </div>
                                        @if(!empty($item['evidence']))
                                            <div class="text-muted mt-0.5 fst-italic" style="font-size: 11px;">
                                                "{{ Str::limit($item['evidence'], 90) }}"
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-2.5 rounded-2 bg-light border text-dark small" style="white-space: pre-line; font-size: 12px;">
                                {{ $tomTat?->CongViecDaHoanThanh ?: 'Đã hoàn thành các hạng mục theo kế hoạch giai đoạn.' }}
                            </div>
                        @endif
                    </div>

                    {{-- 3. Khó khăn & Rào cản phát hiện --}}
                    <div class="mb-3">
                        <div class="fw-bold text-warning-emphasis small mb-1.5 d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-triangle-exclamation text-warning"></i>
                            <span>3. Khó khăn & Vướng mắc:</span>
                        </div>
                        <div class="p-2.5 rounded-2 bg-light border text-dark small" style="white-space: pre-line; font-size: 12px;">
                            {{ $tomTat?->KhoKhan ?: 'Không ghi nhận vướng mắc kỹ thuật đặc biệt nào trong tài liệu nộp.' }}
                        </div>
                    </div>

                    {{-- 4. Kế hoạch tiếp theo trích xuất --}}
                    @if($tomTat?->KeHoachTuanToi)
                    <div class="mb-3">
                        <div class="fw-bold text-info small mb-1.5 d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-calendar-check text-info"></i>
                            <span>4. Kế hoạch giai đoạn tiếp theo:</span>
                        </div>
                        <div class="p-2.5 rounded-2 bg-light border text-dark small" style="white-space: pre-line; font-size: 12px;">
                            {{ $tomTat->KeHoachTuanToi }}
                        </div>
                    </div>
                    @endif

                    {{-- Gợi ý kiểm tra đối chiếu --}}
                    <div class="p-2.5 rounded-2 border border-info border-opacity-25 bg-info-subtle bg-opacity-25 text-info-emphasis small" style="font-size: 11.5px; line-height: 1.5;">
                        <i class="fa-solid fa-lightbulb text-warning me-1"></i>
                        <strong>Mẹo đối chiếu:</strong> Bạn có thể so sánh giữa nội dung sinh viên tự khai báo ở cột trái và kết quả AI trích xuất từ file PDF ở đây để quyết định đánh giá <strong>Đạt</strong> hay <strong>Yêu cầu nộp lại</strong>.
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<style>
.gv-review-container {
    max-width: 1400px;
    margin: 0 auto;
}
.btn-xs {
    padding: 2px 8px;
    font-size: 11px;
    line-height: 1.5;
}
</style>
@endsection
