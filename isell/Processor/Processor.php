<?php
date_default_timezone_set('Europe/Kiev');
set_include_path('.'.PATH_SEPARATOR.'isell/Processor/');
error_reporting(E_ERROR | E_PARSE);
ini_set('html_errors','off');
class Processor  extends CI_Model {
    public function on(){
	if( isset($_REQUEST['mod']) ){
	    $this->executeModule();
	}
	else{
	    header('Location:../');
	}
    }
    private function executeModule(){
	//include '_NILUA.php';
	$mod=$_REQUEST['mod'];
	$processorName=$mod.'Proc';
	if( file_exists("isell/Processor/mods/$processorName.php") ){
	    include "mods/$processorName.php";
	    $proc=new $processorName();
	}
	else{
	    die("No such processor found! $processorName");
	}
    }
}
