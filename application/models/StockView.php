<?php
require_once 'Stock.php';
class StockView extends Stock{
    public function stockViewGet(){
	$page=$this->request('page','int');
	$rows=10000;;
	$parent_id=$this->request('parent_id','int');
	$having=$this->decodeFilterRules();
	$out_type=$this->request('out_type');
	
	$table=$this->listFetch($page,$rows,$parent_id,$having);
	foreach ($table['rows'] as $row) {
            $row->product_quantity==0?$row->product_quantity='':'';
        }
	$dump=[
	    'tpl_files'=>$this->Base->acomp('language').'/StockValidation.xlsx',
	    'title'=>"Залишки на складі",
	    'user_data'=>[
		'email'=>$this->Base->svar('pcomp')?$this->Base->svar('pcomp')->company_email:'',
		'text'=>'Доброго дня'
	    ],
	    'view'=>[
		'p'=>$this->Base->svar('pcomp'),
		'date'=>date('d.m.Y H:i'),
		'user_sign'=>$this->Base->svar('user_sign'),
		'cat_name'=>$this->get_value("SELECT label FROM stock_tree WHERE branch_id='{$parent_id}'"),
		'stock'=>$table
	    ]
	];
	$ViewManager=$this->Base->load_model('ViewManager');
	$ViewManager->store($dump);
	$ViewManager->outRedirect($out_type);
    }
    public function stockMoveViewGet(){
	$page=$this->request('page','int');
	$rows=$this->request('rows','int');
	$having=$this->decodeFilterRules();
	$out_type=$this->request('out_type');
	
	$dump=[
	    'tpl_files'=>$this->Base->acomp('language').'/StockMovements.xlsx',
	    'title'=>"Рух товарів",
	    'user_data'=>[
		'email'=>$this->Base->svar('pcomp')?$this->Base->svar('pcomp')->company_email:'',
		'text'=>'Доброго дня'
	    ],
	    'view'=>[
		'date'=>date('d.m.Y H:i'),
		'user_sign'=>$this->Base->svar('user_sign'),
		'table'=>$this->movementsFetch($page,$rows,$having)
	    ]
	];
	$ViewManager=$this->Base->load_model('ViewManager');
	$ViewManager->store($dump);
	$ViewManager->outRedirect($out_type);
    }
}