ALTER TABLE `nilua`.`acc_tree` 
DROP COLUMN `top_id`,
ADD COLUMN `acc_code` INT(10) UNSIGNED  NOT NULL AFTER `path`,
ADD COLUMN `acc_type` VARCHAR(2) NULL AFTER `acc_code`,
ADD COLUMN `acc_role` VARCHAR(45) NULL AFTER `acc_type`,
ADD COLUMN `curr_id` INT UNSIGNED NULL AFTER `acc_role`,
ADD COLUMN `is_favorite` TINYINT(1) NULL AFTER `curr_id`,
ADD COLUMN `use_clientbank` TINYINT(1) NULL AFTER `is_favorite`,
ADD INDEX `curr_code_idx` (`curr_id` ASC);
ALTER TABLE `nilua`.`acc_tree` 
ADD CONSTRAINT `curr_code`
  FOREIGN KEY (`curr_id`)
  REFERENCES `nilua`.`curr_list` (`curr_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

update acc_tree set acc_code=null;

ALTER TABLE `nilua`.`acc_tree` 
CHANGE COLUMN `acc_code` `acc_code` INT(10) UNSIGNED NULL ,
ADD UNIQUE INDEX `acc_code_UNIQUE` (`acc_code` ASC);

ALTER TABLE `nilua`.`acc_trans` 
ADD CONSTRAINT `cred_code`
  FOREIGN KEY (`acc_credit_code`)
  REFERENCES `nilua`.`acc_tree` (`acc_code`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `nilua`.`acc_trans` 
ADD CONSTRAINT `deb_code`
  FOREIGN KEY (`acc_debit_code`)
  REFERENCES `nilua`.`acc_tree` (`acc_code`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;
