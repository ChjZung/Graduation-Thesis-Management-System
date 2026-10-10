@extends('layouts.giangvien')

@section('page_title', 'Chi Tiết Đề Tài - ' . $detai->TenDeTai)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Header & Breadcrumb -->
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('giangvien.detai.index') }}" class="text-decoration-none">Đề tài của tôi</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Chi tiết đề tài</li>
                </ol>
            </nav>
@php
    $dangKyApproved = $detai->dangKyDeTais->where('TrangThai', 'Đã duyệt')->first();
    $hasGroupAssigned = !empty($dangKyApproved) && !empty($dangKyApproved->nhom);
    $phanBien = $detai->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
    $isOutlineFinalized = !empty($detai->FileDeCuong) && $phanBien && $phanBien->KetQua === 'Đạt' && !empty($detai->NgayDuyetBM);
    $canNop = !in_array($detai->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt cấp Khoa', 'Từ chối']) && $hasGroupAssigned && !$isOutlineFinalized && $detai->TrangThai !== 'Hoàn thành';
@endphp
            <div class="d-flex align-items-center gap-2">
                @if($canNop)
                    <a href="{{ route('giangvien.detai.nop_decuong.show', $detai->MaDeTai) }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold shadow-xs">
                        <i class="fa-solid fa-file-arrow-up me-1"></i> Trang Nộp Đề Cương
                    </a>
                @endif
                <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light border btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay Lại
                </a>
            </div>
        </div>

        @if($detai->TrangThai === 'Từ chối' || ($detai->LyDoTuChoi && in_array($detai->TrangThai, ['Từ chối', 'Không đạt phản biện'])))
            <div class="alert alert-danger border-danger shadow-none mb-4 p-3 rounded-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-circle-xmark fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading fw-bold text-danger mb-1" style="font-size: 15px;">ĐỀ TÀI ĐÃ BỊ TỪ CHỐI PHÊ DUYỆT</h6>
                        <div class="text-dark" style="font-size: 13.5px; line-height: 1.5;">
                            <strong>Lý do từ chối:</strong> 
                            <span class="text-danger-emphasis">{{ $detai->LyDoTuChoi ?: 'Chưa có ghi chú lý do chi tiết từ cấp phê duyệt.' }}</span>
                        </div>
                        <div class="text-muted mt-1" style="font-size: 13px;">
                            <i class="fa-solid fa-circle-info me-1"></i>Đề tài ở trạng thái bị từ chối chỉ hỗ trợ xem chi tiết lưu trữ, không thể chỉnh sửa, xóa hoặc nộp lại.
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($detai->hasDraft())
            <div class="alert alert-warning border-warning shadow-none mb-4 p-3 rounded-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-pen text-warning fs-3"></i>
                        <div>
                            <h6 class="alert-heading fw-bold text-dark mb-0" style="font-size: 14px;">Đề tài đang có một Bản Nháp chỉnh sửa chưa nộp lại</h6>
                            <div class="text-muted" style="font-size: 13px;">Bản đang hiển thị dưới đây là bản chính thức hiện tại trên hệ thống. Bản chính chỉ cập nhật khi quý Thầy/Cô nộp lại bản nháp.</div>
                        </div>
                    </div>
                    <a href="{{ route('giangvien.detai.edit', $detai->MaDeTai) }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-semibold text-dark" style="font-size: 13px;">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Mở bản nháp chỉnh sửa
                    </a>
                </div>
            </div>
        @endif

        <!-- Topic Details Card -->
        <div class="card card-premium mb-4">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span>
                    <i class="fa-solid fa-circle-info text-primary me-2"></i>
                    Thông Tin Chi Tiết Đề Tài
                </span>
                <span class="badge bg-light text-dark border font-monospace px-2.5 py-1.5">{{ $detai->MaDeTai }}</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-8">
                        <h4 class="fw-bold text-primary mb-3">{{ $detai->TenDeTai }}</h4>

                        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
                            <span class="badge bg-primary-subtle text-primary border px-2.5 py-1.5">
                                <i class="fa-solid fa-book-open me-1"></i>{{ $detai->HocPhan ?? 'Khóa luận tốt nghiệp' }}
                            </span>
                            <span class="badge bg-light text-secondary border px-2.5 py-1.5">
                                <i class="fa-regular fa-calendar-days me-1"></i>{{ $detai->hocKy->TenHocKy ?? $detai->MaHocKy }}
                            </span>
                            <span class="badge bg-light text-secondary border px-2.5 py-1.5">
                                <i class="fa-solid fa-layer-group me-1"></i>{{ $detai->LinhVuc ?? 'CNTT' }}
                            </span>
                            <span class="badge bg-light text-secondary border px-2.5 py-1.5">
                                <i class="fa-solid fa-users me-1"></i>3 Sinh viên
                            </span>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">
                                <i class="fa-solid fa-align-left text-primary me-1"></i> Mô Tả Đề Tài & Mục Tiêu Nghiên Cứu
                            </h6>
                            <div class="text-secondary" style="white-space: pre-line; line-height: 1.6;">
                                {{ $detai->MoTa ?: 'Chưa cập nhật mô tả đề tài.' }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">
                                <i class="fa-solid fa-list-check text-primary me-1"></i> Yêu Cầu Năng Lực Đối Với Sinh Viên
                            </h6>
                            <div class="text-secondary" style="white-space: pre-line; line-height: 1.6;">
                                {{ $detai->YeuCau ?: 'Chưa cập nhật yêu cầu năng lực sinh viên.' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 bg-light-subtle mb-3">
                            <div class="small text-muted fw-semibold mb-2">Trạng Thái Đề Tài:</div>
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
                            <span class="badge {{ $sCfg['class'] }} px-3 py-2 fs-6 rounded-pill d-inline-flex align-items-center gap-1 mb-2">
                                <i class="{{ $sCfg['icon'] }}"></i> {{ $sCfg['text'] }}
                            </span>

                            <div class="small text-muted border-top pt-2 mt-2">
                                <div><i class="fa-regular fa-user me-1"></i>GVHD: <strong>{{ $gv->HoTen }}</strong></div>
                                @if($gv->boMon)
                                    <div><i class="fa-solid fa-building-user me-1"></i>Bộ môn: <strong>{{ $gv->boMon->TenBoMon }}</strong></div>
                                @endif
                                @if($detai->NgayDeXuat)
                                    <div><i class="fa-regular fa-calendar-plus me-1"></i>Ngày đề xuất: {{ \Carbon\Carbon::parse($detai->NgayDeXuat)->format('d/m/Y') }}</div>
                                @endif
                                @if($detai->NgayCongBo)
                                    <div><i class="fa-solid fa-bullhorn me-1"></i>Ngày công bố: {{ \Carbon\Carbon::parse($detai->NgayCongBo)->format('d/m/Y') }}</div>
                                @endif
                            </div>
                        </div>

                        <!-- Card Đề Cương Chi Tiết -->
                        <div class="border rounded-3 p-3 bg-white shadow-xs">
                            <div class="small text-muted fw-semibold mb-2 d-flex align-items-center justify-content-between">
                                <span><i class="fa-solid fa-paperclip text-primary me-1"></i>Đề Cương Chi Tiết:</span>
                                @if($detai->TrangThai === 'Đã công bố')
                                    <span class="badge bg-success-subtle text-success border">Đã công bố</span>
                                @endif
                            </div>

                            @if($detai->FileDeCuong)
                                @php
                                    $filePathShow = Str::startsWith($detai->FileDeCuong, ['http', 'storage/']) ? asset($detai->FileDeCuong) : asset('storage/' . $detai->FileDeCuong);
                                @endphp
                                <div class="p-2.5 bg-light rounded-3 border mb-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="fa-solid fa-file-word text-primary fs-4"></i>
                                        <div class="overflow-hidden">
                                            <div class="fw-semibold text-dark text-truncate small" title="{{ basename($detai->FileDeCuong) }}">
                                                {{ basename($detai->FileDeCuong) }}
                                            </div>
                                            <span class="text-muted" style="font-size: 0.75rem;">Đề cương chính thức</span>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1.5 flex-wrap">
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 flex-fill"
                                                onclick="quickPreviewOutline('{{ $filePathShow }}', '{{ basename($detai->FileDeCuong) }}', '{{ addslashes($detai->TenDeTai) }}')">
                                            <i class="fa-solid fa-eye me-1"></i> Xem Nhanh
                                        </button>
                                        <a href="{{ $filePathShow }}" download="{{ basename($detai->FileDeCuong) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            <i class="fa-solid fa-download me-1"></i> Tải Về
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="text-muted small p-3 bg-light rounded text-center mb-3">
                                    <i class="fa-solid fa-file-circle-xmark text-secondary fs-3 mb-1 d-block"></i>
                                    Chưa nộp file đề cương chi tiết.
                                </div>
                            @endif

                            @if($canNop)
                                <a href="{{ route('giangvien.detai.nop_decuong.show', $detai->MaDeTai) }}" class="btn btn-outline-primary btn-sm w-100 rounded-pill">
                                    <i class="fa-solid fa-file-arrow-up me-1"></i> Vào Trang Nộp Đề Cương
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Nhóm Sinh Viên Thực Hiện -->
        @php
            $dangKy = $detai->dangKyDeTais->firstWhere('TrangThai', 'Đã duyệt') ?? $detai->dangKyDeTais->first();
            $nhom = $dangKy?->nhom;
        @endphp
        <div class="card card-premium mb-4">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span>
                    <i class="fa-solid fa-users-gear text-primary me-2"></i>
                    Nhóm Sinh Viên Thực Hiện Đề Tài
                </span>
                @if($nhom)
                    <span class="badge bg-success text-white rounded-pill px-3">Đã gán nhóm</span>
                @else
                    <span class="badge bg-light text-secondary border rounded-pill px-3">Chưa có nhóm</span>
                @endif
            </div>
            <div class="card-body p-4">
                @if($nhom)
                    <div class="row g-3 align-items-center mb-3 pb-3 border-bottom">
                        <div class="col-md-6">
                            <h5 class="fw-bold text-dark mb-1">{{ $nhom->TenNhom }}</h5>
                            <div class="small text-muted">
                                <i class="fa-solid fa-user-tie me-1 text-primary"></i>Trưởng nhóm: <strong>{{ $nhom->truongNhom->HoTen ?? 'Chưa rõ' }}</strong>
                                (MSSV: {{ $nhom->truongNhom->MaSV ?? '—' }})
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <span class="badge bg-primary-subtle text-primary border px-3 py-2 fs-7 rounded-pill">
                                <i class="fa-solid fa-user-check me-1"></i>{{ $nhom->thanhViens->where('TrangThai', 'da_tham_gia')->count() }}/3 thành viên chính thức
                            </span>
                        </div>
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
                    <div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-user-group fs-2 text-secondary mb-2 d-block"></i>
                        <div>Đề tài hiện chưa được gán cho nhóm sinh viên nào.</div>
                        <div class="small mt-1">
                            Sau khi đề tài được thẩm định đề cương và Ban chủ nhiệm Khoa công bố chính thức, Giảng viên có thể gán nhóm sinh viên đủ 3 thành viên tại danh sách đề tài.
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Card Kết Quả Phản Biện & Nhận Xét -->
        @if($detai->phanCongPhanBiens && $detai->phanCongPhanBiens->count() > 0)
        <div class="card card-premium mb-4">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <span>
                    <i class="fa-solid fa-comment-dots text-primary me-2"></i>
                    Kết Quả Thẩm Định Của Cán Bộ Phản Biện
                </span>
            </div>
            <div class="card-body p-4">
                @foreach($detai->phanCongPhanBiens as $pb)
                <div class="border rounded-3 p-3 bg-white shadow-xs mb-3">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2 pb-2 border-bottom">
                        <div>
                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-user-tie text-primary"></i>
                                CB Phản Biện: {{ $pb->giangVien->HoTen ?? 'Chưa rõ' }}
                            </div>
                            <div class="small text-muted">
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
                        <div class="small text-muted fw-semibold mb-1">Nội dung nhận xét:</div>
                        <div class="p-3 bg-light rounded-3 text-dark small" style="white-space: pre-line;">{{ $pb->NhanXet ?: 'Chưa có nhận xét chi tiết.' }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('giangvien.detai.index') }}" class="btn btn-light border px-4 rounded-pill">Quay Lại</a>
            @if(in_array($detai->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Yêu cầu chỉnh sửa', 'Yêu cầu điều chỉnh']))
                <a href="{{ route('giangvien.detai.edit', $detai->MaDeTai) }}" class="btn btn-primary px-4 rounded-pill">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Chỉnh Sửa Đề Tài
                </a>
            @endif
        </div>

    </div>
</div>
@endsection
