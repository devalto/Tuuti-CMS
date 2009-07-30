SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `tuuti` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
USE `tuuti`;

-- -----------------------------------------------------
-- Table `USER`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `USER` ;

CREATE  TABLE IF NOT EXISTS `USER` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NULL ,
  `complete_name` VARCHAR(90) NULL ,
  `password` VARCHAR(32) NULL ,
  `activated` TINYINT(1) UNSIGNED NULL ,
  `role` ENUM('ADMIN', 'USER') NULL DEFAULT 'USER' ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `LANGUAGE`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `LANGUAGE` ;

CREATE  TABLE IF NOT EXISTS `LANGUAGE` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `value` VARCHAR(15) NULL ,
  `abbrev` VARCHAR(2) NULL ,
  `priority` TINYINT(2) NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX unique_abrev (`abbrev` ASC) ,
  UNIQUE INDEX unique_priority (`priority` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SECTION`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SECTION` ;

CREATE  TABLE IF NOT EXISTS `SECTION` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `pretty_url_title` VARCHAR(50) NULL ,
  `type` ENUM('ONE ARTICLE', 'MULTIPLE ARTICLE') NULL DEFAULT 'ONE ARTICLE' ,
  `article_order_field` ENUM('CREATION DATE', 'MODIFICATION DATE', 'CUSTOM DATE', 'ALPHABETICAL', 'PRIORITY') NULL DEFAULT 'CREATION DATE' ,
  `nb_column` TINYINT(1) UNSIGNED NULL DEFAULT 1 ,
  `priority` TINYINT(2) UNSIGNED NULL DEFAULT 1 ,
  `display_in_nav` TINYINT(1) UNSIGNED NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX unique_priority (`priority` ASC) ,
  UNIQUE INDEX unique_pretty_url (`pretty_url_title` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SECTION_NAME`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `SECTION_NAME` ;

CREATE  TABLE IF NOT EXISTS `SECTION_NAME` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `value` VARCHAR(50) NULL ,
  `fk_lang` INT(11) UNSIGNED NOT NULL ,
  `fk_section` INT(11) UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`, `fk_lang`, `fk_section`) ,
  INDEX fk_SECTIONS_NAMES_LANGUAGES (`fk_lang` ASC) ,
  INDEX fk_SECTIONS_NAMES_SECTIONS (`fk_section` ASC) ,
  UNIQUE INDEX unique_section_lang (`fk_section` ASC, `fk_lang` ASC) ,
  CONSTRAINT `fk_SECTIONS_NAMES_LANGUAGES`
    FOREIGN KEY (`fk_lang` )
    REFERENCES `LANGUAGE` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_SECTIONS_NAMES_SECTIONS`
    FOREIGN KEY (`fk_section` )
    REFERENCES `SECTION` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `ARTICLE`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ARTICLE` ;

CREATE  TABLE IF NOT EXISTS `ARTICLE` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `title` VARCHAR(50) NULL ,
  `content` MEDIUMTEXT NULL ,
  `priority` TINYINT(3) UNSIGNED NULL ,
  `date_custom` DATETIME NULL ,
  `fk_section` INT(11) UNSIGNED NOT NULL ,
  `fk_lang` INT(11) UNSIGNED NOT NULL ,
  `created_on` DATETIME NULL ,
  `created_by` VARCHAR(45) NULL ,
  `modified_on` DATETIME NULL ,
  `modified_by` VARCHAR(45) NULL ,
  PRIMARY KEY (`id`, `fk_section`, `fk_lang`) ,
  INDEX fk_ARTICLE_SECTION (`fk_section` ASC) ,
  INDEX fk_ARTICLE_LANGUAGE (`fk_lang` ASC) ,
  UNIQUE INDEX unique_section_priority (`fk_section` ASC, `fk_lang` ASC, `priority` ASC) ,
  CONSTRAINT `fk_ARTICLE_SECTION`
    FOREIGN KEY (`fk_section` )
    REFERENCES `SECTION` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_ARTICLE_LANGUAGE`
    FOREIGN KEY (`fk_lang` )
    REFERENCES `LANGUAGE` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
