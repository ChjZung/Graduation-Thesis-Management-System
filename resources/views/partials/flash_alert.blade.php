{{-- ════ KHỐI HIỂN THỊ THÔNG BÁO FLASH MESSAGE TOÀN HỆ THỐNG ════ --}}
@if (isset($errors) && $errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 mb-4" role="alert"
         style="border-left: 4px solid #dc3545 !important; border-radius: 10px;">
        <i class="fa-solid fa-circle-xmark me-2"></i>
        <strong>Đã có lỗi xảy ra:</strong>
        <ul class="mb-0 mt-1 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 mb-4" role="alert"
         style="border-left: 4px solid #198754 !important; border-radius: 10px;">
        <i class="fa-solid fa-circle-check me-2"></i>
        <span>{!! session('success') !!}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 mb-4" role="alert"
         style="border-left: 4px solid #dc3545 !important; border-radius: 10px;">
        <i class="fa-solid fa-circle-xmark me-2"></i>
        <span>{!! session('error') !!}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show border-0 mb-4" role="alert"
         style="border-left: 4px solid #f59e0b !important; border-radius: 10px;">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        <span>{!! session('warning') !!}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show border-0 mb-4" role="alert"
         style="border-left: 4px solid #0dcaf0 !important; border-radius: 10px;">
        <i class="fa-solid fa-circle-info me-2"></i>
        <span>{!! session('info') !!}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('status'))
    <div class="alert alert-info alert-dismissible fade show border-0 mb-4" role="alert"
         style="border-left: 4px solid #0dcaf0 !important; border-radius: 10px;">
        <i class="fa-solid fa-circle-info me-2"></i>
        <span>{!! session('status') !!}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
