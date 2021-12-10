/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 100419
 Source Host           : localhost:3306
 Source Schema         : api_mabar

 Target Server Type    : MySQL
 Target Server Version : 100419
 File Encoding         : 65001

 Date: 04/12/2021 01:38:18
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for m_gender
-- ----------------------------
DROP TABLE IF EXISTS `m_gender`;
CREATE TABLE `m_gender`  (
  `gender_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
  `gender` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`gender_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of m_gender
-- ----------------------------
INSERT INTO `m_gender` VALUES (1, 'Laki-Laki', '2021-11-12 22:18:15', NULL);
INSERT INTO `m_gender` VALUES (2, 'Perempuan', '2021-11-12 22:18:18', NULL);

-- ----------------------------
-- Table structure for m_request_join_team
-- ----------------------------
DROP TABLE IF EXISTS `m_request_join_team`;
CREATE TABLE `m_request_join_team`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `team_id` int NOT NULL,
  `answer` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '1= diterima, 0=ditolak',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of m_request_join_team
-- ----------------------------

-- ----------------------------
-- Table structure for m_team
-- ----------------------------
DROP TABLE IF EXISTS `m_team`;
CREATE TABLE `m_team`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `admin_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `personnel` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of m_team
-- ----------------------------
INSERT INTO `m_team` VALUES (2, 'Janu Team', 'Mobile legend', '2', '[]', NULL, '2021-11-21 13:57:04', '2021-11-21 13:57:04');

-- ----------------------------
-- Table structure for m_tournament
-- ----------------------------
DROP TABLE IF EXISTS `m_tournament`;
CREATE TABLE `m_tournament`  (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `id_created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'id_user host pembuat tournament',
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `number_of_participants` int NOT NULL,
  `register_date_start` date NOT NULL,
  `register_date_end` date NOT NULL,
  `prize` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `poster` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`, `id_created_by`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of m_tournament
-- ----------------------------
INSERT INTO `m_tournament` VALUES (1, 'Janu Tournament satu', '3', '2021-10-20 00:00:00', '2021-10-23 00:00:00', '\"tournamen buatan janu pertama\"', 8, '2021-10-10', '2021-10-15', '\"10.000.000\"', NULL, '2021-11-21 07:55:11', '2021-11-21 08:19:58');
INSERT INTO `m_tournament` VALUES (2, 'Janu Tournament', '3', '2021-10-20 00:00:00', '2021-10-23 00:00:00', '\"tournamen buatan janu\"', 8, '2021-10-10', '2021-10-15', '\"10.000.000\"', NULL, '2021-11-21 08:10:09', '2021-11-21 08:10:09');

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES (1, '2021_10_01_015553_create_users_table', 1);
INSERT INTO `migrations` VALUES (2, '2021_11_01_063213_create_personnel_table', 2);
INSERT INTO `migrations` VALUES (3, '2021_11_01_064634_create_m_gender_table', 2);
INSERT INTO `migrations` VALUES (4, '2021_11_02_050040_create_m_team_table', 2);

-- ----------------------------
-- Table structure for personnel
-- ----------------------------
DROP TABLE IF EXISTS `personnel`;
CREATE TABLE `personnel`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `firstname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `lastname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `gender_id` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `birthdate` date NULL DEFAULT NULL,
  `address` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `sub_district_id` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `district_id` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `province_id` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `zipcode` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `team_id` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `role` tinyint(1) UNSIGNED ZEROFILL NULL DEFAULT 1 COMMENT '1 = personnel, 2 = host',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of personnel
-- ----------------------------
INSERT INTO `personnel` VALUES (1, 2, 'firstname', 'lastname', '1', '2000-01-01', 'address', '1', NULL, '3', '50299', '2', '0811112312', 2, '2021-11-12 20:39:19', '2021-11-21 13:57:04');
INSERT INTO `personnel` VALUES (2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2021-11-14 22:35:20');
INSERT INTO `personnel` VALUES (5, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, '2021-11-21 14:10:20', '2021-11-21 14:11:07');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `users_username_unique`(`username`) USING BTREE,
  UNIQUE INDEX `users_email_unique`(`email`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'test2', 'email@gmail.com', '$2y$10$nX43vCPiWfkCRREYRnpPaO0KJnfzwYceIuqBVmy/03EzHR1Pa/oRm', '2021-10-03 20:46:49', '2021-10-03 20:46:49');
INSERT INTO `users` VALUES (2, 'personel1', 'personel1@mail.ocm', '$2y$10$wM/bS3ueL0sZG0seK.cJc.Rx2MFSnmt3yLOBonEibM4xB6YwLaWdq', '2021-11-12 20:39:19', '2021-11-12 20:39:19');
INSERT INTO `users` VALUES (3, 'host', 'host@mail.ocm', '$2y$10$L5dQza5ue/hxJ2cKnOHy/.R7NY6spdFdGk6adI6c.Xb9j.jd13t7S', '2021-11-21 14:10:20', '2021-11-21 14:10:20');

SET FOREIGN_KEY_CHECKS = 1;
