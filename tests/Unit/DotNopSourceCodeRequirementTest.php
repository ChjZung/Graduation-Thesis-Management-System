<?php

namespace Tests\Unit;

use App\Models\DotNopBaoCao;
use App\Models\GiaiDoanThucHien;
use App\Services\ThesisProgressService;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\MySqlConnection;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DotNopSourceCodeRequirementTest extends TestCase
{
    public function test_new_submission_round_does_not_require_source_code_by_default(): void
    {
        $this->assertFalse((new DotNopBaoCao)->YeuCauSourceCode);
    }

    #[DataProvider('checkboxValues')]
    public function test_creating_a_round_preserves_the_checkbox_choice(array $data, bool $expected): void
    {
        // Chỉ ghi nhận SQL: mọi lần thử kết nối database thật đều làm test thất bại.
        $connection = new class(function () {
            throw new \RuntimeException('This unit test must not connect to a database.');
        }, 'source_code_unit') extends MySqlConnection {
            // Chế độ pretend vẫn định dạng SQL để ghi log; không cần PDO chỉ để quote chuỗi.
            public function escape($value, $binary = false)
            {
                return is_string($value)
                    ? "'" . str_replace("'", "''", $value) . "'"
                    : parent::escape($value, $binary);
            }
        };
        $resolver = new ConnectionResolver(['source_code_unit' => $connection]);
        $resolver->setDefaultConnection('source_code_unit');
        $previousResolver = Model::getConnectionResolver();
        Model::setConnectionResolver($resolver);

        try {
            $stage = new GiaiDoanThucHien(['MaDeTai' => 'DT_UNIT']);
            $stage->MaGiaiDoan = 1;
            $round = null;
            $connection->pretend(function () use ($stage, $data, &$round) {
                $round = (new ThesisProgressService)->taoDotNop($stage, array_merge([
                    'TenDotNop' => 'Đợt kiểm tra yêu cầu mã nguồn',
                ], $data));
            });

            $this->assertInstanceOf(DotNopBaoCao::class, $round);
            $this->assertSame($expected, $round->YeuCauSourceCode);
        } finally {
            if ($previousResolver) {
                Model::setConnectionResolver($previousResolver);
            } else {
                Model::unsetConnectionResolver();
            }
        }
    }

    public static function checkboxValues(): array
    {
        return [
            'unchecked checkbox omitted by browser' => [[], false],
            'unchecked hidden value' => [['YeuCauSourceCode' => '0'], false],
            'checked checkbox' => [['YeuCauSourceCode' => '1'], true],
            'explicit boolean false' => [['YeuCauSourceCode' => false], false],
            'explicit boolean true' => [['YeuCauSourceCode' => true], true],
        ];
    }

    #[DataProvider('submissionLinks')]
    public function test_submission_link_is_only_required_when_enabled(bool $required, array $data, bool $passes): void
    {
        $round = new DotNopBaoCao(['YeuCauSourceCode' => $required]);
        $factory = new Factory(new Translator(new ArrayLoader, 'vi'));
        $validator = $factory->make($data, ['LinkCode' => $round->quyTacLinkMaNguon()]);

        $this->assertSame($passes, $validator->passes());
        if (!$required) {
            $this->assertArrayNotHasKey('LinkCode', $validator->validated());
        }
    }

    public static function submissionLinks(): array
    {
        return [
            'not required, no link' => [false, [], true],
            'not required, stale invalid field is ignored' => [false, ['LinkCode' => 'old-invalid-value'], true],
            'required, no link' => [true, [], false],
            'required, invalid URL' => [true, ['LinkCode' => 'invalid'], false],
            'required, GitHub' => [true, ['LinkCode' => 'https://github.com/team/project'], true],
            'required, GitLab' => [true, ['LinkCode' => 'https://gitlab.com/team/project'], true],
            'required, Bitbucket' => [true, ['LinkCode' => 'https://bitbucket.org/team/project'], true],
            'required, unrelated URL' => [true, ['LinkCode' => 'https://example.com/project'], false],
            'required, misleading hostname' => [true, ['LinkCode' => 'https://github.com.example.com/project'], false],
        ];
    }

    public function test_template_rounds_leave_source_code_requirement_to_the_lecturer(): void
    {
        foreach (ThesisProgressService::GIAI_DOAN_KY_THUAT_MAU as $stage) {
            foreach ($stage['dot_nops'] as $round) {
                $this->assertFalse($round['source_code'], $round['ten']);
            }
        }
    }
}
