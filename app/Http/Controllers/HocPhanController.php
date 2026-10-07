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
        $query = HocPhan::with(['khoa', 'boMon'])->withCount(['deTais', 'nhoms']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('MaHocPhan', 'like', "%{$s}%")
                  ->orWhere('TenHocPhan', 'like', "%{$s}%");
            });
        }

        if ($request->filled('ma_khoa')) {
            $query->where('MaKhoa', $request->ma_khoa);
        }

        if ($request->filled('ma_bomon')) {
            if ($request->ma_bomon === 'DUNG_CHUNG') {
                $query->whereNull('MaBoMon');
            } else {
                $query->where('MaBoMon', $request->ma_bomon);
            }
        }

        if ($request->filled('loai_hoc_phan')) {
            $query->where('LoaiHocPhan', $request->loai_hoc_phan);
        }

        $hocphans = $query->orderBy('MaKhoa')->orderBy('MaBoMon')->orderBy('TenHocPhan')->paginate(10)->withQueryString();
        $khoas = Khoa::orderBy('TenKhoa')->get();
        $bomons = BoMon::orderBy('TenBoMon')->get();

        $totalHocPhan = HocPhan::count();
        $cnttKhoa = Khoa::where('TenKhoa', 'like', '%Công nghệ thông tin%')->orWhere('MaKhoa', 'like', '%CNTT%')->first();
        $hpCntt = $cnttKhoa ? HocPhan::where('MaKhoa', $cnttKhoa->MaKhoa)->count() : 0;
        $hpDungChung = HocPhan::whereNull('MaBoMon')->count();
        $hpChuyenNganh = HocPhan::whereNotNull('MaBoMon')->count();

        $stats = [
            'total'       => $totalHocPhan,
            'cntt'        => $hpCntt,
            'dung_chung'  => $hpDungChung,
            'chuyen_nganh'=> $hpChuyenNganh,
        ];

        return view('admin.hocphan.index', compact('hocphans', 'khoas', 'bomons', 'stats'));
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
            'MaKhoa'      => 'nullable|exists:Khoa,MaKhoa',
            'MaBoMon'     => 'nullable|exists:BoMon,MaBoMon',
            'LoaiHocPhan' => 'required|string|max:50',
            'TrangThai'   => 'required|string|max:50',
        ], [
            'MaHocPhan.unique'     => 'Mã học phần đã tồn tại trong hệ thống.',
            'TenHocPhan.required'  => 'Vui lòng nhập tên học phần.',
            'TenHocPhan.unique'    => 'Tên học phần này đã tồn tại trong hệ thống.',
            'SoTinChi.required'    => 'Vui lòng nhập số tín chỉ.',
            'SoTinChi.min'         => 'Số tín chỉ tối thiểu là 1.',
        ]);

        $maHocPhan = $request->filled('MaHocPhan') ? $request->MaHocPhan : IdGenerator::nextHocPhan();

        HocPhan::create([
            'MaHocPhan'   => $maHocPhan,
            'TenHocPhan'  => $request->TenHocPhan,
            'SoTinChi'    => $request->SoTinChi,
            'MaKhoa'      => $request->MaKhoa ?: 'CNTT',
            'MaBoMon'     => $request->MaBoMon ?: null,
            'LoaiHocPhan' => $request->LoaiHocPhan,
            'TrangThai'   => $request->TrangThai,
            'MoTa'        => $request->MoTa,
        ]);

        return redirect()->route('hocphan.index')->with('success', "Thêm học phần '{$request->TenHocPhan}' thành công!");
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

        $request->validate([
            'TenHocPhan'  => 'required|string|max:150|unique:HocPhan,TenHocPhan,' . $id . ',MaHocPhan',
            'SoTinChi'    => 'required|integer|min:1|max:30',
            'MaKhoa'      => 'nullable|exists:Khoa,MaKhoa',
            'MaBoMon'     => 'nullable|exists:BoMon,MaBoMon',
            'LoaiHocPhan' => 'required|string|max:50',
            'TrangThai'   => 'required|string|max:50',
        ], [
            'TenHocPhan.required' => 'Vui lòng nhập tên học phần.',
            'TenHocPhan.unique'   => 'Tên học phần này đã tồn tại trong hệ thống.',
        ]);

        $hocphan->update([
            'TenHocPhan'  => $request->TenHocPhan,
            'SoTinChi'    => $request->SoTinChi,
            'MaKhoa'      => $request->MaKhoa ?: null,
            'MaBoMon'     => $request->MaBoMon ?: null,
            'LoaiHocPhan' => $request->LoaiHocPhan,
            'TrangThai'   => $request->TrangThai,
            'MoTa'        => $request->MoTa,
        ]);

        return redirect()->route('hocphan.index')->with('success', 'Cập nhật học phần thành công!');
    }

    public function destroy($id)
    {
        $hocphan = HocPhan::withCount(['deTais', 'nhoms'])->findOrFail($id);

        if ($hocphan->de_tais_count > 0 || $hocphan->nhoms_count > 0) {
            return redirect()->back()->withErrors("Không thể xóa học phần '{$hocphan->TenHocPhan}' do đang có {$hocphan->de_tais_count} đề tài và {$hocphan->nhoms_count} nhóm khóa luận liên kết.");
        }

        try {
            $hocphan->delete();
            return redirect()->route('hocphan.index')->with('success', "Xóa học phần '{$hocphan->TenHocPhan}' thành công!");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa học phần '{$hocphan->TenHocPhan}': " . $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importHocPhan', [], 'Học Phần');
    }
}
