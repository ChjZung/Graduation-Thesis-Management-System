# Tóm tắt báo cáo PDF theo Map–Reduce phân cấp

> Tài liệu lịch sử V4. Pipeline đang hoạt động dùng [V5 với các mục tóm tắt linh hoạt](AI_FLEXIBLE_SUMMARY_V5.md); không còn ép ba nhóm cố định.

## Phạm vi

Hệ thống tích hợp LLM API, không huấn luyện hay fine-tune mô hình. Các PDF khóa luận đã thực hiện là **dữ liệu thực nghiệm**. Giảng viên quyết định đánh giá cuối cùng. Đối chiếu tài liệu không chứng minh phần mềm hoặc repository thực tế chạy đúng.

## Hiện trạng và thay đổi

| Phần | Trước | Sau |
|---|---|---|
| Trích xuất | PyMuPDF, số trang vật lý, phát hiện ảnh | Giữ thư viện; cache trích xuất theo SHA-256 nội dung trong thư mục riêng |
| Phân đoạn | Theo ngưỡng ký tự, có thể để một đoạn dài vượt ngưỡng | Ngắt theo chương/mục; chia cả dòng quá dài; giới hạn ký tự và token ước lượng; lưu khoảng ký tự nguồn, chồng lấn |
| Map | Có JSON và kiểm tra trang ở cuối pipeline | Kiểm tra câu trích ngay trong **chính chunk** và trang gốc, rồi gắn mã nguồn ổn định |
| Reduce | Một tầng trung gian và một tầng cuối | Đóng gói theo ngân sách đầu vào, lặp nhiều tầng có giới hạn; giữ nguồn và nhóm; không cho xóa hết một nhóm có thông tin |
| Lỗi | Giữ được một số kết quả Map | Giữ cả nguồn khi Reduce lỗi/không hội tụ; báo chưa hoàn tất; tiếp tục bằng checkpoint các lượt sau |
| Đối chiếu | Tìm toàn văn, kiểm chứng câu trích và mức liên quan | Ưu tiên thân mục liên quan; giảm ưu tiên câu giới thiệu cấu trúc chương; sửa riêng tối đa 3 tiêu chí lỗi/lượt |
| Giao diện | Tổng quan, ba nhóm tiến độ, đối chiếu và popup | Giữ giao diện hiện có; phân biệt đoạn liên quan và bằng chứng đã kiểm chứng; thông báo lỗi phiên/quyền/tệp/phản hồi không hợp lệ |

Không thay đổi dữ liệu bài nộp hay điểm. Không đồng bộ vào bản sao `ai_module/`: code được website gọi nằm trong `app/Services/` và `resources/views/partials/`.

## Luồng và file thực thi

1. Laravel lưu bài nộp, đưa công việc vào hàng đợi AI: `app/Services/AiReportProcessingService.php`.
2. `app/Services/AiSummaryService.php` chạy Python bằng cấu hình hiện tại; giữ model/API key và điều tiết quota.
3. `app/Services/ai_summarizer.py`: đọc PDF/DOCX, tái sử dụng cache trích xuất, chạy Map và gọi Reduce.
4. `app/Services/ai_summary_hierarchy.py`: phân đoạn, kiểm tra nguồn Map và tổng hợp nhiều tầng.
5. `app/Services/ai_requirement_review.py`: đọc lại **văn bản gốc** theo tên giai đoạn, tên đợt và yêu cầu, không chỉ dựa vào bản Reduce.
6. `app/Services/pdf_evidence_retrieval.py`, `ai_citation_audit.py`: tìm nguồn và kiểm chứng mức liên quan, giữ điều kiện/phủ định.
7. Laravel lưu JSON theo phiên bản bài nộp; `AiResultMerger.php` giữ kết quả hợp lệ cùng schema, không để schema cũ che kết quả mới.
8. `resources/views/partials/ai_summary_11_sections.blade.php` có tên file cũ nhưng hiển thị tổng quan và ba nhóm; đối chiếu nằm bên dưới. Popup vẫn dùng `pdf_citation_preview.py` để mở trang thật và tô vàng khi khớp.

## Hợp đồng JSON

Map từ chunk 2, trang 9 (nội dung dưới đây chỉ minh họa schema):

```json
{
  "document_content": [],
  "completed_work": [{
    "content": "Đã khảo sát hiện trạng",
    "evidence": "Nhóm đã khảo sát hiện trạng quản lý khóa luận.",
    "source_pages": [9],
    "source_chunk_ids": [2],
    "source_ids": ["C2_completed_work_1"],
    "verified": true
  }],
  "difficulties": [],
  "next_steps": []
}
```

LLM Map trả nội dung, câu trích và trang; máy chủ thêm mã nguồn sau khi kiểm tra. `verified` chỉ xác nhận câu khớp nguồn, **không phải xác suất đúng hoặc xác nhận đã đáp ứng yêu cầu**.

Reduce nhận các record trên, chọn ý đại diện và loại trùng. Mỗi record xuất ra sao chép nguồn cùng nhóm. Máy chủ từ chối nguồn lạ, đổi nhóm hoặc xóa toàn bộ nhóm có thông tin. Cách làm này ưu tiên tóm tắt có thể kiểm tra, hạn chế diễn đạt tự do. Không biến `next_steps` hay kiến thức nền thành `completed_work`.

JSON bài nộp có `schema_version: 4`, metadata `analysis` và ba nhóm chính:

- `completed_work`: Công việc hoàn thành.
- `difficulties`: Khó khăn gặp phải.
- `next_steps`: Kế hoạch tiếp theo.

