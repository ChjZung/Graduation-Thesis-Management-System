<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\BaoCaoTienDo;
use App\Models\KhoiBaoCaoTienDo;
use App\Models\GiangVien;
use App\Models\Nhom;
use App\Models\PhieuDangKy;
use App\Models\DeTai;
use App\Models\GiaiDoanThucHien;
use App\Models\DotNopBaoCao;
use App\Models\TienDoNghiepVuKhoaLuan;
use App\Models\HoSoBaoVe;
use App\Models\LichSuChuyenGiaiDoan;
use App\Services\ThesisProgressService;
use App\Services\AiSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DuyetBaoCaoController extends Controller
{
    protected ThesisProgressService $progressService;

    public function __construct(ThesisProgressService $progressService)
    {
        $this->progressService = $progressService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        $nhoms = Nhom::with([
                'deTai.giangVien',
                'truongNhom',
                'thanhViens.sinhVien',
                'baoCaos.tomTatBaoCao',
                'baoCaos.khoiBaoCaos',
                'hoSoBaoVe',
            ])
            ->where(function ($query) use ($giangVien) {
                $query->whereHas('phieuDangKys', function ($q) use ($giangVien) {
                    $q->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])
                      ->whereHas('deTai', fn($d) => $d->where('MaGV', $giangVien->MaGV));
                })->orWhereHas('dangKyDeTai', function ($q) use ($giangVien) {
                    $q->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])
                      ->whereHas('deTai', fn($d) => $d->where('MaGV', $giangVien->MaGV));
                })->orWhereHas('deTai', function ($d) use ($giangVien) {
                    $d->where('MaGV', $giangVien->MaGV);
                });
            })
            ->get();

        foreach ($nhoms as $nhom) {
            \App\Http\Controllers\Admin\TheoDoiDoAnController::processNhomStageData($nhom);

            $milestones = app(\App\Services\ReportMilestoneProgressService::class)->forGroup($nhom);
            $nhom->report_milestone_progress = $milestones;
            if ($milestones['stage_count'] > 0) {
                $nhom->progress_percent = $milestones['percentage'];
                $rounds = collect($milestones['stages'])->flatMap(fn ($stage) => $stage['rounds']);
                $hasOverdue = $rounds->contains(fn ($round) => !empty($round['overdue']));
                $hasPending = $rounds->contains(fn ($round) => in_array($round['status'], ['Chờ duyệt', 'Chờ GVHD duyệt', 'Đã nộp']));
                $nhom->status_code = $hasOverdue ? 'QUA_HAN' : ($hasPending ? 'CHO_XU_LY' :
                    ($milestones['percentage'] >= 99.999999 ? 'HOAN_THANH' : 'DANG_THUC_HIEN'));
            }
        }

        // Thống kê 4 thẻ KPI theo chuẩn
        $tongSoNhom = $nhoms->count();
        $baocaoChoDuyet = 0;
        $baocaoDaDat = 0;
        $nhomCanNhacNho = 0;

        foreach ($nhoms as $nhom) {
            $milestones = $nhom->report_milestone_progress;
            $needsReminder = in_array($nhom->status_code, ['QUA_HAN', 'SAP_DEN_HAN']);
            foreach ($milestones['stages'] as $stage) {
                foreach ($stage['rounds'] as $round) {
                    if (in_array($round['status'], ['Chờ duyệt', 'Chờ GVHD duyệt', 'Đã nộp'])) $baocaoChoDuyet++;
                    elseif ($round['completed']) $baocaoDaDat++;
                    elseif ($round['status'] === 'Yêu cầu nộp lại') $needsReminder = true;
                }
            }
            if ($needsReminder) {
                $nhomCanNhacNho++;
            }
        }

        // Xác định nhóm đang được chọn (ưu tiên query param ?nhom=..., đảm bảo nhóm thuộc quyền của GV)
        $selectedNhomQuery = $request->query('nhom');
        $activeNhom = $nhoms->firstWhere('MaNhom', $selectedNhomQuery);
        if (!$activeNhom) {
            $activeNhom = $nhoms->first();
        }
        $activeNhomId = $activeNhom?->MaNhom;
        $activeGd = $request->query('gd');

        return view('giangvien.baocao.index', compact(
            'giangVien', 'nhoms', 'tongSoNhom', 'baocaoChoDuyet', 'baocaoDaDat', 'nhomCanNhacNho', 'activeNhomId', 'activeGd'
        ));
    }

    public function show($id)
    {
        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        $baoCao = BaoCaoTienDo::with([
            'deTai.giangVien',
            'tomTatBaoCao',
            'khoiBaoCaos',
        ])->findOrFail($id);

        if (!$baoCao->deTai || $baoCao->deTai->MaGV !== $giangVien->MaGV) {
            abort(403, 'Bạn không phải Giảng viên hướng dẫn của đề tài này.');
        }

        $phieuDangKy = PhieuDangKy::with([
            'nhom.thanhViens.sinhVien',
        ])->where('MaDeTai', $baoCao->MaDeTai)
          ->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])
          ->first();

        $nhom = $phieuDangKy?->nhom;
        $deTai = $baoCao->deTai;
        $truongNhom = $nhom?->thanhViens->firstWhere('VaiTro', 'Nhóm trưởng')?->sinhVien 
            ?? $nhom?->thanhViens->firstWhere('VaiTro', 'Trưởng nhóm')?->sinhVien
            ?? $nhom?->thanhViens->first()?->sinhVien;

        $thanhViens = $nhom?->thanhViens ?? collect();

        // Tự động sinh tóm tắt AI cho giảng viên xem trực tiếp ngay khi mở trang
        $latestKhoi = $baoCao->khoiMoiNhat ?? $baoCao->khoiBaoCaos->last();
        if ($latestKhoi) {
            $this->ensureAiSummaryForKhoi($latestKhoi, $baoCao);
            $baoCao->refresh();
        } elseif (!$baoCao->tomTatBaoCao) {
            $this->ensureAiSummaryForBaoCao($baoCao);
            $baoCao->refresh();
        }
        $tomTat = $baoCao->tomTatBaoCao;

        // Lấy tất cả 6 mốc của đề tài này để hiển thị trên lộ trình 6 giai đoạn
        $allBaoCaos = BaoCaoTienDo::where('MaDeTai', $baoCao->MaDeTai)
            ->with('khoiBaoCaos')
            ->orderBy('LanBaoCao')
            ->get()
            ->keyBy('LanBaoCao');

        // Kiểm tra xem Mốc tiếp theo đã nộp chưa để khóa kết quả
        $lanTiepTheo = $baoCao->LanBaoCao + 1;
        $daNopMocSau = BaoCaoTienDo::where('MaDeTai', $baoCao->MaDeTai)
            ->where('LanBaoCao', $lanTiepTheo)
            ->exists();

        return view('giangvien.baocao.show', compact(
            'giangVien',
            'baoCao',
            'nhom',
            'deTai',
            'truongNhom',
            'thanhViens',
            'tomTat',
            'allBaoCaos',
            'daNopMocSau'
        ));
    }

    public function storeNhanXet(Request $request, $maBaoCao)
    {
        $request->validate([
            'NoiDung'     => 'required|string',
            'LoaiNhanXet' => 'required|in:Đạt,Yêu cầu nộp lại',
        ], [
            'NoiDung.required'     => 'Vui lòng nhập nhận xét.',
            'LoaiNhanXet.required' => 'Vui lòng chọn kết quả đánh giá.',
            'LoaiNhanXet.in'       => 'Kết quả phải là "Đạt" hoặc "Yêu cầu nộp lại".',
        ]);

        $cleanedNoiDung = \App\Helpers\HtmlHelper::clean($request->NoiDung);
        $plainNoiDung = trim(strip_tags(html_entity_decode($cleanedNoiDung, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        if (mb_strlen($plainNoiDung) < 5) {
            return back()->withInput()->withErrors(['NoiDung' => 'Vui lòng nhập nhận xét chi tiết hơn (tối thiểu 5 ký tự).']);
        }

        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }
        $baoCao = BaoCaoTienDo::with('deTai')->findOrFail($maBaoCao);

        // Policy: Chỉ GV hướng dẫn của đề tài mới được đánh giá
        if (!$baoCao->deTai || $baoCao->deTai->MaGV !== $giangVien->MaGV) {
            abort(403, 'Bạn không phải Giảng viên hướng dẫn của nhóm này và không có quyền nhận xét báo cáo.');
        }

        // Nếu nhóm sinh viên đã nộp Mốc tiếp theo thì khóa, không cho đổi về 'Yêu cầu nộp lại'
        $lanTiepTheo = $baoCao->LanBaoCao + 1;
        $daNopMocSau = BaoCaoTienDo::where('MaDeTai', $baoCao->MaDeTai)
            ->where('LanBaoCao', $lanTiepTheo)
            ->exists();

        if ($daNopMocSau && $request->LoaiNhanXet === 'Yêu cầu nộp lại') {
            return back()->with('error', "Không thể chuyển trạng thái về \"Yêu cầu nộp lại\" do nhóm sinh viên đã nộp bài Mốc {$lanTiepTheo}.");
        }

        $isFirstReview = ($baoCao->TrangThai === 'Chờ duyệt');

        // Cập nhật khối báo cáo mới nhất của đợt nộp này
        $khoiMoiNhat = KhoiBaoCaoTienDo::where('MaBaoCao', $baoCao->MaBaoCao)->latest('LanNop')->first();
        $nguoiNhanXet = ($giangVien->HocHam ? $giangVien->HocHam . '.' : '') . ($giangVien->HocVi ? $giangVien->HocVi . '. ' : '') . $giangVien->HoTen;

        if ($khoiMoiNhat) {
            $khoiMoiNhat->update([
                'TrangThai'    => $request->LoaiNhanXet,
                'NhanXetGV'    => $cleanedNoiDung,
                'NguoiNhanXet' => $nguoiNhanXet,
                'NgayNhanXet'  => now(),
            ]);
        } else {
            KhoiBaoCaoTienDo::create([
                'MaKhoi'       => 'KHOI_' . $baoCao->MaBaoCao . '_1',
                'MaBaoCao'     => $baoCao->MaBaoCao,
                'MaDeTai'      => $baoCao->MaDeTai,
                'LanBaoCao'    => $baoCao->LanBaoCao,
                'LanNop'       => 1,
                'TieuDe'       => $baoCao->TieuDe,
                'NoiDungBaoCao'=> $baoCao->NoiDungBaoCao,
                'TenFile'      => $baoCao->TenFile,
                'DuongDanFile' => $baoCao->DuongDanFile,
                'LinkCode'     => $baoCao->LinkCode,
                'TrangThai'    => $request->LoaiNhanXet,
                'NhanXetGV'    => $cleanedNoiDung,
                'NguoiNhanXet' => $nguoiNhanXet,
                'NgayNop'      => $baoCao->NgayNop ?? now(),
                'NgayNhanXet'  => now(),
            ]);
        }

        $updateData = [
            'TrangThai' => $request->LoaiNhanXet,
            'NhanXet'   => $cleanedNoiDung,
        ];
        if ($request->LoaiNhanXet === 'Đạt') {
            $updateData['PhanTramHoanThanh'] = 100;
            $updateData['NgayXacNhan'] = now();
            $updateData['NguoiXacNhan'] = $nguoiNhanXet;
        }
        $baoCao->update($updateData);

        if ($isFirstReview) {
            $msg = $request->LoaiNhanXet === 'Đạt'
                ? "Đã đánh giá \"Đạt\" cho Mốc {$baoCao->LanBaoCao}. Sinh viên đã được mở khóa nộp Mốc tiếp theo!"
                : "Đã gửi đánh giá \"Yêu cầu nộp lại\" cho Mốc {$baoCao->LanBaoCao}.";
        } else {
            $msg = "Đã cập nhật lại nhận xét và đánh giá cho Mốc {$baoCao->LanBaoCao}.";
        }

        return redirect()->route('giangvien.baocao.show', $baoCao->MaBaoCao)->with('success', $msg);
    }

    public function showKhoi($id)
    {
        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên của tài khoản này.');
        }

        $khoi = KhoiBaoCaoTienDo::with([
            'baoCaoTienDo.deTai.giangVien',
            'tomTat'
        ])->where('MaKhoi', $id)->orWhere('id', $id)->firstOrFail();

        $baoCao = $khoi->baoCaoTienDo;
        if (!$baoCao || !$baoCao->deTai || $baoCao->deTai->MaGV !== $giangVien->MaGV) {
            abort(403, 'Bạn không phải Giảng viên hướng dẫn của đề tài này.');
        }

        $phieuDangKy = PhieuDangKy::with(['nhom.thanhViens.sinhVien'])
            ->where('MaDeTai', $baoCao->MaDeTai)
            ->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])
            ->first();

        $nhom = $phieuDangKy?->nhom;
        $deTai = $baoCao->deTai;

        // Tự động sinh tóm tắt AI cho giảng viên xem trực tiếp, không bắt buộc sinh viên phải bấm phân tích trước
        $this->ensureAiSummaryForKhoi($khoi, $baoCao);
        $khoi->refresh();
        $tomTat = $khoi->tomTat ?? $baoCao->tomTatBaoCao;

        return view('giangvien.baocao.show_khoi', compact('giangVien', 'khoi', 'baoCao', 'nhom', 'deTai', 'tomTat'));
    }

    /**
     * Tự động sinh tóm tắt AI cho báo cáo nhỏ khi GVHD mở xem
     */
    private function ensureAiSummaryForKhoi(KhoiBaoCaoTienDo $khoi, BaoCaoTienDo $baoCao): void
    {
        $processing = app(\App\Services\AiReportProcessingService::class);
        $data = $processing->payload($khoi);
        if (!empty($data['schema_version']) && $processing->sourceMatches($khoi, $data)) return;
        if (!empty($data['_processing'])) return; // Explicit retry preserves the existing cooldown.
        if (!$khoi->DuongDanFile) return;
        try { $processing->enqueue($khoi); $khoi->refresh(); }
        catch (\Throwable) { \Illuminate\Support\Facades\Log::warning('Unable to enqueue report summary.'); }
    }

    public function storeNhanXetKhoi(Request $request, $id)
    {
        $request->validate([
            'NoiDung' => 'required|string',
            'LoaiNhanXet' => 'required|in:Đạt,Yêu cầu nộp lại',
        ], [
            'NoiDung.required' => 'Vui lòng nhập nội dung nhận xét.',
            'LoaiNhanXet.required' => 'Vui lòng chọn kết quả đánh giá.',
        ]);

        $cleanedNoiDung = \App\Helpers\HtmlHelper::clean($request->NoiDung);
        $plainNoiDung = trim(strip_tags(html_entity_decode($cleanedNoiDung, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        if (mb_strlen($plainNoiDung) < 5) {
            return back()->withInput()->withErrors(['NoiDung' => 'Vui lòng nhập nhận xét chi tiết hơn (tối thiểu 5 ký tự).']);
        }

        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên.');
        }

        $khoi = KhoiBaoCaoTienDo::with('baoCaoTienDo.deTai')->where('MaKhoi', $id)->orWhere('id', $id)->firstOrFail();
        $baoCao = $khoi->baoCaoTienDo;

        if (!$baoCao || !$baoCao->deTai || $baoCao->deTai->MaGV !== $giangVien->MaGV) {
            abort(403, 'Bạn không phải Giảng viên hướng dẫn của đề tài này.');
        }

        $nguoiNhanXet = ($giangVien->HocHam ? $giangVien->HocHam . '.' : '') . ($giangVien->HocVi ? $giangVien->HocVi . '. ' : '') . $giangVien->HoTen;

        $khoi->update([
            'TrangThai' => $request->LoaiNhanXet,
            'NhanXetGV' => $cleanedNoiDung,
            'NguoiNhanXet' => $nguoiNhanXet,
            'NgayNhanXet' => now(),
        ]);

        return redirect()->back()->with('success', "Đã lưu nhận xét cho báo cáo nhỏ thành công (Kết quả: {$request->LoaiNhanXet})!");
    }

    /**
     * Tự động sinh tóm tắt AI cho báo cáo lớn giai đoạn khi GVHD mở xem
     */
    private function ensureAiSummaryForBaoCao(BaoCaoTienDo $baoCao): void
    {
        // Legacy snapshots remain readable. API calls must run on a version in the queue.
        $latest = $baoCao->khoiBaoCaos()->orderByDesc('LanNop')->first();
        if ($latest) $this->ensureAiSummaryForKhoi($latest, $baoCao);
    }

    public function xacNhanGiaiDoan(Request $request, $maBaoCao)
    {
        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên.');
        }

        $baoCao = BaoCaoTienDo::with('deTai')->findOrFail($maBaoCao);
        if (!$baoCao->deTai || $baoCao->deTai->MaGV !== $giangVien->MaGV) {
            abort(403, 'Bạn không phải Giảng viên hướng dẫn của đề tài này.');
        }

        $nguoiXacNhan = ($giangVien->HocHam ? $giangVien->HocHam . '.' : '') . ($giangVien->HocVi ? $giangVien->HocVi . '. ' : '') . $giangVien->HoTen;
        $phanTram = (int)$request->input('PhanTramHoanThanh', 100);
        $nhanXet = $request->input('NhanXetTongQuat', 'Giảng viên hướng dẫn xác nhận sinh viên hoàn thành đạt yêu cầu giai đoạn này.');
        $cleanedNhanXet = \App\Helpers\HtmlHelper::clean($nhanXet);

        $baoCao->update([
            'TrangThai' => 'Đạt',
            'PhanTramHoanThanh' => $phanTram,
            'NhanXet' => $cleanedNhanXet,
            'NgayXacNhan' => now(),
            'NguoiXacNhan' => $nguoiXacNhan,
        ]);

        return redirect()->route('giangvien.baocao.show', $maBaoCao)->with('success', "Đã xác nhận hoàn thành Giai đoạn {$baoCao->LanBaoCao}! Nhóm sinh viên đã đủ điều kiện chuyển sang giai đoạn tiếp theo.");
    }

    // =========================================================================
    // QUẢN LÝ CÁC GIAI ĐOẠN & ĐỢT NỘP BÁO CÁO DO GIẢNG VIÊN THIẾT LẬP
    // =========================================================================

    /**
     * Xác định mã nhóm cần redirect về
     */
    protected function resolveNhomId(Request $request, ?DeTai $deTai = null): ?string
    {
        if ($request->filled('nhom')) {
            return $request->input('nhom');
        }
        if ($deTai) {
            return Nhom::where('MaDeTai', $deTai->MaDeTai)->first()?->MaNhom
                ?? PhieuDangKy::where('MaDeTai', $deTai->MaDeTai)->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])->first()?->MaNhom
                ?? $deTai->nhoms()->first()?->MaNhom;
        }
        return null;
    }

    /**
     * Helper redirect về trang tiến độ và giữ lại nhóm và giai đoạn đang chọn
     */
    protected function redirectBackToBaocao(Request $request, ?DeTai $deTai = null, ?int $thuTuGd = null)
    {
        $nhom = $this->resolveNhomId($request, $deTai);
        $gd = $request->input('gd') ?: $thuTuGd;
        $params = [];
        if ($nhom) {
            $params['nhom'] = $nhom;
        }
        if ($gd) {
            $params['gd'] = $gd;
        }
        return redirect()->route('giangvien.baocao.index', $params);
    }

    /**
     * Tạo giai đoạn thực hiện mới cho đề tài
     */
    public function storeGiaiDoan(Request $request)
    {
        $request->validate([
            'MaDeTai' => 'required|string|exists:detai,MaDeTai',
            'TenGiaiDoan' => 'required|string|max:255',
            'MoTa' => 'nullable|string',
            'NoiDungCongViec' => 'nullable|string',
            'KetQuaDauRa' => 'nullable|string',
            'NgayBatDau' => 'nullable|date',
            'NgayKetThuc' => 'nullable|date|after_or_equal:NgayBatDau',
            'YeuCauCanDat' => 'nullable|string',
        ]);

        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        $deTai = DeTai::where('MaDeTai', $request->MaDeTai)->where('MaGV', $giangVien->MaGV)->firstOrFail();

        $this->progressService->taoGiaiDoan($deTai, $request->all());
        return $this->redirectBackToBaocao($request, $deTai)->with('success', "Đã thêm giai đoạn '{$request->TenGiaiDoan}' thành công!");
    }

    /**
     * Cập nhật giai đoạn
     */
    public function updateGiaiDoan(Request $request, $id)
    {
        $giangVien = GiangVien::getLoggedInGiangVien(Auth::user());
        $giaiDoan = GiaiDoanThucHien::with('deTai')->findOrFail($id);
        abort_unless($giangVien && $giaiDoan->deTai && $giaiDoan->deTai->MaGV === $giangVien->MaGV,
            403, 'Bạn không phải GVHD của đề tài này.');

        $data = $request->validateWithBag('updateStage', [
            'TenGiaiDoan' => 'required|string|max:255',
            'MoTa' => 'nullable|string',
            'NoiDungCongViec' => 'nullable|string',
            'KetQuaDauRa' => 'nullable|string',
            'NgayBatDau' => 'nullable|date',
            'NgayKetThuc' => $request->filled('NgayBatDau')
                ? 'nullable|date|after_or_equal:NgayBatDau' : 'nullable|date',
            'YeuCauCanDat' => 'nullable|string',
            'TrangThai' => 'required|in:CHUA_BAT_DAU,DANG_THUC_HIEN,HOAN_THANH',
        ], [
            'NgayKetThuc.after_or_equal' => 'Ngày kết thúc phải bằng hoặc sau ngày bắt đầu.',
            'TenGiaiDoan.required' => 'Vui lòng nhập tên giai đoạn.',
        ]);

        $this->progressService->capNhatGiaiDoan($giaiDoan, $data);
        return $this->redirectBackToBaocao($request, $giaiDoan->deTai, $giaiDoan->ThuTu)->with('success', "Đã cập nhật giai đoạn '{$giaiDoan->TenGiaiDoan}' thành công!");
    }

    /**
     * Xóa giai đoạn
     */
    public function destroyGiaiDoan($id)
    {
        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        $giaiDoan = GiaiDoanThucHien::with('deTai')->findOrFail($id);

        abort_unless($giangVien && $giaiDoan->deTai && $giaiDoan->deTai->MaGV === $giangVien->MaGV,
            403, 'Bạn không phải GVHD của đề tài này.');

        $deTai = $giaiDoan->deTai;
        try {
            $this->progressService->xoaGiaiDoan($giaiDoan);
            return $this->redirectBackToBaocao(request(), $deTai)->with('success', "Đã xóa giai đoạn thành công!");
        } catch (\Throwable $e) {
            return $this->redirectBackToBaocao(request(), $deTai)->with('error', $e->getMessage());
        }
    }

    /**
     * Tạo đợt nộp báo cáo mới trong giai đoạn
     */
    public function storeDotNop(Request $request)
    {
        $request->validate([
            'MaGiaiDoan' => 'required|integer|exists:giaidoanthuchien,MaGiaiDoan',
            'TenDotNop' => 'required|string|max:255',
            'MoTa' => 'nullable|string',
            'NoiDungYeuCau' => 'nullable|string',
            'NgayMoNop' => 'nullable|date',
            'HanNop' => 'nullable|date',
            'DinhDangChoPhep' => 'nullable|string',
            'YeuCauSourceCode' => 'nullable|boolean',
            'SoLanNopToiDa' => 'nullable|integer|min:1',
        ]);

        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        $giaiDoan = GiaiDoanThucHien::with('deTai')->findOrFail($request->MaGiaiDoan);

        if ($giaiDoan->deTai->MaGV !== $giangVien->MaGV) {
            abort(403, 'Bạn không phải GVHD của đề tài này.');
        }

        $dotNop = $this->progressService->taoDotNop($giaiDoan, array_merge($request->all(), [
            'YeuCauSourceCode' => $request->boolean('YeuCauSourceCode'),
        ]));
        return $this->redirectBackToBaocao($request, $giaiDoan->deTai, $giaiDoan->ThuTu)->with('success', "Đã tạo đợt nộp '{$dotNop->TenDotNop}' thành công!");
    }

    /**
     * Cập nhật đợt nộp báo cáo
     */
    public function updateDotNop(Request $request, $id)
    {
        $giangVien = GiangVien::getLoggedInGiangVien(Auth::user());
        $dotNop = DotNopBaoCao::with(['deTai', 'giaiDoan'])->findOrFail($id);
        abort_unless($giangVien && $dotNop->deTai && $dotNop->deTai->MaGV === $giangVien->MaGV,
            403, 'Bạn không phải GVHD của đề tài này.');

        $data = $request->validateWithBag('updateRound', [
            'TenDotNop' => 'required|string|max:255',
            'MoTa' => 'nullable|string',
            'NoiDungYeuCau' => 'nullable|string',
            'NgayMoNop' => 'nullable|date',
            'HanNop' => $request->filled('NgayMoNop') ? 'nullable|date|after_or_equal:NgayMoNop' : 'nullable|date',
            'DinhDangChoPhep' => 'nullable|string',
            'YeuCauSourceCode' => 'nullable|boolean',
            'SoLanNopToiDa' => 'nullable|integer|min:1',
            'TrangThai' => 'required|in:DANG_MO,TAM_DONG,DA_DONG,HOAN_THANH',
        ], [
            'TenDotNop.required' => 'Vui lòng nhập tên đợt nộp.',
            'HanNop.after_or_equal' => 'Hạn nộp phải bằng hoặc sau ngày mở nộp.',
        ]);
        $data['YeuCauSourceCode'] = $request->boolean('YeuCauSourceCode');
        $dotNop->update($data);

        return $this->redirectBackToBaocao($request, $dotNop->deTai, $dotNop->giaiDoan?->ThuTu)->with('success', "Đã cập nhật đợt nộp '{$dotNop->TenDotNop}' thành công!");
    }

    public function destroyDotNop($id)
    {
        $giangVien = GiangVien::getLoggedInGiangVien(Auth::user());
        $dotNop = DotNopBaoCao::with(['deTai', 'giaiDoan'])->findOrFail($id);
        abort_unless($giangVien && $dotNop->deTai && $dotNop->deTai->MaGV === $giangVien->MaGV,
            403, 'Bạn không phải GVHD của đề tài này.');

        $deTai = $dotNop->deTai;
        $thuTu = $dotNop->giaiDoan?->ThuTu;
        try {
            $this->progressService->xoaDotNop($dotNop);
            return $this->redirectBackToBaocao(request(), $deTai, $thuTu)->with('success', 'Đã xóa đợt nộp báo cáo.');
        } catch (\DomainException $e) {
            return $this->redirectBackToBaocao(request(), $deTai, $thuTu)->with('error', $e->getMessage());
        }
    }

    public function capNhatYeuCauMaNguon(Request $request, $id)
    {
        $request->validate(['YeuCauSourceCode' => 'required|boolean']);

        $giangVien = GiangVien::getLoggedInGiangVien(Auth::user());
        $dotNop = DotNopBaoCao::with(['deTai', 'giaiDoan'])->findOrFail($id);
        if (!$giangVien || !$dotNop->deTai || $dotNop->deTai->MaGV !== $giangVien->MaGV) {
            abort(403, 'Bạn không phải GVHD của đề tài này.');
        }

        $dotNop->update(['YeuCauSourceCode' => $request->boolean('YeuCauSourceCode')]);

        return $this->redirectBackToBaocao($request, $dotNop->deTai, $dotNop->giaiDoan?->ThuTu)->with('success', $dotNop->YeuCauSourceCode
            ? 'Đã bật yêu cầu nộp link GitHub / GitLab / Bitbucket cho đợt này.'
            : 'Đã tắt yêu cầu nộp link mã nguồn cho đợt này.');
    }

    /**
     * Chuyển trạng thái nhanh đợt nộp (Đóng / Mở)
     */
    public function toggleDotNop(Request $request, $id)
    {
        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        $dotNop = DotNopBaoCao::with(['deTai', 'giaiDoan'])->findOrFail($id);

        if ($dotNop->deTai->MaGV !== $giangVien->MaGV) {
            abort(403, 'Bạn không phải GVHD của đề tài này.');
        }

        $newStatus = ($dotNop->TrangThai === 'DANG_MO') ? 'DA_DONG' : 'DANG_MO';
        $dotNop->update(['TrangThai' => $newStatus]);

        $msg = ($newStatus === 'DANG_MO') ? 'Đã mở lại đợt nộp báo cáo.' : 'Đã đóng đợt nộp báo cáo.';
        return $this->redirectBackToBaocao($request, $dotNop->deTai, $dotNop->giaiDoan?->ThuTu)->with('success', $msg);
    }

    /**
     * Xem chi tiết đợt nộp báo cáo & danh sách các phiên bản (Versions)
     */
    public function showDotNop($id)
    {
        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên.');
        }

        $dotNop = DotNopBaoCao::with([
            'giaiDoan',
            'deTai.giangVien',
            'khoiBaoCaos' => fn($q) => $q->orderBy('LanNop', 'desc'),
        ])->findOrFail($id);

        if (!$dotNop->deTai || $dotNop->deTai->MaGV !== $giangVien->MaGV) {
            abort(403, 'Bạn không phải Giảng viên hướng dẫn của đề tài này.');
        }

        $phieuDangKy = PhieuDangKy::with(['nhom.thanhViens.sinhVien'])
            ->where('MaDeTai', $dotNop->MaDeTai)
            ->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])
            ->first();

        $nhom = $phieuDangKy?->nhom;
        $deTai = $dotNop->deTai;
        $phienBans = $nhom ? app(\App\Services\ReportMilestoneProgressService::class)->versionsForRound($dotNop, $nhom) : $dotNop->khoiBaoCaos;
        $latestPhienBan = $phienBans->first();

        $reportMilestoneProgress = $nhom ? app(\App\Services\ReportMilestoneProgressService::class)->forGroup($nhom, $dotNop->deTai) : null;
        return view('giangvien.baocao.show_dot', compact(
            'giangVien', 'dotNop', 'nhom', 'deTai', 'phienBans', 'latestPhienBan', 'reportMilestoneProgress'
        ));
    }

    /**
     * Đánh giá một phiên bản của đợt nộp báo cáo (Đạt hoặc Yêu cầu nộp lại)
     */
    public function danhGiaPhienBan(Request $request, $id)
    {
        $request->validate([
            'MaKhoi' => 'required|string|exists:khoibaocaotiendo,MaKhoi',
            'HanhDong' => 'required|in:DAT,YEU_CAU_NOP_LAI',
            'NhanXet' => 'required|string|min:5',
        ], [
            'NhanXet.required' => 'Vui lòng nhập nhận xét chuyên môn.',
            'NhanXet.min' => 'Nhận xét phải có tối thiểu 5 ký tự.',
        ]);

        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        $dotNop = DotNopBaoCao::with('deTai')->findOrFail($id);

        if ($dotNop->deTai->MaGV !== $giangVien->MaGV) {
            abort(403, 'Bạn không phải GVHD của đề tài này.');
        }

        $phienBan = KhoiBaoCaoTienDo::where('MaKhoi', $request->MaKhoi)->firstOrFail();

        $this->progressService->danhGiaPhienBanBaoCao(
            $dotNop,
            $phienBan,
            $request->HanhDong,
            $request->NhanXet,
            $giangVien
        );

        $msg = ($request->HanhDong === 'DAT')
            ? "Đã xác nhận hoàn thành đợt nộp '{$dotNop->TenDotNop}' (Phiên bản v{$phienBan->LanNop})!"
            : "Đã yêu cầu sinh viên bổ sung / nộp lại cho đợt '{$dotNop->TenDotNop}'. Sinh viên có thể nộp phiên bản tiếp theo.";

        return redirect()->back()->with('success', $msg);
    }

    /**
     * GVHD xác nhận nhóm hoàn thành toàn bộ giai đoạn thực hiện & cho phép nộp hồ sơ bảo vệ
     */
    public function choPhepNopHoSoBaoVe(Request $request, $maNhom)
    {
        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên.');
        }

        $nhom = Nhom::with(['deTai.giangVien', 'thanhViens.sinhVien'])->findOrFail($maNhom);
        $deTai = $nhom->deTai;

        if (!$deTai || $deTai->MaGV !== $giangVien->MaGV) {
            abort(403, 'Bạn không phải Giảng viên hướng dẫn của nhóm này.');
        }

        $nguoiXacNhan = ($giangVien->HocHam ? $giangVien->HocHam . '.' : '') . ($giangVien->HocVi ? $giangVien->HocVi . '. ' : '') . $giangVien->HoTen;

        DB::transaction(function () use ($nhom, $deTai, $giangVien, $nguoiXacNhan, $request, $user) {
            // 1. Cập nhật tiến độ nghiệp vụ sang Giai đoạn 6 (Hoàn thiện và bảo vệ)
            $tienDo = TienDoNghiepVuKhoaLuan::firstOrCreate(
                ['MaNhom' => $nhom->MaNhom],
                [
                    'MaDeTai' => $deTai->MaDeTai,
                    'MaHocKy' => $nhom->MaHocKy ?? $deTai->MaHocKy,
                ]
            );

            $gdTruoc = $tienDo->GiaiDoanHienTai;
            $tienDo->GiaiDoanHienTai = 6;
            $tienDo->TrangThaiGiaiDoan = 'DANG_THUC_HIEN';
            $tienDo->NgayHoanThanh = now();
            $tienDo->save();

            // 2. Ghi nhật ký chuyển giai đoạn
            LichSuChuyenGiaiDoan::create([
                'MaNhom' => $nhom->MaNhom,
                'GiaiDoanTruoc' => $gdTruoc,
                'GiaiDoanSau' => 6,
                'TrangThaiTruoc' => 'DANG_THUC_HIEN',
                'TrangThaiSau' => 'DANG_THUC_HIEN',
                'HanhDong' => 'GVHD xác nhận hoàn thành giai đoạn thực hiện & mở cổng nộp hồ sơ bảo vệ',
                'NguoiThucHien' => $nguoiXacNhan,
                'LyDo' => $request->input('LyDo', 'GVHD xác nhận nhóm đã hoàn thành khối lượng đề tài và đủ điều kiện nộp hồ sơ bảo vệ.'),
            ]);

            // 3. Đánh dấu tất cả các giai đoạn và đợt nộp kỹ thuật của đề tài sang HOAN_THANH
            GiaiDoanThucHien::where('MaDeTai', $deTai->MaDeTai)->update(['TrangThai' => 'HOAN_THANH']);
            DotNopBaoCao::where('MaDeTai', $deTai->MaDeTai)->update(['TrangThai' => 'HOAN_THANH']);

            // 4. Đồng bộ tạo bản ghi BaoCaoTienDo mốc 5 Đạt để tương thích ngược 100%
            BaoCaoTienDo::updateOrCreate(
                [
                    'MaDeTai' => $deTai->MaDeTai,
                    'LanBaoCao' => 5,
                ],
                [
                    'MaBaoCao' => 'BC_TD5_' . $deTai->MaDeTai,
                    'TieuDe' => 'Báo cáo hoàn thành giai đoạn thực hiện khóa luận',
                    'NoiDungBaoCao' => 'GVHD ' . $nguoiXacNhan . ' xác nhận nhóm hoàn thành đủ khối lượng thực hiện khóa luận.',
                    'TrangThai' => 'Đạt',
                    'PhanTramHoanThanh' => 100,
                    'NgayNop' => now(),
                    'NgayXacNhan' => now(),
                    'NguoiXacNhan' => $nguoiXacNhan,
                    'NhanXet' => 'GVHD đã xác nhận nhóm hoàn thành đủ điều kiện chuyển sang giai đoạn nộp hồ sơ bảo vệ.',
                ]
            );

            // 5. Khởi tạo hồ sơ bảo vệ (nếu chưa có) ở trạng thái 'Chưa nộp' để sinh viên thấy ngay
            HoSoBaoVe::firstOrCreate(
                ['MaNhom' => $nhom->MaNhom],
                [
                    'MaHoSo' => 'HSBV_' . $nhom->MaNhom,
                    'MaDeTai' => $deTai->MaDeTai,
                    'NgayLap' => now(),
                    'TrangThai' => 'Chưa nộp',
                ]
            );

            // 6. Gửi thông báo cho toàn bộ thành viên nhóm
            \App\Services\ThongBaoService::guiDenNhom(
                $nhom->MaNhom,
                'Cổng nộp hồ sơ bảo vệ đã mở',
                "Giảng viên hướng dẫn {$nguoiXacNhan} đã xác nhận nhóm {$nhom->TenNhom} hoàn thành giai đoạn thực hiện đề tài. Cổng nộp hồ sơ bảo vệ chính thức đã mở, các bạn hãy vào khu vực Hồ sơ bảo vệ để nộp báo cáo và các liên kết dự án.",
                'Nhóm'
            );
        });

        return redirect()->route('giangvien.baocao.index', ['nhom' => $nhom->MaNhom])->with('success', "Đã xác nhận hoàn thành giai đoạn thực hiện cho nhóm '{$nhom->TenNhom}'! Cổng nộp hồ sơ bảo vệ đã được mở cho sinh viên.");
    }

    /**
     * GVHD hủy hoặc thu hồi xác nhận mở cổng nộp hồ sơ bảo vệ nếu cần nhóm bổ sung thêm
     */
    public function thuHoiChoPhepBaoVe(Request $request, $maNhom)
    {
        $user = Auth::user();
        $giangVien = GiangVien::getLoggedInGiangVien($user);
        if (!$giangVien) {
            abort(404, 'Không tìm thấy hồ sơ giảng viên.');
        }

        $nhom = Nhom::with(['deTai.giangVien'])->findOrFail($maNhom);
        $deTai = $nhom->deTai;

        if (!$deTai || $deTai->MaGV !== $giangVien->MaGV) {
            abort(403, 'Bạn không phải Giảng viên hướng dẫn của nhóm này.');
        }

        // Không thu hồi nếu sinh viên đã nộp hồ sơ và đang chờ hoặc đã duyệt
        $hoSo = HoSoBaoVe::where('MaNhom', $nhom->MaNhom)->first();
        if ($hoSo && !in_array($hoSo->TrangThai, ['Chưa nộp', 'Yêu cầu chỉnh sửa'])) {
            return redirect()->route('giangvien.baocao.index', ['nhom' => $nhom->MaNhom])->with('error', "Không thể thu hồi do sinh viên đã nộp hồ sơ bảo vệ (trạng thái: {$hoSo->TrangThai}).");
        }

        if ($hoSo && $hoSo->TrangThai === 'Chưa nộp') {
            $hoSo->delete();
        }

        $tienDo = TienDoNghiepVuKhoaLuan::where('MaNhom', $nhom->MaNhom)->first();
        if ($tienDo) {
            $tienDo->update([
                'GiaiDoanHienTai' => 5,
                'TrangThaiGiaiDoan' => 'DANG_THUC_HIEN',
            ]);
        }

        BaoCaoTienDo::where('MaDeTai', $deTai->MaDeTai)->where('LanBaoCao', 5)->delete();

        // Đặt lại giai đoạn 6 sang CHUA_MO và giai đoạn 5 sang DANG_THUC_HIEN
        $gd6 = GiaiDoanThucHien::where('MaDeTai', $deTai->MaDeTai)->where('ThuTu', 6)->first();
        if ($gd6) {
            $gd6->update(['TrangThai' => 'CHUA_MO']);
        }
        $gd5 = GiaiDoanThucHien::where('MaDeTai', $deTai->MaDeTai)->where('ThuTu', 5)->first();
        if ($gd5) {
            $gd5->update(['TrangThai' => 'DANG_THUC_HIEN']);
        }

        $nguoiXacNhan = ($giangVien->HocHam ? $giangVien->HocHam . '.' : '') . ($giangVien->HocVi ? $giangVien->HocVi . '. ' : '') . $giangVien->HoTen;
        LichSuChuyenGiaiDoan::create([
            'MaNhom' => $nhom->MaNhom,
            'GiaiDoanTruoc' => 6,
            'GiaiDoanSau' => 5,
            'TrangThaiTruoc' => 'DANG_THUC_HIEN',
            'TrangThaiSau' => 'DANG_THUC_HIEN',
            'HanhDong' => 'GVHD thu hồi xác nhận mở cổng nộp hồ sơ bảo vệ',
            'NguoiThucHien' => $nguoiXacNhan,
            'LyDo' => $request->input('LyDo', 'GVHD thu hồi xác nhận mở cổng nộp hồ sơ bảo vệ để nhóm hoàn thiện thêm nội dung đề tài.'),
        ]);

        return redirect()->route('giangvien.baocao.index', ['nhom' => $nhom->MaNhom])->with('success', "Đã thu hồi xác nhận giai đoạn nộp hồ sơ bảo vệ của nhóm '{$nhom->TenNhom}'. Nhóm đã quay lại Giai đoạn 5 (hoàn thiện tiến độ).");
    }
}
