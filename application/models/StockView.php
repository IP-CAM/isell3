<?php
require_once 'Stock.php';
class StockView extends Stock{
    public function stockViewGet(){
	$page=$this->request('page','int');
	$rows=10000;//$this->request('rows','int');
	$parent_id=$this->request('parent_id','int');
	$having=$this->decodeFilterRules();
	$out_type=$this->request('out_type');
	$doc_view_id=$this->stockViewStore($page, $rows, $parent_id, $having);
        
        $out=$this->viewFileGet($doc_view_id,$out_type,'send_headers');
        exit($out);
    }
    public function viewFileGet($doc_view_id,$out_type,$header_mode='send_headers'){
        $view=$this->stockViewFill($doc_view_id);
        return $this->stockViewOut($view, $out_type,$header_mode);
    }
    private function stockViewStore($page, $rows, $parent_id, $having) {
        $doc_view_id = time();
        $views = $this->Base->svar('storedStockViews');
	$views[$doc_view_id]=(object)[
	    'doc_view_id'=>$doc_view_id,
	    'having'=>$having,
	    'parent_id'=>$parent_id,
	    'page'=>$page,
	    'rows'=>$rows
	];
        $this->Base->svar('storedStockViews', $views);
        return $doc_view_id;
    }
    private function stockViewFill($doc_view_id){
        $views = $this->Base->svar('storedStockViews');
        $view = $views[$doc_view_id];
        if (!$view) {
            die('Образ под таким номером не найден!');
        }
	$view->date=date('d.m.Y H:i');
	$view->p = $this->Base->svar('pcomp');
	$view->user_sign = $this->Base->svar('user_sign');
	$view->cat_name=$this->get_value("SELECT label FROM stock_tree WHERE branch_id='{$view->parent_id}'");
	$view->stock=(object) $this->listFetch($view->page,$view->rows,$view->parent_id,$view->having);
	return $view;	
    }
    private function stockViewOut($view,$out_type,$header_mode){
	$acomp_lang=$this->Base->acomp('language');
        foreach ($view->stock->rows as $row) {
            $row->product_quantity==0?$row->product_quantity='':'';
        }
        $FileEngine=$this->Base->load_model('FileEngine');
	$FileEngine->assign($view, $acomp_lang.'/StockValidation.xlsx');
        if ($out_type == 'print') {
            $file_name = '.print';
            $FileEngine->show_controls = true;
            $FileEngine->user_data = [
                'title' => "Залишки на складі",
                'msg' => 'Доброго дня',
                'email' => $view->p->company_email,
                'fgenerator'=>'StockView',
                'out_type'=>$out_type,
                'doc_view_id' => $view->doc_view_id
                ];
        } else {
	    $file_name = "Остатки_на_склвде_{$view->date}$out_type";
	}
        $FileEngine->header_mode=$header_mode;
        return $FileEngine->fetch($file_name);
    }
}