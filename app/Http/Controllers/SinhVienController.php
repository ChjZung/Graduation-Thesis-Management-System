<?php
namespace App\Http\Controllers;
use App\Models\SinhVien;
use App\Models\Lop;
use App\Models\TaiKhoan;
use App\Http\Traits\HandlesExcelImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SinhVienController extends Controller
{
    use HandlesExcelImport;
    public function index() {
        $sinhviens = SinhVien::with('lop', 'taiKhoan')->paginate(10);
        return view('admin.sinhvien.index', compact('sinhviens'));
    }
    public function create() {
        $lops = Lop::all();
        return view('admin.sinhvien.create', compact('lops'));
    }
    public function store(Request $request) {
        $request->validate([
            'TenDangNhap' => 'required|string|max:50|unique:tai_khoans,TenDangNhap',
            'HoTen' => 'required|string|max:100',
            'MaLop' => 'required|exists:lops,MaLop',
            'Email' => 'required|email|max:100|unique:sinh_viens,Email',
            'SoDienThoai' => ['required', 'string', 'regex:/^0[0-9]{8,10}$/', 'unique:sinh_viens,SoDienThoai']
        ], [
            'TenDangNhap.required' => 'Vui lòng nhập tên đăng nhập.',
            'TenDangNhap.unique' => 'Tên đăng nhập đã tồn tại.',
            'HoTen.required' => 'Vui lòng nhập họ tên.',
            'MaLop.required' => 'Vui lòng chọn lớp.',
            'Email.required' => 'Vui lòng nhập email.',
            'Email.email' => 'Định dạng email không hợp lệ.',
            'Email.unique' => 'Email đã được sử dụng.',
            'SoDienThoai.required' => 'Vui lòng nhập số điện thoại.',
            'SoDienThoai.unique' => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có từ 9-11 chữ số hợp lệ.',
        ]);

        DB::transaction(function () use ($request) {
            $maTK = 'TK_SV_' . \Illuminate\Support\Str::random(6);
            $tk = TaiKhoan::create([
                'MaTK' => $maTK,
                'TenDangNhap' => $request->TenDangNhap,
                'MatKhau' => Hash::make('123456'),
                'MaVaiTro' => 'VT03',
                'TrangThai' => true
            ]);
            SinhVien::create([
                'MaTK' => $tk->MaTK,
                'MaLop' => $request->MaLop,
                'HoTen' => $request->HoTen,
                'Email' => $request->Email,
                'SoDienThoai' => $request->SoDienThoai
            ]);
        });
        return redirect()->route('sinhvien.index')->with('success', 'Thêm sinh viên thành công!');
    }
    public function edit($id) {
        $sinhvien = SinhVien::findOrFail($id);
        $lops = Lop::all();
        return view('admin.sinhvien.edit', compact('sinhvien', 'lops'));
    }
    public function update(Request $request, $id) {
        $sinhvien = SinhVien::findOrFail($id);
        
        $request->validate([
            'TenDangNhap' => 'nullable|string|max:50|unique:tai_khoans,TenDangNhap,' . $sinhvien->MaTK . ',MaTK',
            'HoTen' => 'required|string|max:100',
            'MaLop' => 'required|exists:lops,MaLop',
            'Email' => 'required|email|max:100|unique:sinh_viens,Email,' . $id . ',MaSV',
            'SoDienThoai' => ['required', 'string', 'regex:/^0[0-9]{8,10}$/', 'unique:sinh_viens,SoDienThoai,' . $id . ',MaSV']
        ], [
            'TenDangNhap.unique' => 'Tên đăng nhập đã tồn tại.',
            'HoTen.required' => 'Vui lòng nhập họ tên.',
            'MaLop.required' => 'Vui lòng chọn lớp.',
            'Email.required' => 'Vui lòng nhập email.',
            'Email.email' => 'Định dạng email không hợp lệ.',
            'Email.unique' => 'Email đã được sử dụng.',
            'SoDienThoai.required' => 'Vui lòng nhập số điện thoại.',
            'SoDienThoai.unique' => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có từ 9-11 chữ số hợp lệ.',
        ]);

        $sinhvien->update($request->only(['MaLop', 'HoTen', 'Email', 'SoDienThoai']));
        if ($request->filled('TenDangNhap')) {
            $sinhvien->taiKhoan->update(['TenDangNhap' => $request->TenDangNhap]);
        }
        return redirect()->route('sinhvien.index')->with('success', 'Cập nhật thành công!');
    }
    public function destroy($id) {
        $sinhvien = SinhVien::findOrFail($id);
        $maTK = $sinhvien->MaTK;

        try {
            DB::transaction(function () use ($sinhvien, $maTK) {
                // Xóa thành viên nhóm
                \App\Models\ThanhVienNhom::where('MaSV', $sinhvien->MaSV)->delete();
                $sinhvien->delete();
                if ($maTK) {
                    TaiKhoan::destroy($maTK);
                }
            });
            return redirect()->route('sinhvien.index')->with('success', 'Xóa sinh viên thành công!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Xóa Sinh viên lỗi: ' . $e->getMessage());
            return redirect()->back()->withErrors("Không thể xóa sinh viên '{$sinhvien->HoTen}' do đang vướng ràng buộc nhóm đồ án hoặc dữ liệu liên quan.");
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importSinhVien', [], 'Sinh Viên');
    }
}