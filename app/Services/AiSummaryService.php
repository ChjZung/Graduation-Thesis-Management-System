<?php

namespace App\Services;

use App\Models\BaoCaoTienDo;

class AiSummaryService
{
    /**
     * Tự động phân tích bài nộp và sinh bản tóm tắt AI.
     * Hiện tại dùng mock/rule-based. Có thể tích hợp OpenAI API sau này.
     */
    public function generate(BaoCaoTienDo $baoCao): array
    {
        $lan = $baoCao->LanBaoCao;
        $tieuDe = $baoCao->TieuDe ?? "Báo cáo mốc {$lan}";
        $noiDung = $baoCao->NoiDungBaoCao ?? '';
        $coFile = !empty($baoCao->DuongDanFile);
        $coGit = !empty($baoCao->LinkCode);

        // Sinh tóm tắt theo từng mốc
        $map = [
            1 => [
                'cong_viec' => "Hoàn thành phân tích yêu cầu và xây dựng đề cương khóa luận. Xác định phạm vi nghiên cứu, mục tiêu và phương pháp thực hiện. " . ($coFile ? "Đã nộp tài liệu PDF đề cương đầy đủ." : ""),
                'kho_khan' => "Gặp khó khăn trong việc thu hẹp phạm vi đề tài và xác định tính khả thi. Cần điều chỉnh một số mục tiêu cho phù hợp với thời gian thực hiện.",
                'ke_hoach' => "Triển khai nghiên cứu lý thuyết chuyên sâu, thu thập tài liệu liên quan và bắt đầu thiết kế kiến trúc hệ thống.",
            ],
            2 => [
                'cong_viec' => "Hoàn thành nghiên cứu lý thuyết và thiết kế kiến trúc tổng thể. Xây dựng cơ sở dữ liệu và thiết kế giao diện mẫu (prototype). " . ($coFile ? "Đã nộp báo cáo thiết kế hệ thống dạng PDF." : ""),
                'kho_khan' => "Gặp khó khăn khi lựa chọn công nghệ phù hợp và thiết kế cơ sở dữ liệu chuẩn hóa. Đã tham khảo giảng viên hướng dẫn và điều chỉnh thiết kế.",
                'ke_hoach' => "Bắt đầu triển khai lập trình module đầu tiên theo thiết kế đã được xác nhận.",
            ],
            3 => [
                'cong_viec' => "Hoàn thành cài đặt các chức năng cốt lõi của hệ thống (ít nhất 60% tính năng). Thực hiện kiểm thử đơn vị (unit testing) cho các module chính. " . ($coFile ? "Đã nộp báo cáo tiến độ lập trình kèm ảnh chụp màn hình." : ""),
                'kho_khan' => "Gặp lỗi khi tích hợp giữa các module và xử lý dữ liệu biên. Đã sửa và kiểm thử lại thành công.",
                'ke_hoach' => "Hoàn thiện toàn bộ tính năng còn lại, viết tài liệu hướng dẫn sử dụng và chuẩn bị demo.",
            ],
            4 => [
                'cong_viec' => "Hoàn thành toàn bộ hệ thống và đẩy code lên repository. " . ($coGit ? "Link GitHub/GitLab đã được cập nhật với commit mới nhất." : "") . " Hệ thống đã hoạt động ổn định trên môi trường local.",
                'kho_khan' => "Gặp khó khăn trong tối ưu hiệu năng và xử lý các trường hợp biên. Đã cải thiện thông qua code review và refactoring.",
                'ke_hoach' => "Viết hoàn chỉnh báo cáo khóa luận, chuẩn bị slide thuyết trình và tài liệu bảo vệ.",
            ],
            5 => [
                'cong_viec' => "Hoàn thành toàn bộ khóa luận bao gồm: hệ thống phần mềm, báo cáo đầy đủ và slide thuyết trình. " . ($coFile ? "Đã nộp báo cáo hoàn chỉnh dạng PDF." : "") . ($coGit ? " Kèm link repository cuối cùng." : ""),
                'kho_khan' => "Gặp khó khăn trong việc hoàn thiện phần viết báo cáo đúng định dạng và chuẩn học thuật. Đã tham khảo các khóa luận mẫu và chỉnh sửa hoàn chỉnh.",
                'ke_hoach' => "Chuẩn bị cho buổi bảo vệ trước Hội đồng: ôn luyện thuyết trình, chuẩn bị câu trả lời cho các câu hỏi dự kiến.",
            ],
        ];

        $data = $map[$lan] ?? [
            'cong_viec' => "Hoàn thành các công việc theo kế hoạch của mốc {$lan}.",
            'kho_khan' => "Gặp một số khó khăn trong quá trình thực hiện và đã xử lý.",
            'ke_hoach' => "Tiếp tục thực hiện các bước tiếp theo trong kế hoạch.",
        ];

        // Tích hợp nội dung do sinh viên nhập vào nếu có
        if (!empty($noiDung)) {
            $data['cong_viec'] .= " Sinh viên ghi chú: " . mb_substr($noiDung, 0, 200) . (mb_strlen($noiDung) > 200 ? '...' : '');
        }

        return [
            'CongViecDaHoanThanh' => $data['cong_viec'],
            'KhoKhan'             => $data['kho_khan'],
            'KeHoachTuanToi'      => $data['ke_hoach'],
            'NoiDungAI'           => "📋 **TÓM TẮT MỐC {$lan}: {$tieuDe}**\n\n✅ **Đã hoàn thành:** {$data['cong_viec']}\n\n⚠️ **Khó khăn:** {$data['kho_khan']}\n\n📅 **Kế hoạch tiếp theo:** {$data['ke_hoach']}",
            'DoTinCayAI'          => 85.00 + ($lan * 2), // Mock confidence score
            'NgayTomTat'          => now(),
            'TrangThai'           => 'Đã tạo',
        ];
    }
}
