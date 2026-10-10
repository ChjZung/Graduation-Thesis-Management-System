<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\DeTai;
use App\Models\GiangVien;
use App\Models\HocKy;
use App\Models\Nhom;
use App\Models\DangKyDeTai;
use App\Models\PhanCongPhanBien;
use App\Models\ThanhVienNhom;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeTaiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);

        if (!$gv) {
            return redirect()->back()->withErrors('Không tìm thấy thông tin Giảng viên liên kết với tài khoản này.');
        }

        $query = DeTai::with(['giangVien', 'hocKy', 'dangKyDeTais.nhom.thanhViens.sinhVien', 'phanCongPhanBiens.giangVien'])->where('MaGV', $gv->MaGV);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $sClean = preg_replace('/[^A-Za-z0-9]/', '', $s);
            $query->where(function($q) use ($s, $sClean) {
                $q->where('TenDeTai', 'LIKE', "%{$s}%")
                  ->orWhere('MaDeTai', 'LIKE', "%{$s}%");
                if (!empty($sClean)) {
                    $q->orWhere('MaDeTai', 'LIKE', "%{$sClean}%");
                }
            });
        }

        if ($request->filled('MaHocKy')) {
            $query->where('MaHocKy', $request->MaHocKy);
        }

        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai);
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        // Danh sách các nhóm chưa có đề tài đã duyệt
        $nhomsChuaCoDeTai = Nhom::with(['truongNhom.taiKhoan', 'thanhViens.sinhVien', 'hocKy', 'hocPhan'])
            ->whereDoesntHave('phieuDangKys', fn($q) => $q->where('TrangThai', 'Đã duyệt'))
            ->get();

        return view('giangvien.detai.index', compact('detais', 'hocKies', 'gv', 'nhomsChuaCoDeTai') + ['NhomChuaCoDeTai' => $nhomsChuaCoDeTai]);
    }

    public function downloadTemplate(Request $request)
    {
        $type = $request->get('type', 'cu_nhan');

        switch ($type) {
            case 'ky_su':
                $file = 'Mau_De_Cuong_Khoa_Luan_Ky_Su.docx';
                $downloadName = 'Mau_De_Cuong_Khoa_Luan_Ky_Su_HUIT.docx';
                break;
            case 'do_an':
                $file = 'Mau_De_Cuong_Do_An_Tot_Nghiep.docx';
                $downloadName = 'Mau_De_Cuong_Do_An_Tot_Nghiep_HUIT.docx';
                break;
            case 'cu_nhan':
            default:
                $file = 'Mau_De_Cuong_Khoa_Luan_Cu_Nhan.docx';
                $downloadName = 'Mau_De_Cuong_Khoa_Luan_Cu_Nhan_HUIT.docx';
                break;
        }

        $filePath = public_path("templates/{$file}");
        if (!file_exists($filePath)) {
            $filePath = public_path('templates/Mau_De_Cuong_Khoa_Luan.docx');
        }

        if (!file_exists($filePath)) {
            return redirect()->back()->withErrors('Không tìm thấy file biểu mẫu đề cương.');
        }

        return response()->download($filePath, $downloadName);
    }

    public function create()
    {
        $user = Auth::user();
        $gv = GiangVien::with('boMon')->where('MaTK', $user->MaTK)->first() 
              ?? GiangVien::getLoggedInGiangVien($user);
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        // Lấy danh sách Học phần thuộc Bộ môn của GV kèm các Học kỳ đang mở (HocPhan_HocKy)
        // Chỉ lấy học phần cho Khóa luận tốt nghiệp
        $hocPhans = \App\Models\HocPhan::with(['hocPhanHocKies' => function($q) {
                $q->where('TrangThai', 'Đang mở');
            }])
            ->where('LoaiHocPhan', 'Khóa luận')
            ->where(function($q) {
                $q->whereIn('TrangThai', ['Đang sử dụng', 'Đang áp dụng', '1'])
                  ->orWhere('TrangThai', true);
            })
            ->when($gv && $gv->MaBoMon, function($q) use ($gv) {
                $q->where('MaBoMon', $gv->MaBoMon);
            })
            ->orderBy('TenHocPhan')
            ->get();

        return view('giangvien.detai.create', compact('hocKies', 'gv', 'hocPhans'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $gv = GiangVien::with('boMon')->where('MaTK', $user->MaTK)->first() 
              ?? GiangVien::getLoggedInGiangVien($user);
        if (!$gv) {
            return redirect()->back()->withErrors('Không tìm thấy thông tin Giảng viên của tài khoản này.');
        }

        $request->validate([
            'TenDeTai' => 'required|string|max:300',
            'MaHocKy' => 'required|exists:HocKy,MaHocKy',
            'MaHocPhan' => 'required|exists:HocPhan,MaHocPhan',
            'MoTa' => 'nullable|string',
            'YeuCau' => 'nullable|string',
            'LinhVuc' => 'nullable|string|max:150',
        ], [
            'TenDeTai.required' => 'Vui lòng nhập tên đề tài.',
            'MaHocKy.required' => 'Vui lòng chọn học kỳ áp dụng.',
            'MaHocPhan.required' => 'Vui lòng chọn môn / học phần cho đề tài.',
            'MaHocPhan.exists' => 'Môn / học phần được chọn không tồn tại trong hệ thống.',
        ]);

        $hocPhan = \App\Models\HocPhan::findOrFail($request->MaHocPhan);

        // Kiểm tra quyền bộ môn: học phần phải thuộc đúng bộ môn của GV
        if ($gv->MaBoMon && $hocPhan->MaBoMon !== $gv->MaBoMon) {
            return redirect()->back()->withInput()->withErrors("Học phần '{$hocPhan->TenHocPhan}' không thuộc bộ môn " . ($gv->boMon->TenBoMon ?? $gv->MaBoMon) . " của bạn!");
        }

        // Kiểm tra học phần có được mở trong học kỳ này không
        $isOpened = \App\Models\HocPhanHocKy::where('MaHocKy', $request->MaHocKy)
            ->where('MaHocPhan', $request->MaHocPhan)
            ->where('TrangThai', 'Đang mở')
            ->exists();

        if (!$isOpened) {
            return redirect()->back()->withInput()->withErrors("Học phần '{$hocPhan->TenHocPhan}' hiện chưa được mở trong học kỳ đã chọn!");
        }

        // Kiểm tra định mức chỉ tiêu đề tài của giảng viên trong học kỳ (tối đa 5 đề tài)
        $maxDinhMuc = 5;
        $chiTieuDb = \Illuminate\Support\Facades\DB::table('chitieuhuongdan')
            ->where('MaGV', $gv->MaGV)
            ->where('MaHocKy', $request->MaHocKy)
            ->value('SoNhomToiDa');
        if ($chiTieuDb && $chiTieuDb > 0) {
            $maxDinhMuc = (int)$chiTieuDb;
        }

        $currentTopicsCount = DeTai::where('MaGV', $gv->MaGV)
            ->where('MaHocKy', $request->MaHocKy)
            ->count();

        if ($currentTopicsCount >= $maxDinhMuc) {
            return redirect()->back()->withInput()->withErrors("Bạn đã đề xuất đạt định mức chỉ tiêu tối đa ({$currentTopicsCount}/{$maxDinhMuc} đề tài) trong học kỳ này theo quy định của Khoa!");
        }

        $count = DeTai::count() + 1;
        $maDT = 'DT' . sprintf('%02d', $count);
        while (DeTai::where('MaDeTai', $maDT)->exists()) {
            $count++;
            $maDT = 'DT' . sprintf('%02d', $count);
        }

        DeTai::create([
            'MaDeTai' => $maDT,
            'MaGV' => $gv->MaGV,
            'TenDeTai' => $request->TenDeTai,
            'MoTa' => $request->MoTa,
            'YeuCau' => $request->YeuCau,
            'LinhVuc' => $request->LinhVuc ?? 'Công Nghệ Thông Tin',
            'MaHocPhan' => $hocPhan->MaHocPhan,
            'HocPhan' => $hocPhan->TenHocPhan,
            'SoLuongSinhVienToiDa' => 3, // Gán cứng 3 sinh viên theo quy định
            'FileDeCuong' => null,
            'MaNganh' => null, // Đã bỏ theo yêu cầu, sinh viên đăng ký theo đúng ngành mình học
            'MaHocKy' => $request->MaHocKy,
            'TrangThai' => 'Chờ duyệt cấp Bộ môn',
            'NgayDeXuat' => now(),
        ]);

        return redirect()->route('giangvien.detai.index')->with('success', "Đề xuất đề tài '{$request->TenDeTai}' cho môn {$hocPhan->TenHocPhan} thành công! Đề tài đã được chuyển tới Trưởng bộ môn phê duyệt.");
    }

    public function show($id)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) return redirect()->route('giangvien.dashboard');

        $detai = DeTai::with([
            'hocKy',
            'giangVien.boMon',
            'taiLieuNops' => fn($q) => $q->orderBy('LanNop', 'desc'),
            'phanCongPhanBiens.giangVien',
            'dangKyDeTais.nhom.thanhViens.sinhVien',
            'dangKyDeTais.nhom.truongNhom'
        ])->where('MaDeTai', $id)->where('MaGV', $gv->MaGV)->firstOrFail();

        return view('giangvien.detai.show', compact('detai', 'gv'));
    }

    public function showNopDeCuong($id)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) return redirect()->route('giangvien.dashboard');

        $detai = DeTai::with([
            'hocKy',
            'giangVien.boMon',
            'taiLieuNops' => fn($q) => $q->orderBy('LanNop', 'desc'),
            'phanCongPhanBiens.giangVien',
            'dangKyDeTais.nhom.thanhViens.sinhVien',
            'dangKyDeTais.nhom.truongNhom'
        ])->where('MaDeTai', $id)->where('MaGV', $gv->MaGV)->firstOrFail();

        // 1. Kiểm tra xem đề tài đã được duyệt đề xuất 2 cấp (Bộ môn & Khoa) chưa
        if (in_array($detai->TrangThai, ['Chờ duyệt cấp Bộ môn', 'Chờ duyệt cấp Khoa', 'Từ chối'])) {
            return redirect()->route('giangvien.detai.index')
                ->withErrors('Đề tài hiện đang ở trạng thái "' . $detai->TrangThai . '". Chỉ đề tài đã được Trưởng bộ môn & Trưởng khoa phê duyệt chủ trương đề xuất và công bố mới có thể nộp đề cương.');
        }

        // 2. Quy trình: Đề tài phải có nhóm sinh viên tham gia đăng ký / gán thành công
        $dangKyApproved = $detai->dangKyDeTais->where('TrangThai', 'Đã duyệt')->first();
        $hasGroupAssigned = !empty($dangKyApproved) && !empty($dangKyApproved->nhom);

        if (!$hasGroupAssigned) {
            return redirect()->route('giangvien.detai.index')
                ->withErrors("Đề tài '{$detai->TenDeTai}' hiện chưa có nhóm sinh viên đăng ký! Theo quy định, chỉ sau khi sinh viên đăng ký đề tài (hoặc Giảng viên gán nhóm đủ 3 thành viên) thì mới được nộp đề cương chi tiết.");
        }

        // 3. Kiểm tra đề cương đã hoàn tất thẩm định và chốt công bố chưa
        $phanBien = $detai->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
        $isOutlineFinalized = !empty($detai->FileDeCuong) && $phanBien && $phanBien->KetQua === 'Đạt' && !empty($detai->NgayDuyetBM);
        $isLocked = $isOutlineFinalized || ($detai->TrangThai === 'Hoàn thành');
        $isNopLai = in_array($detai->TrangThai, ['Yêu cầu chỉnh sửa đề cương', 'Đã cập nhật đề cương - Chờ phản biện lại']);

        return view('giangvien.detai.nop_decuong', compact('detai', 'gv', 'isLocked', 'isNopLai'));
    }

    public function nopDeCuong(Request $request, $id)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) return redirect()->route('giangvien.dashboard');

        $detai = DeTai::with(['phanCongPhanBiens', 'dangKyDeTais.nhom'])->where('MaDeTai', $id)->where('MaGV', $gv->MaGV)->firstOrFail();

        // 1. Ràng buộc: Đề tài phải có nhóm sinh viên đăng ký
        $dangKyApproved = $detai->dangKyDeTais->where('TrangThai', 'Đã duyệt')->first();
        $hasGroupAssigned = !empty($dangKyApproved) && !empty($dangKyApproved->nhom);
        if (!$hasGroupAssigned) {
            return redirect()->back()->withErrors('Đề tài chưa có nhóm sinh viên đăng ký! Vui lòng chờ sinh viên đăng ký đề tài hoặc gán nhóm trước khi nộp đề cương.');
        }

        // 2. Ràng buộc: Khi đề cương đã hoàn tất thẩm định đạt và TBM đã duyệt chốt thì khóa nộp
        $phanBien = $detai->phanCongPhanBiens->firstWhere('VaiTro', 'Phản biện đề cương');
        $isOutlineFinalized = !empty($detai->FileDeCuong) && $phanBien && $phanBien->KetQua === 'Đạt' && !empty($detai->NgayDuyetBM);
        if ($isOutlineFinalized || $detai->TrangThai === 'Hoàn thành') {
            return redirect()->back()->withErrors('Đề cương đã hoàn tất quy trình thẩm định và đã được phê duyệt chính thức. Đề cương hiện đã bị khóa chỉnh sửa.');
        }

        $allowedStates = [
            'Đã công bố',
            'Trưởng khoa đã duyệt - Chờ nộp đề cương', 
            'Yêu cầu chỉnh sửa đề cương', 
            'Trưởng khoa đã duyệt', 
            'Đã duyệt',
            'Đang phản biện đề cương',
            'Đã cập nhật đề cương - Chờ phản biện lại',
            'Đã nộp đề cương - Chờ phân công PB',
            'Đã phản biện - Chờ duyệt BM',
            'Đã phản biện - Chờ TBM duyệt đề cương'
        ];
        if (!in_array($detai->TrangThai, $allowedStates)) {
            return redirect()->back()->withErrors('Đề tài hiện tại chưa được phê duyệt đề xuất hoặc không ở trạng thái được phép nộp đề cương chi tiết.');
        }

        $request->validate([
            'FileDeCuong' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ], [
            'FileDeCuong.required' => 'Vui lòng chọn file đề cương chi tiết.',
            'FileDeCuong.mimes'    => 'File đề cương phải có định dạng .pdf, .doc hoặc .docx.',
            'FileDeCuong.max'      => 'Dung lượng file đề cương tối đa là 10MB.',
        ]);

        $file = $request->file('FileDeCuong');
        $filename = 'de_cuong_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('de_cuong', $filename, 'public');

        $isNopLai = in_array($detai->TrangThai, ['Đã cập nhật đề cương - Chờ phản biện lại', 'Yêu cầu chỉnh sửa đề cương']);
        $lanNop = \App\Models\TaiLieuNop::where('MaDeTai', $detai->MaDeTai)->where('LoaiTaiLieu', 'Đề cương chi tiết')->count() + 1;

        // Lưu vết lịch sử nộp tài liệu đề cương
        \App\Models\TaiLieuNop::create([
            'MaTaiLieu'   => 'TL_' . strtoupper(Str::random(8)),
            'TenTaiLieu'  => $file->getClientOriginalName(),
            'DuongDan'    => 'storage/' . $path,
            'LoaiTaiLieu' => 'Đề cương chi tiết',
            'LanNop'      => $lanNop,
            'NgayNop'     => now(),
            'GhiChu'      => $isNopLai ? 'Nộp lại đề cương sau khi chỉnh sửa theo góp ý phản biện' : 'Nộp đề cương chi tiết lần ' . $lanNop,
            'MaDeTai'     => $detai->MaDeTai,
        ]);

        // Kiểm tra xem đề tài đã có Giảng viên phản biện chưa
        $phanCongPB = PhanCongPhanBien::with('giangVien')
            ->where('MaDeTai', $detai->MaDeTai)
            ->where('VaiTro', 'Phản biện đề cương')
            ->first();

        if ($phanCongPB) {
            // Đã có phân công phản biện trước đó -> Đây là lần nộp lại sau khi chỉnh sửa
            $detai->update([
                'FileDeCuong' => 'storage/' . $path,
                'TrangThai'   => 'Đã cập nhật đề cương - Chờ phản biện lại',
            ]);

            $phanCongPB->update([
                'TrangThai'   => 'Đã nộp lại đề cương',
                'KetQua'      => 'Đã nộp lại',
                'NgayDanhGia' => null,
            ]);

            // Gửi thông báo đến GV phản biện
            if ($phanCongPB->giangVien && $phanCongPB->giangVien->MaTK) {
                ThongBaoService::guiDen(
                    $phanCongPB->giangVien->MaTK,
                    '🔔 Giảng viên đã nộp lại Đề cương chỉnh sửa',
                    "Giảng viên {$gv->HoTen} đã cập nhật và nộp lại Đề cương chi tiết cho đề tài '{$detai->TenDeTai}' theo góp ý của Thầy/Cô. Vui lòng vào xem và phản biện lại.",
                    'Đề tài'
                );
            }

            // Gửi thông báo đến Trưởng Bộ Môn
            $maBoMon = $gv->MaBoMon ?? $detai->giangVien?->MaBoMon;
            $tbmUser = \App\Models\TaiKhoan::where(function($q) {
                    $q->where('MaVaiTro', 'VT04')
                      ->orWhere('TenDangNhap', 'LIKE', 'TBM_%');
                })
                ->where(function($q) use ($maBoMon) {
                    if ($maBoMon) {
                        $q->where('TenDangNhap', 'LIKE', '%' . $maBoMon . '%');
                    }
                })->first();

            if ($tbmUser) {
                ThongBaoService::guiDen(
                    $tbmUser->MaTK,
                    '📄 Đề cương đã được nộp lại sau chỉnh sửa',
                    "Giảng viên {$gv->HoTen} đã nộp lại file Đề cương đã chỉnh sửa cho đề tài '{$detai->TenDeTai}'. Đề cương đã được chuyển lại cho GV phản biện ({$phanCongPB->giangVien->HoTen}) xem xét.",
                    'Đề tài'
                );
            }

            return redirect()->route('giangvien.detai.nop_decuong.show', $detai->MaDeTai)->with('success', "Đã nộp lại Đề cương chi tiết (Lần {$lanNop}) cho đề tài '{$detai->TenDeTai}' thành công! Hệ thống đã gửi thông báo đến Giảng viên phản biện ({$phanCongPB->giangVien->HoTen}) để thẩm định lại.");
        } else {
            // Nộp lần đầu
            $detai->update([
                'FileDeCuong' => 'storage/' . $path,
                'TrangThai'   => 'Đã nộp đề cương - Chờ phân công PB',
            ]);

            $maBoMon = $gv->MaBoMon ?? $detai->giangVien?->MaBoMon;
            $tbmUser = \App\Models\TaiKhoan::where(function($q) {
                    $q->where('MaVaiTro', 'VT04')
                      ->orWhere('TenDangNhap', 'LIKE', 'TBM_%');
                })
                ->where(function($q) use ($maBoMon) {
                    if ($maBoMon) {
                        $q->where('TenDangNhap', 'LIKE', '%' . $maBoMon . '%');
                    }
                })->first();

            if ($tbmUser) {
                ThongBaoService::guiDen(
                    $tbmUser->MaTK,
                    '📄 Giảng viên đã nộp đề cương chi tiết',
                    "Giảng viên {$gv->HoTen} vừa nộp file Đề cương chi tiết (Lần {$lanNop}) cho đề tài '{$detai->TenDeTai}'. Vui lòng tiến hành phân công Giảng viên phản biện.",
                    'Đề tài'
                );
            }

            return redirect()->route('giangvien.detai.nop_decuong.show', $detai->MaDeTai)->with('success', "Đã nộp Đề cương chi tiết cho đề tài '{$detai->TenDeTai}' thành công! Đề cương đã được chuyển tới Trưởng bộ môn để phân công phản biện.");
        }
    }

    public function edit($id)
    {
        $user = Auth::user();
        $gv = GiangVien::with('boMon')->where('MaTK', $user->MaTK)->first() 
              ?? GiangVien::getLoggedInGiangVien($user);
        if (!$gv) return redirect()->route('giangvien.dashboard');
        $detai = DeTai::where('MaDeTai', $id)->where('MaGV', $gv->MaGV)->firstOrFail();
        if ($detai->TrangThai === 'Từ chối') {
            return redirect()->route('giangvien.detai.show', $detai->MaDeTai)
                ->withErrors('Đề tài này đã bị từ chối phê duyệt. Bạn chỉ có thể xem chi tiết đề tài.');
        }
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        // Lấy danh sách Học phần thuộc Bộ môn của GV kèm các Học kỳ đang mở (HocPhan_HocKy)
        // Chỉ lấy học phần cho Khóa luận tốt nghiệp
        $hocPhans = \App\Models\HocPhan::with(['hocPhanHocKies' => function($q) {
                $q->where('TrangThai', 'Đang mở');
            }])
            ->where('LoaiHocPhan', 'Khóa luận')
            ->where(function($q) {
                $q->whereIn('TrangThai', ['Đang sử dụng', 'Đang áp dụng', '1'])
                  ->orWhere('TrangThai', true);
            })
            ->when($gv && $gv->MaBoMon, function($q) use ($gv) {
                $q->where('MaBoMon', $gv->MaBoMon);
            })
            ->orderBy('TenHocPhan')
            ->get();

        $banNhap = $detai->ban_nhap_data;
        return view('giangvien.detai.edit', compact('detai', 'hocKies', 'gv', 'hocPhans', 'banNhap'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $gv = GiangVien::with('boMon')->where('MaTK', $user->MaTK)->first() 
              ?? GiangVien::getLoggedInGiangVien($user);
        if (!$gv) return redirect()->route('giangvien.dashboard');
        $detai = DeTai::where('MaDeTai', $id)->where('MaGV', $gv->MaGV)->firstOrFail();

        if ($detai->TrangThai === 'Từ chối') {
            return redirect()->route('giangvien.detai.show', $detai->MaDeTai)
                ->withErrors('Đề tài này đã bị từ chối phê duyệt. Không thể chỉnh sửa hoặc nộp lại.');
        }

        $request->validate([
            'TenDeTai' => 'required|string|max:300',
            'MaHocKy' => 'required|exists:HocKy,MaHocKy',
            'MaHocPhan' => 'required|exists:HocPhan,MaHocPhan',
            'MoTa' => 'nullable|string',
            'YeuCau' => 'nullable|string',
            'LinhVuc' => 'nullable|string|max:150',
            'FileDeCuong' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ], [
            'TenDeTai.required' => 'Vui lòng nhập tên đề tài.',
            'MaHocKy.required' => 'Vui lòng chọn học kỳ áp dụng.',
            'MaHocPhan.required' => 'Vui lòng chọn môn / học phần cho đề tài.',
            'MaHocPhan.exists' => 'Môn / học phần được chọn không tồn tại trong hệ thống.',
        ]);

        $hocPhan = \App\Models\HocPhan::findOrFail($request->MaHocPhan);

        // Kiểm tra quyền bộ môn: học phần phải thuộc bộ môn của GV
        if ($gv->MaBoMon && $hocPhan->MaBoMon !== $gv->MaBoMon) {
            return redirect()->back()->withInput()->withErrors("Học phần '{$hocPhan->TenHocPhan}' không thuộc bộ môn " . ($gv->boMon->TenBoMon ?? $gv->MaBoMon) . " của bạn!");
        }

        // Kiểm tra học phần có được mở trong học kỳ này không
        $isOpened = \App\Models\HocPhanHocKy::where('MaHocKy', $request->MaHocKy)
            ->where('MaHocPhan', $request->MaHocPhan)
            ->where('TrangThai', 'Đang mở')
            ->exists();

        if (!$isOpened) {
            return redirect()->back()->withInput()->withErrors("Học phần '{$hocPhan->TenHocPhan}' hiện chưa được mở trong học kỳ đã chọn!");
        }

        $actionType = $request->input('action_type', 'resubmit');

        // NẾU LƯU BẢN NHÁP (DRAFT): CHỈ LƯU VÀO DuLieuNhap, BẢN CHÍNH Ở MỤC DUYỆT GIỮ NGUYÊN 100%
        if ($actionType === 'draft') {
            $draftData = [
                'TenDeTai'    => $request->TenDeTai,
                'MaHocKy'     => $request->MaHocKy,
                'MaHocPhan'   => $hocPhan->MaHocPhan,
                'HocPhan'     => $hocPhan->TenHocPhan,
                'LinhVuc'     => $request->LinhVuc,
                'MoTa'        => $request->MoTa,
                'YeuCau'      => $request->YeuCau,
                'saved_at'    => now()->format('H:i d/m/Y'),
            ];

            // Nếu có upload file đề cương mới trong bản nháp
            if ($request->hasFile('FileDeCuong') && $request->file('FileDeCuong')->isValid()) {
                $file = $request->file('FileDeCuong');
                $filename = 'de_cuong_draft_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('de_cuong', $filename, 'public');
                $draftData['FileDeCuong'] = 'storage/' . $path;
            } else {
                $draftData['FileDeCuong'] = $detai->ban_nhap_data['FileDeCuong'] ?? $detai->FileDeCuong;
            }

            $detai->update([
                'DuLieuNhap' => json_encode($draftData, JSON_UNESCAPED_UNICODE),
            ]);

            return redirect()->route('giangvien.detai.index')
                ->with('success', "Đã lưu các thay đổi vào Bản nháp thành công! Bản chính ở mục nộp duyệt vẫn được giữ nguyên cho đến khi bạn nộp lại bản nháp.");
        }

        // NẾU NỘP LẠI (RESUBMIT): ÁP DỤNG THAY ĐỔI VÀO BẢN CHÍNH VÀ XÓA BẢN NHÁP
        $data = [
            'TenDeTai'             => $request->TenDeTai,
            'MaHocKy'              => $request->MaHocKy,
            'MaHocPhan'            => $hocPhan->MaHocPhan,
            'HocPhan'              => $hocPhan->TenHocPhan,
            'MaNganh'              => null,
            'SoLuongSinhVienToiDa' => 3, // Gán cứng 3 sinh viên theo quy định
            'MoTa'                 => $request->MoTa,
            'YeuCau'               => $request->YeuCau,
            'LinhVuc'              => $request->LinhVuc,
            'DuLieuNhap'           => null, // Xóa bản nháp sau khi đã nộp chính thức
        ];

        $canUploadOutline = $detai->FileDeCuong || in_array($detai->TrangThai, ['Trưởng khoa đã duyệt - Chờ nộp đề cương', 'Đã nộp đề cương - Chờ phân công PB', 'Yêu cầu chỉnh sửa đề cương', 'Đang phản biện đề cương', 'Đã cập nhật đề cương - Chờ phản biện lại']);
        if ($canUploadOutline && $request->hasFile('FileDeCuong') && $request->file('FileDeCuong')->isValid()) {
            $file = $request->file('FileDeCuong');
            $filename = 'de_cuong_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('de_cuong', $filename, 'public');
            $data['FileDeCuong'] = 'storage/' . $path;

            $phanCongPB = PhanCongPhanBien::with('giangVien')
                ->where('MaDeTai', $detai->MaDeTai)
                ->where('VaiTro', 'Phản biện đề cương')
                ->first();
            if ($phanCongPB) {
                $data['TrangThai'] = 'Đã cập nhật đề cương - Chờ phản biện lại';
                $phanCongPB->update([
                    'TrangThai'   => 'Đã nộp lại đề cương',
                    'KetQua'      => 'Đã nộp lại',
                    'NgayDanhGia' => null,
                ]);

                if ($phanCongPB->giangVien && $phanCongPB->giangVien->MaTK) {
                    ThongBaoService::guiDen(
                        $phanCongPB->giangVien->MaTK,
                        '🔔 Giảng viên đã nộp lại Đề cương chỉnh sửa',
                        "Giảng viên {$gv->HoTen} đã cập nhật file Đề cương chi tiết cho đề tài '{$detai->TenDeTai}'. Vui lòng vào xem và phản biện lại.",
                        'Đề tài'
                    );
                }
            } elseif ($detai->TrangThai === 'Trưởng khoa đã duyệt - Chờ nộp đề cương') {
                $data['TrangThai'] = 'Đã nộp đề cương - Chờ phân công PB';
            }
        }

        if (in_array($detai->TrangThai, ['Yêu cầu điều chỉnh', 'Yêu cầu chỉnh sửa', 'Chờ duyệt cấp Bộ môn'])) {
            $data['TrangThai'] = 'Chờ duyệt cấp Bộ môn';
            $data['NgayDeXuat'] = now();
            $data['LyDoTuChoi'] = null;
        }

        $detai->update($data);

        // Gửi thông báo đến Trưởng Bộ Môn khi nộp lại phê duyệt
        $maBoMon = $gv->MaBoMon ?? $detai->giangVien?->MaBoMon;
        $tbmUser = \App\Models\TaiKhoan::where(function($q) {
                $q->where('MaVaiTro', 'VT04')
                  ->orWhere('TenDangNhap', 'LIKE', 'TBM_%');
            })
            ->where(function($q) use ($maBoMon) {
                if ($maBoMon) {
                    $q->where('TenDangNhap', 'LIKE', '%' . $maBoMon . '%');
                }
            })->first();

        if ($tbmUser) {
            \App\Services\ThongBaoService::guiDen(
                $tbmUser->MaTK,
                '📝 Đề tài đã được nộp lại phê duyệt',
                "Giảng viên {$gv->HoTen} đã chỉnh sửa và nộp lại đề tài '{$detai->TenDeTai}' để Trưởng bộ môn phê duyệt.",
                'Đề tài'
            );
        }

        return redirect()->route('giangvien.detai.index')->with('success', "Đã nộp lại yêu cầu phê duyệt đề tài '{$detai->TenDeTai}' thành công! Hồ sơ đã được chuyển tới Trưởng bộ môn xem xét.");
    }

    public function discardDraft($id)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) return redirect()->route('giangvien.dashboard');
        $detai = DeTai::where('MaDeTai', $id)->where('MaGV', $gv->MaGV)->firstOrFail();

        $detai->update(['DuLieuNhap' => null]);

        return redirect()->route('giangvien.detai.edit', $detai->MaDeTai)
            ->with('success', 'Đã hủy bản nháp thành công! Các thông tin đã được khôi phục về bản chính ban đầu.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) return redirect()->route('giangvien.dashboard');
        $detai = DeTai::where('MaDeTai', $id)->where('MaGV', $gv->MaGV)->firstOrFail();

        // Chặn xóa nếu đề tài bị từ chối
        if ($detai->TrangThai === 'Từ chối') {
            return redirect()->back()->withErrors('Đề tài đã bị từ chối phê duyệt, chỉ được phép xem chi tiết và không thể xóa.');
        }

        // Không cho phép xóa nếu đề tài đã được Trưởng khoa duyệt hoặc đã có nhóm đăng ký/gán
        if ($detai->dangKyDeTais()->whereIn('TrangThai', ['Chờ duyệt', 'Đã duyệt'])->exists()) {
            return redirect()->back()->withErrors('Không thể xóa đề tài này do đã có nhóm sinh viên đăng ký/thực hiện.');
        }

        if (in_array($detai->TrangThai, ['Đã duyệt', 'Trưởng khoa đã duyệt', 'Đã công bố']) || !empty($detai->NgayDuyetKhoa)) {
            return redirect()->back()->withErrors('Đề tài đã được Trưởng khoa phê duyệt chính thức, không thể xóa!');
        }

        try {
            $detai->delete();
            return redirect()->route('giangvien.detai.index')->with('success', 'Xóa đề tài thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors('Không thể xóa đề tài này do có dữ liệu liên quan.');
        }
    }

    public function ganNhom(Request $request, $id)
    {
        $request->validate([
            'MaNhom' => 'required|exists:Nhom,MaNhom',
        ], [
            'MaNhom.required' => 'Vui lòng chọn nhóm sinh viên cần gán.',
        ]);

        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) return redirect()->route('giangvien.dashboard');
        $detai = DeTai::where('MaDeTai', $id)->where('MaGV', $gv->MaGV)->firstOrFail();

        if (!in_array($detai->TrangThai, ['Đã duyệt', 'Trưởng khoa đã duyệt', 'Đã công bố'])) {
            return redirect()->back()->withErrors('Chỉ đề tài đã được Trưởng khoa phê duyệt hoặc Giáo vụ công bố mới có thể gán nhóm thực hiện!');
        }

        $nhom = Nhom::with('thanhViens')->findOrFail($request->MaNhom);

        // Quy định: Chỉ được phép gán khi nhóm đã có đủ 3 thành viên
        $countMembers = $nhom->thanhViens->where('TrangThai', 'da_tham_gia')->count();
        if ($countMembers < 3) {
            return redirect()->back()->withErrors("Quy định: Chỉ được phép gán nhóm khi nhóm đã có đủ 3 thành viên theo quy định! Nhóm '{$nhom->TenNhom}' hiện chỉ có {$countMembers}/3 thành viên.");
        }
        $maxSV = $detai->SoLuongSinhVienToiDa ?? 3;
        if ($countMembers > $maxSV) {
            return redirect()->back()->withErrors("Đề tài chỉ tiếp nhận tối đa {$maxSV} sinh viên. Nhóm hiện có {$countMembers} thành viên!");
        }

        DB::transaction(function () use ($detai, $nhom, $gv) {
            // Hủy các đơn đăng ký cũ nếu có của nhóm hoặc đề tài này
            DangKyDeTai::where('MaDeTai', $detai->MaDeTai)->delete();
            DangKyDeTai::where('MaNhom', $nhom->MaNhom)->delete();

            $maDK = 'DK_' . Str::upper(Str::random(6));

            DangKyDeTai::create([
                'MaDangKy'     => $maDK,
                'MaNhom'       => $nhom->MaNhom,
                'MaDeTai'      => $detai->MaDeTai,
                'NgayDangKy'   => now(),
                'NgayDuyet'    => now(),
                'TrangThai'    => 'Đã duyệt',
            ]);

            $nhom->update([
                'TrangThai' => 'Đã duyệt',
            ]);
        });

        // Gửi thông báo cho nhóm
        ThongBaoService::guiDenNhom(
            $nhom->MaNhom,
            '✅ Giảng viên đã gán đề tài cho nhóm!',
            "Giảng viên {$gv->HoTen} đã trực tiếp gán nhóm của bạn vào đề tài: '{$detai->TenDeTai}'",
            'Đề tài'
        );

        return redirect()->route('giangvien.detai.index')
            ->with('success', "Đã gán thành công Nhóm '{$nhom->TenNhom}' vào đề tài '{$detai->TenDeTai}'!");
    }

    public function myTasks()
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) {
            return redirect()->route('giangvien.dashboard');
        }

        $nhoms = Nhom::with(['deTai', 'truongNhom', 'dangKyDeTai.deTai', 'baoCaos', 'hoSoBaoVe'])
            ->where(function($query) use ($gv) {
                $query->whereHas('dangKyDeTai', function($q) use ($gv) {
                    $q->where('TrangThai', 'Đã duyệt')
                      ->whereHas('deTai', function($dtQ) use ($gv) {
                          $dtQ->where('MaGV', $gv->MaGV);
                      });
                })->orWhereHas('deTai', function($q) use ($gv) {
                    $q->where('MaGV', $gv->MaGV);
                });
            })->get();

        $stats = [
            'sap_den_han'  => 0,
            'qua_han'      => 0,
            'cho_nhan_xet' => 0,
        ];

        $today = \Carbon\Carbon::today();
        foreach ($nhoms as $nhom) {
            foreach ($nhom->baoCaos as $bc) {
                if (empty($bc->NhanXet)) {
                    $stats['cho_nhan_xet']++;
                }
            }
        }

        return view('giangvien.my_tasks', compact('nhoms', 'stats'));
    }
}