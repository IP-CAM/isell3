ALTER TABLE `event_list` 
CHANGE COLUMN `event_is_private` `event_is_private` TINYINT(1) NOT NULL AFTER `event_id`,
ADD COLUMN `event_user_liable` VARCHAR(45) NULL DEFAULT NULL AFTER `event_user_id`,
CHANGE COLUMN `event_status` `event_status` VARCHAR(20) NOT NULL AFTER `event_user_liable`,
ADD COLUMN `event_priority` VARCHAR(45) NULL DEFAULT NULL AFTER `event_status`,
ADD COLUMN `event_repeat` VARCHAR(45) NULL DEFAULT NULL AFTER `event_date`;

ALTER TABLE `isell_nu`.`price_list` 
ADD COLUMN `label` VARCHAR(45) NOT NULL AFTER `product_code`,
DROP PRIMARY KEY,
ADD PRIMARY KEY USING BTREE (`product_code`, `label`);