`document_content`/`overall_summary` là tổng quan nội dung PDF, không phải nhóm tiến độ thứ tư. Nhóm thiếu thông tin giữ `[]`. Các trường `analysis.total_chunks`, `analyzed_chunks`, `reduce_completed`, `reduce_levels`, `errors` và `visual_unread_pages` giải thích độ bao phủ. Có ảnh không đồng nghĩa đã đọc được nội dung ảnh.

Đối chiếu có `schema_version: 3`, `context_hash`, `coverage`, `items` và `citation_audit`. Mỗi tiêu chí có:

```json
{
  "id": 1,
  "requirement": "Yêu cầu của giảng viên",
  "status": "met",
  "explanation": "Giải thích bằng chứng hỗ trợ yêu cầu",
  "evidence": [{
    "page": 9,
    "quote": "Câu nguyên văn",
    "verified": true,
    "relevance_verified": true,
    "supports": "Phần yêu cầu được câu này chứng minh"
  }]
}
```

Trạng thái: `met`, `partial`, `not_found`, `insufficient_evidence`. Không kết luận `not_found` khi chưa đủ bao phủ hoặc còn đoạn cần xem lại. Trích dẫn chưa qua kiểm chứng mức liên quan chỉ là đoạn tham khảo. JSON minh họa không phải kết quả thực nghiệm.

## Giới hạn và phục hồi

- Token dùng ước lượng cục bộ theo byte UTF-8, không phải số token do nhà cung cấp xác nhận. Phân đoạn mặc định giới hạn 12.000 ký tự/6.000 token ước lượng; dữ liệu mỗi batch Reduce tối đa 7.000 token ước lượng, còn phần chỉ dẫn bên ngoài.
- Reduce tối đa 12 tầng; nếu không thu gọn, đầu vào quá lớn hoặc API lỗi thì trả kết quả nguồn giữ lại và đánh dấu chưa hoàn tất, không cố gửi request không giới hạn.
- `ai_processing.py` giới hạn đồng thời 2 lượt, điều tiết theo quota đã cấu hình, tối đa 2 lần thử mỗi lời gọi có thể phục hồi; không gọi dồn khi quota/xác thực lỗi. Checkpoint riêng có thời hạn 3 ngày, không chứa key trong chẩn đoán.
- Đổi yêu cầu giảng viên chạy lại đối chiếu; cache trích xuất và các Map không phụ thuộc yêu cầu vẫn dùng lại khi nội dung/model/prompt không đổi. Đổi schema dùng namespace mới để tránh tái sử dụng kết quả sai hợp đồng.
- Worker vẫn cần chạy: `php artisan ai:work` hoặc `CHAY_AI.bat`. Tài liệu dài có thể cần nhiều lượt nền, mỗi lượt giữ các bước thành công.
- Chưa triển khai OCR/vision cho trang scan và sơ đồ. Cảnh báo minh bạch, không diễn giải ảnh thành kết luận thiếu nội dung.
- Không có cam kết LLM luôn đánh giá đúng. Khớp trích dẫn, kiểm chứng bằng LLM và giảng viên kiểm tra trực tiếp là các bước khác nhau.

## Thực nghiệm và nghiệm thu

Kết quả chạy: 100 kiểm thử Python; 29 kiểm thử PHP (87 assertions); 6 tình huống popup trong trình duyệt.

Kiểm thử tự động dùng API giả lập để kiểm tra lỗi quota/timeout/JSON, chống sai nguồn, nguồn xuyên nhiều tầng, nhóm rỗng, sửa riêng tiêu chí, quyền xem và popup. Không coi API giả lập là đánh giá chất lượng Gemini.

Kiểm tra cục bộ hai tệp đã nộp (xem `map_reduce_v4_local_checks.json`):

| PDF | Trang | Chunk | Ký tự không trắng bị bỏ sót | Gọi LLM |
|---|---:|---:|---:|---:|
| `baocao_dot26_v1_1792515385.pdf` | 62 | 30 | 0 | 0 |
| `baocao_dot17_v1_1792394248.pdf` | 72 | 16 | 0 | 0 |

Đây là kết quả kiểm tra **trích xuất/phân đoạn lớp văn bản**, không chứng minh đã hiểu hết ảnh, bảng hoặc tóm tắt đúng ngữ nghĩa. File gốc được kiểm tra SHA-256 trước/sau và không bị sửa.

Để đánh giá chất lượng thực tế, nhóm lập bộ đáp án thủ công: tiêu chí → câu gốc → trang → kết luận mong đợi. Chạy các PDF có nội dung/thiếu nội dung, sơ đồ/scan, báo cáo nhiều chương và câu trùng từ khóa nhưng sai ngữ cảnh. Ghi số yêu cầu đúng, câu trích đúng trang/ý nghĩa, nội dung chính bị bỏ sót, thông tin không có nguồn, thời gian, lượt API và cache hit. Không tự ghi tỷ lệ chính xác khi chưa chấm bộ đáp án này.

Sơ đồ hoạt động UML: [nguồn PlantUML](ai_map_reduce_activity.puml), [bản SVG xem nhanh](ai_map_reduce_activity.svg). Bản SVG trình bày các bước chính; bản PlantUML thể hiện vòng lặp và các nhánh chi tiết. Các sửa đổi kế tiếp nếu cần nên dựa trên bộ đáp án thực nghiệm, không điều chỉnh cố định trang hay nội dung riêng một PDF.
