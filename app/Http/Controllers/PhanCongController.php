<?php
namespace App\Http\Controllers;

use App\Models\PhanCongHuongDanLop;
use App\Models\GiangVien;
use App\Models\Lop;
use App\Models\HocKy;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;

class PhanCongController extends Controller
{
    use HandlesExcelImport;
    public function index() {
        $phancongs = PhanCongHuongDanLop::with(['giangVien.boMon', 'lop', 'hocKy'])->paginate(10);
        $giangviens = GiangVien::with('boMon')->get();
        $lops = Lop::all();
        $hockys = HocKy::all();
        return view('admin.phancong.index', compact('phancongs', 'giangviens', 'lops', 'hockys'));
    }

    public function store(Request $request) {
        $request->validate([
            'MaGV' => 'required|exists:giang_viens,MaGV',
            'MaLop' => 'required|exists:lops,MaLop',
            'MaHocKy' => 'required|exists:hoc_kies,MaHocKy'
        ]);

        // Check if already assigned
        $exists = PhanCongHuongDanLop::where('MaGV', $request->MaGV)
                                     ->where('MaLop', $request->MaLop)
                                     ->where('MaHocKy', $request->MaHocKy)
                                     ->exists();
        if ($exists) {
            return redirect()->back()->withErrors('Giảng viên này đã được phân công hướng dẫn lớp này trong học kỳ này rồi!');
        }

        $pc = PhanCongHuongDanLop::create([
            'MaGV' => $request->MaGV,
            'MaLop' => $request->MaLop,
            'MaHocKy' => $request->MaHocKy,
            'NgayPhanCong' => date('Y-m-d')
        ]);

        \App\Models\AuditLog::log('phan_cong_gv', 'PhanCongHuongDanLop', $pc->MaPhanCong, [
            'MaGV' => $request->MaGV,
            'MaLop' => $request->MaLop,
            'MaHocKy' => $request->MaHocKy
        ]);

        return redirect()->back()->with('success', 'Phân công thành công!');
    }

    public function destroy($id) {
        $pc = PhanCongHuongDanLop::findOrFail($id);
        \App\Models\AuditLog::log('xoa_phan_cong', 'PhanCongHuongDanLop', $id, ['MaGV' => $pc->MaGV, 'MaLop' => $pc->MaLop]);
        $pc->delete();
        return redirect()->back()->with('success', 'Xóa phân công thành công!');
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importPhanCong', [], 'Phân Công Hướng Dẫn');
    }
}
