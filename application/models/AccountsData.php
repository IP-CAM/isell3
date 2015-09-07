<?php
require_once 'AccountsCore.php';
class AccountsData extends AccountsCore{
    public $min_level=2;
    public function transNameListFetch($selected_acc=null){
	$q=$this->input->get('q');
	$this->check($q);
	$this->check($selected_acc);
	$user_level = $this->Base->svar('user_level');
	$curr_id=$this->Base->acomp('curr_id');
	$sql="SELECT
		acc_debit_code,
		acc_credit_code,
		trans_name,
		CONCAT(acc_debit_code,'_',acc_credit_code) trans_type,
		CONCAT(trans_name,' ',acc_debit_code,'_',acc_credit_code) trans_type_name,
		IF(atd.curr_id<>'$curr_id' OR atc.curr_id<>'$curr_id',1,0) use_alt_currency,
		user_level
	    FROM 
		acc_trans_names 
		    JOIN
		acc_tree atd ON atd.acc_code=acc_debit_code
		    JOIN
		acc_tree atc ON atc.acc_code=acc_credit_code
	    WHERE 
		user_level<='$user_level' AND IF('$selected_acc',acc_debit_code='$selected_acc' OR acc_credit_code='$selected_acc',1)
	    HAVING trans_type_name LIKE '%$q%'
	    ORDER BY trans_name";
	return $this->get_list($sql);
    }
    public function transNameUpdate($trans_type,$field,$value){
	$this->Base->set_level(3);
	$this->check($trans_type,'[a-z0-9_]*');
	$this->check($field,'[a-z0-9_]*');
	$this->check($value);
	$type=  explode('_', $trans_type);
	$this->query("UPDATE acc_trans_names SET $field='$value' WHERE acc_debit_code='$type[0]' AND acc_credit_code='$type[1]'");
	return $this->db->affected_rows()>0;
    }
    public function transNameCreate($acc_debit_code,$acc_credit_code){
	$this->Base->set_level(3);
	$this->check($acc_debit_code);
	$this->check($acc_credit_code);
	return $this->query("INSERT INTO acc_trans_names SET acc_debit_code='$acc_debit_code', acc_credit_code='$acc_credit_code', trans_name='---', user_level=3");
    }
    public function transNameDelete($trans_type){
	$this->check($trans_type);
	$dc=  explode('_', $trans_type);
	$this->query("DELETE FROM acc_trans_names WHERE acc_debit_code='$dc[0]' AND acc_credit_code='$dc[1]'");
	return $this->db->affected_rows()>0;
    }
    public function accountTreeFetch( $parent_id = null ) {
	if( $parent_id == null ){
	    $parent_id=$this->input->get('id') or $parent_id=0;
	}
	$res = $this->query("SELECT *,CONCAT(acc_code,' ',label) text,branch_id id FROM acc_tree WHERE parent_id='$parent_id' ORDER BY acc_code");
	$branches = array();
	foreach ($res->result() as $row) {
	    $row->state = $row->is_leaf ? '' : 'closed';
	    $branches[] = $row;
	}
	$res->free_result();
	return $branches;
    }
    public function accountTreeUpdate($branch_id,$field,$value) {
	$this->Base->set_level(3);
	$this->check($branch_id,'int');
	$this->check($field);
	$this->check($value);
	return $this->treeUpdate('acc_tree', $branch_id, $field, $value);
    }
    public function balanceTreeDelete( $branch_id ){
	return $this->treeDelete('acc_tree',$branch_id);
    }
    public function accountFavoritesFetch(){
	$sql="SELECT 
		acc_code,
		label
	    FROM
		acc_tree
	    WHERE 
		is_favorite=1";
	return $this->get_list($sql);
    }
    public function accountFavoritesToggle( $acc_code, $is_favorite ){
	$this->check($acc_code);
	$this->check($is_favorite,'bool');
	return $this->update('acc_tree',['is_favorite'=>$is_favorite],['acc_code'=>$acc_code]);
    }
//    public function accountPropsGet( $acc_code ){
//	$this->check($acc_code);
//	$sql="SELECT 
//		*
//	    FROM
//		acc_tree
//	    WHERE 
//		acc_code='$acc_code'";
//	return $this->get_row($sql);	
//    }
}