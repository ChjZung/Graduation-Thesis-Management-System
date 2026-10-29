<div class="modal-header bg-light py-3 border-bottom">
    <div class="d-flex align-items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left text-primary fs-5"></i>
        <h5 class="modal-title fw-bold text-dark fs-6 mb-0">
            Lịch Sử Kiểm Toán &amp; Quyết Định Công Bố / Thu Hồi Kết Quả
        </h5>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
    @if($logs->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-receipt fa-3x mb-3 text-secondary opacity-50"></i>
            <h6>Chưa ghi nhận lịch sử công bố hoặc thu hồi nào.</h6>
        </div>
    @else
        <div class="table-responsive rounded-3 border">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 45px;" class="text-center">STT</th>
                        <th style="width: 120px;" class="text-center">Hành Động</th>
                        <th style="width: 110px;">Phạm Vi</th>
                        <th style="min-width: 180px;">Đối Tượng &amp; Chi Tiết</th>
                        <th style="width: 90px;" class="text-center">Quy Mô</th>
                        <th style="min-width: 150px;">Người Thực Hiện</th>
                        <th style="min-width: 130px;">Thời Gian</th>
                        <th style="min-width: 200px;">Lý Do / Ghi Chú</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $idx => $log)
                        @php
                            $isCongBo = ($log->LoaiHanhDong === 'CONG_BO');
                            $phamViText = match($log->PhamVi) {
                                'DE_TAI'   => 'Từng Đề Tài',
                                'HOI_DONG' => 'Toàn Hội Đồng',
                                'TOAN_DOT' => 'Toàn Bộ Đợt',
                                default    => $log->PhamVi,
                            };
                        @endphp
                        <tr>
                            <td class="text-center text-muted">{{ $idx + 1 }}</td>
                            <td class="text-center">
                                @if($isCongBo)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fw-bold">
                                        <i class="fa-solid fa-bullhorn me-1"></i> Công Bố
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 fw-bold">
                                        <i class="fa-solid fa-undo me-1"></i> Thu Hồi
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace">
                                    {{ $phamViText }}
                                </span>
                            </td>
                            <td>
                                @if($log->PhamVi === 'DE_TAI')
                                    <div class="fw-bold text-dark" style="overflow-wrap: anywhere; word-break: break-word;">
                                        {{ $log->DuLieuSnapshot['de_tai'] ?? $log->MaHoSo }}
                                    </div>
                                    <div class="small text-muted font-monospace">Mã hồ sơ: {{ $log->MaHoSo }}</div>
                                @elseif($log->PhamVi === 'HOI_DONG')
                                    <div class="fw-bold text-dark" style="overflow-wrap: anywhere; word-break: break-word;">
                                        {{ $log->DuLieuSnapshot['hoi_dong'] ?? $log->hoiDong?->TenHoiDong ?? $log->MaHoiDong }}
                                    </div>
                                    <div class="small text-muted font-monospace">Mã HĐ: {{ $log->MaHoiDong }}</div>
                                @else
                                    <div class="fw-bold text-dark">Toàn bộ đợt học kỳ: {{ $log->MaHocKy }}</div>
                                @endif
                            </td>
                            <td class="text-center small">
                                <div><strong>{{ $log->SoLuongDeTai }}</strong> đề tài</div>
                                <div class="text-muted">{{ $log->SoLuongSinhVien }} SV</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark" style="overflow-wrap: anywhere; word-break: break-word;">
                                    {{ $log->NguoiThucHien }}
                                </div>
                                @if($log->MaTK)
                                    <div class="text-muted font-monospace" style="font-size: 11px;">TK: {{ $log->MaTK }}</div>
                                @endif
                            </td>
                            <td class="small text-muted">
                                <div>{{ $log->created_at ? $log->created_at->format('d/m/Y') : '' }}</div>
                                <div class="fw-bold text-dark">{{ $log->created_at ? $log->created_at->format('H:i:s') : '' }}</div>
                            </td>
                            <td class="small" style="overflow-wrap: anywhere; word-break: break-word;">
                                <div class="{{ !$isCongBo ? 'text-danger fw-semibold' : 'text-secondary' }}">
                                    {{ $log->LyDo ?? 'Không có ghi chú' }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
<div class="modal-footer py-2 bg-light">
    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
</div>
