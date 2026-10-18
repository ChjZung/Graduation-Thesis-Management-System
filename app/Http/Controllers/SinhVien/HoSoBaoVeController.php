<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\HoSoBaoVe;
use App\Models\PhienBanHoSoBaoVe;
use App\Models\TepHoSoBaoVe;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use App\Models\Nhom;
use App\Models\BaoCaoTienDo;
use App\Helpers\IdGenerator;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HoSoBaoVeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->first();

        if (!$sinhVien) {
            return redirect()->route('sinhvien.nhom.index')
                ->with('error', 'Bạn chưa có hồ sơ sinh viên trong hệ thống.');
        }

        // Lấy danh sách tất cả các nhóm mà sinh viên tham gia hợp lệ
        $memberships = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('TrangThai', 'da_tham_gia')
            ->with([
                'nhom.hocPhan',
                'nhom.hocKy',
                'nhom.deTai.giangVien',
                'nhom.thanhViens.sinhVien',
                'nhom.truongNhom',
            ])
            ->get();

        if ($memberships->isEmpty()) {
            return view('sinhvien.hoso_baove.index', [
                'sinhVien'    => $sinhVien,
                'memberships' => collect(),
                'nhom'        => null,
                'hoSo'        => null,
                'moc5Dat'     => false,
            ]);
        }

        // Xác định nhóm được chọn (mặc định là nhóm đầu tiên hoặc theo query param)
        $selectedNhomId = $request->query('nhom_id');
        $selectedMembership = $selectedNhomId 
            ? $memberships->firstWhere('MaNhom', $selectedNhomId) 
            : null;

        if (!$selectedMembership) {
            $selectedMembership = $memberships->first();
        }

        $nhom = $selectedMembership->nhom;

        // Kiểm tra điều kiện hoàn thành Mốc 5 (Đạt)
        $moc5Dat = ($nhom && $nhom->deTai) ? BaoCaoTienDo::where('MaDeTai', $nhom->deTai->MaDeTai)
            ->where('LanBaoCao', 5)
            ->where('TrangThai', 'Đạt')
            ->exists() : false;

        // Tải hồ sơ bảo vệ cùng lịch sử phiên bản và tệp đính kèm
        $hoSo = $nhom ? HoSoBaoVe::with([
            'hoiDong.thanhViens.giangVien',
            'tepHoSoBaoVes',
            'phanCongPhanBien.giangVien',
            'phienBans.sinhVienNop',
            'phienBans.giangVienDanhGia',
            'deTai.giangVien'
        ])->where('MaNhom', $nhom->MaNhom)->first() : null;

        return view('sinhvien.hoso_baove.index', compact('sinhVien', 'memberships', 'nhom', 'hoSo', 'moc5Dat'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->firstOrFail();

        // Xác thực sinh viên có thuộc nhóm gửi lên không
        $request->validate([
            'MaNhom' => 'required|string|exists:Nhom,MaNhom',
        ]);

        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('MaNhom', $request->MaNhom)
            ->where('TrangThai', 'da_tham_gia')
            ->first();

        if (!$thanhVienRecord) {
            return back()->with('error', 'Bạn không phải là thành viên hợp lệ của nhóm này.');
        }

        $nhom = Nhom::with(['deTai.giangVien'])->findOrFail($request->MaNhom);

        if (!$nhom->deTai) {
            return back()->with('error', 'Nhóm của bạn chưa có đề tài được duyệt.');
        }

        // Kiểm tra điều kiện tiên quyết: phải hoàn thành Đạt Mốc 5
        $moc5Dat = BaoCaoTienDo::where('MaDeTai', $nhom->deTai->MaDeTai)
            ->where('LanBaoCao', 5)
            ->where('TrangThai', 'Đạt')
            ->exists();

        if (!$moc5Dat) {
            return back()->with('error', 'Nhóm cần hoàn thành và được GVHD đánh giá "Đạt" ở Mốc 5 báo cáo tiến độ trước khi nộp hồ sơ bảo vệ.');
        }

        $hoSoHienTai = HoSoBaoVe::with(['phienBans', 'tepHoSoBaoVes'])->where('MaNhom', $nhom->MaNhom)->first();

        // Kiểm tra nếu hồ sơ đã phê duyệt đủ điều kiện hoặc đã phân công thì khóa chỉnh sửa
        if ($hoSoHienTai && in_array($hoSoHienTai->TrangThai, ['Đã phê duyệt', 'Đủ điều kiện bảo vệ', 'Đã phân công', 'Đã phân công hội đồng', 'Đã lưu trữ'])) {
            return back()->with('error', 'Hồ sơ bảo vệ của nhóm đã được phê duyệt đủ điều kiện hoặc đang trong giai đoạn tổ chức bảo vệ. Không thể chỉnh sửa nộp đè.');
        }

        // Quy tắc validation
        $rules = [
            'LinkSourceCode'    => 'required|url|max:500',
            'GhiChuSourceCode'  => 'nullable|string|max:1000',
            'CoTrienKhai'       => 'required|in:co,khong',
            'LinkDeploy'        => 'nullable|required_if:CoTrienKhai,co|url|max:500',
            'GhiChuDeploy'      => 'nullable|string|max:1000',
            'LyDoKhongDeploy'   => 'nullable|required_if:CoTrienKhai,khong|string|max:1000',
            'LienKetKhac'       => 'nullable|array',
            'LienKetKhac.*.tieu_de' => 'nullable|string|max:150',
            'LienKetKhac.*.url'     => 'nullable|url|max:500',
            'LienKetKhac.*.mo_ta'   => 'nullable|string|max:300',
            'GhiChuSinhVien'    => 'nullable|string|max:1000',
        ];

        // Nếu là nộp lần đầu thì FileToanVan, MinhChungTurnitin và TyLeTrungLap bắt buộc
        $phienBanMoiNhat = $hoSoHienTai?->phienBans->first();
        if (!$hoSoHienTai || !$phienBanMoiNhat || empty($phienBanMoiNhat->DuongDanBaoCao)) {
            $rules['FileToanVan'] = 'required|file|mimes:pdf|max:51200';
            $rules['MinhChungTurnitin'] = 'required|file|mimes:pdf|max:30720';
            $rules['TyLeTrungLap'] = 'required|numeric|min:0|max:100';
        } else {
            $rules['FileToanVan'] = 'nullable|file|mimes:pdf|max:51200';
            $rules['MinhChungTurnitin'] = 'nullable|file|mimes:pdf|max:30720';
            $rules['TyLeTrungLap'] = 'nullable|numeric|min:0|max:100';
        }

        $messages = [
            'LinkSourceCode.required'     => 'Vui lòng cung cấp đường dẫn mã nguồn dự án (GitHub/GitLab).',
            'LinkSourceCode.url'          => 'Đường dẫn mã nguồn dự án phải là một URL hợp lệ (bắt đầu bằng http:// hoặc https://).',
            'CoTrienKhai.required'        => 'Vui lòng xác nhận trạng thái triển khai sản phẩm trực tuyến.',
            'LinkDeploy.required_if'      => 'Vui lòng cung cấp đường dẫn triển khai trực tuyến (Deploy URL) khi chọn có triển khai.',
            'LinkDeploy.url'              => 'Đường dẫn triển khai phải là một URL hợp lệ.',
            'LyDoKhongDeploy.required_if' => 'Vui lòng nêu rõ lý do không triển khai trực tuyến (ví dụ: mô hình IoT phần cứng, ứng dụng nội bộ, tài nguyên hạ tầng...).',
            'FileToanVan.required'        => 'Vui lòng tải lên tệp Báo cáo khóa luận toàn văn (định dạng PDF).',
            'FileToanVan.mimes'           => 'Tệp Báo cáo khóa luận toàn văn phải có định dạng PDF.',
            'FileToanVan.max'             => 'Dung lượng tệp Báo cáo khóa luận toàn văn không được vượt quá 50MB.',
            'MinhChungTurnitin.required'  => 'Vui lòng tải lên tệp Minh chứng kết quả kiểm tra Turnitin (PDF).',
            'MinhChungTurnitin.mimes'     => 'Tệp Minh chứng Turnitin phải có định dạng PDF.',
            'MinhChungTurnitin.max'        => 'Dung lượng tệp Minh chứng Turnitin không được vượt quá 30MB.',
            'TyLeTrungLap.required'       => 'Vui lòng nhập tỷ lệ trùng lặp Turnitin (%).',
            'TyLeTrungLap.numeric'        => 'Tỷ lệ trùng lặp Turnitin phải là số.',
            'TyLeTrungLap.max'            => 'Tỷ lệ trùng lặp không thể vượt quá 100%.',
        ];

        $request->validate($rules, $messages);

        // Chuẩn hóa danh sách liên kết bổ sung
        $lienKetKhacList = [];
        if ($request->has('LienKetKhac') && is_array($request->LienKetKhac)) {
            foreach ($request->LienKetKhac as $lk) {
                if (!empty($lk['url']) && !empty($lk['tieu_de'])) {
                    $lienKetKhacList[] = [
                        'tieu_de' => trim($lk['tieu_de']),
                        'url'     => trim($lk['url']),
                        'mo_ta'   => trim($lk['mo_ta'] ?? ''),
                    ];
                }
            }
        }

        DB::transaction(function () use ($request, $sinhVien, $nhom, $hoSoHienTai, $phienBanMoiNhat, $lienKetKhacList) {
            $maHoSo = $hoSoHienTai ? $hoSoHienTai->MaHoSo : IdGenerator::nextHoSoBaoVe();

            // Tính số thứ tự phiên bản mới
            $soPhienBan = $hoSoHienTai ? ($hoSoHienTai->PhienBanHienTai + 1) : 1;
            $tenPhienBan = number_format($soPhienBan, 1, '.', '');

            // Xử lý tệp Báo cáo toàn văn
            $tenFileBaoCao = $phienBanMoiNhat?->TenFileBaoCao;
            $duongDanBaoCao = $phienBanMoiNhat?->DuongDanBaoCao;
            $dungLuongBaoCao = $phienBanMoiNhat?->DungLuongBaoCao;

            if ($request->hasFile('FileToanVan')) {
                $fileTV = $request->file('FileToanVan');
                $tenFileBaoCao = $fileTV->getClientOriginalName();
                $duongDanBaoCao = $fileTV->store("hoso/{$nhom->MaNhom}", 'public');
                $dungLuongBaoCao = $fileTV->getSize();
            }

            // Xử lý tệp Minh chứng Turnitin
            $tenFileTurnitin = $phienBanMoiNhat?->TenFileTurnitin;
            $duongDanTurnitin = $phienBanMoiNhat?->DuongDanTurnitin;

            if ($request->hasFile('MinhChungTurnitin')) {
                $fileTI = $request->file('MinhChungTurnitin');
                $tenFileTurnitin = $fileTI->getClientOriginalName();
                $duongDanTurnitin = $fileTI->store("hoso/{$nhom->MaNhom}", 'public');
            }

            // Tỷ lệ Turnitin
            $tyLeTurnitin = $request->filled('TyLeTrungLap') 
                ? (float)$request->TyLeTrungLap 
                : ($phienBanMoiNhat?->TyLeTurnitin ?? $hoSoHienTai?->TyLeTrungLap ?? 0);

            $linkDeploy = ($request->CoTrienKhai === 'co') ? $request->LinkDeploy : null;
            $ghiChuDeploy = ($request->CoTrienKhai === 'co') ? $request->GhiChuDeploy : null;
            $lyDoKhongDeploy = ($request->CoTrienKhai === 'khong') ? $request->LyDoKhongDeploy : null;

            $trangThaiMoi = $hoSoHienTai ? 'Đã nộp lại - Chờ kiểm tra' : 'Chờ GVHD kiểm tra';

            $ghiChuText = "Tỷ lệ đạo văn Turnitin: {$tyLeTurnitin}%" . ($request->GhiChuSinhVien ? ' | ' . $request->GhiChuSinhVien : '');

            // Cập nhật hoặc tạo HoSoBaoVe tổng thể
            $hoSoData = [
                'NgayNop'          => now()->toDateString(),
                'NgayNopGanNhat'   => now(),
                'TrangThai'        => $trangThaiMoi,
                'XacNhanGVHD'      => 'Chờ xác nhận',
                'GhiChu'           => $ghiChuText,
                'LinkSourceCode'   => $request->LinkSourceCode,
                'GhiChuSourceCode' => $request->GhiChuSourceCode,
                'LinkDeploy'       => $linkDeploy,
                'GhiChuDeploy'     => $ghiChuDeploy,
                'LyDoKhongDeploy'  => $lyDoKhongDeploy,
                'LienKetKhac'      => $lienKetKhacList,
                'PhienBanHienTai'  => $soPhienBan,
                'ThanhPhanCanSua'  => null, // Reset thành phần cần sửa khi sinh viên đã nộp lại
            ];

            if ($hoSoHienTai) {
                $hoSoHienTai->update($hoSoData);
                $hoSo = $hoSoHienTai;
            } else {
                $hoSoData['MaHoSo']  = $maHoSo;
                $hoSoData['NgayLap'] = now()->toDateString();
                $hoSoData['MaNhom']  = $nhom->MaNhom;
                $hoSoData['MaDeTai'] = $nhom->deTai->MaDeTai;
                $hoSo = HoSoBaoVe::create($hoSoData);
            }

            // Tạo bản ghi snapshot phiên bản mới vào phien_ban_ho_so_bao_ve
            PhienBanHoSoBaoVe::create([
                'MaHoSo'           => $hoSo->MaHoSo,
                'PhienBan'         => $tenPhienBan,
                'TenFileBaoCao'    => $tenFileBaoCao,
                'DuongDanBaoCao'   => $duongDanBaoCao,
                'DungLuongBaoCao'  => $dungLuongBaoCao,
                'LinkSourceCode'   => $request->LinkSourceCode,
                'GhiChuSourceCode' => $request->GhiChuSourceCode,
                'LinkDeploy'       => $linkDeploy,
                'GhiChuDeploy'     => $ghiChuDeploy,
                'LyDoKhongDeploy'  => $lyDoKhongDeploy,
                'LienKetKhac'      => $lienKetKhacList,
                'TyLeTurnitin'     => $tyLeTurnitin,
                'TenFileTurnitin'  => $tenFileTurnitin,
                'DuongDanTurnitin' => $duongDanTurnitin,
                'GhiChuSinhVien'   => $request->GhiChuSinhVien,
                'NgayNop'          => now(),
                'NguoiNop'         => $sinhVien->MaSV,
                'TrangThai'        => $trangThaiMoi,
            ]);

            // Cập nhật bảng TepHoSoBaoVe cho tương thích ngược với Admin & Download
            if ($duongDanBaoCao) {
                $tepTV = $hoSo->tepHoSoBaoVes()->where('LoaiTep', 'Khóa luận toàn văn')->first();
                if ($tepTV) {
                    $tepTV->update([
                        'TenTep'       => $tenFileBaoCao,
                        'DuongDanFile' => $duongDanBaoCao,
                        'PhienBan'     => $tenPhienBan,
                        'NgayNop'      => now()->toDateString(),
                        'TrangThai'    => 'Đã nộp',
                    ]);
                } else {
                    TepHoSoBaoVe::create([
                        'MaTep'        => IdGenerator::nextTepHoSoBaoVe(),
                        'TenTep'       => $tenFileBaoCao ?: 'Báo cáo khóa luận toàn văn.pdf',
                        'LoaiTep'      => 'Khóa luận toàn văn',
                        'DuongDanFile' => $duongDanBaoCao,
                        'PhienBan'     => $tenPhienBan,
                        'NgayNop'      => now()->toDateString(),
                        'TrangThai'    => 'Đã nộp',
                        'MaHoSo'       => $hoSo->MaHoSo,
                    ]);
                }
            }

            if ($duongDanTurnitin) {
                $tepTI = $hoSo->tepHoSoBaoVes()->where('LoaiTep', 'Minh chứng đạo văn')->first();
                if ($tepTI) {
                    $tepTI->update([
                        'TenTep'       => $tenFileTurnitin,
                        'DuongDanFile' => $duongDanTurnitin,
                        'PhienBan'     => $tenPhienBan,
                        'NgayNop'      => now()->toDateString(),
                        'TrangThai'    => 'Đã nộp',
                    ]);
                } else {
                    TepHoSoBaoVe::create([
                        'MaTep'        => IdGenerator::nextTepHoSoBaoVe(),
                        'TenTep'       => $tenFileTurnitin ?: 'Minh chứng kiểm tra Turnitin.pdf',
                        'LoaiTep'      => 'Minh chứng đạo văn',
                        'DuongDanFile' => $duongDanTurnitin,
                        'PhienBan'     => $tenPhienBan,
                        'NgayNop'      => now()->toDateString(),
                        'TrangThai'    => 'Đã nộp',
                        'MaHoSo'       => $hoSo->MaHoSo,
                    ]);
                }
            }
        });

        // Gửi thông báo đến Giảng viên hướng dẫn
        $tieuDeTB = $hoSoHienTai 
            ? "📝 Nhóm {$nhom->TenNhom} đã nộp lại hồ sơ bảo vệ (Phiên bản mới)" 
            : "📥 Nhóm {$nhom->TenNhom} đã nộp hồ sơ bảo vệ khóa luận";

        $noiDungTB = "Sinh viên {$sinhVien->HoTen} ({$sinhVien->MaSV}) đại diện nhóm '{$nhom->TenNhom}' đã nộp hồ sơ bảo vệ đề tài '{$nhom->deTai->TenDeTai}'. Vui lòng kiểm tra báo cáo, mã nguồn và sản phẩm triển khai để xác nhận điều kiện bảo vệ.";

        ThongBaoService::guiDenGVHD(
            $nhom->MaNhom,
            $tieuDeTB,
            $noiDungTB,
            'Bảo vệ',
            route('giangvien.xacnhan_baove.show', $hoSoHienTai ? $hoSoHienTai->MaHoSo : HoSoBaoVe::where('MaNhom', $nhom->MaNhom)->value('MaHoSo'))
        );

        return redirect()->route('sinhvien.hoso.index', ['nhom_id' => $nhom->MaNhom])
            ->with('success', 'Nộp hồ sơ bảo vệ khóa luận thành công! Giảng viên hướng dẫn sẽ kiểm tra hồ sơ và phản hồi.');
    }

    /**
     * Sinh viên nộp bản khóa luận hoàn chỉnh sau khi bảo vệ tại Hội đồng
     */
    public function nopBanHoanChinh(Request $request)
    {
        $request->validate([
            'MaNhom'        => 'required|string|exists:Nhom,MaNhom',
            'FileBanChinhSua' => 'required|file|mimes:pdf,doc,docx,zip,rar|max:51200',
            'GhiChu'        => 'nullable|string|max:500',
        ], [
            'FileBanChinhSua.required' => 'Vui lòng đính kèm tệp Báo cáo khóa luận bản hoàn chỉnh sau bảo vệ.',
            'FileBanChinhSua.mimes'    => 'Tệp phải có định dạng .pdf, .doc, .docx, .zip, .rar.',
            'FileBanChinhSua.max'      => 'Dung lượng tệp không được vượt quá 50MB.',
        ]);

        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->firstOrFail();
        $thanhVienRecord = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)
            ->where('MaNhom', $request->MaNhom)
            ->where('TrangThai', 'da_tham_gia')
            ->firstOrFail();

        $hoSo = HoSoBaoVe::where('MaNhom', $thanhVienRecord->MaNhom)->firstOrFail();

        $file = $request->file('FileBanChinhSua');
        $path = $file->store("hoso/{$hoSo->MaNhom}", 'public');

        $hoSo->update([
            'FileBanChinhSua'    => $path,
            'NgayNopBanChinhSua' => now(),
            'GhiChu'             => $hoSo->GhiChu . ($request->GhiChu ? ' | Bản hoàn chỉnh: ' . $request->GhiChu : ' | Đã nộp bản hoàn chỉnh sau bảo vệ'),
        ]);

        TepHoSoBaoVe::create([
            'MaTep'        => IdGenerator::nextTepHoSoBaoVe(),
            'TenTep'       => $file->getClientOriginalName(),
            'LoaiTep'      => 'Bản hoàn chỉnh sau bảo vệ',
            'DuongDanFile' => $path,
            'PhienBan'     => 'Final',
            'NgayNop'      => now()->toDateString(),
            'TrangThai'    => 'Đã nộp',
            'MaHoSo'       => $hoSo->MaHoSo,
        ]);

        return redirect()->back()->with('success', 'Nộp bản khóa luận hoàn chỉnh sau bảo vệ thành công! Hồ sơ đã sẵn sàng để Giáo vụ lưu trữ.');
    }
}
