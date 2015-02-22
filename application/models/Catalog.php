<?php

class Catalog extends CI_Model {
    ////////////////////////////////////////////////////
    // CORE LIST FUNCTIONS
    ////////////////////////////////////////////////////
    protected function get_list( $query ){
	$list=array();
	if(is_string($query)){
	    $query=$this->db->query($query);
	}
	if( !$query || $query->num_rows()==0 ){
	    $this->db->_error_number()?$this->Base->db_msg():'';
	    return NULL;
	}
	foreach( $query->result() as $row ){
	    $list[]=$row;
	}
	$query->free_result();
	return $list;
    }
    protected function get_row( $query ){
	if(is_string($query)){
	    $query=$this->db->query($query);
	}
	if( !$query || $query->num_rows()==0 ){
	    $this->db->_error_number()?$this->Base->db_msg():'';
	    return NULL;
	}
	$row=$query->row();
	$query->free_result();
	return $row;
    }
    
    protected function get( $table, $key ){
	return $this->db->get_where( $table, $key )->row();
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
    protected function treeFetch($table, $parent_id = 0, $depth = 'all') {
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
    // EASYUI DATAGRID FUNCTIONS
    ////////////////////////////////////////////////////    
    protected function decodeFilterRules(){
	$raw=$this->input->get('filterRules');
	$filter=json_decode($raw);
	if( !is_array($filter) || count($filter)===0 ){
	    return 1;
	}
	$having=array();
	foreach( $filter as $rule ){
	    $having[]="$rule->field LIKE '%$rule->value%'";
	}
	return implode(' AND ',$having);
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
}