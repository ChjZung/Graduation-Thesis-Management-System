<?php

namespace App\Http\Controllers\TruongBoMon;

use App\Http\Controllers\Controller;
use App\Models\BoMon;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\PhanCongPhanBien;
use App\Models\ChiTietDuyetDeTai;
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
        if (!$user) return null;

        $gv = GiangVien::getLoggedInGiangVien($user);
        if ($gv && $gv->MaBoMon) {
            $bm = BoMon::where('MaBoMon', $gv->MaBoMon)->orWhere('MaBoMon', 'BM_' . $gv->MaBoMon)->first();
            if ($bm) return $bm;
        }

        $allBoMons = BoMon::all();
        foreach ($allBoMons as $itemBm) {
            $code = $itemBm->MaBoMon;
            if (str_contains($user->TenDangNhap, '_' . $code . '_') || str_contains($user->TenDangNhap, '_' . $code) || str_contains($user->TenDangNhap, 'BM_' . $code)) {
                return $itemBm;
            }
        }

        if (!empty($gv?->HoTen)) {
            $found = BoMon::where('TruongBoMon', 'like', '%' . $gv->HoTen . '%')->first();
            if ($found) return $found;
        }

        return null;
    }

    /**
     * Ràng buộc phân quyền P0: Trưởng bộ môn chỉ phân công đề tài thuộc Bộ môn mình phụ trách
     */
    private function assertDeTaiBelongsToBoMon(DeTai $detai, $boMon)
    {
        if (!$boMon) {
            abort(403, 'Bạn chưa được phân công phụ trách Bộ môn nào để thực hiện thao tác này.');
        }
        $detai->loadMissing('giangVien.boMon', 'hocPhanRef');
        $detaiMaBoMon = $detai->giangVien?->MaBoMon ?? $detai->hocPhanRef?->MaBoMon;
        $cleanDtBm = str_replace('BM_', '', $detaiMaBoMon ?? '');
        $cleanCurBm = str_replace('BM_', '', $boMon->MaBoMon ?? '');

        if (!$cleanDtBm || $cleanDtBm !== $cleanCurBm) {
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

        // Giảng viên thuộc cùng Bộ môn để chọn làm GV phản biện (Loại trừ GV đề xuất & Trưởng bộ môn)
        $allGiangViens = GiangVien::with('boMon')
            ->where(function($q) use ($maBoMon) {
                $q->where('MaBoMon', $maBoMon)
                  ->orWhere('MaBoMon', 'BM_' . str_replace('BM_', '', $maBoMon));
            })
            ->when($currentGv, fn($q) => $q->where('MaGV', '!=', $currentGv->MaGV))
            ->orderBy('HoTen')
            ->get();

        // Lấy thống kê chỉ tiêu phản biện của từng giảng viên (mặc định 5 đề tài, cho phép tùy chỉnh)
        $selectedHocKy = $request->input('MaHocKy');
        $statsPBMap = ChiTieuPhanBienService::getChiTieuStatsBatch($allGiangViens->pluck('MaGV')->toArray(), $selectedHocKy);

        // Chỉ hiển thị đề tài đủ điều kiện phản biện (đã có đề cương chi tiết, không bị từ chối / chưa duyệt tạo)
        $query = DeTai::with(['giangVien.boMon', 'hocKy', 'nganh', 'phanCongPhanBiens.giangVien', 'chiTietDuyets'])
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
                $q->whereIn('TrangThai', ['Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương'])
                  ->orWhere(fn($approved) => $approved->daDuyetDeCuong())
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
                    $q->whereIn('TrangThai', ['Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương'])
                      ->orWhere(fn($approved) => $approved->daDuyetDeCuong())
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
        if (!$detai->coTepDeCuong()) {
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

        // 4. Ràng buộc nghiệp vụ P0: Giảng viên được phân công PHẢI thuộc Bộ môn quản lý đề tài
        $gvPB = GiangVien::with('boMon')->find($request->MaGVPhanBien);
        $detaiMaBoMon = $detai->giangVien?->MaBoMon ?? $detai->hocPhanRef?->MaBoMon ?? $boMon?->MaBoMon;
        $cleanDtBm = str_replace('BM_', '', $detaiMaBoMon ?? '');
        $cleanGvBm = str_replace('BM_', '', $gvPB?->MaBoMon ?? '');

        if (!$cleanDtBm || $cleanDtBm !== $cleanGvBm) {
            $tenBmDetai = $detai->giangVien?->boMon?->TenBoMon ?? $boMon->TenBoMon;
            return redirect()->back()->withErrors("Giảng viên phản biện được phân công phải thuộc cùng Bộ môn quản lý đề tài (Bộ môn '{$tenBmDetai}')! Không cho phép phân công giảng viên thuộc bộ môn khác.");
        }

        // Quy định: Phân công phản biện không bị giới hạn số lượng đề tài, Trưởng bộ môn phân công theo năng lực chuyên môn.
        // Chỉ lưu thông tin thống kê số lượng đang phản biện để tham khảo nếu cần.

        $oldStatus = $detai->TrangThai;

        DB::transaction(function () use ($request, $detai, $gvPB, $user, $oldStatus) {
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

            $currentGv = GiangVien::getLoggedInGiangVien($user);
            $maDuyet = IdGenerator::nextChiTietDuyetDeTai() ?? ('CTD' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT));
            ChiTietDuyetDeTai::create([
                'MaDuyet'       => $maDuyet,
                'MaDeTai'       => $detai->MaDeTai,
                'MaGV'          => $currentGv ? $currentGv->MaGV : null,
                'NguoiThucHien' => ($currentGv ? $currentGv->HoTen : 'Trưởng Bộ Môn') . ' - Trưởng Bộ Môn',
                'HanhDong'      => 'Phân công phản biện đề cương',
                'TrangThaiCu'   => $oldStatus,
                'TrangThai'     => 'Đang phản biện đề cương',
                'LyDo'          => "Trưởng bộ môn phân công Giảng viên {$gvPB->HoTen} ({$gvPB->MaGV}) phản biện đề cương.",
                'NgayDuyet'     => now(),
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
