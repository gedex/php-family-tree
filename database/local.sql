
--
-- Table structure for table `families`
--

DROP TABLE IF EXISTS `families`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `families` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `birth_date` date DEFAULT NULL,
  `birth_place` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `gender` char(1) COLLATE utf8_unicode_ci DEFAULT 'M',
  `married` int(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `families`
--
INSERT INTO `families` VALUES (1,NULL,1,36,'Parent',NULL,'','M',1);
INSERT INTO `families` VALUES (2,1,2,25,'1st child',NULL,'','M',0);
INSERT INTO `families` VALUES (3,1,26,35,'2nd child',NULL,'','F',0);
INSERT INTO `families` VALUES (4,2,3,4,'Child #4 of first child',NULL,'','M',1);
INSERT INTO `families` VALUES (5,2,5,12,'Child #4 of first child',NULL,'','M',1);
INSERT INTO `families` VALUES (6,2,13,16,'Child #1 of first child',NULL,'','M',1);
INSERT INTO `families` VALUES (7,2,17,18,'Child #2 of first child',NULL,'','M',1);
INSERT INTO `families` VALUES (8,2,19,22,'Child #3 of first child',NULL,'','M',1);
INSERT INTO `families` VALUES (9,2,23,24,'Child #4 of first child',NULL,'','M',1);
INSERT INTO `families` VALUES (10,3,27,28,'Child #1 of 2nd child',NULL,'','M',1);
INSERT INTO `families` VALUES (11,3,29,30,'Child #2 of 2nd child',NULL,'','M',1);
INSERT INTO `families` VALUES (12,3,31,32,'Child #3 of 2nd child',NULL,'','M',1);
INSERT INTO `families` VALUES (13,3,33,34,'Child #4 of 2nd child',NULL,'','M',1);
INSERT INTO `families` VALUES (14,8,20,21,'Child #3 of first child',NULL,'','M',1);
INSERT INTO `families` VALUES (15,6,14,15,'Akeda Bagus',NULL,'','M',1);
INSERT INTO `families` VALUES (16,5,6,7,'Akeda Bagus',NULL,'','M',1);
INSERT INTO `families` VALUES (17,5,8,9,'Dwi',NULL,'','F',1);
INSERT INTO `families` VALUES (18,5,10,11,'yay',NULL,'','F',1);
