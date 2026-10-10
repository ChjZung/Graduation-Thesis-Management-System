-- =========================================================================
-- Migration: Bổ sung các cột lưu trữ lịch sử xử lý phê duyệt đề tài
-- Bảng áp dụng: ChiTietDuyetDeTai
-- =========================================================================

ALTER TABLE `ChiTietDuyetDeTai`
    ADD COLUMN IF NOT EXISTS `HanhDong` VARCHAR(100) NULL AFTER `TrangThai`,
    ADD COLUMN IF NOT EXISTS `TrangThaiCu` VARCHAR(50) NULL AFTER `HanhDong`,
    ADD COLUMN IF NOT EXISTS `NguoiThucHien` VARCHAR(100) NULL AFTER `MaGV`;
