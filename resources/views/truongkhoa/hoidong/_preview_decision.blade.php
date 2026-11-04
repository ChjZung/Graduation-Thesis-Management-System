<div class="card border-0 p-3 p-md-4 decision-preview-content">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-2 decision-preview-toolbar">
        <div>
            @unless(request()->boolean('popup'))
            <h4 class="fw-bold text-primary">Xem trước quyết định</h4>
            @endunless
            <div>{{ $faculty->TenKhoa }} · {{ $councils->first()->hocKy?->TenDayDu ?? $scope['MaHocKy'] }} · <strong>{{ $scope['DotBaoVe'] }}</strong></div>
            <div class="small text-muted mt-1">Đầy đủ {{ $councils->count() }} hội đồng trong đợt. Phạm vi nhận thông báo khi công bố: toàn bộ tài khoản hệ thống.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(request()->boolean('popup'))
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
            @else
            <a class="btn btn-outline-secondary" href="{{ route('truongkhoa.hoidong.index', $scope) }}">Về danh sách</a>
            @endif
            <a class="btn btn-outline-primary" href="{{ route('truongkhoa.hoidong.previewDecisionPdf', ['token' => $token, 'download' => 1]) }}">Tải bản xem trước</a>
            <form method="POST" action="{{ route('truongkhoa.hoidong.congBoToanDot') }}" onsubmit="if(!confirm('Công bố đúng PDF đang xem và gửi thông báo tới toàn bộ tài khoản hệ thống?')) return false; this.querySelector('button').disabled = true; return true;">
                @csrf
                <input type="hidden" name="MaHocKy" value="{{ $scope['MaHocKy'] }}">
                <input type="hidden" name="DotBaoVe" value="{{ $scope['DotBaoVe'] }}">
                <input type="hidden" name="preview_token" value="{{ $token }}">
                <button class="btn btn-success" @disabled($issues || !$pendingCount)>Công bố quyết định</button>
            </form>
        </div>
    </div>
    @if($issues)
        <div class="alert alert-warning"><strong>Cần bổ sung trước khi công bố:</strong><ul class="mb-0">@foreach($issues as $issue)<li>{{ $issue }}</li>@endforeach</ul></div>
    @elseif(!$pendingCount)
        <div class="alert alert-info">Đợt này đã công bố. Đây là bản xem trước dữ liệu hiện tại; tệp đã phát hành vẫn được giữ nguyên trong thông báo cũ.</div>
    @endif
    <iframe title="Quyết định chung và danh sách hội đồng" src="{{ route('truongkhoa.hoidong.previewDecisionPdf', ['token' => $token]) }}" class="w-100 border rounded" style="flex: 1 1 auto; height: 78vh; min-height: 240px;"></iframe>
</div>
