<?php

class Catalog extends CI_Model {
    ////////////////////////////////////////////////////
    // CORE LIST FUNCTIONS
    ////////////////////////////////////////////////////
    private function get( $table_name, $key ){
	return $this->db->get_where( $table_name, $key )->row();
    }
    private function create($table,$data) {
	$this->db->insert($table, $data);
	$this->db->_error_number()?$this->Base->db_msg():'';
	return $this->db->insert_id();
    }
    private function update($table, $data, $key) {
	$ok=$this->db->update($table, $data, $key);
	$this->db->_error_number()?$this->Base->db_msg():'';
	return $ok;
    }
    private function delete($table, $key) {
	$ok=$this->db->delete($table, $key);
	$this->db->_error_number()?$this->Base->db_msg():'';
	return $ok;
    }
    ////////////////////////////////////////////////////
    // CORE TREE FUNCTIONS
    ////////////////////////////////////////////////////
    private function treeFetch($table, $parent_id = 0, $depth = 'all') {
	$branches = array();
	$res = $this->db->query("SELECT * FROM $table WHERE parent_id=$parent_id ORDER BY is_leaf,label");
	foreach ($res->result() as $row) {
	    //$this->treeUpdatePath($table, $row->branch_id);
	    if ($depth == 'top') {
		$row->state = $row->is_leaf ? '' : 'closed';
	    } else {
		$row->children = $this->treeFetch($table, $row->branch_id);
	    }
	    $branches[] = $row;
	}
	$res->free_result();
	return $branches;
    }
    public function treeCreate($table,$type,$parent_id,$label=''){
	if( $this->treeisLeaf($table,$parent_id) || !$label ){
	    return false;
	}
	$branch_id=$this->create($table,array(
	    'parent_id'=>$parent_id,
	    'is_leaf'=>($type=='leaf'),
	    'path'=>'/-newbranch-/'
	    ));
	$this->treeUpdate($table, $branch_id, 'label', $label);
	return $branch_id;
    }
    public function treeUpdate($table,$branch_id,$field,$value) {
	$value=  rawurldecode($value);
	if( $field=='parent_id' && $this->treeisLeaf($table,$value) || $field=='label' && !$value ){
	    /*parent must be not leaf and label should not be empty*/
	    return false;
	}
	$this->update($table, array($field => $value),array('branch_id' => $branch_id));
	$this->treeUpdatePath($table, $branch_id);
	return true;
    }
    private function treeUpdatePath($table, $branch_id) {
	$this->db->query(
		"SELECT @old_path:=COALESCE(t1.path, ''),@new_path:=CONCAT(COALESCE(t2.path, '/'), t1.label, '/')
		FROM $table t1
			LEFT JOIN
		    $table t2 ON t1.parent_id = t2.branch_id 
		WHERE
		    t1.branch_id = $branch_id");
	$this->db->query(
		"UPDATE $table 
		SET 
		    path = IF(@old_path,REPLACE(path, @old_path, @new_path),@new_path)
		WHERE
		    IF(@old_path,path LIKE CONCAT(@old_path, '%'),branch_id=$branch_id)");
    }
    public function treeDelete($table,$branch_id){
	$branch = $this->db->get_where($table, array('branch_id'=>$branch_id))->row();
	if( $branch && $branch->path ){
	    $this->db->query("START TRANSACTION");
	    $this->db->query("DELETE FROM $table WHERE path LIKE '{$branch->path}%'");
	    $this->db->_error_number()?$this->Base->db_msg():'';
	    $deleted=$this->db->affected_rows();
	    $this->db->query("COMMIT");
	    return $deleted;
	}
	$this->Base->msg("iSell: Such branch is not found or path is not set!");
	return false;
    }
   private function treeisLeaf($table,$branch_id){
	$row = $this->db->get_where($table, array('branch_id' => $branch_id))->row();
	if ( $row && $row->is_leaf) {
	    return true;
	}
	return false;
    }
    ////////////////////////////////////////////////////
    // CORE TABLE ROW FUNCTIONS
    ////////////////////////////////////////////////////    
    public function rowGet( $table, $key_field, $id ){
	$key=array($key_field=>$id);
	return $this->get( $table, $key );
    }
    public function rowCreate( $table, $field, $value ){
	$data=array($field=>$value);
	return $this->create( $table, $data );
    }
    public function rowDelete( $table, $key_field, $id ){
	$key=array($key_field=>$id);
	$this->delete($table, $key);
    }
    public function rowUpdateField( $table, $key_field, $id, $field, $value ){
	$key=array($key_field=>$id);
	$data=array($field=>$value);
	return $this->update($table,$data,$key);
    }
    public function rowCreateSet( $table ){
	$json=$this->input->post('row_data');
	$data=  json_decode($json);
	return $this->create( $table, $data );
    }
    public function rowUpdateSet( $table, $key_field, $id ){
	$key=array($key_field=>$id);
	$json=$this->input->post('row_data');
	$data=  json_decode($json);
	return $this->update($table,$data,$key);
    }
    
    
    
    
    
    
    ////////////////////////////////////////////////////
    // ACCOUNTS SPECIFIC FUNCTIONS
    ////////////////////////////////////////////////////
    public function accountBalanceTreeFetch( $parent_id=0, $idate='', $fdate='', $show_unused=0 ){
	$this->Base->set_level(3);
	$this->db->query("SET @idate='$idate 00:00:00', @fdate='$fdate 23:59:59', @parent_id='$parent_id';");
	$sql=
	"SELECT 
	    d.branch_id,
	    d.label,
	    d.acc_code,
	    d.acc_type,
	    d.curr_id,
	    (SELECT curr_symbol FROM curr_list WHERE curr_id=d.curr_id) curr_symbol,
	    d.is_favorite,
	    d.use_clientbank,
	    IF( is_leaf,'','closed') state,
	    is_leaf,
	    IF(d.acc_type='P',-1,1)*(COALESCE(open_d,0)-COALESCE(open_c,0)) open_bal,
	    period_d,
	    period_c,
	    IF(d.acc_type='P',-1,1)*(COALESCE(close_d,0)-COALESCE(close_c,0)) close_bal
	FROM
	    (SELECT 
		tree.*,
		ROUND(SUM(IF(dtrans.cstamp < @idate, dtrans.amount, 0)), 2) open_d,
		ROUND(SUM(IF(dtrans.cstamp > @idate AND dtrans.cstamp < @fdate,dtrans.amount,0)),2) period_d,
		ROUND(SUM(IF(dtrans.cstamp < @fdate, dtrans.amount, 0)), 2) close_d
	    FROM
		acc_tree tree
		    LEFT JOIN 
		acc_tree subtree ON subtree.path LIKE CONCAT(tree.path,'%')
		    LEFT JOIN
		acc_trans dtrans ON dtrans.acc_debit_code = subtree.acc_code
	    WHERE
		tree.parent_id=@parent_id
	    GROUP BY tree.branch_id) d
	JOIN
	    (SELECT 
		tree.branch_id,
		ROUND(SUM(IF(ctrans.cstamp < @idate, ctrans.amount, 0)), 2) open_c,
		ROUND(SUM(IF(ctrans.cstamp > @idate AND ctrans.cstamp < @fdate,ctrans.amount,0)),2) period_c,
		ROUND(SUM(IF(ctrans.cstamp < @fdate, ctrans.amount, 0)), 2) close_c
	    FROM
		acc_tree tree
		    LEFT JOIN 
		acc_tree subtree ON subtree.path LIKE CONCAT(tree.path,'%')
		    LEFT JOIN
		acc_trans ctrans ON ctrans.acc_credit_code = subtree.acc_code
	    WHERE
		tree.parent_id=@parent_id
	    GROUP BY tree.branch_id) c 
	ON (d.branch_id=c.branch_id) 
	HAVING IF( $show_unused, 1, open_bal OR  period_d OR period_c OR close_bal )
	ORDER BY acc_code";
	return $this->Base->get_list($sql);
    }
    public function accountBalanceTreeCreate( $parent_id, $label ){
	$this->treeUpdate('acc_tree',$parent_id,'is_leaf',0);
	$new_code=  $this->accountCodeAssign( $parent_id );
	$branch_id= $this->treeCreate('acc_tree','leaf',$parent_id,$label);
	$ok=$this->update('acc_tree',array('acc_code'=>$new_code),array('branch_id'=>$branch_id));
	if( $ok ){
	    return "$branch_id,$new_code";
	}
	return "$branch_id,";
    }
    private function accountCodeAssign( $parent_id ){
	$row=$this->db->query("SELECT MAX(acc_code)+1 acc_code FROM acc_tree WHERE parent_id=$parent_id")->row();
	if( !$row->acc_code ){
	    $row=$this->db->query("SELECT CONCAT(acc_code,'1') acc_code FROM acc_tree WHERE branch_id=$parent_id")->row();
	}
	return $row->acc_code;
    }
    
    
    
    
    ////////////////////////////////////////////////////
    // COMPANY SPECIFIC FUNCTIONS
    ////////////////////////////////////////////////////
    public function companyTreeFetch() {
	$parent_id = $this->input->post('id') or $parent_id = 0;
	$table = "companies_tree LEFT JOIN companies_list USING(branch_id)";
	return $this->treeFetch($table, $parent_id, 'top');
    }
    public function companyCreate($parent_id){
    }
    public function companyUpdate($company_id, $field, $value) {
	$key = array(
	    'company_id' => $company_id
	);
	$data = array(
	    $field => $value
	);
	return $this->update("companies_tree LEFT JOIN companies_list USING(branch_id)", $key, $data);
    }
    public function companyDelete($company_id){
	$company_id=(int) $company_id;
	$row = $this->db->query("SELECT branch_id FROM companies_list WHERE company_id='$company_id'")->row();
	if( $row && $row->branch_id ){
	    return $this->treeDelete('companies_tree', $row->branch_id);
	}
	return false;
    }
}

class CatalogUtils {

//    public function CatalogUtils($db) {
//	$this->db = $db;
//    }
//
//    public function struct($table) {
//
//	function calc_props($type) {
//	    if (strstr($type, 'tinyint')) {
//		return array(
//		    'width' => 20,
//		    'cellalign' => 'center',
//		    'bool' => 1
//		);
//	    }
//	    if (strstr($type, 'int') || strstr($type, 'double')) {
//		return array(
//		    'width' => 50,
//		    'cellalign' => 'right'
//		);
//	    }
//	    if (strstr($type, 'text')) {
//		return array(
//		    'width' => 200
//		);
//	    }
//	    return array(
//		'width' => 100
//	    );
//	}
//
//	$struct = array();
//	$res = $this->db->query("SHOW FULL COLUMNS FROM $table");
//	foreach ($res->result() as $col) {
//	    $props = (object) calc_props($col->Type);
//	    $props->datafield = $col->Field;
//	    $props->text = $col->Comment;
//	    $props->key = $col->Key;
//	    $struct['cols'][] = $props;
//	}
//	$res->free_result();
//	return $struct;
//    }

}
