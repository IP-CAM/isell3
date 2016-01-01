ALTER TABLE `acc_tree` DROP COLUMN `branch_data`;

ALTER TABLE `companies_tree` DROP COLUMN `top_id`,DROP COLUMN `branch_data`;

UPDATE companies_tree set path=CONCAT(REPLACE(path,'>','/'),'/');

UPDATE user_list SET user_assigned_path=CONCAT(REPLACE(user_assigned_path,'>','/'),'/'),user_assigned_stat=CONCAT(REPLACE(user_assigned_stat,'>','/'),'/');

ALTER TABLE `companies_list` ADD COLUMN `is_active` TINYINT(1) NULL AFTER `is_supplier`;




update acc_check_list set date=null where date='0000-00-00 00:00:00';
update acc_check_list set assumption_date=null where assumption_date='0000-00-00 00:00:00';
update acc_check_list set value_date=null where value_date='0000-00-00 00:00:00';
update document_list set reg_stamp=null where reg_stamp='0000-00-00 00:00:00';





-- MySQL Workbench Synchronization
-- Generated: 2015-10-03 12:29
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: baycik

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `acc_check_list` 
CHANGE COLUMN `main_acc_code` `main_acc_code` VARCHAR(6) NOT NULL ,
ADD COLUMN `active_company_id` INT(10) UNSIGNED NOT NULL FIRST,
ADD INDEX `fk_acc_check_list_acc_tree1_idx` (`main_acc_code` ASC),
ADD INDEX `fk_acc_check_list_companies_list1_idx` (`active_company_id` ASC),
DROP INDEX `mac_idx` ;

ALTER TABLE `acc_trans` 
CHANGE COLUMN `active_company_id` `active_company_id` INT(10) UNSIGNED NOT NULL FIRST,
CHANGE COLUMN `passive_company_id` `passive_company_id` INT(10) UNSIGNED NOT NULL AFTER `active_company_id`,
CHANGE COLUMN `trans_status` `trans_status` TINYINT(1) NOT NULL ,
CHANGE COLUMN `cstamp` `cstamp` TIMESTAMP NULL DEFAULT NULL ,
CHANGE COLUMN `created_by` `created_by` INT(11) NULL DEFAULT NULL ,
CHANGE COLUMN `modified_by` `modified_by` INT(11) NULL DEFAULT NULL ,
ADD INDEX `fk_acc_trans_acc_trans_status1_idx` (`trans_status` ASC),
ADD INDEX `fk_acc_trans_user_list1_idx` (`modified_by` ASC),
ADD INDEX `fk_acc_trans_user_list2_idx` (`created_by` ASC);

ALTER TABLE `acc_trans_names` 
CHANGE COLUMN `acc_debit_code` `acc_debit_code` VARCHAR(6) NOT NULL ,
CHANGE COLUMN `acc_credit_code` `acc_credit_code` VARCHAR(6) NOT NULL ,
ADD INDEX `fk_acc_trans_names_acc_tree2_idx` (`acc_credit_code` ASC);

ALTER TABLE `acc_trans_status` 
CHANGE COLUMN `trans_status` `trans_status` TINYINT(1) NOT NULL ;

ALTER TABLE `companies_list` 
CHANGE COLUMN `curr_code` `curr_code` VARCHAR(3) NULL DEFAULT NULL ,
ADD COLUMN `is_active` TINYINT(3) NULL DEFAULT NULL AFTER `is_supplier`,
ADD INDEX `fk_companies_list_companies_tree1_idx` (`branch_id` ASC),
ADD INDEX `fk_companies_list_curr_list1_idx` (`curr_code` ASC),
DROP INDEX `comp_tree_idx` ;

ALTER TABLE `companies_tree` 
CHANGE COLUMN `branch_id` `branch_id` INT(10) UNSIGNED NULL DEFAULT NULL AUTO_INCREMENT ,
ADD COLUMN `branch_data` TEXT NOT NULL AFTER `is_leaf`,
ADD COLUMN `top_id` INT(10) UNSIGNED NOT NULL AFTER `level`;

ALTER TABLE `curr_list` 
CHANGE COLUMN `curr_code` `curr_code` VARCHAR(3) NULL DEFAULT NULL ,
CHANGE COLUMN `curr_name` `curr_name` VARCHAR(45) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
ADD UNIQUE INDEX `curr_code_UNIQUE` (`curr_code` ASC);

ALTER TABLE `document_list` 
CHANGE COLUMN `active_company_id` `active_company_id` INT(10) UNSIGNED NOT NULL FIRST,
CHANGE COLUMN `passive_company_id` `passive_company_id` INT(10) UNSIGNED NOT NULL AFTER `active_company_id`,
CHANGE COLUMN `cstamp` `cstamp` TIMESTAMP NULL DEFAULT NULL ,
CHANGE COLUMN `reg_stamp` `reg_stamp` TIMESTAMP NULL DEFAULT NULL ,
CHANGE COLUMN `created_by` `created_by` INT(11) NULL DEFAULT NULL ,
CHANGE COLUMN `modified_by` `modified_by` INT(11) NULL DEFAULT NULL ,
ADD INDEX `fk_document_list_document_types1_idx` (`doc_type` ASC),
ADD INDEX `fk_document_list_user_list1_idx` (`created_by` ASC),
ADD INDEX `fk_document_list_user_list2_idx` (`modified_by` ASC);

