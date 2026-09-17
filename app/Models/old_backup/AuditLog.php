<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'MaTK',
        'HanhDong',
        'DoiTuong',
        'DoiTuongId',
        'DuLieu',
        'IPAddress',
    ];

    protected $casts = [
        'DuLieu' => 'array',
    ];

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTK', 'MaTK');
    }

    public static function log(string $hanhDong, string $doiTuong, $doiTuongId = null, array $duLieu = [])
    {
        try {
            $user = Auth::user();
            return self::create([
                'MaTK' => $user ? $user->MaTK : null,
                'HanhDong' => $hanhDong,
                'DoiTuong' => $doiTuong,
                'DoiTuongId' => $doiTuongId,
                'DuLieu' => !empty($duLieu) ? $duLieu : null,
                'IPAddress' => Request::ip(),
            ]);
        } catch (\Throwable $e) {
            // Fail safely so system functions are not interrupted by log write errors
            \Illuminate\Support\Facades\Log::error("AuditLog Error: " . $e->getMessage());
            return null;
        }
    }
}
