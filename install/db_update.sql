ALTER TABLE `document_trans` 
ADD COLUMN `label` VARCHAR(45) NULL DEFAULT NULL AFTER `type`;

UPDATE document_trans SET label='profit' WHERE `type`='791_441';
UPDATE document_trans SET label='total' WHERE `type`='361_702' OR  `type`='28_631' OR  `type`='361_44' OR `type`='44_631';
UPDATE document_trans SET label='vatless' WHERE `type`='702_791' OR  `type`='281_28' OR  `type`='44_441' OR `type`='441_44';
UPDATE document_trans SET label='vat' WHERE `type`='702_641' OR  `type`='641_28' OR  `type`='44_641' OR `type`='641_44';
UPDATE document_trans SET label='self' WHERE `type`='791_281';


