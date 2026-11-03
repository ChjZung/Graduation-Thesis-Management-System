<?php

namespace Tests\Unit;

use App\Services\AiRequirementCrossMatcher;
use Tests\TestCase;

class AiRequirementCrossMatcherTest extends TestCase
{
    public function test_seven_relevant_captions_are_not_silently_cut_to_six(): void
    {
        $req='Trình bày các sơ đồ hoạt động của hệ thống';$caps=['assets'=>[]];
        for($i=1;$i<=7;$i++)$caps['assets'][]=['page'=>$i,'display_text'=>"Hình 3.$i Sơ đồ hoạt động module $i"];
        $this->assertCount(7,AiRequirementCrossMatcher::findMatchingCaptions(AiRequirementCrossMatcher::tokenize($req),$req,$caps));
    }
    public function test_joined_pdf_words_keep_scope_and_exact_quote(): void
    {
        $raw='Hình 3.3. Sơ đồlớp';
        $this->assertTrue(\App\Services\AiRequirementRelevance::relevant('Trình bày sơ đồ lớp',$raw));
        $this->assertFalse(\App\Services\AiRequirementRelevance::relevant('Sơ đồ lớp mức thiết kế','Sơ đồlớp mức phântích'));
        $this->assertFalse(\App\Services\AiRequirementRelevance::relevant('Sơ đồ lớp mức phân tích','Sơ đồlớp mức thiếtkế'));
        $this->assertFalse(\App\Services\AiRequirementRelevance::relevant('Tuần tự theo dõi tiến độ','Sơ đồtuầntự đăngkýđềtài'));
        $this->assertFalse(\App\Services\AiRequirementRelevance::relevant('Giao diện đăng nhập','Giao diện danh sách người dùng'));
        $this->assertFalse(\App\Services\AiRequirementRelevance::relevant('Thiết kế CSDL và ERD, các bảng dữ liệu','Bảng 6.2 Nhóm dữ liệu thử nghiệm'));
        $result=AiRequirementCrossMatcher::crossMatch(['caption_index'=>['assets'=>[['page'=>11,'display_text'=>$raw]]]],['submission_requirements'=>'1. Trình bày sơ đồ lớp.']);
        $this->assertSame($raw,$result[0]['report_points'][0]['source_references'][0]['quote']);
    }
    public function test_named_usecase_does_not_match_a_different_module(): void
    {
        $this->assertFalse(\App\Services\AiRequirementRelevance::relevant('Đặc tả Use Case quản lý đề tài','Use Case quản lý danh sách sinh viên đủ điều kiện'));
        $this->assertTrue(\App\Services\AiRequirementRelevance::relevant('Đặc tả Use Case quản lý đề tài','Use Case quản lý đề tài'));
    }
    public function test_erd_is_not_lost_when_text_is_found_on_another_page(): void
    {
        $d=json_decode(file_get_contents(__DIR__.'/../fixtures/requirement_scope.json'),true);
        $d['chapters'][0]['sections'][]=['id'=>'TABLES','title'=>'Cơ sở dữ liệu và quan hệ giữa các bảng dữ liệu chính',
            'start_page'=>92,'end_page'=>92,'items'=>[['content'=>'Cơ sở dữ liệu gồm các bảng dữ liệu chính và quan hệ giữa các bảng.',
            'source_references'=>[['page'=>92,'quote'=>'Cơ sở dữ liệu gồm các bảng dữ liệu chính và quan hệ giữa các bảng.']]]]];
        $point=AiRequirementCrossMatcher::crossMatch($d,$d['context'])[0]['report_points'][0];
        $this->assertSame([92],array_column($point['source_references'],'page'));
        $this->assertSame([80],array_column($point['manual_references'],'page'));
    }
    public function test_requirement_scope_excludes_wrong_diagrams_and_parent_sources(): void
    {
        $d=json_decode(file_get_contents(__DIR__.'/../fixtures/requirement_scope.json'),true);
        $out=AiRequirementCrossMatcher::crossMatch($d,$d['context']);
        foreach([0=>80,1=>81] as $i=>$page){
            $p=$out[$i]['report_points'][0];
            $this->assertSame([$page],array_column($p['source_references'],'page'));
            $this->assertSame([$page],array_column($p['manual_references'],'page'));
            $this->assertSame('NEEDS_VISUAL_REVIEW',$p['check_status']);
            $this->assertStringNotContainsString('hoạt động',$p['explanation']);
        }
        $this->assertSame('not_found',$out[2]['report_relation']);
        $this->assertEmpty($out[2]['report_points'][0]['source_references']);
    }
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
