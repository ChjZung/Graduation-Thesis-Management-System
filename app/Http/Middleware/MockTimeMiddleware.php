<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MockTimeMiddleware
{
    /**
     * Handle an incoming request.
     * Áp dụng thời gian ảo từ persistence file / session trong suốt quá trình xử lý Controller & View.
     * Tự động khôi phục về thời gian thực tế trong khối finally để StartSession ghi Cookie expiration
     * với thời gian thực của máy chủ, ngăn chặn trình duyệt tự xóa Session Cookie và không bao giờ bị văng/đá đăng nhập.
     */
    public function handle(Request $request, Closure $next)
    {
        $mockTime = null;
        $mockFilePath = storage_path('framework/mock_time.txt');

        if (file_exists($mockFilePath)) {
            $mockTime = trim(file_get_contents($mockFilePath));
        } elseif ($request->hasSession() && $request->session()->has('mock_system_time')) {
            $mockTime = $request->session()->get('mock_system_time');
        }

        if (!empty($mockTime)) {
            try {
                $parsed = Carbon::parse($mockTime);
                Carbon::setTestNow($parsed);
            } catch (\Exception $e) {
                if (file_exists($mockFilePath)) {
                    @unlink($mockFilePath);
                }
                Carbon::setTestNow(null);
            }
        }

        try {
            return $next($request);
        } finally {
            // QUAN TRỌNG: Khôi phục lại thời gian thực trước khi StartSession ghi Cookie expiration,
            // đảm bảo Cookie expiration luôn ở tương lai theo giờ máy chủ thực tế,
            // triệt tiêu hoàn toàn lỗi bị văng / đá ra màn hình đăng nhập khi tua về quá khứ!
            Carbon::setTestNow(null);
        }
    }
}
