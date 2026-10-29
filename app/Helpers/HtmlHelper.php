<?php

namespace App\Helpers;

class HtmlHelper
{
    /**
     * Danh sách các thẻ HTML an toàn cho phép trong Rich Text
     */
    protected static array $allowedTags = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
        'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'span', 'div', 'blockquote', 'hr', 'a'
    ];

    /**
     * Làm sạch HTML, loại bỏ triệt để mã XSS độc hại (script, iframe, onerror, javascript:...)
     * Đồng thời tự động chuẩn hóa văn bản thuần cũ (plain text) có dấu xuống dòng \n thành đoạn HTML hợp lệ.
     */
    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $raw = trim($html);

        // 1. Loại bỏ các khối UI toolbar/footer của rich text editor lọt vào nội dung (ngăn chặn lỗi lồng editor)
        $raw = preg_replace('/<div[^>]*class=["\'][^"\']*(rte-toolbar|rte-footer)[^"\']*["\'][^>]*>[\s\S]*?<\/div>/i', '', $raw);

        // 2. Nếu nội dung là văn bản thuần không chứa thẻ HTML, chuyển đổi \n thành <p> và <br>
        if (!preg_match('/<[a-z][\s\S]*>/i', $raw)) {
            $paragraphs = preg_split('/\r\n\r\n|\n\n/', $raw);
            $formatted = '';
            foreach ($paragraphs as $p) {
                $trimmed = trim($p);
                if ($trimmed !== '') {
                    $formatted .= '<p>' . nl2br(e($trimmed)) . '</p>';
                }
            }
            return $formatted ?: nl2br(e($raw));
        }

        // 3. Loại bỏ các thẻ nguy hiểm và nội dung bên trong chúng (script, iframe, style, applet, object...)
        $sanitized = preg_replace('/<(script|style|iframe|object|embed|applet)[\s\S]*?<\/\1>/i', '', $raw);
        $sanitized = preg_replace('/<(script|style|iframe|object|embed|applet)[\s\S]*?>/i', '', $sanitized);

        // 4. Sử dụng strip_tags để chỉ giữ lại các thẻ an toàn trong $allowedTags (không bao gồm img)
        $allowedTagString = '<' . implode('><', self::$allowedTags) . '>';
        $sanitized = strip_tags($sanitized, $allowedTagString);

        // 5. Loại bỏ các thuộc tính nguy hiểm: on* (onclick, onerror...), javascript:, vbscript:
        $sanitized = preg_replace('/\s+on[a-z]+\s*=\s*(["\'][^"\']*["\']|[^\s>]+)/i', '', $sanitized);

        // 6. Loại bỏ href/src chứa javascript:, vbscript:, data:
        $sanitized = preg_replace('/\s+(href|src)\s*=\s*(["\']?\s*(javascript|vbscript|data):[^"\'>]*["\']?)/i', '', $sanitized);

        // 7. Chuẩn hóa thuộc tính các thẻ: chỉ cho phép text-align trong style, an toàn cho thẻ <a>
        $sanitized = preg_replace_callback('/<([a-z0-9]+)\s+([^>]+)>/i', function ($matches) {
            $tag = strtolower($matches[1]);
            $attrs = $matches[2];

            if ($tag === 'a') {
                $extra = '';
                if (preg_match('/href\s*=\s*["\']([^"\']*)["\']/i', $attrs, $hm)) {
                    $url = trim($hm[1]);
                    if (preg_match('/^(https?:\/\/|mailto:|\/)/i', $url)) {
                        $extra = ' href="' . e($url) . '" target="_blank" rel="noopener noreferrer"';
                    }
                }
                return "<a{$extra}>";
            }

            // Trích xuất text-align nếu có cho các block văn bản
            $styleAttr = '';
            if (preg_match('/style\s*=\s*["\']([^"\']*)["\']/i', $attrs, $sm)) {
                $styleContent = $sm[1];
                if (preg_match('/text-align\s*:\s*(left|center|right|justify)/i', $styleContent, $tm)) {
                    $align = strtolower($tm[1]);
                    $styleAttr = " style=\"text-align: {$align};\"";
                }
            }

            return "<{$tag}{$styleAttr}>";
        }, $sanitized);

        return trim($sanitized);
    }

    /**
     * Render an toàn nội dung Rich Text:
     * - Tự động giải mã nếu nội dung bị mã hóa thực thể HTML (&lt;p&gt;...).
     * - Làm sạch XSS triệt để, giữ lại các thẻ an toàn.
     * - Tự động chuyển đổi xuống dòng cho văn bản thuần nếu không có thẻ HTML.
     */
    public static function renderRichText(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $raw = trim($html);

        // Trường hợp nội dung bị mã hóa entity (ví dụ: &lt;p&gt;...)
        if (preg_match('/&lt;[a-z\/]/i', $raw) || (stripos($raw, '&lt;') !== false && stripos($raw, '<') === false)) {
            $raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return self::clean($raw);
    }

    /**
     * Chuyển đổi mã HTML/Rich Text thành văn bản thuần hoàn chỉnh (dùng cho textarea, export, không giới hạn độ dài):
     * - Giữ nguyên các ký tự ngắt dòng \n từ <p>, <div>, <br>, <li>, v.v.
     * - Loại bỏ hoàn toàn các thẻ HTML và giải mã các thực thể HTML.
     * - KHÔNG bị cắt ngắn ký tự.
     */
    public static function toPlainText(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $content = trim($html);

        // Nếu nội dung bị mã hóa entity, giải mã trước
        if (preg_match('/&lt;[a-z\/]/i', $content) || (stripos($content, '&lt;') !== false && stripos($content, '<') === false)) {
            $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // Chuyển đổi ngắt dòng và thẻ khối thành ký tự xuống dòng \n
        $content = preg_replace('/<(br|hr)\s*\/?>/i', "\n", $content);
        $content = preg_replace('/<\/(p|div|h[1-6]|li|tr|blockquote)>/i', "\n", $content);

        // Bỏ tất cả các thẻ còn lại
        $content = strip_tags($content);

        // Giải mã các entity còn lại
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $content = str_replace(["\xc2\xa0", '&nbsp;'], ' ', $content);

        // Chuẩn hóa ngắt dòng
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        // Thu gọn 3 dòng trống trở lên thành tối đa 2 dòng trống
        $content = preg_replace("/\n{3,}/", "\n\n", $content);

        return trim($content);
    }

    /**
     * Rút gọn nội dung Rich Text thành văn bản thuần an toàn cho việc hiển thị tóm tắt/tiêu đề
     */
    public static function plainText(?string $html, int $limit = 100): string
    {
        if (empty($html)) {
            return '';
        }

        $plain = strip_tags(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $plain = preg_replace('/\s+/', ' ', $plain);
        $plain = trim($plain);

        return \Illuminate\Support\Str::limit($plain, $limit);
    }
}
