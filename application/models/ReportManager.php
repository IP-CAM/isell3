<?php
require_once 'Catalog.php';
class ReportManager extends Catalog {
    private $plugin_folder='application/views/plugins/reports/';
    private $current_info;
    public function listFetch(){
	$plugins=$this->scanFolder($this->plugin_folder);
	$reports=[];
	foreach($plugins as $plugin_folder){
	    $info=$this->infoGet($plugin_folder);
	    $reports[]=$info;
	}
	return $reports;
    }
    private function scanFolder( $path ){
	$this->Base->set_level(4);
	$files = array_diff(scandir($path), array('.', '..'));
	arsort($files);
	return array_values($files);	
    }
    private function infoGet( $report_id=null ){
	$info=include $this->plugin_folder.$report_id."/info.php";
	$info['report_id']=$report_id;
	return $info;
    }
    
    public function formGet( $report_id=null ){
	$this->check($report_id,'\w+');
	if( $report_id ){
	    return file_get_contents($this->plugin_folder.$report_id.'/form.html');
	}
	show_error('X-isell-error: Report id is not supplied!', 500);
    }
    
    public function formSubmit( $report_id=null ){
	$this->current_info=$this->infoGet($report_id);
	$Plugin=$this->Base->load_plugin('reports',$report_id);
	$dump=[
	    'tpl_files_folder'=>"plugins/reports/{$this->current_info['report_id']}/",
	    'tpl_files'=>$this->current_info['template'],
	    'title'=>$this->current_info['title'],
	    'user_data'=>[
		'email'=>$this->Base->svar('pcomp')?$this->Base->svar('pcomp')->company_email:'',
		'text'=>$this->current_info['title']
	    ],
	    'view'=>$Plugin->viewGet()
	];
	$ViewManager=$this->Base->load_model('ViewManager');
	$ViewManager->store($dump);
	$ViewManager->outRedirect('.print');
	
	//print_r($view);exit;
	
	//return $this->out( $view );
    }
    
    private function out( $view ){
	$FileEngine=$this->Base->load_model('FileEngine');
	$FileEngine->templateDefFolder="plugins/reports/{$this->current_info['report_id']}/";
	$FileEngine->assign($view, $this->current_info['template']);
//	if ( $out_type=='.print' ) {
//	    $file_name = '.print';
//	    $FileEngine->show_controls = false;
//	    $FileEngine->user_data = [
//		'title' => $this->dump->title,
//		'msg' => $this->dump->user_data->text,
//		'email' => $this->dump->user_data->email,
//		'fgenerator'=>'ViewManager',
//		'out_type'=>$out_type,
//		'dump_id' => $this->dump->dump_id
//		];
//	} else {
//	    $file_name = str_replace(' ','_',$this->dump->title).$out_type;
//	}
	$FileEngine->show_controls=false;
	$FileEngine->header_mode='no_headers';
	return $FileEngine->fetch('.html');
    }
}