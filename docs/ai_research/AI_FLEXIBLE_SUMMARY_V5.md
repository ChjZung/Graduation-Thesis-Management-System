# Tóm tắt linh hoạt theo yêu cầu giảng viên — V5

Phiên bản này tiếp nối [Map–Reduce V4](AI_MAP_REDUCE_V4.md) và thay cách hiển thị ba nhóm cố định. Ba nhóm giảng viên gợi ý là ví dụ, không phải cấu trúc bắt buộc. Không ép báo cáo phần mềm, nghiên cứu AI hay khóa luận học thuật về cùng 3/7/11 mục.

## Hành vi

1. **Tổng quan tài liệu**: giới thiệu ngắn nội dung toàn báo cáo, có nguồn.
2. **Các mục trọng tâm**: tên và số mục tùy nội dung PDF và yêu cầu cụ thể của giảng viên. Tên giai đoạn/đợt bổ sung ngữ cảnh; số thứ tự giai đoạn không quyết định phải khảo sát, thiết kế hay thực nghiệm.
3. **Nội dung khác trong tài liệu**: mặc định thu gọn, chứa những ý quan trọng ngoài yêu cầu đợt nộp. Không dùng những mục này làm tiêu chí bắt buộc.
4. **Đối chiếu yêu cầu**: giữ riêng bên dưới. Đọc lại văn bản gốc để tìm bằng chứng, không suy kết luận từ các mục tóm tắt. Giữ các trạng thái đáp ứng, đáp ứng một phần, chưa tìm thấy và chưa đủ bằng chứng.

Không tạo thẻ rỗng hoặc câu “báo cáo thiếu mục X” chỉ để đủ nhóm. Khi không có nội dung trọng tâm được trích xuất, hiển thị giới hạn và phần đối chiếu, không tự kết luận sinh viên chưa làm.

Yêu cầu trống → `document_fallback` được máy chủ áp dụng. Yêu cầu chung chung hoặc mâu thuẫn → prompt yêu cầu mô hình trả `document_fallback` kèm giải thích; nhận diện ngữ nghĩa trường hợp này vẫn phụ thuộc mô hình. Không tự đặt thêm bộ tiêu chí theo loại khóa luận. Khi API lỗi, chỉ giữ nguồn đã kiểm tra hoặc đoạn trích cục bộ, ghi rõ chưa hoàn tất.

## Luồng thực thi

`AiReportProcessingService.php` → job `AnalyzeReportVersion` → `AiSummaryService.php` → `ai_summarizer.py` → **`ai_flexible_summary.py`**.

- PyMuPDF và bộ phân đoạn có cấu trúc hiện có tiếp tục giữ trang vật lý, khoảng ký tự nguồn và overlap. DOCX giữ đoạn/bảng, không giả số trang Word.
- Map độc lập với yêu cầu đợt nộp, thu thập các ý có nguồn từ từng chunk. Tối đa 12 ý/chunk; kiểm tra nguyên văn trong đúng chunk và trang được nêu.
- Reduce nhiều tầng nhận ngữ cảnh giảng viên ở **mọi tầng**. Mô hình chỉ chọn mã nguồn để tổ chức tổng quan, mục trọng tâm và mục khác. Máy chủ sao chép nội dung, tính chất ý, câu trích và trang từ nguồn đã kiểm tra.
- Không có ý nghĩa “bước Reduce xác nhận chất lượng thực nghiệm”: `reported_result` chỉ có nghĩa tác giả báo cáo kết quả đó. Việc mô hình phân loại đúng và tóm tắt đúng ý vẫn cần đánh giá thực nghiệm.
- Đối chiếu riêng dùng `ai_requirement_review.py`, truy tìm văn bản gốc và kiểm chứng nguồn/mức liên quan bằng `ai_citation_audit.py`.
- Lưu JSON theo từng phiên bản bài nộp. Blade dùng schema 5 cho giao diện linh hoạt; schema lịch sử vẫn đọc được.

`ai_summary_hierarchy.py` tiếp tục cung cấp bộ phân đoạn và ước lượng token. Hàm `run_legacy_map_reduce_pipeline` giữ triển khai cũ để tương thích và so sánh; entrypoint CLI và web gọi pipeline V5.

## Hợp đồng dữ liệu

Map do mô hình trả:

```json
{"items":[{"topic":"Mô hình đề xuất","kind":"proposal","content":"Nhóm đề xuất mô hình Transformer.","evidence":"Nhóm đề xuất mô hình Transformer cho bài toán phân loại văn bản.","source_pages":[16]}]}
```

Sau kiểm tra, máy chủ thêm `source_ids: ["F2_1"]`, `source_chunk_ids` và `verified`. Các loại ý: `background`, `requirement`, `proposal`, `method`, `reported_work`, `reported_result`, `limitation`, `future_work`, `other`. Đây là thuộc tính từng ý, **không phải danh sách mục bắt buộc**. Giao diện hiển thị nhãn tính chất để phân biệt đề xuất, kế hoạch và kết quả được báo cáo.

Reduce do mô hình trả:

```json
{
  "overview_source_ids":["F1_1"],
  "scope":{"mode":"focused","note":"Trọng tâm mô hình và thiết kế thực nghiệm theo yêu cầu."},
  "summary_sections":[{"title":"Mô hình và thiết kế thực nghiệm","source_ids":["F2_1","F3_1"]}],
  "additional_sections":[{"title":"Bối cảnh và khảo sát","source_ids":["F1_1"]}]
}
```

