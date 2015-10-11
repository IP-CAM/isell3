<?php
require_once 'AccountsCore.php';
class AccountsView extends AccountsCore{
    public function ledgerViewGet(){
	$page=$this->request('page','int');
	$rows=$this->request('rows','int');
	$idate=$this->request('idate','\d\d\d\d-\d\d-\d\d');
	$fdate=$this->request('fdate','\d\d\d\d-\d\d-\d\d');
	$acc_code=$this->request('acc_code','int');
	$out_type=$this->request('out_type');
	$use_passive_filter=$this->request('use_passive_filter','bool');
	
	$doc_view_id=$this->ledgerViewStore($acc_code, $idate, $fdate, $page, $rows, $use_passive_filter);
	$view=$this->ledgerViewFill($doc_view_id);
	$out=$this->ledgerViewOut($view, $out_type);
	exit($out);
    }
    private function ledgerViewStore($acc_code, $idate, $fdate, $page, $rows, $use_passive_filter) {
        $doc_view_id = time();
        $views = $this->Base->svar('storedLedgerViews');
	$views[$doc_view_id]=(object)[
	    'doc_view_id'=>$doc_view_id,
	    'active_company_id'=>$this->Base->acomp('company_id'),
	    'passive_company_id'=>$this->Base->pcomp('company_id'),
	    'acc_code'=>$acc_code,
	    'idate'=>$idate,
	    'fdate'=>$fdate,
	    'page'=>$page,
	    'rows'=>$rows,
	    'use_passive_filter'=>$use_passive_filter
	];
        $this->Base->svar('storedLedgerViews', $views);
        return $doc_view_id;
    }
    private function ledgerViewFill($doc_view_id){
        $views = $this->Base->svar('storedLedgerViews');
        $view = $views[$doc_view_id];
        if (!$view) {
            die('Образ под таким номером не найден!');
        }
        $Utils=$this->Base->load_model('Utils');
	$Company=$this->Base->load_model('Company');
	$previous_acomp_id=$this->Base->acomp('company_id');
	$previous_pcomp_id=$this->Base->pcomp('company_id');
	$Company->selectActiveCompany( $view->active_company_id );
	$Company->selectPassiveCompany( $view->passive_company_id );

        $view->ledger = (object) $this->ledgerFetch($view->acc_code, $view->idate, $view->fdate, $view->page, $view->rows, $view->use_passive_filter);
        $view->a = $this->Base->svar('acomp');
        $view->p = $this->Base->svar('pcomp');
        $view->user_sign = $this->Base->svar('user_sign');
        $view->idate = date('d.m.Y', strtotime($view->idate));
        $view->fdate = date('d.m.Y', strtotime($view->fdate));
        $view->spell = $Utils->spellAmount( abs($view->ledger->sub_totals->fbal) );//$view->ledger->sub_totals->fbal
        $view->localDate = $Utils->getLocalDate($view->fdate);
	
	$Company->selectActiveCompany( $previous_acomp_id );	
	$Company->selectPassiveCompany( $previous_pcomp_id );
	return $view;	
    }
    private function ledgerViewOut($view,$out_type){
	$acomp_lang=$this->Base->acomp('language');
        $FileEngine=$this->Base->load_model('FileEngine');
        if ($view->use_passive_filter) {//Convert trans status to readible form
            foreach ($view->ledger->rows as $row) {
                $arr = explode(' ', $row->trans_status);
                $row->trans_status = $arr[1];
		$row->debit==0?$row->debit='':'';
		$row->credit==0?$row->credit='':'';
            }
            $FileEngine->assign($view, $acomp_lang.'/LedgerPayments.xlsx');
	    $file_name = "Акт_Сверки_{$view->fdate}$out_type";
        } else {
            $FileEngine->assign($view, $acomp_lang.'/LedgerTransactions.xlsx');
            $file_name = "Выписка_Счета_{$view->acc_code}$out_type";
        }
        if ($out_type == 'print') {
            $file_name = '.print';
            $FileEngine->show_controls = true;
            $FileEngine->user_data = ['title' => "Виписка з рахунку", 'msg' => 'Доброго дня', 'doc_view_id' => $view->doc_view_id];//'email' => $view['p']->company_email, 
        }
        return $FileEngine->fetch($file_name);
    }
}