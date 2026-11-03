<?php
namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AiDemoSeedSafetyTest extends TestCase
{
    public function test_demo_sql_is_blocked_without_explicit_testing_opt_in(): void
    {
        $this->app->instance('env', 'testing');
        config(['database.allow_destructive_demo_seed'=>false]);
        DB::shouldReceive('unprepared')->never();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Demo seed disabled');
        (new \Database\Seeders\TestNhomBaoCaoSeeder)->run();
    }

    public function test_demo_sql_is_blocked_outside_testing_even_with_opt_in(): void
    {
        $this->app->instance('env', 'production');
        config(['database.allow_destructive_demo_seed'=>true]);
        DB::shouldReceive('unprepared')->never();
        $this->expectException(\RuntimeException::class);
        (new \Database\Seeders\TestNhomBaoCaoSeeder)->run();
    }
}
