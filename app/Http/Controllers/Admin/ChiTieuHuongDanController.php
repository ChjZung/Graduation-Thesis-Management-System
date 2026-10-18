<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiangVien;
use App\Models\BoMon;
use App\Models\Khoa;
use App\Models\HocKy;
use App\Models\ChiTieuHuongDan;
use App\Services\ChiTieuService;
use App\Helpers\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ChiTieuHuongDanController extends Controller
{
    /**
     * Màn hình giáo vụ: Xem và thiết lập chỉ tiêu hướng dẫn theo giảng viên và học kỳ.
     */
    public function index(Request $request)
    {
        $hocKys = HocKy::orderBy('MaHocKy', 'desc')->get();
        $currentHk = HocKy::where('TrangThai', 'Đang diễn ra')->first() ?? $hocKys->first();
        $selectedHocKy = $request->get('MaHocKy', $currentHk?->MaHocKy ?? '');

        $khoas = Khoa::orderBy('TenKhoa')->get();
        $bomons = BoMon::with('khoa')->orderBy('TenBoMon')->get();

        // 1. Lọc giảng viên theo các tiêu chí cơ bản
        $gvQuery = GiangVien::with(['boMon.khoa', 'taiKhoan']);

        if ($request->filled('MaKhoa')) {
            $maKhoa = $request->MaKhoa;
            $gvQuery->whereHas('boMon', fn($b) => $b->where('MaKhoa', $maKhoa));
        }

        if ($request->filled('MaBoMon')) {
            $gvQuery->where('MaBoMon', $request->MaBoMon);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $gvQuery->where(function ($q) use ($s) {
                $q->where('HoTen', 'LIKE', "%{$s}%")
                  ->orWhere('MaGV', 'LIKE', "%{$s}%")
                  ->orWhere('Email', 'LIKE', "%{$s}%");
            });
        }

        $allMatchingGvs = $gvQuery->orderBy('MaBoMon')->orderBy('MaGV')->get();
        $allGvIds = $allMatchingGvs->pluck('MaGV')->toArray();

        // 2. Nạp dữ liệu cấu hình chỉ tiêu và thống kê số nhóm đã dùng theo học kỳ
        $customConfigs = ChiTieuHuongDan::where('MaHocKy', $selectedHocKy)
            ->whereIn('MaGV', $allGvIds)
            ->get()
            ->keyBy('MaGV');

        $statsBatch = ChiTieuService::getChiTieuStatsBatch($allGvIds, $selectedHocKy);

        // 3. Ghép nối dữ liệu chi tiết cho từng giảng viên
        $enrichedList = $allMatchingGvs->map(function ($gv) use ($customConfigs, $statsBatch) {
            $maGV = $gv->MaGV;
            $custom = $customConfigs->get($maGV);
            $stat = $statsBatch[$maGV] ?? [
                'tong'       => 5,
                'da_dung'    => 0,
                'con_lai'    => 5,
                'tinh_trang' => 'con_chi_tieu',
                'is_full'    => false,
                'label'      => '0/5',
            ];

            $gv->is_custom = !is_null($custom);
            $gv->custom_record = $custom;
            $gv->chi_tieu_tong = $stat['tong'];
            $gv->da_dung = $stat['da_dung'];
            $gv->con_lai = $stat['con_lai'];
            $gv->tinh_trang = $stat['tinh_trang'];
            $gv->is_full = $stat['is_full'];
            $gv->label = $stat['label'];
            $gv->ngay_phan_bo = $custom?->NgayPhanBo;

            return $gv;
        });

        // 4. Thống kê KPI tổng quan (toàn bộ danh sách giảng viên phù hợp)
        $kpiStats = [
            'tong_gv'         => $enrichedList->count(),
            'da_thiet_lap'    => $enrichedList->where('is_custom', true)->count(),
            'dung_mac_dinh'   => $enrichedList->where('is_custom', false)->count(),
            'dang_huong_dan'  => $enrichedList->where('da_dung', '>', 0)->count(),
            'het_chi_tieu'    => $enrichedList->where('tinh_trang', 'het_chi_tieu')->count(),
            'vuot_chi_tieu'   => $enrichedList->where('tinh_trang', 'vuot_chi_tieu')->count(),
        ];

        // 5. Lọc theo tình trạng chỉ tiêu (nếu có yêu cầu từ giao diện)
        if ($request->filled('tinh_trang')) {
            $tt = $request->tinh_trang;
            if ($tt === 'con_chi_tieu') {
                $enrichedList = $enrichedList->where('tinh_trang', 'con_chi_tieu');
            } elseif ($tt === 'het_chi_tieu') {
                $enrichedList = $enrichedList->where('tinh_trang', 'het_chi_tieu');
            } elseif ($tt === 'vuot_chi_tieu') {
                $enrichedList = $enrichedList->where('tinh_trang', 'vuot_chi_tieu');
            } elseif ($tt === 'da_thiet_lap') {
                $enrichedList = $enrichedList->where('is_custom', true);
            } elseif ($tt === 'dung_mac_dinh') {
                $enrichedList = $enrichedList->where('is_custom', false);
            }
        }

        // 6. Phân trang: Giới hạn 5 thông tin / 1 trang theo yêu cầu giao diện
        $perPage = (int) $request->get('per_page', 5);
        if ($perPage <= 0) {
            $perPage = 5;
        }
        $currentPage = (int) $request->input('page', LengthAwarePaginator::resolveCurrentPage() ?: 1);
        if ($currentPage <= 0) {
            $currentPage = 1;
        }
        $currentItems = $enrichedList->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedGiangViens = new LengthAwarePaginator(
            $currentItems,
            $enrichedList->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.chi_tieu_huong_dan.index', [
            'giangviens'    => $paginatedGiangViens,
            'hocKys'        => $hocKys,
            'selectedHocKy' => $selectedHocKy,
            'currentHk'     => $currentHk,
            'khoas'         => $khoas,
            'bomons'        => $bomons,
            'kpiStats'      => $kpiStats,
            'perPage'       => $perPage,
        ]);
    }

    /**
     * Thiết lập / Cập nhật chỉ tiêu cho 1 giảng viên trong học kỳ.
     * Validate nghiêm ngặt số nguyên >= 0 theo yêu cầu P4.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'MaGV'        => 'required|string|exists:GiangVien,MaGV',
            'MaHocKy'     => 'required|string|exists:HocKy,MaHocKy',
            'SoNhomToiDa' => 'required|integer|min:0',
        ], [
            'MaGV.required'        => 'Vui lòng chỉ định mã giảng viên.',
            'MaGV.exists'          => 'Giảng viên không tồn tại trong hệ thống.',
            'MaHocKy.required'     => 'Vui lòng chọn học kỳ áp dụng.',
            'MaHocKy.exists'       => 'Học kỳ không tồn tại trong hệ thống.',
            'SoNhomToiDa.required' => 'Vui lòng nhập chỉ tiêu hướng dẫn tối đa.',
            'SoNhomToiDa.integer'  => 'Chỉ tiêu hướng dẫn phải là số nguyên (không được là số thập phân).',
            'SoNhomToiDa.min'      => 'Chỉ tiêu hướng dẫn phải là số nguyên lớn hơn hoặc bằng 0 (>= 0).',
        ]);

        $maGV = $validated['MaGV'];
        $maHocKy = $validated['MaHocKy'];
        $soNhomToiDa = (int)$validated['SoNhomToiDa'];

        $gv = GiangVien::findOrFail($maGV);

        DB::transaction(function () use ($maGV, $maHocKy, $soNhomToiDa) {
            $record = ChiTieuHuongDan::where('MaGV', $maGV)
                ->where('MaHocKy', $maHocKy)
                ->first();

            if ($record) {
                $record->update([
                    'SoNhomToiDa' => $soNhomToiDa,
                    'NgayPhanBo'  => now()->toDateString(),
                ]);
            } else {
                ChiTieuHuongDan::create([
                    'MaChiTieu'   => IdGenerator::nextChiTieuHuongDan(),
                    'MaGV'        => $maGV,
                    'MaHocKy'     => $maHocKy,
                    'SoNhomToiDa' => $soNhomToiDa,
                    'NgayPhanBo'  => now()->toDateString(),
                ]);
            }
        });

        return redirect()->back()->with('success', "Đã thiết lập chỉ tiêu hướng dẫn cho giảng viên {$gv->HoTen} ({$maGV}) thành công: tối đa {$soNhomToiDa} nhóm trong học kỳ {$maHocKy}.");
    }

    /**
     * Thiết lập chỉ tiêu hàng loạt cho nhiều giảng viên.
     * Validate nghiêm ngặt số nguyên >= 0 theo yêu cầu P4.
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'MaHocKy'     => 'required|string|exists:HocKy,MaHocKy',
            'MaBoMon'     => 'nullable|string|exists:BoMon,MaBoMon',
            'SoNhomToiDa' => 'required|integer|min:0',
            'apply_scope' => 'required|string|in:all,default_only',
        ], [
            'MaHocKy.required'     => 'Vui lòng chọn học kỳ áp dụng.',
            'MaHocKy.exists'       => 'Học kỳ không tồn tại trong hệ thống.',
            'SoNhomToiDa.required' => 'Vui lòng nhập chỉ tiêu hướng dẫn áp dụng.',
            'SoNhomToiDa.integer'  => 'Chỉ tiêu hướng dẫn phải là số nguyên (không được là số thập phân).',
            'SoNhomToiDa.min'      => 'Chỉ tiêu hướng dẫn phải là số nguyên lớn hơn hoặc bằng 0 (>= 0).',
            'apply_scope.required' => 'Vui lòng chọn phạm vi áp dụng.',
            'apply_scope.in'       => 'Phạm vi áp dụng không hợp lệ.',
        ]);

        $maHocKy = $validated['MaHocKy'];
        $soNhomToiDa = (int)$validated['SoNhomToiDa'];
        $maBoMon = $validated['MaBoMon'] ?? null;
        $applyScope = $validated['apply_scope'];

        $query = GiangVien::query();
        if ($maBoMon) {
            $query->where('MaBoMon', $maBoMon);
        }
        $targetGvs = $query->get();

        $updatedCount = 0;
        DB::transaction(function () use ($targetGvs, $maHocKy, $soNhomToiDa, $applyScope, &$updatedCount) {
            $existingRecords = ChiTieuHuongDan::where('MaHocKy', $maHocKy)
                ->whereIn('MaGV', $targetGvs->pluck('MaGV'))
                ->get()
                ->keyBy('MaGV');

            foreach ($targetGvs as $gv) {
                $existing = $existingRecords->get($gv->MaGV);

                if ($applyScope === 'default_only' && $existing) {
                    // Bỏ qua các GV đã có chỉ tiêu riêng nếu chọn default_only
                    continue;
                }

                if ($existing) {
                    $existing->update([
                        'SoNhomToiDa' => $soNhomToiDa,
                        'NgayPhanBo'  => now()->toDateString(),
                    ]);
                } else {
                    ChiTieuHuongDan::create([
                        'MaChiTieu'   => IdGenerator::nextChiTieuHuongDan(),
                        'MaGV'        => $gv->MaGV,
                        'MaHocKy'     => $maHocKy,
                        'SoNhomToiDa' => $soNhomToiDa,
                        'NgayPhanBo'  => now()->toDateString(),
                    ]);
                }
                $updatedCount++;
            }
        });

        return redirect()->back()->with('success', "Đã áp dụng chỉ tiêu {$soNhomToiDa} nhóm thành công cho {$updatedCount} giảng viên trong học kỳ {$maHocKy}.");
    }

    /**
     * Khôi phục về định mức mặc định (xóa bản ghi cấu hình riêng trong học kỳ).
     */
    public function reset(Request $request)
    {
        $validated = $request->validate([
            'MaGV'    => 'required|string|exists:GiangVien,MaGV',
            'MaHocKy' => 'required|string|exists:HocKy,MaHocKy',
        ]);

        $gv = GiangVien::findOrFail($validated['MaGV']);

        ChiTieuHuongDan::where('MaGV', $validated['MaGV'])
            ->where('MaHocKy', $validated['MaHocKy'])
            ->delete();

        return redirect()->back()->with('success', "Đã khôi phục chỉ tiêu hướng dẫn của giảng viên {$gv->HoTen} về mức mặc định hệ thống (5 nhóm) cho học kỳ {$validated['MaHocKy']}.");
    }
}
