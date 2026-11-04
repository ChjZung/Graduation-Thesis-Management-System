@php
    $noticeAttachment = \App\Services\NotificationAttachmentService::resolve($tb);
    $noticeExtension = $noticeAttachment ? strtoupper(pathinfo(parse_url($noticeAttachment['path'], PHP_URL_PATH), PATHINFO_EXTENSION)) : '';
@endphp
@if($noticeAttachment)
    <a href="{{ $attachmentShowUrl }}" class="badge {{ $noticeAttachment['missing'] ? 'bg-warning-subtle text-warning-emphasis' : 'bg-light text-primary' }} border rounded-pill px-2 py-1 text-decoration-none small">
        <i class="fa-solid {{ $noticeAttachment['missing'] ? 'fa-triangle-exclamation' : 'fa-paperclip' }} me-1"></i>
        {{ $noticeAttachment['missing'] ? 'Tệp đính kèm không còn trên máy chủ' : 'Tệp đính kèm'.($noticeExtension ? ' · '.$noticeExtension : '') }}
    </a>
@endif
