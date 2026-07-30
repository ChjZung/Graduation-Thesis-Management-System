@extends('layouts.giangvien')
@section('page_title', 'Quản Lý Sản Phẩm & Báo Cáo Nhóm')
@section('content')

{{-- Import Result --}}
@if(session('import_result'))
<div class="alert alert-info alert-dismissible fade show">
    <i class="fa-solid fa-circle-info me-2"></i>
    {!! session('import_result') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-premium mb-4 shadow-sm">
    <div class="card-header-premium d-flex justify-content-between align-items-center bg-white p-3 border-bottom">
        <span class="fw-bold fs-5 text-primary"><i class="fa-solid fa-box-open me-2"></i>Danh Sách Nhóm Tôi Hướng Dẫn</span>
        <form method="GET" action="{{ route('giangvien.sanpham.index') }}" class="d-flex gap-2 align-items-center">
            <select name="maNhom" class="form-select form-select-sm" style="width:220px">
                <option value="">— Tất cả nhóm —</option>
                @foreach($allNhoms as $n)
                    <option value="{{ $n->MaNhom }}" {{ $selectedNhomId == $n->MaNhom ? 'selected' : '' }}>
                        {{ $n->TenNhom }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-filter me-1"></i>Lọc
            </button>
            @if($selectedNhomId)
                <a href="{{ route('giangvien.sanpham.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-xmark me-1"></i>Xoá lọc
                </a>
            @endif
        </form>
    </div>

    <div class="card-body p-0">
        @forelse($nhoms as $nhom)
        <div class="border-bottom p-4">
            {{-- Header nhóm --}}
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="fa-solid fa-users text-primary me-2"></i>{{ $nhom->TenNhom }}
                        <span class="badge bg-{{ $nhom->TrangThai == 'Đang hoạt động' || $nhom->TrangThai == 'Đã nộp sản phẩm' ? 'success' : 'secondary' }} ms-2 fs-7">
                            {{ $nhom->TrangThai }}
                        </span>
                    </h5>
                    <div class="text-muted small">
                        <i class="fa-solid fa-book me-1"></i>Môn: <strong>{{ $nhom->monHoc->TenMon ?? '—' }}</strong>
                        &nbsp;|&nbsp;
                        <i class="fa-solid fa-graduation-cap me-1"></i>Đề tài:
                        @if($nhom->dangKyDeTai && $nhom->dangKyDeTai->deTai)
                            <strong class="text-primary">{{ $nhom->dangKyDeTai->deTai->TenDeTai }}</strong>
                            <span class="badge bg-{{ $nhom->dangKyDeTai->TrangThai == 'Đã duyệt' ? 'success' : ($nhom->dangKyDeTai->TrangThai == 'Từ chối' ? 'danger' : 'warning text-dark') }} ms-1">
                                {{ $nhom->dangKyDeTai->TrangThai }}
                            </span>
                        @else
                            <span class="text-muted">Chưa đăng ký</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Thành viên --}}
            <div class="mb-3">
                <span class="text-muted small fw-semibold"><i class="fa-solid fa-user-group me-1"></i>Thành viên nhóm:</span>
                <div class="d-flex flex-wrap gap-2 mt-1">
                    @foreach($nhom->thanhVienNhoms->whereIn('TrangThai', ['da_chap_nhan', 'da_tham_gia']) as $tv)
                    <span class="badge bg-light text-dark border">
                        {{ $tv->sinhVien->HoTen ?? '?' }}
                        @if($tv->VaiTro == 'Trưởng nhóm' || $nhom->TruongNhom == $tv->MaSV)
                            <i class="fa-solid fa-crown text-warning ms-1" title="Trưởng nhóm"></i>
                        @endif
                    </span>
                    @endforeach
                </div>
            </div>

            {{-- Accordion báo cáo tiến độ --}}
            <div class="accordion accordion-flush" id="acc-nhom-{{ $nhom->MaNhom }}">
                <div class="accordion-item border rounded mb-2">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-semibold py-2" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapse-bc-{{ $nhom->MaNhom }}">
                            <i class="fa-solid fa-clipboard-list me-2 text-info"></i>
                            Báo cáo tiến độ
                            <span class="badge bg-info text-dark ms-2">{{ $nhom->baoCaos->count() }}</span>
                        </button>
                    </h2>
                    <div id="collapse-bc-{{ $nhom->MaNhom }}" class="accordion-collapse collapse">
                        <div class="accordion-body p-0">
                            @if($nhom->baoCaos->isEmpty())
                                <p class="text-muted text-center py-3 mb-0">Nhóm chưa nộp báo cáo tiến độ nào.</p>
                            @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Lần</th>
                                            <th>Nội dung</th>
                                            <th>Ngày nộp</th>
                                            <th>File / Link</th>
                                            <th>Thao tác</th>
                                            <th>Nhận xét</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($nhom->baoCaos as $bc)
                                        @php
                                            $fileBc = $bc->FileBaoCao;
                                            $isUrlBc = filter_var($fileBc, FILTER_VALIDATE_URL) || str_starts_with($fileBc, 'http://') || str_starts_with($fileBc, 'https://');
                                            $targetUrlBc = $isUrlBc ? $fileBc : asset($fileBc ? (str_starts_with($fileBc, '/') ? $fileBc : '/' . $fileBc) : '#');
                                        @endphp
                                        <tr>
                                            <td><span class="badge bg-primary rounded-pill">{{ $bc->LanBaoCao }}</span></td>
                                            <td class="small">{{ Str::limit($bc->NoiDung, 60) }}</td>
                                            <td class="small text-muted">{{ \Carbon\Carbon::parse($bc->NgayNop)->format('d/m/Y') }}</td>
                                            <td>
                                                @if($fileBc)
                                                    <span class="badge bg-light text-secondary border">
                                                        <i class="{{ $isUrlBc ? 'fa-solid fa-link' : 'fa-solid fa-file' }} me-1"></i>
                                                        {{ $isUrlBc ? 'Link đính kèm' : 'File đính kèm' }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($fileBc)
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ $targetUrlBc }}" target="_blank" class="btn btn-outline-primary py-0 px-2" title="Xem file / link">
                                                            <i class="fa-solid fa-eye me-1"></i>Xem file
                                                        </a>
                                                        <a href="{{ $targetUrlBc }}" download class="btn btn-outline-success py-0 px-2" title="Tải về">
                                                            <i class="fa-solid fa-download me-1"></i>Download
                                                        </a>
                                                    </div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($bc->nhanXets->isNotEmpty())
                                                    <span class="text-success small">
                                                        <i class="fa-solid fa-check-circle me-1"></i>
                                                        {{ Str::limit($bc->nhanXets->last()->NoiDung ?? '', 50) }}
                                                    </span>
                                                @else
                                                    <form action="{{ route('giangvien.baocao.nhanxet', $bc->MaBaoCao) }}" method="POST" class="d-flex gap-1">
                                                        @csrf
                                                        <input type="text" name="NoiDung" class="form-control form-control-sm" placeholder="Nhận xét..." required>
                                                        <button type="submit" class="btn btn-sm btn-success px-2">
                                                            <i class="fa-solid fa-paper-plane"></i>
                                                        </button>
                                                    </form>
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

                {{-- Accordion sản phẩm --}}
                <div class="accordion-item border rounded">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-semibold py-2" type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapse-sp-{{ $nhom->MaNhom }}">
                            <i class="fa-solid fa-box me-2 text-warning"></i>
                            Sản phẩm nộp
                            <span class="badge bg-warning text-dark ms-2">{{ $nhom->sanPhams->count() }}</span>
                        </button>
                    </h2>
                    <div id="collapse-sp-{{ $nhom->MaNhom }}" class="accordion-collapse collapse">
                        <div class="accordion-body p-0">
                            @if($nhom->sanPhams->isEmpty())
                                <p class="text-muted text-center py-3 mb-0">Nhóm chưa nộp sản phẩm.</p>
                            @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tên sản phẩm / Source code</th>
                                            <th>Ngày nộp</th>
                                            <th>File / Link</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($nhom->sanPhams as $sp)
                                        @php
                                            $linkSp = $sp->LinkFile;
                                            $isUrlSp = filter_var($linkSp, FILTER_VALIDATE_URL) || str_starts_with($linkSp, 'http://') || str_starts_with($linkSp, 'https://');
                                            $targetUrlSp = $isUrlSp ? $linkSp : asset($linkSp ? (str_starts_with($linkSp, '/') ? $linkSp : '/' . $linkSp) : '#');
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">{{ $sp->TenSanPham ?? 'Sản phẩm đồ án' }}</td>
                                            <td class="small text-muted">{{ \Carbon\Carbon::parse($sp->NgayNop)->format('d/m/Y') }}</td>
                                            <td>
                                                @if($linkSp)
                                                    <span class="badge bg-light text-secondary border">
                                                        <i class="{{ $isUrlSp ? 'fa-brands fa-github' : 'fa-solid fa-file-zipper' }} me-1"></i>
                                                        {{ $isUrlSp ? 'Link đính kèm' : 'File đính kèm' }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($linkSp)
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ $targetUrlSp }}" target="_blank" class="btn btn-outline-primary py-0 px-2" title="Xem sản phẩm">
                                                            <i class="fa-solid fa-eye me-1"></i>Xem file
                                                        </a>
                                                        <a href="{{ $targetUrlSp }}" download class="btn btn-outline-success py-0 px-2" title="Download">
                                                            <i class="fa-solid fa-download me-1"></i>Download file
                                                        </a>
                                                    </div>
                                                @else
                                                    <span class="text-muted">—</span>
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
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-inbox fa-3x mb-3 opacity-25"></i>
            <p class="fs-5 mb-0">Bạn chưa có nhóm nào được phân công hướng dẫn.</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Pagination --}}
<div class="d-flex justify-content-center">
    {{ $nhoms->links('pagination::bootstrap-5') }}
</div>

@endsection
