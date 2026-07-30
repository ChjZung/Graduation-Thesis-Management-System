@extends('layouts.admin')
@section('page_title', 'Quản Lý Sản Phẩm Đồ Án')
@section('content')

<div class="card card-premium shadow-sm mb-4">
    <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary fw-bold">
            <i class="fa-solid fa-boxes-packing me-2"></i>Quản Lý Sản Phẩm Đồ Án Sinh Viên
        </h5>
    </div>
    
    <div class="card-body bg-light border-bottom">
        <form method="GET" action="{{ route('admin.sanpham.index') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Học kỳ</label>
                <select name="MaHocKy" class="form-select form-select-sm">
                    <option value="">-- Tất cả --</option>
                    @foreach($hockys as $hk)
                        <option value="{{ $hk->MaHocKy }}" {{ request('MaHocKy') == $hk->MaHocKy ? 'selected' : '' }}>
                            {{ $hk->TenHocKy }} ({{ $hk->NamHoc }})
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

            <div class="col-md-2">
                <label class="form-label small fw-semibold">Lớp học</label>
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
                <label class="form-label small fw-semibold">Giảng viên hướng dẫn</label>
                <select name="MaGV" class="form-select form-select-sm">
                    <option value="">-- Tất cả giảng viên --</option>
                    @foreach($giangviens as $gv)
                        <option value="{{ $gv->MaGV }}" {{ request('MaGV') == $gv->MaGV ? 'selected' : '' }}>
                            {{ $gv->HoTen }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100">
                    <i class="fa-solid fa-filter me-1"></i>Lọc
                </button>
                <a href="{{ route('admin.sanpham.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill" title="Xóa lọc">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>STT</th>
                        <th>Nhóm & Môn Học</th>
                        <th>Đề Tài</th>
                        <th>Giảng Viên</th>
                        <th>File Sản Phẩm</th>
                        <th>File Báo Cáo</th>
                        <th>Ngày Nộp</th>
                        <th>Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sanphams as $index => $sp)
                    @php
                        $nhom = $sp->nhomDoAn;
                        $deTai = $nhom->dangKyDeTai->deTai ?? null;
                        $gvName = $deTai->giangVien->HoTen ?? '—';
                        $linkSp = $sp->LinkFile;
                        $isUrlSp = filter_var($linkSp, FILTER_VALIDATE_URL) || str_starts_with($linkSp, 'http://') || str_starts_with($linkSp, 'https://');
                        $targetUrlSp = $isUrlSp ? $linkSp : asset($linkSp ? (str_starts_with($linkSp, '/') ? $linkSp : '/' . $linkSp) : '#');
                    @endphp
                    <tr>
                        <td>{{ $sanphams->firstItem() + $index }}</td>
                        <td>
                            <strong class="text-primary">{{ $nhom->TenNhom ?? 'N/A' }}</strong><br>
                            <small class="text-muted"><i class="fa-solid fa-book me-1"></i>{{ $nhom->monHoc->TenMon ?? 'N/A' }}</small>
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $deTai->TenDeTai ?? 'Chưa đăng ký' }}</span>
                        </td>
                        <td>{{ $gvName }}</td>
                        <td>
                            @if($linkSp)
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ $targetUrlSp }}" target="_blank" class="btn btn-outline-primary py-0 px-2" title="Xem file">
                                        <i class="fa-solid fa-eye me-1"></i>Xem file
                                    </a>
                                    <a href="{{ $targetUrlSp }}" download class="btn btn-outline-success py-0 px-2" title="Download">
                                        <i class="fa-solid fa-download me-1"></i>Download
                                    </a>
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($sp->baoCaos->isNotEmpty())
                                @php
                                    $lastBc = $sp->baoCaos->last();
                                    $fileBc = $lastBc->FileBaoCao;
                                    $isUrlBc = filter_var($fileBc, FILTER_VALIDATE_URL) || str_starts_with($fileBc, 'http://') || str_starts_with($fileBc, 'https://');
                                    $targetUrlBc = $isUrlBc ? $fileBc : asset($fileBc ? (str_starts_with($fileBc, '/') ? $fileBc : '/' . $fileBc) : '#');
                                @endphp
                                @if($fileBc)
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ $targetUrlBc }}" target="_blank" class="btn btn-outline-info py-0 px-2" title="Xem báo cáo">
                                            <i class="fa-solid fa-file-pdf me-1"></i>Báo cáo Lần {{ $lastBc->LanBaoCao }}
                                        </a>
                                        <a href="{{ $targetUrlBc }}" download class="btn btn-outline-success py-0 px-2" title="Tải báo cáo">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="badge bg-light text-dark border">Có {{ $sp->baoCaos->count() }} báo cáo</span>
                                @endif
                            @else
                                <span class="text-muted">Chưa nộp</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ \Carbon\Carbon::parse($sp->NgayNop)->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge bg-success">Đã nộp sản phẩm</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-box-open fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0">Không tìm thấy sản phẩm đồ án nào phù hợp.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card-footer bg-white d-flex justify-content-center pt-3">
        {{ $sanphams->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>

@endsection
