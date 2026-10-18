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
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

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
        $querySv = SinhVien::select('SinhVien.*')
            ->distinct()
            ->with(['lop.nganh.khoa', 'taiKhoan', 'nhoms']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $querySv->where(function ($q) use ($s, $sClean) {
                $q->where('SinhVien.HoTen', 'LIKE', "%{$s}%")
                  ->orWhere('SinhVien.Email', 'LIKE', "%{$s}%")
                  ->orWhere('SinhVien.MaSV', 'LIKE', "%{$s}%")
                  ->orWhere('SinhVien.SoDienThoai', 'LIKE', "%{$s}%");
                if (!empty($sClean)) {
                    $q->orWhere('SinhVien.MaSV', 'LIKE', "%{$sClean}%")
                      ->orWhere('SinhVien.SoDienThoai', 'LIKE', "%{$sClean}%");
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
            $querySv->where('SinhVien.MaLop', $request->MaLop);
        }

        if ($request->filled('MaHocKy_filter')) {
            $hkFilter = $request->MaHocKy_filter;
            $querySv->where(function($q) use ($hkFilter) {
                $q->whereHas('danhSachSVDuDieuKiens', fn($d) => $d->where('MaHocKy', $hkFilter))
                  ->orWhereHas('nhoms', fn($n) => $n->where('MaHocKy', $hkFilter));
            });
        }

        if ($request->filled('trang_thai_hoc_tap')) {
            $querySv->where('SinhVien.TrangThai', $request->trang_thai_hoc_tap);
        }

        if ($request->filled('dieu_kien')) {
            if ($request->dieu_kien === 'du') {
                $querySv->where('SinhVien.SoTinChiTichLuy', '>=', 115)->where('SinhVien.DiemTichLuy', '>=', 2.0);
            } elseif ($request->dieu_kien === 'chua') {
                $querySv->where(function($q) {
                    $q->where('SinhVien.SoTinChiTichLuy', '<', 115)
                      ->orWhereNull('SinhVien.SoTinChiTichLuy')
                      ->orWhere('SinhVien.DiemTichLuy', '<', 2.0)
                      ->orWhereNull('SinhVien.DiemTichLuy');
                });
            }
        }

        $totalSV = SinhVien::distinct('MaSV')->count('MaSV');
        $duDkSV = SinhVien::where('SoTinChiTichLuy', '>=', 115)->where('DiemTichLuy', '>=', 2.0)->distinct('MaSV')->count('MaSV');
        $thieuDkSV = $totalSV - $duDkSV;
        $activeSV = SinhVien::whereHas('taiKhoan', fn($tk) => $tk->where('TrangThai', true))->distinct('MaSV')->count('MaSV');

        $statsSv = [
            'total'     => $totalSV,
            'du_dk'     => $duDkSV,
            'pct_du_dk' => $totalSV > 0 ? round(($duDkSV / $totalSV) * 100, 1) : 0,
            'thieu_dk'  => $thieuDkSV,
            'active'    => $activeSV,
        ];

        $sinhviens = $querySv->orderBy('SinhVien.MaSV')->paginate(15, ['*'], 'page_sv')->withQueryString();

        // ══════════════════════════════════════════════════════════════
        // 2. DATA CHO TAB 2: DANH SÁCH SV ĐỦ ĐIỀU KIỆN (THEO HỌC KỲ)
        // ══════════════════════════════════════════════════════════════
        $queryDk = \App\Models\DanhSachSVDuDieuKien::select('DanhSachSVDuDieuKien.*')
            ->distinct()
            ->with(['sinhVien.lop.nganh.khoa', 'sinhVien.nhoms', 'hocKy'])
            ->where('DanhSachSVDuDieuKien.MaHocKy', $selectedHocKy);

        if ($request->filled('search_dk')) {
            $s = trim($request->search_dk);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $queryDk->where(function ($rootQ) use ($s, $sClean) {
                $rootQ->where('DanhSachSVDuDieuKien.MaSV', 'LIKE', "%{$s}%");
                if (!empty($sClean)) {
                    $rootQ->orWhere('DanhSachSVDuDieuKien.MaSV', 'LIKE', "%{$sClean}%");
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
            $queryDk->where('DanhSachSVDuDieuKien.TrangThai', $request->trang_thai_dk);
        }

        $dsDuDieuKien = $queryDk->orderBy('DanhSachSVDuDieuKien.NgayXetDuyet', 'desc')
            ->orderBy('DanhSachSVDuDieuKien.MaSV', 'asc')
            ->paginate(15, ['*'], 'page_dk')
            ->withQueryString();

        $totalEvaluated = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $selectedHocKy)->distinct('MaSV')->count('MaSV');
        $totalEligible  = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $selectedHocKy)->where('TrangThai', 'Đủ điều kiện')->distinct('MaSV')->count('MaSV');
        $totalIneligible = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $selectedHocKy)->where('TrangThai', 'Chưa đủ điều kiện')->distinct('MaSV')->count('MaSV');
        $publishedCount = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $selectedHocKy)->where('GhiChu', 'LIKE', '%Công bố%')->count();

        $statsDk = [
            'total_evaluated' => $totalEvaluated,
            'total_eligible'  => $totalEligible,
            'total_ineligible'=> $totalIneligible,
            'is_published'    => $publishedCount > 0,
        ];

        $allSinhViens = SinhVien::select('MaSV', 'HoTen', 'MaLop')->distinct()->orderBy('HoTen')->get();

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
            'TenDangNhap' => ['required', 'string', 'max:20', 'regex:/^(SV\d{8}|\d{10})$/', 'unique:TaiKhoan,TenDangNhap', 'unique:SinhVien,MaSV'],
            'HoTen' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\p{L}\s]+$/u'],
            'MaLop' => 'required|exists:Lop,MaLop',
            'Email' => 'required|email|max:100|unique:SinhVien,Email',
            'SoDienThoai' => ['nullable', 'string', 'regex:/^0[0-9]{9}$/', 'unique:SinhVien,SoDienThoai'],
            'SoTinChiTichLuy' => 'nullable|integer|min:0',
            'DiemTichLuy' => 'nullable|numeric|min:0|max:4',
            'TrangThai' => 'nullable|string',
        ], [
            'TenDangNhap.required' => 'Vui lòng nhập MSSV.',
            'TenDangNhap.regex'    => 'Mã sinh viên phải có định dạng chuẩn (SV + 8 số hoặc 10 chữ số MSSV).',
            'TenDangNhap.unique'   => 'MSSV / Tên đăng nhập đã tồn tại trong hệ thống.',
            'HoTen.required'       => 'Vui lòng nhập họ và tên sinh viên.',
            'HoTen.min'            => 'Họ và tên sinh viên phải có tối thiểu 2 ký tự.',
            'HoTen.max'            => 'Họ và tên sinh viên không được vượt quá 100 ký tự.',
            'HoTen.regex'          => 'Họ và tên chỉ được chứa chữ cái và khoảng trắng, không chứa số hoặc ký tự đặc biệt.',
            'MaLop.required'       => 'Vui lòng chọn lớp học.',
            'Email.required'       => 'Vui lòng nhập email.',
            'Email.email'          => 'Định dạng email không hợp lệ.',
            'Email.unique'         => 'Email đã được sử dụng.',
            'SoDienThoai.regex'    => 'Số điện thoại phải gồm đúng 10 chữ số và bắt đầu bằng số 0.',
            'SoDienThoai.unique'   => 'Số điện thoại đã được sử dụng.',
        ]);

        $mssv = trim($request->TenDangNhap);
        $lop = Lop::with('nganh')->findOrFail($request->MaLop);
        $initialPassword = \Illuminate\Support\Str::password(10, true, true, false, false);

        DB::transaction(function () use ($request, $mssv, $lop, $initialPassword) {
            $tk = TaiKhoan::create([
                'MaTK'              => $mssv,
                'TenDangNhap'       => $mssv,
                'MatKhau'           => Hash::make($initialPassword),
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

        return redirect()->route('sinhvien.index')->with('success', "Thêm sinh viên '{$request->HoTen}' (MSSV: {$mssv}) thành công! Mật khẩu khởi tạo tạm thời: [{$initialPassword}]. Người dùng bắt buộc đổi mật khẩu khi đăng nhập lần đầu.");
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
            'HoTen' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\p{L}\s]+$/u'],
            'MaLop' => 'required|exists:Lop,MaLop',
            'Email' => 'required|email|max:100|unique:SinhVien,Email,' . $id . ',MaSV',
            'SoDienThoai' => ['nullable', 'string', 'regex:/^0[0-9]{9}$/', 'unique:SinhVien,SoDienThoai,' . $id . ',MaSV']
        ], [
            'HoTen.required'     => 'Vui lòng nhập họ và tên sinh viên.',
            'HoTen.min'          => 'Họ và tên sinh viên phải có tối thiểu 2 ký tự.',
            'HoTen.max'          => 'Họ và tên sinh viên không được vượt quá 100 ký tự.',
            'HoTen.regex'        => 'Họ và tên chỉ được chứa chữ cái và khoảng trắng, không chứa số hoặc ký tự đặc biệt.',
            'MaLop.required'     => 'Vui lòng chọn lớp.',
            'Email.required'     => 'Vui lòng nhập email.',
            'Email.email'        => 'Định dạng email không hợp lệ.',
            'Email.unique'       => 'Email đã được sử dụng.',
            'SoDienThoai.unique' => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex'  => 'Số điện thoại phải gồm đúng 10 chữ số và bắt đầu bằng số 0.',
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

            $initialPassword = \Illuminate\Support\Str::password(10, true, true, false, false);
            $sv = DB::transaction(function () use ($request, $mssv, $lop, $initialPassword) {
                // Tự động tạo tài khoản Portal cho sinh viên mới với mật khẩu ngẫu nhiên
                $tk = TaiKhoan::create([
                    'MaTK'              => $mssv,
                    'TenDangNhap'       => $mssv,
                    'MatKhau'           => Hash::make($initialPassword),
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
                ->with('success', "Đã tạo sinh viên {$sv->HoTen} ({$sv->MaSV}), cấp tài khoản đăng nhập (Mật khẩu tạm thời: [{$initialPassword}]) và thêm vào học kỳ {$request->MaHocKy} thành công!");

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
                $tempPassExisting = \Illuminate\Support\Str::password(10, true, true, false, false);
                $tk = TaiKhoan::firstOrCreate(
                    ['TenDangNhap' => $sv->MaSV],
                    [
                        'MaTK'              => $sv->MaSV,
                        'TenDangNhap'       => $sv->MaSV,
                        'MatKhau'           => Hash::make($tempPassExisting),
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
                    'GhiChu' => $request->filled('GhiChu') ? \App\Helpers\HtmlHelper::clean($request->GhiChu) : 'Thêm từ danh sách sinh viên trường',
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
            'GhiChu' => 'nullable|string',
        ]);

        $ds = \App\Models\DanhSachSVDuDieuKien::findOrFail($request->MaDSDK);
        $ds->update([
            'TrangThai' => $request->TrangThai,
            'GhiChu' => $request->filled('GhiChu') ? \App\Helpers\HtmlHelper::clean($request->GhiChu) : 'Cập nhật thủ công bởi Admin',
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
        $totalEvaluated = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $maHocKy)->distinct('MaSV')->count('MaSV');
        $totalEligible  = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $maHocKy)->where('TrangThai', 'Đủ điều kiện')->distinct('MaSV')->count('MaSV');
        $totalIneligible = \App\Models\DanhSachSVDuDieuKien::where('MaHocKy', $maHocKy)->where('TrangThai', 'Chưa đủ điều kiện')->distinct('MaSV')->count('MaSV');

        // 3. Tự động xuất file Excel danh sách sinh viên đủ điều kiện để đính kèm vào thông báo
        $fileDinhKem = $this->generateDuDieuKienExcelFile($maHocKy);

        // 4. Tạo thông báo chính thức gửi đến toàn thể sinh viên
        $maGVu = Auth::user()->giaoVu->MaGVu ?? 'GVU01';
        $shortHk = preg_replace('/[^A-Za-z0-9]/', '', $maHocKy);
        $maTB = substr('TB_DK_' . $shortHk, 0, 20);

        $noiDungThongBao = "Khoa CNTT và Ban Giáo vụ trân trọng thông báo kết quả rà soát danh sách sinh viên đủ điều kiện thực hiện Khóa luận tốt nghiệp trong {$tenHocKy}:\n\n"
            . "• Tổng số sinh viên được rà soát: {$totalEvaluated} sinh viên\n"
            . "• Số lượng sinh viên ĐỦ ĐIỀU KIỆN: {$totalEligible} sinh viên (Đạt tích lũy >= 115 tín chỉ và ĐTB >= 2.0)\n"
            . "• Số lượng sinh viên CHƯA ĐỦ ĐIỀU KIỆN: {$totalIneligible} sinh viên\n\n"
            . "Danh sách chi tiết được đính kèm trong tệp bảng tính Excel chính thức kèm theo thông báo này. "
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
                'FileDinhKem'  => $fileDinhKem,
            ]
        );

        return redirect()->route('sinhvien.index', ['tab' => 'du_dieu_kien', 'MaHocKy' => $maHocKy])
            ->with('success', "Đã công bố danh sách chính thức ({$totalEligible} SV đủ điều kiện), xuất file Excel đính kèm và phát hành thông báo thành công!");
    }

    /**
     * Tạo file Excel danh sách sinh viên đủ điều kiện và lưu vào storage public announcements
     */
    public function generateDuDieuKienExcelFile(string $maHocKy): ?string
    {
        $hocKy = \App\Models\HocKy::find($maHocKy);
        $tenHocKy = $hocKy ? "{$hocKy->TenHocKy} ({$hocKy->NamHoc})" : $maHocKy;
        $shortHk = preg_replace('/[^A-Za-z0-9]/', '', $maHocKy);

        $ds = \App\Models\DanhSachSVDuDieuKien::select('DanhSachSVDuDieuKien.*')
            ->distinct()
            ->with(['sinhVien.lop.nganh', 'hocKy'])
            ->where('DanhSachSVDuDieuKien.MaHocKy', $maHocKy)
            ->orderBy('DanhSachSVDuDieuKien.MaSV')
            ->get();

        if ($ds->isEmpty()) {
            return null;
        }

        // Tạo thư mục announcements nếu chưa tồn tại
        if (!Storage::disk('public')->exists('announcements')) {
            Storage::disk('public')->makeDirectory('announcements');
        }

        $fileName = 'DanhSach_SV_DuDieuKien_' . $shortHk . '.xlsx';
        $relativeStoragePath = 'announcements/' . $fileName;
        $fullPath = storage_path('app/public/' . $relativeStoragePath);

        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('DS_DuDieuKien');

            // Banner tiêu đề trường / khoa
            $sheet->setCellValue('A1', 'TRƯỜNG ĐẠI HỌC CÔNG THƯƠNG TP. HỒ CHÍ MINH - KHOA CÔNG NGHỆ THÔNG TIN');
            $sheet->mergeCells('A1:I1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('1E3A8A');

            // Tiêu đề báo cáo
            $sheet->setCellValue('A2', "DANH SÁCH SINH VIÊN ĐỦ ĐIỀU KIỆN LÀM KHÓA LUẬN TỐT NGHIỆP - {$tenHocKy}");
            $sheet->mergeCells('A2:I2');
            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('1E40AF');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Thời gian và tiêu chuẩn
            $sheet->setCellValue('A3', 'Ngày công bố: ' . now()->format('d/m/Y H:i') . ' | Tiêu chí: Tích lũy >= 115 tín chỉ, Điểm TB tích lũy >= 2.0');
            $sheet->mergeCells('A3:I3');
            $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('64748B');
            $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Header bảng
            $headers = ['STT', 'Mã Sinh Viên', 'Họ Và Tên', 'Lớp', 'Ngành Học', 'Số Tín Chỉ TL', 'ĐTB Tích Lũy', 'Trạng Thái', 'Tiêu Chí & Ghi Chú'];
            $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
            foreach ($headers as $i => $h) {
                $sheet->setCellValue($cols[$i] . '5', $h);
            }

            $sheet->getStyle('A5:I5')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E40AF'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                ],
            ]);
            $sheet->getRowDimension(5)->setRowHeight(28);

            // Dữ liệu từng sinh viên
            $row = 6;
            foreach ($ds as $idx => $item) {
                $sv = $item->sinhVien;
                $sheet->setCellValue('A' . $row, $idx + 1);
                $sheet->setCellValueExplicit('B' . $row, $item->MaSV, DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $row, $sv->HoTen ?? '');
                $sheet->setCellValue('D' . $row, $sv->lop->TenLop ?? '');
                $sheet->setCellValue('E' . $row, $sv->lop->nganh->TenNganh ?? '');
                $sheet->setCellValue('F' . $row, (int)($sv->SoTinChiTichLuy ?? 0));
                $sheet->setCellValue('G' . $row, number_format((float)($sv->DiemTichLuy ?? 0), 2));
                $sheet->setCellValue('H' . $row, $item->TrangThai);
                $sheet->setCellValue('I' . $row, trim(($item->DieuKien ?? '') . ' (' . ($item->GhiChu ?? '') . ')'));

                // Căn lề
                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Màu sắc trạng thái
                if ($item->TrangThai === 'Đủ điều kiện') {
                    $sheet->getStyle('H' . $row)->getFont()->setBold(true)->getColor()->setRGB('15803D');
                    $sheet->getStyle('H' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCFCE7');
                } else {
                    $sheet->getStyle('H' . $row)->getFont()->setBold(true)->getColor()->setRGB('B91C1C');
                    $sheet->getStyle('H' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEE2E2');
                }

                $sheet->getStyle("A{$row}:I{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
                $sheet->getRowDimension($row)->setRowHeight(22);
                $row++;
            }

            // Tự động căn chỉnh độ rộng cột
            foreach ($cols as $c) {
                $sheet->getColumnDimension($c)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($fullPath);

            return $relativeStoragePath;
        } catch (\Throwable $e) {
            \Log::error('Lỗi khi xuất file Excel đủ điều kiện: ' . $e->getMessage());

            // Fallback tạo file CSV có BOM
            $csvFileName = 'DanhSach_SV_DuDieuKien_' . $shortHk . '.csv';
            $csvRelPath = 'announcements/' . $csvFileName;
            $csvFullPath = storage_path('app/public/' . $csvRelPath);

            $file = fopen($csvFullPath, 'w');
            if ($file) {
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
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
                return $csvRelPath;
            }

            return null;
        }
    }

    public function dieuKienExport(Request $request)
    {
        $hocKys = \App\Models\HocKy::orderBy('MaHocKy', 'desc')->get();
        $maHocKy = $request->get('MaHocKy', $hocKys->first()->MaHocKy ?? '');

        // Ưu tiên xuất file Excel .xlsx chuẩn
        $excelPath = $this->generateDuDieuKienExcelFile($maHocKy);
        if ($excelPath && file_exists(storage_path('app/public/' . $excelPath))) {
            return response()->download(storage_path('app/public/' . $excelPath), 'Danh_Sach_SV_Du_Dieu_Kien_' . ($maHocKy ?: 'All') . '.xlsx');
        }

        // Fallback CSV
        $ds = \App\Models\DanhSachSVDuDieuKien::select('DanhSachSVDuDieuKien.*')
            ->distinct()
            ->with(['sinhVien.lop.nganh', 'hocKy'])
            ->when($maHocKy, fn($q) => $q->where('DanhSachSVDuDieuKien.MaHocKy', $maHocKy))
            ->orderBy('DanhSachSVDuDieuKien.MaSV')
            ->get();

        $filename = 'Danh_Sach_SV_Du_Dieu_Kien_' . ($maHocKy ?: 'All') . '.csv';
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