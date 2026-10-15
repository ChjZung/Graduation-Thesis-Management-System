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
        $userKey = 'read_thong_bao_ids_' . ($user?->MaTK ?? 'guest');
        $readIds = session()->get($userKey, session()->get('read_thong_bao_ids', []));
        if (!is_array($readIds)) { $readIds = []; }

        $sv = \App\Models\SinhVien::where('MaTK', $user->MaTK)->first();
        $userNhomCodes = $sv ? \App\Models\ThanhVienNhom::where('MaSV', $sv->MaSV)->pluck('MaNhom')->toArray() : [];

        $query = ThongBao::whereIn('TrangThai', ['Đã phát hành', 'ĐÃ GỬI', 'Đã gửi', 'da_gui', 'ACTIVE', 'Đã tạo', 'DaPhatHanh'])
            ->where(function($q) use ($user, $userNhomCodes) {
                $q->whereIn('DoiTuongNhan', ['Sinh viên', 'Toàn thể', 'Tất cả', 'Toàn trường', 'Toàn thể sinh viên', 'Toàn thể GV và SV', ''])
                  ->orWhereNull('DoiTuongNhan')
                  ->orWhere('DoiTuongNhan', $user->MaTK)
                  ->orWhere('DoiTuongNhan', 'like', "%{$user->MaTK}%")
                  ->orWhere('MaThongBao', 'like', 'TB_KH_%')
                  ->orWhere('MaThongBao', 'like', 'TB_DK_%');

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
        $user = Auth::user();
        $userKey = 'read_thong_bao_ids_' . ($user?->MaTK ?? 'guest');
        $readIds = session()->get($userKey, session()->get('read_thong_bao_ids', []));
        if (!is_array($readIds)) { $readIds = []; }
        if (!in_array($id, $readIds)) {
            $readIds[] = $id;
            session([$userKey => $readIds, 'read_thong_bao_ids' => $readIds]);
        }
        return redirect()->back();
    }

    public function markAllRead()
    {
        $user = Auth::user();
        $userKey = 'read_thong_bao_ids_' . ($user?->MaTK ?? 'guest');
        $allIds = ThongBao::whereIn('TrangThai', ['Đã phát hành', 'ĐÃ GỬI', 'ACTIVE'])->pluck('MaThongBao')->toArray();
        $existing = session()->get($userKey, session()->get('read_thong_bao_ids', []));
        if (!is_array($existing)) { $existing = []; }
        $merged = array_values(array_unique(array_merge($existing, $allIds)));
        session([$userKey => $merged, 'read_thong_bao_ids' => $merged]);
        return redirect()->back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc!');
    }

    /**
     * API endpoint trả về số thông báo chưa đọc (dùng cho badge)
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $userKey = 'read_thong_bao_ids_' . ($user?->MaTK ?? 'guest');
        $readIds = session()->get($userKey, session()->get('read_thong_bao_ids', []));
        if (!is_array($readIds)) { $readIds = []; }
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

        // Kiểm tra quyền truy cập thông báo theo Policy (Chống IDOR)
        if (\Illuminate\Support\Facades\Gate::forUser(Auth::user())->denies('view', $thongBao)) {
            abort(403, 'Bạn không có quyền xem thông báo này.');
        }

        // Đánh dấu đã đọc
        $user = Auth::user();
        $userKey = 'read_thong_bao_ids_' . ($user?->MaTK ?? 'guest');
        $readIds = session()->get($userKey, session()->get('read_thong_bao_ids', []));
        if (!is_array($readIds)) { $readIds = []; }
        if (!in_array($id, $readIds)) {
            $readIds[] = $id;
            session([$userKey => $readIds, 'read_thong_bao_ids' => $readIds]);
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
            } elseif (in_array($ext, ['xlsx', 'xls', 'csv'])) {
                $fileType = 'excel';
            }
        }

        // Lấy dữ liệu danh sách sinh viên đủ điều kiện nếu là thông báo điều kiện
        $duDieuKienList = collect();
        $duDieuKienStats = null;
        if (str_starts_with($thongBao->MaThongBao, 'TB_DK_')) {
            $shortHk = substr($thongBao->MaThongBao, strlen('TB_DK_'));
            $hocKy = \App\Models\HocKy::all()->first(function($hk) use ($shortHk) {
                return preg_replace('/[^A-Za-z0-9]/', '', $hk->MaHocKy) === $shortHk;
            }) ?? \App\Models\HocKy::latest('MaHocKy')->first();

            if ($hocKy) {
                $duDieuKienList = \App\Models\DanhSachSVDuDieuKien::with(['sinhVien.lop.nganh'])
                    ->where('MaHocKy', $hocKy->MaHocKy)
                    ->orderBy('MaSV')
                    ->get();

                $duDieuKienStats = [
                    'total' => $duDieuKienList->count(),
                    'eligible' => $duDieuKienList->where('TrangThai', 'Đủ điều kiện')->count(),
                    'ineligible' => $duDieuKienList->where('TrangThai', '!=', 'Đủ điều kiện')->count(),
                    'tenHocKy' => "{$hocKy->TenHocKy} ({$hocKy->NamHoc})",
                ];
            }
        }

        // Preview cho các file Excel đính kèm khác
        $excelRows = [];
        if ($fileType === 'excel' && $duDieuKienList->isEmpty() && !empty($fileUrl) && file_exists(public_path($fileUrl))) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(public_path($fileUrl));
                $sheet = $spreadsheet->getActiveSheet();
                $excelRows = array_slice($sheet->toArray(null, true, true, false), 0, 50);
            } catch (\Throwable $e) {
                \Log::warning('Lỗi đọc dữ liệu xem trước Excel: ' . $e->getMessage());
            }
        }

        return view('thongbao.show', [
            'thongBao'        => $thongBao,
            'fileUrl'         => $fileUrl,
            'fileType'        => $fileType,
            'duDieuKienList'  => $duDieuKienList,
            'duDieuKienStats' => $duDieuKienStats,
            'excelRows'       => $excelRows,
            'layout'          => 'layouts.sinhvien',
            'backUrl'         => route('sinhvien.thongbao.index'),
            'totalSent'       => 0,
            'readCount'       => 0,
            'unreadCount'     => 0,
        ]);
    }
}
