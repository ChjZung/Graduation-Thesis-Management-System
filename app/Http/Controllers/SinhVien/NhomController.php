<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\Nhom;
use App\Models\ThanhVienNhom;
use App\Models\SinhVien;
use App\Models\TaiKhoan;
use App\Helpers\IdGenerator;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NhomController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::with('lop.nganh', 'taiKhoan')->where('MaTK', $user->MaTK)->first();

        if (!$sinhVien) {
            return redirect()->route('sinhvien.dashboard')
                ->with('error', 'Hồ sơ sinh viên chưa được thiết lập. Vui lòng liên hệ Giáo vụ Khoa để được hỗ trợ.');
        }

        // Lấy danh sách tất cả các nhóm mà sinh viên này đang tham gia chính thức (không lọc cứng theo 1 học kỳ)
        $sinhVienAllGroups = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->with(['nhom.hocPhan', 'nhom.hocKy', 'nhom.dangKyDeTai.deTai'])
            ->get();

        // Xác định nhóm ưu tiên (nếu sinh viên đã có nhóm chính thức)
        // Xác định nhóm ưu tiên (nếu sinh viên đã có nhóm chính thức)
        $userActiveGroup = $sinhVienAllGroups->first()?->nhom;

        // Xác định Bộ môn chuyên ngành theo nhóm hiện tại hoặc theo Ngành học của sinh viên
        $svBoMon = $sinhVien->getMaBoMon();
        $requestedBoMon = $request->input('bo_mon');
        // Nếu đã có nhóm chính thức: ưu tiên bộ môn của nhóm đó
        // Nếu chưa có nhóm: nếu request có bo_mon thì lấy request, nếu không thì mặc định là 'ALL' để không chặn hiển thị các nhóm khác
        if ($userActiveGroup && $userActiveGroup->hocPhan?->MaBoMon) {
            $selectedBoMon = $requestedBoMon ?? $userActiveGroup->hocPhan->MaBoMon;
        } else {
            $selectedBoMon = $requestedBoMon ?? 'ALL';
        }

        // Lấy danh sách Môn / Học phần đang mở
        $hocPhanQuery = \App\Models\HocPhan::with(['hocPhanHocKies' => function($q) {
                $q->where('TrangThai', 'Đang mở');
            }])
            ->where(function($q) {
                $q->whereIn('TrangThai', ['Đang sử dụng', 'Đang áp dụng', '1'])
                  ->orWhere('TrangThai', true);
            });

        if ($selectedBoMon && $selectedBoMon !== 'ALL') {
            $hocPhanQuery->where(function($q) use ($selectedBoMon, $userActiveGroup) {
                $q->where('MaBoMon', $selectedBoMon);
                if ($userActiveGroup && $userActiveGroup->MaHocPhan) {
                    $q->orWhere('MaHocPhan', $userActiveGroup->MaHocPhan);
                }
            });
        }
        $hocPhans = $hocPhanQuery->orderBy('TenHocPhan')->get();

        $hocKies = \App\Models\HocKy::orderBy('MaHocKy', 'desc')->get();
        $boMons = \App\Models\BoMon::where('MaKhoa', 'CNTT')->orderBy('TenBoMon')->get();

        // 2. Phân giải môn học được chọn (selectedHocPhan)
        $requestedHp = $request->input('hoc_phan') ?? $request->input('MaHocPhan');
        if ($requestedHp && ($requestedHp === 'ALL' || $hocPhans->firstWhere('MaHocPhan', $requestedHp))) {
            $selectedHocPhan = $requestedHp;
        } elseif ($userActiveGroup && $userActiveGroup->MaHocPhan) {
            $selectedHocPhan = $userActiveGroup->MaHocPhan;
        } else {
            // Khi chưa có nhóm: Mặc định là 'ALL' để sinh viên nhìn thấy toàn bộ các nhóm đang mở trong học kỳ
            $selectedHocPhan = 'ALL';
        }

        // 2. Phân giải học kỳ (maHocKy):
        // Ưu tiên 1: Tham số truyền vào từ Request (?hoc_ky=...)
        // Ưu tiên 2: Tự động nhận diện học kỳ của nhóm sinh viên đang tham gia
        // Ưu tiên 3: Học kỳ của Kế hoạch đang mở hoặc Học kỳ đang diễn ra
        $requestedHocKy = $request->input('hoc_ky') ?? $request->input('MaHocKy');
        $defaultHocKy = null;
        $activePlan = \App\Services\PlanPhaseService::getActivePlan();
        if ($activePlan && !empty($activePlan->MaHocKy)) {
            $defaultHocKy = $activePlan->MaHocKy;
        } else {
            $currentHk = \App\Models\HocKy::where('TrangThai', 'Đang diễn ra')->first() ?? \App\Models\HocKy::latest('MaHocKy')->first();
            $defaultHocKy = $currentHk?->MaHocKy;
        }

        if ($requestedHocKy) {
            $maHocKy = $requestedHocKy;
        } elseif ($userActiveGroup && !empty($userActiveGroup->MaHocKy)) {
            $maHocKy = $userActiveGroup->MaHocKy;
        } else {
            $maHocKy = $defaultHocKy;
        }

        // 3. Kiểm tra nhóm mà sinh viên đang tham gia chính thức ('da_tham_gia') theo HỌC KỲ này (mỗi SV chỉ thuộc 1 nhóm/kỳ)
        $nhomHocKyHienTai = $sinhVienAllGroups->first(function($item) use ($maHocKy) {
            return $item->nhom && ($item->nhom->MaHocKy === $maHocKy || empty($maHocKy));
        })?->nhom;

        // KHI ĐÃ CÓ NHÓM: Lọc mỗi học phần của nhóm đã tạo/tham gia trong học kỳ đó
        if ($nhomHocKyHienTai && $nhomHocKyHienTai->MaHocPhan) {
            $selectedHocPhan = $nhomHocKyHienTai->MaHocPhan;
            if ($nhomHocKyHienTai->hocPhan) {
                $hocPhans = collect([$nhomHocKyHienTai->hocPhan]);
            } else {
                $hpObj = \App\Models\HocPhan::find($nhomHocKyHienTai->MaHocPhan);
                $hocPhans = $hpObj ? collect([$hpObj]) : $hocPhans->where('MaHocPhan', $nhomHocKyHienTai->MaHocPhan)->values();
            }
        }

        // Lấy danh sách lời mời gia nhập nhóm gửi tới SV này ('cho_xac_nhan') (hiển thị xuyên suốt để SV biết và phản hồi)
        $loiMois = ThanhVienNhom::with([
                'nhom.truongNhom.taiKhoan',
                'nhom.hocKy',
                'nhom.hocPhan',
                'nhom.thanhViens' => fn($q) => $q->where('TrangThai', 'da_tham_gia')->with('sinhVien')
            ])
            ->where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'cho_xac_nhan')
            ->whereHas('nhom', function($q) use ($maHocKy) {
                if ($maHocKy && $maHocKy !== 'ALL') {
                    $q->where('MaHocKy', $maHocKy)->orWhereNull('MaHocKy');
                }
            })
            ->get();

        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->whereHas('nhom', function($q) use ($maHocKy, $selectedHocPhan) {
                if ($maHocKy && $maHocKy !== 'ALL') {
                    $q->where(function($sub) use ($maHocKy) {
                        $sub->where('MaHocKy', $maHocKy)->orWhereNull('MaHocKy');
                    });
                }
                if ($selectedHocPhan && $selectedHocPhan !== 'ALL') {
                    $q->where(function($sub) use ($selectedHocPhan) {
                        $sub->where('MaHocPhan', $selectedHocPhan)->orWhereNull('MaHocPhan');
                    });
                }
            })
            ->first();

        $nhomCurrent = null;
        $yeuCauXinVao = collect();
        $loiMoiDaGui = collect();
        $isNhomLocked = false;

        if ($thanhVienRecord) {
            $nhomCurrent = Nhom::with([
                    'deTai.giangVien.boMon',
                    'deTai.hocKy',
                    'deTai.hocPhanRef',
                    'deTai.nganh',
                    'dangKyDeTai.deTai.giangVien.boMon',
                    'dangKyDeTai.deTai.hocKy',
                    'dangKyDeTai.deTai.hocPhanRef',
                    'dangKyDeTai.deTai.nganh',
                    'truongNhom.taiKhoan',
                    'hocPhan',
                    'hocKy'
                ])
                ->where('MaNhom', $thanhVienRecord->MaNhom)
                ->first();

            if ($nhomCurrent && $nhomCurrent->MaHocKy && !$requestedHocKy) {
                $maHocKy = $nhomCurrent->MaHocKy;
            }

            if ($nhomCurrent) {
                // Nhóm đã khóa khi đề tài đang chờ duyệt hoặc đã được duyệt chính thức
                $isNhomLocked = ($nhomCurrent->dangKyDeTai && in_array($nhomCurrent->dangKyDeTai->TrangThai, ['Chờ duyệt', 'Đã duyệt']));

                // Danh sách thành viên chính thức
                $nhomCurrent->thanhViens = ThanhVienNhom::with('sinhVien.lop.nganh', 'sinhVien.taiKhoan')
                    ->where('MaNhom', $nhomCurrent->MaNhom)
                    ->where('TrangThai', 'da_tham_gia')
                    ->get();

                // Nếu là trưởng nhóm: Lấy danh sách SV xin vào nhóm & Lời mời nhóm đã gửi
                if ($nhomCurrent->MaTruongNhom === $sinhVien->MaSV) {
                    $yeuCauXinVao = ThanhVienNhom::with('sinhVien.lop.nganh', 'sinhVien.taiKhoan')
                        ->where('MaNhom', $nhomCurrent->MaNhom)
                        ->where('TrangThai', 'xin_gia_nhap')
                        ->get();

                    $loiMoiDaGui = ThanhVienNhom::with('sinhVien.lop.nganh', 'sinhVien.taiKhoan')
                        ->where('MaNhom', $nhomCurrent->MaNhom)
                        ->where('TrangThai', 'cho_xac_nhan')
                        ->get();
                }
            }

            $groupPhaseState = \App\Services\PlanPhaseService::getGroupPhaseState($maHocKy);
            $isTaoNhomOpen = $groupPhaseState['is_open'];

            return view('sinhvien.nhom.index', compact('sinhVien', 'nhomCurrent', 'yeuCauXinVao', 'loiMoiDaGui', 'isNhomLocked', 'hocPhans', 'selectedHocPhan', 'selectedBoMon', 'sinhVienAllGroups', 'hocKies', 'boMons', 'maHocKy', 'isTaoNhomOpen', 'groupPhaseState', 'nhomHocKyHienTai', 'loiMois'));
        }

        // 2. KHI CHƯA CÓ NHÓM CHO MÔN NÀY:
        // Lấy danh sách yêu cầu xin gia nhập mà SV này đã gửi đi ('xin_gia_nhap')
        $yeuCauDaGui = ThanhVienNhom::with(['nhom.truongNhom.taiKhoan'])
            ->where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'xin_gia_nhap')
            ->get()
            ->keyBy('MaNhom');

        // Danh sách tất cả các nhóm chưa khóa trong học kỳ hiện tại (ưu tiên nhóm mới nhất lên đầu)
        $queryNhoms = Nhom::with([
                'truongNhom.taiKhoan',
                'truongNhom.lop.nganh',
                'hocPhan',
                'hocKy',
                'thanhViens' => fn($q) => $q->where('TrangThai', 'da_tham_gia')->with('sinhVien.lop.nganh', 'sinhVien.taiKhoan')
            ])
            ->orderBy('created_at', 'desc')
            ->orderBy('MaNhom', 'desc');

        if ($maHocKy) {
            $queryNhoms->where(function($q) use ($maHocKy) {
                $q->where('MaHocKy', $maHocKy)->orWhereNull('MaHocKy');
            });
        }

        // Nhóm chưa khóa đề tài
        $queryNhoms->whereDoesntHave('phieuDangKys', fn($q) => $q->whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt']));

        if ($request->filled('q')) {
            // Khi tìm kiếm: Tìm kiếm rộng trên toàn bộ học kỳ theo Tên nhóm, Mã nhóm, MSSV / Họ tên Trưởng nhóm hoặc Thành viên
            $search = trim($request->q);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $search);
            $queryNhoms->where(function ($q) use ($search, $sClean) {
                $q->where('TenNhom', 'LIKE', "%{$search}%")
                  ->orWhere('MaNhom', 'LIKE', "%{$search}%");
                if (!empty($sClean)) {
                    $q->orWhere('MaNhom', 'LIKE', "%{$sClean}%");
                }
                $q->orWhereHas('truongNhom', function ($sq) use ($search, $sClean) {
                    $sq->where('HoTen', 'LIKE', "%{$search}%")
                       ->orWhere('SinhVien.MaSV', 'LIKE', "%{$search}%")
                       ->orWhereHas('taiKhoan', fn($tq) => $tq->where('TenDangNhap', 'LIKE', "%{$search}%"));
                    if (!empty($sClean)) {
                        $sq->orWhere('SinhVien.MaSV', 'LIKE', "%{$sClean}%")
                           ->orWhereHas('taiKhoan', fn($tq) => $tq->where('TenDangNhap', 'LIKE', "%{$sClean}%"));
                    }
                })
                ->orWhereHas('thanhViens.sinhVien', function ($sq) use ($search, $sClean) {
                    $sq->where('HoTen', 'LIKE', "%{$search}%")
                       ->orWhere('SinhVien.MaSV', 'LIKE', "%{$search}%")
                       ->orWhereHas('taiKhoan', fn($tq) => $tq->where('TenDangNhap', 'LIKE', "%{$search}%"));
                    if (!empty($sClean)) {
                        $sq->orWhere('SinhVien.MaSV', 'LIKE', "%{$sClean}%")
                           ->orWhereHas('taiKhoan', fn($tq) => $tq->where('TenDangNhap', 'LIKE', "%{$sClean}%"));
                    }
                });
            });
        } else {
            // Khi không tìm kiếm theo từ khóa 'q': Lọc theo học phần được chọn (nếu khác 'ALL')
            if ($selectedHocPhan && $selectedHocPhan !== 'ALL') {
                $queryNhoms->where(function($q) use ($selectedHocPhan) {
                    $q->where('MaHocPhan', $selectedHocPhan)->orWhereNull('MaHocPhan');
                    if (in_array($selectedHocPhan, ['HP_KLCN', 'HP_KLCN_CNPM', 'HP_KLCN_HTTT', 'HP_KLCN_KTPM', 'HP_KLCN_MMT', 'HP_KLCN_ATTT', 'HP_KLCN_KHMT'])) {
                        $q->orWhereIn('MaHocPhan', ['HP_KLCN', 'HP_KLCN_CNPM', 'HP_KLCN_HTTT', 'HP_KLCN_KTPM', 'HP_KLCN_MMT', 'HP_KLCN_ATTT', 'HP_KLCN_KHMT']);
                    }
                });
            } elseif ($selectedBoMon && $selectedBoMon !== 'ALL') {
                $queryNhoms->whereHas('hocPhan', fn($q) => $q->where('MaBoMon', $selectedBoMon));
            }
        }

        $nhomsOpen = $queryNhoms->get();
        $groupPhaseState = \App\Services\PlanPhaseService::getGroupPhaseState($maHocKy);
        $isTaoNhomOpen = $groupPhaseState['is_open'];

        return view('sinhvien.nhom.index', compact('sinhVien', 'nhomCurrent', 'loiMois', 'yeuCauDaGui', 'nhomsOpen', 'isNhomLocked', 'hocPhans', 'selectedHocPhan', 'selectedBoMon', 'sinhVienAllGroups', 'hocKies', 'boMons', 'maHocKy', 'isTaoNhomOpen', 'groupPhaseState', 'nhomHocKyHienTai') + ['NhomOpen' => $nhomsOpen]);
    }

    /**
     * Tự động tạo nhóm với tên mặc định là "Nhóm" + "MSSV của nhóm trưởng"
     */
    public function store(Request $request)
    {
        $request->validate([
            'MaHocKy'   => 'required|exists:HocKy,MaHocKy',
            'MaHocPhan' => 'required|exists:HocPhan,MaHocPhan',
        ], [
            'MaHocKy.required'   => 'Vui lòng chọn học kỳ muốn tạo nhóm.',
            'MaHocKy.exists'     => 'Học kỳ được chọn không hợp lệ trong hệ thống.',
            'MaHocPhan.required' => 'Vui lòng chọn học phần khóa luận muốn tạo nhóm.',
            'MaHocPhan.exists'   => 'Học phần được chọn không tồn tại trong hệ thống.',
        ]);

        // 0. Ràng buộc thời gian: Kiểm tra server-side còn trong thời hạn tạo nhóm theo Kế hoạch không
        $groupPhaseState = \App\Services\PlanPhaseService::getGroupPhaseState($request->MaHocKy);
        if (!$groupPhaseState['is_open']) {
            return redirect()->back()->withInput()->withErrors($groupPhaseState['message']);
        }

        $sinhVien = SinhVien::with('taiKhoan')->where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $hocPhan = \App\Models\HocPhan::findOrFail($request->MaHocPhan);
        $hocKy = \App\Models\HocKy::findOrFail($request->MaHocKy);

        // 1. Ràng buộc học kỳ & học phần: Học phần phải thuộc bộ môn của sinh viên và được mở trong học kỳ này
        $svBoMon = $sinhVien->getMaBoMon();
        if ($hocPhan->MaBoMon && $hocPhan->MaBoMon !== $svBoMon) {
            return redirect()->back()->withErrors("Học phần '{$hocPhan->TenHocPhan}' không thuộc bộ môn chuyên ngành của bạn!");
        }

        $isOpened = \App\Models\HocPhanHocKy::where('MaHocKy', $request->MaHocKy)
            ->where('MaHocPhan', $request->MaHocPhan)
            ->where('TrangThai', 'Đang mở')
            ->exists();

        if (!$isOpened) {
            return redirect()->back()->withErrors("Học phần '{$hocPhan->TenHocPhan}' hiện chưa được mở trong học kỳ {$hocKy->TenHocKy}!");
        }

        $maHocKy = $request->MaHocKy;
        $maHocPhan = $request->MaHocPhan;

        // 2. Kiểm tra điều kiện xét duyệt học vụ của sinh viên
        $isEligible = \App\Models\DanhSachSVDuDieuKien::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'Đủ điều kiện')
            ->exists() || $sinhVien->isDuDieuKien();

        if (!$isEligible) {
            return redirect()->back()->withErrors('Bạn chưa đủ điều kiện xét duyệt học vụ để tạo nhóm!');
        }

        // 3. RÀNG BUỘC: Mỗi sinh viên chỉ được tham gia tối đa 1 nhóm trong cùng một đợt/học kỳ khóa luận
        $alreadyInGroup = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->whereHas('nhom', function($q) use ($maHocKy) {
                $q->where('MaHocKy', $maHocKy);
            })
            ->with(['nhom.hocPhan'])
            ->first();

        if ($alreadyInGroup) {
            $nhomCu = $alreadyInGroup->nhom;
            $tenNhomCu = $nhomCu->TenNhom ?? $nhomCu->MaNhom;
            $tenHPCu = $nhomCu->hocPhan->TenHocPhan ?? $nhomCu->MaHocPhan;
            return redirect()->back()->withErrors("Bạn đã tham gia nhóm '{$tenNhomCu}' (Môn: {$tenHPCu}) trong học kỳ {$hocKy->TenHocKy}. Mỗi sinh viên chỉ được tham gia tối đa 1 nhóm trong cùng một học kỳ khóa luận!");
        }

        // Tên nhóm gán mặc định là: "Nhóm " + MSSV
        $mssv = $sinhVien->taiKhoan->TenDangNhap ?? $sinhVien->MaSV;
        $tenNhom = "Nhóm " . $mssv;

        DB::transaction(function () use ($tenNhom, $sinhVien, $maHocKy, $maHocPhan) {
            $maNhom = IdGenerator::nextNhom();

            Nhom::create([
                'MaNhom'    => $maNhom,
                'TenNhom'   => $tenNhom,
                'TrangThai' => 'Đang hoạt động',
                'NgayTao'   => now(),
                'MaHocKy'   => $maHocKy,
                'MaHocPhan' => $maHocPhan,
            ]);

            ThanhVienNhom::create([
                'MaNhom'      => $maNhom,
                'MaSV'        => $sinhVien->MaSV,
                'VaiTro'      => 'Trưởng nhóm',
                'TrangThai'   => 'da_tham_gia',
                'NgayThamGia' => now(),
            ]);

            // Dọn dẹp tất cả lời mời hoặc yêu cầu khác của sinh viên này CHO MÔN HỌC ĐÓ trong kỳ này
            ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
                ->where('MaNhom', '!=', $maNhom)
                ->whereHas('nhom', function($q) use ($maHocKy, $maHocPhan) {
                    $q->where('MaHocKy', $maHocKy)->where('MaHocPhan', $maHocPhan);
                })
                ->delete();
        });

        return redirect()->route('sinhvien.nhom.index', ['hoc_phan' => $maHocPhan, 'hoc_ky' => $maHocKy])
            ->with('success', "Khởi tạo '{$tenNhom}' cho môn {$hocPhan->TenHocPhan} ({$hocKy->TenHocKy}) thành công!");
    }

    /**
     * Lấy thông tin chi tiết thành viên của một nhóm (cho Modal Chi Tiết Nhóm theo spec)
     */
    public function chiTietNhom($maNhom)
    {
        $nhom = Nhom::with([
            'truongNhom.taiKhoan',
            'truongNhom.lop.nganh',
            'hocKy',
            'hocPhan',
            'thanhViens' => fn($q) => $q->where('TrangThai', 'da_tham_gia')->with('sinhVien.lop.nganh', 'sinhVien.taiKhoan')
        ])->findOrFail($maNhom);

        $currentUser = Auth::user();
        $currentUserSV = SinhVien::where('MaTK', $currentUser->MaTK)->first();

        $soLuong = $nhom->thanhViens->count();
        $conSlot = max(0, 3 - $soLuong);

        // Kiểm tra trạng thái của người xem
        $hasGroup = $currentUserSV ? ThanhVienNhom::where('MaSV', $currentUserSV->MaSV)->where('TrangThai', 'da_tham_gia')->exists() : false;
        $hasRequested = $currentUserSV ? ThanhVienNhom::where('MaNhom', $maNhom)->where('MaSV', $currentUserSV->MaSV)->where('TrangThai', 'xin_gia_nhap')->exists() : false;
        $isMyGroup = $currentUserSV ? ($nhom->MaTruongNhom === $currentUserSV->MaSV || $nhom->thanhViens->contains('MaSV', $currentUserSV->MaSV)) : false;

        $thanhViensData = [];
        // Trưởng nhóm đầu tiên
        if ($nhom->truongNhom) {
            $tn = $nhom->truongNhom;
            $thanhViensData[] = [
                'HoTen'        => $tn->HoTen,
                'MSSV'         => $tn->taiKhoan->TenDangNhap ?? $tn->MaSV,
                'TenLop'       => $tn->lop->TenLop ?? 'Chưa rõ',
                'TenNganh'     => $tn->lop->nganh->TenNganh ?? 'Công Nghệ Thông Tin',
                'VaiTro'       => 'Trưởng nhóm',
                'IsLeader'     => true,
            ];
        }

        // Các thành viên còn lại
        foreach ($nhom->thanhViens as $tv) {
            if ($tv->MaSV === $nhom->MaTruongNhom) continue;
            $sv = $tv->sinhVien;
            if (!$sv) continue;
            $thanhViensData[] = [
                'HoTen'        => $sv->HoTen,
                'MSSV'         => $sv->taiKhoan->TenDangNhap ?? $sv->MaSV,
                'TenLop'       => $sv->lop->TenLop ?? 'Chưa rõ',
                'TenNganh'     => $sv->lop->nganh->TenNganh ?? 'Công Nghệ Thông Tin',
                'VaiTro'       => 'Thành viên',
                'IsLeader'     => false,
            ];
        }

        return response()->json([
            'success'        => true,
            'nhom'           => [
                'MaNhom'     => $nhom->MaNhom,
                'TenNhom'    => $nhom->TenNhom,
                'MaHocKy'    => $nhom->MaHocKy,
                'TenHocKy'   => $nhom->hocKy->TenHocKy ?? 'Chưa xác định',
                'MaHocPhan'  => $nhom->MaHocPhan,
                'TenHocPhan' => $nhom->hocPhan->TenHocPhan ?? 'Khóa luận cử nhân',
                'SoLuong'    => $soLuong,
                'ConSlot'    => $conSlot,
                'DaDu'       => ($soLuong >= 3),
            ],
            'thanhViens'     => $thanhViensData,
            'userStatus'     => [
                'hasGroup'     => $hasGroup,
                'hasRequested' => $hasRequested,
                'isMyGroup'    => $isMyGroup,
            ]
        ]);
    }

    /**
     * Tra cứu sinh viên theo MSSV và trả về Student Verification Card (theo đúng promt_fix.txt)
     */
    public function traCuuSinhVien(Request $request)
    {
        $mssv = trim($request->input('mssv', ''));
        $maNhom = trim($request->input('ma_nhom', ''));

        if (empty($mssv)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập Mã số sinh viên (MSSV) để tra cứu.'
            ], 422);
        }

        $user = Auth::user();
        $currentUserSV = SinhVien::where('MaTK', $user->MaTK)->first();

        $mssvClean = preg_replace('/[^A-Za-z0-9]/', '', $mssv);

        // 1. Tìm sinh viên từ database (Hỗ trợ cả MSSV nhập có dấu chấm/cách và mã chuẩn)
        $sinhVien = SinhVien::with(['taiKhoan', 'lop.nganh'])
            ->where(function($q) use ($mssv, $mssvClean) {
                $q->where('MaSV', $mssv)
                  ->orWhereHas('taiKhoan', fn($tq) => $tq->where('TenDangNhap', $mssv));
                if (!empty($mssvClean)) {
                    $q->orWhere('MaSV', $mssvClean)
                      ->orWhereHas('taiKhoan', fn($tq) => $tq->where('TenDangNhap', $mssvClean));
                }
            })
            ->first();

        if (!$sinhVien) {
            return response()->json([
                'success' => false,
                'message' => "Không tìm thấy sinh viên nào có MSSV \"{$mssv}\" trong hệ thống."
            ], 404);
        }

        // Lấy nhóm hiện tại nếu có
        $nhom = $maNhom ? Nhom::with('dangKyDeTai')->find($maNhom) : null;

        // 2. Kiểm tra các điều kiện (Validation Rules):
        $canInvite = true;
        $statusText = '🟢 Chưa tham gia nhóm (Đủ điều kiện tham gia)';
        $badgeClass = 'bg-success';
        $isJoinRequest = false;

        // 2.1 Có phải chính người đang đăng nhập không?
        if ($currentUserSV && $sinhVien->MaSV === $currentUserSV->MaSV) {
            $canInvite = false;
            $statusText = '⚠️ Bạn không thể gửi lời mời cho chính mình.';
            $badgeClass = 'bg-warning text-dark';
        } else {
            // 2.2 Sinh viên có đủ điều kiện trong học kỳ này không?
            $isEligible = \App\Models\DanhSachSVDuDieuKien::where('MaSV', $sinhVien->MaSV)
                ->where('TrangThai', 'Đủ điều kiện')
                ->exists() || $sinhVien->isDuDieuKien();

            if (!$isEligible) {
                $canInvite = false;
                $statusText = '🔴 Chưa đủ điều kiện làm khóa luận (Tích lũy < 115 tín chỉ hoặc CPA < 2.0).';
                $badgeClass = 'bg-danger';
            } else {
                $inGroup = ThanhVienNhom::with('nhom.hocPhan')
                    ->where('MaSV', $sinhVien->MaSV)
                    ->where('TrangThai', 'da_tham_gia')
                    ->whereHas('nhom', function($q) use ($nhom) {
                        if ($nhom && $nhom->MaHocKy) {
                            $q->where('MaHocKy', $nhom->MaHocKy);
                        }
                    })
                    ->first();

                if ($inGroup) {
                    $canInvite = false;
                    $statusText = "🔴 Đã thuộc nhóm: \"{$inGroup->nhom->TenNhom}\" trong học kỳ này (mỗi SV chỉ thuộc 1 nhóm)";
                    $badgeClass = 'bg-danger';
                }
                // 2.3 Nhóm đã bị khóa do duyệt đề tài chưa?
                elseif ($nhom && $nhom->dangKyDeTai && in_array($nhom->dangKyDeTai->TrangThai, ['Chờ duyệt', 'Đã duyệt'])) {
                    $canInvite = false;
                    $statusText = '🔴 Nhóm đã gửi đơn đăng ký đề tài và bị khóa, không thể mời thêm thành viên.';
                    $badgeClass = 'bg-danger';
                }
                // 2.4 Nhóm đã đủ 3 thành viên chưa?
                elseif ($nhom && ThanhVienNhom::where('MaNhom', $nhom->MaNhom)->where('TrangThai', 'da_tham_gia')->count() >= 3) {
                    $canInvite = false;
                    $statusText = '🔴 Nhóm đã đạt tối đa 3 thành viên theo quy định.';
                    $badgeClass = 'bg-danger';
                }
                // 2.5 Đã có lời mời chờ xử lý từ nhóm này chưa?
                elseif ($nhom && ThanhVienNhom::where('MaNhom', $nhom->MaNhom)->where('MaSV', $sinhVien->MaSV)->where('TrangThai', 'cho_xac_nhan')->exists()) {
                    $canInvite = false;
                    $statusText = '🟡 Đang có lời mời chờ sinh viên phản hồi.';
                    $badgeClass = 'bg-warning text-dark';
                }
                // 2.6 Sinh viên đã gửi yêu cầu xin vào nhóm này chưa?
                elseif ($nhom && ThanhVienNhom::where('MaNhom', $nhom->MaNhom)->where('MaSV', $sinhVien->MaSV)->where('TrangThai', 'xin_gia_nhap')->exists()) {
                    $canInvite = true;
                    $isJoinRequest = true;
                    $statusText = '🔵 Sinh viên đã gửi yêu cầu xin vào nhóm trước đó. Bấm để duyệt ngay!';
                    $badgeClass = 'bg-info text-dark';
                }
            }
        }

        return response()->json([
            'success' => true,
            'student' => [
                'MaSV'        => $sinhVien->MaSV,
                'MSSV'        => $sinhVien->taiKhoan->TenDangNhap ?? $sinhVien->MaSV,
                'HoTen'       => $sinhVien->HoTen,
                'TenLop'      => $sinhVien->lop->TenLop ?? 'Chưa rõ',
                'TenNganh'    => $sinhVien->lop->nganh->TenNganh ?? 'Công Nghệ Thông Tin',
                'Email'       => $sinhVien->Email ?? ($sinhVien->taiKhoan->TenDangNhap . '@st.huit.edu.vn'),
                'SoDienThoai' => $sinhVien->SoDienThoai ?? 'Chưa cập nhật',
            ],
            'can_invite'      => $canInvite,
            'status_text'     => $statusText,
            'badge_class'     => $badgeClass,
            'is_join_request' => $isJoinRequest,
        ]);
    }

    /**
     * Xác nhận và gửi lời mời (Re-validation toàn bộ ở Backend trong DB Transaction)
     */
    public function moiThanhVien(Request $request)
    {
        $request->validate([
            'MaNhom' => 'required|exists:Nhom,MaNhom',
            'MaSV'   => 'required|exists:SinhVien,MaSV',
        ], [
            'MaNhom.required' => 'Thiếu thông tin nhóm.',
            'MaSV.required'   => 'Vui lòng tra cứu và chọn sinh viên muốn mời.',
        ]);

        $nhom = Nhom::with('dangKyDeTai')->findOrFail($request->MaNhom);

        // 0. Ràng buộc thời gian Kế hoạch của học kỳ
        $groupPhaseState = \App\Services\PlanPhaseService::getGroupPhaseState($nhom->MaHocKy);
        if (!$groupPhaseState['is_open']) {
            return redirect()->back()->withErrors($groupPhaseState['message']);
        }

        // 1. Kiểm tra quyền của người gửi lời mời
        if ($nhom->MaTruongNhom !== $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Chỉ Trưởng nhóm mới có quyền gửi lời mời thành viên!');
        }

        // 2. Kiểm tra nhóm đã khóa chưa
        if ($nhom->dangKyDeTai && in_array($nhom->dangKyDeTai->TrangThai, ['Chờ duyệt', 'Đã duyệt'])) {
            return redirect()->back()->withErrors('Nhóm đã gửi đơn đăng ký đề tài và bị khóa. Không thể mời thêm thành viên!');
        }

        // 3. Tái kiểm tra số lượng thành viên trong nhóm
        $count = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)
            ->where('TrangThai', 'da_tham_gia')
            ->count();

        if ($count >= 3) {
            return redirect()->back()->withErrors('Nhóm khóa luận đã đủ tối đa 3 thành viên theo quy định!');
        }

        // Lấy thông tin sinh viên được mời
        $svThem = SinhVien::with(['taiKhoan', 'lop'])->findOrFail($request->MaSV);

        if ($svThem->MaSV === $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Bạn không thể tự mời chính mình!');
        }

        // Kiểm tra xem sinh viên được mời có đủ điều kiện không
        $isEligible = \App\Models\DanhSachSVDuDieuKien::where('MaSV', $svThem->MaSV)
            ->where('TrangThai', 'Đủ điều kiện')
            ->exists() || $svThem->isDuDieuKien();

        if (!$isEligible) {
            return redirect()->back()->withErrors("Sinh viên {$svThem->HoTen} chưa đủ điều kiện làm khóa luận!");
        }

        // Tái kiểm tra sinh viên được mời đã có nhóm trong học kỳ này chưa (Chống Race Condition)
        $alreadyInGroup = ThanhVienNhom::where('MaSV', $svThem->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->whereHas('nhom', function($q) use ($nhom) {
                if ($nhom->MaHocKy) {
                    $q->where('MaHocKy', $nhom->MaHocKy);
                }
            })
            ->with('nhom')
            ->first();

        if ($alreadyInGroup) {
            return redirect()->back()->withErrors("Sinh viên {$svThem->HoTen} đã thuộc nhóm '{$alreadyInGroup->nhom->TenNhom}' trong học kỳ này!");
        }

        DB::transaction(function () use ($nhom, $svThem, $sinhVien) {
            // Nếu sinh viên trước đó đã gửi yêu cầu xin gia nhập -> Duyệt trực tiếp vào nhóm
            $requested = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)
                ->where('MaSV', $svThem->MaSV)
                ->where('TrangThai', 'xin_gia_nhap')
                ->first();

            if ($requested) {
                ThanhVienNhom::where('MaNhom', $nhom->MaNhom)
                    ->where('MaSV', $svThem->MaSV)
                    ->update([
                        'TrangThai'   => 'da_tham_gia',
                        'NgayThamGia' => now(),
                    ]);

                // Dọn dẹp lời mời khác của SV này
                ThanhVienNhom::where('MaSV', $svThem->MaSV)
                    ->where('MaNhom', '!=', $nhom->MaNhom)
                    ->delete();

                ThongBaoService::guiDen(
                    $svThem->MaTK,
                    '🎉 Yêu cầu gia nhập nhóm đã được duyệt!',
                    "Trưởng nhóm {$sinhVien->HoTen} đã chấp nhận yêu cầu của bạn vào nhóm '{$nhom->TenNhom}'.",
                    'Nhóm'
                );
                return;
            }

            // Kiểm tra lời mời trùng lặp
            $existingInvite = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)
                ->where('MaSV', $svThem->MaSV)
                ->where('TrangThai', 'cho_xac_nhan')
                ->exists();

            if ($existingInvite) {
                return;
            }

            ThanhVienNhom::updateOrCreate(
                [
                    'MaNhom' => $nhom->MaNhom,
                    'MaSV'   => $svThem->MaSV,
                ],
                [
                    'VaiTro'      => 'Thành viên',
                    'TrangThai'   => 'cho_xac_nhan',
                    'NgayThamGia' => null,
                ]
            );

            // Gửi thông báo đến sinh viên được mời
            ThongBaoService::guiDen(
                $svThem->MaTK,
                '📩 Bạn nhận được lời mời tham gia nhóm khóa luận!',
                "Trưởng nhóm {$sinhVien->HoTen} đã mời bạn tham gia nhóm '{$nhom->TenNhom}'. Vui lòng vào mục Nhóm Khóa Luận để xác nhận.",
                'Nhóm'
            );
        });

        return redirect()->back()->with('invite_success', "Đã gửi lời mời gia nhập nhóm đến sinh viên {$svThem->HoTen} ({$svThem->taiKhoan->TenDangNhap}) thành công!");
    }

    /**
     * Khai trừ thành viên khỏi nhóm (Chỉ Trưởng nhóm và trước khi duyệt đề tài)
     */
    public function khaiTruThanhVien($maNhom, $maSV)
    {
        $currentUser = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $currentUser->MaTK)->firstOrFail();
        $nhom = Nhom::with('dangKyDeTai')->findOrFail($maNhom);

        // 0. Ràng buộc thời gian Kế hoạch
        $groupPhaseState = \App\Services\PlanPhaseService::getGroupPhaseState($nhom->MaHocKy);
        if (!$groupPhaseState['is_open']) {
            return redirect()->back()->withErrors($groupPhaseState['message']);
        }

        // 1. Chỉ Trưởng nhóm mới có quyền
        if ($nhom->MaTruongNhom !== $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Chỉ Trưởng nhóm mới có quyền khai trừ thành viên!');
        }

        // 2. Không được tự khai trừ chính mình
        if ($sinhVien->MaSV === $maSV) {
            return redirect()->back()->withErrors('Trưởng nhóm không thể tự khai trừ chính mình!');
        }

        // 3. Khóa sau khi đề tài đã được duyệt chính thức hoặc chờ duyệt
        if ($nhom->dangKyDeTai && in_array($nhom->dangKyDeTai->TrangThai, ['Chờ duyệt', 'Đã duyệt'])) {
            return redirect()->back()->withErrors('Đề tài của nhóm đã được gửi đi. Nhóm đã bị khóa và không thể khai trừ thành viên!');
        }

        // 4. Kiểm tra thành viên có tồn tại trong nhóm không
        $targetMember = ThanhVienNhom::with('sinhVien')
            ->where('MaNhom', $maNhom)
            ->where('MaSV', $maSV)
            ->where('TrangThai', 'da_tham_gia')
            ->first();

        if (!$targetMember) {
            return redirect()->back()->withErrors('Không tìm thấy thành viên này trong nhóm.');
        }

        $tenSV = $targetMember->sinhVien->HoTen ?? $maSV;
        $maTK = $targetMember->sinhVien->MaTK ?? null;

        DB::transaction(function () use ($maNhom, $maSV, $maTK, $nhom, $sinhVien) {
            ThanhVienNhom::where('MaNhom', $maNhom)
                ->where('MaSV', $maSV)
                ->where('TrangThai', 'da_tham_gia')
                ->delete();

            if ($maTK) {
                ThongBaoService::guiDen(
                    $maTK,
                    '⚠️ Thông báo khai trừ khỏi nhóm',
                    "Bạn đã được Trưởng nhóm {$sinhVien->HoTen} khai trừ khỏi nhóm '{$nhom->TenNhom}'. Bạn hiện có thể tạo nhóm mới hoặc xin gia nhập nhóm khác.",
                    'Nhóm'
                );
            }
        });

        return redirect()->back()->with('success', "Đã khai trừ sinh viên {$tenSV} khỏi nhóm thành công.");
    }

    public function xinGiaNhap(Request $request, $maNhom)
    {
        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $nhom = Nhom::with('dangKyDeTai')->findOrFail($maNhom);

        // 0. Ràng buộc thời gian Kế hoạch
        $groupPhaseState = \App\Services\PlanPhaseService::getGroupPhaseState($nhom->MaHocKy);
        if (!$groupPhaseState['is_open']) {
            return redirect()->back()->withErrors($groupPhaseState['message']);
        }

        // Kiểm tra SV đã có nhóm trong học kỳ này chưa
        $alreadyInGroup = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->whereHas('nhom', function($q) use ($nhom) {
                if ($nhom->MaHocKy) {
                    $q->where('MaHocKy', $nhom->MaHocKy);
                }
            })
            ->with('nhom')
            ->first();

        if ($alreadyInGroup) {
            return redirect()->back()->withErrors("Bạn đã thuộc nhóm '{$alreadyInGroup->nhom->TenNhom}' trong học kỳ này rồi, không thể xin gia nhập nhóm khác.");
        }

        $isEligible = \App\Models\DanhSachSVDuDieuKien::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'Đủ điều kiện')
            ->exists() || $sinhVien->isDuDieuKien();

        if (!$isEligible) {
            return redirect()->back()->withErrors('Bạn chưa đủ điều kiện làm khóa luận để xin gia nhập nhóm!');
        }

        if ($nhom->dangKyDeTai && in_array($nhom->dangKyDeTai->TrangThai, ['Chờ duyệt', 'Đã duyệt'])) {
            return redirect()->back()->withErrors('Nhóm này đã gửi đơn đăng ký đề tài và bị khóa tuyển thành viên.');
        }

        $count = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)
            ->where('TrangThai', 'da_tham_gia')
            ->count();

        if ($count >= 3) {
            return redirect()->back()->withErrors('Nhóm này đã đủ 3 thành viên, không thể gửi yêu cầu xin gia nhập.');
        }

        // Nếu nhóm đã từng gửi lời mời cho SV này -> Nhắc nhở xác nhận lời mời thay vì tự động kết nối
        $invited = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)
            ->where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'cho_xac_nhan')
            ->first();

        if ($invited) {
            return redirect()->back()->with('warning', "Nhóm này đã gửi lời mời cho bạn trước đó. Bạn hãy bấm 'Chấp Nhận' ở danh sách Lời Mời Nhóm để tham gia!");
        }


        // Kiểm tra xem đã gửi yêu cầu xin vào nhóm này chưa
        $existing = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)
            ->where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'xin_gia_nhap')
            ->first();

        if ($existing) {
            return redirect()->back()->withErrors('Bạn đã gửi yêu cầu xin gia nhập nhóm này rồi. Vui lòng chờ Trưởng nhóm phê duyệt.');
        }

        ThanhVienNhom::create([
            'MaNhom'      => $nhom->MaNhom,
            'MaSV'        => $sinhVien->MaSV,
            'VaiTro'      => 'Thành viên',
            'TrangThai'   => 'xin_gia_nhap',
            'NgayThamGia' => null,
        ]);

        return redirect()->back()->with('success', "Đã gửi yêu cầu xin gia nhập nhóm '{$nhom->TenNhom}'. Vui lòng chờ Trưởng nhóm phê duyệt!");
    }

    public function huyXinGiaNhap($maNhom)
    {
        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();

        ThanhVienNhom::where('MaNhom', $maNhom)
            ->where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'xin_gia_nhap')
            ->delete();

        return redirect()->back()->with('success', 'Đã hủy yêu cầu xin gia nhập nhóm.');
    }

    public function duyetYeuCauXinVao($maNhom, $maSV)
    {
        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $nhom = Nhom::with('dangKyDeTai')->findOrFail($maNhom);

        $groupPhaseState = \App\Services\PlanPhaseService::getGroupPhaseState($nhom->MaHocKy);
        if (!$groupPhaseState['is_open']) {
            return redirect()->back()->withErrors($groupPhaseState['message']);
        }

        if ($nhom->MaTruongNhom !== $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Chỉ Trưởng nhóm mới có quyền phê duyệt yêu cầu xin vào nhóm!');
        }

        if ($nhom->dangKyDeTai && in_array($nhom->dangKyDeTai->TrangThai, ['Chờ duyệt', 'Đã duyệt'])) {
            return redirect()->back()->withErrors('Nhóm đã gửi đơn đăng ký đề tài và bị khóa. Không thể thêm thành viên mới!');
        }

        $count = ThanhVienNhom::where('MaNhom', $maNhom)
            ->where('TrangThai', 'da_tham_gia')
            ->count();

        if ($count >= 3) {
            return redirect()->back()->withErrors('Nhóm đã đủ 3 thành viên, không thể thêm thành viên mới!');
        }

        // Kiểm tra sinh viên xin vào đã có nhóm khác trong học kỳ này chưa
        $alreadyInAnother = ThanhVienNhom::where('MaSV', $maSV)
            ->where('TrangThai', 'da_tham_gia')
            ->whereHas('nhom', function($q) use ($nhom) {
                if ($nhom->MaHocKy) {
                    $q->where('MaHocKy', $nhom->MaHocKy);
                }
            })
            ->exists();

        if ($alreadyInAnother) {
            ThanhVienNhom::where('MaNhom', $maNhom)->where('MaSV', $maSV)->delete();
            return redirect()->back()->withErrors('Sinh viên này đã tham gia một nhóm khác trong học kỳ này. Yêu cầu đã tự động bị hủy.');
        }

        ThanhVienNhom::where('MaNhom', $maNhom)
            ->where('MaSV', $maSV)
            ->where('TrangThai', 'xin_gia_nhap')
            ->update([
                'TrangThai'   => 'da_tham_gia',
                'NgayThamGia' => now(),
            ]);

        // Dọn dẹp các lời mời hoặc yêu cầu khác của sinh viên này
        ThanhVienNhom::where('MaSV', $maSV)
            ->where('MaNhom', '!=', $maNhom)
            ->delete();

        return redirect()->back()->with('success', 'Đã phê duyệt thành viên vào nhóm thành công!');
    }

    public function tuChoiYeuCauXinVao($maNhom, $maSV)
    {
        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $nhom = Nhom::findOrFail($maNhom);

        if ($nhom->MaTruongNhom !== $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Chỉ Trưởng nhóm mới có quyền từ chối yêu cầu xin vào nhóm!');
        }

        ThanhVienNhom::where('MaNhom', $maNhom)
            ->where('MaSV', $maSV)
            ->where('TrangThai', 'xin_gia_nhap')
            ->delete();

        return redirect()->back()->with('success', 'Đã từ chối yêu cầu xin gia nhập nhóm.');
    }

    public function xacNhanLoiMoi($maNhom)
    {
        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $nhom = Nhom::findOrFail($maNhom);

        $groupPhaseState = \App\Services\PlanPhaseService::getGroupPhaseState($nhom->MaHocKy);
        if (!$groupPhaseState['is_open']) {
            return redirect()->back()->withErrors($groupPhaseState['message']);
        }

        // 1. Kiểm tra lời mời có tồn tại cho sinh viên này không
        $hasInvite = ThanhVienNhom::where('MaNhom', $maNhom)
            ->where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'cho_xac_nhan')
            ->exists();

        if (!$hasInvite) {
            return redirect()->back()->withErrors('Không tìm thấy lời mời này.');
        }

        // 2. Kiểm tra SV đã thuộc nhóm khác trong học kỳ này chưa
        $alreadyInAnotherGroup = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->whereHas('nhom', function($q) use ($nhom) {
                if ($nhom->MaHocKy) {
                    $q->where('MaHocKy', $nhom->MaHocKy);
                }
            })
            ->with('nhom')
            ->first();

        if ($alreadyInAnotherGroup) {
            return redirect()->back()->withErrors("Bạn đã thuộc nhóm '{$alreadyInAnotherGroup->nhom->TenNhom}' trong học kỳ này rồi. Mỗi sinh viên chỉ được tham gia tối đa 1 nhóm trong cùng một học kỳ!");
        }

        // 3. Kiểm tra số lượng thành viên hiện tại của nhóm
        $count = ThanhVienNhom::where('MaNhom', $maNhom)
            ->where('TrangThai', 'da_tham_gia')
            ->count();

        if ($count >= 3) {
            ThanhVienNhom::where('MaNhom', $maNhom)
                ->where('MaSV', $sinhVien->MaSV)
                ->where('TrangThai', 'cho_xac_nhan')
                ->delete();
            return redirect()->back()->withErrors('Không thể gia nhập: Nhóm đã đủ 3 thành viên!');
        }

        // 4. CHỈ CẬP NHẬT ĐÚNG BẢN GHI CỦA SINH VIÊN HIỆN TẠI
        ThanhVienNhom::where('MaNhom', $maNhom)
            ->where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'cho_xac_nhan')
            ->update([
                'TrangThai'   => 'da_tham_gia',
                'NgayThamGia' => now(),
            ]);

        // 5. Dọn dẹp tất cả các lời mời hoặc yêu cầu khác của sinh viên này ở các nhóm khác trong cùng học kỳ
        ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('MaNhom', '!=', $maNhom)
            ->whereHas('nhom', function($q) use ($nhom) {
                if ($nhom->MaHocKy) {
                    $q->where('MaHocKy', $nhom->MaHocKy);
                }
            })
            ->delete();

        return redirect()->route('sinhvien.nhom.index', ['hoc_phan' => $nhom->MaHocPhan, 'hoc_ky' => $nhom->MaHocKy])
            ->with('success', 'Bạn đã tham gia nhóm thành công!');
    }

    public function tuChoiLoiMoi($maNhom)
    {
        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();
        ThanhVienNhom::where('MaNhom', $maNhom)
            ->where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'cho_xac_nhan')
            ->delete();

        return redirect()->back()->with('success', 'Đã từ chối lời mời gia nhập nhóm.');
    }

    public function huyLoiMoiDaGui($maNhom, $maSV)
    {
        $sinhVien = SinhVien::where('MaTK', Auth::user()->MaTK)->firstOrFail();
        $nhom = Nhom::findOrFail($maNhom);

        if ($nhom->MaTruongNhom !== $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Chỉ Trưởng nhóm mới có quyền thu hồi lời mời!');
        }

        ThanhVienNhom::where('MaNhom', $maNhom)
            ->where('MaSV', $maSV)
            ->where('TrangThai', 'cho_xac_nhan')
            ->delete();

        return redirect()->back()->with('success', 'Đã thu hồi lời mời thành viên.');
    }

    public function myTasks()
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->first();
        if (!$sinhVien) {
            return redirect()->route('sinhvien.dashboard');
        }

        $thanhVien = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->where('TrangThai', 'da_tham_gia')->first();
        $nhom = $thanhVien ? Nhom::with(['deTai', 'truongNhom', 'dangKyDeTai.giangVienHuongDan', 'baoCaos', 'hoSoBaoVe'])->find($thanhVien->MaNhom) : null;

        $activePlan = \App\Services\PlanPhaseService::getActivePlan();
        $taskList = [];
        $today = \Carbon\Carbon::today();

        if ($activePlan && $nhom) {
            foreach ($activePlan->mocThoiGians as $moc) {
                $statusLabel = 'Chưa bắt đầu';
                $badgeClass = 'bg-secondary';
                $warning = null;

                $startDate = \Carbon\Carbon::parse($moc->NgayBatDau);
                $endDate = \Carbon\Carbon::parse($moc->NgayKetThuc);
                $daysLeft = (int) $today->diffInDays($endDate, false);

                if (str_contains($moc->LoaiGiaiDoan, 'BAO_CAO_TIEN_DO')) {
                    $lan = str_contains($moc->LoaiGiaiDoan, '1') ? 1 : (str_contains($moc->LoaiGiaiDoan, '2') ? 2 : 3);
                    $bc = $nhom->baoCaos->where('LanBaoCao', $lan)->first();
                    if ($bc) {
                        $statusLabel = 'Đã nộp (' . date('d/m/Y', strtotime($bc->NgayNop)) . ')';
                        $badgeClass = 'bg-success';
                    } else {
                        if ($daysLeft < 0) {
                            $statusLabel = 'Quá hạn nộp!';
                            $badgeClass = 'bg-danger';
                            $warning = 'Đã quá hạn ' . abs($daysLeft) . ' ngày';
                        } elseif ($daysLeft <= 7) {
                            $statusLabel = 'Sắp đến hạn';
                            $badgeClass = 'bg-warning text-dark';
                            $warning = 'Còn ' . $daysLeft . ' ngày nữa hết hạn';
                        } else {
                            $statusLabel = $today->between($startDate, $endDate) ? 'Đang diễn ra' : 'Chưa đến mốc';
                            $badgeClass = $today->between($startDate, $endDate) ? 'bg-primary' : 'bg-secondary';
                        }
                    }
                } elseif ($moc->LoaiGiaiDoan === 'DAO_VAN') {
                    if ($nhom->hoSoBaoVe && $nhom->hoSoBaoVe->TyLeTrungLap !== null) {
                        $statusLabel = 'Đã hoàn thành (' . $nhom->hoSoBaoVe->TyLeTrungLap . '%)';
                        $badgeClass = 'bg-success';
                    } else {
                        $statusLabel = ($daysLeft < 0) ? 'Quá hạn' : (($daysLeft <= 7) ? 'Sắp đến hạn' : 'Chưa đến mốc');
                        $badgeClass = ($daysLeft < 0) ? 'bg-danger' : (($daysLeft <= 7) ? 'bg-warning text-dark' : 'bg-secondary');
                    }
                } else {
                    $statusLabel = ($daysLeft < 0) ? 'Đã qua' : ($today->between($startDate, $endDate) ? 'Đang diễn ra' : 'Chưa đến mốc');
                    $badgeClass = ($daysLeft < 0) ? 'bg-dark' : ($today->between($startDate, $endDate) ? 'bg-primary' : 'bg-secondary');
                }

                $taskList[] = [
                    'moc'          => $moc,
                    'status_label' => $statusLabel,
                    'badge_class'  => $badgeClass,
                    'warning'      => $warning,
                ];
            }
        }

        return view('sinhvien.my_tasks', compact('nhom', 'activePlan', 'taskList'));
    }
}