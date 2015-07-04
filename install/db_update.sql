INSERT IGNORE INTO `document_types` (doc_type,`doc_type_name`, `icon_name`) VALUES (14,'Письмо', 'letter');
INSERT IGNORE INTO `document_view_types` (view_type_id,`doc_type`, `view_name`, `view_tpl`) VALUES (28,'14', 'Лист про повернення коштів', 'html_form/list_povern_koshtiv.html');

INSERT IGNORE INTO `document_view_types` (`doc_type`, `view_name`, `view_efield_labels`, `view_tpl`) VALUES ('1', 'Товарно-Транспортна Накладна', '{\"vehicle\":\"Автомобіль\",\"vehicle2\":\"Номер причіпа\",\"del_comp\":\"Перевізник\",\"del_driver\":\"Водій\",\"place_number\":\"Місць цифрами\",\"place_number2\":\"Місць словами\",\"weight\":\"Вага словами\"}', 'xlsx/UKR_TTN_2014.xlsx' );

