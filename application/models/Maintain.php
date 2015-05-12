<?php

class Maintain extends CI_Model {

    public function Maintain() {
	$this->dirParent=realpath('..');
	$this->dirWork = realpath('.');
	if( file_exists($this->dirWork.'/.git') ){
	    print("Work folder contains .git folder. Update may corrupt your work! Workdir is set to -isell3 ");
	    $this->dirWork = $this->dirParent.'/-isell3';//realpath('.');
	}	
	$this->dirUnpack=$this->dirParent.'/isell3_update';
	$this->dirBackup=$this->dirParent.'/isell3_backup';
	$this->zipPath = $this->dirUnpack.'/isell3_update.zip';
	$this->zipSubFolder = '/isell3-master/';
	parent::__construct();
    }

    public function autoUpdate( $step='' ){
	if( !$step ){
	    header("Refresh: 2; url= download");
	    return  'start downloading...';
	}
	if( $this->appUpdate($step) ){
	    if( $step=='download' ){
		header("Refresh: 2; url= unpack");
		return  'unpacking...';
	    }
	    if( $step=='unpack' ){
		header("Refresh: 2; url= swap");
		return  'installing...';
	    }
	    if( $step=='swap' ){
		return  'update succeded!';
	    }
	}
	else{
	    return "Failure at step: $step";
	}
    }
    
    public function appUpdate($action = 'download') {
	if ($action == 'download') {
	    return $this->updateDownload(BAY_UPDATE_URL, $this->zipPath);
	}
	if ($action == 'unpack') {
	    return $this->updateUnpack();
	}
	if ($action == 'swap') {
	    return $this->updateSwap();
	}
	if ($action == 'init') {
	    //copy config file;run db migrations
	}
    }

    private function updateDownload($updateUrl, $updateFile) {
	set_time_limit(240);
	if( !file_exists ($this->dirUnpack) ){
	    mkdir($this->dirUnpack);
	}
	return copy($updateUrl, $updateFile);
    }

    private function updateUnpack() {
	$zip = new ZipArchive;
	if ($zip->open($this->zipPath) === TRUE) {
	    $zip->extractTo($this->dirUnpack);
	    $zip->close();
	    return true;
	} else {
	    return false;
	}
    }

    private function updateSwap() {
	if( file_exists($this->dirBackup)
	    && file_exists($this->dirWork)
	    && file_exists($this->dirUnpack . $this->zipSubFolder)
	    && file_exists($this->dirUnpack)){
	    $this->delTree($this->dirBackup);
	    rename($this->dirWork, $this->dirBackup);
	    rename($this->dirUnpack . $this->zipSubFolder, $this->dirWork);
	    $this->delTree($this->dirUnpack);
	    return true;
	}
	return false;
    }

    private function delTree($dir) {
	if( !file_exists ($dir) ){
	    return false;
	}
	$files = array_diff(scandir($dir), array('.', '..'));
	foreach ($files as $file) {
	    (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
	}
	return rmdir($dir);
    }

}
