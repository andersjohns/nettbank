-- MySQL dump 10.13  Distrib 8.0.34, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: nettbank
-- ------------------------------------------------------

DROP TABLE IF EXISTS `aktivitetslogger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aktivitetslogger` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bruker_id` int NOT NULL,
  `handling` text NOT NULL,
  `tidspunkt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `bruker_id` (`bruker_id`),
  CONSTRAINT `aktivitetslogger_ibfk_1` FOREIGN KEY (`bruker_id`) REFERENCES `brukere` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `brukere`
--

DROP TABLE IF EXISTS `brukere`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brukere` (
  `id` int NOT NULL AUTO_INCREMENT,
  `navn` varchar(255) NOT NULL,
  `adresse` text,
  `telefon` varchar(20) DEFAULT NULL,
  `epost` varchar(255) NOT NULL,
  `passord_hash` varchar(255) NOT NULL,
  `er_virksomhet` tinyint(1) DEFAULT '0',
  `er_aktiv` tinyint(1) DEFAULT '1',
  `rolle` varchar(50) DEFAULT 'user',
  `opprettet_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `epost` (`epost`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kontoer`
--

DROP TABLE IF EXISTS `kontoer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kontoer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bruker_id` int NOT NULL,
  `kontonummer` varchar(30) NOT NULL,
  `kontotype` varchar(20) NOT NULL,
  `saldo` decimal(15,2) DEFAULT '0.00',
  `rente` decimal(5,2) DEFAULT '0.00',
  `opprettet_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kontonummer` (`kontonummer`),
  KEY `bruker_id` (`bruker_id`),
  CONSTRAINT `kontoer_ibfk_1` FOREIGN KEY (`bruker_id`) REFERENCES `brukere` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `renter`
--

DROP TABLE IF EXISTS `renter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `renter` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kontotype` varchar(20) NOT NULL,
  `rente` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `transaksjoner`
--

DROP TABLE IF EXISTS `transaksjoner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksjoner` (
  `id` int NOT NULL AUTO_INCREMENT,
  `konto_id` int NOT NULL,
  `type` varchar(50) NOT NULL,
  `beløp` decimal(15,2) NOT NULL,
  `referanse` varchar(255) DEFAULT NULL,
  `opprettet_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `konto_id` (`konto_id`),
  CONSTRAINT `transaksjoner_ibfk_1` FOREIGN KEY (`konto_id`) REFERENCES `kontoer` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;


