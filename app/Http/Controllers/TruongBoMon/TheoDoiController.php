<?php

namespace App\Http\Controllers\TruongBoMon;

use App\Http\Controllers\Controller;
use App\Models\BoMon;
use App\Models\ChiTieuHuongDan;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\MocThoiGianKhoaLuan;
use App\Models\Nhom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TheoDoiController extends Controller
{
    private function getBoMon(): BoMon
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

        abort(403, 'Không thể xác định Bộ môn của Trưởng bộ môn này.');
    }

    public function index(Request $request)
    {
        $boMon = $this->getBoMon();
        $maBoMon = $boMon->MaBoMon;

        // Học kỳ lọc
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $currentHocKy = HocKy::where('TrangThai', 'Đang diễn ra')->first() ?? $hocKies->first();
        $selectedHocKy = $request->input('MaHocKy', $currentHocKy?->MaHocKy ?? '');

        // Lấy danh sách GV thuộc Bộ môn kèm thống kê đề tài và nhóm hướng dẫn
        $giangViens = GiangVien::where('MaBoMon', $maBoMon)
            ->withCount([
                'deTais',
                'deTais as de_tais_cong_bo_count' => fn($q) => $q->where('TrangThai', 'Đã công bố'),
            ])
            ->orderBy('HoTen')
            ->get();

        $gvIds = $giangViens->pluck('MaGV');

        // Tải chỉ tiêu hướng dẫn cho từng GV (theo học kỳ hiện tại)
        $chiTieus = ChiTieuHuongDan::whereIn('MaGV', $gvIds)
            ->when($selectedHocKy, fn($q) => $q->where('MaHocKy', $selectedHocKy))
            ->get()
            ->keyBy('MaGV');

        // Gắn số nhóm đang hướng dẫn và chỉ tiêu cho từng GV
        foreach ($giangViens as $gvItem) {
            // Số nhóm đang hướng dẫn thực tế
            $gvItem->nhoms_count = Nhom::whereHas('deTai', function ($q) use ($gvItem, $selectedHocKy) {
                $q->where('MaGV', $gvItem->MaGV);
                if ($selectedHocKy && $selectedHocKy !== 'ALL') {
                    $q->where('MaHocKy', $selectedHocKy);
                }
            })->count();

            // Chỉ tiêu hướng dẫn (SoNhomToiDa)
            $ct = $chiTieus->get($gvItem->MaGV);
            $gvItem->chi_tieu_nhom = $ct ? $ct->SoNhomToiDa : null;
        }

        // Danh sách nhóm sinh viên thuộc đề tài của GV trong Bộ môn
        $queryNhoms = Nhom::with([
            'deTai.giangVien',
            'truongNhom.lop',
            'thanhViens.sinhVien',
            'dangKyDeTai',
            'baoCaos.mocThoiGian',
            'hoSoBaoVe.hoiDong',
            'hoSoBaoVe.tepHoSoBaoVes',
        ])->whereHas('deTai', function ($q) use ($gvIds, $selectedHocKy) {
            $q->whereIn('MaGV', $gvIds);
            if ($selectedHocKy && $selectedHocKy !== 'ALL') {
                $q->where('MaHocKy', $selectedHocKy);
            }
        });

        // Lọc theo Giảng viên hướng dẫn
        if ($request->filled('MaGV')) {
            $queryNhoms->whereHas('deTai', fn($q) => $q->where('MaGV', $request->MaGV));
        }

        // Lọc theo Trạng thái nhóm
        if ($request->filled('TrangThaiNhom')) {
            $queryNhoms->where('TrangThai', $request->TrangThaiNhom);
        }

        // Tìm kiếm tự do
        if ($request->filled('search')) {
            $s = trim($request->search);
            $queryNhoms->where(function ($q) use ($s) {
                $q->where('TenNhom', 'like', "%{$s}%")
                  ->orWhere('MaNhom', 'like', "%{$s}%")
                  ->orWhereHas('deTai', fn($dq) => $dq->where('TenDeTai', 'like', "%{$s}%"))
                  ->orWhereHas('truongNhom', fn($sq) => $sq->where('HoTen', 'like', "%{$s}%")->orWhere('MaSV', 'like', "%{$s}%"));
            });
        }

        $nhoms = $queryNhoms->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // ─── Xác định nhóm chậm tiến độ ───────────────────────────
        // Lấy mốc thời gian của học kỳ hiện tại
        $mocThoiGians = collect();
        if ($currentHocKy) {
            $mocThoiGians = MocThoiGianKhoaLuan::whereHas('keHoach', fn($q) => $q->where('MaHocKy', $currentHocKy->MaHocKy))
                ->orderBy('NgayKetThuc')
                ->get();
        }

        // Tính nhóm chậm: nhóm đang hoạt động nhưng chưa nộp báo cáo đúng hạn
        $today = now()->toDateString();
        $nhomChamCount = 0;
        foreach ($nhoms as $nhom) {
            if ($nhom->TrangThai === 'Hoàn thành') continue;
            foreach ($nhom->baoCaos as $bc) {
                if ($bc->TrangThai === 'Chờ nhận xét' && $bc->NgayNop && $bc->NgayNop < $today) {
                    $nhomChamCount++;
                    break;
                }
            }
        }

        // Thống kê tiến độ các nhóm
        $nhomBoMonBase = Nhom::whereHas('deTai', function ($q) use ($gvIds, $selectedHocKy) {
            $q->whereIn('MaGV', $gvIds);
            if ($selectedHocKy && $selectedHocKy !== 'ALL') {
                $q->where('MaHocKy', $selectedHocKy);
            }
        });

        $stats = [
            'total_nhom'          => (clone $nhomBoMonBase)->count(),
            'total_gv'            => $giangViens->count(),
            'total_detai'         => DeTai::whereIn('MaGV', $gvIds)->count(),
            'detai_cong_bo'       => DeTai::whereIn('MaGV', $gvIds)->where('TrangThai', 'Đã công bố')->count(),
            'nhom_hoan_thanh'     => (clone $nhomBoMonBase)->where('TrangThai', 'Hoàn thành')->count(),
            'nhom_dang_thuc_hien' => (clone $nhomBoMonBase)->where('TrangThai', '!=', 'Hoàn thành')->count(),
            'nhom_cham_tien_do'   => $nhomChamCount,
        ];

        return view('truongbomon.theodoi.index', compact(
            'boMon',
            'giangViens',
            'nhoms',
            'stats',
            'chiTieus',
            'mocThoiGians',
            'hocKies',
            'selectedHocKy',
            'currentHocKy'
        ));
    }
}
