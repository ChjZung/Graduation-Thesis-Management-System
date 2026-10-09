-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: quanly_doan
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `baocaotiendo`
--

DROP TABLE IF EXISTS `baocaotiendo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `baocaotiendo` (
  `MaBaoCao` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LanBaoCao` int NOT NULL,
  `TieuDe` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NoiDungBaoCao` longtext COLLATE utf8mb4_unicode_ci,
  `NgayNop` date DEFAULT NULL,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chờ nhận xét',
  `TenFile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DuongDanFile` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `NhanXet` longtext COLLATE utf8mb4_unicode_ci,
  `MaMoc` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaDeTai` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaBaoCao`),
  KEY `baocaotiendo_mamoc_foreign` (`MaMoc`),
  KEY `baocaotiendo_madetai_foreign` (`MaDeTai`),
  CONSTRAINT `baocaotiendo_madetai_foreign` FOREIGN KEY (`MaDeTai`) REFERENCES `detai` (`MaDeTai`) ON DELETE CASCADE,
  CONSTRAINT `baocaotiendo_mamoc_foreign` FOREIGN KEY (`MaMoc`) REFERENCES `mocthoigiankhoaluan` (`MaMoc`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `baocaotiendo`
--

LOCK TABLES `baocaotiendo` WRITE;
/*!40000 ALTER TABLE `baocaotiendo` DISABLE KEYS */;
/*!40000 ALTER TABLE `baocaotiendo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bieumau`
--

DROP TABLE IF EXISTS `bieumau`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bieumau` (
  `MaBieuMau` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenBieuMau` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DuongDanFile` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MakeHoach` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaBieuMau`),
  KEY `bieumau_makehoach_foreign` (`MakeHoach`),
  CONSTRAINT `bieumau_makehoach_foreign` FOREIGN KEY (`MakeHoach`) REFERENCES `kehoachkhoaluan` (`MakeHoach`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bieumau`
--

LOCK TABLES `bieumau` WRITE;
/*!40000 ALTER TABLE `bieumau` DISABLE KEYS */;
/*!40000 ALTER TABLE `bieumau` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bomon`
--

DROP TABLE IF EXISTS `bomon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bomon` (
  `MaBoMon` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenBoMon` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TruongBoMon` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MoTa` text COLLATE utf8mb4_unicode_ci,
  `MaKhoa` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaBoMon`),
  KEY `bomon_makhoa_foreign` (`MaKhoa`),
  CONSTRAINT `bomon_makhoa_foreign` FOREIGN KEY (`MaKhoa`) REFERENCES `khoa` (`MaKhoa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bomon`
--

LOCK TABLES `bomon` WRITE;
/*!40000 ALTER TABLE `bomon` DISABLE KEYS */;
INSERT INTO `bomon` VALUES ('BM_ATTT','An toàn thông tin','TS. Lê Hải Đăng',NULL,'CNTT','2026-10-08 08:35:59','2026-10-08 08:35:59'),('BM_CNPM','Công nghệ phần mềm','PGS.TS. Nguyễn Văn Hùng',NULL,'CNTT','2026-10-08 07:05:16','2026-10-08 20:57:05'),('BM_HTTT','Hệ thống thông tin','TS. Phạm Minh Tuấn',NULL,'CNTT','2026-10-08 08:35:59','2026-10-08 08:35:59'),('BM_KHMT','Khoa học máy tính','PGS.TS. Võ Quốc Phong',NULL,'CNTT','2026-10-08 08:35:59','2026-10-08 08:35:59');
/*!40000 ALTER TABLE `bomon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('he-thong-quan-ly-cong-tac-khoa-luan-tot-nghiep-cache-2001230139|127.0.0.1','i:1;',1786356060),('he-thong-quan-ly-cong-tac-khoa-luan-tot-nghiep-cache-2001230139|127.0.0.1:timer','i:1786356060;',1786356060);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chitietduyetdetai`
--

DROP TABLE IF EXISTS `chitietduyetdetai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chitietduyetdetai` (
  `MaDuyet` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgayDuyet` date DEFAULT NULL,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LyDo` longtext COLLATE utf8mb4_unicode_ci,
  `MaGV` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaDeTai` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaDuyet`),
  KEY `chitietduyetdetai_magv_foreign` (`MaGV`),
  KEY `chitietduyetdetai_madetai_foreign` (`MaDeTai`),
  CONSTRAINT `chitietduyetdetai_madetai_foreign` FOREIGN KEY (`MaDeTai`) REFERENCES `detai` (`MaDeTai`) ON DELETE CASCADE,
  CONSTRAINT `chitietduyetdetai_magv_foreign` FOREIGN KEY (`MaGV`) REFERENCES `giangvien` (`MaGV`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chitietduyetdetai`
--

LOCK TABLES `chitietduyetdetai` WRITE;
/*!40000 ALTER TABLE `chitietduyetdetai` DISABLE KEYS */;
/*!40000 ALTER TABLE `chitietduyetdetai` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chitieuhuongdan`
--

DROP TABLE IF EXISTS `chitieuhuongdan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chitieuhuongdan` (
  `MaChiTieu` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `SoNhomToiDa` int NOT NULL DEFAULT '5',
  `NgayPhanBo` date DEFAULT NULL,
  `MaHocKy` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaGV` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaChiTieu`),
  UNIQUE KEY `chitieuhuongdan_magv_mahocky_unique` (`MaGV`,`MaHocKy`),
  KEY `chitieuhuongdan_mahocky_foreign` (`MaHocKy`),
  CONSTRAINT `chitieuhuongdan_magv_foreign` FOREIGN KEY (`MaGV`) REFERENCES `giangvien` (`MaGV`) ON DELETE CASCADE,
  CONSTRAINT `chitieuhuongdan_mahocky_foreign` FOREIGN KEY (`MaHocKy`) REFERENCES `hocky` (`MaHocKy`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chitieuhuongdan`
--

LOCK TABLES `chitieuhuongdan` WRITE;
/*!40000 ALTER TABLE `chitieuhuongdan` DISABLE KEYS */;
/*!40000 ALTER TABLE `chitieuhuongdan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chuyennganh`
--

DROP TABLE IF EXISTS `chuyennganh`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chuyennganh` (
  `MaChuyenNganh` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenChuyenNganh` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Đang hoạt động',
  `MaNganh` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaChuyenNganh`),
  KEY `chuyennganh_manganh_foreign` (`MaNganh`),
  CONSTRAINT `chuyennganh_manganh_foreign` FOREIGN KEY (`MaNganh`) REFERENCES `nganh` (`MaNganh`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chuyennganh`
--

LOCK TABLES `chuyennganh` WRITE;
/*!40000 ALTER TABLE `chuyennganh` DISABLE KEYS */;
/*!40000 ALTER TABLE `chuyennganh` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `danhsachsvdudieukien`
--

DROP TABLE IF EXISTS `danhsachsvdudieukien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `danhsachsvdudieukien` (
  `MaDSDK` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgayXetDuyet` date DEFAULT NULL,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đủ điều kiện',
  `GhiChu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DieuKien` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaSV` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaHocKy` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaDSDK`),
  UNIQUE KEY `danhsachsvdudieukien_masv_mahocky_unique` (`MaSV`,`MaHocKy`),
  KEY `danhsachsvdudieukien_mahocky_foreign` (`MaHocKy`),
  CONSTRAINT `danhsachsvdudieukien_mahocky_foreign` FOREIGN KEY (`MaHocKy`) REFERENCES `hocky` (`MaHocKy`) ON DELETE CASCADE,
  CONSTRAINT `danhsachsvdudieukien_masv_foreign` FOREIGN KEY (`MaSV`) REFERENCES `sinhvien` (`MaSV`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `danhsachsvdudieukien`
--

LOCK TABLES `danhsachsvdudieukien` WRITE;
/*!40000 ALTER TABLE `danhsachsvdudieukien` DISABLE KEYS */;
INSERT INTO `danhsachsvdudieukien` VALUES ('DK_2001210001_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 128/115 | ĐTB: 3.45/2','2001210001','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210001_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 128/115 | ĐTB: 3.45/2','2001210001','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210002_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 125/115 | ĐTB: 3.20/2','2001210002','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210002_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 125/115 | ĐTB: 3.20/2','2001210002','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210003_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 130/115 | ĐTB: 3.65/2','2001210003','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210003_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 130/115 | ĐTB: 3.65/2','2001210003','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210004_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 122/115 | ĐTB: 3.10/2','2001210004','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210004_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 122/115 | ĐTB: 3.10/2','2001210004','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210005_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 129/115 | ĐTB: 3.52/2','2001210005','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210005_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 129/115 | ĐTB: 3.52/2','2001210005','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210006_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 132/115 | ĐTB: 3.70/2','2001210006','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210006_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 132/115 | ĐTB: 3.70/2','2001210006','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210007_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 124/115 | ĐTB: 3.15/2','2001210007','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210007_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 124/115 | ĐTB: 3.15/2','2001210007','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210008_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 126/115 | ĐTB: 3.28/2','2001210008','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210008_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 126/115 | ĐTB: 3.28/2','2001210008','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210009_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 131/115 | ĐTB: 3.60/2','2001210009','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210009_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 131/115 | ĐTB: 3.60/2','2001210009','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210010_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 127/115 | ĐTB: 3.35/2','2001210010','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210010_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 127/115 | ĐTB: 3.35/2','2001210010','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210011_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 125/115 | ĐTB: 3.22/2','2001210011','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210011_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 125/115 | ĐTB: 3.22/2','2001210011','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210012_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 135/115 | ĐTB: 3.85/2','2001210012','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210012_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 135/115 | ĐTB: 3.85/2','2001210012','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210013_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 130/115 | ĐTB: 3.58/2','2001210013','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210013_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 130/115 | ĐTB: 3.58/2','2001210013','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210014_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 121/115 | ĐTB: 3.05/2','2001210014','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210014_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 121/115 | ĐTB: 3.05/2','2001210014','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210015_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 128/115 | ĐTB: 3.42/2','2001210015','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210015_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 128/115 | ĐTB: 3.42/2','2001210015','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210016_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 124/115 | ĐTB: 3.18/2','2001210016','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210016_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 124/115 | ĐTB: 3.18/2','2001210016','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210017_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 129/115 | ĐTB: 3.50/2','2001210017','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210017_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 129/115 | ĐTB: 3.50/2','2001210017','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210018_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 126/115 | ĐTB: 3.30/2','2001210018','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210018_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 126/115 | ĐTB: 3.30/2','2001210018','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210019_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 132/115 | ĐTB: 3.68/2','2001210019','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210019_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 132/115 | ĐTB: 3.68/2','2001210019','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210020_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 123/115 | ĐTB: 3.12/2','2001210020','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210020_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 123/115 | ĐTB: 3.12/2','2001210020','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210021_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 127/115 | ĐTB: 3.38/2','2001210021','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210021_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 127/115 | ĐTB: 3.38/2','2001210021','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210022_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 125/115 | ĐTB: 3.25/2','2001210022','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210022_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 125/115 | ĐTB: 3.25/2','2001210022','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210023_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 130/115 | ĐTB: 3.55/2','2001210023','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210023_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 130/115 | ĐTB: 3.55/2','2001210023','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210024_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 128/115 | ĐTB: 3.48/2','2001210024','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210024_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 128/115 | ĐTB: 3.48/2','2001210024','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210025_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 134/115 | ĐTB: 3.75/2','2001210025','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210025_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 134/115 | ĐTB: 3.75/2','2001210025','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210026_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 122/115 | ĐTB: 3.10/2','2001210026','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210026_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 122/115 | ĐTB: 3.10/2','2001210026','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210027_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 129/115 | ĐTB: 3.51/2','2001210027','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210027_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 129/115 | ĐTB: 3.51/2','2001210027','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210028_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 126/115 | ĐTB: 3.32/2','2001210028','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210028_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 126/115 | ĐTB: 3.32/2','2001210028','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210029_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 131/115 | ĐTB: 3.62/2','2001210029','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210029_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 131/115 | ĐTB: 3.62/2','2001210029','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210030_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 125/115 | ĐTB: 3.20/2','2001210030','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210030_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 125/115 | ĐTB: 3.20/2','2001210030','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210031_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 133/115 | ĐTB: 3.72/2','2001210031','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210031_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 133/115 | ĐTB: 3.72/2','2001210031','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210032_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 124/115 | ĐTB: 3.15/2','2001210032','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210032_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 124/115 | ĐTB: 3.15/2','2001210032','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210033_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 128/115 | ĐTB: 3.44/2','2001210033','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210033_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 128/115 | ĐTB: 3.44/2','2001210033','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210034_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 123/115 | ĐTB: 3.14/2','2001210034','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210034_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 123/115 | ĐTB: 3.14/2','2001210034','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210035_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 130/115 | ĐTB: 3.56/2','2001210035','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210035_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 130/115 | ĐTB: 3.56/2','2001210035','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210036_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 126/115 | ĐTB: 3.28/2','2001210036','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210036_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 126/115 | ĐTB: 3.28/2','2001210036','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210037_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 127/115 | ĐTB: 3.36/2','2001210037','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210037_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 127/115 | ĐTB: 3.36/2','2001210037','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210038_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 132/115 | ĐTB: 3.66/2','2001210038','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210038_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 132/115 | ĐTB: 3.66/2','2001210038','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210039_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 135/115 | ĐTB: 3.82/2','2001210039','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210039_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 135/115 | ĐTB: 3.82/2','2001210039','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210040_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 125/115 | ĐTB: 3.22/2','2001210040','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210040_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 125/115 | ĐTB: 3.22/2','2001210040','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210041_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 129/115 | ĐTB: 3.49/2','2001210041','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210041_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 129/115 | ĐTB: 3.49/2','2001210041','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210042_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 127/115 | ĐTB: 3.34/2','2001210042','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210042_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 127/115 | ĐTB: 3.34/2','2001210042','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210043_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 131/115 | ĐTB: 3.63/2','2001210043','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210043_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 131/115 | ĐTB: 3.63/2','2001210043','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210044_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 124/115 | ĐTB: 3.17/2','2001210044','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210044_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 124/115 | ĐTB: 3.17/2','2001210044','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210045_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 130/115 | ĐTB: 3.54/2','2001210045','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210045_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 130/115 | ĐTB: 3.54/2','2001210045','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210046_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 136/115 | ĐTB: 3.88/2','2001210046','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210046_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 136/115 | ĐTB: 3.88/2','2001210046','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210047_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 126/115 | ĐTB: 3.30/2','2001210047','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210047_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 126/115 | ĐTB: 3.30/2','2001210047','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210048_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 125/115 | ĐTB: 3.21/2','2001210048','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210048_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 125/115 | ĐTB: 3.21/2','2001210048','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210049_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 128/115 | ĐTB: 3.46/2','2001210049','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210049_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 128/115 | ĐTB: 3.46/2','2001210049','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001210050_HK2617','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 123/115 | ĐTB: 3.11/2','2001210050','HK2617_1','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001210050_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 123/115 | ĐTB: 3.11/2','2001210050','HK2627_2','2026-10-08 20:48:12','2026-08-09 16:59:00'),('DK_2001230136_HK2627','2026-08-09','Đủ điều kiện','Đủ điều kiện làm khóa luận tốt nghiệp','Tín chỉ: 120/115 | ĐTB: 3.49/2','2001230136','HK2627_2','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001230137_HK2627','2026-08-09','Đủ điều kiện','Tạo mới hồ sơ & cấp tài khoản vào học kỳ','Tín chỉ: 120 | ĐTB: 2.50','2001230137','HK2627_2','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001230138_HK2627','2026-08-09','Đủ điều kiện','Tạo mới hồ sơ & cấp tài khoản vào học kỳ','Tín chỉ: 120 | ĐTB: 2.50','2001230138','HK2627_2','2026-08-09 16:59:00','2026-08-09 16:59:00'),('DK_2001230139_HK2627','2026-08-10','Đủ điều kiện','Tạo mới hồ sơ & cấp tài khoản vào học kỳ','Tín chỉ: 120 | ĐTB: 3.00','2001230139','HK2627_2','2026-08-10 03:00:00','2026-08-10 03:00:00');
/*!40000 ALTER TABLE `danhsachsvdudieukien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detai`
--

DROP TABLE IF EXISTS `detai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detai` (
  `MaDeTai` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenDeTai` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MoTa` longtext COLLATE utf8mb4_unicode_ci,
  `YeuCau` longtext COLLATE utf8mb4_unicode_ci,
  `LinhVuc` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SoLuongSinhVienToiDa` int unsigned NOT NULL DEFAULT '2',
  `FileDeCuong` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaNganh` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chờ duyệt',
  `LyDoTuChoi` text COLLATE utf8mb4_unicode_ci,
  `NgayDeXuat` date DEFAULT NULL,
  `NgayDuyetBM` datetime DEFAULT NULL,
  `NguoiDuyetBM` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `NgayDuyetKhoa` datetime DEFAULT NULL,
  `NguoiDuyetKhoa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `NgayCongBo` datetime DEFAULT NULL,
  `NgayDuyet` datetime DEFAULT NULL,
  `MaGV` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaHocKy` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `HocPhan` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Khóa luận tốt nghiệp',
  `MaHocPhan` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaDeTai`),
  KEY `detai_magv_foreign` (`MaGV`),
  KEY `detai_mahocky_foreign` (`MaHocKy`),
  KEY `detai_mahocphan_foreign` (`MaHocPhan`),
  CONSTRAINT `detai_magv_foreign` FOREIGN KEY (`MaGV`) REFERENCES `giangvien` (`MaGV`) ON DELETE CASCADE,
  CONSTRAINT `detai_mahocky_foreign` FOREIGN KEY (`MaHocKy`) REFERENCES `hocky` (`MaHocKy`) ON DELETE CASCADE,
  CONSTRAINT `detai_mahocphan_foreign` FOREIGN KEY (`MaHocPhan`) REFERENCES `hocphan` (`MaHocPhan`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detai`
--

LOCK TABLES `detai` WRITE;
/*!40000 ALTER TABLE `detai` DISABLE KEYS */;
INSERT INTO `detai` VALUES ('DT01','Hệ thống bán hàng','wqewretrytj','qwwertryt','Công Nghệ Phần Mềm & Trí Tuệ Nhân Tạo',3,'de_cuong/sample.docx',NULL,'Đã công bố',NULL,'2026-08-09','2026-10-09 09:15:11','GV00000001','2026-08-09 23:59:00','GV00000002','2026-08-09 23:59:00',NULL,'GV00000004','HK2627_2','Khóa luận cử nhân','HP_KLCN_CNPM','2026-08-09 16:59:00','2026-10-09 02:15:11'),('DT02','Hệ thống bán nước hoa','sdafsdgf','asdfggdas','Công Nghệ Phần Mềm & Trí Tuệ Nhân Tạo',3,'storage/de_cuong/de_cuong_1791532450_mau-de-cuong-khoa-luan-cu-nhan-huit.docx',NULL,'Đã công bố',NULL,'2026-08-10','2026-08-10 10:00:00','GV00000001','2026-08-10 10:00:00','GV00000002','2026-08-10 10:00:00',NULL,'GV00000004','HK2627_2','Khóa luận cử nhân','HP_KLCN_CNPM','2026-08-10 03:00:00','2026-08-10 03:00:00'),('DT03','xây dựng hệ thống quản lí khóa luận tốt nghiệp','jkhfiu3wq9hy','wudgu8wyhiur3hw9o','Công Nghệ Phần Mềm & Trí Tuệ Nhân Tạo',3,NULL,NULL,'Từ chối','không phù hợp','2026-08-10',NULL,NULL,NULL,NULL,NULL,NULL,'GV00000004','HK2627_2','Khóa luận cử nhân','HP_KLCN_CNPM','2026-08-10 03:00:00','2026-08-09 16:59:00'),('DT04','hệ tho61ngquan3 lý nah2 hàng thông mih','wsjagfdyway98ifgwlifyhilkewjdshifhiewshfitest','wjhgeuqwghuihyeriuw','Công Nghệ Phần Mềm & Trí Tuệ Nhân Tạo',3,NULL,NULL,'Đã công bố',NULL,'2026-08-09','2026-08-09 23:59:00','GV00000001','2026-08-09 23:59:00','GV00000002','2026-08-09 23:59:00',NULL,'GV00000004','HK2627_2','Khóa luận tốt nghiệp','HP_KLTN_CNPM','2026-08-09 16:59:00','2026-08-09 16:59:00');
/*!40000 ALTER TABLE `detai` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `giangvien`
--

DROP TABLE IF EXISTS `giangvien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `giangvien` (
  `MaGV` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaTK` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `HoTen` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgaySinh` date DEFAULT NULL,
  `GioiTinh` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SoDienThoai` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `HocHam` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `HocVi` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang công tác',
  `MaBoMon` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaGV`),
  KEY `giangvien_matk_foreign` (`MaTK`),
  KEY `giangvien_mabomon_foreign` (`MaBoMon`),
  CONSTRAINT `giangvien_mabomon_foreign` FOREIGN KEY (`MaBoMon`) REFERENCES `bomon` (`MaBoMon`) ON DELETE SET NULL,
  CONSTRAINT `giangvien_matk_foreign` FOREIGN KEY (`MaTK`) REFERENCES `taikhoan` (`MaTK`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `giangvien`
--

LOCK TABLES `giangvien` WRITE;
/*!40000 ALTER TABLE `giangvien` DISABLE KEYS */;
INSERT INTO `giangvien` VALUES ('GV00000001','TK_GV00000001','PGS.TS. Nguyễn Văn Hùng','1975-04-12','Nam','hungnv@huit.edu.vn','0903123001','Phó Giáo sư','Tiến sĩ','Đang công tác','BM_CNPM','2026-09-29 03:38:44','2026-10-08 21:30:08'),('GV00000002','TK_GV00000002','TS. Trần Anh Dũng','1979-08-20','Nam','dungta@huit.edu.vn','0903123002',NULL,'Tiến sĩ','Đang công tác','BM_CNPM','2026-09-29 03:38:44','2026-10-08 21:30:08'),('GV00000003','TK_GV00000003','TS. Phạm Minh Tuấn','1982-11-15','Nam','tuanpm@huit.edu.vn','0903123003',NULL,'Tiến sĩ','Đang công tác','BM_HTTT','2026-09-29 03:38:45','2026-10-08 21:30:08'),('GV00000004','TK_GV00000004','TS. Trịnh Văn Thành','1980-02-18','Nam','thanhtv@huit.edu.vn','0903123004',NULL,'Tiến sĩ','Đang công tác','BM_CNPM','2026-09-29 03:38:46','2026-10-08 21:30:08'),('GV00000005','TK_GV00000005','TS. Lê Hải Đăng','1984-06-25','Nam','danglh@huit.edu.vn','0903123005',NULL,'Tiến sĩ','Đang công tác','BM_ATTT','2026-09-29 03:38:46','2026-10-08 21:30:08'),('GV00000006','TK_GV00000006','PGS.TS. Võ Quốc Phong','1976-09-30','Nam','phongvq@huit.edu.vn','0903123006','Phó Giáo sư','Tiến sĩ','Đang công tác','BM_KHMT','2026-09-29 03:38:47','2026-10-08 21:30:08'),('GV00000007','TK_GV00000007','TS. Đỗ Anh Minh','1985-03-14','Nam','minhda@huit.edu.vn','0903123007',NULL,'Tiến sĩ','Đang công tác','BM_CNPM','2026-09-29 03:38:47','2026-10-08 21:30:08'),('GV00000008','TK_GV00000008','PGS.TS. Trần Đình Nam','1974-12-05','Nam','namtd@huit.edu.vn','0903123008','Phó Giáo sư','Tiến sĩ','Đang công tác','BM_HTTT','2026-09-29 03:38:48','2026-10-08 21:30:08'),('GV00000009','TK_GV00000009','GS.TS. Lê Thị Mai','1970-07-22','Nữ','mailt@huit.edu.vn','0903123009','Giáo sư','Tiến sĩ','Đang công tác','BM_ATTT','2026-09-29 03:38:48','2026-10-08 21:30:08'),('GV00000010','TK_GV00000010','PGS.TS. Vũ Trọng Hưng','1973-05-19','Nam','hungvt@huit.edu.vn','0903123010','Phó Giáo sư','Tiến sĩ','Đang công tác','BM_KHMT','2026-09-29 03:38:49','2026-10-08 21:30:08'),('GV00000011','TK_GV00000011','TS. Bùi Thu Hà','1983-10-10','Nữ','habth@huit.edu.vn','0903123011',NULL,'Tiến sĩ','Đang công tác','BM_CNPM','2026-09-29 03:38:49','2026-10-08 21:30:08'),('GV00000012','TK_GV00000012','TS. Nguyễn Văn An','1981-01-28','Nam','annv@huit.edu.vn','0903123012',NULL,'Tiến sĩ','Đang công tác','BM_HTTT','2026-09-29 03:38:50','2026-10-08 21:30:08'),('GV00000013','TK_GV00000013','PGS.TS. Đỗ Hoài Nam','1977-03-08','Nam','namdh@huit.edu.vn','0903123013','Phó Giáo sư','Tiến sĩ','Đang công tác','BM_ATTT','2026-09-29 03:38:50','2026-10-08 21:30:08'),('GV00000014','TK_GV00000014','TS. Trương Tuyết Mai','1986-09-17','Nữ','maitt@huit.edu.vn','0903123014',NULL,'Tiến sĩ','Đang công tác','BM_KHMT','2026-09-29 03:38:51','2026-10-08 21:30:08'),('GV00000015','TK_GV00000015','TS. Vũ Anh Quân','1982-04-03','Nam','quanva@huit.edu.vn','0903123015',NULL,'Tiến sĩ','Đang công tác','BM_CNPM','2026-09-29 03:38:51','2026-10-08 21:30:08'),('GV00000016','TK_GV00000016','TS. Hồ Vĩnh Thắng','1980-11-29','Nam','thanghv@huit.edu.vn','0903123016',NULL,'Tiến sĩ','Đang công tác','BM_HTTT','2026-09-29 03:38:52','2026-10-08 21:30:08'),('GV00000017','TK_GV00000017','PGS.TS. Đặng Quốc Thịnh','1972-08-16','Nam','thinhdq@huit.edu.vn','0903123017','Phó Giáo sư','Tiến sĩ','Đang công tác','BM_ATTT','2026-09-29 03:38:53','2026-10-08 21:30:08'),('GV00000018','TK_GV00000018','TS. Phan Trọng Nghĩa','1983-02-11','Nam','nghiapt@huit.edu.vn','0903123018',NULL,'Tiến sĩ','Đang công tác','BM_KHMT','2026-09-29 03:38:53','2026-10-08 21:30:08'),('GV00000019','TK_GV00000019','TS. Chu Văn Quân','1981-12-24','Nam','quancv@huit.edu.vn','0903123019',NULL,'Tiến sĩ','Đang công tác','BM_CNPM','2026-09-29 03:38:54','2026-10-08 21:30:08'),('GV00000020','TK_GV00000020','TS. Phạm Thị Bích Loan','1978-06-30','Nữ','loanptb@huit.edu.vn','0903123020',NULL,'Tiến sĩ','Đang công tác','BM_HTTT','2026-09-29 03:38:54','2026-10-08 21:30:08'),('GV00000021','TK_GV00000021','ThS. Đặng Hải Giang','1988-05-14','Nam','giangdh@huit.edu.vn','0903123021',NULL,'Thạc sĩ','Đang công tác','BM_ATTT','2026-09-29 03:38:55','2026-10-08 21:30:08'),('GV00000022','TK_GV00000022','ThS. Trần Thị Bích','1989-09-02','Nữ','bichtb@huit.edu.vn','0903123022',NULL,'Thạc sĩ','Đang công tác','BM_CNPM','2026-09-29 03:38:55','2026-10-08 21:30:08'),('GV00000023','TK_GV00000023','TS. Lê Hoàng Cường','1984-12-18','Nam','cuonglh@huit.edu.vn','0903123023',NULL,'Tiến sĩ','Đang công tác','BM_HTTT','2026-09-29 03:38:56','2026-10-08 21:30:08'),('GV00000024','TK_GV00000024','ThS. Ngô Đức Hải','1990-07-07','Nam','haind@huit.edu.vn','0903123024',NULL,'Thạc sĩ','Đang công tác','BM_CNPM','2026-09-29 03:38:56','2026-10-08 21:30:08'),('GV00000025','TK_GV00000025','TS. Dương Thúy Hằng','1983-04-26','Nữ','hangdt@huit.edu.vn','0903123025',NULL,'Tiến sĩ','Đang công tác','BM_ATTT','2026-09-29 03:38:57','2026-10-08 21:30:08'),('GV00000026','TK_GV00000026','ThS. Lý Thanh Lan','1991-02-14','Nữ','lanlt@huit.edu.vn','0903123026',NULL,'Thạc sĩ','Đang công tác','BM_HTTT','2026-09-29 03:38:57','2026-10-08 21:30:08'),('GV00000027','TK_GV00000027','TS. Trịnh Văn Khang','1982-08-09','Nam','khangtv@huit.edu.vn','0903123027',NULL,'Tiến sĩ','Đang công tác','BM_KHMT','2026-09-29 03:38:58','2026-10-08 21:30:08'),('GV00000028','TK_GV00000028','ThS. Mai Tuyết Nga','1987-11-21','Nữ','ngamt@huit.edu.vn','0903123028',NULL,'Thạc sĩ','Đang công tác','BM_ATTT','2026-09-29 03:38:58','2026-10-08 21:30:08'),('GV00000029','TK_GV00000029','TS. Võ Đình Phúc','1980-03-31','Nam','phucvd@huit.edu.vn','0903123029',NULL,'Tiến sĩ','Đang công tác','BM_HTTT','2026-09-29 03:38:59','2026-10-08 21:30:08'),('GV00000030','TK_GV00000030','ThS. Hoàng Thị Em','1992-01-19','Nữ','emht@huit.edu.vn','0903123030',NULL,'Thạc sĩ','Đang công tác','BM_KHMT','2026-09-29 03:38:59','2026-10-08 21:30:08'),('GV00000031','TK_GV00000031','TS. Đinh Hoàng Xuân','1985-06-12','Nữ','xuandh@huit.edu.vn','0903123031',NULL,'Tiến sĩ','Đang công tác','BM_ATTT','2026-09-29 03:39:00','2026-10-08 21:30:08'),('GV00000032','TK_GV00000032','TS. Cao Thái Vinh','1984-10-05','Nam','vinhct@huit.edu.vn','0903123032',NULL,'Tiến sĩ','Đang công tác','BM_HTTT','2026-09-29 03:39:00','2026-10-08 21:30:08'),('GV00000033','TK_GV00000033','TS. Phan Văn Phúc','1979-05-23','Nam','phucpv@huit.edu.vn','0903123033',NULL,'Tiến sĩ','Đang công tác','BM_KHMT','2026-09-29 03:39:01','2026-10-08 21:30:08'),('GV00000034','TK_GV00000034','TS. Hoàng Yến Linh','1986-07-15','Nữ','linhhy@huit.edu.vn','0903123034',NULL,'Tiến sĩ','Đang công tác','BM_ATTT','2026-09-29 03:39:02','2026-10-08 21:30:08'),('GV00000035','TK_GV00000035','TS. Bùi Mai Hoa','1981-09-09','Nữ','hoabm@huit.edu.vn','0903123035',NULL,'Tiến sĩ','Đang công tác','BM_CNPM','2026-09-29 03:39:02','2026-10-08 21:30:08'),('GV00000036','TK_GV00000036','TS. Võ Duy Hùng','1983-02-27','Nam','hungvd@huit.edu.vn','0903123036',NULL,'Tiến sĩ','Đang công tác','BM_HTTT','2026-09-29 03:39:03','2026-10-08 21:30:08'),('GV00000037','TK_GV00000037','PGS.TS. Mai Thế Vinh','1975-11-11','Nam','vinhtm@huit.edu.vn','0903123037','Phó Giáo sư','Tiến sĩ','Đang công tác','BM_ATTT','2026-09-29 03:39:03','2026-10-08 21:30:08'),('GV00000038','TK_GV00000038','ThS. Nguyễn Hoàng Anh','1989-08-18','Nam','anhnh@huit.edu.vn','0903123038',NULL,'Thạc sĩ','Đang công tác','BM_CNPM','2026-09-29 03:39:04','2026-10-08 21:30:08'),('GV00000039','TK_GV00000039','ThS. Lê Phương Thảo','1990-12-03','Nữ','thaolp@huit.edu.vn','0903123039',NULL,'Thạc sĩ','Đang công tác','BM_KHMT','2026-09-29 03:39:04','2026-10-08 21:30:08'),('GV00000040','TK_GV00000040','TS. Vũ Đình Khôi','1984-04-17','Nam','khoivd@huit.edu.vn','0903123040',NULL,'Tiến sĩ','Đang công tác','BM_HTTT','2026-09-29 03:39:05','2026-10-08 21:30:08'),('GV00000041','TK_GV00000041','ThS. Huỳnh Quốc Bảo','1991-03-25','Nam','baohq@huit.edu.vn','0903123041',NULL,'Thạc sĩ','Đang công tác','BM_ATTT','2026-09-29 03:39:06','2026-10-08 21:30:08'),('GV00000042','TK_GV00000042','TS. Tôn Nữ Quỳnh Như','1987-10-30','Nữ','nhutnq@huit.edu.vn','0903123042',NULL,'Tiến sĩ','Đang công tác','BM_KHMT','2026-09-29 03:39:06','2026-10-08 21:30:08'),('GV00000043','TK_GV00000043','ThS. Cù Huy Sơn','1988-06-19','Nam','sonch@huit.edu.vn','0903123043',NULL,'Thạc sĩ','Đang công tác','BM_HTTT','2026-09-29 03:39:07','2026-10-08 21:30:08'),('GV00000044','TK_GV00000044','TS. Thạch Văn Tài','1983-01-08','Nam','taitv@huit.edu.vn','0903123044',NULL,'Tiến sĩ','Đang công tác','BM_ATTT','2026-09-29 03:39:07','2026-10-08 21:30:08'),('GV00000045','TK_GV00000045','ThS. Uông Thị Uyên','1992-09-14','Nữ','uyenut@huit.edu.vn','0903123045',NULL,'Thạc sĩ','Đang công tác','BM_CNPM','2026-09-29 03:39:08','2026-10-08 21:30:08'),('GV00000046','TK_GV00000046','TS. Phí Văn Việt','1985-05-22','Nam','vietpv@huit.edu.vn','0903123046',NULL,'Tiến sĩ','Đang công tác','BM_KHMT','2026-09-29 03:39:09','2026-10-08 21:30:08'),('GV00000047','TK_GV00000047','ThS. Mạc Thị Xuyến','1990-08-04','Nữ','xuyenmt@huit.edu.vn','0903123047',NULL,'Thạc sĩ','Đang công tác','BM_HTTT','2026-09-29 03:39:09','2026-10-08 21:30:08'),('GV00000048','TK_GV00000048','TS. La Quốc Ý','1982-12-16','Nam','ylq@huit.edu.vn','0903123048',NULL,'Tiến sĩ','Đang công tác','BM_ATTT','2026-09-29 03:39:10','2026-10-08 21:30:08'),('GV00000049','TK_GV00000049','ThS. Sầm Ngọc Anh','1993-04-11','Nữ','anhsn@huit.edu.vn','0903123049',NULL,'Thạc sĩ','Đang công tác','BM_CNPM','2026-09-29 03:39:10','2026-10-08 21:30:08'),('GV00000050','TK_GV00000050','TS. Nông Văn Bắc','1981-07-29','Nam','bacnv@huit.edu.vn','0903123050',NULL,'Tiến sĩ','Đang công tác','BM_KHMT','2026-09-29 03:39:11','2026-10-08 21:30:08');
/*!40000 ALTER TABLE `giangvien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `giaovu`
--

DROP TABLE IF EXISTS `giaovu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `giaovu` (
  `MaGVu` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaTK` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `HoTen` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SoDienThoai` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ChucVu` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaKhoa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaGVu`),
  KEY `giaovu_matk_foreign` (`MaTK`),
  KEY `giaovu_makhoa_foreign` (`MaKhoa`),
  CONSTRAINT `giaovu_makhoa_foreign` FOREIGN KEY (`MaKhoa`) REFERENCES `khoa` (`MaKhoa`) ON DELETE SET NULL,
  CONSTRAINT `giaovu_matk_foreign` FOREIGN KEY (`MaTK`) REFERENCES `taikhoan` (`MaTK`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `giaovu`
--

LOCK TABLES `giaovu` WRITE;
/*!40000 ALTER TABLE `giaovu` DISABLE KEYS */;
INSERT INTO `giaovu` VALUES ('GVU01','TK_ADMIN','Quản trị viên Giáo vụ','giaovu@huit.edu.vn','0908123456','Giáo vụ Khoa',NULL,'2026-09-23 05:09:02','2026-09-29 02:03:39');
/*!40000 ALTER TABLE `giaovu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hocky`
--

DROP TABLE IF EXISTS `hocky`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hocky` (
  `MaHocKy` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenHocKy` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NamHoc` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgayDiHoc` date DEFAULT NULL,
  `NgayKetThuc` date DEFAULT NULL,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang diễn ra',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaHocKy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hocky`
--

LOCK TABLES `hocky` WRITE;
/*!40000 ALTER TABLE `hocky` DISABLE KEYS */;
INSERT INTO `hocky` VALUES ('HK2425_1','Học kỳ 1','2024-2025','2024-09-02','2025-01-15','Đã kết thúc','2026-10-08 06:34:22','2026-10-08 06:34:22'),('HK2617_1','Học kỳ 1','2026-2027','2026-07-20','2027-01-16','Đã kết thúc','2026-10-08 06:34:22','2026-10-08 09:39:18'),('HK2627_2','Học kỳ 2 — Năm học 2026–2027','2026-2027','2026-02-15','2026-06-30','Đang diễn ra','2026-10-08 11:26:18','2026-10-08 11:26:18');
/*!40000 ALTER TABLE `hocky` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hocphan`
--

DROP TABLE IF EXISTS `hocphan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hocphan` (
  `MaHocPhan` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenHocPhan` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `SoTinChi` int NOT NULL DEFAULT '3',
  `SoTietLT` int NOT NULL DEFAULT '0',
  `SoTietTH` int NOT NULL DEFAULT '0',
  `SoTietKhac` int NOT NULL DEFAULT '0',
  `MaKhoa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaBoMon` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LoaiHocPhan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chuyên ngành',
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang áp dụng',
  `MoTa` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaHocPhan`),
  KEY `hocphan_makhoa_foreign` (`MaKhoa`),
  KEY `hocphan_mabomon_foreign` (`MaBoMon`),
  CONSTRAINT `hocphan_mabomon_foreign` FOREIGN KEY (`MaBoMon`) REFERENCES `bomon` (`MaBoMon`) ON DELETE SET NULL,
  CONSTRAINT `hocphan_makhoa_foreign` FOREIGN KEY (`MaKhoa`) REFERENCES `khoa` (`MaKhoa`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hocphan`
--

LOCK TABLES `hocphan` WRITE;
/*!40000 ALTER TABLE `hocphan` DISABLE KEYS */;
INSERT INTO `hocphan` VALUES ('HP_ATTT01','An toàn mạng máy tính',3,30,30,0,'CNTT','BM_ATTT','Chuyên ngành','Đang sử dụng','Học phần thuộc Bộ môn An toàn thông tin','2026-10-08 08:35:59','2026-10-08 08:35:59'),('HP_CNPM01','Lập trình Web nâng cao',3,30,30,0,'CNTT','BM_CNPM','Chuyên ngành','Đang sử dụng','Học phần chuyên ngành Công nghệ phần mềm','2026-10-08 08:35:59','2026-10-08 08:35:59'),('HP_CNPM02','Đồ án chuyên ngành Công nghệ phần mềm',3,15,60,0,'CNTT','BM_CNPM','Đồ án','Đang sử dụng','Đồ án chuyên ngành thuộc Bộ môn CNPM','2026-10-08 08:35:59','2026-10-08 08:35:59'),('HP_HTTT01','Phân tích thiết kế hệ thống',3,45,0,0,'CNTT','BM_HTTT','Chuyên ngành','Đang sử dụng','Học phần thuộc Bộ môn Hệ thống thông tin','2026-10-08 08:35:59','2026-10-08 08:35:59'),('HP_KHMT01','Trí tuệ nhân tạo và Học máy',3,30,30,0,'CNTT','BM_KHMT','Chuyên ngành','Đang sử dụng','Học phần chuyên ngành thuộc Bộ môn Khoa học máy tính','2026-10-08 23:07:10','2026-10-08 23:07:10'),('HP_KLCN_ATTT','Khóa luận cử nhân',10,0,0,150,'CNTT','BM_ATTT','Khóa luận','Đang sử dụng','Học phần khóa luận cử nhân thuộc An toàn thông tin','2026-10-08 23:07:10','2026-10-08 23:07:10'),('HP_KLCN_CNPM','Khóa luận cử nhân',10,0,0,150,'CNTT','BM_CNPM','Khóa luận','Đang sử dụng','Học phần khóa luận cử nhân thuộc Công nghệ phần mềm','2026-10-08 23:07:10','2026-10-08 23:07:10'),('HP_KLCN_HTTT','Khóa luận cử nhân',10,0,0,150,'CNTT','BM_HTTT','Khóa luận','Đang sử dụng','Học phần khóa luận cử nhân thuộc Hệ thống thông tin','2026-10-08 23:07:10','2026-10-08 23:07:10'),('HP_KLCN_KHMT','Khóa luận cử nhân',10,0,0,150,'CNTT','BM_KHMT','Khóa luận','Đang sử dụng','Học phần khóa luận cử nhân thuộc Khoa học máy tính','2026-10-08 23:07:10','2026-10-08 23:07:10'),('HP_KLKS_ATTT','Khóa luận kỹ sư',10,0,0,150,'CNTT','BM_ATTT','Khóa luận','Đang sử dụng','Học phần khóa luận kỹ sư thuộc An toàn thông tin','2026-10-08 23:07:10','2026-10-08 23:07:10'),('HP_KLKS_CNPM','Khóa luận kỹ sư',10,0,0,150,'CNTT','BM_CNPM','Khóa luận','Đang sử dụng','Học phần khóa luận kỹ sư thuộc Công nghệ phần mềm','2026-10-08 23:07:10','2026-10-08 23:07:10'),('HP_KLKS_HTTT','Khóa luận kỹ sư',10,0,0,150,'CNTT','BM_HTTT','Khóa luận','Đang sử dụng','Học phần khóa luận kỹ sư thuộc Hệ thống thông tin','2026-10-08 23:07:10','2026-10-08 23:07:10'),('HP_KLKS_KHMT','Khóa luận kỹ sư',10,0,0,150,'CNTT','BM_KHMT','Khóa luận','Đang sử dụng','Học phần khóa luận kỹ sư thuộc Khoa học máy tính','2026-10-08 23:07:10','2026-10-08 23:07:10'),('HP_KLTN_ATTT','Khóa luận tốt nghiệp',10,0,0,150,'CNTT','BM_ATTT','Khóa luận','Đang sử dụng','Học phần khóa luận tốt nghiệp thuộc An toàn thông tin','2026-10-08 23:07:10','2026-10-08 23:07:10'),('HP_KLTN_CNPM','Khóa luận tốt nghiệp',10,0,0,150,'CNTT','BM_CNPM','Khóa luận','Đang sử dụng','Học phần khóa luận tốt nghiệp thuộc Công nghệ phần mềm','2026-10-08 23:07:10','2026-10-08 23:07:10'),('HP_KLTN_HTTT','Khóa luận tốt nghiệp',10,0,0,150,'CNTT','BM_HTTT','Khóa luận','Đang sử dụng','Học phần khóa luận tốt nghiệp thuộc Hệ thống thông tin','2026-10-08 23:07:10','2026-10-08 23:07:10'),('HP_KLTN_KHMT','Khóa luận tốt nghiệp',10,0,0,150,'CNTT','BM_KHMT','Khóa luận','Đang sử dụng','Học phần khóa luận tốt nghiệp thuộc Khoa học máy tính','2026-10-08 23:07:10','2026-10-08 23:07:10');
/*!40000 ALTER TABLE `hocphan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hocphan_hocky`
--

DROP TABLE IF EXISTS `hocphan_hocky`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hocphan_hocky` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `MaHocPhan` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaHocKy` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang mở',
  `GhiChu` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hocphan_hocky_mahocphan_mahocky_unique` (`MaHocPhan`,`MaHocKy`),
  KEY `hocphan_hocky_mahocky_foreign` (`MaHocKy`),
  CONSTRAINT `hocphan_hocky_mahocky_foreign` FOREIGN KEY (`MaHocKy`) REFERENCES `hocky` (`MaHocKy`) ON DELETE CASCADE,
  CONSTRAINT `hocphan_hocky_mahocphan_foreign` FOREIGN KEY (`MaHocPhan`) REFERENCES `hocphan` (`MaHocPhan`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hocphan_hocky`
--

LOCK TABLES `hocphan_hocky` WRITE;
/*!40000 ALTER TABLE `hocphan_hocky` DISABLE KEYS */;
INSERT INTO `hocphan_hocky` VALUES (58,'HP_ATTT01','HK2627_2','Đang mở','Mở cho HK2 2026-2027','2026-10-08 23:07:10','2026-10-08 23:07:10'),(59,'HP_CNPM01','HK2627_2','Đang mở','Mở cho HK2 2026-2027','2026-10-08 23:07:10','2026-10-08 23:07:10'),(60,'HP_CNPM02','HK2627_2','Đang mở','Mở cho HK2 2026-2027','2026-10-08 23:07:10','2026-10-08 23:07:10'),(61,'HP_HTTT01','HK2627_2','Đang mở','Mở cho HK2 2026-2027','2026-10-08 23:07:10','2026-10-08 23:07:10'),(62,'HP_KHMT01','HK2627_2','Đang mở','Mở cho HK2 2026-2027','2026-10-08 23:07:10','2026-10-08 23:07:10'),(63,'HP_KLCN_ATTT','HK2627_2','Đang mở','Mở cho HK2 2026-2027','2026-10-08 23:07:10','2026-10-08 23:07:10'),(64,'HP_KLCN_CNPM','HK2627_2','Đang mở','Mở cho HK2 2026-2027','2026-10-08 23:07:10','2026-10-08 23:07:10'),(65,'HP_KLCN_HTTT','HK2627_2','Đang mở','Mở cho HK2 2026-2027','2026-10-08 23:07:10','2026-10-08 23:07:10'),(66,'HP_KLCN_KHMT','HK2627_2','Đang mở','Mở cho HK2 2026-2027','2026-10-08 23:07:10','2026-10-08 23:07:10'),(67,'HP_KLKS_ATTT','HK2627_2','Đang mở','Mở cho HK2 2026-2027','2026-10-08 23:07:10','2026-10-08 23:07:10'),(68,'HP_KLKS_CNPM','HK2627_2','Đang mở','Mở cho HK2 2026-2027','2026-10-08 23:07:10','2026-10-08 23:07:10'),(69,'HP_KLKS_HTTT','HK2627_2','Đang mở','Mở cho HK2 2026-2027','2026-10-08 23:07:10','2026-10-08 23:07:10'),(70,'HP_KLKS_KHMT','HK2627_2','Đang mở','Mở cho HK2 2026-2027','2026-10-08 23:07:10','2026-10-08 23:07:10'),(71,'HP_KLTN_ATTT','HK2627_2','Đang mở','Mở cho HK2 2026-2027','2026-10-08 23:07:10','2026-10-08 23:07:10'),(72,'HP_KLTN_CNPM','HK2627_2','Đang mở','Mở cho HK2 2026-2027','2026-10-08 23:07:10','2026-10-08 23:07:10'),(73,'HP_KLTN_HTTT','HK2627_2','Đang mở','Mở cho HK2 2026-2027','2026-10-08 23:07:10','2026-10-08 23:07:10'),(74,'HP_KLTN_KHMT','HK2627_2','Đang mở','Mở cho HK2 2026-2027','2026-10-08 23:07:10','2026-10-08 23:07:10');
/*!40000 ALTER TABLE `hocphan_hocky` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hoidong`
--

DROP TABLE IF EXISTS `hoidong`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hoidong` (
  `MaHoiDong` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenHoiDong` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ThoiGianBatDau` datetime DEFAULT NULL,
  `ThoiGianKetThuc` datetime DEFAULT NULL,
  `DiaDiem` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chưa diễn ra',
  `GhiChu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `NgayBaoVe` date DEFAULT NULL,
  `MaDeTai` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaGV` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaHocKy` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaHoiDong`),
  KEY `hoidong_madetai_foreign` (`MaDeTai`),
  KEY `hoidong_magv_foreign` (`MaGV`),
  KEY `hoidong_mahocky_foreign` (`MaHocKy`),
  CONSTRAINT `hoidong_madetai_foreign` FOREIGN KEY (`MaDeTai`) REFERENCES `detai` (`MaDeTai`) ON DELETE SET NULL,
  CONSTRAINT `hoidong_magv_foreign` FOREIGN KEY (`MaGV`) REFERENCES `giangvien` (`MaGV`) ON DELETE SET NULL,
  CONSTRAINT `hoidong_mahocky_foreign` FOREIGN KEY (`MaHocKy`) REFERENCES `hocky` (`MaHocKy`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hoidong`
--

LOCK TABLES `hoidong` WRITE;
/*!40000 ALTER TABLE `hoidong` DISABLE KEYS */;
/*!40000 ALTER TABLE `hoidong` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hosobaove`
--

DROP TABLE IF EXISTS `hosobaove`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hosobaove` (
  `MaHoSo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgayLap` date DEFAULT NULL,
  `NgayNop` date DEFAULT NULL,
  `NgayXacNhan` date DEFAULT NULL,
  `XacNhanGVHD` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chưa duyệt',
  `ThoiGianBaoVe` datetime DEFAULT NULL,
  `PhongBaoVe` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `FileBanChinhSua` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `NgayNopBanChinhSua` datetime DEFAULT NULL,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chờ xác nhận',
  `GhiChu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaGVu` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaHoiDong` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaNhom` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaDeTai` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaHoSo`),
  KEY `hosobaove_magvu_foreign` (`MaGVu`),
  KEY `hosobaove_mahoidong_foreign` (`MaHoiDong`),
  KEY `hosobaove_manhom_foreign` (`MaNhom`),
  KEY `hosobaove_madetai_foreign` (`MaDeTai`),
  CONSTRAINT `hosobaove_madetai_foreign` FOREIGN KEY (`MaDeTai`) REFERENCES `detai` (`MaDeTai`) ON DELETE SET NULL,
  CONSTRAINT `hosobaove_magvu_foreign` FOREIGN KEY (`MaGVu`) REFERENCES `giaovu` (`MaGVu`) ON DELETE SET NULL,
  CONSTRAINT `hosobaove_mahoidong_foreign` FOREIGN KEY (`MaHoiDong`) REFERENCES `hoidong` (`MaHoiDong`) ON DELETE SET NULL,
  CONSTRAINT `hosobaove_manhom_foreign` FOREIGN KEY (`MaNhom`) REFERENCES `nhom` (`MaNhom`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hosobaove`
--

LOCK TABLES `hosobaove` WRITE;
/*!40000 ALTER TABLE `hosobaove` DISABLE KEYS */;
/*!40000 ALTER TABLE `hosobaove` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kehoachkhoaluan`
--

DROP TABLE IF EXISTS `kehoachkhoaluan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kehoachkhoaluan` (
  `MakeHoach` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenKeHoach` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NoiDung` longtext COLLATE utf8mb4_unicode_ci,
  `FileDinhKem` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Dự thảo',
  `NgayTao` date DEFAULT NULL,
  `MaHocKy` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaGVu` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MakeHoach`),
  KEY `kehoachkhoaluan_mahocky_foreign` (`MaHocKy`),
  KEY `kehoachkhoaluan_magvu_foreign` (`MaGVu`),
  CONSTRAINT `kehoachkhoaluan_magvu_foreign` FOREIGN KEY (`MaGVu`) REFERENCES `giaovu` (`MaGVu`) ON DELETE SET NULL,
  CONSTRAINT `kehoachkhoaluan_mahocky_foreign` FOREIGN KEY (`MaHocKy`) REFERENCES `hocky` (`MaHocKy`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kehoachkhoaluan`
--

LOCK TABLES `kehoachkhoaluan` WRITE;
/*!40000 ALTER TABLE `kehoachkhoaluan` DISABLE KEYS */;
INSERT INTO `kehoachkhoaluan` VALUES ('KH_2026_R0JW','Kế hoạch Khóa luận HK1 2026-2027','Văn bản thông báo chính thức số 27/TB-KCNTT','official_documents/Official_Plan_1791478724_jRYUcu.pdf','ĐÃ CÔNG BỐ','2026-10-08','HK2627_2','GVU01','2026-10-08 09:58:44','2026-10-08 09:59:22');
/*!40000 ALTER TABLE `kehoachkhoaluan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ketquasinhvien`
--

DROP TABLE IF EXISTS `ketquasinhvien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ketquasinhvien` (
  `MaKetQua` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DiemPhanBien` decimal(4,2) DEFAULT NULL,
  `DiemHoiDongTB` decimal(4,2) DEFAULT NULL,
  `DiemTongKet` decimal(4,2) DEFAULT NULL,
  `KetQua` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LoaiKhoaLuan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'KLCN',
  `NhanXetChung` longtext COLLATE utf8mb4_unicode_ci,
  `NgayCham` date DEFAULT NULL,
  `MaSV` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaHoSo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaHocKy` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaKetQua`),
  KEY `ketquasinhvien_masv_foreign` (`MaSV`),
  KEY `ketquasinhvien_mahoso_foreign` (`MaHoSo`),
  KEY `ketquasinhvien_mahocky_foreign` (`MaHocKy`),
  CONSTRAINT `ketquasinhvien_mahocky_foreign` FOREIGN KEY (`MaHocKy`) REFERENCES `hocky` (`MaHocKy`) ON DELETE SET NULL,
  CONSTRAINT `ketquasinhvien_mahoso_foreign` FOREIGN KEY (`MaHoSo`) REFERENCES `hosobaove` (`MaHoSo`) ON DELETE SET NULL,
  CONSTRAINT `ketquasinhvien_masv_foreign` FOREIGN KEY (`MaSV`) REFERENCES `sinhvien` (`MaSV`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ketquasinhvien`
--

LOCK TABLES `ketquasinhvien` WRITE;
/*!40000 ALTER TABLE `ketquasinhvien` DISABLE KEYS */;
/*!40000 ALTER TABLE `ketquasinhvien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `khoa`
--

DROP TABLE IF EXISTS `khoa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `khoa` (
  `MaKhoa` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenKhoa` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TruongKhoa` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MoTa` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaKhoa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `khoa`
--

LOCK TABLES `khoa` WRITE;
/*!40000 ALTER TABLE `khoa` DISABLE KEYS */;
INSERT INTO `khoa` VALUES ('ATTT','Khoa An toàn Thông tin & Mạng','TS. Trịnh Văn Thành',NULL,'2026-09-29 03:37:09','2026-09-29 03:37:09'),('CNTT','Khoa Công nghệ Thông tin','TS. Trần Anh Dũng',NULL,'2026-09-29 03:37:09','2026-10-08 20:59:40');
/*!40000 ALTER TABLE `khoa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lichgaphuongdan`
--

DROP TABLE IF EXISTS `lichgaphuongdan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lichgaphuongdan` (
  `MaLichGap` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ThoiGianBatDau` datetime NOT NULL,
  `ThoiGianKetThuc` datetime NOT NULL,
  `DiaDiem` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `NoiDung` longtext COLLATE utf8mb4_unicode_ci,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đã lên lịch',
  `NgayTao` date DEFAULT NULL,
  `MaNhom` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaGV` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaLichGap`),
  KEY `lichgaphuongdan_manhom_foreign` (`MaNhom`),
  KEY `lichgaphuongdan_magv_foreign` (`MaGV`),
  CONSTRAINT `lichgaphuongdan_magv_foreign` FOREIGN KEY (`MaGV`) REFERENCES `giangvien` (`MaGV`) ON DELETE CASCADE,
  CONSTRAINT `lichgaphuongdan_manhom_foreign` FOREIGN KEY (`MaNhom`) REFERENCES `nhom` (`MaNhom`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lichgaphuongdan`
--

LOCK TABLES `lichgaphuongdan` WRITE;
/*!40000 ALTER TABLE `lichgaphuongdan` DISABLE KEYS */;
/*!40000 ALTER TABLE `lichgaphuongdan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lop`
--

DROP TABLE IF EXISTS `lop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lop` (
  `MaLop` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenLop` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KhoaHoc` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaNganh` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaKhoa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaLop`),
  KEY `lop_manganh_foreign` (`MaNganh`),
  KEY `lop_makhoa_foreign` (`MaKhoa`),
  CONSTRAINT `lop_makhoa_foreign` FOREIGN KEY (`MaKhoa`) REFERENCES `khoa` (`MaKhoa`) ON DELETE SET NULL,
  CONSTRAINT `lop_manganh_foreign` FOREIGN KEY (`MaNganh`) REFERENCES `nganh` (`MaNganh`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lop`
--

LOCK TABLES `lop` WRITE;
/*!40000 ALTER TABLE `lop` DISABLE KEYS */;
INSERT INTO `lop` VALUES ('12DHTH01','12DHTH01','2021-2025','7480201','CNTT','2026-10-08 08:26:20','2026-10-08 08:26:20'),('12DHTH02','12DHTH02','2021-2025','7480201','CNTT','2026-10-08 08:26:20','2026-10-08 08:26:20'),('12DHTH03','12DHTH03','2021-2025','7480201','CNTT','2026-10-08 08:26:20','2026-10-08 08:26:20'),('12DHTH04','12DHTH04','2021-2025','7480201','CNTT','2026-10-08 08:26:20','2026-10-08 08:26:20'),('12DHTH05','12DHTH05','2021-2025','7480201','CNTT','2026-10-08 08:26:20','2026-10-08 08:26:20');
/*!40000 ALTER TABLE `lop` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000001_create_cache_table',1),(2,'0001_01_01_000002_create_jobs_table',1),(3,'2026_09_15_000001_create_ql_khoa_luan_tot_nghiep_tables',1),(4,'2026_09_15_000002_create_yeu_cau_doi_mat_khau_table',1),(5,'2026_09_24_000001_add_extended_fields_to_detai_table',2),(6,'2026_09_24_000002_add_hoc_phan_to_detai_table',3),(7,'2026_09_29_075407_update_system_roles_and_workflow_fields',4),(8,'2026_09_29_080000_add_truong_khoa_and_truong_bo_mon_columns',5),(9,'2026_09_29_083000_add_magv_and_filedinhkem_to_thongbao_table',5),(10,'2026_10_07_075824_add_mahocky_to_nhom_table',5),(11,'2026_10_07_202500_create_hoc_phan_table',5),(12,'2026_10_07_202600_add_mahocphan_to_nhom_and_detai',5),(13,'2026_10_07_224000_create_hoc_phan_hoc_ky_table',5),(14,'2026_10_08_153450_add_tiet_hoc_to_hocphan_table',6),(15,'2026_10_08_234500_add_doituong_and_filedinhkem_to_kehoach_tables',7),(16,'2026_10_09_020000_update_phieuchamdiem_for_council_rubric',8),(17,'2026_10_08_000000_create_password_reset_tokens_table',9),(18,'2026_10_09_050000_standardize_giangvien_and_taikhoan_structure',10),(19,'2026_10_09_060000_standardize_hocphan_status_and_sync_hocky',11),(20,'2026_10_09_070000_assign_all_hocphan_to_bomon_and_clean_dung_chung',12);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mocthoigiankhoaluan`
--

DROP TABLE IF EXISTS `mocthoigiankhoaluan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mocthoigiankhoaluan` (
  `MaMoc` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenMoc` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DoiTuongThucHien` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Tất cả',
  `NgayBatDau` date NOT NULL,
  `NgayKetThuc` date NOT NULL,
  `MoTa` longtext COLLATE utf8mb4_unicode_ci,
  `MakeHoach` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaMoc`),
  KEY `mocthoigiankhoaluan_makehoach_foreign` (`MakeHoach`),
  CONSTRAINT `mocthoigiankhoaluan_makehoach_foreign` FOREIGN KEY (`MakeHoach`) REFERENCES `kehoachkhoaluan` (`MakeHoach`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mocthoigiankhoaluan`
--

LOCK TABLES `mocthoigiankhoaluan` WRITE;
/*!40000 ALTER TABLE `mocthoigiankhoaluan` DISABLE KEYS */;
INSERT INTO `mocthoigiankhoaluan` VALUES ('MOC_R0JW_01','Sinh viên tạo nhóm trên phần mềm HUIT-STUDENT (mỗi nhóm 3 SV/1 đề tài)','Sinh viên','2026-08-10','2026-08-10','Trích xuất tự động từ văn bản 27/TB-KCNTT [TAO_NHOM]','KH_2026_R0JW','2026-10-08 09:58:44','2026-10-08 09:58:44'),('MOC_R0JW_02','Nhóm trưởng các nhóm thực hiện đăng ký đề tài theo đúng chuyên ngành','Nhóm trưởng','2026-08-11','2026-08-11','Trích xuất tự động từ văn bản 27/TB-KCNTT [DANG_KY_DE_TAI]','KH_2026_R0JW','2026-10-08 09:58:44','2026-10-08 09:58:44'),('MOC_R0JW_03','Xử lý các trường hợp ngoại lệ trực tiếp tại VPK','Giáo vụ Khoa','2026-08-12','2026-08-12','Trích xuất tự động từ văn bản 27/TB-KCNTT [XU_LY_NGOAI_LE]','KH_2026_R0JW','2026-10-08 09:58:44','2026-10-08 09:58:44'),('MOC_R0JW_04','Khoa tổng hợp & thông báo chính thức danh sách SV đăng ký đề tài và GVHD','Khoa CNTT','2026-08-14','2026-08-14','Trích xuất tự động từ văn bản 27/TB-KCNTT [CONG_BO_GVHD]','KH_2026_R0JW','2026-10-08 09:58:44','2026-10-08 09:58:44'),('MOC_R0JW_05','Sinh viên chủ động liên hệ GVHD qua email để trao đổi kế hoạch thực hiện','Sinh viên & GVHD','2026-08-14','2026-08-17','Trích xuất tự động từ văn bản 27/TB-KCNTT [LIEN_HE_GVHD]','KH_2026_R0JW','2026-10-08 09:58:44','2026-10-08 09:58:44'),('MOC_R0JW_06','Thời gian thực hiện khóa luận (KLTN & KLCN: 12 tuần)','Sinh viên & GVHD','2026-08-17','2026-11-08','Trích xuất tự động từ văn bản 27/TB-KCNTT [THUC_HIEN]','KH_2026_R0JW','2026-10-08 09:58:44','2026-10-08 09:58:44'),('MOC_R0JW_07','Sinh viên nộp báo cáo Khóa luận cử nhân, Khóa luận tốt nghiệp','Sinh viên','2026-11-11','2026-11-11','Trích xuất tự động từ văn bản 27/TB-KCNTT [NOP_BAO_CAO]','KH_2026_R0JW','2026-10-08 09:58:44','2026-10-08 09:58:44'),('MOC_R0JW_08','Thông báo lịch làm việc của các Hội đồng bảo vệ KLCN, KLTN đến SV','Khoa CNTT','2026-11-13','2026-11-13','Trích xuất tự động từ văn bản 27/TB-KCNTT [THONG_BAO_HOI_DONG]','KH_2026_R0JW','2026-10-08 09:58:44','2026-10-08 09:58:44'),('MOC_R0JW_09','Tổ chức các buổi bảo vệ khóa luận tốt nghiệp trước Hội đồng chấm','Khoa & Hội đồng','2026-11-18','2026-11-28','Trích xuất tự động từ văn bản 27/TB-KCNTT [BAO_VE]','KH_2026_R0JW','2026-10-08 09:58:44','2026-10-08 09:58:44');
/*!40000 ALTER TABLE `mocthoigiankhoaluan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nganh`
--

DROP TABLE IF EXISTS `nganh`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nganh` (
  `MaNganh` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenNganh` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaKhoa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaNganh`),
  KEY `nganh_makhoa_foreign` (`MaKhoa`),
  CONSTRAINT `nganh_makhoa_foreign` FOREIGN KEY (`MaKhoa`) REFERENCES `khoa` (`MaKhoa`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nganh`
--

LOCK TABLES `nganh` WRITE;
/*!40000 ALTER TABLE `nganh` DISABLE KEYS */;
INSERT INTO `nganh` VALUES ('7480104','Hệ thống Thông tin','CNTT','2026-09-29 03:38:01','2026-09-29 03:38:01'),('7480201','Công nghệ Thông tin','CNTT','2026-09-29 03:38:01','2026-09-29 03:38:01'),('7480202','An toàn Thông tin','CNTT','2026-09-29 03:38:01','2026-09-29 03:38:01');
/*!40000 ALTER TABLE `nganh` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nhom`
--

DROP TABLE IF EXISTS `nhom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nhom` (
  `MaNhom` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenNhom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang hoạt động',
  `NgayTao` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `MaHocKy` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaHocPhan` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`MaNhom`),
  KEY `nhom_mahocky_foreign` (`MaHocKy`),
  KEY `nhom_mahocphan_foreign` (`MaHocPhan`),
  CONSTRAINT `nhom_mahocky_foreign` FOREIGN KEY (`MaHocKy`) REFERENCES `hocky` (`MaHocKy`) ON DELETE CASCADE,
  CONSTRAINT `nhom_mahocphan_foreign` FOREIGN KEY (`MaHocPhan`) REFERENCES `hocphan` (`MaHocPhan`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nhom`
--

LOCK TABLES `nhom` WRITE;
/*!40000 ALTER TABLE `nhom` DISABLE KEYS */;
INSERT INTO `nhom` VALUES ('N01','Nhóm 2001210044','Đã duyệt','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00','HK2627_2','HP_KLCN_CNPM'),('N02','Nhóm 2001210045','Đang hoạt động','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00','HK2627_2','HP_KLCN_CNPM'),('N03','Nhóm 2001230136','Đang hoạt động','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00','HK2627_2','HP_KLCN_CNPM'),('N04','Nhóm 2001230138','Đang hoạt động','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00','HK2627_2','HP_KLCN_CNPM'),('N05','Nhóm 2001230138','Đang hoạt động','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00','HK2627_2','HP_CNPM02'),('N06','Nhóm 2001230138','Đang hoạt động','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00','HK2627_2','HP_KLTN_CNPM'),('N07','Nhóm 2001230138','Đang hoạt động','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00','HK2627_2','HP_CNPM01'),('N08','Nhóm 2001230138','Đang hoạt động','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00','HK2627_2','HP_KLKS_CNPM');
/*!40000 ALTER TABLE `nhom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_reset_tokens_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phancongphanbien`
--

DROP TABLE IF EXISTS `phancongphanbien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phancongphanbien` (
  `MaPhanCong` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `VaiTro` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Phản biện',
  `NgayPhanCong` date DEFAULT NULL,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đã phân công',
  `NhanXet` longtext COLLATE utf8mb4_unicode_ci,
  `KetQua` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `NgayDanhGia` datetime DEFAULT NULL,
  `MaGV` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaDeTai` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaPhanCong`),
  KEY `phancongphanbien_magv_foreign` (`MaGV`),
  KEY `phancongphanbien_madetai_foreign` (`MaDeTai`),
  CONSTRAINT `phancongphanbien_madetai_foreign` FOREIGN KEY (`MaDeTai`) REFERENCES `detai` (`MaDeTai`) ON DELETE CASCADE,
  CONSTRAINT `phancongphanbien_magv_foreign` FOREIGN KEY (`MaGV`) REFERENCES `giangvien` (`MaGV`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phancongphanbien`
--

LOCK TABLES `phancongphanbien` WRITE;
/*!40000 ALTER TABLE `phancongphanbien` DISABLE KEYS */;
INSERT INTO `phancongphanbien` VALUES ('PC01','Phản biện đề cương','2026-08-10','Đã phản biện','k co y kien gi','Đạt','2026-08-10 10:00:00','GV00000024','DT02','2026-08-10 03:00:00','2026-08-10 03:00:00');
/*!40000 ALTER TABLE `phancongphanbien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phieuchamdiem`
--

DROP TABLE IF EXISTS `phieuchamdiem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phieuchamdiem` (
  `MaPhieuChamDiem` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Diem` decimal(4,2) NOT NULL,
  `LoaiKhoaLuan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'KLCN',
  `NgayCham` date DEFAULT NULL,
  `NhanXet` longtext COLLATE utf8mb4_unicode_ci,
  `ChiTietDiem` longtext COLLATE utf8mb4_unicode_ci,
  `MaDeTai` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaHoiDong` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaGV` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaSV` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaHoSo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaHocKy` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaPhieuChamDiem`),
  KEY `phieuchamdiem_madetai_foreign` (`MaDeTai`),
  KEY `phieuchamdiem_mahoidong_foreign` (`MaHoiDong`),
  KEY `phieuchamdiem_magv_foreign` (`MaGV`),
  KEY `phieuchamdiem_mahoso_foreign` (`MaHoSo`),
  KEY `phieuchamdiem_mahocky_foreign` (`MaHocKy`),
  KEY `phieuchamdiem_masv_foreign` (`MaSV`),
  CONSTRAINT `phieuchamdiem_madetai_foreign` FOREIGN KEY (`MaDeTai`) REFERENCES `detai` (`MaDeTai`) ON DELETE CASCADE,
  CONSTRAINT `phieuchamdiem_magv_foreign` FOREIGN KEY (`MaGV`) REFERENCES `giangvien` (`MaGV`) ON DELETE CASCADE,
  CONSTRAINT `phieuchamdiem_mahocky_foreign` FOREIGN KEY (`MaHocKy`) REFERENCES `hocky` (`MaHocKy`) ON DELETE SET NULL,
  CONSTRAINT `phieuchamdiem_mahoidong_foreign` FOREIGN KEY (`MaHoiDong`) REFERENCES `hoidong` (`MaHoiDong`) ON DELETE SET NULL,
  CONSTRAINT `phieuchamdiem_mahoso_foreign` FOREIGN KEY (`MaHoSo`) REFERENCES `hosobaove` (`MaHoSo`) ON DELETE SET NULL,
  CONSTRAINT `phieuchamdiem_masv_foreign` FOREIGN KEY (`MaSV`) REFERENCES `sinhvien` (`MaSV`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phieuchamdiem`
--

LOCK TABLES `phieuchamdiem` WRITE;
/*!40000 ALTER TABLE `phieuchamdiem` DISABLE KEYS */;
/*!40000 ALTER TABLE `phieuchamdiem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phieudangky`
--

DROP TABLE IF EXISTS `phieudangky`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phieudangky` (
  `MaDangKy` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgayDangKy` date DEFAULT NULL,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chờ duyệt',
  `NgayDuyet` date DEFAULT NULL,
  `LyDoTuChoi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaNhom` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaDeTai` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaDangKy`),
  KEY `phieudangky_manhom_foreign` (`MaNhom`),
  KEY `phieudangky_madetai_foreign` (`MaDeTai`),
  CONSTRAINT `phieudangky_madetai_foreign` FOREIGN KEY (`MaDeTai`) REFERENCES `detai` (`MaDeTai`) ON DELETE CASCADE,
  CONSTRAINT `phieudangky_manhom_foreign` FOREIGN KEY (`MaNhom`) REFERENCES `nhom` (`MaNhom`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phieudangky`
--

LOCK TABLES `phieudangky` WRITE;
/*!40000 ALTER TABLE `phieudangky` DISABLE KEYS */;
INSERT INTO `phieudangky` VALUES ('DK_BJOUZD','2026-08-11','Đã duyệt','2026-08-11',NULL,'N02','DT02','2026-08-11 02:30:00','2026-08-11 02:30:00');
/*!40000 ALTER TABLE `phieudangky` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quydinhkhoaluan`
--

DROP TABLE IF EXISTS `quydinhkhoaluan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quydinhkhoaluan` (
  `MaQuyDinh` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenQuyDinh` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `GiaTri` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MoTa` longtext COLLATE utf8mb4_unicode_ci,
  `MakeHoach` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaQuyDinh`),
  KEY `quydinhkhoaluan_makehoach_foreign` (`MakeHoach`),
  CONSTRAINT `quydinhkhoaluan_makehoach_foreign` FOREIGN KEY (`MakeHoach`) REFERENCES `kehoachkhoaluan` (`MakeHoach`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quydinhkhoaluan`
--

LOCK TABLES `quydinhkhoaluan` WRITE;
/*!40000 ALTER TABLE `quydinhkhoaluan` DISABLE KEYS */;
INSERT INTO `quydinhkhoaluan` VALUES ('QD_R0JW_01','Số tín chỉ tích lũy tối thiểu làm KLTN','115 Tín chỉ','Sinh viên phải tích lũy tối thiểu 115 tín chỉ và không nợ các môn điều kiện tiên quyết.','KH_2026_R0JW','2026-10-08 09:58:44','2026-10-08 09:58:44'),('QD_R0JW_02','Điểm trung bình tích lũy tối thiểu (GPA)','2.0 GPA','Điểm trung bình tích lũy thang điểm 4.0 đạt từ 2.0 trở lên tại thời điểm xét duyệt.','KH_2026_R0JW','2026-10-08 09:58:44','2026-10-08 09:58:44'),('QD_R0JW_03','Số lượng sinh viên tối đa trong một nhóm','2 - 3 Sinh viên','Mỗi nhóm khóa luận gồm 2 đến 3 sinh viên (trừ trường hợp đặc biệt được Trưởng khoa duyệt).','KH_2026_R0JW','2026-10-08 09:58:44','2026-10-08 09:58:44'),('QD_R0JW_04','Định mức đề tài tối đa một giảng viên hướng dẫn','Tối đa 5 Đề tài / GV','Mỗi giảng viên hướng dẫn tối đa 5 đề tài/nhóm trong một học kỳ để đảm bảo chất lượng hướng dẫn.','KH_2026_R0JW','2026-10-08 09:58:44','2026-10-08 09:58:44'),('QD_R0JW_05','Ngưỡng trùng lặp kiểm tra Turnitin tối đa','<= 20%','Báo cáo toàn văn quét qua hệ thống Turnitin có độ trùng lặp không được vượt quá 20%.','KH_2026_R0JW','2026-10-08 09:58:44','2026-10-08 09:58:44'),('QD_R0JW_06','Điểm tổng kết tối thiểu để đạt Khóa luận','>= 5.0 Điểm','Điểm tổng kết bảo vệ theo trọng số (GVHD 30%, GVPB 30%, Hội đồng 40%) phải đạt từ 5.0 trở lên.','KH_2026_R0JW','2026-10-08 09:58:44','2026-10-08 09:58:44'),('QD_R0JW_07','Thời gian thực hiện khóa luận tốt nghiệp','12 - 15 Tuần','Thời gian từ khi công bố đề tài chính thức đến khi nộp báo cáo hoàn chỉnh bảo vệ.','KH_2026_R0JW','2026-10-08 09:58:44','2026-10-08 09:58:44'),('QD_R0JW_08','Yêu cầu hồ sơ và sản phẩm nộp bảo vệ','03 Cuốn báo cáo + Source code + Slide','Sinh viên nộp cuốn báo cáo đúng format, mã nguồn hoàn chỉnh và slide trình bày trước ngày bảo vệ.','KH_2026_R0JW','2026-10-08 09:58:44','2026-10-08 09:58:44');
/*!40000 ALTER TABLE `quydinhkhoaluan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sinhvien`
--

DROP TABLE IF EXISTS `sinhvien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sinhvien` (
  `MaSV` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaTK` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `HoTen` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NgaySinh` date DEFAULT NULL,
  `GioiTinh` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SoDienThoai` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `NgayNhapHoc` date DEFAULT NULL,
  `KhoaHoc` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SoTinChiTichLuy` int NOT NULL DEFAULT '0',
  `DiemTichLuy` decimal(4,2) NOT NULL DEFAULT '0.00',
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đang học',
  `MaKhoa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaNganh` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaLop` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaSV`),
  KEY `sinhvien_matk_foreign` (`MaTK`),
  KEY `sinhvien_makhoa_foreign` (`MaKhoa`),
  KEY `sinhvien_manganh_foreign` (`MaNganh`),
  KEY `sinhvien_malop_foreign` (`MaLop`),
  CONSTRAINT `sinhvien_makhoa_foreign` FOREIGN KEY (`MaKhoa`) REFERENCES `khoa` (`MaKhoa`) ON DELETE SET NULL,
  CONSTRAINT `sinhvien_malop_foreign` FOREIGN KEY (`MaLop`) REFERENCES `lop` (`MaLop`) ON DELETE SET NULL,
  CONSTRAINT `sinhvien_manganh_foreign` FOREIGN KEY (`MaNganh`) REFERENCES `nganh` (`MaNganh`) ON DELETE SET NULL,
  CONSTRAINT `sinhvien_matk_foreign` FOREIGN KEY (`MaTK`) REFERENCES `taikhoan` (`MaTK`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sinhvien`
--

LOCK TABLES `sinhvien` WRITE;
/*!40000 ALTER TABLE `sinhvien` DISABLE KEYS */;
INSERT INTO `sinhvien` VALUES ('2001210001','2001210001','Nguyễn Hoàng Long','2003-05-12','Nam','2001210001@st.huit.edu.vn','0978001001',NULL,'2021-2025',128,3.45,'Đang học','CNTT','7480201','12DHTH01','2026-09-29 03:39:48','2026-10-08 08:26:20'),('2001210002','2001210002','Trần Minh Quân','2003-08-20','Nam','2001210002@st.huit.edu.vn','0978001002',NULL,'2021-2025',125,3.20,'Đang học','CNTT','7480201','12DHTH02','2026-09-29 03:39:49','2026-10-08 08:26:20'),('2001210003','2001210003','Lê Phương Anh','2003-11-15','Nữ','2001210003@st.huit.edu.vn','0978001003',NULL,'2021-2025',130,3.65,'Đang học','CNTT','7480201','12DHTH03','2026-09-29 03:39:50','2026-10-08 08:26:20'),('2001210004','2001210004','Phạm Đức Duy','2003-02-18','Nam','2001210004@st.huit.edu.vn','0978001004',NULL,'2021-2025',122,3.10,'Đang học','CNTT','7480201','12DHTH04','2026-09-29 03:39:50','2026-10-08 08:26:20'),('2001210005','2001210005','Hoàng Thùy Linh','2003-06-25','Nữ','2001210005@st.huit.edu.vn','0978001005',NULL,'2021-2025',129,3.52,'Đang học','CNTT','7480201','12DHTH05','2026-09-29 03:39:51','2026-10-08 08:26:20'),('2001210006','2001210006','Huỳnh Quốc Huy','2003-09-30','Nam','2001210006@st.huit.edu.vn','0978001006',NULL,'2021-2025',132,3.70,'Đang học','CNTT',NULL,NULL,'2026-09-29 03:39:51','2026-09-29 03:39:51'),('2001210007','2001210007','Võ Thị Kim Ngân','2003-03-14','Nữ','2001210007@st.huit.edu.vn','0978001007',NULL,'2021-2025',124,3.15,'Đang học','CNTT',NULL,NULL,'2026-09-29 03:39:52','2026-09-29 03:39:52'),('2001210008','2001210008','Đặng Thành Nam','2003-12-05','Nam','2001210008@st.huit.edu.vn','0978001008',NULL,'2021-2025',126,3.28,'Đang học','CNTT','7480104',NULL,'2026-09-29 03:39:52','2026-09-29 03:39:52'),('2001210009','2001210009','Bùi Thị Thanh Hằng','2003-07-22','Nữ','2001210009@st.huit.edu.vn','0978001009',NULL,'2021-2025',131,3.60,'Đang học','CNTT','7480104',NULL,'2026-09-29 03:39:53','2026-09-29 03:39:53'),('2001210010','2001210010','Đỗ Trọng Hiếu','2003-05-19','Nam','2001210010@st.huit.edu.vn','0978001010',NULL,'2021-2025',127,3.35,'Đang học','CNTT','7480202',NULL,'2026-09-29 03:39:53','2026-09-29 03:39:53'),('2001210011','2001210011','Hồ Diệu Linh','2003-10-10','Nữ','2001210011@st.huit.edu.vn','0978001011',NULL,'2021-2025',125,3.22,'Đang học','CNTT','7480202',NULL,'2026-09-29 03:39:54','2026-09-29 03:39:54'),('2001210012','2001210012','Ngô Văn Tuấn','2003-01-28','Nam','2001210012@st.huit.edu.vn','0978001012',NULL,'2021-2025',135,3.85,'Đang học','CNTT',NULL,NULL,'2026-09-29 03:39:54','2026-09-29 03:39:54'),('2001210013','2001210013','Dương Bảo Ngọc','2003-03-08','Nữ','2001210013@st.huit.edu.vn','0978001013',NULL,'2021-2025',130,3.58,'Đang học','CNTT',NULL,NULL,'2026-09-29 03:39:55','2026-09-29 03:39:55'),('2001210014','2001210014','Lý Gia Kiệt','2003-09-17','Nam','2001210014@st.huit.edu.vn','0978001014',NULL,'2021-2025',121,3.05,'Đang học',NULL,NULL,NULL,'2026-09-29 03:39:55','2026-09-29 03:39:55'),('2001210015','2001210015','Mai Thảo My','2003-04-03','Nữ','2001210015@st.huit.edu.vn','0978001015',NULL,'2021-2025',128,3.42,'Đang học',NULL,NULL,NULL,'2026-09-29 03:39:56','2026-09-29 03:39:56'),('2001210016','2001210016','Trịnh Quốc Khánh','2003-11-29','Nam','2001210016@st.huit.edu.vn','0978001016',NULL,'2021-2025',124,3.18,'Đang học',NULL,NULL,NULL,'2026-09-29 03:39:56','2026-09-29 03:39:56'),('2001210017','2001210017','Phan Thu Trang','2003-08-16','Nữ','2001210017@st.huit.edu.vn','0978001017',NULL,'2021-2025',129,3.50,'Đang học',NULL,NULL,NULL,'2026-09-29 03:39:57','2026-09-29 03:39:57'),('2001210018','2001210018','Chu Minh Trí','2003-02-11','Nam','2001210018@st.huit.edu.vn','0978001018',NULL,'2021-2025',126,3.30,'Đang học',NULL,NULL,NULL,'2026-09-29 03:39:57','2026-09-29 03:39:57'),('2001210019','2001210019','Lương Bích Phượng','2003-12-24','Nữ','2001210019@st.huit.edu.vn','0978001019',NULL,'2021-2025',132,3.68,'Đang học',NULL,NULL,NULL,'2026-09-29 03:39:58','2026-09-29 03:39:58'),('2001210020','2001210020','Tạ Hữu Đạt','2003-06-30','Nam','2001210020@st.huit.edu.vn','0978001020',NULL,'2021-2025',123,3.12,'Đang học',NULL,NULL,NULL,'2026-09-29 03:39:58','2026-09-29 03:39:58'),('2001210021','2001210021','Vũ Mỹ Duyên','2003-05-14','Nữ','2001210021@st.huit.edu.vn','0978001021',NULL,'2021-2025',127,3.38,'Đang học',NULL,NULL,NULL,'2026-09-29 03:39:59','2026-09-29 03:39:59'),('2001210022','2001210022','Cao Đình Phúc','2003-09-02','Nam','2001210022@st.huit.edu.vn','0978001022',NULL,'2021-2025',125,3.25,'Đang học',NULL,NULL,NULL,'2026-09-29 03:39:59','2026-09-29 03:39:59'),('2001210023','2001210023','Lâm Ngọc Bích','2003-12-18','Nữ','2001210023@st.huit.edu.vn','0978001023',NULL,'2021-2025',130,3.55,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:00','2026-09-29 03:40:00'),('2001210024','2001210024','Đinh Tiến Dũng','2003-07-07','Nam','2001210024@st.huit.edu.vn','0978001024',NULL,'2021-2025',128,3.48,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:01','2026-09-29 03:40:01'),('2001210025','2001210025','Quách Ánh Tuyết','2003-04-26','Nữ','2001210025@st.huit.edu.vn','0978001025',NULL,'2021-2025',134,3.75,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:01','2026-09-29 03:40:01'),('2001210026','2001210026','Ân Hoàng Phúc','2003-02-14','Nam','2001210026@st.huit.edu.vn','0978001026',NULL,'2021-2025',122,3.10,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:02','2026-09-29 03:40:02'),('2001210027','2001210027','Phùng Mai Chi','2003-08-09','Nữ','2001210027@st.huit.edu.vn','0978001027',NULL,'2021-2025',129,3.51,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:02','2026-09-29 03:40:02'),('2001210028','2001210028','Diệp Văn Hùng','2003-11-21','Nam','2001210028@st.huit.edu.vn','0978001028',NULL,'2021-2025',126,3.32,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:03','2026-09-29 03:40:03'),('2001210029','2001210029','Trang Kiều Oanh','2003-03-31','Nữ','2001210029@st.huit.edu.vn','0978001029',NULL,'2021-2025',131,3.62,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:03','2026-09-29 03:40:03'),('2001210030','2001210030','Lưu Quang Khải','2003-01-19','Nam','2001210030@st.huit.edu.vn','0978001030',NULL,'2021-2025',125,3.20,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:04','2026-09-29 03:40:04'),('2001210031','2001210031','Kiều Thanh Tâm','2003-06-12','Nữ','2001210031@st.huit.edu.vn','0978001031',NULL,'2021-2025',133,3.72,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:04','2026-09-29 03:40:04'),('2001210032','2001210032','Nghiêm Bá Thông','2003-10-05','Nam','2001210032@st.huit.edu.vn','0978001032',NULL,'2021-2025',124,3.15,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:05','2026-09-29 03:40:05'),('2001210033','2001210033','Bạch Hoài An','2003-05-23','Nữ','2001210033@st.huit.edu.vn','0978001033',NULL,'2021-2025',128,3.44,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:05','2026-09-29 03:40:05'),('2001210034','2001210034','Triệu Công Hậu','2003-07-15','Nam','2001210034@st.huit.edu.vn','0978001034',NULL,'2021-2025',123,3.14,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:06','2026-09-29 03:40:06'),('2001210035','2001210035','Doãn Cẩm Tú','2003-09-09','Nữ','2001210035@st.huit.edu.vn','0978001035',NULL,'2021-2025',130,3.56,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:06','2026-09-29 03:40:06'),('2001210036','2001210036','Vi Nhật Minh','2003-02-27','Nam','2001210036@st.huit.edu.vn','0978001036',NULL,'2021-2025',126,3.28,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:07','2026-09-29 03:40:07'),('2001210037','2001210037','Khổng Ngọc Trâm','2003-11-11','Nữ','2001210037@st.huit.edu.vn','0978001037',NULL,'2021-2025',127,3.36,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:07','2026-09-29 03:40:07'),('2001210038','2001210038','Thái Tấn Phát','2003-08-18','Nam','2001210038@st.huit.edu.vn','0978001038',NULL,'2021-2025',132,3.66,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:08','2026-09-29 03:40:08'),('2001210039','2001210039','Tôn Nữ Diễm My','2003-12-03','Nữ','2001210039@st.huit.edu.vn','0978001039',NULL,'2021-2025',135,3.82,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:08','2026-09-29 03:40:08'),('2001210040','2001210040','Hứa Tấn Lộc','2003-04-17','Nam','2001210040@st.huit.edu.vn','0978001040',NULL,'2021-2025',125,3.22,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:09','2026-09-29 03:40:09'),('2001210041','2001210041','La Thị Ngọc Giàu','2003-03-25','Nữ','2001210041@st.huit.edu.vn','0978001041',NULL,'2021-2025',129,3.49,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:09','2026-09-29 03:40:09'),('2001210042','2001210042','Nông Đức Mạnh','2003-10-30','Nam','2001210042@st.huit.edu.vn','0978001042',NULL,'2021-2025',127,3.34,'Đang học','CNTT','7480201','12DHTH01','2026-09-29 03:40:10','2026-10-08 08:26:20'),('2001210043','2001210043','Lục Hải Yến','2003-06-19','Nữ','2001210043@st.huit.edu.vn','0978001043',NULL,'2021-2025',131,3.63,'Đang học','CNTT',NULL,NULL,'2026-09-29 03:40:11','2026-09-29 03:40:11'),('2001210044','2001210044','Châu Gia Hưng','2003-01-08','Nam','2001210044@st.huit.edu.vn','0978001044',NULL,'2021-2025',124,3.17,'Đang học','CNTT','7480104',NULL,'2026-09-29 03:40:11','2026-09-29 03:40:11'),('2001210045','2001210045','Mạc Quỳnh Giao','2003-09-14','Nữ','2001210045@st.huit.edu.vn','0978001045',NULL,'2021-2025',130,3.54,'Đang học','CNTT','7480202',NULL,'2026-09-29 03:40:12','2026-09-29 03:40:12'),('2001210046','2001210046','Phí Trọng Nhân','2003-05-22','Nam','2001210046@st.huit.edu.vn','0978001046',NULL,'2021-2025',136,3.88,'Đang học','CNTT',NULL,NULL,'2026-09-29 03:40:12','2026-09-29 03:40:12'),('2001210047','2001210047','Uông Bích Ngọc','2003-08-04','Nữ','2001210047@st.huit.edu.vn','0978001047',NULL,'2021-2025',126,3.30,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:13','2026-09-29 03:40:13'),('2001210048','2001210048','Thạch Gia Bảo','2003-12-16','Nam','2001210048@st.huit.edu.vn','0978001048',NULL,'2021-2025',125,3.21,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:13','2026-09-29 03:40:13'),('2001210049','2001210049','Cù Hoàng Yến','2003-04-11','Nữ','2001210049@st.huit.edu.vn','0978001049',NULL,'2021-2025',128,3.46,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:14','2026-09-29 03:40:14'),('2001210050','2001210050','Sầm Văn Quyết','2003-07-29','Nam','2001210050@st.huit.edu.vn','0978001050',NULL,'2021-2025',123,3.11,'Đang học',NULL,NULL,NULL,'2026-09-29 03:40:14','2026-09-29 03:40:14'),('2001230136','2001230136','Nguyễn Thị Thùy Dương',NULL,NULL,'thuyduong2001230136@gmaill.com','0387971013',NULL,'2021-2025',120,3.49,'Đang học','CNTT','7480201','12DHTH01','2026-08-09 16:59:00','2026-08-09 16:59:00'),('2001230137','2001230137','Nguyễn Thị Thùy Dương1',NULL,NULL,'thuyduong12001230136@gmaill.com','0387971012',NULL,'2021-2025',120,2.50,'Đang học','CNTT','7480201','12DHTH01','2026-08-09 16:59:00','2026-08-09 16:59:00'),('2001230138','2001230138','Nguyễn Thị Thùy Dương2',NULL,NULL,'thuyduong612001230136@gmaill.com','0387971011',NULL,'2021-2025',120,2.50,'Đang học','CNTT','7480201','12DHTH01','2026-08-09 16:59:00','2026-08-09 16:59:00'),('2001230139','2001230139','Trịnh Trần Phương Tuấn',NULL,NULL,'thuyduong612111230136@gmaill.com','0387971221',NULL,'2021-2025',120,3.00,'Đang học','CNTT','7480201','12DHTH01','2026-08-10 03:00:00','2026-08-10 03:00:00');
/*!40000 ALTER TABLE `sinhvien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taikhoan`
--

DROP TABLE IF EXISTS `taikhoan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `taikhoan` (
  `MaTK` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenDangNhap` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MatKhau` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaVaiTro` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TrangThai` tinyint(1) NOT NULL DEFAULT '1',
  `SoLanDangNhapSai` int NOT NULL DEFAULT '0',
  `BatBuocDoiMatKhau` tinyint(1) NOT NULL DEFAULT '1',
  `TrangThaiMatKhau` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INITIAL',
  `LanDangNhapDau` datetime DEFAULT NULL,
  `NgayDoiMatKhau` datetime DEFAULT NULL,
  `LanDangNhapCuoi` datetime DEFAULT NULL,
  `NgayKhoa` datetime DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaTK`),
  UNIQUE KEY `taikhoan_tendangnhap_unique` (`TenDangNhap`),
  KEY `taikhoan_mavaitro_foreign` (`MaVaiTro`),
  CONSTRAINT `taikhoan_mavaitro_foreign` FOREIGN KEY (`MaVaiTro`) REFERENCES `vaitro` (`MaVaiTro`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taikhoan`
--

LOCK TABLES `taikhoan` WRITE;
/*!40000 ALTER TABLE `taikhoan` DISABLE KEYS */;
INSERT INTO `taikhoan` VALUES ('2001210001','2001210001','$2y$12$nV3o8Matpt.96mGMA26FXum.Jv.qkBXdR3HzIM0xi1kwa6L2dMZbu','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:48','2026-09-29 03:39:48'),('2001210002','2001210002','$2y$12$9nltOk1qlaH3HJ8xe7HvrOk32loB0LIc9NL6xLyks7G7LpfaM/lsO','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:49','2026-09-29 03:39:49'),('2001210003','2001210003','$2y$12$lJ/LIbo6eA6E8QHopWVib.8P7Dpvb5mqWLTgeesdzF2sEaGGbjOo.','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:50','2026-09-29 03:39:50'),('2001210004','2001210004','$2y$12$tT2/hqv8dMALuRYvw9oup.b3pC1Z353f63mv5Sr9irX0QqClZu.vi','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:50','2026-09-29 03:39:50'),('2001210005','2001210005','$2y$12$yyEZb2ET/MLZebrIx//zAOnFBAsY0auqAGX7sBL.THhX6o0bA3Rp2','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:51','2026-09-29 03:39:51'),('2001210006','2001210006','$2y$12$RU7K0E5xiWBfQUhudHY7Yucg2J/J4H4VSj1mZrCWhrGcBgZg9VDZm','VT03',1,0,1,'INITIAL',NULL,NULL,'2026-08-09 23:59:00',NULL,NULL,'2026-09-29 03:39:51','2026-08-09 16:59:00'),('2001210007','2001210007','$2y$12$y4eEbt6QtfzIS109bNq4f.Nwv9a6fNDkpOSN57AgYHZvlhdTYT7Om','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:52','2026-09-29 03:39:52'),('2001210008','2001210008','$2y$12$H85Xp4RGJ8ZYcFL/jVqaFu9myiEpAwOjlpQcEHjuKppxFbOGuOqb6','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:52','2026-09-29 03:39:52'),('2001210009','2001210009','$2y$12$7uVQopjs7fW2b2bgyyAn2ey647CBrDRiNYNrCm83GyjGGZKt3M5DW','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:53','2026-09-29 03:39:53'),('2001210010','2001210010','$2y$12$nHjY9akEJsxZzt74.o84y./sOtpu2AIsyL6hJCWlPNvQKeNHgaZ6m','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:53','2026-09-29 03:39:53'),('2001210011','2001210011','$2y$12$gv.fty9oBnjnqmlvm.MsVeTZv4GX7E8Kq04L19AvUGlQWyhgvMtwG','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:54','2026-09-29 03:39:54'),('2001210012','2001210012','$2y$12$wiPd3u1nkR61/mTEZSkvkuqTziZ8drWwTsxR1ODI8o8mCw66VYAw.','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:54','2026-09-29 03:39:54'),('2001210013','2001210013','$2y$12$TJMtv1mdXoZOendYcDfALODxmvKZEI4kgpkn5jXXRoPkLc.iXYuIi','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:55','2026-09-29 03:39:55'),('2001210014','2001210014','$2y$12$2.vQgvShQpMYFQd..Y.FUO6/asmmj.UI9hREMiGxamVUQK0Qa6bdi','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:55','2026-09-29 03:39:55'),('2001210015','2001210015','$2y$12$yws1NEjFaMXd93PQoui/B..fgaQ0JgYiMZtlD4SFny3DQTQ2iJeSO','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:56','2026-09-29 03:39:56'),('2001210016','2001210016','$2y$12$PswMDle2IbhgZUCgOZkkbOkndxUihT6q.kqVwINeAt7KkdJMQym.q','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:56','2026-09-29 03:39:56'),('2001210017','2001210017','$2y$12$8CqVzXBospGRxm1dbLhOAuaYcwEjDwXdgSrN26WmXfrAvkLqGd/PO','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:57','2026-09-29 03:39:57'),('2001210018','2001210018','$2y$12$jZqHYIpL2seglwG5Sf1y7OH.FftJ.Qm5A3f6vXm9bzupMaCx7jGiy','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:57','2026-09-29 03:39:57'),('2001210019','2001210019','$2y$12$kr6YZn8yxFpPo6aGDL19YuBVEBnLOl2l1fQgfyuDjgyru7N9jxBQO','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:58','2026-09-29 03:39:58'),('2001210020','2001210020','$2y$12$1NBmFEBzcIscQxOo9sfmG.S5NloiIhQMX9NzYHaa3UY0LGIAZTF3i','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:58','2026-09-29 03:39:58'),('2001210021','2001210021','$2y$12$K8uSiDxaaPu3fySOTPSzA.s9m6odiCroFmDrcpq40YyU3L6fhTFPK','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:59','2026-09-29 03:39:59'),('2001210022','2001210022','$2y$12$zeL9mqKPLkRGyfcp85RyRuI5jxlpItMUxZDfRSYtnmcn4NOBMAP1K','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:39:59','2026-09-29 03:39:59'),('2001210023','2001210023','$2y$12$DBAoIqFXzmJs0802r9BW5.LS93sOu8ti3NeK742dr2xySL5TEi1LC','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:00','2026-09-29 03:40:00'),('2001210024','2001210024','$2y$12$7ArKdcdf.2BFwCF4eTOf7OqFuNN9BIXH.awUTv25szMeWbwkxsL9m','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:01','2026-09-29 03:40:01'),('2001210025','2001210025','$2y$12$n8pt1a8C3pPdHfYJtIoFJui9j.Crb3IvS9/cMbu6D4GmVtleyNKfy','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:01','2026-09-29 03:40:01'),('2001210026','2001210026','$2y$12$N2kAR59ZcVtFeEzp7TG9Uef3WH4ziPjEvw3UFImQ37cRIPuVyh7Ia','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:02','2026-09-29 03:40:02'),('2001210027','2001210027','$2y$12$8Uj/9q97cmX7iY3aGgTbyOW0WiWTy5bw8uQmnRdgNS1rB.v4TM2EO','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:02','2026-09-29 03:40:02'),('2001210028','2001210028','$2y$12$gV5OyaZ93GurdXJbxFS0m.7vSFBKZ8K5hzizpgB.vZVejK7XugpUG','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:03','2026-09-29 03:40:03'),('2001210029','2001210029','$2y$12$80tRzla5E9BnPUq9aLB/auayutcjC.48dFr/c.DLhWNw9rY/hKj4a','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:03','2026-09-29 03:40:03'),('2001210030','2001210030','$2y$12$971fTC.mhM8L6GHi8o.tf.EaW5j6eg/j8c6Uf5t0m9HsnPJaYJuyW','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:04','2026-09-29 03:40:04'),('2001210031','2001210031','$2y$12$q8J9owkDX2Iwn8qMuZSXQePR1CLHzDuLy1Trf8.7O0vYfn.6CBvZO','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:04','2026-09-29 03:40:04'),('2001210032','2001210032','$2y$12$Y03t7AP/0kauWCabxE4G4ecl2N5j14a6CAqBwjQ31/2U9inj2V0Fu','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:05','2026-09-29 03:40:05'),('2001210033','2001210033','$2y$12$gCrYpSsiUIfgOztR3GT2Le.QpXHf0N7hvPNABBVYwEF3xO.ttLnPa','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:05','2026-09-29 03:40:05'),('2001210034','2001210034','$2y$12$ddkQ.FJYoyu8IIssG9F./OBorT43CDxMcoDWSIKa.wFuWWP.nrhaO','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:06','2026-09-29 03:40:06'),('2001210035','2001210035','$2y$12$cT3AJsi37BI9XQLQ6gkBD..DKC2F2KO9/21gWW4YyWLPnuarfUwoa','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:06','2026-09-29 03:40:06'),('2001210036','2001210036','$2y$12$jFtwb.ujP.xCpeCeOKiQAe7AzS5E73xoOFZ1MmiPSVdm5D8iFCyua','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:07','2026-09-29 03:40:07'),('2001210037','2001210037','$2y$12$I4jQahXctBnI2jgWydhQt.W4CzAlesB.JYry8NMSR2fxKfS0o1rba','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:07','2026-09-29 03:40:07'),('2001210038','2001210038','$2y$12$3kj/WaR2tV3zBl7ui0gdueLvBsUODHcgCMtxsmjUcvhEVbj.AILS.','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:08','2026-09-29 03:40:08'),('2001210039','2001210039','$2y$12$8WtIls2J8BzxgHQKprylduQfTbjqFVpjJmlgQ8nOHSkoprBRQa4dm','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:08','2026-09-29 03:40:08'),('2001210040','2001210040','$2y$12$IIQ8bP1tJz0ymf5LR9cpAuf5GFolZtnChG0Gw6CmCGe9OjWEo47Jy','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:09','2026-09-29 03:40:09'),('2001210041','2001210041','$2y$12$OvGekx92344o48UxoD9dLOivfKIKVzboddtofDBIZuEjJfyQCfJIW','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:09','2026-09-29 03:40:09'),('2001210042','2001210042','$2y$12$rpW0fNjA0qYYqvfxDP1UWer.hxYsNA5rmamAbqBikIPdwEvwquCjW','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:10','2026-09-29 03:40:10'),('2001210043','2001210043','$2y$12$XS7LTpiDYc8pBoWF66SM/OGs164iDMTmyX4lEF7mSQqyShvxCCQNG','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:11','2026-09-29 03:40:11'),('2001210044','2001210044','$2y$12$MO3/u7mn2SI81N4PW2lQK.Q4/6cZ.EfOL0iECzdJ8/jB.kV/Qd1WK','VT03',1,0,0,'ACTIVE','2026-10-09 05:20:11','2026-10-09 05:20:11','2026-08-10 10:00:00',NULL,NULL,'2026-09-29 03:40:11','2026-08-10 03:00:00'),('2001210045','2001210045','$2y$12$0MEBmqYnsMVxx9ia3TKvleyOQ1Ozfo9Hgk82mKcAq0GQM/CjXOt4e','VT03',1,0,0,'ACTIVE','2026-08-10 10:00:00','2026-08-10 10:00:00','2026-08-10 10:00:00',NULL,NULL,'2026-09-29 03:40:12','2026-08-10 03:00:00'),('2001210046','2001210046','$2y$12$ErWWEvnjeKcSbZ.l3VVwguCCfDN.gwUG6A1Uw/T.TyuyfCcHyyFjC','VT03',1,0,0,'ACTIVE','2026-08-10 10:00:00','2026-08-10 10:00:00','2026-08-10 10:00:00',NULL,NULL,'2026-09-29 03:40:12','2026-08-10 03:00:00'),('2001210047','2001210047','$2y$12$xwKpbu6fNV9fxAxQu6iYnuW1RyY.JYlCzF5JiIPgOoAV0xUiS6nFe','VT03',1,0,0,'ACTIVE','2026-08-10 10:00:00','2026-08-10 10:00:00','2026-08-10 10:00:00',NULL,NULL,'2026-09-29 03:40:13','2026-08-10 03:00:00'),('2001210048','2001210048','$2y$12$3LMMvn4sOarS5.OMct0gJe7mnKHTZrWTfcdAR6xTToiNuF16LOOja','VT03',1,0,0,'ACTIVE','2026-08-10 10:00:00','2026-08-10 10:00:00','2026-08-10 10:00:00',NULL,NULL,'2026-09-29 03:40:13','2026-08-10 03:00:00'),('2001210049','2001210049','$2y$12$/Jm59TwR9Djs4zC.7/Jp/.J/y/BD/gtxK9neLvmQDWXXJnvs.TC9u','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:14','2026-09-29 03:40:14'),('2001210050','2001210050','$2y$12$80aXc3ffTP.sYasCds7NG.ag25sv/Zngp2WfrRqDTci/6qUbHO9qO','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-09-29 03:40:14','2026-09-29 03:40:14'),('2001230136','2001230136','$2y$12$IZ8Zc6FIz8TcyL43wIqO9uV9k/gFOynRnXygy9JkDWEjAnkeCP4eS','VT03',1,0,0,'ACTIVE','2026-08-09 23:59:00','2026-08-09 23:59:00','2026-08-09 23:59:00',NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('2001230137','2001230137','$2y$12$cr1lbUK52RqE0nEJUQuBT.lfJyNkwQudHWwwaclxYG.bYY/MNxB/G','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('2001230138','2001230138','$2y$12$oUnmM4WaRiUezam.jNv3xOxa1FdDrbXgDtd64kPO9/TUqOdaVntQS','VT03',1,0,0,'ACTIVE','2026-08-10 10:00:00','2026-08-10 10:00:00','2026-08-10 10:00:00',NULL,NULL,'2026-08-09 16:59:00','2026-08-10 03:00:00'),('2001230139','2001230139','$2y$12$3IffOwEahtwd4zpxeeiuxOkgxfSE/6tAZm2NVHYZ9rVoKXtIPXq4e','VT03',1,0,1,'INITIAL',NULL,NULL,NULL,NULL,NULL,'2026-08-10 03:00:00','2026-08-10 03:00:00'),('TK_ADMIN','admin','$2y$12$iXU8UeLRE5ufW5.ZYiRdkuHhmB8XI.DqFb.SIg6IJUSCotghRVX2e','VT01',1,0,0,'ACTIVE','2026-09-29 09:03:39','2026-09-23 12:09:02','2026-08-10 10:00:00',NULL,NULL,'2026-09-23 05:09:02','2026-08-10 03:00:00'),('TK_GIAOVU01','giaovu01','$2y$12$iXU8UeLRE5ufW5.ZYiRdkuHhmB8XI.DqFb.SIg6IJUSCotghRVX2e','VT01',1,0,0,'ACTIVE','2026-09-29 09:03:39',NULL,NULL,NULL,NULL,NULL,'2026-09-29 02:03:39'),('TK_GV00000001','GV00000001','$2y$12$ND0lOKjHtjaklEyTLgc.LeWPmuWZi4zckTo81sSSdNruQ5PCXKtbW','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000002','GV00000002','$2y$12$QQzk16znSWmS.Q1qAnu/IOaScO9FwfmLm3Ovf.D9NNq/mArD1Riqe','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000003','GV00000003','$2y$12$Bvr4tkSPhYa5NMJ6E.AYLucBtJS5RwlVm1FQuI7FHelhn0MOLXJam','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000004','GV00000004','$2y$12$4JqZ0hYI8WcMRblsTjpW6O91hpCYMEe1Up2Hlz8iO6RYCcwwRBkZm','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,'2026-08-09 23:59:00',NULL,NULL,NULL,'2026-08-09 16:59:00'),('TK_GV00000005','GV00000005','$2y$12$7J3FxfWG6ZxhcFTjJgLWzOjSML52QjiK1y5MukNG6/jY3/rYm1F4.','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,'2026-08-10 10:00:00',NULL,NULL,NULL,'2026-08-10 03:00:00'),('TK_GV00000006','GV00000006','$2y$12$NQIrS35ESF7gRdN8swsK1OgbNH4zaeNfInqGpGo.ZQIwgSbSQQEli','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000007','GV00000007','$2y$12$iUxurktNzOZUi.8pVZPpZuNg01APijoKjMhOPlPC21jORGpLLEYDS','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000008','GV00000008','$2y$12$OmQtYrsCtQ5vab5T9JwNSOt37I0Uj4gEHArqdyMIff0tOzVUyzMce','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000009','GV00000009','$2y$12$r7jnFiKUr7LoBS/1a5EwJuzD7CSCXq3UTg/8SGMCD67kJUcJAq2By','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000010','GV00000010','$2y$12$rh4tYqBVSVQk1KYm4hcf9O7AHq6NXOlv20F/9yOfsxc3yWmAB9cmi','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000011','GV00000011','$2y$12$r079FBinGZbxC9OAZW7Yg.IYO5igouQYlY./yli8tXZ4oIsZXNhtO','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000012','GV00000012','$2y$12$tVZcfCXWiZWhscSjTOEci.G5jmaQAkqjmj4j9BJUkGnrNfoMaox9G','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000013','GV00000013','$2y$12$4dGRx1vMmWHTeEni9MaGHeMK8HUkg8G.NLwh4TWI/ZDkKHelFzaxm','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000014','GV00000014','$2y$12$/mctuGgzpqmnf.KH9T2cu.eOai9E8DGPRyQXqTPlNFy1C7NDJmsBG','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000015','GV00000015','$2y$12$esKAj/3YnVmAy1Z.QML.e.UJqjIuj9fjQuoUTrcsbkYjllHEzM8L2','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000016','GV00000016','$2y$12$BYyUUqh22OwN9ake31IEiuxM43ZvVn9CEP1DhetRIyA6t6Oo.0nTK','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000017','GV00000017','$2y$12$GuIr8iwQAjFS5qWlYuNeqOKUUprpu1s5I/5IFTTxx.mO0Sgo2H5t2','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000018','GV00000018','$2y$12$4CpmQIz3O9CXB7yVoyUWMufOigNcXfueD5xUsi0u.ReJen6wb7o9m','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000019','GV00000019','$2y$12$wJhA2jquwZdD4vEHDx9y6eQagXUAJL.Na86OuTRiDdmpHNO4TQ2ye','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000020','GV00000020','$2y$12$KsnpFm3TvBFNvoQhxSX4nu4jcf8CIUesT8qHY3JIMNSnXNqpSKwxu','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000021','GV00000021','$2y$12$xKpl1KpiPb1KGlVAf3WbxOgjVD1W/ETt0KzLkK0c9thJ0ai4Pf78i','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000022','GV00000022','$2y$12$c30htgyjWxcy510z5Q5RdOS/jFkqUgvlSmKXc2wZ6DJjd97ctRjCS','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000023','GV00000023','$2y$12$b7HphE0ibI1ibslCNUbBwO/Ss.Cv1pswa7olHL4mu.h9R67LLxcti','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000024','GV00000024','$2y$12$HJLqhAG9xgASijjSP2sjieFzK6H.2DvfSAPh/iw/eDC0VPtQgV3Yy','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,'2026-08-10 10:00:00',NULL,NULL,NULL,'2026-08-10 03:00:00'),('TK_GV00000025','GV00000025','$2y$12$G02aP15sotSx51eF.XqMq.V3olCCa2.XfGdpbAZUhkv/0waWqfmCK','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000026','GV00000026','$2y$12$8EosZhY1AuZPBzXNq5ul0ONUDSe.eCdcvjmKBrLkVKckABmiQ/6na','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000027','GV00000027','$2y$12$dHoEe.oqDBVVFt2IELEA7ep2iRGluGTf/RNFXgtyJtRTNsP4xZxF.','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000028','GV00000028','$2y$12$y1pll4JXO9cmaSfqD6Ugiuizcd4fsK01p.MCt8e.DlNeDSh26H4R6','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000029','GV00000029','$2y$12$qc8WT3Uoo.Q/KVraoXUDAeKFJQq/OCniubzJEAkXmckLCvsVelWW.','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000030','GV00000030','$2y$12$isqMWYhZ9R7Z0r.npB1JdehJf4Nt7vcmpyZZxU1mpnD6AFYA7TSHu','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000031','GV00000031','$2y$12$hwgOJCucUHnG8M.9y.HEmurgLAC1hwKLuJ0RJCySZEueyhyiWtyc6','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000032','GV00000032','$2y$12$yA/Ud0wtRc/f80aojBirOOM/7nFyuCWmV7Qj2H/Gqr85z0koF5kse','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000033','GV00000033','$2y$12$CRnp1.eTcuYnNaPqvqYXKuiHVWLXvomHbsCr/PanscuEJyTGdxgIW','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000034','GV00000034','$2y$12$0O2L.Ml7mkb9FR8ZEKxKxegQRZ3nO5VkP5ZfAsTq/UsGRStPzva9O','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000035','GV00000035','$2y$12$8JwuZUeeryv9hl5qwjtlTOuqtS0ftnzlU6F4469Y8bwzxtiG9Vd..','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000036','GV00000036','$2y$12$xqwHDSkOZmWA3VQ.3stmrO9rjgda6eyV5sxG/tFvVtE99EyF4b7oe','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000037','GV00000037','$2y$12$/RjioPKcGR9c2N0KjdJTM.ISNYAqk2oXnJYPuHus7ygvvT9rtDf.G','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000038','GV00000038','$2y$12$1aF4I0GCeFkpkuYdrGzrV.K0Q/h0YD1382a6Nh2xnnKElamHTWkBi','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000039','GV00000039','$2y$12$epDb0ASpkDJ7BuTsgpUfjOaL5UNDtrM78/y/YC464Dd9K1Yj3CB1C','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000040','GV00000040','$2y$12$BZjfFwgyFr6AyEY0xSYS9uA4qdUQQxxDG5v9uhXv8p2VKNkERppai','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000041','GV00000041','$2y$12$yvKqgG0QtGDGal/sCCjDHu5U4XlO/Gc.z4lDTTCt9elwepRwoWchS','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000042','GV00000042','$2y$12$vkmKDG9tIARRmAhucgpPs.Rtt8/.yh/qrr3VFc.XAKh80EX5DRWfG','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000043','GV00000043','$2y$12$FtguTyTvbmiA146m5l0FqunT/WE9/8kAsfwSQPuziX3nHAeS8GNri','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000044','GV00000044','$2y$12$EQFTl4XNyVd8aRIXrzhBIO3v0UPIqfXKY/rp6ThAopwy4RGmN49t.','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000045','GV00000045','$2y$12$cumgEvp776PHRESyWzERdO8ftO.F4Nip.VAu/6/4FepqshsmKCkyG','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000046','GV00000046','$2y$12$caTAmkIEc5yVXILcmG2s1.9eUWFzouSexfe/QxFVSQ3v/ZN0dmTXG','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000047','GV00000047','$2y$12$6ra.9rhR3.y5L5Me4kG/B.1kIUk75noeye7Vz..Uqki7xvhMwTbku','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000048','GV00000048','$2y$12$AqolCJ09tT2t8X.7b.e6vujQsEH2GfGqV6XE5v7XnetGlXcZhvhV.','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000049','GV00000049','$2y$12$dHiMsL5h08zyVLf9RQlx1ueQ6K609stquTzdg9sEGoV5bu7G2AwIO','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_GV00000050','GV00000050','$2y$12$zXOqbNGwtFyoBBHKT/axcOisieoPfC4NTkyld9/6CddN3c1dVl3gW','VT02',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_TBM_BM_ATTT_GV00000005','TBM_BM_ATTT_GV00000005','$2y$12$JW9fGtYINkZb15eZs5dNhOMsPhZcVJi0hWDL3Bol0QDnxuhADkWyu','VT04',1,0,0,'ACTIVE',NULL,NULL,NULL,NULL,NULL,'2026-10-08 23:07:09','2026-10-08 23:07:09'),('TK_TBM_BM_CNPM_GV00000001','TBM_BM_CNPM_GV00000001','$2y$12$c3orH8pP1fYsng7.lDmhC.35kkTwekmMrpcUwpZoy7/UXMCofCN9e','VT04',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,'2026-08-09 23:59:00',NULL,NULL,NULL,'2026-08-09 16:59:00'),('TK_TBM_BM_HTTT_GV00000003','TBM_BM_HTTT_GV00000003','$2y$12$6IaV58j5u3/bA07T01aNDOCV6dUEzPTfpxYrHYGj1zfW8XiL0vblG','VT04',1,0,0,'ACTIVE',NULL,NULL,NULL,NULL,NULL,'2026-10-08 23:07:08','2026-10-08 23:07:08'),('TK_TBM_BM_KHMT_GV00000006','TBM_BM_KHMT_GV00000006','$2y$12$C56rEES2aRfYQ1arIFVATe.12/9713447CI6QcXnWsoPOMO3PGNwW','VT04',1,0,0,'ACTIVE',NULL,NULL,NULL,NULL,NULL,'2026-10-08 23:07:10','2026-10-08 23:07:10'),('TK_TK_ATTT_GV00000004','TK_ATTT_GV00000004','$2y$12$gUNbjIktSv5KTx/ZBW2EqeZ1uj/yLFutwmItdPo9G5TFJ/EeACuQS','VT05',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,NULL,NULL,NULL,NULL,'2026-10-08 21:30:08'),('TK_TK_CNTT_GV00000002','TK_CNTT_GV00000002','$2y$12$fGjGh7bUyhbcPzOShU83LO9vS8qdRyTbLeaEEtdwr9YxO.Cpropam','VT05',1,0,0,'ACTIVE','2026-10-09 04:30:08',NULL,'2026-08-09 23:59:00',NULL,NULL,NULL,'2026-08-09 16:59:00');
/*!40000 ALTER TABLE `taikhoan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tailieunop`
--

DROP TABLE IF EXISTS `tailieunop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tailieunop` (
  `MaTaiLieu` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenTaiLieu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DuongDan` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LoaiTaiLieu` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LanNop` int NOT NULL DEFAULT '1',
  `NgayNop` date DEFAULT NULL,
  `GhiChu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaDeTai` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaTaiLieu`),
  KEY `tailieunop_madetai_foreign` (`MaDeTai`),
  CONSTRAINT `tailieunop_madetai_foreign` FOREIGN KEY (`MaDeTai`) REFERENCES `detai` (`MaDeTai`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tailieunop`
--

LOCK TABLES `tailieunop` WRITE;
/*!40000 ALTER TABLE `tailieunop` DISABLE KEYS */;
/*!40000 ALTER TABLE `tailieunop` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tephosobaove`
--

DROP TABLE IF EXISTS `tephosobaove`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tephosobaove` (
  `MaTep` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenTep` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LoaiTep` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DuongDanFile` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PhienBan` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `NgayNop` date DEFAULT NULL,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `GhiChu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaHoSo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaTep`),
  KEY `tephosobaove_mahoso_foreign` (`MaHoSo`),
  CONSTRAINT `tephosobaove_mahoso_foreign` FOREIGN KEY (`MaHoSo`) REFERENCES `hosobaove` (`MaHoSo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tephosobaove`
--

LOCK TABLES `tephosobaove` WRITE;
/*!40000 ALTER TABLE `tephosobaove` DISABLE KEYS */;
/*!40000 ALTER TABLE `tephosobaove` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thanhvienhoidong`
--

DROP TABLE IF EXISTS `thanhvienhoidong`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thanhvienhoidong` (
  `MaHoiDong` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaGV` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `VaiTro` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaHoiDong`,`MaGV`),
  KEY `thanhvienhoidong_magv_foreign` (`MaGV`),
  CONSTRAINT `thanhvienhoidong_magv_foreign` FOREIGN KEY (`MaGV`) REFERENCES `giangvien` (`MaGV`) ON DELETE CASCADE,
  CONSTRAINT `thanhvienhoidong_mahoidong_foreign` FOREIGN KEY (`MaHoiDong`) REFERENCES `hoidong` (`MaHoiDong`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thanhvienhoidong`
--

LOCK TABLES `thanhvienhoidong` WRITE;
/*!40000 ALTER TABLE `thanhvienhoidong` DISABLE KEYS */;
/*!40000 ALTER TABLE `thanhvienhoidong` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thanhviennhom`
--

DROP TABLE IF EXISTS `thanhviennhom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thanhviennhom` (
  `MaNhom` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaSV` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `VaiTro` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Thành viên',
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'da_tham_gia',
  `NgayThamGia` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaNhom`,`MaSV`),
  KEY `thanhviennhom_masv_foreign` (`MaSV`),
  CONSTRAINT `thanhviennhom_manhom_foreign` FOREIGN KEY (`MaNhom`) REFERENCES `nhom` (`MaNhom`) ON DELETE CASCADE,
  CONSTRAINT `thanhviennhom_masv_foreign` FOREIGN KEY (`MaSV`) REFERENCES `sinhvien` (`MaSV`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thanhviennhom`
--

LOCK TABLES `thanhviennhom` WRITE;
/*!40000 ALTER TABLE `thanhviennhom` DISABLE KEYS */;
INSERT INTO `thanhviennhom` VALUES ('N01','2001210044','Trưởng nhóm','da_tham_gia','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00'),('N01','2001210046','Thành viên','da_tham_gia','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00'),('N02','2001210045','Trưởng nhóm','da_tham_gia','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00'),('N02','2001210047','Thành viên','da_tham_gia','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00'),('N02','2001210048','Thành viên','da_tham_gia','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00'),('N03','2001230136','Trưởng nhóm','da_tham_gia','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00'),('N03','2001230137','Thành viên','cho_xac_nhan',NULL,'2026-08-10 03:00:00','2026-08-10 03:00:00'),('N04','2001230138','Trưởng nhóm','da_tham_gia','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00'),('N05','2001230138','Trưởng nhóm','da_tham_gia','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00'),('N06','2001230138','Trưởng nhóm','da_tham_gia','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00'),('N07','2001230138','Trưởng nhóm','da_tham_gia','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00'),('N08','2001230138','Trưởng nhóm','da_tham_gia','2026-08-10','2026-08-10 03:00:00','2026-08-10 03:00:00');
/*!40000 ALTER TABLE `thanhviennhom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thongbao`
--

DROP TABLE IF EXISTS `thongbao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thongbao` (
  `MaThongBao` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TieuDe` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NoiDung` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `LoaiThongBao` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DoiTuongNhan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `NgayTao` date DEFAULT NULL,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đã phát hành',
  `MaGVu` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `MaGV` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `FileDinhKem` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaThongBao`),
  KEY `thongbao_magvu_foreign` (`MaGVu`),
  KEY `thongbao_magv_foreign` (`MaGV`),
  CONSTRAINT `thongbao_magv_foreign` FOREIGN KEY (`MaGV`) REFERENCES `giangvien` (`MaGV`) ON DELETE SET NULL,
  CONSTRAINT `thongbao_magvu_foreign` FOREIGN KEY (`MaGVu`) REFERENCES `giaovu` (`MaGVu`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thongbao`
--

LOCK TABLES `thongbao` WRITE;
/*!40000 ALTER TABLE `thongbao` DISABLE KEYS */;
INSERT INTO `thongbao` VALUES ('TB_2CAVSLF','⚠️ Yêu cầu chỉnh sửa đề tài từ Trưởng bộ môn','Đề tài \'sJGDUA\' có yêu cầu chỉnh sửa từ Trưởng bộ môn: ikfosuf9eo','Đề tài','Cá nhân','2026-08-09','Đã tạo',NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('TB_3YCWXTQ','📄 Giảng viên đã nộp đề cương chi tiết','Giảng viên TS. Trịnh Văn Thành vừa nộp file Đề cương chi tiết cho đề tài \'Hệ thống bán nước hoa\'. Vui lòng tiến hành phân công Giảng viên phản biện.','Đề tài','Cá nhân','2026-08-10','Đã tạo',NULL,NULL,NULL,'2026-08-10 03:00:00','2026-08-10 03:00:00'),('TB_3YEZWDI','✅ Đề tài đã được Trưởng khoa phê duyệt & Công bố','Đề tài \'swjdhoiwhohwq\' đã được Trưởng khoa phê duyệt và chính thức CÔNG BỐ để sinh viên đăng ký theo kế hoạch.','Đề tài','Cá nhân','2026-08-09','Đã tạo',NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('TB_8JO6XSO','✅ Đề tài đã được Trưởng khoa phê duyệt & Công bố','Đề tài \'AJXHAIHOAXXA\' đã được Trưởng khoa phê duyệt và chính thức CÔNG BỐ để sinh viên đăng ký theo kế hoạch.','Đề tài','Cá nhân','2026-08-09','Đã tạo',NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('TB_9PL7MYF','📋 Phân công phản biện đề cương đề tài','Bạn vừa được Trưởng bộ môn phân công phản biện đề cương cho đề tài: \'Hệ thống bán nước hoa\'. Vui lòng xem và gửi nhận xét đánh giá.','Đề tài','Cá nhân','2026-08-10','Đã tạo',NULL,NULL,NULL,'2026-08-10 03:00:00','2026-08-10 03:00:00'),('TB_ADTXS0E','✅ Đề tài đã được Trưởng bộ môn phê duyệt','Đề tài \'hyqwduigwiwu\' đã được Bộ môn phê duyệt và chuyển tiếp lên Trưởng khoa xem xét.','Đề tài','Cá nhân','2026-08-09','Đã tạo',NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('TB_CCOA6MZ','✅ Đề tài đã được Trưởng khoa phê duyệt & Công bố','Đề tài \'hyqwduigwiwu\' đã được Trưởng khoa phê duyệt và chính thức CÔNG BỐ để sinh viên đăng ký theo kế hoạch.','Đề tài','Cá nhân','2026-08-09','Đã tạo',NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('TB_CMVRBWF','⚠️ Yêu cầu chỉnh sửa đề tài từ Trưởng bộ môn','Đề tài \'hệ tho61ngquan3 lý nah2 hàng thông mih\' có yêu cầu chỉnh sửa từ Trưởng bộ môn: nội dung mô tả chưa đủ','Đề tài','Cá nhân','2026-08-09','Đã tạo',NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('TB_DAYFGF1','✅ Đề tài đã được Trưởng bộ môn phê duyệt','Đề tài \'AJXHAIHOAXXA\' đã được Bộ môn phê duyệt và chuyển tiếp lên Trưởng khoa xem xét.','Đề tài','Cá nhân','2026-08-09','Đã tạo',NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('TB_DK_HK26272','Công bố Danh sách Sinh viên Đủ điều kiện làm Khóa luận Tốt nghiệp - Học kỳ 2 — Năm học 2026–2027 (2026-2027)','Khoa CNTT và Ban Giáo vụ trân trọng thông báo kết quả rà soát danh sách sinh viên đủ điều kiện thực hiện Khóa luận tốt nghiệp trong Học kỳ 2 — Năm học 2026–2027 (2026-2027):\n\n• Tổng số sinh viên được rà soát: 50 sinh viên\n• Số lượng sinh viên ĐỦ ĐIỀU KIỆN: 50 sinh viên (Đạt tích lũy >= 115 tín chỉ và ĐTB >= 2.0)\n• Số lượng sinh viên CHƯA ĐỦ ĐIỀU KIỆN: 0 sinh viên\n\nSinh viên đủ điều kiện vui lòng chủ động tìm kiếm thành viên và tiến hành ghép nhóm (tối đa 3 SV/nhóm) tại phân hệ \"Nhóm Khóa Luận\", sau đó thực hiện đăng ký đề tài theo kế hoạch thời gian của Khoa.\nMọi thắc mắc hoặc khiếu nại về điều kiện khóa luận, sinh viên vui lòng liên hệ Văn phòng Giáo vụ Khoa trước thời hạn quy định.','Khóa luận','Sinh viên','2026-10-09','Đã phát hành','GVU01',NULL,NULL,'2026-10-08 20:48:25','2026-10-08 20:48:25'),('TB_FBP2IAJ','📩 Bạn nhận được lời mời tham gia nhóm khóa luận!','Trưởng nhóm Châu Gia Hưng đã mời bạn tham gia nhóm \'Nhóm 2001210044\'. Vui lòng vào mục Nhóm Khóa Luận để xác nhận.','Nhóm','Cá nhân','2026-08-10','Đã tạo',NULL,NULL,NULL,'2026-08-10 03:00:00','2026-08-10 03:00:00'),('TB_FF7XJHW','❌ Đề tài bị Trưởng bộ môn từ chối','Đề tài \'xây dựng hệ thống quản lí khóa luận tốt nghiệp\' đã bị Trưởng bộ môn từ chối. Lý do: không phù hợp','Đề tài','Cá nhân','2026-08-09','Đã tạo',NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('TB_FQYA83K','✅ Đề tài đã được Trưởng khoa phê duyệt & Công bố','Đề tài \'hệ tho61ngquan3 lý nah2 hàng thông mih\' đã được Trưởng khoa phê duyệt và chính thức CÔNG BỐ để sinh viên đăng ký theo kế hoạch.','Đề tài','Cá nhân','2026-08-09','Đã tạo',NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('TB_GILVYRM','[Khóa luận] Thông báo chính thức Kế hoạch Khóa luận 2026-2027','Khoa đã ban hành /T về kế hoạch thực hiện Khóa luận tốt nghiệp. File gốc đính kèm: /storage/official_documents/Official_Plan_1790679298_fnDakE.pdf','Kế hoạch','Toàn thể','2026-09-29','Đã phát hành','GVU01',NULL,NULL,'2026-09-29 03:54:58','2026-09-29 03:54:58'),('TB_GVQU6BK','✅ Đề cương đã phản biện ĐẠT - Chờ duyệt công bố','Đề tài \'Hệ thống bán nước hoa\' đã có kết quả phản biện từ GV ThS. Ngô Đức Hải: \'Đạt\'. Vui lòng vào xem và phê duyệt công bố.','Đề tài','Cá nhân','2026-08-10','Đã tạo',NULL,NULL,NULL,'2026-08-10 03:00:00','2026-08-10 03:00:00'),('TB_H7KRCBA','📩 Bạn nhận được lời mời tham gia nhóm khóa luận!','Trưởng nhóm Nguyễn Thị Thùy Dương đã mời bạn tham gia nhóm \'Nhóm 2001230136\'. Vui lòng vào mục Nhóm Khóa Luận để xác nhận.','Nhóm','Cá nhân','2026-08-10','Đã tạo',NULL,NULL,NULL,'2026-08-10 03:00:00','2026-08-10 03:00:00'),('TB_HOCI2LI','✅ Đề tài đã được Trưởng bộ môn phê duyệt','Đề tài \'Hệ thống bán nước hoa\' đã được Bộ môn phê duyệt và chuyển tiếp lên Trưởng khoa xem xét.','Đề tài','Cá nhân','2026-08-10','Đã tạo',NULL,NULL,NULL,'2026-08-10 03:00:00','2026-08-10 03:00:00'),('TB_HPO0VTN','[Khóa luận] Thông báo chính thức Kế hoạch Khóa luận 2026-2027','Khoa đã ban hành 27/TB-KCNTT về kế hoạch thực hiện Khóa luận tốt nghiệp. File gốc đính kèm: /storage/official_documents/Official_Plan_1791477268_WN0qev.pdf','Kế hoạch','Toàn thể','2026-10-08','Đã phát hành','GVU01',NULL,NULL,'2026-10-08 09:34:28','2026-10-08 09:34:28'),('TB_ILJ5CF6','📝 Kết quả phản biện đề cương đề tài','Đề tài \'Hệ thống bán nước hoa\' đã có kết quả phản biện từ ThS. Ngô Đức Hải: \'Đạt\'. Vui lòng kiểm tra nhận xét chi tiết.','Đề tài','Cá nhân','2026-08-10','Đã tạo',NULL,NULL,NULL,'2026-08-10 03:00:00','2026-08-10 03:00:00'),('TB_JNP28JU','📩 Bạn nhận được lời mời tham gia nhóm khóa luận!','Trưởng nhóm Châu Gia Hưng đã mời bạn tham gia nhóm \'Nhóm 2001210044\'. Vui lòng vào mục Nhóm Khóa Luận để xác nhận.','Nhóm','Cá nhân','2026-08-10','Đã tạo',NULL,NULL,NULL,'2026-08-10 03:00:00','2026-08-10 03:00:00'),('TB_JOZAWMT','📩 Bạn nhận được lời mời tham gia nhóm khóa luận!','Trưởng nhóm Châu Gia Hưng đã mời bạn tham gia nhóm \'Nhóm 2001210044\'. Vui lòng vào mục Nhóm Khóa Luận để xác nhận.','Nhóm','Cá nhân','2026-08-10','Đã tạo',NULL,NULL,NULL,'2026-08-10 03:00:00','2026-08-10 03:00:00'),('TB_KBPGKCZ','[Khóa luận] Thông báo chính thức Kế hoạch Khóa luận 2026-2027','Khoa đã ban hành 27/TB-KCNTT về kế hoạch thực hiện Khóa luận tốt nghiệp.\n\nFile gốc đính kèm: http://127.0.0.1:8000/storage/official_documents/Official_Plan_1791478724_jRYUcu.pdf','Kế hoạch','Toàn thể','2026-10-08','Đã phát hành','GVU01',NULL,'storage/official_documents/Official_Plan_1791478724_jRYUcu.pdf','2026-10-08 09:58:44','2026-10-08 09:58:44'),('TB_KDXOLTC','[Khóa luận] Thông báo chính thức Kế hoạch Khóa luận 2026-2027','Khoa đã ban hành 27/TB-KCNTT về kế hoạch thực hiện Khóa luận tốt nghiệp. File gốc đính kèm: /storage/official_documents/Official_Plan_1791477558_rfOiSR.pdf','Kế hoạch','Toàn thể','2026-10-08','Đã phát hành','GVU01',NULL,NULL,'2026-10-08 09:39:18','2026-10-08 09:39:18'),('TB_LW5O4N4','✅ Đề tài đã được Trưởng khoa phê duyệt & Công bố','Đề tài \'Hệ thống bán hàng\' đã được Trưởng khoa phê duyệt và chính thức CÔNG BỐ để sinh viên đăng ký theo kế hoạch.','Đề tài','Cá nhân','2026-08-09','Đã tạo',NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('TB_LY3M1HU','🎉 Đề tài đã được Trưởng bộ môn duyệt đề cương và CÔNG BỐ','Đề tài \'Hệ thống bán nước hoa\' đã hoàn tất phản biện đề cương đạt yêu cầu và được Trưởng bộ môn phê duyệt chính thức CÔNG BỐ cho sinh viên đăng ký!','Đề tài','Cá nhân','2026-08-10','Đã tạo',NULL,NULL,NULL,'2026-08-10 03:00:00','2026-08-10 03:00:00'),('TB_MQ41VNN','📝 Đề tài đã được nộp lại phê duyệt','Giảng viên TS. Trịnh Văn Thành đã chỉnh sửa và nộp lại đề tài \'AJXHAIHOAXXA\' để Trưởng bộ môn phê duyệt.','Đề tài','Cá nhân','2026-08-09','Đã tạo',NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('TB_OGRZ4XL','📝 Đề tài đã được nộp lại phê duyệt','Giảng viên TS. Trịnh Văn Thành đã chỉnh sửa và nộp lại đề tài \'sJGDUA\' để Trưởng bộ môn phê duyệt.','Đề tài','Cá nhân','2026-08-09','Đã tạo',NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('TB_PECPKVE','✅ Đề tài đã được Trưởng bộ môn phê duyệt','Đề tài \'swjdhoiwhohwq\' đã được Bộ môn phê duyệt và chuyển tiếp lên Trưởng khoa xem xét.','Đề tài','Cá nhân','2026-08-09','Đã tạo',NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('TB_PGV0EYL','📩 Bạn nhận được lời mời tham gia nhóm khóa luận!','Trưởng nhóm Mạc Quỳnh Giao đã mời bạn tham gia nhóm \'Nhóm 2001210045\'. Vui lòng vào mục Nhóm Khóa Luận để xác nhận.','Nhóm','Cá nhân','2026-08-10','Đã tạo',NULL,NULL,NULL,'2026-08-10 03:00:00','2026-08-10 03:00:00'),('TB_QWKQSWA','✅ Giảng viên đã gán đề tài cho nhóm!','Giảng viên TS. Trịnh Văn Thành đã trực tiếp gán nhóm của bạn vào đề tài: \'Hệ thống bán hàng\'','Đề tài','Cá nhân','2026-08-10','Đã tạo',NULL,NULL,NULL,'2026-08-10 03:00:00','2026-08-10 03:00:00'),('TB_SG9LE2Y','✅ Đề tài đã được Trưởng khoa phê duyệt & Công bố','Đề tài \'sJGDUA\' đã được Trưởng khoa phê duyệt và chính thức CÔNG BỐ để sinh viên đăng ký theo kế hoạch.','Đề tài','Cá nhân','2026-08-09','Đã tạo',NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('TB_UIG5AWQ','[Khóa luận] Thông báo chính thức Kế hoạch Khóa luận 2026-2027','Khoa đã ban hành 27/TB-KCNTT về kế hoạch thực hiện Khóa luận tốt nghiệp. File gốc đính kèm: /storage/official_documents/Official_Plan_1791477344_6kkVVy.pdf','Kế hoạch','Toàn thể','2026-10-08','Đã phát hành','GVU01',NULL,NULL,'2026-10-08 09:35:44','2026-10-08 09:35:44'),('TB_USEAFFX','✅ Đề tài đã được Trưởng bộ môn phê duyệt','Đề tài \'Hệ thống bán hàng\' đã được Bộ môn phê duyệt và chuyển tiếp lên Trưởng khoa xem xét.','Đề tài','Cá nhân','2026-08-09','Đã tạo',NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('TB_W0I3MRL','[KHOA CNTT] Thông Báo Công Bố Kế Hoạch Khóa Luận Tốt Nghiệp - Học kỳ 2','Khoa Công nghệ Thông tin chính thức công bố Kế hoạch Khóa luận HK1 2026-2027 (Văn bản thông báo chính thức số 27/TB-KCNTT).\n\nĐề nghị toàn thể Giảng viên và Sinh viên theo dõi chi tiết các mốc thời gian quy trình và thực hiện theo đúng kế hoạch.\n\n📄 File công văn đính kèm: http://127.0.0.1:8000/storage/official_documents/Official_Plan_1791478724_jRYUcu.pdf','Kế hoạch','Toàn thể','2026-10-08','Đã phát hành','GVU01',NULL,'storage/official_documents/Official_Plan_1791478724_jRYUcu.pdf','2026-10-08 09:59:22','2026-10-08 09:59:22'),('TB_W12MQVE','✅ Đề tài đã được Trưởng bộ môn phê duyệt','Đề tài \'sJGDUA\' đã được Bộ môn phê duyệt và chuyển tiếp lên Trưởng khoa xem xét.','Đề tài','Cá nhân','2026-08-09','Đã tạo',NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00'),('TB_YA3Q28O','✅ Đề tài đã được Trưởng khoa phê duyệt & Công bố','Đề tài \'Hệ thống bán nước hoa\' đã được Trưởng khoa phê duyệt và chính thức CÔNG BỐ để sinh viên đăng ký theo kế hoạch.','Đề tài','Cá nhân','2026-08-10','Đã tạo',NULL,NULL,NULL,'2026-08-10 03:00:00','2026-08-10 03:00:00'),('TB_YVPPR1W','✅ Đề tài đã được Trưởng bộ môn phê duyệt','Đề tài \'hệ tho61ngquan3 lý nah2 hàng thông mih\' đã được Bộ môn phê duyệt và chuyển tiếp lên Trưởng khoa xem xét.','Đề tài','Cá nhân','2026-08-09','Đã tạo',NULL,NULL,NULL,'2026-08-09 16:59:00','2026-08-09 16:59:00');
/*!40000 ALTER TABLE `thongbao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tomtatbaocao`
--

DROP TABLE IF EXISTS `tomtatbaocao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tomtatbaocao` (
  `MaTomTat` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `CongViecDaHoanThanh` longtext COLLATE utf8mb4_unicode_ci,
  `KhoKhan` longtext COLLATE utf8mb4_unicode_ci,
  `KeHoachTuanToi` longtext COLLATE utf8mb4_unicode_ci,
  `NoiDungAI` longtext COLLATE utf8mb4_unicode_ci,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Đã tạo',
  `NgayTomTat` date DEFAULT NULL,
  `MaBaoCao` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaTomTat`),
  UNIQUE KEY `tomtatbaocao_mabaocao_unique` (`MaBaoCao`),
  CONSTRAINT `tomtatbaocao_mabaocao_foreign` FOREIGN KEY (`MaBaoCao`) REFERENCES `baocaotiendo` (`MaBaoCao`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tomtatbaocao`
--

LOCK TABLES `tomtatbaocao` WRITE;
/*!40000 ALTER TABLE `tomtatbaocao` DISABLE KEYS */;
/*!40000 ALTER TABLE `tomtatbaocao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vaitro`
--

DROP TABLE IF EXISTS `vaitro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vaitro` (
  `MaVaiTro` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `TenVaiTro` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaVaiTro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vaitro`
--

LOCK TABLES `vaitro` WRITE;
/*!40000 ALTER TABLE `vaitro` DISABLE KEYS */;
INSERT INTO `vaitro` VALUES ('VT01','Giáo vụ','2026-09-23 05:09:02','2026-09-29 02:03:39'),('VT02','Giảng viên','2026-09-23 05:09:02','2026-09-29 02:03:39'),('VT03','Sinh viên','2026-09-23 05:09:02','2026-09-29 02:03:39'),('VT04','Trưởng bộ môn',NULL,'2026-09-29 02:03:39'),('VT05','Trưởng khoa',NULL,'2026-09-29 02:03:39');
/*!40000 ALTER TABLE `vaitro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yeucaudoimatkhau`
--

DROP TABLE IF EXISTS `yeucaudoimatkhau`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `yeucaudoimatkhau` (
  `MaYeuCau` bigint unsigned NOT NULL AUTO_INCREMENT,
  `TenDangNhap` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `HoTen` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LyDo` text COLLATE utf8mb4_unicode_ci,
  `TrangThai` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chờ duyệt',
  `NgayGui` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `NgayDuyet` datetime DEFAULT NULL,
  `NguoiDuyet` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaYeuCau`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yeucaudoimatkhau`
--

LOCK TABLES `yeucaudoimatkhau` WRITE;
/*!40000 ALTER TABLE `yeucaudoimatkhau` DISABLE KEYS */;
/*!40000 ALTER TABLE `yeucaudoimatkhau` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'quanly_doan'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-09 21:36:11
