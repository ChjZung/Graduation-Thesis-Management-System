<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiaiDoanThucHien extends Model
{
    use HasFactory;

    protected $table = 'giaidoanthuchien';
    protected $primaryKey = 'MaGiaiDoan';

    protected $fillable = [
        'MaDeTai',
        'TenGiaiDoan',
        'MoTa',
        'NoiDungCongViec',
        'KetQuaDauRa',
        'NgayBatDau',
        'NgayKetThuc',
        'YeuCauCanDat',
        'ThuTu',
        'TrangThai', // CHUA_BAT_DAU, DANG_THUC_HIEN, HOAN_THANH
    ];

    protected function casts(): array
    {
        return [
            'NgayBatDau' => 'date',
            'NgayKetThuc' => 'date',
            'ThuTu' => 'integer',
        ];
    }

    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }

    public function dotNops()
    {
        return $this->hasMany(DotNopBaoCao::class, 'MaGiaiDoan', 'MaGiaiDoan')->orderBy('ThuTu')->orderBy('MaDotNop');
    }

    public function khoiBaoCaos()
    {
        return $this->hasMany(KhoiBaoCaoTienDo::class, 'MaGiaiDoan', 'MaGiaiDoan');
    }

    /**
     * Tự động tính toán trạng thái giai đoạn dựa trên các đợt nộp
     */
    public function capNhatTrangThai()
    {
        $dots = $this->dotNops;
        if ($dots->isEmpty()) {
            return $this->TrangThai;
        }

        $allDone = true;
        $hasAnySubmitOrInProgress = false;

        foreach ($dots as $dot) {
            if ($dot->TrangThai !== 'HOAN_THANH') {
                $allDone = false;
            }
            if ($dot->khoiBaoCaos()->exists()) {
                $hasAnySubmitOrInProgress = true;
            }
        }

        if ($allDone) {
            $newStatus = 'HOAN_THANH';
        } elseif ($hasAnySubmitOrInProgress) {
            $newStatus = 'DANG_THUC_HIEN';
        } else {
            $newStatus = 'CHUA_BAT_DAU';
        }

        if ($this->TrangThai !== $newStatus) {
            $this->TrangThai = $newStatus;
            $this->save();
        }

        return $newStatus;
    }
}
