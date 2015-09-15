<?php
/**
 * ACCOUNTS SPECIFIC FUNCTIONS
 * Accounting processing
 *
 * @author Baycik
 */
include 'Catalog.php';
class AccountsCore extends Catalog{
    public $min_level=1;
    private function getAccountProperties( $acc_code ) {
        $sql="SELECT
            * 
            FROM acc_tree at
            JOIN curr_list cl ON IF(at.curr_id,cl.curr_id=at.curr_id,cl.curr_id=1)
            WHERE acc_code='$acc_code'";
        return $this->get_row($sql);
    }
    private function ledgerCreate( $acc_code, $use_alt_amount=false ){
	$this->check($acc_code);
	$this->check($use_alt_amount,'bool');
	
	$this->db->query("SET @acc_code:=?, @use_alt_amount=?;",[$acc_code,$use_alt_amount]);
	$this->db->query("DROP TEMPORARY TABLE IF EXISTS tmp_ledger;");
	$sql="CREATE TEMPORARY TABLE tmp_ledger ( INDEX(cstamp) ) ENGINE=MyISAM AS (
	    SELECT 
		trans_id,
		editable,
		nick,
		description,
		cstamp,
		passive_company_id,
		company_name,
		DATE_FORMAT(cstamp, '%d.%m.%Y') trans_date,
		CONCAT(code, ' ', descr) trans_status,
		CONCAT(acc_debit_code, ' ', acc_credit_code) trans_type,
		IF(@acc_code = acc_debit_code,
		    ROUND(IF(@use_alt_amount,amount_alt,amount),2),
		    0) debit,
		IF(@acc_code = acc_credit_code,
		    ROUND(IF(@use_alt_amount,amount_alt,amount),2),
		    0) credit
	    FROM
		acc_trans
		    JOIN
		companies_list ON company_id = passive_company_id
		    JOIN
		acc_trans_status USING (trans_status)
		    LEFT JOIN
		user_list ON user_id = modified_by
	    WHERE
		@acc_code = acc_debit_code
		    OR @acc_code = acc_credit_code)";
	$this->query($sql);
    }
    private function ledgerGetSubtotals( $idate, $fdate ){
	$sql="SELECT 
		    SUM(IF('$idate'>cstamp,debit-credit,0)) ibal,
		    SUM(IF('$idate'<cstamp AND cstamp<='$fdate',debit,0)) pdebit,
		    SUM(IF('$idate'<cstamp AND cstamp<='$fdate',credit,0)) pcredit,
		    SUM(IF(cstamp<='$fdate',debit-credit,0)) fbal
		FROM tmp_ledger";
	return $this->get_row($sql);
    }
    public function ledgerFetch( $acc_code, $idate='', $fdate='', $page=1, $rows=30 ){
	$this->Base->set_level(3);
	$this->check($idate,'\d\d\d\d-\d\d-\d\d');
	$this->check($fdate,'\d\d\d\d-\d\d-\d\d');
	$this->check($acc_code,'int');
	$this->check($page,'int');
	$this->check($rows,'int');
	$idate.=' 00:00:00';
	$fdate.=' 23:59:59';
	if( !$acc_code || !$idate || !$fdate ){
	    return [];
	}

	$having=$this->decodeFilterRules();
	$offset=$page>0?($page-1)*$rows:0;
	
	$props=$this->getAccountProperties( $acc_code );
	$default_curr_id=$this->Base->acomp('curr_id');
	$using_alt_currency=$default_curr_id!=$props->curr_id;
	$this->ledgerCreate($acc_code, $using_alt_currency );
	$sql="SELECT * FROM tmp_ledger 
		WHERE '$idate'<cstamp AND cstamp<='$fdate'
		HAVING $having
		ORDER BY cstamp DESC 
		LIMIT $rows OFFSET $offset";
	$result_rows=$this->get_list($sql);
	$total_estimate=$offset+(count($result_rows)==$rows?$rows+1:count($result_rows));
	
	$sub_totals=$this->ledgerGetSubtotals($idate, $fdate);
	return ['rows'=>$result_rows,'total'=>$total_estimate,'props'=>$props,'sub_totals'=>$sub_totals,'using_alt_currency'=>$using_alt_currency];
    }
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
	$this->Base->set_level(3);
	$this->treeUpdate('acc_tree',$parent_id,'is_leaf',0);
	$new_code=  $this->accountCodeAssign( $parent_id );
	$branch_id= $this->treeCreate('acc_tree','leaf',$parent_id,$label);
	$ok=$this->rowUpdate('acc_tree',array('acc_code'=>$new_code),array('branch_id'=>$branch_id));
	if( $ok ){
	    return "$branch_id,$new_code";
	}
	return "$branch_id,";
    }
    private function accountCodeAssign( $parent_id ){
	$acc_code=$this->get_value("SELECT MAX(acc_code)+1 acc_code FROM acc_tree WHERE parent_id=$parent_id");
	if( !$acc_code ){
	    $acc_code=$this->get_value("SELECT CONCAT(acc_code,'1') acc_code FROM acc_tree WHERE branch_id=$parent_id");
	}
	return $acc_code;
    }
    public function transFullGet( $trans_id ){
	$this->check($trans_id,'int');
	$curr_id=$this->Base->acomp('curr_id');
	$sql="SELECT
		trans.*,
		(SELECT IF(label,label,company_name) FROM companies_list LEFT JOIN companies_tree USING(branch_id) WHERE company_id=passive_company_id) label,
		CONCAT(acc_debit_code, '_', acc_credit_code) trans_type,
		trans_name,
		IF(atd.curr_id<>'$curr_id' OR atc.curr_id<>'$curr_id',1,0) use_alt_currency,
		atn.user_level,
		nick
	    FROM 
		acc_trans trans
		    LEFT JOIN
		acc_trans_names atn USING(acc_debit_code,acc_credit_code)
		    JOIN
		acc_tree atd ON atd.acc_code=acc_debit_code
		    JOIN
		acc_tree atc ON atc.acc_code=acc_credit_code
		    LEFT JOIN
		user_list ON user_id=modified_by
	    WHERE
		trans_id='$trans_id'";
	return $this->get_row($sql);
    }
    public function transGet( $trans_id ){
	$this->check($trans_id,'int');
	$sql="SELECT * FROM acc_trans trans WHERE trans_id='$trans_id'";
	return $this->get_row($sql);	
    }
    public function transGetDocId( $trans_id ){
	$this->check($trans_id,'int');
	$sql="SELECT doc_id FROM document_trans WHERE trans_id='$trans_id'";
	return $this->get_value($sql);	
    }
    public function transCheckLevel($trans_type){
	$user_level=$this->Base->svar('user_level');
	if( $user_level>=3 ){
	    return true;
	}
	$sql="SELECT 1 FROM acc_trans_names WHERE CONCAT(acc_debit_code,'_',acc_credit_code)='$trans_type' AND user_level<='$user_level'";
	return $this->get_value($sql);
    }
    public function transCreateUpdate( $trans_id, $passive_company_id, $trans_type, $trans_date=null, $amount=null, $amount_alt=null, $description=null ){
	$this->Base->set_level(2);
	$this->check($trans_id,'int');
	$this->check($trans_type);
	$this->check($trans_date,'\d\d\d\d-\d\d-\d\d');
	$this->check($amount,'double');
	$this->check($amount_alt,'double');
	$this->check($description);
	if( !$this->transCheckLevel($trans_type) ){
	    $this->Base->msg('access denied');
	    return false;
	}
	$user_id=$this->Base->svar('user_id');
	$acc_codes=  explode('_',$trans_type);
	$data=[
	    'passive_company_id'=>$passive_company_id,
	    'acc_debit_code'=>$acc_codes[0],
	    'acc_credit_code'=>$acc_codes[1],
	    'cstamp'=>$trans_date.date(" H:i:s"),
	    'amount'=>$amount,
	    'amount_alt'=>$amount_alt,
	    'description'=>$description,
	    'modified_by'=>$user_id
	];
	if( $trans_id ){
	    $this->update('acc_trans', $data, ['trans_id'=>$trans_id,'editable'=>1]);
	    return $this->db->affected_rows()>0?$trans_id:false;
	} else {
	    $data['editable']=1;
	    $data['active_company_id']=$this->Base->acomp('company_id');
	    $data['created_by']=$user_id;
	    $this->create('acc_trans', $data);
	    return $this->db->insert_id();
	}
    }
    public function transDelete( $trans_id ){
	$trans=$this->transGet($trans_id);
	if( $trans && $this->transCheckLevel($trans->acc_debit_code.'_'.$trans->acc_credit_code) ){
	    $this->delete('acc_trans',['trans_id'=>$trans_id,'editable'=>1]);
	    return $this->db->affected_rows()>0?true:false;
	}
	$this->Base->msg('access denied');
	return false;
    }
}