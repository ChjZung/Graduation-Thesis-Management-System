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

        if ($user && preg_match('/^TBM_[A-Z0-9]+_([A-Z0-9]+)_/i', $user->TenDangNhap, $m)) {
            $bm = BoMon::where('MaBoMon', $m[1])->first();
            if ($bm) return $bm;
        }

        return BoMon::where('TruongBoMon', 'like', '%' . ($gv->HoTen ?? '') . '%')->first() ?? BoMon::first();
    }

    public function index(Request $request)
    {
        $boMon = $this->getBoMon();
        $maBoMon = $boMon ? $boMon->MaBoMon : 'CNPM';

        $giangViens = GiangVien::where('MaBoMon', $maBoMon)->orderBy('HoTen')->get();
        $gvIds = $giangViens->pluck('MaGV');

        // Toàn bộ GV để chọn làm GV phản biện
        $allGiangViens = GiangVien::orderBy('HoTen')->get();

        $query = DeTai::with(['giangVien.boMon', 'hocKy', 'nganh', 'phanCongPhanBiens.giangVien'])
            ->whereIn('MaGV', $gvIds);

        // Tab lọc
        $statusTab = $request->input('tab', 'chua_phan_cong');

        if ($statusTab === 'chua_phan_cong') {
            $query->where('TrangThai', 'Chờ duyệt cấp Bộ môn')
                  ->whereDoesntHave('phanCongPhanBiens', fn($q) => $q->where('VaiTro', 'Phản biện đề cương'));
        } elseif ($statusTab === 'dang_phan_bien') {
            $query->where('TrangThai', 'Đang phản biện đề cương');
        } elseif ($statusTab === 'da_phan_bien') {
            $query->where(function($q) {
                $q->where('TrangThai', 'Đã phản biện - Chờ duyệt BM')
                  ->orWhereHas('phanCongPhanBiens', fn($pq) => $pq->where('VaiTro', 'Phản biện đề cương')->whereNotNull('KetQua'));
            });
        } elseif ($statusTab === 'ALL') {
            // Không giới hạn
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
                ->where('TrangThai', 'Chờ duyệt cấp Bộ môn')
                ->whereDoesntHave('phanCongPhanBiens', fn($q) => $q->where('VaiTro', 'Phản biện đề cương'))
                ->count(),
            'dang_phan_bien' => DeTai::whereIn('MaGV', $gvIds)
                ->where('TrangThai', 'Đang phản biện đề cương')
                ->count(),
            'da_phan_bien'   => DeTai::whereIn('MaGV', $gvIds)
                ->where(function($q) {
                    $q->where('TrangThai', 'Đã phản biện - Chờ duyệt BM')
                      ->orWhereHas('phanCongPhanBiens', fn($pq) => $pq->where('VaiTro', 'Phản biện đề cương')->whereNotNull('KetQua'));
                })->count(),
            'total'          => DeTai::whereIn('MaGV', $gvIds)->count(),
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

        // Quy tắc BR07: GV đề xuất đề tài không được làm GV phản biện chính đề tài đó
        if ($detai->MaGV === $request->MaGVPhanBien) {
            return redirect()->back()->withErrors('Giảng viên đề xuất đề tài không được làm Giảng viên phản biện cho đề tài này!');
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
