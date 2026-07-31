<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\HandlesExcelImport;
use App\Models\LopHocPhan;
use App\Models\SinhVienLopHocPhan;
use App\Models\MonHoc;
use App\Models\HocKy;
use App\Models\GiangVien;
use App\Models\SinhVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LopHocPhanController extends Controller
{
    use HandlesExcelImport;

    public function import(Request $request)
    {
        return $this->runImport($request, 'importLopHocPhan', [], 'Lớp Học Phần');
    }
    public function index(Request $request)
    {
        $query = LopHocPhan::with(['monHoc', 'hocKy', 'giangVien', 'sinhVienLopHocPhans']);

        if ($request->filled('ma_mon')) {
            $query->where('MaMon', $request->ma_mon);
        }

        if ($request->filled('ma_hoc_ky')) {
            $query->where('MaHocKy', $request->ma_hoc_ky);
        }

        if ($request->filled('ma_gv')) {
            $query->where('MaGV', $request->ma_gv);
        }

        $lopHocPhans = $query->orderBy('MaLopHP', 'desc')->paginate(15);
        $monHocs = MonHoc::all();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $giangViens = GiangVien::all();

        return view('admin.lophocphan.index', compact('lopHocPhans', 'monHocs', 'hocKies', 'giangViens'));
    }

    public function create()
    {
        $monHocs = MonHoc::all();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $giangViens = GiangVien::all();

        return view('admin.lophocphan.create', compact('monHocs', 'hocKies', 'giangViens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenLopHP' => 'required|string|max:100|unique:lop_hoc_phans,TenLopHP',
            'MaMon' => 'required|exists:mon_hocs,MaMon',
            'MaHocKy' => 'required|exists:hoc_kies,MaHocKy',
            'MaGV' => 'required|exists:giang_viens,MaGV',
            'SiSoToiDa' => 'required|integer|min:1|max:200',
            'TrangThai' => 'required|in:Đang mở,Đã đóng',
        ], [
            'TenLopHP.required' => 'Vui lòng nhập tên lớp học phần.',
            'TenLopHP.unique' => 'Tên lớp học phần này đã tồn tại.',
            'MaMon.required' => 'Vui lòng chọn môn học.',
            'MaHocKy.required' => 'Vui lòng chọn học kỳ.',
            'MaGV.required' => 'Vui lòng chọn giảng viên phụ trách.',
            'SiSoToiDa.required' => 'Vui lòng nhập sĩ số tối đa.',
        ]);

        LopHocPhan::create($request->all());

        return redirect()->route('admin.lophocphan.index')
            ->with('success', 'Tạo Lớp Học Phần thành công!');
    }

    public function show($id)
    {
        $lopHocPhan = LopHocPhan::with(['monHoc', 'hocKy', 'giangVien', 'sinhVienLopHocPhans.sinhVien.lop'])
            ->findOrFail($id);

        // All students enrolled in this section
        $enrolledSvIds = $lopHocPhan->sinhVienLopHocPhans->pluck('MaSV')->toArray();

        // Query available students not yet in this section
        $availableStudents = SinhVien::with('lop')
            ->whereNotIn('MaSV', $enrolledSvIds)
            ->orderBy('HoTen')
            ->get();

        return view('admin.lophocphan.show', compact('lopHocPhan', 'availableStudents'));
    }

    public function edit($id)
    {
        $lopHocPhan = LopHocPhan::findOrFail($id);
        $monHocs = MonHoc::all();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        $giangViens = GiangVien::all();

        return view('admin.lophocphan.edit', compact('lopHocPhan', 'monHocs', 'hocKies', 'giangViens'));
    }

    public function update(Request $request, $id)
    {
        $lopHocPhan = LopHocPhan::findOrFail($id);

        $request->validate([
            'TenLopHP' => 'required|string|max:100|unique:lop_hoc_phans,TenLopHP,' . $id . ',MaLopHP',
            'MaMon' => 'required|exists:mon_hocs,MaMon',
            'MaHocKy' => 'required|exists:hoc_kies,MaHocKy',
            'MaGV' => 'required|exists:giang_viens,MaGV',
            'SiSoToiDa' => 'required|integer|min:1|max:200',
            'TrangThai' => 'required|in:Đang mở,Đã đóng',
        ]);

        $lopHocPhan->update($request->all());

        return redirect()->route('admin.lophocphan.index')
            ->with('success', 'Cập nhật Lớp Học Phần thành công!');
    }

    public function destroy($id)
    {
        $lopHocPhan = LopHocPhan::findOrFail($id);
        $lopHocPhan->delete();

        return redirect()->route('admin.lophocphan.index')
            ->with('success', 'Đã xóa Lớp Học Phần thành công!');
    }

    public function addStudent(Request $request, $id)
    {
        $lopHocPhan = LopHocPhan::findOrFail($id);

        $request->validate([
            'MaSV' => 'required|exists:sinh_viens,MaSV',
        ], [
            'MaSV.required' => 'Vui lòng chọn sinh viên.',
        ]);

        $maSV = $request->MaSV;

        // Check if student is already in ANY class section for this Subject & Semester
        $existing = SinhVienLopHocPhan::with('lopHocPhan')
            ->where('MaSV', $maSV)
            ->where('MaMon', $lopHocPhan->MaMon)
            ->where('MaHocKy', $lopHocPhan->MaHocKy)
            ->first();

        if ($existing) {
            $tenLopCu = $existing->lopHocPhan->TenLopHP ?? 'Khác';
            return back()->with('error', "Sinh viên này đã thuộc Lớp Học Phần '{$tenLopCu}' của môn này trong cùng học kỳ!");
        }

        // Check class capacity limit
        $currentCount = SinhVienLopHocPhan::where('MaLopHP', $lopHocPhan->MaLopHP)->count();
        if ($currentCount >= $lopHocPhan->SiSoToiDa) {
            return back()->with('error', "Lớp Học Phần đã đủ sĩ số tối đa ({$lopHocPhan->SiSoToiDa} sinh viên)!");
        }

        // Add student
        SinhVienLopHocPhan::create([
            'MaSV' => $maSV,
            'MaLopHP' => $lopHocPhan->MaLopHP,
            'MaMon' => $lopHocPhan->MaMon,
            'MaHocKy' => $lopHocPhan->MaHocKy,
            'NgayDangKy' => now(),
        ]);

        return back()->with('success', 'Thêm sinh viên vào Lớp Học Phần thành công!');
    }

    public function removeStudent($id, $maSV)
    {
        $lopHocPhan = LopHocPhan::findOrFail($id);

        SinhVienLopHocPhan::where('MaLopHP', $lopHocPhan->MaLopHP)
            ->where('MaSV', $maSV)
            ->delete();

        return back()->with('success', 'Đã xóa sinh viên khỏi Lớp Học Phần!');
    }

    public function importStudents(Request $request, $id)
    {
        $lopHocPhan = LopHocPhan::findOrFail($id);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ], [
            'file.required' => 'Vui lòng chọn tệp Excel để import.',
            'file.mimes' => 'Tệp phải có định dạng .xlsx, .xls hoặc .csv.',
        ]);

        try {
            $importService = new \App\Services\ExcelImportService();
            $result = $importService->importSinhVienLopHocPhan($request->file('file'), $lopHocPhan->MaLopHP);

            $msg = "Đã import thành công {$result['success_count']} sinh viên vào Lớp Học Phần!";
            if (!empty($result['errors'])) {
                $errMsgs = array_map(fn($e) => $e['reason'] ?? '', $result['errors']);
                return redirect()->back()->with('success', $msg)->withErrors($errMsgs);
            }
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Lỗi import: ' . $e->getMessage());
        }
    }
}
