@extends('layouts.giangvien')
@section('page_title', 'Duyệt Nhóm Đăng Ký Đề Tài')
@section('content')
<div class="card card-premium">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Tên Nhóm</th>
                    <th>Đề Tài Đăng Ký</th>
                    <th>Ngày Đăng Ký</th>
                    <th>Trạng Thái</th>
                    <th class="text-center">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dangkys as $dk)
                <tr>
                    <td class="fw-bold">{{ $dk->nhomDoAn->TenNhom ?? '' }}</td>
                    <td class="text-primary">{{ $dk->deTai->TenDeTai ?? '' }}</td>
                    <td>{{ $dk->NgayDangKy }}</td>
                    <td>
                        @if($dk->TrangThai == 'Chờ duyệt')
                            <span class="badge bg-warning text-dark">Chờ duyệt</span>
                        @elseif($dk->TrangThai == 'Đã duyệt')
                            <span class="badge bg-success">Đã duyệt</span>
                        @else
                            <span class="badge bg-danger">Từ chối</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($dk->TrangThai == 'Chờ duyệt')
                        <form action="{{ route('giangvien.duyet.update', $dk->MaDangKy) }}" method="POST" class="d-inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="TrangThai" value="Đã duyệt">
                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3"><i class="fa-solid fa-check"></i> Duyệt</button>
                        </form>
                        <form action="{{ route('giangvien.duyet.update', $dk->MaDangKy) }}" method="POST" class="d-inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="TrangThai" value="Từ chối">
                            <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3"><i class="fa-solid fa-xmark"></i> Từ chối</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Chưa có nhóm nào đăng ký đề tài của bạn</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $dangkys->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection