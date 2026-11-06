<?php
namespace App\Services;

use App\Models\{TaiKhoan, GiangVien, Nganh, Khoa, HoiDong, SinhVien};
use Illuminate\Validation\ValidationException;

class CloRoundReportService
{
    public function allowedMajors(TaiKhoan $user)
    {
        $role = $user->vaiTro?->TenVaiTro;
        $query = Nganh::query();
        if (in_array($role, ['Admin', 'Giáo vụ'])) return $query->orderBy('TenNganh');
        $teacher = GiangVien::getLoggedInGiangVien($user);
        if ($role === 'Trưởng khoa') {
            $faculty = preg_match('/^TK_([A-Z0-9]+)_/i', $user->TenDangNhap, $match)
                ? Khoa::find($match[1]) : $teacher?->boMon?->khoa;
            abort_unless($faculty, 403, 'Chưa xác định khoa phụ trách.');
            return $query->where('MaKhoa', $faculty->MaKhoa)->orderBy('TenNganh');
        }
        abort_unless($role === 'Giảng viên' && $teacher, 403);
        $studentMajors = SinhVien::with('lop')->where(function ($q) use ($teacher) {
            $q->whereHas('thanhVienNhoms.nhom.deTai', fn ($topic) => $topic->where('MaGV', $teacher->MaGV))
                ->orWhereHas('thanhVienNhoms.nhom.hoSoBaoVes.hoiDong.thanhViens', fn ($members) => $members->where('MaGV', $teacher->MaGV));
        })
            ->get()->map(fn ($student) => self::studentMajor($student))->filter()->unique();
        return $query->whereIn('MaNganh', $studentMajors)->orderBy('TenNganh');
    }

    public static function studentMajor(?SinhVien $student): ?string
    {
        if (!$student) return null;
        return filled($student->MaNganh) ? $student->MaNganh : $student->lop?->MaNganh;
    }

    public static function filterStudentMajor($query, string $major): void
    {
        $query->where(function ($q) use ($major) {
            $q->where('MaNganh', $major)->orWhere(function ($fallback) use ($major) {
                $fallback->where(fn ($missing) => $missing->whereNull('MaNganh')->orWhere('MaNganh', ''))
                    ->whereHas('lop', fn ($lop) => $lop->where('MaNganh', $major));
            });
        });
    }

    public function councils(string $semester, ?string $round, string $major, ?string $councilId = null)
    {
        return HoiDong::with(['hocKy', 'khoa', 'thanhViens.giangVien', 'hoSoBaoVes.nhom.deTai.hocPhan', 'hoSoBaoVes.nhom.deTai.giangVien',
            'hoSoBaoVes.nhom.thanhViens.sinhVien.lop.nganh', 'hoSoBaoVes.nhom.thanhViens.sinhVien.nganh'])
            ->where('MaHocKy', $semester)
            ->when(filled($round), fn ($q) => $q->where('DotBaoVe', $round))
            ->when(filled($councilId), fn ($q) => $q->where('MaHoiDong', $councilId))
            ->whereHas('hoSoBaoVes.nhom.thanhViens.sinhVien', fn ($q) => self::filterStudentMajor($q, $major))
            ->orderBy('MaHoiDong')->get();
    }

