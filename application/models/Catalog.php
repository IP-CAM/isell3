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
	return $this->db->insert_id();
    }
    private function update($table, $data, $key) {
	return $this->db->update($table, $data, $key);
    }
    private function delete($table, $key) {
	return $this->db->delete($table, $key);
    }
    ////////////////////////////////////////////////////
    // CORE TREE FUNCTIONS
    ////////////////////////////////////////////////////
    private function treeFetch($table, $parent_id = 0, $depth = 'all') {
	$branches = array();
	$res = $this->db->query("SELECT * FROM $table WHERE parent_id=$parent_id ORDER BY is_leaf,label");
	foreach ($res->result() as $row) {
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
		    path = REPLACE(path, @old_path, @new_path)
		WHERE
		    path LIKE CONCAT(@old_path, '%')");
    }
    public function treeDelete($table,$branch_id){
	$branch = $this->db->get_where($table, array('branch_id'=>$branch_id))->row();
	if( $branch && $branch->path ){
	    $this->db->query("START TRANSACTION");
	    $this->db->query("DELETE FROM $table WHERE path LIKE '{$branch->path}%'");
	    $deleted=$this->db->affected_rows();
	    $this->db->query("COMMIT");
	    return $deleted;
	}
	msg("iSell: Such branch is not found or path is not set!");
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

    public function CatalogUtils($db) {
	$this->db = $db;
    }

    public function struct($table) {

	function calc_props($type) {
	    if (strstr($type, 'tinyint')) {
		return array(
		    'width' => 20,
		    'cellalign' => 'center',
		    'bool' => 1
		);
	    }
	    if (strstr($type, 'int') || strstr($type, 'double')) {
		return array(
		    'width' => 50,
		    'cellalign' => 'right'
		);
	    }
	    if (strstr($type, 'text')) {
		return array(
		    'width' => 200
		);
	    }
	    return array(
		'width' => 100
	    );
	}

	$struct = array();
	$res = $this->db->query("SHOW FULL COLUMNS FROM $table");
	foreach ($res->result() as $col) {
	    $props = (object) calc_props($col->Type);
	    $props->datafield = $col->Field;
	    $props->text = $col->Comment;
	    $props->key = $col->Key;
	    $struct['cols'][] = $props;
	}
	$res->free_result();
	return $struct;
    }

}
