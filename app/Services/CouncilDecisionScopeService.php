<?php
namespace App\Services;

use App\Enums\VaiTroHoiDong;
use App\Models\HoiDong;
use Illuminate\Support\Collection;

class CouncilDecisionScopeService
{
    public function councils(string $faculty, string $semester, string $round, bool $lock = false): Collection
    {
        $query = HoiDong::with(['khoa', 'nganh', 'hocKy', 'thanhViens.giangVien.boMon',
            'hoSoBaoVes.nhom.thanhVienNhoms.sinhVien.lop', 'hoSoBaoVes.nhom.deTai.giangVien.boMon',
            'hoSoBaoVes.phanCongPhanBien.giangVien.boMon'])
            ->where('MaKhoa', $faculty)->where('MaHocKy', $semester)->where('DotBaoVe', $round)->orderBy('MaHoiDong');
        if ($lock) $query->lockForUpdate();
        $items = $query->get();
        foreach ($items as $item) HoiDongPdfService::prepareData($item);
        return $items;
    }

    public function fingerprint(Collection $items): string
    {
        $data = $items->map(function ($council) {
            $prepared = HoiDongPdfService::prepareData($council);
            return ['council' => $council->getAttributes(), 'faculty' => $prepared['khoa']?->getAttributes(),
                'semester' => $council->hocKy?->getAttributes(), 'major' => $council->nganh?->getAttributes(),
                'members' => $prepared['thanhViens']->map(fn ($member) => [$member->MaGV, $member->VaiTro, $member->giangVien?->getAttributes(), $member->giangVien?->boMon?->getAttributes()])->values()->all(),
                'topics' => $prepared['danhSachDeTai'], 'students' => $prepared['danhSachSinhVien']];
        });
        return hash('sha256', json_encode($data->all(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    public function issues(Collection $items): array
    {
        if ($items->isEmpty()) return ['Đợt này chưa có hội đồng.'];
        $errors = [];
        foreach ($items as $council) {
            $roles = $council->thanhViens->map(fn ($member) => VaiTroHoiDong::normalize($member->VaiTro));
            if ($roles->count() < 3 || collect(['Chủ tịch', 'Thư ký', 'Phản biện'])->contains(fn ($role) => $roles->filter(fn ($value) => $value === $role)->count() !== 1) || $council->hoSoBaoVes->isEmpty()) {
                $errors[] = $council->TenHoiDong.': chưa đủ thành phần bắt buộc hoặc chưa phân công nhóm đề tài.';
            }
            if (!in_array($council->TrangThaiCongBo, ['Chưa công bố', 'Đã công bố'])) $errors[] = $council->TenHoiDong.': cần kiểm tra trạng thái công bố.';
        }
        foreach (['SoQuyetDinh' => 'Số quyết định', 'NgayBanHanh' => 'Ngày ban hành', 'NguoiKy' => 'Người ký', 'ChucVuNguoiKy' => 'Chức vụ người ký'] as $field => $label) {
            $values = $items->pluck($field)->map(fn ($value) => trim((string) $value));
            if ($values->contains('')) $errors[] = $label.': chưa được cấu hình đầy đủ.';
            elseif ($values->unique()->count() !== 1) $errors[] = $label.': chưa thống nhất giữa các hội đồng trong đợt.';
        }
        return $errors;
    }
}
