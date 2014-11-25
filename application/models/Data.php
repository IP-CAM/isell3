<?php

class Data extends CI_Model{
    public function gridGet( $table_name, $select='*' ){
	$sql="SELECT $select FROM $table_name";
	return $this->db->query($sql)->result_array();
    }
    public function treeFetch( $table, $parent_id ){
	$branches=array();
	$res=$this->db->query("SELECT * FROM $table WHERE parent_id=$parent_id ORDER BY label");
	foreach($res->result() as $row){
	    $row->children=$this->treeFetch($table, $row->branch_id);
	    $branches[]=$row;
	}
	$res->free_result();
	return $branches;
    }
}