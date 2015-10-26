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
}
