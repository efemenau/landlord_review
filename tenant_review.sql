-- Tenant Review Database Schema
-- Host: localhost:3306
-- Generation Time: Apr 01, 2025
-- Server version: 8.0.41
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tenant_review`
--

-- ---------------------------
-- Table structure for `buildings`
-- ---------------------------
CREATE TABLE `buildings` (
  `building_id` CHAR(39) NOT NULL,
  `landlord_id` CHAR(39) NOT NULL,
  `building_name` VARCHAR(100) NOT NULL,
  `country` VARCHAR(100) NOT NULL,
  `state_county` VARCHAR(100) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `town` VARCHAR(100) DEFAULT NULL,
  `zip_code` VARCHAR(20) DEFAULT NULL,
  `address` TEXT NOT NULL,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `last_updated` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `building_type` ENUM('apartment', 'house', 'commercial', 'other') DEFAULT 'apartment'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Trigger to auto-generate building_id
DELIMITER $$
CREATE TRIGGER `before_insert_buildings`
BEFORE INSERT ON `buildings`
FOR EACH ROW
BEGIN
    IF NEW.building_id IS NULL OR NEW.building_id = '' THEN
        SET NEW.building_id = CONCAT('BL-', UUID());
    END IF;
END$$
DELIMITER ;

-- ---------------------------
-- Table structure for `landlords`
-- ---------------------------
CREATE TABLE `landlords` (
  `landlord_id` CHAR(39) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `company` VARCHAR(100) DEFAULT NULL,
  `verification_status` TINYINT DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Trigger to auto-generate landlord_id
DELIMITER $$
CREATE TRIGGER `before_insert_landlords`
BEFORE INSERT ON `landlords`
FOR EACH ROW
BEGIN
    IF NEW.landlord_id IS NULL OR NEW.landlord_id = '' THEN
        SET NEW.landlord_id = CONCAT('LA-', UUID());
    END IF;
END$$
DELIMITER ;

-- ---------------------------
-- Table structure for `tenants`
-- ---------------------------
CREATE TABLE `tenants` (
  `tenant_id` CHAR(39) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `registered_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ---------------------------
-- Table structure for `reviews`
-- ---------------------------
CREATE TABLE `reviews` (
  `review_id` CHAR(39) NOT NULL,
  `tenant_id` CHAR(39) NOT NULL,
  `landlord_id` CHAR(39) NOT NULL,
  `building_id` CHAR(39) DEFAULT NULL,
  `cleanliness_rating` TINYINT NOT NULL,
  `security_rating` TINYINT NOT NULL,
  `payment_options_rating` TINYINT NOT NULL,
  `maintenance_rating` TINYINT NOT NULL,
  `payment_grace_rating` TINYINT NOT NULL,
  `communication_rating` TINYINT NOT NULL,
  `responsiveness_rating` TINYINT NOT NULL,
  `overall_rating` DECIMAL(3,2) GENERATED ALWAYS AS (
    (
      `cleanliness_rating` +
      `security_rating` +
      `payment_options_rating` +
      `maintenance_rating` +
      `payment_grace_rating` +
      `communication_rating` +
      `responsiveness_rating`
    ) / 7
  ) VIRTUAL,
  `review_title` VARCHAR(120) NOT NULL,
  `review_text` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `approved_status` TINYINT DEFAULT 0,
  `reported_count` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Trigger to auto-generate review_id
DELIMITER $$
CREATE TRIGGER `before_insert_reviews`
BEFORE INSERT ON `reviews`
FOR EACH ROW
BEGIN
    IF NEW.review_id IS NULL OR NEW.review_id = '' THEN
        SET NEW.review_id = CONCAT('RV-', UUID());
    END IF;
END$$
DELIMITER ;

-- ---------------------------
-- Table structure for `review_helpfulness`
-- ---------------------------
CREATE TABLE `review_helpfulness` (
  `helpful_id` CHAR(39) NOT NULL,
  `review_id` CHAR(39) NOT NULL,
  `user_id` CHAR(39) NOT NULL,
  `is_helpful` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Trigger to auto-generate helpful_id
DELIMITER $$
CREATE TRIGGER `before_insert_helpfulness`
BEFORE INSERT ON `review_helpfulness`
FOR EACH ROW
BEGIN
    IF NEW.helpful_id IS NULL OR NEW.helpful_id = '' THEN
        SET NEW.helpful_id = CONCAT('HL-', UUID());
    END IF;
END$$
DELIMITER ;

COMMIT;
