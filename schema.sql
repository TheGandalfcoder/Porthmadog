-- ============================================================
-- Porthmadog RFC - 50th Anniversary Website
-- Database Schema
-- ============================================================

-- SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
-- SET time_zone = "+00:00";
-- SET NAMES utf8mb4;

-- CREATE DATABASE IF NOT EXISTS `porthmadog_rfc`
--     CHARACTER SET utf8mb4
--     COLLATE utf8mb4_unicode_ci;

-- USE `porthmadog_rfc`;

-- ------------------------------------------------------------
-- Table: admin_users
-- Stores admin credentials. Passwords stored as bcrypt hashes.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `username`      VARCHAR(80)     NOT NULL,
    `password_hash` VARCHAR(255)    NOT NULL,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: players
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `players` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(120)    NOT NULL,
    `position`      VARCHAR(60)     NOT NULL,
    `squad_number`  TINYINT UNSIGNED         DEFAULT NULL,
    `age`           TINYINT UNSIGNED         DEFAULT NULL,
    `bio`           TEXT,
    `photo_path`    VARCHAR(255)             DEFAULT NULL,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: fixtures
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fixtures` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `match_date`    DATETIME        NOT NULL,
    `opponent`      VARCHAR(120)    NOT NULL,
    `location`      ENUM('home','away') NOT NULL DEFAULT 'home',
    `competition`   VARCHAR(120)    NOT NULL DEFAULT 'League',
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: results
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `results` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `match_date`    DATETIME        NOT NULL,
    `opponent`      VARCHAR(120)    NOT NULL,
    `our_score`     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `opponent_score` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `location`      ENUM('home','away') NOT NULL DEFAULT 'home',
    `competition`   VARCHAR(120)    NOT NULL DEFAULT 'League',
    `match_report`  TEXT,
    `motm`          VARCHAR(120)             DEFAULT NULL,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: club_info
-- Single-row table for editable club content.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `club_info` (
    `id`                    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `history_content`       TEXT,
    `anniversary_message`   TEXT,
    `founded_year`          YEAR            NOT NULL DEFAULT '1975',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Seed: club_info default row
-- ------------------------------------------------------------
INSERT INTO `club_info` (`history_content`, `anniversary_message`, `founded_year`)
VALUES (
    '<p>Porthmadog RFC was founded in 1975 and has been a cornerstone of rugby in North Wales ever since. From humble beginnings on a muddy pitch, the club has grown into a thriving community institution.</p>\n<p>Over five decades the club has produced international players, won regional championships, and - most importantly - provided a home for hundreds of players who simply love the game.</p>',
    '<p>As we celebrate our <strong>50th Anniversary</strong>, we reflect on half a century of rugby, community, and camaraderie. Thank you to every player, volunteer, supporter, and sponsor who has made this club what it is today. Here is to the next 50 years!</p>',
    1975
);

-- ------------------------------------------------------------
-- Table: player_stats
-- Per-player, per-season stats (tries, assists, MOTM awards).
-- season format: '2025/26'
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `player_stats` (
    `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `player_id`  INT UNSIGNED     NOT NULL,
    `season`     VARCHAR(10)      NOT NULL,
    `tries`      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `assists`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `motm_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_player_season` (`player_id`, `season`),
    CONSTRAINT `fk_ps_player` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: staff
-- Coaches and committee members.
-- category: 'coach' or 'committee'
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `staff` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(120)    NOT NULL,
    `role`       VARCHAR(120)    NOT NULL DEFAULT '',
    `category`   ENUM('coach','committee') NOT NULL DEFAULT 'coach',
    `bio`        TEXT,
    `photo_path` VARCHAR(255)    DEFAULT NULL,
    `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Migrations: run these in phpMyAdmin if upgrading existing DB
-- ------------------------------------------------------------
-- ALTER TABLE `club_info`
--     ADD COLUMN `contact_email`   VARCHAR(120) DEFAULT NULL,
--     ADD COLUMN `contact_phone`   VARCHAR(60)  DEFAULT NULL,
--     ADD COLUMN `contact_address` TEXT         DEFAULT NULL;
--
-- Also create the uploads/staff/ directory on your server.
-- CREATE TABLE player_stats — see full definition above (run the full CREATE TABLE).
-- Social media columns:
-- ALTER TABLE `club_info`
--     ADD COLUMN `social_facebook`  VARCHAR(255) DEFAULT NULL,
--     ADD COLUMN `social_twitter`   VARCHAR(255) DEFAULT NULL,
--     ADD COLUMN `social_instagram` VARCHAR(255) DEFAULT NULL;

-- ------------------------------------------------------------
-- Seed: sample admin user  (password: Admin@1234  — CHANGE THIS)
-- Run setup/create_admin.php to generate a fresh hash safely.
-- ------------------------------------------------------------
-- INSERT INTO `admin_users` (`username`, `password_hash`)
-- VALUES ('admin', '$2y$12$PLACEHOLDER_CHANGE_VIA_SETUP_SCRIPT');
