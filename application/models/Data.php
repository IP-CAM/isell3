<?php

require_once 'Catalog.php';
class Data extends Catalog {
    private $permitted_tables=["prod_list","price_list"];
    public function import( $table_name ){
	if( !in_array($table_name, $this->permitted_tables) ){
	    return false;
	}
	$source = array_map('addslashes',$this->request('source','raw'));
	$target = array_map('addslashes',$this->request('target','raw'));
	$source_fields=  implode(',', $source);
	$target_fields=  implode(',', $target);
	
	$i=0;
	$update_set=[];
	foreach( $target as $tfield ){
	    if( $tfield=='product_code' ){
		$i++;
		continue;
	    }
	    $update_set[]="$tfield={$source[$i]}";
	    $i++;
	}
	
	$sql="INSERT INTO $table_name ($target_fields) SELECT $source_fields FROM imported_data ON DUPLICATE KEY UPDATE ".implode(',',$update_set);
	$this->query($sql);
        return $this->db->affected_rows();
    }
}