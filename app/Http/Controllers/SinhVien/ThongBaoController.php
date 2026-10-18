<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\ThongBao;
use Illuminate\Support\Facades\Auth;

class ThongBaoController extends Controller
{
    public function index(?\Illuminate\Http\Request $request = null)
    {
        $request = $request ?? request();
        $user = Auth::user();
        $readIds = \App\Services\ThongBaoService::getReadIds($user?->MaTK);

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

        // Bộ lọc từ khóa
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('TieuDe', 'like', "%{$s}%")
                  ->orWhere('NoiDung', 'like', "%{$s}%")
                  ->orWhere('MaThongBao', 'like', "%{$s}%");
            });
        }

        // Bộ lọc Loại thông báo
        if ($request->filled('LoaiThongBao')) {
            $query->where('LoaiThongBao', $request->LoaiThongBao);
        }

        // Bộ lọc Trạng thái đã đọc / chưa đọc
        if ($request->filled('is_read')) {
            if ($request->is_read === 'read') {
                $query->whereIn('MaThongBao', $readIds);
            } elseif ($request->is_read === 'unread') {
                $query->whereNotIn('MaThongBao', $readIds);
            }
        }

        $allVisibleCodes = (clone $query)->pluck('MaThongBao')->toArray();
        $unreadCount = count(array_diff($allVisibleCodes, $readIds));
        $targetAllCount = (clone $query)->whereIn('DoiTuongNhan', ['Toàn trường', 'Toàn thể', 'Tất cả', 'Toàn thể GV và SV'])->count();

        $stats = [
            'total_thongbao'       => count($allVisibleCodes),
            'active_count'         => count($allVisibleCodes),
            'unread_count'         => $unreadCount,
            'draft_schedule_count' => 0,
            'target_all_count'     => $targetAllCount,
        ];

        $thongbaos = $query->paginate(15)->withQueryString();

        $thongbaos->getCollection()->transform(function($tb) use ($readIds) {
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

        $thongBaos = $thongbaos;
        $role = 'Sinh viên';
        $layout = 'layouts.sinhvien';

        return view('sinhvien.thongbao.index', compact('thongbaos', 'thongBaos', 'stats', 'readIds', 'role', 'layout'));
    }

    public function markRead($id)
    {
        $user = Auth::user();
        \App\Services\ThongBaoService::markAsRead($user?->MaTK, $id);
        return redirect()->back();
    }

    public function markAllRead()
    {
        $user = Auth::user();
        $allIds = ThongBao::whereIn('TrangThai', ['Đã phát hành', 'ĐÃ GỬI', 'ACTIVE'])->pluck('MaThongBao')->toArray();
        \App\Services\ThongBaoService::markAsRead($user?->MaTK, $allIds);
        return redirect()->back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc!');
    }

    /**
     * API endpoint trả về số thông báo chưa đọc (dùng cho badge)
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $readIds = \App\Services\ThongBaoService::getReadIds($user?->MaTK);
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

        // Đánh dấu đã đọc bền vững
        $user = Auth::user();
        $readIds = \App\Services\ThongBaoService::markAsRead($user?->MaTK, $id);

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

        $officialListData = \App\Services\ThongBaoService::getOfficialListData($thongBao);
        if ($officialListData && !empty($officialListData['excelUrl'])) {
            $fileUrl = $officialListData['excelUrl'];
            $fileType = 'excel';
        }

        return view('thongbao.show', [
            'thongBao'          => $thongBao,
            'fileUrl'           => $fileUrl,
            'fileType'          => $fileType,
            'duDieuKienList'    => $duDieuKienList,
            'duDieuKienStats'   => $duDieuKienStats,
            'excelRows'         => $excelRows,
            'officialListData'  => $officialListData,
            'layout'            => 'layouts.sinhvien',
            'backUrl'           => route('sinhvien.thongbao.index'),
            'totalSent'         => 0,
            'readCount'         => 0,
            'unreadCount'       => 0,
        ]);
    }
}
