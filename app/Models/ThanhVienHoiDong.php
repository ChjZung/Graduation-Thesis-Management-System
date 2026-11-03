<?php

namespace App\Models;

use App\Enums\VaiTroHoiDong;
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
     * Nhãn vai trò rút gọn chuẩn hóa theo VaiTroHoiDong
     */
    public function getShortVaiTroAttribute(): string
    {
        return VaiTroHoiDong::normalize($this->VaiTro);
    }

    /**
     * Tên vai trò chuẩn thống nhất
     */
    public function getVaiTroChuanAttribute(): string
    {
        return VaiTroHoiDong::normalize($this->VaiTro);
    }

    /**
     * Màu sắc badge chuẩn HUIT
     */
    public function getBadgeClassAttribute(): string
    {
        return VaiTroHoiDong::getBadgeClass($this->VaiTro);
    }

    /**
     * Thứ tự ưu tiên sắp xếp: Chủ tịch (1) -> Thư ký (2) -> Phản biện (3) -> Ủy viên (4)
     */
    public function getRolePriorityAttribute(): int
    {
        return VaiTroHoiDong::getPriority($this->VaiTro);
    }
}
