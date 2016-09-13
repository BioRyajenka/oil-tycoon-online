-- MySQL Script generated by MySQL Workbench
-- 09/14/16 02:44:13
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema oil_tycoon
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema oil_tycoon
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `oil_tycoon` DEFAULT CHARACTER SET utf8 ;
-- -----------------------------------------------------
-- Schema oil_tycoon_world_parameters
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema oil_tycoon_world_parameters
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `oil_tycoon_world_parameters` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
USE `oil_tycoon` ;

-- -----------------------------------------------------
-- Table `oil_tycoon`.`user_credentials`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `oil_tycoon`.`user_credentials` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `login` CHAR(45) CHARACTER SET 'utf8' NOT NULL,
  `nickname` VARCHAR(45) CHARACTER SET 'utf8' NOT NULL,
  `password` CHAR(45) CHARACTER SET 'utf8' NOT NULL,
  `regdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `email` CHAR(45) CHARACTER SET 'utf8' NOT NULL,
  `gender` ENUM('male', 'female') CHARACTER SET 'utf8' NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `login_UNIQUE` (`login` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `oil_tycoon`.`facilities`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `oil_tycoon`.`facilities` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` ENUM('none', 'locked', 'silo', 'transport depot', 'science lab', 'scout depot', 'rig') NOT NULL,
  `level` INT NULL,
  `data` TINYTEXT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `oil_tycoon`.`field`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `oil_tycoon`.`field` (
  `cell_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `x` INT NOT NULL,
  `y` INT NOT NULL,
  `land_cost` FLOAT NOT NULL DEFAULT 20,
  `oil_sell_cost` FLOAT NOT NULL,
  `oil_amount` FLOAT NOT NULL,
  `image_name` CHAR(45) NOT NULL,
  `owner_id` INT UNSIGNED NULL,
  `facility1_id` INT UNSIGNED NOT NULL,
  `facility2_id` INT UNSIGNED NOT NULL,
  `facility3_id` INT UNSIGNED NOT NULL,
  `facility4_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`cell_id`),
  INDEX `owner_id_idx` (`owner_id` ASC),
  INDEX `facilities_ids_idx` (`facility1_id` ASC),
  INDEX `fk_4_idx` (`facility2_id` ASC),
  INDEX `fk_5_idx` (`facility3_id` ASC),
  INDEX `fk_6_idx` (`facility4_id` ASC),
  CONSTRAINT `fk_1`
    FOREIGN KEY (`owner_id`)
    REFERENCES `oil_tycoon`.`user_credentials` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_2`
    FOREIGN KEY (`facility1_id`)
    REFERENCES `oil_tycoon`.`facilities` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_4`
    FOREIGN KEY (`facility2_id`)
    REFERENCES `oil_tycoon`.`facilities` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_5`
    FOREIGN KEY (`facility3_id`)
    REFERENCES `oil_tycoon`.`facilities` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_6`
    FOREIGN KEY (`facility4_id`)
    REFERENCES `oil_tycoon`.`facilities` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `oil_tycoon`.`user_gamedata`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `oil_tycoon`.`user_gamedata` (
  `user_id` INT UNSIGNED NOT NULL,
  `money` FLOAT UNSIGNED NOT NULL,
  `maxlevel_silo` INT UNSIGNED NOT NULL,
  `maxlevel_transport_depot` INT UNSIGNED NOT NULL,
  `maxlevel_scouting_depot` INT UNSIGNED NOT NULL,
  `maxlevel_rig` INT UNSIGNED NOT NULL,
  `transport_speed` FLOAT NOT NULL,
  `researched_unique_technologies` SET('spying1', 'spying2', 'spying3', 'trading', 'banking') NOT NULL,
  `color` VARCHAR(10) NOT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_3`
    FOREIGN KEY (`user_id`)
    REFERENCES `oil_tycoon`.`user_credentials` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `oil_tycoon`.`user_knowledge_oil`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `oil_tycoon`.`user_knowledge_oil` (
  `user_id` INT UNSIGNED NOT NULL,
  `cell_id` INT UNSIGNED NOT NULL,
  `amount` FLOAT NOT NULL,
  `discovered` TIMESTAMP NOT NULL,
  INDEX `fk_7_idx` (`user_id` ASC),
  INDEX `fk_8_idx` (`cell_id` ASC),
  CONSTRAINT `fk_7`
    FOREIGN KEY (`user_id`)
    REFERENCES `oil_tycoon`.`user_credentials` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_8`
    FOREIGN KEY (`cell_id`)
    REFERENCES `oil_tycoon`.`field` (`cell_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `oil_tycoon`.`user_knowledge_facilities`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `oil_tycoon`.`user_knowledge_facilities` (
  `user_id` INT UNSIGNED NOT NULL,
  `cell_id` INT UNSIGNED NOT NULL,
  `facility1_type` ENUM('none', 'locked', 'silo', 'transport depot', 'science lab', 'scouting depot', 'rig') NOT NULL,
  `facility1_lvl` INT NOT NULL,
  `facility2_type` ENUM('none', 'locked', 'silo', 'transport depot', 'science lab', 'scouting depot', 'rig') NOT NULL,
  `facility2_lvl` INT NOT NULL,
  `facility3_type` ENUM('none', 'locked', 'silo', 'transport depot', 'science lab', 'scouting depot', 'rig') NOT NULL,
  `facility3_lvl` INT NOT NULL,
  `facility4_type` ENUM('none', 'locked', 'silo', 'transport depot', 'science lab', 'scouting depot', 'rig') NOT NULL,
  `facility4_lvl` INT NOT NULL,
  INDEX `fk_9_idx` (`user_id` ASC),
  INDEX `fk_10_idx` (`cell_id` ASC),
  CONSTRAINT `fk_9`
    FOREIGN KEY (`user_id`)
    REFERENCES `oil_tycoon`.`user_credentials` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_10`
    FOREIGN KEY (`cell_id`)
    REFERENCES `oil_tycoon`.`field` (`cell_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `oil_tycoon`.`facility_timings`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `oil_tycoon`.`facility_timings` (
  `level` INT UNSIGNED NOT NULL,
  `research_time` FLOAT NOT NULL,
  `construction_time` FLOAT NOT NULL,
  PRIMARY KEY (`level`))
ENGINE = InnoDB;

USE `oil_tycoon_world_parameters` ;

-- -----------------------------------------------------
-- Table `oil_tycoon_world_parameters`.`facility_parameters`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `oil_tycoon_world_parameters`.`facility_parameters` (
  `primary_key` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` ENUM('silo', 'transport depot', 'science lab', 'scout depot', 'rig') NOT NULL,
  `level` INT UNSIGNED NOT NULL,
  `cost` FLOAT NOT NULL,
  `data` TINYTEXT NOT NULL,
  PRIMARY KEY (`primary_key`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `oil_tycoon_world_parameters`.`science_facility_levels_research_cost`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `oil_tycoon_world_parameters`.`science_facility_levels_research_cost` (
  `level` INT UNSIGNED NOT NULL,
  `cost` FLOAT NOT NULL,
  PRIMARY KEY (`level`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `oil_tycoon_world_parameters`.`science_unique_technologies_parameters`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `oil_tycoon_world_parameters`.`science_unique_technologies_parameters` (
  `technology_name` CHAR(45) NOT NULL,
  `available_at_level` INT UNSIGNED NOT NULL,
  `research_cost` FLOAT NOT NULL,
  `research_time` FLOAT NOT NULL,
  PRIMARY KEY (`technology_name`))
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;