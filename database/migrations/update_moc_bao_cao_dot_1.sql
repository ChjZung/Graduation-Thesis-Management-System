-- File Migration: Lưu mốc thời gian nộp Báo cáo tiến độ đợt 1 theo quy định C1
-- Cho phép NgayBatDau và NgayKetThuc mang giá trị NULL (NULL = không giới hạn)
ALTER TABLE `mocthoigiankhoaluan` MODIFY `NgayBatDau` DATE NULL DEFAULT NULL;
ALTER TABLE `mocthoigiankhoaluan` MODIFY `NgayKetThuc` DATE NULL DEFAULT NULL;

-- Cập nhật / Thêm mốc Báo cáo tiến độ đợt 1: Bắt đầu từ 15/09/2026 trở đi (gồm 15/9), kết thúc NULL (không giới hạn)
INSERT INTO `mocthoigiankhoaluan` (`MaMoc`, `TenMoc`, `DoiTuongThucHien`, `NgayBatDau`, `NgayKetThuc`, `MoTa`, `MakeHoach`, `created_at`, `updated_at`)
VALUES (
    'MOC_R0JW_BC1', 
    'Báo cáo tiến độ đợt 1', 
    'Sinh viên', 
    '2026-09-15', 
    NULL, 
    'Báo cáo tiến độ đợt 1 (Mở từ ngày 15/09/2026 trở đi) [BAO_CAO_TIEN_DO_1]', 
    'KH_2026_R0JW', 
    NOW(), 
    NOW()
)
ON DUPLICATE KEY UPDATE 
    `TenMoc` = 'Báo cáo tiến độ đợt 1',
    `NgayBatDau` = '2026-09-15',
    `NgayKetThuc` = NULL,
    `MoTa` = 'Báo cáo tiến độ đợt 1 (Mở từ ngày 15/09/2026 trở đi) [BAO_CAO_TIEN_DO_1]',
    `updated_at` = NOW();
