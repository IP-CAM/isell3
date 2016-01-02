<?php
require_once 'DocumentUtils.php';
class DocumentTrans extends DocumentUtils{
    private $transDocConfig=[
	1=>[
	    'name'	=>"Расходный документ",
	    'total'	=>'361_702',
	    'vat'	=>'702_641',
	    'vatless'	=>'702_791',
	    'self'	=>'791_281',
	    'profit'	=>'791_441'
	],
	2=>[
	    'name'	=>"Приходный документ",
	    'total'	=>'63_631',
	    'vat'	=>'641_63',
	    'vatless'	=>'',
	    'self'	=>'281_63',
	    'profit'	=>''
	]
    ];
    private function transDocResultGet(){
	$doc_id=$this->doc('doc_id');
	$doc_vat_rate=$this->doc('vat_rate');
	$sql="
	    SELECT
		total,
		vatless,
		self,
		total-vatless vat,
		vatless-self profit
	    FROM
		(SELECT
		    SUM(ROUND(product_quantity*invoice_price*(1+$doc_vat_rate/100),2)) total,
		    SUM(ROUND(product_quantity*invoice_price,2)) vatless,
		    SUM(ROUND(product_quantity*self_price,2)) self
		FROM
		    document_entries
		WHERE doc_id='$doc_id') t";
	return $this->get_row($sql);
    }
    public function transDocUpdate( $doc_id ){
	$this->selectDoc($doc_id);
    }
    
    
        protected function footerGet111111111111(){
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
}
