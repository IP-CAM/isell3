SET NAMES 'utf8';

ALTER TABLE `document_view_types` 
ADD COLUMN `blank_set` VARCHAR(2) NULL AFTER `doc_types`;
update `document_view_types` set blank_set='ua';