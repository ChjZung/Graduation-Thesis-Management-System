<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DotNopBaoCao extends Model
{
    use HasFactory;

    protected $table = 'dotnopbaocao';
    protected $primaryKey = 'MaDotNop';

    protected $attributes = [
        'YeuCauSourceCode' => false,
    ];

    protected $fillable = [
        'MaGiaiDoan',
        'MaDeTai',
        'TenDotNop',
        'MoTa',
        'NoiDungYeuCau',
        'NgayMoNop',
        'HanNop',
        'DinhDangChoPhep',
        'YeuCauSourceCode',
        'SoLanNopToiDa',
        'TrangThai', // DANG_MO, TAM_DONG, DA_DONG, HOAN_THANH
        'ThuTu',
    ];

    protected function casts(): array
    {
        return [
            'NgayMoNop' => 'datetime',
            'HanNop' => 'datetime',
            'YeuCauSourceCode' => 'boolean',
            'SoLanNopToiDa' => 'integer',
            'ThuTu' => 'integer',
        ];
    }

    public function quyTacLinkMaNguon(): array
    {
        return $this->YeuCauSourceCode
            ? ['required', 'url', 'regex:#^https?://(www\.)?(github\.com|gitlab\.com|bitbucket\.org)/.+#i']
            : ['exclude'];
    }

    public function nguCanhDoiChieuAi(): array
    {
        $plain = static function ($text): string {
            $text = preg_replace('/<\/(?:p|div|li|h[1-6])>|<br\s*\/?\s*>/iu', "\n", (string) $text);
            return trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        };

        return [
            'stage_title' => $plain($this->giaiDoan?->TenGiaiDoan),
            'stage_requirements' => $plain($this->giaiDoan?->YeuCauCanDat),
            'round_title' => $plain($this->TenDotNop),
            'round_description' => $plain($this->MoTa),
            'submission_requirements' => $plain($this->NoiDungYeuCau),
            'require_source_code' => (bool) $this->YeuCauSourceCode,
        ];
    }

    public function maNguCanhDoiChieuAi(): string
    {
        $context = $this->nguCanhDoiChieuAi();
        ksort($context);
        return hash('sha256', json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    public function giaiDoan()
    {
        return $this->belongsTo(GiaiDoanThucHien::class, 'MaGiaiDoan', 'MaGiaiDoan');
    }

    public function deTai()
    {
        return $this->belongsTo(DeTai::class, 'MaDeTai', 'MaDeTai');
    }

    /**
     * Danh sách tất cả các phiên bản (lần nộp) của đợt báo cáo này
     */
    public function khoiBaoCaos()
    {
        return $this->hasMany(KhoiBaoCaoTienDo::class, 'MaDotNop', 'MaDotNop')
                    ->orderBy('LanNop', 'desc');
    }

    /**
     * Phiên bản mới nhất của đợt nộp này
     */
    public function phienBanMoiNhat()
    {
        return $this->hasOne(KhoiBaoCaoTienDo::class, 'MaDotNop', 'MaDotNop')
                    ->latestOfMany('LanNop');
    }

    /**
     * Kiểm tra đợt nộp có đang mở để sinh viên nộp báo cáo hay không
     */
    public function isMoNop(): bool
    {
        if ($this->TrangThai === 'DA_DONG' || $this->TrangThai === 'HOAN_THANH') {
            return false;
        }

        $now = now();
        if ($this->NgayMoNop && $now->lt($this->NgayMoNop)) {
            return false;
        }
        if ($this->HanNop && $now->gt($this->HanNop)) {
            return false;
        }

        return true;
    }

    /**
     * Lấy trạng thái hiển thị cho sinh viên và giảng viên
     */
    public function getTrangThaiHienThi(): array
    {
        $latest = $this->phienBanMoiNhat;
        if (!$latest) {
            $isOverdue = $this->HanNop && now()->gt($this->HanNop);
            return [
                'code' => $isOverdue ? 'QUA_HAN' : 'CHUA_NOP',
                'label' => $isOverdue ? 'Quá hạn nộp' : 'Chưa nộp',
                'badge' => $isOverdue ? 'bg-danger' : 'bg-secondary',
            ];
        }

        if ($latest->TrangThai === 'Đạt') {
            return [
                'code' => 'DAT',
                'label' => 'Đã xác nhận đạt yêu cầu',
                'badge' => 'bg-success',
            ];
        }

        if ($latest->TrangThai === 'Yêu cầu nộp lại' || $latest->TrangThai === 'Cần bổ sung') {
            return [
                'code' => 'NOP_LAI',
                'label' => 'Cần bổ sung / nộp lại (v' . $latest->LanNop . ')',
                'badge' => 'bg-warning text-dark',
            ];
        }

        return [
            'code' => 'CHO_DUYET',
            'label' => 'Đã nộp v' . $latest->LanNop . ' (Chờ GV nhận xét)',
            'badge' => 'bg-info text-dark',
        ];
    }
}