ALTER TABLE `document_types` 
CHANGE COLUMN `doc_type` `doc_type` TINYINT(1) UNSIGNED NOT NULL ;

ALTER TABLE `document_view_list` 
CHANGE COLUMN `tstamp` `tstamp` TIMESTAMP NULL DEFAULT NULL ;

ALTER TABLE `document_view_types` 
CHANGE COLUMN `doc_type` `doc_type` TINYINT(1) UNSIGNED NOT NULL ;

ALTER TABLE `pref_list` 
ADD COLUMN `active_company_id` INT(10) UNSIGNED NOT NULL FIRST,
ADD INDEX `fk_pref_list_companies_list1_idx` (`active_company_id` ASC);

ALTER TABLE `stock_entries` 
CHANGE COLUMN `parent_id` `parent_id` INT(10) UNSIGNED NULL DEFAULT NULL ,
CHANGE COLUMN `fetch_stamp` `fetch_stamp` TIMESTAMP NULL DEFAULT NULL ,
ADD INDEX `fk_stock_entries_stock_tree1_idx` (`parent_id` ASC);

ALTER TABLE `stock_tree` 
CHANGE COLUMN `branch_id` `branch_id` INT(10) UNSIGNED NULL DEFAULT NULL AUTO_INCREMENT ;

ALTER TABLE `user_list` 
CHANGE COLUMN `user_sign` `user_sign` VARCHAR(45) NULL DEFAULT NULL ,
CHANGE COLUMN `user_position` `user_position` VARCHAR(255) NULL DEFAULT NULL ;

DROP TABLE IF EXISTS `acc_list` ;

ALTER TABLE `acc_check_list` 
ADD CONSTRAINT `fk_acc_check_list_acc_tree1`
  FOREIGN KEY (`main_acc_code`)
  REFERENCES `acc_tree` (`acc_code`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_acc_check_list_companies_list1`
  FOREIGN KEY (`active_company_id`)
  REFERENCES `companies_list` (`company_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `acc_trans` 
ADD CONSTRAINT `fk_acc_trans_acc_trans_status1`
  FOREIGN KEY (`trans_status`)
  REFERENCES `acc_trans_status` (`trans_status`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_acc_trans_user_list1`
  FOREIGN KEY (`modified_by`)
  REFERENCES `user_list` (`user_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_acc_trans_user_list2`
  FOREIGN KEY (`created_by`)
  REFERENCES `user_list` (`user_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `acc_trans_names` 
ADD CONSTRAINT `fk_acc_trans_names_acc_tree1`
  FOREIGN KEY (`acc_debit_code`)
  REFERENCES `acc_tree` (`acc_code`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_acc_trans_names_acc_tree2`
  FOREIGN KEY (`acc_credit_code`)
  REFERENCES `acc_tree` (`acc_code`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `companies_list` 
ADD CONSTRAINT `fk_companies_list_companies_tree1`
  FOREIGN KEY (`branch_id`)
  REFERENCES `companies_tree` (`branch_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_companies_list_curr_list1`
  FOREIGN KEY (`curr_code`)
  REFERENCES `curr_list` (`curr_code`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `document_list` 
ADD CONSTRAINT `fk_document_list_document_types1`
  FOREIGN KEY (`doc_type`)
  REFERENCES `document_types` (`doc_type`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_document_list_user_list1`
  FOREIGN KEY (`created_by`)
  REFERENCES `user_list` (`user_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_document_list_user_list2`
  FOREIGN KEY (`modified_by`)
  REFERENCES `user_list` (`user_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `document_view_types` 
ADD CONSTRAINT `FK_document_view_types_1`
  FOREIGN KEY (`doc_type`)
  REFERENCES `document_types` (`doc_type`);

ALTER TABLE `event_list` 
ADD CONSTRAINT `userid`
  FOREIGN KEY (`event_user_id`)
  REFERENCES `user_list` (`user_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
ADD CONSTRAINT `fk_event_list_companies_list1`
  FOREIGN KEY (`active_company_id`)
  REFERENCES `companies_list` (`company_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `pref_list` 
ADD CONSTRAINT `fk_pref_list_companies_list1`
  FOREIGN KEY (`active_company_id`)
  REFERENCES `companies_list` (`company_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `stock_entries` 
ADD CONSTRAINT `fk_stock_entries_stock_tree1`
  FOREIGN KEY (`parent_id`)
  REFERENCES `stock_tree` (`branch_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
