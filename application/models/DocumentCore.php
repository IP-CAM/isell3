<?php
/**
 * Description of Document
 *
 * @author Baycik
 */
require_once 'Catalog.php';
class DocumentUtils extends Catalog{
    protected function selectDoc( $doc_id ){
	$this->Base->svar('doc_id',$doc_id);
	unset( $this->_doc );
    }
    private function checkPassiveLoad(){
	if( $this->Base->pcomp('company_id')!==$this->_doc->passive_company_id  ){
	    $this->Base->load_model('Company');
	    $this->Base->Company->selectPassiveCompany( $this->_doc->passive_company_id );	
	}
    }
    private function loadDoc($doc_id) {
	$this->_doc = $this->get_row("SELECT *, DATE_FORMAT(cstamp,'%d.%m.%Y') AS doc_date FROM document_list WHERE doc_id='$doc_id'");
	$this->_doc->vat_ratio=1 + $this->_doc->vat_rate / 100;
	$this->checkPassiveLoad();
    }
    protected function doc($name) {
	if ( !isset($this->_doc) ) {
	    $doc_id = $this->Base->svar('doc_id');
	    $this->loadDoc($doc_id);
	}
	return isset($this->_doc->$name)?$this->_doc->$name:NULL;
    }
    protected function isServiceDoc(){
	return $this->doc('doc_type')==3 OR $this->doc('doc_type')==4;
    }
    protected function isCommited() {
	return $this->doc('is_commited');
    }
    protected function setDocumentModifyingUser() {
	$user_id = $this->Base->svar('user_id');
	$this->rowUpdateField( 'document_list', 'doc_id', $this->doc('doc_id'), 'modified_by', $user_id );
    }
    
}
class DocumentCore extends DocumentUtils{
    public function listFetch(){
	$having=$this->decodeFilterRules();
	$pcomp_id=$this->Base->pcomp('company_id');
	$sql="
	    SELECT 
		doc_id,
		CONCAT(icon_name,' ',doc_type_name) doc_type_icon,
		DATE_FORMAT(dl.cstamp,'%d.%m.%Y') doc_date,
		doc_num,
		doc_type_name,
		333.44 amount,
		label company_name,
		GROUP_CONCAT(CONCAT(LEFT(view_name,3),view_num)) views,
		IF(is_commited,'ok РџСЂРѕРІРµРґРµРЅ','') as commited,
		'payed Оплачено' trans_status
	    FROM 
		document_list dl
		    JOIN
		document_types dt USING(doc_type)
		    JOIN
		companies_list cl ON passive_company_id=company_id
		    JOIN
		companies_tree ct USING(branch_id)
		    LEFT JOIN
		document_view_list dv USING(doc_id)
		    LEFT JOIN
		document_view_types dvt USING(view_type_id)
	    WHERE dl.doc_type<10
	    GROUP BY doc_id
	    HAVING $having
	    ORDER BY cstamp DESC
	    LIMIT 130
	";
	return $this->get_list($sql);
    }
    public function headGet( $doc_id ){
	$this->selectDoc($doc_id);
	$sql="
	    SELECT
		passive_company_id,
		IF(is_reclamation,-1,1)*doc_type doc_type,
		is_reclamation,
		is_commited,
		notcount,
		vat_rate,
		use_vatless_price,
		signs_after_dot,
		doc_ratio,
		doc_num,
		DATE_FORMAT(cstamp,'%d.%m.%Y') doc_date,
		doc_data,
		(SELECT last_name FROM user_list WHERE user_id=created_by) created_by,
		(SELECT last_name FROM user_list WHERE user_id=modified_by) modified_by
	    FROM
		document_list
	    WHERE doc_id=$doc_id
	";
	return $this->get_row($sql);
    }
    public function headUpdate( $field, $new_val ){
	switch( $field ){
	    case 'doc_ratio':
		$field='ratio';
		break;
	    case 'doc_num':
		$field='num';
		break;
	    case 'doc_date':
		$field='date';
		break;
	    case 'passive_company_id':
		if( $this->isCommited() ){
		    return false;
		}
		else{
		    $doc_id=$this->doc('doc_id');
		    $passive_company_id=$new_val;
		    $this->db->query("UPDATE document_list SET passive_company_id=$passive_company_id WHERE doc_id=$doc_id");
		    return true;
		}
		break;
	}
	$new_val=  rawurldecode($new_val);
	$Document2=$this->Base->bridgeLoad('Document');
	return $Document2->updateHead($new_val,$field);
    }

}