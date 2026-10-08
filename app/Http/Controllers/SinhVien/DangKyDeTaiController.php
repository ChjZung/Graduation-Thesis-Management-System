<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\DeTai;
use App\Models\DangKyDeTai;
use App\Models\Nhom;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Lấy học kỳ
        $requestedHocKy = $request->input('MaHocKy') ?? $request->input('HocKy') ?? $request->input('hoc_ky');
        $activePlan = \App\Services\PlanPhaseService::getActivePlan();
        $defaultHocKy = $activePlan ? $activePlan->MaHocKy : null;
        if (!$defaultHocKy) {
            $currentHk = \App\Models\HocKy::where('TrangThai', 'Đang diễn ra')->first() ?? \App\Models\HocKy::latest('MaHocKy')->first();
            $defaultHocKy = $currentHk?->MaHocKy;
        }
        $maHocKy = $requestedHocKy ?? $defaultHocKy;

        // 2. Lấy danh sách Môn / Học phần và Bộ môn từ DB
        $hocPhans = \App\Models\HocPhan::where('TrangThai', 'Đang áp dụng')
            ->orderBy('MaKhoa')
            ->orderBy('MaBoMon')
            ->get();
        $boMons = \App\Models\BoMon::where('MaKhoa', 'CNTT')->orderBy('TenBoMon')->get();

        $selectedHocPhan = $request->input('HocPhan') ?? $request->input('hoc_phan') ?? $request->input('MaHocPhan');
        $selectedBoMon = $request->input('bo_mon') ?? $request->input('MaBoMon');

        if (!$selectedBoMon && $selectedHocPhan) {
            $currentHp = $hocPhans->firstWhere('MaHocPhan', $selectedHocPhan);
            if ($currentHp) {
                $selectedBoMon = $currentHp->MaBoMon ?: 'DUNG_CHUNG';
            }
        }
        if (!$selectedBoMon) {
            $selectedBoMon = 'DUNG_CHUNG';
        }

        // 1. Kiểm tra Nhóm của sinh viên theo môn và học kỳ
        $thanhVienQuery = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia');

        if ($selectedHocPhan) {
            $thanhVienQuery->whereHas('nhom', function($q) use ($selectedHocPhan, $maHocKy) {
                if ($maHocKy) {
                    $q->where(function($sq) use ($maHocKy) {
                        $sq->where('MaHocKy', $maHocKy)->orWhereNull('MaHocKy');
                    });
                }
                $q->where(function($sq) use ($selectedHocPhan) {
                    $sq->where('MaHocPhan', $selectedHocPhan)
                      ->orWhereHas('hocPhan', fn($hpq) => $hpq->where('TenHocPhan', $selectedHocPhan));
                });
            });
        } elseif ($maHocKy) {
            $thanhVienQuery->whereHas('nhom', function($q) use ($maHocKy) {
                $q->where('MaHocKy', $maHocKy)->orWhereNull('MaHocKy');
            });
        }

        $thanhVienRecord = $thanhVienQuery->first() ?? ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->where('TrangThai', 'da_tham_gia')->first();

        $nhom = null;
        $dangKyCurrent = null;
        $soThanhVien = 0;

        if ($thanhVienRecord) {
            $nhom = Nhom::with(['thanhViens', 'hocPhan'])->where('MaNhom', $thanhVienRecord->MaNhom)->first();
            if ($nhom) {
                $soThanhVien = $nhom->thanhViens->where('TrangThai', 'da_tham_gia')->count();
                $dangKyCurrent = DangKyDeTai::with('deTai.giangVien')
                    ->where('MaNhom', $nhom->MaNhom)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
        }

        $hocKies = \App\Models\HocKy::orderBy('MaHocKy', 'desc')->get();
        $selectedHocKy = $requestedHocKy ?? ($nhom?->MaHocKy ?? $maHocKy);

        // 3. Lọc danh sách đề tài theo học kỳ, bộ môn và môn học đã công bố
        $query = DeTai::with(['giangVien.boMon', 'nganh', 'hocPhanRef', 'hocKy'])->where('TrangThai', 'Đã công bố');

        if ($selectedHocKy) {
            $query->where('MaHocKy', $selectedHocKy);
        }

        if ($selectedHocPhan) {
            $query->where(function($q) use ($selectedHocPhan) {
                $q->where('MaHocPhan', $selectedHocPhan)
                  ->orWhere('HocPhan', $selectedHocPhan);
            });
        } elseif ($selectedBoMon === 'DUNG_CHUNG') {
            $query->whereHas('hocPhanRef', fn($q) => $q->whereNull('MaBoMon'));
        } elseif ($selectedBoMon && $selectedBoMon !== 'ALL') {
            $query->where(function($q) use ($selectedBoMon) {
                $q->whereHas('giangVien', fn($gv) => $gv->where('MaBoMon', $selectedBoMon))
                  ->orWhereHas('hocPhanRef', fn($hp) => $hp->where('MaBoMon', $selectedBoMon));
            });
        }

        if ($request->filled('search')) {
            $query->where('TenDeTai', 'LIKE', '%' . trim($request->search) . '%');
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // 4. Lấy map các đề tài đã có nhóm đăng ký ('Chờ duyệt' hoặc 'Đã duyệt')
        $deTaiDaDangKys = DangKyDeTai::whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])
            ->with('nhom')
            ->get()
            ->keyBy('MaDeTai');

        $regState = \App\Services\PlanPhaseService::getRegistrationState();

        return view('sinhvien.dangky.index', compact('detais', 'nhom', 'dangKyCurrent', 'sinhVien', 'soThanhVien', 'deTaiDaDangKys', 'hocPhans', 'selectedHocPhan', 'selectedBoMon', 'hocKies', 'boMons', 'selectedHocKy', 'maHocKy', 'regState'));
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
        $deTai = DeTai::findOrFail($request->MaDeTai);

        // 0. Kiểm tra đề tài đã được công bố chính thức chưa
        if ($deTai->TrangThai !== 'Đã công bố') {
            return redirect()->back()->withErrors('Đề tài này chưa được công bố chính thức cho sinh viên đăng ký!');
        }

        // 1. Kiểm tra sinh viên có thuộc nhóm chính thức tương ứng không
        $thanhVienQuery = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia');

        if ($deTai->MaHocPhan) {
            $thanhVienQuery->whereHas('nhom', function($q) use ($deTai) {
                $q->where('MaHocPhan', $deTai->MaHocPhan)->orWhereNull('MaHocPhan');
            });
        }

        $thanhVienRecord = $thanhVienQuery->first() ?? ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->where('TrangThai', 'da_tham_gia')->first();

        if (!$thanhVienRecord) {
            return redirect()->back()->withErrors('Bạn chưa có nhóm khóa luận cho môn học này! Vui lòng tạo nhóm hoặc gia nhập nhóm trước khi đăng ký đề tài.');
        }

        $nhom = Nhom::where('MaNhom', $thanhVienRecord->MaNhom)->firstOrFail();

        // 2. Chỉ Trưởng nhóm được đăng ký
        if ($nhom->MaTruongNhom != $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Chỉ Trưởng nhóm mới có quyền đại diện đăng ký đề tài!');
        }

        // 3. QUY ĐỊNH: Nhóm phải có ĐỦ 3 THÀNH VIÊN chính thức mới được phép đăng ký đề tài
        $countMembers = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)
            ->where('TrangThai', 'da_tham_gia')
            ->count();

        $minRequired = 3;
        if ($countMembers < $minRequired) {
            return redirect()->back()->withErrors("Quy định: Nhóm phải có đủ {$minRequired} thành viên mới được phép đăng ký đề tài! Hiện tại nhóm của bạn có {$countMembers}/{$minRequired} thành viên. Vui lòng mời hoặc phê duyệt thêm thành viên trước khi đăng ký đề tài.");
        }

        $maxSV = $deTai->SoLuongSinhVienToiDa ?? 3;
        if ($countMembers > $maxSV) {
            return redirect()->back()->withErrors("Đề tài '{$deTai->TenDeTai}' chỉ tiếp nhận tối đa {$maxSV} sinh viên. Nhóm của bạn hiện có {$countMembers} thành viên!");
        }

        // 4. Kiểm tra đề tài đã được nhóm khác đăng ký chưa (Chặn nếu đã có nhóm đăng ký)
        $alreadyTaken = DangKyDeTai::where('MaDeTai', $deTai->MaDeTai)
            ->whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])
            ->exists();

        if ($alreadyTaken) {
            return redirect()->back()->withErrors('Đề tài này đã có nhóm khác đăng ký. Vui lòng chọn đề tài khác!');
        }

        // 5. Kiểm tra nhóm đã đăng ký đề tài nào chưa (Chặn nhóm đã đăng ký đề tài)
        $existingRegistration = DangKyDeTai::where('MaNhom', $nhom->MaNhom)
            ->whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])
            ->first();

        if ($existingRegistration) {
            return redirect()->back()->withErrors('Nhóm của bạn đã đăng ký đề tài rồi! Mỗi nhóm chỉ được đăng ký 1 đề tài.');
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

        $nhom->update(['MaDeTai' => $deTai->MaDeTai]);

        return redirect()->back()->with('success', "Đăng ký đề tài '{$deTai->TenDeTai}' thành công! Đề tài đã được gán trực tiếp cho nhóm của bạn.");
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
}