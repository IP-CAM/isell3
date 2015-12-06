<?php
require_once 'AccountsData.php';
class AccountsBank extends AccountsData{
    public $min_level=3;
    public function clientBankGet( $main_acc_code=0, $page=1, $rows=30 ){
        $this->check($page,'int');
        $this->check($rows,'int');
        $this->check($main_acc_code);
	$active_company_id=$this->Base->acomp('company_id');
        
	$having=$this->decodeFilterRules();
	$offset=$page>0?($page-1)*$rows:0;
	$sql="SELECT check_id,trans_id,number,correspondent_name,correspondent_code,correspondent_account,correspondent_bank_name,correspondent_bank_code,assignment,
		    IF(trans_id,'ok Проведен','gray Непроведен') AS status,
		    IF(debit_amount,ROUND(debit_amount,2),'') AS debit,
		    IF(credit_amount,ROUND(credit_amount,2),'') AS credit,
		    DATE_FORMAT(transaction_date,'%d.%m.%Y') AS tdate
                FROM acc_check_list 
		WHERE main_acc_code='$main_acc_code' AND active_company_id='$active_company_id'
		HAVING $having
		ORDER BY transaction_date DESC 
		LIMIT $rows OFFSET $offset";
	$result_rows=$this->get_list($sql);
	$total_estimate=$offset+(count($result_rows)==$rows?$rows+1:count($result_rows));
        
	return ['rows'=>$result_rows,'total'=>$total_estimate];
    }
    public function getCorrespondentStats(){
	$check_id=$this->request('check_id','int',0);
	$check=$this->getCheck($check_id);
	
        $Company=$this->Base->load_model("Company");
        $company_id=$Company->companyFindByCode( $check->correspondent_code );
	if( !$company_id ){
	    return null;
	}
	
	$pcomp=$Company->selectPassiveCompany($company_id);
	$favs=$this->accountFavoritesFetch(true);
	foreach($favs as $acc){
	    $this->appendSuggestions($acc,$check);
	}
	return [
	    'trans_id'=>$check->trans_id,
	    'pcomp'=>$pcomp,
	    'favs'=>$favs
	];
    }
    private function getCheck( $check_id ){
	return $this->get_row("SELECT * FROM acc_check_list WHERE check_id=$check_id");
    }
    private function appendSuggestions( &$acc, $check ){
	$active_company_id=$this->Base->acomp('company_id');
	$passive_company_id=$this->Base->pcomp('company_id');
	$sql="SELECT 
		    at.*,
		    DATE_FORMAT(tstamp,'%d.%m.%Y') date,
		    code,
		    descr
		FROM 
		    acc_trans  at
		        JOIN
		    acc_trans_status USING (trans_status)
			JOIN
		    acc_check_list acl ON debit_amount=amount OR credit_amount=amount
		WHERE 
		    at.active_company_id=$active_company_id
		    AND at.passive_company_id=$passive_company_id
                    AND IF(debit_amount>0,acc_credit_code='{$acc->acc_code}',acc_debit_code='{$acc->acc_code}')
		    AND trans_status IN(1,2,3)
		    AND acl.check_id={$check->check_id}";
	$acc->suggs=$this->get_list($sql);
	return $acc;
    }
}