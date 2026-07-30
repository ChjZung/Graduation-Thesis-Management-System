@extends('layouts.admin')
@section('page_title', 'Tổng Hợp Đề Tài & Kết Quả')

@section('content')
<div class="card card-premium">
    <div class="card-header-premium d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-square-poll-vertical text-primary me-2"></i> Kết Quả Đồ Án Toàn Hệ Thống</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0 table-hover align-middle">
                <thead>
                    <tr>
                        <th width="5%">STT</th>
                        <th width="15%">Nhóm</th>
                        <th width="25%">Đề Tài</th>
                        <th width="15%">Giảng Viên HD</th>
                        <th width="8%">Báo Cáo</th>
                        <th width="8%">Bảo Vệ</th>
                        <th width="8%">Tổng</th>
                        <th width="8%">Hệ 4</th>
                        <th width="8%">Xếp Loại</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($danhSach as $key => $item)
                        <tr>
                            <td>{{ $danhSach->firstItem() + $key }}</td>
                            <td>
                                <span class="fw-bold text-dark">{{ $item->nhomDoAn->TenNhom ?? 'N/A' }}</span><br>
                                <small class="text-muted">Nhóm trưởng: {{ $item->nhomDoAn->sinhVienTruongNhom->HoTen ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $item->deTai->TenDeTai ?? 'Chưa xác định' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><i class="fa-solid fa-chalkboard-user text-primary me-1"></i> {{ $item->giangVien->HoTen ?? 'N/A' }}</span>
                            </td>
                            
                            @if($item->nhomDoAn && $item->nhomDoAn->chamDiem)
                                <td>
                                    <span class="fw-bold {{ $item->nhomDoAn->chamDiem->DiemBaoCao > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $item->nhomDoAn->chamDiem->DiemBaoCao ?? '0' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold {{ $item->nhomDoAn->chamDiem->DiemBaoVe > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $item->nhomDoAn->chamDiem->DiemBaoVe ?? '0' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $tongDiem = ($item->nhomDoAn->chamDiem->DiemBaoCao + $item->nhomDoAn->chamDiem->DiemBaoVe) / 2;
                                        if ($tongDiem >= 8.5) { $diemHe4 = 4.0; $xepLoai = 'Xuất sắc'; $badgeClass = 'bg-primary'; }
                                        elseif ($tongDiem >= 8.0) { $diemHe4 = 3.5; $xepLoai = 'Giỏi'; $badgeClass = 'bg-success'; }
                                        elseif ($tongDiem >= 7.0) { $diemHe4 = 3.0; $xepLoai = 'Khá'; $badgeClass = 'bg-info text-dark'; }
                                        elseif ($tongDiem >= 5.5) { $diemHe4 = 2.0; $xepLoai = 'Trung bình'; $badgeClass = 'bg-warning text-dark'; }
                                        elseif ($tongDiem >= 4.0) { $diemHe4 = 1.0; $xepLoai = 'Yếu'; $badgeClass = 'bg-danger'; }
                                        else { $diemHe4 = 0.0; $xepLoai = 'Kém'; $badgeClass = 'bg-danger'; }
                                    @endphp
                                    <span class="fw-bold text-dark fs-6">
                                        {{ number_format($tongDiem, 1) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary">{{ number_format($diemHe4, 1) }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }}">{{ $xepLoai }}</span>
                                </td>
                            @else
                                <td><span class="text-muted fst-italic">-</span></td>
                                <td><span class="text-muted fst-italic">-</span></td>
                                <td><span class="badge bg-secondary">N/A</span></td>
                                <td><span class="text-muted fst-italic">-</span></td>
                                <td><span class="text-muted fst-italic">-</span></td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-folder-open fs-2 mb-2 opacity-50"></i><br>
                                Chưa có nhóm nào được duyệt đề tài và phân công hướng dẫn.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($danhSach->hasPages())
    <div class="card-footer bg-white border-0 pt-3 pb-3">
        <div class="d-flex justify-content-center">
            {{ $danhSach->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
