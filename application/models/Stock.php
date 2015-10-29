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
    public function listFetch( $page=1, $rows=30, $parent_id=0 ){
	$this->check($page,'int');
	$this->check($rows,'int');
	$this->check($parent_id,'int');
	$offset=($page-1)*$rows;
	if( $offset<0 ){
	    $offset=0;
	}
	$where='';
	if( $parent_id ){
	    $where="WHERE se.parent_id='$parent_id'";
	}
	$sql="SELECT
		label parent_label,
		45 m3,
		56 m1,
		ru,
		se.*
	    FROM
		stock_entries se
		    JOIN
		prod_list USING(product_code)
		    LEFT JOIN
		stock_tree ON se.parent_id=branch_id
	    $where
	    LIMIT $rows OFFSET $offset";
	$result_rows=$this->get_list($sql);
	$total_estimate=$offset+(count($result_rows)==$rows?$rows+1:count($result_rows));
	return array('rows'=>$result_rows,'total'=>$total_estimate);
    }
    
}
