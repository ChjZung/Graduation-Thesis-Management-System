@extends('layouts.admin')

@section('page_title', 'Sao lưu và phục hồi dữ liệu')

@section('content')
@php
    $formatTime = fn ($ts) => $ts ? (new \DateTimeImmutable('@'.$ts))->setTimezone(new \DateTimeZone(config('mysql_backup.timezone')))->format('d/m/Y H:i:s') : '—';
    $states = ['PENDING'=>'Đang chờ', 'RUNNING'=>'Đang sao lưu', 'SUCCESS'=>'Thành công', 'FAILED'=>'Thất bại'];
    $verification = ['NOT_VERIFIED'=>'Chưa kiểm chứng','VERIFYING'=>'Đang kiểm chứng','VERIFIED'=>'Đã kiểm chứng','FAILED'=>'Kiểm chứng thất bại'];
@endphp
<div class="container-fluid py-3">
    <h1 class="h3 mb-3">Sao lưu và phục hồi dữ liệu</h1>
    @foreach(['success'=>'success','error'=>'danger'] as $flash=>$tone)
        @if(session($flash))<div class="alert alert-{{ $tone }}" role="status">{{ session($flash) }}</div>@endif
    @endforeach
    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="card card-body h-100">Bản đã sao lưu <strong>{{ $overview['count'] }}</strong></div></div>
        <div class="col-md-3"><div class="card card-body h-100">Dung lượng <strong>{{ number_format($overview['bytes']/1048576, 2) }} MiB</strong></div></div>
        <div class="col-md-3"><div class="card card-body h-100">Lần thành công gần nhất <strong>{{ $formatTime($overview['last_success_at']) }}</strong></div></div>
        <div class="col-md-3"><div class="card card-body h-100">Scheduler <strong>{{ $overview['scheduler_seen'] ? 'Có tín hiệu gần đây' : 'Chưa thấy tín hiệu gần đây' }}</strong><small>{{ $formatTime($overview['last_tick_at']) }}</small></div></div>
    </div>
    @if($overview['overdue'])<div class="alert alert-warning">Đã quá giờ dự kiến và chưa có bản tự động thành công. Scheduler sẽ sao lưu bù khi hoạt động.</div>@endif
    <p>Sao lưu tự động {{ $overview['automatic'] ? 'đang bật, lịch kế tiếp '.$formatTime($overview['next_due_at']) : 'đang tắt' }}. Lịch được cấu hình trong môi trường triển khai.</p>

    <div class="d-flex mb-3 gap-2 flex-wrap align-items-center">
        <form method="POST" action="{{ route('admin.mysql-backup.store') }}">
            @csrf
            <button class="btn btn-primary" type="submit">Sao lưu ngay</button>
        </form>
        <a class="btn btn-outline-secondary" href="{{ route('admin.mysql-backup.index', array_filter(['status'=>$status])) }}">Làm mới</a>
        <form method="GET" class="d-flex gap-2 align-items-center" action="{{ route('admin.mysql-backup.index') }}">
            <label for="backup-status" class="visually-hidden">Trạng thái</label>
            <select name="status" id="backup-status" class="form-select">
                <option value="">Tất cả trạng thái</option>
                @foreach($states as $key=>$label)<option value="{{ $key }}" @selected($status===$key)>{{ $label }}</option>@endforeach
            </select>
            <button class="btn btn-outline-secondary" type="submit">Lọc</button>
        </form>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th scope="col">Bản sao lưu</th><th scope="col">Thời gian</th><th scope="col">Nguồn</th><th scope="col">Trạng thái</th><th scope="col">Thao tác</th></tr></thead>
                <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td><code>{{ $row['id'] }}.sql</code><br><small>{{ $row['type']==='automatic' ? 'Tự động' : 'Thủ công' }} · {{ $row['actor'] ?? 'Scheduler' }}</small></td>
                            <td><small>Bắt đầu {{ $formatTime($row['started_at'] ?? null) }}<br>Kết thúc {{ $formatTime($row['finished_at'] ?? null) }}</small><br>{{ isset($row['seconds']) ? $row['seconds'].' giây' : '' }}</td>
                            <td>{{ $row['source']['schema'] ?? '—' }}<br><small>{{ isset($row['bytes']) ? number_format($row['bytes']/1024, 1).' KiB' : '' }}</small>
                                @if(!empty($row['sha256']))<details><summary>SHA-256</summary><code class="text-break">{{ $row['sha256'] }}</code></details>@endif
                            </td>
                            <td>{{ $states[$row['status']] }}<br><small>{{ $verification[$row['verification']] }}</small>
                                @if(!empty($row['error']) || !empty($row['restore_error']))<div class="text-danger small">{{ $row['error'] ?? $row['restore_error'] }}</div>@endif
                            </td>
                            <td>
                                @if($row['status']==='SUCCESS')<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.mysql-backup.download',$row['id']) }}">Tải xuống</a>@endif
                                @if(in_array($row['status'], ['SUCCESS','FAILED'], true))
                                    <form method="POST" class="d-inline" action="{{ route('admin.mysql-backup.destroy',$row['id']) }}" onsubmit="return confirm('Xóa file sao lưu này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty<tr><td colspan="5" class="text-center p-4">Chưa có bản sao lưu.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $records->links('pagination::bootstrap-5') }}</div>
    <p class="small text-muted">Bản này sao lưu bảng MySQL. Dùng lệnh <code>php artisan backup:mysql restore</code> để phục hồi vào schema độc lập.</p>
</div>
@endsection

