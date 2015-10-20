SET NAMES utf8;

DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `parent_id` int(12) NOT NULL,
  `lft` int(12) NOT NULL,
  `rgt` int(12) NOT NULL,
  `deep` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;