<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\HandlesExcelImport;
use App\Models\TaiKhoan;
use App\Models\VaiTro;
use App\Models\GiangVien;
use App\Models\SinhVien;
use App\Models\GiaoVu;
use App\Models\Khoa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaiKhoanController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request)
    {
        $query = TaiKhoan::with(['vaiTro', 'giangVien', 'sinhVien', 'giaoVu']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('TenDangNhap', 'LIKE', "%{$s}%")
                  ->orWhere('MaTK', 'LIKE', "%{$s}%")
                  ->orWhereHas('giangVien', fn($g) => $g->where('HoTen', 'LIKE', "%{$s}%")->orWhere('Email', 'LIKE', "%{$s}%"))
                  ->orWhereHas('sinhVien', fn($sv) => $sv->where('HoTen', 'LIKE', "%{$s}%")->orWhere('Email', 'LIKE', "%{$s}%"))
                  ->orWhereHas('giaoVu', fn($gv) => $gv->where('HoTen', 'LIKE', "%{$s}%")->orWhere('Email', 'LIKE', "%{$s}%"));
            });
        }

        if ($request->filled('vai_tro')) {
            $query->where('MaVaiTro', $request->vai_tro);
        }

        if ($request->filled('trang_thai')) {
            $query->where('TrangThai', (bool)$request->trang_thai);
        }

        if ($request->filled('mat_khau_status')) {
            $query->where('TrangThaiMatKhau', $request->mat_khau_status);
        }

        $totalTK = TaiKhoan::count();
        $adminCount = TaiKhoan::where('MaVaiTro', 'VT01')->count();
        $gvCount = TaiKhoan::where('MaVaiTro', 'VT02')->count();
        $svCount = TaiKhoan::where('MaVaiTro', 'VT03')->count();
        $lockedCount = TaiKhoan::where('TrangThai', 0)->count();

        $stats = [
            'total'     => $totalTK,
            'admin'     => $adminCount,
            'giangvien' => $gvCount,
            'sinhvien'  => $svCount,
            'locked'    => $lockedCount,
        ];

        $taiKhoans = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $vaiTros = VaiTro::orderBy('MaVaiTro')->get();

        return view('admin.taikhoan.index', compact('taiKhoans', 'vaiTros', 'stats'));
    }

    public function create()
    {
        $vaiTros = VaiTro::orderBy('MaVaiTro')->get();
        $khoas = Khoa::orderBy('TenKhoa')->get();
        return view('admin.taikhoan.create', compact('vaiTros', 'khoas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenDangNhap'  => 'required|string|max:50|unique:TaiKhoan,TenDangNhap',
            'MaVaiTro'     => 'required|exists:VaiTro,MaVaiTro',
            'HoTen'        => 'required|string|max:100',
            'Email'        => 'nullable|email|max:100',
            'SoDienThoai'  => ['nullable', 'string', 'regex:/^0[0-9]{8,10}$/'],
            'MatKhau'      => 'nullable|string|min:6',
        ], [
            'TenDangNhap.required' => 'Vui lòng nhập Tên đăng nhập.',
            'TenDangNhap.unique'   => 'Tên đăng nhập này đã tồn tại trong hệ thống.',
            'MaVaiTro.required'    => 'Vui lòng chọn vai trò người dùng.',
            'HoTen.required'       => 'Vui lòng nhập họ tên người dùng.',
            'Email.email'          => 'Định dạng email không hợp lệ.',
            'SoDienThoai.regex'    => 'Số điện thoại phải bắt đầu bằng số 0 và có từ 9-11 chữ số.',
        ]);

        $username = trim($request->TenDangNhap);
        $password = $request->filled('MatKhau') ? $request->MatKhau : '123456';

        DB::transaction(function () use ($request, $username, $password) {
            $tk = TaiKhoan::create([
                'MaTK'              => $username,
                'TenDangNhap'       => $username,
                'MatKhau'           => Hash::make($password),
                'MaVaiTro'          => $request->MaVaiTro,
                'TrangThai'         => true,
                'TrangThaiMatKhau'  => 'INITIAL',
                'BatBuocDoiMatKhau' => true,
                'SoLanDangNhapSai'  => 0,
            ]);

            // Cập nhật hồ sơ thực thể tương ứng
            if ($request->MaVaiTro === 'VT01') {
                GiaoVu::create([
                    'MaGVu'       => 'GVU_' . Str::upper(Str::random(5)),
                    'MaTK'        => $tk->MaTK,
                    'HoTen'       => trim($request->HoTen),
                    'Email'       => $request->Email ? trim($request->Email) : null,
                    'SoDienThoai' => $request->SoDienThoai ? trim($request->SoDienThoai) : null,
                    'ChucVu'      => 'Cán bộ giáo vụ',
                    'MaKhoa'      => $request->MaKhoa ?: null,
                ]);
            }
        });

        return redirect()->route('admin.taikhoan.index')->with('success', "Tạo tài khoản '{$username}' thành công! (Mật khẩu: {$password})");
    }

    public function show($id)
    {
        $taiKhoan = TaiKhoan::with([
            'vaiTro',
            'giangVien.boMon.khoa',
            'sinhVien.lop.nganh.khoa',
            'giaoVu.khoa'
        ])->findOrFail($id);

        return view('admin.taikhoan.show', compact('taiKhoan'));
    }

    public function edit($id)
    {
        $taiKhoan = TaiKhoan::with(['vaiTro', 'giangVien', 'sinhVien', 'giaoVu'])->findOrFail($id);
        $vaiTros = VaiTro::orderBy('MaVaiTro')->get();

        return view('admin.taikhoan.edit', compact('taiKhoan', 'vaiTros'));
    }

    public function update(Request $request, $id)
    {
        $taiKhoan = TaiKhoan::findOrFail($id);

        $request->validate([
            'MaVaiTro'          => 'required|exists:VaiTro,MaVaiTro',
            'TrangThai'         => 'required|boolean',
            'BatBuocDoiMatKhau' => 'nullable|boolean',
            'MatKhauMoi'        => 'nullable|string|min:6',
        ], [
            'MaVaiTro.required' => 'Vui lòng chọn vai trò.',
            'MatKhauMoi.min'    => 'Mật khẩu mới tối thiểu 6 ký tự.',
        ]);

        // Bảo vệ tài khoản đang đăng nhập
        if ($taiKhoan->MaTK === auth()->id() && !$request->TrangThai) {
            return redirect()->back()->withErrors('Không thể tự khóa tài khoản Admin đang sử dụng!');
        }

        $dataUpdate = [
            'MaVaiTro'          => $request->MaVaiTro,
            'TrangThai'         => (bool)$request->TrangThai,
            'BatBuocDoiMatKhau' => $request->has('BatBuocDoiMatKhau'),
        ];

        if ($request->filled('MatKhauMoi')) {
            $dataUpdate['MatKhau'] = Hash::make($request->MatKhauMoi);
            $dataUpdate['TrangThaiMatKhau'] = 'ACTIVE';
            $dataUpdate['NgayDoiMatKhau'] = now();
        }

        if (!$request->TrangThai && $taiKhoan->TrangThai) {
            $dataUpdate['NgayKhoa'] = now();
        }

        $taiKhoan->update($dataUpdate);

        return redirect()->route('admin.taikhoan.index')->with('success', "Cập nhật tài khoản '{$taiKhoan->TenDangNhap}' thành công!");
    }

    public function destroy($id)
    {
        $taiKhoan = TaiKhoan::findOrFail($id);

        if ($taiKhoan->MaTK === auth()->id()) {
            return redirect()->back()->withErrors('Không thể tự xóa tài khoản bạn đang đăng nhập!');
        }

        try {
            DB::transaction(function () use ($taiKhoan) {
                // Xóa profile con nếu là Giáo vụ tạo riêng
                GiaoVu::where('MaTK', $taiKhoan->MaTK)->delete();
                $taiKhoan->delete();
            });
            return redirect()->route('admin.taikhoan.index')->with('success', "Xóa tài khoản '{$taiKhoan->TenDangNhap}' thành công!");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa tài khoản '{$taiKhoan->TenDangNhap}' do đang có dữ liệu hồ sơ liên kết.");
        }
    }

    public function toggleLock($id)
    {
        $taiKhoan = TaiKhoan::findOrFail($id);

        if ($taiKhoan->MaTK === auth()->id()) {
            return redirect()->back()->withErrors('Không thể tự khóa tài khoản Admin bạn đang sử dụng!');
        }

        $taiKhoan->TrangThai = !$taiKhoan->TrangThai;
        $taiKhoan->NgayKhoa = $taiKhoan->TrangThai ? null : now();
        $taiKhoan->save();

        $msg = $taiKhoan->TrangThai ? "Mở khóa tài khoản '{$taiKhoan->TenDangNhap}' thành công!" : "Đã khóa tài khoản '{$taiKhoan->TenDangNhap}'!";
        return redirect()->back()->with('success', $msg);
    }

    public function resetPassword(Request $request, $id)
    {
        $taiKhoan = TaiKhoan::findOrFail($id);

        $taiKhoan->update([
            'MatKhau'           => Hash::make('123456'),
            'TrangThaiMatKhau'  => 'INITIAL',
            'BatBuocDoiMatKhau' => true,
            'SoLanDangNhapSai'  => 0,
        ]);

        return redirect()->back()->with('success', "Đã đặt lại mật khẩu của tài khoản '{$taiKhoan->TenDangNhap}' về mặc định: 123456 (Bắt buộc đổi khi đăng nhập)!");
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importTaiKhoan', [], 'Tài khoản');
    }
}
