<?php
/**
 * Description of Document
 *
 * @author Baycik
 */
require_once 'Catalog.php';
class DocumentCore extends Catalog{
    protected function selectDoc( $doc_id ){
	$this->Base->svar('doc_id',$doc_id);
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

}
class Document extends DocumentCore{
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
		'payed РћРїР»Р°С‡РµРЅ' trans_status
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
		IF(is_reclamation,-1,1)*doc_type doc_type,
		is_reclamation,
		is_commited,
		notcount,
		vat_rate,
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
    public function suggestFetch(){
	$q=$this->input->get('q');
	$company_lang = $this->Base->pcomp('language');
	$sql="
	    SELECT
		product_code,
		CONCAT(product_code,' ',$company_lang) label
	    FROM
		prod_list
	    WHERE
		product_code LIKE '%$q%' OR $company_lang LIKE '%$q%'
	    LIMIT 15
	    ";
	return $this->get_list($sql);
    }
    private function footerGet(){
	$doc_id=$this->doc('doc_id');
	$this->calcCorrections();
	$sql = "
	    SELECT
		total_weight,
		total_volume,
		vatless,
		total - vatless vat,
		total,
		@curr_symbol curr_symbol,
		self
	    FROM
		(SELECT
		    ROUND(SUM(product_quantity*product_weight),2) total_weight,
		    ROUND(SUM(product_quantity*product_volume),2) total_volume,
		    SUM(ROUND(product_quantity*invoice_price * @curr_correction,2)) vatless,
		    SUM(ROUND(product_quantity*invoice_price * @curr_correction * @vat_ratio,2)) total,
		    SUM(ROUND(product_quantity*self_price,2)) self
		FROM
		    document_entries JOIN prod_list USING(product_code)
		WHERE doc_id='$doc_id') t";
	return $this->get_row($sql);
    }
    public function entriesFetch(){
	$doc_id=$this->doc('doc_id');
	$this->calcCorrections();
	$company_lang = $this->Base->pcomp('language');
	$sql = "SELECT
                doc_entry_id,
                pl.product_code,
                $company_lang product_name,
                product_quantity,
                product_unit,
                ROUND(invoice_price * @vat_correction * @curr_correction,signs_after_dot) AS product_price,
                ROUND(invoice_price * @vat_correction * @curr_correction * product_quantity,2) AS product_sum,
                CHK_ENTRY(doc_entry_id) AS row_status,
                party_label,
                product_uktzet
            FROM
                document_list
		    JOIN
		document_entries USING(doc_id)
		    JOIN 
		prod_list pl USING(product_code)
            WHERE
                doc_id='$doc_id'
            ORDER BY pl.product_code";
	return $this->get_list($sql);
    }
    public function documentGet(){
	$document=array();
	$document['entries']=$this->entriesFetch();
	$document['footer']=$this->footerGet();
	return $document;
    }
    private function calcCorrections() {
	$doc_id=$this->doc('doc_id');
	$curr_symbol=$this->Base->pcomp('curr_symbol');
	$native_curr=($this->Base->pcomp('curr_code') == $this->Base->acomp('curr_code'))?1:0;
	$sql="SELECT 
		@vat_ratio:=1+vat_rate/100 vat_ratio,
		@vat_correction:=IF(use_vatless_price,1,@vat_ratio) vat_correction,
		@curr_correction:=IF($native_curr,1,1/doc_ratio) curr_correction,
		@curr_symbol:='$curr_symbol' curr_symbol
	    FROM
		document_list
	    WHERE
		doc_id=$doc_id";
	return $this->get_row($sql);
    }
}
