-- MySQL Script generated by MySQL Workbench
-- Thu Jun 24 07:18:54 2021
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Table `lks_item`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lks_item` ;

CREATE TABLE IF NOT EXISTS `lks_item` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `reference` VARCHAR(64) NOT NULL,
  `item_type` TINYINT(2) NOT NULL,
  `provider` VARCHAR(64) NOT NULL,
  `identifier` VARCHAR(64) NOT NULL,
  `vendor_name` VARCHAR(64) NOT NULL,
  `vendor_url` VARCHAR(256) NOT NULL,
  `description` TEXT NOT NULL,
  `post_datetime` DATETIME NOT NULL,
  `price_in_euro` DECIMAL(10,2) NOT NULL,
  `screenshots` TEXT NOT NULL,
  `status` TINYINT(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `provider_UNIQUE` (`provider` ASC, `identifier` ASC),
  UNIQUE INDEX `reference_UNIQUE` (`reference` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lks_user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lks_user` ;

CREATE TABLE IF NOT EXISTS `lks_user` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(128) NOT NULL,
  `password` VARCHAR(64) NOT NULL,
  `company` VARCHAR(64) NOT NULL,
  `first_name` VARCHAR(64) NOT NULL,
  `last_name` VARCHAR(64) NOT NULL,
  `address` VARCHAR(128) NOT NULL,
  `zip_postal_code` VARCHAR(24) NOT NULL,
  `city` VARCHAR(128) NOT NULL,
  `state_province_region` VARCHAR(128) NOT NULL,
  `country` VARCHAR(64) NOT NULL,
  `phone` VARCHAR(24) NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `token_first_connection_time` DATETIME NULL,
  `token_last_connection_time` DATETIME NULL,
  `reset_password_token` VARCHAR(64) NOT NULL,
  `reset_password_token_time` DATETIME NULL,
  `remember_me_token` VARCHAR(64) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lks_user_has_item`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lks_user_has_item` ;

CREATE TABLE IF NOT EXISTS `lks_user_has_item` (
  `user_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `purchase_date` DATETIME NOT NULL,
  `payment_method` VARCHAR(64) NOT NULL,
  `purchased_price_in_euro` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`user_id`, `item_id`),
  INDEX `fk_lks_user_has_lks_item_lks_item1_idx` (`item_id` ASC),
  INDEX `fk_lks_user_has_lks_item_lks_user1_idx` (`user_id` ASC),
  CONSTRAINT `fk_lks_user_has_lks_item_lks_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `lks_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_lks_user_has_lks_item_lks_item1`
    FOREIGN KEY (`item_id`)
    REFERENCES `lks_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lks_user_has_item`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lks_user_has_item` ;

CREATE TABLE IF NOT EXISTS `lks_user_has_item` (
  `user_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `purchase_date` DATETIME NOT NULL,
  `payment_method` VARCHAR(64) NOT NULL,
  `purchased_price_in_euro` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`user_id`, `item_id`),
  INDEX `fk_lks_user_has_lks_item_lks_item1_idx` (`item_id` ASC),
  INDEX `fk_lks_user_has_lks_item_lks_user1_idx` (`user_id` ASC),
  CONSTRAINT `fk_lks_user_has_lks_item_lks_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `lks_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_lks_user_has_lks_item_lks_item1`
    FOREIGN KEY (`item_id`)
    REFERENCES `lks_item` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lks_invoice`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lks_invoice` ;

CREATE TABLE IF NOT EXISTS `lks_invoice` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `invoice_number` VARCHAR(32) NOT NULL,
  `payment_method` VARCHAR(64) NOT NULL,
  `purchase_date` DATETIME NULL,
  `company` VARCHAR(64) NOT NULL,
  `first_name` VARCHAR(64) NOT NULL,
  `last_name` VARCHAR(64) NOT NULL,
  `email` VARCHAR(128) NOT NULL,
  `phone` VARCHAR(24) NOT NULL,
  `address` VARCHAR(128) NOT NULL,
  `zip_postal_code` VARCHAR(24) NOT NULL,
  `city` VARCHAR(128) NOT NULL,
  `state_province_region` VARCHAR(128) NOT NULL,
  `country` VARCHAR(64) NOT NULL,
  `discount_code` VARCHAR(32) NULL,
  `discount_label` VARCHAR(128) NOT NULL,
  `discount_amount_in_euro` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `invoice_number_UNIQUE` (`invoice_number` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `lks_invoice_line`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lks_invoice_line` ;

CREATE TABLE IF NOT EXISTS `lks_invoice_line` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `invoice_id` INT NOT NULL,
  `product_reference` VARCHAR(64) NOT NULL,
  `product_name` VARCHAR(256) NOT NULL,
  `quantity` INT NOT NULL,
  `unit_price_in_euro` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_lks_invoice_line_lks_invoice1_idx` (`invoice_id` ASC),
  CONSTRAINT `fk_lks_invoice_line_lks_invoice1`
    FOREIGN KEY (`invoice_id`)
    REFERENCES `lks_invoice` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
