<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-table-list text-primary me-2"></i>
            Danh Sách Điểm Khóa Luận Sinh Viên (<span id="span-total-students">{{ $ketQuas->total() }}</span> sinh viên)
        </h6>
        <div class="small text-muted" id="span-page-info">Trang {{ $ketQuas->currentPage() }}/{{ max(1, $ketQuas->lastPage()) }}</div>
    </div>
    <div class="card-body p-0">
        @if($ketQuas->isEmpty())
            <div class="text-center py-5 px-3">
                <i class="fa-solid fa-clipboard-question text-muted fa-3x mb-3 opacity-50"></i>
                <h6 class="fw-bold text-secondary mb-1">Không tìm thấy kết quả phù hợp</h6>
                <p class="small text-muted mb-0">Vui lòng thử thay đổi từ khóa tìm kiếm (mã SV, họ tên, đề tài) hoặc các bộ lọc (Bộ môn, Học kỳ, Xếp loại).</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">STT</th>
                            <th style="width: 110px;">Mã SV</th>
                            <th>Họ và Tên</th>
                            <th>Lớp & Bộ Môn</th>
                            <th>Đề Tài Khóa Luận</th>
                            <th class="text-center">Loại KL</th>
                            <th class="text-center">Điểm TB Sơ Bộ</th>
                            <th class="text-center text-primary">Điểm Chính Thức</th>
                            <th class="text-center">Trạng Thái</th>
                            <th class="text-center">Xếp Loại</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ketQuas as $idx => $kq)
                            <tr>
                                <td class="text-center text-muted small">{{ $ketQuas->firstItem() + $idx }}</td>
                                <td class="fw-bold text-danger">{{ $kq->MaSV }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $kq->sinhVien->HoTen ?? 'N/A' }}</div>
                                    <div class="small text-muted">{{ $kq->sinhVien->Email ?? '' }}</div>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark">{{ $kq->sinhVien->lop->TenLop ?? ($kq->sinhVien->MaLop ?? 'N/A') }}</div>
                                    <div class="small text-muted">
                                        {{ $kq->hoSoBaoVe->deTai->giangVien->boMon->TenBoMon ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark" style="max-width: 250px;">
                                        {{ $kq->hoSoBaoVe->deTai->TenDeTai ?? 'Chưa xác định' }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border font-monospace">
                                        {{ $kq->LoaiKhoaLuan ?? 'KLCN' }}
                                    </span>
                                </td>
                                <td class="text-center fw-semibold text-dark">
                                    {{ number_format((float)($kq->DiemHoiDongTB ?? 0), 2) }}
                                </td>
                                <td class="text-center fw-bold fs-6 text-primary">
                                    {{ number_format((float)($kq->DiemChinhThuc ?? $kq->DiemTongKet ?? $kq->DiemHoiDongTB ?? 0), 2) }}
                                </td>
                                <td class="text-center">
                                    @if($kq->TrangThai === 'Đã công bố')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">Đã công bố</span>
                                    @elseif($kq->TrangThai === 'Đã xác nhận')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1">Đã xác nhận</span>
                                    @elseif($kq->TrangThai === 'Đã đủ phiếu hợp lệ')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1">Đã đủ phiếu</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1">Chưa đủ phiếu</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $diem = (float)($kq->DiemChinhThuc ?? $kq->DiemTongKet ?? 0);
                                    @endphp
                                    @if($diem >= 9.0)
                                        <span class="badge bg-success px-2 py-1">Xuất sắc</span>
                                    @elseif($diem >= 8.0)
                                        <span class="badge bg-primary px-2 py-1">Giỏi</span>
                                    @elseif($diem >= 7.0)
                                        <span class="badge bg-warning text-dark px-2 py-1">Khá</span>
                                    @elseif($diem >= 5.0)
                                        <span class="badge bg-secondary px-2 py-1">Trung bình</span>
                                    @else
                                        <span class="badge bg-danger px-2 py-1">Không đạt</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($ketQuas->hasPages())
                <div class="p-3 border-top d-flex justify-content-end ajax-pagination-wrapper">
                    {{ $ketQuas->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
