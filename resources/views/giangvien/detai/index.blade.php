@extends('layouts.giangvien')
@section('page_title', 'Quản Lý Đề Tài')
@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-premium shadow-sm mb-4">
    <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
        <span class="fw-bold fs-5 text-primary"><i class="fa-solid fa-book me-2"></i>Danh Sách Đề Tài Phụ Trách ({{ $detais->total() }})</span>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.import.template', 'detais') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-file-arrow-down me-1"></i>File mẫu .xlsx
            </a>
            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa-solid fa-file-excel me-1"></i>Import Excel
            </button>
            <a href="{{ route('giangvien.detai.create') }}" class="btn btn-success btn-sm rounded-pill px-3">
                <i class="fa-solid fa-plus me-1"></i>Thêm đề tài
            </a>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="card-body bg-light border-bottom p-3">
        <form method="GET" action="{{ route('giangvien.detai.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Học kỳ</label>
                <select name="MaHocKy" class="form-select form-select-sm">
                    <option value="">-- Tất cả học kỳ --</option>
                    @foreach($hockys as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Lớp phụ trách</label>
                <select name="MaLop" class="form-select form-select-sm">
                    <option value="">-- Tất cả lớp --</option>
                    @foreach($lops as $l)
                        <option value="{{ $l->MaLop }}" {{ request('MaLop') == $l->MaLop ? 'selected' : '' }}>
                            {{ $l->TenLop }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Môn học</label>
                <select name="MaMon" class="form-select form-select-sm">
                    <option value="">-- Tất cả môn --</option>
                    @foreach($monhocs as $mh)
                        <option value="{{ $mh->MaMon }}" {{ request('MaMon') == $mh->MaMon ? 'selected' : '' }}>
                            {{ $mh->TenMon }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 w-100">
                    <i class="fa-solid fa-filter me-1"></i>Lọc
                </button>
                <a href="{{ route('giangvien.detai.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="Xóa lọc">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if(session('import_result'))
        <div class="alert alert-info alert-dismissible fade show m-3">
            <i class="fa-solid fa-circle-info me-2"></i>
            {!! session('import_result') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Mã</th>
                    <th>Tên Đề Tài</th>
                    <th>Lớp & Môn Học</th>
                    <th>Học Kỳ</th>
                    <th>Hạn Đăng Ký</th>
                    <th>Hạn Báo Cáo</th>
                    <th>Hạn Nộp SP</th>
                    <th>Trạng Thái</th>
                    <th>Tài Liệu</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detais as $dt)
                <tr>
                    <td class="text-muted small">{{ $dt->MaDeTai }}</td>
                    <td class="fw-semibold text-primary" style="max-width:220px">
                        <span title="{{ $dt->TenDeTai }}">{{ Str::limit($dt->TenDeTai, 55) }}</span>
                    </td>
                    <td class="small">
                        <strong>{{ $dt->lop->TenLop ?? '—' }}</strong><br>
                        <span class="text-muted">{{ $dt->monHoc->TenMon ?? '—' }}</span>
                    </td>
                    <td class="small text-muted">{{ $dt->hocKy->TenHocKy ?? '—' }}</td>
                    <td class="small {{ $dt->HanDangKy && \Carbon\Carbon::parse($dt->HanDangKy)->isPast() ? 'text-danger' : 'text-success' }}">
                        {{ $dt->HanDangKy ? \Carbon\Carbon::parse($dt->HanDangKy)->format('d/m/Y') : '—' }}
                    </td>
                    <td class="small {{ $dt->HanBaoCao && \Carbon\Carbon::parse($dt->HanBaoCao)->isPast() ? 'text-danger' : '' }}">
                        {{ $dt->HanBaoCao ? \Carbon\Carbon::parse($dt->HanBaoCao)->format('d/m/Y') : '—' }}
                    </td>
                    <td class="small {{ $dt->HanNopSanPham && \Carbon\Carbon::parse($dt->HanNopSanPham)->isPast() ? 'text-danger' : '' }}">
                        {{ $dt->HanNopSanPham ? \Carbon\Carbon::parse($dt->HanNopSanPham)->format('d/m/Y') : '—' }}
                    </td>
                    <td>
                        <span class="badge bg-{{ $dt->TrangThai == 'Đang mở đăng ký' ? 'success' : ($dt->TrangThai == 'Đã đăng ký' ? 'primary' : 'secondary') }} rounded-pill">
                            {{ $dt->TrangThai }}
                        </span>
                    </td>
                    <td>
                        @if($dt->FileTaiLieu)
                            <a href="{{ route('giangvien.detai.downloadTaiLieu', $dt->MaDeTai) }}" class="btn btn-sm btn-outline-info rounded-pill" title="Tải xuống tài liệu đính kèm">
                                <i class="fa-solid fa-file-arrow-down me-1"></i> Tải về
                            </a>
                        @else
                            <button type="button" class="btn btn-sm btn-light text-muted rounded-pill" data-bs-toggle="modal" data-bs-target="#uploadDocModal{{ $dt->MaDeTai }}" title="Tải lên tài liệu PDF/Word/ZIP">
                                <i class="fa-solid fa-paperclip me-1"></i> Đính kèm
                            </button>
                        @endif
                    </td>
                    <td class="text-nowrap">
                        <button type="button" class="btn btn-sm btn-light text-secondary me-1" data-bs-toggle="modal" data-bs-target="#uploadDocModal{{ $dt->MaDeTai }}" title="Tải lên/Cập nhật file">
                            <i class="fa-solid fa-upload"></i>
                        </button>
                        <a href="{{ route('giangvien.detai.edit', $dt->MaDeTai) }}" class="btn btn-sm btn-light text-primary me-1" title="Sửa">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form action="{{ route('giangvien.detai.destroy', $dt->MaDeTai) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Xoá đề tài này?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-light text-danger" title="Xoá">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>

                <!-- MODAL UPLOAD TÀI LIỆU -->
                <div class="modal fade" id="uploadDocModal{{ $dt->MaDeTai }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form action="{{ route('giangvien.detai.uploadTaiLieu', $dt->MaDeTai) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title"><i class="fa-solid fa-paperclip me-2"></i>Đính Kèm Tài Liệu Đề Tài</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-2"><strong>Đề tài:</strong> {{ $dt->TenDeTai }}</p>
                                    @if($dt->FileTaiLieu)
                                        <div class="alert alert-info py-2 small d-flex justify-content-between align-items-center mb-3">
                                            <span><i class="fa-solid fa-file-lines me-1"></i>Đã có tài liệu: <code>{{ basename($dt->FileTaiLieu) }}</code></span>
                                            <a href="{{ route('giangvien.detai.downloadTaiLieu', $dt->MaDeTai) }}" class="btn btn-sm btn-info text-white">Tải về</a>
                                        </div>
                                    @endif
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Chọn tệp (.pdf, .doc, .docx, .zip, .rar - tối đa 20MB)</label>
                                        <input type="file" name="file_tai_lieu" class="form-control" accept=".pdf,.doc,.docx,.zip,.rar" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-upload me-1"></i>Lưu Tệp</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-5">
                    <i class="fa-solid fa-inbox fa-2x mb-2 opacity-25 d-block"></i>
                    Không có đề tài nào phù hợp.
                </td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="p-3 d-flex justify-content-center">
            {{ $detais->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('giangvien.detai.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-file-excel me-2"></i>Import Đề Tài từ Excel/CSV</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning d-flex gap-2 align-items-start">
                        <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                        <div>
                            <strong>Hướng dẫn:</strong> Tải file mẫu, điền dữ liệu đúng định dạng rồi upload lại.<br>
                            Định dạng cột: <code>TenDeTai, MaMon, MaHocKy, MaLop, MoTa, YeuCau, HanDangKy, HanBaoCao, HanNopSanPham</code>
                            <br>
                            <a href="{{ route('admin.import.template', 'detais') }}" class="btn btn-sm btn-outline-success mt-2 rounded-pill px-3">
                                <i class="fa-solid fa-download me-1"></i>Tải file mẫu .xlsx
                            </a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn file CSV/Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required accept=".csv,.xlsx">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="fa-solid fa-upload me-1"></i>Tải Lên & Import
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection