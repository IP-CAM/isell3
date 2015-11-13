<?php
require_once 'Catalog.php';
class Stock extends Catalog {
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
		st.label parent_label,
		SUM(IF(TO_DAYS(NOW()) - TO_DAYS(dl.cstamp) <= 90,de.product_quantity,0)) m3,
		SUM(IF(TO_DAYS(NOW()) - TO_DAYS(dl.cstamp) <= 30,de.product_quantity,0)) m1,
		pl.*,
		pp.sell,
		pp.buy,
		pp.curr_code,
		se.stock_entry_id,
		se.parent_id,
		se.party_label,
		se.product_quantity,
		se.product_wrn_quantity,
		se.self_price
	    FROM
		stock_entries se
		    JOIN
		prod_list pl USING(product_code)
		    LEFT JOIN
		price_list pp USING(product_code)
		    LEFT JOIN
		stock_tree st ON se.parent_id=branch_id
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
	$this->Base->set_level(2);
	$product_code=$this->request('product_code');
        $product_code_new=$this->request('product_code_new','^[\p{L}\d\. ,-]+$');
        if( !$product_code_new ){
            return false;
        }
	$product=[
	    'prod_list.product_code'=>$product_code_new,
	    'ru'=>$this->request('ru'),
	    'ua'=>$this->request('ua'),
	    'en'=>$this->request('en'),
	    'product_unit'=>$this->request('product_unit'),
	    'product_spack'=>$this->request('product_spack','int'),
	    'product_bpack'=>$this->request('product_bpack','int'),
	    'product_weight'=>$this->request('product_weight','double'),
	    'product_volume'=>$this->request('product_volume','double'),
	    'product_uktzet'=>$this->request('product_uktzet'),
	    'barcode'=>$this->request('barcode'),
	    'parent_id'=>$this->request('parent_id','int'),
	    'product_wrn_quantity'=>$this->request('product_wrn_quantity','int'),
	    'party_label'=>$this->request('party_label'),
	    'buy'=>$this->request('buy','double'),
	    'sell'=>$this->request('sell','double'),
	    'curr_code'=>$this->request('curr_code'),
	];
	if( !$product_code ){//NEW RECORD
	    $this->create(BAY_DB_MAIN.'.prod_list', ['product_code'=>$product_code_new]);
	    $this->create(BAY_DB_MAIN.'.price_list', ['product_code'=>$product_code_new]);
	    $this->create(BAY_DB_MAIN.'.stock_entries', ['product_code'=>$product_code_new]);
	}
	return $this->update(BAY_DB_MAIN.'.stock_entries JOIN '.BAY_DB_MAIN.'.prod_list USING(product_code) LEFT JOIN '.BAY_DB_MAIN.'.price_list USING(product_code)', $product, ['product_code'=>$product_code]);
    }
    public function productDelete(){
	$product_code=$this->request('product_code');
	return $this->delete(BAY_DB_MAIN.'.stock_entries', ['product_code'=>$product_code,'product_quantity'=>0]);
    }
    public function movementsFetch( $page=1, $rows=30, $having=null ){
	$this->check($page,'int');
	$this->check($rows,'int');
	$offset=($page-1)*$rows;
	if( $offset<0 ){
	    $offset=0;
	}
	if( !$having ){
	    $having=$this->decodeFilterRules();
	}
        $sql="SELECT
                DATE_FORMAT(dl.cstamp,'%d.%m.%Y') oper_date,
                CONCAT(dt.doc_type_name,IF(dl.is_reclamation,' (Возврат)',''),' #',dl.doc_num) doc,
                label,
                product_code,
                ru,
                IF(doc_type=1,product_quantity,'') sell,
                IF(doc_type=2,product_quantity,'') buy
            FROM
                document_entries de
                    JOIN
                document_list dl USING(doc_id)
                    JOIN
                document_types dt USING(doc_type)
                    JOIN
                prod_list USING(product_code)
                    JOIN
                companies_list ON passive_company_id=company_id
                    LEFT JOIN
                companies_tree USING(branch_id)
            WHERE
                is_commited
            HAVING $having
            ORDER BY dl.cstamp DESC
            LIMIT $rows OFFSET $offset";
	$result_rows=$this->get_list($sql);
	$this->distinctMovementsRows($result_rows);
	$total_estimate=$offset+(count($result_rows)==$rows?$rows+1:count($result_rows));
	return array('rows'=>$result_rows,'total'=>$total_estimate);
    }
    private function distinctMovementsRows( &$result_rows ){
	$prev_concat='';
	foreach( $result_rows as $row ){
	    $concat=$row->oper_date.$row->doc.$row->label;
	    if( $prev_concat==$concat ){
		$row->oper_date='';
		$row->doc='';
		$row->label='';
	    }
	    $prev_concat=$concat;
	}
    }
    public function import_(){
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
    public function import(){
	$label=$this->request('label');
	$source = array_map('addslashes',$this->request('source','raw'));
	$target = array_map('addslashes',$this->request('target','raw'));
	
        $source[]=$this->request('parent_id','int');
        $target[]='parent_id';
	
	$this->importInTable('prod_list', $source, $target, '/product_code/ru/ua/en/product_spack/product_bpack/product_weight/product_volume/product_unit/product_uktzet/barcode/', $label);
	$this->importInTable('price_list', $source, $target, '/product_code/sell/buy/curr_code/', $label);
	$this->importInTable('stock_entries', $source, $target, '/product_code/parent_id/', $label);
	$this->query("DELETE FROM imported_data WHERE {$source[0]} IN (SELECT product_code FROM stock_entries)");
        return  $this->db->affected_rows();
    }
    private function importInTable( $table, $src, $trg, $filter, $label ){
	$set=[];
	$target=[];
	$source=[];
	for( $i=0;$i<count($trg);$i++ ){
            if( strpos($filter,"/{$trg[$i]}/")!==false && !empty($src[$i]) ){
		$target[]=$trg[$i];
		$source[]=$src[$i];
		$set[]="{$trg[$i]}=$src[$i]";
	    }
	}
	$target_list=  implode(',', $target);
	$source_list=  implode(',', $source);
	$set_list=  implode(',', $set);
	$this->query("INSERT INTO $table ($target_list) SELECT $source_list FROM imported_data WHERE label='$label' ON DUPLICATE KEY UPDATE $set_list");
	//print("INSERT INTO $table ($target_list) SELECT $source_list FROM imported_data ON DUPLICATE KEY UPDATE $set_list");
	return $this->db->affected_rows();
    }
}
