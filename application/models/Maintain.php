<?php

class Maintain extends CI_Model{

    public function Maintain(){
	$this->zipPath='E:\update.zip';
	$this->zipSubFolder='/isell3-master/';
	$this->unpackDir='E:\unpack';
	$this->workDir='E:\isell3';
	parent::__construct();
    }
    
    public function appUpdate($action = 'download') {
	if ($action == 'download') {
	    return $this->updateDownload( BAY_UPDATE_URL, $this->zipPath );
	}
	if ($action == 'unpack' ){
	    return $this->updateUnpack();
	}
	if ($action == 'swap'){
	    return $this->updateSwap();
	}
	if ($action == 'init'){
	    
	}
    }

    private function updateDownload($updateUrl, $updateFile) {
	set_time_limit(240);
	$update = @file_get_contents($updateUrl);
	if ($update === false) {
	    msg('Could not download update `' . $updateUrl . '`!');
	    return false;
	}
	$handle = fopen($updateFile, 'w');
	if (!$handle) {
	    msg('Could not save update file `' . $updateFile . '`!');
	    return false;
	}
	if (!fwrite($handle, $update)) {
	    msg('Could not write to update file `' . $updateFile . '`!');
	    return false;
	}
	fclose($handle);
	return true;
    }

    
    private function updateUnpack() {
	$zip = new ZipArchive;
	if ($zip->open( $this->zipPath ) === TRUE) {
	    $zip->extractTo($this->unpackDir);
	    $zip->close();
	    return true;
	} else {
	    return false;
	}
    }

    private function updateSwap(){
	rename( $this->workDir , date("Y-m-d-H-i-s-").$this->workDir );
	rename( $this->unpackDir.$this->zipSubFolder , $this->workDir );
	unlink($this->unpackDir);
    }
    
}