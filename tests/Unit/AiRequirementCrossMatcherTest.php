<?php

namespace Tests\Unit;

use App\Services\AiRequirementCrossMatcher;
use Tests\TestCase;

class AiRequirementCrossMatcherTest extends TestCase
{
    public function test_cross_matcher_links_requirements_to_chapters(): void
    {
        $chapters = [
            [
                'id' => 'CH1',
                'title' => 'CHƯƠNG 4: THIẾT KẾ HỆ THỐNG',
                'start_page' => 80,
                'end_page' => 88,
                'items' => [
                    ['content' => 'Tổng quan thiết kế hệ thống.', 'source_references' => [['page' => 80, 'quote' => 'Thiết kế hệ thống']]]
                ],
                'sections' => [
                    [
                        'id' => 'SEC1',
                        'title' => 'THIẾT KẾ CƠ SỞ DỮ LIỆU',
                        'start_page' => 80,
                        'end_page' => 80,
                        'items' => [
                            ['content' => 'Mô hình cơ sở dữ liệu quan hệ MySQL.', 'source_references' => [['page' => 80, 'quote' => 'Cơ sở dữ liệu MySQL']]]
                        ],
                        'sections' => []
                    ],
                    [
                        'id' => 'SEC2',
                        'title' => 'SƠ ĐỒ LỚP Ở MỨC THIẾT KẾ',
                        'start_page' => 81,
                        'end_page' => 81,
                        'items' => [],
                        'sections' => []
                    ]
                ]
            ]
        ];

        $context = [
            'submission_requirements' => "1. Thiết kế cơ sở dữ liệu và trình bày sơ đồ ERD.\n2. Xây dựng sơ đồ lớp ở mức thiết kế."
        ];

        $captionIndex = [
            'assets' => [
                [
                    'asset_id' => 'cap1',
                    'page' => 80,
                    'display_text' => 'Hình 4.1 Sơ đồ ERD',
                    'section_title' => 'THIẾT KẾ CƠ SỞ DỮ LIỆU'
                ],
                [
                    'asset_id' => 'cap2',
                    'page' => 81,
                    'display_text' => 'Hình 4.2 Sơ đồ lớp mức thiết kế',
                    'section_title' => 'SƠ ĐỒ LỚP Ở MỨC THIẾT KẾ'
                ]
            ]
        ];

        $data = [
            'chapters' => $chapters,
            'caption_index' => $captionIndex,
        ];

        $matched = AiRequirementCrossMatcher::crossMatch($data, $context);

        $this->assertCount(2, $matched);
        $this->assertSame('related_text', $matched[0]['report_relation']);
        $this->assertNotEmpty($matched[0]['report_points'][0]['source_references']);
        $this->assertStringContainsString('CƠ SỞ DỮ LIỆU', $matched[0]['report_points'][0]['title']);

        $this->assertSame('related_text', $matched[1]['report_relation']);
        $this->assertNotEmpty($matched[1]['report_points'][0]['source_references']);
        $this->assertStringContainsString('SƠ ĐỒ LỚP', $matched[1]['report_points'][0]['title']);
    }
}
