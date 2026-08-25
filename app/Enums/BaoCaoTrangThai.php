<?php

namespace App\Enums;

enum BaoCaoTrangThai: string
{
    case ChoDuyet      = 'Chờ duyệt';
    case Dat           = 'Đạt';
    case YeuCauNopLai  = 'Yêu cầu nộp lại';

    public function label(): string
    {
        return $this->value;
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::ChoDuyet     => 'bg-warning text-dark',
            self::Dat          => 'bg-success',
            self::YeuCauNopLai => 'bg-danger',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::ChoDuyet     => 'fa-hourglass-half',
            self::Dat          => 'fa-check-circle',
            self::YeuCauNopLai => 'fa-redo-alt',
        };
    }
}
