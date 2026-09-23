<?php

namespace App\Http\Controllers;

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
                  ->orWhereHas('taiKhoan', fn($t) => $t->where('TenDangNhap', 'LIKE', "%{$s}%"));
            });
        }

        if ($request->filled('MaLop')) {
            $query->where('MaLop', $request->MaLop);
        }

        if ($request->filled('dieu_kien')) {
            if ($request->dieu_kien === 'du') {
                $query->where('SoTinChiTichLuy', '>=', 115);
            } elseif ($request->dieu_kien === 'chua') {
                $query->where(function($q) {
                    $q->where('SoTinChiTichLuy', '<', 115)
                      ->orWhereNull('SoTinChiTichLuy');
                });
            }
        }

        $totalSV = SinhVien::count();
        $duDkSV = SinhVien::where('SoTinChiTichLuy', '>=', 115)->count();
        $thieuDkSV = SinhVien::where('SoTinChiTichLuy', '<', 115)->orWhereNull('SoTinChiTichLuy')->count();
        $activeSV = SinhVien::whereHas('taiKhoan', fn($tk) => $tk->where('TrangThai', true))->count();

        $stats = [
            'total' => $totalSV,
            'du_dk' => $duDkSV,
            'pct_du_dk' => $totalSV > 0 ? round(($duDkSV / $totalSV) * 100, 1) : 0,
            'thieu_dk' => $thieuDkSV,
            'active' => $activeSV,
        ];

        $sinhviens = $query->with('nhoms')->orderBy('MaSV')->paginate(10);
        $lops = Lop::with('nganh')->orderBy('TenLop')->get();

        return view('admin.sinhvien.index', compact('sinhviens', 'lops', 'stats'));
    }

    public function create()
    {
        $Lop = Lop::with('nganh.khoa')->orderBy('TenLop')->get();
        return view('admin.sinhvien.create', compact('Lop'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenDangNhap' => 'required|string|max:50|unique:TaiKhoan,TenDangNhap|unique:SinhVien,MaSV',
            'HoTen' => 'required|string|max:100',
            'MaLop' => 'required|exists:Lop,MaLop',
            'Email' => 'required|email|max:100|unique:SinhVien,Email',
            'SoDienThoai' => ['nullable', 'string', 'regex:/^0[0-9]{8,10}$/', 'unique:SinhVien,SoDienThoai']
        ], [
            'TenDangNhap.required' => 'Vui lòng nhập MSSV.',
            'TenDangNhap.unique'   => 'MSSV / Tên đăng nhập đã tồn tại trong hệ thống.',
            'HoTen.required'       => 'Vui lòng nhập họ tên sinh viên.',
            'MaLop.required'       => 'Vui lòng chọn lớp học.',
            'Email.required'       => 'Vui lòng nhập email.',
            'Email.email'          => 'Định dạng email không hợp lệ.',
            'Email.unique'         => 'Email đã được sử dụng.',
            'SoDienThoai.unique'   => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex'    => 'Số điện thoại phải bắt đầu bằng số 0 và có từ 9-11 chữ số.',
        ]);

        $mssv = trim($request->TenDangNhap);
        $lop = Lop::with('nganh')->findOrFail($request->MaLop);

        DB::transaction(function () use ($request, $mssv, $lop) {
            $tk = TaiKhoan::create([
                'MaTK'              => $mssv,
                'TenDangNhap'       => $mssv,
                'MatKhau'           => Hash::make('123456'),
                'MaVaiTro'          => 'VT03',
                'TrangThai'         => true,
                'TrangThaiMatKhau'  => 'INITIAL',
                'BatBuocDoiMatKhau' => true,
                'SoLanDangNhapSai'  => 0,
            ]);

            SinhVien::create([
                'MaSV'            => $mssv,
                'MaTK'            => $tk->MaTK,
                'MaLop'           => $request->MaLop,
                'MaNganh'         => $lop->MaNganh,
                'MaKhoa'          => $lop->MaKhoa,
                'HoTen'           => trim($request->HoTen),
                'Email'           => trim($request->Email),
                'SoDienThoai'     => $request->SoDienThoai ? trim($request->SoDienThoai) : null,
                'KhoaHoc'         => $lop->KhoaHoc,
                'SoTinChiTichLuy' => 0,
                'DiemTichLuy'     => 0.00,
                'TrangThai'       => 'Đang học',
            ]);
        });

        return redirect()->route('sinhvien.index')->with('success', "Thêm sinh viên '{$request->HoTen}' (MSSV: {$mssv}) thành công! (Mật khẩu mặc định: 123456)");
    }

    public function show($id)
    {
        $sinhvien = SinhVien::with([
            'lop.nganh.khoa',
            'taiKhoan.vaiTro',
            'nhoms' => function($q) {
                $q->with([
                    'sinhViens',
                    'dangKyDeTai.deTai.giangVien',
                    'baoCaos.mocThoiGian',
                    'hoSoBaoVe.hoiDong',
                ]);
            },
            'ketQuaSinhViens.hocKy',
        ])->findOrFail($id);

        $tinChi = $sinhvien->SoTinChiTichLuy ?? 0;
        $isDuDieuKien = $tinChi >= 115;
        $gpa = $sinhvien->DiemTichLuy ?? 0.0;
        $nhomHienTai = $sinhvien->nhoms->first();
        $ketQua = $sinhvien->ketQuaSinhViens->first();

        $stats = [
            'tin_chi'       => $tinChi,
            'is_du_dk'      => $isDuDieuKien,
            'gpa'           => $gpa,
            'has_nhom'      => $nhomHienTai ? true : false,
            'trang_thai_tk' => $sinhvien->taiKhoan ? $sinhvien->taiKhoan->TrangThai : false,
        ];

        return view('admin.sinhvien.show', compact('sinhvien', 'stats', 'nhomHienTai', 'ketQua'));
    }

    public function edit($id)
    {
        $sinhvien = SinhVien::with('taiKhoan')->findOrFail($id);
        $Lop = Lop::with('nganh.khoa')->orderBy('TenLop')->get();
        return view('admin.sinhvien.edit', compact('sinhvien', 'Lop'));
    }

    public function update(Request $request, $id)
    {
        $sinhvien = SinhVien::with('taiKhoan')->findOrFail($id);

        $request->validate([
            'HoTen' => 'required|string|max:100',
            'MaLop' => 'required|exists:Lop,MaLop',
            'Email' => 'required|email|max:100|unique:SinhVien,Email,' . $id . ',MaSV',
            'SoDienThoai' => ['nullable', 'string', 'regex:/^0[0-9]{8,10}$/', 'unique:SinhVien,SoDienThoai,' . $id . ',MaSV']
        ], [
            'HoTen.required' => 'Vui lòng nhập họ tên.',
            'MaLop.required' => 'Vui lòng chọn lớp.',
            'Email.required' => 'Vui lòng nhập email.',
            'Email.email'    => 'Định dạng email không hợp lệ.',
            'Email.unique'   => 'Email đã được sử dụng.',
            'SoDienThoai.unique' => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex'  => 'Số điện thoại phải bắt đầu bằng số 0 và có từ 9-11 chữ số.',
        ]);

        $lop = Lop::findOrFail($request->MaLop);

        $sinhvien->update([
            'MaLop'       => $request->MaLop,
            'MaNganh'     => $lop->MaNganh,
            'MaKhoa'      => $lop->MaKhoa,
            'HoTen'       => trim($request->HoTen),
            'Email'       => trim($request->Email),
            'SoDienThoai' => $request->SoDienThoai ? trim($request->SoDienThoai) : null,
        ]);

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