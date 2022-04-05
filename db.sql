-- MySQL dump 10.13  Distrib 8.0.23, for Linux (x86_64)
--
-- Host: localhost    Database: test_db
-- ------------------------------------------------------
-- Server version	8.0.23-0ubuntu0.20.04.1

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
-- Table structure for table `Akce`
--

DROP TABLE IF EXISTS `Akce`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Akce` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nazev` varchar(50) DEFAULT NULL,
  `datum` date NOT NULL,
  `typ` int NOT NULL,
  `zadal` char(10) NOT NULL,
  `editoval` char(10) NOT NULL,
  `dat_editace` datetime NOT NULL,
  `poznamka` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `AkceTyp`
--

DROP TABLE IF EXISTS `AkceTyp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `AkceTyp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nazev` varchar(30) NOT NULL,
  `poznamka` varchar(200) DEFAULT NULL,
  `zadal` char(10) NOT NULL,
  `editoval` char(10) NOT NULL,
  `dat_editace` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Dary`
--

DROP TABLE IF EXISTS `Dary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Dary` (
  `id` int NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL,
  `castka` int NOT NULL,
  `ucel` varchar(50) DEFAULT NULL,
  `darce` int NOT NULL,
  `organizace` int DEFAULT NULL,
  `hmotny` tinyint(1) NOT NULL,
  `zadal` char(10) NOT NULL,
  `editoval` char(10) NOT NULL,
  `dat_editace` datetime NOT NULL,
  `poznamka` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Interakce`
--

DROP TABLE IF EXISTS `Interakce`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Interakce` (
  `clovek` int NOT NULL,
  `organizace` int DEFAULT NULL,
  `akce` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Lide`
--

DROP TABLE IF EXISTS `Lide`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Lide` (
  `id` int NOT NULL AUTO_INCREMENT,
  `jmeno` varchar(30) NOT NULL,
  `prijmeni` varchar(30) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `prac_email` varchar(50) DEFAULT NULL,
  `telefon` char(9) DEFAULT NULL,
  `ulice_cislo` varchar(50) DEFAULT NULL,
  `mesto` varchar(30) DEFAULT NULL,
  `organizace` int DEFAULT NULL,
  `prac_pozice` varchar(50) DEFAULT NULL,
  `kontaktovat` tinyint(1) NOT NULL,
  `zadal` char(10) NOT NULL,
  `editoval` char(10) NOT NULL,
  `dat_editace` datetime NOT NULL,
  `poznamka` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1020 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Organizace`
--

DROP TABLE IF EXISTS `Organizace`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Organizace` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nazev` varchar(50) DEFAULT NULL,
  `ulice_cislo` varchar(50) DEFAULT NULL,
  `mesto` varchar(30) DEFAULT NULL,
  `zadal` char(10) NOT NULL,
  `editoval` char(10) NOT NULL,
  `dat_editace` datetime NOT NULL,
  `poznamka` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10000007 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `SysUsers`
--

DROP TABLE IF EXISTS `SysUsers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `SysUsers` (
  `username` char(10) NOT NULL,
  `pass_hash` varchar(255) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-03-15  3:22:31
