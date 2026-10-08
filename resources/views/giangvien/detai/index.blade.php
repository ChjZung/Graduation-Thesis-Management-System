@extends('layouts.giangvien')

@section('page_title', 'Quản Lý Đề Tài Của Tôi')

@section('content')


<div class="card card-premium">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-folder-open text-primary me-2"></i>Danh Sách Đề Tài Đề Xuất</span>
        <a href="{{ route('giangvien.detai.create') }}" class="btn btn-success btn-sm rounded-pill px-3">
            <i class="fa-solid fa-plus me-1"></i> Đề Xuất Đề Tài Mới
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th width="10%">Mã Đề Tài</th>
                        <th width="32%">Tên Đề Tài & Học Phần</th>
                        <th width="12%">Lĩnh Vực</th>
                        <th width="18%">Nhóm Thực Hiện</th>
                        <th width="14%" class="text-center">Trạng Thái</th>
                        <th width="14%" class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detais as $dt)
                    @php
                        $dangKyApproved = $dt->dangKyDeTais->where('TrangThai', 'Đã duyệt')->first();
                        $dangKyPending = $dt->dangKyDeTais->where('TrangThai', 'Chờ duyệt')->first();
                        $hasGroupAssigned = !empty($dangKyApproved) && !empty($dangKyApproved->nhom);

                        // Chỉ khi ĐÃ CÓ NHÓM SINH VIÊN GÁN CHO ĐỀ TÀI thì nút Nộp Đề Cương mới được phép hiển thị
                        $canNopDeCuong = $hasGroupAssigned && in_array($dt->TrangThai, [
                            'Đã công bố',
                            'Trưởng khoa đã duyệt',
                            'Trưởng khoa đã duyệt - Chờ nộp đề cương',
                            'Yêu cầu chỉnh sửa đề cương',
                            'Đã cập nhật đề cương - Chờ phản biện lại'
                        ]);
                        $isNopLai = in_array($dt->TrangThai, ['Đã cập nhật đề cương - Chờ phản biện lại', 'Yêu cầu chỉnh sửa đề cương']);
                    @endphp
                    <tr>
                        <td>
                            <span class="badge bg-light text-dark fw-bold border">{{ $dt->MaDeTai }}</span>
                            <div class="small text-muted mt-1">{{ $dt->hocKy->TenHocKy ?? '' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-primary-custom">{{ $dt->TenDeTai }}</div>
                            <div class="d-flex flex-wrap gap-1 align-items-center mt-1">
                                <span class="badge bg-primary-subtle text-primary border">
                                    {{ $dt->HocPhan ?? 'Khóa luận tốt nghiệp' }}
                                </span>
                                <span class="badge bg-light text-dark border">
                                    <i class="fa-solid fa-user-group me-1 text-secondary"></i>Tối đa {{ $dt->SoLuongSinhVienToiDa ?? 2 }} SV
                                </span>
                                @if($dt->nganh)
                                    <span class="badge bg-light text-secondary border">{{ $dt->nganh->TenNganh }}</span>
                                @else
                                    <span class="badge bg-light text-secondary border">Toàn khoa</span>
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

                            @if(in_array($dt->TrangThai, ['Yêu cầu chỉnh sửa', 'Yêu cầu điều chỉnh', 'Từ chối']) && $dt->LyDoTuChoi)
                                <div class="small text-danger mt-1 p-2 bg-danger-subtle rounded border border-danger-subtle">
                                    <strong><i class="fa-solid fa-circle-exclamation me-1"></i>Ý kiến phản hồi:</strong> {{ $dt->LyDoTuChoi }}
                                </div>
                            @endif
                        </td>
                        <td><span class="badge bg-light text-secondary border">{{ $dt->LinhVuc ?? 'CNTT' }}</span></td>
                        <td>
                            @if($dangKyApproved && $dangKyApproved->nhom)
                                <div class="fw-bold text-success"><i class="fa-solid fa-users me-1"></i>{{ $dangKyApproved->nhom->TenNhom }}</div>
                                <div class="small text-muted">
                                    {{ $dangKyApproved->nhom->thanhViens->where('TrangThai', 'da_tham_gia')->count() }}/{{ $dt->SoLuongSinhVienToiDa ?? 3 }} SV
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
                                <span class="badge bg-primary rounded-pill px-3"><i class="fa-solid fa-bullhorn me-1"></i>Đã công bố</span>
                            @elseif($dt->TrangThai === 'Trưởng khoa đã duyệt - Chờ nộp đề cương')
                                <span class="badge bg-warning text-dark rounded-pill px-3"><i class="fa-solid fa-file-arrow-up me-1"></i>Khoa duyệt - Chờ nộp ĐC</span>
                            @elseif($dt->TrangThai === 'Đã nộp đề cương - Chờ phân công PB')
                                <span class="badge bg-info text-white rounded-pill px-3"><i class="fa-solid fa-file-circle-check me-1"></i>Đã nộp ĐC - Chờ PB</span>
                            @elseif($dt->TrangThai === 'Đã cập nhật đề cương - Chờ phản biện lại')
                                <span class="badge bg-primary text-white rounded-pill px-3"><i class="fa-solid fa-rotate me-1"></i>Đã nộp lại ĐC - Chờ PB</span>
                            @elseif($dt->TrangThai === 'Đang phản biện đề cương')
                                <span class="badge bg-info-subtle text-info border rounded-pill px-3"><i class="fa-solid fa-spinner me-1"></i>Đang phản biện</span>
                            @elseif($dt->TrangThai === 'Đã phản biện - Chờ duyệt BM' || $dt->TrangThai === 'Đã phản biện - Chờ TBM duyệt đề cương')
                                <span class="badge bg-primary-subtle text-primary border rounded-pill px-3"><i class="fa-solid fa-check-double me-1"></i>Đã PB - Chờ TBM</span>
                            @elseif($dt->TrangThai === 'Chờ duyệt cấp Khoa')
                                <span class="badge bg-danger rounded-pill px-3"><i class="fa-solid fa-paper-plane me-1"></i>Chờ Khoa duyệt</span>
                            @elseif($dt->TrangThai === 'Chờ duyệt cấp Bộ môn')
                                <span class="badge bg-warning-subtle text-warning-emphasis border rounded-pill px-3"><i class="fa-solid fa-clock me-1"></i>Chờ BM duyệt</span>
                            @elseif($dt->TrangThai === 'Yêu cầu chỉnh sửa đề cương' || $dt->TrangThai === 'Yêu cầu chỉnh sửa' || $dt->TrangThai === 'Yêu cầu điều chỉnh')
                                <span class="badge bg-warning text-dark rounded-pill px-3"><i class="fa-solid fa-wrench me-1"></i>Cần sửa</span>
                            @elseif($dt->TrangThai === 'Không đạt phản biện' || $dt->TrangThai === 'Từ chối')
                                <span class="badge bg-danger rounded-pill px-3"><i class="fa-solid fa-xmark me-1"></i>Từ chối</span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-3">{{ $dt->TrangThai }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1 flex-wrap">
                                @if($canNopDeCuong)
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-2 py-1 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNopDeCuong{{ $dt->MaDeTai }}" title="Nộp Đề Cương Chi Tiết">
                                        <i class="fa-solid fa-file-arrow-up me-1"></i>{{ $isNopLai ? 'Nộp lại ĐC' : 'Nộp Đề Cương' }}
                                    </button>
                                @endif
                                @if(in_array($dt->TrangThai, ['Đã duyệt', 'Trưởng khoa đã duyệt', 'Đã công bố']) && !$hasGroupAssigned)
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1" data-bs-toggle="modal" data-bs-target="#modalGanNhom{{ $dt->MaDeTai }}" title="Gán nhóm cho đề tài này">
                                        <i class="fa-solid fa-user-check me-1"></i>Gán Nhóm
                                    </button>
                                @endif
                                <a href="{{ route('giangvien.detai.edit', $dt->MaDeTai) }}" class="btn btn-sm btn-light text-primary rounded-circle" title="Sửa">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                @if(!$hasGroupAssigned)
                                <form action="{{ route('giangvien.detai.destroy', $dt->MaDeTai) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đề tài này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle" title="Xóa">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- MODAL NỘP ĐỀ CƯƠNG CHI TIẾT (CHỈ HIỂN THỊ KHI ĐÃ ĐƯỢC GÁN CHO NHÓM SINH VIÊN) -->
                    @if($canNopDeCuong)
                    <div class="modal fade" id="modalNopDeCuong{{ $dt->MaDeTai }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('giangvien.detai.nopDeCuong', $dt->MaDeTai) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-header">
                                        <h6 class="modal-title fw-bold text-primary">
                                            <i class="fa-solid fa-file-arrow-up me-2"></i>{{ $isNopLai ? 'Nộp Lại Đề Cương Chi Tiết' : 'Nộp Đề Cương Chi Tiết' }}
                                        </h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label class="form-label text-muted small mb-0">Tên đề tài:</label>
                                            <div class="fw-bold text-dark">{{ $dt->TenDeTai }}</div>
                                        </div>
                                        <div class="alert alert-info py-2 small my-3">
                                            <i class="fa-solid fa-circle-info me-1"></i>
                                            Đề tài đã được gán cho nhóm <strong>{{ $dangKyApproved->nhom->TenNhom ?? 'sinh viên' }}</strong>. Vui lòng tải lên file Đề cương chi tiết (.pdf, .doc, .docx) để Trưởng bộ môn tiến hành phân công Giảng viên phản biện.
                                        </div>

                                        <div class="p-2 mb-3 bg-light rounded-3 border d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <div class="small text-muted">
                                                <i class="fa-solid fa-file-word text-primary me-1"></i> Chưa có biểu mẫu đề cương chuẩn?
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3 dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                                                    <i class="fa-solid fa-download me-1"></i> Tải file mẫu (.docx)
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 small">
                                                    <li><h6 class="dropdown-header small fw-bold text-muted">Chọn biểu mẫu theo môn/học phần</h6></li>
                                                    <li><a class="dropdown-item py-1.5" href="{{ route('giangvien.detai.download_template', ['type' => 'cu_nhan']) }}"><i class="fa-solid fa-graduation-cap text-primary me-2"></i> Khóa Luận Cử Nhân</a></li>
                                                    <li><a class="dropdown-item py-1.5" href="{{ route('giangvien.detai.download_template', ['type' => 'ky_su']) }}"><i class="fa-solid fa-gears text-success me-2"></i> Khóa Luận Kỹ Sư</a></li>
                                                    <li><a class="dropdown-item py-1.5" href="{{ route('giangvien.detai.download_template', ['type' => 'do_an']) }}"><i class="fa-solid fa-folder-open text-warning me-2"></i> Đồ Án Tốt Nghiệp</a></li>
                                                </ul>
                                            </div>
                                        </div>

                                        @if($dt->FileDeCuong)
                                        @php
                                            $filePathModal = Str::startsWith($dt->FileDeCuong, ['http', 'storage/']) ? asset($dt->FileDeCuong) : asset('storage/' . $dt->FileDeCuong);
                                        @endphp
                                        <div class="mb-3 p-2 bg-light rounded border d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="small text-muted d-block">File đề cương đã nộp:</span>
                                                <strong class="small text-dark">{{ basename($dt->FileDeCuong) }}</strong>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                    onclick="quickPreviewOutline('{{ $filePathModal }}', '{{ basename($dt->FileDeCuong) }}', '{{ addslashes($dt->TenDeTai) }}')">
                                                <i class="fa-solid fa-eye me-1"></i>Xem nhanh
                                            </button>
                                        </div>
                                        @endif

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Chọn File Đề Cương Chi Tiết <span class="text-danger">*</span>:</label>
                                            <input type="file" name="FileDeCuong" class="form-control" accept=".pdf,.doc,.docx" required>
                                            <div class="form-text text-muted small">
                                                Định dạng chấp nhận: .pdf, .doc, .docx (Dung lượng tối đa 10MB).
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-semibold shadow-sm">
                                            <i class="fa-solid fa-paper-plane me-1"></i>Xác Nhận Nộp Đề Cương
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif

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
                                                    @endphp
                                                    <option value="{{ $nhomOption->MaNhom }}">
                                                        {{ $nhomOption->TenNhom }} (Trưởng nhóm: {{ $nhomOption->truongNhom->HoTen ?? 'Chưa rõ' }} - {{ $soTV }}/3 SV)
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
                            <i class="fa-solid fa-folder-open fs-1 text-light mb-3 d-block"></i>
                            Bạn chưa đề xuất đề tài nào. Bấm <strong>"Đề Xuất Đề Tài Mới"</strong> để bắt đầu.
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