    public function prepare(TaiKhoan $user, string $semester, ?string $round, string $major, string $kind, ?string $councilId = null, array $filters = [], bool $preview = false): array
    {
        $nganh = $this->allowedMajors($user)->where('MaNganh', $major)->first();
        abort_unless($nganh, 403, 'Không có quyền xuất báo cáo ngành này.');
        if (filled($councilId) && $user->vaiTro?->TenVaiTro === 'Giảng viên') {
            $teacher = GiangVien::getLoggedInGiangVien($user);
            abort_unless(HoiDong::whereKey($councilId)->whereHas('thanhViens', fn ($q) => $q->where('MaGV', $teacher->MaGV))->exists(), 403,
                'Bạn chỉ được chọn hội đồng mình tham gia.');
        }
        $councils = $this->councils($semester, $round, $major, $councilId);
        if (filled($councilId) && $councils->isEmpty()) {
            throw ValidationException::withMessages(['MaHoiDong' => 'Hội đồng không thuộc học kỳ, ngành sinh viên hoặc đợt đã chọn.']);
        }
        if ($user->vaiTro?->TenVaiTro === 'Giảng viên') {
            $teacher = GiangVien::getLoggedInGiangVien($user);
            abort_unless($councils->contains(fn ($hd) => $hd->thanhViens->contains('MaGV', $teacher->MaGV) || $hd->hoSoBaoVes->contains(fn ($hs) => $hs->nhom?->deTai?->MaGV === $teacher->MaGV && $hs->nhom->thanhViens->contains(fn ($member) => self::studentMajor($member->sinhVien) === $major))), 403,
                'Bạn chưa được phân công hướng dẫn hoặc tham gia hội đồng có sinh viên trong phạm vi này.');
        }
        $service = app(CloCalculationService::class);
        $results = []; $students = [];
        foreach ($councils as $council) {
            $dossiers = $council->hoSoBaoVes->filter(function ($hs) use ($major, $filters) {
                $group = $hs->nhom;
                if (!$group || (filled($filters['MaHocPhan'] ?? null) && $group->deTai?->MaHocPhan !== $filters['MaHocPhan'])) return false;
                $members = $group->thanhViens->filter(fn ($member) => self::studentMajor($member->sinhVien) === $major
                    && (blank($filters['MaLop'] ?? null) || $member->sinhVien?->MaLop === $filters['MaLop'])
                    && (blank($filters['MaSV'] ?? null) || $member->sinhVien?->MaSV === $filters['MaSV']))->values();
                if ($members->isEmpty()) return false;
                // Mixed-major groups: only selected students enter completeness checks and every export sheet.
                $group->setRelation('thanhViens', $members);
                $group->setRelation('thanhVienNhoms', $members);
                return true;
            });
            foreach ($dossiers as $dossier) {
                if (!$dossier->xacDinhLoaiKhoaLuan()) throw ValidationException::withMessages(['report' => "Hồ sơ {$dossier->MaHoSo} chưa xác định loại khóa luận. Cần bổ sung trước khi xuất."]);
            }
            $dossiers = $dossiers->filter(fn ($hs) => $hs->xacDinhLoaiKhoaLuan() === $kind)->unique('MaNhom')->values();
            if ($dossiers->isEmpty()) continue;
            $council->setRelation('hoSoBaoVes', $dossiers);
            $check = $service->kiemTraDu3PhieuCham($council);
            if (!$preview && !$check['du_3_phieu']) throw ValidationException::withMessages(['report' => $council->TenHoiDong.': '.$check['message']]);
            $data = $service->calculateCouncilClo($council, $kind, false);
            foreach ($data['students'] as $student) {
                if (!$preview && !$student['is_official']) throw ValidationException::withMessages(['report' => $council->TenHoiDong.': cần đúng 3 giám khảo có phiếu chấm hoàn thành để xuất CLO chính thức.']);
                if (isset($students[$student['maSV']])) throw ValidationException::withMessages(['report' => "Sinh viên {$student['maSV']} xuất hiện ở nhiều hội đồng. Cần kiểm tra phân công trước khi xuất."]);
                $student['maHoiDong'] = $council->MaHoiDong;
                $student['tenHoiDong'] = $council->TenHoiDong;
                $students[$student['maSV']] = $student;
            }
            $results[] = $data;
        }
        if (!$students) throw ValidationException::withMessages(['report' => 'Không có sinh viên phù hợp trong ngành, học kỳ, đợt và loại khóa luận đã chọn.']);
        $students = collect($students)->sortBy('maSV', SORT_NATURAL)->values()->map(function ($student, $i) { $student['stt'] = $i + 1; return $student; })->all();
        $type = filled($councilId) ? 'council' : (filled($round) ? 'round' : 'major');
        $label = match ($type) {
            'council' => 'Hội đồng: '.$councils->first()->TenHoiDong.' ('.$councilId.')',
            'round' => 'Đợt bảo vệ: '.$round,
            default => 'Toàn ngành trong học kỳ',
        };
        return ['councils' => $results, 'students' => $students, 'cloGroups' => $results[0]['cloGroups'], 'rubric' => $results[0]['rubric'],
            'scope' => ['type' => $type, 'label' => $label, 'MaHoiDong' => $councilId, 'MaHocKy' => $semester, 'DotBaoVe' => $round, 'MaNganh' => $major, 'TenNganh' => $nganh->TenNganh, 'TenKhoa' => $nganh->khoa?->TenKhoa, 'loai' => $kind]];
    }
}
