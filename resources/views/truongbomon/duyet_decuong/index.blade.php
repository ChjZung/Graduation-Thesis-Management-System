@extends('layouts.truongbomon')

@section('page_title', 'Duyệt Đề Cương Đề Tài - Bộ Môn ' . ($boMon->TenBoMon ?? ''))

@section('content')
<div class="container-fluid px-0">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-file-circle-check text-primary me-2"></i>
                Phê Duyệt Đề Cương Chi Tiết & Công Bố Đề Tài
            </h4>
            <p class="text-muted mb-0 small">
                Thẩm tra kết quả đánh giá từ Giảng viên phản biện, kiểm tra nhanh nội dung đề cương và tiến hành phê duyệt công bố cho sinh viên đăng ký.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('truongbomon.phancong.index') }}" class="btn btn-sm btn-outline-primary rounded-3 px-3 fw-semibold">
                <i class="fa-solid fa-user-plus me-1"></i> Phân công phản biện
            </a>
            <a href="{{ route('truongbomon.duyet_detai.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Duyệt đề xuất đề tài
            </a>
        </div>
    </div>

    <!-- Filter & Tabs Card -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <!-- Navigation Tabs -->
            <ul class="nav nav-pills flex-column flex-sm-row gap-1 mb-3">
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ $statusTab === 'cho_duyet' ? 'active bg-success text-white' : '' }}" 
                       href="{{ route('truongbomon.duyet_decuong.index', array_merge(request()->except('tab', 'page'), ['tab' => 'cho_duyet'])) }}">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i> Chờ TBM Duyệt & Công Bố
                        <span class="badge {{ $counts['cho_duyet'] > 0 ? 'bg-danger' : 'bg-light text-dark' }} ms-1">{{ $counts['cho_duyet'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ $statusTab === 'dang_phan_bien' ? 'active bg-primary text-white' : '' }}" 
                       href="{{ route('truongbomon.duyet_decuong.index', array_merge(request()->except('tab', 'page'), ['tab' => 'dang_phan_bien'])) }}">
                        <i class="fa-solid fa-spinner me-1"></i> Đang Phản Biện
                        <span class="badge bg-secondary ms-1">{{ $counts['dang_phan_bien'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ $statusTab === 'yeu_cau_sua' ? 'active bg-warning text-dark' : '' }}" 
                       href="{{ route('truongbomon.duyet_decuong.index', array_merge(request()->except('tab', 'page'), ['tab' => 'yeu_cau_sua'])) }}">
                        <i class="fa-solid fa-wrench me-1"></i> Yêu Cầu Chỉnh Sửa
                        <span class="badge bg-secondary ms-1">{{ $counts['yeu_cau_sua'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ $statusTab === 'da_cong_bo' ? 'active bg-info text-white' : '' }}" 
                       href="{{ route('truongbomon.duyet_decuong.index', array_merge(request()->except('tab', 'page'), ['tab' => 'da_cong_bo'])) }}">
                        <i class="fa-solid fa-bullhorn me-1"></i> Đã Công Bố
                        <span class="badge bg-secondary ms-1">{{ $counts['da_cong_bo'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ $statusTab === 'ALL' ? 'active bg-dark text-white' : '' }}" 
                       href="{{ route('truongbomon.duyet_decuong.index', array_merge(request()->except('tab', 'page'), ['tab' => 'ALL'])) }}">
                        Tất Cả Đề Cương <span class="badge bg-secondary ms-1">{{ $counts['total'] }}</span>
                    </a>
                </li>
            </ul>

            <!-- Filter Controls -->
            <form method="GET" action="{{ route('truongbomon.duyet_decuong.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="{{ $statusTab }}">
                
                <div class="col-md-3">
                    <select name="MaHocKy" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="ALL">-- Tất cả học kỳ --</option>
                        @foreach($hocKies as $hk)
                            <option value="{{ $hk->MaHocKy }}" {{ $selectedHocKy == $hk->MaHocKy ? 'selected' : '' }}>
                                {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="MaGV" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Tất cả GV đề xuất --</option>
                        @foreach($giangViens as $g)
                            <option value="{{ $g->MaGV }}" {{ request('MaGV') == $g->MaGV ? 'selected' : '' }}>
                                {{ $g->HoTen }} ({{ $g->MaGV }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Tìm tên đề tài, mã ĐT, GV..." value="{{ request('search') }}">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-2 text-end">
                    <a href="{{ route('truongbomon.duyet_decuong.index', ['tab' => $statusTab]) }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="fa-solid fa-rotate-left me-1"></i> Làm mới
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Box for pending approval -->
    @if($statusTab === 'cho_duyet' && $counts['cho_duyet'] > 0)
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center justify-content-between mb-4 rounded-3 p-3">
        <div class="d-flex align-items-center gap-3">
            <div class="fs-2 text-success">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1 text-success">Có {{ $counts['cho_duyet'] }} đề cương đã được Giảng viên phản biện thẩm định ĐẠT YÊU CẦU!</h6>
                <div class="small text-dark">
                    Trưởng bộ môn vui lòng kiểm tra nhanh nội dung đề cương và nhấn <strong>Phê duyệt & Công bố</strong> để chính thức mở đăng ký cho sinh viên.
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Topics Table -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body p-0">
            @if($detais->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary"></i>
                    <h6>Không có đề tài nào phù hợp với bộ lọc hiện tại.</h6>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">Mã ĐT</th>
                                <th style="min-width: 250px;">Tên Đề Tài & Ngành</th>
                                <th style="min-width: 150px;">GV Đề Xuất</th>
                                <th class="text-center" style="min-width: 130px;">File Đề Cương</th>
                                <th style="min-width: 180px;">Kết Quả Phản Biện</th>
                                <th class="text-center" style="min-width: 130px;">Trạng Thái</th>
                                <th class="text-end" style="min-width: 180px;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detais as $dt)
                                @php
                                    $pb = $dt->phanCongPhanBiens->where('VaiTro', 'Phản biện đề cương')->first();
                                    $isNopLai = $pb && ($pb->KetQua === 'Đã nộp lại' || $pb->TrangThai === 'Đã nộp lại đề cương' || $dt->TrangThai === 'Đã cập nhật đề cương - Chờ phản biện lại');
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark fw-bold border">{{ $dt->MaDeTai }}</span>
                                        <div class="small text-muted mt-1">{{ $dt->hocKy->TenHocKy ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $dt->TenDeTai }}</div>
                                        <div class="d-flex flex-wrap gap-1 align-items-center mt-1">
                                            <span class="badge bg-primary-subtle text-primary border">
                                                {{ $dt->HocPhan ?? 'Khóa luận tốt nghiệp' }}
                                            </span>
                                            <span class="badge bg-light text-muted border">
                                                <i class="fa-solid fa-users me-1"></i>Tối đa {{ $dt->SoLuongSinhVienToiDa ?? 2 }} SV
                                            </span>
                                            @if($dt->nganh)
                                                <span class="badge bg-light text-secondary border">{{ $dt->nganh->TenNganh }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $dt->giangVien->HoTen ?? 'N/A' }}</div>
                                        <div class="small text-muted">{{ $dt->giangVien->MaGV ?? '' }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($dt->FileDeCuong)
                                            @php
                                                $filePath = Str::startsWith($dt->FileDeCuong, ['http', 'storage/']) ? asset($dt->FileDeCuong) : asset('storage/' . $dt->FileDeCuong);
                                                $fileName = basename($dt->FileDeCuong);
                                            @endphp
                                            <div class="d-flex flex-column gap-1 align-items-center">
                                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-2.5 py-1 shadow-sm"
                                                        onclick="quickPreviewOutline('{{ $filePath }}', '{{ $fileName }}', '{{ addslashes($dt->TenDeTai) }}')"
                                                        title="Xem nhanh đề cương trực tiếp trên web">
                                                    <i class="fa-solid fa-eye me-1"></i> Xem nhanh
                                                </button>
                                                <a href="{{ $filePath }}" target="_blank" class="text-muted small text-decoration-none">
                                                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Tải về
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-danger small"><i class="fa-solid fa-circle-exclamation me-1"></i> Chưa có</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pb)
                                            <div class="fw-semibold text-dark">{{ $pb->giangVien->HoTen ?? 'N/A' }}</div>
                                            <div class="mt-1">
                                                @if($pb->KetQua === 'Đạt')
                                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> ĐẠT YÊU CẦU</span>
                                                @elseif($isNopLai)
                                                    <span class="badge bg-primary"><i class="fa-solid fa-rotate me-1"></i> Đã nộp lại ĐC</span>
                                                @elseif($pb->KetQua === 'Yêu cầu chỉnh sửa')
                                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-pen me-1"></i> Yêu cầu sửa</span>
                                                @elseif($pb->KetQua === 'Không đạt')
                                                    <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> Không đạt</span>
                                                @else
                                                    <span class="badge bg-info text-white"><i class="fa-solid fa-hourglass-start me-1"></i> Đang phản biện</span>
                                                @endif
                                            </div>
                                            @if($pb->NhanXet)
                                                <div class="small text-muted mt-1 text-truncate" style="max-width: 220px;" title="{{ $pb->NhanXet }}">
                                                    <i class="fa-regular fa-comment-dots me-1 text-primary"></i>{{ $pb->NhanXet }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-triangle-exclamation me-1"></i> Chưa phân công</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($dt->TrangThai === 'Đã công bố')
                                            <span class="badge bg-success rounded-pill px-2.5 py-1"><i class="fa-solid fa-bullhorn me-1"></i>Đã công bố</span>
                                        @elseif($dt->TrangThai === 'Đã phản biện - Chờ duyệt BM' || $dt->TrangThai === 'Đã phản biện - Chờ TBM duyệt đề cương')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold">
                                                <i class="fa-solid fa-circle-check me-1"></i>Chờ TBM duyệt
                                            </span>
                                        @elseif($dt->TrangThai === 'Đã cập nhật đề cương - Chờ phản biện lại')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">
                                                <i class="fa-solid fa-rotate me-1"></i>Đã nộp lại ĐC
                                            </span>
                                        @elseif($dt->TrangThai === 'Đang phản biện đề cương')
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1">
                                                <i class="fa-solid fa-spinner me-1"></i>Đang phản biện
                                            </span>
                                        @elseif($dt->TrangThai === 'Yêu cầu chỉnh sửa đề cương')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">
                                                <i class="fa-solid fa-wrench me-1"></i>Yêu cầu sửa ĐC
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1">{{ $dt->TrangThai }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                            @if($pb && $pb->KetQua === 'Đạt' && $dt->TrangThai !== 'Đã công bố')
                                                <!-- Nút Duyệt và công bố -->
                                                <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 fw-bold shadow-sm"
                                                        data-bs-toggle="modal" data-bs-target="#modalDuyetDC{{ $dt->MaDeTai }}">
                                                    <i class="fa-solid fa-bullhorn me-1"></i> Duyệt & Công bố
                                                </button>
                                            @endif

                                            @if($dt->TrangThai !== 'Đã công bố')
                                                <!-- Nút Yêu cầu chỉnh sửa đề cương -->
                                                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2 py-1"
                                                        data-bs-toggle="modal" data-bs-target="#modalYeuCauSuaDC{{ $dt->MaDeTai }}"
                                                        title="Yêu cầu GV chỉnh sửa lại đề cương">
                                                    <i class="fa-solid fa-pen-to-square"></i> Sửa ĐC
                                                </button>
                                            @endif

                                            <a href="{{ route('truongbomon.duyet_detai.show', $dt->MaDeTai) }}" 
                                               class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" 
                                               title="Xem chi tiết đề tài">
                                                <i class="fa-solid fa-circle-info"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- MODAL PHÊ DUYỆT & CÔNG BỐ -->
                                @if($pb && $pb->KetQua === 'Đạt' && $dt->TrangThai !== 'Đã công bố')
                                <div class="modal fade" id="modalDuyetDC{{ $dt->MaDeTai }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 shadow border-0">
                                            <form action="{{ route('truongbomon.duyet_decuong.duyetVaCongBo', $dt->MaDeTai) }}" method="POST">
                                                @csrf
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                                                        <i class="fa-solid fa-bullhorn"></i>
                                                        Phê Duyệt Đề Cương & Công Bố Đề Tài
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start p-4">
                                                    <div class="alert alert-success-subtle border border-success-subtle rounded-3 small mb-3">
                                                        <i class="fa-solid fa-check-circle me-1 text-success"></i>
                                                        Đề cương chi tiết đã được Giảng viên phản biện <strong>{{ $pb->giangVien->HoTen }}</strong> đánh giá <strong>ĐẠT YÊU CẦU</strong>.
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label text-muted small fw-bold">Mã & Tên Đề Tài:</label>
                                                        <div class="fw-bold text-dark fs-6">{{ $dt->MaDeTai }} - {{ $dt->TenDeTai }}</div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label text-muted small fw-bold">Giảng Viên Đề Xuất (HD):</label>
                                                        <div class="fw-semibold text-dark">{{ $dt->giangVien->HoTen }}</div>
                                                    </div>

                                                    @if($pb->NhanXet)
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted small fw-bold">Nhận xét của Giảng viên phản biện:</label>
                                                        <div class="p-2.5 bg-light rounded border small fst-italic text-secondary">
                                                            "{{ $pb->NhanXet }}"
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="p-3 bg-light rounded-3 border small">
                                                        <i class="fa-solid fa-info-circle text-primary me-1"></i>
                                                        Sau khi bấm <strong>Xác Nhận & Công Bố</strong>:
                                                        <ul class="mb-0 ps-3 mt-1">
                                                            <li>Trạng thái đề tài chuyển thành <strong>Đã công bố</strong>.</li>
                                                            <li>Đề tài sẽ chính thức xuất hiện trên cổng đăng ký cho Sinh viên.</li>
                                                            <li>Hệ thống tự động gửi thông báo chúc mừng tới Giảng viên hướng dẫn.</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light border-0">
                                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                                                        <i class="fa-solid fa-check me-1"></i> Xác Nhận & Công Bố
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- MODAL YÊU CẦU CHỈNH SỬA ĐỀ CƯƠNG -->
                                <div class="modal fade" id="modalYeuCauSuaDC{{ $dt->MaDeTai }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 shadow border-0">
                                            <form action="{{ route('truongbomon.duyet_decuong.yeuCauChinhSua', $dt->MaDeTai) }}" method="POST">
                                                @csrf
                                                <div class="modal-header bg-warning text-dark">
                                                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                        Yêu Cầu Chỉnh Sửa Đề Cương
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted small fw-bold">Tên đề tài:</label>
                                                        <div class="fw-bold text-dark">{{ $dt->TenDeTai }}</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Ý kiến / Nội dung yêu cầu chỉnh sửa đề cương <span class="text-danger">*</span></label>
                                                        <textarea name="YeuCauSua" class="form-control" rows="4" required 
                                                                  placeholder="Ghi rõ nội dung đề cương hoặc phương pháp cần giảng viên chỉnh sửa lại...">{{ $pb && $pb->NhanXet ? "Ý kiến GVPB: " . $pb->NhanXet : '' }}</textarea>
                                                        <div class="form-text small text-muted">
                                                            Ý kiến này sẽ được gửi trực tiếp qua hệ thống thông báo cho Giảng viên đề xuất để nộp lại file đề cương mới.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light border-0">
                                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">
                                                        <i class="fa-solid fa-paper-plane me-1"></i> Gửi Yêu Cầu Chỉnh Sửa
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($detais->hasPages())
                    <div class="p-3 border-top d-flex justify-content-end">
                        {{ $detais->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Modal Quick Preview Đề Cương -->
@include('partials.modal_preview_decuong')

@endsection
