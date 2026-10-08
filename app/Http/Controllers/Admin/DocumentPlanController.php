<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KeHoachKhoaLuan;
use App\Models\MocThoiGianKhoaLuan;
use App\Models\QuyDinhKhoaLuan;
use App\Models\HocKy;
use App\Models\HocPhanHocKy;
use App\Models\Khoa;
use App\Models\GiaoVu;
use App\Models\ThongBao;
use App\Services\DocumentParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentPlanController extends Controller
{
    protected DocumentParserService $parserService;

    public function __construct(DocumentParserService $parserService)
    {
        $this->parserService = $parserService;
    }

    /**
     * Màn hình Form Upload Văn bản thông báo
     */
    public function importForm()
    {
        // Cho phép chọn học kỳ Chưa bắt đầu hoặc Đang diễn ra
        $hocKies = HocKy::whereIn('TrangThai', ['Chưa bắt đầu', 'đang diễn ra', 'Đang diễn ra'])
            ->orderBy('MaHocKy', 'desc')->get();
        if ($hocKies->isEmpty()) {
            $hocKies = HocKy::orderBy('MaHocKy', 'desc')->get();
        }
        $khoas = Khoa::all();
        return view('admin.kehoach.import_document', compact('hocKies', 'khoas') + ['Khoa' => $khoas]);
    }

    /**
     * Xử lý Phân Tích File Upload (PDF/Word) -> Trích xuất & Chuyển sang Preview
     */
    public function processParse(Request $request)
    {
        $request->validate([
            'MaHocKy'       => 'required|string|exists:HocKy,MaHocKy',
            'document_file' => 'required|file|mimes:pdf,docx|max:10240',
        ], [
            'MaHocKy.required'       => 'Vui lòng chọn học kỳ áp dụng cho kế hoạch.',
            'MaHocKy.exists'         => 'Học kỳ đã chọn không tồn tại trong hệ thống.',
            'document_file.required' => 'Vui lòng chọn file văn bản thông báo (PDF hoặc Word).',
            'document_file.mimes'    => 'Hệ thống chỉ hỗ trợ file định dạng .pdf hoặc .docx.',
            'document_file.max'      => 'Kích thước file không được vượt quá 10MB.',
        ]);

        $hocKy = HocKy::where('MaHocKy', $request->input('MaHocKy'))->first();
        if (!$hocKy) {
            return back()->withInput()->withErrors([
                'MaHocKy' => 'Học kỳ được chọn không hợp lệ trong hệ thống.'
            ]);
        }

        $file = $request->file('document_file');
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = 'DocPlan_' . time() . '_' . Str::random(5) . '.' . $ext;
        $tempPath = $file->storeAs('temp_docs', $filename, 'public');
        $fullPath = storage_path('app/public/' . $tempPath);

        // Bóc tách dữ liệu tiêu đề và mốc thời gian từ file văn bản
        $parsed = $this->parserService->parseDocument($fullPath, $ext);

        // Tự động tìm kế hoạch đang có của Học kỳ này (nếu có cập nhật lại) để tính diff version
        $existingPlan = KeHoachKhoaLuan::with(['mocThoiGians'])
            ->where('MaHocKy', $hocKy->MaHocKy)
            ->orderBy('created_at', 'desc')
            ->first();

        $diffs = [];
        $isVersionUpdate = false;
        $nextVersionNumber = 1;

        if ($existingPlan && $existingPlan->mocThoiGians && $existingPlan->mocThoiGians->count() > 0) {
            $isVersionUpdate = true;
            $nextVersionNumber = 2;

            foreach ($parsed['milestones'] as $phaseIndex => $newPhase) {
                $oldPhase = $existingPlan->mocThoiGians->first(function ($m) use ($newPhase) {
                    return $m->TenMoc === ($newPhase['TenMoc'] ?? '');
                });

                if ($oldPhase) {
                    $oldStart = $oldPhase->NgayBatDau ? date('Y-m-d', strtotime($oldPhase->NgayBatDau)) : null;
                    $oldEnd = $oldPhase->NgayKetThuc ? date('Y-m-d', strtotime($oldPhase->NgayKetThuc)) : null;

                    $newStart = $newPhase['NgayBatDau'] ?? null;
                    $newEnd = $newPhase['NgayKetThuc'] ?? null;

                    if ($oldStart !== $newStart || $oldEnd !== $newEnd) {
                        $diffs[] = [
                            'TenMoc'     => $newPhase['TenMoc'] ?? '',
                            'phase_name' => $newPhase['TenMoc'] ?? '',
                            'Truoc'      => ($oldStart && $oldEnd) ? date('d/m/Y', strtotime($oldStart)) . " - " . date('d/m/Y', strtotime($oldEnd)) : 'Chưa có',
                            'old_range'  => ($oldStart && $oldEnd) ? date('d/m/Y', strtotime($oldStart)) . " - " . date('d/m/Y', strtotime($oldEnd)) : 'Chưa có',
                            'Sau'        => ($newStart && $newEnd) ? date('d/m/Y', strtotime($newStart)) . " - " . date('d/m/Y', strtotime($newEnd)) : 'Chưa có',
                            'new_range'  => ($newStart && $newEnd) ? date('d/m/Y', strtotime($newStart)) . " - " . date('d/m/Y', strtotime($newEnd)) : 'Chưa có',
                            'Loai'       => 'Dời lịch',
                        ];
                    }
                }
            }
        }

        // Lưu vào Session để chờ Khoa kiểm tra & Xác nhận
        session([
            'parsed_doc_data' => [
                'ma_hoc_ky'           => $hocKy->MaHocKy,
                'ten_hoc_ky'          => $hocKy->TenHocKy,
                'temp_file_path'      => $tempPath,
                'original_name'       => $file->getClientOriginalName(),
                'ext'                 => $ext,
                'size'                => $file->getSize(),
                'header'              => $parsed['header'],
                'milestones'          => $parsed['milestones'],
                'regulations'         => $parsed['regulations'] ?? [],
                'is_version_update'   => $isVersionUpdate,
                'next_version_number' => $nextVersionNumber,
                'diffs'               => $diffs,
                'existing_plan'       => $existingPlan,
            ]
        ]);

        return redirect()->route('admin.kehoach.previewDocument');
    }

    /**
     * Giao diện Preview Kế Hoạch & So Sánh Khác Biệt Mốc Thời Gian
     */
    public function previewDocument()
    {
        $previewData = session('parsed_doc_data');
        if (empty($previewData)) {
            return redirect()->route('admin.kehoach.importDocument')
                ->with('error', 'Dữ liệu phân tích đã hết hạn. Vui lòng upload lại văn bản thông báo.');
        }

        return view('admin.kehoach.preview_document', [
            'previewData' => $previewData,
            'data'        => $previewData,
        ]);
    }

    /**
     * Xác Nhận Import -> Cập Nhật DB Transaction, Sinh Lịch & Gửi Notification Đính Kèm File Gốc
     */
    public function confirmImport(Request $request)
    {
        $previewData = session('parsed_doc_data');
        if (!$previewData) {
            return redirect()->route('admin.kehoach.importDocument')
                ->with('error', 'Phiên làm việc đã hết hạn. Vui lòng upload lại văn bản thông báo.');
        }

        $user = Auth::user();

        DB::transaction(function () use ($previewData, $user, $request) {
            $header = $previewData['header'] ?? [];
            $milestones = $previewData['milestones'] ?? [];

            // 1. Chuyển File từ Temp sang Lưu Trữ Vĩnh Viễn
            $permanentFilename = 'Official_Plan_' . time() . '_' . Str::random(6) . '.' . ($previewData['ext'] ?? 'pdf');
            $permanentPath = 'official_documents/' . $permanentFilename;
            if (!empty($previewData['temp_file_path']) && Storage::disk('public')->exists($previewData['temp_file_path'])) {
                Storage::disk('public')->copy($previewData['temp_file_path'], $permanentPath);
            }

            // 2. Đảm bảo Học kỳ hợp lệ và Kích hoạt trạng thái 'Đang diễn ra'
            $maHocKy = $previewData['ma_hoc_ky'] ?? $request->input('MaHocKy');
            $hocKy = $maHocKy ? HocKy::where('MaHocKy', $maHocKy)->first() : null;

            if (!$hocKy) {
                $hocKy = HocKy::where('TrangThai', 'Chưa bắt đầu')->first() ?? HocKy::first();
                $maHocKy = $hocKy ? $hocKy->MaHocKy : 'HK2627_1';
            }

            if ($hocKy) {
                // Đóng các học kỳ cũ khác đang diễn ra sang 'Đã kết thúc' để tránh xung đột
                HocKy::where('MaHocKy', '!=', $hocKy->MaHocKy)
                    ->where('TrangThai', 'Đang diễn ra')
                    ->update(['TrangThai' => 'Đã kết thúc']);

                $hocKy->update(['TrangThai' => 'Đang diễn ra']);
            }

            // Tự động mở các môn học phần Khóa luận (Cử nhân & Tốt nghiệp) cho học kỳ này
            $candidateHocPhans = ['HP_KLCN', 'HP_KLTN'];
            $existingHocPhans = DB::table('hocphan')->whereIn('MaHocPhan', $candidateHocPhans)->pluck('MaHocPhan')->toArray();
            foreach ($existingHocPhans as $hpCode) {
                HocPhanHocKy::updateOrCreate(
                    [
                        'MaHocPhan' => $hpCode,
                        'MaHocKy'   => $maHocKy,
                    ],
                    [
                        'TrangThai' => 'Đang mở',
                        'GhiChu'    => 'Mở tự động theo Kế hoạch khóa luận ' . ($header['TenKeHoach'] ?? $hocKy?->TenHocKy ?? ''),
                    ]
                );
            }

            // 3. Lấy mã giáo vụ
            $maGVu = GiaoVu::first()?->MaGVu ?? 'GVU01';

            // 4. Tạo hoặc Cập nhật Kế Hoạch Khóa Luận
            $currentPlanForHocKy = KeHoachKhoaLuan::where('MaHocKy', $maHocKy)->first();
            $maKeHoach = $currentPlanForHocKy->MakeHoach 
                ?? ($previewData['existing_plan']->MakeHoach ?? null)
                ?? ('KH_' . date('Y') . '_' . Str::upper(Str::random(4)));

            $keHoach = KeHoachKhoaLuan::updateOrCreate(
                ['MakeHoach' => $maKeHoach],
                [
                    'TenKeHoach' => $header['TenKeHoach'] ?? ("Kế hoạch Khóa luận " . ($header['NamHoc'] ?? date('Y'))),
                    'MaHocKy'    => $maHocKy,
                    'NoiDung'    => "Văn bản thông báo chính thức số " . ($header['SoThongBao'] ?? 'TB-KCNTT'),
                    'TrangThai'  => 'Đang thực hiện',
                    'NgayTao'    => now()->toDateString(),
                    'MaGVu'      => $maGVu,
                ]
            );

            // 5. Cập nhật / Sinh các mốc thời gian quy trình (Xóa các mốc cũ của kế hoạch để tránh mốc rác/gộp còn tồn tại)
            MocThoiGianKhoaLuan::where('MakeHoach', $keHoach->MakeHoach)->delete();

            foreach ($milestones as $idx => $m) {
                $maMoc = 'MOC_' . substr($keHoach->MakeHoach, -4) . '_' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
                MocThoiGianKhoaLuan::create([
                    'MaMoc'       => $maMoc,
                    'TenMoc'      => $m['TenMoc'] ?? ('Mốc quy trình ' . ($idx + 1)),
                    'NgayBatDau'  => !empty($m['NgayBatDau']) ? date('Y-m-d', strtotime($m['NgayBatDau'])) : now()->toDateString(),
                    'NgayKetThuc' => !empty($m['NgayKetThuc']) ? date('Y-m-d', strtotime($m['NgayKetThuc'])) : now()->addDays(7)->toDateString(),
                    'MoTa'        => "Trích xuất tự động từ văn bản " . ($header['SoThongBao'] ?? '') . (!empty($m['LoaiGiaiDoan']) ? " [{$m['LoaiGiaiDoan']}]" : ''),
                    'MakeHoach'   => $keHoach->MakeHoach,
                ]);
            }

            // 5b. Tự động lưu Quy định Khóa luận trích xuất từ văn bản kế hoạch vào CSDL
            $regulations = $previewData['regulations'] ?? [];
            if (empty($regulations)) {
                $regulations = \App\Services\PlanPhaseService::getDefaultRegulations();
            }
            foreach ($regulations as $idx => $r) {
                $cleanSuffix = preg_replace('/[^A-Za-z0-9]/', '', $keHoach->MakeHoach);
                $maQD = 'QD_' . substr($cleanSuffix, -4) . '_' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
                $maQD = substr($maQD, 0, 20);

                QuyDinhKhoaLuan::updateOrCreate(
                    [
                        'MakeHoach'  => $keHoach->MakeHoach,
                        'TenQuyDinh' => $r['TenQuyDinh'],
                    ],
                    [
                        'MaQuyDinh'  => $maQD,
                        'GiaTri'     => $r['GiaTri'] ?? 'Theo quy định',
                        'MoTa'       => $r['MoTa'] ?? null,
                    ]
                );
            }

            // 6. Gửi Thông Báo Tự Động cho Sinh viên & Giảng viên
            $isUpdate = !empty($previewData['is_version_update']);
            $title = "[Khóa luận] " . ($isUpdate ? "Thông báo ĐIỀU CHỈNH mốc thời gian Khóa luận" : "Thông báo chính thức Kế hoạch Khóa luận " . ($header['NamHoc'] ?? ''));
            $content = "Khoa đã ban hành " . ($header['SoThongBao'] ?? "văn bản thông báo") . " về kế hoạch thực hiện Khóa luận tốt nghiệp. File gốc đính kèm: /storage/" . $permanentPath;

            $maTB = 'TB_' . Str::upper(Str::random(7));
            while (ThongBao::where('MaThongBao', $maTB)->exists()) {
                $maTB = 'TB_' . Str::upper(Str::random(7));
            }

            ThongBao::create([
                'MaThongBao'   => $maTB,
                'TieuDe'       => $title,
                'NoiDung'      => $content,
                'LoaiThongBao' => 'Kế hoạch',
                'DoiTuongNhan' => 'Toàn thể',
                'NgayTao'      => now(),
                'TrangThai'    => 'Đã phát hành',
                'MaGVu'        => $maGVu,
            ]);
        });

        // Xóa dữ liệu tạm session
        session()->forget('parsed_doc_data');

        return redirect()->route('admin.kehoach.index')
            ->with('success', '🎉 Đã import văn bản thông báo thành công! Kế hoạch các mốc, Lịch quy trình và Thông báo đính kèm file gốc đã được sinh tự động.');
    }

    /**
     * Xem Lịch sử Các Phiên Bản Văn Bản Thông Báo
     */
    public function history($maVanBan)
    {
        return redirect()->route('admin.kehoach.index');
    }

    /**
     * Khôi phục Mốc Thời Gian Kế Hoạch Về Phiên Bản Cũ (Rollback)
     */
    public function rollbackVersion($maVanBan, $versionId)
    {
        return redirect()->route('admin.kehoach.index');
    }
}
