<?php
require_once 'Catalog.php';
class Stock extends Catalog {
    public function import(){
        $parent_id=$this->request('parent_id','int');
        $pcode_col=$this->request('product_code');
        $label=$this->request('label');
	$where=$label?"AND label='$label'":'';
        $sql="
	    INSERT INTO ".BAY_DB_MAIN.".stock_entries(product_code,parent_id)
            SELECT
                product_code,
		'$parent_id'
            FROM
                imported_data
                    JOIN
                prod_list ON product_code=$pcode_col
            WHERE
                product_code NOT IN (SELECT product_code FROM ".BAY_DB_MAIN.".stock_entries)
		$where";
	$this->query($sql);
        return $this->db->affected_rows();
    }
    public function branchFetch() {
	$parent_id=$this->request('id','int',0);
	return $this->treeFetch("stock_tree", $parent_id, 'top');
    }
    public function stockTreeCreate($parent_id,$label){
	$this->Base->set_level(2);
	$this->check($parent_id,'int');
	$this->check($label);
	return $this->treeCreate('stock_tree', 'folder', $parent_id, $label);
    }
    public function stockTreeUpdate($branch_id,$field,$value) {
	$this->Base->set_level(2);
	$this->check($branch_id,'int');
	$this->check($field);
	$this->check($value);
	return $this->treeUpdate('stock_tree', $branch_id, $field, $value);
    }
    public function stockTreeDelete( $branch_id ){
	$this->Base->set_level(4);
	$this->check($branch_id,'int');
	$sub_ids=$this->treeGetSub('stock_tree', $branch_id);
	$in=implode(',', $sub_ids);
	$this->query("DELETE FROM stock_tree WHERE branch_id IN ($in)");
	$deleted=$this->db->affected_rows();
	return $deleted;
    }
    public function listFetch( $page=1, $rows=30, $parent_id=0, $having=null ){
	$this->check($page,'int');
	$this->check($rows,'int');
	$this->check($parent_id,'int');
	$offset=($page-1)*$rows;
	if( $offset<0 ){
	    $offset=0;
	}
	if( !$having ){
	    $having=$this->decodeFilterRules();
	}
	$where='';
	if( $parent_id ){
	    $branch_ids=$this->treeGetSub('stock_tree',$parent_id);
	    $where="WHERE se.parent_id IN (".implode(',',$branch_ids).")";
	}
	$sql="SELECT
		label parent_label,
		SUM(IF(TO_DAYS(NOW()) - TO_DAYS(dl.cstamp) <= 90,de.product_quantity,0)) m3,
		SUM(IF(TO_DAYS(NOW()) - TO_DAYS(dl.cstamp) <= 30,de.product_quantity,0)) m1,
		pl.*,
		se.*
	    FROM
		stock_entries se
		    JOIN
		prod_list pl USING(product_code)
		    LEFT JOIN
		stock_tree ON se.parent_id=branch_id
		    LEFT JOIN
		document_entries de USING(product_code)
		    LEFT JOIN
		document_list dl ON de.doc_id=dl.doc_id AND dl.is_commited=1 AND dl.doc_type=1
	    $where
	    GROUP BY se.product_code
	    HAVING $having
	    LIMIT $rows OFFSET $offset";
	$result_rows=$this->get_list($sql);
	$total_estimate=$offset+(count($result_rows)==$rows?$rows+1:count($result_rows));
	return array('rows'=>$result_rows,'total'=>$total_estimate);
    }
    public function labelFetch(){
	$q=$this->request('q','string',0);
	return $this->get_list("SELECT branch_id,label FROM stock_tree WHERE label LIKE '%$q%'");
    }
    public function productSave(){
	$stock_entry_id=$this->request('stock_entry_id','int');
	$product=[
	    'product_code'=>$this->request('product_code','[\w\d,.-\s]+'),
	    'parent_id'=>$this->request('parent_id','int'),
	    'product_unit'=>$this->request('product_unit'),
	    'product_wrn_quantity'=>$this->request('product_wrn_quantity','int'),
	    'ru'=>$this->request('ru'),
	    'ua'=>$this->request('ua'),
	    'en'=>$this->request('en'),
	    'product_spack'=>$this->request('product_spack'),
	    'product_bpack'=>$this->request('product_bpack'),
	    'product_weight'=>$this->request('product_weight'),
	    'product_volume'=>$this->request('product_volume'),
	    'product_uktzet'=>$this->request('product_uktzet'),
	    'barcode'=>$this->request('barcode'),
	    'party_label'=>$this->request('party_label')
	];
	return $this->update('stock_entries JOIN prod_list USING(product_code)', $product, ['stock_entry_id'=>$stock_entry_id]);
    }
}
