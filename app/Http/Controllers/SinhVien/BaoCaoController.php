<?php

namespace App\Http\Controllers\SinhVien;

use App\Http\Controllers\Controller;
use App\Models\BaoCaoTienDo;
use App\Models\MocThoiGianKhoaLuan;
use App\Models\KeHoachKhoaLuan;
use App\Models\Nhom;
use App\Models\SinhVien;
use App\Models\ThanhVienNhom;
use App\Models\KhoiBaoCaoTienDo;
use App\Models\TomTatBaoCao;
use App\Helpers\IdGenerator;
use App\Jobs\GenerateAiSummaryJob;
use App\Models\GiaiDoanThucHien;
use App\Models\DotNopBaoCao;
use App\Services\ThesisProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BaoCaoController extends Controller
{
    protected ThesisProgressService $progressService;

    public function __construct(ThesisProgressService $progressService)
    {
        $this->progressService = $progressService;
    }
    // Danh sách 6 giai đoạn tiến độ khóa luận
    const MOCS = [
        1 => ['ten' => 'GĐ 1: Nhận đề cương & Thiết kế CSDL', 'loai' => 'pdf', 'mo_ta' => 'Nộp file PDF đề cương chi tiết đề tài và sơ đồ thiết kế cơ sở dữ liệu.'],
        2 => ['ten' => 'GĐ 2: Thiết kế hệ thống & Xây dựng chức năng', 'loai' => 'pdf_git', 'mo_ta' => 'Nộp tài liệu thiết kế kiến trúc hệ thống, giao diện và link GitHub/GitLab các chức năng đã xây dựng.'],
        3 => ['ten' => 'GĐ 3: Kiểm thử hoàn thiện & Báo cáo dự thảo', 'loai' => 'pdf_git', 'mo_ta' => 'Nộp kết quả kiểm thử (Test report), dự thảo toàn văn khóa luận và hoàn thiện source code.'],
        4 => ['ten' => 'GĐ 4: Hoàn thiện hồ sơ bảo vệ', 'loai' => 'pdf', 'mo_ta' => 'Nộp kết quả kiểm tra đạo văn Turnitin và hồ sơ xin bảo vệ đã có ý kiến của GVHD.'],
        5 => ['ten' => 'GĐ 5: Tiến hành bảo vệ trước Hội đồng', 'loai' => 'pdf', 'mo_ta' => 'Nộp slide thuyết trình và tóm tắt đề tài phục vụ phiên bảo vệ trước Hội đồng chấm.'],
        6 => ['ten' => 'GĐ 6: Nhận kết quả & Nộp lại kết quả hoàn chỉnh', 'loai' => 'pdf_git', 'mo_ta' => 'Nộp bản khóa luận hoàn chỉnh đã chỉnh sửa theo kết luận và góp ý của Hội đồng chấm.'],
    ];

    public function index(Request $request)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->first();

        if (!$sinhVien) {
            return redirect()->route('sinhvien.nhom.index')
                ->with('error', 'Bạn chưa có hồ sơ sinh viên. Vui lòng liên hệ Giáo vụ.');
        }

        // Lấy tất cả nhóm sinh viên đang tham gia
        $allStudentMemberships = ThanhVienNhom::with([
            'nhom.hocPhan',
            'nhom.hocKy',
            'nhom.dangKyDeTai.deTai.giangVien',
            'nhom.thanhViens.sinhVien',
        ])
        ->where('MaSV', $sinhVien->MaSV)
        ->where('TrangThai', 'da_tham_gia')
        ->get();

        if ($allStudentMemberships->isEmpty()) {
            return view('sinhvien.baocao.index', [
                'error'                 => 'Bạn chưa có nhóm. Vui lòng tạo hoặc gia nhập nhóm trước.',
                'sinhVien'              => $sinhVien,
                'nhom'                  => null,
                'mocs'                  => self::MOCS,
                'baoCaos'               => collect(),
                'mocHienTai'            => 1,
                'mocDeadlines'          => [],
                'allStudentMemberships' => collect(),
                'stageBreakdown'        => [],
            ]);
        }

        $selectedNhomId = $request->get('nhom_id');
        $activeMembership = null;
        if ($selectedNhomId) {
            $activeMembership = $allStudentMemberships->firstWhere('MaNhom', $selectedNhomId);
        }
        if (!$activeMembership) {
            $activeMembership = $allStudentMemberships->first();
        }

        $nhom = Nhom::with(['deTai.giangVien.boMon', 'hocPhan', 'hocKy', 'truongNhom'])->find($activeMembership->MaNhom);

        if (!$nhom || !$nhom->deTai) {
            return view('sinhvien.baocao.index', [
                'error'                 => 'Nhóm ' . ($nhom?->TenNhom ?? '') . ' chưa đăng ký đề tài hoặc đề tài chưa được duyệt.',
                'sinhVien'              => $sinhVien,
                'nhom'                  => $nhom,
                'mocs'                  => self::MOCS,
                'baoCaos'               => collect(),
                'mocHienTai'            => 1,
                'mocDeadlines'          => [],
                'allStudentMemberships' => $allStudentMemberships,
                'stageBreakdown'        => [],
                'isLeader'              => false,
            ]);
        }

        $isLeader = ($nhom->truongNhom?->MaSV === $sinhVien->MaSV) || (mb_stripos($activeMembership->VaiTro, 'trưởng') !== false);

        $stageBreakdownData = ThesisProgressService::getStageProgressBreakdown($nhom);
        $stageBreakdown = $stageBreakdownData['stages'] ?? [];
        $nhom->stage_breakdown = $stageBreakdown;
        $nhom->progress_percent = $stageBreakdownData['overall_percent'] ?? 0;

        $baoCaos = BaoCaoTienDo::with(['tomTatBaoCao', 'khoiBaoCaos'])
            ->where('MaDeTai', $nhom->deTai->MaDeTai)
            ->orderBy('LanBaoCao')
            ->get()
            ->keyBy('LanBaoCao');

        $mocHienTai = 1;
        for ($i = 1; $i <= 6; $i++) {
            if (!isset($baoCaos[$i])) {
                $mocHienTai = $i;
                break;
            }
            if ($baoCaos[$i]->TrangThai !== 'Đạt') {
                $mocHienTai = $i;
                break;
            }
            $mocHienTai = $i + 1;
        }
        if ($mocHienTai > 6) $mocHienTai = 6;

        // Lấy deadline các mốc từ KeHoach để hiển thị cảnh báo
        $maKH = $nhom->deTai?->hocKy?->keHoachKhoaLuan?->MakeHoach;
        $timeCheckHienTai = $this->checkThoiGianNopBaoCao($mocHienTai, $maKH);
        $mocDeadlines = $this->getMocDeadlines();

        $giaiDoans = $nhom->deTai->giaiDoanThucHiens()->with('dotNops.khoiBaoCaos')->get();

        return view('sinhvien.baocao.index', compact(
            'sinhVien', 'nhom', 'baoCaos', 'mocHienTai', 'mocDeadlines', 'timeCheckHienTai', 'giaiDoans',
            'allStudentMemberships', 'stageBreakdown', 'isLeader'
        ) + ['mocs' => self::MOCS, 'error' => null]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->firstOrFail();

        $thanhVienQuery = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->where('TrangThai', 'da_tham_gia');
        $thanhVienRecord = null;
        if ($request->filled('MaNhom') || $request->filled('nhom_id')) {
            $reqNhomId = $request->input('MaNhom') ?: $request->input('nhom_id');
            $thanhVienRecord = (clone $thanhVienQuery)->where('MaNhom', $reqNhomId)->first();
        }
        if (!$thanhVienRecord) {
            $thanhVienRecord = $thanhVienQuery->firstOrFail();
        }
        $nhom = Nhom::with(['deTai.hocKy.keHoachKhoaLuan'])->findOrFail($thanhVienRecord->MaNhom);
        $deTai = $nhom->deTai;

        if (!$deTai) {
            return back()->with('error', 'Nhóm của bạn chưa có đề tài được duyệt.');
        }

        $isLeader = ($nhom->truongNhom?->MaSV === $sinhVien->MaSV) || (mb_stripos($thanhVienRecord->VaiTro, 'trưởng') !== false);
        if (!$isLeader) {
            return back()->with('error', 'Chỉ Nhóm trưởng mới có quyền đại diện nhóm nộp báo cáo tiến độ.');
        }

        $lan = (int) $request->input('LanBaoCao');
        $mocInfo = self::MOCS[$lan] ?? null;

        if (!$mocInfo) {
            return back()->with('error', 'Giai đoạn báo cáo không hợp lệ.');
        }

        // C1: Kiểm tra mốc thời gian nộp báo cáo tiến độ theo DB (chặn server-side)
        $maKH = $deTai->hocKy?->keHoachKhoaLuan?->MakeHoach;
        $timeCheck = $this->checkThoiGianNopBaoCao($lan, $maKH);
        if (!$timeCheck['is_open']) {
            return back()->withErrors(['error' => $timeCheck['message']])->with('error', $timeCheck['message'])->withInput();
        }

        // Validate theo loại mốc
        $rules = ['LanBaoCao' => 'required|integer|min:1|max:6'];
        $messages = [];

        $existing = BaoCaoTienDo::where('MaDeTai', $deTai->MaDeTai)
            ->where('LanBaoCao', $lan)
            ->first();

        $isResubmit = ($existing && $existing->TrangThai === 'Yêu cầu nộp lại');

        if (in_array($mocInfo['loai'], ['pdf', 'pdf_git'])) {
            // Nếu đã có file trước đó và đang chỉnh sửa thì không bắt buộc upload lại file mới
            $fileRule = ($existing && $existing->DuongDanFile) ? 'nullable|file|mimes:pdf,docx|max:20480' : 'required|file|mimes:pdf,docx|max:20480';
            $rules['FileBaoCao'] = $fileRule;
            $messages['FileBaoCao.required'] = 'Vui lòng đính kèm file PDF.';
            $messages['FileBaoCao.mimes'] = 'Chỉ chấp nhận file PDF.';
            $messages['FileBaoCao.max'] = 'File PDF tối đa 20MB.';
        }
        if (in_array($mocInfo['loai'], ['git', 'pdf_git'])) {
            $rules['LinkCode'] = 'nullable|url';
            $messages['LinkCode.url'] = 'Link Code phải là URL hợp lệ (bắt đầu https://).';
        }

        $rules['NoiDungBaoCao'] = 'nullable|string';
        $request->validate($rules, $messages);

        $cleanedNoiDungBaoCao = '';
        if ($request->filled('NoiDungBaoCao')) {
            $cleanedNoiDungBaoCao = \App\Helpers\HtmlHelper::clean($request->NoiDungBaoCao);
            $plainNoiDung = trim(strip_tags(html_entity_decode($cleanedNoiDungBaoCao, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if (mb_strlen($plainNoiDung) < 20) {
                return back()->withInput()->withErrors(['NoiDungBaoCao' => 'Nội dung tóm tắt tiến độ báo cáo phải có tối thiểu 20 ký tự.']);
            }
        }

        // Kiểm tra thứ tự mốc: Phải hoàn thành mốc trước mới được nộp mốc sau
        if ($lan > 1) {
            $mocTruoc = BaoCaoTienDo::where('MaDeTai', $deTai->MaDeTai)
                ->where('LanBaoCao', $lan - 1)
                ->where('TrangThai', 'Đạt')
                ->first();
            if (!$mocTruoc) {
                return back()->with('error', "Bạn cần hoàn thành Giai đoạn " . ($lan - 1) . " và được Giảng viên đánh giá \"Đạt\" trước khi nộp Giai đoạn {$lan}.");
            }
        }

        if ($existing && $existing->TrangThai === 'Đạt') {
            return back()->with('error', "Giai đoạn {$lan} đã được Giảng viên đánh giá Đạt, không cần nộp lại.");
        }

        $maBaoCao = null;
        $lanNop = 1;

        DB::transaction(function () use ($request, $cleanedNoiDungBaoCao, $nhom, $deTai, $lan, $mocInfo, $existing, $timeCheck, $isResubmit, &$maBaoCao, &$lanNop) {
            $tenFile = $existing?->TenFile;
            $duongDanFile = $existing?->DuongDanFile;
            $linkCode = $request->input('LinkCode');
            if ($request->hasFile('FileBaoCao')) {
                $file = $request->file('FileBaoCao');
                $tenFile = $file->getClientOriginalName();
                $duongDanFile = $file->store("baocao/{$nhom->MaNhom}/moc{$lan}", 'public');
            }

            $tieuDe = $request->input('TieuDe') ?: $mocInfo['ten'];
            $noiDung = $cleanedNoiDungBaoCao . ($linkCode ? "\nLink Code: " . $linkCode : '');

            if ($existing) {
                $maBaoCao = $existing->MaBaoCao;
                // Xóa tóm tắt AI cũ nếu có
                $existing->tomTatBaoCao()?->delete();

                if ($isResubmit) {
                    // C2: GIẢNG VIÊN YÊU CẦU CHỈNH SỬA -> SINH VIÊN NỘP LẠI TẠO KHỐI MỚI (IMMUTABLE BLOCK)
                    $maxLanNop = KhoiBaoCaoTienDo::where('MaBaoCao', $existing->MaBaoCao)->max('LanNop') ?? 1;
                    $lanNop = $maxLanNop + 1;
                    $maKhoi = 'KHOI_' . $existing->MaBaoCao . '_' . $lanNop;

                    KhoiBaoCaoTienDo::create([
                        'MaKhoi'        => $maKhoi,
                        'MaBaoCao'      => $existing->MaBaoCao,
                        'MaDeTai'       => $deTai->MaDeTai,
                        'LanBaoCao'     => $lan,
                        'LanNop'        => $lanNop,
                        'TieuDe'        => $tieuDe,
                        'NoiDungBaoCao' => $noiDung,
                        'TenFile'       => $tenFile,
                        'DuongDanFile'  => $duongDanFile,
                        'LinkCode'      => $linkCode,
                        'TrangThai'     => 'Chờ duyệt',
                        'NhanXetGV'     => null,
                        'NguoiNhanXet'  => null,
                        'NgayNop'       => now(),
                    ]);

                    $existing->update([
                        'TieuDe'        => $tieuDe,
                        'NoiDungBaoCao' => $noiDung,
                        'NgayNop'       => now()->toDateString(),
                        'TenFile'       => $tenFile,
                        'DuongDanFile'  => $duongDanFile,
                        'TrangThai'     => 'Chờ duyệt',
                        'NhanXet'       => null,
                        'MaMoc'         => $timeCheck['ma_moc'] ?? $existing->MaMoc,
                    ]);
                } else {
                    // Đang ở trạng thái Chờ duyệt: Cập nhật khối hiện tại
                    $existing->update([
                        'TieuDe'        => $tieuDe,
                        'NoiDungBaoCao' => $noiDung,
                        'NgayNop'       => now()->toDateString(),
                        'TenFile'       => $tenFile,
                        'DuongDanFile'  => $duongDanFile,
                        'TrangThai'     => 'Chờ duyệt',
                        'NhanXet'       => null,
                        'MaMoc'         => $timeCheck['ma_moc'] ?? $existing->MaMoc,
                    ]);

                    $khoiHienTai = KhoiBaoCaoTienDo::where('MaBaoCao', $existing->MaBaoCao)->latest('LanNop')->first();
                    if ($khoiHienTai) {
                        $lanNop = $khoiHienTai->LanNop;
                        $khoiHienTai->update([
                            'TieuDe'        => $tieuDe,
                            'NoiDungBaoCao' => $noiDung,
                            'TenFile'       => $tenFile,
                            'DuongDanFile'  => $duongDanFile,
                            'LinkCode'      => $linkCode,
                            'NgayNop'       => now(),
                        ]);
                    } else {
                        KhoiBaoCaoTienDo::create([
                            'MaKhoi'        => 'KHOI_' . $existing->MaBaoCao . '_1',
                            'MaBaoCao'      => $existing->MaBaoCao,
                            'MaDeTai'       => $deTai->MaDeTai,
                            'LanBaoCao'     => $lan,
                            'LanNop'        => 1,
                            'TieuDe'        => $tieuDe,
                            'NoiDungBaoCao' => $noiDung,
                            'TenFile'       => $tenFile,
                            'DuongDanFile'  => $duongDanFile,
                            'LinkCode'      => $linkCode,
                            'TrangThai'     => 'Chờ duyệt',
                            'NgayNop'       => now(),
                        ]);
                    }
                }
            } else {
                // Lần đầu nộp bài cho giai đoạn này: Tạo BaoCaoTienDo và Khối 1
                $maBaoCao = IdGenerator::nextBaoCao();
                BaoCaoTienDo::create([
                    'MaBaoCao'      => $maBaoCao,
                    'MaDeTai'       => $deTai->MaDeTai,
                    'LanBaoCao'     => $lan,
                    'TieuDe'        => $tieuDe,
                    'NoiDungBaoCao' => $noiDung,
                    'NgayNop'       => now()->toDateString(),
                    'TenFile'       => $tenFile,
                    'DuongDanFile'  => $duongDanFile,
                    'TrangThai'     => 'Chờ duyệt',
                    'MaMoc'         => $timeCheck['ma_moc'] ?? null,
                ]);

                KhoiBaoCaoTienDo::create([
                    'MaKhoi'        => 'KHOI_' . $maBaoCao . '_1',
                    'MaBaoCao'      => $maBaoCao,
                    'MaDeTai'       => $deTai->MaDeTai,
                    'LanBaoCao'     => $lan,
                    'LanNop'        => 1,
                    'TieuDe'        => $tieuDe,
                    'NoiDungBaoCao' => $noiDung,
                    'TenFile'       => $tenFile,
                    'DuongDanFile'  => $duongDanFile,
                    'LinkCode'      => $linkCode,
                    'TrangThai'     => 'Chờ duyệt',
                    'NgayNop'       => now(),
                ]);
            }
        });

        // BƯỚC 5: Dispatch Queue Job bất đồng bộ
        $aiVersion = KhoiBaoCaoTienDo::where('MaBaoCao',$maBaoCao)->orderByDesc('LanNop')->first();
        if ($aiVersion && $aiVersion->DuongDanFile) app(\App\Services\AiReportProcessingService::class)->enqueue($aiVersion);

        $successMsg = $isResubmit
            ? "Nộp lại báo cáo tiến độ GĐ {$lan} thành công (Lần nộp {$lanNop})! Hệ thống đã tạo khối nộp bài mới và gửi đến Giảng viên hướng dẫn."
            : "Nộp và lưu thông tin tiến độ GĐ {$lan} thành công! Hệ thống đã kích hoạt trợ lý AI phân tích và tóm tắt nội dung báo cáo.";

        return redirect()->route('sinhvien.baocao.index')->with('success', $successMsg);
    }

    /**
     * C1: Kiểm tra mốc thời gian nộp báo cáo tiến độ theo DB.
     * Quy tắc C1:
     * - Chỉ được nộp báo cáo tiến độ đợt 1 từ ngày 15/9 trở đi (gồm 15/9).
     * - Lưu mốc ngày trong DB (có ngày bắt đầu/kết thúc, NULL = không giới hạn).
     * - Chặn server-side nếu chưa đến ngày bắt đầu hoặc đã quá ngày kết thúc.
     */
    public function checkThoiGianNopBaoCao(int $lan, ?string $maKeHoach = null, $nowDate = null): array
    {
        $now = $nowDate ? Carbon::parse($nowDate) : Carbon::now();
        $today = $now->format('Y-m-d');

        // Tìm mốc trong DB
        $mocQuery = MocThoiGianKhoaLuan::query();
        if ($maKeHoach) {
            $mocQuery->where('MakeHoach', $maKeHoach);
        } else {
            $activePlan = \App\Services\PlanPhaseService::getActivePlan();
            if ($activePlan) {
                $mocQuery->where('MakeHoach', $activePlan->MakeHoach);
            }
        }

        $moc = $mocQuery->where(function($q) use ($lan) {
            $q->where('MoTa', 'LIKE', "%[BAO_CAO_TIEN_DO_{$lan}]%")
              ->orWhere('TenMoc', 'LIKE', "%báo cáo tiến độ đợt {$lan}%")
              ->orWhere('TenMoc', 'LIKE', "%tiến độ đợt {$lan}%")
              ->orWhere('TenMoc', 'LIKE', "%GĐ {$lan}%")
              ->orWhere('TenMoc', 'LIKE', "%Giai đoạn {$lan}%");
        })->first();

        // Mặc định cho đợt 1 nếu chưa cấu hình mốc trong DB: từ 15/09/2026 trở đi (gồm 15/9), kết thúc NULL (không giới hạn)
        $startDate = $moc ? $moc->NgayBatDau : ($lan === 1 ? '2026-09-15' : null);
        $endDate   = $moc ? $moc->NgayKetThuc : null;
        $maMoc     = $moc?->MaMoc;

        // 1. Kiểm tra ngày bắt đầu (Nếu có ngày bắt đầu, trước ngày này sẽ chặn; gồm ngày bắt đầu)
        if (!empty($startDate)) {
            $startFormatted = Carbon::parse($startDate)->format('Y-m-d');
            if ($today < $startFormatted) {
                $displayDate = Carbon::parse($startDate)->format('d/m/Y');
                return [
                    'is_open'    => false,
                    'code'       => 'CHUA_DEN_HAN_NOP',
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                    'ma_moc'     => $maMoc,
                    'message'    => "Chỉ được nộp báo cáo tiến độ đợt {$lan} từ ngày {$displayDate} trở đi (gồm cả ngày {$displayDate})! Hiện tại hệ thống chưa mở nhận bài.",
                ];
            }
        }

        // 2. Kiểm tra ngày kết thúc (NULL = không giới hạn)
        if (!empty($endDate)) {
            $endFormatted = Carbon::parse($endDate)->format('Y-m-d');
            if ($today > $endFormatted) {
                $displayDate = Carbon::parse($endDate)->format('d/m/Y');
                return [
                    'is_open'    => false,
                    'code'       => 'QUA_HAN_NOP',
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                    'ma_moc'     => $maMoc,
                    'message'    => "Đã quá hạn nộp báo cáo tiến độ đợt {$lan}! Hạn cuối nộp bài là 23:59 ngày {$displayDate}.",
                ];
            }
        }

        // 3. Trong khung thời gian hợp lệ (hoặc không giới hạn)
        return [
            'is_open'    => true,
            'code'       => 'DANG_MO',
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'ma_moc'     => $maMoc,
            'message'    => "Đang mở nộp báo cáo tiến độ đợt {$lan}." . (!empty($startDate) ? " (Mở từ ngày " . Carbon::parse($startDate)->format('d/m/Y') . ")" : ""),
        ];
    }

    /**
     * BƯỚC 4: Kiểm tra deadline nộp bài theo MocThoiGian.
     * Trả về null nếu còn hạn, trả về chuỗi lỗi nếu quá hạn.
     */
    private function checkDeadline(int $lan): ?string
    {
        $timeCheck = $this->checkThoiGianNopBaoCao($lan);
        return $timeCheck['is_open'] ? null : $timeCheck['message'];
    }

    /**
     * Lấy map deadline các mốc để hiển thị trên UI.
     */
    private function getMocDeadlines(): array
    {
        $keHoach = KeHoachKhoaLuan::where('TrangThai', 'Đang thực hiện')->first()
            ?? KeHoachKhoaLuan::whereIn('TrangThai', ['ĐÃ CÔNG BỐ', 'Đang thực hiện', 'Đã công bố'])->latest()->first();
        if (!$keHoach) return [];

        $mocs = MocThoiGianKhoaLuan::where('MakeHoach', $keHoach->MakeHoach)->get();
        $map = [];
        foreach ($mocs as $moc) {
            foreach (range(1, 6) as $lan) {
                if (str_contains($moc->TenMoc, "Mốc {$lan}") || str_contains($moc->TenMoc, "GĐ {$lan}") || str_contains($moc->TenMoc, "Giai đoạn {$lan}") || str_contains($moc->TenMoc, "đợt {$lan}") || str_contains($moc->MoTa ?? '', "[BAO_CAO_TIEN_DO_{$lan}]")) {
                    $map[$lan] = $moc->NgayKetThuc;
                }
            }
        }
        return $map;
    }

    public function createKhoi($lanBaoCao)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->firstOrFail();

        $thanhVienQuery = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->where('TrangThai', 'da_tham_gia');
        $thanhVien = null;
        if (request()->filled('nhom_id') || request()->filled('MaNhom')) {
            $reqNhom = request()->input('nhom_id') ?: request()->input('MaNhom');
            $thanhVien = (clone $thanhVienQuery)->where('MaNhom', $reqNhom)->first();
        }
        if (!$thanhVien) {
            $thanhVien = $thanhVienQuery->firstOrFail();
        }
        $nhom = Nhom::with(['deTai.giangVien'])->findOrFail($thanhVien->MaNhom);
        $deTai = $nhom->deTai;

        if (!$deTai) {
            return redirect()->route('sinhvien.baocao.index')->with('error', 'Nhóm chưa có đề tài được phê duyệt.');
        }

        $lan = (int)$lanBaoCao;
        if (!isset(self::MOCS[$lan])) {
            return redirect()->route('sinhvien.baocao.index')->with('error', 'Giai đoạn không hợp lệ.');
        }

        $mocInfo = self::MOCS[$lan];
        $baoCao = BaoCaoTienDo::where('MaDeTai', $deTai->MaDeTai)->where('LanBaoCao', $lan)->first();

        $soKhoi = $baoCao ? KhoiBaoCaoTienDo::where('MaBaoCao', $baoCao->MaBaoCao)->count() : 0;
        $lanNop = $soKhoi + 1;

        return view('sinhvien.baocao.create_khoi', compact('sinhVien', 'nhom', 'deTai', 'lan', 'mocInfo', 'baoCao', 'lanNop'));
    }

    public function storeKhoi(Request $request)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->firstOrFail();

        $thanhVienQuery = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->where('TrangThai', 'da_tham_gia');
        $thanhVien = null;
        if ($request->filled('nhom_id') || $request->filled('MaNhom')) {
            $reqNhom = $request->input('nhom_id') ?: $request->input('MaNhom');
            $thanhVien = (clone $thanhVienQuery)->where('MaNhom', $reqNhom)->first();
        }
        if (!$thanhVien) {
            $thanhVien = $thanhVienQuery->firstOrFail();
        }
        $nhom = Nhom::with(['deTai.giangVien', 'truongNhom'])->findOrFail($thanhVien->MaNhom);
        $deTai = $nhom->deTai;

        if (!$deTai) {
            return redirect()->route('sinhvien.baocao.index')->with('error', 'Nhóm chưa có đề tài.');
        }

        $isLeader = ($nhom->truongNhom?->MaSV === $sinhVien->MaSV) || (mb_stripos($thanhVien->VaiTro, 'trưởng') !== false);
        if (!$isLeader) {
            return redirect()->back()->with('error', 'Chỉ Nhóm trưởng mới có quyền thêm nội dung báo cáo tiến độ.');
        }

        $lan = (int)$request->input('LanBaoCao');
        $hasFile = $request->hasFile('FileBaoCao') || $request->filled('ExistingFileUrl');

        $request->validate([
            'LanBaoCao' => 'required|integer|between:1,6',
            'TieuDe' => 'required|string|max:255',
            'KetQuaDatDuoc' => $hasFile ? 'nullable|string' : 'required|string|min:10',
            'KhoKhan' => 'nullable|string',
            'HuongGiaiQuyet' => 'nullable|string',
            'KeHoachTiepTheo' => 'nullable|string',
            'FileBaoCao' => 'nullable|file|mimes:pdf,docx|max:25600',
            'LinkCode' => 'nullable|url',
        ], [
            'TieuDe.required' => 'Vui lòng nhập tiêu đề báo cáo.',
            'KetQuaDatDuoc.required' => 'Vui lòng đính kèm file báo cáo PDF hoặc nhập kết quả công việc.',
            'FileBaoCao.mimes' => 'Chỉ chấp nhận file đính kèm định dạng PDF hoặc DOCX.',
            'FileBaoCao.max' => 'Dung lượng file đính kèm tối đa là 25MB.',
        ]);

        $cleanedKetQua = $request->filled('KetQuaDatDuoc') ? \App\Helpers\HtmlHelper::clean($request->KetQuaDatDuoc) : null;
        $cleanedKhoKhan = $request->filled('KhoKhan') ? \App\Helpers\HtmlHelper::clean($request->KhoKhan) : null;
        $cleanedHuongGiaiQuyet = $request->filled('HuongGiaiQuyet') ? \App\Helpers\HtmlHelper::clean($request->HuongGiaiQuyet) : null;
        $cleanedKeHoach = $request->filled('KeHoachTiepTheo') ? \App\Helpers\HtmlHelper::clean($request->KeHoachTiepTheo) : null;
        // Tỷ lệ hoàn thành được AI tự động đánh giá khách quan dựa trên tệp báo cáo, không lấy từ SV
        $phanTram = null;

        $baoCao = BaoCaoTienDo::firstOrCreate(
            ['MaDeTai' => $deTai->MaDeTai, 'LanBaoCao' => $lan],
            [
                'MaBaoCao' => IdGenerator::nextBaoCao(),
                'TieuDe' => self::MOCS[$lan]['ten'],
                'TrangThai' => 'Chờ duyệt',
                'NgayNop' => now()->toDateString(),
                'PhanTramHoanThanh' => null,
            ]
        );

        $soKhoi = KhoiBaoCaoTienDo::where('MaBaoCao', $baoCao->MaBaoCao)->count();
        $lanNop = $soKhoi + 1;
        $maKhoi = 'KHOI_' . $baoCao->MaBaoCao . '_' . $lanNop . '_' . time();

        $tenFile = null;
        $duongDanFile = null;
        if ($request->hasFile('FileBaoCao')) {
            $file = $request->file('FileBaoCao');
            $tenFile = $file->getClientOriginalName();
            $duongDanFile = $file->store("baocao/{$nhom->MaNhom}/moc{$lan}", 'public');
        } elseif ($request->filled('ExistingFileUrl')) {
            $duongDanFile = $request->input('ExistingFileUrl');
            $tenFile = $request->input('ExistingFileName') ?: basename($duongDanFile);
        }

        // AI results are produced by the server worker, never trusted from hidden form fields.
        $tomTatJson = null;
        $tomTatAi = null;

        $khoi = KhoiBaoCaoTienDo::create([
            'MaKhoi' => $maKhoi,
            'MaBaoCao' => $baoCao->MaBaoCao,
            'MaDeTai' => $deTai->MaDeTai,
            'LanBaoCao' => $lan,
            'LanNop' => $lanNop,
            'TieuDe' => $request->input('TieuDe'),
            'NoiDungBaoCao' => $cleanedKetQua ?: ($tomTatAi ?: 'Báo cáo tiến độ phân tích bởi AI'),
            'KetQuaDatDuoc' => $cleanedKetQua,
            'KhoKhan' => $cleanedKhoKhan,
            'HuongGiaiQuyet' => $cleanedHuongGiaiQuyet,
            'KeHoachTiepTheo' => $cleanedKeHoach,
            'PhanTramHoanThanh' => $phanTram,
            'TenFile' => $tenFile,
            'DuongDanFile' => $duongDanFile,
            'LinkCode' => $request->input('LinkCode'),
            'TomTatAI' => $tomTatAi,
            'TomTatJson' => $tomTatJson,
            'TrangThai' => 'Chờ duyệt',
            'NgayNop' => now(),
        ]);

        $baoCao->update([
            'TrangThai' => 'Chờ duyệt',
            'PhanTramHoanThanh' => max((int)$baoCao->PhanTramHoanThanh, $phanTram),
            'NgayNop' => now()->toDateString(),
            'TenFile' => $tenFile ?: $baoCao->TenFile,
            'DuongDanFile' => $duongDanFile ?: $baoCao->DuongDanFile,
        ]);

        if ($khoi->DuongDanFile) app(\App\Services\AiReportProcessingService::class)->enqueue($khoi);

        return redirect()->route('sinhvien.baocao.index')->with('success', "Đã tạo và nộp báo cáo nhỏ (Báo cáo đợt {$lanNop}) cho Giai đoạn {$lan} thành công!");
    }

    public function showKhoi($id)
    {
        $khoi = KhoiBaoCaoTienDo::with(['baoCaoTienDo.deTai.giangVien', 'tomTat'])
            ->where('MaKhoi', $id)->orWhere('id', $id)->firstOrFail();
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->orWhere('MaSV', $user->TenDangNhap)->firstOrFail();
        $thanhVien = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->first();
        $nhom = $thanhVien ? Nhom::find($thanhVien->MaNhom) : null;

        if ($nhom && $khoi->baoCaoTienDo && $khoi->baoCaoTienDo->deTai && $khoi->baoCaoTienDo->deTai->MaDeTai !== $nhom->MaDeTai) {
            abort(403, 'Bạn không có quyền xem báo cáo này.');
        }

        $mocInfo = self::MOCS[$khoi->LanBaoCao] ?? null;

        return view('sinhvien.baocao.show_khoi', compact('khoi', 'nhom', 'mocInfo'));
    }

    public function editKhoi($id)
    {
        $khoi = KhoiBaoCaoTienDo::with(['baoCaoTienDo.deTai'])
            ->where('MaKhoi', $id)->orWhere('id', $id)->firstOrFail();
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->orWhere('MaSV', $user->TenDangNhap)->firstOrFail();
        $thanhVien = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->firstOrFail();
        $nhom = Nhom::findOrFail($thanhVien->MaNhom);

        if ($khoi->baoCaoTienDo && $khoi->baoCaoTienDo->deTai && $khoi->baoCaoTienDo->deTai->MaDeTai !== $nhom->MaDeTai) {
            abort(403, 'Bạn không có quyền chỉnh sửa báo cáo này.');
        }

        if ($khoi->TrangThai === 'Đạt') {
            return redirect()->route('sinhvien.baocao.show_khoi', $id)->with('error', 'Báo cáo đã được GVHD đánh giá Đạt, không thể chỉnh sửa.');
        }

        $lan = $khoi->LanBaoCao;
        $mocInfo = self::MOCS[$lan] ?? null;

        return view('sinhvien.baocao.edit_khoi', compact('khoi', 'nhom', 'lan', 'mocInfo'));
    }

    public function updateKhoi(Request $request, $id)
    {
        $khoi = KhoiBaoCaoTienDo::with('baoCaoTienDo')
            ->where('MaKhoi', $id)->orWhere('id', $id)->firstOrFail();
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->orWhere('MaSV', $user->TenDangNhap)->firstOrFail();
        $thanhVien = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->firstOrFail();
        $nhom = Nhom::findOrFail($thanhVien->MaNhom);

        if ($khoi->baoCaoTienDo && $khoi->baoCaoTienDo->deTai && $khoi->baoCaoTienDo->deTai->MaDeTai !== $nhom->MaDeTai) {
            abort(403, 'Bạn không có quyền cập nhật báo cáo này.');
        }

        $request->validate([
            'TieuDe' => 'required|string|max:255',
            'KetQuaDatDuoc' => 'required|string',
            'KhoKhan' => 'nullable|string',
            'HuongGiaiQuyet' => 'nullable|string',
            'KeHoachTiepTheo' => 'nullable|string',
            'FileBaoCao' => 'nullable|file|mimes:pdf,docx|max:25600',
            'LinkCode' => 'nullable|url',
        ], [
            'TieuDe.required' => 'Vui lòng nhập tiêu đề báo cáo.',
            'KetQuaDatDuoc.required' => 'Vui lòng nhập kết quả đã thực hiện / đạt được.',
            'FileBaoCao.mimes' => 'Chỉ chấp nhận file đính kèm định dạng PDF hoặc DOCX.',
            'FileBaoCao.max' => 'Dung lượng file đính kèm tối đa là 25MB.',
        ]);

        $cleanedKetQua = \App\Helpers\HtmlHelper::clean($request->KetQuaDatDuoc);
        $plainKetQua = trim(strip_tags(html_entity_decode($cleanedKetQua, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        if (mb_strlen($plainKetQua) < 10) {
            return redirect()->back()->withInput()->withErrors(['KetQuaDatDuoc' => 'Nội dung kết quả tối thiểu 10 ký tự.']);
        }

        $cleanedKhoKhan = $request->filled('KhoKhan') ? \App\Helpers\HtmlHelper::clean($request->KhoKhan) : null;
        $cleanedHuongGiaiQuyet = $request->filled('HuongGiaiQuyet') ? \App\Helpers\HtmlHelper::clean($request->HuongGiaiQuyet) : null;
        $cleanedKeHoach = $request->filled('KeHoachTiepTheo') ? \App\Helpers\HtmlHelper::clean($request->KeHoachTiepTheo) : null;

        $tenFile = $khoi->TenFile;
        $duongDanFile = $khoi->DuongDanFile;
        if ($request->hasFile('FileBaoCao')) {
            $previousData = app(\App\Services\AiReportProcessingService::class)->payload($khoi);
            if (!empty($previousData['schema_version']) && empty($previousData['_source_hash']) && $duongDanFile) {
                try {
                    $previousData['_source_hash'] = hash_file('sha256', app(\App\Services\AiReportProcessingService::class)->path($khoi));
                    $khoi->TomTatJson = $previousData;
                } catch (\Throwable) { /* Missing old source remains explicitly unavailable. */ }
            }
            $file = $request->file('FileBaoCao');
            $tenFile = $file->getClientOriginalName();
            $duongDanFile = $file->store("baocao/{$nhom->MaNhom}/moc{$khoi->LanBaoCao}", 'public');
        }

        $khoi->update([
            'TieuDe' => $request->input('TieuDe'),
            'NoiDungBaoCao' => $cleanedKetQua,
            'KetQuaDatDuoc' => $cleanedKetQua,
            'KhoKhan' => $cleanedKhoKhan,
            'HuongGiaiQuyet' => $cleanedHuongGiaiQuyet,
            'KeHoachTiepTheo' => $cleanedKeHoach,
            'PhanTramHoanThanh' => $khoi->PhanTramHoanThanh, // Giữ tỷ lệ do AI thẩm định, không ghi đè từ request
            'TenFile' => $tenFile,
            'DuongDanFile' => $duongDanFile,
            'LinkCode' => $request->input('LinkCode', $khoi->LinkCode),
            'TrangThai' => 'Chờ duyệt',
            'NgayNop' => now(),
        ]);

        $khoi->baoCaoTienDo->update([
            'TrangThai' => 'Chờ duyệt',
            'PhanTramHoanThanh' => max((int)$khoi->baoCaoTienDo->PhanTramHoanThanh, (int)$khoi->PhanTramHoanThanh),
            'NgayNop' => now()->toDateString(),
        ]);

        if ($request->hasFile('FileBaoCao')) {
            app(\App\Services\AiReportProcessingService::class)->enqueue($khoi);
        }

        return redirect()->route('sinhvien.baocao.show_khoi', $id)->with('success', 'Đã cập nhật và nộp lại báo cáo thành công!');
    }

    public function destroyKhoi($id)
    {
        $khoi = KhoiBaoCaoTienDo::with('baoCaoTienDo')->where('MaKhoi', $id)->orWhere('id', $id)->firstOrFail();
        if ($khoi->TrangThai !== 'Chờ duyệt') {
            return back()->with('error', 'Chỉ có thể xóa báo cáo nhỏ đang ở trạng thái Chờ duyệt.');
        }

        $khoi->delete();
        return redirect()->route('sinhvien.baocao.index')->with('success', 'Đã xóa báo cáo nhỏ thành công.');
    }

    public function ajaxAiSummary(Request $request)
    {
        $request->validate([
            'FilePdf' => 'required|file|mimes:pdf,docx|max:25600',
            'LanBaoCao' => 'nullable|integer|between:1,6',
        ]);

        try {
            $file = $request->file('FilePdf');
            $stage = (int)$request->input('LanBaoCao', 1);

            $tempPath = $file->store('temp_ai', 'public');
            $fullPath = Storage::disk('public')->path($tempPath);

            $aiService = app(\App\Services\AiSummaryService::class);
            $result = $aiService->summarizePdf($fullPath, $stage);

            $result['temp_file_url'] = $tempPath;
            $result['temp_file_name'] = $file->getClientOriginalName();

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Lỗi xử lý tóm tắt AI: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sinh viên nộp báo cáo phiên bản mới cho một đợt nộp (Version N + 1)
     */
    public function nopPhienBan(Request $request, $id)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->firstOrFail();
        $thanhVien = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->where('TrangThai', 'da_tham_gia')->firstOrFail();
        $nhom = Nhom::with(['deTai', 'truongNhom'])->findOrFail($thanhVien->MaNhom);

        $dotNop = DotNopBaoCao::with(['giaiDoan', 'deTai'])->findOrFail($id);

        if (!$nhom->deTai || $nhom->deTai->MaDeTai !== $dotNop->MaDeTai) {
            abort(403, 'Đợt nộp báo cáo này không thuộc đề tài của nhóm bạn.');
        }

        // CHỈ CHO PHÉP NHÓM TRƯỞNG NỘP BÁO CÁO TIẾN ĐỘ
        $isLeader = ($nhom->truongNhom?->MaSV === $sinhVien->MaSV) || (mb_stripos($thanhVien->VaiTro, 'trưởng') !== false);
        if (!$isLeader) {
            return redirect()->back()->with('error', 'Chỉ Nhóm trưởng mới có quyền đại diện nhóm nộp báo cáo tiến độ.');
        }

        // Kiểm tra quyền hạn nộp: Phải đang mở hoặc được yêu cầu nộp lại
        $latest = app(\App\Services\ReportMilestoneProgressService::class)->versionsForRound($dotNop, $nhom)->first();
        $canSubmit = $dotNop->isMoNop() || ($latest && ($latest->TrangThai === 'Yêu cầu nộp lại' || $latest->TrangThai === 'Cần bổ sung'));

        if (!$canSubmit) {
            $now = now();
            if ($dotNop->NgayMoNop && $now->lt($dotNop->NgayMoNop)) {
                $errorMsg = 'Đợt nộp này chưa mở. Thời gian mở nộp: ' . \Carbon\Carbon::parse($dotNop->NgayMoNop)->format('H:i · d/m/Y') . '.';
            } elseif ($dotNop->HanNop && $now->gt($dotNop->HanNop)) {
                $errorMsg = 'Đợt nộp này đã quá hạn nộp từ lúc ' . \Carbon\Carbon::parse($dotNop->HanNop)->format('H:i · d/m/Y') . '. Vui lòng liên hệ GVHD.';
            } else {
                $errorMsg = 'Đợt nộp này hiện đang đóng hoặc đã được xác nhận hoàn thành.';
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $rules = [
            'FilePdf' => $latest ? 'nullable|file|mimes:pdf,docx|max:25600' : 'required|file|mimes:pdf,docx|max:25600',
            'NoiDungBaoCao' => 'nullable|string',
            'KetQuaDatDuoc' => 'nullable|string',
            'KhoKhan' => 'nullable|string',
            'CongViecChuaHoanThanh' => 'nullable|string',
            'KeHoachTiepTheo' => 'nullable|string',
            'PhanTramHoanThanh' => 'nullable|integer|between:0,100',
        ];

        $rules['LinkCode'] = $dotNop->quyTacLinkMaNguon();

        $messages = [
            'FilePdf.required' => 'Vui lòng đính kèm file báo cáo định dạng PDF hoặc DOCX để AI tự động phân tích.',
            'FilePdf.mimes' => 'File báo cáo phải có định dạng PDF hoặc DOCX.',
            'FilePdf.max' => 'Dung lượng file tối đa là 25MB.',
            'LinkCode.required' => 'Đợt nộp này yêu cầu cung cấp đường dẫn mã nguồn dự án (GitHub / GitLab / Bitbucket).',
            'LinkCode.regex' => 'Đường dẫn mã nguồn phải thuộc GitHub, GitLab hoặc Bitbucket hợp lệ.',
        ];

        $submissionData = $request->validate($rules, $messages);

        try {
            $khoi = $this->progressService->nopBaoCaoPhienBanMoi(
                $dotNop,
                $sinhVien,
                $submissionData,
                $request->file('FilePdf')
            );

            return redirect()->route('sinhvien.baocao.dot_nop.show', $dotNop->MaDotNop)
                ->with('success', "Đã nộp thành công Báo cáo Phiên bản v{$khoi->LanNop}! Giảng viên hướng dẫn sẽ nhận xét và đánh giá sớm nhất.");
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Lỗi khi nộp báo cáo: ' . $e->getMessage());
        }
    }

    /**
     * Sinh viên xem chi tiết đợt nộp & lịch sử các phiên bản
     */
    public function showDotNop($id)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where('MaTK', $user->MaTK)->firstOrFail();
        $thanhVien = ThanhVienNhom::where('MaSV', $sinhVien->MaSV)->where('TrangThai', 'da_tham_gia')->firstOrFail();
        $nhom = Nhom::with(['deTai.giangVien', 'truongNhom'])->findOrFail($thanhVien->MaNhom);

        $dotNop = DotNopBaoCao::with([
            'giaiDoan',
            'deTai.giangVien',
            'khoiBaoCaos' => fn($q) => $q->orderBy('LanNop', 'desc'),
        ])->findOrFail($id);

        if (!$nhom->deTai || $nhom->deTai->MaDeTai !== $dotNop->MaDeTai) {
            abort(403, 'Đợt nộp báo cáo này không thuộc đề tài của nhóm bạn.');
        }

        $phienBans = $nhom ? app(\App\Services\ReportMilestoneProgressService::class)->versionsForRound($dotNop, $nhom) : $dotNop->khoiBaoCaos;
        $latestPhienBan = $phienBans->first();
        $isLeader = ($nhom->truongNhom?->MaSV === $sinhVien->MaSV) || (mb_stripos($thanhVien->VaiTro, 'trưởng') !== false);

        $reportMilestoneProgress = $nhom ? app(\App\Services\ReportMilestoneProgressService::class)->forGroup($nhom, $dotNop->deTai) : null;
        return view('sinhvien.baocao.show_dot', compact(
            'sinhVien', 'nhom', 'dotNop', 'phienBans', 'latestPhienBan', 'isLeader', 'reportMilestoneProgress'
        ));
    }
}
