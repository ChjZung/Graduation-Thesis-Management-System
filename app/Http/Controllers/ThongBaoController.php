<?php

namespace App\Http\Controllers;

use App\Models\ThongBao;
use App\Models\NguoiNhanThongBao;
use App\Models\KeHoachKhoaLuan;
use App\Models\TaiKhoan;
use App\Services\FileUploadService;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ThongBaoController extends Controller
{
    /**
     * Danh sách Thông báo Hệ thống kèm Bộ lọc & Phân trang
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $user->loadMissing('vaiTro');
        $role = $user->vaiTro->TenVaiTro ?? '';

        if ($role === 'Sinh viên') {
            return redirect()->route('sinhvien.thongbao.index');
        }

        $query = ThongBao::with(['giaoVu', 'giangVien'])->orderBy('created_at', 'desc');

        // Phân quyền hiển thị: Người dùng không phải Admin/Giáo vụ chỉ thấy thông báo chung hoặc gửi đích danh cho mình
        if ($role !== 'Admin' && $role !== 'Giáo vụ') {
            $curMaTK = $user->MaTK ?? '';
            $curMaGV = $user->MaGV ?? '';
            $query->where(function($q) use ($curMaTK, $curMaGV) {
                $q->whereIn('DoiTuongNhan', ['Tất cả', 'Toàn thể', 'Toàn trường', 'Toàn thể GV và SV', 'Toàn thể sinh viên'])
                  ->orWhereNull('DoiTuongNhan')
                  ->orWhere('DoiTuongNhan', '')
                  ->orWhere('DoiTuongNhan', 'Giảng viên');
                if ($curMaTK) {
                    $q->orWhere('DoiTuongNhan', $curMaTK);
                }
                if ($curMaGV) {
                    $q->orWhere('DoiTuongNhan', $curMaGV);
                }
            });
        } else {
            // Admin và Giáo vụ quản lý các thông báo công khai / chính thức của hệ thống (Trường, Khoa, Kế hoạch, Bộ môn, GV, SV)
            // Loại bỏ các thông báo cá nhân nội bộ (mời nhóm của SV, kết quả duyệt đề tài của GV) khỏi bảng quản lý của Admin
            $query->whereNotIn('LoaiThongBao', ['Nhóm', 'Đề tài'])
                  ->where(function($q) {
                      $q->whereIn('DoiTuongNhan', ['Tất cả', 'Toàn thể', 'Toàn trường', 'Toàn thể sinh viên', 'Toàn thể GV và SV', 'Sinh viên', 'Giảng viên', 'Admin', 'Giáo vụ'])
                        ->orWhereNull('DoiTuongNhan')
                        ->orWhere('DoiTuongNhan', '')
                        ->orWhere('MaThongBao', 'like', 'TB_KH_%')
                        ->orWhere('MaThongBao', 'like', 'TB_DK_%');
                  });
        }

        // 1. Tìm kiếm theo từ khóa (Tiêu đề, Nội dung, Mã thông báo)
        if ($request->filled('search')) {
            $s = trim($request->search);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $query->where(function($q) use ($s, $sClean) {
                $q->where('TieuDe', 'like', "%{$s}%")
                  ->orWhere('NoiDung', 'like', "%{$s}%")
                  ->orWhere('MaThongBao', 'like', "%{$s}%");
                if (!empty($sClean)) {
                    $q->orWhere('MaThongBao', 'like', "%{$sClean}%");
                }
            });
        }

        // 2. Lọc theo Loại thông báo
        if ($request->filled('LoaiThongBao')) {
            $query->where('LoaiThongBao', $request->LoaiThongBao);
        }

        // 3. Lọc theo Trạng thái
        if ($request->filled('TrangThai')) {
            $tt = $request->TrangThai;
            if ($tt === 'ĐÃ GỬI') {
                $query->whereIn('TrangThai', ['ĐÃ GỬI', 'Đã phát hành', 'ACTIVE', 'da_gui']);
            } else {
                $query->where('TrangThai', $tt);
            }
        }

        // 4. Lọc theo Đối tượng nhận
        if ($request->filled('DoiTuongNhan')) {
            $dt = $request->DoiTuongNhan;
            if ($dt === 'Toàn trường') {
                $query->whereIn('DoiTuongNhan', ['Tất cả', 'Toàn thể', 'Toàn trường', 'Toàn thể GV và SV']);
            } else {
                $query->where('DoiTuongNhan', $dt);
            }
        }

        // 5. Lọc theo Người tạo / Nguồn phát hành
        if ($request->filled('NguoiTao')) {
            $nt = $request->NguoiTao;
            if ($nt === 'Khoa') {
                $query->where(function($q) {
                    $q->whereNotNull('MaGVu')->orWhereNull('MaGV');
                });
            } elseif ($nt === 'GiangVien') {
                $query->whereNotNull('MaGV');
            }
        }

        // 6. Lọc theo Thời gian đăng hoặc ngày tạo
        if ($request->filled('tu_ngay')) {
            $query->where(function($q) use ($request) {
                $q->whereDate('NgayTao', '>=', $request->tu_ngay)
                  ->orWhereDate('created_at', '>=', $request->tu_ngay);
            });
        }
        if ($request->filled('den_ngay')) {
            $query->where(function($q) use ($request) {
                $q->whereDate('NgayTao', '<=', $request->den_ngay)
                  ->orWhereDate('created_at', '<=', $request->den_ngay);
            });
        }

        // 7. Lọc theo trạng thái đã đọc / chưa đọc
        $curMaTK = $user->MaTK ?? '';
        $readIds = \App\Services\ThongBaoService::getReadIds($curMaTK);
        if ($request->filled('is_read')) {
            if ($request->is_read === 'read') {
                $query->whereIn('MaThongBao', $readIds);
            } elseif ($request->is_read === 'unread') {
                $query->whereNotIn('MaThongBao', $readIds);
            }
        }

        $thongbaos = $query->paginate(10)->withQueryString();

        // Thống kê tổng quan dựa trên phạm vi được xem
        $baseScopeQuery = ThongBao::whereNotIn('LoaiThongBao', ['Nhóm', 'Đề tài']);
        if ($role !== 'Admin' && $role !== 'Giáo vụ') {
            $baseScopeQuery->where(function($q) use ($user) {
                $q->whereIn('DoiTuongNhan', ['Tất cả', 'Toàn thể', 'Toàn trường', 'Toàn thể GV và SV'])
                  ->orWhereNull('DoiTuongNhan')
                  ->orWhere('DoiTuongNhan', 'Giảng viên');
                if ($user->MaTK) $q->orWhere('DoiTuongNhan', $user->MaTK);
                if ($user->MaGV) $q->orWhere('DoiTuongNhan', $user->MaGV);
            });
        } else {
            $baseScopeQuery->where(function($q) {
                $q->whereIn('DoiTuongNhan', ['Tất cả', 'Toàn thể', 'Toàn trường', 'Toàn thể sinh viên', 'Toàn thể GV và SV', 'Sinh viên', 'Giảng viên', 'Admin', 'Giáo vụ'])
                  ->orWhereNull('DoiTuongNhan')
                  ->orWhere('DoiTuongNhan', '')
                  ->orWhere('MaThongBao', 'like', 'TB_KH_%')
                  ->orWhere('MaThongBao', 'like', 'TB_DK_%');
            });
        }

        $allVisibleCodes = (clone $baseScopeQuery)->pluck('MaThongBao')->toArray();
        $unreadCount = count(array_diff($allVisibleCodes, $readIds));

        $stats = [
            'total_thongbao'       => (clone $baseScopeQuery)->count(),
            'active_count'         => (clone $baseScopeQuery)->whereIn('TrangThai', ['ĐÃ GỬI', 'ACTIVE', 'Đã phát hành', 'da_gui'])->count(),
            'unread_count'         => $unreadCount,
            'draft_schedule_count' => (clone $baseScopeQuery)->whereIn('TrangThai', ['NHÁP', 'ĐÃ LÊN LỊCH'])->count(),
        ];

        $layout = match($role) {
            'Admin', 'Giáo vụ' => 'layouts.admin',
            'Trưởng bộ môn'    => 'layouts.truongbomon',
            'Trưởng khoa'      => 'layouts.truongkhoa',
            'Giảng viên'       => 'layouts.giangvien',
            default            => 'layouts.sinhvien',
        };

        return view('thongbao.index', compact('layout', 'thongbaos', 'stats', 'role', 'readIds'));
    }

    /**
     * Giao diện Tạo mới Thông báo
     */
    public function create()
    {
        $keHoachs = KeHoachKhoaLuan::orderBy('created_at', 'desc')->get();
        return view('thongbao.create', compact('keHoachs'));
    }

    /**
     * Xử lý Lưu Thông báo Mới (Nháp / Lên lịch / Gửi ngay)
     */
    public function store(Request $request)
    {
        $request->validate([
            'TieuDe'       => 'required|max:255',
            'NoiDung'      => 'required',
            'LoaiThongBao' => 'required',
            'DoiTuongNhan' => 'required',
            'FileDinhKem'  => 'nullable|file|mimes:pdf,docx,doc,zip,rar,png,jpg,jpeg,xlsx,xls,csv|max:10240',
        ], [
            'TieuDe.required'       => 'Vui lòng nhập tiêu đề thông báo.',
            'NoiDung.required'      => 'Vui lòng nhập nội dung thông báo.',
            'LoaiThongBao.required' => 'Vui lòng chọn loại thông báo.',
            'DoiTuongNhan.required' => 'Vui lòng chọn đối tượng nhận.',
            'FileDinhKem.mimes'      => 'File đính kèm chỉ hỗ trợ định dạng PDF, WORD (DOCX), EXCEL (XLSX, CSV), NÉN (ZIP, RAR), HÌNH ẢNH.',
        ]);

        $filePath = null;
        if ($request->hasFile('FileDinhKem')) {
            $filePath = FileUploadService::uploadFile($request->file('FileDinhKem'), 'announcements');
        }

        $maTB = 'TB_' . Str::upper(Str::random(7));
        while (ThongBao::where('MaThongBao', $maTB)->exists()) {
            $maTB = 'TB_' . Str::upper(Str::random(7));
        }

        $actionType = $request->input('action_type', 'send_now');
        $trangThai = 'NHÁP';

        if ($actionType === 'send_now') {
            $trangThai = 'Đã phát hành';
        } elseif ($actionType === 'schedule') {
            $trangThai = 'ĐÃ LÊN LỊCH';
        }

        $user = Auth::user();
        $giaoVu = \App\Models\GiaoVu::where('MaTK', $user?->MaTK)->first();
        $maGVu = $giaoVu?->MaGVu ?? (\App\Models\GiaoVu::value('MaGVu') ?? null);
        $maGV = null;
        if (!$maGVu) {
            $gv = \App\Models\GiangVien::getLoggedInGiangVien($user);
            $maGV = $gv?->MaGV ?? null;
        }

        $thongBao = ThongBao::create([
            'MaThongBao'   => $maTB,
            'MaGVu'        => $maGVu,
            'MaGV'         => $maGV,
            'TieuDe'       => $request->TieuDe,
            'NoiDung'      => \App\Helpers\HtmlHelper::clean($request->NoiDung),
            'LoaiThongBao' => $request->LoaiThongBao,
            'DoiTuongNhan' => $request->DoiTuongNhan,
            'FileDinhKem'  => $filePath,
            'NgayTao'      => now(),
            'TrangThai'    => $trangThai,
        ]);

        // Nếu phát hành ngay -> Phân phối notification tới người nhận
        if (in_array($trangThai, ['Đã phát hành', 'ĐÃ GỬI'])) {
            $this->dispatchRecipients($thongBao);
        }

        return redirect()->route('thongbao.index')->with('success', 'Đã lưu và phát hành thông báo thành công!');
    }

    /**
     * Trang Xem Chi Tiết Thông Báo & Lịch Sử Người Nhận (Hỗ trợ Split-view xem công văn trực tiếp)
     */
    public function show($id)
    {
        $user = Auth::user();
        $user?->loadMissing('vaiTro');
        if ($user?->vaiTro?->TenVaiTro === 'Sinh viên') {
            return redirect()->route('sinhvien.thongbao.show', $id);
        }

        $thongBao = ThongBao::with(['giaoVu', 'giangVien'])->where('MaThongBao', $id)->firstOrFail();

        // Đánh dấu thông báo đã đọc bền vững
        $curMaTK = $user->MaTK ?? '';
        $readIds = \App\Services\ThongBaoService::markAsRead($curMaTK, $id);

        // 1. Tự động kiểm tra file công văn đính kèm
        $fileUrl = $thongBao->FileDinhKem;
        if (!empty($fileUrl)) {
            $fileUrl = ltrim($fileUrl, '/');
            if (!str_starts_with($fileUrl, 'storage/') && !str_starts_with($fileUrl, 'http')) {
                $fileUrl = 'storage/' . $fileUrl;
            }
        }

        // 2. Tự động kiểm tra và sinh file Excel danh sách nếu là thông báo công bố sinh viên đủ điều kiện (TB_DK_*)
        if (str_starts_with($thongBao->MaThongBao, 'TB_DK_') && (empty($fileUrl) || !file_exists(public_path($fileUrl)))) {
            $shortHk = substr($thongBao->MaThongBao, strlen('TB_DK_'));
            $hocKy = \App\Models\HocKy::all()->first(function($hk) use ($shortHk) {
                return preg_replace('/[^A-Za-z0-9]/', '', $hk->MaHocKy) === $shortHk;
            }) ?? \App\Models\HocKy::latest('MaHocKy')->first();

            if ($hocKy) {
                $sinhVienController = app(\App\Http\Controllers\SinhVienController::class);
                $relPath = $sinhVienController->generateDuDieuKienExcelFile($hocKy->MaHocKy);
                if ($relPath) {
                    $thongBao->update(['FileDinhKem' => $relPath]);
                    $fileUrl = 'storage/' . $relPath;
                }
            }
        }

        // 3. Nếu thông báo chưa có file, tự động lấy file công văn từ kế hoạch khóa luận tương ứng hoặc mới nhất
        if (empty($fileUrl) || !file_exists(public_path($fileUrl))) {
            $latestPlan = \App\Models\KeHoachKhoaLuan::whereNotNull('FileDinhKem')->latest()->first();
            if ($latestPlan && !empty($latestPlan->FileDinhKem)) {
                $candidate = 'storage/' . ltrim($latestPlan->FileDinhKem, '/');
                if (file_exists(public_path($candidate))) {
                    $fileUrl = $candidate;
                }
            }
        }

        // 4. Xác định loại file (PDF, Ảnh, Excel)
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

        // 5. Lấy dữ liệu danh sách sinh viên đủ điều kiện nếu là thông báo điều kiện
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

        $target = $thongBao->DoiTuongNhan;
        if ($target === 'Sinh viên') {
            $totalSent = \App\Models\SinhVien::count();
        } elseif ($target === 'Giảng viên') {
            $totalSent = \App\Models\GiangVien::count();
        } else {
            $totalSent = \App\Models\TaiKhoan::count();
        }
        $readCount = 0;
        $unreadCount = $totalSent;

        $user = Auth::user();
        $user?->loadMissing('vaiTro');
        $role = $user?->vaiTro?->TenVaiTro ?? '';

        $layout = match($role) {
            'Admin', 'Giáo vụ' => 'layouts.admin',
            'Trưởng bộ môn'    => 'layouts.truongbomon',
            'Trưởng khoa'      => 'layouts.truongkhoa',
            'Giảng viên'       => 'layouts.giangvien',
            default            => 'layouts.sinhvien',
        };

        $officialListData = \App\Services\ThongBaoService::getOfficialListData($thongBao);
        if ($officialListData && !empty($officialListData['excelUrl'])) {
            $fileUrl = $officialListData['excelUrl'];
            $fileType = 'excel';
        }

        return view('thongbao.show', compact(
            'thongBao', 'fileUrl', 'fileType', 'totalSent', 'readCount', 'unreadCount',
            'duDieuKienList', 'duDieuKienStats', 'excelRows', 'officialListData', 'layout', 'role'
        ));
    }

    /**
     * Trang Chỉnh Sửa Thông Báo (Dành cho Nháp / Chưa gửi)
     */
    public function edit($id)
    {
        $thongBao = ThongBao::where('MaThongBao', $id)->firstOrFail();

        if (in_array($thongBao->TrangThai, ['ĐÃ GỬI', 'ĐÃ HỦY'])) {
            return redirect()->route('thongbao.show', $id)
                ->withErrors('Thông báo đã gửi hoặc đã hủy không thể chỉnh sửa trực tiếp nội dung để bảo toàn lịch sử.');
        }

        $keHoachs = KeHoachKhoaLuan::orderBy('created_at', 'desc')->get();
        return view('thongbao.edit', compact('thongBao', 'keHoachs'));
    }

    /**
     * Xử lý Cập Nhật Thông Báo
     */
    public function update(Request $request, $id)
    {
        $thongBao = ThongBao::where('MaThongBao', $id)->firstOrFail();

        if (in_array($thongBao->TrangThai, ['ĐÃ GỬI', 'ĐÃ HỦY'])) {
            return redirect()->route('thongbao.show', $id)
                ->withErrors('Thông báo đã phát hành không được chỉnh sửa.');
        }

        $request->validate([
            'TieuDe'       => 'required|max:255',
            'NoiDung'      => 'required',
            'LoaiThongBao' => 'required',
            'DoiTuongNhan' => 'required',
            'FileDinhKem'  => 'nullable|file|mimes:pdf,docx,doc,zip,rar,png,jpg,jpeg,xlsx,xls,csv|max:10240',
        ]);

        $filePath = $thongBao->FileDinhKem;
        if ($request->hasFile('FileDinhKem')) {
            $filePath = FileUploadService::uploadFile($request->file('FileDinhKem'), 'announcements');
        }

        $actionType = $request->input('action_type', 'save_draft');
        $trangThai = $thongBao->TrangThai;
        $ngayGui = $thongBao->NgayGui;

        if ($actionType === 'send_now') {
            $trangThai = 'Đã phát hành';
        } elseif ($actionType === 'schedule') {
            $trangThai = 'ĐÃ LÊN LỊCH';
        }

        $thongBao->update([
            'TieuDe'       => $request->TieuDe,
            'NoiDung'      => \App\Helpers\HtmlHelper::clean($request->NoiDung),
            'LoaiThongBao' => $request->LoaiThongBao,
            'DoiTuongNhan' => $request->DoiTuongNhan,
            'FileDinhKem'  => $filePath,
            'TrangThai'    => $trangThai,
        ]);

        if (in_array($trangThai, ['Đã phát hành', 'ĐÃ GỬI'])) {
            $this->dispatchRecipients($thongBao);
        }

        return redirect()->route('thongbao.show', $thongBao->MaThongBao)->with('success', 'Đã cập nhật thông báo!');
    }

    /**
     * Phát Hành Ngay (Send Now Action)
     */
    public function sendNow($id)
    {
        $thongBao = ThongBao::where('MaThongBao', $id)->firstOrFail();

        if (in_array($thongBao->TrangThai, ['Đã phát hành', 'ĐÃ GỬI'])) {
            return redirect()->back()->with('info', 'Thông báo này đã được phát hành trước đó.');
        }

        $thongBao->update([
            'TrangThai' => 'Đã phát hành',
        ]);

        $this->dispatchRecipients($thongBao);

        return redirect()->route('thongbao.show', $id)->with('success', '🎉 Đã phát hành thông báo thành công!');
    }

    /**
     * Xóa Nháp / Hủy / Soft Delete Thông Báo
     */
    public function destroy($id)
    {
        $thongBao = ThongBao::where('MaThongBao', $id)->firstOrFail();

        if ($thongBao->TrangThai === 'NHÁP' || $thongBao->TrangThai === 'ĐÃ LÊN LỊCH') {
            $thongBao->delete(); // Soft Delete
            return redirect()->route('thongbao.index')->with('success', 'Đã xóa thông báo nháp!');
        }

        // Với thông báo đã gửi: Đổi trạng thái sang ĐÃ HỦY để giữ lịch sử người nhận
        $thongBao->update(['TrangThai' => 'ĐÃ HỦY']);
        return redirect()->route('thongbao.index')->with('success', 'Đã thu hồi / chuyển trạng thái HỦY thông báo!');
    }

    /**
     * Phân phối Notification tới từng tài khoản người nhận
     */
    private function dispatchRecipients(ThongBao $thongBao): void
    {
        $target = $thongBao->DoiTuongNhan;
        $query = TaiKhoan::query();

        if ($target === 'Sinh viên') {
            $query->whereHas('vaiTro', fn($q) => $q->where('TenVaiTro', 'Sinh viên'));
        } elseif ($target === 'Giảng viên') {
            $query->whereHas('vaiTro', fn($q) => $q->where('TenVaiTro', 'Giảng viên'));
        }

        $taiKhoans = $query->get();
        $fileUrl = $thongBao->FileDinhKem ? asset($thongBao->FileDinhKem) : null;

        if (class_exists(\App\Models\NguoiNhanThongBao::class) && \Illuminate\Support\Facades\Schema::hasTable('nguoi_nhan_thong_baos')) {
            foreach ($taiKhoans as $tk) {
                \App\Models\NguoiNhanThongBao::updateOrCreate(
                    [
                        'MaThongBao' => $thongBao->MaThongBao,
                        'MaTK'       => $tk->MaTK,
                    ],
                    [
                        'TieuDe'     => $thongBao->TieuDe,
                        'NoiDung'    => $thongBao->NoiDung,
                        'Loai'       => $thongBao->LoaiThongBao,
                        'DuongDan'   => $fileUrl,
                        'DaDoc'      => false,
                        'NgayDoc'    => null,
                    ]
                );
            }
        }
    }

    /**
     * Đánh dấu tất cả thông báo hệ thống là đã đọc cho người dùng hiện tại
     */
    public function markAllReadGlobal(Request $request)
    {
        $curUser = Auth::user();
        if (!$curUser) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập'], 401);
        }

        $curUser->loadMissing('vaiTro');
        $role = $curUser->vaiTro?->TenVaiTro ?? '';
        $curUserMaTK = $curUser->MaTK ?? '';
        $curUserTen = $curUser->TenDangNhap ?? '';

        $notiQuery = ThongBao::whereIn('TrangThai', ['Đã phát hành', 'ĐÃ GỬI', 'da_gui', 'ACTIVE', 'DaPhatHanh']);

        if ($role === 'Sinh viên') {
            $sv = \App\Models\SinhVien::where('MaTK', $curUserMaTK)->orWhere('MaSV', $curUserTen)->first();
            $userNhomCodes = $sv ? \App\Models\ThanhVienNhom::where('MaSV', $sv->MaSV)->where('TrangThai', 'da_tham_gia')->pluck('MaNhom')->toArray() : [];

            $notiQuery->where(function($q) use ($curUserMaTK, $curUserTen, $userNhomCodes) {
                $q->whereIn('DoiTuongNhan', ['Sinh viên', 'Tất cả', 'Toàn thể', 'Toàn trường', 'Toàn thể sinh viên', 'Toàn thể GV và SV'])
                  ->orWhereNull('DoiTuongNhan')
                  ->orWhere('DoiTuongNhan', '');
                if ($curUserMaTK) {
                    $q->orWhere('DoiTuongNhan', $curUserMaTK)->orWhere('DoiTuongNhan', 'like', "%{$curUserMaTK}%");
                }
                if ($curUserTen) {
                    $q->orWhere('DoiTuongNhan', $curUserTen)->orWhere('DoiTuongNhan', 'like', "%{$curUserTen}%");
                }
                if (!empty($userNhomCodes)) {
                    $q->orWhere('DoiTuongNhan', 'like', '%Tất cả các nhóm%');
                    foreach ($userNhomCodes as $code) {
                        $q->orWhere('DoiTuongNhan', 'like', "%{$code}%");
                    }
                }
            });
        } elseif (in_array($role, ['Giảng viên', 'Trưởng bộ môn', 'Trưởng khoa'])) {
            $gv = \App\Models\GiangVien::getLoggedInGiangVien($curUser);
            $curMaGV = $gv?->MaGV ?? ($curUser?->MaGV ?? '');
            $notiQuery->where(function($q) use ($curUserMaTK, $curUserTen, $curMaGV) {
                $q->whereIn('DoiTuongNhan', ['Giảng viên', 'Tất cả', 'Toàn thể', 'Toàn trường', 'Toàn thể GV và SV'])
                  ->orWhereNull('DoiTuongNhan')
                  ->orWhere('DoiTuongNhan', '');
                if ($curUserMaTK) {
                    $q->orWhere('DoiTuongNhan', $curUserMaTK)->orWhere('DoiTuongNhan', 'like', "%{$curUserMaTK}%");
                }
                if ($curUserTen) {
                    $q->orWhere('DoiTuongNhan', $curUserTen)->orWhere('DoiTuongNhan', 'like', "%{$curUserTen}%");
                }
                if ($curMaGV) {
                    $q->orWhere('DoiTuongNhan', $curMaGV)->orWhere('DoiTuongNhan', 'like', "%{$curMaGV}%");
                }
            });
        } elseif (in_array($role, ['Admin', 'Giáo vụ'])) {
            $notiQuery->where(function($q) use ($curUserMaTK, $curUserTen) {
                $q->whereIn('DoiTuongNhan', ['Admin', 'Giáo vụ', 'Toàn thể', 'Tất cả', 'Toàn trường'])
                  ->orWhereNull('DoiTuongNhan')
                  ->orWhere('DoiTuongNhan', '');
                if ($curUserMaTK) {
                    $q->orWhere('DoiTuongNhan', $curUserMaTK);
                }
                if ($curUserTen) {
                    $q->orWhere('DoiTuongNhan', $curUserTen);
                }
            })->whereNotIn('LoaiThongBao', ['Nhóm', 'Đề tài']);
        }

        $allIds = $notiQuery->pluck('MaThongBao')->toArray();
        $curUserMaTK = $curUser->MaTK ?? '';
        $merged = \App\Services\ThongBaoService::markAsRead($curUserMaTK, $allIds);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã đánh dấu tất cả thông báo là đã đọc!',
                'count'   => 0
            ]);
        }

        return redirect()->back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc!');
    }

    /**
     * Đánh dấu 1 thông báo cụ thể là đã đọc cho người dùng hiện tại
     */
    public function markReadGlobal(Request $request, $id)
    {
        $curUser = Auth::user();
        if (!$curUser) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập'], 401);
        }

        $curUserMaTK = $curUser->MaTK ?? '';
        $readIds = \App\Services\ThongBaoService::markAsRead($curUserMaTK, $id);

        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu thông báo là đã đọc!',
            'readIds' => $readIds
        ]);
    }
}
