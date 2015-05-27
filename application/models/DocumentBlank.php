<?php
require_once 'DocumentCore.php';
class DocumentBlank extends DocumentCore {
    public function listFetch( $page=1, $rows=30, $mode='' ) {
	$offset=($page-1)*$rows;
	if( $offset<0 ){
	    $offset=0;
	}
        $sql = "SELECT 
                    doc_id,
                    doc_type_name,
                    doc_num,
		    IF(html>'','ok','unknown') saved,
		    label company_name,
                    icon_name doc_type_icon,
                    DATE_FORMAT(dl.cstamp, '%d.%m.%Y') as doc_date,
                    COALESCE(view_name, CONCAT('REG ', doc_type_name)) as view_name
                FROM
                    document_list dl
			JOIN
		    companies_list cl ON passive_company_id=company_id
			JOIN
		    companies_tree ct USING(branch_id)
                        JOIN
                    document_types USING (doc_type)
                        LEFT JOIN
                    document_view_list USING (doc_id)
                        LEFT JOIN
                    document_view_types USING (view_type_id)
                WHERE
                    dl.doc_type > 9
                        AND dl.active_company_id = '" . $this->Base->acomp('company_id') . "'
                        AND dl.passive_company_id = '" . $this->Base->pcomp('company_id') . "'
                ORDER BY html>'',cstamp , doc_num";
	$result_rows=$this->get_list($sql);
	$total_estimate=$offset+(count($result_rows)==$rows?$rows+1:count($result_rows));
	return array('rows'=>$result_rows,'total'=>$total_estimate);
    }
    public function availFetch() {
        $avail_docs = $this->get_list("SELECT * FROM document_types WHERE doc_type>=10");
        foreach ($avail_docs as &$doc) {
            $doc->avail_views = $this->get_list("SELECT view_type_id,view_name,IF(view_file='',0,1) AS only_reg FROM document_view_types WHERE doc_type='$doc->doc_type' AND view_file<>''");
        }
        return $avail_docs;
    }
    public function blankCreate( $view_type_id, $register_only = false ){
        $doc_type = $this->get_value("SELECT doc_type FROM document_view_types WHERE view_type_id='$view_type_id'");
	$Document2=$this->Base->bridgeLoad('Document');
        $Document2->add($doc_type);
        if ($register_only === false){
            $Document2->insertView($view_type_id);
        }
        $doc_id=$Document2->doc('doc_id');
        $this->Base->svar('selectedBlankId',$doc_id);
        return $doc_id;
    }
    public function blankGet($doc_id) {
        $this->Base->svar('selectedBlankId',$doc_id);
	$Document2=$this->Base->bridgeLoad('Document');
        $Document2->selectDoc($this->Base->svar('selectedBlankId'));
        $blank = $this->get_row("SELECT * FROM document_view_list JOIN document_view_types USING(view_type_id) WHERE doc_id='$doc_id'");
        if (!$blank) {//only registry record
            $doc_type = $Document2->doc('doc_type');
            $blank = $this->get_row("SELECT view_name FROM document_view_types WHERE doc_type='$doc_type'");
        } elseif ($blank->html) {
            $blank->html = stripslashes($blank->html);
        } else {
            $blank->html = file_get_contents('views/rpt/' . $blank->view_file, true);
            $blank->loaded_is_tpl = true;
        }
        $blank->doc_num = $Document2->doc('doc_num');
        $blank->doc_date = $Document2->doc('doc_date');
        $blank->doc_data = $Document2->doc('doc_data');
        return $blank;
    }
    public function getFillData(){
	$Company=$this->Base->load_model('Company');
	$Pref=$this->Base->load_model('Pref');
	$fillData = new stdClass();
	$fillData->a=$Company->companyGet($this->Base->acomp('company_id'));
	$fillData->p=$Company->companyGet($this->Base->pcomp('company_id'));
	$fillData->staff=$Pref->getStaffList();
	return $fillData;
    }
}
