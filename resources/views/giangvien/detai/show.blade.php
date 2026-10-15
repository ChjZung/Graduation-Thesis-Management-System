@extends('layouts.giangvien')

@section('page_title', 'Chi Tiết Đề Tài - ' . $detai->TenDeTai)

@section('content')
<div class="container-fluid px-0">
    @php
        $dangKyApproved = $detai->dangKyDeTais->where('TrangThai', 'Đã duyệt')->first();
        $hasGroupAssigned = !empty($dangKyApproved) && !empty($dangKyApproved->nhom);
        $phanBienHienTai = $detai->phanCongPhanBiens?->firstWhere('VaiTro', 'Phản biện đề cương');
        $isOutlineFinalized = !empty($detai->FileDeCuong) && $phanBienHienTai && $phanBienHienTai->KetQua === 'Đạt' && !empty($detai->NgayDuyetBM);
        $canNop = !in_array($detai->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt cấp Khoa', 'Từ chối']) && $hasGroupAssigned && !$isOutlineFinalized && $detai->TrangThai !== 'Hoàn thành';
        $canEdit = in_array($detai->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Yêu cầu chỉnh sửa', 'Yêu cầu điều chỉnh']) && !$hasGroupAssigned && $detai->TrangThai !== 'Từ chối';
    @endphp

    <!-- HEADER & ACTION BAR -->
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('giangvien.detai.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Đề tài của tôi
            </a>
            <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5 font-monospace">
                Mã ĐT: <strong class="text-dark">{{ $detai->MaDeTai }}</strong>
            </span>
            <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5">
                Học kỳ: <strong class="text-dark">{{ $detai->hocKy->TenHocKy ?? $detai->MaHocKy }}</strong>
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($canEdit)
                <a href="{{ route('giangvien.detai.edit', $detai->MaDeTai) }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-semibold text-dark shadow-xs">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Chỉnh Sửa Đề Tài
                </a>
            @endif
            @if($canNop)
                <a href="{{ route('giangvien.detai.nop_decuong.show', $detai->MaDeTai) }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold shadow-xs">
                    <i class="fa-solid fa-file-arrow-up me-1"></i> Trang Nộp Đề Cương
                </a>
            @endif
        </div>
    </div>

    <!-- CẢNH BÁO TỪ CHỐI (NẾU CÓ) -->
    @if($detai->TrangThai === 'Từ chối' || ($detai->LyDoTuChoi && in_array($detai->TrangThai, ['Từ chối', 'Không đạt phản biện'])))
        <div class="alert alert-danger border-danger shadow-sm mb-4 p-3 rounded-3">
            <div class="d-flex align-items-start gap-3">
                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                    <i class="fa-solid fa-circle-xmark fs-5"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="alert-heading fw-bold text-danger mb-1" style="font-size: 14.5px;">ĐỀ TÀI ĐÃ BỊ TỪ CHỐI PHÊ DUYỆT</h6>
                    <div class="text-dark" style="font-size: 13.5px; line-height: 1.5;">
                        <strong>Lý do từ chối:</strong> 
                        <span class="text-danger-emphasis">{{ $detai->LyDoTuChoi ?: 'Chưa có ghi chú lý do chi tiết từ cấp phê duyệt.' }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- CẢNH BÁO BẢN NHÁP (NẾU CÓ) -->
    @if($detai->hasDraft())
        <div class="alert alert-warning border-warning shadow-sm mb-4 p-3 rounded-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-file-pen text-warning fs-3"></i>
                    <div>
                        <h6 class="alert-heading fw-bold text-dark mb-0" style="font-size: 14px;">Đề tài đang có một Bản Nháp chỉnh sửa chưa nộp lại</h6>
                        <div class="text-muted small">Bản chính sẽ chỉ được cập nhật khi quý Thầy/Cô hoàn tất và nộp lại bản nháp.</div>
                    </div>
                </div>
                <a href="{{ route('giangvien.detai.edit', $detai->MaDeTai) }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-semibold text-dark shadow-xs" style="font-size: 13px;">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Mở bản nháp chỉnh sửa
                </a>
            </div>
        </div>
    @endif


    <!-- MAIN TWO-COLUMN DASHBOARD LAYOUT (8 COLS LEFT / 4 COLS RIGHT) -->
    <div class="row g-4">
        <!-- CỘT TRÁI: THÔNG TIN CHUYÊN MÔN, MÔ TẢ ĐỀ TÀI & NHÓM SINH VIÊN (8 CỘT) -->
        <div class="col-lg-8">
            <!-- CARD 1: THÔNG TIN VÀ MÔ TẢ ĐỀ TÀI -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark fs-6">
                        <i class="fa-solid fa-file-lines text-primary me-2"></i> Thông Tin &amp; Nội Dung Đề Tài
                    </span>
                    <span class="badge bg-light text-secondary border font-monospace px-2.5 py-1">
                        {{ $detai->MaDeTai }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <!-- Tên đề tài lớn, rõ ràng -->
                    <h4 class="fw-bold text-dark mb-3" style="line-height: 1.45;">{{ $detai->TenDeTai }}</h4>

                    <!-- Badges học phần, học kỳ, lĩnh vực, quy mô -->
                    <div class="d-flex flex-wrap gap-2 align-items-center mb-4 pb-3 border-bottom">
                        <span class="badge bg-primary-subtle text-primary border px-3 py-1.5" style="font-size: 13px;">
                            <i class="fa-solid fa-book-open me-1.5"></i>{{ $detai->HocPhan ?? 'Khóa luận tốt nghiệp' }}
                        </span>
                        <span class="badge bg-light text-dark border px-3 py-1.5" style="font-size: 13px;">
                            <i class="fa-regular fa-calendar-days text-primary me-1.5"></i>{{ $detai->hocKy->TenHocKy ?? $detai->MaHocKy }}
                        </span>
                        <span class="badge bg-light text-secondary border px-3 py-1.5" style="font-size: 13px;">
                            <i class="fa-solid fa-layer-group text-primary me-1.5"></i>{{ $detai->LinhVuc ?? 'CNTT' }}
                        </span>
                        <span class="badge bg-light text-secondary border px-3 py-1.5" style="font-size: 13px;">
                            <i class="fa-solid fa-users text-primary me-1.5"></i>Quy mô: 3 Sinh viên / nhóm
                        </span>
                    </div>

                    <!-- VÙNG MÔ TẢ ĐỀ TÀI (HIỂN THỊ RICH TEXT AN TOÀN) -->
                    <div class="mb-4">
                        <div class="fw-bold text-dark small text-uppercase mb-2" style="letter-spacing: 0.5px;">
                            <i class="fa-solid fa-align-left text-primary me-1.5"></i> Mô Tả Chi Tiết Đề Tài:
                        </div>
                        <div class="p-3.5 bg-light-subtle rounded-3 border text-dark rich-content-display" style="line-height: 1.7; font-size: 14px;">
                            {!! \App\Helpers\HtmlHelper::clean($detai->MoTa) ?: '<em class="text-muted">Chưa cập nhật nội dung mô tả đề tài.</em>' !!}
                        </div>
                    </div>

                    <!-- VÙNG YÊU CẦU ĐỐI VỚI SINH VIÊN -->
                    @if($detai->YeuCau)
                    <div class="mb-2">
                        <div class="fw-bold text-dark small text-uppercase mb-2" style="letter-spacing: 0.5px;">
                            <i class="fa-solid fa-list-check text-primary me-1.5"></i> Yêu Cầu Năng Lực Đối Với Sinh Viên:
                        </div>
                        <div class="p-3 bg-light-subtle rounded-3 border text-dark" style="line-height: 1.6; font-size: 13.5px; white-space: pre-line;">
                            {{ $detai->YeuCau }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- CARD 2: NHÓM SINH VIÊN THỰC HIỆN -->
            @php
                $dangKy = $detai->dangKyDeTais->firstWhere('TrangThai', 'Đã duyệt') ?? $detai->dangKyDeTais->first();
                $nhom = $dangKy?->nhom;
            @endphp
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark fs-6">
                        <i class="fa-solid fa-users-gear text-primary me-2"></i> Nhóm Sinh Viên Thực Hiện Đề Tài
                    </span>
                    @if($nhom)
                        <span class="badge bg-success-subtle text-success border rounded-pill px-3 py-1">Đã gán nhóm</span>
                    @else
                        <span class="badge bg-light text-secondary border rounded-pill px-3 py-1">Chưa có nhóm</span>
                    @endif
                </div>
                <div class="card-body p-4">
                    @if($nhom)
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-3 border-bottom">
                            <div>
                                <h5 class="fw-bold text-dark mb-1">{{ $nhom->TenNhom }}</h5>
                                <div class="small text-muted">
                                    <i class="fa-solid fa-user-tie me-1 text-primary"></i>Trưởng nhóm: <strong>{{ $nhom->truongNhom->HoTen ?? 'Chưa rõ' }}</strong>
                                    (MSSV: {{ $nhom->truongNhom->MaSV ?? '—' }})
                                </div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border px-3 py-2 rounded-pill" style="font-size: 13px;">
                                <i class="fa-solid fa-user-check me-1"></i>{{ $nhom->thanhViens->where('TrangThai', 'da_tham_gia')->count() }}/3 thành viên chính thức
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-custom table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="10%">STT</th>
                                        <th width="20%">MSSV</th>
                                        <th width="35%">Họ Và Tên</th>
                                        <th width="20%">Vai Trò</th>
                                        <th width="15%" class="text-center">Trạng Thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($nhom->thanhViens as $idx => $tv)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td class="font-monospace fw-semibold">{{ $tv->sinhVien->MaSV ?? '—' }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $tv->sinhVien->HoTen ?? '—' }}</div>
                                            <div class="small text-muted">{{ $tv->sinhVien->taiKhoan->Email ?? '' }}</div>
                                        </td>
                                        <td>
                                            @if($nhom->MaTruongNhom === $tv->MaSV)
                                                <span class="badge bg-primary-subtle text-primary border rounded-pill">Trưởng nhóm</span>
                                            @else
                                                <span class="badge bg-light text-secondary border rounded-pill">Thành viên</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($tv->TrangThai === 'da_tham_gia')
                                                <span class="badge bg-success-subtle text-success border rounded-pill px-2.5">Đã tham gia</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning-emphasis border rounded-pill px-2.5">Chờ xác nhận</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">Chưa có thông tin thành viên.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-light border rounded-3 p-3 mb-0 d-flex align-items-center gap-3">
                            <i class="fa-solid fa-users text-secondary fs-4"></i>
                            <div>
                                <div class="fw-bold text-dark">Chưa có nhóm sinh viên đăng ký đề tài này</div>
                                <div class="text-muted small">Đề tài đang ở trạng thái chờ sinh viên đăng ký hoặc Thầy/Cô có thể gán nhóm đủ 3 thành viên tại danh sách đề tài.</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- CARD 3: KẾT QUẢ THẨM ĐỊNH CỦA CÁN BỘ PHẢN BIỆN (HIỆN KHI CÓ PHÂN CÔNG PHẢN BIỆN) -->
            @if($detai->phanCongPhanBiens && $detai->phanCongPhanBiens->count() > 0)
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark fs-6">
                        <i class="fa-solid fa-comment-dots text-primary me-2"></i> Kết Quả Thẩm Định Của Cán Bộ Phản Biện
                    </span>
                </div>
                <div class="card-body p-4">
                    @foreach($detai->phanCongPhanBiens as $pb)
                    <div class="border rounded-3 p-3 bg-light-subtle mb-3">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2 pb-2 border-bottom">
                            <div>
                                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-user-tie text-primary"></i>
                                    CB Phản Biện: {{ $pb->giangVien->HoTen ?? 'Chưa rõ' }}
                                </div>
                                <div class="small text-muted mt-0.5">
                                    Vai trò: {{ $pb->VaiTro ?? 'Phản biện đề cương' }}
                                    @if($pb->NgayDanhGia)
                                        &bull; Ngày thẩm định: {{ \Carbon\Carbon::parse($pb->NgayDanhGia)->format('d/m/Y H:i') }}
                                    @endif
                                </div>
                            </div>
                            <div>
                                @if($pb->KetQua === 'Đạt')
                                    <span class="badge bg-success text-white px-3 py-1.5 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i>Đạt</span>
                                @elseif($pb->KetQua === 'Yêu cầu chỉnh sửa')
                                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill"><i class="fa-solid fa-wrench me-1"></i>Yêu Cầu Chỉnh Sửa</span>
                                @elseif($pb->KetQua === 'Không đạt')
                                    <span class="badge bg-danger text-white px-3 py-1.5 rounded-pill"><i class="fa-solid fa-circle-xmark me-1"></i>Không Đạt</span>
                                @else
                                    <span class="badge bg-secondary text-white px-3 py-1.5 rounded-pill">{{ $pb->KetQua ?? 'Đang thẩm định' }}</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="small text-muted fw-semibold mb-1">Nội dung nhận xét chi tiết:</div>
                            <div class="p-3 bg-white rounded-3 border text-dark small" style="line-height: 1.6; white-space: pre-line;">{{ $pb->NhanXet ?: 'Chưa có nhận xét chi tiết.' }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- CỘT PHẢI: TRẠNG THÁI QUẢN TRỊ & FILE ĐỀ CƯƠNG (4 CỘT) -->
        <div class="col-lg-4">
            <!-- CARD QUẢN TRỊ & TRẠNG THÁI -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <span class="fw-bold text-dark fs-6">
                        <i class="fa-solid fa-circle-info text-primary me-2"></i> Trạng Thái &amp; Phê Duyệt
                    </span>
                </div>
                <div class="card-body p-4">
                    @php
                        $tt = trim($detai->TrangThai ?? 'Chờ duyệt');
                        $statusBadgeMap = [
                            'Chờ duyệt cấp Bộ môn' => ['class' => 'bg-warning text-dark', 'icon' => 'fa-solid fa-clock', 'text' => 'Chờ duyệt cấp BM'],
                            'Chờ duyệt cấp Khoa'   => ['class' => 'bg-info text-white', 'icon' => 'fa-solid fa-paper-plane', 'text' => 'Chờ duyệt cấp Khoa'],
                            'Trưởng khoa đã duyệt - Chờ nộp đề cương' => ['class' => 'bg-warning text-dark', 'icon' => 'fa-solid fa-file-arrow-up', 'text' => 'Chờ nộp đề cương'],
                            'Đã nộp đề cương - Chờ phân công PB' => ['class' => 'bg-info text-white', 'icon' => 'fa-solid fa-file-circle-check', 'text' => 'Đã nộp ĐC - Chờ PB'],
                            'Đang phản biện đề cương' => ['class' => 'bg-info text-white', 'icon' => 'fa-solid fa-spinner', 'text' => 'Đang phản biện'],
                            'Đã cập nhật đề cương - Chờ phản biện lại' => ['class' => 'bg-primary text-white', 'icon' => 'fa-solid fa-rotate', 'text' => 'Đã nộp lại ĐC'],
                            'Yêu cầu chỉnh sửa đề cương' => ['class' => 'bg-warning text-dark', 'icon' => 'fa-solid fa-triangle-exclamation', 'text' => 'Cần sửa đề cương'],
                            'Đã duyệt'             => ['class' => 'bg-success text-white', 'icon' => 'fa-solid fa-circle-check', 'text' => 'Đã duyệt'],
                            'Trưởng khoa đã duyệt' => ['class' => 'bg-success text-white', 'icon' => 'fa-solid fa-circle-check', 'text' => 'Trưởng khoa đã duyệt'],
                            'Đã công bố'           => ['class' => 'bg-success text-white', 'icon' => 'fa-solid fa-bullhorn', 'text' => 'Đã công bố chính thức'],
                            'Từ chối'              => ['class' => 'bg-danger text-white', 'icon' => 'fa-solid fa-xmark', 'text' => 'Từ chối'],
                            'Không đạt phản biện'  => ['class' => 'bg-danger text-white', 'icon' => 'fa-solid fa-xmark', 'text' => 'Không đạt phản biện'],
                        ];
                        $sCfg = $statusBadgeMap[$tt] ?? ['class' => 'bg-secondary text-white', 'icon' => 'fa-solid fa-circle-info', 'text' => $tt];
                    @endphp

                    <div class="mb-3">
                        <div class="small text-muted fw-semibold mb-1.5">Trạng thái hiện tại:</div>
                        <span class="badge {{ $sCfg['class'] }} px-3 py-2 fs-6 rounded-pill d-inline-flex align-items-center gap-1.5">
                            <i class="{{ $sCfg['icon'] }}"></i> {{ $sCfg['text'] }}
                        </span>
                    </div>

                    @if(in_array($detai->TrangThai, ['Yêu cầu chỉnh sửa', 'Yêu cầu điều chỉnh', 'Yêu cầu chỉnh sửa đề cương']) && $detai->LyDoTuChoi)
                        <div class="alert alert-warning border-warning p-2.5 rounded-3 mb-3 small">
                            <strong class="d-block mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Ý kiến góp ý / Yêu cầu:</strong>
                            <div class="text-danger fw-semibold">{{ $detai->LyDoTuChoi }}</div>
                        </div>
                    @endif

                    <div class="small text-muted border-top pt-3 mt-3">
                        <div class="mb-2 d-flex justify-content-between">
                            <span><i class="fa-regular fa-user me-1.5 text-primary"></i>GV Hướng dẫn:</span>
                            <strong class="text-dark">{{ $gv->HoTen }}</strong>
                        </div>
                        @if($gv->boMon)
                        <div class="mb-2 d-flex justify-content-between">
                            <span><i class="fa-solid fa-building me-1.5 text-primary"></i>Bộ môn:</span>
                            <strong class="text-dark">{{ $gv->boMon->TenBoMon }}</strong>
                        </div>
                        @endif
                        @if($detai->NgayDeXuat)
                        <div class="mb-2 d-flex justify-content-between">
                            <span><i class="fa-regular fa-calendar-plus me-1.5 text-primary"></i>Ngày đề xuất:</span>
                            <span class="text-dark">{{ \Carbon\Carbon::parse($detai->NgayDeXuat)->format('d/m/Y') }}</span>
                        </div>
                        @endif
                        @if($detai->NgayDuyetBM)
                        <div class="mb-2 d-flex justify-content-between">
                            <span><i class="fa-solid fa-check me-1.5 text-success"></i>Ngày BM duyệt:</span>
                            <span class="text-dark">{{ \Carbon\Carbon::parse($detai->NgayDuyetBM)->format('d/m/Y') }}</span>
                        </div>
                        @endif
                        @if($detai->NgayCongBo)
                        <div class="mb-0 d-flex justify-content-between">
                            <span><i class="fa-solid fa-bullhorn me-1.5 text-success"></i>Ngày công bố:</span>
                            <span class="text-dark">{{ \Carbon\Carbon::parse($detai->NgayCongBo)->format('d/m/Y') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- CARD FILE ĐỀ CƯƠNG CHI TIẾT -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark fs-6">
                        <i class="fa-solid fa-paperclip text-primary me-2"></i> File Đề Cương Chi Tiết
                    </span>
                    @if($detai->FileDeCuong)
                        <span class="badge bg-success-subtle text-success border">Đã có file</span>
                    @else
                        <span class="badge bg-light text-muted border">Chưa nộp</span>
                    @endif
                </div>
                <div class="card-body p-4">
                    @if($detai->FileDeCuong)
                        @php
                            $filePathShow = Str::startsWith($detai->FileDeCuong, ['http', 'storage/']) ? asset($detai->FileDeCuong) : asset('storage/' . $detai->FileDeCuong);
                            $fileName = basename($detai->FileDeCuong);
                        @endphp
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fa-solid fa-file-word text-primary fs-3"></i>
                                <div class="overflow-hidden">
                                    <div class="fw-semibold text-dark text-truncate small" title="{{ $fileName }}">
                                        {{ $fileName }}
                                    </div>
                                    <span class="text-muted" style="font-size: 0.75rem;">Đề cương chính thức</span>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 flex-fill fw-semibold shadow-xs"
                                        onclick="quickPreviewOutline('{{ $filePathShow }}', '{{ $fileName }}', '{{ addslashes($detai->TenDeTai) }}')">
                                    <i class="fa-solid fa-eye me-1"></i> Xem Nhanh
                                </button>
                                <a href="{{ $filePathShow }}" download="{{ $fileName }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="fa-solid fa-download me-1"></i> Tải Về
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="text-muted small p-3 bg-light rounded-3 text-center mb-3">
                            <i class="fa-solid fa-file-circle-xmark text-secondary fs-3 mb-1 d-block"></i>
                            Chưa nộp file đề cương chi tiết.
                        </div>
                    @endif

                    @if($canNop)
                        <a href="{{ route('giangvien.detai.nop_decuong.show', $detai->MaDeTai) }}" class="btn btn-outline-primary btn-sm w-100 rounded-pill fw-semibold">
                            <i class="fa-solid fa-file-arrow-up me-1"></i> Mở Trang Nộp Đề Cương
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
