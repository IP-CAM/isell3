<?php
require_once 'DocumentItems.php';
class DocumentView extends DocumentItems{
    public $min_level=1;
    public function viewListFetch( $doc_id ){
	$this->check($doc_id);
	if( $doc_id ){
	    $this->selectDoc($doc_id);
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
	} else {
	    return [];
	}

    }
    public function viewUpdate($doc_view_id, $is_extra, $field, $value='') {
	$this->check($doc_view_id,'int');
	$this->check($field);
	$this->check($value);
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
    
    public function docViewGet(){
        $doc_view_id=$this->request('doc_view_id', 'int');
        $out_type=$this->request('out_type');
        $doc_view=$this->get_row("SELECT doc_view_id,doc_id FROM document_view_list WHERE doc_view_id='$doc_view_id'");
        $doc_id=$doc_view['doc_id'];
        $this->selectDoc($doc_id);
        $doc_view=  $this->docViewCompile($doc_view_id);
        
        //$Company=$this->Base->load_model("Company");
        
        $acomp=$this->Base->svar('acomp');
        $pcomp=$this->Base->svar('pcomp');
        
        $dump=[
	    'tpl_files'=>$this->Base->acomp('language').'/StockValidation.xlsx',
	    'title'=>"Залишки на складі",
	    'user_data'=>[
		'email'=>$this->Base->svar('pcomp')?$this->Base->svar('pcomp')->company_email:'',
		'text'=>'Доброго дня'
	    ],
            'view'=>[
                'head'=>$this->headGet($doc_id),
                'rows'=>$this->entriesFetch(),
                'footer'=>$this->footerGet(),
                'doc_view'=>$doc_view
            ]
        ];
        $dump=$this->docViewDumpPrepare($dump);
    }
    
    private function docViewDumpPrepare($dump){
        $Utils=$this->Base->load_model('Utils');
        
        $doc_view->total_spell=$Utils->spellAmount(152.36);
        $doc_view->loc_date=$Utils->getLocalDate($doc_view->tstamp);
        $doc_view->extra=json_decode($doc_view);
        
        
        return $dump;
    }
}