<?php
class Sell_analyse extends Catalog{
    private $idate;
    private $fdate;
    private $all_active;
    public function formSubmit( $fvalue ){
	$this->check($fvalue['idate'],'\d\d.\d\d.\d\d\d\d');
	$this->check($fvalue['fdate'],'\d\d.\d\d.\d\d\d\d');
	$this->check($fvalue['all_active'],'bool');
	$this->idate=$this->dmy2iso($fvalue['idate']);
	$this->fdate=$this->dmy2iso($fvalue['fdate']);
	$this->all_active=$fvalue['all_active'];
    }
    private function dmy2iso( $dmy ){
	$chunks=  explode('.', $dmy);
	return "$chunks[2]-$chunks[1]-$chunks[0]";
    }
    public function viewGet(){
	$active_filter=$this->all_active?'':' AND active_company_id='.$this->Base->acomp('company_id');
	$sql="SELECT 
		*
	    FROM
		document_list
		    JOIN
		document_entries USING(doc_id)
		    JOIN
		prod_list USING(product_code)
	    WHERE 
		cstamp>'$this->idate' AND cstamp<'$this->fdate'
		AND doc_type=1 AND is_commited=1 $active_filter";
	
	$view=[
		'rows'=>$this->get_list($sql)
		];
	return $view;	
    }
}