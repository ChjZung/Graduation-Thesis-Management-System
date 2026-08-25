<?php

namespace App\Enums;

enum NhomTrangThai: string
{
    case DangHoatDong = 'Đang hoạt động';
    case DaDuyet      = 'Đã duyệt';
    case DaHuy        = 'Đã hủy';

    public function label(): string
    {
        return match($this) {
            self::DangHoatDong => 'Đang hoạt động',
            self::DaDuyet      => 'Đã duyệt',
            self::DaHuy        => 'Đã hủy',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::DangHoatDong => 'bg-warning text-dark',
            self::DaDuyet      => 'bg-success',
            self::DaHuy        => 'bg-danger',
        };
    }
}
