<?php

namespace App\Http\Controllers;

use App\Http\Traits\HandlesExcelImport;
use App\Models\SinhVien;
use App\Models\Lop;
use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SinhVienController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'danh_muc');
        $lops = Lop::with('nganh.khoa')->orderBy('TenLop')->get();
        $hocKys = \App\Models\HocKy::orderBy('MaHocKy', 'desc')->get();
        $selectedHocKy = $request->get('MaHocKy', $hocKys->first()->MaHocKy ?? '');

        // ══════════════════════════════════════════════════════════════
        // 1. DATA CHO TAB 1: QUẢN LÝ DANH MỤC SINH VIÊN
        // ══════════════════════════════════════════════════════════════
        $querySv = SinhVien::with(['lop.nganh.khoa', 'taiKhoan', 'nhoms']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $querySv->where(function ($q) use ($s, $sClean) {
                $q->where('HoTen', 'LIKE', "%{$s}%")
                  ->orWhere('Email', 'LIKE', "%{$s}%")
                  ->orWhere('MaSV', 'LIKE', "%{$s}%")
                  ->orWhere('SoDienThoai', 'LIKE', "%{$s}%");
                if (!empty($sClean)) {
                    $q->orWhere('MaSV', 'LIKE', "%{$sClean}%")
                      ->orWhere('SoDienThoai', 'LIKE', "%{$sClean}%");
                }
                $q->orWhereHas('taiKhoan', function($t) use ($s, $sClean) {
                    $t->where('TenDangNhap', 'LIKE', "%{$s}%");
                    if (!empty($sClean)) {
                        $t->orWhere('TenDangNhap', 'LIKE', "%{$sClean}%");
                    }
                });
            });
        }

        if ($request->filled('MaLop')) {
            $querySv->where('MaLop', $request->MaLop);
        }

        if ($request->filled('MaHocKy_filter')) {
            $hkFilter = $request->MaHocKy_filter;
            $querySv->where(function($q) use ($hkFilter) {
                $q->whereHas('danhSachSVDuDieuKiens', fn($d) => $d->where('MaHocKy', $hkFilter))
                  ->orWhereHas('nhoms', fn($n) => $n->where('MaHocKy', $hkFilter));
            });
        }

        if ($request->filled('trang_thai_hoc_tap')) {
            $querySv->where('TrangThai', $request->trang_thai_hoc_tap);
        }

        if ($request->filled('dieu_kien')) {
            if ($request->dieu_kien === 'du') {
                $querySv->where('SoTinChiTichLuy', '>=', 115)->where('DiemTichLuy', '>=', 2.0);
            } elseif ($request->dieu_kien === 'chua') {
                $querySv->where(function($q) {
                    $q->where('SoTinChiTichLuy', '<', 115)
                      ->orWhereNull('SoTinChiTichLuy')
                      ->orWhere('DiemTichLuy', '<', 2.0)
                      ->orWhereNull('DiemTichLuy');
                });
            }
        }

        $totalSV = SinhVien::count();
        $duDkSV = SinhVien::where('SoTinChiTichLuy', '>=', 115)->where('DiemTichLuy', '>=', 2.0)->count();
        $thieuDkSV = $totalSV - $duDkSV;
        $activeSV = SinhVien::whereHas('taiKhoan', fn($tk) => $tk->where('TrangThai', true))->count();

        $statsSv = [
            'total'     => $totalSV,
            'du_dk'     => $duDkSV,
            'pct_du_dk' => $totalSV > 0 ? round(($duDkSV / $totalSV) * 100, 1) : 0,
            'thieu_dk'  => $thieuDkSV,
            'active'    => $activeSV,
        ];

        $sinhviens = $querySv->orderBy('MaSV')->paginate(15, ['*'], 'page_sv')->withQueryString();

        // ══════════════════════════════════════════════════════════════
        // 2. DATA CHO TAB 2: DANH SÁCH SV ĐỦ ĐIỀU KIỆN (THEO HỌC KỲ)
        // ══════════════════════════════════════════════════════════════
        $queryDk = \App\Models\DanhSachSVDuDieuKien::with(['sinhVien.lop.nganh.khoa', 'sinhVien.nhoms', 'hocKy'])
            ->where('MaHocKy', $selectedHocKy);

        if ($request->filled('search_dk')) {
            $s = trim($request->search_dk);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $queryDk->where(function ($rootQ) use ($s, $sClean) {
                $rootQ->where('MaSV', 'LIKE', "%{$s}%");
                if (!empty($sClean)) {
                    $rootQ->orWhere('MaSV', 'LIKE', "%{$sClean}%");
                }
                $rootQ->orWhereHas('sinhVien', function ($q) use ($s, $sClean) {
                    $q->where('HoTen', 'LIKE', "%{$s}%")
                      ->orWhere('MaSV', 'LIKE', "%{$s}%")
                      ->orWhere('Email', 'LIKE', "%{$s}%")
                      ->orWhere('SoDienThoai', 'LIKE', "%{$s}%");
                    if (!empty($sClean)) {
                        $q->orWhere('MaSV', 'LIKE', "%{$sClean}%");
                    }
                });
            });
        }

        if ($request->filled('MaLop_dk')) {
            $queryDk->whereHas('sinhVien', fn($q) => $q->where('MaLop', $request->MaLop_dk));
        }

        if ($request->filled('trang_thai_dk')) {
            $queryDk->where('TrangThai', $request->trang_thai_dk);
        }

        $dsDuDieuKien = $queryDk->orderBy('NgayXetDuyet', 'desc')
            ->orderBy('MaSV', 'asc')
            ->paginate(15, ['*'], 'page_dk')
            ->withQueryString();

        $totalEvaluated = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $selectedHocKy)->count();
        $totalEligible  = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $selectedHocKy)->where('TrangThai', 'Đủ điều kiện')->count();
        $totalIneligible = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $selectedHocKy)->where('TrangThai', 'Chưa đủ điều kiện')->count();
        $publishedCount = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $selectedHocKy)->where('GhiChu', 'LIKE', '%Công bố%')->count();

        $statsDk = [
            'total_evaluated' => $totalEvaluated,
            'total_eligible'  => $totalEligible,
            'total_ineligible'=> $totalIneligible,
            'is_published'    => $publishedCount > 0,
        ];

        $allSinhViens = SinhVien::select('MaSV', 'HoTen', 'MaLop')->orderBy('HoTen')->get();

        return view('admin.sinhvien.index', compact(
            'activeTab',
            'sinhviens',
            'lops',
            'statsSv',
            'hocKys',
            'selectedHocKy',
            'dsDuDieuKien',
            'statsDk',
            'allSinhViens'
        ));
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
            'SoDienThoai' => ['nullable', 'string', 'regex:/^0[0-9]{8,10}$/', 'unique:SinhVien,SoDienThoai'],
            'SoTinChiTichLuy' => 'nullable|integer|min:0',
            'DiemTichLuy' => 'nullable|numeric|min:0|max:4',
            'TrangThai' => 'nullable|string',
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

            $soTinChi = $request->filled('SoTinChiTichLuy') ? (int)$request->SoTinChiTichLuy : 0;
            $diemTichLuy = $request->filled('DiemTichLuy') ? (float)$request->DiemTichLuy : 0.00;
            $trangThai = $request->filled('TrangThai') ? $request->TrangThai : 'Đang học';

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
                'SoTinChiTichLuy' => $soTinChi,
                'DiemTichLuy'     => $diemTichLuy,
                'TrangThai'       => $trangThai,
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
        return redirect()->route('sinhvien.index', array_merge($request->except('tab'), ['tab' => 'du_dieu_kien']));
    }

    public function dieuKienRaSoat(Request $request)
    {
        $request->validate([
            'MaHocKy' => 'required|exists:HocKy,MaHocKy',
            'MinTinChi' => 'nullable|integer|min:0',
            'MinGPA' => 'nullable|numeric|min:0|max:4',
            'PhamVi' => 'nullable|string',
            'MaLop_rasoat' => 'nullable|string',
        ]);

        $maHocKy = $request->MaHocKy;
        $minTinChi = (int)$request->get('MinTinChi', 115);
        $minGpa = (float)$request->get('MinGPA', 2.0);
        $phamVi = $request->get('PhamVi', 'ky_hien_tai');

        if ($phamVi === 'ky_hien_tai') {
            // Chỉ rà soát các sinh viên hiện đã có trong danh sách của học kỳ này
            $sinhVienIds = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $maHocKy)->pluck('MaSV');
            if ($sinhVienIds->isEmpty()) {
                return redirect()->route('sinhvien.index', ['tab' => 'du_dieu_kien', 'MaHocKy' => $maHocKy])
                    ->with('warning', "Học kỳ này hiện chưa có sinh viên nào trong danh sách. Vui lòng bấm 'Thêm SV Vào Kỳ Này' hoặc chọn phạm vi 'Rà soát theo Lớp' / 'Toàn trường'!");
            }
            $sinhViens = SinhVien::whereIn('MaSV', $sinhVienIds)->get();
        } elseif ($phamVi === 'theo_lop') {
            if (!$request->filled('MaLop_rasoat')) {
                return redirect()->back()->withErrors('Vui lòng chọn lớp cần rà soát sinh viên!');
            }
            $sinhViens = SinhVien::where('MaLop', $request->MaLop_rasoat)
                ->where('TrangThai', '!=', 'Đã tốt nghiệp')
                ->get();
        } else {
            // toan_bo
            $sinhViens = SinhVien::where('TrangThai', '!=', 'Đã tốt nghiệp')->get();
        }

        $countEligible = 0;
        $countIneligible = 0;

        foreach ($sinhViens as $sv) {
            $lyDoKhongDat = [];

            // 1. Kiểm tra số tín chỉ tích lũy (>= X)
            $tc = (int)($sv->SoTinChiTichLuy ?? 0);
            if ($tc < $minTinChi) {
                $lyDoKhongDat[] = "Thiếu " . ($minTinChi - $tc) . " TC ({$tc}/{$minTinChi})";
            }

            // 2. Kiểm tra điểm trung bình tích lũy GPA (>= Y)
            $gpa = (float)($sv->DiemTichLuy ?? 0.0);
            if ($gpa < $minGpa) {
                $lyDoKhongDat[] = "ĐTB chưa đạt (" . number_format($gpa, 2) . "/{$minGpa})";
            }

            // 3. Kiểm tra không bị hạn chế học tập
            if (!in_array($sv->TrangThai, ['Đang học', 'Đủ điều kiện'])) {
                $lyDoKhongDat[] = "Bị hạn chế học tập (Trạng thái: {$sv->TrangThai})";
            }

            $isEligible = count($lyDoKhongDat) === 0;
            $trangThai = $isEligible ? 'Đủ điều kiện' : 'Chưa đủ điều kiện';
            $dieuKienText = "Tín chỉ: {$tc}/{$minTinChi} | ĐTB: " . number_format($gpa, 2) . "/{$minGpa}";

            $ghiChu = $isEligible 
                ? 'Đủ điều kiện làm khóa luận tốt nghiệp' 
                : 'Không đủ điều kiện: ' . implode('; ', $lyDoKhongDat);

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
                    'GhiChu' => $ghiChu,
                ]
            );

            if ($isEligible) $countEligible++;
            else $countIneligible++;
        }

        return redirect()->route('sinhvien.index', ['tab' => 'du_dieu_kien', 'MaHocKy' => $maHocKy])
            ->with('success', "Đã hoàn tất rà soát điều kiện học kỳ cho {$sinhViens->count()} sinh viên! ({$countEligible} Đủ điều kiện, {$countIneligible} Không đủ điều kiện)");
    }

    public function dieuKienXoa($id)
    {
        $item = \App\Models\DanhSachSVDuDieuKien::where('MaDSDK', $id)->firstOrFail();
        $maHk = $item->MaHocKy;
        $sv = $item->sinhVien;
        $name = $sv ? "{$sv->HoTen} ({$sv->MaSV})" : $item->MaSV;
        $item->delete();

        return redirect()->route('sinhvien.index', ['tab' => 'du_dieu_kien', 'MaHocKy' => $maHk])
            ->with('success', "Đã xóa sinh viên {$name} khỏi danh sách học kỳ {$maHk} thành công!");
    }

    public function dieuKienThem(Request $request)
    {
        $loaiThem = $request->input('loai_them', 'co_san');

        if ($loaiThem === 'moi') {
            $request->validate([
                'TenDangNhap' => 'required|string|max:50|unique:TaiKhoan,TenDangNhap|unique:SinhVien,MaSV',
                'HoTen' => 'required|string|max:100',
                'MaLop' => 'required|exists:Lop,MaLop',
                'Email' => 'nullable|email|max:100|unique:SinhVien,Email',
                'SoDienThoai' => ['nullable', 'string', 'regex:/^0[0-9]{8,10}$/'],
                'SoTinChiTichLuy' => 'nullable|integer|min:0',
                'DiemTichLuy' => 'nullable|numeric|min:0|max:4',
                'MaHocKy' => 'required|exists:HocKy,MaHocKy',
                'TrangThai' => 'required|in:Đủ điều kiện,Chưa đủ điều kiện',
                'GhiChu' => 'nullable|string|max:255',
            ], [
                'TenDangNhap.required' => 'Vui lòng nhập MSSV.',
                'TenDangNhap.unique'   => 'MSSV / Tên đăng nhập này đã tồn tại trong hệ thống.',
                'HoTen.required'       => 'Vui lòng nhập họ tên sinh viên.',
                'MaLop.required'       => 'Vui lòng chọn lớp học.',
                'Email.email'          => 'Định dạng email không hợp lệ.',
                'Email.unique'         => 'Email đã được sử dụng.',
                'SoDienThoai.regex'    => 'Số điện thoại phải bắt đầu bằng số 0 và có 9-11 chữ số.',
            ]);

            $mssv = trim($request->TenDangNhap);
            $lop = Lop::with('nganh')->findOrFail($request->MaLop);

            $sv = DB::transaction(function () use ($request, $mssv, $lop) {
                // Tự động tạo tài khoản Portal cho sinh viên mới (Mật khẩu mặc định: 123456, VT03)
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

                $soTinChi = $request->filled('SoTinChiTichLuy') ? (int)$request->SoTinChiTichLuy : 120;
                $diemTichLuy = $request->filled('DiemTichLuy') ? (float)$request->DiemTichLuy : 2.50;
                $email = $request->filled('Email') ? trim($request->Email) : ($mssv . '@st.huit.edu.vn');

                return SinhVien::create([
                    'MaSV'            => $mssv,
                    'MaTK'            => $tk->MaTK,
                    'MaLop'           => $request->MaLop,
                    'MaNganh'         => $lop->MaNganh,
                    'MaKhoa'          => $lop->MaKhoa,
                    'HoTen'           => trim($request->HoTen),
                    'Email'           => $email,
                    'SoDienThoai'     => $request->SoDienThoai ? trim($request->SoDienThoai) : null,
                    'KhoaHoc'         => $lop->KhoaHoc ?? '2021-2025',
                    'SoTinChiTichLuy' => $soTinChi,
                    'DiemTichLuy'     => $diemTichLuy,
                    'TrangThai'       => 'Đang học',
                ]);
            });

            $shortHk = preg_replace('/[^A-Za-z0-9]/', '', $request->MaHocKy);
            $maDsdk = substr('DK_' . $sv->MaSV . '_' . $shortHk, 0, 20);
            $tc = (int)($sv->SoTinChiTichLuy ?? 0);
            $gpa = (float)($sv->DiemTichLuy ?? 0.0);
            $dieuKienText = "Tín chỉ: {$tc} | ĐTB: " . number_format($gpa, 2);

            \App\Models\DanhSachSVDuDieuKien::updateOrCreate(
                [
                    'MaSV' => $sv->MaSV,
                    'MaHocKy' => $request->MaHocKy,
                ],
                [
                    'MaDSDK' => $maDsdk,
                    'NgayXetDuyet' => now(),
                    'TrangThai' => $request->TrangThai,
                    'DieuKien' => $dieuKienText,
                    'GhiChu' => $request->GhiChu ? trim($request->GhiChu) : 'Tạo mới hồ sơ & cấp tài khoản vào học kỳ',
                ]
            );

            return redirect()->route('sinhvien.index', ['tab' => 'du_dieu_kien', 'MaHocKy' => $request->MaHocKy])
                ->with('success', "Đã tạo sinh viên {$sv->HoTen} ({$sv->MaSV}), cấp tài khoản đăng nhập (Mật khẩu: 123456) và thêm vào học kỳ {$request->MaHocKy} thành công!");

        } else {
            // Chọn sinh viên có sẵn trong hệ thống: TUYỆT ĐỐI KHÔNG TẠO MỚI TÀI KHOẢN (bảo toàn tài khoản & mật khẩu gốc)
            $request->validate([
                'MaSV' => 'required|exists:SinhVien,MaSV',
                'MaHocKy' => 'required|exists:HocKy,MaHocKy',
                'TrangThai' => 'required|in:Đủ điều kiện,Chưa đủ điều kiện',
                'GhiChu' => 'nullable|string|max:255',
            ], [
                'MaSV.required' => 'Vui lòng chọn sinh viên từ danh sách.',
                'MaSV.exists'   => 'Sinh viên được chọn không tồn tại trong hệ thống.',
            ]);

            $sv = SinhVien::findOrFail($request->MaSV);

            // Đảm bảo sinh viên có tài khoản nhưng không ghi đè tài khoản sẵn có
            if (!$sv->MaTK || !TaiKhoan::where('MaTK', $sv->MaTK)->exists()) {
                $tk = TaiKhoan::firstOrCreate(
                    ['TenDangNhap' => $sv->MaSV],
                    [
                        'MaTK'              => $sv->MaSV,
                        'TenDangNhap'       => $sv->MaSV,
                        'MatKhau'           => Hash::make('123456'),
                        'MaVaiTro'          => 'VT03',
                        'TrangThai'         => true,
                        'TrangThaiMatKhau'  => 'INITIAL',
                        'BatBuocDoiMatKhau' => true,
                        'SoLanDangNhapSai'  => 0,
                    ]
                );
                if ($sv->MaTK !== $tk->MaTK) {
                    $sv->update(['MaTK' => $tk->MaTK]);
                }
            }

            $shortHk = preg_replace('/[^A-Za-z0-9]/', '', $request->MaHocKy);
            $maDsdk = substr('DK_' . $sv->MaSV . '_' . $shortHk, 0, 20);

            $tc = (int)($sv->SoTinChiTichLuy ?? 0);
            $gpa = (float)($sv->DiemTichLuy ?? 0.0);
            $dieuKienText = "Tín chỉ: {$tc} | ĐTB: " . number_format($gpa, 2);

            \App\Models\DanhSachSVDuDieuKien::updateOrCreate(
                [
                    'MaSV' => $sv->MaSV,
                    'MaHocKy' => $request->MaHocKy,
                ],
                [
                    'MaDSDK' => $maDsdk,
                    'NgayXetDuyet' => now(),
                    'TrangThai' => $request->TrangThai,
                    'DieuKien' => $dieuKienText,
                    'GhiChu' => $request->GhiChu ? trim($request->GhiChu) : 'Thêm từ danh sách sinh viên trường',
                ]
            );

            return redirect()->route('sinhvien.index', ['tab' => 'du_dieu_kien', 'MaHocKy' => $request->MaHocKy])
                ->with('success', "Đã thêm sinh viên {$sv->HoTen} ({$sv->MaSV}) vào danh sách học kỳ {$request->MaHocKy} thành công! (Bảo toàn tài khoản hiện có)");
        }
    }

    public function dieuKienImportExcel(Request $request)
    {
        $request->validate([
            'MaHocKy' => 'required|exists:HocKy,MaHocKy',
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ], [
            'file.required' => 'Vui lòng chọn file CSV/Excel để import.',
            'file.mimes'    => 'Chỉ chấp nhận file định dạng .xlsx, .xls, .csv.',
        ]);

        $maHocKy = $request->MaHocKy;
        return $this->runImport($request, 'importSinhVienHocKy', [$maHocKy], "Danh sách sinh viên học kỳ {$maHocKy}");
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

        return redirect()->route('sinhvien.index', ['tab' => 'du_dieu_kien', 'MaHocKy' => $ds->MaHocKy])
            ->with('success', "Đã cập nhật trạng thái xét duyệt cho sinh viên {$ds->MaSV} thành '{$request->TrangThai}'!");
    }

    public function dieuKienCongBo(Request $request)
    {
        $request->validate([
            'MaHocKy' => 'required|exists:HocKy,MaHocKy',
        ]);

        $maHocKy = $request->MaHocKy;
        $hocKy = \App\Models\HocKy::find($maHocKy);
        $tenHocKy = $hocKy ? "{$hocKy->TenHocKy} ({$hocKy->NamHoc})" : $maHocKy;

        // 1. Cập nhật ghi chú đã công bố nếu chưa có
        \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $maHocKy)
            ->where(function($q) {
                $q->whereNull('GhiChu')
                  ->orWhere('GhiChu', 'NOT LIKE', '%Đã công bố%');
            })
            ->update([
                'GhiChu' => DB::raw("CONCAT(COALESCE(GhiChu, ''), ' - Đã công bố')")
            ]);

        // 2. Thống kê số lượng sinh viên
        $totalEvaluated = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $maHocKy)->count();
        $totalEligible  = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $maHocKy)->where('TrangThai', 'Đủ điều kiện')->count();
        $totalIneligible = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $maHocKy)->where('TrangThai', 'Chưa đủ điều kiện')->count();

        // 3. Tạo thông báo chính thức gửi đến toàn thể sinh viên
        $maGVu = Auth::user()->giaoVu->MaGVu ?? 'GVU01';
        $shortHk = preg_replace('/[^A-Za-z0-9]/', '', $maHocKy);
        $maTB = substr('TB_DK_' . $shortHk, 0, 20);

        $noiDungThongBao = "Khoa CNTT và Ban Giáo vụ trân trọng thông báo kết quả rà soát danh sách sinh viên đủ điều kiện thực hiện Khóa luận tốt nghiệp trong {$tenHocKy}:\n\n"
            . "• Tổng số sinh viên được rà soát: {$totalEvaluated} sinh viên\n"
            . "• Số lượng sinh viên ĐỦ ĐIỀU KIỆN: {$totalEligible} sinh viên (Đạt tích lũy >= 115 tín chỉ và ĐTB >= 2.0)\n"
            . "• Số lượng sinh viên CHƯA ĐỦ ĐIỀU KIỆN: {$totalIneligible} sinh viên\n\n"
            . "Sinh viên đủ điều kiện vui lòng chủ động tìm kiếm thành viên và tiến hành ghép nhóm (tối đa 3 SV/nhóm) tại phân hệ \"Nhóm Khóa Luận\", sau đó thực hiện đăng ký đề tài theo kế hoạch thời gian của Khoa.\n"
            . "Mọi thắc mắc hoặc khiếu nại về điều kiện khóa luận, sinh viên vui lòng liên hệ Văn phòng Giáo vụ Khoa trước thời hạn quy định.";

        \App\Models\ThongBao::updateOrCreate(
            ['MaThongBao' => $maTB],
            [
                'TieuDe'       => "Công bố Danh sách Sinh viên Đủ điều kiện làm Khóa luận Tốt nghiệp - {$tenHocKy}",
                'NoiDung'      => $noiDungThongBao,
                'LoaiThongBao' => 'Khóa luận',
                'DoiTuongNhan' => 'Sinh viên',
                'NgayTao'      => now(),
                'TrangThai'    => 'Đã phát hành',
                'MaGVu'        => $maGVu,
            ]
        );

        return redirect()->route('sinhvien.index', ['tab' => 'du_dieu_kien', 'MaHocKy' => $maHocKy])
            ->with('success', "Đã công bố danh sách chính thức ({$totalEligible} SV đủ điều kiện) và phát hành thông báo tới toàn thể sinh viên cho học kỳ {$tenHocKy}!");
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