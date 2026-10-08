<?php

namespace App\Http\Controllers;

use App\Helpers\IdGenerator;
use App\Models\HocPhan;
use App\Models\Khoa;
use App\Models\BoMon;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class HocPhanController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'bomon'); // 'bomon' hoặc 'dung_chung'

        // 1. Query cho Tab Học phần theo bộ môn
        $queryBoMon = HocPhan::with(['khoa', 'boMon', 'hocPhanHocKies.hocKy'])
            ->withCount(['deTais', 'nhoms'])
            ->whereNotNull('MaBoMon');

        // 2. Query cho Tab Học phần dùng chung (không thuộc riêng bất kỳ bộ môn nào)
        $queryDungChung = HocPhan::with(['khoa', 'hocPhanHocKies.hocKy'])
            ->withCount(['deTais', 'nhoms'])
            ->whereNull('MaBoMon');

        // Áp dụng bộ lọc cho Tab Bộ môn
        if ($activeTab === 'bomon') {
            if ($request->filled('search')) {
                $s = trim($request->search);
                $queryBoMon->where(function ($q) use ($s) {
                    $q->where('MaHocPhan', 'like', "%{$s}%")
                      ->orWhere('TenHocPhan', 'like', "%{$s}%");
                });
            }
            if ($request->filled('ma_bomon')) {
                $queryBoMon->where('MaBoMon', $request->ma_bomon);
            }
            if ($request->filled('trang_thai')) {
                $queryBoMon->where('TrangThai', $request->trang_thai);
            }
        }

        // Áp dụng bộ lọc cho Tab Dùng chung
        if ($activeTab === 'dung_chung') {
            if ($request->filled('search')) {
                $s = trim($request->search);
                $queryDungChung->where(function ($q) use ($s) {
                    $q->where('MaHocPhan', 'like', "%{$s}%")
                      ->orWhere('TenHocPhan', 'like', "%{$s}%");
                });
            }
            if ($request->filled('trang_thai')) {
                $queryDungChung->where('TrangThai', $request->trang_thai);
            }
        }

        $hocPhanBoMons = $queryBoMon->orderBy('MaBoMon')->orderBy('TenHocPhan')->paginate(10, ['*'], 'bm_page')->withQueryString();
        $hocPhanDungChungs = $queryDungChung->orderBy('TenHocPhan')->paginate(10, ['*'], 'dc_page')->withQueryString();

        $bomons = BoMon::orderBy('TenBoMon')->get();
        $khoas = Khoa::orderBy('TenKhoa')->get();

        // Thống kê 4 thẻ
        $stats = [
            'total'       => HocPhan::count(),
            'theo_bomon'  => HocPhan::whereNotNull('MaBoMon')->count(),
            'dung_chung'  => HocPhan::whereNull('MaBoMon')->count(),
            'so_bomon'    => BoMon::count(),
        ];

        $suggestedMaHocPhan = IdGenerator::nextHocPhan();

        return view('admin.hocphan.index', compact(
            'hocPhanBoMons',
            'hocPhanDungChungs',
            'bomons',
            'khoas',
            'stats',
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

        $isDungChung = $request->boolean('is_dung_chung') || empty($request->MaBoMon);

        $request->validate([
            'MaHocPhan'   => 'nullable|string|max:20|unique:HocPhan,MaHocPhan',
            'TenHocPhan'  => 'required|string|max:150|unique:HocPhan,TenHocPhan',
            'SoTinChi'    => 'required|integer|min:1|max:30',
            'SoTietLT'    => 'nullable|integer|min:0',
            'SoTietTH'    => 'nullable|integer|min:0',
            'SoTietKhac'  => 'nullable|integer|min:0',
            'MaKhoa'      => 'nullable|exists:Khoa,MaKhoa',
            'MaBoMon'     => $isDungChung ? 'nullable' : 'required|exists:BoMon,MaBoMon',
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
            'MaBoMon'     => $isDungChung ? null : $request->MaBoMon,
            'LoaiHocPhan' => $isDungChung ? ($request->LoaiHocPhan ?: 'Khóa luận') : ($request->LoaiHocPhan ?: 'Chuyên ngành'),
            'TrangThai'   => $request->TrangThai,
            'MoTa'        => $request->MoTa,
        ]);

        $targetTab = $isDungChung ? 'dung_chung' : 'bomon';
        return redirect()->route('hocphan.index', ['tab' => $targetTab])
            ->with('success', "Thêm học phần '{$hocphan->TenHocPhan}' thành công!");
    }

    public function show($id)
    {
        $hocphan = HocPhan::with(['khoa', 'boMon', 'deTais.giangVien', 'nhoms.truongNhom'])
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

        $isDungChung = $request->boolean('is_dung_chung') || $hocphan->isDungChung();

        $rules = [
            'TenHocPhan'  => 'required|string|max:150|unique:HocPhan,TenHocPhan,' . $id . ',MaHocPhan',
            'SoTinChi'    => 'required|integer|min:1|max:30',
            'SoTietLT'    => 'nullable|integer|min:0',
            'SoTietTH'    => 'nullable|integer|min:0',
            'SoTietKhac'  => 'nullable|integer|min:0',
            'TrangThai'   => 'required|string|max:50',
            'MoTa'        => 'nullable|string',
        ];

        if (!$isDungChung) {
            $rules['MaBoMon'] = 'required|exists:BoMon,MaBoMon';
        }

        $request->validate($rules, [
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
            'TrangThai'   => $request->TrangThai,
            'MoTa'        => $request->MoTa,
        ];

        if ($request->filled('LoaiHocPhan')) {
            $updateData['LoaiHocPhan'] = $request->LoaiHocPhan;
        }

        if ($isDungChung) {
            $updateData['MaBoMon'] = null; // Luôn đảm bảo không thuộc bộ môn nào
        } else {
            $updateData['MaBoMon'] = $request->MaBoMon;
        }

        $hocphan->update($updateData);

        $targetTab = $hocphan->isDungChung() ? 'dung_chung' : 'bomon';
        return redirect()->route('hocphan.index', ['tab' => $targetTab])
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
            $targetTab = $hocphan->isDungChung() ? 'dung_chung' : 'bomon';
            $hocphan->delete();
            return redirect()->route('hocphan.index', ['tab' => $targetTab])
                ->with('success', "Xóa học phần '{$tenHp}' thành công!");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa học phần '{$hocphan->TenHocPhan}': " . $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importHocPhan', [], 'Học Phần');
    }
}
