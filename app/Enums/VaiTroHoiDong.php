<?php

namespace App\Enums;

enum VaiTroHoiDong: string
{
    case CHU_TICH  = 'Chủ tịch';
    case THU_KY    = 'Thư ký';
    case PHAN_BIEN = 'Phản biện';
    case UY_VIEN   = 'Ủy viên';

    /**
     * Nhãn hiển thị chuẩn tiếng Việt
     */
    public function label(): string
    {
        return $this->value;
    }

    /**
     * Danh sách tất cả các giá trị chuẩn
     */
    public static function all(): array
    {
        return [
            self::CHU_TICH->value,
            self::THU_KY->value,
            self::PHAN_BIEN->value,
            self::UY_VIEN->value,
        ];
    }

    /**
     * Danh sách nhãn cho dropdown
     */
    public static function labels(): array
    {
        return [
            self::CHU_TICH->value  => 'Chủ tịch',
            self::THU_KY->value    => 'Thư ký',
            self::PHAN_BIEN->value => 'Phản biện',
            self::UY_VIEN->value   => 'Ủy viên',
        ];
    }

    /**
     * Chuẩn hóa mọi biến thể vai trò (không phân biệt hoa/thường, có/không dấu, gạch dưới, khoảng trắng)
     * về 1 trong 4 giá trị chuẩn duy nhất:
     * - 'Chủ tịch'
     * - 'Thư ký'
     * - 'Phản biện'
     * - 'Ủy viên'
     *
     * @param string|null $raw
     * @return string
     */
    public static function normalize(?string $raw): string
    {
        if ($raw === null || trim($raw) === '') {
            return self::UY_VIEN->value;
        }

        $s = mb_strtolower(trim($raw), 'UTF-8');
        // Thay thế ký tự phân cách gạch nối, gạch dưới, khoảng trắng thừa
        $clean = preg_replace('/[_\-\s]+/u', ' ', $s);

        // Chuỗi không dấu để so khớp linh hoạt
        $ascii = self::removeVietnameseAccents($clean);

        // 1. Thư ký ("Thư ký", "thu_ky", "Ủy viên thư ký", "uy_vien_thu_ky", "UV thư ký", "uv_thu_ky",...)
        if (
            str_contains($clean, 'thư ký') || 
            str_contains($clean, 'thu ky') || 
            str_contains($ascii, 'thu ky') ||
            str_contains($ascii, 'thuky') ||
            str_contains($clean, 'secretary')
        ) {
            return self::THU_KY->value;
        }

        // 2. Phản biện ("Ủy viên phản biện", "phản biện", "phan_bien", "UV phản biện", "uv_phan_bien",...)
        if (
            str_contains($clean, 'phản biện') || 
            str_contains($clean, 'phan bien') || 
            str_contains($ascii, 'phan bien') ||
            str_contains($ascii, 'phanbien') ||
            str_contains($clean, 'reviewer')
        ) {
            return self::PHAN_BIEN->value;
        }

        // 3. Chủ tịch ("Chủ tịch", "chu_tich", "Chủ tịch Hội đồng", "chu_tich_hoi_dong",...)
        if (
            str_contains($clean, 'chủ tịch') || 
            str_contains($clean, 'chu tich') || 
            str_contains($ascii, 'chu tich') ||
            str_contains($ascii, 'chutich') ||
            str_contains($clean, 'president') ||
            str_contains($clean, 'chairman')
        ) {
            return self::CHU_TICH->value;
        }

        // 4. Còn lại mặc định là Ủy viên
        return self::UY_VIEN->value;
    }

    /**
     * Bỏ dấu tiếng Việt phục vụ so khớp không dấu
     */
    public static function removeVietnameseAccents(string $str): string
    {
        $str = mb_strtolower($str, 'UTF-8');
        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        ];
        foreach ($unicode as $nonAccent => $accent) {
            $str = preg_replace("/($accent)/iu", $nonAccent, $str);
        }
        return $str;
    }

    /**
     * Kiểm tra có phải Chủ tịch
     */
    public static function isChuTich(?string $raw): bool
    {
        return self::normalize($raw) === self::CHU_TICH->value;
    }

    /**
     * Kiểm tra có phải Thư ký
     */
    public static function isThuKy(?string $raw): bool
    {
        return self::normalize($raw) === self::THU_KY->value;
    }

    /**
     * Kiểm tra có phải Phản biện
     */
    public static function isPhanBien(?string $raw): bool
    {
        return self::normalize($raw) === self::PHAN_BIEN->value;
    }

    /**
     * Kiểm tra có phải Ủy viên
     */
    public static function isUyVien(?string $raw): bool
    {
        return self::normalize($raw) === self::UY_VIEN->value;
    }

    /**
     * Lớp màu badge chuẩn HUIT
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::CHU_TICH  => 'bg-danger-subtle text-danger border-danger-subtle',
            self::PHAN_BIEN => 'bg-primary-subtle text-primary border-primary-subtle',
            self::THU_KY    => 'bg-info-subtle text-info border-info-subtle',
            self::UY_VIEN   => 'bg-secondary-subtle text-secondary border-secondary-subtle',
        };
    }

    /**
     * Thứ tự ưu tiên sắp xếp: Chủ tịch (1) -> Phản biện (2) -> Thư ký (3) -> Ủy viên (4)
     */
    public function priority(): int
    {
        return match($this) {
            self::CHU_TICH  => 1,
            self::PHAN_BIEN => 2,
            self::THU_KY    => 3,
            self::UY_VIEN   => 4,
        };
    }

    /**
     * Lấy badge class từ chuỗi bất kỳ
     */
    public static function getBadgeClass(?string $raw): string
    {
        $norm = self::normalize($raw);
        $case = self::tryFrom($norm) ?? self::UY_VIEN;
        return $case->badgeClass();
    }

    /**
     * Lấy thứ tự ưu tiên từ chuỗi bất kỳ
     */
    public static function getPriority(?string $raw): int
    {
        $norm = self::normalize($raw);
        $case = self::tryFrom($norm) ?? self::UY_VIEN;
        return $case->priority();
    }
}
