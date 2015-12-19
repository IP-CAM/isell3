<?php
require_once 'Catalog.php';
class ReportManager extends Catalog {
    private $plugin_folder='application/views/plugins/reports/';
    public function listFetch(){
	$plugins=$this->scanFolder($this->plugin_folder);
	$reports=[];
	foreach($plugins as $plugin_folder){
	    $info=include $this->plugin_folder.$plugin_folder."/info.php";
	    $info['report_id']=$plugin_folder;
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
    
    public function formGet( $report_id=null ){
	$this->check($report_id,'\w+');
	if( $report_id ){
	    return file_get_contents($this->plugin_folder.$report_id.'/form.html');
	}
	show_error('X-isell-error: Report id is not supplied!', 500);
    }
}