Máy chủ giải mã thành `summary_sections: [{id,title,items}]`, `additional_sections`, `document_content` và `summary_scope: {mode,note,context_hash}`; thêm `schema_version: 5` cùng metadata độ bao phủ. Một ý không được lặp trong hai mục; nguồn lạ và JSON sai bị từ chối. Tổng quan có thể tham chiếu cùng nguồn với một mục chi tiết.

Các trường cũ `completed_work/difficulties/next_steps` được giữ để tương thích snapshot/export cũ, không quyết định cấu trúc giao diện V5. Danh sách difficulties tương thích hiện để trống vì một hạn chế nghiên cứu không mặc nhiên là khó khăn thực hiện của nhóm.

Ví dụ đầu ra theo hai yêu cầu khác nhau: [JSON minh họa](flexible_summary_v5_examples.json). Dữ liệu này là fixture tổng hợp với API giả lập, không phải kết quả đánh giá Gemini.

## Cache, giới hạn và thao tác

- Namespace V5 tránh dùng nhầm JSON V4. Cache theo nội dung file, prompt, model và hash key; không ghi key thô vào chẩn đoán.
- Cùng file nhưng đổi yêu cầu: dùng lại trích xuất và Map hợp lệ, chạy lại Reduce theo trọng tâm mới và đối chiếu. Nếu chọn “Đối chiếu lại” khi ngữ cảnh V5 đã đổi, job tự chuyển sang xử lý đầy đủ. Giao diện đánh dấu kết quả theo yêu cầu cũ.
- Mỗi batch Reduce mặc định tối đa 6.500 token **ước lượng của dữ liệu cộng ngữ cảnh**, chưa tính toàn bộ chỉ dẫn. Tối đa 12 tầng. Nếu đầu vào không vừa, không thu gọn hoặc API lỗi, giữ các ý nguồn và đánh dấu chưa hoàn tất.
- Quota, giới hạn đồng thời, thời gian mỗi lượt nền và lịch thử lại dùng cơ chế hiện có. Không tự đổi key, model hay bỏ qua cooldown.
- Trang có ảnh/scan/sơ đồ chưa đọc được được ghi rõ. Chưa bổ sung OCR/vision; không cam kết đã hiểu mọi bảng hoặc ảnh chỉ vì đã trích được văn bản.
- Popup giữ kiểm tra phiên, quyền và tệp; mở trang nguồn, tô vàng khi khớp. Kiểm tra câu trích không tương đương kiểm chứng nội dung luận văn là sự thật.
- Không cần thay schema database. Báo cáo đã lưu không tự chạy lại hàng loạt. Chọn **Phân tích lại** trên bài muốn cập nhật, worker cần chạy bằng `php artisan ai:work` hoặc `CHAY_AI.bat`.

## Kiểm thử và thực nghiệm

Kết quả xác minh cục bộ: **110 kiểm thử Python; 34 kiểm thử PHP với 102 assertions**. Hai kích thước giao diện và 6 tình huống lỗi popup đã qua kiểm tra.

Kiểm thử Python gồm: cùng PDF đổi yêu cầu và tái sử dụng Map, nội dung nghiên cứu AI không bị ép thành website, số giai đoạn không áp cấu trúc, yêu cầu trống/chung chung/mâu thuẫn, không có mục trọng tâm, giữ tính chất đề xuất/kế hoạch/kết quả, sai nguồn/trang, Reduce lỗi và nhiều tầng. Kiểm thử PHP kiểm tra lưu schema, giữ snapshot phù hợp ngữ cảnh, hàng đợi và lỗi Python. Các kiểm thử dùng API giả lập, không đo năng lực ngữ nghĩa Gemini.

Giao diện được render với Blade thật rồi kiểm tra bằng trình duyệt headless ở 390px và 1440px: mục động, phần khác thu gọn, nhãn loại ý, cảnh báo ngữ cảnh cũ, mở trích dẫn từ popup xem lớn và không tràn ngang. Tình huống lỗi popup được kiểm tra riêng.

Hai PDF thực tế 62 và 72 trang được kiểm tra trích xuất/phân đoạn và chế độ giữ đoạn trích khi không gọi API. [Kết quả kiểm tra cục bộ](flexible_summary_v5_local_checks.json) ghi rõ số trang/chunk, nguồn khớp và file gốc không thay đổi. Đây **không phải** phép đo độ chính xác tóm tắt thực tế của Gemini.

Để nghiệm thu nội dung thật: giảng viên chuẩn bị đáp án tiêu chí → câu nguyên văn → trang → kết luận; chạy một báo cáo khảo sát, một báo cáo thực nghiệm và một tài liệu có phần thiếu. Ghi ý chính bị bỏ sót, ý không có nguồn, trích dẫn đúng trang/đúng ngữ cảnh, thời gian, số lượt API và tỷ lệ dùng cache. Chỉ công bố độ chính xác sau khi chấm bộ đáp án.

Sơ đồ hoạt động: [PlantUML](ai_flexible_summary_activity.puml), [SVG](ai_flexible_summary_activity.svg).
