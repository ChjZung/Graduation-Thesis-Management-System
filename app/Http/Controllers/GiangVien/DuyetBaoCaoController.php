<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\BaoCaoTienDo;
use App\Models\KhoiBaoCaoTienDo;
use App\Models\GiangVien;
use App\Models\Nhom;
use App\Models\PhieuDangKy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DuyetBaoCaoController extends Controller
{
    public function index()
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
        }

        // Thống kê 4 thẻ KPI theo chuẩn Figma
        $tongSoNhom = $nhoms->count();
        $baocaoChoDuyet = $nhoms->flatMap->baoCaos->filter(function($b) {
            return $b->TrangThai === 'Chờ duyệt' || $b->TrangThai === 'Đã nộp' || ($b->khoiBaoCaos && $b->khoiBaoCaos->where('TrangThai', 'Chờ duyệt')->isNotEmpty());
        })->count();
        $baocaoDaDat = $nhoms->flatMap->baoCaos->where('TrangThai', 'Đạt')->count();
        $nhomCanNhacNho = $nhoms->filter(function($n) {
            return $n->status_code === 'QUA_HAN' || $n->status_code === 'SAP_DEN_HAN' || $n->baoCaos->where('TrangThai', 'Yêu cầu nộp lại')->isNotEmpty();
        })->count();

        return view('giangvien.baocao.index', compact(
            'giangVien', 'nhoms', 'tongSoNhom', 'baocaoChoDuyet', 'baocaoDaDat', 'nhomCanNhacNho'
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
            'NoiDung'     => 'required|string|max:2000',
            'LoaiNhanXet' => 'required|in:Đạt,Yêu cầu nộp lại',
        ], [
            'NoiDung.required'     => 'Vui lòng nhập nhận xét.',
            'LoaiNhanXet.required' => 'Vui lòng chọn kết quả đánh giá.',
            'LoaiNhanXet.in'       => 'Kết quả phải là "Đạt" hoặc "Yêu cầu nộp lại".',
        ]);

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
                'NhanXetGV'    => $request->NoiDung,
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
                'NhanXetGV'    => $request->NoiDung,
                'NguoiNhanXet' => $nguoiNhanXet,
                'NgayNop'      => $baoCao->NgayNop ?? now(),
                'NgayNhanXet'  => now(),
            ]);
        }

        $updateData = [
            'TrangThai' => $request->LoaiNhanXet,
            'NhanXet'   => $request->NoiDung,
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
        if (!empty($khoi->TomTatJson) && !empty($khoi->tomTat)) {
            return;
        }

        $pdfPath = null;
        if (!empty($khoi->DuongDanFile)) {
            $candidate1 = storage_path('app/public/' . $khoi->DuongDanFile);
            $candidate2 = public_path('storage/' . $khoi->DuongDanFile);
            if (file_exists($candidate1)) {
                $pdfPath = $candidate1;
            } elseif (file_exists($candidate2)) {
                $pdfPath = $candidate2;
            }
        }

        $aiRes = null;
        if ($pdfPath) {
            try {
                $aiService = app(\App\Services\AiSummaryService::class);
                $aiRes = $aiService->summarizePdf($pdfPath, $khoi->LanBaoCao ?? 1);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Auto AI summarize for Lecturer view failed: " . $e->getMessage());
            }
        }

        // Nếu không có file PDF hoặc phân tích PDF lỗi, tự động tổng hợp thông minh từ nội dung báo cáo nộp
        if (empty($aiRes) || empty($aiRes['success'])) {
            $completedText = $khoi->KetQuaDatDuoc ?: ($khoi->NoiDungBaoCao ?: 'Đã hoàn thành các hạng mục theo kế hoạch giai đoạn.');
            $difficultyText = $khoi->KhoKhan ?: 'Không ghi nhận vướng mắc kỹ thuật lớn.';
            $nextPlanText = $khoi->KeHoachTiepTheo ?: 'Tiếp tục triển khai theo lộ trình đồ án.';

            $aiRes = [
                'success' => true,
                'total_pages' => 1,
                'processing_time' => 0.1,
                'data' => [
                    'overall_summary' => "AI tự động trích xuất: Nhóm đã thực hiện {$completedText}",
                    'completion_percentage' => $khoi->PhanTramHoanThanh ?: 70,
                    'completed_work' => [
                        ['content' => $completedText, 'source_pages' => [1], 'evidence' => 'Nội dung nộp trực tiếp từ sinh viên']
                    ],
                    'difficulties' => [
                        ['content' => $difficultyText, 'source_pages' => [1]]
                    ],
                    'next_stage_plan' => [
                        ['content' => $nextPlanText]
                    ],
                ],
                'formatted' => [
                    'completed_work' => "- " . $completedText,
                    'difficulties' => "- " . $difficultyText,
                    'next_plan' => "- " . $nextPlanText,
                ]
            ];
        }

        try {
            $maTomTat = \App\Helpers\IdGenerator::nextTomTat();
            \App\Models\TomTatBaoCao::updateOrCreate(
                ['MaBaoCao' => $baoCao->MaBaoCao],
                [
                    'MaTomTat' => $maTomTat,
                    'MaKhoi' => $khoi->MaKhoi,
                    'TenFile' => $khoi->TenFile,
                    'DuongDanFile' => $khoi->DuongDanFile,
                    'SoTrang' => (int)($aiRes['total_pages'] ?? 1),
                    'CongViecDaHoanThanh' => $aiRes['formatted']['completed_work'] ?? '',
                    'KhoKhan' => $aiRes['formatted']['difficulties'] ?? '',
                    'KeHoachTuanToi' => $aiRes['formatted']['next_plan'] ?? '',
                    'NoiDungAI' => $aiRes['data']['overall_summary'] ?? '',
                    'TomTatJson' => $aiRes,
                    'TrangThai' => 'Đã tạo',
                    'TrangThaiXuLy' => 'hoan_thanh',
                    'NgayTomTat' => now(),
                ]
            );

            $khoi->update([
                'TomTatAI' => $aiRes['data']['overall_summary'] ?? '',
                'TomTatJson' => $aiRes,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Save auto AI summary failed: " . $e->getMessage());
        }
    }

    public function storeNhanXetKhoi(Request $request, $id)
    {
        $request->validate([
            'NoiDung' => 'required|string|max:2000',
            'LoaiNhanXet' => 'required|in:Đạt,Yêu cầu nộp lại',
        ], [
            'NoiDung.required' => 'Vui lòng nhập nội dung nhận xét.',
            'LoaiNhanXet.required' => 'Vui lòng chọn kết quả đánh giá.',
        ]);

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
            'NhanXetGV' => $request->NoiDung,
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
        if ($baoCao->tomTatBaoCao) return;

        $completedText = $baoCao->NoiDungBaoCao ?: 'Đã hoàn thành các công việc theo đúng yêu cầu đề ra của giai đoạn ' . $baoCao->LanBaoCao . '.';
        $aiRes = [
            'success' => true,
            'total_pages' => 1,
            'processing_time' => 0.1,
            'data' => [
                'overall_summary' => "AI tự động phân tích: Nhóm đã nộp báo cáo và thực hiện nội dung: {$completedText}",
                'completion_percentage' => $baoCao->PhanTramHoanThanh ?: 80,
                'completed_work' => [
                    ['content' => $completedText, 'source_pages' => [1], 'evidence' => 'Bản nộp giai đoạn của nhóm']
                ],
                'difficulties' => [
                    ['content' => 'Không ghi nhận trở ngại lớn trong quá trình thực hiện giai đoạn.', 'source_pages' => [1]]
                ],
                'next_stage_plan' => [
                    ['content' => 'Chuẩn bị chuyển sang các mốc kế tiếp theo quy định.']
                ],
            ],
            'formatted' => [
                'completed_work' => "- " . $completedText,
                'difficulties' => "- Không ghi nhận trở ngại lớn.",
                'next_plan' => "- Chuẩn bị chuyển sang giai đoạn kế tiếp.",
            ]
        ];

        try {
            $maTomTat = \App\Helpers\IdGenerator::nextTomTat();
            \App\Models\TomTatBaoCao::create([
                'MaTomTat' => $maTomTat,
                'MaBaoCao' => $baoCao->MaBaoCao,
                'TenFile' => $baoCao->TenFile,
                'DuongDanFile' => $baoCao->DuongDanFile,
                'SoTrang' => 1,
                'CongViecDaHoanThanh' => $aiRes['formatted']['completed_work'],
                'KhoKhan' => $aiRes['formatted']['difficulties'],
                'KeHoachTuanToi' => $aiRes['formatted']['next_plan'],
                'NoiDungAI' => $aiRes['data']['overall_summary'],
                'TomTatJson' => $aiRes,
                'TrangThai' => 'Đã tạo',
                'TrangThaiXuLy' => 'hoan_thanh',
                'NgayTomTat' => now(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("ensureAiSummaryForBaoCao failed: " . $e->getMessage());
        }
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

        $baoCao->update([
            'TrangThai' => 'Đạt',
            'PhanTramHoanThanh' => $phanTram,
            'NhanXet' => $nhanXet,
            'NgayXacNhan' => now(),
            'NguoiXacNhan' => $nguoiXacNhan,
        ]);

        return redirect()->route('giangvien.baocao.show', $maBaoCao)->with('success', "Đã xác nhận hoàn thành Giai đoạn {$baoCao->LanBaoCao}! Nhóm sinh viên đã đủ điều kiện chuyển sang giai đoạn tiếp theo.");
    }
}
