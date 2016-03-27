SET NAMES 'utf8';

ALTER TABLE `prod_list` 
CHANGE COLUMN `product_code` `product_code` VARCHAR(45) NOT NULL COMMENT 'Код товара' ,
CHANGE COLUMN `ru` `ru` VARCHAR(255) NOT NULL COMMENT 'Название Рус.' ,
CHANGE COLUMN `ua` `ua` VARCHAR(255) NOT NULL COMMENT 'Назва Укр.' ,
CHANGE COLUMN `en` `en` VARCHAR(255) NOT NULL COMMENT 'Name En.' ,
CHANGE COLUMN `product_uktzet` `product_uktzet` VARCHAR(10) NOT NULL COMMENT 'Таможенный код' ,
CHANGE COLUMN `barcode` `barcode` VARCHAR(13) NOT NULL COMMENT 'Штрихкод' ,
CHANGE COLUMN `product_bpack` `product_spack` INT(10) UNSIGNED NOT NULL COMMENT 'Мал. упак.' ,
CHANGE COLUMN `product_spack` `product_bpack` INT(10) UNSIGNED NOT NULL COMMENT 'Бол. упак.' ,
CHANGE COLUMN `product_weight` `product_weight` DOUBLE NOT NULL COMMENT 'Вес ед.' ,
CHANGE COLUMN `product_volume` `product_volume` DOUBLE NOT NULL COMMENT 'Объем ед.' ,
CHANGE COLUMN `product_unit` `product_unit` VARCHAR(5) NOT NULL COMMENT 'Единица' ,
CHANGE COLUMN `is_service` `is_service` TINYINT(3) UNSIGNED NOT NULL COMMENT 'Услуга?' ,
CHANGE COLUMN `analyse_type` `analyse_type` VARCHAR(45) NULL COMMENT 'Тип' ,
CHANGE COLUMN `analyse_group` `analyse_group` VARCHAR(45) NULL DEFAULT NULL COMMENT 'Группа' ,
CHANGE COLUMN `analyse_class` `analyse_class` VARCHAR(45) NULL DEFAULT NULL COMMENT 'Класс' ,
CHANGE COLUMN `analyse_section` `analyse_section` VARCHAR(45) NULL DEFAULT NULL COMMENT 'Раздел' ;


ALTER TABLE `isell3`.`price_list` 
DROP FOREIGN KEY `FK_prodcode`;
ALTER TABLE `isell3`.`price_list` 
CHANGE COLUMN `label` `label` VARCHAR(45) NOT NULL COMMENT 'Группа цен' AFTER `curr_code`,
CHANGE COLUMN `product_code` `product_code` VARCHAR(45) NOT NULL COMMENT 'Код товара' ,
CHANGE COLUMN `sell` `sell` DOUBLE NOT NULL COMMENT 'Продажа' ,
CHANGE COLUMN `buy` `buy` DOUBLE NOT NULL COMMENT 'Покупка' ,
CHANGE COLUMN `curr_code` `curr_code` VARCHAR(45) NOT NULL COMMENT 'Код валюты' ;
ALTER TABLE `isell3`.`price_list` 
ADD CONSTRAINT `FK_prodcode`
  FOREIGN KEY (`product_code`)
  REFERENCES `isell3`.`prod_list` (`product_code`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

