<?php

namespace App\Services;

use App\Models\BaoCaoTienDo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AiSummaryService
{
    private const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent?key={key}';
    private const MAX_RETRIES    = 3;
    private const CHUNK_SIZE     = 3000; // Ký tự tối đa mỗi chunk gửi Gemini

    /**
     * Tạo tóm tắt AI cho báo cáo tiến độ.
     * Nếu có GEMINI_API_KEY → dùng Gemini API thật.
     * Nếu không có key → fallback sang rule-based mock.
     */
    public function generate(BaoCaoTienDo $baoCao): array
    {
        $apiKey = config('services.gemini.api_key');

        if ($apiKey) {
            return $this->generateWithGemini($baoCao, $apiKey);
        }

        return $this->generateMock($baoCao);
    }

    // ─── BƯỚC 5: Gemini API Integration ──────────────────────────────────────

    private function generateWithGemini(BaoCaoTienDo $baoCao, string $apiKey): array
    {
        $vanBan = $this->extractText($baoCao);
        $prompt = $this->buildPrompt($baoCao->LanBaoCao, $vanBan);

        $lastError = null;

        // Retry tối đa MAX_RETRIES lần nếu JSON parse thất bại
        for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
            try {
                $url = str_replace(
                    ['{model}', '{key}'],
                    ['gemini-2.0-flash', $apiKey],
                    self::GEMINI_API_URL
                );

                $response = Http::timeout(30)->post($url, [
                    'contents' => [[
                        'parts' => [['text' => $prompt]],
                    ]],
                    'generationConfig' => [
                        'temperature'       => 0.3,
                        'maxOutputTokens'   => 1024,
                        'responseMimeType'  => 'application/json',
                    ],
                ]);

                if ($response->failed()) {
                    throw new \RuntimeException("Gemini API HTTP error: " . $response->status());
                }

                $rawText = $response->json('candidates.0.content.parts.0.text');
                $data = json_decode($rawText, true, 512, JSON_THROW_ON_ERROR);

                return $this->formatAiResponse($data, $baoCao->LanBaoCao);
            } catch (\Throwable $e) {
                $lastError = $e;
                Log::warning("AiSummaryService: Lần thử {$attempt}/{$this->MAX_RETRIES} thất bại: " . $e->getMessage());
                sleep(1); // Đợi 1 giây trước khi retry
            }
        }

        // Tất cả lần retry thất bại → fallback sang mock
        Log::error('AiSummaryService: Gemini API thất bại hoàn toàn. Fallback sang mock. Lỗi: ' . $lastError?->getMessage());
        return $this->generateMock($baoCao);
    }

    /**
     * Đọc nội dung văn bản từ báo cáo:
     * Ưu tiên: PDF → NoiDungBaoCao → LinkCode (nếu Git)
     */
    private function extractText(BaoCaoTienDo $baoCao): string
    {
        $parts = [];

        // Thử đọc PDF nếu có package smalot/pdfparser
        if ($baoCao->DuongDanFile && class_exists('\Smalot\PdfParser\Parser')) {
            try {
                $path = Storage::disk('public')->path($baoCao->DuongDanFile);
                if (file_exists($path)) {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($path);
                    $pdfText = $pdf->getText();
                    if (!empty(trim($pdfText))) {
                        $parts[] = mb_substr($pdfText, 0, self::CHUNK_SIZE);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('AiSummaryService: Không đọc được PDF: ' . $e->getMessage());
            }
        }

        // Thêm nội dung ghi chú thủ công của SV
        if (!empty($baoCao->NoiDungBaoCao)) {
            $parts[] = "Ghi chú sinh viên: " . mb_substr($baoCao->NoiDungBaoCao, 0, 500);
        }

        if (!empty($baoCao->LinkCode)) {
            $parts[] = "Link source code: " . $baoCao->LinkCode;
        }

        return implode("\n\n", $parts) ?: "Báo cáo Mốc {$baoCao->LanBaoCao}";
    }

    /**
     * Xây dựng Prompt chuẩn với JSON schema output.
     */
    private function buildPrompt(int $lan, string $vanBan): string
    {
        // Lọc Prompt Injection cơ bản
        $vanBan = preg_replace('/ignore\s+previous\s+instructions?/i', '[FILTERED]', $vanBan);

        return <<<PROMPT
Bạn là trợ lý AI chuyên phân tích báo cáo tiến độ khóa luận tốt nghiệp đại học (Mốc {$lan}/5).
Đọc nội dung báo cáo dưới đây và trích xuất thông tin, trả về ĐÚNG FORMAT JSON sau (không thêm markdown):

{
  "CongViecDaHoanThanh": "Mô tả ngắn gọn công việc đã hoàn thành trong tuần/giai đoạn này",
  "KhoKhan": "Các khó khăn đã gặp và cách giải quyết",
  "KeHoachTuanToi": "Kế hoạch cụ thể cho giai đoạn tiếp theo"
}

---
NỘI DUNG BÁO CÁO:
{$vanBan}
PROMPT;
    }

    private function formatAiResponse(array $data, int $lan): array
    {
        $tieuDe = "Báo cáo mốc {$lan}";
        $cong = $data['CongViecDaHoanThanh'] ?? 'Hoàn thành các công việc theo kế hoạch.';
        $kho  = $data['KhoKhan'] ?? 'Không ghi nhận khó khăn đặc biệt.';
        $ke   = $data['KeHoachTuanToi'] ?? 'Tiếp tục các bước theo lộ trình.';

        return [
            'CongViecDaHoanThanh' => $cong,
            'KhoKhan'             => $kho,
            'KeHoachTuanToi'      => $ke,
            'NoiDungAI'           => "📋 **TÓM TẮT MỐC {$lan}: {$tieuDe}**\n\n✅ **Đã hoàn thành:** {$cong}\n\n⚠️ **Khó khăn:** {$kho}\n\n📅 **Kế hoạch tiếp theo:** {$ke}",
            'DoTinCayAI'          => 92.00, // Gemini API → độ tin cậy cao hơn mock
            'NgayTomTat'          => now(),
            'TrangThai'           => 'Đã tạo',
        ];
    }

    // ─── Rule-based Mock (Fallback) ───────────────────────────────────────────

    private function generateMock(BaoCaoTienDo $baoCao): array
    {
        $lan     = $baoCao->LanBaoCao;
        $tieuDe  = $baoCao->TieuDe ?? "Báo cáo mốc {$lan}";
        $noiDung = $baoCao->NoiDungBaoCao ?? '';
        $coFile  = !empty($baoCao->DuongDanFile);
        $coGit   = !empty($baoCao->LinkCode);

        $map = [
            1 => [
                'cong_viec' => "Hoàn thành phân tích yêu cầu và xây dựng đề cương khóa luận. Xác định phạm vi nghiên cứu, mục tiêu và phương pháp thực hiện. " . ($coFile ? "Đã nộp tài liệu PDF đề cương đầy đủ." : ""),
                'kho_khan'  => "Gặp khó khăn trong việc thu hẹp phạm vi đề tài và xác định tính khả thi. Đã điều chỉnh một số mục tiêu cho phù hợp với thời gian.",
                'ke_hoach'  => "Triển khai nghiên cứu lý thuyết chuyên sâu, thu thập tài liệu và bắt đầu thiết kế kiến trúc hệ thống.",
            ],
            2 => [
                'cong_viec' => "Hoàn thành nghiên cứu lý thuyết và thiết kế kiến trúc tổng thể. Xây dựng cơ sở dữ liệu và thiết kế giao diện mẫu. " . ($coFile ? "Đã nộp báo cáo thiết kế hệ thống PDF." : ""),
                'kho_khan'  => "Gặp khó khăn khi lựa chọn công nghệ phù hợp và thiết kế CSDL chuẩn hóa. Đã tham khảo GVHD và điều chỉnh thiết kế.",
                'ke_hoach'  => "Bắt đầu triển khai lập trình module đầu tiên theo thiết kế đã xác nhận.",
            ],
            3 => [
                'cong_viec' => "Hoàn thành cài đặt các chức năng cốt lõi (≥60% tính năng). Thực hiện kiểm thử đơn vị cho các module chính. " . ($coFile ? "Đã nộp báo cáo tiến độ lập trình kèm ảnh màn hình." : ""),
                'kho_khan'  => "Gặp lỗi khi tích hợp giữa các module và xử lý dữ liệu biên. Đã sửa và kiểm thử lại thành công.",
                'ke_hoach'  => "Hoàn thiện toàn bộ tính năng còn lại, viết tài liệu hướng dẫn và chuẩn bị demo.",
            ],
            4 => [
                'cong_viec' => "Hoàn thành toàn bộ hệ thống và đẩy code lên repository. " . ($coGit ? "Link GitHub/GitLab đã cập nhật commit mới nhất. " : "") . "Hệ thống hoạt động ổn định trên môi trường local.",
                'kho_khan'  => "Gặp khó khăn trong tối ưu hiệu năng và xử lý các trường hợp biên. Đã cải thiện qua code review.",
                'ke_hoach'  => "Viết hoàn chỉnh báo cáo khóa luận, chuẩn bị slide và tài liệu bảo vệ.",
            ],
            5 => [
                'cong_viec' => "Hoàn thành toàn bộ khóa luận: phần mềm, báo cáo đầy đủ và slide thuyết trình. " . ($coFile ? "Đã nộp báo cáo hoàn chỉnh PDF. " : "") . ($coGit ? "Kèm link repository cuối cùng." : ""),
                'kho_khan'  => "Gặp khó khăn trong hoàn thiện phần viết báo cáo đúng định dạng học thuật. Đã tham khảo mẫu và chỉnh sửa hoàn chỉnh.",
                'ke_hoach'  => "Chuẩn bị bảo vệ: ôn luyện thuyết trình, chuẩn bị câu trả lời cho các câu hỏi dự kiến.",
            ],
        ];

        $data = $map[$lan] ?? [
            'cong_viec' => "Hoàn thành các công việc theo kế hoạch của mốc {$lan}.",
            'kho_khan'  => "Gặp một số khó khăn trong quá trình thực hiện và đã xử lý.",
            'ke_hoach'  => "Tiếp tục thực hiện các bước tiếp theo trong kế hoạch.",
        ];

        if (!empty($noiDung)) {
            $data['cong_viec'] .= " Ghi chú sinh viên: " . mb_substr($noiDung, 0, 200);
        }

        return [
            'CongViecDaHoanThanh' => $data['cong_viec'],
            'KhoKhan'             => $data['kho_khan'],
            'KeHoachTuanToi'      => $data['ke_hoach'],
            'NoiDungAI'           => "📋 **TÓM TẮT MỐC {$lan}: {$tieuDe}**\n\n✅ **Đã hoàn thành:** {$data['cong_viec']}\n\n⚠️ **Khó khăn:** {$data['kho_khan']}\n\n📅 **Kế hoạch tiếp theo:** {$data['ke_hoach']}",
            'DoTinCayAI'          => 70.00 + ($lan * 2), // Mock → độ tin cậy thấp hơn
            'NgayTomTat'          => now(),
            'TrangThai'           => 'Đã tạo',
        ];
    }
}
