<?php
require_once 'Catalog.php';
class Stock extends Catalog {
    public function import(){
        $parent_id=$this->request('parent_id','');
        $label=$this->request('label');
        $pcode_col=$this->request('product_code');
        $sql="
            SELECT
                product_code
            FROM
                imported_data
                    JOIN
                prod_list ON product_code=$pcode_col
            WHERE
                product_code NOT IN (SELECT product_code FROM stock_entries)";
        return $this->get_list($sql);
    }
}
