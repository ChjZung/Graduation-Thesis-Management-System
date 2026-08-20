<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\NguoiNhanThongBao;
use Illuminate\Support\Facades\Auth;

class ThongBaoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $thongBaos = NguoiNhanThongBao::where('MaTK', $user->MaTK)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Đánh dấu tất cả là đã đọc
        NguoiNhanThongBao::where('MaTK', $user->MaTK)
            ->where('DaDoc', false)
            ->update(['DaDoc' => true, 'NgayDoc' => now()]);

        return view('sinhvien.thongbao.index', compact('thongBaos'));
    }

    public function markRead($id)
    {
        NguoiNhanThongBao::where('MaThongBao', $id)
            ->where('MaTK', Auth::user()->MaTK)
            ->update(['DaDoc' => true, 'NgayDoc' => now()]);
        return redirect()->back();
    }

    public function markAllRead()
    {
        NguoiNhanThongBao::where('MaTK', Auth::user()->MaTK)
            ->where('DaDoc', false)
            ->update(['DaDoc' => true, 'NgayDoc' => now()]);
        return redirect()->back()->with('success', 'Đã đánh dấu tất cả là đã đọc!');
    }

    /**
     * API endpoint trả về số thông báo chưa đọc (dùng cho badge)
     */
    public function unreadCount()
    {
        $count = NguoiNhanThongBao::where('MaTK', Auth::user()->MaTK)
            ->where('DaDoc', false)->count();
        return response()->json(['count' => $count]);
    }
}
