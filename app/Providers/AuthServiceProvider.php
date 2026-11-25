<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\DeTai;
use App\Models\BaoCaoTienDo;
use App\Models\HoSoBaoVe;
use App\Models\ThongBao;
use App\Policies\DeTaiPolicy;
use App\Policies\BaoCaoPolicy;
use App\Policies\HoSoBaoVePolicy;
use App\Policies\ThongBaoPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Đăng ký Policy cho từng Model.
     */
    protected $policies = [
        DeTai::class        => DeTaiPolicy::class,
        BaoCaoTienDo::class => BaoCaoPolicy::class,
        HoSoBaoVe::class    => HoSoBaoVePolicy::class,
        ThongBao::class     => ThongBaoPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        \Illuminate\Support\Facades\Gate::define('manage-mysql-backups', function (\App\Models\TaiKhoan $user): bool {
            return config('mysql_backup.enabled')
                && in_array((string) $user->getAuthIdentifier(), config('mysql_backup.operator_ids', []), true)
                && in_array($user->vaiTro?->TenVaiTro, ['Admin', 'Giáo vụ'], true);
        });
    }
}
