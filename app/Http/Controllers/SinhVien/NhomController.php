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

        // 1. Kiểm tra nhóm mà sinh viên đang tham gia chính thức ('da_tham_gia')
        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->first();

        $nhomCurrent = null;
        $yeuCauXinVao = collect();
        $loiMoiDaGui = collect();
        $isNhomLocked = false;

        if ($thanhVienRecord) {
            // Khi SV ĐÃ CÓ NHÓM -> Tự động dọn dẹp các lời mời hoặc yêu cầu xin vào ở nhóm khác
            ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
                ->where('TrangThai', '!=', 'da_tham_gia')
                ->delete();

            $nhomCurrent = Nhom::with(['deTai.giangVien', 'truongNhom.taiKhoan', 'dangKyDeTai'])
                ->where('MaNhom', $thanhVienRecord->MaNhom)
                ->first();

            if ($nhomCurrent) {
                // Nhóm đã khóa khi đề tài được duyệt chính thức
                $isNhomLocked = ($nhomCurrent->dangKyDeTai && $nhomCurrent->dangKyDeTai->TrangThai === 'Đã duyệt');

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

            return view('sinhvien.nhom.index', compact('sinhVien', 'nhomCurrent', 'yeuCauXinVao', 'loiMoiDaGui', 'isNhomLocked'));
        }

        // 2. KHI CHƯA CÓ NHÓM:
        // Lấy danh sách lời mời gia nhập nhóm gửi tới SV này ('cho_xac_nhan')
        $loiMois = ThanhVienNhom::with(['nhom.truongNhom.taiKhoan', 'nhom.thanhViens' => fn($q) => $q->where('TrangThai', 'da_tham_gia')->with('sinhVien')])
            ->where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'cho_xac_nhan')
            ->get();

        // Lấy danh sách yêu cầu xin gia nhập mà SV này đã gửi đi ('xin_gia_nhap')
        $yeuCauDaGui = ThanhVienNhom::with(['nhom.truongNhom.taiKhoan'])
            ->where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'xin_gia_nhap')
            ->get()
            ->keyBy('MaNhom');

        // Danh sách tất cả các nhóm (kèm tìm kiếm theo Tên nhóm hoặc MSSV trưởng nhóm)
        $queryNhoms = Nhom::with([
                'truongNhom.taiKhoan',
                'truongNhom.lop.nganh',
                'thanhViens' => fn($q) => $q->where('TrangThai', 'da_tham_gia')->with('sinhVien.lop.nganh', 'sinhVien.taiKhoan')
            ])
            ->whereDoesntHave('dangKyDeTai', fn($q) => $q->where('TrangThai', 'Đã duyệt'));

        if ($request->filled('q')) {
            $search = trim($request->q);
            $queryNhoms->where(function ($q) use ($search) {
                $q->where('TenNhom', 'LIKE', "%{$search}%")
                  ->orWhere('MaNhom', 'LIKE', "%{$search}%")
                  ->orWhereHas('truongNhom', function ($sq) use ($search) {
                      $sq->where('HoTen', 'LIKE', "%{$search}%")
                         ->orWhere('MaSV', 'LIKE', "%{$search}%")
                         ->orWhereHas('taiKhoan', fn($tq) => $tq->where('TenDangNhap', 'LIKE', "%{$search}%"));
                  });
            });
        }

        $nhomsOpen = $queryNhoms->get();

        return view('sinhvien.nhom.index', compact('sinhVien', 'nhomCurrent', 'loiMois', 'yeuCauDaGui', 'nhomsOpen', 'isNhomLocked'));
    }

    /**
     * Tự động tạo nhóm với tên mặc định là "Nhóm" + "MSSV của nhóm trưởng"
     */
    public function store(Request $request)
    {
        $sinhVien = SinhVien::with('taiKhoan')->where('MaTK', Auth::user()->MaTK)->firstOrFail();

        // Kiểm tra SV đã ở trong nhóm nào chính thức chưa
        $alreadyInGroup = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->exists();

        if ($alreadyInGroup) {
            return redirect()->back()->withErrors('Bạn đã thuộc một nhóm khóa luận rồi!');
        }

        // Tên nhóm gán cứng mặc định là: "Nhóm " + MSSV
        $mssv = $sinhVien->taiKhoan->TenDangNhap ?? $sinhVien->MaSV;
        $tenNhom = "Nhóm " . $mssv;

        DB::transaction(function () use ($tenNhom, $sinhVien) {
            $maNhom = IdGenerator::nextNhom();

            Nhom::create([
                'MaNhom'       => $maNhom,
                'TenNhom'      => $tenNhom,
                'MaTruongNhom' => $sinhVien->MaSV,
                'TrangThai'    => 'Đang hoạt động',
                'NgayTao'      => now(),
            ]);

            ThanhVienNhom::create([
                'MaNhom'      => $maNhom,
                'MaSV'        => $sinhVien->MaSV,
                'VaiTro'      => 'Trưởng nhóm',
                'TrangThai'   => 'da_tham_gia',
                'NgayThamGia' => now(),
            ]);

            // Dọn dẹp tất cả lời mời hoặc yêu cầu khác của sinh viên này
            ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
                ->where('MaNhom', '!=', $maNhom)
                ->delete();
        });

        return redirect()->route('sinhvien.nhom.index')->with('success', "Khởi tạo '{$tenNhom}' thành công!");
    }

    /**
     * Lấy thông tin chi tiết thành viên của một nhóm (cho Modal Chi Tiết Nhóm theo spec)
     */
    public function chiTietNhom($maNhom)
    {
        $nhom = Nhom::with([
            'truongNhom.taiKhoan',
            'truongNhom.lop.nganh',
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

        // 1. Tìm sinh viên từ database (Exact match qua MSSV/TenDangNhap hoặc MaSV)
        $sinhVien = SinhVien::with(['taiKhoan', 'lop.nganh'])
            ->whereHas('taiKhoan', fn($q) => $q->where('TenDangNhap', $mssv))
            ->orWhere('MaSV', $mssv)
            ->orWhere('MaSoSinhVien', $mssv)
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
        }
        // 2.2 Sinh viên đã thuộc nhóm khác chưa?
        else {
            $inGroup = ThanhVienNhom::with('nhom')
                ->where('MaSV', $sinhVien->MaSV)
                ->where('TrangThai', 'da_tham_gia')
                ->first();

            if ($inGroup) {
                $canInvite = false;
                $statusText = "🔴 Đã thuộc nhóm: \"{$inGroup->nhom->TenNhom}\"";
                $badgeClass = 'bg-danger';
            }
            // 2.3 Nhóm đã bị khóa do duyệt đề tài chưa?
            elseif ($nhom && $nhom->dangKyDeTai && $nhom->dangKyDeTai->TrangThai === 'Đã duyệt') {
                $canInvite = false;
                $statusText = '🔴 Nhóm đã được duyệt đề tài và bị khóa, không thể mời thêm thành viên.';
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
            'MaNhom' => 'required|exists:nhoms,MaNhom',
            'MaSV'   => 'required|exists:sinh_viens,MaSV',
        ], [
            'MaNhom.required' => 'Thiếu thông tin nhóm.',
            'MaSV.required'   => 'Vui lòng tra cứu và chọn sinh viên muốn mời.',
        ]);

        $currentUser = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $currentUser->MaTK)->firstOrFail();
        $nhom = Nhom::with('dangKyDeTai')->findOrFail($request->MaNhom);

        // 1. Kiểm tra quyền của người gửi lời mời
        if ($nhom->MaTruongNhom !== $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Chỉ Trưởng nhóm mới có quyền gửi lời mời thành viên!');
        }

        // 2. Kiểm tra nhóm đã khóa chưa
        if ($nhom->dangKyDeTai && $nhom->dangKyDeTai->TrangThai === 'Đã duyệt') {
            return redirect()->back()->withErrors('Nhóm đã được duyệt đề tài và bị khóa. Không thể mời thêm thành viên!');
        }

        // 3. Tái kiểm tra số lượng thành viên trong nhóm
        $count = ThanhVienNhom::where('MaNhom', $nhom->MaNhom)
            ->where('TrangThai', 'da_tham_gia')
            ->count();

        if ($count >= 3) {
            return redirect()->back()->withErrors('Nhóm khóa luận đã đủ tối đa 3 thành viên theo quy định!');
        }

        // 4. Lấy thông tin sinh viên được mời
        $svThem = SinhVien::with(['taiKhoan', 'lop'])->findOrFail($request->MaSV);

        if ($svThem->MaSV === $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Bạn không thể tự mời chính mình!');
        }

        // 5. Tái kiểm tra sinh viên được mời đã có nhóm chưa (Chống Race Condition)
        $alreadyInGroup = ThanhVienNhom::where('MaSV', $svThem->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->exists();

        if ($alreadyInGroup) {
            return redirect()->back()->withErrors("Sinh viên {$svThem->HoTen} đã thuộc một nhóm khóa luận khác!");
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

            ThanhVienNhom::create([
                'MaNhom'      => $nhom->MaNhom,
                'MaSV'        => $svThem->MaSV,
                'VaiTro'      => 'Thành viên',
                'TrangThai'   => 'cho_xac_nhan',
                'NgayThamGia' => null,
            ]);

            // Gửi thông báo đến sinh viên được mời
            ThongBaoService::guiDen(
                $svThem->MaTK,
                '📩 Bạn nhận được lời mời tham gia nhóm khóa luận!',
                "Trưởng nhóm {$sinhVien->HoTen} đã mời bạn tham gia nhóm '{$nhom->TenNhom}'. Vui lòng vào mục Nhóm Khóa Luận để xác nhận.",
                'Nhóm'
            );
        });

        return redirect()->back()->with('success', "Đã gửi lời mời gia nhập nhóm đến sinh viên {$svThem->HoTen} ({$svThem->taiKhoan->TenDangNhap}) thành công!");
    }

    /**
     * Khai trừ thành viên khỏi nhóm (Chỉ Trưởng nhóm và trước khi duyệt đề tài)
     */
    public function khaiTruThanhVien($maNhom, $maSV)
    {
        $currentUser = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $currentUser->MaTK)->firstOrFail();
        $nhom = Nhom::with('dangKyDeTai')->findOrFail($maNhom);

        // 1. Chỉ Trưởng nhóm mới có quyền
        if ($nhom->MaTruongNhom !== $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Chỉ Trưởng nhóm mới có quyền khai trừ thành viên!');
        }

        // 2. Không được tự khai trừ chính mình
        if ($sinhVien->MaSV === $maSV) {
            return redirect()->back()->withErrors('Trưởng nhóm không thể tự khai trừ chính mình!');
        }

        // 3. Khóa sau khi đề tài đã được duyệt chính thức
        if ($nhom->dangKyDeTai && $nhom->dangKyDeTai->TrangThai === 'Đã duyệt') {
            return redirect()->back()->withErrors('Đề tài của nhóm đã được duyệt chính thức. Nhóm đã bị khóa và không thể khai trừ thành viên!');
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

        // Kiểm tra SV đã có nhóm chưa
        $alreadyInGroup = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->exists();

        if ($alreadyInGroup) {
            return redirect()->back()->withErrors('Bạn đã thuộc một nhóm khóa luận rồi, không thể xin gia nhập nhóm khác.');
        }

        $nhom = Nhom::with('dangKyDeTai')->findOrFail($maNhom);

        if ($nhom->dangKyDeTai && $nhom->dangKyDeTai->TrangThai === 'Đã duyệt') {
            return redirect()->back()->withErrors('Nhóm này đã được duyệt đề tài và bị khóa tuyển thành viên.');
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

        if ($nhom->MaTruongNhom !== $sinhVien->MaSV) {
            return redirect()->back()->withErrors('Chỉ Trưởng nhóm mới có quyền phê duyệt yêu cầu xin vào nhóm!');
        }

        if ($nhom->dangKyDeTai && $nhom->dangKyDeTai->TrangThai === 'Đã duyệt') {
            return redirect()->back()->withErrors('Nhóm đã duyệt đề tài và bị khóa. Không thể thêm thành viên mới!');
        }

        $count = ThanhVienNhom::where('MaNhom', $maNhom)
            ->where('TrangThai', 'da_tham_gia')
            ->count();

        if ($count >= 3) {
            return redirect()->back()->withErrors('Nhóm đã đủ 3 thành viên, không thể thêm thành viên mới!');
        }

        // Kiểm tra sinh viên xin vào đã có nhóm khác chưa
        $alreadyInAnother = ThanhVienNhom::where('MaSV', $maSV)
            ->where('TrangThai', 'da_tham_gia')
            ->exists();

        if ($alreadyInAnother) {
            ThanhVienNhom::where('MaNhom', $maNhom)->where('MaSV', $maSV)->delete();
            return redirect()->back()->withErrors('Sinh viên này đã tham gia một nhóm khác trước đó. Yêu cầu đã tự động bị hủy.');
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

        // 1. Kiểm tra lời mời có tồn tại cho sinh viên này không
        $hasInvite = ThanhVienNhom::where('MaNhom', $maNhom)
            ->where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'cho_xac_nhan')
            ->exists();

        if (!$hasInvite) {
            return redirect()->back()->withErrors('Không tìm thấy lời mời này.');
        }

        // 2. Kiểm tra SV đã thuộc nhóm khác chưa
        $alreadyInAnotherGroup = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->exists();

        if ($alreadyInAnotherGroup) {
            ThanhVienNhom::where('MaNhom', $maNhom)
                ->where('MaSV', $sinhVien->MaSV)
                ->where('TrangThai', 'cho_xac_nhan')
                ->delete();
            return redirect()->back()->withErrors('Bạn đã thuộc một nhóm khóa luận khác. Lời mời này đã được hủy tự động.');
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

        // 5. Dọn dẹp tất cả các lời mời hoặc yêu cầu khác của sinh viên này ở các nhóm khác
        ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('MaNhom', '!=', $maNhom)
            ->delete();

        return redirect()->route('sinhvien.nhom.index')->with('success', 'Bạn đã tham gia nhóm thành công!');
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
}