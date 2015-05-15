<?php
/**
 * ACCOUNTS SPECIFIC FUNCTIONS
 * Accounting processing
 *
 * @author Baycik
 */
include 'Catalog.php';
class Accounts extends Catalog{
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
        $balance=$this->get_list($sql);
	return $balance?$balance:array();
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
}
