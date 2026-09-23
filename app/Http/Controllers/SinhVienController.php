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

        $sinhviens = $query->with('nhoms')->orderBy('MaSV')->paginate(5);
        $lops = Lop::with('nganh')->orderBy('TenLop')->get();

        return view('admin.sinhvien.index', compact('sinhviens', 'lops', 'stats'));
    }

    public function create()
    {
        $lops = Lop::with('nganh.khoa')->orderBy('TenLop')->get();
        return view('admin.sinhvien.create', compact('lops') + ['Lop' => $lops]);
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
        $lops = Lop::with('nganh.khoa')->orderBy('TenLop')->get();
        return view('admin.sinhvien.edit', compact('sinhvien', 'lops') + ['Lop' => $lops]);
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
            'MaLop'           => $request->MaLop,
            'MaNganh'         => $lop->MaNganh,
            'MaKhoa'          => $lop->MaKhoa,
            'HoTen'           => trim($request->HoTen),
            'Email'           => trim($request->Email),
            'SoDienThoai'     => $request->SoDienThoai ? trim($request->SoDienThoai) : null,
            'SoTinChiTichLuy' => $request->has('SoTinChiTichLuy') ? (int)$request->SoTinChiTichLuy : $sinhvien->SoTinChiTichLuy,
            'DiemTichLuy'     => $request->has('DiemTichLuy') ? (float)$request->DiemTichLuy : $sinhvien->DiemTichLuy,
            'TrangThai'       => $request->has('TrangThai') ? $request->TrangThai : $sinhvien->TrangThai,
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

    // ══════════════════════════════════════════════════════════════════════
    // QUẢN LÝ DANH SÁCH SINH VIÊN ĐỦ ĐIỀU KIỆN LÀM KHÓA LUẬN (BR03)
    // ══════════════════════════════════════════════════════════════════════

    public function dieuKienIndex(Request $request)
    {
        $hocKys = \App\Models\HocKy::orderBy('MaHocKy', 'desc')->get();
        $selectedHocKy = $request->get('MaHocKy', $hocKys->first()->MaHocKy ?? '');

        $query = \App\Models\DanhSachSVDuDieuKien::with(['sinhVien.lop.nganh.khoa', 'hocKy'])
            ->where('MaHocKy', $selectedHocKy);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->whereHas('sinhVien', function ($q) use ($s) {
                $q->where('HoTen', 'LIKE', "%{$s}%")
                  ->orWhere('MaSV', 'LIKE', "%{$s}%")
                  ->orWhere('Email', 'LIKE', "%{$s}%");
            });
        }

        if ($request->filled('MaLop')) {
            $query->whereHas('sinhVien', fn($q) => $q->where('MaLop', $request->MaLop));
        }

        if ($request->filled('trang_thai')) {
            $query->where('TrangThai', $request->trang_thai);
        }

        $dsDuDieuKien = $query->paginate(15)->withQueryString();

        $totalEvaluated = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $selectedHocKy)->count();
        $totalEligible  = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $selectedHocKy)->where('TrangThai', 'Đủ điều kiện')->count();
        $totalIneligible = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $selectedHocKy)->where('TrangThai', 'Chưa đủ điều kiện')->count();
        $publishedCount = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $selectedHocKy)->where('GhiChu', 'LIKE', '%Công bố%')->count();

        $stats = [
            'total_evaluated' => $totalEvaluated,
            'total_eligible'  => $totalEligible,
            'total_ineligible'=> $totalIneligible,
            'is_published'    => $publishedCount > 0,
        ];

        $lops = Lop::with('nganh')->orderBy('TenLop')->get();

        return view('admin.sinhvien.du_dieu_kien', compact('dsDuDieuKien', 'hocKys', 'selectedHocKy', 'lops', 'stats'));
    }

    public function dieuKienRaSoat(Request $request)
    {
        $request->validate([
            'MaHocKy' => 'required|exists:HocKy,MaHocKy',
            'MinTinChi' => 'nullable|integer|min:0',
            'MinGPA' => 'nullable|numeric|min:0|max:4',
        ]);

        $maHocKy = $request->MaHocKy;
        $minTinChi = $request->get('MinTinChi', 115);
        $minGpa = $request->get('MinGPA', 2.0);

        $sinhViens = SinhVien::where('TrangThai', 'Đang học')->get();
        $countEligible = 0;
        $countIneligible = 0;

        foreach ($sinhViens as $sv) {
            $isEligible = ($sv->SoTinChiTichLuy >= $minTinChi) && ($sv->DiemTichLuy >= $minGpa);
            $trangThai = $isEligible ? 'Đủ điều kiện' : 'Chưa đủ điều kiện';
            $dieuKienText = "Tín chỉ: {$sv->SoTinChiTichLuy}/{$minTinChi} | ĐTB: {$sv->DiemTichLuy}/{$minGpa}";

            $shortHk = preg_replace('/[^A-Za-z0-9]/', '', $maHocKy);
            $maDsdk = substr('DK_' . $sv->MaSV . '_' . $shortHk, 0, 20);

            \App\Models\DanhSachSVDuDieuKien::updateOrCreate(
                [
                    'MaSV' => $sv->MaSV,
                    'MaHocKy' => $maHocKy,
                ],
                [
                    'MaDSDK' => $maDsdk,
                    'NgayXetDuyet' => now(),
                    'TrangThai' => $trangThai,
                    'DieuKien' => $dieuKienText,
                    'GhiChu' => $isEligible ? 'Rà soát tự động: Đủ điều kiện làm khóa luận' : 'Chưa đủ điều kiện tín chỉ/GPA',
                ]
            );

            if ($isEligible) $countEligible++;
            else $countIneligible++;
        }

        return redirect()->route('admin.sinhvien.dieu_kien', ['MaHocKy' => $maHocKy])
            ->with('success', "Đã rà soát tự động thành công cho {$sinhViens->count()} sinh viên! ({$countEligible} Đủ điều kiện, {$countIneligible} Chưa đủ điều kiện)");
    }

    public function dieuKienCapNhat(Request $request)
    {
        $request->validate([
            'MaDSDK' => 'required|exists:DanhSachSVDuDieuKien,MaDSDK',
            'TrangThai' => 'required|in:Đủ điều kiện,Chưa đủ điều kiện',
            'GhiChu' => 'nullable|string|max:255',
        ]);

        $ds = \App\Models\DanhSachSVDuDieuKien::findOrFail($request->MaDSDK);
        $ds->update([
            'TrangThai' => $request->TrangThai,
            'GhiChu' => $request->GhiChu ? trim($request->GhiChu) : 'Cập nhật thủ công bởi Admin',
            'NgayXetDuyet' => now(),
        ]);

        return redirect()->back()->with('success', "Đã cập nhật trạng thái xét duyệt cho sinh viên {$ds->MaSV} thành '{$request->TrangThai}'!");
    }

    public function dieuKienCongBo(Request $request)
    {
        $request->validate([
            'MaHocKy' => 'required|exists:HocKy,MaHocKy',
        ]);

        \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $request->MaHocKy)
            ->update([
                'GhiChu' => DB::raw("CONCAT(COALESCE(GhiChu, ''), ' - Đã công bố')")
            ]);

        return redirect()->back()->with('success', "Đã công bố danh sách sinh viên đủ điều kiện làm khóa luận cho học kỳ {$request->MaHocKy}!");
    }

    public function dieuKienExport(Request $request)
    {
        $maHocKy = $request->get('MaHocKy');
        $ds = \App\Models\DanhSachSVDuDieuKien::with(['sinhVien.lop.nganh', 'hocKy'])
            ->when($maHocKy, fn($q) => $q->where('MaHocKy', $maHocKy))
            ->get();

        $filename = 'Danh_Sach_SV_Du_Dieu_Kien_' . ($maHocKy ?? 'All') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($ds) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel UTF-8
            fputcsv($file, ['STT', 'MSSV', 'Họ Và Tên', 'Lớp', 'Ngành', 'Số Tín Chỉ', 'ĐTB Tích Lũy', 'Trạng Thái', 'Tiêu Chí / Ghi Chú']);

            foreach ($ds as $idx => $item) {
                $sv = $item->sinhVien;
                fputcsv($file, [
                    $idx + 1,
                    $item->MaSV,
                    $sv->HoTen ?? '',
                    $sv->lop->TenLop ?? '',
                    $sv->lop->nganh->TenNganh ?? '',
                    $sv->SoTinChiTichLuy ?? 0,
                    $sv->DiemTichLuy ?? 0.0,
                    $item->TrangThai,
                    $item->DieuKien . ' (' . $item->GhiChu . ')',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}