<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mime\MimeTypes;
use App\Services\OfficeDocumentMimeTypeGuesser;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend(\Illuminate\Foundation\Console\ServeCommand::class,
            fn ($command, $app) => $app->make(\App\Console\Commands\ServeWithAiWorkerCommand::class));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // Đảm bảo các định dạng tài liệu Office (.docx, .xlsx, .pptx, .pdf, .doc) luôn được nhận diện MIME chuẩn xác
        // giải quyết triệt để lỗi PHP finfo trả về application/octet-stream trên môi trường Windows/XAMPP
        MimeTypes::getDefault()->registerGuesser(new OfficeDocumentMimeTypeGuesser());
    }
}
