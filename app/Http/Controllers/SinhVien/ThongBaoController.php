<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\ThongBao;
use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThongBaoController extends Controller
{
    public function index()
    {
        $maTK = Auth::user()->MaTK;

        // Lấy danh sách tài khoản Admin để hiển thị thông báo chung
        $adminTKs = TaiKhoan::where('MaVaiTro', 1)->pluck('MaTK')->toArray();
        $allowedTKs = array_merge([$maTK], $adminTKs);

        $thongbaos = ThongBao::whereIn('MaTK', $allowedTKs)
                            ->with('taiKhoan')
                            ->orderBy('MaThongBao', 'desc')
                            ->paginate(15);

        return view('sinhvien.thongbao.index', compact('thongbaos'));
    }

    public function markRead($id)
    {
        $tb = ThongBao::where('MaThongBao', $id)->where('MaTK', Auth::user()->MaTK)->first();
        if ($tb) {
            $tb->update(['DaDoc' => true]);
        }
        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        ThongBao::where('MaTK', Auth::user()->MaTK)->update(['DaDoc' => true]);
        return redirect()->back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc!');
    }
}
