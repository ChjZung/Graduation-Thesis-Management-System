<?php

namespace App\Http\Controllers;

use App\Helpers\IdGenerator;
use App\Models\HocPhan;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Models\HocKy;
use App\Models\HocPhanHocKy;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class HocPhanController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'danh_muc'); // 'danh_muc' hoặc 'phan_bo_hocky'

        // Danh sách học kỳ cho Tab 2
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $defaultHk = $hocKies->where('TrangThai', 'Đang diễn ra')->first()?->MaHocKy ?? $hocKies->first()?->MaHocKy;
        $selectedHocKy = $request->get('ma_hocky', $defaultHk);
        $currentHocKyObj = $hocKies->firstWhere('MaHocKy', $selectedHocKy);

        // 1. Query cho Tab 1: Danh mục học phần (theo bộ môn)
        $queryDanhMuc = HocPhan::with(['khoa', 'boMon', 'hocPhanHocKies.hocKy'])
            ->withCount(['deTais', 'nhoms']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $queryDanhMuc->where(function ($q) use ($s, $sClean) {
                $q->where('MaHocPhan', 'like', "%{$s}%")
                  ->orWhere('TenHocPhan', 'like', "%{$s}%");
                if (!empty($sClean)) {
                    $q->orWhere('MaHocPhan', 'like', "%{$sClean}%");
                }
            });
        }
        if ($request->filled('ma_bomon')) {
            $queryDanhMuc->where('MaBoMon', $request->ma_bomon);
        }
        if ($request->filled('loai_hocphan')) {
            $queryDanhMuc->where('LoaiHocPhan', $request->loai_hocphan);
        }
        if ($request->filled('trang_thai')) {
            $queryDanhMuc->where('TrangThai', $request->trang_thai);
        }

        $hocPhanBoMons = $queryDanhMuc->orderBy('MaBoMon')->orderBy('LoaiHocPhan', 'desc')->orderBy('TenHocPhan')->paginate(15, ['*'], 'hp_page')->withQueryString();

        // 2. Query cho Tab 2: Phân bổ học phần theo học kỳ
        $queryPhanBo = HocPhan::with(['boMon', 'hocPhanHocKies' => function($q) use ($selectedHocKy) {
            $q->where('MaHocKy', $selectedHocKy);
        }]);

        if ($request->filled('ma_bomon_hk')) {
            $queryPhanBo->where('MaBoMon', $request->ma_bomon_hk);
        }
        if ($request->filled('search_hk')) {
            $s = trim($request->search_hk);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $queryPhanBo->where(function ($q) use ($s, $sClean) {
                $q->where('MaHocPhan', 'like', "%{$s}%")
                  ->orWhere('TenHocPhan', 'like', "%{$s}%");
                if (!empty($sClean)) {
                    $q->orWhere('MaHocPhan', 'like', "%{$sClean}%");
                }
            });
        }

        $hocPhanTheoHocKy = $queryPhanBo->orderBy('MaBoMon')->orderBy('LoaiHocPhan', 'desc')->orderBy('TenHocPhan')->get();

        // Thống kê Tab 2
        $totalHpHk = $hocPhanTheoHocKy->count();
        $openedHpHk = $hocPhanTheoHocKy->filter(function($hp) {
            return $hp->hocPhanHocKies->where('TrangThai', 'Đang mở')->isNotEmpty();
        })->count();

        $statsHk = [
            'total'  => $totalHpHk,
            'opened' => $openedHpHk,
            'closed' => $totalHpHk - $openedHpHk,
        ];

        $bomons = BoMon::orderBy('TenBoMon')->get();
        $khoas = Khoa::orderBy('TenKhoa')->get();

        // Thống kê 4 thẻ đầu trang
        $stats = [
            'total'   => HocPhan::count(),
            'cnpm'    => HocPhan::where('MaBoMon', 'BM_CNPM')->count(),
            'httt'    => HocPhan::where('MaBoMon', 'BM_HTTT')->count(),
            'attt_khmt' => HocPhan::whereIn('MaBoMon', ['BM_ATTT', 'BM_KHMT'])->count(),
        ];

        $suggestedMaHocPhan = IdGenerator::nextHocPhan();

        return view('admin.hocphan.index', compact(
            'hocPhanBoMons',
            'hocPhanTheoHocKy',
            'bomons',
            'khoas',
            'hocKies',
            'selectedHocKy',
            'currentHocKyObj',
            'stats',
            'statsHk',
            'activeTab',
            'suggestedMaHocPhan'
        ));
    }

    public function create()
    {
        $khoas = Khoa::orderBy('TenKhoa')->get();
        $bomons = BoMon::orderBy('TenBoMon')->get();
        return view('admin.hocphan.create', compact('khoas', 'bomons'));
    }

    public function store(Request $request)
    {
        if ($request->filled('TenHocPhan')) {
            $request->merge(['TenHocPhan' => trim($request->TenHocPhan)]);
        }
        if ($request->filled('MaHocPhan')) {
            $request->merge(['MaHocPhan' => strtoupper(trim($request->MaHocPhan))]);
        }

        $request->validate([
            'MaHocPhan'   => 'nullable|string|max:20|unique:HocPhan,MaHocPhan',
            'TenHocPhan'  => 'required|string|max:150|unique:HocPhan,TenHocPhan',
            'SoTinChi'    => 'required|integer|min:1|max:30',
            'SoTietLT'    => 'nullable|integer|min:0',
            'SoTietTH'    => 'nullable|integer|min:0',
            'SoTietKhac'  => 'nullable|integer|min:0',
            'MaKhoa'      => 'nullable|exists:Khoa,MaKhoa',
            'MaBoMon'     => 'required|exists:BoMon,MaBoMon',
            'LoaiHocPhan' => 'nullable|string|max:50',
            'TrangThai'   => 'required|string|max:50',
            'MoTa'        => 'nullable|string',
        ], [
            'MaHocPhan.unique'    => 'Mã học phần đã tồn tại trong hệ thống.',
            'TenHocPhan.required' => 'Vui lòng nhập tên học phần.',
            'TenHocPhan.unique'   => 'Tên học phần này đã tồn tại trong hệ thống.',
            'SoTinChi.required'   => 'Vui lòng nhập số tín chỉ.',
            'MaBoMon.required'    => 'Vui lòng chọn Bộ môn phụ trách cho học phần này.',
        ]);

        $maHocPhan = $request->filled('MaHocPhan') ? $request->MaHocPhan : IdGenerator::nextHocPhan();

        $hocphan = HocPhan::create([
            'MaHocPhan'   => $maHocPhan,
            'TenHocPhan'  => $request->TenHocPhan,
            'SoTinChi'    => $request->SoTinChi,
            'SoTietLT'    => $request->input('SoTietLT', 0) ?: 0,
            'SoTietTH'    => $request->input('SoTietTH', 0) ?: 0,
            'SoTietKhac'  => $request->input('SoTietKhac', 0) ?: 0,
            'MaKhoa'      => $request->MaKhoa ?: 'CNTT',
            'MaBoMon'     => $request->MaBoMon,
            'LoaiHocPhan' => $request->LoaiHocPhan ?: 'Chuyên ngành',
            'TrangThai'   => $request->TrangThai,
            'MoTa'        => $request->MoTa,
        ]);

        // Nếu học phần ở trạng thái Đang sử dụng, tự động mở cho các học kỳ đang diễn ra
        if ($hocphan->TrangThai === 'Đang sử dụng' || $hocphan->TrangThai === 'Đang áp dụng') {
            $activeHks = HocKy::where('TrangThai', 'Đang diễn ra')->pluck('MaHocKy');
            foreach ($activeHks as $hkCode) {
                HocPhanHocKy::updateOrCreate(
                    ['MaHocPhan' => $hocphan->MaHocPhan, 'MaHocKy' => $hkCode],
                    ['TrangThai' => 'Đang mở', 'GhiChu' => 'Mở tự động khi tạo mới']
                );
            }
        }

        return redirect()->route('hocphan.index', ['tab' => 'danh_muc'])
            ->with('success', "Thêm học phần '{$hocphan->TenHocPhan}' thuộc Bộ môn thành công!");
    }

    public function show($id)
    {
        $hocphan = HocPhan::with(['khoa', 'boMon', 'deTais.giangVien', 'nhoms.truongNhom', 'hocPhanHocKies.hocKy'])
            ->withCount(['deTais', 'nhoms'])
            ->findOrFail($id);

        return view('admin.hocphan.show', compact('hocphan'));
    }

    public function edit($id)
    {
        $hocphan = HocPhan::findOrFail($id);
        $khoas = Khoa::orderBy('TenKhoa')->get();
        $bomons = BoMon::orderBy('TenBoMon')->get();
        return view('admin.hocphan.edit', compact('hocphan', 'khoas', 'bomons'));
    }

    public function update(Request $request, $id)
    {
        $hocphan = HocPhan::findOrFail($id);

        if ($request->filled('TenHocPhan')) {
            $request->merge(['TenHocPhan' => trim($request->TenHocPhan)]);
        }

        $request->validate([
            'TenHocPhan'  => 'required|string|max:150|unique:HocPhan,TenHocPhan,' . $id . ',MaHocPhan',
            'SoTinChi'    => 'required|integer|min:1|max:30',
            'SoTietLT'    => 'nullable|integer|min:0',
            'SoTietTH'    => 'nullable|integer|min:0',
            'SoTietKhac'  => 'nullable|integer|min:0',
            'MaBoMon'     => 'required|exists:BoMon,MaBoMon',
            'TrangThai'   => 'required|string|max:50',
            'MoTa'        => 'nullable|string',
        ], [
            'TenHocPhan.required' => 'Vui lòng nhập tên học phần.',
            'TenHocPhan.unique'   => 'Tên học phần này đã tồn tại trong hệ thống.',
            'MaBoMon.required'    => 'Vui lòng chọn Bộ môn phụ trách.',
        ]);

        $updateData = [
            'TenHocPhan'  => $request->TenHocPhan,
            'SoTinChi'    => $request->SoTinChi,
            'SoTietLT'    => $request->input('SoTietLT', 0) ?: 0,
            'SoTietTH'    => $request->input('SoTietTH', 0) ?: 0,
            'SoTietKhac'  => $request->input('SoTietKhac', 0) ?: 0,
            'MaBoMon'     => $request->MaBoMon,
            'TrangThai'   => $request->TrangThai,
            'MoTa'        => $request->MoTa,
        ];

        if ($request->filled('LoaiHocPhan')) {
            $updateData['LoaiHocPhan'] = $request->LoaiHocPhan;
        }

        $hocphan->update($updateData);

        return redirect()->route('hocphan.index', ['tab' => 'danh_muc'])
            ->with('success', "Cập nhật học phần '{$hocphan->TenHocPhan}' thành công!");
    }

    public function destroy($id)
    {
        $hocphan = HocPhan::withCount(['deTais', 'nhoms'])->findOrFail($id);

        if ($hocphan->de_tais_count > 0 || $hocphan->nhoms_count > 0) {
            return redirect()->back()->withErrors("Không thể xóa học phần đang được sử dụng trong hệ thống. Vui lòng chuyển trạng thái sang “Ngừng sử dụng”. (Hiện có {$hocphan->de_tais_count} đề tài và {$hocphan->nhoms_count} nhóm khóa luận liên quan)");
        }

        try {
            $tenHp = $hocphan->TenHocPhan;
            HocPhanHocKy::where('MaHocPhan', $hocphan->MaHocPhan)->delete();
            $hocphan->delete();
            return redirect()->route('hocphan.index', ['tab' => 'danh_muc'])
                ->with('success', "Xóa học phần '{$tenHp}' thành công!");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa học phần '{$hocphan->TenHocPhan}': " . $e->getMessage());
        }
    }

    /**
     * Bật / Tắt mở một học phần trong học kỳ cụ thể (AJAX hoặc Form POST)
     */
    public function toggleHocKy(Request $request)
    {
        $request->validate([
            'MaHocPhan' => 'required|exists:HocPhan,MaHocPhan',
            'MaHocKy'   => 'required|exists:HocKy,MaHocKy',
        ]);

        $maHp = $request->MaHocPhan;
        $maHk = $request->MaHocKy;

        $record = HocPhanHocKy::where('MaHocPhan', $maHp)->where('MaHocKy', $maHk)->first();

        if ($record && $record->TrangThai === 'Đang mở') {
            $newStatus = 'Ngừng mở';
            $record->update(['TrangThai' => $newStatus]);
        } else {
            $newStatus = 'Đang mở';
            HocPhanHocKy::updateOrCreate(
                ['MaHocPhan' => $maHp, 'MaHocKy' => $maHk],
                ['TrangThai' => $newStatus, 'GhiChu' => 'Cập nhật từ Quản lý học phần']
            );
        }

        $hp = HocPhan::find($maHp);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'    => true,
                'status'     => $newStatus,
                'is_opened'  => ($newStatus === 'Đang mở'),
                'message'    => "Đã " . ($newStatus === 'Đang mở' ? 'mở' : 'đóng') . " học phần {$hp?->TenHocPhan} trong học kỳ đã chọn.",
            ]);
        }

        return redirect()->back()->with('success', "Đã chuyển trạng thái học phần '{$hp?->TenHocPhan}' sang: {$newStatus}!");
    }

    /**
     * Mở tất cả hoặc đóng tất cả học phần cho một học kỳ
     */
    public function bulkHocKy(Request $request)
    {
        $request->validate([
            'MaHocKy' => 'required|exists:HocKy,MaHocKy',
            'action'  => 'required|in:open_all,close_all',
        ]);

        $maHk = $request->MaHocKy;
        $action = $request->action;
        $hk = HocKy::find($maHk);

        $hocPhans = HocPhan::whereIn('TrangThai', ['Đang sử dụng', 'Đang áp dụng', '1'])->get();

        if ($action === 'open_all') {
            foreach ($hocPhans as $hp) {
                HocPhanHocKy::updateOrCreate(
                    ['MaHocPhan' => $hp->MaHocPhan, 'MaHocKy' => $maHk],
                    ['TrangThai' => 'Đang mở', 'GhiChu' => 'Mở hàng loạt']
                );
            }
            $msg = "Đã mở tất cả " . $hocPhans->count() . " học phần cho {$hk?->TenHocKy}!";
        } else {
            HocPhanHocKy::where('MaHocKy', $maHk)->update(['TrangThai' => 'Ngừng mở']);
            $msg = "Đã đóng tất cả học phần trong {$hk?->TenHocKy}!";
        }

        return redirect()->route('hocphan.index', ['tab' => 'phan_bo_hocky', 'ma_hocky' => $maHk])
            ->with('success', $msg);
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importHocPhan', [], 'Học Phần');
    }
}
