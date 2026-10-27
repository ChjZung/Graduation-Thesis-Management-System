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
        abort_unless($path && is_file($path) && $prefix && str_starts_with($normalized, $prefix), 404, 'Không tìm thấy tệp báo cáo.');
        abort_unless(in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['pdf', 'docx']), 422, 'Chỉ hỗ trợ PDF và DOCX.');
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
            ])->run(strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'docx'
                ? [$python, base_path('app/Services/report_document.py'), '--file', $path, '--unit', (string) $input['page'], '--quote=' . ($input['quote'] ?? '')]
                : [$python, base_path('app/Services/pdf_citation_preview.py'), '--pdf', $path, '--page', (string) $input['page'], '--quote=' . ($input['quote'] ?? '')]);
            $data = json_decode($process->output(), true);
            if ($process->successful() && is_array($data) && (isset($data['image']) || ($data['kind'] ?? '') === 'docx')) {
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
        $path = $this->pdfPath($report);
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'docx') {
            return response()->download($path, basename($report->TenFile ?: 'bao-cao.docx'), [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
            ]);
        }
        return response()->file($this->pdfPath($report), [
            'Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
        ]);
    }

    public function download(string $id)
    {
        $report = $this->authorizedReport($id);
        $path = $this->pdfPath($report);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $name = basename(str_replace('\\', '/', $report->TenFile ?: ('bao-cao.' . $extension)));
        return response()->download($path, $name, [
            'Content-Type' => $extension === 'docx'
                ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                : 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function status(string $id, \App\Services\AiReportProcessingService $processing)
    {
        return response()->json($processing->status($this->authorizedReport($id)))->header('Cache-Control','private, no-store');
    }

    public function review(string $id, \App\Services\AiReportProcessingService $processing)
    {
        $report = $this->authorizedReport($id);
        abort_unless($report->dotNop,422,'Báo cáo chưa liên kết với đợt nộp của giảng viên.');
        $this->pdfPath($report);
        $state = $processing->enqueue($report,true);
        return back()->with('success',$state['message']);
    }

    public function reanalyze(string $id, \App\Services\AiReportProcessingService $processing)
    {
        $report = $this->authorizedReport($id);
        $this->pdfPath($report);
        $state = $processing->enqueue($report);
        if (request()->expectsJson()) return response()->json(['state'=>$processing->status($report->fresh()),'reused'=>(bool)($state['reused'] ?? false)])->header('Cache-Control','private, no-store');
        return back()->with('success',$state['message']);
    }
}
