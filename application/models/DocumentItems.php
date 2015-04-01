<?php
require_once 'DocumentCore.php';
class DocumentItems extends DocumentCore{
    public function suggestFetch(){
	$q=$this->input->get('q');
	$clues=  explode(' ', $q);
	$company_lang = $this->Base->pcomp('language');
	$where=array();
	foreach ($clues as $clue) {
            if ($clue == ''){
                continue;
	    }
            $where[]="(product_code LIKE '%$clue%' OR $company_lang LIKE '%$clue%')";
        }
	if( $this->isServiceDoc() ){
	    $where[]='is_service=1';
	}
	$sql="
	    SELECT
		product_code,
		$company_lang label
	    FROM
		prod_list
	    WHERE
		".( implode(' AND ',$where) )."
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
    public function get(){
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