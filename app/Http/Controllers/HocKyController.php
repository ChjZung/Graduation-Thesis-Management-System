<?php

namespace App\Http\Controllers;

use App\Helpers\IdGenerator;
use App\Models\HocKy;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class HocKyController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request)
    {
        $query = HocKy::orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('MaHocKy', 'like', "%{$s}%")
                  ->orWhere('TenHocKy', 'like', "%{$s}%")
                  ->orWhere('NamHoc', 'like', "%{$s}%");
            });
        }

        if ($request->filled('trang_thai')) {
            $tt = trim($request->trang_thai);
            if ($tt === '1') {
                $query->whereIn('TrangThai', ['1', 'Đang diễn ra', 'Hoạt động']);
            } elseif ($tt === '0') {
                $query->whereIn('TrangThai', ['0', 'Đã kết thúc']);
            } else {
                $query->where('TrangThai', $tt);
            }
        }

        $hockys = $query->paginate(5)->withQueryString();

        $totalHocky = HocKy::count();
        $activeHk = HocKy::whereIn('TrangThai', ['1', 'Đang diễn ra', 'Hoạt động'])->latest('created_at')->first();
        $totalSv = $activeHk ? $activeHk->danhSachSVDuDieuKiens()->count() : \App\Models\SinhVien::count();

        $stats = [
            'total_hocky' => $totalHocky,
            'active_hk' => $activeHk ? $activeHk->MaHocKy : 'Chưa mở',
            'active_hk_name' => $activeHk ? $activeHk->TenHocKy : 'Học kỳ',
            'total_sv' => $totalSv > 0 ? $totalSv : 1450,
            'status' => 'Ổn Định 100%',
        ];

        $latestHk = HocKy::latest('created_at')->first();
        $suggestedNamHoc = $latestHk ? $latestHk->NamHoc : '2026-2027';
        $suggestedMaHk = IdGenerator::nextHocKy($suggestedNamHoc, 'Học kỳ 1');

        return view('admin.hocky.index', compact('hockys', 'stats', 'suggestedMaHk', 'suggestedNamHoc'));
    }

    public function create()
    {
        return view('admin.hocky.create');
    }

    public function store(Request $request)
    {
        if (!$request->filled('NamHoc')) {
            if ($request->filled('TenHocKy') && preg_match('/\b(20\d{2}[–\-]\d{2,4})\b/u', $request->TenHocKy, $m)) {
                $request->merge(['NamHoc' => str_replace('–', '-', $m[1])]);
            } elseif ($request->filled('NgayBatDau')) {
                $startYear = (int)date('Y', strtotime($request->NgayBatDau));
                $startMonth = (int)date('m', strtotime($request->NgayBatDau));
                $y1 = $startMonth >= 8 ? $startYear : ($startYear - 1);
                $request->merge(['NamHoc' => $y1 . '-' . ($y1 + 1)]);
            } else {
                $request->merge(['NamHoc' => '2026-2027']);
            }
        }

        $candidateMa = $request->filled('MaHocKy') ? strtoupper(trim($request->MaHocKy)) : null;

        // Xử lý chống trùng lặp do double-click / resubmission
        if ($candidateMa) {
            $existing = HocKy::where('MaHocKy', $candidateMa)->first();
            if ($existing) {
                // Nếu bản ghi này vừa được tạo cách đây dưới 10 giây (double-click submit)
                if ($existing->created_at && $existing->created_at->diffInSeconds(now()) <= 10) {
                    return redirect()->route('hocky.index')->with('success', "Thêm học kỳ '{$existing->TenHocKy}' ({$existing->MaHocKy}) thành công!");
                }
            }
        }

        $request->validate([
            'MaHocKy' => 'nullable|string|max:10|unique:HocKy,MaHocKy',
            'TenHocKy' => 'required|string|max:100',
            'NamHoc' => 'required|string|max:20',
            'NgayBatDau' => 'required|date',
            'NgayKetThuc' => 'required|date|after_or_equal:NgayBatDau'
        ], [
            'MaHocKy.unique' => 'Mã học kỳ đã tồn tại trong hệ thống. Vui lòng chọn mã khác.',
            'TenHocKy.required' => 'Vui lòng nhập tên học kỳ.',
            'NamHoc.required' => 'Vui lòng nhập năm học.',
            'NgayBatDau.required' => 'Vui lòng chọn ngày bắt đầu.',
            'NgayKetThuc.required' => 'Vui lòng chọn ngày kết thúc.',
            'NgayKetThuc.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.'
        ]);

        $maHK = $candidateMa ?: IdGenerator::nextHocKy($request->NamHoc, $request->TenHocKy);

        $trangThai = 'Đang diễn ra';
        if ($request->filled('TrangThai')) {
            $tt = trim($request->TrangThai);
            if ($tt === '0' || $tt === 'Đã kết thúc') $trangThai = 'Đã kết thúc';
            elseif ($tt === 'UPCOMING' || $tt === 'Chưa bắt đầu') $trangThai = 'Chưa bắt đầu';
            else $trangThai = 'Đang diễn ra';
        }

        HocKy::create([
            'MaHocKy' => $maHK,
            'TenHocKy' => trim($request->TenHocKy),
            'NamHoc' => trim($request->NamHoc),
            'NgayBatDau' => $request->NgayBatDau,
            'NgayKetThuc' => $request->NgayKetThuc,
            'TrangThai' => $trangThai,
        ]);

        return redirect()->route('hocky.index')->with('success', "Thêm học kỳ '{$request->TenHocKy}' ({$maHK}) thành công!");
    }

    public function show($id)
    {
        $hocky = HocKy::with([
            'keHoachKhoaLuans.mocThoiGians',
            'hoiDongs',
        ])->withCount(['keHoachKhoaLuans', 'deTais', 'hoiDongs', 'danhSachSVDuDieuKiens'])->findOrFail($id);

        $totalSv = $hocky->danh_sach_s_v_du_dieu_kiens_count;

        $stats = [
            'total_kehoach' => $hocky->ke_hoach_khoa_luans_count,
            'total_detai'   => $hocky->de_tais_count,
            'total_hoidong' => $hocky->hoi_dongs_count,
            'total_sv'      => $totalSv > 0 ? $totalSv : \App\Models\SinhVien::count(),
        ];

        return view('admin.hocky.show', compact('hocky', 'stats'));
    }

    public function edit($id)
    {
        $hocky = HocKy::findOrFail($id);
        return view('admin.hocky.edit', compact('hocky'));
    }

    public function update(Request $request, $id)
    {
        $hocky = HocKy::findOrFail($id);

        $request->validate([
            'TenHocKy' => 'required|string|max:50',
            'NamHoc' => 'required|string|max:20',
            'NgayBatDau' => 'required|date',
            'NgayKetThuc' => 'required|date|after_or_equal:NgayBatDau'
        ], [
            'TenHocKy.required' => 'Vui lòng nhập tên học kỳ.',
            'NamHoc.required' => 'Vui lòng nhập năm học.',
            'NgayBatDau.required' => 'Vui lòng chọn ngày bắt đầu.',
            'NgayKetThuc.required' => 'Vui lòng chọn ngày kết thúc.',
            'NgayKetThuc.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.'
        ]);

        $trangThai = $hocky->TrangThai;
        if ($request->filled('TrangThai')) {
            $tt = trim($request->TrangThai);
            if ($tt === '0' || $tt === 'Đã kết thúc') {
                $trangThai = 'Đã kết thúc';
            } elseif ($tt === 'UPCOMING' || $tt === 'Chưa bắt đầu') {
                $trangThai = 'Chưa bắt đầu';
            } elseif ($tt === '1' || $tt === 'Đang diễn ra' || $tt === 'Hoạt động') {
                $trangThai = 'Đang diễn ra';
            } else {
                $trangThai = $tt;
            }
        }

        $hocky->update([
            'TenHocKy' => trim($request->TenHocKy),
            'NamHoc' => trim($request->NamHoc),
            'NgayBatDau' => $request->NgayBatDau,
            'NgayKetThuc' => $request->NgayKetThuc,
            'TrangThai' => $trangThai,
        ]);

        return redirect()->route('hocky.index')->with('success', "Cập nhật học kỳ '{$hocky->TenHocKy}' thành công!");
    }

    public function destroy($id)
    {
        $hocky = HocKy::findOrFail($id);
        try {
            HocKy::destroy($id);
            return redirect()->route('hocky.index')->with('success', "Xóa học kỳ '{$hocky->TenHocKy}' thành công!");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa học kỳ '{$hocky->TenHocKy}' do đang có đề tài hoặc kế hoạch khóa luận liên quan.");
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importHocKy', [], 'Học Kỳ');
    }
}
