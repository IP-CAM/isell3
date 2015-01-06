<?php
require_once 'ProcessorBase.php';
class ModuleListProc extends ProcessorBase {
    function onDefault(){
	$mods=json_decode(file_get_contents('config/modules.json',true));
	$alowed=array();
	foreach( $mods as $mod ){
	    if( $this->svar('user_level')>=$mod->level && strpos(BAY_ACTIVE_MODULES, "/{$mod->name}/")!==false ){
		$alowed[]=$mod;
	    }
	}
	$this->response($alowed);
    }
}