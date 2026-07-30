@extends('layouts.sinhvien')
@section('title', 'Nộp Báo Cáo Tiến Độ')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 text-primary-custom"><i class="fa-solid fa-file-invoice me-2"></i>Báo Cáo Tiến Độ - {{ $nhom->TenNhom ?? '' }}</h4>
        <small class="text-muted"><i class="fa-solid fa-book me-1"></i>Môn: {{ $nhom->monHoc->TenMon ?? 'N/A' }}</small>
    </div>

    <div class="d-flex gap-2 align-items-center">
        @if(isset($allNhoms) && $allNhoms->count() > 1)
        <form method="GET" action="{{ route('sinhvien.baocao.index') }}" class="d-flex gap-2 align-items-center me-2">
            <select name="maNhom" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach($allNhoms as $n)
                    <option value="{{ $n->MaNhom }}" {{ $nhom->MaNhom == $n->MaNhom ? 'selected' : '' }}>
                        {{ $n->TenNhom }} ({{ $n->monHoc->TenMon ?? '' }})
                    </option>
                @endforeach
            </select>
        </form>
        @endif

        @if($nhom->TruongNhom == $sv->MaSV)
        <button class="btn btn-primary-custom rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-upload me-2"></i>Nộp Báo Cáo
        </button>
        @endif
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-premium">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4">Lần nộp</th>
                    <th>Nội dung</th>
                    <th>File đính kèm</th>
                    <th>Ngày nộp</th>
                    <th>Trạng thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($baocaos as $bc)
                <tr>
                    <td class="px-4 fw-bold text-muted">Lần {{ $bc->LanBaoCao }}</td>
                    <td>{{ $bc->NoiDung }}</td>
                    <td>
                        @if($bc->FileBaoCao)
                            @if(Str::startsWith($bc->FileBaoCao, '/storage') || Str::contains($bc->FileBaoCao, 'storage/'))
                                <a href="{{ asset($bc->FileBaoCao) }}" download class="btn btn-sm btn-outline-success rounded-pill px-3"><i class="fa-solid fa-download me-1"></i>Tải File</a>
                            @else
                                <a href="{{ $bc->FileBaoCao }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3"><i class="fa-solid fa-link me-1"></i>Mở Link</a>
                            @endif
                        @else
                            <span class="text-muted small">Không có</span>
                        @endif
                    </td>
                    <td>{{ date('d/m/Y', strtotime($bc->NgayNop)) }}</td>
                    <td>
                        <span class="badge {{ $bc->TrangThai == 'Đã nhận xét' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $bc->TrangThai }}</span>
                    </td>
                    <td class="text-center">
                        @if($bc->TrangThai == 'Đã nhận xét')
                            @php
                                $allNhanXet = '';
                                if ($bc->nhanXets->count() > 0) {
                                    foreach($bc->nhanXets as $nx) {
                                        $date = date('d/m/Y', strtotime($nx->NgayNhanXet));
                                        $noiDung = nl2br(htmlspecialchars($nx->NoiDung));
                                        $allNhanXet .= "<div class='mb-2 border-bottom border-light pb-2'><strong class='text-primary'>[{$date}]</strong>: {$noiDung}</div>";
                                    }
                                } else {
                                    $allNhanXet = 'Không có nội dung nhận xét.';
                                }
                            @endphp
                            <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3" data-nhanxet="{{ $allNhanXet }}" onclick="showNhanXet(this)">
                                <i class="fa-solid fa-eye me-1"></i>Xem
                            </button>
                        @else
                            <span class="text-muted small">Chưa có</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Nhóm chưa nộp báo cáo nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Nộp Báo Cáo -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('sinhvien.baocao.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-file-arrow-up me-2"></i>Nộp Báo Cáo Mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Nội dung báo cáo <span class="text-danger">*</span></label>
                        <textarea name="NoiDung" class="form-control" rows="4" required placeholder="Tóm tắt công việc tiến độ đã làm..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Tải Lên File Đính Kèm (.ZIP, .RAR, .PDF, .DOCX)</label>
                        <input type="file" name="FileUpLoad" class="form-control" accept=".zip,.rar,.pdf,.docx,.doc">
                        <div class="form-text text-muted">Dung lượng tối đa 20MB.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Hoặc Dán Link File (Google Drive, GitHub...)</label>
                        <input type="url" name="FileBaoCao" class="form-control" placeholder="https://drive.google.com/...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4">Nộp Báo Cáo</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function showNhanXet(btn) {
        let noiDung = btn.getAttribute('data-nhanxet') || 'Không có nội dung nhận xét.';
        
        Swal.fire({
            title: '<h4 class="text-primary-custom mb-0"><i class="fa-solid fa-comment-dots me-2"></i>Nhận Xét Của Giảng Viên</h4>',
            html: '<div class="text-start bg-light p-3 rounded border text-dark mt-3" style="font-size: 1.1rem; line-height: 1.6;">' + noiDung + '</div>',
            confirmButtonText: '<i class="fa-solid fa-check me-1"></i>Đã Hiểu',
            confirmButtonColor: '#3699ff',
            background: '#fff',
            borderRadius: '1rem',
            width: '600px'
        });
    }
</script>
@endsection
