-- MySQL dump 10.14  Distrib 5.5.40-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: owncloud
-- ------------------------------------------------------
-- Server version	5.5.40-MariaDB-1~wheezy-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `oc_polls_events`
--

DROP TABLE IF EXISTS `oc_polls_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oc_polls_events` (
  `id` varchar(8) COLLATE utf8_bin NOT NULL,
  `title` varchar(128) COLLATE utf8_bin NOT NULL,
  `description` varchar(1024) COLLATE utf8_bin NOT NULL,
  `owner` varchar(64) COLLATE utf8_bin NOT NULL,
  `created` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `access` varchar(1024) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oc_polls_events`
--

LOCK TABLES `oc_polls_events` WRITE;
/*!40000 ALTER TABLE `oc_polls_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `oc_polls_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oc_polls_dts`
--

DROP TABLE IF EXISTS `oc_polls_dts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oc_polls_dts` (
  `id` varchar(8) COLLATE utf8_bin NOT NULL DEFAULT '',
  `dt` varchar(32) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oc_polls_dts`
--

LOCK TABLES `oc_polls_dts` WRITE;
/*!40000 ALTER TABLE `oc_polls_dts` DISABLE KEYS */;
INSERT INTO `oc_polls_dts` VALUES ('0','22.11.2014_00:00'),('0','22.11.2014_00:25'),('0','28.11.2014_00:00'),('0','28.11.2014_00:25');
/*!40000 ALTER TABLE `oc_polls_dts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oc_polls_comments`
--

DROP TABLE IF EXISTS `oc_polls_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oc_polls_comments` (
  `id` varchar(8) COLLATE utf8_bin NOT NULL DEFAULT '',
  `user` varchar(64) COLLATE utf8_bin NOT NULL,
  `dt` varchar(32) COLLATE utf8_bin NOT NULL,
  `comment` varchar(1024) COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oc_polls_comments`
--

LOCK TABLES `oc_polls_comments` WRITE;
/*!40000 ALTER TABLE `oc_polls_comments` DISABLE KEYS */;
INSERT INTO `oc_polls_comments` VALUES ('0','rr','1416905272','asdf');
/*!40000 ALTER TABLE `oc_polls_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oc_polls_particip`
--

DROP TABLE IF EXISTS `oc_polls_particip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oc_polls_particip` (
  `id` varchar(8) COLLATE utf8_bin NOT NULL DEFAULT '',
  `dt` varchar(32) COLLATE utf8_bin NOT NULL,
  `user` varchar(64) COLLATE utf8_bin NOT NULL,
  `ok` varchar(8) COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oc_polls_particip`
--

LOCK TABLES `oc_polls_particip` WRITE;
/*!40000 ALTER TABLE `oc_polls_particip` DISABLE KEYS */;
INSERT INTO `oc_polls_particip` VALUES ('0','28.11.2014_00:25','rr','yes'),('0','28.11.2014_00:00','rr','yes');
/*!40000 ALTER TABLE `oc_polls_particip` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-11-25  9:48:28
