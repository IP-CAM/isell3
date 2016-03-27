<?php

require_once 'Catalog.php';
class Data extends Catalog {
    function __construct(){
	$this->permited_tables = json_decode(file_get_contents('application/config/permited_tables.json', true));
    }
    
    public function import( $table_name ){
	if( !$this->checkTable($table_name) ){
	    return false;
	}
	$source = array_map('addslashes',$this->request('source','raw'));
	$target = array_map('addslashes',$this->request('target','raw'));
	$source_fields=  implode(',', $source);
	$target_fields=  implode(',', $target);
	
        $product_code_source='';
	$i=0;
	$update_set=[];
	foreach( $target as $tfield ){
	    if( $tfield=='product_code' ){
                $product_code_source=$source[$i];
		$i++;
		continue;
	    }
	    $update_set[]="$tfield={$source[$i]}";
	    $i++;
	}
	
	$sql="INSERT INTO $table_name ($target_fields) SELECT $source_fields FROM imported_data ".(($table_name=='price_list')?"WHERE $product_code_source IN (SELECT product_code FROM prod_list)":"")." ON DUPLICATE KEY UPDATE ".implode(',',$update_set)
            ;
	$this->query($sql);
        return $this->db->affected_rows();
    }
    private function checkTable($table_name) {
	foreach ($this->permited_tables as $table) {
	    if ( isset($table->level) && $this->Base->svar('user_level') < $table->level){
		continue;
            }
	    if ($table_name == $table->table_name){
		return true;
            }
	}
	return false;
    }

    public function permitedTableList() {
	$table_list = [];
	foreach ($this->permited_tables as $table) {
	    if (isset($table->level) && $this->Base->svar('user_level') < $table->level || isset($table->hidden) && $table->hidden){
		continue;
            }
	    $table_list[] = $table;
	}
	return $table_list;
    }
    
    public function tableStructure($table_name){
	if( !$this->checkTable($table_name) ){
	    return false;
	}
	return $this->get_list("SHOW FULL COLUMNS FROM $table_name");
    }
    public function tableData($table_name){
	if( !$this->checkTable($table_name) ){
	    return false;
	}
	$page=$this->request('page','int');
	$rows=$this->request('rows','int');
	$having=$this->decodeFilterRules();
	$offset=($page-1)*$rows;
	if( $offset<0 ){
	    $offset=0;
	}
	return [
		    'rows'=>$this->get_list("SELECT * FROM $table_name WHERE $having LIMIT $rows OFFSET $offset"),
		    'total'=>$this->get_value("SELECT COUNT(*) FROM $table_name WHERE $having")
		];
    }
    public function tableRowsDelete($table_name){
	$this->Base->set_level(3);
	if( !$this->checkTable($table_name) ){
	    return false;
	}
	$key=$this->request('key');
	$values=$this->request('values','raw');
	return $this->delete($table_name,$key,$values);
    }
    public function tableRowUpdate($table_name){
	$this->Base->set_level(3);
	if( !$this->checkTable($table_name) ){
	    return false;
	}
	$key=$this->request('key');
	$key_val=$this->request('key_val');
	$inp=$this->request('inp');
	$inp_val=$this->request('inp_val');
	if( $key===$inp ){
	    /*
	     * On new record key == inp
	     */
	    $this->query("INSERT INTO $table_name SET $inp='$inp_val'");
	} else {
	    $this->query("INSERT INTO $table_name SET $key='$key_val', $inp='$inp_val' ON DUPLICATE KEY UPDATE $inp='$inp_val'");
	}
	return $this->db->affected_rows();
    }
}