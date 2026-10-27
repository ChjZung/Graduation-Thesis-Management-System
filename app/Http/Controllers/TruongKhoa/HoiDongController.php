<?php

namespace App\Http\Controllers\TruongKhoa;

use App\Http\Controllers\Controller;
use App\Models\HoiDong;
use App\Models\ThanhVienHoiDong;
use App\Models\GiangVien;
use App\Models\HoSoBaoVe;
use App\Models\PhanCongPhanBien;
use App\Models\Khoa;
use App\Models\HocKy;
use App\Models\Nganh;
use App\Models\BoMon;
use App\Models\TaiKhoan;
use App\Helpers\IdGenerator;
use App\Services\HoiDongPdfService;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HoiDongController extends Controller
{
    /**
     * Xác định Khoa do Trưởng khoa phụ trách
     */
    private function getKhoa()
    {
        $user = Auth::user();
        if ($user && preg_match('/^TK_([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
            $khoa = Khoa::where('MaKhoa', $m[1])->first();
            if ($khoa) return $khoa;
        }

        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv) {
            $gv->loadMissing('boMon.khoa');
            if ($gv->boMon && $gv->boMon->khoa) {
                return $gv->boMon->khoa;
            }
        }

        if (!empty($gv?->HoTen)) {
            $byName = Khoa::where('TruongKhoa', 'like', '%' . $gv->HoTen . '%')->first();
            if ($byName) return $byName;
        }

        return Khoa::first() ?? new Khoa(['MaKhoa' => 'CNTT', 'TenKhoa' => 'Khoa Công nghệ Thông tin']);
    }

    /**
     * Danh sách Hội đồng bảo vệ khóa luận thuộc Khoa
     */
    public function index(Request $request)
    {
        $khoa = $this->getKhoa();
        $maKhoa = $khoa->MaKhoa;

        $query = HoiDong::with([
            'thanhViens.giangVien.boMon',
            'hoSoBaoVes.nhom.deTai.giangVien',
            'hocKy',
            'nganh',
        ])->where(function($q) use ($maKhoa) {
            $q->where('MaKhoa', $maKhoa)
              ->orWhereHas('hoSoBaoVes.nhom.deTai.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa))
              ->orWhereHas('thanhViens.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa))
              ->orDoesntHave('hoSoBaoVes');
        })->orderBy('ThoiGianBatDau', 'desc');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('TenHoiDong', 'like', "%{$s}%")
                  ->orWhere('MaHoiDong', 'like', "%{$s}%")
                  ->orWhere('DiaDiem', 'like', "%{$s}%")
                  ->orWhere('SoQuyetDinh', 'like', "%{$s}%");
            });
        }

        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai);
        }

        if ($request->filled('TrangThaiCongBo')) {
            $query->where('TrangThaiCongBo', $request->TrangThaiCongBo);
        }

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('DotBaoVe')) {
            $query->where('DotBaoVe', 'like', "%{$request->DotBaoVe}%");
        }

        $hoiDongs = $query->paginate(12)->withQueryString();
        $hocKys = HocKy::orderBy('created_at', 'desc')->get();

        return view('truongkhoa.hoidong.index', compact('hoiDongs', 'khoa', 'hocKys'));
    }

    /**
     * Màn hình Wizard 4 bước thành lập Hội đồng mới
     */
    public function create()
    {
        $khoa = $this->getKhoa();

        // 1. Học kỳ & Ngành
        $hocKys = HocKy::orderBy('created_at', 'desc')->get();
        $hocKyHienTai = HocKy::getHocKyHienTai();
        $selectedMaHocKy = old('MaHocKy', $hocKyHienTai?->MaHocKy ?? $hocKys->first()?->MaHocKy);

        $nganhs = Nganh::where('MaKhoa', $khoa->MaKhoa)->get();
        if ($nganhs->isEmpty()) {
            $nganhs = Nganh::all();
        }
        $boMons = BoMon::where('MaKhoa', $khoa->MaKhoa)->get();

        // 2. Toàn bộ Giảng viên thuộc Khoa (Nhóm theo Bộ môn và sắp xếp theo Học vị -> Tên tiếng Việt)
        $groupedGiangViens = GiangVien::getGroupedByBoMonForSelect($khoa->MaKhoa);
        $giangViens = $groupedGiangViens->flatten(1);

        // Thống kê số hội đồng của giảng viên cho toàn bộ các học kỳ (phục vụ đổi học kỳ tức thì trên JS)
        $assignmentService = app(\App\Services\CouncilAssignmentService::class);
        $allSemestersFacultyStats = $assignmentService->getAllSemestersFacultyCouncilStats();
        $currentSemesterFacultyStats = $allSemestersFacultyStats[$selectedMaHocKy]['faculties'] ?? [];
        $currentTenHocKy = $allSemestersFacultyStats[$selectedMaHocKy]['tenHocKy'] ?? ($hocKyHienTai?->TenDayDu ?? 'Học kỳ');

        // 3. Danh sách Đề tài / Nhóm sinh viên đủ điều kiện tham gia bảo vệ
        // Chỉ cho phép chọn nhóm vừa "Đã duyệt bảo vệ" vừa có báo cáo toàn văn hợp lệ
        $danhSachHoSo = HoSoBaoVe::with([
            'nhom.truongNhom.nganh',
            'nhom.truongNhom.lop.nganh',
            'nhom.thanhVienNhoms.sinhVien.nganh',
            'nhom.thanhVienNhoms.sinhVien.lop.nganh',
            'nhom.deTai.nganh',
            'nhom.deTai.giangVien.boMon',
            'hoiDong',
            'tepHoSoBaoVes',
            'phienBans',
        ])
        ->whereHas('nhom.deTai')
        ->duDieuKienVaoHoiDong()
        ->get()
        ->filter(fn($hs) => $hs->hasBaoCaoToanVan());

        // Gợi ý mã Hội đồng tiếp theo
        $nextMaHoiDong = IdGenerator::nextHoiDong();

        // Thống kê phân công hội đồng trước khi thành lập
        $thongKeTruocPhanCong = $assignmentService->thongKeTruocPhanCong($selectedMaHocKy, $khoa->MaKhoa);

        // 4. Lịch bảo vệ của các Hội đồng đang hoạt động (phục vụ kiểm tra trùng lịch bảo vệ của giảng viên)
        $existingCouncils = HoiDong::with(['thanhViens.giangVien'])
            ->where('TrangThai', '!=', 'Đã hủy')
            ->whereNotNull('ThoiGianBatDau')
            ->whereNotNull('ThoiGianKetThuc')
            ->get()
            ->map(function($hd) {
                return [
                    'MaHoiDong'       => $hd->MaHoiDong,
                    'TenHoiDong'      => $hd->TenHoiDong,
                    'ThoiGianBatDau'  => $hd->ThoiGianBatDau ? Carbon::parse($hd->ThoiGianBatDau)->toISOString() : null,
                    'ThoiGianKetThuc' => $hd->ThoiGianKetThuc ? Carbon::parse($hd->ThoiGianKetThuc)->toISOString() : null,
                    'DiaDiem'         => $hd->DiaDiem,
                    'thanh_viens'     => $hd->thanhViens->map(fn($tv) => [
                        'MaGV'   => $tv->MaGV,
                        'VaiTro' => $tv->VaiTro,
                        'HoTen'  => $tv->giangVien?->HoTen ?? $tv->MaGV,
                    ])->values()->toArray(),
                ];
            });

        return view('truongkhoa.hoidong.create', compact(
            'khoa',
            'hocKys',
            'hocKyHienTai',
            'selectedMaHocKy',
            'currentTenHocKy',
            'currentSemesterFacultyStats',
            'allSemestersFacultyStats',
            'nganhs',
            'boMons',
            'giangViens',
            'groupedGiangViens',
            'danhSachHoSo',
            'nextMaHoiDong',
            'thongKeTruocPhanCong',
            'existingCouncils'
        ));
    }

    /**
     * Lưu quyết định thành lập Hội đồng mới (Xử lý toàn bộ Wizard)
     */
    public function store(Request $request)
    {
        // Older open wizard tabs submit display labels such as "Ủy viên 1".
        // Persist the canonical role; numbering belongs only to the presentation.
        $members = $request->input('thanh_viens');
        if (is_array($members)) {
            foreach ($members as &$member) {
                if (is_array($member) && is_string($member['VaiTro'] ?? null)
                    && preg_match('/^Ủy viên\s+[1-9]\d*$/u', trim($member['VaiTro']))) {
                    $member['VaiTro'] = 'Ủy viên';
                }
            }
            unset($member);
            $request->merge(['thanh_viens' => $members]);
        }

        $request->validate([
            // Bước 1: Thông tin hội đồng & Quyết định
            'TenHoiDong'      => 'required|string|max:200',
            'MaHocKy'         => 'required|exists:HocKy,MaHocKy',
            'MaNganh'         => 'nullable|exists:Nganh,MaNganh',
            'DotBaoVe'        => 'required|string|max:100',
            'NgayBaoVe'       => 'required|date',
            'ThoiGianBatDau'  => 'required|date',
            'ThoiGianKetThuc' => 'required|date|after:ThoiGianBatDau',
            'DiaDiem'         => 'required|string|max:200',
            'GhiChu'          => 'nullable|string|max:500',
            'SoQuyetDinh'     => 'nullable|string|max:100',
            'NgayBanHanh'     => 'nullable|date',
            'NguoiKy'         => 'nullable|string|max:150',
            'ChucVuNguoiKy'   => 'nullable|string|max:150',

            // Bước 2: Danh sách đề tài đã chọn (tối thiểu 1 đề tài)
            'danh_sach_ho_so' => 'required|array|min:1',
            'danh_sach_ho_so.*' => 'required|exists:HoSoBaoVe,MaHoSo',

            // Bước 3: Thành viên hội đồng (tối thiểu 3 giảng viên, mỗi giảng viên duy nhất 1 chức vụ)
            'thanh_viens'          => 'required|array|min:3',
            'thanh_viens.*.MaGV'   => 'required|exists:GiangVien,MaGV|distinct',
            'thanh_viens.*.VaiTro' => 'required|in:Chủ tịch,Thư ký,Ủy viên thư ký,Phản biện,Ủy viên phản biện,Thành viên,Ủy viên',
        ], [
            'TenHoiDong.required'         => 'Vui lòng nhập tên Hội đồng bảo vệ.',
            'MaHocKy.required'            => 'Vui lòng chọn học kỳ áp dụng.',
            'DotBaoVe.required'           => 'Vui lòng nhập hoặc chọn đợt bảo vệ khóa luận.',
            'NgayBaoVe.required'          => 'Vui lòng chọn ngày tổ chức bảo vệ.',
            'ThoiGianBatDau.required'     => 'Vui lòng chọn thời gian bắt đầu bảo vệ.',
            'ThoiGianKetThuc.required'    => 'Vui lòng chọn thời gian kết thúc.',
            'ThoiGianKetThuc.after'       => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'DiaDiem.required'            => 'Vui lòng nhập địa điểm / phòng bảo vệ.',
            'danh_sach_ho_so.required'    => 'Vui lòng chọn ít nhất 01 đề tài/nhóm sinh viên cho Hội đồng.',
            'danh_sach_ho_so.min'         => 'Hội đồng cần ít nhất 01 đề tài để tổ chức chấm.',
            'thanh_viens.required'        => 'Vui lòng phân công thành viên Hội đồng.',
            'thanh_viens.min'             => 'Hội đồng bảo vệ cần ít nhất 3 Giảng viên.',
            'thanh_viens.*.VaiTro.in'     => 'Vai trò thành viên hội đồng không hợp lệ. Vui lòng chọn đúng chức danh.',
            'thanh_viens.*.VaiTro.required' => 'Vui lòng chọn vai trò cho thành viên hội đồng.',
            'thanh_viens.*.MaGV.required' => 'Vui lòng chọn giảng viên cho mỗi chức danh.',
            'thanh_viens.*.MaGV.distinct' => 'Mỗi giảng viên chỉ được giữ một chức vụ trong cùng một hội đồng bảo vệ.',
        ]);

        // Kiểm tra điều kiện hồ sơ đưa vào Hội đồng: Phải đã được duyệt bảo vệ VÀ có báo cáo toàn văn
        foreach ($request->danh_sach_ho_so as $maHoSo) {
            $hs = HoSoBaoVe::with(['tepHoSoBaoVes', 'phienBans'])->where('MaHoSo', $maHoSo)->first();
            if (!$hs || !$hs->hasBaoCaoToanVan()) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['danh_sach_ho_so' => "Hồ sơ '{$maHoSo}' chưa có báo cáo toàn văn hợp lệ. Không thể đưa vào Hội đồng bảo vệ."]);
            }
            if ($hs->XacNhanGVHD !== 'Đã duyệt' && !in_array($hs->TrangThai, ['Đủ điều kiện bảo vệ', 'Đã phê duyệt', 'Đã nộp', 'Đã phân công'])) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['danh_sach_ho_so' => "Hồ sơ '{$maHoSo}' chưa được Giảng viên hướng dẫn duyệt bảo vệ. Không thể đưa vào Hội đồng."]);
            }
        }

        // Kiểm tra cơ cấu thành viên bắt buộc
        $vaiTros = array_map(function($tv) {
            $v = trim($tv['VaiTro']);
            if ($v === 'Ủy viên thư ký') return 'Thư ký';
            if ($v === 'Ủy viên phản biện') return 'Phản biện';
            if ($v === 'Ủy viên') return 'Thành viên';
            return $v;
        }, $request->thanh_viens);

        if (!in_array('Chủ tịch', $vaiTros)) {
            return redirect()->back()->withInput()->withErrors(['thanh_viens' => 'Cơ cấu Hội đồng bắt buộc phải có chức danh Chủ tịch Hội đồng.']);
        }
        if (!in_array('Thư ký', $vaiTros)) {
            return redirect()->back()->withInput()->withErrors(['thanh_viens' => 'Cơ cấu Hội đồng bắt buộc phải có chức danh Thư ký (hoặc Ủy viên thư ký).']);
        }
        if (!in_array('Phản biện', $vaiTros)) {
            return redirect()->back()->withInput()->withErrors(['thanh_viens' => 'Cơ cấu Hội đồng bắt buộc phải có chức danh Phản biện (hoặc Ủy viên phản biện).']);
        }

        // Kiểm tra không phân công trùng lặp 1 giảng viên vào nhiều vị trí trong cùng Hội đồng (Báo lỗi chi tiết từng người và vai trò)
        $maGVs = array_column($request->thanh_viens, 'MaGV');
        $gvCounts = array_count_values(array_filter($maGVs));
        $duplicates = array_keys(array_filter($gvCounts, fn($count) => $count > 1));

        if (!empty($duplicates)) {
            $conflictDetails = [];
            foreach ($duplicates as $dupMa) {
                $gv = GiangVien::find($dupMa);
                $tenGV = $gv ? ($gv->danh_xung_option ?? $gv->HoTen) : $dupMa;
                $roles = collect($request->thanh_viens)
                    ->where('MaGV', $dupMa)
                    ->pluck('VaiTro')
                    ->map(fn($v) => trim($v))
                    ->unique()
                    ->join(' và ');
                $conflictDetails[] = "{$tenGV} bị phân công trùng lặp ở các chức danh: {$roles}";
            }
            return redirect()->back()->withInput()->withErrors([
                'thanh_viens' => 'Không được phân công cùng một Giảng viên vào nhiều chức danh trong cùng một Hội đồng. Phát hiện vi phạm: ' . implode('; ', $conflictDetails) . '.'
            ]);
        }

        // Kiểm tra quy định: GVHD không được tham gia Hội đồng chấm nhóm do mình hướng dẫn
        $hoSosChon = HoSoBaoVe::with(['nhom.deTai.giangVien'])
            ->whereIn('MaHoSo', $request->danh_sach_ho_so)
            ->get();

        $gvhdXungDot = [];
        foreach ($hoSosChon as $hs) {
            $gvhd = $hs->nhom?->deTai?->giangVien;
            if ($gvhd && in_array($gvhd->MaGV, $maGVs)) {
                $vaiTro = collect($request->thanh_viens)->firstWhere('MaGV', $gvhd->MaGV)['VaiTro'] ?? 'Thành viên';
                $gvhdXungDot[] = "Giảng viên {$gvhd->HoTen} (chức vụ: {$vaiTro}) đang là GVHD của nhóm {$hs->MaNhom} (Đề tài: '{$hs->nhom?->deTai?->TenDeTai}')";
            }
        }
        if (!empty($gvhdXungDot)) {
            return redirect()->back()->withInput()->withErrors([
                'thanh_viens' => 'Theo quy chế đào tạo, Giảng viên hướng dẫn KHÔNG ĐƯỢC tham gia Hội đồng chấm nhóm do mình hướng dẫn. Phát hiện vi phạm: ' . implode('; ', $gvhdXungDot) . '.'
            ]);
        }

        // Kiểm tra trùng lịch bảo vệ: Một giảng viên có thể tham gia nhiều hội đồng nhưng KHÔNG ĐƯỢC trùng lịch
        $start = Carbon::parse($request->ThoiGianBatDau);
        $end = Carbon::parse($request->ThoiGianKetThuc);

        $conflictingCouncils = HoiDong::with(['thanhViens.giangVien'])
            ->where('TrangThai', '!=', 'Đã hủy')
            ->where(function($q) use ($start, $end) {
                $q->where('ThoiGianBatDau', '<', $end)
                  ->where('ThoiGianKetThuc', '>', $start);
            })
            ->get();

        $scheduleConflicts = [];
        foreach ($conflictingCouncils as $cHd) {
            $cHdMembers = $cHd->thanhViens->keyBy('MaGV');
            foreach ($request->thanh_viens as $tv) {
                $magv = $tv['MaGV'] ?? null;
                if ($magv && $cHdMembers->has($magv)) {
                    $gv = GiangVien::find($magv);
                    $tenGV = $gv ? ($gv->danh_xung_option ?? $gv->HoTen) : $magv;
                    $otherRole = $cHdMembers->get($magv)->VaiTro ?? 'Thành viên';
                    $thisRole = $tv['VaiTro'] ?? 'Thành viên';
                    $timeStr = Carbon::parse($cHd->ThoiGianBatDau)->format('H:i d/m/Y') . ' đến ' . Carbon::parse($cHd->ThoiGianKetThuc)->format('H:i d/m/Y');
                    $scheduleConflicts[] = "Giảng viên {$tenGV} (vai trò: {$thisRole}) đã có lịch tham gia '{$cHd->TenHoiDong}' với vai trò {$otherRole} (từ {$timeStr} tại {$cHd->DiaDiem})";
                }
            }
        }

        if (!empty($scheduleConflicts)) {
            return redirect()->back()->withInput()->withErrors([
                'thanh_viens' => 'Theo quy chế đào tạo, một giảng viên có thể tham gia nhiều hội đồng nhưng KHÔNG ĐƯỢC TRÙNG LỊCH BẢO VỆ. Phát hiện vi phạm: ' . implode('; ', $scheduleConflicts) . '.'
            ]);
        }

        // Kiểm tra trùng lặp đề tài ở phía máy chủ
        $alreadyAssigned = HoSoBaoVe::whereIn('MaHoSo', $request->danh_sach_ho_so)
            ->whereNotNull('MaHoiDong')
            ->with(['hoiDong', 'nhom.deTai'])
            ->get();

        if ($alreadyAssigned->isNotEmpty()) {
            $dsBiTrung = $alreadyAssigned->map(function($hs) {
                return "Đề tài '{$hs->nhom?->deTai?->TenDeTai}' (Nhóm {$hs->MaNhom}) đã thuộc Hội đồng {$hs->hoiDong?->TenHoiDong}";
            })->join('; ');
            return redirect()->back()->withInput()->withErrors(['danh_sach_ho_so' => "Phát hiện đề tài đã được phân công vào Hội đồng khác: {$dsBiTrung}. Khi nào nhóm được hủy phân công hoặc Hội đồng cũ bị xóa thì mới được gán sang Hội đồng mới!"]);
        }

        $khoa = $this->getKhoa();
        $hoiDongMoi = null;
        $isCongBoNgay = $request->boolean('cong_bo_ngay');

        DB::transaction(function () use ($request, $khoa, &$hoiDongMoi, $isCongBoNgay) {
            $maHoiDong = IdGenerator::nextHoiDong();

            $hoiDongMoi = HoiDong::create([
                'MaHoiDong'       => $maHoiDong,
                'TenHoiDong'      => $request->TenHoiDong,
                'MaHocKy'         => $request->MaHocKy,
                'MaKhoa'          => $khoa->MaKhoa,
                'MaNganh'         => $request->MaNganh,
                'DotBaoVe'        => $request->DotBaoVe,
                'NgayBaoVe'       => $request->NgayBaoVe,
                'ThoiGianBatDau'  => $request->ThoiGianBatDau,
                'ThoiGianKetThuc' => $request->ThoiGianKetThuc,
                'DiaDiem'         => $request->DiaDiem,
                'TrangThai'       => 'Chưa diễn ra',
                'GhiChu'          => $request->GhiChu,
                'SoQuyetDinh'     => $request->SoQuyetDinh,
                'NgayBanHanh'     => $request->NgayBanHanh ?: now()->toDateString(),
                'NguoiKy'         => $request->NguoiKy ?: ($khoa->TruongKhoa ?: 'PGS.TS. Nguyễn Xuân Hoàn'),
                'ChucVuNguoiKy'   => $request->ChucVuNguoiKy ?: 'HIỆU TRƯỞNG',
                'TrangThaiCongBo' => 'Chưa công bố',
            ]);

            // Thêm thành viên Hội đồng
            foreach ($request->thanh_viens as $tv) {
                $vaiTro = $tv['VaiTro'];
                // Chuẩn hóa tên vai trò
                if ($vaiTro === 'Phản biện') $vaiTro = 'Ủy viên phản biện';
                if ($vaiTro === 'Thư ký') $vaiTro = 'Ủy viên thư ký';
                if ($vaiTro === 'Thành viên') $vaiTro = 'Ủy viên';

                ThanhVienHoiDong::create([
                    'MaHoiDong' => $hoiDongMoi->MaHoiDong,
                    'MaGV'      => $tv['MaGV'],
                    'VaiTro'    => $vaiTro,
                ]);
            }

            // Tìm giảng viên phản biện của Hội đồng để đồng bộ phân công phản biện cho đề tài
            $gvPhanBien = collect($request->thanh_viens)->first(function($tv) {
                return str_contains(mb_strtolower($tv['VaiTro']), 'phản biện');
            })['MaGV'] ?? null;

            // Gắn danh sách đề tài vào Hội đồng
            foreach ($request->danh_sach_ho_so as $maHoSo) {
                $hoSo = HoSoBaoVe::where('MaHoSo', $maHoSo)->first();
                if ($hoSo) {
                    $hoSo->update([
                        'MaHoiDong'     => $hoiDongMoi->MaHoiDong,
                        'TrangThai'     => 'Đã phân công',
                        'ThoiGianBaoVe' => $request->ThoiGianBatDau,
                        'PhongBaoVe'    => $request->DiaDiem,
                    ]);

                    // Đồng bộ phản biện đề tài nếu có
                    if ($gvPhanBien && $hoSo->MaDeTai) {
                        PhanCongPhanBien::updateOrCreate(
                            ['MaDeTai' => $hoSo->MaDeTai],
                            [
                                'MaPhanCong'   => IdGenerator::nextPhanCong(),
                                'MaGV'         => $gvPhanBien,
                                'VaiTro'       => 'Phản biện',
                                'NgayPhanCong' => now()->toDateString(),
                                'TrangThai'    => 'Đã phân công',
                            ]
                        );
                    }
                }
            }
        });

        // Nếu người dùng chọn Công bố ngay
        if ($isCongBoNgay && $hoiDongMoi) {
            $this->executeCongBo($hoiDongMoi);
            return redirect()->route('truongkhoa.hoidong.show', $hoiDongMoi->MaHoiDong)
                ->with('success', "Thành lập và Công bố chính thức Hội đồng {$hoiDongMoi->TenHoiDong} thành công!");
        }

        return redirect()->route('truongkhoa.hoidong.show', $hoiDongMoi->MaHoiDong)
            ->with('success', "Trưởng khoa đã thành lập Hội đồng {$hoiDongMoi->TenHoiDong} thành công! Bạn có thể xem trước văn bản và thực hiện công bố chính thức.");
    }

    /**
     * Chi tiết Hội đồng, quản lý thành viên, đề tài và văn bản công bố
     */
    public function show($id)
    {
        $khoa = $this->getKhoa();
        $hoiDong = HoiDong::with([
            'khoa',
            'nganh',
            'hocKy',
            'thanhViens.giangVien.boMon.khoa',
            'hoSoBaoVes.nhom.truongNhom.nganh',
            'hoSoBaoVes.nhom.truongNhom.lop.nganh',
            'hoSoBaoVes.nhom.thanhVienNhoms.sinhVien.nganh',
            'hoSoBaoVes.nhom.thanhVienNhoms.sinhVien.lop.nganh',
            'hoSoBaoVes.nhom.deTai.nganh',
            'hoSoBaoVes.nhom.deTai.giangVien.boMon',
            'hoSoBaoVes.phanCongPhanBien.giangVien.boMon',
            'hoSoBaoVes.tepHoSoBaoVes',
        ])->findOrFail($id);

        $giangViens = GiangVien::with('boMon')
            ->whereHas('boMon', fn($bm) => $bm->where('MaKhoa', $khoa->MaKhoa))
            ->orderBy('HoTen')
            ->get();

        if ($giangViens->isEmpty()) {
            $giangViens = GiangVien::with('boMon')->orderBy('HoTen')->get();
        }

        // Nhóm đủ điều kiện chưa được phân công vào bất kỳ Hội đồng nào
        $nhomDuDieuKien = HoSoBaoVe::with([
            'nhom.truongNhom.nganh',
            'nhom.truongNhom.lop.nganh',
            'nhom.thanhVienNhoms.sinhVien.nganh',
            'nhom.thanhVienNhoms.sinhVien.lop.nganh',
            'nhom.deTai.nganh',
            'nhom.deTai.giangVien.boMon',
        ])
            ->whereNull('MaHoiDong')
            ->where(function($q) {
                $q->whereIn('TrangThai', ['Đủ điều kiện bảo vệ', 'Đã phê duyệt'])
                  ->orWhere('XacNhanGVHD', 'Đã duyệt');
            })
            ->get();

        // Danh sách MaGV đang là GVHD của các nhóm trong Hội đồng này (không được làm thành viên HĐ)
        $assignedGvhdMaGVs = $hoiDong->hoSoBaoVes
            ->map(fn($hs) => $hs->nhom?->deTai?->MaGV)
            ->filter()
            ->unique()
            ->toArray();

        // Danh sách MaGV đang là thành viên Hội đồng (nhóm do các GV này hướng dẫn không được gán vào HĐ)
        $councilMemberMaGVs = $hoiDong->thanhViens->pluck('MaGV')->toArray();

        // Lịch bảo vệ giao thoa của các Hội đồng khác (phục vụ cảnh báo trùng lịch khi thêm TV)
        $conflictingScheduleMap = [];
        if ($hoiDong->ThoiGianBatDau && $hoiDong->ThoiGianKetThuc) {
            $start = Carbon::parse($hoiDong->ThoiGianBatDau);
            $end = Carbon::parse($hoiDong->ThoiGianKetThuc);
            $otherOverlappingCouncils = HoiDong::with(['thanhViens'])
                ->where('MaHoiDong', '!=', $hoiDong->MaHoiDong)
                ->where('TrangThai', '!=', 'Đã hủy')
                ->where(function($q) use ($start, $end) {
                    $q->where('ThoiGianBatDau', '<', $end)
                      ->where('ThoiGianKetThuc', '>', $start);
                })
                ->get();

            foreach ($otherOverlappingCouncils as $oHd) {
                $timeStr = Carbon::parse($oHd->ThoiGianBatDau)->format('H:i d/m') . ' - ' . Carbon::parse($oHd->ThoiGianKetThuc)->format('H:i');
                foreach ($oHd->thanhViens as $tv) {
                    $conflictingScheduleMap[$tv->MaGV] = "Trùng lịch: {$oHd->TenHoiDong} ({$timeStr})";
                }
            }
        }

        // Thống kê số hội đồng của giảng viên trong học kỳ của hội đồng này (loại trừ chính hội đồng đang xem)
        $assignmentService = app(\App\Services\CouncilAssignmentService::class);
        $facultyCouncilStats = $assignmentService->getFacultyCouncilStatsBySemester($hoiDong->MaHocKy ?? '', $hoiDong->MaHoiDong);
        $tenHocKyHoiDong = $hoiDong->hocKy?->TenDayDu ?? ($hoiDong->MaHocKy ?? 'học kỳ');

        return view('truongkhoa.hoidong.show', compact('hoiDong', 'giangViens', 'nhomDuDieuKien', 'khoa', 'assignedGvhdMaGVs', 'councilMemberMaGVs', 'conflictingScheduleMap', 'facultyCouncilStats', 'tenHocKyHoiDong'));
    }

    /**
     * Xem trước file PDF văn bản công bố Hội đồng
     */
    public function previewPdf($id)
    {
        $hoiDong = HoiDong::findOrFail($id);
        $pdf = HoiDongPdfService::generatePdf($hoiDong);
        return $pdf->stream("Quyet_Dinh_Hoi_Dong_{$hoiDong->MaHoiDong}.pdf");
    }

    /**
     * Tải xuống file PDF văn bản công bố Hội đồng
     */
    public function downloadPdf($id)
    {
        $hoiDong = HoiDong::findOrFail($id);
        $pdf = HoiDongPdfService::generatePdf($hoiDong);
        return $pdf->download("Quyet_Dinh_Hoi_Dong_{$hoiDong->MaHoiDong}.pdf");
    }

    /**
     * Công bố chính thức Hội đồng bảo vệ
     */
    public function congBo($id)
    {
        $hoiDong = HoiDong::with(['thanhViens', 'hoSoBaoVes'])->findOrFail($id);

        if ($hoiDong->thanhViens->count() < 3) {
            return redirect()->back()->with('error', 'Hội đồng cần tối thiểu 3 thành viên (Chủ tịch, Thư ký, Phản biện) trước khi công bố.');
        }

        if ($hoiDong->hoSoBaoVes->isEmpty()) {
            return redirect()->back()->with('error', 'Hội đồng chưa có đề tài/nhóm sinh viên nào. Vui lòng phân công đề tài trước khi công bố.');
        }

        $this->executeCongBo($hoiDong);

        return redirect()->back()->with('success', "Đã công bố chính thức Hội đồng bảo vệ {$hoiDong->TenHoiDong}! Hệ thống đã tạo văn bản PDF và phát thông báo đến Giáo vụ, Giảng viên và Sinh viên liên quan.");
    }

    /**
     * Logic phát hành công bố & phân phối thông báo
     */
    private function executeCongBo(HoiDong $hoiDong)
    {
        // 1. Tạo và lưu file PDF văn bản quyết định hợp nhất
        $filePath = HoiDongPdfService::savePdfToStorage($hoiDong);

        // 2. Cập nhật trạng thái công bố
        $hoiDong->update([
            'TrangThaiCongBo' => 'Đã công bố',
            'NgayCongBo'      => now(),
            'NguoiCongBo'     => Auth::user()->TenDangNhap ?? 'Trưởng khoa',
            'FileQuyetDinh'   => $filePath,
        ]);

        $hoiDong->loadMissing([
            'thanhViens.giangVien.taiKhoan',
            'hoSoBaoVes.nhom.deTai.giangVien.taiKhoan',
            'hoSoBaoVes.nhom.thanhVienNhoms.sinhVien.taiKhoan',
        ]);

        $tieuDe = "Thông báo công bố Hội đồng bảo vệ khóa luận: {$hoiDong->TenHoiDong}";
        $ngayBvFormatted = $hoiDong->NgayBaoVe ? Carbon::parse($hoiDong->NgayBaoVe)->format('d/m/Y') : ($hoiDong->ThoiGianBatDau ? Carbon::parse($hoiDong->ThoiGianBatDau)->format('d/m/Y') : 'Xem chi tiết');
        $gioBvFormatted = $hoiDong->ThoiGianBatDau ? Carbon::parse($hoiDong->ThoiGianBatDau)->format('H:i') : '07:30';

        // 3. Thông báo cho Giảng viên thành viên Hội đồng
        foreach ($hoiDong->thanhViens as $tv) {
            $maTK = $tv->giangVien?->MaTK ?? $tv->giangVien?->taiKhoan?->MaTK;
            if ($maTK) {
                $noiDung = "Kính gửi Thầy/Cô {$tv->giangVien->HoTen},\n\nKhoa đã công bố Quyết định thành lập Hội đồng bảo vệ khóa luận tốt nghiệp.\nThầy/Cô được phân công tham gia: {$hoiDong->TenHoiDong} với vai trò {$tv->VaiTro}.\n- Thời gian: {$gioBvFormatted} ngày {$ngayBvFormatted}\n- Địa điểm: {$hoiDong->DiaDiem}\n\nKính đề nghị Thầy/Cô sắp xếp thời gian tham gia đúng giờ.";
                ThongBaoService::guiDen($maTK, $tieuDe, $noiDung, 'Hội đồng', asset($filePath));
            }
        }

        // 4. Thông báo cho Giảng viên hướng dẫn & Sinh viên
        foreach ($hoiDong->hoSoBaoVes as $hoSo) {
            $nhom = $hoSo->nhom;
            $deTai = $nhom?->deTai;

            // GVHD
            $gvhdTK = $deTai?->giangVien?->MaTK ?? $deTai?->giangVien?->taiKhoan?->MaTK;
            if ($gvhdTK) {
                $noiDungGVHD = "Kính gửi Thầy/Cô {$deTai->giangVien->HoTen},\n\nĐề tài '{$deTai->TenDeTai}' (Nhóm: {$nhom->MaNhom}) do Thầy/Cô hướng dẫn đã được xếp lịch bảo vệ tại: {$hoiDong->TenHoiDong}.\n- Thời gian: {$gioBvFormatted} ngày {$ngayBvFormatted}\n- Địa điểm: {$hoiDong->DiaDiem}.";
                ThongBaoService::guiDen($gvhdTK, $tieuDe, $noiDungGVHD, 'Hội đồng', asset($filePath));
            }

            // Sinh viên trong nhóm
            if ($nhom && $nhom->thanhVienNhoms) {
                foreach ($nhom->thanhVienNhoms as $tvn) {
                    $svTK = $tvn->sinhVien?->MaTK ?? $tvn->sinhVien?->taiKhoan?->MaTK;
                    if ($svTK) {
                        $noiDungSV = "Chào bạn {$tvn->sinhVien->HoTen},\n\nKhoa đã công bố chính thức Lịch bảo vệ Khóa luận tốt nghiệp của nhóm bạn ({$nhom->MaNhom}) cho đề tài '{$deTai->TenDeTai}'.\n- Hội đồng: {$hoiDong->TenHoiDong}\n- Thời gian: {$gioBvFormatted} ngày {$ngayBvFormatted}\n- Địa điểm: {$hoiDong->DiaDiem}\n\nChúc bạn chuẩn bị chu đáo và bảo vệ thành công!";
                        ThongBaoService::guiDen($svTK, $tieuDe, $noiDungSV, 'Hội đồng', asset($filePath));
                    }
                }
            }
        }

        // 5. Thông báo cho Giáo vụ / Admin
        $giaoVus = TaiKhoan::whereHas('vaiTro', fn($q) => $q->whereIn('TenVaiTro', ['Admin', 'Giáo vụ']))->get();
        foreach ($giaoVus as $gvu) {
            $noiDungGVu = "Trưởng khoa đã ban hành và công bố Hội đồng bảo vệ khóa luận: {$hoiDong->TenHoiDong} (Số QĐ: {$hoiDong->SoQuyetDinh}).\nVăn bản quyết định đã được số hóa và sẵn sàng tra cứu, xuất báo cáo.";
            ThongBaoService::guiDen($gvu->MaTK, $tieuDe, $noiDungGVu, 'Hội đồng', asset($filePath));
        }
    }

    /**
     * Cập nhật thông tin Quyết định thành lập Hội đồng
     */
    public function updateQuyetDinh(Request $request, $id)
    {
        $request->validate([
            'SoQuyetDinh'   => 'nullable|string|max:100',
            'NgayBanHanh'   => 'nullable|date',
            'NguoiKy'       => 'nullable|string|max:150',
            'ChucVuNguoiKy' => 'nullable|string|max:150',
            'DotBaoVe'      => 'nullable|string|max:100',
        ]);

        $hoiDong = HoiDong::findOrFail($id);
        $hoiDong->update($request->only(['SoQuyetDinh', 'NgayBanHanh', 'NguoiKy', 'ChucVuNguoiKy', 'DotBaoVe']));

        // Nếu đã công bố thì làm mới lại file PDF
        if ($hoiDong->TrangThaiCongBo === 'Đã công bố') {
            HoiDongPdfService::savePdfToStorage($hoiDong);
        }

        return redirect()->back()->with('success', 'Đã cập nhật thông tin quyết định thành công!');
    }

    /**
     * Cập nhật trạng thái diễn ra của Hội đồng
     */
    public function updateTrangThai(Request $request, $id)
    {
        $request->validate([
            'TrangThai' => 'required|in:Chưa diễn ra,Đang diễn ra,Đã kết thúc,Đã hủy',
        ]);

        $hoiDong = HoiDong::findOrFail($id);
        $hoiDong->update(['TrangThai' => $request->TrangThai]);

        return redirect()->back()->with('success', "Cập nhật trạng thái Hội đồng thành '{$request->TrangThai}' thành công!");
    }

    /**
     * Phân công thêm nhóm sinh viên vào Hội đồng bảo vệ
     */
    public function phanCongNhom(Request $request, $id)
    {
        $request->validate([
            'MaNhom'        => 'required|exists:HoSoBaoVe,MaNhom',
            'ThoiGianBaoVe' => 'nullable|date',
            'PhongBaoVe'    => 'nullable|string|max:100',
        ]);

        $hoiDong = HoiDong::with(['thanhViens', 'hoSoBaoVes.nhom.deTai.giangVien'])->findOrFail($id);

        if ($hoiDong->TrangThaiCongBo === 'Đã công bố') {
            return redirect()->back()->with('error', 'Hội đồng này đã được công bố chính thức. Không thể thêm nhóm mới sau khi đã ban hành quyết định! Vui lòng thu hồi công bố nếu cần điều chỉnh.');
        }

        $hoSo = HoSoBaoVe::with(['nhom.deTai.giangVien', 'tepHoSoBaoVes', 'phienBans'])->where('MaNhom', $request->MaNhom)->first();

        if (!$hoSo) {
            return redirect()->back()->with('error', 'Nhóm này chưa nộp hồ sơ bảo vệ.');
        }

        // Kiểm tra báo cáo toàn văn và phê duyệt GVHD
        if (!$hoSo->hasBaoCaoToanVan()) {
            return redirect()->back()->with('error', "Không thể phân công nhóm {$hoSo->MaNhom} vào Hội đồng! Nhóm chưa nộp báo cáo toàn văn hợp lệ.");
        }

        if ($hoSo->XacNhanGVHD !== 'Đã duyệt' && !in_array($hoSo->TrangThai, ['Đã phê duyệt', 'Đủ điều kiện bảo vệ', 'Đã phân công'])) {
            return redirect()->back()->with('error', "Không thể phân công nhóm {$hoSo->MaNhom} vào Hội đồng! Hồ sơ chưa được Giảng viên hướng dẫn duyệt bảo vệ.");
        }

        // 1. Kiểm tra nhóm đã thuộc Hội đồng khác chưa
        if (!empty($hoSo->MaHoiDong)) {
            if ($hoSo->MaHoiDong == $hoiDong->MaHoiDong) {
                return redirect()->back()->with('error', "Nhóm {$hoSo->MaNhom} đã được phân công vào Hội đồng này rồi.");
            }
            $hdKhac = HoiDong::find($hoSo->MaHoiDong);
            $tenHdKhac = $hdKhac ? $hdKhac->TenHoiDong : $hoSo->MaHoiDong;
            return redirect()->back()->with('error', "Nhóm {$hoSo->MaNhom} đã được gán vào Hội đồng khác ({$tenHdKhac}). Theo quy định, một nhóm chỉ được tham gia 01 Hội đồng bảo vệ. Khi nào nhóm được hủy phân công hoặc Hội đồng cũ bị xóa thì mới được gán sang Hội đồng mới!");
        }

        // 2. Kiểm tra GVHD của nhóm có đang là thành viên của Hội đồng này không
        $gvhd = $hoSo->nhom?->deTai?->giangVien;
        if ($gvhd) {
            $memberConflict = $hoiDong->thanhViens->firstWhere('MaGV', $gvhd->MaGV);
            if ($memberConflict) {
                return redirect()->back()->with('error', "Không thể phân công nhóm {$hoSo->MaNhom} vào Hội đồng này! Giảng viên hướng dẫn của nhóm ({$gvhd->HoTen}) hiện đang là {$memberConflict->VaiTro} của Hội đồng. Theo quy định của Bộ và Nhà trường, GVHD không được tham gia Hội đồng chấm nhóm do mình hướng dẫn.");
            }
        }

        DB::transaction(function () use ($request, $hoiDong, $hoSo) {
            $updateData = [
                'MaHoiDong' => $hoiDong->MaHoiDong,
                'TrangThai' => 'Đã phân công',
            ];
            if ($request->filled('ThoiGianBaoVe')) {
                $updateData['ThoiGianBaoVe'] = $request->ThoiGianBaoVe;
            } elseif (!$hoSo->ThoiGianBaoVe && $hoiDong->ThoiGianBatDau) {
                $updateData['ThoiGianBaoVe'] = $hoiDong->ThoiGianBatDau;
            }

            if ($request->filled('PhongBaoVe')) {
                $updateData['PhongBaoVe'] = $request->PhongBaoVe;
            } elseif (!$hoSo->PhongBaoVe && $hoiDong->DiaDiem) {
                $updateData['PhongBaoVe'] = $hoiDong->DiaDiem;
            }

            $hoSo->update($updateData);

            // Tự động phân công phản biện từ thành viên phản biện của Hội đồng nếu có
            $tvPhanBien = $hoiDong->thanhViens->first(fn($tv) => str_contains(mb_strtolower($tv->VaiTro), 'phản biện'));
            if ($tvPhanBien && $hoSo->MaDeTai) {
                PhanCongPhanBien::updateOrCreate(
                    ['MaDeTai' => $hoSo->MaDeTai],
                    [
                        'MaPhanCong'   => IdGenerator::nextPhanCong(),
                        'MaGV'         => $tvPhanBien->MaGV,
                        'VaiTro'       => 'Phản biện',
                        'NgayPhanCong' => now()->toDateString(),
                        'TrangThai'    => 'Đã phân công',
                    ]
                );
            }
        });

        // Nếu đã công bố thì làm mới lại file PDF
        if ($hoiDong->TrangThaiCongBo === 'Đã công bố') {
            HoiDongPdfService::savePdfToStorage($hoiDong);
        }

        return redirect()->back()->with('success', "Đã phân công nhóm {$hoSo->MaNhom} vào Hội đồng {$hoiDong->TenHoiDong} thành công!");
    }

    /**
     * Hủy phân công nhóm khỏi Hội đồng
     */
    public function huyPhanCongNhom($id, $maHoSo)
    {
        $hoiDong = HoiDong::findOrFail($id);
        if ($hoiDong->TrangThaiCongBo === 'Đã công bố') {
            return redirect()->back()->with('error', 'Hội đồng này đã được công bố chính thức. Không thể hủy phân công hoặc xóa nhóm sinh viên sau khi đã công bố quyết định! Vui lòng thu hồi công bố nếu cần điều chỉnh.');
        }

        $hoSo = HoSoBaoVe::where('MaHoiDong', $id)->where('MaHoSo', $maHoSo)->firstOrFail();
        $hoSo->update([
            'MaHoiDong' => null,
            'TrangThai' => 'Đủ điều kiện bảo vệ',
        ]);

        return redirect()->back()->with('success', 'Đã hủy phân công nhóm khỏi hội đồng!');
    }

    /**
     * Bổ sung thành viên vào Hội đồng
     */
    public function themThanhVien(Request $request, $id)
    {
        $request->validate([
            'MaGV'   => 'required|exists:GiangVien,MaGV',
            'VaiTro' => 'required|in:Chủ tịch,Thư ký,Ủy viên thư ký,Phản biện,Ủy viên phản biện,Thành viên,Ủy viên',
        ]);

        $hoiDong = HoiDong::with(['thanhViens', 'hoSoBaoVes.nhom.deTai.giangVien'])->findOrFail($id);

        $exists = ThanhVienHoiDong::where('MaHoiDong', $id)->where('MaGV', $request->MaGV)->first();
        if ($exists) {
            $gv = GiangVien::find($request->MaGV);
            $tenGV = $gv ? ($gv->danh_xung_option ?? $gv->HoTen) : $request->MaGV;
            return redirect()->back()->with('error', "Giảng viên {$tenGV} đã giữ chức vụ '{$exists->VaiTro}' trong Hội đồng này. Mỗi người chỉ giữ một chức vụ trong một Hội đồng.");
        }

        // Kiểm tra giảng viên có đang là GVHD của bất kỳ nhóm nào đã gán vào Hội đồng này không
        $gvhdConflicts = [];
        foreach ($hoiDong->hoSoBaoVes as $hs) {
            if ($hs->nhom?->deTai?->MaGV === $request->MaGV) {
                $gvhdConflicts[] = "Nhóm {$hs->nhom?->MaNhom} (Đề tài: '{$hs->nhom?->deTai?->TenDeTai}')";
            }
        }
        if (!empty($gvhdConflicts)) {
            $gv = GiangVien::find($request->MaGV);
            $tenGV = $gv ? ($gv->danh_xung_option ?? $gv->HoTen) : $request->MaGV;
            return redirect()->back()->with('error', "Không thể thêm Giảng viên {$tenGV} vào Hội đồng vì Giảng viên đang là Cán bộ hướng dẫn của: " . implode(', ', $gvhdConflicts) . ". Theo quy định đào tạo, GVHD không được tham gia Hội đồng chấm nhóm do mình hướng dẫn.");
        }

        // Kiểm tra trùng lịch bảo vệ: Một giảng viên có thể tham gia nhiều hội đồng nhưng không được trùng lịch
        if ($hoiDong->ThoiGianBatDau && $hoiDong->ThoiGianKetThuc) {
            $start = Carbon::parse($hoiDong->ThoiGianBatDau);
            $end = Carbon::parse($hoiDong->ThoiGianKetThuc);

            $conflictingCouncil = HoiDong::with(['thanhViens'])
                ->where('MaHoiDong', '!=', $id)
                ->where('TrangThai', '!=', 'Đã hủy')
                ->where(function($q) use ($start, $end) {
                    $q->where('ThoiGianBatDau', '<', $end)
                      ->where('ThoiGianKetThuc', '>', $start);
                })
                ->whereHas('thanhViens', function($q) use ($request) {
                    $q->where('MaGV', $request->MaGV);
                })
                ->first();

            if ($conflictingCouncil) {
                $gv = GiangVien::find($request->MaGV);
                $tenGV = $gv ? ($gv->danh_xung_option ?? $gv->HoTen) : $request->MaGV;
                $otherRole = $conflictingCouncil->thanhViens->firstWhere('MaGV', $request->MaGV)?->VaiTro ?? 'Thành viên';
                $timeStr = Carbon::parse($conflictingCouncil->ThoiGianBatDau)->format('H:i d/m/Y') . ' đến ' . Carbon::parse($conflictingCouncil->ThoiGianKetThuc)->format('H:i d/m/Y');
                return redirect()->back()->with('error', "Giảng viên {$tenGV} đã có lịch tham gia '{$conflictingCouncil->TenHoiDong}' với vai trò {$otherRole} (từ {$timeStr} tại {$conflictingCouncil->DiaDiem}). Không thể phân công do trùng lịch bảo vệ.");
            }
        }

        $vaiTro = $request->VaiTro;
        if ($vaiTro === 'Phản biện') $vaiTro = 'Ủy viên phản biện';
        if ($vaiTro === 'Thư ký') $vaiTro = 'Ủy viên thư ký';
        if ($vaiTro === 'Thành viên') $vaiTro = 'Ủy viên';

        ThanhVienHoiDong::create([
            'MaHoiDong' => $hoiDong->MaHoiDong,
            'MaGV'      => $request->MaGV,
            'VaiTro'    => $vaiTro,
        ]);

        if ($hoiDong->TrangThaiCongBo === 'Đã công bố') {
            HoiDongPdfService::savePdfToStorage($hoiDong);
        }

        return redirect()->back()->with('success', 'Đã thêm thành viên vào Hội đồng thành công!');
    }

    /**
     * Đổi vai trò thành viên Hội đồng
     */
    public function doiVaiTroThanhVien(Request $request, $id, $maGV)
    {
        $request->validate([
            'VaiTro' => 'required|in:Chủ tịch,Thư ký,Ủy viên thư ký,Phản biện,Ủy viên phản biện,Thành viên,Ủy viên',
        ]);

        $vaiTro = $request->VaiTro;
        if ($vaiTro === 'Phản biện') $vaiTro = 'Ủy viên phản biện';
        if ($vaiTro === 'Thư ký') $vaiTro = 'Ủy viên thư ký';
        if ($vaiTro === 'Thành viên') $vaiTro = 'Ủy viên';

        $tv = ThanhVienHoiDong::where('MaHoiDong', $id)->where('MaGV', $maGV)->firstOrFail();
        $tv->update(['VaiTro' => $vaiTro]);

        $hoiDong = HoiDong::find($id);
        if ($hoiDong && $hoiDong->TrangThaiCongBo === 'Đã công bố') {
            HoiDongPdfService::savePdfToStorage($hoiDong);
        }

        return redirect()->back()->with('success', 'Đã cập nhật vai trò thành viên thành công!');
    }

    /**
     * Xóa thành viên khỏi Hội đồng
     */
    public function xoaThanhVien($id, $maGV)
    {
        $hoiDong = HoiDong::with('thanhViens')->findOrFail($id);
        if ($hoiDong->thanhViens->count() <= 3) {
            return redirect()->back()->with('error', 'Hội đồng cần duy trì tối thiểu 3 thành viên theo quy định.');
        }

        $tv = ThanhVienHoiDong::where('MaHoiDong', $id)->where('MaGV', $maGV)->firstOrFail();
        $tv->delete();

        if ($hoiDong->TrangThaiCongBo === 'Đã công bố') {
            HoiDongPdfService::savePdfToStorage($hoiDong);
        }

        return redirect()->back()->with('success', 'Đã xóa thành viên khỏi Hội đồng thành công!');
    }

    /**
     * Xóa hoàn toàn Hội đồng (nếu chưa diễn ra chấm điểm)
     */
    public function destroy($id)
    {
        $hoiDong = HoiDong::with(['hoSoBaoVes', 'phieuChamDiems'])->findOrFail($id);

        if ($hoiDong->phieuChamDiems->isNotEmpty()) {
            return redirect()->back()->with('error', 'Hội đồng đã có phiếu chấm điểm của Giảng viên, không thể xóa!');
        }

        DB::transaction(function() use ($hoiDong) {
            // Hủy liên kết hồ sơ bảo vệ
            foreach ($hoiDong->hoSoBaoVes as $hs) {
                $hs->update([
                    'MaHoiDong' => null,
                    'TrangThai' => 'Đủ điều kiện bảo vệ',
                ]);
            }
            // Xóa thành viên
            ThanhVienHoiDong::where('MaHoiDong', $hoiDong->MaHoiDong)->delete();
            // Xóa hội đồng
            $hoiDong->delete();
        });

        return redirect()->route('truongkhoa.hoidong.index')
            ->with('success', 'Đã xóa Hội đồng bảo vệ thành công!');
    }
}
