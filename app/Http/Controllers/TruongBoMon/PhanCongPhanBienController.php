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

    public function index(Request $request)
    {
        $boMon = $this->getBoMon();
        $maBoMon = $boMon ? $boMon->MaBoMon : 'BM_CNPM';

        $giangViens = GiangVien::where('MaBoMon', $maBoMon)->orderBy('HoTen')->get();
        $gvIds = $giangViens->pluck('MaGV');

        $user = Auth::user();
        $currentGv = GiangVien::getLoggedInGiangVien($user);

        // Giảng viên thuộc cùng Bộ môn để chọn làm GV phản biện (Loại trừ Trưởng bộ môn)
        $allGiangViens = GiangVien::with('boMon')
            ->where('MaBoMon', $maBoMon)
            ->when($currentGv, fn($q) => $q->where('MaGV', '!=', $currentGv->MaGV))
            ->orderBy('HoTen')
            ->get();

        $query = DeTai::with(['giangVien.boMon', 'hocKy', 'nganh', 'phanCongPhanBiens.giangVien'])
            ->where(function($q) use ($gvIds, $maBoMon) {
                $q->whereIn('MaGV', $gvIds)
                  ->orWhereHas('hocPhanRef', fn($hp) => $hp->where('MaBoMon', $maBoMon));
            });

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

        // Đếm số lượng cho các tab
        $counts = [
            'chua_phan_cong' => DeTai::whereIn('MaGV', $gvIds)
                ->whereIn('TrangThai', ['Đã nộp đề cương - Chờ phân công PB', 'Trưởng khoa đã duyệt - Chờ nộp đề cương'])
                ->whereDoesntHave('phanCongPhanBiens', fn($q) => $q->where('VaiTro', 'Phản biện đề cương'))
                ->count(),
            'dang_phan_bien' => DeTai::whereIn('MaGV', $gvIds)
                ->whereIn('TrangThai', ['Đang phản biện đề cương', 'Đã cập nhật đề cương - Chờ phản biện lại', 'Yêu cầu chỉnh sửa đề cương'])
                ->count(),
            'da_phan_bien'   => DeTai::whereIn('MaGV', $gvIds)
                ->where(function($q) {
                    $q->whereIn('TrangThai', ['Đã phản biện - Chờ duyệt BM', 'Đã phản biện - Chờ TBM duyệt đề cương', 'Đã công bố'])
                      ->orWhereHas('phanCongPhanBiens', fn($pq) => $pq->where('VaiTro', 'Phản biện đề cương')->whereNotNull('KetQua'));
                })->count(),
            'total'          => DeTai::whereIn('MaGV', $gvIds)
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

        return view('truongbomon.phancong.index', compact('boMon', 'detais', 'counts', 'hocKies', 'giangViens', 'allGiangViens', 'statusTab'));
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'MaGVPhanBien' => 'required|exists:GiangVien,MaGV',
        ], [
            'MaGVPhanBien.required' => 'Vui lòng chọn Giảng viên phản biện đề cương.',
        ]);

        $detai = DeTai::findOrFail($id);

        if (!$detai->FileDeCuong) {
            return redirect()->back()->withErrors('Đề tài chưa có file Đề cương chi tiết. Không thể phân công phản biện ở giai đoạn này!');
        }

        // Trưởng bộ môn không được tự phân công phản biện cho bản thân
        $user = Auth::user();
        $currentGv = GiangVien::getLoggedInGiangVien($user);
        if ($currentGv && $request->MaGVPhanBien === $currentGv->MaGV) {
            return redirect()->back()->withErrors('Trưởng bộ môn không được tự phân công phản biện đề cương cho chính bản thân! Vui lòng phân công cho giảng viên khác trong bộ môn.');
        }

        // Quy tắc BR07: GV đề xuất đề tài không được làm GV phản biện chính đề tài đó
        if ($detai->MaGV === $request->MaGVPhanBien) {
            return redirect()->back()->withErrors('Giảng viên đề xuất đề tài không được làm Giảng viên phản biện cho đề tài này!');
        }

        // Giảng viên phản biện phải thuộc cùng Bộ môn với đề tài
        $gvPB = GiangVien::find($request->MaGVPhanBien);
        if ($detai->giangVien && $gvPB && $gvPB->MaBoMon !== $detai->giangVien->MaBoMon) {
            return redirect()->back()->withErrors('Giảng viên phản biện phải thuộc cùng Bộ môn với đề tài (' . ($detai->giangVien->boMon->TenBoMon ?? '') . ')!');
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
        $gvPB = GiangVien::find($request->MaGVPhanBien);
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
}
