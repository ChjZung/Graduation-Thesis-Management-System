<?php
namespace App\Http\Controllers;

use App\Http\Traits\HandlesExcelImport;
use App\Models\GiangVien;
use App\Models\BoMon;
use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class GiangVienController extends Controller
{
    use HandlesExcelImport;
    public function index() {
        $giangviens = GiangVien::with('boMon', 'taiKhoan')->paginate(10);
        return view('admin.giangvien.index', compact('giangviens'));
    }
    public function create() {
        $bomons = BoMon::all();
        return view('admin.giangvien.create', compact('bomons'));
    }
    public function store(Request $request) {
        $request->validate([
            'TenDangNhap' => 'required|string|max:50|unique:tai_khoans,TenDangNhap',
            'HoTen' => 'required|string|max:100',
            'HocVi' => 'required|string|max:50',
            'MaBoMon' => 'required|exists:bo_mons,MaBoMon',
            'Email' => 'required|email|max:100|unique:giang_viens,Email',
            'SoDienThoai' => ['required', 'string', 'regex:/^0[0-9]{8,10}$/', 'unique:giang_viens,SoDienThoai']
        ], [
            'TenDangNhap.required' => 'Vui lòng nhập tên đăng nhập.',
            'TenDangNhap.unique' => 'Tên đăng nhập đã tồn tại.',
            'HoTen.required' => 'Vui lòng nhập họ tên.',
            'HocVi.required' => 'Vui lòng nhập học vị.',
            'MaBoMon.required' => 'Vui lòng chọn bộ môn.',
            'Email.required' => 'Vui lòng nhập email.',
            'Email.email' => 'Định dạng email không hợp lệ.',
            'Email.unique' => 'Email đã được sử dụng.',
            'SoDienThoai.required' => 'Vui lòng nhập số điện thoại.',
            'SoDienThoai.unique' => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có từ 9-11 chữ số hợp lệ.',
        ]);

        DB::transaction(function () use ($request) {
            $tk = TaiKhoan::create([
                'TenDangNhap' => $request->TenDangNhap,
                'MatKhau' => Hash::make('123456'),
                'MaVaiTro' => 2,
                'TrangThai' => true
            ]);
            GiangVien::create([
                'MaTK' => $tk->MaTK,
                'MaBoMon' => $request->MaBoMon,
                'HoTen' => $request->HoTen,
                'Email' => $request->Email,
                'SoDienThoai' => $request->SoDienThoai,
                'HocVi' => $request->HocVi
            ]);
        });
        return redirect()->route('giangvien.index')->with('success', 'Thêm giảng viên thành công!');
    }
    public function edit($id) {
        $giangvien = GiangVien::findOrFail($id);
        $bomons = BoMon::all();
        return view('admin.giangvien.edit', compact('giangvien', 'bomons'));
    }
    public function update(Request $request, $id) {
        $giangvien = GiangVien::findOrFail($id);
        
        $request->validate([
            'TenDangNhap' => 'nullable|string|max:50|unique:tai_khoans,TenDangNhap,' . $giangvien->MaTK . ',MaTK',
            'HoTen' => 'required|string|max:100',
            'HocVi' => 'required|string|max:50',
            'MaBoMon' => 'required|exists:bo_mons,MaBoMon',
            'Email' => 'required|email|max:100|unique:giang_viens,Email,' . $id . ',MaGV',
            'SoDienThoai' => ['required', 'string', 'regex:/^0[0-9]{8,10}$/', 'unique:giang_viens,SoDienThoai,' . $id . ',MaGV']
        ], [
            'TenDangNhap.unique' => 'Tên đăng nhập đã tồn tại.',
            'HoTen.required' => 'Vui lòng nhập họ tên.',
            'HocVi.required' => 'Vui lòng nhập học vị.',
            'MaBoMon.required' => 'Vui lòng chọn bộ môn.',
            'Email.required' => 'Vui lòng nhập email.',
            'Email.email' => 'Định dạng email không hợp lệ.',
            'Email.unique' => 'Email đã được sử dụng.',
            'SoDienThoai.required' => 'Vui lòng nhập số điện thoại.',
            'SoDienThoai.unique' => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có từ 9-11 chữ số hợp lệ.',
        ]);

        $giangvien->update($request->only(['MaBoMon', 'HoTen', 'Email', 'SoDienThoai', 'HocVi']));
        if ($request->filled('TenDangNhap')) {
            $giangvien->taiKhoan->update(['TenDangNhap' => $request->TenDangNhap]);
        }
        return redirect()->route('giangvien.index')->with('success', 'Cập nhật thành công!');
    }
    public function destroy($id) {
        $giangvien = GiangVien::findOrFail($id);
        $maTK = $giangvien->MaTK;

        try {
            DB::transaction(function () use ($giangvien, $maTK) {
                \App\Models\PhanCongHuongDanLop::where('MaGV', $giangvien->MaGV)->delete();
                $giangvien->delete();
                if ($maTK) {
                    TaiKhoan::destroy($maTK);
                }
            });
            return redirect()->route('giangvien.index')->with('success', 'Xóa giảng viên thành công!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Xóa Giảng viên lỗi: ' . $e->getMessage());
            return redirect()->back()->withErrors("Không thể xóa giảng viên '{$giangvien->HoTen}' do đang vướng ràng buộc đề tài hoặc dữ liệu liên quan.");
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importGiangVien', [], 'Giảng viên');
    }
}