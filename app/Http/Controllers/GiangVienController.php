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

        $totalGV = GiangVien::count();
        $gvHuongDan = GiangVien::has('deTais')->count();
        $gvHoiDong = GiangVien::has('thanhVienHoiDongs')->count();
        $gvActive = GiangVien::whereHas('taiKhoan', fn($tk) => $tk->where('TrangThai', true))->count();

        $stats = [
            'total' => $totalGV,
            'huong_dan' => $gvHuongDan,
            'hoi_dong' => $gvHoiDong,
            'active' => $gvActive,
        ];

        $giangviens = $query->withCount('deTais')->orderBy('MaGV')->paginate(10);
        $bomons = BoMon::with('khoa')->orderBy('TenBoMon')->get();

        return view('admin.giangvien.index', compact('giangviens', 'bomons', 'stats'));
    }

    public function create()
    {
        $bomons = BoMon::with('khoa')->orderBy('TenBoMon')->get();
        return view('admin.giangvien.create', compact('bomons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenDangNhap' => 'required|string|max:50|unique:TaiKhoan,TenDangNhap|unique:GiangVien,MaGV',
            'HoTen' => 'required|string|max:100',
            'HocVi' => 'required|string|max:50',
            'MaBoMon' => 'required|exists:BoMon,MaBoMon',
            'Email' => 'required|email|max:100|unique:GiangVien,Email',
            'SoDienThoai' => ['nullable', 'string', 'regex:/^0[0-9]{8,10}$/', 'unique:GiangVien,SoDienThoai']
        ], [
            'TenDangNhap.required' => 'Vui lòng nhập Mã giảng viên (MaGV).',
            'TenDangNhap.unique'   => 'Mã giảng viên / Tên đăng nhập đã tồn tại trong hệ thống.',
            'HoTen.required'       => 'Vui lòng nhập họ tên giảng viên.',
            'HocVi.required'       => 'Vui lòng chọn hoặc nhập học vị.',
            'MaBoMon.required'     => 'Vui lòng chọn bộ môn.',
            'Email.required'       => 'Vui lòng nhập email.',
            'Email.email'          => 'Định dạng email không hợp lệ.',
            'Email.unique'         => 'Email đã được sử dụng bởi giảng viên khác.',
            'SoDienThoai.unique'   => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex'    => 'Số điện thoại phải bắt đầu bằng số 0 và có từ 9-11 chữ số.',
        ]);

        $maGV = trim($request->TenDangNhap);

        DB::transaction(function () use ($request, $maGV) {
            $tk = TaiKhoan::create([
                'MaTK'              => $maGV,
                'TenDangNhap'       => $maGV,
                'MatKhau'           => Hash::make('123456'),
                'MaVaiTro'          => 'VT02',
                'TrangThai'         => true,
                'TrangThaiMatKhau'  => 'INITIAL',
                'BatBuocDoiMatKhau' => true,
                'SoLanDangNhapSai'  => 0,
            ]);

            GiangVien::create([
                'MaGV'        => $maGV,
                'MaTK'        => $tk->MaTK,
                'MaBoMon'     => $request->MaBoMon,
                'HoTen'       => trim($request->HoTen),
                'Email'       => trim($request->Email),
                'SoDienThoai' => $request->SoDienThoai ? trim($request->SoDienThoai) : null,
                'HocVi'       => trim($request->HocVi),
                'TrangThai'   => 'Đang công tác',
            ]);
        });

        return redirect()->route('giangvien.index')->with('success', "Thêm giảng viên '{$request->HoTen}' (Mã GV: {$maGV}) thành công! (Mật khẩu mặc định: 123456)");
    }

    public function show($id)
    {
        $giangvien = GiangVien::with([
            'boMon.khoa',
            'taiKhoan',
            'deTais.hocKy',
            'deTais.phieuDangKys.nhom.sinhViens',
            'thanhVienHoiDongs.hoiDong.hocKy',
            'chiTieuHuongDans.hocKy',
        ])->withCount(['deTais', 'thanhVienHoiDongs'])->findOrFail($id);

        $totalDeTai = $giangvien->de_tais_count;
        $totalHoiDong = $giangvien->thanh_vien_hoi_dongs_count;
        $nhomHuongDan = \App\Models\Nhom::whereHas('phieuDangKys.deTai', function($q) use ($id) {
            $q->where('MaGV', $id);
        })->count();

        $latestChiTieu = $giangvien->chiTieuHuongDans()->latest()->first();
        $chiTieuToiDa = $latestChiTieu ? $latestChiTieu->SoLuongToiDa : 5;

        $stats = [
            'total_detai'    => $totalDeTai,
            'nhom_huong_dan' => $nhomHuongDan,
            'chi_tieu_toida' => $chiTieuToiDa,
            'total_hoidong'  => $totalHoiDong,
        ];

        return view('admin.giangvien.show', compact('giangvien', 'stats'));
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
            'HoTen' => 'required|string|max:100',
            'HocVi' => 'required|string|max:50',
            'MaBoMon' => 'required|exists:BoMon,MaBoMon',
            'Email' => 'required|email|max:100|unique:GiangVien,Email,' . $id . ',MaGV',
            'SoDienThoai' => ['nullable', 'string', 'regex:/^0[0-9]{8,10}$/', 'unique:GiangVien,SoDienThoai,' . $id . ',MaGV']
        ], [
            'HoTen.required'   => 'Vui lòng nhập họ tên.',
            'HocVi.required'   => 'Vui lòng nhập học vị.',
            'MaBoMon.required' => 'Vui lòng chọn bộ môn.',
            'Email.required'   => 'Vui lòng nhập email.',
            'Email.email'      => 'Định dạng email không hợp lệ.',
            'Email.unique'     => 'Email đã được sử dụng.',
            'SoDienThoai.unique' => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex'  => 'Số điện thoại phải bắt đầu bằng số 0 và có từ 9-11 chữ số.',
        ]);

        $giangvien->update([
            'MaBoMon'     => $request->MaBoMon,
            'HoTen'       => trim($request->HoTen),
            'Email'       => trim($request->Email),
            'SoDienThoai' => $request->SoDienThoai ? trim($request->SoDienThoai) : null,
            'HocVi'       => trim($request->HocVi),
        ]);

        return redirect()->route('giangvien.index')->with('success', 'Cập nhật thông tin giảng viên thành công!');
    }

    public function destroy($id)
    {
        $giangvien = GiangVien::findOrFail($id);
        $maTK = $giangvien->MaTK;

        try {
            DB::transaction(function () use ($giangvien, $maTK) {
                $giangvien->delete();
                if ($maTK) {
                    TaiKhoan::destroy($maTK);
                }
            });
            return redirect()->route('giangvien.index')->with('success', "Xóa giảng viên '{$giangvien->HoTen}' thành công!");
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Không thể xóa giảng viên '{$giangvien->HoTen}' do đang có đề tài, hướng dẫn hoặc hội đồng liên quan.");
        }
    }

    public function importExcel(Request $request)
    {
        return $this->runImport($request, 'importGiangVien', [], 'Giảng viên');
    }
}