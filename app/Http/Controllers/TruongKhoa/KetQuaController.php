<?php

namespace App\Http\Controllers\TruongKhoa;

use App\Http\Controllers\Controller;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\HoiDong;
use App\Models\HoSoBaoVe;
use App\Models\PhieuChamDiem;
use App\Models\KetQuaSinhVien;
use App\Models\LichSuCongBoKetQua;
use App\Services\GradeCalculationService;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KetQuaController extends Controller
{
    public function __construct(private GradeCalculationService $gradeService) {}

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
            $byName = Khoa::where('TruongKhoa', 'like', '%' . ($gv->HoTen ?? '') . '%')->first();
            if ($byName) return $byName;
        }

        return Khoa::first();
    }

    public function index(Request $request)
    {
        $khoa = $this->getKhoa();
        if (!$khoa) {
            abort(403, 'Tài khoản Trưởng khoa chưa được liên kết với Khoa nào.');
        }
        $maKhoa = $khoa->MaKhoa;

        $viewMode = $request->query('view', 'hoidong'); // 'hoidong' hoặc 'toankhoa'

        $boMons = BoMon::where('MaKhoa', $maKhoa)->orderBy('TenBoMon')->get();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $activeHocKy = HocKy::where('TrangThai', 'Đang diễn ra')->first() 
            ?? HocKy::where('TrangThai', true)->first() 
            ?? $hocKies->first();

        $selectedHocKy = $request->query('MaHocKy');
        $currentMaHocKy = $request->filled('MaHocKy') ? $request->MaHocKy : ($activeHocKy?->MaHocKy);

        // Danh sách tất cả Hội đồng để làm filter dropdown
        $allHoiDongs = HoiDong::where(function ($q) use ($maKhoa) {
                $q->where('MaKhoa', $maKhoa)
                  ->orWhereNull('MaKhoa')
                  ->orWhere('MaKhoa', '')
                  ->orWhereHas('hoSoBaoVes.nhom.deTai.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa));
            })
            ->when($currentMaHocKy, fn($q) => $q->where('MaHocKy', $currentMaHocKy))
            ->orderBy('TenHoiDong')
            ->get();

        // 1. Dữ liệu Tab Tổng Hợp Theo Hội Đồng
        $hoiDongsQuery = HoiDong::where(function ($q) use ($maKhoa) {
                $q->where('MaKhoa', $maKhoa)
                  ->orWhereNull('MaKhoa')
                  ->orWhere('MaKhoa', '')
                  ->orWhereHas('hoSoBaoVes.nhom.deTai.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa))
                  ->orWhereHas('thanhViens.giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $maKhoa));
            })
            ->with([
                'thanhViens.giangVien.boMon',
                'hoSoBaoVes.nhom.deTai.giangVien.boMon',
                'hoSoBaoVes.deTai.giangVien.boMon',
                'hoSoBaoVes.nhom.thanhViens.sinhVien',
            ])
            ->orderBy('NgayBaoVe', 'desc')
            ->orderBy('MaHoiDong', 'desc');

        if ($request->filled('MaHocKy')) {
            $hoiDongsQuery->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('MaHoiDong')) {
            $hoiDongsQuery->where('MaHoiDong', $request->MaHoiDong);
        }

        if ($request->filled('MaBoMon')) {
            $maBM = $request->MaBoMon;
            $hoiDongsQuery->where(function ($sq) use ($maBM) {
                $sq->whereHas('thanhViens.giangVien', fn($gv) => $gv->where('MaBoMon', $maBM))
                   ->orWhereHas('hoSoBaoVes.nhom.deTai.giangVien', fn($gv) => $gv->where('MaBoMon', $maBM))
                   ->orWhereHas('hoSoBaoVes.deTai.giangVien', fn($gv) => $gv->where('MaBoMon', $maBM));
            });
        }

        $searchTermHd = $request->input('search_hd') ?? $request->input('search');
        if (!empty($searchTermHd) && trim($searchTermHd) !== '') {
            $s = mb_substr(trim($searchTermHd), 0, 100);
            $escaped = addcslashes($s, '%_\\');
            $hoiDongsQuery->where(function ($q) use ($escaped) {
                $q->where('TenHoiDong', 'like', "%{$escaped}%")
                  ->orWhere('DiaDiem', 'like', "%{$escaped}%")
                  ->orWhere('MaHoiDong', 'like', "%{$escaped}%")
                  ->orWhereHas('thanhViens.giangVien', fn($gv) => $gv->where('HoTen', 'like', "%{$escaped}%"))
                  ->orWhereHas('hoSoBaoVes.nhom.deTai', fn($dt) => $dt->where('TenDeTai', 'like', "%{$escaped}%"))
                  ->orWhereHas('hoSoBaoVes.deTai', fn($dt) => $dt->where('TenDeTai', 'like', "%{$escaped}%"))
                  ->orWhereHas('hoSoBaoVes.nhom', fn($n) => $n->where('TenNhom', 'like', "%{$escaped}%"));
            });
        }

        $hoiDongs = $hoiDongsQuery->get();

        // Tiến độ chấm điểm của từng Hội đồng
        $tienDoHoiDongs = [];
        foreach ($hoiDongs as $hd) {
            $tienDoHoiDongs[$hd->MaHoiDong] = $this->gradeService->kiemTraTienDoChamHoiDong($hd->MaHoiDong);
        }

        // Lọc theo trạng thái công bố trên Tab Hội Đồng nếu có filter
        if ($request->filled('trang_thai_cong_bo')) {
            $ttFilter = $request->trang_thai_cong_bo;
            $hoiDongs = $hoiDongs->filter(function ($hd) use ($ttFilter) {
                $daCongBo = $hd->hoSoBaoVes->isNotEmpty() && $hd->hoSoBaoVes->every(fn($h) => in_array($h->TrangThaiKetQua, ['DaCongBo', 'DaHoanThanh', 'CoYeuCauChinhSua']));
                if ($ttFilter === 'DaCongBo') return $daCongBo;
                if ($ttFilter === 'ChuaCongBo') return !$daCongBo;
                if ($ttFilter === 'ChuaDuPhieu') {
                    return $hd->hoSoBaoVes->contains(fn($h) => in_array($h->TrangThaiKetQua, ['ChuaDuPhieu', 'DangNhapDiem']));
                }
                return true;
            });
        }

        // 2. Dữ liệu Tab Toàn Khoa - Danh Sách Sinh Viên Theo Đề Tài
        $query = KetQuaSinhVien::with([
            'sinhVien.lop.nganh',
            'hocKy',
            'hoSoBaoVe.nhom.truongNhom',
            'hoSoBaoVe.nhom.deTai.giangVien.boMon',
            'hoSoBaoVe.deTai.giangVien.boMon',
            'hoSoBaoVe.hoiDong.thanhViens.giangVien',
        ])->where(function ($sub) use ($maKhoa) {
            $sub->whereHas('sinhVien', fn($q) => $q->where('MaKhoa', $maKhoa))
                ->orWhereHas('hoSoBaoVe.deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa))
                ->orWhereHas('hoSoBaoVe.nhom.deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));
        });

        // Áp dụng bộ lọc toàn diện
        $this->filterKetQuaQuery($query, $request);

        $ketQuas = (clone $query)->orderBy('DiemTongKet', 'desc')
            ->orderBy('MaSV', 'asc')
            ->paginate(15)
            ->withQueryString();

        // 3. TÍNH TOÁN CÁC THẺ KPI TỔNG HỢP VÀ BIỂU ĐỒ ĐỒNG NHẤT VỚI BỘ LỌC
        $allKqRecords = (clone $query)->get();
        $totalSV = $allKqRecords->count();

        // Danh sách điểm hợp lệ (> 0)
        $scoresList = $allKqRecords->map(fn($item) => (float)($item->DiemChinhThuc ?? $item->DiemTongKet ?? $item->DiemHoiDongTB))
            ->filter(fn($val) => $val > 0);

        $diemTB = $scoresList->isNotEmpty() ? round($scoresList->avg(), 2) : 0.0;
        $diemMax = $scoresList->isNotEmpty() ? round($scoresList->max(), 2) : 0.0;
        $diemMin = $scoresList->isNotEmpty() ? round($scoresList->min(), 2) : 0.0;

        $countDat = $scoresList->filter(fn($v) => $v >= 5.0)->count();
        $countKhongDat = $scoresList->filter(fn($v) => $v < 5.0)->count();
        $tyLeDat = $scoresList->isNotEmpty() ? round(($countDat / $scoresList->count()) * 100, 1) : 0;

        // Thống kê Đề tài & Hội đồng (từ các hồ sơ trong tập kết quả lọc hoặc hội đồng lọc)
        $hoSoIds = $allKqRecords->pluck('MaHoSo')->filter()->unique();
        if ($hoSoIds->isNotEmpty()) {
            $filteredHoSos = HoSoBaoVe::whereIn('MaHoSo', $hoSoIds)->with(['nhom.thanhViens', 'hoiDong.thanhViens'])->get();
        } else {
            $filteredHoSos = collect();
        }

        $tongDeTai = $filteredHoSos->count();
        $soHoiDong = $filteredHoSos->pluck('MaHoiDong')->filter()->unique()->count();

        $deTaiDaCongBo = $filteredHoSos->filter(fn($h) => in_array($h->TrangThaiKetQua, ['DaCongBo', 'CoYeuCauChinhSua', 'DaHoanThanh']))->count();
        $deTaiChuaCongBo = $tongDeTai - $deTaiDaCongBo;

        // Đề tài đã đủ 100% phiếu chấm
        $deTaiDaCham = 0;
        $deTaiChuaCham = 0;
        $canhBaoDeTaiThieuDiem = [];

        foreach ($filteredHoSos as $h) {
            $chk = $this->gradeService->kiemTraDieuKienCongBoDeTai($h->MaHoSo);
            if ($chk['du_dieu_kien']) {
                $deTaiDaCham++;
            } else {
                $deTaiChuaCham++;
                $canhBaoDeTaiThieuDiem[] = [
                    'ma_ho_so'           => $h->MaHoSo,
                    'ten_de_tai'         => $chk['ten_de_tai'] ?? ($h->nhom?->deTai?->TenDeTai ?? 'Chưa xác định'),
                    'ten_nhom'           => $chk['ten_nhom'] ?? ($h->nhom?->TenNhom ?? $h->MaNhom),
                    'ten_hoi_dong'       => $chk['ten_hoi_dong'] ?? ($h->hoiDong?->TenHoiDong ?? 'Chưa phân công'),
                    'ma_hoi_dong'        => $chk['ma_hoi_dong'] ?? $h->MaHoiDong,
                    'gv_chua_hoan_thanh' => $chk['gv_chua_hoan_thanh'] ?? [],
                    'ly_do'              => $chk['ly_do'] ?? 'Chưa hoàn thành nộp phiếu.',
                ];
            }
        }

        $tyLeHoanThanh = ($tongDeTai > 0) ? round(($deTaiDaCham / $tongDeTai) * 100, 1) : 0;

        // Phân bố điểm theo thang chữ cho Biểu đồ
        $chartDistribution = [
            'A'      => $scoresList->filter(fn($v) => $v >= 8.5)->count(),
            'B_plus' => $scoresList->filter(fn($v) => $v >= 8.0 && $v < 8.5)->count(),
            'B'      => $scoresList->filter(fn($v) => $v >= 7.0 && $v < 8.0)->count(),
            'C_plus' => $scoresList->filter(fn($v) => $v >= 6.5 && $v < 7.0)->count(),
            'C'      => $scoresList->filter(fn($v) => $v >= 5.5 && $v < 6.5)->count(),
            'D_plus' => $scoresList->filter(fn($v) => $v >= 5.0 && $v < 5.5)->count(),
            'D'      => $scoresList->filter(fn($v) => $v >= 4.0 && $v < 5.0)->count(),
            'F'      => $scoresList->filter(fn($v) => $v < 4.0)->count(),
        ];

        // Thống kê Hội đồng cho Biểu đồ (chống tràn nhãn trục x và xoay chéo cắt chữ)
        $chartCouncils = [];
        foreach ($hoiDongs as $hd) {
            $td = $tienDoHoiDongs[$hd->MaHoiDong] ?? [];
            $rawName = $hd->TenHoiDong ?: ('HĐ ' . $hd->MaHoiDong);
            $shortName = mb_substr($rawName, 0, 14) . (mb_strlen($rawName) > 14 ? '…' : '');
            $chartCouncils[] = [
                'name'     => $shortName,
                'fullName' => $rawName,
                'code'     => $hd->MaHoiDong,
                'total'    => $td['tongSoDeTai'] ?? 0,
                'duPhieu'  => $td['soDeTaiDuPhieu'] ?? 0,
            ];
        }

        $stats = [
            'tong_sinh_vien'        => $totalSV,
            'tong_de_tai'           => $tongDeTai,
            'so_hoi_dong'           => $soHoiDong ?: $hoiDongs->count(),
            'de_tai_da_cham'        => $deTaiDaCham,
            'de_tai_chua_cham'      => $deTaiChuaCham,
            'ty_le_hoan_thanh'      => $tyLeHoanThanh,
            'de_tai_da_cong_bo'     => $deTaiDaCongBo,
            'de_tai_chua_cong_bo'   => $deTaiChuaCongBo,
            'diem_trung_binh'       => $diemTB,
            'diem_cao_nhat'         => $diemMax,
            'diem_thap_nhat'        => $diemMin,
            'so_dat'                => $countDat,
            'so_khong_dat'          => $countKhongDat,
            'ty_le_dat'             => $tyLeDat,
            'chart_distribution'    => $chartDistribution,
            'chart_councils'        => $chartCouncils,
            'de_tai_thieu_diem'     => $canhBaoDeTaiThieuDiem,
            // Các metrics cũ để tương thích view & tests
            'total'                 => $totalSV,
            'xuat_sac'              => $allKqRecords->where('DiemTongKet', '>=', 9.0)->count(),
            'gioi'                  => $allKqRecords->whereBetween('DiemTongKet', [8.0, 8.99])->count(),
            'kha'                   => $allKqRecords->whereBetween('DiemTongKet', [7.0, 7.99])->count(),
            'trung_binh'            => $allKqRecords->whereBetween('DiemTongKet', [5.0, 6.99])->count(),
            'khong_dat'             => $allKqRecords->where('DiemTongKet', '<', 5.0)->count(),
        ];

        // Lấy 5 logs mới nhất để hiển thị nhanh
        $recentLogs = LichSuCongBoKetQua::latest()->take(5)->get();

        // Phản hồi AJAX linh hoạt
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            if ($viewMode === 'hoidong') {
                $html = view('truongkhoa.ketqua.partials._hoidong_list', compact('hoiDongs', 'tienDoHoiDongs'))->render();
                return response()->json([
                    'success'  => true,
                    'viewMode' => 'hoidong',
                    'html'     => $html,
                    'total'    => $hoiDongs->count(),
                    'stats'    => $stats,
                ]);
            }

            $html = view('truongkhoa.ketqua.partials._toankhoa_table', compact('ketQuas'))->render();
            return response()->json([
                'success'  => true,
                'viewMode' => 'toankhoa',
                'html'     => $html,
                'total'    => $ketQuas->total(),
                'stats'    => $stats,
            ]);
        }

        return view('truongkhoa.ketqua.index', compact(
            'khoa',
            'viewMode',
            'hoiDongs',
            'tienDoHoiDongs',
            'ketQuas',
            'boMons',
            'hocKies',
            'currentMaHocKy',
            'allHoiDongs',
            'stats',
            'recentLogs'
        ));
    }

    /**
     * Màn hình Chi tiết Tổng hợp Điểm, Đối chiếu Điểm & Công bố Hội đồng
     */
    public function showHoiDong(string $id, Request $request)
    {
        $khoa = $this->getKhoa();
        $tienDo = $this->gradeService->kiemTraTienDoChamHoiDong($id);

        if (!$tienDo['exists']) {
            abort(404, 'Không tìm thấy Hội đồng bảo vệ.');
        }

        $hoiDong = $tienDo['hoiDong'];

        // Danh sách giảng viên trong khoa để phân công kiểm tra báo cáo chỉnh sửa (nếu cần)
        $giangViensKhoa = GiangVien::whereHas('boMon', fn($q) => $q->where('MaKhoa', $khoa->MaKhoa))->orderBy('HoTen')->get();

        // Lấy tất cả các phiếu chấm gốc để modal xem trước hiển thị chi tiết
        $allPhieuChams = PhieuChamDiem::with(['giangVien', 'sinhVien'])
            ->where('MaHoiDong', $id)
            ->get();

        $rubricKLCN = PhieuChamDiem::getRubricKLCN();
        $rubricKLKS = PhieuChamDiem::getRubricKLKS();

        // Kiểm tra điều kiện công bố cho Hội đồng này
        $chkHd = $this->gradeService->kiemTraDieuKienCongBoHoiDong($id);

        return view('truongkhoa.ketqua.show_hoidong', compact(
            'khoa',
            'hoiDong',
            'tienDo',
            'giangViensKhoa',
            'allPhieuChams',
            'rubricKLCN',
            'rubricKLKS',
            'chkHd'
        ));
    }

    /**
     * Trưởng khoa xác nhận kết quả bảo vệ cho một nhóm/hồ sơ
     */
    public function xacNhanKetQua(Request $request)
    {
        $request->validate([
            'MaHoSo'           => 'required|exists:HoSoBaoVe,MaHoSo',
            'dieu_chinh_diem'  => 'nullable|array',
            'ghi_chu'          => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $nguoiXacNhan = $user->HoTen ?? ($user->TenDangNhap ?? 'Trưởng khoa');

        $this->gradeService->xacNhanKetQua(
            $request->MaHoSo,
            $nguoiXacNhan,
            $request->dieu_chinh_diem,
            $request->ghi_chu
        );

        return redirect()->back()->with('success', 'Đã xác nhận kết quả bảo vệ của nhóm thành công! Điểm chính thức đã được chốt và lưu vết kiểm toán.');
    }

    /**
     * Mức 1: Trưởng khoa công bố kết quả cho 1 Đề tài
     */
    public function congBoDeTai(Request $request)
    {
        $request->validate([
            'MaHoSo' => 'required|exists:HoSoBaoVe,MaHoSo',
        ]);

        $user = Auth::user();
        $nguoiCongBo = $user->HoTen ?? ($user->TenDangNhap ?? 'Trưởng khoa');
        $maTK = $user->MaTK ?? null;

        $res = $this->gradeService->congBoDeTai($request->MaHoSo, $nguoiCongBo, $maTK);

        if ($request->ajax()) {
            return response()->json($res);
        }

        if ($res['success']) {
            return redirect()->back()->with('success', $res['message']);
        }
        return redirect()->back()->with('error', $res['message']);
    }

    /**
     * Mức 1: Trưởng khoa thu hồi công bố kết quả của 1 Đề tài
     */
    public function thuHoiDeTai(Request $request)
    {
        $request->validate([
            'MaHoSo' => 'required|exists:HoSoBaoVe,MaHoSo',
            'ly_do'  => 'required|string|min:5|max:1000',
        ], [
            'ly_do.required' => 'Vui lòng cung cấp lý do thu hồi quyết định công bố kết quả.',
            'ly_do.min'      => 'Lý do thu hồi cần tối thiểu 5 ký tự.',
        ]);

        $user = Auth::user();
        $nguoiThuHoi = $user->HoTen ?? ($user->TenDangNhap ?? 'Trưởng khoa');
        $maTK = $user->MaTK ?? null;

        $res = $this->gradeService->thuHoiCongBoDeTai($request->MaHoSo, $nguoiThuHoi, $request->ly_do, $maTK);

        if ($request->ajax()) {
            return response()->json($res);
        }

        if ($res['success']) {
            return redirect()->back()->with('success', $res['message']);
        }
        return redirect()->back()->with('error', $res['message']);
    }

    /**
     * Mức 2: Trưởng khoa công bố kết quả chính thức cho 1 Hội đồng
     */
    public function congBoHoiDong(Request $request)
    {
        $request->validate([
            'MaHoiDong' => 'required|exists:HoiDong,MaHoiDong',
        ]);

        $user = Auth::user();
        $nguoiCongBo = $user->HoTen ?? ($user->TenDangNhap ?? 'Trưởng khoa');
        $maTK = $user->MaTK ?? null;

        $res = $this->gradeService->congBoHoiDong($request->MaHoiDong, $nguoiCongBo, $maTK);

        if ($request->ajax()) {
            return response()->json($res);
        }

        if ($res['success']) {
            return redirect()->back()->with('success', $res['message']);
        }
        return redirect()->back()->with('error', $res['message']);
    }

    /**
     * Mức 2: Trưởng khoa thu hồi công bố kết quả của 1 Hội đồng
     */
    public function thuHoiHoiDong(Request $request)
    {
        $request->validate([
            'MaHoiDong' => 'required|exists:HoiDong,MaHoiDong',
            'ly_do'     => 'required|string|min:5|max:1000',
        ], [
            'ly_do.required' => 'Vui lòng cung cấp lý do thu hồi quyết định công bố của Hội đồng.',
            'ly_do.min'      => 'Lý do thu hồi cần tối thiểu 5 ký tự.',
        ]);

        $user = Auth::user();
        $nguoiThuHoi = $user->HoTen ?? ($user->TenDangNhap ?? 'Trưởng khoa');
        $maTK = $user->MaTK ?? null;

        $res = $this->gradeService->thuHoiCongBoHoiDong($request->MaHoiDong, $nguoiThuHoi, $request->ly_do, $maTK);

        if ($request->ajax()) {
            return response()->json($res);
        }

        if ($res['success']) {
            return redirect()->back()->with('success', $res['message']);
        }
        return redirect()->back()->with('error', $res['message']);
    }

    /**
     * Mức 3: Trưởng khoa công bố kết quả chính thức Toàn Bộ Đợt / Học kỳ
     */
    public function congBoToanDot(Request $request)
    {
        $request->validate([
            'MaHocKy' => 'required|exists:HocKy,MaHocKy',
        ]);

        $khoa = $this->getKhoa();
        $user = Auth::user();
        $nguoiCongBo = $user->HoTen ?? ($user->TenDangNhap ?? 'Trưởng khoa');
        $maTK = $user->MaTK ?? null;

        $res = $this->gradeService->congBoToanDot($request->MaHocKy, $nguoiCongBo, $maTK, $khoa?->MaKhoa);

        if ($request->ajax()) {
            return response()->json($res);
        }

        if ($res['success']) {
            return redirect()->back()->with('success', $res['message']);
        }
        return redirect()->back()->with('error', $res['message']);
    }

    /**
     * Mức 3: Trưởng khoa thu hồi công bố kết quả Toàn Bộ Đợt / Học kỳ
     */
    public function thuHoiToanDot(Request $request)
    {
        $request->validate([
            'MaHocKy' => 'required|exists:HocKy,MaHocKy',
            'ly_do'   => 'required|string|min:5|max:1000',
        ], [
            'ly_do.required' => 'Vui lòng cung cấp lý do thu hồi quyết định công bố toàn đợt.',
            'ly_do.min'      => 'Lý do thu hồi cần tối thiểu 5 ký tự.',
        ]);

        $khoa = $this->getKhoa();
        $user = Auth::user();
        $nguoiThuHoi = $user->HoTen ?? ($user->TenDangNhap ?? 'Trưởng khoa');
        $maTK = $user->MaTK ?? null;

        $res = $this->gradeService->thuHoiCongBoToanDot($request->MaHocKy, $nguoiThuHoi, $request->ly_do, $maTK, $khoa?->MaKhoa);

        if ($request->ajax()) {
            return response()->json($res);
        }

        if ($res['success']) {
            return redirect()->back()->with('success', $res['message']);
        }
        return redirect()->back()->with('error', $res['message']);
    }

    /**
     * Alias cũ cho công bố hội đồng
     */
    public function congBoKetQua(Request $request)
    {
        return $this->congBoHoiDong($request);
    }

    /**
     * AJAX: Kiểm tra điều kiện công bố trước khi mở Modal xác nhận
     */
    public function kiemTraDieuKienAjax(Request $request)
    {
        $phamVi = $request->query('pham_vi', 'DE_TAI');
        $khoa = $this->getKhoa();

        if ($phamVi === 'DE_TAI') {
            $data = $this->gradeService->kiemTraDieuKienCongBoDeTai($request->query('MaHoSo', ''));
        } elseif ($phamVi === 'HOI_DONG') {
            $data = $this->gradeService->kiemTraDieuKienCongBoHoiDong($request->query('MaHoiDong', ''));
        } else {
            $maHocKy = $request->query('MaHocKy') ?? (HocKy::where('TrangThai', true)->value('MaHocKy') ?? 'HK_CURRENT');
            $data = $this->gradeService->kiemTraDieuKienCongBoToanDot($maHocKy, $khoa?->MaKhoa);
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Bảng 3: Chi tiết điểm từng đề tài theo Rubric (AJAX / Modal)
     */
    public function chiTietRubricAjax(string $maHoSo)
    {
        $data = $this->gradeService->getChiTietDiemRubric($maHoSo);
        return view('truongkhoa.ketqua.partials._modal_rubric_detail', $data);
    }

    /**
     * Xem Lịch sử Audit Log Công bố / Thu hồi (AJAX / Modal)
     */
    public function lichSuCongBoAjax(Request $request)
    {
        $logs = LichSuCongBoKetQua::with(['hoiDong', 'hoSoBaoVe'])
            ->latest()
            ->paginate(15);

        if ($request->ajax()) {
            return view('truongkhoa.ketqua.partials._modal_audit_log', compact('logs'));
        }

        return view('truongkhoa.ketqua.partials._modal_audit_log', compact('logs'));
    }

    /**
     * In / Báo Cáo Tổng Hợp Điểm Khóa Luận (Print-ready view)
     */
    public function inBaoCaoTongHop(Request $request)
    {
        $khoa = $this->getKhoa();
        $maKhoa = $khoa ? $khoa->MaKhoa : 'CNTT';
        $maHocKy = $request->query('MaHocKy') ?? (HocKy::where('TrangThai', 'Đang diễn ra')->value('MaHocKy') ?? 'HK2627_1');
        $hocKy = HocKy::find($maHocKy);

        $query = KetQuaSinhVien::with([
            'sinhVien.lop.nganh',
            'hoSoBaoVe.nhom.deTai.giangVien.boMon',
            'hoSoBaoVe.deTai.giangVien.boMon',
            'hoSoBaoVe.hoiDong.thanhViens.giangVien',
        ])->where(function ($sub) use ($maKhoa) {
            $sub->whereHas('sinhVien', fn($q) => $q->where('MaKhoa', $maKhoa))
                ->orWhereHas('hoSoBaoVe.deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa))
                ->orWhereHas('hoSoBaoVe.nhom.deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));
        });

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        } else {
            $query->where('MaHocKy', $maHocKy);
        }

        $this->filterKetQuaQuery($query, $request);
        $kqs = $query->orderBy('DiemTongKet', 'desc')->get();

        $hoiDongs = HoiDong::where('MaHocKy', $maHocKy)
            ->with(['thanhViens.giangVien', 'hoSoBaoVes.nhom.deTai'])
            ->orderBy('TenHoiDong')
            ->get();

        return view('truongkhoa.ketqua.print_report', compact('khoa', 'hocKy', 'kqs', 'hoiDongs'));
    }

    /**
     * Gửi thông báo nhắc nhở giảng viên nộp phiếu chấm điểm
     */
    public function nhacNhoChamDiem(Request $request)
    {
        $request->validate([
            'MaHoiDong' => 'required|exists:HoiDong,MaHoiDong',
            'MaHoSo'    => 'nullable|exists:HoSoBaoVe,MaHoSo',
        ]);

        $sent = $this->gradeService->guiNhacNhoChamDiem($request->MaHoiDong, $request->MaHoSo);

        return redirect()->back()->with('success', "Đã gửi thông báo nhắc nhở đến {$sent} lượt giảng viên chưa hoàn thành phiếu chấm điểm!");
    }

    /**
     * Trưởng khoa ghi nhận yêu cầu chỉnh sửa báo cáo sau bảo vệ
     */
    public function yeuCauChinhSua(Request $request)
    {
        $request->validate([
            'MaHoSo'                    => 'required|exists:HoSoBaoVe,MaHoSo',
            'YeuCauChinhSuaSauBaoVe'    => 'required|string|max:3000',
            'HanNopBaoCaoSauBaoVe'      => 'required|date|after:now',
            'NguoiKiemTraChinhSua'      => 'required|string|max:100',
        ], [
            'YeuCauChinhSuaSauBaoVe.required' => 'Vui lòng nhập nội dung cần chỉnh sửa theo kết luận của Hội đồng.',
            'HanNopBaoCaoSauBaoVe.required'   => 'Vui lòng thiết lập thời hạn hoàn thành nộp lại báo cáo.',
            'HanNopBaoCaoSauBaoVe.after'      => 'Thời hạn nộp lại phải sau thời điểm hiện tại.',
            'NguoiKiemTraChinhSua.required'   => 'Vui lòng chỉ định người kiểm tra, xác nhận báo cáo sau chỉnh sửa.',
        ]);

        $hoSo = HoSoBaoVe::with(['nhom.deTai.giangVien', 'hoiDong'])->findOrFail($request->MaHoSo);

        $hoSo->YeuCauChinhSuaSauBaoVe = $request->YeuCauChinhSuaSauBaoVe;
        $hoSo->HanNopBaoCaoSauBaoVe = $request->HanNopBaoCaoSauBaoVe;
        $hoSo->NguoiKiemTraChinhSua = $request->NguoiKiemTraChinhSua;
        $hoSo->TrangThaiChinhSuaSauBaoVe = 'ChoSinhVienNop';
        $hoSo->TrangThaiKetQua = 'CoYeuCauChinhSua';
        $hoSo->save();

        // Gửi thông báo đến nhóm sinh viên
        ThongBaoService::guiDenNhom(
            $hoSo->MaNhom,
            'Yêu cầu chỉnh sửa báo cáo sau bảo vệ khóa luận',
            "Hội đồng {$hoSo->hoiDong?->TenHoiDong} yêu cầu nhóm chỉnh sửa, hoàn thiện báo cáo khóa luận. Hạn chót: " . \Carbon\Carbon::parse($request->HanNopBaoCaoSauBaoVe)->format('d/m/Y H:i') . ". Vui lòng vào mục 'Hoàn thiện báo cáo sau bảo vệ' để xem chi tiết và nộp lại.",
            'Yêu cầu chỉnh sửa'
        );

        // Gửi thông báo đến người kiểm tra
        ThongBaoService::gui(
            $request->NguoiKiemTraChinhSua,
            'Phân công kiểm tra báo cáo sau bảo vệ',
            "Thầy/Cô được Trưởng khoa phân công kiểm tra, xác nhận báo cáo chỉnh sửa sau bảo vệ của nhóm {$hoSo->nhom?->TenNhom} (Đề tài: {$hoSo->nhom?->deTai?->TenDeTai}).",
            'Phân công'
        );

        return redirect()->back()->with('success', 'Đã ghi nhận yêu cầu chỉnh sửa báo cáo sau bảo vệ và phát thông báo đến nhóm sinh viên cùng giảng viên phụ trách kiểm tra!');
    }

    /**
     * Trưởng khoa mở khóa phiếu chấm điểm theo yêu cầu của giám khảo
     */
    public function moKhoaPhieu(Request $request)
    {
        $request->validate([
            'MaPhieuChamDiem' => 'required|exists:PhieuChamDiem,MaPhieuChamDiem',
            'LyDoMoKhoa'      => 'nullable|string|max:500',
        ]);

        $phieu = PhieuChamDiem::with(['giangVien', 'hoiDong'])->findOrFail($request->MaPhieuChamDiem);
        $phieu->TrangThai = 'Lưu nháp';
        $phieu->NgayXacNhan = null;
        $phieu->GhiChuMoKhoa = $request->LyDoMoKhoa ?? 'Trưởng khoa cho phép mở khóa để điều chỉnh điểm.';
        $phieu->save();

        // Cập nhật lại tổng hợp điểm hội đồng
        $this->gradeService->tongHopKetQuaHoiDong($phieu->MaHoiDong);

        // Gửi thông báo cho giám khảo
        ThongBaoService::gui(
            $phieu->MaGV,
            'Phiếu chấm điểm đã được mở khóa',
            "Trưởng khoa đã mở khóa phiếu chấm điểm của Thầy/Cô cho sinh viên {$phieu->MaSV} (HĐ: {$phieu->hoiDong?->TenHoiDong}). Thầy/Cô có thể vào chỉnh sửa và Ký xác nhận lại.",
            'Mở khóa'
        );

        return redirect()->back()->with('success', 'Đã mở khóa phiếu chấm điểm thành công! Giám khảo có thể chỉnh sửa lại phiếu này.');
    }

    public function exportExcel(Request $request)
    {
        $khoa = $this->getKhoa();
        $maKhoa = $khoa ? $khoa->MaKhoa : 'CNTT';

        $query = KetQuaSinhVien::with([
            'sinhVien.lop.nganh',
            'hocKy',
            'hoSoBaoVe.nhom.deTai.giangVien.boMon',
            'hoSoBaoVe.deTai.giangVien.boMon',
            'hoSoBaoVe.hoiDong'
        ])->where(function ($sub) use ($maKhoa) {
            $sub->whereHas('sinhVien', fn($q) => $q->where('MaKhoa', $maKhoa))
                ->orWhereHas('hoSoBaoVe.deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa))
                ->orWhereHas('hoSoBaoVe.nhom.deTai.giangVien.boMon', fn($q) => $q->where('MaKhoa', $maKhoa));
        });

        $this->filterKetQuaQuery($query, $request);

        $list = $query->orderBy('DiemTongKet', 'desc')->get();

        $filename = 'KetQua_KhoaLuan_' . $maKhoa . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($list) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, [
                'STT',
                'Mã SV',
                'Họ và Tên',
                'Lớp',
                'Bộ môn',
                'Đề tài khóa luận',
                'Loại Khóa Luận',
                'Điểm TB Sơ Bộ',
                'Điểm Chính Thức',
                'Trạng Thái',
                'Xếp Loại',
                'Người Xác Nhận',
                'Ngày Công Bố',
            ]);

            foreach ($list as $index => $row) {
                $diemTB = (float)($row->DiemHoiDongTB ?? 0);
                $diemCT = (float)($row->DiemChinhThuc ?? $row->DiemTongKet ?? $diemTB);
                $deTai = $row->hoSoBaoVe?->nhom?->deTai ?? $row->hoSoBaoVe?->deTai;
                $tenDeTai = $deTai?->TenDeTai ?? 'N/A';
                $tenBoMon = $deTai?->giangVien?->boMon?->TenBoMon ?? 'N/A';

                fputcsv($handle, [
                    $index + 1,
                    $row->MaSV,
                    $row->sinhVien->HoTen ?? 'N/A',
                    $row->sinhVien->lop->TenLop ?? ($row->sinhVien->MaLop ?? 'N/A'),
                    $tenBoMon,
                    $tenDeTai,
                    $row->LoaiKhoaLuan ?? 'KLCN',
                    number_format($diemTB, 2),
                    number_format($diemCT, 2),
                    $row->TrangThai ?? 'Chưa xác định',
                    $row->KetQua ?? ($row->xep_loai ?? 'N/A'),
                    $row->NguoiXacNhan ?? '',
                    $row->NgayCongBo ? \Carbon\Carbon::parse($row->NgayCongBo)->format('d/m/Y H:i') : '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Áp dụng bộ lọc toàn diện cho truy vấn Kết Quả Sinh Viên
     */
    protected function filterKetQuaQuery($query, Request $request): void
    {
        // 1. Lọc theo Học kỳ
        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        // 2. Lọc theo Hội đồng
        if ($request->filled('MaHoiDong')) {
            $query->whereHas('hoSoBaoVe', fn($q) => $q->where('MaHoiDong', $request->MaHoiDong));
        }

        // 3. Lọc theo Bộ môn (qua GVHD của Đề tài)
        if ($request->filled('MaBoMon')) {
            $maBM = $request->MaBoMon;
            $query->where(function ($sq) use ($maBM) {
                $sq->whereHas('hoSoBaoVe.nhom.deTai.giangVien', fn($q) => $q->where('MaBoMon', $maBM))
                   ->orWhereHas('hoSoBaoVe.deTai.giangVien', fn($q) => $q->where('MaBoMon', $maBM));
            });
        }

        // 4. Lọc theo Trạng thái công bố
        if ($request->filled('trang_thai_cong_bo')) {
            $ttFilter = $request->trang_thai_cong_bo;
            if ($ttFilter === 'DaCongBo') {
                $query->where(function ($sq) {
                    $sq->where('TrangThai', 'Đã công bố')
                       ->orWhereHas('hoSoBaoVe', fn($h) => $h->whereIn('TrangThaiKetQua', ['DaCongBo', 'CoYeuCauChinhSua', 'DaHoanThanh']));
                });
            } elseif ($ttFilter === 'ChuaCongBo') {
                $query->where(function ($sq) {
                    $sq->where('TrangThai', '!=', 'Đã công bố')->orWhereNull('TrangThai');
                })->whereHas('hoSoBaoVe', fn($h) => $h->whereNotIn('TrangThaiKetQua', ['DaCongBo', 'CoYeuCauChinhSua', 'DaHoanThanh']));
            } elseif ($ttFilter === 'ChuaDuPhieu') {
                $query->whereHas('hoSoBaoVe', fn($h) => $h->whereIn('TrangThaiKetQua', ['ChuaDuPhieu', 'DangNhapDiem']));
            }
        }

        // 5. Lọc theo Xếp loại (thang điểm 10)
        if ($request->filled('xep_loai')) {
            $xl = $request->xep_loai;
            $scoreCol = DB::raw('COALESCE(DiemChinhThuc, DiemTongKet, DiemHoiDongTB, 0)');
            if ($xl === 'XuatSac') {
                $query->where($scoreCol, '>=', 9.0);
            } elseif ($xl === 'Gioi') {
                $query->whereBetween($scoreCol, [8.0, 8.99]);
            } elseif ($xl === 'Kha') {
                $query->whereBetween($scoreCol, [7.0, 7.99]);
            } elseif ($xl === 'TrungBinh') {
                $query->whereBetween($scoreCol, [5.0, 6.99]);
            } elseif ($xl === 'KhongDat') {
                $query->where($scoreCol, '<', 5.0);
            }
        }

        // 6. Tìm kiếm linh hoạt từ khóa (Mã SV, họ tên, lớp, nhóm, tên đề tài, hội đồng)
        $searchTerm = $request->input('search');
        if (!empty($searchTerm) && trim($searchTerm) !== '') {
            $s = mb_substr(trim($searchTerm), 0, 100);
            $escaped = addcslashes($s, '%_\\');
            $query->where(function ($q) use ($escaped) {
                $q->where('MaSV', 'like', "%{$escaped}%")
                  ->orWhereHas('sinhVien', fn($sv) => $sv->where('HoTen', 'like', "%{$escaped}%"))
                  ->orWhereHas('sinhVien.lop', fn($l) => $l->where('TenLop', 'like', "%{$escaped}%"))
                  ->orWhereHas('hoSoBaoVe.nhom', fn($n) => $n->where('TenNhom', 'like', "%{$escaped}%"))
                  ->orWhereHas('hoSoBaoVe.nhom.deTai', fn($dt) => $dt->where('TenDeTai', 'like', "%{$escaped}%"))
                  ->orWhereHas('hoSoBaoVe.deTai', fn($dt) => $dt->where('TenDeTai', 'like', "%{$escaped}%"))
                  ->orWhereHas('hoSoBaoVe.hoiDong', fn($hd) => $hd->where('TenHoiDong', 'like', "%{$escaped}%"));
            });
        }
    }
}
