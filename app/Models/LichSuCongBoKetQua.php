<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LichSuCongBoKetQua extends Model
{
    use HasFactory;

    protected $table = 'lich_su_cong_bo_ket_qua';

    protected $fillable = [
        'LoaiHanhDong',    // CONG_BO, THU_HOI
        'PhamVi',          // DE_TAI, HOI_DONG, TOAN_DOT
        'MaHocKy',
        'MaHoiDong',
        'MaHoSo',
        'SoLuongDeTai',
        'SoLuongSinhVien',
        'NguoiThucHien',
        'MaTK',
        'LyDo',
        'DuLieuSnapshot',
    ];

    protected function casts(): array
    {
        return [
            'DuLieuSnapshot' => 'array',
            'SoLuongDeTai' => 'integer',
            'SoLuongSinhVien' => 'integer',
        ];
    }

    public function hoiDong()
    {
        return $this->belongsTo(HoiDong::class, 'MaHoiDong', 'MaHoiDong');
    }

    public function hoSoBaoVe()
    {
        return $this->belongsTo(HoSoBaoVe::class, 'MaHoSo', 'MaHoSo');
    }

    public function hocKy()
    {
        return $this->belongsTo(HocKy::class, 'MaHocKy', 'MaHocKy');
    }
}
