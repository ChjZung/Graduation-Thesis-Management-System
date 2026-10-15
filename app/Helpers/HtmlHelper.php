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
        'span', 'div', 'blockquote', 'hr'
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

        // 1. Nếu nội dung là văn bản thuần không chứa thẻ HTML, chuyển đổi \n thành <p> và <br>
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

        // 2. Loại bỏ các thẻ nguy hiểm và nội dung bên trong chúng (script, iframe, style, applet, object...)
        $sanitized = preg_replace('/<(script|style|iframe|object|embed|applet)[\s\S]*?<\/\1>/i', '', $raw);
        $sanitized = preg_replace('/<(script|style|iframe|object|embed|applet)[\s\S]*?>/i', '', $sanitized);

        // 3. Sử dụng strip_tags để chỉ giữ lại các thẻ an toàn trong $allowedTags
        $allowedTagString = '<' . implode('><', self::$allowedTags) . '>';
        $sanitized = strip_tags($sanitized, $allowedTagString);

        // 4. Loại bỏ các thuộc tính nguy hiểm: on*, javascript:, data:text/html, vbscript:
        // Loại bỏ các sự kiện inline: onclick, onerror, onload, onmouseover...
        $sanitized = preg_replace('/\s+on[a-z]+\s*=\s*(["\'][^"\']*["\']|[^\s>]+)/i', '', $sanitized);

        // Loại bỏ href/src chứa javascript:, vbscript:, data:
        $sanitized = preg_replace('/\s+(href|src)\s*=\s*(["\']?\s*(javascript|vbscript|data):[^"\'>]*["\']?)/i', '', $sanitized);

        // 5. Chuẩn hóa thuộc tính style: chỉ cho phép text-align
        $sanitized = preg_replace_callback('/<([a-z0-9]+)\s+([^>]+)>/i', function ($matches) {
            $tag = strtolower($matches[1]);
            $attrs = $matches[2];

            // Trích xuất text-align nếu có
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
