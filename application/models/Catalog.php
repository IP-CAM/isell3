<?php

class Catalog extends CI_Model {
    ////////////////////////////////////////////////////
    // CORE LIST FUNCTIONS
    ////////////////////////////////////////////////////
    public function get($table, $id) {
	echo $company_id;
    }
    public function create($table,$data) {
	$msg=$this->db->insert($table, $data);
	return $this->db->insert_id();
    }
    private function update($table, $data, $key) {
	return $this->db->update($table, $data, $key);
    }
    public function delete($table, $id) {
	
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
    ///////////////////////////////////////////////////
    
    
    
    
    
    
    public function insertTreeBranch($table_name, $parent_id, $label, $is_leaf, $branch_data) {
	$parent = $this->Base->get_row("SELECT is_leaf,level,top_id FROM $table_name WHERE branch_id='$parent_id'");
	if ($parent['is_leaf'])
	    return -1;
	//$top_id=$this->getTopBranch()
	$this->Base->query("INSERT INTO $table_name SET top_id='{$parent['top_id']}', level='{$parent['level']}', parent_id='$parent_id', label='$label', is_leaf='$is_leaf', branch_data='$branch_data'");
	$new_branch_id = mysql_insert_id();
	if ($parent_id == 0) {
	    //New branch is root so top_id==branch_id;
	    $this->Base->query("UPDATE $table_name SET top_id=branch_id WHERE branch_id=$new_branch_id");
	} else {
	    //$this->Base->updateTreeBranchPath($table_name,$new_branch_id);
	}
	return $new_branch_id;
    }

    public function updateTreeBranch($table_name, $branch_id, $parent_id, $label, $is_leaf = NULL, $branch_data = NULL) {
	$parent = $this->Base->get_row("SELECT is_leaf,level,top_id FROM $table_name WHERE branch_id='$parent_id'");
	$branch = $this->Base->get_row("SELECT top_id FROM $table_name WHERE branch_id='$branch_id'");
	if (!$parent['is_leaf']) {
	    $top_id = $parent_id == 0 ? $branch_id : $parent['top_id'];
	    $set = '';
	    $set.=$is_leaf !== NULL ? ",is_leaf='$is_leaf'" : '';
	    $set.=$branch_data !== NULL ? ",branch_data='$branch_data'" : '';
	    $this->Base->query("UPDATE $table_name SET top_id='$top_id', parent_id='$parent_id',label='$label' $set WHERE branch_id='$branch_id'");
	    /*
	     * UPDATING top_id of nested branches if changed
	     */
	    if ($branch['top_id'] != $top_id) {
		$sub_parents_ids = $this->getSubBranchIds($table_name, $branch_id);
		$sub_parents_where = "branch_id='" . implode("' OR branch_id='", $sub_parents_ids) . "'";
		$this->Base->query("UPDATE $table_name SET top_id=$top_id WHERE $sub_parents_where");
	    }
	}
	//$this->Base->updateTreeBranchPath($table_name,$branch_id);
	return $this->Base->get_row("SELECT * FROM $table_name WHERE branch_id='$branch_id'");
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
