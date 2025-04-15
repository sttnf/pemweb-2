/*M!999999\- enable the sandbox mode */
-- MariaDB dump 10.19-11.7.2-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: dbpuskesmas
-- ------------------------------------------------------
-- Server version	11.7.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

CREATE DATABASE dbpuskesmas;
USE dbpuskesmas;

--
-- Table structure for table `kelurahan`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kelurahan` (
                             `id` int(11) NOT NULL AUTO_INCREMENT,
                             `nama` varchar(45) NOT NULL,
                             `kec_id` int(11) DEFAULT NULL,
                             PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paramedik`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `paramedik` (
                             `id` int(11) NOT NULL AUTO_INCREMENT,
                             `nama` varchar(45) NOT NULL,
                             `gender` char(1) DEFAULT NULL,
                             `tmp_lahir` varchar(30) DEFAULT NULL,
                             `tgl_lahir` date DEFAULT NULL,
                             `kategori` enum('dokter','perawat','bidan') NOT NULL,
                             `telpon` varchar(20) DEFAULT NULL,
                             `alamat` varchar(100) DEFAULT NULL,
                             `unit_kerja_id` int(11) DEFAULT NULL,
                             PRIMARY KEY (`id`),
                             KEY `unit_kerja_id` (`unit_kerja_id`),
                             CONSTRAINT `paramedik_ibfk_1` FOREIGN KEY (`unit_kerja_id`) REFERENCES `unit_kerja` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pasien`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pasien` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `nama` varchar(45) NOT NULL,
                          `tmp_lahir` varchar(30) DEFAULT NULL,
                          `tgl_lahir` date DEFAULT NULL,
                          `gender` char(1) DEFAULT NULL,
                          `email` varchar(50) DEFAULT NULL,
                          `alamat` varchar(100) DEFAULT NULL,
                          `kelurahan_id` int(11) DEFAULT NULL,
                          PRIMARY KEY (`id`),
                          KEY `kelurahan_id` (`kelurahan_id`),
                          CONSTRAINT `pasien_ibfk_1` FOREIGN KEY (`kelurahan_id`) REFERENCES `kelurahan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `periksa`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `periksa` (
                           `id` int(11) NOT NULL AUTO_INCREMENT,
                           `tanggal` date NOT NULL,
                           `berat` double DEFAULT NULL,
                           `tinggi` double DEFAULT NULL,
                           `tensi` varchar(20) DEFAULT NULL,
                           `keterangan` varchar(100) DEFAULT NULL,
                           `pasien_id` int(11) DEFAULT NULL,
                           `dokter_id` int(11) DEFAULT NULL,
                           PRIMARY KEY (`id`),
                           KEY `pasien_id` (`pasien_id`),
                           KEY `dokter_id` (`dokter_id`),
                           CONSTRAINT `periksa_ibfk_1` FOREIGN KEY (`pasien_id`) REFERENCES `pasien` (`id`) ON DELETE CASCADE,
                           CONSTRAINT `periksa_ibfk_2` FOREIGN KEY (`dokter_id`) REFERENCES `paramedik` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `unit_kerja`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `unit_kerja` (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `nama` varchar(45) NOT NULL,
                              PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-04-15 14:14:09
