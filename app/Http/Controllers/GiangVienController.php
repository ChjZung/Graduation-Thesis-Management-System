<?php

namespace App\Http\Controllers;

use App\Helpers\IdGenerator;
use App\Http\Traits\HandlesExcelImport;
use App\Models\GiangVien;
use App\Models\BoMon;
use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GiangVienController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request)
    {
        $query = GiangVien::with(['boMon.khoa', 'taiKhoan']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('HoTen', 'LIKE', "%{$s}%")
                  ->orWhere('Email', 'LIKE', "%{$s}%")
                  ->orWhere('MaGV', 'LIKE', "%{$s}%")
                  ->orWhereHas('taiKhoan', fn($t) => $t->where('TenDangNhap', 'LIKE', "%{$s}%"));
            });
        }

        if ($request->filled('MaBoMon')) {
            $query->where('MaBoMon', $request->MaBoMon);
        }

        $giangviens = $query->orderBy('MaGV')->paginate(10);
        $bomons = BoMon::with('khoa')->orderBy('TenBoMon')->get();

        return view('admin.giangvien.index', compact('giangviens', 'bomons'));
    }

    public function create()
    {
        $bomons = BoMon::with('khoa')->orderBy('TenBoMon')->get();
        return view('admin.giangvien.create', compact('bomons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenDangNhap' => 'required|string|max:50|unique:tai_khoans,TenDangNhap',
            'HoTen' => 'required|string|max:100',
            'HocVi' => 'required|string|max:50',
            'MaBoMon' => 'required|exists:bo_mons,MaBoMon',
            'Email' => 'required|email|max:100|unique:giang_viens,Email',
            'SoDienThoai' => ['nullable', 'string', 'regex:/^0[0-9]{8,10}$/', 'unique:giang_viens,SoDienThoai']
        ], [
            'TenDangNhap.required' => 'Vui lòng nhập tên đăng nhập (Mã GV).',
            'TenDangNhap.unique' => 'Tên đăng nhập đã tồn tại trong hệ thống.',
            'HoTen.required' => 'Vui lòng nhập họ tên giảng viên.',
            'HocVi.required' => 'Vui lòng chọn hoặc nhập học vị.',
            'MaBoMon.required' => 'Vui lòng chọn bộ môn.',
            'Email.required' => 'Vui lòng nhập email.',
            'Email.email' => 'Định dạng email không hợp lệ.',
            'Email.unique' => 'Email đã được sử dụng bởi giảng viên khác.',
            'SoDienThoai.unique' => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có từ 9-11 chữ số.',
        ]);

        DB::transaction(function () use ($request) {
            $maGV = IdGenerator::nextGiangVien();
            $maTK = 'TK_' . $maGV;

            $tk = TaiKhoan::create([
                'MaTK'              => $maTK,
                'TenDangNhap'       => trim($request->TenDangNhap),
                'MatKhau'           => Hash::make('123456'),
                'MaVaiTro'          => 'VT02',
                'TrangThai'         => true,
                'password_status'   => 'INITIAL',
                'BatBuocDoiMatKhau' => true,
                'SoLanDangNhapSai'  => 0,
            ]);

            GiangVien::create([
                'MaGV'        => $maGV,
                'MaTK'        => $tk->MaTK,
                'MaBoMon'     => $request->MaBoMon,
                'MaSoCanBo'   => trim($request->TenDangNhap),
                'HoTen'       => trim($request->HoTen),
                'Email'       => trim($request->Email),
                'SoDienThoai' => $request->SoDienThoai ? trim($request->SoDienThoai) : null,
                'HocVi'       => trim($request->HocVi),
                'TrangThai'   => true,
            ]);
        });

        return redirect()->route('giangvien.index')->with('success', "Thêm giảng viên '{$request->HoTen}' thành công! (Mật khẩu mặc định: 123456)");
    }

    public function edit($id)
    {
        $giangvien = GiangVien::with('taiKhoan')->findOrFail($id);
        $bomons = BoMon::with('khoa')->orderBy('TenBoMon')->get();
        return view('admin.giangvien.edit', compact('giangvien', 'bomons'));
    }

    public function update(Request $request, $id)
    {
        $giangvien = GiangVien::with('taiKhoan')->findOrFail($id);

        $request->validate([
            'TenDangNhap' => 'nullable|string|max:50|unique:tai_khoans,TenDangNhap,' . $giangvien->MaTK . ',MaTK',
            'HoTen' => 'required|string|max:100',
            'HocVi' => 'required|string|max:50',
            'MaBoMon' => 'required|exists:bo_mons,MaBoMon',
            'Email' => 'required|email|max:100|unique:giang_viens,Email,' . $id . ',MaGV',
            'SoDienThoai' => ['nullable', 'string', 'regex:/^0[0-9]{8,10}$/', 'unique:giang_viens,SoDienThoai,' . $id . ',MaGV']
        ], [
            'TenDangNhap.unique' => 'Tên đăng nhập đã tồn tại.',
            'HoTen.required' => 'Vui lòng nhập họ tên.',
            'HocVi.required' => 'Vui lòng nhập học vị.',
            'MaBoMon.required' => 'Vui lòng chọn bộ môn.',
            'Email.required' => 'Vui lòng nhập email.',
            'Email.email' => 'Định dạng email không hợp lệ.',
            'Email.unique' => 'Email đã được sử dụng.',
            'SoDienThoai.unique' => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex' => 'Số điện thoại phải bắt đầu bằng số 0 và có từ 9-11 chữ số.',
        ]);

        $giangvien->update([
            'MaBoMon'     => $request->MaBoMon,
            'HoTen'       => trim($request->HoTen),
            'Email'       => trim($request->Email),
            'SoDienThoai' => $request->SoDienThoai ? trim($request->SoDienThoai) : null,
            'HocVi'       => trim($request->HocVi),
        ]);

        if ($request->filled('TenDangNhap') && $giangvien->taiKhoan) {
            $giangvien->taiKhoan->update(['TenDangNhap' => trim($request->TenDangNhap)]);
        }

        return redirect()->route('giangvien.index')->with('success', 'Cập nhật thông tin giảng viên thành công!');
    }

    public function destroy($id)
    {
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
            return redirect()->route('giangvien.index')->with('success', "Xóa giảng viên '{$giangvien->HoTen}' thành công!");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa giảng viên '{$giangvien->HoTen}' do đang hướng dẫn hoặc phản biện đề tài.");
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importGiangVien', [], 'Giảng viên');
    }
}