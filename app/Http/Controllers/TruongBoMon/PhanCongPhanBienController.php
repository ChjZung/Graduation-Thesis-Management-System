<?php

namespace App\Http\Controllers\TruongBoMon;

use App\Http\Controllers\Controller;
use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\PhanCongPhanBien;
use App\Helpers\IdGenerator;
use App\Services\ThongBaoService;
use App\Services\ChiTieuPhanBienService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PhanCongPhanBienController extends Controller
{
    private function getBoMon()
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv && $gv->MaBoMon) {
            $bm = BoMon::where('MaBoMon', $gv->MaBoMon)->first();
            if ($bm) return $bm;
        }

        if ($user && preg_match('/^TBM_(?:BM_)?([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
            $bm = BoMon::where('MaBoMon', $m[1])->orWhere('MaBoMon', 'BM_' . $m[1])->first();
            if ($bm) return $bm;
        }

        return BoMon::where('TruongBoMon', 'like', '%' . ($gv->HoTen ?? '') . '%')->first() ?? BoMon::first();
    }

    /**
     * Ràng buộc phân quyền P0: Trưởng bộ môn chỉ phân công đề tài thuộc Bộ môn mình phụ trách
     */
    private function assertDeTaiBelongsToBoMon(DeTai $detai, $boMon)
    {
        if (!$boMon) return;
        $detai->loadMissing('giangVien', 'hocPhanRef');
        $isGvInBoMon = $detai->giangVien && $detai->giangVien->MaBoMon === $boMon->MaBoMon;
        $isHpInBoMon = $detai->hocPhanRef && $detai->hocPhanRef->MaBoMon === $boMon->MaBoMon;
        if (!$isGvInBoMon && !$isHpInBoMon) {
            abort(403, "Bạn không có quyền thao tác trên đề tài không thuộc Bộ môn '{$boMon->TenBoMon}'.");
        }
    }

    public function index(Request $request)
    {
        $boMon = $this->getBoMon();
        $maBoMon = $boMon ? $boMon->MaBoMon : 'BM_CNPM';
        $maKhoa = $boMon?->MaKhoa ?? 'CNTT';

        $giangViens = GiangVien::where('MaBoMon', $maBoMon)->orderBy('HoTen')->get();
        $gvIds = $giangViens->pluck('MaGV');

        $user = Auth::user();
        $currentGv = GiangVien::getLoggedInGiangVien($user);

        // Giảng viên thuộc cùng Khoa để chọn làm GV phản biện (Loại trừ Trưởng bộ môn)
        $allGiangViens = GiangVien::with('boMon')
            ->whereHas('boMon', fn($b) => $b->where('MaKhoa', $maKhoa))
            ->when($currentGv, fn($q) => $q->where('MaGV', '!=', $currentGv->MaGV))
            ->orderBy('HoTen')
            ->get();

        // Lấy thống kê chỉ tiêu phản biện của từng giảng viên (mặc định 5 đề tài, cho phép tùy chỉnh)
        $selectedHocKy = $request->input('MaHocKy');
        $statsPBMap = ChiTieuPhanBienService::getChiTieuStatsBatch($allGiangViens->pluck('MaGV')->toArray(), $selectedHocKy);

        // Chỉ hiển thị đề tài đủ điều kiện phản biện (đã có đề cương chi tiết, không bị từ chối / chưa duyệt tạo)
        $query = DeTai::with(['giangVien.boMon', 'hocKy', 'nganh', 'phanCongPhanBiens.giangVien'])
            ->where(function($q) use ($gvIds, $maBoMon) {
                $q->whereIn('MaGV', $gvIds)
                  ->orWhereHas('hocPhanRef', fn($hp) => $hp->where('MaBoMon', $maBoMon));
            })
            ->whereNotNull('FileDeCuong')
            ->where('FileDeCuong', '!=', '')
            ->whereNotIn('TrangThai', ['Từ chối', 'Bị từ chối', 'Chờ duyệt', 'Chờ duyệt cấp Bộ môn', 'Chờ duyệt cấp Khoa']);

        // Tab lọc
        $statusTab = $request->input('tab', 'chua_phan_cong');

        if ($statusTab === 'chua_phan_cong') {
            $query->whereIn('TrangThai', ['Đã nộp đề cương - Chờ phân công PB', 'Trưởng khoa đã duyệt - Chờ nộp đề cương'])
                  ->whereDoesntHave('phanCongPhanBiens', fn($q) => $q->where('VaiTro', 'Phản biện đề cương'));
        } elseif ($statusTab === 'dang_phan_bien') {
            $query->whereIn('TrangThai', ['Đang phản biện đề cương', 'Đã cập nhật đề cương - Chờ phản biện lại', 'Yêu cầu chỉnh sửa đề cương']);
        } elseif ($statusTab === 'da_phan_bien') {
            $query->where(function($q) {
                $q->whereIn('TrangThai', ['Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương', 'Đã công bố'])
                  ->orWhereHas('phanCongPhanBiens', fn($pq) => $pq->where('VaiTro', 'Phản biện đề cương')->whereNotNull('KetQua'));
            });
        } elseif ($statusTab === 'ALL') {
            $query->whereIn('TrangThai', [
                'Trưởng khoa đã duyệt - Chờ nộp đề cương',
                'Đã nộp đề cương - Chờ phân công PB',
                'Đang phản biện đề cương',
                'Đã cập nhật đề cương - Chờ phản biện lại',
                'Đã phản biện - Chờ duyệt BM',
                'Đã phản biện - Chờ TBM duyệt đề cương',
                'Đã công bố',
                'Yêu cầu chỉnh sửa đề cương',
                'Không đạt phản biện'
            ]);
        }

        // Lọc theo Học kỳ
        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        // Lọc theo Giảng viên đề xuất
        if ($request->filled('MaGV')) {
            $query->where('MaGV', $request->MaGV);
        }

        // Tìm kiếm
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('TenDeTai', 'like', "%{$s}%")
                  ->orWhere('MaDeTai', 'like', "%{$s}%")
                  ->orWhereHas('giangVien', fn($g) => $g->where('HoTen', 'like', "%{$s}%"));
            });
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        // Base query đếm số lượng cho các tab: chỉ tính đề tài đủ điều kiện phản biện
        $baseCountQuery = DeTai::where(function($q) use ($gvIds, $maBoMon) {
                $q->whereIn('MaGV', $gvIds)
                  ->orWhereHas('hocPhanRef', fn($hp) => $hp->where('MaBoMon', $maBoMon));
            })
            ->whereNotNull('FileDeCuong')
            ->where('FileDeCuong', '!=', '')
            ->whereNotIn('TrangThai', ['Từ chối', 'Bị từ chối', 'Chờ duyệt', 'Chờ duyệt cấp Bộ môn', 'Chờ duyệt cấp Khoa']);

        $counts = [
            'chua_phan_cong' => (clone $baseCountQuery)
                ->whereIn('TrangThai', ['Đã nộp đề cương - Chờ phân công PB', 'Trưởng khoa đã duyệt - Chờ nộp đề cương'])
                ->whereDoesntHave('phanCongPhanBiens', fn($q) => $q->where('VaiTro', 'Phản biện đề cương'))
                ->count(),
            'dang_phan_bien' => (clone $baseCountQuery)
                ->whereIn('TrangThai', ['Đang phản biện đề cương', 'Đã cập nhật đề cương - Chờ phản biện lại', 'Yêu cầu chỉnh sửa đề cương'])
                ->count(),
            'da_phan_bien'   => (clone $baseCountQuery)
                ->where(function($q) {
                    $q->whereIn('TrangThai', ['Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương', 'Đã công bố'])
                      ->orWhereHas('phanCongPhanBiens', fn($pq) => $pq->where('VaiTro', 'Phản biện đề cương')->whereNotNull('KetQua'));
                })->count(),
            'total'          => (clone $baseCountQuery)
                ->whereIn('TrangThai', [
                    'Trưởng khoa đã duyệt - Chờ nộp đề cương',
                    'Đã nộp đề cương - Chờ phân công PB',
                    'Đang phản biện đề cương',
                    'Đã cập nhật đề cương - Chờ phản biện lại',
                    'Đã phản biện - Chờ duyệt BM',
                    'Đã phản biện - Chờ TBM duyệt đề cương',
                    'Đã công bố',
                    'Yêu cầu chỉnh sửa đề cương',
                    'Không đạt phản biện'
                ])->count(),
        ];

        return view('truongbomon.phancong.index', compact('boMon', 'maKhoa', 'detais', 'counts', 'hocKies', 'giangViens', 'allGiangViens', 'statsPBMap', 'statusTab'));
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'MaGVPhanBien' => 'required|exists:GiangVien,MaGV',
        ], [
            'MaGVPhanBien.required' => 'Vui lòng chọn Giảng viên phản biện đề cương.',
        ]);

        $boMon = $this->getBoMon();
        $detai = DeTai::with(['giangVien.boMon', 'hocPhanRef.boMon', 'nganh'])->findOrFail($id);
        $this->assertDeTaiBelongsToBoMon($detai, $boMon);

        // 1. Chỉ phân công đề tài đủ điều kiện phản biện (phải có file đề cương)
        if (!$detai->FileDeCuong) {
            return redirect()->back()->withErrors('Đề tài chưa có file Đề cương chi tiết. Không thể phân công phản biện ở giai đoạn này!');
        }

        if (in_array($detai->TrangThai, ['Từ chối', 'Bị từ chối', 'Chờ duyệt', 'Chờ duyệt cấp Bộ môn', 'Chờ duyệt cấp Khoa'])) {
            return redirect()->back()->withErrors('Đề tài chưa được duyệt đề xuất hoặc đã bị từ chối. Không đủ điều kiện phân công phản biện!');
        }

        // 2. Trưởng bộ môn không được tự phân công phản biện cho bản thân
        $user = Auth::user();
        $currentGv = GiangVien::getLoggedInGiangVien($user);
        if ($currentGv && $request->MaGVPhanBien === $currentGv->MaGV) {
            return redirect()->back()->withErrors('Trưởng bộ môn không được tự phân công phản biện đề cương cho chính bản thân! Vui lòng phân công cho giảng viên khác trong bộ môn.');
        }

        // 3. Quy tắc BR07: GV đề xuất đề tài không được làm GV phản biện chính đề tài đó
        if ($detai->MaGV === $request->MaGVPhanBien) {
            return redirect()->back()->withErrors('Giảng viên đề xuất đề tài không được làm Giảng viên phản biện cho đề tài này!');
        }

        // 4. Chỉ GV cùng khoa với đề tài
        $gvPB = GiangVien::with('boMon')->find($request->MaGVPhanBien);
        $detaiKhoa = $detai->giangVien?->boMon?->MaKhoa ?? $detai->hocPhanRef?->boMon?->MaKhoa ?? $detai->nganh?->MaKhoa ?? $boMon?->MaKhoa;
        $gvKhoa = $gvPB?->boMon?->MaKhoa;

        if ($detaiKhoa && $gvKhoa && $detaiKhoa !== $gvKhoa) {
            return redirect()->back()->withErrors("Giảng viên phản biện phải thuộc cùng Khoa với đề tài (Khoa {$detaiKhoa})!");
        }

        // 5. Chặn server-side: 1 GV tối đa phản biện 5 đề tài (cho phép tùy chỉnh qua ChiTieuPhanBienService)
        $statsPB = ChiTieuPhanBienService::getChiTieuStats($request->MaGVPhanBien, $detai->MaHocKy, $detai->MaDeTai);
        if ($statsPB['da_dung'] >= $statsPB['tong']) {
            return redirect()->back()->withErrors("Giảng viên {$gvPB->HoTen} đã đạt tối đa hạn mức phản biện {$statsPB['tong']} đề tài trong học kỳ ({$statsPB['da_dung']}/{$statsPB['tong']})! Vui lòng chọn giảng viên khác hoặc nâng hạn mức.");
        }

        DB::transaction(function () use ($request, $detai) {
            $maPC = IdGenerator::nextPhanCong();

            PhanCongPhanBien::updateOrCreate(
                [
                    'MaDeTai' => $detai->MaDeTai,
                    'VaiTro'  => 'Phản biện đề cương',
                ],
                [
                    'MaPhanCong'   => $maPC,
                    'MaGV'         => $request->MaGVPhanBien,
                    'NgayPhanCong' => now()->toDateString(),
                    'TrangThai'    => 'Đang phản biện',
                    'NhanXet'      => null,
                    'KetQua'       => null,
                    'NgayDanhGia'  => null,
                ]
            );

            $detai->update([
                'TrangThai' => 'Đang phản biện đề cương',
            ]);
        });

        // Gửi thông báo đến GV phản biện
        if ($gvPB && $gvPB->MaTK) {
            ThongBaoService::guiDen(
                $gvPB->MaTK,
                '📋 Phân công phản biện đề cương đề tài',
                "Bạn vừa được Trưởng bộ môn phân công phản biện đề cương cho đề tài: '{$detai->TenDeTai}'. Vui lòng xem và gửi nhận xét đánh giá.",
                'Đề tài'
            );
        }

        return redirect()->back()->with('success', "Đã phân công Giảng viên phản biện đề cương cho đề tài '{$detai->TenDeTai}' thành công!");
    }

    /**
     * Tùy chỉnh hạn mức phản biện cho Giảng viên (D1).
     */
    public function updateChiTieu(Request $request)
    {
        $request->validate([
            'MaGV'         => 'required|exists:GiangVien,MaGV',
            'SoDeTaiToiDa' => 'required|integer|min:0|max:100',
            'MaHocKy'      => 'nullable|string',
            'GhiChu'       => 'nullable|string|max:500',
        ], [
            'MaGV.required'         => 'Vui lòng chọn giảng viên cần tùy chỉnh hạn mức.',
            'SoDeTaiToiDa.required' => 'Vui lòng nhập số đề tài phản biện tối đa.',
            'SoDeTaiToiDa.integer'  => 'Hạn mức phản biện phải là số nguyên.',
            'SoDeTaiToiDa.min'      => 'Hạn mức phản biện không được nhỏ hơn 0.',
            'SoDeTaiToiDa.max'      => 'Hạn mức phản biện tối đa là 100 đề tài.',
        ]);

        $gv = GiangVien::findOrFail($request->MaGV);
        ChiTieuPhanBienService::updateChiTieu(
            $request->MaGV,
            (int)$request->SoDeTaiToiDa,
            $request->filled('MaHocKy') ? $request->MaHocKy : null,
            $request->GhiChu
        );

        return redirect()->back()->with('success', "Đã cập nhật hạn mức phản biện cho giảng viên {$gv->HoTen} thành {$request->SoDeTaiToiDa} đề tài thành công!");
    }

    /**
     * Khôi phục hạn mức phản biện về mặc định 5 đề tài.
     */
    public function resetChiTieu(Request $request)
    {
        $request->validate([
            'MaGV'    => 'required|exists:GiangVien,MaGV',
            'MaHocKy' => 'nullable|string',
        ]);

        ChiTieuPhanBienService::resetChiTieu(
            $request->MaGV,
            $request->filled('MaHocKy') ? $request->MaHocKy : null
        );

        return redirect()->back()->with('success', 'Đã đặt lại hạn mức phản biện về mặc định (5 đề tài)!');
    }
}
