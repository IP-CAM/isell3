ALTER TABLE `document_trans` 
ADD COLUMN `trans_role` VARCHAR(45) NULL DEFAULT NULL AFTER `type`;

UPDATE document_trans SET trans_role='profit' WHERE `type`='791_441';
UPDATE document_trans SET trans_role='total' WHERE `type`='361_702' OR  `type`='28_631' OR  `type`='361_44' OR `type`='44_631';
UPDATE document_trans SET trans_role='vatless' WHERE `type`='702_791' OR  `type`='281_28' OR  `type`='44_441' OR `type`='441_44';
UPDATE document_trans SET trans_role='vat' WHERE `type`='702_641' OR  `type`='641_28' OR  `type`='44_641' OR `type`='641_44';
UPDATE document_trans SET trans_role='self' WHERE `type`='791_281';


ALTER TABLE `document_view_list` 
ADD COLUMN `view_role` VARCHAR(45) NULL DEFAULT NULL AFTER `freezed`;

UPDATE document_view_list dvl JOIN document_view_types dvt USING(view_type_id) SET dvl.view_role=dvt.view_role;


