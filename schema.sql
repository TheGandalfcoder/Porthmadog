-- ============================================================
-- Porthmadog RFC - 50th Anniversary Website
-- Database Schema
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `porthmadog_rfc`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `porthmadog_rfc`;

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
-- Seed: sample admin user  (password: Admin@1234  — CHANGE THIS)
-- Run setup/create_admin.php to generate a fresh hash safely.
-- ------------------------------------------------------------
-- INSERT INTO `admin_users` (`username`, `password_hash`)
-- VALUES ('admin', '$2y$12$PLACEHOLDER_CHANGE_VIA_SETUP_SCRIPT');
