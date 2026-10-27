@php
    $extension = strtolower(pathinfo($report->DuongDanFile ?? '', PATHINFO_EXTENSION));
    $fileName = $report->TenFile ?: ('Bao_cao.' . ($extension ?: 'pdf'));
    $isWord = in_array($extension, ['docx', 'doc']);
@endphp
<div class="report-attachment p-3 rounded-3 border bg-light d-flex flex-wrap align-items-center justify-content-between gap-3 w-100 min-w-0 max-w-full overflow-hidden">
    <div class="d-flex align-items-center gap-3 flex-grow-1" style="min-width: 0; flex-basis: 180px;">
        <i class="fa-solid {{ $isWord ? 'fa-file-word text-primary' : 'fa-file-pdf text-danger' }} fs-3 flex-shrink-0" aria-hidden="true"></i>
        <div class="text-truncate">
            <div class="fw-bold text-dark text-truncate small" title="{{ $fileName }}">{{ $fileName }}</div>
            <span class="text-muted small">Tệp báo cáo đã nộp</span>
        </div>
    </div>
    @if($report->DuongDanFile)
        <div class="d-flex flex-wrap gap-2" role="group" aria-label="Thao tác với tệp báo cáo">
            <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-3 text-nowrap"
                data-report-preview data-url="{{ route('baocao.ai.pdf', $report->MaKhoi) }}"
                data-file-name="{{ $fileName }}" data-download-url="{{ route('baocao.ai.download', $report->MaKhoi) }}"
                onclick="quickPreviewOutline(this.dataset.url, this.dataset.fileName, '', { downloadUrl: this.dataset.downloadUrl })">
                <i class="fa-solid fa-eye me-1" aria-hidden="true"></i>Xem nhanh
            </button>
            <a href="{{ route('baocao.ai.download', $report->MaKhoi) }}" download="{{ $fileName }}"
                class="btn btn-sm btn-outline-secondary rounded-3 px-3 text-nowrap" data-report-download>
                <i class="fa-solid fa-download me-1" aria-hidden="true"></i>Tải xuống
            </a>
        </div>
    @else
        <span class="badge bg-secondary">Không có tệp đính kèm</span>
    @endif
</div>
