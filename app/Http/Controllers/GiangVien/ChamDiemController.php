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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChamDiemController extends Controller
{
    public function __construct(private GradeCalculationService $gradeService) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        // Lấy tất cả Hội đồng mà giảng viên này tham gia
        $hoiDongs = HoiDong::whereHas('thanhViens', fn($q) => $q->where('MaGV', $giangVien->MaGV))
            ->with([
                'thanhViens.giangVien',
                'hoSoBaoVes.nhom.thanhViens.sinhVien.lop',
                'hoSoBaoVes.nhom.deTai.giangVien',
                'hoSoBaoVes.nhom.truongNhom',
            ])
            ->get();

        $activeHoiDong = null;
        if ($hoiDongs->isNotEmpty()) {
            if ($request->filled('hd')) {
                $activeHoiDong = $hoiDongs->firstWhere('MaHoiDong', $request->hd) ?? $hoiDongs->first();
            } else {
                $activeHoiDong = $hoiDongs->first();
            }
        }

        // Lấy nhóm / hồ sơ bảo vệ đang chọn
        $activeHoSo = null;
        if ($activeHoiDong && $activeHoiDong->hoSoBaoVes->isNotEmpty()) {
            if ($request->filled('hoso')) {
                $activeHoSo = $activeHoiDong->hoSoBaoVes->firstWhere('MaHoSo', $request->hoso) ?? $activeHoiDong->hoSoBaoVes->first();
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

        $rubricKLCN = PhieuChamDiem::getRubricKLCN();
        $rubricKLKS = PhieuChamDiem::getRubricKLKS();

        $activeNhom = $activeHoSo?->nhom;
        $activeDeTai = $activeNhom?->deTai;
        $activeThanhViens = $activeNhom?->thanhViens ?? collect();
        $sinhViens = $activeThanhViens->pluck('sinhVien')->filter();

        $tvRecord = $activeHoiDong?->thanhViens->firstWhere('MaGV', $giangVien->MaGV);
        $vaiTroGV = $tvRecord?->VaiTro ?? 'Thành viên Hội đồng';

        $rubric = ($loaiKhoaLuan === 'KLKS') ? $rubricKLKS : $rubricKLCN;
        $tenBieuMau = ($loaiKhoaLuan === 'KLKS') ? 'PHIẾU CHẤM ĐIỂM KHÓA LUẬN KỸ SƯ' : 'PHIẾU CHẤM ĐIỂM KHÓA LUẬN CỬ NHÂN';

        return view('giangvien.chamdiem.index', compact(
            'giangVien',
            'hoiDongs',
            'activeHoiDong',
            'activeHoSo',
            'activeDeTai',
            'sinhViens',
            'vaiTroGV',
            'rubric',
            'tenBieuMau',
            'loaiKhoaLuan',
            'canhBaoChuaXacDinhLoai',
            'diemDaCham',
            'daKhoaPhieu',
            'phieuCu',
            'rubricKLCN',
            'rubricKLKS'
        ));
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

        // Kiểm tra quyền: Chỉ Giảng viên thuộc Hội đồng mới được chấm điểm
        $isMember = ThanhVienHoiDong::where('MaHoiDong', $request->MaHoiDong)
            ->where('MaGV', $giangVien->MaGV)
            ->exists();

        if (!$isMember) {
            abort(403, 'Bạn không thuộc Hội đồng bảo vệ này và không có quyền nhập điểm.');
        }

        $hoSo = HoSoBaoVe::with(['nhom.deTai', 'nhom.thanhViens'])->findOrFail($request->MaHoSo);

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
        $rubric = ($request->LoaiKhoaLuan === 'KLKS') ? PhieuChamDiem::getRubricKLKS() : PhieuChamDiem::getRubricKLCN();
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

        DB::transaction(function () use ($request, $giangVien, $hoSo, $maDeTai, $maHocKy, $validStudentIds, $rubricMap, $isFinal, $trangThaiPhieu) {
            foreach ($request->diems as $key => $diemData) {
                $maSV = is_numeric($key) ? ($diemData['MaSV'] ?? null) : $key;
                if (!$maSV) continue;

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

                // Điểm khuyến khích không vượt quá 1.0
                if ($diemKhuyenKhich > 1.0) $diemKhuyenKhich = 1.0;

                // Tổng điểm tối đa là 10.0
                $diemSo = round(min(10.0, $tongDiemCoBan + $diemKhuyenKhich), 2);

                $nhanXet = $diemData['NhanXet'] ?? null;

                // Tạo mã phiếu chấm duy nhất
                $hashId = substr(md5($request->MaHoiDong . '_' . $giangVien->MaGV . '_' . $maSV), 0, 8);
                $maPhieu = 'PCD_' . strtoupper($hashId);

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
                        'MaDeTai'         => $maDeTai,
                        'MaHoSo'          => $hoSo->MaHoSo,
                        'MaHocKy'         => $maHocKy,
                        'NgayCham'        => now(),
                    ]
                );
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
            ? 'Đã Ký xác nhận & Khóa phiếu chấm điểm thành công! Điểm số đã được ghi nhận vào hệ thống.'
            : 'Đã lưu bản nháp phiếu chấm điểm thành công! Thầy/Cô có thể tiếp tục chỉnh sửa trước khi Ký xác nhận.';

        return redirect()->route('giangvien.chamdiem.index', [
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
}
