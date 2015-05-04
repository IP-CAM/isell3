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
		$company_lang label,
		product_spack,
		product_quantity
	    FROM
		prod_list
		    JOIN
		stock_entries USING(product_code)
	    WHERE
		".( implode(' AND ',$where) )."
		    ORDER BY fetch_count DESC, product_code
	    LIMIT 15
	    ";
	return $this->get_list($sql);
    }
    private function footerGet(){
	$doc_id=$this->doc('doc_id');
	//$curr_symbol=$this->Base->pcomp('curr_symbol');
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
                REPLACE(FORMAT(invoice_price * @vat_correction * @curr_correction,signs_after_dot),',',' ') AS product_price,
                REPLACE(FORMAT(invoice_price * @vat_correction * @curr_correction * product_quantity,2),',',' ') AS product_sum,
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
    public function entryAdd( $code, $quantity ){
	$Document2=$this->Base->bridgeLoad('Document');
	return $Document2->addEntry( $code, $quantity );
    }
    public function entryUpdate( $doc_entry_id, $name, $value ){
	$Document2=$this->Base->bridgeLoad('Document');
	switch( $name ){
	    case 'product_quantity':
		return $Document2->updateEntry($doc_entry_id, $value, NULL);
	    case 'product_price':
		return $Document2->updateEntry($doc_entry_id, NULL, $value);
	}
    }
    public function entryDelete( $ids ){
	$ids_arr=  json_decode('[['.str_replace(',', '],[', rawurldecode($ids)).']]');
	$Document2=$this->Base->bridgeLoad('Document');
	return $Document2->deleteEntry($ids_arr);
    }
    public function entryStatsGet( $product_code ){
	$curr=$this->get_row("SELECT curr_symbol FROM curr_list WHERE curr_code='".$this->Base->pcomp('curr_code')."'");
	$sql="SELECT 
	    product_quantity
	FROM 
	    stock_entries
	WHERE
	    product_code='$product_code'";
	$stats=$this->get_row($sql);
	$stats->curr_symbol=$curr->curr_symbol;
	$stats->price=$this->entryPriceGet($product_code);
	return $stats;
    }
    private function entryPriceGet( $product_code ){
	$Document2=$this->Base->bridgeLoad('Document');
	$invoice=$Document2->getProductInvoicePrice($product_code);
	$invoice=round($invoice,$this->doc('signs_after_dot'));
	if( !$this->doc('use_vatless_price') ){
	    $invoice*=1+$this->doc('vat_rate')/100;
	}
	return round($invoice,$this->doc('signs_after_dot'));
    }
    public function entryDocumentGet(){
	$document=array();
	$document['entries']=$this->entriesFetch();
	$document['footer']=$this->footerGet();
	return $document;
    }
    public function entryDocumentCommit(){
	$Document2=$this->Base->bridgeLoad('Document');
	return $Document2->commit();
    }
    public function entryDocumentUncommit(){
	$Document2=$this->Base->bridgeLoad('Document');
	return $Document2->uncommit();
    }
    public function recalc( $proc=0 ){
	$Document2=$this->Base->bridgeLoad('Document');
	$Document2->recalc($proc);
    }
    private function calcCorrections() {
	$doc_id=$this->doc('doc_id');
	$curr_code=$this->Base->pcomp('curr_code');
	$native_curr=($this->Base->pcomp('curr_code') == $this->Base->acomp('curr_code'))?1:0;
	$sql="SELECT 
		@vat_ratio:=1+vat_rate/100 vat_ratio,
		@vat_correction:=IF(use_vatless_price,1,@vat_ratio) vat_correction,
		@curr_correction:=IF($native_curr,1,1/doc_ratio) curr_correction,
		@curr_symbol:=(SELECT curr_symbol FROM curr_list WHERE curr_code='$curr_code') curr_symbol
	    FROM
		document_list
	    WHERE
		doc_id=$doc_id";
	return $this->get_row($sql);
    }
}