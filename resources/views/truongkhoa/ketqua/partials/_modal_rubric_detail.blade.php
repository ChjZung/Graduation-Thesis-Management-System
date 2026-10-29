<div class="modal-header bg-light py-3 border-bottom">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary text-white">Bảng 3: Chi Tiết Đánh Giá Rubric</span>
            <span class="badge bg-light text-secondary border font-monospace">Hồ sơ: {{ $hoSo->MaHoSo }}</span>
            <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace">
                {{ $loaiKL === 'KLKS' ? 'Khóa Luận Kỹ Sư' : 'Khóa Luận Cử Nhân' }}
            </span>
        </div>
        <h5 class="modal-title fw-bold text-dark fs-6" style="overflow-wrap: anywhere; word-break: break-word;">
            {{ $hoSo->nhom?->deTai?->TenDeTai ?? 'Chưa xác định tên đề tài' }}
        </h5>
        <div class="small text-muted mt-1">
            Nhóm: <strong>{{ $hoSo->nhom?->TenNhom }}</strong> &bull; 
            GVHD: <strong>{{ $hoSo->nhom?->deTai?->giangVien?->HoTen ?? 'N/A' }}</strong> &bull; 
            Hội đồng: <strong>{{ $hoiDong?->TenHoiDong }}</strong>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
    @foreach($chiTietSinhVien as $idx => $item)
        @php
            $sv = $item['sinhVien'];
        @endphp
        <div class="card border mb-4 shadow-xs rounded-3 overflow-hidden">
            <div class="card-header bg-primary bg-opacity-10 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <span class="badge bg-primary text-white me-2">Sinh viên {{ $idx + 1 }}</span>
                    <strong class="fs-6 text-dark">{{ $sv->HoTen }}</strong>
                    <span class="font-monospace text-muted ms-2">({{ $sv->MaSV }} &bull; {{ $sv->lop->TenLop ?? 'Lớp' }})</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="small">Điểm TB HĐ: <strong class="fs-6 text-dark">{{ number_format($item['diemTB'], 2) }}</strong></span>
                    <span class="small">Điểm Chính Thức: <strong class="fs-5 text-primary">{{ number_format($item['diemChinhThuc'] ?? $item['diemTB'], 2) }}</strong></span>
                    <span class="badge bg-success rounded-pill px-3 py-1">{{ $item['xepLoai'] }}</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0" style="font-size: 12.5px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">STT</th>
                                <th style="min-width: 160px;">Giám Khảo &amp; Vai Trò</th>
                                @foreach($rubric as $tcKey => $tcInfo)
                                    <th class="text-center" style="min-width: 90px;" title="{{ $tcInfo['ten'] ?? $tcKey }}">
                                        <div>{{ $tcInfo['ma'] ?? $tcKey }}</div>
                                        <div class="text-muted" style="font-size: 11px;">({{ $tcInfo['diem_toi_da'] ?? 0 }}đ)</div>
                                    </th>
                                @endforeach
                                <th style="width: 90px;" class="text-center text-primary">Điểm Tổng</th>
                                <th style="min-width: 200px;">Nhận Xét Của Giám Khảo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($item['diemGiamKhaos'] as $gIdx => $gk)
                                @php
                                    $badgeRole = match($gk['VaiTro']) {
                                        'Chủ tịch'  => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        'Thư ký'    => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        'Phản biện' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                        default     => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                    };
                                @endphp
                                <tr>
                                    <td class="text-center text-muted">{{ $gIdx + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark" style="overflow-wrap: anywhere; word-break: break-word;">
                                            {{ $gk['HoTen'] }}
                                        </div>
                                        <span class="badge {{ $badgeRole }} rounded-pill px-2 py-0" style="font-size: 10.5px;">
                                            {{ $gk['VaiTro'] }}
                                        </span>
                                    </td>
                                    {{-- Điểm từng tiêu chí rubric --}}
                                    @foreach($rubric as $tcKey => $tcInfo)
                                        @php
                                            $diemTc = $gk['ChiTietDiem'][$tcKey] ?? ($gk['ChiTietDiem']['tieuchis'][$tcKey] ?? null);
                                        @endphp
                                        <td class="text-center">
                                            @if($diemTc !== null)
                                                <span class="fw-semibold text-dark">{{ number_format((float)$diemTc, 2) }}</span>
                                            @else
                                                <span class="text-muted">--</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center fw-bold fs-6 text-primary">
                                        @if($gk['Diem'] !== null)
                                            {{ number_format((float)$gk['Diem'], 2) }}
                                        @else
                                            <span class="badge bg-light text-muted border">Chưa nộp</span>
                                        @endif
                                    </td>
                                    <td class="small text-secondary" style="overflow-wrap: anywhere; word-break: break-word;">
                                        {{ !empty($gk['NhanXet']) ? $gk['NhanXet'] : 'Không có nhận xét chi tiết.' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
<div class="modal-footer py-2 bg-light">
    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
</div>
