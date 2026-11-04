<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Helpers\HtmlHelper;

class HtmlHelperDecodingTest extends TestCase
{
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
