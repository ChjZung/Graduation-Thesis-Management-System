<?php

namespace App\Http\Controllers;

use App\Helpers\IdGenerator;
use App\Http\Traits\HandlesExcelImport;
use App\Models\GiangVien;
use App\Models\BoMon;
use App\Models\TaiKhoan;
use App\Models\ChiTieuHuongDan;
use App\Services\ChiTieuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class GiangVienController extends Controller
{
    use HandlesExcelImport;

    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'danhsach');
        $hocKys = \App\Models\HocKy::orderBy('MaHocKy', 'desc')->get();
        $currentHk = \App\Models\HocKy::where('TrangThai', 'Đang diễn ra')->first() ?? $hocKys->first();
        $selectedHocKy = $request->get('MaHocKy', $request->get('MaHocKy_filter', $currentHk?->MaHocKy ?? ''));
        $scopedHocKy = $selectedHocKy;

        // Lấy danh sách tên Trưởng khoa và Trưởng bộ môn trong hệ thống
        $truongKhoaNames = \App\Models\Khoa::whereNotNull('TruongKhoa')->pluck('TruongKhoa')->filter()->toArray();
        $truongBoMonNames = BoMon::whereNotNull('TruongBoMon')->pluck('TruongBoMon')->filter()->toArray();
        $allChucVuNames = array_unique(array_merge($truongKhoaNames, $truongBoMonNames));

        // ══════════════════════════════════════════════════════════════
        // 1. DATA CHO TAB 1: DANH MỤC GIẢNG VIÊN (TOÀN TRƯỜNG)
        // ══════════════════════════════════════════════════════════════
        $query = GiangVien::with(['boMon.khoa', 'taiKhoan']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $query->where(function ($q) use ($s, $sClean) {
                $q->where('HoTen', 'LIKE', "%{$s}%")
                  ->orWhere('Email', 'LIKE', "%{$s}%")
                  ->orWhere('MaGV', 'LIKE', "%{$s}%")
                  ->orWhereHas('taiKhoan', fn($t) => $t->where('TenDangNhap', 'LIKE', "%{$s}%"));
                if (!empty($sClean)) {
                    $q->orWhere('MaGV', 'LIKE', "%{$sClean}%")
                      ->orWhereHas('taiKhoan', fn($t) => $t->where('TenDangNhap', 'LIKE', "%{$sClean}%"));
                }
            });
        }

        if ($request->filled('MaBoMon')) {
            $query->where('MaBoMon', $request->MaBoMon);
        }

        // Bộ lọc theo Chức vụ / Vai trò
        if ($request->filled('ChucVu')) {
            $cv = $request->ChucVu;
            if ($cv === 'TK') {
                $query->where(function($q) use ($truongKhoaNames) {
                    $q->whereIn('HoTen', $truongKhoaNames)
                      ->orWhereHas('taiKhoan', fn($t) => $t->where('MaVaiTro', 'VT05')->orWhere('TenDangNhap', 'LIKE', 'TK_%'));
                });
            } elseif ($cv === 'TBM') {
                $query->where(function($q) use ($truongBoMonNames) {
                    $q->whereIn('HoTen', $truongBoMonNames)
                      ->orWhereHas('taiKhoan', fn($t) => $t->where('MaVaiTro', 'VT04')->orWhere('TenDangNhap', 'LIKE', 'TBM_%'));
                });
            } elseif ($cv === 'co_chuc_vu') {
                $query->where(function($q) use ($allChucVuNames) {
                    $q->whereIn('HoTen', $allChucVuNames)
                      ->orWhereHas('taiKhoan', fn($t) => $t->whereIn('MaVaiTro', ['VT04', 'VT05'])->orWhere('TenDangNhap', 'LIKE', 'TK_%')->orWhere('TenDangNhap', 'LIKE', 'TBM_%'));
                });
            } elseif ($cv === 'GV') {
                $query->whereNotIn('HoTen', $allChucVuNames)
                      ->whereDoesntHave('taiKhoan', fn($t) => $t->whereIn('MaVaiTro', ['VT04', 'VT05'])->orWhere('TenDangNhap', 'LIKE', 'TK_%')->orWhere('TenDangNhap', 'LIKE', 'TBM_%'));
            }
        }

        // Lọc theo Học kỳ nếu có MaHocKy_filter
        if ($request->filled('MaHocKy_filter')) {
            $hkFilter = $request->MaHocKy_filter;
            $query->where(function($q) use ($hkFilter) {
                $q->whereHas('deTais', fn($dt) => $dt->where('MaHocKy', $hkFilter))
                  ->orWhereHas('phanCongPhanBiens.deTai', fn($dt) => $dt->where('MaHocKy', $hkFilter))
                  ->orWhereHas('thanhVienHoiDongs.hoiDong', fn($hd) => $hd->where('MaHocKy', $hkFilter))
                  ->orWhereHas('chiTieuHuongDans', fn($ct) => $ct->where('MaHocKy', $hkFilter));
            });
        }

        // Bộ lọc Tình trạng hướng dẫn trong Tab 1
        if ($request->filled('tinh_trang_hd')) {
            $statusHd = $request->tinh_trang_hd;
            if ($statusHd === 'co_hd') {
                $query->whereHas('deTais.phieuDangKys', function($q) use ($scopedHocKy) {
                    $q->where('TrangThai', 'Đã duyệt');
                    if ($scopedHocKy) {
                        $q->whereHas('deTai', fn($dq) => $dq->where('MaHocKy', $scopedHocKy));
                    }
                });
            } elseif ($statusHd === 'dat_dinh_muc') {
                $allGvList = GiangVien::pluck('MaGV')->toArray();
                $allStatsMap = ChiTieuService::getChiTieuStatsBatch($allGvList, $scopedHocKy);
                $fullGvIds = [];
                foreach ($allStatsMap as $mgv => $st) {
                    if ($st['is_full']) {
                        $fullGvIds[] = $mgv;
                    }
                }
                $query->whereIn('MaGV', $fullGvIds);
            } elseif ($statusHd === 'chua_hd') {
                $query->whereDoesntHave('deTais.phieuDangKys', function($q) use ($scopedHocKy) {
                    $q->where('TrangThai', 'Đã duyệt');
                    if ($scopedHocKy) {
                        $q->whereHas('deTai', fn($dq) => $dq->where('MaHocKy', $scopedHocKy));
                    }
                });
            }
        }

        $giangviens = $query->withCount([
            'deTais' => function($q) use ($scopedHocKy) {
                $q->whereIn('TrangThai', ['Đã công bố', 'Trưởng khoa đã duyệt', 'Đã duyệt', 'Đã đăng ký', 'Hoàn thành']);
                if ($scopedHocKy) {
                    $q->where('MaHocKy', $scopedHocKy);
                }
            }
        ])->orderBy('MaGV')->paginate(15, ['*'], 'page_gv')->withQueryString();

        $councilAssignmentService = app(\App\Services\CouncilAssignmentService::class);
        $giangviens->getCollection()->transform(function($gv) use ($councilAssignmentService, $scopedHocKy) {
            $gv->hoidong_stats = $councilAssignmentService->thongKeGiangVienHoiDong($gv->MaGV, $scopedHocKy);
            return $gv;
        });

        $pageGvIds = $giangviens->pluck('MaGV')->toArray();
        $chiTieuStatsBatch = ChiTieuService::getChiTieuStatsBatch($pageGvIds, $scopedHocKy);
        foreach ($giangviens as $gv) {
            $gv->chi_tieu_stats = $chiTieuStatsBatch[$gv->MaGV] ?? [
                'tong'       => 5,
                'da_dung'    => 0,
                'con_lai'    => 5,
                'tinh_trang' => 'con_chi_tieu',
                'is_full'    => false,
                'label'      => '0/5',
            ];
        }

        // Thống kê toàn trường cho Tab 1
        $totalGV = GiangVien::count();
        $statsGv = [
            'total'      => $totalGV,
            'hoc_vi_cao' => GiangVien::whereIn('HocVi', ['Tiến sĩ', 'Phó Giáo sư', 'Giáo sư', 'TS', 'PGS', 'GS'])->count(),
            'chuc_vu'    => GiangVien::where(function($q) use ($allChucVuNames) {
                $q->whereIn('HoTen', $allChucVuNames)
                  ->orWhereHas('taiKhoan', fn($t) => $t->whereIn('MaVaiTro', ['VT04', 'VT05'])->orWhere('TenDangNhap', 'LIKE', 'TK_%')->orWhere('TenDangNhap', 'LIKE', 'TBM_%'));
            })->count(),
            'active'     => GiangVien::whereHas('taiKhoan', fn($tk) => $tk->where('TrangThai', true))->count(),
        ];

        // ══════════════════════════════════════════════════════════════
        // 2. DATA CHO TAB 2: QUẢN LÝ & PHÂN BỔ GV THEO HỌC KỲ
        // ══════════════════════════════════════════════════════════════
        $queryHk = GiangVien::with(['boMon.khoa', 'taiKhoan']);

        // Mặc định: Giảng viên tham gia học kỳ (có chỉ tiêu hoặc đề tài trong học kỳ)
        $phamViHk = $request->get('pham_vi', 'tham_gia');
        if ($phamViHk !== 'tat_ca') {
            $queryHk->where(function($q) use ($selectedHocKy) {
                $q->whereHas('chiTieuHuongDans', fn($ct) => $ct->where('MaHocKy', $selectedHocKy))
                  ->orWhereHas('deTais', fn($dt) => $dt->where('MaHocKy', $selectedHocKy));
            });
        }

        if ($request->filled('search_hk')) {
            $s = trim($request->search_hk);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $queryHk->where(function ($q) use ($s, $sClean) {
                $q->where('HoTen', 'LIKE', "%{$s}%")
                  ->orWhere('Email', 'LIKE', "%{$s}%")
                  ->orWhere('MaGV', 'LIKE', "%{$s}%")
                  ->orWhereHas('taiKhoan', fn($t) => $t->where('TenDangNhap', 'LIKE', "%{$s}%"));
                if (!empty($sClean)) {
                    $q->orWhere('MaGV', 'LIKE', "%{$sClean}%")
                      ->orWhereHas('taiKhoan', fn($t) => $t->where('TenDangNhap', 'LIKE', "%{$sClean}%"));
                }
            });
        }

        if ($request->filled('MaBoMon_hk')) {
            $queryHk->where('MaBoMon', $request->MaBoMon_hk);
        }

        if ($request->filled('tinh_trang_hd_hk')) {
            $statusHdHk = $request->tinh_trang_hd_hk;
            if ($statusHdHk === 'co_hd') {
                $queryHk->whereHas('deTais.phieuDangKys', function($q) use ($selectedHocKy) {
                    $q->where('TrangThai', 'Đã duyệt');
                    if ($selectedHocKy) {
                        $q->whereHas('deTai', fn($dq) => $dq->where('MaHocKy', $selectedHocKy));
                    }
                });
            } elseif ($statusHdHk === 'dat_dinh_muc') {
                $allGvList = GiangVien::pluck('MaGV')->toArray();
                $allStatsMap = ChiTieuService::getChiTieuStatsBatch($allGvList, $selectedHocKy);
                $fullGvIds = [];
                foreach ($allStatsMap as $mgv => $st) {
                    if ($st['is_full']) {
                        $fullGvIds[] = $mgv;
                    }
                }
                $queryHk->whereIn('MaGV', $fullGvIds);
            } elseif ($statusHdHk === 'chua_hd') {
                $queryHk->whereDoesntHave('deTais.phieuDangKys', function($q) use ($selectedHocKy) {
                    $q->where('TrangThai', 'Đã duyệt');
                    if ($selectedHocKy) {
                        $q->whereHas('deTai', fn($dq) => $dq->where('MaHocKy', $selectedHocKy));
                    }
                });
            }
        }

        $dsGiangVienHk = $queryHk->orderBy('MaGV')->paginate(15, ['*'], 'page_hk')->withQueryString();

        $pageHkGvIds = $dsGiangVienHk->pluck('MaGV')->toArray();
        $chiTieuStatsHk = ChiTieuService::getChiTieuStatsBatch($pageHkGvIds, $selectedHocKy);
        $customConfigsHk = ChiTieuHuongDan::where('MaHocKy', $selectedHocKy)
            ->whereIn('MaGV', $pageHkGvIds)
            ->get()
            ->keyBy('MaGV');

        foreach ($dsGiangVienHk as $gv) {
            $gv->chi_tieu_stats = $chiTieuStatsHk[$gv->MaGV] ?? [
                'tong'       => 5,
                'da_dung'    => 0,
                'con_lai'    => 5,
                'tinh_trang' => 'con_chi_tieu',
                'is_full'    => false,
                'label'      => '0/5',
            ];
            $gv->chi_tieu_record = $customConfigsHk->get($gv->MaGV);
            $gv->de_tais_hk_count = \App\Models\DeTai::where('MaGV', $gv->MaGV)
                ->where('MaHocKy', $selectedHocKy)
                ->whereIn('TrangThai', ['Đã công bố', 'Trưởng khoa đã duyệt', 'Đã duyệt', 'Đã đăng ký', 'Hoàn thành'])
                ->count();
        }

        // Thống kê tổng quan học kỳ (4 KPI cards Tab 2: Tập trung đề tài & định mức hướng dẫn)
        $totalGvHk = GiangVien::where(function($q) use ($selectedHocKy) {
            $q->whereHas('chiTieuHuongDans', fn($ct) => $ct->where('MaHocKy', $selectedHocKy))
              ->orWhereHas('deTais', fn($dt) => $dt->where('MaHocKy', $selectedHocKy));
        })->count();

        $gvHuongDan = GiangVien::whereHas('deTais.phieuDangKys', function($q) use ($selectedHocKy) {
            $q->where('TrangThai', 'Đã duyệt');
            if ($selectedHocKy) {
                $q->whereHas('deTai', fn($dq) => $dq->where('MaHocKy', $selectedHocKy));
            }
        })->count();

        $deTaiCongBoQuery = \App\Models\DeTai::whereIn('TrangThai', ['Đã công bố', 'Trưởng khoa đã duyệt']);
        if ($selectedHocKy) {
            $deTaiCongBoQuery->where('MaHocKy', $selectedHocKy);
        }
        $totalDeTaiCongBo = $deTaiCongBoQuery->count();

        $nhomDaGanQuery = \App\Models\DangKyDeTai::where('TrangThai', 'Đã duyệt');
        if ($selectedHocKy) {
            $nhomDaGanQuery->whereHas('deTai', fn($dq) => $dq->where('MaHocKy', $selectedHocKy));
        }
        $totalNhomDaGan = $nhomDaGanQuery->count();

        $statsHk = [
            'total_gv'       => $totalGvHk,
            'huong_dan'      => $gvHuongDan,
            'pct_huong_dan'  => $totalGvHk > 0 ? round(($gvHuongDan / $totalGvHk) * 100, 1) : 0,
            'de_tai_cong_bo' => $totalDeTaiCongBo,
            'nhom_da_gan'    => $totalNhomDaGan,
            'total'          => $totalGV,
            'active'         => $statsGv['active'],
            'chuc_vu'        => $statsGv['chuc_vu'],
        ];

        // Biến alias $stats để tương thích với các view/tests cũ
        $stats = $statsHk;

        $bomons = BoMon::with('khoa')->orderBy('TenBoMon')->get();
        $allGiangViens = GiangVien::with('boMon')->orderBy('HoTen')->get();

        // ══════════════════════════════════════════════════════════════
        // 3. DATA CHO TAB 3: TÀI KHOẢN PORTAL & PHÂN QUYỀN
        // ══════════════════════════════════════════════════════════════
        $tkChucVus = TaiKhoan::whereIn('MaVaiTro', ['VT04', 'VT05'])
            ->orWhere('TenDangNhap', 'LIKE', 'TK_%')
            ->orWhere('TenDangNhap', 'LIKE', 'TBM_%')
            ->get();

        return view('admin.giangvien.index', compact(
            'activeTab',
            'giangviens',
            'dsGiangVienHk',
            'bomons',
            'allGiangViens',
            'stats',
            'statsGv',
            'statsHk',
            'truongKhoaNames',
            'truongBoMonNames',
            'tkChucVus',
            'hocKys',
            'selectedHocKy'
        ));
    }

    public function create()
    {
        $bomons = BoMon::with('khoa')->orderBy('TenBoMon')->get();
        return view('admin.giangvien.create', compact('bomons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenDangNhap' => [
                'required',
                'string',
                'size:10',
                'regex:/^GV\d{8}$/',
                'unique:TaiKhoan,TenDangNhap',
                'unique:GiangVien,MaGV'
            ],
            'HoTen'       => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\p{L}\s]+$/u'],
            'HocVi'       => 'required|string|max:50',
            'MaBoMon'     => 'required|exists:BoMon,MaBoMon',
            'Email'       => 'required|email|max:100|unique:GiangVien,Email',
            'SoDienThoai' => [
                'nullable',
                'string',
                'regex:/^0[0-9]{9}$/',
                'unique:GiangVien,SoDienThoai'
            ],
            'MaHocKy'     => 'nullable|exists:HocKy,MaHocKy'
        ], [
            'TenDangNhap.required' => 'Vui lòng nhập Mã giảng viên (MaGV).',
            'TenDangNhap.size'     => 'Mã giảng viên phải có đúng 10 ký tự (ví dụ: GV00000001).',
            'TenDangNhap.regex'    => 'Mã giảng viên phải có tiền tố GV theo sau bởi 8 chữ số (VD: GV00000001).',
            'TenDangNhap.unique'   => 'Mã giảng viên / Tên đăng nhập đã tồn tại trong hệ thống.',
            'HoTen.required'       => 'Vui lòng nhập họ và tên giảng viên.',
            'HoTen.min'            => 'Họ và tên giảng viên phải có tối thiểu 2 ký tự.',
            'HoTen.max'            => 'Họ và tên giảng viên không được vượt quá 100 ký tự.',
            'HoTen.regex'          => 'Họ và tên chỉ được chứa chữ cái và khoảng trắng, không chứa số hoặc ký tự đặc biệt.',
            'HocVi.required'       => 'Vui lòng chọn hoặc nhập học vị.',
            'MaBoMon.required'     => 'Vui lòng chọn bộ môn.',
            'Email.required'       => 'Vui lòng nhập email.',
            'Email.email'          => 'Định dạng email không hợp lệ.',
            'Email.unique'         => 'Email đã được sử dụng bởi giảng viên khác.',
            'SoDienThoai.unique'   => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex'    => 'Số điện thoại phải gồm đúng 10 chữ số và bắt đầu bằng số 0.',
        ]);

        $maGV = trim($request->TenDangNhap);
        $initialPassword = \Illuminate\Support\Str::password(10, true, true, false, false);

        DB::transaction(function () use ($request, $maGV, $initialPassword) {
            $tk = TaiKhoan::create([
                'MaTK'              => $maGV,
                'TenDangNhap'       => $maGV,
                'MatKhau'           => Hash::make($initialPassword),
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

            if ($request->filled('MaHocKy')) {
                ChiTieuHuongDan::firstOrCreate([
                    'MaHocKy' => $request->MaHocKy,
                    'MaGV'    => $maGV,
                ], [
                    'MaChiTieu'   => 'CT_' . substr(md5($maGV . '_' . $request->MaHocKy . '_' . uniqid()), 0, 16),
                    'SoNhomToiDa' => 5,
                    'NgayPhanBo'  => now(),
                ]);
            }
        });

        $redirectParams = ['tab' => 'danhsach'];
        if ($request->filled('MaHocKy')) {
            $redirectParams['MaHocKy_filter'] = $request->MaHocKy;
        }

        return redirect()->route('giangvien.index', $redirectParams)
            ->with('success', "Thêm giảng viên '{$request->HoTen}' (Mã GV: {$maGV}) thành công! Mật khẩu khởi tạo tạm thời: [{$initialPassword}]. Người dùng bắt buộc phải đổi mật khẩu khi đăng nhập lần đầu.");
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
        ])->withCount([
            'deTais' => fn($q) => $q->whereIn('TrangThai', ['Đã công bố', 'Trưởng khoa đã duyệt', 'Đã duyệt', 'Đã đăng ký', 'Hoàn thành']),
            'thanhVienHoiDongs'
        ])->findOrFail($id);

        $totalDeTai = $giangvien->de_tais_count;
        $totalHoiDong = $giangvien->thanh_vien_hoi_dongs_count;

        $currentHk = \App\Models\HocKy::where('TrangThai', 'Đang diễn ra')->first() ?? \App\Models\HocKy::latest('MaHocKy')->first();
        $scopedHocKy = $currentHk?->MaHocKy;

        // DÙNG CHUNG HÀM ĐẾM ChiTieuService (Chuẩn P1 / A3)
        $chiTieuStats = ChiTieuService::getChiTieuStats($id, $scopedHocKy);

        // Thống kê hội đồng bảo vệ chi tiết (Chuẩn Yêu cầu 6.4: 1 hội đồng = 1 lượt)
        $hoidongStats = app(\App\Services\CouncilAssignmentService::class)->thongKeGiangVienHoiDong($id, $scopedHocKy);

        $stats = [
            'total_detai'    => (int)$totalDeTai,
            'nhom_huong_dan' => $chiTieuStats['da_dung'],
            'chi_tieu_toida' => $chiTieuStats['tong'],
            'con_lai'        => $chiTieuStats['con_lai'],
            'tinh_trang'     => $chiTieuStats['tinh_trang'],
            'is_full'        => $chiTieuStats['is_full'],
            'label'          => $chiTieuStats['label'],
            'total_hoidong'  => (int)$hoidongStats['tong_so_hoidong'],
        ];

        return view('admin.giangvien.show', compact('giangvien', 'stats', 'scopedHocKy', 'hoidongStats'));
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
            'HoTen'       => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\p{L}\s]+$/u'],
            'HocVi'       => 'required|string|max:50',
            'MaBoMon'     => 'required|exists:BoMon,MaBoMon',
            'Email'       => 'required|email|max:100|unique:GiangVien,Email,' . $id . ',MaGV',
            'SoDienThoai' => [
                'nullable',
                'string',
                'regex:/^0[0-9]{9}$/',
                'unique:GiangVien,SoDienThoai,' . $id . ',MaGV'
            ]
        ], [
            'HoTen.required'     => 'Vui lòng nhập họ và tên giảng viên.',
            'HoTen.min'          => 'Họ và tên giảng viên phải có tối thiểu 2 ký tự.',
            'HoTen.max'          => 'Họ và tên giảng viên không được vượt quá 100 ký tự.',
            'HoTen.regex'        => 'Họ và tên chỉ được chứa chữ cái và khoảng trắng, không chứa số hoặc ký tự đặc biệt.',
            'HocVi.required'     => 'Vui lòng nhập học vị.',
            'MaBoMon.required'   => 'Vui lòng chọn bộ môn.',
            'Email.required'     => 'Vui lòng nhập email.',
            'Email.email'        => 'Định dạng email không hợp lệ.',
            'Email.unique'       => 'Email đã được sử dụng.',
            'SoDienThoai.unique' => 'Số điện thoại đã được sử dụng.',
            'SoDienThoai.regex'  => 'Số điện thoại phải gồm đúng 10 chữ số và bắt đầu bằng số 0.',
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
        $maHocKy = $request->input('MaHocKy');
        $params = $maHocKy ? [$maHocKy] : [];
        return $this->runImport($request, 'importGiangVien', $params, 'Giảng viên');
    }

    /**
     * Bổ nhiệm Giảng viên làm Trưởng khoa hoặc Trưởng bộ môn
     * Quy tắc riêng biệt:
     * - Giảng viên thường: Mã GV (VD: GV00000001) - Role VT02
     * - Trưởng khoa: TK_{MaKhoa}_{MaGV} (Role VT05) - KHÔNG đè lên tài khoản GV
     * - Trưởng bộ môn: TBM_{MaBoMon}_{MaGV} (Role VT04) - KHÔNG đè lên tài khoản GV
     */
    public function boNhiem(Request $request, $id)
    {
        $gv = GiangVien::with('boMon.khoa', 'taiKhoan')->findOrFail($id);

        $request->validate([
            'chuc_vu' => 'required|in:TK,TBM,HUY',
            'ma_khoa' => 'required_if:chuc_vu,TK|nullable|exists:Khoa,MaKhoa',
            'ma_bomon' => 'required_if:chuc_vu,TBM|nullable|exists:BoMon,MaBoMon',
        ], [
            'chuc_vu.required' => 'Vui lòng chọn chức vụ bổ nhiệm hoặc chọn Hủy chức vụ.',
            'ma_khoa.required_if' => 'Vui lòng chọn Khoa bổ nhiệm.',
            'ma_bomon.required_if' => 'Vui lòng chọn Bộ môn bổ nhiệm.',
        ]);

        $chucVu = $request->chuc_vu;

        try {
            $msgSuccess = '';
            DB::transaction(function() use ($gv, $chucVu, $request, &$msgSuccess) {
                // 1. Luôn đảm bảo tài khoản GV gốc của giảng viên có vai trò VT02 (Giảng viên)
                $tkGV = TaiKhoan::where('TenDangNhap', $gv->MaGV)->first();
                if (!$tkGV) {
                    $tempPassGV = \Illuminate\Support\Str::password(10, true, true, false, false);
                    $tkGV = TaiKhoan::create([
                        'MaTK'              => $gv->MaGV,
                        'TenDangNhap'       => $gv->MaGV,
                        'MatKhau'           => Hash::make($tempPassGV),
                        'MaVaiTro'          => 'VT02',
                        'TrangThai'         => true,
                        'TrangThaiMatKhau'  => 'INITIAL',
                        'BatBuocDoiMatKhau' => true,
                        'SoLanDangNhapSai'  => 0,
                    ]);
                    $gv->update(['MaTK' => $tkGV->MaTK]);
                } else {
                    $tkGV->update(['MaVaiTro' => 'VT02']);
                }

                // 2. Trường hợp Hủy chức vụ: Xóa các tài khoản chức vụ riêng biệt và gỡ tên khỏi Khoa/Bộ môn
                if ($chucVu === 'HUY') {
                    TaiKhoan::where('TenDangNhap', 'LIKE', "TK_%_{$gv->MaGV}")
                        ->orWhere('TenDangNhap', 'LIKE', "TBM_%_{$gv->MaGV}")
                        ->delete();

                    \App\Models\Khoa::where('TruongKhoa', $gv->HoTen)->update(['TruongKhoa' => null]);
                    BoMon::where('TruongBoMon', $gv->HoTen)->update(['TruongBoMon' => null]);

                    $msgSuccess = "Đã hủy chức vụ và thu hồi tài khoản chức năng của giảng viên '{$gv->HoTen}'. Tài khoản Giảng viên [{$gv->MaGV}] vẫn được giữ nguyên vai trò Giảng viên bình thường.";
                    return;
                }

                // 3. Trường hợp Bổ nhiệm Trưởng khoa (TK)
                if ($chucVu === 'TK') {
                    $maKhoa = $request->ma_khoa;
                    $usernameTK = 'TK_' . $maKhoa . '_' . $gv->MaGV;
                    $tempPassTK = \Illuminate\Support\Str::password(10, true, true, false, false);

                    // Xóa tài khoản TBM cũ nếu có
                    TaiKhoan::where('TenDangNhap', 'LIKE', "TBM_%_{$gv->MaGV}")->delete();
                    BoMon::where('TruongBoMon', $gv->HoTen)->update(['TruongBoMon' => null]);

                    // Tạo tài khoản chức vụ Trưởng khoa riêng biệt
                    $existingTK = TaiKhoan::where('TenDangNhap', $usernameTK)->first();
                    if (!$existingTK) {
                        TaiKhoan::create([
                            'MaTK'              => $usernameTK,
                            'TenDangNhap'       => $usernameTK,
                            'MatKhau'           => Hash::make($tempPassTK),
                            'MaVaiTro'          => 'VT05',
                            'TrangThai'         => true,
                            'TrangThaiMatKhau'  => 'INITIAL',
                            'BatBuocDoiMatKhau' => true,
                            'SoLanDangNhapSai'  => 0,
                        ]);
                        $msgSuccess = "Đã bổ nhiệm giảng viên '{$gv->HoTen}' làm Trưởng khoa! Cấp tài khoản chức vụ riêng: [{$usernameTK}] (Role: Trưởng khoa) với mật khẩu tạm thời: [{$tempPassTK}]. Yêu cầu đổi mật khẩu ở lần đăng nhập đầu tiên.";
                    } else {
                        $existingTK->update(['MaVaiTro' => 'VT05', 'TrangThai' => true]);
                        $msgSuccess = "Đã bổ nhiệm giảng viên '{$gv->HoTen}' làm Trưởng khoa! Kích hoạt lại tài khoản chức vụ: [{$usernameTK}] (Role: Trưởng khoa).";
                    }

                    \App\Models\Khoa::where('MaKhoa', $maKhoa)->update(['TruongKhoa' => $gv->HoTen]);
                } else {
                    // 4. Trường hợp Bổ nhiệm Trưởng bộ môn (TBM)
                    $maBM = $request->ma_bomon;
                    $bm = BoMon::findOrFail($maBM);
                    $usernameTBM = 'TBM_' . $maBM . '_' . $gv->MaGV;
                    $tempPassTBM = \Illuminate\Support\Str::password(10, true, true, false, false);

                    // Xóa tài khoản TK cũ nếu có
                    TaiKhoan::where('TenDangNhap', 'LIKE', "TK_%_{$gv->MaGV}")->delete();
                    \App\Models\Khoa::where('TruongKhoa', $gv->HoTen)->update(['TruongKhoa' => null]);

                    // Tạo tài khoản chức vụ Trưởng bộ môn riêng biệt
                    $existingTBM = TaiKhoan::where('TenDangNhap', $usernameTBM)->first();
                    if (!$existingTBM) {
                        TaiKhoan::create([
                            'MaTK'              => $usernameTBM,
                            'TenDangNhap'       => $usernameTBM,
                            'MatKhau'           => Hash::make($tempPassTBM),
                            'MaVaiTro'          => 'VT04',
                            'TrangThai'         => true,
                            'TrangThaiMatKhau'  => 'INITIAL',
                            'BatBuocDoiMatKhau' => true,
                            'SoLanDangNhapSai'  => 0,
                        ]);
                        $msgSuccess = "Đã bổ nhiệm giảng viên '{$gv->HoTen}' làm Trưởng bộ môn! Cấp tài khoản chức vụ riêng: [{$usernameTBM}] (Role: Trưởng bộ môn) với mật khẩu tạm thời: [{$tempPassTBM}]. Yêu cầu đổi mật khẩu ở lần đăng nhập đầu tiên.";
                    } else {
                        $existingTBM->update(['MaVaiTro' => 'VT04', 'TrangThai' => true]);
                        $msgSuccess = "Đã bổ nhiệm giảng viên '{$gv->HoTen}' làm Trưởng bộ môn! Kích hoạt lại tài khoản chức vụ: [{$usernameTBM}] (Role: Trưởng bộ môn).";
                    }

                    $bm->update(['TruongBoMon' => $gv->HoTen]);
                    $gv->update(['MaBoMon' => $maBM]);
                }
            });

            return redirect()->route('giangvien.index', ['tab' => 'taikhoan'])->with('success', $msgSuccess);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors("Lỗi khi xử lý chức vụ: " . $e->getMessage());
        }
    }

    /**
     * Hủy chức vụ Trưởng khoa / Trưởng bộ môn đã cấp cho giảng viên
     */
    public function huyBoNhiem($id)
    {
        $gv = GiangVien::with('taiKhoan')->findOrFail($id);

        try {
            DB::transaction(function () use ($gv) {
                // Xóa các tài khoản chức vụ riêng biệt
                TaiKhoan::where('TenDangNhap', 'LIKE', "TK_%_{$gv->MaGV}")
                    ->orWhere('TenDangNhap', 'LIKE', "TBM_%_{$gv->MaGV}")
                    ->delete();

                // Gỡ tên khỏi bảng Khoa và BoMon
                \App\Models\Khoa::where('TruongKhoa', $gv->HoTen)->update(['TruongKhoa' => null]);
                BoMon::where('TruongBoMon', $gv->HoTen)->update(['TruongBoMon' => null]);

                // Đảm bảo tài khoản giảng viên gốc vẫn là VT02
                TaiKhoan::where('TenDangNhap', $gv->MaGV)->update(['MaVaiTro' => 'VT02']);
            });

            return redirect()->route('giangvien.index', ['tab' => 'taikhoan'])->with(
                'success',
                "Đã hủy chức vụ đã cấp của giảng viên '{$gv->HoTen}' thành công! Tài khoản chức năng đã bị xóa, tài khoản Giảng viên [{$gv->MaGV}] vẫn hoạt động bình thường."
            );
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Lỗi khi hủy chức vụ: " . $e->getMessage());
        }
    }

    /**
     * Xóa tài khoản Portal đã cấp cho Giảng viên (giữ nguyên hồ sơ Giảng viên)
     */
    public function xoaTaiKhoanPortal($id)
    {
        $gv = GiangVien::with('taiKhoan')->findOrFail($id);

        try {
            DB::transaction(function () use ($gv) {
                // Xóa tài khoản chức vụ nếu có
                TaiKhoan::where('TenDangNhap', 'LIKE', "TK_%_{$gv->MaGV}")
                    ->orWhere('TenDangNhap', 'LIKE', "TBM_%_{$gv->MaGV}")
                    ->delete();

                // Xóa tài khoản GV gốc
                TaiKhoan::where('TenDangNhap', $gv->MaGV)->orWhere('MaTK', $gv->MaTK)->delete();

                // Gỡ tên chức vụ nếu có
                \App\Models\Khoa::where('TruongKhoa', $gv->HoTen)->update(['TruongKhoa' => null]);
                BoMon::where('TruongBoMon', $gv->HoTen)->update(['TruongBoMon' => null]);

                // Gỡ liên kết ở bảng giảng viên
                $gv->update(['MaTK' => null]);
            });

            return redirect()->route('giangvien.index', ['tab' => 'taikhoan'])->with(
                'success',
                "Đã xóa toàn bộ tài khoản Portal đã cấp cho giảng viên '{$gv->HoTen}' thành công! Hồ sơ giảng viên vẫn được giữ nguyên an toàn."
            );
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors("Lỗi khi xóa tài khoản portal: " . $e->getMessage());
        }
    }

    /**
     * Import danh sách tài khoản Trưởng khoa & Trưởng bộ môn từ file Excel/CSV
     */
    public function importTaiKhoanTBM_TK(Request $request)
    {
        return $this->runImport($request, 'importTaiKhoanTBM_TK', [], 'Tài khoản Trưởng khoa / Trưởng bộ môn');
    }

    /**
     * Xuất danh sách Giảng viên ra file Excel / CSV (hỗ trợ bộ lọc tìm kiếm & bộ môn)
     */
    public function exportExcel(Request $request)
    {
        $query = GiangVien::with(['boMon.khoa', 'taiKhoan.vaiTro'])->withCount('deTais');

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

        $hkFilter = $request->get('MaHocKy_filter', $request->get('MaHocKy'));
        if (!empty($hkFilter)) {
            $query->where(function($q) use ($hkFilter) {
                $q->whereHas('deTais', fn($dt) => $dt->where('MaHocKy', $hkFilter))
                  ->orWhereHas('phanCongPhanBiens.deTai', fn($dt) => $dt->where('MaHocKy', $hkFilter))
                  ->orWhereHas('thanhVienHoiDongs.hoiDong', fn($hd) => $hd->where('MaHocKy', $hkFilter))
                  ->orWhereHas('chiTieuHuongDans', fn($ct) => $ct->where('MaHocKy', $hkFilter));
            });
        }

        $scopedHocKy = $hkFilter;

        if ($request->filled('tinh_trang_hd')) {
            $statusHd = $request->tinh_trang_hd;
            if ($statusHd === 'co_hd') {
                $query->whereHas('deTais.phieuDangKys', function($q) use ($scopedHocKy) {
                    $q->where('TrangThai', 'Đã duyệt');
                    if ($scopedHocKy) {
                        $q->whereHas('deTai', fn($dq) => $dq->where('MaHocKy', $scopedHocKy));
                    }
                });
            } elseif ($statusHd === 'dat_dinh_muc') {
                $allGvList = GiangVien::pluck('MaGV')->toArray();
                $allStatsMap = ChiTieuService::getChiTieuStatsBatch($allGvList, $scopedHocKy);
                $fullGvIds = [];
                foreach ($allStatsMap as $mgv => $st) {
                    if ($st['is_full']) {
                        $fullGvIds[] = $mgv;
                    }
                }
                $query->whereIn('MaGV', $fullGvIds);
            } elseif ($statusHd === 'chua_hd') {
                $query->whereDoesntHave('deTais.phieuDangKys', function($q) use ($scopedHocKy) {
                    $q->where('TrangThai', 'Đã duyệt');
                    if ($scopedHocKy) {
                        $q->whereHas('deTai', fn($dq) => $dq->where('MaHocKy', $scopedHocKy));
                    }
                });
            }
        }

        $giangviens = $query->withCount([
            'deTais' => function($q) use ($scopedHocKy) {
                $q->whereIn('TrangThai', ['Đã công bố', 'Trưởng khoa đã duyệt', 'Đã duyệt', 'Đã đăng ký', 'Hoàn thành']);
                if ($scopedHocKy) {
                    $q->where('MaHocKy', $scopedHocKy);
                }
            }
        ])->orderBy('MaGV')->get();

        $allExportGvIds = $giangviens->pluck('MaGV')->toArray();
        $chiTieuStatsExport = ChiTieuService::getChiTieuStatsBatch($allExportGvIds, $scopedHocKy);

        $filename = "DS_GIANG_VIEN_KHOA_CNTT_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-Type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($giangviens, $chiTieuStatsExport) {
            $file = fopen('php://output', 'w');
            // BOM UTF-8 để Microsoft Excel nhận diện tiếng Việt có dấu
            fputs($file, "\xEF\xBB\xBF");

            // Header hàng tiêu đề
            fputcsv($file, [
                'STT',
                'Mã Giảng Viên',
                'Họ và Tên',
                'Học Vị',
                'Học Hàm',
                'Giới Tính',
                'Email',
                'Số Điện Thoại',
                'Bộ Môn',
                'Khoa',
                'Vai Trò Portal',
                'Định Mức Hướng Dẫn',
                'Số Đề Tài Đề Xuất',
                'Trạng Thái Công Tác',
                'Tài Khoản Portal'
            ]);

            foreach ($giangviens as $idx => $gv) {
                $tk = $gv->taiKhoan;
                $vaiTro = $tk?->vaiTro?->TenVaiTro ?? 'Giảng viên';
                $trangThaiTK = ($tk && $tk->TrangThai) ? 'Đang hoạt động' : 'Chưa kích hoạt / Khóa';
                $ct = $chiTieuStatsExport[$gv->MaGV] ?? ['label' => '0/5'];

                fputcsv($file, [
                    $idx + 1,
                    $gv->MaGV,
                    $gv->HoTen,
                    $gv->HocVi ?? 'Cử nhân / Kỹ sư',
                    $gv->HocHam ?? '—',
                    $gv->GioiTinh ?? '—',
                    $gv->Email ?? '—',
                    $gv->SoDienThoai ?? '—',
                    $gv->boMon->TenBoMon ?? 'Chưa phân bổ',
                    $gv->boMon->khoa->TenKhoa ?? 'Khoa Công nghệ Thông tin',
                    $vaiTro,
                    $ct['label'] . ' nhóm',
                    $gv->de_tais_count ?? 0,
                    $gv->TrangThai ?? 'Đang công tác',
                    $trangThaiTK
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}