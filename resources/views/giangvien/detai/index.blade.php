@extends('layouts.giangvien')

@section('page_title', 'Quản Lý Đề Tài Của Tôi')

@section('content')

<!-- Card Danh Sách Đề Tài -->
<div class="card card-premium">
    <div class="card-header-premium d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-folder-open text-primary fs-5"></i>
            <span class="fw-bold">Danh Sách Đề Tài Đề Xuất</span>
        </div>
        <a href="{{ route('giangvien.detai.create') }}" class="btn btn-success btn-sm rounded-pill px-3 shadow-xs">
            <i class="fa-solid fa-plus me-1"></i> Đề Xuất Đề Tài Mới
        </a>
    </div>

    <!-- Thanh Bộ Lọc Tìm Kiếm & Học Kỳ -->
    <div class="p-3 bg-light border-bottom">
        <form action="{{ route('giangvien.detai.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm theo tên đề tài..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="MaHocKy" class="form-select form-select-sm">
                    <option value="">-- Tất cả học kỳ --</option>
                    @foreach($hocKies as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="TrangThai" class="form-select form-select-sm">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="Chờ duyệt cấp Bộ môn" {{ request('TrangThai') == 'Chờ duyệt cấp Bộ môn' ? 'selected' : '' }}>Chờ duyệt cấp Bộ môn</option>
                    <option value="Chờ duyệt cấp Khoa" {{ request('TrangThai') == 'Chờ duyệt cấp Khoa' ? 'selected' : '' }}>Chờ duyệt cấp Khoa</option>
                    <option value="Trưởng khoa đã duyệt - Chờ nộp đề cương" {{ request('TrangThai') == 'Trưởng khoa đã duyệt - Chờ nộp đề cương' ? 'selected' : '' }}>Chờ nộp đề cương</option>
                    <option value="Đã nộp đề cương - Chờ phân công PB" {{ request('TrangThai') == 'Đã nộp đề cương - Chờ phân công PB' ? 'selected' : '' }}>Đã nộp ĐC - Chờ PB</option>
                    <option value="Đang phản biện đề cương" {{ request('TrangThai') == 'Đang phản biện đề cương' ? 'selected' : '' }}>Đang phản biện đề cương</option>
                    <option value="Yêu cầu chỉnh sửa đề cương" {{ request('TrangThai') == 'Yêu cầu chỉnh sửa đề cương' ? 'selected' : '' }}>Yêu cầu chỉnh sửa đề cương</option>
                    <option value="Đã công bố" {{ request('TrangThai') == 'Đã công bố' ? 'selected' : '' }}>Đã công bố chính thức</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 flex-fill">
                    <i class="fa-solid fa-filter me-1"></i> Lọc
                </button>
                @if(request()->hasAny(['search', 'MaHocKy', 'TrangThai']))
                    <a href="{{ route('giangvien.detai.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5" title="Đặt lại">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th width="9%" class="text-center">Mã ĐT</th>
                        <th width="33%">Tên Đề Tài & Môn Học</th>
                        <th width="16%" class="text-nowrap">Học Kỳ</th>
                        <th width="15%">Nhóm Thực Hiện</th>
                        <th width="13%" class="text-center">Trạng Thái</th>
                        <th width="14%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detais as $dt)
                    @php
                        $dangKyApproved = $dt->dangKyDeTais->where('TrangThai', 'Đã duyệt')->first();
                        $dangKyPending = $dt->dangKyDeTais->where('TrangThai', 'Chờ duyệt')->first();
                        $hasGroupAssigned = !empty($dangKyApproved) && !empty($dangKyApproved->nhom);
                        $isDaCongBo = ($dt->TrangThai === 'Đã công bố');

                        // 1. Đề xuất đề tài đã được Trưởng khoa phê duyệt & công bố chính thức (hoặc các giai đoạn tiếp theo)
                        $isProposalApproved = in_array($dt->TrangThai, [
                            'Đã công bố',
                            'Trưởng khoa đã duyệt',
                            'Đã duyệt',
                            'Trưởng khoa đã duyệt - Chờ nộp đề cương',
                            'Đã nộp đề cương - Chờ phân công PB',
                            'Đang phản biện đề cương',
                            'Đã cập nhật đề cương - Chờ phản biện lại',
                            'Đã phản biện - Chờ duyệt BM',
                            'Đã phản biện - Chờ TBM duyệt đề cương',
                            'Yêu cầu chỉnh sửa đề cương',
                            'Hoàn thành'
                        ]);

                        // 2. Kiểm tra xem đề cương đã hoàn tất thẩm định đạt và TBM duyệt chốt chưa
                        $phanBien = $dt->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
                        $isOutlineFinalized = !empty($dt->FileDeCuong) && $phanBien && $phanBien->KetQua === 'Đạt' && !empty($dt->NgayDuyetBM);
                        $isLocked = $isOutlineFinalized || ($dt->TrangThai === 'Hoàn thành');

                        // 3. Quy trình chuẩn: Đề xuất duyệt 2 cấp -> SV tham gia đăng ký xong (hoặc GV gán nhóm) -> HIỆN NÚT NỘP ĐỀ CƯƠNG
                        $canNopDeCuong = $isProposalApproved && $hasGroupAssigned && !$isLocked;
                        $isNopLai = in_array($dt->TrangThai, ['Đã cập nhật đề cương - Chờ phản biện lại', 'Yêu cầu chỉnh sửa đề cương']);
                        $canEdit = in_array($dt->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Yêu cầu chỉnh sửa', 'Yêu cầu điều chỉnh']) && !$hasGroupAssigned && $dt->TrangThai !== 'Từ chối';
                    @endphp
                    <tr>
                        <td class="text-center">
                            <span class="badge bg-light text-dark fw-bold border font-monospace">{{ $dt->MaDeTai }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-primary-custom mb-1 d-flex align-items-center gap-1.5 flex-wrap">
                                <span>{{ $dt->TenDeTai }}</span>
                                @if($dt->hasDraft())
                                    <span class="badge bg-warning-subtle text-warning-emphasis border" title="Đang có bản nháp chỉnh sửa chưa nộp lại" style="font-size: 12px;">
                                        <i class="fa-solid fa-file-pen me-1"></i>Có bản nháp
                                    </span>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                <span class="badge bg-primary-subtle text-primary border">
                                    {{ $dt->HocPhan ?? 'Khóa luận tốt nghiệp' }}
                                </span>
                                <span class="badge bg-light text-dark border">
                                    <i class="fa-solid fa-users me-1 text-secondary"></i>3 SV
                                </span>
                                @if($dt->LinhVuc)
                                    <span class="badge bg-light text-secondary border">{{ $dt->LinhVuc }}</span>
                                @endif

                                @if($dt->FileDeCuong)
                                    @php
                                        $filePath = Str::startsWith($dt->FileDeCuong, ['http', 'storage/']) ? asset($dt->FileDeCuong) : asset('storage/' . $dt->FileDeCuong);
                                    @endphp
                                    <button type="button" class="badge bg-success-subtle text-success border text-decoration-none btn p-1" 
                                            onclick="quickPreviewOutline('{{ $filePath }}', '{{ basename($dt->FileDeCuong) }}', '{{ addslashes($dt->TenDeTai) }}')"
                                            title="Xem nhanh đề cương chi tiết">
                                        <i class="fa-solid fa-paperclip me-1"></i>Đề cương
                                    </button>
                                @endif
                            </div>

                            @if(in_array($dt->TrangThai, ['Yêu cầu chỉnh sửa', 'Yêu cầu điều chỉnh', 'Yêu cầu chỉnh sửa đề cương', 'Từ chối']) && $dt->LyDoTuChoi)
                                <div class="small text-danger mt-1.5 p-2 bg-danger-subtle rounded border border-danger-subtle">
                                    <strong><i class="fa-solid fa-circle-exclamation me-1"></i>Ý kiến phản hồi:</strong> {{ $dt->LyDoTuChoi }}
                                </div>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <span class="text-dark small fw-medium">
                                <i class="fa-regular fa-calendar-days text-primary me-1"></i>{{ $dt->hocKy->TenHocKy ?? $dt->MaHocKy }}
                            </span>
                        </td>
                        <td>
                            @if($dangKyApproved && $dangKyApproved->nhom)
                                <div class="fw-bold text-success"><i class="fa-solid fa-users me-1"></i>{{ $dangKyApproved->nhom->TenNhom }}</div>
                                <div class="small text-muted">
                                    {{ $dangKyApproved->nhom->thanhViens->where('TrangThai', 'da_tham_gia')->count() }}/3 SV
                                </div>
                            @elseif($dangKyPending && $dangKyPending->nhom)
                                <div class="fw-semibold text-warning-emphasis"><i class="fa-solid fa-clock me-1"></i>{{ $dangKyPending->nhom->TenNhom }}</div>
                                <div class="small text-muted">(Đang chờ duyệt)</div>
                            @else
                                <span class="text-muted small">Chưa có nhóm</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($dt->TrangThai === 'Đã công bố')
                                <span class="badge bg-success text-white rounded-pill px-2.5 py-1"><i class="fa-solid fa-bullhorn me-1"></i>Đã công bố</span>
                            @elseif($dt->TrangThai === 'Trưởng khoa đã duyệt - Chờ nộp đề cương')
                                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1"><i class="fa-solid fa-file-arrow-up me-1"></i>Chờ nộp ĐC</span>
                            @elseif($dt->TrangThai === 'Đã nộp đề cương - Chờ phân công PB')
                                <span class="badge bg-info text-white rounded-pill px-2.5 py-1"><i class="fa-solid fa-file-circle-check me-1"></i>Đã nộp ĐC</span>
                            @elseif($dt->TrangThai === 'Đã cập nhật đề cương - Chờ phản biện lại')
                                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1"><i class="fa-solid fa-rotate me-1"></i>Đã nộp lại ĐC</span>
                            @elseif($dt->TrangThai === 'Đang phản biện đề cương')
                                <span class="badge bg-info-subtle text-info border rounded-pill px-2.5 py-1"><i class="fa-solid fa-spinner me-1"></i>Đang PB</span>
                            @elseif($dt->TrangThai === 'Đã phản biện - Chờ duyệt BM' || $dt->TrangThai === 'Đã phản biện - Chờ TBM duyệt đề cương')
                                <span class="badge bg-primary-subtle text-primary border rounded-pill px-2.5 py-1"><i class="fa-solid fa-check-double me-1"></i>Đã PB - Chờ TBM</span>
                            @elseif($dt->TrangThai === 'Chờ duyệt cấp Khoa')
                                <span class="badge bg-danger rounded-pill px-2.5 py-1"><i class="fa-solid fa-paper-plane me-1"></i>Chờ Khoa duyệt</span>
                            @elseif($dt->TrangThai === 'Chờ duyệt cấp Bộ môn')
                                <span class="badge bg-warning-subtle text-warning-emphasis border rounded-pill px-2.5 py-1"><i class="fa-solid fa-clock me-1"></i>Chờ BM duyệt</span>
                            @elseif($dt->TrangThai === 'Yêu cầu chỉnh sửa đề cương' || $dt->TrangThai === 'Yêu cầu chỉnh sửa' || $dt->TrangThai === 'Yêu cầu điều chỉnh')
                                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1"><i class="fa-solid fa-wrench me-1"></i>Cần sửa</span>
                            @elseif($dt->TrangThai === 'Không đạt phản biện' || $dt->TrangThai === 'Từ chối')
                                <span class="badge bg-danger rounded-pill px-2.5 py-1"><i class="fa-solid fa-xmark me-1"></i>Từ chối</span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-2.5 py-1">{{ $dt->TrangThai }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1 flex-wrap">
                                <!-- Nút Nộp Đề Cương (Chỉ mở khi đã có nhóm sinh viên đăng ký) -->
                                @if($canNopDeCuong)
                                    <a href="{{ route('giangvien.detai.nop_decuong.show', $dt->MaDeTai) }}" 
                                       class="btn btn-sm btn-primary rounded-pill px-2.5 py-1 fw-semibold shadow-xs" 
                                       title="Mở trang nộp và xem nhận xét đề cương">
                                        <i class="fa-solid fa-file-arrow-up me-1"></i>{{ $isNopLai ? 'Nộp lại ĐC' : 'Nộp Đề Cương' }}
                                    </a>
                                @elseif($isLocked)
                                    <span class="badge bg-light text-muted border py-1.5 px-2" title="Đề cương đã được thẩm định đạt và phê duyệt hoàn tất (Đã khóa nộp)">
                                        <i class="fa-solid fa-lock text-secondary me-1"></i>Đã khóa ĐC
                                    </span>
                                @elseif(in_array($dt->TrangThai, ['Đã công bố', 'Trưởng khoa đã duyệt', 'Đã duyệt']) && !$hasGroupAssigned)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border py-1.5 px-2" title="Đề tài đã công bố, đang chờ sinh viên đăng ký để mở nộp đề cương">
                                        <i class="fa-solid fa-users-viewfinder me-1"></i>Chờ SV đăng ký
                                    </span>
                                @endif

                                <!-- Gán Nhóm (Chỉ cho phép khi đề tài đã được duyệt/công bố và chưa có nhóm) -->
                                @if(in_array($dt->TrangThai, ['Đã duyệt', 'Trưởng khoa đã duyệt', 'Đã công bố']) && !$hasGroupAssigned)
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1" data-bs-toggle="modal" data-bs-target="#modalGanNhom{{ $dt->MaDeTai }}" title="Gán nhóm cho đề tài này">
                                        <i class="fa-solid fa-user-check me-1"></i>Gán Nhóm
                                    </button>
                                @endif

                                <!-- Nút Xem Chi Tiết Đề Tài -->
                                <a href="{{ route('giangvien.detai.show', $dt->MaDeTai) }}" class="btn btn-sm btn-light border text-primary rounded-circle" title="Xem chi tiết đề tài">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                <!-- Nút Sửa Đề Tài (Ẩn nếu đề tài bị từ chối) -->
                                @if(!$isDaCongBo && !$hasGroupAssigned && $dt->TrangThai !== 'Từ chối')
                                <a href="{{ route('giangvien.detai.edit', $dt->MaDeTai) }}" class="btn btn-sm btn-light border text-secondary rounded-circle" title="Chỉnh sửa đề tài">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                @endif

                                <!-- Nút Xóa Đề Tài (Chỉ khi chưa duyệt/chưa có nhóm và không bị từ chối) -->
                                @if(!$hasGroupAssigned && !in_array($dt->TrangThai, ['Đã duyệt', 'Trưởng khoa đã duyệt', 'Đã công bố', 'Từ chối']))
                                <form action="{{ route('giangvien.detai.destroy', $dt->MaDeTai) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đề tài này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border text-danger rounded-circle" title="Xóa đề tài">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- MODAL GÁN NHÓM CHO ĐỀ TÀI NÀY -->
                    @if(in_array($dt->TrangThai, ['Đã duyệt', 'Trưởng khoa đã duyệt', 'Đã công bố']) && !$dangKyApproved)
                    <div class="modal fade" id="modalGanNhom{{ $dt->MaDeTai }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('giangvien.detai.ganNhom', $dt->MaDeTai) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h6 class="modal-title fw-bold text-primary">
                                            <i class="fa-solid fa-user-check me-2"></i>Gán Nhóm Cho Đề Tài: {{ $dt->TenDeTai }}
                                        </h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="small text-muted mb-2">
                                            Danh sách nhóm thuộc <strong>{{ $dt->hocKy->TenHocKy ?? 'học kỳ này' }}</strong> - Môn <strong>{{ $dt->HocPhan ?? 'Khóa luận' }}</strong> chưa đăng ký đề tài:
                                        </p>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Chọn Nhóm Sinh Viên <span class="text-danger">*</span>:</label>
                                            @php
                                                $allNhoms = $nhomsChuaCoDeTai ?? $NhomChuaCoDeTai ?? collect();
                                                $dsNhoms = $allNhoms->filter(function($n) use ($dt) {
                                                    if (!empty($dt->MaHocKy) && !empty($n->MaHocKy) && $n->MaHocKy !== $dt->MaHocKy) {
                                                        return false;
                                                    }
                                                    if (!empty($dt->MaHocPhan) && !empty($n->MaHocPhan) && $n->MaHocPhan !== $dt->MaHocPhan) {
                                                        return false;
                                                    }
                                                    return true;
                                                });
                                            @endphp
                                            <select name="MaNhom" class="form-select" required>
                                                <option value="">-- Chọn nhóm sinh viên --</option>
                                                @forelse($dsNhoms as $nhomOption)
                                                    @php
                                                        $soTV = $nhomOption->thanhViens->where('TrangThai', 'da_tham_gia')->count();
                                                        $isDuThanhVien = ($soTV >= 3);
                                                    @endphp
                                                    <option value="{{ $nhomOption->MaNhom }}" {{ !$isDuThanhVien ? 'disabled class=text-muted' : '' }}>
                                                        {{ $nhomOption->TenNhom }} (Trưởng nhóm: {{ $nhomOption->truongNhom->HoTen ?? 'Chưa rõ' }} - {{ $soTV }}/3 SV){{ !$isDuThanhVien ? ' ❌ [Chưa đủ 3 SV - Không thể gán]' : ' ✅ [Đủ 3 SV]' }}
                                                    </option>
                                                @empty
                                                    <option value="" disabled>Không tìm thấy nhóm phù hợp trong học kỳ này</option>
                                                @endforelse
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-bold">
                                            <i class="fa-solid fa-check me-1"></i>Xác Nhận Gán
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-folder-open fs-1 text-secondary mb-3 d-block"></i>
                            Bạn chưa có đề tài nào phù hợp với bộ lọc. Bấm <strong>"Đề Xuất Đề Tài Mới"</strong> để bắt đầu.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($detais->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $detais->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection