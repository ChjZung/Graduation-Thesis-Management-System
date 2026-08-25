<?php

namespace App\Enums;

enum DangKyTrangThai: string
{
    case ChoDuyet = 'Chờ duyệt';
    case DaDuyet  = 'Đã duyệt';
    case TuChoi   = 'Từ chối';

    public function label(): string
    {
        return $this->value;
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::ChoDuyet => 'bg-warning text-dark',
            self::DaDuyet  => 'bg-success',
            self::TuChoi   => 'bg-danger',
        };
    }
}
