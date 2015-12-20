<?php
class Sell_analyse extends Catalog{
    private $idate;
    private $fdate;
    private $all_active;
    public function __construct() {
	$this->idate=$this->dmy2iso( $this->request('idate','\d\d.\d\d.\d\d\d\d') );
	$this->fdate=$this->dmy2iso( $this->request('fdate','\d\d.\d\d.\d\d\d\d') );
	$this->all_active=$this->request('all_active','bool');
	$this->group_by_filter=$this->request('group_by_filter');
	$this->group_by=$this->request('group_by','\w+');
	if( !in_array($this->group_by, ['parent_id','analyse_type','analyse_group','analyse_class','analyse_section']) ){
	    $this->group_by='parent_id';
	}
	parent::__construct();
    }
    private function dmy2iso( $dmy ){
	$chunks=  explode('.', $dmy);
	return "$chunks[2]-$chunks[1]-$chunks[0]";
    }
    public function viewGet(){
	$active_filter=$this->all_active?'':' AND active_company_id='.$this->Base->acomp('company_id');
	$sql="SELECT 
		(SELECT label FROM stock_tree WHERE branch_id=se.parent_id) cathegory,
		IF($this->group_by='parent_id','',$this->group_by) group_by,
		AVG( product_sell/product_quantity ) avg
	    FROM
		stock_entries se
		    JOIN
		prod_list USING(product_code)
		    JOIN
		(SELECT 
		    product_code,SUM(product_quantity) product_sell 
		 FROM 
		    document_list dl JOIN document_entries de USING(doc_id)
		 WHERE 
		    cstamp>'$this->idate' AND cstamp<'$this->fdate'
		    AND doc_type=1 AND is_commited=1 $active_filter
		 GROUP BY product_code) entries USING(product_code)
	    GROUP BY
		$this->group_by
	    HAVING $this->group_by LIKE '%$this->group_by_filter%'";
	$view=[
		'rows'=>$this->get_list($sql)
		];
	return $view;	
    }
}