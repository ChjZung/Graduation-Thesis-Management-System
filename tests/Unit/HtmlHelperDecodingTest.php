<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Helpers\HtmlHelper;

class HtmlHelperDecodingTest extends TestCase
{
    private function visibleText(string $html): string
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?><div id="content">'.$html.'</div>');
        return $dom->getElementById('content')->textContent;
    }

    public function test_clean_legacy_text_preserves_entities_without_double_encoding(): void
    {
        $html = HtmlHelper::clean("Nghiên cứu &amp; phát triển&nbsp;ứng dụng\nYêu cầu: tiếng Việt, \"dấu nháy\".");
        $this->assertStringNotContainsString('&amp;amp;', $html);
        $this->assertStringNotContainsString('&amp;nbsp;', $html);
        $this->assertStringContainsString('<br', $html);
        $this->assertStringContainsString("Nghiên cứu & phát triển\u{00a0}ứng dụng", $this->visibleText($html));
        $this->assertStringContainsString('"dấu nháy"', $this->visibleText($html));
    }

    public function test_clean_edit_save_cycles_do_not_add_entity_layers(): void
    {
        $html = HtmlHelper::clean("R&D &amp; dữ liệu&nbsp;Việt Nam\nDòng tiếp theo");
        $expected = $this->visibleText($html);
        for ($i = 0; $i < 4; $i++) {
            $html = HtmlHelper::clean($html);
            $this->assertSame($expected, $this->visibleText($html));
        }
    }

    public function test_clean_does_not_turn_encoded_markup_into_executable_html(): void
    {
        $html = HtmlHelper::clean('&lt;script&gt;alert(1)&lt;/script&gt; &amp; ví dụ');
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringContainsString('<script>alert(1)</script> & ví dụ', $this->visibleText($html));
        $clean = HtmlHelper::clean('<p onclick="alert(1)">Nội dung &amp; an toàn</p><script>alert(1)</script>');
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertSame('Nội dung & an toàn', $this->visibleText($clean));
    }

    public function test_clean_does_not_recursively_decode_literal_entity_examples(): void
    {
        $this->assertSame('Ví dụ &amp; trong HTML', $this->visibleText(HtmlHelper::clean('Ví dụ &amp;amp; trong HTML')));
    }

    public function test_render_rich_text_decodes_double_encoded_entities()
    {
        $raw = '<p>Công Nghệ Phần Mềm &amp;amp; Trí Tuệ ong cha&amp;nbsp;với ứng dụng AI</p>';
        $clean = HtmlHelper::renderRichText($raw);

        $this->assertStringNotContainsString('&amp;amp;', $clean);
        $this->assertStringNotContainsString('&amp;nbsp;', $clean);
        $this->assertStringContainsString('Công Nghệ Phần Mềm & Trí Tuệ ong cha', $clean);
        $this->assertStringContainsString('<p>', $clean);
        $this->assertStringContainsString('</p>', $clean);
    }

    public function test_to_plain_text_strips_html_and_decodes_entities()
    {
        $raw = '<p>Phân hệ 1: Quản lý hàng hóa</p><p>Tiếp nhận &amp;amp; xử lý đơn&amp;nbsp;hàng</p>';
        $plain = HtmlHelper::toPlainText($raw);

        $this->assertStringNotContainsString('<p>', $plain);
        $this->assertStringNotContainsString('</p>', $plain);
        $this->assertStringNotContainsString('&amp;amp;', $plain);
        $this->assertStringNotContainsString('&amp;nbsp;', $plain);
        $this->assertStringNotContainsString('&nbsp;', $plain);
        $this->assertStringContainsString("Phân hệ 1: Quản lý hàng hóa\nTiếp nhận & xử lý đơn hàng", $plain);
    }

    public function test_legacy_double_escaped_html_tags_are_restored_cleanly()
    {
        $raw = '&lt;p&gt;Mô tả &amp;amp; Tính năng&lt;/p&gt;';
        $clean = HtmlHelper::renderRichText($raw);

        $this->assertStringNotContainsString('&lt;p&gt;', $clean);
        $this->assertStringNotContainsString('&amp;amp;', $clean);
        $this->assertStringContainsString('<p>', $clean);
        $this->assertStringContainsString('Mô tả & Tính năng', $clean);
    }
}
