<?php
require_once 'Document.php';
class View extends Document{
    public function listFetch(){
	$doc_id=$this->doc('doc_id');
	$sql="SELECT 
		    doc_view_id,
		    view_num,
		    view_name,
		    DATE_FORMAT(tstamp, '%d.%m.%Y') AS view_date,
		    view_type_id,
		    view_efield_values,
		    view_efield_labels,
		    view_file,
		    freezed,
		    view_hidden
		FROM
		    document_view_types
			LEFT JOIN 
		    document_view_list USING (view_type_id)
		WHERE
		    doc_id = '$doc_id'
		";
	return $this->get_list($sql);
    }
}