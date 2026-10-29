<div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; min-width: 0; max-width: 100%;">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-table-list text-primary me-2"></i>
            Danh Sách Sinh Viên Theo Đề Tài Khóa Luận (<span id="span-total-students">{{ $ketQuas->total() }}</span> sinh viên)
        </h6>
        <div class="small text-muted" id="span-page-info">
            Trang {{ $ketQuas->currentPage() }}/{{ max(1, $ketQuas->lastPage()) }}
        </div>
    </div>
    <div class="card-body p-0" style="min-width: 0; overflow: hidden;">
        @if($ketQuas->isEmpty())
            <div class="text-center py-5 px-3">
                <i class="fa-solid fa-clipboard-question text-muted fa-3x mb-3 opacity-50"></i>
                <h6 class="fw-bold text-secondary mb-1">Không tìm thấy kết quả phù hợp</h6>
                <p class="small text-muted mb-0">Vui lòng thử thay đổi từ khóa tìm kiếm hoặc các tiêu chí bộ lọc (Học kỳ, Hội đồng, Bộ môn, Trạng thái, Xếp loại).</p>
            </div>
        @else
            <div class="table-scroll-wrapper position-relative w-100" style="min-width: 0;">
                <div class="table-responsive custom-table-scroll" style="max-height: 70vh; overflow-x: scroll !important; overflow-y: auto !important; max-width: 100%;">
                    <table class="table table-hover align-middle mb-0" id="tableToanKhoa" style="font-size: 13px; table-layout: fixed; width: max-content; min-width: 100%;">
                        <colgroup>
                            <col style="width: 50px; min-width: 50px;">
                            <col style="width: 110px; min-width: 110px;">
                            <col style="width: 210px; min-width: 210px;">
                            <col style="width: 120px; min-width: 120px;">
                            <col style="width: 160px; min-width: 160px;">
                            <col style="width: 260px; min-width: 260px;">
                            <col style="width: 150px; min-width: 150px;">
                            <col style="width: 180px; min-width: 180px;">
                            <col style="width: 190px; min-width: 190px;">
                            <col style="width: 100px; min-width: 100px;">
                            <col style="width: 90px; min-width: 90px;">
                            <col style="width: 120px; min-width: 120px;">
                            <col style="width: 140px; min-width: 140px;">
                            <col style="width: 110px; min-width: 110px;">
                        </colgroup>
                        <thead class="sticky-top">
                            <tr>
                                <th style="width: 50px; min-width: 50px; max-width: 50px;" class="text-center sticky-col-stt">STT</th>
                                <th style="width: 110px; min-width: 110px; max-width: 110px;" class="sticky-col-masv">Mã SV</th>
                                <th style="width: 210px; min-width: 210px; max-width: 210px;" class="sticky-col-hoten">Họ và Tên SV</th>
                                <th style="width: 120px; min-width: 120px; max-width: 120px;">Lớp</th>
                                <th style="width: 160px; min-width: 160px; max-width: 160px;">Nhóm</th>
                                <th style="width: 260px; min-width: 260px; max-width: 260px;">Tên Đề Tài Khóa Luận</th>
                                <th style="width: 150px; min-width: 150px; max-width: 150px;">GV Hướng Dẫn</th>
                                <th style="width: 180px; min-width: 180px; max-width: 180px;">Hội Đồng</th>
                                <th style="width: 190px; min-width: 190px; max-width: 190px;" class="text-center">Ngày Giờ &amp; Phòng</th>
                                <th style="width: 100px; min-width: 100px; max-width: 100px;" class="text-center">Điểm Tổng Kết</th>
                                <th style="width: 90px; min-width: 90px; max-width: 90px;" class="text-center">Điểm Chữ</th>
                                <th style="width: 120px; min-width: 120px; max-width: 120px;" class="text-center">Kết Quả</th>
                                <th style="width: 140px; min-width: 140px; max-width: 140px;" class="text-center">Trạng Thái Công Bố</th>
                                <th style="width: 110px; min-width: 110px; max-width: 110px;" class="text-end pe-3">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ketQuas as $idx => $kq)
                                @php
                                    $hoSo = $kq->hoSoBaoVe;
                                    $nhom = $hoSo?->nhom;
                                    $deTai = $nhom?->deTai;
                                    $hoiDong = $hoSo?->hoiDong;
                                    $diem = (float)($kq->DiemChinhThuc ?? $kq->DiemTongKet ?? $kq->DiemHoiDongTB ?? 0);
                                    $isPublished = ($kq->TrangThai === 'Đã công bố');
                                    $isPass = ($diem >= 5.0 && in_array($kq->TrangThai, ['Đã công bố', 'Đã xác nhận', 'Đã đủ phiếu hợp lệ']));

                                    $rawLocation = $hoSo?->PhongBaoVe ?? $hoiDong?->DiaDiem ?? '';
                                    $cleanLocation = trim(preg_replace('/\s*\(.*?\)/u', '', $rawLocation));
                                    if (empty($cleanLocation)) {
                                        $cleanLocation = 'Phòng BV';
                                    }
                                    $gioBaoVe = '';
                                    $ngayBaoVe = '';
                                    if ($hoSo && $hoSo->ThoiGianBaoVe) {
                                        $dt = \Carbon\Carbon::parse($hoSo->ThoiGianBaoVe);
                                        $ngayBaoVe = $dt->format('d/m/Y');
                                        $gioBaoVe = $dt->format('H:i');
                                    } elseif ($hoiDong && $hoiDong->NgayBaoVe) {
                                        $dt = \Carbon\Carbon::parse($hoiDong->NgayBaoVe);
                                        $ngayBaoVe = $dt->format('d/m/Y');
                                        $gioBaoVe = $dt->format('H:i') !== '00:00' ? $dt->format('H:i') : '';
                                    }
                                    $fullLocationTooltip = trim(($ngayBaoVe ? $ngayBaoVe . ' ' : '') . ($gioBaoVe ? $gioBaoVe . ' • ' : '') . $rawLocation);
                                @endphp
                                <tr>
                                    <td class="text-center text-muted sticky-col-stt">{{ $ketQuas->firstItem() + $idx }}</td>
                                    <td class="font-monospace fw-bold text-danger sticky-col-masv" style="white-space: nowrap;">{{ $kq->MaSV }}</td>
                                    <td class="sticky-col-hoten">
                                        <div class="fw-bold text-dark line-clamp-1" 
                                             style="max-width: 100%;"
                                             title="{{ $kq->sinhVien->HoTen ?? 'N/A' }}">
                                            {{ $kq->sinhVien->HoTen ?? 'N/A' }}
                                        </div>
                                        <div class="small text-muted line-clamp-1" 
                                             style="max-width: 100%; font-size: 11.5px;"
                                             title="{{ $kq->sinhVien->Email ?? '' }}">
                                            {{ $kq->sinhVien->Email ?? '' }}
                                        </div>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <span class="badge bg-light text-secondary border font-monospace text-truncate d-inline-block" style="max-width: 105px;" title="{{ $kq->sinhVien->lop->TenLop ?? ($kq->sinhVien->MaLop ?? 'N/A') }}">
                                            {{ $kq->sinhVien->lop->TenLop ?? ($kq->sinhVien->MaLop ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark line-clamp-2" title="{{ $nhom->TenNhom ?? ($hoSo->MaNhom ?? 'N/A') }}">
                                            {{ $nhom->TenNhom ?? ($hoSo->MaNhom ?? 'N/A') }}
                                        </div>
                                        @if($nhom && $nhom->MaTruongNhom === $kq->MaSV)
                                            <div class="text-warning small" style="font-size: 11px; white-space: nowrap;"><i class="fa-solid fa-crown me-1"></i>Trưởng nhóm</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark line-clamp-3" title="{{ $deTai->TenDeTai ?? 'Chưa xác định' }}">
                                            {{ $deTai->TenDeTai ?? 'Chưa xác định' }}
                                        </div>
                                        <span class="badge bg-light text-secondary border font-monospace mt-1" style="font-size: 10.5px; white-space: nowrap;">
                                            {{ $kq->LoaiKhoaLuan ?? 'KLCN' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark line-clamp-1" title="{{ $deTai->giangVien->HoTen ?? 'Chưa phân công' }}">
                                            {{ $deTai->giangVien->HoTen ?? 'Chưa phân công' }}
                                        </div>
                                        <div class="text-muted small line-clamp-1" style="font-size: 11.5px;" title="{{ $deTai->giangVien->boMon->TenBoMon ?? '' }}">
                                            {{ $deTai->giangVien->boMon->TenBoMon ?? '' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($hoiDong)
                                            <div class="fw-semibold text-primary small line-clamp-2" title="{{ $hoiDong->TenHoiDong }} (Mã: {{ $hoiDong->MaHoiDong }})">
                                                {{ $hoiDong->TenHoiDong }}
                                            </div>
                                            <div class="text-muted font-monospace line-clamp-1" style="font-size: 11px;">Mã: {{ $hoiDong->MaHoiDong }}</div>
                                        @else
                                            <span class="text-muted fst-italic small">Chưa xếp HĐ</span>
                                        @endif
                                    </td>
                                    <td class="text-center small" style="width: 190px; min-width: 190px; max-width: 190px; overflow: hidden;" title="{{ $fullLocationTooltip }}">
                                        @if($ngayBaoVe)
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 100%;">
                                                <i class="fa-regular fa-calendar-days text-primary me-1"></i>{{ $ngayBaoVe }}
                                            </div>
                                            <div class="text-muted text-truncate" style="font-size: 11.5px; max-width: 100%;">
                                                @if($gioBaoVe)
                                                    <span class="fw-semibold text-secondary">{{ $gioBaoVe }}</span> &bull; 
                                                @endif
                                                <i class="fa-solid fa-location-dot text-danger me-1"></i>{{ $cleanLocation }}
                                            </div>
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold fs-6 text-primary" style="white-space: nowrap;">
                                        {{ number_format($diem, 2) }}
                                    </td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        @if($diem > 0)
                                            <span class="badge bg-light text-dark border fw-bold" style="white-space: nowrap; display: inline-flex; align-items: center; justify-content: center; min-width: 32px;">{{ $kq->diem_chu }}</span>
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        @if($diem >= 5.0)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fw-bold" style="white-space: nowrap; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid fa-check me-1"></i> Đạt ({{ $kq->KetQua ?? 'Đạt' }})
                                            </span>
                                        @elseif($diem > 0)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 fw-bold" style="white-space: nowrap; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid fa-xmark me-1"></i> Không đạt
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1" style="white-space: nowrap; display: inline-flex; align-items: center; justify-content: center;">
                                                Chưa chấm
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        @if($kq->TrangThai === 'Đã công bố')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fw-bold" style="white-space: nowrap; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid fa-circle-check me-1"></i> Đã công bố
                                            </span>
                                        @elseif($kq->TrangThai === 'Đã xác nhận')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1 fw-bold" style="white-space: nowrap; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid fa-stamp me-1"></i> Đã xác nhận
                                            </span>
                                        @elseif($kq->TrangThai === 'Đã đủ phiếu hợp lệ')
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1 fw-bold" style="white-space: nowrap; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid fa-check-double me-1"></i> Đã đủ phiếu
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 fw-bold" style="white-space: nowrap; display: inline-flex; align-items: center; justify-content: center;">
                                                <i class="fa-solid fa-hourglass-half me-1"></i> Chưa đủ phiếu
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3" style="white-space: nowrap;">
                                        <div class="d-inline-flex gap-1 justify-content-end" style="white-space: nowrap;">
                                            @if($hoSo)
                                                <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-2 py-1 btn-xem-rubric" 
                                                    data-mahoso="{{ $hoSo->MaHoSo }}" title="Xem chi tiết điểm từng giám khảo theo rubric">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>
                                            @endif

                                            @if($hoSo && !$isPublished)
                                                <button type="button" class="btn btn-xs btn-success rounded-pill px-2 py-1 btn-congbo-detai" 
                                                    data-mahoso="{{ $hoSo->MaHoSo }}" data-tendetai="{{ $deTai->TenDeTai ?? $hoSo->MaHoSo }}" title="Công bố kết quả đề tài">
                                                    <i class="fa-solid fa-bullhorn"></i>
                                                </button>
                                            @elseif($hoSo && $isPublished)
                                                <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 btn-thuhoi-detai" 
                                                    data-mahoso="{{ $hoSo->MaHoSo }}" data-tendetai="{{ $deTai->TenDeTai ?? $hoSo->MaHoSo }}" title="Thu hồi công bố kết quả đề tài">
                                                    <i class="fa-solid fa-undo"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($ketQuas->hasPages())
                <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 ajax-pagination-wrapper">
                    <div class="small text-muted">
                        Hiển thị từ {{ $ketQuas->firstItem() }} đến {{ $ketQuas->lastItem() }} trên tổng số {{ $ketQuas->total() }} sinh viên
                    </div>
                    <div>
                        {{ $ketQuas->links() }}
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
