<?php
namespace App\Http\Controllers;

use App\Models\PhanCongHuongDanLop;
use App\Models\LopHocPhan;
use App\Models\GiangVien;
use App\Models\Lop;
use App\Models\HocKy;
use App\Models\AuditLog;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class PhanCongController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request) {
        $phancongs = PhanCongHuongDanLop::with(['giangVien.boMon', 'lop', 'hocKy'])->paginate(10, ['*'], 'page_hc');
        $lophocphans = LopHocPhan::with(['giangVien.boMon', 'monHoc', 'hocKy', 'sinhVienLopHocPhans'])->orderBy('MaLopHP', 'desc')->paginate(10, ['*'], 'page_hp');
        
        $giangviens = GiangVien::with('boMon')->get();
        $lops = Lop::all();
        $hockys = HocKy::orderBy('MaHocKy', 'desc')->get();

        return view('admin.phancong.index', compact('phancongs', 'lophocphans', 'giangviens', 'lops', 'hockys'));
    }

    public function store(Request $request) {
        $loai = $request->input('LoaiPhanCong', 'lop_hanh_chinh');

        if ($loai === 'lop_hoc_phan') {
            $request->validate([
                'MaGV' => 'required|exists:giang_viens,MaGV',
                'MaLopHP' => 'required|exists:lop_hoc_phans,MaLopHP',
            ], [
                'MaGV.required' => 'Vui lòng chọn Giảng viên.',
                'MaLopHP.required' => 'Vui lòng chọn Lớp Học Phần.',
            ]);

            $lhp = LopHocPhan::with('giangVien')->findOrFail($request->MaLopHP);

            if ($lhp->MaGV && $lhp->MaGV == $request->MaGV) {
                return redirect()->to(route('phancong.index') . '?tab=hp')->withErrors("Giảng viên " . ($lhp->giangVien->HoTen ?? 'này') . " đã được phân công phụ trách Lớp Học Phần {$lhp->TenLopHP} từ trước rồi!");
            }

            $lhp->update(['MaGV' => $request->MaGV]);

            AuditLog::log('phan_cong_lhp', 'LopHocPhan', $lhp->MaLopHP, [
                'MaGV' => $request->MaGV,
                'MaLopHP' => $lhp->MaLopHP
            ]);

            return redirect()->to(route('phancong.index') . '?tab=hp')->with('success', "Cập nhật phân công Giảng viên phụ trách Lớp Học Phần {$lhp->TenLopHP} thành công!");
        }

        // Lớp Hành Chính
        $request->validate([
            'MaGV' => 'required|exists:giang_viens,MaGV',
            'MaLop' => 'required|exists:lops,MaLop',
            'MaHocKy' => 'required|exists:hoc_kies,MaHocKy'
        ], [
            'MaGV.required' => 'Vui lòng chọn Giảng viên.',
            'MaLop.required' => 'Vui lòng chọn Lớp Hành chính.',
            'MaHocKy.required' => 'Vui lòng chọn Học kỳ.',
        ]);

        // Check if class is already assigned to any lecturer in this semester
        $existing = PhanCongHuongDanLop::with('giangVien')
            ->where('MaLop', $request->MaLop)
            ->where('MaHocKy', $request->MaHocKy)
            ->first();

        if ($existing) {
            if ($existing->MaGV == $request->MaGV) {
                return redirect()->to(route('phancong.index') . '?tab=hc')->withErrors("Giảng viên " . ($existing->giangVien->HoTen ?? 'này') . " đã được phân công hướng dẫn Lớp này trong học kỳ này rồi!");
            }

            $existing->update(['MaGV' => $request->MaGV, 'NgayPhanCong' => date('Y-m-d')]);

            AuditLog::log('cap_nhat_phan_cong_gv', 'PhanCongHuongDanLop', $existing->MaPhanCong, [
                'MaGV' => $request->MaGV,
                'MaLop' => $request->MaLop,
                'MaHocKy' => $request->MaHocKy
            ]);

            return redirect()->to(route('phancong.index') . '?tab=hc')->with('success', 'Cập nhật phân công Giảng viên hướng dẫn Lớp Hành chính thành công!');
        }

        $pc = PhanCongHuongDanLop::create([
            'MaGV' => $request->MaGV,
            'MaLop' => $request->MaLop,
            'MaHocKy' => $request->MaHocKy,
            'NgayPhanCong' => date('Y-m-d')
        ]);

        AuditLog::log('phan_cong_gv', 'PhanCongHuongDanLop', $pc->MaPhanCong, [
            'MaGV' => $request->MaGV,
            'MaLop' => $request->MaLop,
            'MaHocKy' => $request->MaHocKy
        ]);

        return redirect()->to(route('phancong.index') . '?tab=hc')->with('success', 'Phân công hướng dẫn Lớp Hành chính thành công!');
    }

    public function unassignLhp($id) {
        $lhp = LopHocPhan::findOrFail($id);
        $lhp->update(['MaGV' => null]);
        AuditLog::log('huy_phan_cong_lhp', 'LopHocPhan', $id, []);
        return redirect()->to(route('phancong.index') . '?tab=hp')->with('success', 'Hủy phân công Giảng viên cho Lớp Học Phần thành công!');
    }

    public function destroy($id) {
        $pc = PhanCongHuongDanLop::findOrFail($id);
        AuditLog::log('xoa_phan_cong', 'PhanCongHuongDanLop', $id, ['MaGV' => $pc->MaGV, 'MaLop' => $pc->MaLop]);
        $pc->delete();
        return redirect()->back()->with('success', 'Xóa phân công thành công!');
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importPhanCong', [], 'Phân Công Hướng Dẫn');
    }
}
