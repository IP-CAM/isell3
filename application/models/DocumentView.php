<?php
require_once 'DocumentItems.php';
class DocumentView extends DocumentItems{
    public function listFetch(){
	$doc_id=$this->doc('doc_id');
	$doc_type=$this->doc('doc_type');
	$sql="SELECT 
		    doc_view_id,
		    view_num,
		    view_name,
		    DATE_FORMAT(tstamp, '%d.%m.%Y') AS view_date,
		    dvt.view_type_id,
		    view_efield_values,
		    view_efield_labels,
		    view_file,
		    freezed,
		    IF(view_hidden AND doc_view_id IS NULL,1,0) view_hidden
		FROM
		    document_view_types dvt
			LEFT JOIN 
		    document_view_list dvl ON dvl.view_type_id=dvt.view_type_id AND doc_id = '$doc_id'
		WHERE
		    doc_type=$doc_type
		GROUP BY 
		    view_type_id
		ORDER BY
		    view_hidden
		";
	return $this->get_list($sql);
    }
    public function viewUpdate( $doc_view_id, $field, $value, $is_extra=0 ){
	$Document2=$this->Base->bridgeLoad('Document');
	return $Document2->updateView($doc_view_id, $field, $value, $is_extra);
    }
    public function viewDelete( $doc_view_id ){
	$Document2=$this->Base->bridgeLoad('Document');
	return $Document2->deleteView($doc_view_id);
    }
    public function viewCreate( $view_type_id ){
	$Document2=$this->Base->bridgeLoad('Document');
	return $Document2->insertView($view_type_id);
    }
}