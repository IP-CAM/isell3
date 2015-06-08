<?php
/**
 * Description of Document
 *
 * @author Baycik
 */
require_once 'Catalog.php';
class DocumentUtils extends Catalog{
    public $min_level=1;
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
    protected function updateProps( $props ){
	$doc_id = $this->doc('doc_id');
	$this->rowUpdate( 'document_list', $props, array('doc_id'=>$doc_id) );
	$this->selectDoc($doc_id);
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
    protected function getNextDocNum($doc_type) {//Util
	$active_company_id = $this->Base->acomp('company_id');
	$next_num = $this->get_value("SELECT MAX(doc_num)+1 FROM document_list WHERE doc_type='$doc_type' AND active_company_id='$active_company_id' AND cstamp>DATE_FORMAT(NOW(),'%Y')");
	return $next_num ? $next_num : 1;
    }
}
class DocumentCore extends DocumentUtils{
    public function listFetch( $page=1, $rows=30, $mode='' ){
	$offset=($page-1)*$rows;
	if( $offset<0 ){
	    $offset=0;
	}
	$having=$this->decodeFilterRules();
	$andwhere='';
	if( $mode==='show_only_pcomp_docs' ){
	    $pcomp_id=$this->Base->pcomp('company_id');
	    $andwhere.=" AND passive_company_id=$pcomp_id";
	}
	$assigned_path=  $this->Base->svar('user_assigned_path');
	if( $assigned_path ){
	    $andwhere.=" AND path LIKE '$assigned_path%'";
	}
	$sql="
	    SELECT 
		doc_id,
		CONCAT(icon_name,' ',doc_type_name) doc_type_icon,
		DATE_FORMAT(dl.cstamp,'%d.%m.%Y') doc_date,
		doc_num,
		doc_type_name,
		(SELECT amount 
		    FROM 
			acc_trans 
			    JOIN 
			document_trans USING(trans_id)
		    WHERE doc_id=dl.doc_id 
		    ORDER BY trans_id LIMIT 1) amount,
		label company_name,
		GROUP_CONCAT(CONCAT(' ',LEFT(view_name,3),view_num)) views,
		IF(is_commited,'ok Проведен','') as commited,
		(SELECT CONCAT(code,' ',descr) FROM acc_trans_status JOIN acc_trans USING(trans_status) JOIN document_trans USING(trans_id) WHERE doc_id=dl.doc_id ORDER BY trans_id LIMIT 1) trans_status
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
	    WHERE dl.doc_type<10 $andwhere
	    GROUP BY doc_id
	    HAVING $having
	    ORDER BY dl.is_commited,dl.cstamp DESC
	    LIMIT $rows OFFSET $offset
	";
	$result_rows=$this->get_list($sql);
	$total_estimate=$offset+(count($result_rows)==$rows?$rows+1:count($result_rows));
	return array('rows'=>$result_rows,'total'=>$total_estimate);
    }
    public function createDoc(){
	$pcomp_id=$this->Base->pcomp('company_id');
	if( $pcomp_id ){
	    $Document2=$this->Base->bridgeLoad('Document');
	    return $Document2->add();
	}
	return 0;
    }
    public function headGet( $doc_id ){
        $doc_id=(int) $doc_id;
//	if( $doc_id==0 ){
//	    $doc_id=$this->createDoc();
//	}
	$this->selectDoc($doc_id);
	$sql="
	    SELECT
		passive_company_id,
		IF(is_reclamation,-doc_type,doc_type) doc_type,
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
	    WHERE doc_id=".(int) $doc_id
	;
	return $this->get_row($sql);
    }
    private function setType( $doc_type ){
	if( $this->isCommited() ){
	    return false;
	}
	else{
	    $doc_id = $this->doc('doc_id');
	    $next_doc_num = $this->getNextDocNum($doc_type);
	    $this->db->query("DELETE FROM document_view_list WHERE doc_id='$doc_id'");
	    $quantity_sign = $doc_type<0 ? -1 : 1;
	    $this->db->query("UPDATE document_entries SET product_quantity=ABS(product_quantity)*$quantity_sign WHERE doc_id=$doc_id");
	    $this->updateProps( array(
		'doc_type'=>abs($doc_type),
		'doc_num'=>$next_doc_num,
		'is_reclamation'=>($doc_type<0)
	    ));
	}
	return true;
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
	    case 'doc_type':
		return $this->setType($new_val);
	}
	$new_val=  rawurldecode($new_val);
	$Document2=$this->Base->bridgeLoad('Document');
	return $Document2->updateHead($new_val,$field);
    }

}