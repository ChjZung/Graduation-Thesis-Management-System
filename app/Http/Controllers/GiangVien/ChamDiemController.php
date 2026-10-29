<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\PhieuChamDiem;
use App\Models\HoiDong;
use App\Models\GiangVien;
use App\Models\ThanhVienHoiDong;
use App\Models\HoSoBaoVe;
use App\Models\HocKy;
use App\Services\GradeCalculationService;
use App\Services\ThongBaoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChamDiemController extends Controller
{
    public function __construct(private GradeCalculationService $gradeService) {}

    /**
     * Chuẩn bị dữ liệu phục vụ Tab Chấm Điểm Hội Đồng
     */
    public static function prepareGradingData($giangVien, Request $request): array
    {
        $now = HoiDong::getSystemNow();

        // Lấy tất cả Hội đồng mà giảng viên này tham gia
        $allHoiDongs = HoiDong::whereHas('thanhViens', fn($q) => $q->where('MaGV', $giangVien->MaGV))
            ->with([
                'thanhViens.giangVien',
                'hoSoBaoVes.nhom.thanhViens.sinhVien.lop',
                'hoSoBaoVes.nhom.deTai.giangVien',
                'hoSoBaoVes.nhom.truongNhom',
            ])
            ->get();

        // CHỈ cho phép hiển thị các Hội đồng đủ điều kiện chấm điểm (Đang diễn ra hoặc Đã kết thúc còn trong hạn)
        $gradingHoiDongs = $allHoiDongs->filter(fn($hd) => $hd->isWithinGradingWindow($now))->values();

        // Tìm hội đồng sắp diễn ra gần nhất để hỗ trợ hiển thị gợi ý thông minh
        $sapDienRaGanNhat = $allHoiDongs->filter(fn($hd) => $hd->trang_thai_thoi_gian === 'SapDienRa')
            ->sortBy(fn($hd) => $hd->thoi_diem_bat_dau?->timestamp ?? PHP_INT_MAX)
            ->first();

        $activeHoiDong = null;
        $reqHd = $request->query('hd') ?? $request->query('MaHoiDong');
        if ($gradingHoiDongs->isNotEmpty()) {
            if (!empty($reqHd)) {
                $activeHoiDong = $gradingHoiDongs->firstWhere('MaHoiDong', $reqHd) ?? $gradingHoiDongs->first();
            } else {
                $activeHoiDong = $gradingHoiDongs->first();
            }
        }

        // Lấy nhóm / hồ sơ bảo vệ đang chọn
        $activeHoSo = null;
        $reqHoSo = $request->query('hoso') ?? $request->query('MaHoSo');
        if ($activeHoiDong && $activeHoiDong->hoSoBaoVes->isNotEmpty()) {
            if (!empty($reqHoSo)) {
                $activeHoSo = $activeHoiDong->hoSoBaoVes->firstWhere('MaHoSo', $reqHoSo) ?? $activeHoiDong->hoSoBaoVes->first();
            } else {
                $activeHoSo = $activeHoiDong->hoSoBaoVes->first();
            }
        }

        // Tự động nhận diện loại khóa luận: Cử nhân (KLCN) hoặc Kỹ sư (KLKS)
        $loaiKhoaLuan = null;
        $canhBaoChuaXacDinhLoai = false;

        if ($activeHoSo) {
            $loaiKhoaLuan = $activeHoSo->xacDinhLoaiKhoaLuan();
            if (!$loaiKhoaLuan) {
                // Nếu chưa tự nhận diện được, kiểm tra query parameter hoặc cảnh báo
                $loaiKhoaLuan = $request->query('loai', null);
                if (!$loaiKhoaLuan) {
                    $canhBaoChuaXacDinhLoai = true;
                    $loaiKhoaLuan = 'KLCN'; // Tạm thời để hiển thị form nhưng kèm cảnh báo
                }
            } else {
                // Nếu có query param ghi đè mà người dùng click
                if ($request->filled('loai')) {
                    $loaiKhoaLuan = $request->query('loai');
                }
            }
        } else {
            $loaiKhoaLuan = $request->query('loai', 'KLCN');
        }

        // Lấy phiếu điểm đã chấm của giảng viên này cho hồ sơ đang chọn
        $diemDaCham = [];
        $daKhoaPhieu = false;
        $phieuCu = null;

        if ($activeHoiDong && $activeHoSo) {
            $phieuRecords = PhieuChamDiem::where('MaHoiDong', $activeHoiDong->MaHoiDong)
                ->where('MaHoSo', $activeHoSo->MaHoSo)
                ->where('MaGV', $giangVien->MaGV)
                ->get();

            $diemDaCham = $phieuRecords->keyBy('MaSV');
            $phieuCu = $phieuRecords->first();
            if ($phieuCu && $phieuCu->TrangThai === 'Đã hoàn thành') {
                $daKhoaPhieu = true;
            }
        }

        $daKyTen = !empty($phieuCu?->ChuKy);
        $chuKyHienTai = $phieuCu?->ChuKy ?? $giangVien->ChuKy;
        $ngayKy = $phieuCu?->NgayKy;

        $customChucNang = $activeHoSo?->TieuChiChucNang;
        $rubricKLCN = PhieuChamDiem::getRubricKLCN($customChucNang);
        $rubricKLKS = PhieuChamDiem::getRubricKLKS($customChucNang);

        $activeNhom = $activeHoSo?->nhom;
        $activeDeTai = $activeNhom?->deTai;
        $activeThanhViens = $activeNhom?->thanhViens ?? collect();
        $sinhViens = $activeThanhViens->pluck('sinhVien')->filter();

        $tvRecord = $activeHoiDong?->thanhViens->firstWhere('MaGV', $giangVien->MaGV);
        $vaiTroGV = $tvRecord?->VaiTro ?? 'Thành viên Hội đồng';
        $isChuTich = in_array($vaiTroGV, ['Chủ tịch', 'Chủ tịch Hội đồng']);
        $isThuKy = in_array($vaiTroGV, ['Thư ký', 'Ủy viên thư ký']);
        $gvhdHoTen = $activeDeTai?->giangVien?->HoTen ?? 'Chưa phân công';

        $rubric = ($loaiKhoaLuan === 'KLKS') ? $rubricKLKS : $rubricKLCN;
        $tenBieuMau = ($loaiKhoaLuan === 'KLKS') ? 'PHIẾU CHẤM ĐIỂM KHÓA LUẬN KỸ SƯ' : 'PHIẾU CHẤM ĐIỂM KHÓA LUẬN CỬ NHÂN';
        $functionalRequirements = PhieuChamDiem::parseFunctionalRequirements($customChucNang, $loaiKhoaLuan);

        // Đếm số lượng nhóm/hồ sơ bảo vệ đang chờ giảng viên này chấm điểm (CHỈ tính trong các hội đồng ĐƯỢC PHÉP CHẤM ĐIỂM)
        $choChamDiemCount = 0;
        foreach ($gradingHoiDongs as $hdItem) {
            foreach ($hdItem->hoSoBaoVes as $hsItem) {
                $daHoanThanh = PhieuChamDiem::where('MaHoiDong', $hdItem->MaHoiDong)
                    ->where('MaHoSo', $hsItem->MaHoSo)
                    ->where('MaGV', $giangVien->MaGV)
                    ->where('TrangThai', 'Đã hoàn thành')
                    ->exists();
                if (!$daHoanThanh) {
                    $choChamDiemCount++;
                }
            }
        }

        return [
            'giangVien'                => $giangVien,
            'gradingHoiDongs'          => $gradingHoiDongs,
            'allHoiDongs'              => $allHoiDongs,
            'sapDienRaGanNhat'         => $sapDienRaGanNhat,
            'activeHoiDong'            => $activeHoiDong,
            'activeHoSo'               => $activeHoSo,
            'activeDeTai'              => $activeDeTai,
            'sinhViens'                => $sinhViens,
            'vaiTroGV'                 => $vaiTroGV,
            'isChuTich'                => $isChuTich,
            'isThuKy'                  => $isThuKy,
            'gvhdHoTen'                => $gvhdHoTen,
            'customChucNang'           => $customChucNang,
            'functionalRequirements'   => $functionalRequirements,
            'rubric'                   => $rubric,
            'tenBieuMau'               => $tenBieuMau,
            'loaiKhoaLuan'             => $loaiKhoaLuan,
            'canhBaoChuaXacDinhLoai'   => $canhBaoChuaXacDinhLoai,
            'diemDaCham'               => $diemDaCham,
            'daKhoaPhieu'              => $daKhoaPhieu,
            'daKyTen'                  => $daKyTen,
            'chuKyHienTai'             => $chuKyHienTai,
            'ngayKy'                   => $ngayKy,
            'phieuCu'                  => $phieuCu,
            'rubricKLCN'               => $rubricKLCN,
            'rubricKLKS'               => $rubricKLKS,
            'choChamDiemCount'         => $choChamDiemCount,
        ];
    }

    /**
     * Chuyển hướng 302 từ URL chấm điểm cũ sang Tab Chấm điểm tại /giangvien/hoi-dong?tab=chamdiem
     * Bảo vệ IDOR và chặn truy cập sớm khi hội đồng chưa đến thời gian chấm điểm
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        $reqHd = $request->query('hd') ?? $request->query('MaHoiDong');

        if ($giangVien && !empty($reqHd)) {
            $targetHd = HoiDong::with('thanhViens')->find($reqHd);
            if ($targetHd) {
                $reason = '';
                if (!$targetHd->canGrade($giangVien, null, $reason)) {
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $reason ?? 'Hội đồng chưa đến thời gian chấm điểm.',
                        ], 403);
                    }
                    return redirect()->route('giangvien.hoidong.index', ['tab' => 'danhsach'])
                        ->with('error', $reason ?? 'Hội đồng chưa đến thời gian chấm điểm.');
                }
            }
        }

        $query = $request->query();
        $query['tab'] = 'chamdiem';

        return redirect()->route('giangvien.hoidong.index', $query, 302);
    }

    public function store(Request $request)
    {
        $request->validate([
            'MaHoiDong'     => 'required|exists:HoiDong,MaHoiDong',
            'MaHoSo'        => 'required|exists:HoSoBaoVe,MaHoSo',
            'LoaiKhoaLuan'  => 'required|in:KLCN,KLKS',
            'diems'         => 'required|array|min:1',
            'action_type'   => 'required|in:luu_nhap,xac_nhan_hoan_thanh',
        ]);

        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        $hoiDong = HoiDong::with('thanhViens')->findOrFail($request->MaHoiDong);
        $hoSo = HoSoBaoVe::with(['nhom.deTai', 'nhom.thanhViens'])->findOrFail($request->MaHoSo);

        // BẢO VỆ CHẶN CHẤM ĐIỂM / LƯU NHÁP KHI HỘI ĐỒNG CHƯA ĐẾN GIỜ (Yêu cầu C)
        $reason = '';
        if (!$hoiDong->canGrade($giangVien, $hoSo, $reason)) {
            \Illuminate\Support\Facades\Log::warning("Chấm điểm ngoài thời gian cho phép: Giảng viên [{$giangVien->MaGV}] cố tình chấm Hội đồng [{$hoiDong->MaHoiDong}] - Hồ sơ [{$hoSo->MaHoSo}]. Lý do từ chối: {$reason}", [
                'ip'        => $request->ip(),
                'user_id'   => $user->MaNguoiDung ?? null,
                'action'    => $request->action_type,
                'system_now'=> HoiDong::getSystemNow()->toDateTimeString(),
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $reason,
                ], 403);
            }

            return redirect()->back()->with('error', $reason);
        }

        // Chống BOLA/IDOR: Xác thực Hồ sơ bảo vệ phải thuộc đúng Hội đồng đang chấm
        if ($hoSo->MaHoiDong !== $request->MaHoiDong) {
            abort(403, 'Hồ sơ bảo vệ này không thuộc Hội đồng bạn đang tham gia chấm điểm.');
        }

        // Kiểm tra xem phiếu chấm đã hoàn thành và khóa chưa
        $existingPhieu = PhieuChamDiem::where('MaHoiDong', $request->MaHoiDong)
            ->where('MaHoSo', $hoSo->MaHoSo)
            ->where('MaGV', $giangVien->MaGV)
            ->first();

        if ($existingPhieu && $existingPhieu->TrangThai === 'Đã hoàn thành' && !$request->has('unlock_token')) {
            return redirect()->back()->with('error', 'Phiếu chấm điểm này đã được Ký xác nhận & Khóa. Nếu cần điều chỉnh, vui lòng gửi yêu cầu mở khóa đến Trưởng khoa.');
        }

        $isFinal = ($request->action_type === 'xac_nhan_hoan_thanh');
        $trangThaiPhieu = $isFinal ? 'Đã hoàn thành' : 'Lưu nháp';

        // Lấy rubric tương ứng
        $customChucNang = $hoSo->TieuChiChucNang;
        $rubric = ($request->LoaiKhoaLuan === 'KLKS') 
            ? PhieuChamDiem::getRubricKLKS($customChucNang) 
            : PhieuChamDiem::getRubricKLCN($customChucNang);
        $rubricMap = collect($rubric)->keyBy('id');

        // Danh sách sinh viên hợp lệ của nhóm
        $validStudentIds = $hoSo->nhom?->thanhViens->pluck('MaSV')->toArray() ?? [];

        $deTai = $hoSo->nhom?->deTai;
        $maDeTai = $deTai?->MaDeTai ?? ($hoSo->MaDeTai ?? 'DT_CHUA_RO');

        $activeHocKy = HocKy::where('TrangThai', true)->first() ?? HocKy::latest()->first();
        $maHocKy = $hoSo->MaHocKy ?? ($activeHocKy?->MaHocKy ?? 'HK_DEF');

        // Cập nhật LoaiKhoaLuan cho HoSoBaoVe nếu chưa có
        if (empty($hoSo->LoaiKhoaLuan)) {
            $hoSo->LoaiKhoaLuan = $request->LoaiKhoaLuan;
            $hoSo->save();
        }

        DB::transaction(function () use ($request, $giangVien, $hoSo, $maDeTai, $maHocKy, $validStudentIds, $rubricMap, $isFinal, $trangThaiPhieu, $existingPhieu) {
            foreach ($request->diems as $key => $diemData) {
                // HTML field names use numeric student IDs as array keys, not row indexes.
                $studentKey = (string) $key;
                $maSV = in_array($studentKey, $validStudentIds, true)
                    ? $studentKey
                    : (string) ($diemData['MaSV'] ?? $studentKey);


                // Chặn BOLA: Không cho chấm điểm sinh viên không thuộc nhóm bảo vệ này
                if (!in_array($maSV, $validStudentIds)) {
                    abort(403, "Sinh viên [{$maSV}] không thuộc nhóm bảo vệ của hồ sơ này.");
                }

                $rawChiTiet = isset($diemData['ChiTietDiem'])
                    ? (is_array($diemData['ChiTietDiem']) ? $diemData['ChiTietDiem'] : json_decode($diemData['ChiTietDiem'], true))
                    : [];

                $validatedChiTiet = [];
                $tongDiemCoBan = 0.0;
                $diemKhuyenKhich = 0.0;

                foreach ($rubricMap as $cId => $criterion) {
                    $maxVal = (float)$criterion['max'];
                    $isBonus = !empty($criterion['is_bonus']);
                    $val = isset($rawChiTiet[$cId]) ? floatval($rawChiTiet[$cId]) : null;

                    if ($isFinal && $val === null && !$isBonus) {
                        abort(422, "Tiêu chí '{$criterion['noi_dung']}' của sinh viên [{$maSV}] chưa được chấm điểm.");
                    }

                    if ($val !== null) {
                        if ($val < 0) $val = 0.0;
                        if ($val > $maxVal) $val = $maxVal;

                        $validatedChiTiet[$cId] = round($val, 2);

                        if ($isBonus) {
                            $diemKhuyenKhich += $val;
                        } else {
                            $tongDiemCoBan += $val;
                        }
                    } else {
                        $validatedChiTiet[$cId] = 0.0;
                    }
                }

                // Lưu thêm các điểm chi tiết của từng yêu cầu chức năng (tc7_*)
                foreach ($rawChiTiet as $subKey => $subVal) {
                    if (str_starts_with($subKey, 'tc7_')) {
                        $validatedChiTiet[$subKey] = round(max(0.0, floatval($subVal)), 2);
                    }
                }

                // Điểm khuyến khích không vượt quá 1.0
                if ($diemKhuyenKhich > 1.0) $diemKhuyenKhich = 1.0;

                // Tổng điểm tối đa là 10.0
                $diemSo = round(min(10.0, $tongDiemCoBan + $diemKhuyenKhich), 2);

                $nhanXet = $diemData['NhanXet'] ?? null;

                // Tạo mã phiếu chấm duy nhất
                $hashId = substr(md5($request->MaHoiDong . '_' . $giangVien->MaGV . '_' . $maSV), 0, 8);
                $maPhieu = 'PCD_' . strtoupper($hashId);

                $chuKyToSave = $request->chu_ky ?? $existingPhieu?->ChuKy ?? $giangVien->ChuKy;
                $ngayKyToSave = !empty($chuKyToSave) ? ($existingPhieu?->NgayKy ?? now()) : null;

                PhieuChamDiem::updateOrCreate(
                    [
                        'MaHoiDong' => $request->MaHoiDong,
                        'MaGV'      => $giangVien->MaGV,
                        'MaSV'      => $maSV,
                    ],
                    [
                        'MaPhieuChamDiem' => $maPhieu,
                        'Diem'            => $diemSo,
                        'DiemKhuyenKhich' => $diemKhuyenKhich,
                        'LoaiKhoaLuan'    => $request->LoaiKhoaLuan,
                        'ChiTietDiem'     => $validatedChiTiet,
                        'NhanXet'         => $nhanXet,
                        'TrangThai'       => $trangThaiPhieu,
                        'NgayXacNhan'     => $isFinal ? now() : null,
                        'ChuKy'           => $chuKyToSave,
                        'NgayKy'          => $ngayKyToSave,
                        'MaDeTai'         => $maDeTai,
                        'MaHoSo'          => $hoSo->MaHoSo,
                        'MaHocKy'         => $maHocKy,
                        'NgayCham'        => now(),
                    ]
                );
            }

            if ($request->filled('chu_ky')) {
                $giangVien->ChuKy = $request->chu_ky;
                $giangVien->save();
            }

            // Tổng hợp kết quả điểm Hội đồng
            $this->gradeService->tongHopKetQuaHoiDong($request->MaHoiDong);
        });

        // Kiểm tra xem toàn bộ hội đồng đã hoàn tất chưa để thông báo cho Trưởng khoa
        if ($isFinal) {
            $tienDo = $this->gradeService->kiemTraTienDoChamHoiDong($request->MaHoiDong);
            if ($tienDo['exists'] && $tienDo['hoiDongDuPhieu']) {
                $hoiDong = $tienDo['hoiDong'];
                if ($hoiDong->MaKhoa) {
                    $truongKhoaGV = GiangVien::whereHas('boMon.khoa', fn($q) => $q->where('MaKhoa', $hoiDong->MaKhoa))
                        ->whereHas('taiKhoan.vaiTro', fn($v) => $v->where('TenVaiTro', 'like', '%Trưởng khoa%'))
                        ->first();
                    if ($truongKhoaGV) {
                        ThongBaoService::gui(
                            $truongKhoaGV->MaGV,
                            'Hội đồng đã hoàn tất chấm điểm',
                            "Hội đồng {$hoiDong->TenHoiDong} đã hoàn tất 100% phiếu chấm điểm từ các giám khảo. Trưởng khoa có thể xem tổng hợp và xác nhận/công bố kết quả.",
                            'Hội đồng'
                        );
                    }
                }
            }
        }

        $msg = $isFinal
            ? 'Đã Xác nhận & Khóa phiếu chấm điểm thành công! Điểm số đã được ghi nhận vào hệ thống.'
            : 'Đã lưu bản nháp phiếu chấm điểm thành công! Thầy/Cô có thể tiếp tục chỉnh sửa trước khi Xác nhận hoàn thành.';

        return redirect()->route('giangvien.hoidong.index', [
            'tab'  => 'chamdiem',
            'hd'   => $request->MaHoiDong,
            'hoso' => $request->MaHoSo,
            'loai' => $request->LoaiKhoaLuan,
        ])->with('success', $msg);
    }

    /**
     * Giảng viên gửi yêu cầu mở khóa phiếu chấm đã ký
     */
    public function yeuCauMoKhoa(Request $request)
    {
        $request->validate([
            'MaHoiDong' => 'required|exists:HoiDong,MaHoiDong',
            'MaHoSo'    => 'required|exists:HoSoBaoVe,MaHoSo',
            'LyDo'      => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(403);
        }

        $hoiDong = HoiDong::findOrFail($request->MaHoiDong);
        $hoSo = HoSoBaoVe::with('nhom.deTai')->findOrFail($request->MaHoSo);

        // Gửi thông báo đến Trưởng khoa
        if ($hoiDong->MaKhoa) {
            $truongKhoaGV = GiangVien::whereHas('boMon.khoa', fn($q) => $q->where('MaKhoa', $hoiDong->MaKhoa))
                ->whereHas('taiKhoan.vaiTro', fn($v) => $v->where('TenVaiTro', 'like', '%Trưởng khoa%'))
                ->first();
            if ($truongKhoaGV) {
                ThongBaoService::gui(
                    $truongKhoaGV->MaGV,
                    'Yêu cầu mở khóa phiếu chấm điểm',
                    "Giám khảo {$giangVien->HoTen} gửi yêu cầu mở khóa phiếu chấm điểm đề tài '{$hoSo->nhom?->deTai?->TenDeTai}' (HĐ: {$hoiDong->TenHoiDong}). Lý do: {$request->LyDo}",
                    'Yêu cầu'
                );
            }
        }

        return redirect()->back()->with('success', 'Đã gửi yêu cầu mở khóa phiếu chấm điểm đến Trưởng khoa thành công!');
    }

    /**
     * Thư ký Hội đồng cập nhật yêu cầu chức năng bài toán (Chuẩn Yêu cầu 3: Chuyển quyền từ Chủ tịch sang Thư ký)
     * Thư ký chịu trách nhiệm chuẩn bị phiếu chấm dựa trên đề cương của GVHD
     */
    public function capNhatChucNang(Request $request)
    {
        $request->validate([
            'MaHoiDong'       => 'required|exists:HoiDong,MaHoiDong',
            'MaHoSo'          => 'required|exists:HoSoBaoVe,MaHoSo',
            'TieuChiChucNang' => 'nullable|string|max:10000',
        ]);

        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(403, 'Không tìm thấy thông tin giảng viên.');
        }

        $hoSo = HoSoBaoVe::findOrFail($request->MaHoSo);

        // Chống BOLA/IDOR: Kiểm tra Hồ sơ phải thuộc Hội đồng
        if ($hoSo->MaHoiDong !== $request->MaHoiDong) {
            abort(403, 'Hồ sơ bảo vệ không thuộc Hội đồng đang làm việc.');
        }

        // Kiểm tra quyền: Bắt buộc là Thư ký Hội đồng hoặc Trưởng khoa/Giáo vụ/Admin
        $tvRecord = ThanhVienHoiDong::where('MaHoiDong', $request->MaHoiDong)
            ->where('MaGV', $giangVien->MaGV)
            ->first();

        $isThuKy = $tvRecord && in_array($tvRecord->VaiTro, ['Thư ký', 'Ủy viên thư ký']);
        $isGiaoVuOrTruongKhoa = $user->isTruongKhoa() || $user->isGiaoVu() || $user->isAdmin();

        if (!$isThuKy && !$isGiaoVuOrTruongKhoa) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chức năng chuẩn bị phiếu chấm và yêu cầu tiêu chí chức năng thuộc thẩm quyền của Thư ký Hội đồng.',
                ], 403);
            }
            return redirect()->back()->with('error', 'Chức năng chuẩn bị phiếu chấm và yêu cầu tiêu chí chức năng thuộc thẩm quyền của Thư ký Hội đồng. Chủ tịch Hội đồng chỉ thực hiện điều hành buổi bảo vệ theo quy chế mới.');
        }

        // Xác định loại khóa luận và trần điểm quy định
        $loaiKhoaLuan = $hoSo->xacDinhLoaiKhoaLuan() ?? 'KLCN';
        $maxCap = PhieuChamDiem::getMaxDiemChucNang($loaiKhoaLuan);

        // Chuẩn hóa và lưu nội dung tiêu chí chức năng
        $rawText = trim($request->TieuChiChucNang ?? '');
        $hoSo->TieuChiChucNang = $rawText;
        $hoSo->TrangThaiChuanBiPhieu = 'DaChuanBi';
        $hoSo->NguoiChuanBiPhieu = $giangVien->MaGV;
        $hoSo->NgayChuanBiPhieu = now();
        $hoSo->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Thư ký Hội đồng đã cập nhật yêu cầu chức năng thành công (Hệ {$loaiKhoaLuan} - Trần: {$maxCap}đ). Toàn bộ phiếu chấm của các thành viên Hội đồng đã được đồng bộ.",
                'data'    => [
                    'TieuChiChucNang' => $hoSo->TieuChiChucNang,
                    'loaiKhoaLuan'    => $loaiKhoaLuan,
                    'maxCap'          => $maxCap,
                    'requirements'    => PhieuChamDiem::parseFunctionalRequirements($rawText, $loaiKhoaLuan),
                ],
            ]);
        }

        return redirect()->route('giangvien.hoidong.index', [
            'tab'  => 'chamdiem',
            'hd'   => $request->MaHoiDong,
            'hoso' => $request->MaHoSo,
        ])->with('success', "Thư ký Hội đồng đã cập nhật yêu cầu chức năng và tiêu chí phiếu chấm thành công (Hệ {$loaiKhoaLuan} - Trần: {$maxCap}đ)! Toàn bộ phiếu chấm của các thành viên Hội đồng đã được đồng bộ.");
    }

    /**
     * Thư ký xem đề cương chi tiết đã duyệt của đề tài (Yêu cầu 3.2 - Read-Only)
     */
    public function xemDeCuong(Request $request, $maHoSo)
    {
        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(403, 'Không tìm thấy thông tin giảng viên.');
        }

        $hoSo = HoSoBaoVe::with([
            'hoiDong.thanhViens.giangVien',
            'nhom.truongNhom',
            'nhom.thanhVienNhoms.sinhVien.lop',
            'nhom.deTai.giangVien.boMon.khoa',
            'nhom.deTai.hocPhanRef',
            'nhom.deTai.nganh',
            'nhom.dangKyDeTai',
        ])->findOrFail($maHoSo);

        // IDOR check: Giảng viên phải là thành viên Hội đồng hoặc cấp quản lý
        $isMember = $hoSo->hoiDong?->thanhViens->firstWhere('MaGV', $giangVien->MaGV);
        $isManager = $user->isTruongKhoa() || $user->isGiaoVu() || $user->isAdmin();

        if (!$isMember && !$isManager) {
            abort(403, 'Bạn không thuộc Hội đồng chấm đề tài này và không có quyền xem đề cương.');
        }

        $nhom = $hoSo->nhom;
        $deTai = $nhom?->deTai;
        $isThuKy = $isMember && in_array($isMember->VaiTro, ['Thư ký', 'Ủy viên thư ký']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'MaHoSo'           => $hoSo->MaHoSo,
                    'TenDeTai'         => $deTai?->TenDeTai ?? 'Chưa cập nhật',
                    'GVHD'             => $deTai?->giangVien?->HoTen ?? 'Chưa phân công',
                    'BoMon'            => $deTai?->giangVien?->boMon?->TenBoMon ?? 'Khoa',
                    'Nganh'            => $hoSo->ten_nganh,
                    'MucTieu'          => $deTai?->MucTieu ?? 'Thực hiện đầy đủ các mục tiêu trong đề cương được phê duyệt.',
                    'PhamVi'           => $deTai?->PhamVi ?? 'Phạm vi nghiên cứu, thiết kế và phát triển đồ án tốt nghiệp.',
                    'NoiDung'          => $deTai?->NoiDung ?? 'Khảo sát hiện trạng, phân tích yêu cầu, thiết kế kiến trúc hệ thống, cài đặt chức năng và thử nghiệm thực tế.',
                    'YeuCau'           => $deTai?->YeuCau ?? '',
                    'SanPham'          => $deTai?->SanPhamDuKien ?? 'Báo cáo toàn văn, phần mềm ứng dụng hoàn chỉnh, tài liệu hướng dẫn kỹ thuật.',
                    'NoiDungDeCuongJson' => $hoSo->NoiDungDeCuongJson,
                    'FileDeCuong'      => $deTai?->FileDeCuong ? asset('storage/' . $deTai->FileDeCuong) : null,
                    'TrangThaiChuanBi' => $hoSo->TrangThaiChuanBiPhieu ?? 'ChuaChuanBi',
                    'NguoiChuanBi'     => $hoSo->nguoiChuanBi?->HoTen ?? $hoSo->NguoiChuanBiPhieu,
                    'NgayChuanBi'      => $hoSo->NgayChuanBiPhieu ? Carbon::parse($hoSo->NgayChuanBiPhieu)->format('d/m/Y H:i') : null,
                    'TieuChiChucNang'  => $hoSo->TieuChiChucNang,
                    'isThuKy'          => $isThuKy,
                ]
            ]);
        }

        return redirect()->route('giangvien.hoidong.index', [
            'tab'  => 'chamdiem',
            'hd'   => $hoSo->MaHoiDong,
            'hoso' => $hoSo->MaHoSo,
        ]);
    }

    /**
     * Thư ký Hội đồng khởi tạo phiếu chấm từ đề cương (Yêu cầu 3.3 - Idempotent)
     */
    public function chuanBiPhieu(Request $request)
    {
        $request->validate([
            'MaHoiDong' => 'required|exists:HoiDong,MaHoiDong',
            'MaHoSo'    => 'required|exists:HoSoBaoVe,MaHoSo',
        ]);

        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) abort(403);

        $tvRecord = ThanhVienHoiDong::where('MaHoiDong', $request->MaHoiDong)
            ->where('MaGV', $giangVien->MaGV)
            ->first();

        $isThuKy = $tvRecord && in_array($tvRecord->VaiTro, ['Thư ký', 'Ủy viên thư ký']);
        $isManager = $user->isTruongKhoa() || $user->isGiaoVu() || $user->isAdmin();

        if (!$isThuKy && !$isManager) {
            return redirect()->back()->with('error', 'Chỉ Thư ký Hội đồng mới có quyền chuẩn bị phiếu chấm cho đề tài này.');
        }

        $hoSo = HoSoBaoVe::findOrFail($request->MaHoSo);
        $hoSo->TrangThaiChuanBiPhieu = 'DaChuanBi';
        $hoSo->NguoiChuanBiPhieu = $giangVien->MaGV;
        $hoSo->NgayChuanBiPhieu = now();
        if (empty($hoSo->TieuChiChucNang)) {
            $hoSo->TieuChiChucNang = "1. Xây dựng và kiểm thử hoàn chỉnh các chức năng nghiệp vụ theo đề cương được duyệt.\n2. Thiết kế và tối ưu cơ sở dữ liệu, đảm bảo toàn vẹn và an toàn dữ liệu.\n3. Đóng gói sản phẩm, thực nghiệm và hoàn thiện báo cáo.";
        }
        $hoSo->save();

        return redirect()->route('giangvien.hoidong.index', [
            'tab'  => 'chamdiem',
            'hd'   => $request->MaHoiDong,
            'hoso' => $request->MaHoSo,
        ])->with('success', 'Thư ký Hội đồng đã chuẩn bị thành công mẫu phiếu chấm điểm cho đề tài!');
    }

    /**
     * Ký tên điện tử vẽ trực tiếp trên hệ thống cho Phiếu chấm điểm
     */
    public function kyTen(Request $request)
    {
        $request->validate([
            'MaHoiDong' => 'required|exists:HoiDong,MaHoiDong',
            'MaHoSo'    => 'required|exists:HoSoBaoVe,MaHoSo',
            'chu_ky'    => 'required|string',
        ]);

        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy giảng viên.'], 403);
        }

        $hoiDong = HoiDong::with('thanhViens')->findOrFail($request->MaHoiDong);
        $hoSo = HoSoBaoVe::findOrFail($request->MaHoSo);

        $reason = '';
        if (!$hoiDong->canGrade($giangVien, $hoSo, $reason)) {
            \Illuminate\Support\Facades\Log::warning("Ký tên ngoài thời gian cho phép: Giảng viên [{$giangVien->MaGV}] cố ký tên Hội đồng [{$hoiDong->MaHoiDong}]. Lý do: {$reason}", [
                'ip'        => $request->ip(),
                'user_id'   => $user->MaNguoiDung ?? null,
                'system_now'=> HoiDong::getSystemNow()->toDateTimeString(),
            ]);

            return response()->json(['success' => false, 'message' => $reason], 403);
        }

        $chuKy = $request->chu_ky;
        $ngayKy = now();

        // Cập nhật chữ ký vào các phiếu chấm hiện có của giảng viên này cho hồ sơ
        PhieuChamDiem::where('MaHoiDong', $request->MaHoiDong)
            ->where('MaHoSo', $request->MaHoSo)
            ->where('MaGV', $giangVien->MaGV)
            ->update([
                'ChuKy'  => $chuKy,
                'NgayKy' => $ngayKy,
            ]);

        // Lưu làm chữ ký cá nhân mặc định của giảng viên
        if ($request->boolean('luu_mac_dinh', true) || empty($giangVien->ChuKy)) {
            $giangVien->ChuKy = $chuKy;
            $giangVien->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã ký tên điện tử thành công! Nút Xác Nhận & Khóa Phiếu Chấm đã được mở khóa.',
            'chu_ky'  => $chuKy,
            'ngay_ky' => $ngayKy->format('d/m/Y H:i'),
            'ten_gv'  => $giangVien->HoTen,
        ]);
    }

    /**
     * Xuất file PDF Phiếu chấm điểm chính thức có Logo và chuẩn định dạng Khoa CNTT
     */
    public function xuatPdf(Request $request)
    {
        $request->validate([
            'MaHoiDong' => 'required|exists:HoiDong,MaHoiDong',
            'MaHoSo'    => 'required|exists:HoSoBaoVe,MaHoSo',
        ]);

        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(403, 'Không tìm thấy thông tin giảng viên.');
        }

        $activeHoiDong = HoiDong::with(['thanhViens.giangVien', 'khoa'])->findOrFail($request->MaHoiDong);
        $activeHoSo = HoSoBaoVe::with(['nhom.thanhViens.sinhVien.lop', 'nhom.deTai.giangVien'])->findOrFail($request->MaHoSo);

        // Chỉ cho phép xuất phiếu khi đã có thông tin chi tiết mục 6 (Cài đặt chức năng theo yêu cầu bài toán)
        if (empty(trim($activeHoSo->TieuChiChucNang ?? ''))) {
            return redirect()->back()->with('error', 'Chưa thể xuất phiếu PDF: Đề tài này chưa có thông tin chi tiết mục 6 (Cài đặt chức năng theo yêu cầu bài toán). Vui lòng yêu cầu Chủ tịch Hội đồng nhấn nút "Cài đặt chức năng" để cấu hình chi tiết trước khi xuất phiếu!');
        }

        $activeNhom = $activeHoSo->nhom;
        $activeDeTai = $activeNhom?->deTai;
        $sinhViens = $activeNhom?->thanhViens->pluck('sinhVien')->filter() ?? collect();

        $tvRecord = $activeHoiDong->thanhViens->firstWhere('MaGV', $giangVien->MaGV);
        $vaiTroGV = $tvRecord?->VaiTro ?? 'Thành viên Hội đồng';
        $gvhdHoTen = $activeDeTai?->giangVien?->HoTen ?? 'Chưa phân công';

        $loaiKhoaLuan = $activeHoSo->LoaiKhoaLuan ?? 'KLCN';
        $customChucNang = $activeHoSo->TieuChiChucNang;

        $rubric = ($loaiKhoaLuan === 'KLKS') 
            ? PhieuChamDiem::getRubricKLKS($customChucNang) 
            : PhieuChamDiem::getRubricKLCN($customChucNang);

        $tenBieuMau = ($loaiKhoaLuan === 'KLKS') 
            ? 'PHIẾU CHẤM ĐIỂM KHÓA LUẬN KỸ SƯ' 
            : 'PHIẾU CHẤM ĐIỂM KHÓA LUẬN CỬ NHÂN';

        $diemDaCham = PhieuChamDiem::where('MaHoiDong', $activeHoiDong->MaHoiDong)
            ->where('MaHoSo', $activeHoSo->MaHoSo)
            ->where('MaGV', $giangVien->MaGV)
            ->get()
            ->keyBy('MaSV');

        $ngayCham = now();
        $firstPhieu = $diemDaCham->first();
        if ($firstPhieu && $firstPhieu->NgayXacNhan) {
            $ngayCham = $firstPhieu->NgayXacNhan;
        }

        // Đường dẫn logo
        $logoPath = public_path('images/logotruong.jpg');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $functionalRequirements = PhieuChamDiem::parseFunctionalRequirements($customChucNang, $loaiKhoaLuan);
        $chuKy = $firstPhieu?->ChuKy ?? $giangVien->ChuKy;

        $pdf = Pdf::loadView('pdf.phieu_cham_diem', compact(
            'activeHoiDong',
            'activeHoSo',
            'activeDeTai',
            'sinhViens',
            'giangVien',
            'vaiTroGV',
            'gvhdHoTen',
            'loaiKhoaLuan',
            'rubric',
            'functionalRequirements',
            'tenBieuMau',
            'diemDaCham',
            'ngayCham',
            'logoBase64',
            'chuKy'
        ))->setPaper('a4', 'portrait');

        $safeDeTai = \Illuminate\Support\Str::slug($activeDeTai?->TenDeTai ?? 'DeTai', '_');
        $filename = "Phieu_Cham_Diem_{$activeHoiDong->MaHoiDong}_{$safeDeTai}_{$giangVien->MaGV}.pdf";
        return $pdf->stream($filename);
    }

    /**
     * Xuất file Word (.docx) Phiếu chấm điểm chính thức dựa trên biểu mẫu chuẩn Khoa CNTT
     */
    public function xuatWord(Request $request)
    {
        $request->validate([
            'MaHoiDong' => 'required|exists:HoiDong,MaHoiDong',
            'MaHoSo'    => 'required|exists:HoSoBaoVe,MaHoSo',
        ]);

        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(403, 'Không tìm thấy thông tin giảng viên.');
        }

        $activeHoiDong = HoiDong::with(['thanhViens.giangVien', 'khoa'])->findOrFail($request->MaHoiDong);
        $activeHoSo = HoSoBaoVe::with(['nhom.thanhViens.sinhVien.lop', 'nhom.deTai.giangVien'])->findOrFail($request->MaHoSo);

        // Chỉ cho phép xuất phiếu khi đã có thông tin chi tiết mục 6 (Cài đặt chức năng theo yêu cầu bài toán)
        if (empty(trim($activeHoSo->TieuChiChucNang ?? ''))) {
            return redirect()->back()->with('error', 'Chưa thể xuất phiếu Word: Đề tài này chưa có thông tin chi tiết mục 6 (Cài đặt chức năng theo yêu cầu bài toán). Vui lòng yêu cầu Chủ tịch Hội đồng nhấn nút "Cài đặt chức năng" để cấu hình chi tiết trước khi xuất phiếu!');
        }

        $activeNhom = $activeHoSo->nhom;
        $activeDeTai = $activeNhom?->deTai;
        $sinhViens = $activeNhom?->thanhViens->pluck('sinhVien')->filter() ?? collect();

        $tvRecord = $activeHoiDong->thanhViens->firstWhere('MaGV', $giangVien->MaGV);
        $vaiTroGV = $tvRecord?->VaiTro ?? 'Thành viên Hội đồng';
        $gvhdHoTen = $activeDeTai?->giangVien?->HoTen ?? 'Chưa phân công';

        $loaiKhoaLuan = $activeHoSo->LoaiKhoaLuan ?? 'KLCN';
        $customChucNang = $activeHoSo->TieuChiChucNang;

        $diemDaCham = PhieuChamDiem::where('MaHoiDong', $activeHoiDong->MaHoiDong)
            ->where('MaHoSo', $activeHoSo->MaHoSo)
            ->where('MaGV', $giangVien->MaGV)
            ->get()
            ->keyBy('MaSV');

        $ngayCham = now()->format('d/m/Y');
        $firstPhieu = $diemDaCham->first();
        if ($firstPhieu && $firstPhieu->NgayXacNhan) {
            $ngayCham = \Carbon\Carbon::parse($firstPhieu->NgayXacNhan)->format('d/m/Y');
        }

        $templateFile = ($loaiKhoaLuan === 'KLKS') 
            ? resource_path('templates/mau_phieu_cham_KLKS.docx')
            : resource_path('templates/mau_phieu_cham_KLCN.docx');

        if (!file_exists($templateFile)) {
            return redirect()->back()->with('error', 'Không tìm thấy file biểu mẫu Word chuẩn trên hệ thống.');
        }

        $scoresData = [];
        foreach ($sinhViens as $sv) {
            $phieu = $diemDaCham->get($sv->MaSV);
            $scoresData[$sv->MaSV] = array_merge(
                $phieu?->ChiTietDiem ?? [],
                ['total' => $phieu?->Diem ?? null]
            );
        }

        $payload = [
            'ten_de_tai'        => $activeDeTai?->TenDeTai ?? 'Chưa cập nhật',
            'sinh_viens'        => $sinhViens->map(fn($s) => [
                'ma_sv'  => $s->MaSV,
                'ho_ten' => $s->HoTen,
                'lop'    => $s->lop?->TenLop ?? ($s->MaLop ?? '')
            ])->values()->toArray(),
            'gvhd_ho_ten'       => $gvhdHoTen,
            'ten_hoi_dong'      => $activeHoiDong->TenHoiDong ?? $activeHoiDong->MaHoiDong,
            'phong'             => $activeHoSo->PhongBaoVe ?? ($activeHoiDong->DiaDiem ?? 'Phòng B.304'),
            'ngay_cham'         => $ngayCham,
            'custom_chuc_nang'  => $customChucNang,
            'scores'            => $scoresData,
            'giang_vien_ho_ten' => $giangVien->HoTen,
            'vai_tro_gv'        => $vaiTroGV,
            'chu_ky'            => $firstPhieu?->ChuKy ?? $giangVien->ChuKy,
            'loai_khoa_luan'    => $loaiKhoaLuan,
        ];

        $tempJson = tempnam(sys_get_temp_dir(), 'phieu_cham_') . '.json';
        file_put_contents($tempJson, json_encode($payload, JSON_UNESCAPED_UNICODE));

        $safeDeTai = \Illuminate\Support\Str::slug($activeDeTai?->TenDeTai ?? 'DeTai', '_');
        $outDocx = tempnam(sys_get_temp_dir(), 'phieu_word_') . '.docx';
        $scriptPath = base_path('scripts/export_phieu_cham_word.py');

        $cmd = "python " . escapeshellarg($scriptPath) . " " . escapeshellarg($templateFile) . " " . escapeshellarg($tempJson) . " " . escapeshellarg($outDocx);
        exec($cmd, $output, $returnCode);

        @unlink($tempJson);

        if ($returnCode !== 0 || !file_exists($outDocx)) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tạo file Word từ biểu mẫu. Vui lòng thử lại hoặc tải file PDF.');
        }

        $filename = "Phieu_Cham_Diem_{$activeHoiDong->MaHoiDong}_{$safeDeTai}_{$giangVien->MaGV}.docx";
        return response()->download($outDocx, $filename)->deleteFileAfterSend(true);
    }
}
