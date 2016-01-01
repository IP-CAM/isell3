<?php
require_once 'AccountsCore.php';
class AccountsView extends AccountsCore{
    public function ledgerPaymentViewGet(){
	$this->ledgerViewGet();
    }
    public function ledgerViewGet(){
	$page=$this->request('page','int');
	$rows=10000;//$this->request('rows','int');
	$idate=$this->request('idate','\d\d\d\d-\d\d-\d\d');
	$fdate=$this->request('fdate','\d\d\d\d-\d\d-\d\d');
	$acc_code=$this->request('acc_code');
	$out_type=$this->request('out_type');
	$use_passive_filter=$this->request('use_passive_filter','bool');
	
	$dump=$this->fillDump($acc_code, $idate, $fdate, $page, $rows, $use_passive_filter);
	
	$ViewManager=$this->Base->load_model('ViewManager');
	$ViewManager->store($dump);
	$ViewManager->outRedirect($out_type);
    }
    private function fillDump($acc_code, $idate, $fdate, $page, $rows, $use_passive_filter){
        $Utils=$this->Base->load_model('Utils');
	$table=$this->ledgerFetch($acc_code, $idate, $fdate, $page, $rows, $use_passive_filter);
        foreach ($table['rows'] as $row) {
            $arr = explode(' ', $row->trans_status);
            $row->trans_status = $arr[1];
            $row->debit==0?$row->debit='':'';
            $row->credit==0?$row->credit='':'';
        }
        if ( $use_passive_filter ){
	    $tpl_files=$this->Base->acomp('language').'/LedgerPayments.xlsx';
	    $title="Акт Сверки на".date('d.m.Y', strtotime($fdate));
        } else {
	    $tpl_files=$this->Base->acomp('language').'/LedgerTransactions.xlsx';
	    $title="Выписка Счета на".date('d.m.Y', strtotime($fdate));
        }
	
	$dump=[
	    'tpl_files'=>$tpl_files,
	    'title'=>$title,
	    'user_data'=>[
		'email'=>$this->Base->svar('pcomp')?$this->Base->svar('pcomp')->company_email:'',
		'text'=>'Доброго дня'
	    ],
	    'view'=>[
		'a'=>$this->Base->svar('acomp'),
		'p'=>$this->Base->svar('pcomp'),
		'user_sign'=>$this->Base->svar('user_sign'),
		'idate_dmy'=>date('d.m.Y', strtotime($idate)),
		'fdate_dmy'=>date('d.m.Y', strtotime($fdate)),
		'spell'=>$Utils->spellAmount( abs($table['sub_totals']->fbal) ),
		'localDate'=>$Utils->getLocalDate($fdate),
		'ledger'=>$table
	    ]
	];
	return $dump;
    }
}