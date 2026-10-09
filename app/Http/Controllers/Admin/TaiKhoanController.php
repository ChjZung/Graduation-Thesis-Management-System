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
use App\Models\BoMon;
use App\Models\Nganh;
use App\Models\Lop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TaiKhoanController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'sinhvien');
        if (!in_array($activeTab, ['sinhvien', 'giangvien', 'truongbomon', 'truongkhoa'])) {
            $activeTab = 'sinhvien';
        }

        // Đếm số lượng tài khoản theo từng tab
        $counts = [
            'sinhvien'    => TaiKhoan::where('MaVaiTro', 'VT03')->count(),
            'giangvien'   => TaiKhoan::where('MaVaiTro', 'VT02')->count(),
            'truongbomon' => TaiKhoan::where('MaVaiTro', 'VT04')->count(),
            'truongkhoa'  => TaiKhoan::where('MaVaiTro', 'VT05')->count(),
        ];

        // Khởi tạo query theo activeTab
        if ($activeTab === 'sinhvien') {
            $query = TaiKhoan::with(['sinhVien.lop.nganh.khoa'])
                ->where('MaVaiTro', 'VT03');

            // 1. Tìm kiếm (Username, MSSV, Họ tên, Email)
            if ($request->filled('search')) {
                $s = trim($request->search);
                $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
                $query->where(function ($q) use ($s, $sClean) {
                    $q->where('TenDangNhap', 'LIKE', "%{$s}%")
                      ->orWhereHas('sinhVien', function($sv) use ($s, $sClean) {
                          $sv->where('MaSV', 'LIKE', "%{$s}%")
                             ->orWhere('HoTen', 'LIKE', "%{$s}%")
                             ->orWhere('Email', 'LIKE', "%{$s}%");
                          if (!empty($sClean)) {
                              $sv->orWhere('MaSV', 'LIKE', "%{$sClean}%");
                          }
                      });
                    if (!empty($sClean)) {
                        $q->orWhere('TenDangNhap', 'LIKE', "%{$sClean}%");
                    }
                });
            }
            // 2. Khoa
            if ($request->filled('ma_khoa')) {
                $query->whereHas('sinhVien', fn($sv) => $sv->where('MaKhoa', $request->ma_khoa)
                    ->orWhereHas('lop.nganh', fn($ng) => $ng->where('MaKhoa', $request->ma_khoa)));
            }
            // 3. Ngành
            if ($request->filled('ma_nganh')) {
                $query->whereHas('sinhVien', fn($sv) => $sv->where('MaNganh', $request->ma_nganh)
                    ->orWhereHas('lop', fn($l) => $l->where('MaNganh', $request->ma_nganh)));
            }
            // 4. Lớp
            if ($request->filled('ma_lop')) {
                $query->whereHas('sinhVien', fn($sv) => $sv->where('MaLop', $request->ma_lop));
            }
            // 5. Khóa
            if ($request->filled('khoa_hoc')) {
                $query->whereHas('sinhVien', fn($sv) => $sv->where('KhoaHoc', $request->khoa_hoc));
            }
            // 6. Trạng thái
            if ($request->filled('trang_thai')) {
                $query->where('TrangThai', $request->trang_thai === '1');
            }

        } elseif ($activeTab === 'giangvien') {
            $query = TaiKhoan::with(['giangVien.boMon.khoa'])
                ->where('MaVaiTro', 'VT02');

            // 1. Tìm kiếm (Mã cán bộ, Họ tên, Email, Username)
            if ($request->filled('search')) {
                $s = trim($request->search);
                $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
                $query->where(function ($q) use ($s, $sClean) {
                    $q->where('TenDangNhap', 'LIKE', "%{$s}%")
                      ->orWhereHas('giangVien', function($g) use ($s, $sClean) {
                          $g->where('MaGV', 'LIKE', "%{$s}%")
                            ->orWhere('HoTen', 'LIKE', "%{$s}%")
                            ->orWhere('Email', 'LIKE', "%{$s}%");
                          if (!empty($sClean)) {
                              $g->orWhere('MaGV', 'LIKE', "%{$sClean}%");
                          }
                      });
                    if (!empty($sClean)) {
                        $q->orWhere('TenDangNhap', 'LIKE', "%{$sClean}%");
                    }
                });
            }
            // 2. Khoa
            if ($request->filled('ma_khoa')) {
                $query->whereHas('giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $request->ma_khoa));
            }
            // 3. Bộ môn
            if ($request->filled('ma_bomon')) {
                $query->whereHas('giangVien', fn($g) => $g->where('MaBoMon', $request->ma_bomon));
            }
            // 4. Trạng thái
            if ($request->filled('trang_thai')) {
                $query->where('TrangThai', $request->trang_thai === '1');
            }

        } elseif ($activeTab === 'truongbomon') {
            $query = TaiKhoan::with(['giangVien.boMon.khoa'])
                ->where('MaVaiTro', 'VT04');

            // 1. Tìm kiếm (Username, Tên, Mã CB)
            if ($request->filled('search')) {
                $s = trim($request->search);
                $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
                $query->where(function ($q) use ($s, $sClean) {
                    $q->where('TenDangNhap', 'LIKE', "%{$s}%")
                      ->orWhereHas('giangVien', function($g) use ($s, $sClean) {
                          $g->where('MaGV', 'LIKE', "%{$s}%")
                            ->orWhere('HoTen', 'LIKE', "%{$s}%");
                          if (!empty($sClean)) {
                              $g->orWhere('MaGV', 'LIKE', "%{$sClean}%");
                          }
                      });
                    if (!empty($sClean)) {
                        $q->orWhere('TenDangNhap', 'LIKE', "%{$sClean}%");
                    }
                });
            }
            // 2. Khoa
            if ($request->filled('ma_khoa')) {
                $query->where(function($q) use ($request) {
                    $q->whereHas('giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $request->ma_khoa))
                      ->orWhere('TenDangNhap', 'LIKE', "TBM_{$request->ma_khoa}_%");
                });
            }
            // 3. Bộ môn
            if ($request->filled('ma_bomon')) {
                $query->where(function($q) use ($request) {
                    $q->whereHas('giangVien', fn($g) => $g->where('MaBoMon', $request->ma_bomon))
                      ->orWhere('TenDangNhap', 'LIKE', "%_{$request->ma_bomon}_%");
                });
            }
            // 4. Trạng thái
            if ($request->filled('trang_thai')) {
                $query->where('TrangThai', $request->trang_thai === '1');
            }

        } else { // truongkhoa
            $query = TaiKhoan::with(['giangVien.boMon.khoa'])
                ->where('MaVaiTro', 'VT05');

            // 1. Tìm kiếm (Username, Tên, Mã CB)
            if ($request->filled('search')) {
                $s = trim($request->search);
                $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
                $query->where(function ($q) use ($s, $sClean) {
                    $q->where('TenDangNhap', 'LIKE', "%{$s}%")
                      ->orWhereHas('giangVien', function($g) use ($s, $sClean) {
                          $g->where('MaGV', 'LIKE', "%{$s}%")
                            ->orWhere('HoTen', 'LIKE', "%{$s}%");
                          if (!empty($sClean)) {
                              $g->orWhere('MaGV', 'LIKE', "%{$sClean}%");
                          }
                      });
                    if (!empty($sClean)) {
                        $q->orWhere('TenDangNhap', 'LIKE', "%{$sClean}%");
                    }
                });
            }
            // 2. Khoa
            if ($request->filled('ma_khoa')) {
                $query->where(function($q) use ($request) {
                    $q->whereHas('giangVien.boMon', fn($bm) => $bm->where('MaKhoa', $request->ma_khoa))
                      ->orWhere('TenDangNhap', 'LIKE', "TK_{$request->ma_khoa}_%");
                });
            }
            // 3. Trạng thái
            if ($request->filled('trang_thai')) {
                $query->where('TrangThai', $request->trang_thai === '1');
            }
        }

        $taiKhoans = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Danh mục phục vụ các bộ lọc & modal
        $khoas = Khoa::orderBy('TenKhoa')->get();
        $boMons = BoMon::with('khoa')->orderBy('TenBoMon')->get();
        $nganhs = \App\Models\Nganh::orderBy('TenNganh')->get();
        $lops = \App\Models\Lop::orderBy('TenLop')->get();
        $khoaHocs = SinhVien::whereNotNull('KhoaHoc')->distinct()->pluck('KhoaHoc')->sortDesc()->values();
        $giangViens = GiangVien::with(['boMon.khoa', 'taiKhoan'])->orderBy('HoTen')->get();

        return view('admin.taikhoan.index', compact(
            'taiKhoans',
            'activeTab',
            'counts',
            'khoas',
            'boMons',
            'nganhs',
            'lops',
            'khoaHocs',
            'giangViens'
        ));
    }

    /**
     * Tạo tài khoản cán bộ: Giảng viên, Trưởng bộ môn, Trưởng khoa
     * - Trưởng bộ môn & Trưởng khoa: Lấy thông tin từ danh sách giảng viên đã có, chỉ bổ nhiệm/thêm vai trò
     * - Giảng viên: Cấp tài khoản mới hoặc chọn từ danh sách giảng viên
     * Username được hệ thống sinh tự động theo quy tắc:
     * - Giảng viên: GV_{MA_KHOA}_{MA_BO_MON}_{MA_CAN_BO}
     * - Trưởng bộ môn: TBM_{MA_KHOA}_{MA_BO_MON}_{MA_CAN_BO} (Business rule: 1 BM chỉ 1 TBM active)
     * - Trưởng khoa: TK_{MA_KHOA}_{MA_CAN_BO} (Business rule: 1 Khoa chỉ 1 TK active)
     */
    public function storeCanBo(Request $request)
    {
        $loai = $request->input('loai_can_bo'); // GIANG_VIEN, TRUONG_BO_MON, TRUONG_KHOA

        if (!in_array($loai, ['GIANG_VIEN', 'TRUONG_BO_MON', 'TRUONG_KHOA'])) {
            return redirect()->back()->withErrors('Loại tài khoản cán bộ không hợp lệ.');
        }

        // Với Trưởng khoa & Trưởng bộ môn: Ưu tiên lấy thông tin từ Giảng viên có sẵn
        $maCanBo = strtoupper(trim(preg_replace('/[^A-Za-z0-9_]/', '', (string)$request->ma_can_bo)));
        $gv = GiangVien::with(['boMon.khoa', 'taiKhoan'])->find($maCanBo);

        if (in_array($loai, ['TRUONG_BO_MON', 'TRUONG_KHOA']) && !$gv && !$request->filled('ho_ten')) {
            return redirect()->back()
                ->withInput()
                ->withErrors('Vui lòng chọn giảng viên từ danh sách giảng viên của trường/khoa.');
        }

        if ($gv) {
            // Lấy tự động thông tin từ hồ sơ Giảng viên đã có trong hệ thống
            $hoTen   = $gv->HoTen;
            $email   = $gv->Email;
            $sdt     = $gv->SoDienThoai;
            $maBoMon = $gv->MaBoMon ?: ($request->filled('ma_bomon') ? strtoupper(trim($request->ma_bomon)) : null);
            $maKhoa  = $gv->boMon?->MaKhoa ?: ($request->filled('ma_khoa') ? strtoupper(trim($request->ma_khoa)) : null);
            $hocVi   = $gv->HocVi;
        } else {
            // Trường hợp nhập giảng viên mới
            $rules = [
                'ma_can_bo'     => 'required|string|max:30',
                'ho_ten'        => 'required|string|max:100',
                'email'         => 'required|email|max:100',
                'so_dien_thoai' => ['nullable', 'string', 'regex:/^0[0-9]{8,10}$/'],
                'ma_khoa'       => 'required|exists:Khoa,MaKhoa',
            ];
            if (in_array($loai, ['GIANG_VIEN', 'TRUONG_BO_MON'])) {
                $rules['ma_bomon'] = 'required|exists:BoMon,MaBoMon';
            }

            $request->validate($rules, [
                'ma_can_bo.required' => 'Vui lòng nhập Mã cán bộ.',
                'ho_ten.required'    => 'Vui lòng nhập Họ tên cán bộ.',
                'email.required'     => 'Vui lòng nhập Email cán bộ.',
                'ma_khoa.required'   => 'Vui lòng chọn Khoa.',
                'ma_bomon.required'  => 'Vui lòng chọn Bộ môn.',
            ]);

            $hoTen   = trim($request->ho_ten);
            $email   = trim($request->email);
            $sdt     = $request->filled('so_dien_thoai') ? trim($request->so_dien_thoai) : null;
            $maKhoa  = strtoupper(trim($request->ma_khoa));
            $maBoMon = $request->filled('ma_bomon') ? strtoupper(trim($request->ma_bomon)) : null;
            $hocVi   = $request->input('hoc_vi', 'Thạc sĩ');
        }

        $password = $request->filled('mat_khau') ? $request->mat_khau : '123456';
        $batBuocDoiMK = $request->has('bat_buoc_doi_mat_khau');

        // Business Rule & Sinh Tên Đăng Nhập
        if ($loai === 'TRUONG_BO_MON') {
            if (!$maBoMon) {
                return redirect()->back()->withInput()->withErrors('Giảng viên này chưa được phân bổ vào Bộ môn nào. Vui lòng phân bổ bộ môn cho giảng viên.');
            }
            if (!$maKhoa) {
                $maKhoa = BoMon::where('MaBoMon', $maBoMon)->value('MaKhoa') ?? 'CNTT';
            }

            // Rule: Mỗi Bộ môn chỉ có duy nhất một Trưởng bộ môn đang hoạt động
            $existingTBM = TaiKhoan::where('MaVaiTro', 'VT04')
                ->where('TrangThai', 1)
                ->where(function($q) use ($maBoMon) {
                    $q->whereHas('giangVien', fn($g) => $g->where('MaBoMon', $maBoMon))
                      ->orWhere('TenDangNhap', 'LIKE', "%_{$maBoMon}_%");
                })
                ->when($gv && $gv->MaTK, fn($q) => $q->where('MaTK', '!=', $gv->MaTK))
                ->first();

            if ($existingTBM) {
                $bm = BoMon::find($maBoMon);
                $tbmName = $existingTBM->giangVien?->HoTen ?? $existingTBM->TenDangNhap;
                return redirect()->back()
                    ->withInput()
                    ->withErrors("Bộ môn '{$bm?->TenBoMon}' hiện đã có Trưởng bộ môn đang hoạt động ({$tbmName}). Một Bộ môn chỉ có duy nhất một Trưởng bộ môn đang hoạt động!");
            }

            $username = "TBM_{$maKhoa}_{$maBoMon}_{$maCanBo}";
            $maVaiTro = 'VT04';
            $redirectTab = 'truongbomon';

        } elseif ($loai === 'TRUONG_KHOA') {
            if (!$maKhoa) {
                return redirect()->back()->withInput()->withErrors('Giảng viên này chưa thuộc Khoa nào. Vui lòng kiểm tra lại thông tin khoa.');
            }

            // Rule: Mỗi Khoa chỉ có duy nhất một Trưởng khoa đang hoạt động
            $existingTK = TaiKhoan::where('MaVaiTro', 'VT05')
                ->where('TrangThai', 1)
                ->where(function($q) use ($maKhoa) {
                    $q->whereHas('giangVien.boMon', fn($b) => $b->where('MaKhoa', $maKhoa))
                      ->orWhere('TenDangNhap', 'LIKE', "TK_{$maKhoa}_%");
                })
                ->when($gv && $gv->MaTK, fn($q) => $q->where('MaTK', '!=', $gv->MaTK))
                ->first();

            if ($existingTK) {
                $kh = Khoa::find($maKhoa);
                $tkName = $existingTK->giangVien?->HoTen ?? $existingTK->TenDangNhap;
                return redirect()->back()
                    ->withInput()
                    ->withErrors("Khoa '{$kh?->TenKhoa}' hiện đã có Trưởng khoa đang hoạt động ({$tkName}). Một Khoa chỉ có duy nhất một Trưởng khoa đang hoạt động!");
            }

            $username = "TK_{$maKhoa}_{$maCanBo}";
            $maVaiTro = 'VT05';
            $redirectTab = 'truongkhoa';

        } else { // GIANG_VIEN
            if (!$maKhoa && $maBoMon) {
                $maKhoa = BoMon::where('MaBoMon', $maBoMon)->value('MaKhoa') ?? 'CNTT';
            }
            $username = "GV_{$maKhoa}_{$maBoMon}_{$maCanBo}";
            $maVaiTro = 'VT02';
            $redirectTab = 'giangvien';
        }

        // Kiểm tra username trùng lặp nếu là tài khoản mới hoàn toàn
        if ((!$gv || !$gv->taiKhoan) && TaiKhoan::where('TenDangNhap', $username)->exists()) {
            return redirect()->back()
                ->withInput()
                ->withErrors("Tên đăng nhập '{$username}' đã tồn tại trong hệ thống. Vui lòng kiểm tra lại.");
        }

        try {
            DB::transaction(function () use ($username, $password, $maVaiTro, $batBuocDoiMK, $maCanBo, $hoTen, $email, $sdt, $maKhoa, $maBoMon, $hocVi, $loai, $gv, $request) {
                // 1. Cập nhật hoặc Khởi tạo Tài Khoản
                if ($gv && $gv->taiKhoan) {
                    $tk = $gv->taiKhoan;
                    $tkUpdate = [
                        'TenDangNhap'       => $username,
                        'MaVaiTro'          => $maVaiTro,
                        'TrangThai'         => true,
                        'BatBuocDoiMatKhau' => $batBuocDoiMK,
                    ];
                    if ($request->filled('mat_khau')) {
                        $tkUpdate['MatKhau'] = Hash::make($password);
                        $tkUpdate['TrangThaiMatKhau'] = 'INITIAL';
                    }
                    $tk->update($tkUpdate);
                } else {
                    $tk = TaiKhoan::create([
                        'MaTK'              => $username,
                        'TenDangNhap'       => $username,
                        'MatKhau'           => Hash::make($password),
                        'MaVaiTro'          => $maVaiTro,
                        'TrangThai'         => true,
                        'TrangThaiMatKhau'  => 'INITIAL',
                        'BatBuocDoiMatKhau' => $batBuocDoiMK,
                        'SoLanDangNhapSai'  => 0,
                    ]);
                }

                // 2. Tạo hoặc Cập nhật Giảng viên
                $targetBM = $maBoMon;
                if (!$targetBM) {
                    $targetBM = BoMon::where('MaKhoa', $maKhoa)->value('MaBoMon');
                }

                if ($gv) {
                    $gv->update([
                        'MaTK'        => $tk->MaTK,
                        'MaBoMon'     => $targetBM ?: $gv->MaBoMon,
                        'TrangThai'   => 'Đang công tác',
                    ]);
                } else {
                    GiangVien::create([
                        'MaGV'        => $maCanBo,
                        'MaTK'        => $tk->MaTK,
                        'HoTen'       => $hoTen,
                        'Email'       => $email,
                        'SoDienThoai' => $sdt,
                        'HocVi'       => $hocVi ?: 'Thạc sĩ',
                        'MaBoMon'     => $targetBM,
                        'TrangThai'   => 'Đang công tác',
                    ]);
                }

                // 3. Cập nhật tên chức vụ vào bảng Khoa hoặc Bộ môn
                if ($loai === 'TRUONG_KHOA') {
                    Khoa::where('MaKhoa', $maKhoa)->update(['TruongKhoa' => $hoTen]);
                } elseif ($loai === 'TRUONG_BO_MON') {
                    BoMon::where('MaBoMon', $maBoMon)->update(['TruongBoMon' => $hoTen]);
                }
            });

            $roleTitle = match($loai) {
                'TRUONG_KHOA'   => 'Trưởng khoa',
                'TRUONG_BO_MON' => 'Trưởng bộ môn',
                default         => 'Giảng viên',
            };

            return redirect()->route('admin.taikhoan.index', ['tab' => $redirectTab])
                ->with('success', "Đã gán vai trò {$roleTitle} cho giảng viên '{$hoTen}' thành công! Tên đăng nhập: [{$username}]");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors("Lỗi khi tạo tài khoản cán bộ: " . $e->getMessage());
        }
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
