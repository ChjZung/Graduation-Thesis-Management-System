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

        $sv = \App\Models\SinhVien::where('MaTK', $user->MaTK)->first();
        $userNhomCodes = $sv ? \App\Models\ThanhVienNhom::where('MaSV', $sv->MaSV)->pluck('MaNhom')->toArray() : [];

        $query = ThongBao::whereIn('TrangThai', ['Đã phát hành', 'ĐÃ GỬI', 'ACTIVE', 'Đã tạo'])
            ->where(function($q) use ($user, $userNhomCodes) {
                $q->whereIn('DoiTuongNhan', ['Sinh viên', 'Toàn thể', 'Tất cả'])
                  ->orWhere('DoiTuongNhan', $user->MaTK)
                  ->orWhere('DoiTuongNhan', 'like', "%{$user->MaTK}%");

                if (!empty($userNhomCodes)) {
                    $q->orWhere('DoiTuongNhan', 'like', '%Tất cả các nhóm%');
                    foreach ($userNhomCodes as $code) {
                        $q->orWhere('DoiTuongNhan', 'like', "%{$code}%");
                    }
                }
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

    /**
     * Chi tiết thông báo xem trực tiếp công văn (Đồng bộ split-view như Admin)
     */
    public function show($id)
    {
        $thongBao = ThongBao::with(['giaoVu', 'giangVien'])->where('MaThongBao', $id)->firstOrFail();

        // Đánh dấu đã đọc
        $readIds = session()->get('read_thong_bao_ids', []);
        if (!in_array($id, $readIds)) {
            $readIds[] = $id;
            session(['read_thong_bao_ids' => $readIds]);
        }

        $fileUrl = $thongBao->FileDinhKem;
        if (!empty($fileUrl)) {
            $fileUrl = ltrim($fileUrl, '/');
            if (!str_starts_with($fileUrl, 'storage/') && !str_starts_with($fileUrl, 'http')) {
                $fileUrl = 'storage/' . $fileUrl;
            }
        }
        if (empty($fileUrl) || !file_exists(public_path($fileUrl))) {
            $latestPlan = \App\Models\KeHoachKhoaLuan::whereNotNull('FileDinhKem')->latest()->first();
            if ($latestPlan && !empty($latestPlan->FileDinhKem)) {
                $candidate = 'storage/' . ltrim($latestPlan->FileDinhKem, '/');
                if (file_exists(public_path($candidate))) {
                    $fileUrl = $candidate;
                }
            }
        }

        $fileType = 'other';
        if (!empty($fileUrl)) {
            $ext = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                $fileType = 'pdf';
            } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'])) {
                $fileType = 'image';
            }
        }

        return view('thongbao.show', [
            'thongBao'  => $thongBao,
            'fileUrl'   => $fileUrl,
            'fileType'  => $fileType,
            'layout'    => 'layouts.sinhvien',
            'backUrl'   => route('sinhvien.thongbao.index'),
            'totalSent' => 0,
            'readCount' => 0,
            'unreadCount' => 0,
        ]);
    }
}
