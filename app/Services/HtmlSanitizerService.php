<?php

namespace App\Services;

class HtmlSanitizerService
{
    /**
     * Danh sách thẻ HTML an toàn được phép sử dụng trong văn bản mô tả / nội dung đề tài
     */
    protected const ALLOWED_TAGS = '<p><br><br/><ul><ol><li><strong><b><em><i><u><s><span><div><blockquote><h1><h2><h3><h4><h5><h6>';

    /**
     * Làm sạch và chuẩn hóa nội dung mô tả đề tài:
     * - Hỗ trợ cả văn bản thuần (giữ nguyên ngắt dòng nl2br) và HTML phong phú.
     * - Làm sạch XSS, loại bỏ hoàn toàn thẻ script, style, iframe, thuộc tính onclick, onload...
     * - Chuẩn hóa các danh sách <li> bị mồ côi (chưa bọc trong <ul>/<ol>) hoặc có <ul> rỗng.
     * - Ngăn ngừa tràn khung và vỡ layout giao diện.
     *
     * @param string|null $content
     * @return string
     */
    public static function cleanDescription(?string $content): string
    {
        if ($content === null || trim($content) === '') {
            return '';
        }

        $raw = trim($content);

        // Trường hợp nội dung bị mã hóa entity (ví dụ: &lt;li&gt;...)
        if (stripos($raw, '&lt;') !== false && stripos($raw, '<') === false) {
            $raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // Kiểm tra xem nội dung có chứa thẻ HTML hay là văn bản thuần
        $hasHtml = (strip_tags($raw) !== $raw);

        // NẾU LÀ VĂN BẢN THUẦN (Plain text)
        if (!$hasHtml) {
            return nl2br(e($raw));
        }

        // NẾU CÓ CHỨA HTML: XỬ LÝ LÀM SẠCH VÀ CHUẨN HÓA BẢO MẬT (XSS PROTECTION)

        // 1. Loại bỏ triệt để các khối mã độc script, style, iframe, object, embed và nội dung bên trong chúng
        $sanitized = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $raw);
        $sanitized = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $sanitized);
        $sanitized = preg_replace('/<(?:iframe|object|embed|applet|form|input|button|svg|canvas)\b[^>]*>(.*?)<\/(?:iframe|object|embed|applet|form|input|button|svg|canvas)>/is', '', $sanitized);

        // 2. Chỉ giữ lại các thẻ HTML an toàn được cho phép (Whitelist tags)
        $sanitized = strip_tags($sanitized, self::ALLOWED_TAGS);

        // 3. Loại bỏ mọi sự kiện JavaScript nội dòng (inline event handlers: onclick, onerror, onload...)
        $sanitized = preg_replace('/\s*on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $sanitized);

        // 4. Loại bỏ các giao thức nguy hiểm trong href/src nếu có
        $sanitized = preg_replace('/(?:\bhref|\bsrc)\s*=\s*["\']?\s*(?:javascript|data|vbscript):/i', '', $sanitized);

        // 5. Dọn dẹp các thẻ danh sách rỗng (như <ul></ul> hoặc <ol></ol>)
        $sanitized = preg_replace('/<ul[^>]*>\s*<\/ul>/i', '', $sanitized);
        $sanitized = preg_replace('/<ol[^>]*>\s*<\/ol>/i', '', $sanitized);

        // 6. Xử lý danh sách <li> mồ côi: Tự động bọc chuỗi các thẻ <li> đứng độc lập vào trong <ul>...</ul>
        $sanitized = preg_replace_callback('/(<(?:ul|ol)[^>]*>.*?<\/(?:ul|ol)>)|((?:<li\b[^>]*>.*?<\/li>\s*)+)/is', function ($matches) {
            if (!empty($matches[1])) {
                return $matches[1]; // Đã nằm trong <ul> hoặc <ol> hợp lệ
            }
            return '<ul>' . trim($matches[2]) . '</ul>';
        }, $sanitized);

        // 7. Chuyển đổi các dấu xuống dòng thực tế (\n) giữa các đoạn văn bản nằm ngoài thẻ khối thành <br>
        $sanitized = preg_replace('/(?<!>)\r?\n(?!<)/', '<br>', $sanitized);

        return trim($sanitized);
    }
}
