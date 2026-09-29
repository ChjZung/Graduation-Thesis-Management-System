@extends('layouts.giangvien')

@section('page_title', 'Xác Nhận Hồ Sơ Bảo Vệ Khóa Luận')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--huit-blue-dark);">
                <i class="fa-solid fa-user-check me-2 text-primary"></i>
                Xác Nhận Điều Kiện Bảo Vệ Khóa Luận (Dành Cho GVHD)
            </h4>
            <p class="text-muted mb-0 small">
                Kiểm tra sản phẩm hoàn thiện và hồ sơ bảo vệ của các nhóm sinh viên quý Thầy/Cô hướng dẫn để xác nhận đủ điều kiện bảo vệ trước Hội đồng.
            </p>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-2">
            <ul class="nav nav-pills flex-column flex-sm-row gap-1">
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ !request()->filled('xac_nhan') ? 'active' : '' }}" 
                       href="{{ route('giangvien.xacnhan_baove.index') }}">
                        Tất cả <span class="badge bg-secondary ms-1">{{ $counts['total'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('xac_nhan') === 'Chờ xác nhận' ? 'active bg-warning text-dark' : '' }}" 
                       href="{{ route('giangvien.xacnhan_baove.index', ['xac_nhan' => 'Chờ xác nhận']) }}">
                        <i class="fa-solid fa-clock me-1"></i> Chờ Xác Nhận 
                        <span class="badge bg-dark ms-1">{{ $counts['cho_xac_nhan'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('xac_nhan') === 'Đã duyệt' ? 'active bg-success' : '' }}" 
                       href="{{ route('giangvien.xacnhan_baove.index', ['xac_nhan' => 'Đã duyệt']) }}">
                        Đã Duyệt Cho Bảo Vệ <span class="badge bg-secondary ms-1">{{ $counts['da_xac_nhan'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request('xac_nhan') === 'Không duyệt' ? 'active bg-danger' : '' }}" 
                       href="{{ route('giangvien.xacnhan_baove.index', ['xac_nhan' => 'Không duyệt']) }}">
                        Không Đồng Ý <span class="badge bg-secondary ms-1">{{ $counts['khong_dat'] }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Dossiers Table -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body p-0">
            @if($hoSos->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary"></i>
                    <h6>Không có hồ sơ bảo vệ nào theo tiêu chí lọc.</h6>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 100px;">Mã Hồ Sơ</th>
                                <th style="min-width: 200px;">Nhóm Sinh Viên</th>
                                <th style="min-width: 250px;">Đề Tài Khóa Luận</th>
                                <th style="min-width: 160px;">Tệp Hồ Sơ Đính Kèm</th>
                                <th style="min-width: 150px;">Xác Nhận GVHD</th>
                                <th class="text-end" style="width: 200px;">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hoSos as $hs)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $hs->MaHoSo }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $hs->nhom->TenNhom ?? 'N/A' }}</div>
                                        <div class="small text-muted">
                                            Trưởng nhóm: {{ $hs->nhom->truongNhom->HoTen ?? 'N/A' }} ({{ $hs->nhom->MaTruongNhom ?? '' }})
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $hs->deTai->TenDeTai ?? 'N/A' }}</div>
                                        <div class="small text-muted">Mã ĐT: {{ $hs->MaDeTai }}</div>
                                    </td>
                                    <td>
                                        @if($hs->tepHoSoBaoVes && $hs->tepHoSoBaoVes->isNotEmpty())
                                            <div class="d-flex flex-column gap-1">
                                                @foreach($hs->tepHoSoBaoVes as $tep)
                                                    <a href="{{ asset('storage/' . $tep->DuongDan) }}" target="_blank" class="small text-truncate" style="max-width: 200px;">
                                                        <i class="fa-solid fa-paperclip me-1"></i> {{ $tep->TenTep ?: 'Tệp đính kèm' }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted small">Chưa đính kèm tệp</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($hs->XacNhanGVHD === 'Đã duyệt')
                                            <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Đã duyệt cho bảo vệ</span>
                                            <div class="small text-muted mt-1">{{ $hs->NgayXacNhan ? \Carbon\Carbon::parse($hs->NgayXacNhan)->format('d/m/Y') : '' }}</div>
                                        @elseif($hs->XacNhanGVHD === 'Không duyệt')
                                            <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> Không đồng ý</span>
                                        @else
                                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Chờ GVHD xác nhận</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <!-- Nút Duyệt / Đồng ý -->
                                            <form action="{{ route('giangvien.xacnhan_baove.xacNhan', $hs->MaHoSo) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Xác nhận đồng ý cho nhóm này đủ điều kiện ra bảo vệ trước Hội đồng?');">
                                                @csrf
                                                <input type="hidden" name="HanhDong" value="dong_y">
                                                <button type="submit" class="btn btn-sm btn-success" title="Xác nhận đủ điều kiện">
                                                    <i class="fa-solid fa-check me-1"></i> Đồng ý
                                                </button>
                                            </form>

                                            <!-- Nút Từ chối -->
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-1" data-bs-toggle="modal" data-bs-target="#modalTuChoi{{ $hs->MaHoSo }}" title="Không đồng ý cho bảo vệ">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>

                                        <!-- Modal Từ Chối -->
                                        <div class="modal fade" id="modalTuChoi{{ $hs->MaHoSo }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered text-start">
                                                <div class="modal-content">
                                                    <form action="{{ route('giangvien.xacnhan_baove.xacNhan', $hs->MaHoSo) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="HanhDong" value="khong_dong_y">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold text-danger">
                                                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                                                Không Đồng Ý Cho Ra Bảo Vệ
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Nhóm sinh viên:</label>
                                                                <div>{{ $hs->nhom->TenNhom ?? '' }} (Đề tài: {{ $hs->deTai->TenDeTai ?? '' }})</div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Lý do chưa đủ điều kiện bảo vệ:</label>
                                                                <textarea name="NhanXet" class="form-control" rows="4" required
                                                                          placeholder="Ghi rõ lý do sản phẩm hoặc báo cáo chưa đạt chuẩn để nhóm biết và khắc phục..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                            <button type="submit" class="btn btn-danger">Xác Nhận Không Đồng Ý</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($hoSos->hasPages())
                    <div class="p-3 border-top d-flex justify-content-end">
                        {{ $hoSos->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
