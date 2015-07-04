<?php
require_once 'DocumentItems.php';
class DocumentView extends DocumentItems{
    public $min_level=1;
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
    public function viewUpdate2222( $doc_view_id, $field, $value, $is_extra=0 ){
	$Document2=$this->Base->bridgeLoad('Document');
	return $Document2->updateView($doc_view_id, $field, $value, $is_extra);
    }
    public function viewUpdate($doc_view_id, $is_extra, $field, $value='') {
	$this->check($doc_view_id,'int');
	$this->check($field,'string');
	$this->check($value,'string');
	$this->check($is_extra);
	
	if ( $this->isCommited() ){
	    $this->Base->set_level(2);
	}
	if ( $this->get_value("SELECT freezed FROM document_view_list WHERE doc_view_id='$doc_view_id'") ){
	    $this->Base->msg('Образ заморожен! Чтобы изменить снимите блокировку!');
	    return false;
	}
	if ( $is_extra==='extra' ) {
	    $extra_fields_str = $this->get_value("SELECT view_efield_values FROM document_view_list WHERE doc_view_id='$doc_view_id'");
	    $extra_fields = json_decode($extra_fields_str);
	    $extra_fields->$field = $value;
	    $field = 'view_efield_values';
	    $value = addslashes(json_encode($extra_fields));
	} else {
	    if ( !in_array($field, array('view_num', 'view_date')) ){
		$this->Base->msg('USING UNALLOWED FIELD NAME');
		return false;
	    }
	    if ($field == 'view_date') {
		$field = 'tstamp';
		preg_match_all('/([0-9]{2})\.([0-9]{2})\.([0-9]{2,4})/', $value, $out);
		$value = date("Y-m-d H:i:s", mktime(0, 0, 0, $out[2][0], $out[1][0], $out[3][0]));
	    }
	}
	$user_id = $this->Base->svar('user_id');
	$this->query("UPDATE document_view_list SET $field='$value',modified_by='$user_id' WHERE doc_view_id='$doc_view_id'");
	return true;
    }
    public function viewDelete( $doc_view_id ){
	$Document2=$this->Base->bridgeLoad('Document');
	return $Document2->deleteView($doc_view_id);
    }
    public function viewCreate( $view_type_id ){
	$Document2=$this->Base->bridgeLoad('Document');
	return $Document2->insertView($view_type_id);
    }
    public function unfreezeView($doc_view_id) {
	$this->query("UPDATE document_view_list SET freezed=0, html='' WHERE doc_view_id='$doc_view_id'");
	return true;
    }

    public function freezeView($doc_view_id, $html) {
	$html = addslashes($html);
	$this->query("UPDATE document_view_list SET freezed=1, html='$html' WHERE doc_view_id='$doc_view_id'");
	return true;
    }
}