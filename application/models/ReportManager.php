<?php

class ReportManager extends CI_Model {
    private $plugin_folder='application/views/plugins/reports/';
    public function listFetch(){
	$plugins=$this->scanFolder($this->plugin_folder);
	$reports=[];
	foreach($plugins as $plugin_folder){
	    $reports[]=include $this->plugin_folder.$plugin_folder."/info.php";
	}
	return $reports;
    }
    private function scanFolder( $path ){
	$this->Base->set_level(4);
	$files = array_diff(scandir($path), array('.', '..'));
	arsort($files);
	return array_values($files);	
    }
    
}