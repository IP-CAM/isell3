<?php
require_once 'AccountsData.php';
class AccountsBank extends AccountsData{
    public $min_level=3;
    public function clientBankGet( $main_acc_code=0, $page=1, $rows=30 ){
        $this->check($main_acc_code);
        $this->check($page,'int');
        $this->check($rows,'int');
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
		    AND trans_status IN(0,1,2,3)
		    AND acl.check_id={$check->check_id}";
	$acc->suggs=$this->get_list($sql);
	return $acc;
    }
    /*
     * IMPORT OF FILE .csv or .xml
     */
    
    public function up( $label='' ){
	if( $_FILES['upload_file'] && !$_FILES['upload_file']['error'] ){
	    return $this->parseImport($_FILES['upload_file']);
	}
        return 'error'.$_FILES['upload_file']['error'];
    }
    
    
    
    private function parseImport( $UPLOADED_FILE ){
        if (strrpos($UPLOADED_FILE['name'], '.xml')) {
            $xml = file_get_contents($UPLOADED_FILE['tmp_name']);
            $report = new SimpleXMLElement($xml);
            foreach ($report->{'document-group'}->document as $document) {
                $this->addCheckDocument($document, $main_acc_code);
            }
	    return 'imported';
        } else if (strrpos($UPLOADED_FILE['name'], '.csv')) {
            $csv = file_get_contents($UPLOADED_FILE['tmp_name']);
            $csv = iconv('Windows-1251', 'UTF-8', $csv);
            $csv_lines = explode("\n", $csv);
            array_shift($csv_lines);
            $this->Base->LoadClass('Pref');
            $prefs=$this->Base->Pref->prefGet();
            $csv_sequence=explode(",",$prefs['clientbank_fields']);
            foreach ($csv_lines as $line) {
                if (!$line)
                    continue;
                $vals = str_getcsv($line, ';');
                $doc = array();
                $i=0;
                foreach($csv_sequence as $field){
                    $doc[trim($field)]=$vals[$i++];
                }
                $this->addCheckDocument($doc, $main_acc_code);
            }
	    return 'imported';
        }
	return 'error'."Формат должен быть .xml .csv";
    }
    private function addCheckDocument($check, $main_acc_code) {
        $fields = ['check_id','trans_id','main_acc_code','number','date','value_date','debit_amount','credit_amount','assumption_date','currency','transaction_date','client_name','client_code','client_account','client_bank_name','client_bank_code','correspondent_name','correspondent_code','correspondent_account','correspondent_bank_name','correspondent_bank_code','assignment','active_company_id'];
	$active_company_id=$this->Base->acomp('company_id');
        $set = ['active_company_id'=>$active_company_id];
        $check['main-acc-code'] = $main_acc_code;
        foreach ($fields as $field) {
            if ($field == 'check_id') {
                continue;
            }
            $xml_field = str_replace('_', '-', $field);
            $val = isset($check[$xml_field]) ? $check[$xml_field] : $check->$xml_field;
            if ($field == 'debit_amount' || $field == 'credit_amount') {
                $val = str_replace(',', '.', $val);
            }
            if (strpos($field, 'date') !== false) {
                preg_match_all('/(\d{2})[^\d](\d{2})[^\d](\d{4})( \d\d:\d\d(:\d\d)?)?/i', $val, $matches);
                $val = "{$matches[3][0]}-{$matches[2][0]}-{$matches[1][0]}{$matches[4][0]}";
            }
            $set[] = "$field='" . addslashes($val) . "' ";
        }
        $this->Base->query("INSERT INTO acc_check_list SET " . implode(',', $set), false);
        return true;
    }
    public function checkDelete( $check_id ){
	$this->check($check_id,'int');
	$check=$this->getCheck($check_id);
	if( $check->trans_id ){
	    $this->transDelete($check->trans_id);
	}
	return $this->delete('acc_check_list',['check_id'=>$check_id]);
    }
}