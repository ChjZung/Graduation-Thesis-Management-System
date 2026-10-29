<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class TimeMachineController extends Controller
{
    /**
     * Cập nhật thời gian ảo hệ thống vào persistence file và session
     */
    public function setMockTime(Request $request)
    {
        // Chặn lạm dụng đổi thời gian hệ thống từ bên ngoài
        if (!app()->environment(['local', 'testing'])) {
            $user = \Illuminate\Support\Facades\Auth::user();
            $user?->loadMissing('vaiTro');
            if (!$user || !in_array($user->vaiTro?->TenVaiTro, ['Admin', 'Giáo vụ'])) {
                abort(403, 'Chức năng điều chỉnh thời gian ảo chỉ dành cho quản trị viên thử nghiệm.');
            }
        }

        $request->validate([
            'mock_time' => 'required|string',
        ]);

        try {
            $parsed = Carbon::parse($request->mock_time);
            $str = $parsed->toDateTimeString();

            // Lưu vào file persistence để không bị mất khi session làm mới
            $mockFilePath = storage_path('framework/mock_time.txt');
            file_put_contents($mockFilePath, $str);

            if ($request->hasSession()) {
                session(['mock_system_time' => $str]);
            }

            \Illuminate\Support\Facades\Cache::flush();

            return redirect()->back()->with('success', "⏰ Đã tua thời gian hệ thống sang: " . $parsed->format('d/m/Y H:i:s'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Định dạng thời gian không hợp lệ: ' . $e->getMessage());
        }
    }

    /**
     * Reset thời gian ảo về thời gian thực tế máy chủ
     */
    public function resetMockTime(Request $request)
    {
        $mockFilePath = storage_path('framework/mock_time.txt');
        if (file_exists($mockFilePath)) {
            @unlink($mockFilePath);
        }

        if ($request->hasSession()) {
            session()->forget('mock_system_time');
        }

        \Illuminate\Support\Facades\Cache::flush();

        Carbon::setTestNow(null);

        return redirect()->back()->with('success', '🟢 Đã khôi phục thời gian thực tế của hệ thống.');
    }
}
