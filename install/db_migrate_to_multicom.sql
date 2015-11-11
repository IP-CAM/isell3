-- MySQL Workbench Synchronization
-- Generated: 2015-11-11 15:16
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: baycik

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `isell3`.`companies_tree` 
CHANGE COLUMN `branch_id` `branch_id` INT(10) UNSIGNED NULL DEFAULT NULL AUTO_INCREMENT ;

ALTER TABLE `isell3`.`stock_tree` 
DROP COLUMN `path`,
CHANGE COLUMN `branch_id` `branch_id` INT(10) UNSIGNED NULL DEFAULT NULL AUTO_INCREMENT ;


-- -----------------------------------------------------
-- Placeholder table for view `isell3`.`document_view_registry`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `isell3`.`document_view_registry` (`doc_date` INT, `company_name` INT, `doc_name` INT, `view_num` INT);

-- -----------------------------------------------------
-- Placeholder table for view `isell3`.`stat_annual_sellbuy`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `isell3`.`stat_annual_sellbuy` (`y` INT, `tp` INT, `pgroup` INT, `cname` INT, `m01` INT, `m02` INT, `m03` INT, `m04` INT, `m05` INT, `m06` INT, `m07` INT, `m08` INT, `m09` INT, `m10` INT, `m11` INT, `m12` INT, `total` INT);

-- -----------------------------------------------------
-- Placeholder table for view `isell3`.`stat_annual_sold`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `isell3`.`stat_annual_sold` (`m` INT, `invoice` INT, `self` INT, `net` INT, `p` INT);

-- -----------------------------------------------------
-- Placeholder table for view `isell3`.`stat_brand_sold`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `isell3`.`stat_brand_sold` (`m` INT, `label` INT, `sum` INT);

-- -----------------------------------------------------
-- Placeholder table for view `isell3`.`stat_sell_analyse`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `isell3`.`stat_sell_analyse` (`m` INT, `label` INT, `product_code` INT, `en` INT, `qty` INT, `avg_self` INT, `self` INT, `avg_sell` INT, `invoice` INT, `net` INT);

-- -----------------------------------------------------
-- Placeholder table for view `isell3`.`stock_entry_view`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `isell3`.`stock_entry_view` (`stock_entry_id` INT, `parent_id` INT, `parent_label` INT, `party_label` INT, `mc3` INT, `m1` INT, `product_wrn_quantity` INT, `product_code` INT, `ru` INT, `product_quantity` INT, `vat_quantity` INT, `self_price` INT);

-- -----------------------------------------------------
-- Placeholder table for view `isell3`.`stat_brand_comp_sold`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `isell3`.`stat_brand_comp_sold` (`m` INT, `label` INT, `company_name` INT, `sum` INT, `self` INT);


USE `isell3`;

-- -----------------------------------------------------
-- View `isell3`.`document_view_registry`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `isell3`.`document_view_registry`;
USE `isell3`;
CREATE 
     OR REPLACE ALGORITHM = UNDEFINED 
    DEFINER = `root`@`localhost` 
    SQL SECURITY DEFINER
VIEW `document_view_registry` AS
    SELECT 
        DATE_FORMAT(`document_list`.`cstamp`,
                '%d.%m.%Y') AS `doc_date`,
        `companies_list`.`company_name` AS `company_name`,
        CONCAT(`document_types`.`doc_type_name`,
                IF(`document_list`.`is_reclamation`,
                    ' (Возврат) #',
                    ' #'),
                `document_list`.`doc_num`) AS `doc_name`,
        CONCAT(`document_view_types`.`view_name`,
                ' #',
                `document_view_list`.`view_num`) AS `view_num`
    FROM
        ((((`document_list`
        JOIN `document_types` ON ((`document_list`.`doc_type` = `document_types`.`doc_type`)))
        JOIN `document_view_list` ON ((`document_list`.`doc_id` = `document_view_list`.`doc_id`)))
        JOIN `document_view_types` ON ((`document_view_list`.`view_type_id` = `document_view_types`.`view_type_id`)))
        JOIN `companies_list` ON ((`companies_list`.`company_id` = `document_list`.`passive_company_id`)))
    WHERE
        `document_list`.`is_commited`
    ORDER BY `document_list`.`cstamp` DESC;


USE `isell3`;

-- -----------------------------------------------------
-- View `isell3`.`stat_annual_sellbuy`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `isell3`.`stat_annual_sellbuy`;
USE `isell3`;
CREATE 
     OR REPLACE ALGORITHM = UNDEFINED 
    DEFINER = `root`@`localhost` 
    SQL SECURITY DEFINER
