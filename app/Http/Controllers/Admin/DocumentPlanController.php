<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KeHoachKhoaLuan;
use App\Models\MocThoiGianKhoaLuan;
use App\Models\ThongBaoVanBan;
use App\Models\ThongBaoVanBanVersion;
use App\Services\DocumentParserService;
use App\Services\ThongBaoService;
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
        $hocKies = \App\Models\HocKy::all();
        $khoas = \App\Models\Khoa::all();
        return view('admin.kehoach.import_document', compact('hocKies', 'khoas') + ['Khoa' => $khoas]);
    }

    /**
     * Xử lý Phân Tích File Upload (PDF/Word) $\rightarrow$ Trích xuất & Chuyển sang Preview
     */
    public function processParse(Request $request)
    {
        $request->validate([
            'document_file' => 'required|file|mimes:pdf,docx|max:10240',
        ], [
            'document_file.required' => 'Vui lòng chọn file văn bản thông báo (PDF hoặc Word).',
            'document_file.mimes'    => 'Hệ thống chỉ hỗ trợ file định dạng .pdf hoặc .docx.',
            'document_file.max'      => 'Kích thước file không được vượt quá 10MB.',
        ]);

        $file = $request->file('document_file');
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = 'DocPlan_' . time() . '_' . Str::random(5) . '.' . $ext;
        $tempPath = $file->storeAs('temp_docs', $filename, 'public');
        $fullPath = storage_path('app/public/' . $tempPath);

        // Bóc tách dữ liệu tiêu đề và mốc thời gian từ file văn bản
        $parsed = $this->parserService->parseDocument($fullPath, $ext);

        // Tự động tìm kế hoạch đang có của Khoa để tính diff version
        $existingPlan = KeHoachKhoaLuan::with(['mocThoiGians', 'thongBaoVanBan.versions'])
            ->where('TrangThai', 'Đang thực hiện')
            ->orderBy('created_at', 'desc')
            ->first();

        $diffs = [];
        $isVersionUpdate = false;
        $nextVersionNumber = 1;

        if ($existingPlan && $existingPlan->mocThoiGians->count() > 0) {
            $isVersionUpdate = true;
            $existingVanBan = ThongBaoVanBan::where('MaKeHoach', $existingPlan->MaKeHoach)->first();
            $nextVersionNumber = $existingVanBan ? ($existingVanBan->PhienBanHienTai + 1) : 2;

            foreach ($parsed['milestones'] as $phaseIndex => $newPhase) {
                $oldPhase = $existingPlan->mocThoiGians->where('LoaiGiaiDoan', $newPhase['LoaiGiaiDoan'])->first();
                if ($oldPhase) {
                    $oldStart = $oldPhase->NgayBatDau ? date('Y-m-d', strtotime($oldPhase->NgayBatDau)) : null;
                    $oldEnd = $oldPhase->NgayKetThuc ? date('Y-m-d', strtotime($oldPhase->NgayKetThuc)) : null;

                    if ($oldStart !== $newPhase['NgayBatDau'] || $oldEnd !== $newPhase['NgayKetThuc']) {
                        $diffs[] = [
                            'phase_name' => $newPhase['TenMoc'],
                            'old_range'  => ($oldStart && $oldEnd) ? date('d/m/Y', strtotime($oldStart)) . " - " . date('d/m/Y', strtotime($oldEnd)) : 'Chưa có',
                            'new_range'  => date('d/m/Y', strtotime($newPhase['NgayBatDau'])) . " - " . date('d/m/Y', strtotime($newPhase['NgayKetThuc'])),
                        ];
                    }
                }
            }
        }

        // Lưu vào Session để chờ Khoa kiểm tra & Xác nhận
        session([
            'parsed_doc_data' => [
                'temp_file_path'      => $tempPath,
                'original_name'       => $file->getClientOriginalName(),
                'ext'                 => $ext,
                'size'                => $file->getSize(),
                'header'              => $parsed['header'],
                'milestones'          => $parsed['milestones'],
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
        $data = session('parsed_doc_data');
        if (!$data) {
            return redirect()->route('admin.kehoach.importDocument')
                ->withErrors('Dữ liệu phân tích đã hết hạn. Vui lòng upload lại văn bản thông báo.');
        }

        return view('admin.kehoach.preview_document', compact('data'));
    }

    /**
     * Xác Nhận Import $\rightarrow$ Cập Nhật DB Transaction, Sinh Lịch & Gửi Notification Đính Kèm File Gốc
     */
    public function confirmImport(Request $request)
    {
        $previewData = session('parsed_doc_data');
        if (!$previewData) {
            return redirect()->route('admin.kehoach.importDocument')
                ->withErrors('Phiên làm việc đã hết hạn.');
        }

        $user = Auth::user();

        DB::transaction(function () use ($previewData, $user) {
            $header = $previewData['header'];
            $milestones = $previewData['milestones'];

            // 1. Chuyển File từ Temp sang Lưu Trữ Vĩnh Viễn
            $permanentFilename = 'Official_Plan_' . time() . '_' . Str::random(6) . '.' . $previewData['ext'];
            $permanentPath = 'official_documents/' . $permanentFilename;
            Storage::disk('public')->copy($previewData['temp_file_path'], $permanentPath);

            // 2. Tạo hoặc Cập nhật Kế Hoạch Khóa Luận
            $maKeHoach = $previewData['existing_plan']->MaKeHoach ?? ('KH_' . date('Y') . '_' . Str::upper(Str::random(4)));

            $keHoach = KeHoachKhoaLuan::updateOrCreate(
                ['MaKeHoach' => $maKeHoach],
                [
                    'TenKeHoach'  => "Kế hoạch Khóa luận " . ($header['NamHoc'] ?? date('Y')),
                    'MaHocKy'     => $header['MaHocKy'] ?? 'HK1-2026-2027',
                    'MoTa'        => "Văn bản thông báo chính thức số " . ($header['SoThongBao'] ?? 'TB-KCNTT'),
                    'NgayBatDau'  => $milestones[1]['NgayBatDau'] ?? now()->toDateString(),
                    'NgayKetThuc' => $milestones[count($milestones)]['NgayKetThuc'] ?? now()->addMonths(4)->toDateString(),
                    'TrangThai'   => 'Đang thực hiện',
                ]
            );

            // 3. Tự Động Cập Nhật / Sinh 12 Mốc Thời Gian Quy Trình
            foreach ($milestones as $idx => $m) {
                MocThoiGianKhoaLuan::updateOrCreate(
                    [
                        'MaKeHoach'     => $keHoach->MaKeHoach,
                        'LoaiGiaiDoan'  => $m['LoaiGiaiDoan'],
                    ],
                    [
                        'MaMoc'        => 'MOC_' . str_pad($idx, 2, '0', STR_PAD_LEFT),
                        'TenMoc'       => $m['TenMoc'],
                        'NgayBatDau'   => $m['NgayBatDau'],
                        'NgayKetThuc'  => $m['NgayKetThuc'],
                        'TrangThai'    => 'Đã lên lịch',
                        'MoTa'         => "Trích xuất tự động từ văn bản " . ($header['SoThongBao'] ?? ''),
                    ]
                );
            }

            // 4. Lưu Bản Ghi Văn Bản Thông Báo Gốc & Quản Lý Phiên Bản
            $vanBan = ThongBaoVanBan::where('MaKeHoach', $keHoach->MaKeHoach)->first();

            if ($vanBan) {
                $vanBan->update([
                    'TenVanBan'       => $previewData['original_name'],
                    'SoThongBao'      => $header['SoThongBao'] ?? $vanBan->SoThongBao,
                    'NgayBanHanh'     => now(),
                    'FileGocPath'     => 'storage/' . $permanentPath,
                    'PhienBanHienTai' => $previewData['next_version_number'],
                    'NguoiUpload'     => $user->MaTK ?? 'ADMIN',
                ]);
            } else {
                $vanBan = ThongBaoVanBan::create([
                    'MaVanBan'        => 'VB_' . Str::upper(Str::random(7)),
                    'MaKeHoach'       => $keHoach->MaKeHoach,
                    'TenVanBan'       => $previewData['original_name'],
                    'SoThongBao'      => $header['SoThongBao'] ?? 'TB-KCNTT',
                    'NgayBanHanh'     => now(),
                    'FileGocPath'     => 'storage/' . $permanentPath,
                    'FileType'        => $previewData['ext'],
                    'FileSize'        => $previewData['size'],
                    'PhienBanHienTai' => 1,
                    'NguoiUpload'     => $user->MaTK ?? 'ADMIN',
                ]);
            }

            ThongBaoVanBanVersion::create([
                'MaVanBan'              => $vanBan->MaVanBan,
                'PhienBan'              => $previewData['next_version_number'],
                'FileGocPath'           => 'storage/' . $permanentPath,
                'NoiDungTrichXuatJson'  => $header,
                'MocThoiGianJson'        => $milestones,
                'ThayDoiSoVoiTruocJson'  => $previewData['diffs'],
                'LyDoThayDoi'           => $previewData['is_version_update'] ? "Cập nhật thông báo điều chỉnh mốc thời gian" : "Ban hành thông báo đợt 1",
                'NguoiThayDoi'          => $user->MaTK ?? 'ADMIN',
            ]);

            // 5. Gửi Thông Báo Tự Động ĐÍNH KÈM FILE VĂN BẢN GỐC cho Sinh viên & Giảng viên
            $title = "[Khóa luận] " . ($previewData['is_version_update'] ? "Thông báo ĐIỀU CHỈNH mốc thời gian Khóa luận" : "Thông báo chính thức Kế hoạch Khóa luận " . $header['NamHoc']);
            $content = "Khoa Công nghệ Thông tin đã ban hành " . ($header['SoThongBao'] ?? "văn bản thông báo") . " về kế hoạch thực hiện Khóa luận tốt nghiệp. Các mốc quan trọng: Đăng ký đề tài (" . date('d/m/Y', strtotime($milestones[1]['NgayKetThuc'] ?? '2026-08-11')) . "), Nộp báo cáo (" . date('d/m/Y', strtotime($milestones[6]['NgayKetThuc'] ?? '2026-11-11')) . "). Vui lòng bấm để xem file thông báo gốc.";

            ThongBaoService::guiThongBaoKemFileGoc($title, $content, 'storage/' . $permanentPath, 'Kế hoạch');
        });

        // Xóa dữ liệu tạm session
        session()->forget('parsed_doc_data');

        return redirect()->route('admin.kehoach.index')
            ->with('success', '🎉 Đã import văn bản thông báo thành công! Kế hoạch 12 mốc, Lịch quy trình và Thông báo đính kèm file gốc đã được sinh tự động.');
    }

    /**
     * Xem Lịch sử Các Phiên Bản Văn Bản Thông Báo
     */
    public function history($maVanBan)
    {
        $vanBan = ThongBaoVanBan::with(['versions', 'keHoach'])->findOrFail($maVanBan);
        return view('admin.kehoach.document_history', compact('vanBan'));
    }

    /**
     * Khôi phục Mốc Thời Gian Kế Hoạch Về Phiên Bản Cũ (Rollback)
     */
    public function rollbackVersion($maVanBan, $versionId)
    {
        $vanBan = ThongBaoVanBan::findOrFail($maVanBan);
        $version = ThongBaoVanBanVersion::where('MaVanBan', $maVanBan)->where('id', $versionId)->firstOrFail();

        DB::transaction(function () use ($vanBan, $version) {
            $milestones = $version->MocThoiGianJson;

            if ($milestones && is_array($milestones)) {
                foreach ($milestones as $idx => $m) {
                    MocThoiGianKhoaLuan::updateOrCreate(
                        [
                            'MaKeHoach'    => $vanBan->MaKeHoach,
                            'LoaiGiaiDoan' => $m['LoaiGiaiDoan'],
                        ],
                        [
                            'TenMoc'      => $m['TenMoc'],
                            'NgayBatDau'  => $m['NgayBatDau'],
                            'NgayKetThuc' => $m['NgayKetThuc'],
                        ]
                    );
                }
            }

            $vanBan->update([
                'PhienBanHienTai' => $version->PhienBan,
                'FileGocPath'     => $version->FileGocPath,
            ]);
        });

        return redirect()->route('admin.kehoach.documentHistory', $maVanBan)
            ->with('success', "↺ Đã khôi phục thành công mốc thời gian kế hoạch về phiên bản v{$version->PhienBan}!");
    }
}
