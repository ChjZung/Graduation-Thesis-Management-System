<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\ThongBao;
use Illuminate\Support\Facades\Auth;

class ThongBaoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $readIds = session()->get('read_thong_bao_ids', []);

        $query = ThongBao::whereIn('TrangThai', ['Đã phát hành', 'ĐÃ GỬI', 'ACTIVE'])
            ->where(function($q) use ($user) {
                $q->whereIn('DoiTuongNhan', ['Sinh viên', 'Toàn thể', 'Tất cả'])
                  ->orWhere('DoiTuongNhan', $user->MaTK)
                  ->orWhere('DoiTuongNhan', 'like', "%{$user->MaTK}%");
            })
            ->orderBy('created_at', 'desc');

        $thongBaos = $query->paginate(15);

        $thongBaos->getCollection()->transform(function($tb) use ($readIds) {
            $tb->DaDoc = in_array($tb->MaThongBao, $readIds);
            $tb->Loai = $tb->LoaiThongBao ?? 'Thông báo';
            $tb->icon = match($tb->LoaiThongBao) {
                'Kế hoạch' => 'fa-calendar-check text-info',
                'Khóa luận' => 'fa-graduation-cap text-primary',
                'Báo cáo'   => 'fa-file-lines text-warning',
                'Hội đồng'  => 'fa-users text-danger',
                default     => 'fa-bullhorn text-primary',
            };
            return $tb;
        });

        return view('sinhvien.thongbao.index', compact('thongBaos'));
    }

    public function markRead($id)
    {
        $readIds = session()->get('read_thong_bao_ids', []);
        if (!in_array($id, $readIds)) {
            $readIds[] = $id;
            session(['read_thong_bao_ids' => $readIds]);
        }
        return redirect()->back();
    }

    public function markAllRead()
    {
        $allIds = ThongBao::whereIn('TrangThai', ['Đã phát hành', 'ĐÃ GỬI', 'ACTIVE'])->pluck('MaThongBao')->toArray();
        session(['read_thong_bao_ids' => $allIds]);
        return redirect()->back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc!');
    }

    /**
     * API endpoint trả về số thông báo chưa đọc (dùng cho badge)
     */
    public function unreadCount()
    {
        $readIds = session()->get('read_thong_bao_ids', []);
        $count = ThongBao::whereIn('TrangThai', ['Đã phát hành', 'ĐÃ GỬI', 'ACTIVE'])
            ->whereNotIn('MaThongBao', $readIds)
            ->count();
        return response()->json(['count' => $count]);
    }
}
