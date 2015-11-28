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
	$sql="SELECT *,
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
	$check_id=$this->request('check_id','int');
	$check=$this->getCheck($check_id);
	
        $Company=$this->Base->load_model("Company");
        $company_id=$Company->companyFindByCode( $check->correspondent_code );
	$pcomp=$Company->selectPassiveCompany($company_id);
	
	
	$favs=$this->accountFavoritesFetch(true);
	foreach($favs as $acc){
	    
	}
	
        if( $company_id ){
            return [
                'pcomp'=>$pcomp,
                'sugg'=>$suggestions
            ];
        }
        return null;
    }
    private function getCheck( $check_id ){
	return $this->get_row("SELECT * FROM acc_check_list WHERE check_id=$check_id");
    }
    private function getSuggestions( $acc ){
	
	return $suggestions;
    }
}