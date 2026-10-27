<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThanhVienHoiDong extends Model
{
    use HasFactory;

    protected $table = 'ThanhVienHoiDong';
    public $incrementing = false;
    protected $primaryKey = ['MaHoiDong', 'MaGV'];

    protected $fillable = [
        'MaHoiDong',
        'MaGV',
        'VaiTro',
    ];

    public $timestamps = true;

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('MaHoiDong', $this->getAttribute('MaHoiDong'))
                     ->where('MaGV', $this->getAttribute('MaGV'));
    }

    public function hoiDong()
    {
        return $this->belongsTo(HoiDong::class, 'MaHoiDong', 'MaHoiDong');
    }

    public function giangVien()
    {
        return $this->belongsTo(GiangVien::class, 'MaGV', 'MaGV');
    }

    /**
     * Nhãn vai trò rút gọn chuẩn hóa
     */
    public function getShortVaiTroAttribute(): string
    {
        $vt = trim($this->VaiTro ?? '');
        return match($vt) {
            'Ủy viên phản biện', 'Phản biện' => 'Phản biện',
            'Ủy viên thư ký', 'Thư ký' => 'Thư ký',
            'Chủ tịch', 'Chủ tịch Hội đồng' => 'Chủ tịch',
            default => 'Ủy viên',
        };
    }

    /**
     * Màu sắc badge chuẩn HUIT
     */
    public function getBadgeClassAttribute(): string
    {
        return match($this->short_vai_tro) {
            'Chủ tịch' => 'bg-danger-subtle text-danger border-danger-subtle',
            'Phản biện' => 'bg-primary-subtle text-primary border-primary-subtle',
            'Thư ký' => 'bg-info-subtle text-info border-info-subtle',
            default => 'bg-secondary-subtle text-secondary border-secondary-subtle',
        };
    }

    /**
     * Thứ tự ưu tiên sắp xếp: Chủ tịch (1) -> Phản biện (2) -> Thư ký (3) -> Ủy viên (4)
     */
    public function getRolePriorityAttribute(): int
    {
        return match($this->short_vai_tro) {
            'Chủ tịch' => 1,
            'Phản biện' => 2,
            'Thư ký' => 3,
            default => 4,
        };
    }
}
