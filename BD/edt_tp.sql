/*
Navicat MySQL Data Transfer

Source Server         : local_Wamp_root
Source Server Version : 50728
Source Host           : localhost:3306
Source Database       : edt_tp

Target Server Type    : MYSQL
Target Server Version : 50728
File Encoding         : 65001

Date: 2025-10-15 23:38:09
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for creneaux
-- ----------------------------
DROP TABLE IF EXISTS `creneaux`;
CREATE TABLE `creneaux` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jour` enum('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi') DEFAULT NULL,
  `heure_debut` time DEFAULT NULL,
  `heure_fin` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_creneau` (`jour`,`heure_debut`,`heure_fin`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for edt_existants
-- ----------------------------
DROP TABLE IF EXISTS `edt_existants`;
CREATE TABLE `edt_existants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('CM','TD') DEFAULT NULL,
  `groupe_td_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `creneau_id` int(11) DEFAULT NULL,
  `matiere` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `groupe_td_id` (`groupe_td_id`),
  KEY `section_id` (`section_id`),
  KEY `creneau_id` (`creneau_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for edt_tp
-- ----------------------------
DROP TABLE IF EXISTS `edt_tp`;
CREATE TABLE `edt_tp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupe_tp_id` int(11) DEFAULT NULL,
  `creneau_id` int(11) DEFAULT NULL,
  `matiere` varchar(100) DEFAULT NULL,
  `salle` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `groupe_tp_id` (`groupe_tp_id`),
  KEY `creneau_id` (`creneau_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for groupes_td
-- ----------------------------
DROP TABLE IF EXISTS `groupes_td`;
CREATE TABLE `groupes_td` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(10) NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`),
  KEY `section_id` (`section_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for groupes_tp
-- ----------------------------
DROP TABLE IF EXISTS `groupes_tp`;
CREATE TABLE `groupes_tp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(20) NOT NULL,
  `groupe_td_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`),
  KEY `groupe_td_id` (`groupe_td_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sections
-- ----------------------------
DROP TABLE IF EXISTS `sections`;
CREATE TABLE `sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