VIEW `isell3`.`stat_annual_sellbuy` AS
    SELECT 
        SUBSTR(`dl`.`cstamp`, 1, 4) AS `y`,
        IF((`dl`.`doc_type` = 1), 'sell', 'buy') AS `tp`,
        `st2`.`label` AS `pgroup`,
        `ct`.`label` AS `cname`,
        ROUND(SUM(IF((SUBSTR(`dl`.`cstamp`, 6, 2) = '01'),
                    (`de`.`invoice_price` * `de`.`product_quantity`),
                    0)),
                0) AS `m01`,
        ROUND(SUM(IF((SUBSTR(`dl`.`cstamp`, 6, 2) = '02'),
                    (`de`.`invoice_price` * `de`.`product_quantity`),
                    0)),
                0) AS `m02`,
        ROUND(SUM(IF((SUBSTR(`dl`.`cstamp`, 6, 2) = '03'),
                    (`de`.`invoice_price` * `de`.`product_quantity`),
                    0)),
                0) AS `m03`,
        ROUND(SUM(IF((SUBSTR(`dl`.`cstamp`, 6, 2) = '04'),
                    (`de`.`invoice_price` * `de`.`product_quantity`),
                    0)),
                0) AS `m04`,
        ROUND(SUM(IF((SUBSTR(`dl`.`cstamp`, 6, 2) = '05'),
                    (`de`.`invoice_price` * `de`.`product_quantity`),
                    0)),
                0) AS `m05`,
        ROUND(SUM(IF((SUBSTR(`dl`.`cstamp`, 6, 2) = '06'),
                    (`de`.`invoice_price` * `de`.`product_quantity`),
                    0)),
                0) AS `m06`,
        ROUND(SUM(IF((SUBSTR(`dl`.`cstamp`, 6, 2) = '07'),
                    (`de`.`invoice_price` * `de`.`product_quantity`),
                    0)),
                0) AS `m07`,
        ROUND(SUM(IF((SUBSTR(`dl`.`cstamp`, 6, 2) = '08'),
                    (`de`.`invoice_price` * `de`.`product_quantity`),
                    0)),
                0) AS `m08`,
        ROUND(SUM(IF((SUBSTR(`dl`.`cstamp`, 6, 2) = '09'),
                    (`de`.`invoice_price` * `de`.`product_quantity`),
                    0)),
                0) AS `m09`,
        ROUND(SUM(IF((SUBSTR(`dl`.`cstamp`, 6, 2) = '10'),
                    (`de`.`invoice_price` * `de`.`product_quantity`),
                    0)),
                0) AS `m10`,
        ROUND(SUM(IF((SUBSTR(`dl`.`cstamp`, 6, 2) = '11'),
                    (`de`.`invoice_price` * `de`.`product_quantity`),
                    0)),
                0) AS `m11`,
        ROUND(SUM(IF((SUBSTR(`dl`.`cstamp`, 6, 2) = '12'),
                    (`de`.`invoice_price` * `de`.`product_quantity`),
                    0)),
                0) AS `m12`,
        ROUND(SUM((`de`.`invoice_price` * `de`.`product_quantity`)),
                0) AS `total`
    FROM
        ((((((`isell3`.`document_list` `dl`
        JOIN `isell3`.`document_entries` `de` ON ((`dl`.`doc_id` = `de`.`doc_id`)))
        JOIN `isell3`.`stock_entries` `se` ON ((`de`.`product_code` = `se`.`product_code`)))
        JOIN `isell3`.`stock_tree` `st1` ON ((`st1`.`branch_id` = `se`.`parent_id`)))
        JOIN `isell3`.`stock_tree` `st2` ON ((`st2`.`branch_id` = `st1`.`top_id`)))
        JOIN `isell3`.`companies_list` `cl` ON ((`dl`.`passive_company_id` = `cl`.`company_id`)))
        JOIN `isell3`.`companies_tree` `ct` ON ((`ct`.`branch_id` = `cl`.`branch_id`)))
    WHERE
        (((`dl`.`doc_type` = 1)
            OR (`dl`.`doc_type` = 2))
            AND (`dl`.`is_commited` = 1))
    GROUP BY SUBSTR(`dl`.`cstamp`, 1, 4) , `dl`.`doc_type` , `st2`.`label` , `cl`.`company_name`;


USE `isell3`;

-- -----------------------------------------------------
-- View `isell3`.`stat_annual_sold`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `isell3`.`stat_annual_sold`;
USE `isell3`;
CREATE 
     OR REPLACE ALGORITHM = UNDEFINED 
    DEFINER = `root`@`localhost` 
    SQL SECURITY DEFINER
