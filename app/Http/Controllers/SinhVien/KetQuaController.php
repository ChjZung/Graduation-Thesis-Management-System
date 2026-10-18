<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Helpers\IdGenerator;
use App\Helpers\HtmlHelper;
use App\Models\KetQuaSinhVien;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use App\Models\Nhom;
use App\Models\HoSoBaoVe;
use App\Models\TepHoSoBaoVe;
use App\Models\PhieuChamDiem;
use App\Models\BaoCaoSauBaoVe;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KetQuaController extends Controller
{
    /**
     * Màn hình Kết Quả Khóa Luận Tốt Nghiệp.
     * Chỉ sinh viên có đề tài bảo vệ đã được Trưởng khoa công bố chính thức mới xem được điểm.
     */
    public function index()
    {
        $user = Auth::user();
        $sinhVien = SinhVien::getLoggedInSinhVien($user);

        if (!$sinhVien) {
            return redirect()->route('login');
        }

        // Kiểm tra xem nhóm đã có Hội đồng và báo cáo xong chưa
        $daBaoCaoXong = $sinhVien->daCoHoiDongVaBaoCaoXong();

        $tv = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->first();

        $nhom = $tv ? Nhom::with([
            'truongNhom',
            'thanhViens.sinhVien',
            'deTai.giangVien.boMon',
            'deTai.hocPhanRef',
        ])->find($tv->MaNhom) : null;

        $hoSo = $nhom ? HoSoBaoVe::with([
            'hoiDong.thanhViens.giangVien.boMon',
            'hoiDong.giangVien',
            'tepHoSoBaoVes',
            'phienBanMoiNhat',
            'baoCaoSauBaoVes',
        ])->where('MaNhom', $nhom->MaNhom)->first() : null;

        $isLeader = false;
        if ($nhom) {
            $isLeader = ($nhom->truongNhom?->MaSV === $sinhVien->MaSV)
                || ($nhom->MaTruongNhom === $sinhVien->MaSV)
                || ($tv && mb_stripos($tv->VaiTro, 'trưởng') !== false);
        }

        // Kết quả điểm của sinh viên hiện tại
        $ketQua = KetQuaSinhVien::with('hocKy')
            ->where('MaSV', $sinhVien->MaSV)
            ->latest()
            ->first();

        // Kiểm tra xem kết quả đã được Trưởng khoa công bố chính thức chưa
        $daCongBoChinhThuc = false;
        if ($ketQua && $ketQua->TrangThai === 'Đã công bố') {
            $daCongBoChinhThuc = true;
        } elseif ($hoSo && in_array($hoSo->TrangThaiKetQua, ['DaCongBo', 'CoYeuCauChinhSua', 'DaNopChinhSua', 'DaHoanThanh'])) {
            $daCongBoChinhThuc = true;
        }

        // Kết quả của toàn bộ các thành viên trong nhóm (chỉ hiển thị khi đã công bố)
        $memberIds = $nhom ? $nhom->thanhViens->pluck('MaSV')->toArray() : [];
        $allKetQuaNhom = (!empty($memberIds) && $daCongBoChinhThuc)
            ? KetQuaSinhVien::whereIn('MaSV', $memberIds)->get()->keyBy('MaSV')
            : collect();

        // Danh sách phiếu chấm điểm từ các thành viên Hội đồng (chỉ hiển thị khi đã công bố)
        $phieuChamDiems = ($hoSo && $daCongBoChinhThuc)
            ? PhieuChamDiem::with('giangVien.boMon')
                ->where('MaHoSo', $hoSo->MaHoSo)
                ->where('TrangThai', 'Đã hoàn thành')
                ->get()
            : collect();

        return view('sinhvien.ketqua.index', compact(
            'sinhVien',
            'nhom',
            'hoSo',
            'isLeader',
            'ketQua',
            'allKetQuaNhom',
            'phieuChamDiems',
            'daBaoCaoXong',
            'daCongBoChinhThuc'
        ));
    }

    /**
     * Màn hình riêng biệt: Bảo vệ khóa luận → Hoàn thiện báo cáo sau bảo vệ
     */
    public function hoanThienBaoCao()
    {
        $user = Auth::user();
        $sinhVien = SinhVien::getLoggedInSinhVien($user);

        if (!$sinhVien) {
            return redirect()->route('login');
        }

        $tv = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->first();

        if (!$tv) {
            return redirect()->route('sinhvien.nhom.index')->with('error', 'Bạn chưa tham gia nhóm khóa luận nào.');
        }

        $nhom = Nhom::with([
            'truongNhom',
            'thanhViens.sinhVien',
            'deTai.giangVien.boMon',
        ])->find($tv->MaNhom);

        $hoSo = HoSoBaoVe::with([
            'hoiDong',
            'baoCaoSauBaoVes.sinhVienNop',
        ])->where('MaNhom', $nhom->MaNhom)->first();

        if (!$hoSo || !$hoSo->hoiDong) {
            return redirect()->route('sinhvien.hoso.index')
                ->with('error', 'Chức năng Hoàn thiện báo cáo sau bảo vệ chỉ áp dụng sau khi nhóm đã có Hội đồng và hoàn thành phiên bảo vệ.');
        }

        $isLeader = ($nhom->truongNhom?->MaSV === $sinhVien->MaSV)
            || ($nhom->MaTruongNhom === $sinhVien->MaSV)
            || ($tv && mb_stripos($tv->VaiTro, 'trưởng') !== false);

        // Lấy danh sách lịch sử nộp chỉnh sửa
        $lichSuNop = $hoSo->baoCaoSauBaoVes;

        return view('sinhvien.ketqua.hoan_thien_bao_cao', compact(
            'sinhVien',
            'nhom',
            'hoSo',
            'isLeader',
            'lichSuNop'
        ));
    }

    /**
     * Nhóm trưởng nộp lại tệp Báo cáo toàn văn đã chỉnh sửa theo kết luận Hội đồng
     */
    public function nopBaoCaoChinhSua(Request $request)
    {
        $request->validate([
            'MaHoSo'               => 'required|string|exists:HoSoBaoVe,MaHoSo',
            'FileBaoCaoChinhSua'   => 'required|file|mimes:pdf|max:51200',
            'TomTatChinhSua'       => 'required|string|max:3000',
        ], [
            'FileBaoCaoChinhSua.required' => 'Vui lòng đính kèm tệp Báo cáo khóa luận đã chỉnh sửa định dạng .PDF.',
            'FileBaoCaoChinhSua.mimes'    => 'Tệp báo cáo chỉnh sửa bắt buộc phải là định dạng chuẩn PDF.',
            'FileBaoCaoChinhSua.max'      => 'Dung lượng tệp không được vượt quá 50MB.',
            'TomTatChinhSua.required'     => 'Vui lòng nhập tóm tắt các nội dung nhóm đã tiếp thu và chỉnh sửa.',
        ]);

        $user = Auth::user();
        $sinhVien = SinhVien::getLoggedInSinhVien($user);
        if (!$sinhVien) abort(403);

        $hoSo = HoSoBaoVe::with(['nhom.truongNhom', 'nhom.deTai.giangVien', 'hoiDong'])->findOrFail($request->MaHoSo);
        $nhom = $hoSo->nhom;

        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('MaNhom', $nhom->MaNhom)
            ->where('TrangThai', 'da_tham_gia')
            ->firstOrFail();

        $isLeader = ($nhom->truongNhom?->MaSV === $sinhVien->MaSV)
            || ($nhom->MaTruongNhom === $sinhVien->MaSV)
            || ($thanhVienRecord && mb_stripos($thanhVienRecord->VaiTro, 'trưởng') !== false);

        if (!$isLeader) {
            return redirect()->back()->with('error', 'Chỉ có Nhóm trưởng mới có quyền đại diện nhóm nộp lại báo cáo chỉnh sửa sau bảo vệ.');
        }

        DB::transaction(function () use ($request, $hoSo, $nhom, $sinhVien) {
            $file = $request->file('FileBaoCaoChinhSua');
            $filePath = $file->store("hoso/{$hoSo->MaNhom}/bao_cao_sau_bao_ve", 'public');

            $lanChinhSuaHienTai = BaoCaoSauBaoVe::where('MaHoSo', $hoSo->MaHoSo)->count() + 1;

            $baoCaoMoi = BaoCaoSauBaoVe::create([
                'MaHoSo'         => $hoSo->MaHoSo,
                'LanChinhSua'    => $lanChinhSuaHienTai,
                'DuongDanFile'   => $filePath,
                'TenFile'        => $file->getClientOriginalName(),
                'DungLuong'      => $file->getSize(),
                'TomTatChinhSua' => HtmlHelper::clean($request->TomTatChinhSua),
                'NgayNop'        => now(),
                'NguoiNop'       => $sinhVien->MaSV,
                'TrangThai'      => 'Đã nộp',
            ]);

            // Cập nhật trạng thái của HoSoBaoVe
            $hoSo->update([
                'TrangThaiChinhSuaSauBaoVe' => 'DaNop',
                'TrangThaiKetQua'           => 'DaNopChinhSua',
            ]);

            // Gửi thông báo đến Người kiểm tra (GVHD hoặc Thành viên HĐ phụ trách)
            $nguoiKiemTra = $hoSo->NguoiKiemTraChinhSua ?? $nhom->deTai?->MaGV;
            if ($nguoiKiemTra) {
                ThongBaoService::gui(
                    $nguoiKiemTra,
                    'Nhóm đã nộp lại báo cáo chỉnh sửa sau bảo vệ',
                    "Nhóm trưởng {$sinhVien->HoTen} (Nhóm {$nhom->TenNhom} - Đề tài: {$nhom->deTai?->TenDeTai}) đã nộp lại Báo cáo chỉnh sửa sau bảo vệ (Lần {$lanChinhSuaHienTai}). Kính mời Thầy/Cô kiểm tra và đánh giá.",
                    'Báo cáo sau bảo vệ'
                );
            }

            // Gửi thông báo cho thành viên trong nhóm
            ThongBaoService::guiDenNhom(
                $nhom->MaNhom,
                'Đã nộp báo cáo hoàn thiện sau bảo vệ',
                "Nhóm trưởng {$sinhVien->HoTen} đã nộp lại Báo cáo khóa luận hoàn thiện sau bảo vệ (Lần {$lanChinhSuaHienTai}) để Thầy/Cô kiểm tra.",
                'Báo cáo sau bảo vệ'
            );
        });

        return redirect()->route('sinhvien.ketqua.hoanthien')
            ->with('success', 'Nộp tệp Báo cáo toàn văn đã chỉnh sửa sau bảo vệ thành công! Hồ sơ đã được chuyển đến Giảng viên phụ trách để kiểm tra và xác nhận.');
    }

    /**
     * Nhóm trưởng nộp bản khóa luận hoàn chỉnh sau bảo vệ & tất cả kết quả khóa luận để lưu trữ tại Khoa
     */
    public function nopKetQuaHoanChinh(Request $request)
    {
        $request->validate([
            'MaNhom'          => 'required|string|exists:Nhom,MaNhom',
            'FileBanChinhSua' => 'nullable|file|mimes:pdf,doc,docx,zip,rar|max:51200',
            'LinkSourceCode'  => 'nullable|url|max:500',
            'LinkDeploy'      => 'nullable|url|max:500',
            'LinkDemo'        => 'nullable|url|max:500',
            'GhiChuChinhSua'  => 'nullable|string|max:1500',
        ], [
            'FileBanChinhSua.mimes'   => 'Tệp báo cáo hoàn chỉnh phải có định dạng .pdf, .doc, .docx, .zip, .rar.',
            'FileBanChinhSua.max'     => 'Dung lượng tệp không được vượt quá 50MB.',
            'LinkSourceCode.url'      => 'Đường dẫn Source Code không hợp lệ (cần bắt đầu bằng http:// hoặc https://).',
            'LinkDeploy.url'          => 'Đường dẫn triển khai sản phẩm không hợp lệ.',
            'LinkDemo.url'            => 'Đường dẫn Video Demo / Slide thuyết trình không hợp lệ.',
        ]);

        $user = Auth::user();
        $sinhVien = SinhVien::getLoggedInSinhVien($user);
        if (!$sinhVien) {
            abort(403, 'Không tìm thấy hồ sơ sinh viên.');
        }

        if (!$sinhVien->daCoHoiDongVaBaoCaoXong()) {
            return redirect()->back()->with('error', 'Cổng nộp kết quả khóa luận hoàn chỉnh chỉ mở sau khi nhóm đã có Hội đồng và hoàn tất phiên báo cáo bảo vệ trước Hội đồng.');
        }

        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('MaNhom', $request->MaNhom)
            ->where('TrangThai', 'da_tham_gia')
            ->firstOrFail();

        $nhom = Nhom::with(['truongNhom', 'deTai.giangVien'])->findOrFail($request->MaNhom);
        $isLeader = ($nhom->truongNhom?->MaSV === $sinhVien->MaSV)
            || ($nhom->MaTruongNhom === $sinhVien->MaSV)
            || ($thanhVienRecord && mb_stripos($thanhVienRecord->VaiTro, 'trưởng') !== false);

        if (!$isLeader) {
            return redirect()->back()->with('error', 'Chỉ có Nhóm trưởng mới có quyền đại diện nhóm nộp hoặc cập nhật kết quả khóa luận hoàn chỉnh sau bảo vệ.');
        }

        $hoSo = HoSoBaoVe::where('MaNhom', $nhom->MaNhom)->firstOrFail();

        if (empty($hoSo->FileBanChinhSua) && !$request->hasFile('FileBanChinhSua')) {
            return redirect()->back()->with('error', 'Vui lòng đính kèm tệp Báo cáo toàn văn khóa luận hoàn chỉnh sau bảo vệ (.pdf, .docx, .zip).');
        }

        DB::transaction(function () use ($request, $hoSo, $nhom, $sinhVien) {
            $updateData = [
                'NgayNopBanChinhSua' => now(),
            ];

            // 1. Tải lên tệp báo cáo hoàn chỉnh
            if ($request->hasFile('FileBanChinhSua')) {
                $file = $request->file('FileBanChinhSua');
                $filePath = $file->store("hoso/{$hoSo->MaNhom}/ban_hoan_chinh", 'public');
                $updateData['FileBanChinhSua'] = $filePath;

                TepHoSoBaoVe::create([
                    'MaTep'        => IdGenerator::nextTepHoSoBaoVe(),
                    'TenTep'       => $file->getClientOriginalName(),
                    'LoaiTep'      => 'Bản hoàn chỉnh sau bảo vệ',
                    'DuongDanFile' => $filePath,
                    'PhienBan'     => 'Final',
                    'NgayNop'      => now()->toDateString(),
                    'TrangThai'    => 'Đã nộp',
                    'MaHoSo'       => $hoSo->MaHoSo,
                ]);
            }

            // 2. Cập nhật mã nguồn & deploy
            if ($request->filled('LinkSourceCode')) {
                $updateData['LinkSourceCode'] = trim($request->LinkSourceCode);
            }
            if ($request->filled('LinkDeploy')) {
                $updateData['LinkDeploy'] = trim($request->LinkDeploy);
            }

            // 3. Cập nhật liên kết demo / slide hoàn chỉnh
            if ($request->filled('LinkDemo')) {
                $lienKetHienTai = is_array($hoSo->LienKetKhac) ? $hoSo->LienKetKhac : [];
                $lienKetHienTai[] = [
                    'loai'    => 'link',
                    'tieu_de' => 'Video Demo / Slide Hoàn Chỉnh Sau Bảo Vệ',
                    'url'     => trim($request->LinkDemo),
                    'mo_ta'   => 'Liên kết sản phẩm hoàn thiện cuối cùng nộp lưu trữ Khoa',
                ];
                $updateData['LienKetKhac'] = $lienKetHienTai;
            }

            // 4. Ghi chú tiếp thu ý kiến Hội đồng
            if ($request->filled('GhiChuChinhSua')) {
                $cleanNote = HtmlHelper::clean($request->GhiChuChinhSua);
                $updateData['GhiChu'] = ($hoSo->GhiChu ? $hoSo->GhiChu . " | " : "") . "[Hoàn chỉnh sau bảo vệ]: " . $cleanNote;
            }

            $hoSo->update($updateData);

            // 5. Gửi thông báo đến thành viên nhóm và GVHD
            ThongBaoService::guiDenNhom(
                $nhom->MaNhom,
                'Đã nộp kết quả khóa luận hoàn chỉnh sau bảo vệ',
                "Nhóm trưởng {$sinhVien->HoTen} đã hoàn tất nộp bản khóa luận hoàn chỉnh sau bảo vệ và toàn bộ kết quả sản phẩm để lưu trữ tại Khoa.",
                'Nhóm'
            );

            if ($nhom->deTai?->MaGV) {
                ThongBaoService::gui(
                    $nhom->deTai->MaGV,
                    'Nhóm khóa luận đã nộp bản hoàn chỉnh lưu trữ khoa',
                    "Nhóm {$nhom->TenNhom} (Đề tài: {$nhom->deTai->TenDeTai}) đã nộp bản khóa luận hoàn chỉnh sau bảo vệ và các kết quả liên quan.",
                    'Giảng viên'
                );
            }
        });

        return redirect()->back()->with('success', 'Nộp toàn bộ kết quả khóa luận hoàn chỉnh sau bảo vệ thành công! Hồ sơ đã sẵn sàng để Giáo vụ Khoa xác nhận lưu trữ chính thức.');
    }
}
