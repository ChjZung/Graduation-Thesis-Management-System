<?php

namespace App\Http\Controllers;

use App\Helpers\IdGenerator;
use App\Http\Traits\HandlesExcelImport;
use App\Models\SinhVien;
use App\Models\Lop;
use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SinhVienController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request)
    {
        $query = SinhVien::with(['lop.nganh.khoa', 'taiKhoan']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('HoTen', 'LIKE', "%{$s}%")
                  ->orWhere('Email', 'LIKE', "%{$s}%")
                  ->orWhere('MaSV', 'LIKE', "%{$s}%")
                  ->orWhere('MaSoSinhVien', 'LIKE', "%{$s}%")
                  ->orWhereHas('taiKhoan', fn($t) => $t->where('TenDangNhap', 'LIKE', "%{$s}%"));
            });
        }

        if ($request->filled('MaLop')) {
            $query->where('MaLop', $request->MaLop);
        }

        $sinhviens = $query->orderBy('MaSV')->paginate(10);
        $lops = Lop::with('nganh')->orderBy('TenLop')->get();

        return view('admin.sinhvien.index', compact('sinhviens', 'lops'));
    }

    public function create()
    {
        $lops = Lop::with('nganh.khoa')->orderBy('TenLop')->get();
        return view('admin.sinhvien.create', compact('lops'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenDangNhap' => 'required|string|max:50|unique:tai_khoans,TenDangNhap',
            'HoTen' => 'required|string|max:100',
            'MaLop' => 'required|exists:lops,MaLop',
            'Email' => 'required|email|max:100|unique:sinh_viens,Email',
            'SoDienThoai' => ['nullable', 'string', 'regex:/^0[0-9]{8,10}$/', 'unique:sinh_viens,SoDienThoai']
        ], [
            'TenDangNhap.required' => 'Vui lòng nhập tên đăng nhập (MSSV).',
            'TenDangNhap.unique' => 'Tên đăng nhập / MSSV đã tồn tại.',
            'HoTen.required' => 'Vui lòng nhập họ tên sinh viên.',
            'MaLop.required' => 'Vui lòng chọn lớp học.',
            'Email.required' => 'Vui lòng nhập email.',
            'Email.email' => 'Định dạng email không hợp lệ.',
            'Email.unique' => 'Email đã được sử dụng.',
            'SoDienThoai.unique' => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có từ 9-11 chữ số.',
        ]);

        DB::transaction(function () use ($request) {
            $maSV = IdGenerator::nextSinhVien();
            $maTK = 'TK_' . $maSV;

            $tk = TaiKhoan::create([
                'MaTK'              => $maTK,
                'TenDangNhap'       => trim($request->TenDangNhap),
                'MatKhau'           => Hash::make('123456'),
                'MaVaiTro'          => 'VT03',
                'TrangThai'         => true,
                'password_status'   => 'INITIAL',
                'BatBuocDoiMatKhau' => true,
                'SoLanDangNhapSai'  => 0,
            ]);

            SinhVien::create([
                'MaSV'          => $maSV,
                'MaTK'          => $tk->MaTK,
                'MaLop'         => $request->MaLop,
                'MaSoSinhVien'  => trim($request->TenDangNhap),
                'HoTen'         => trim($request->HoTen),
                'Email'         => trim($request->Email),
                'SoDienThoai'   => $request->SoDienThoai ? trim($request->SoDienThoai) : null,
                'TrangThai'     => 'Đang học',
            ]);
        });

        return redirect()->route('sinhvien.index')->with('success', "Thêm sinh viên '{$request->HoTen}' thành công! (Mật khẩu mặc định: 123456)");
    }

    public function edit($id)
    {
        $sinhvien = SinhVien::with('taiKhoan')->findOrFail($id);
        $lops = Lop::with('nganh.khoa')->orderBy('TenLop')->get();
        return view('admin.sinhvien.edit', compact('sinhvien', 'lops'));
    }

    public function update(Request $request, $id)
    {
        $sinhvien = SinhVien::with('taiKhoan')->findOrFail($id);

        $request->validate([
            'TenDangNhap' => 'nullable|string|max:50|unique:tai_khoans,TenDangNhap,' . $sinhvien->MaTK . ',MaTK',
            'HoTen' => 'required|string|max:100',
            'MaLop' => 'required|exists:lops,MaLop',
            'Email' => 'required|email|max:100|unique:sinh_viens,Email,' . $id . ',MaSV',
            'SoDienThoai' => ['nullable', 'string', 'regex:/^0[0-9]{8,10}$/', 'unique:sinh_viens,SoDienThoai,' . $id . ',MaSV']
        ], [
            'TenDangNhap.unique' => 'Tên đăng nhập đã tồn tại.',
            'HoTen.required' => 'Vui lòng nhập họ tên.',
            'MaLop.required' => 'Vui lòng chọn lớp.',
            'Email.required' => 'Vui lòng nhập email.',
            'Email.email' => 'Định dạng email không hợp lệ.',
            'Email.unique' => 'Email đã được sử dụng.',
            'SoDienThoai.unique' => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có từ 9-11 chữ số.',
        ]);

        $sinhvien->update([
            'MaLop'       => $request->MaLop,
            'HoTen'       => trim($request->HoTen),
            'Email'       => trim($request->Email),
            'SoDienThoai' => $request->SoDienThoai ? trim($request->SoDienThoai) : null,
        ]);

        if ($request->filled('TenDangNhap') && $sinhvien->taiKhoan) {
            $sinhvien->taiKhoan->update(['TenDangNhap' => trim($request->TenDangNhap)]);
        }

        return redirect()->route('sinhvien.index')->with('success', 'Cập nhật thông tin sinh viên thành công!');
    }

    public function destroy($id)
    {
        $sinhvien = SinhVien::findOrFail($id);
        $maTK = $sinhvien->MaTK;

        try {
            DB::transaction(function () use ($sinhvien, $maTK) {
                \App\Models\ThanhVienNhom::where('MaSV', $sinhvien->MaSV)->delete();
                $sinhvien->delete();
                if ($maTK) {
                    TaiKhoan::destroy($maTK);
                }
            });
            return redirect()->route('sinhvien.index')->with('success', "Xóa sinh viên '{$sinhvien->HoTen}' thành công!");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa sinh viên '{$sinhvien->HoTen}' do đang tham gia đề tài hoặc nhóm đồ án.");
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importSinhVien', [], 'Sinh viên');
    }
}