VIEW `stat_annual_sold` AS
    SELECT 
        SUBSTR(`document_list`.`cstamp`,
            1,
            7) AS `m`,
        ROUND(SUM((`document_entries`.`product_quantity` * `document_entries`.`invoice_price`)),
                0) AS `invoice`,
        ROUND(SUM((`document_entries`.`product_quantity` * `document_entries`.`self_price`)),
                0) AS `self`,
        ROUND(SUM((`document_entries`.`product_quantity` * (`document_entries`.`invoice_price` - `document_entries`.`self_price`))),
                0) AS `net`,
        CONCAT(ROUND(((SUM((`document_entries`.`product_quantity` * (`document_entries`.`invoice_price` - `document_entries`.`self_price`))) / SUM((`document_entries`.`product_quantity` * `document_entries`.`self_price`))) * 100),
                        0),
                '%') AS `p`
    FROM
        (((`document_entries`
        JOIN `document_list` ON ((`document_entries`.`doc_id` = `document_list`.`doc_id`)))
        JOIN `companies_list` ON ((`companies_list`.`company_id` = `document_list`.`passive_company_id`)))
        JOIN `companies_tree` ON ((`companies_list`.`branch_id` = `companies_tree`.`branch_id`)))
    WHERE
        ((`document_list`.`doc_type` = 1)
            AND (`document_list`.`is_commited` = 1))
    GROUP BY SUBSTR(`document_list`.`cstamp`,
        1,
        7);


USE `isell3`;

-- -----------------------------------------------------
-- View `isell3`.`stat_brand_sold`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `isell3`.`stat_brand_sold`;
USE `isell3`;
CREATE 
     OR REPLACE ALGORITHM = UNDEFINED 
    DEFINER = `root`@`localhost` 
    SQL SECURITY DEFINER
VIEW `isell3`.`stat_brand_sold` AS
    SELECT 
        SUBSTR(`dl`.`cstamp`, 1, 7) AS `m`,
        `st2`.`label` AS `label`,
        ROUND(SUM((`de`.`invoice_price` * `de`.`product_quantity`)),
                0) AS `sum`
    FROM
        ((((`isell3`.`document_entries` `de`
        JOIN `isell3`.`document_list` `dl` ON ((`de`.`doc_id` = `dl`.`doc_id`)))
        JOIN `isell3`.`stock_entries` `se` ON ((`de`.`product_code` = `se`.`product_code`)))
        JOIN `isell3`.`stock_tree` `st` ON ((`se`.`parent_id` = `st`.`branch_id`)))
        JOIN `isell3`.`stock_tree` `st2` ON ((`st`.`top_id` = `st2`.`branch_id`)))
    WHERE
        ((`dl`.`is_commited` = 1)
            AND (`dl`.`doc_type` = 1))
    GROUP BY `st2`.`branch_id` , SUBSTR(`dl`.`cstamp`, 1, 7)
    ORDER BY `st2`.`label` , SUBSTR(`dl`.`cstamp`, 1, 7);


USE `isell3`;

-- -----------------------------------------------------
-- View `isell3`.`stat_sell_analyse`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `isell3`.`stat_sell_analyse`;
USE `isell3`;
CREATE 
     OR REPLACE ALGORITHM = UNDEFINED 
    DEFINER = `root`@`localhost` 
    SQL SECURITY DEFINER
VIEW `stat_sell_analyse` AS
    SELECT 
        SUBSTR(`document_list`.`cstamp`,
            1,
            7) AS `m`,
        `stock_tree`.`label` AS `label`,
        `de`.`product_code` AS `product_code`,
        IF((`prod_list`.`en` <> ''),
            `prod_list`.`en`,
            `prod_list`.`ru`) AS `en`,
        SUM(`de`.`product_quantity`) AS `qty`,
        ROUND((SUM((`de`.`self_price` * `de`.`product_quantity`)) / SUM(`de`.`product_quantity`)),
                2) AS `avg_self`,
        ROUND(SUM((`de`.`self_price` * `de`.`product_quantity`)),
                0) AS `self`,
        ROUND((SUM((`de`.`invoice_price` * `de`.`product_quantity`)) / SUM(`de`.`product_quantity`)),
                2) AS `avg_sell`,
        ROUND(SUM((`de`.`invoice_price` * `de`.`product_quantity`)),
                0) AS `invoice`,
        ROUND(SUM(((`de`.`invoice_price` - `de`.`self_price`) * `de`.`product_quantity`)),
                0) AS `net`
    FROM
        ((((`document_entries` `de`
        JOIN `document_list` ON ((`de`.`doc_id` = `document_list`.`doc_id`)))
        JOIN `prod_list` ON ((`de`.`product_code` = `prod_list`.`product_code`)))
        JOIN `stock_entries` `se` ON ((`de`.`product_code` = `se`.`product_code`)))
        JOIN `stock_tree` ON ((`stock_tree`.`branch_id` = `se`.`parent_id`)))
    WHERE
        ((`document_list`.`doc_type` = 1)
            AND `document_list`.`is_commited`)
    GROUP BY SUBSTR(`document_list`.`cstamp`,
        1,
        7) , `prod_list`.`product_uktzet` , `de`.`product_code`;


