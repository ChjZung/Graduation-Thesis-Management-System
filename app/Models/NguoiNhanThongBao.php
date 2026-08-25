<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NguoiNhanThongBao extends Model
{
    use HasFactory;

    protected $table = 'nguoi_nhan_thong_baos';
    protected $primaryKey = 'MaThongBao';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MaThongBao', 'MaTK', 'TieuDe', 'NoiDung', 'Loai', 'DuongDan', 'DaDoc', 'NgayDoc',
    ];


    protected $casts = [
        'DaDoc' => 'boolean',
    ];

    public $timestamps = true;

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTK', 'MaTK');
    }

    /**
     * Lấy icon phù hợp với loại thông báo
     */
    public function getIconAttribute(): string
    {
        return match($this->Loai) {
            'Đề tài'       => 'fa-book-open text-primary',
            'Đăng ký'      => 'fa-clipboard-list text-success',
            'Báo cáo'      => 'fa-file-invoice text-warning',
            'Hội đồng'     => 'fa-landmark text-danger',
            'Kết quả'      => 'fa-award text-purple',
            default        => 'fa-bell text-secondary',
        };
    }
}
