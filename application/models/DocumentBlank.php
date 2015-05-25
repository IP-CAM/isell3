<?php
require_once 'DocumentCore.php';
class DocumentBlank extends DocumentCore {
    public function listFetch( $page=1, $rows=30, $mode='' ) {
	$offset=($page-1)*$rows;
	if( $offset<0 ){
	    $offset=0;
	}
        $sql = "SELECT 
		    label company_name,
                    doc_id,
                    icon_name doc_type_icon,
                    doc_type_name,
                    DATE_FORMAT(dl.cstamp, '%d.%m.%Y') as doc_date,
                    doc_num,
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
                ORDER BY cstamp , doc_num";
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
    public function createBlank( $view_type_id, $register_only = false ){
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
}
