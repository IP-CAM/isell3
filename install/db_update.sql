CREATE TABLE  IF NOT EXISTS `imported_data` (
  `row_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(45)  NOT NULL,
  `A` text,
  `B` text,
  `C` text,
  `D` text,
  `E` text,
  `F` text,
  `G` text,
  `H` text,
  `I` text,
  `K` text,
  `L` text,
  `M` text,
  `N` text,
  `O` text,
  `P` text,
  `Q` text,
  PRIMARY KEY (`row_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
ALTER TABLE `stock_tree` 
ADD COLUMN `path` TEXT NULL AFTER `level`;
