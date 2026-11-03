@props(['b' => null, 'item' => null, 'hoSo' => null])

@php
    $bRecord = $b ?? $item ?? null;
    $lan = $bRecord->LanChinhSua ?? 1;
    $tenTep = $bRecord->TenFile ?? '';
    $fileGiaiTrinh = $bRecord->FileGiaiTrinh ?? null;
    $tenFileGiaiTrinh = $bRecord->TenFileGiaiTrinh ?? 'Ban_Giai_Trinh.pdf';
    $fileMinhChung = $bRecord->FileMinhChung ?? null;
    $tenFileMinhChung = $bRecord->TenFileMinhChung ?? 'MinhChung.zip';
    $dungLuong = $bRecord->DungLuong ?? null;

    // Tiêu đề đề tài (nếu có) để gắn vào modal preview
    $tenDeTai = $hoSo?->nhom?->deTai?->TenDeTai ?? $bRecord?->hoSoBaoVe?->nhom?->deTai?->TenDeTai ?? '';
    $deTaiSuffix = $tenDeTai ? ' - ' . addslashes($tenDeTai) : '';

    // Nhận diện icon theo định dạng tệp chính
    $ext = strtolower(pathinfo($tenTep, PATHINFO_EXTENSION));
    $fileIcon = match($ext) {
        'doc', 'docx' => 'fa-solid fa-file-word text-primary',
        'zip', 'rar', '7z' => 'fa-solid fa-file-zipper text-warning',
        default => 'fa-solid fa-file-pdf text-danger'
    };
@endphp

@once
@push('styles')
<style>
/* ════ CẤU TRÚC FILE-ACTIONS COMPONENT DÙNG CHUNG ════ */
.file-actions-group {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
    width: 100%;
}

.file-name-heading {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    font-size: 0.8125rem;
    line-height: 1.35;
    color: #1e293b;
    word-break: break-word;
    overflow-wrap: anywhere;
    margin-bottom: 2px;
}

.file-name-heading i {
    margin-top: 2px;
    flex-shrink: 0;
}

.file-links-list {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
    width: 100%;
}

.file-link {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    font-size: 0.8125rem !important; /* 13px */
    font-weight: 500 !important;
    line-height: 1.35 !important;
    color: var(--huit-blue, #0072ce) !important;
    text-decoration: none !important;
    background: transparent !important;
    border: none !important;
    padding: 1px 0 !important;
    margin: 0 !important;
    cursor: pointer;
    white-space: nowrap !important;
    text-align: left !important;
    transition: color 0.15s ease-in-out;
}

.file-link:hover,
.file-link:focus {
    color: var(--huit-navy, #004a8f) !important;
    text-decoration: underline !important;
}

.file-link i,
.file-link .fa-fw {
    width: 1.25em !important;
    text-align: center !important;
    flex-shrink: 0 !important;
    font-size: 0.9em !important;
    color: currentColor !important;
}

.file-link.disabled,
.file-link[aria-disabled="true"] {
    opacity: 0.5;
    pointer-events: none;
    cursor: not-allowed;
    text-decoration: none !important;
}
</style>
@endpush
@endonce

<div class="file-actions-group">
    @if(!empty($bRecord) && !empty($tenTep))
        {{-- Tên tệp chính --}}
        <div class="file-name-heading text-break-anywhere" title="{{ $tenTep }}">
            <i class="{{ $fileIcon }} fa-fw flex-shrink-0"></i>
            <span class="fw-semibold text-dark">{{ $tenTep }}</span>
        </div>

        @if(!empty($dungLuong))
            <div class="file-size-badge text-muted small ps-3 ms-2 mb-1" style="font-size: 0.72rem; line-height: 1;">
                {{ round($dungLuong / 1024 / 1024, 2) . ' MB' }}
            </div>
        @endif

        {{-- Danh sách link tác vụ tệp (4 link căn đều, icon fa-fw cùng 1 cột) --}}
        <div class="file-links-list">
            {{-- 1. Xem PDF --}}
            <a href="{{ route('baocao_sau_baove.pdf', $bRecord->id) }}"
               class="file-link"
               target="_blank"
               rel="noopener noreferrer"
               onclick="if(typeof quickPreviewOutline === 'function'){ event.preventDefault(); quickPreviewOutline('{{ route('baocao_sau_baove.pdf', $bRecord->id) }}', '{{ addslashes($tenTep) }}', 'Báo cáo hoàn thiện Lần {{ $lan }}{{ $deTaiSuffix }}', { downloadUrl: '{{ route('baocao_sau_baove.download', $bRecord->id) }}', title: 'Báo Cáo Sau Bảo Vệ (Lần {{ $lan }}): {{ addslashes($tenTep) }}' }); }"
               aria-label="Xem PDF báo cáo phiên bản Lần {{ $lan }}"
               title="Xem PDF {{ $tenTep }}">
                <i class="fa-solid fa-eye fa-fw"></i>
                <span>Xem PDF</span>
            </a>

            {{-- 2. Tải về --}}
            <a href="{{ route('baocao_sau_baove.download', $bRecord->id) }}"
               download="{{ $tenTep }}"
               class="file-link"
               aria-label="Tải về tệp báo cáo phiên bản Lần {{ $lan }}"
               title="Tải về {{ $tenTep }}">
                <i class="fa-solid fa-download fa-fw"></i>
                <span>Tải về</span>
            </a>

            {{-- 3. Xem giải trình (ẩn nếu không có tệp giải trình) --}}
            @if(!empty($fileGiaiTrinh))
            <a href="{{ route('baocao_sau_baove.pdf', ['id' => $bRecord->id, 'type' => 'giai_trinh']) }}"
               class="file-link"
               target="_blank"
               rel="noopener noreferrer"
               onclick="if(typeof quickPreviewOutline === 'function'){ event.preventDefault(); quickPreviewOutline('{{ route('baocao_sau_baove.pdf', ['id' => $bRecord->id, 'type' => 'giai_trinh']) }}', '{{ addslashes($tenFileGiaiTrinh) }}', 'Bản Giải Trình Lần {{ $lan }}{{ $deTaiSuffix }}', { downloadUrl: '{{ route('baocao_sau_baove.download', ['id' => $bRecord->id, 'type' => 'giai_trinh']) }}', title: 'Bản Giải Trình (Lần {{ $lan }}): {{ addslashes($tenFileGiaiTrinh) }}' }); }"
               aria-label="Xem bản giải trình phiên bản Lần {{ $lan }}"
               title="{{ $tenFileGiaiTrinh }}">
                <i class="fa-regular fa-file-lines fa-fw"></i>
                <span>Xem giải trình</span>
            </a>
            @endif

            {{-- 4. Tải minh chứng (ẩn nếu không có tệp minh chứng) --}}
            @if(!empty($fileMinhChung))
            <a href="{{ route('baocao_sau_baove.download', ['id' => $bRecord->id, 'type' => 'minh_chung']) }}"
               download="{{ $tenFileMinhChung }}"
               class="file-link"
               aria-label="Tải tệp minh chứng phiên bản Lần {{ $lan }}"
               title="{{ $tenFileMinhChung }}">
                <i class="fa-solid fa-paperclip fa-fw"></i>
                <span>Tải minh chứng</span>
            </a>
            @endif
        </div>
    @else
        <div class="text-muted fst-italic small d-inline-flex align-items-center gap-1">
            <i class="fa-solid fa-circle-exclamation fa-fw text-secondary"></i>
            <span>Chưa có tệp</span>
        </div>
    @endif
</div>
