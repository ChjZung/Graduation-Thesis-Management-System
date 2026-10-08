@php
    $mockFilePath = storage_path('framework/mock_time.txt');
    $mockFileVal = file_exists($mockFilePath) ? trim(file_get_contents($mockFilePath)) : null;
    $mockVal = !empty($mockFileVal) ? $mockFileVal : (session()->has('mock_system_time') ? session('mock_system_time') : null);
    $isMocked = !empty($mockVal);
    $currentSystemTime = $isMocked ? \Carbon\Carbon::parse($mockVal) : \Carbon\Carbon::now();
@endphp

<!-- TIME MACHINE FLOATING WIDGET (TEST CONTROLLER) -->
<div id="time-machine-widget" style="position: fixed; bottom: 20px; right: 20px; z-index: 999999; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <!-- Nút Trigger Thu Gọn -->
    <div class="d-flex align-items-center gap-2 p-2 rounded-pill shadow-lg border {{ $isMocked ? 'bg-warning bg-opacity-25 border-warning' : 'bg-white border-primary' }}" style="backdrop-filter: blur(8px);">
        <button type="button" class="btn btn-sm {{ $isMocked ? 'btn-warning text-dark font-weight-bold' : 'btn-outline-primary' }} rounded-pill px-3 d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="collapse" data-bs-target="#timeMachinePanel" aria-expanded="false">
            <span class="fs-6">⏰</span>
            <span class="fw-bold">{{ $isMocked ? 'Giờ Ảo: ' . $currentSystemTime->format('d/m/Y H:i') : 'Giờ Thật: ' . $currentSystemTime->format('d/m H:i') }}</span>
            <span class="badge {{ $isMocked ? 'bg-danger' : 'bg-success' }} rounded-pill ms-1" style="font-size: 0.7rem;">
                {{ $isMocked ? 'MOCKING' : 'REAL' }}
            </span>
        </button>

        @if($isMocked)
            <form action="{{ route('dev.reset-mock-time') }}" method="POST" class="m-0 p-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-danger rounded-pill px-2 py-1 shadow-sm" title="Quay lại giờ thực tế" style="font-size: 0.75rem;">
                    ↺ Giờ thật
                </button>
            </form>
        @endif
    </div>

    <!-- Bảng Điều Khiển Mở Rộng -->
    <div class="collapse mt-2 shadow-lg rounded-4 overflow-hidden border border-secondary border-opacity-25" id="timeMachinePanel" style="width: 380px; max-width: 90vw; background: #ffffff;">
        <div class="card border-0 rounded-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-5">⏱️</span>
                    <div>
                        <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">Cỗ Máy Thời Gian (Time Travel)</h6>
                        <small class="text-white-50" style="font-size: 0.75rem;">Mô phỏng thời gian test theo Kế hoạch</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-toggle="collapse" data-bs-target="#timeMachinePanel"></button>
            </div>

            <div class="card-body p-3" style="max-height: 480px; overflow-y: auto;">
                <!-- Trạng thái hiện tại -->
                <div class="alert {{ $isMocked ? 'alert-warning border-warning' : 'alert-success border-success' }} py-2 px-3 mb-3 rounded-3" style="font-size: 0.85rem;">
                    <div class="d-flex justify-content-between">
                        <span>Trạng thái:</span>
                        <strong class="{{ $isMocked ? 'text-danger' : 'text-success' }}">{{ $isMocked ? 'Đang giả lập thời gian ảo' : 'Đang chạy thời gian thực' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span>Giờ hệ thống:</span>
                        <span class="badge bg-dark font-monospace fs-6">{{ $currentSystemTime->format('d/m/Y H:i:s') }}</span>
                    </div>
                </div>

                <!-- Các mốc chuẩn 1-Click theo Kế Hoạch Khoa -->
                <label class="form-label fw-bold text-secondary mb-2" style="font-size: 0.8rem; text-transform: uppercase;">
                    ⚡ Chọn nhanh mốc kế hoạch (1-Click)
                </label>
                
                <div class="d-grid gap-2 mb-3">
                    <!-- Mốc 0: Trước khi mở Tạo nhóm (Chưa mở cổng) -->
                    <form action="{{ route('dev.mock-time') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="mock_time" value="2026-08-09 23:59:00">
                        <button type="submit" class="btn btn-outline-dark btn-sm w-100 text-start py-1 px-2 rounded-2 d-flex justify-content-between align-items-center">
                            <span><strong class="text-dark">09/08</strong> 23:59 | ⏳ Trước ngày Tạo Nhóm</span>
                            <span class="badge bg-secondary">Chưa mở cổng</span>
                        </button>
                    </form>

                    <!-- Mốc 1: Tạo nhóm -->
                    <form action="{{ route('dev.mock-time') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="mock_time" value="2026-08-10 10:00:00">
                        <button type="submit" class="btn btn-outline-success btn-sm w-100 text-start py-1 px-2 rounded-2 d-flex justify-content-between align-items-center">
                            <span><strong class="text-primary">10/08</strong> 10:00 | 👥 Mở Tạo Nhóm</span>
                            <span class="badge bg-success-subtle text-success border border-success">Mở tạo nhóm</span>
                        </button>
                    </form>

                    <form action="{{ route('dev.mock-time') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="mock_time" value="2026-08-10 23:59:30">
                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100 text-start py-1 px-2 rounded-2 d-flex justify-content-between align-items-center">
                            <span><strong class="text-secondary">10/08</strong> 23:59 | ⌛ Hết Hạn Tạo Nhóm</span>
                            <span class="badge bg-secondary-subtle text-secondary border">Khóa tạo nhóm</span>
                        </button>
                    </form>

                    <!-- Mốc 2: Đăng ký đề tài chính thức -->
                    <form action="{{ route('dev.mock-time') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="mock_time" value="2026-08-11 09:30:00">
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100 text-start py-1 px-2 rounded-2 d-flex justify-content-between align-items-center">
                            <span><strong class="text-primary">11/08</strong> 09:30 | 📝 Mở ĐK Chính Thức</span>
                            <span class="badge bg-primary">08:00 - 20:00</span>
                        </button>
                    </form>

                    <!-- Khóa tạm thời sau 20h -->
                    <form action="{{ route('dev.mock-time') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="mock_time" value="2026-08-11 20:30:00">
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100 text-start py-1 px-2 rounded-2 d-flex justify-content-between align-items-center">
                            <span><strong class="text-danger">11/08</strong> 20:30 | 🔒 Khóa Tạm Thời Đợt 1</span>
                            <span class="badge bg-danger">Hết hạn đợt 1</span>
                        </button>
                    </form>

                    <!-- Mốc 3: Đăng ký bổ sung & Ngoại lệ -->
                    <form action="{{ route('dev.mock-time') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="mock_time" value="2026-08-12 09:00:00">
                        <button type="submit" class="btn btn-outline-warning text-dark btn-sm w-100 text-start py-1 px-2 rounded-2 d-flex justify-content-between align-items-center">
                            <span><strong class="text-warning text-dark">12/08</strong> 09:00 | 🔄 Mở ĐK Bổ Sung</span>
                            <span class="badge bg-warning text-dark">08:00 - 16:00</span>
                        </button>
                    </form>

                    <!-- Mốc 4: Công bố danh sách -->
                    <form action="{{ route('dev.mock-time') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="mock_time" value="2026-08-14 09:00:00">
                        <button type="submit" class="btn btn-outline-info text-dark btn-sm w-100 text-start py-1 px-2 rounded-2 d-flex justify-content-between align-items-center">
                            <span><strong class="text-info text-dark">14/08</strong> 09:00 | 📢 Công Bố Danh Sách</span>
                            <span class="badge bg-info text-dark">Công bố GVHD</span>
                        </button>
                    </form>

                    <!-- Mốc 5: Báo cáo tiến độ 1 -->
                    <form action="{{ route('dev.mock-time') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="mock_time" value="2026-09-15 09:00:00">
                        <button type="submit" class="btn btn-outline-dark btn-sm w-100 text-start py-1 px-2 rounded-2 d-flex justify-content-between align-items-center">
                            <span><strong>15/09</strong> | 📊 Báo Cáo Tiến Độ 1</span>
                            <span class="badge bg-secondary">Đề cương & CSDL</span>
                        </button>
                    </form>
                </div>

                <hr class="my-3">

                <!-- Tự nhập giờ tùy ý -->
                <form action="{{ route('dev.mock-time') }}" method="POST">
                    @csrf
                    <label class="form-label fw-bold text-secondary mb-1" style="font-size: 0.8rem; text-transform: uppercase;">
                        📅 Nhập thời gian tùy chọn:
                    </label>
                    <div class="input-group input-group-sm mb-2">
                        <input type="datetime-local" name="mock_time" class="form-control form-control-sm" required value="{{ $currentSystemTime->format('Y-m-d\TH:i') }}">
                        <button type="submit" class="btn btn-primary btn-sm">Tua giờ</button>
                    </div>
                </form>

                @if($isMocked)
                    <form action="{{ route('dev.reset-mock-time') }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm w-100 rounded-pill py-1">
                            ↺ Khôi phục về thời gian thật
                        </button>
                    </form>
                @endif
            </div>
            
            <div class="card-footer bg-light py-2 px-3 text-center text-muted" style="font-size: 0.72rem;">
                💡 <em>Dùng để test mọi kịch bản thời gian của Kế hoạch Khóa luận.</em>
            </div>
        </div>
    </div>
</div>
