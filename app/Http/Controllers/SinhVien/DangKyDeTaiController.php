<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\DeTai;
use App\Models\DangKyDeTai;
use App\Models\Nhom;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use App\Models\GiangVien;
use App\Services\ChiTieuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DangKyDeTaiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->first();

        if (!$sinhVien) {
            return redirect()->route('sinhvien.nhom.index')
                ->with('error', 'Hồ sơ sinh viên chưa được thiết lập. Vui lòng liên hệ Giáo vụ Khoa.');
        }

        // 1. Kiểm tra nhóm của sinh viên (ưu tiên nhóm mà sinh viên đã tham gia chính thức)
        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->with(['nhom.thanhViens', 'nhom.hocPhan', 'nhom.hocKy'])
            ->latest('created_at')
            ->first();

        $nhom = null;
        $dangKyCurrent = null;
        $soThanhVien = 0;
        $isGroupLocked = false;

        if ($thanhVienRecord) {
            $nhom = Nhom::with(['thanhViens.sinhVien', 'hocPhan', 'hocKy'])->where('MaNhom', $thanhVienRecord->MaNhom)->first();
            if ($nhom) {
                $isGroupLocked = true;
                $soThanhVien = $nhom->thanhViens->where('TrangThai', 'da_tham_gia')->count();
                $dangKyCurrent = DangKyDeTai::with([
                    'deTai.giangVien.boMon',
                    'deTai.hocPhanRef',
                    'deTai.hocKy',
                    'deTai.nganh',
                    'nhom.thanhViens.sinhVien'
                ])
                    ->where('MaNhom', $nhom->MaNhom)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
        }

        // Lấy học kỳ
        $activePlan = \App\Services\PlanPhaseService::getActivePlan();
        $defaultHocKy = $activePlan ? $activePlan->MaHocKy : null;
        if (!$defaultHocKy) {
            $currentHk = \App\Models\HocKy::where('TrangThai', 'Đang diễn ra')->first() ?? \App\Models\HocKy::latest('MaHocKy')->first();
            $defaultHocKy = $currentHk?->MaHocKy;
        }

        $svBoMon = $sinhVien->getMaBoMon();

        if ($isGroupLocked) {
            // P3 -> B2: Sau khi tạo nhóm, khóa bộ lọc ở trang đăng ký đề tài;
            // Bộ lọc cố định tương đồng với bộ lọc của học phần và học kỳ của nhóm đó.
            $selectedHocKy = $nhom->MaHocKy ?: $defaultHocKy;
            $maHocKy = $selectedHocKy;
            $selectedHocPhan = $nhom->MaHocPhan;
            $selectedBoMon = $nhom->hocPhan?->MaBoMon ?: $svBoMon;
        } else {
            // Khi chưa tạo nhóm: Cho phép lọc tự do theo request
            $requestedHocKy = $request->input('MaHocKy') ?? $request->input('HocKy') ?? $request->input('hoc_ky');
            $selectedHocKy = $requestedHocKy ?? $defaultHocKy;
            $maHocKy = $selectedHocKy;
            $selectedBoMon = $svBoMon;
            $selectedHocPhan = $request->input('HocPhan') ?? $request->input('hoc_phan') ?? $request->input('MaHocPhan');
        }

        // 2. Lấy danh sách Môn / Học phần
        $hocPhans = \App\Models\HocPhan::where(function($q) {
                $q->whereIn('TrangThai', ['Đang sử dụng', 'Đang áp dụng', '1'])
                  ->orWhere('TrangThai', true);
            })
            ->where(function($q) use ($selectedBoMon, $selectedHocPhan) {
                $q->where('MaBoMon', $selectedBoMon);
                if ($selectedHocPhan) {
                    $q->orWhere('MaHocPhan', $selectedHocPhan);
                }
            })
            ->orderBy('TenHocPhan')
            ->get();

        if (!$isGroupLocked && $selectedHocPhan && !$hocPhans->firstWhere('MaHocPhan', $selectedHocPhan)) {
            $selectedHocPhan = null;
        }

        $boMons = \App\Models\BoMon::where('MaKhoa', 'CNTT')->orderBy('TenBoMon')->get();
        $hocKies = \App\Models\HocKy::orderBy('MaHocKy', 'desc')->get();

        // 3. Lọc danh sách đề tài theo học kỳ, bộ môn và môn học đã công bố
        $query = DeTai::with(['giangVien.boMon', 'nganh', 'hocPhanRef', 'hocKy'])->where('TrangThai', 'Đã công bố');

        if ($selectedHocKy) {
            $query->where('MaHocKy', $selectedHocKy);
        }

        // Lọc theo bộ môn chuyên ngành của sinh viên / học phần
        $query->where(function($q) use ($selectedBoMon) {
            $q->whereHas('giangVien', fn($gv) => $gv->where('MaBoMon', $selectedBoMon))
              ->orWhereHas('hocPhanRef', fn($hp) => $hp->where('MaBoMon', $selectedBoMon));
        });

        if ($selectedHocPhan) {
            $query->where(function($q) use ($selectedHocPhan, $nhom) {
                $q->where('MaHocPhan', $selectedHocPhan)
                  ->orWhere('HocPhan', $selectedHocPhan);
                if ($nhom && $nhom->hocPhan && $nhom->hocPhan->TenHocPhan) {
                    $q->orWhere('HocPhan', $nhom->hocPhan->TenHocPhan);
                }
            });
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $query->where(function($q) use ($s, $sClean) {
                $q->where('TenDeTai', 'LIKE', "%{$s}%")
                  ->orWhere('MaDeTai', 'LIKE', "%{$s}%")
                  ->orWhereHas('giangVien', fn($gv) => $gv->where('HoTen', 'LIKE', "%{$s}%"));
                if (!empty($sClean)) {
                    $q->orWhere('MaDeTai', 'LIKE', "%{$sClean}%");
                }
            });
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(5)->withQueryString();

        // 4. Lấy map các đề tài đã có nhóm đăng ký ('Chờ duyệt' hoặc 'Đã duyệt')
        $deTaiDaDangKys = DangKyDeTai::whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])
            ->with('nhom')
            ->get()
            ->keyBy('MaDeTai');

        $regState = \App\Services\PlanPhaseService::getRegistrationState();

        return view('sinhvien.dangky.index', compact(
            'detais', 'nhom', 'dangKyCurrent', 'sinhVien', 'soThanhVien',
            'deTaiDaDangKys', 'hocPhans', 'selectedHocPhan', 'selectedBoMon',
            'hocKies', 'boMons', 'selectedHocKy', 'maHocKy', 'regState', 'isGroupLocked'
        ));
    }

    public function store(Request $request)
    {
        // 0. Kiểm tra tiến độ thời gian theo Kế hoạch
        $regState = \App\Services\PlanPhaseService::getRegistrationState();
        if (!$regState['can_register']) {
            return redirect()->back()->withErrors($regState['message']);
        }

        $request->validate([
            'MaDeTai' => 'required|exists:DeTai,MaDeTai',
        ], [
            'MaDeTai.required' => 'Vui lòng chọn đề tài muốn đăng ký.',
        ]);

        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->firstOrFail();

        try {
            return DB::transaction(function () use ($request, $sinhVien) {
                // 1. Khóa dòng đề tài để kiểm tra trạng thái và tránh race condition
                $deTai = DeTai::where('MaDeTai', $request->MaDeTai)->lockForUpdate()->firstOrFail();

                // Kiểm tra đề tài đã được công bố chính thức chưa
                if ($deTai->TrangThai !== 'Đã công bố') {
                    throw new \Exception('Đề tài này chưa được công bố chính thức cho sinh viên đăng ký!');
                }

                // 2. Kiểm tra sinh viên có thuộc nhóm chính thức tương ứng không
                $thanhVienQuery = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
                    ->where('TrangThai', 'da_tham_gia');

                if ($deTai->MaHocPhan) {
                    $thanhVienQuery->whereHas('nhom', function($q) use ($deTai) {
                        $q->where('MaHocPhan', $deTai->MaHocPhan)->orWhereNull('MaHocPhan');
                    });
                }

                $thanhVienRecord = $thanhVienQuery->first() ?? ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->where('TrangThai', 'da_tham_gia')->first();

                if (!$thanhVienRecord) {
                    throw new \Exception('Bạn chưa có nhóm khóa luận cho môn học này! Vui lòng tạo nhóm hoặc gia nhập nhóm trước khi đăng ký đề tài.');
                }

                // 3. Khóa dòng nhóm
                $nhom = Nhom::where('MaNhom', $thanhVienRecord->MaNhom)->lockForUpdate()->firstOrFail();

                // Kiểm tra tương thích Học Phần & Học Kỳ giữa Nhóm và Đề Tài (B2)
                if (!empty($nhom->MaHocPhan) && !empty($deTai->MaHocPhan) && $nhom->MaHocPhan !== $deTai->MaHocPhan) {
                    throw new \Exception("Không thể đăng ký: Đề tài này thuộc học phần '{$deTai->MaHocPhan}', nhưng nhóm của bạn được tạo cho học phần '{$nhom->MaHocPhan}'. Vui lòng chỉ chọn đề tài thuộc đúng học phần của nhóm.");
                }

                if (!empty($nhom->MaHocKy) && !empty($deTai->MaHocKy) && $nhom->MaHocKy !== $deTai->MaHocKy) {
                    throw new \Exception("Không thể đăng ký: Đề tài này thuộc học kỳ '{$deTai->MaHocKy}', nhưng nhóm của bạn thuộc học kỳ '{$nhom->MaHocKy}'.");
                }

                // Chỉ Trưởng nhóm được đăng ký
                if ($nhom->MaTruongNhom != $sinhVien->MaSV) {
                    throw new \Exception('Chỉ Trưởng nhóm mới có quyền đại diện đăng ký đề tài!');
                }

                // QUY ĐỊNH: Nhóm phải có ĐỦ 3 THÀNH VIÊN chính thức mới được phép đăng ký đề tài
                $countMembers = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)
                    ->where('TrangThai', 'da_tham_gia')
                    ->count();

                $minRequired = 3;
                if ($countMembers < $minRequired) {
                    throw new \Exception("Quy định: Nhóm phải có đủ {$minRequired} thành viên mới được phép đăng ký đề tài! Hiện tại nhóm của bạn có {$countMembers}/{$minRequired} thành viên. Vui lòng mời hoặc phê duyệt thêm thành viên trước khi đăng ký đề tài.");
                }

                $maxSV = $deTai->SoLuongSinhVienToiDa ?? 3;
                if ($countMembers > $maxSV) {
                    throw new \Exception("Đề tài '{$deTai->TenDeTai}' chỉ tiếp nhận tối đa {$maxSV} sinh viên. Nhóm của bạn hiện có {$countMembers} thành viên!");
                }

                // 4. Kiểm tra đề tài đã được nhóm khác đăng ký chưa (có khóa dòng)
                $alreadyTaken = DangKyDeTai::where('MaDeTai', $deTai->MaDeTai)
                    ->whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyTaken) {
                    throw new \Exception('Đề tài này đã có nhóm khác đăng ký thành công. Vui lòng chọn đề tài khác!');
                }

                // 5. Kiểm tra nhóm đã đăng ký đề tài nào chưa (có khóa dòng)
                $existingRegistration = DangKyDeTai::where('MaNhom', $nhom->MaNhom)
                    ->whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])
                    ->lockForUpdate()
                    ->first();

                if ($existingRegistration) {
                    throw new \Exception('Nhóm của bạn đã đăng ký đề tài rồi! Mỗi nhóm chỉ được đăng ký 1 đề tài.');
                }

                // 6. Kiểm tra chỉ tiêu hướng dẫn của Giảng viên (P1: lấy từ DB qua ChiTieuService, khóa dòng, không hard-code 5)
                $gvDeTai = $deTai->MaGV;
                if ($gvDeTai) {
                    $gvRecord = GiangVien::where('MaGV', $gvDeTai)->lockForUpdate()->first();
                    $chiTieuStats = ChiTieuService::getChiTieuStats($gvDeTai, $deTai->MaHocKy, true);

                    if ($chiTieuStats['is_full']) {
                        $tenGV = $gvRecord?->HoTen ?? $gvDeTai;
                        throw new \Exception("Giảng viên hướng dẫn ({$tenGV}) đã nhận đủ chỉ tiêu hướng dẫn ({$chiTieuStats['da_dung']}/{$chiTieuStats['tong']} nhóm) trong học kỳ này! Hết chỉ tiêu, vui lòng chọn đề tài của giảng viên khác.");
                    }
                }

                $maDK = 'DK_' . Str::upper(Str::random(6));

                // Đăng ký xong thì gán trực tiếp cho nhóm luôn, không cần ai duyệt nữa
                DangKyDeTai::create([
                    'MaDangKy'     => $maDK,
                    'MaNhom'       => $nhom->MaNhom,
                    'MaDeTai'      => $deTai->MaDeTai,
                    'MaGVHuongDan' => $deTai->MaGV,
                    'NgayDangKy'   => now(),
                    'TrangThai'    => 'Đã duyệt',
                    'NgayDuyet'    => now(),
                ]);

                return redirect()->back()->with('success', "Đăng ký đề tài '{$deTai->TenDeTai}' thành công! Đề tài đã được gán trực tiếp cho nhóm của bạn.");
            });
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function destroy($id)
    {
        $regState = \App\Services\PlanPhaseService::getRegistrationState();
        if (!$regState['can_register']) {
            return redirect()->back()->withErrors('Cổng đăng ký đề tài hiện đang tạm khóa hoặc đã đóng. Không thể hủy đăng ký đề tài!');
        }

        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->firstOrFail();

        $dangKy = DangKyDeTai::with(['nhom', 'deTai'])->findOrFail($id);

        if ($dangKy->nhom->MaTruongNhom != $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Chỉ Trưởng nhóm mới có quyền hủy đăng ký đề tài!');
        }

        // Quy định: Đề tài của nhóm sinh viên sau khi được Trưởng khoa duyệt thì không được phép hủy, chỉ đợi nhận đề cương
        if ($dangKy->deTai && (in_array($dangKy->deTai->TrangThai, ['Đã duyệt', 'Trưởng khoa đã duyệt', 'Đã công bố']) || !empty($dangKy->deTai->NgayDuyetKhoa))) {
            return redirect()->back()->withErrors('Đề tài của nhóm đã được Trưởng khoa phê duyệt chính thức và chốt danh sách thực hiện, không được phép hủy! Nhóm vui lòng chờ Giảng viên hướng dẫn nộp Đề cương chi tiết.');
        }

        $dangKy->nhom->update(['MaDeTai' => null]);
        $dangKy->delete();

        return redirect()->back()->with('success', 'Đã hủy đăng ký đề tài thành công.');
    }

    public function showDeTai($maDeTai)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->first();

        $deTai = DeTai::with(['giangVien.boMon', 'hocPhanRef.boMon', 'hocKy', 'nganh'])
            ->where('MaDeTai', $maDeTai)
            ->firstOrFail();

        // Thông tin nhóm của sinh viên (nếu có)
        $nhom = null;
        $dangKyCurrent = null;
        $soThanhVien = 0;
        if ($sinhVien) {
            $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
                ->where('TrangThai', 'da_tham_gia')
                ->latest('created_at')
                ->first();

            if ($thanhVienRecord) {
                $nhom = Nhom::with(['thanhViens.sinhVien', 'hocPhan', 'hocKy'])->where('MaNhom', $thanhVienRecord->MaNhom)->first();
                if ($nhom) {
                    $soThanhVien = $nhom->thanhViens->where('TrangThai', 'da_tham_gia')->count();
                    $dangKyCurrent = DangKyDeTai::where('MaNhom', $nhom->MaNhom)
                        ->orderBy('created_at', 'desc')
                        ->first();
                }
            }
        }

        // Kiểm tra đề tài này đã có nhóm nào đăng ký chưa
        $daDangKy = DangKyDeTai::where('MaDeTai', $deTai->MaDeTai)
            ->whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])
            ->with('nhom')
            ->first();

        $regState = \App\Services\PlanPhaseService::getRegistrationState();

        return view('sinhvien.dangky.show_detai', compact(
            'deTai', 'nhom', 'dangKyCurrent', 'sinhVien', 'soThanhVien', 'daDangKy', 'regState'
        ));
    }
}