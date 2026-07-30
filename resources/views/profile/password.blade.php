@extends($layout)
@section('title', 'Đổi Mật Khẩu')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-primary-custom"><i class="fa-solid fa-key me-2"></i>Đổi Mật Khẩu</h4>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-premium">
            <div class="card-body p-4">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form method="POST" action="{{ route('password.change.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Mật khẩu hiện tại</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Mật khẩu mới</label>
                        <input type="password" class="form-control" name="new_password" required minlength="6">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">Xác nhận mật khẩu mới</label>
                        <input type="password" class="form-control" name="new_password_confirmation" required minlength="6">
                    </div>
                    
                    <button type="submit" class="btn btn-primary-custom w-100 rounded-pill">
                        <i class="fa-solid fa-save me-2"></i>Cập nhật mật khẩu
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