USE `isell3`;

-- -----------------------------------------------------
-- View `isell3`.`stock_entry_view`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `isell3`.`stock_entry_view`;
USE `isell3`;
CREATE 
     OR REPLACE ALGORITHM = UNDEFINED 
    DEFINER = `root`@`localhost` 
    SQL SECURITY DEFINER
VIEW `stock_entry_view` AS
    SELECT 
        `se`.`stock_entry_id` AS `stock_entry_id`,
        `se`.`parent_id` AS `parent_id`,
        `st`.`label` AS `parent_label`,
        `se`.`party_label` AS `party_label`,
        ROUND(((SUM(IF(((`dl`.`is_commited` = 1)
                        AND (`dl`.`doc_type` = 1)
                        AND ((TO_DAYS(NOW()) - TO_DAYS(`dl`.`cstamp`)) <= 90)),
                    `de`.`product_quantity`,
                    0)) / MAX(IF(((`dl`.`is_commited` = 1)
                        AND (`dl`.`doc_type` = 1)
                        AND ((TO_DAYS(NOW()) - TO_DAYS(`dl`.`cstamp`)) <= 90)
                        AND ((TO_DAYS(NOW()) - TO_DAYS(`dl`.`cstamp`)) >= 30)),
                    (TO_DAYS(NOW()) - TO_DAYS(`dl`.`cstamp`)),
                    30))) * 30),
                0) AS `mc3`,
        SUM(IF((((TO_DAYS(NOW()) - TO_DAYS(`dl`.`cstamp`)) <= 30)
                AND (`dl`.`is_commited` = 1)
                AND (`dl`.`doc_type` = 1)),
            `de`.`product_quantity`,
            0)) AS `m1`,
        `se`.`product_wrn_quantity` AS `product_wrn_quantity`,
        `se`.`product_code` AS `product_code`,
        `prod_list`.`ru` AS `ru`,
        `se`.`product_quantity` AS `product_quantity`,
        `se`.`vat_quantity` AS `vat_quantity`,
        `se`.`self_price` AS `self_price`
    FROM
        ((((`stock_entries` `se`
        LEFT JOIN `prod_list` ON ((`se`.`product_code` = `prod_list`.`product_code`)))
        LEFT JOIN `document_entries` `de` ON ((`se`.`product_code` = `de`.`product_code`)))
        LEFT JOIN `document_list` `dl` ON ((`de`.`doc_id` = `dl`.`doc_id`)))
        LEFT JOIN `stock_tree` `st` ON ((`st`.`branch_id` = `se`.`parent_id`)))
    GROUP BY `se`.`product_code`;


USE `isell3`;

-- -----------------------------------------------------
-- View `isell3`.`stat_brand_comp_sold`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `isell3`.`stat_brand_comp_sold`;
USE `isell3`;

CREATE 
     OR REPLACE ALGORITHM = UNDEFINED 
    DEFINER = `root`@`localhost` 
    SQL SECURITY DEFINER
VIEW `stat_brand_comp_sold` AS
    SELECT 
        SUBSTR(`dl`.`cstamp`, 1, 7) AS `m`,
        `st2`.`label` AS `label`,
        company_name,
        ROUND(SUM((`de`.`invoice_price` * `de`.`product_quantity`)),
                0) AS `sum`,
		ROUND(SUM(de.self_price*de.product_quantity)) self
    FROM
        ((((`document_entries` `de`
        JOIN `document_list` `dl` ON ((`de`.`doc_id` = `dl`.`doc_id`)))
        JOIN `companies_list` `cl` ON company_id=passive_company_id
        JOIN `stock_entries` `se` ON ((`de`.`product_code` = `se`.`product_code`)))
        JOIN `stock_tree` `st` ON ((`se`.`parent_id` = `st`.`branch_id`)))
        JOIN `stock_tree` `st2` ON ((`st`.`top_id` = `st2`.`branch_id`)))
    WHERE
        ((`dl`.`is_commited` = 1)
            AND (`dl`.`doc_type` = 1))
    GROUP BY `st2`.`branch_id` , SUBSTR(`dl`.`cstamp`, 1, 7)
    ORDER BY `st2`.`label` , SUBSTR(`dl`.`cstamp`, 1, 7);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
