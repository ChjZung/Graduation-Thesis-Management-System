<?php

namespace App\Http\Controllers;

use App\Models\GiangVien;
use App\Models\KhoiBaoCaoTienDo;
use App\Models\PhieuDangKy;
use App\Models\SinhVien;
use App\Services\AiSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;

class BaoCaoAiController extends Controller
{
    private function authorizedReport(string $id): KhoiBaoCaoTienDo
    {
        $report = KhoiBaoCaoTienDo::with(['deTai', 'baoCao.deTai', 'dotNop.giaiDoan', 'tomTat'])->findOrFail($id);
        $topic = $report->deTai ?? $report->baoCao?->deTai;
        abort_unless($topic, 404, 'Không tìm thấy đề tài của báo cáo.');
        $lecturer = GiangVien::getLoggedInGiangVien(Auth::user());
        $owner = $lecturer && $topic->MaGV === $lecturer->MaGV;
        $student = SinhVien::where('MaTK', Auth::user()->MaTK)->first();
        $member = $student && PhieuDangKy::where('MaDeTai', $topic->MaDeTai)
            ->whereIn('TrangThai', ['Đã duyệt', 'da_duyet'])
            ->whereHas('nhom.thanhVienNhoms', fn ($q) => $q->where('MaSV', $student->MaSV)->where('TrangThai', 'da_tham_gia'))->exists();
        abort_unless($owner || $member, 403, 'Bạn không có quyền xem báo cáo này.');
        return $report;
    }

    private function pdfPath(KhoiBaoCaoTienDo $report): string
    {
        $root = realpath(storage_path('app/public'));
        $path = $root && $report->DuongDanFile ? realpath($root . DIRECTORY_SEPARATOR . $report->DuongDanFile) : false;
        $prefix = $root ? rtrim(str_replace('\\', '/', $root), '/') . '/' : '';
        $normalized = $path ? str_replace('\\', '/', $path) : '';
        if (PHP_OS_FAMILY === 'Windows') {
            $prefix = strtolower($prefix);
            $normalized = strtolower($normalized);
        }
        abort_unless($path && is_file($path) && $prefix && str_starts_with($normalized, $prefix), 404, 'Không tìm thấy tệp PDF của báo cáo.');
        return $path;
    }

    public function preview(Request $request, string $id)
    {
        $input = $request->validate(['page' => 'required|integer|min:1|max:10000', 'quote' => 'nullable|string|max:3000']);
        $report = $this->authorizedReport($id);
        $path = $this->pdfPath($report);
        $python = env('PYTHON_PATH') ?: (file_exists('C:\\laragon\\bin\\python\\python-3.10\\python.exe') ? 'C:\\laragon\\bin\\python\\python-3.10\\python.exe' : 'python');
        try {
            $process = Process::timeout(30)->env([
                'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows', 'SYSTEMROOT' => getenv('SYSTEMROOT') ?: 'C:\\Windows',
                'PATH' => getenv('PATH') ?: 'C:\\Windows\\system32;C:\\Windows',
                'TEMP' => getenv('TEMP') ?: sys_get_temp_dir(), 'TMP' => getenv('TMP') ?: sys_get_temp_dir(),
                'PYTHONUTF8' => '1', 'PYTHONIOENCODING' => 'utf-8',
            ])->run([$python, base_path('app/Services/pdf_citation_preview.py'), '--pdf', $path, '--page', (string) $input['page'], '--quote=' . ($input['quote'] ?? '')]);
            $data = json_decode($process->output(), true);
            if ($process->successful() && is_array($data) && isset($data['image'])) {
                return response()->json($data)->header('Cache-Control', 'private, no-store');
            }
        } catch (\Throwable $e) {
            // Do not disclose local paths or process commands through HTTP errors.
        }
        return response()->json(['message' => 'Chưa mở được trang PDF. Bạn có thể chọn Mở PDF đầy đủ để kiểm tra trực tiếp.'], 422);
    }

    public function pdf(string $id)
    {
        $report = $this->authorizedReport($id);
        return response()->file($this->pdfPath($report), [
            'Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
        ]);
    }

    public function review(string $id, AiSummaryService $ai)
    {
        $report = $this->authorizedReport($id);
        abort_unless($report->dotNop, 422, 'Báo cáo chưa liên kết với đợt nộp của giảng viên.');
        $result = $ai->summarizePdf($this->pdfPath($report), $report->dotNop->giaiDoan?->ThuTu ?? 1, [
            'review_context' => $report->dotNop->nguCanhDoiChieuAi(), 'review_only' => true,
        ]);
        $review = $result['data']['requirements_review'] ?? null;
        if (!is_array($review)) {
            return back()->with('error', 'Chưa đối chiếu được yêu cầu. Vui lòng kiểm tra dịch vụ AI hoặc nội dung văn bản của PDF.');
        }
        DB::transaction(function () use ($report, $review) {
            // Only append the review; keep all existing summary sections.
            $report->update(['TomTatJson' => $this->withReview($report->TomTatJson, $review)]);
            if ($report->tomTat) {
                $report->tomTat->update(['TomTatJson' => $this->withReview($report->tomTat->TomTatJson, $review)]);
            }
        });
        return back()->with('success', 'Đã cập nhật phần đối chiếu yêu cầu. Xem kết quả bên dưới báo cáo AI.');
    }

    public function reanalyze(string $id, AiSummaryService $ai)
    {
        $report = $this->authorizedReport($id);
        $options = $report->dotNop ? ['review_context' => $report->dotNop->nguCanhDoiChieuAi()] : [];
        $result = $ai->summarizePdf($this->pdfPath($report), $report->dotNop?->giaiDoan?->ThuTu ?? 1, $options);
        if (empty($result['success']) || empty($result['data']['analysis']['total_pages'])) {
            return back()->with('error', 'Chưa đọc được PDF để phân tích lại. Bản tóm tắt cũ vẫn được giữ.');
        }
        $ai->saveVersionSummary($report, $result);
        return back()->with('success', 'Đã phân tích lại PDF và cập nhật trích dẫn. Nội dung báo cáo do sinh viên nhập được giữ nguyên.');
    }

    private function withReview($payload, array $review): array
    {
        if (is_string($payload)) $payload = json_decode($payload, true);
        $payload = is_array($payload) ? $payload : [];
        if (isset($payload['data']) && is_array($payload['data'])) {
            $payload['data']['requirements_review'] = $review;
        } else {
            $payload['requirements_review'] = $review;
        }
        return $payload;
    }
}
