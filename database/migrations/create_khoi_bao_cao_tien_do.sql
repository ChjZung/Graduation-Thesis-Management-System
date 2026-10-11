-- Migration C2: Lưu báo cáo tiến độ theo khối bất biến cho từng lần nộp
-- Tạo bảng `khoibaocaotiendo` để lưu trữ các khối nộp bài (submission blocks)

CREATE TABLE IF NOT EXISTS `khoibaocaotiendo` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `MaKhoi` VARCHAR(30) UNIQUE NOT NULL,
    `MaBaoCao` VARCHAR(20) NOT NULL,
    `MaDeTai` VARCHAR(20) NOT NULL,
    `LanBaoCao` INT NOT NULL,
    `LanNop` INT NOT NULL DEFAULT 1,
    `TieuDe` VARCHAR(200) NOT NULL,
    `NoiDungBaoCao` LONGTEXT NULL,
    `TenFile` VARCHAR(255) NULL,
    `DuongDanFile` VARCHAR(500) NULL,
    `LinkCode` VARCHAR(500) NULL,
    `TrangThai` VARCHAR(50) NOT NULL DEFAULT 'Chờ duyệt',
    `NhanXetGV` LONGTEXT NULL,
    `NguoiNhanXet` VARCHAR(100) NULL,
    `NgayNop` DATETIME NULL,
    `NgayNhanXet` DATETIME NULL,
    `TomTatAI` LONGTEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_khoi_baocao` (`MaBaoCao`),
    INDEX `idx_khoi_detai` (`MaDeTai`),
    INDEX `idx_khoi_lanbaocao` (`LanBaoCao`),
    CONSTRAINT `fk_khoibaocao_baocao` FOREIGN KEY (`MaBaoCao`) REFERENCES `baocaotiendo` (`MaBaoCao`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backfill dữ liệu từ bảng `baocaotiendo` hiện có vào `khoibaocaotiendo` (nếu chưa có)
INSERT INTO `khoibaocaotiendo` (
    `MaKhoi`, `MaBaoCao`, `MaDeTai`, `LanBaoCao`, `LanNop`, `TieuDe`, `NoiDungBaoCao`, 
    `TenFile`, `DuongDanFile`, `TrangThai`, `NhanXetGV`, `NgayNop`, `created_at`, `updated_at`
)
SELECT 
    CONCAT('KHOI_', `MaBaoCao`, '_1'),
    `MaBaoCao`,
    `MaDeTai`,
    `LanBaoCao`,
    1,
    `TieuDe`,
    `NoiDungBaoCao`,
    `TenFile`,
    `DuongDanFile`,
    `TrangThai`,
    `NhanXet`,
    IFNULL(`NgayNop`, NOW()),
    IFNULL(`created_at`, NOW()),
    IFNULL(`updated_at`, NOW())
FROM `baocaotiendo`
WHERE `MaBaoCao` NOT IN (SELECT `MaBaoCao` FROM `khoibaocaotiendo`);
