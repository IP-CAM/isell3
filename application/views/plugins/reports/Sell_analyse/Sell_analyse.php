<?php
class Sell_analyse extends Catalog{
    private $idate;
    private $fdate;
    private $all_active;
    public function __construct() {
	$this->idate=$this->dmy2iso( $this->request('idate','\d\d.\d\d.\d\d\d\d') );
	$this->fdate=$this->dmy2iso( $this->request('fdate','\d\d.\d\d.\d\d\d\d') );
	$this->all_active=$this->request('all_active','bool');	
	parent::__construct();
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