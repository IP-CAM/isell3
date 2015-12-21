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
	if( !in_array($this->group_by, ['parent_id','product_code','analyse_type','analyse_group','analyse_class','analyse_section']) ){
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
        $sell_buy_table="
            SELECT
                product_code,
                SUM( IF(doc_type=2,product_quantity,-product_quantity) ) leftover,
                SUM( IF(doc_type=2,invoice_price*product_quantity,0) )/SUM( IF(doc_type=2,product_quantity,0) ) buy_avg,
                SUM( IF(doc_type=1 AND cstamp>'$this->idate',invoice_price*product_quantity,0) ) sell_prod_sum
            FROM
                document_entries de
                    JOIN
                document_list dl USING(doc_id)
            WHERE
                (doc_type=1 OR doc_type=2) AND cstamp<'$this->fdate' AND is_commited=1 $active_filter
            GROUP BY product_code";
        $sql="
            SELECT 
                IF('$this->group_by'='parent_id',(SELECT label FROM stock_tree WHERE branch_id=se.parent_id),$this->group_by) group_by,
                SUM(sell_prod_sum) sell_sum,
                SUM(buy_avg*leftover) stock_sum
            FROM
                stock_entries se
                    JOIN
                prod_list pl USING(product_code)
                    LEFT JOIN
                ($sell_buy_table) sellbuy USING(product_code)
            GROUP BY
		$this->group_by
            HAVING $this->group_by LIKE '%$this->group_by_filter%'";
        $rows=$this->get_list($sql);
        $total_sell=0;
        $total_stock=0;
        foreach( $rows as $row ){
            $total_sell+=$row->sell_sum;
            $total_stock+=$row->stock_sum;
        }
        foreach( $rows as $row ){
            $row->sell_proc=    round( $row->sell_sum/$total_sell, 4);
            $row->stock_proc=   round( $row->stock_sum/$total_stock, 4);
        }
	$view=[
                'total_sell'=>round($total_sell,2),
                'total_stock'=>round($total_stock,2),
		'rows'=>$rows
		];
	return $view;	
    }
}