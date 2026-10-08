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

        Carbon::setTestNow(null);

        return redirect()->back()->with('success', '🟢 Đã khôi phục thời gian thực tế của hệ thống.');
    }
}
