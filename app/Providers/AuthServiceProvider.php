<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\DeTai;
use App\Models\BaoCaoTienDo;
use App\Models\HoSoBaoVe;
use App\Policies\DeTaiPolicy;
use App\Policies\BaoCaoPolicy;
use App\Policies\ChamDiemPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Đăng ký Policy cho từng Model.
     */
    protected $policies = [
        DeTai::class        => DeTaiPolicy::class,
        BaoCaoTienDo::class => BaoCaoPolicy::class,
        HoSoBaoVe::class    => ChamDiemPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
