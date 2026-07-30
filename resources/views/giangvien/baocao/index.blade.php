@extends('layouts.giangvien')
@section('title', 'Duyệt Báo Cáo Tiến Độ')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-primary-custom"><i class="fa-solid fa-clipboard-check me-2"></i>Duyệt Báo Cáo Nhóm</h4>
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
                    <th class="px-4">Nhóm</th>
                    <th>Lần</th>
                    <th>Nội dung SV</th>
                    <th>File</th>
                    <th>Nhận Xét Của GV</th>
                    <th class="text-end px-4">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($baocaos as $bc)
                <tr>
                    <td class="px-4 fw-bold text-muted">{{ $bc->nhomDoAn->TenNhom }}</td>
                    <td>Lần {{ $bc->LanBaoCao }}</td>
                    <td>{{ Str::limit($bc->NoiDung, 50) }}</td>
                    <td>
                        @if($bc->FileBaoCao)
                            @if(Str::startsWith($bc->FileBaoCao, '/storage') || Str::contains($bc->FileBaoCao, 'storage/'))
                                <a href="{{ asset($bc->FileBaoCao) }}" download class="btn btn-sm btn-outline-success rounded-pill px-3"><i class="fa-solid fa-download me-1"></i>Tải File .Zip</a>
                            @else
                                <a href="{{ $bc->FileBaoCao }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3"><i class="fa-solid fa-link me-1"></i>Xem Link</a>
                            @endif
                        @else
                            <span class="text-muted small">Không có</span>
                        @endif
                    </td>
                    <td>
                        @if($bc->nhanXets->count() > 0)
                            <span class="text-success small"><i class="fa-solid fa-check"></i> Đã nhận xét</span>
                        @else
                            <span class="text-warning small text-dark">Chưa nhận xét</span>
                        @endif
                    </td>
                    <td class="text-end px-4">
                        <button class="btn btn-sm btn-primary-custom rounded-pill" data-bs-toggle="modal" data-bs-target="#nxModal{{ $bc->MaBaoCao }}">
                            Nhận xét
                        </button>
                    </td>
                </tr>
                
                <!-- Modal Nhận Xét -->
                <div class="modal fade" id="nxModal{{ $bc->MaBaoCao }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form action="{{ route('giangvien.baocao.nhanxet', $bc->MaBaoCao) }}" method="POST">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Nhận Xét Lần {{ $bc->LanBaoCao }} - {{ $bc->nhomDoAn->TenNhom }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-start">
                                    <div class="p-3 bg-light rounded mb-3">
                                        <p class="mb-1 fw-bold small text-muted">Nội dung sinh viên nộp:</p>
                                        <p class="mb-0">{{ $bc->NoiDung }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold">Nhận xét của giảng viên <span class="text-danger">*</span></label>
                                        <textarea name="NoiDung" class="form-control" rows="4" required placeholder="Nhập phản hồi..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Hủy</button>
                                    <button type="submit" class="btn btn-primary-custom rounded-pill">Gửi Nhận Xét</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">
    {{ $baocaos->links('pagination::bootstrap-5') }}
</div>
@endsection
