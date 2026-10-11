-- Migration D1: Quản lý chỉ tiêu phản biện đề tài của Giảng viên
-- Mặc định mỗi GV tối đa 5 đề tài phản biện, cho phép tùy chỉnh trong DB

CREATE TABLE IF NOT EXISTS `chitieuphanbien` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `MaChiTieuPB` VARCHAR(30) UNIQUE NOT NULL,
    `MaGV` VARCHAR(20) NOT NULL,
    `MaHocKy` VARCHAR(20) NULL,
    `SoDeTaiToiDa` INT NOT NULL DEFAULT 5,
    `GhiChu` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_ctpb_magv` (`MaGV`),
    INDEX `idx_ctpb_mahocky` (`MaHocKy`),
    CONSTRAINT `fk_ctpb_giangvien` FOREIGN KEY (`MaGV`) REFERENCES `giangvien` (`MaGV`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
