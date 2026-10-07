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

        $query = DeTai::with(['giangVien', 'dangKyDeTais.nhom.thanhViens.sinhVien'])->where('MaGV', $gv->MaGV);

        if ($request->filled('search')) {
            $query->where('TenDeTai', 'LIKE', '%' . trim($request->search) . '%');
        }

        if ($request->filled('TrangThai')) {
            $query->where('TrangThai', $request->TrangThai);
        }

        $detais = $query->orderBy('created_at', 'desc')->paginate(5)->withQueryString();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        // Danh sách các nhóm chưa có đề tài đã duyệt
        $NhomChuaCoDeTai = Nhom::with(['truongNhom', 'thanhViens.sinhVien'])
            ->whereDoesntHave('phieuDangKys', fn($q) => $q->where('TrangThai', 'Đã duyệt'))
            ->get();

        return view('giangvien.detai.index', compact('detais', 'hocKies', 'gv', 'NhomChuaCoDeTai'));
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

        // Lấy danh sách Học phần mà giảng viên này được phép đề xuất:
        // 1. Học phần dùng chung toàn khoa (MaBoMon is null: Khóa luận cử nhân, Khóa luận kỹ sư)
        // 2. Học phần chuyên ngành thuộc đúng Bộ môn của GV (MaBoMon == $gv->MaBoMon)
        $hocPhans = \App\Models\HocPhan::where('TrangThai', 'Đang áp dụng')
            ->where(function($q) use ($gv) {
                $q->whereNull('MaBoMon');
                if ($gv && $gv->MaBoMon) {
                    $q->orWhere('MaBoMon', $gv->MaBoMon);
                }
            })
            ->orderByRaw('MaBoMon IS NOT NULL')
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
            'SoLuongSinhVienToiDa' => 'required|integer|min:1|max:3',
            'MoTa' => 'nullable|string',
            'YeuCau' => 'nullable|string',
            'LinhVuc' => 'nullable|string|max:150',
        ], [
            'TenDeTai.required' => 'Vui lòng nhập tên đề tài.',
            'MaHocKy.required' => 'Vui lòng chọn học kỳ áp dụng.',
            'MaHocPhan.required' => 'Vui lòng chọn môn / học phần cho đề tài.',
            'MaHocPhan.exists' => 'Môn / học phần được chọn không tồn tại trong hệ thống.',
            'SoLuongSinhVienToiDa.required' => 'Vui lòng nhập số sinh viên tối đa.',
            'SoLuongSinhVienToiDa.min' => 'Số sinh viên tối đa ít nhất là 1.',
            'SoLuongSinhVienToiDa.max' => 'Số sinh viên tối đa không vượt quá 3.',
        ]);

        $hocPhan = \App\Models\HocPhan::findOrFail($request->MaHocPhan);

        // Kiểm tra quyền bộ môn: học phần phải là dùng chung (MaBoMon is null) HOẶC thuộc bộ môn của GV
        if ($hocPhan->MaBoMon !== null && $gv->MaBoMon && $hocPhan->MaBoMon !== $gv->MaBoMon) {
            return redirect()->back()->withErrors("Học phần '{$hocPhan->TenHocPhan}' không thuộc bộ môn được gán của bạn!");
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
            'SoLuongSinhVienToiDa' => (int)$request->SoLuongSinhVienToiDa,
            'FileDeCuong' => null,
            'MaNganh' => null, // Đã bỏ theo yêu cầu, sinh viên đăng ký theo đúng ngành mình học
            'MaHocKy' => $request->MaHocKy,
            'TrangThai' => 'Chờ duyệt cấp Bộ môn',
            'NgayDeXuat' => now(),
        ]);

        return redirect()->route('giangvien.detai.index')->with('success', "Đề xuất đề tài '{$request->TenDeTai}' cho môn {$hocPhan->TenHocPhan} thành công! Đề tài đã được chuyển tới Trưởng bộ môn phê duyệt.");
    }

    public function nopDeCuong(Request $request, $id)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) return redirect()->route('giangvien.dashboard');

        $detai = DeTai::where('MaDeTai', $id)->where('MaGV', $gv->MaGV)->firstOrFail();

        $allowedStates = [
            'Trưởng khoa đã duyệt - Chờ nộp đề cương', 
            'Yêu cầu chỉnh sửa đề cương', 
            'Trưởng khoa đã duyệt', 
            'Đã duyệt',
            'Đang phản biện đề cương',
            'Đã cập nhật đề cương - Chờ phản biện lại'
        ];
        if (!in_array($detai->TrangThai, $allowedStates)) {
            return redirect()->back()->withErrors('Đề tài hiện tại không ở trạng thái được phép nộp đề cương chi tiết.');
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

            return redirect()->back()->with('success', "Đã nộp lại Đề cương chi tiết đã chỉnh sửa cho đề tài '{$detai->TenDeTai}' thành công! Hệ thống đã gửi thông báo đến Giảng viên phản biện ({$phanCongPB->giangVien->HoTen}) để thẩm định lại.");
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
                    "Giảng viên {$gv->HoTen} vừa nộp file Đề cương chi tiết cho đề tài '{$detai->TenDeTai}'. Vui lòng tiến hành phân công Giảng viên phản biện.",
                    'Đề tài'
                );
            }

            return redirect()->back()->with('success', "Đã nộp Đề cương chi tiết cho đề tài '{$detai->TenDeTai}' thành công! Đề cương đã được chuyển tới Trưởng bộ môn để phân công phản biện.");
        }
    }

    public function edit($id)
    {
        $user = Auth::user();
        $gv = GiangVien::with('boMon')->where('MaTK', $user->MaTK)->first() 
              ?? GiangVien::getLoggedInGiangVien($user);
        if (!$gv) return redirect()->route('giangvien.dashboard');
        $detai = DeTai::where('MaDeTai', $id)->where('MaGV', $gv->MaGV)->firstOrFail();
        $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();

        $hocPhans = \App\Models\HocPhan::where('TrangThai', 'Đang áp dụng')
            ->where(function($q) use ($gv) {
                $q->whereNull('MaBoMon');
                if ($gv && $gv->MaBoMon) {
                    $q->orWhere('MaBoMon', $gv->MaBoMon);
                }
            })
            ->orderByRaw('MaBoMon IS NOT NULL')
            ->orderBy('TenHocPhan')
            ->get();

        return view('giangvien.detai.edit', compact('detai', 'hocKies', 'gv', 'hocPhans'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $gv = GiangVien::with('boMon')->where('MaTK', $user->MaTK)->first() 
              ?? GiangVien::getLoggedInGiangVien($user);
        if (!$gv) return redirect()->route('giangvien.dashboard');
        $detai = DeTai::where('MaDeTai', $id)->where('MaGV', $gv->MaGV)->firstOrFail();

        $request->validate([
            'TenDeTai' => 'required|string|max:300',
            'MaHocKy' => 'required|exists:HocKy,MaHocKy',
            'MaHocPhan' => 'required|exists:HocPhan,MaHocPhan',
            'SoLuongSinhVienToiDa' => 'required|integer|min:1|max:3',
            'MoTa' => 'nullable|string',
            'YeuCau' => 'nullable|string',
            'LinhVuc' => 'nullable|string|max:150',
            'FileDeCuong' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ], [
            'TenDeTai.required' => 'Vui lòng nhập tên đề tài.',
            'MaHocKy.required' => 'Vui lòng chọn học kỳ áp dụng.',
            'MaHocPhan.required' => 'Vui lòng chọn môn / học phần cho đề tài.',
            'MaHocPhan.exists' => 'Môn / học phần được chọn không tồn tại trong hệ thống.',
            'SoLuongSinhVienToiDa.required' => 'Vui lòng nhập số sinh viên tối đa.',
        ]);

        $hocPhan = \App\Models\HocPhan::findOrFail($request->MaHocPhan);
        if ($hocPhan->MaBoMon !== null && $gv->MaBoMon && $hocPhan->MaBoMon !== $gv->MaBoMon) {
            return redirect()->back()->withErrors("Học phần '{$hocPhan->TenHocPhan}' không thuộc bộ môn được gán của bạn!");
        }

        $data = [
            'TenDeTai' => $request->TenDeTai,
            'MaHocKy' => $request->MaHocKy,
            'MaHocPhan' => $hocPhan->MaHocPhan,
            'HocPhan' => $hocPhan->TenHocPhan,
            'MaNganh' => null,
            'SoLuongSinhVienToiDa' => (int)$request->SoLuongSinhVienToiDa,
            'MoTa' => $request->MoTa,
            'YeuCau' => $request->YeuCau,
            'LinhVuc' => $request->LinhVuc,
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

        if (in_array($detai->TrangThai, ['Yêu cầu điều chỉnh', 'Yêu cầu chỉnh sửa', 'Từ chối', 'Không đạt phản biện'])) {
            $data['TrangThai'] = 'Chờ duyệt cấp Bộ môn';
            $data['NgayDeXuat'] = now();
        }

        $detai->update($data);

        return redirect()->route('giangvien.detai.index')->with('success', 'Cập nhật đề tài thành công!' . ($detai->wasChanged('TrangThai') ? ' Đề tài đã được nộp lại cho Trưởng bộ môn xem xét.' : ''));
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $gv = GiangVien::getLoggedInGiangVien($user);
        if (!$gv) return redirect()->route('giangvien.dashboard');
        $detai = DeTai::where('MaDeTai', $id)->where('MaGV', $gv->MaGV)->firstOrFail();

        try {
            $detai->delete();
            return redirect()->route('giangvien.detai.index')->with('success', 'Xóa đề tài thành công!');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors('Không thể xóa đề tài này do đã có sinh viên đăng ký.');
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

        // Quy định: Nhóm từ 1 đến 3 thành viên
        $countMembers = $nhom->thanhViens->where('TrangThai', 'da_tham_gia')->count();
        if ($countMembers < 1 || $countMembers > 3) {
            return redirect()->back()->withErrors("Quy định: Nhóm phải có từ 1 đến 3 thành viên. Nhóm '{$nhom->TenNhom}' hiện có {$countMembers} thành viên!");
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
                'MaDeTai'   => $detai->MaDeTai,
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