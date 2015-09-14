<?php

class Maintain extends CI_Model {

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
		return  'installing files...';
	    }
	    if( $step=='swap' ){
		header("Refresh: 2; url= install");
		return  'finishing installation...';
	    }
	    if( $step=='install' ){
		return  'update succeded!';
	    }
	}
	else{
	    return "Failure at step: $step";
	}
    }
    
    private function setupUpdater(){
	$this->dirParent=realpath('..');
	$this->dirWork = realpath('.');
	if( file_exists($this->dirWork.'/.git') ){
	    $this->Base->msg("Work folder contains .git folder. Update may corrupt your work! Workdir is set to -isell3 ");
	    $this->dirWork = $this->dirParent.'/-isell3';//realpath('.');
	}	
	$this->dirUnpack=$this->dirParent.'/isell3_update';
	$this->dirBackup=$this->dirParent.'/isell3_backup';
	$this->zipPath = $this->dirUnpack.'/isell3_update.zip';
	$this->zipSubFolder = '/isell3-master/';	
    }
    
    public function appUpdate($action = 'download') {
	$this->setupUpdater();
	if ($action == 'download') {
	    return $this->updateDownload(BAY_UPDATE_URL, $this->zipPath);
	}
	if ($action == 'unpack') {
	    return $this->updateUnpack();
	}
	if ($action == 'swap') {
	    return $this->updateSwap();
	}
	if ($action == 'install') {
	    return $this->updateInstall();
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
        //$this->load->helper('file');
        //delete_files('./path/to/directory/', TRUE);
        
        
        
	if( file_exists($this->dirWork)
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
    
    public function updateInstall(){
	$this->dirWork = realpath('.');
	$file = fopen($this->dirWork.'/install/db_update.sql', "r");
	while(!feof($file)){
	    $line = fgets($file);
	    $this->db->query($line);
	}
	fclose($file);
	return true;
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

    public function getCurrentVersionStamp(){
	$this->dirWork = realpath('.');
	if( file_exists($this->dirWork.'/.git') ){
	    return date(time());
	}
	return date ("Y-m-d\TH:i:s\Z", filemtime($this->dirWork));
    }
    private $path_to_backup_folder="/ISELL-DB-BACKUP/";
    public function backupDump(){
        $this->Base->set_level(4);
        $path_to_mysql=$this->db->query("SHOW VARIABLES LIKE 'basedir'")->row()->Value;
	if( !file_exists ($this->path_to_backup_folder) ){
	    mkdir($this->path_to_backup_folder);
	}
        $filename=$this->path_to_backup_folder.date('Y-m-d_H-i-s')."-".BAY_DB_NAME.'-ISELL-DB-BACKUP.sql';
        exec("$path_to_mysql/bin/mysqldump --user=".BAY_DB_USER." --password=".BAY_DB_PASS."  --default-character-set=utf8 --single-transaction=TRUE --routines --events  ".BAY_DB_NAME." >".$filename,$output);
        if( count($output) ){
            file_put_contents($filename.'.log', implode( "\n", $output ));
            return false;
        }
        return true;
    }
    public function backupImport( $file ){
	$this->Base->set_level(4);
        $path_to_mysql=$this->db->query("SHOW VARIABLES LIKE 'basedir'")->row()->Value;
	if( file_exists($this->path_to_backup_folder.$file) ){
	    //echo "$path_to_mysql/bin/mysql --user=".BAY_DB_USER." --password=".BAY_DB_PASS." --single-transaction ".BAY_DB_NAME." <".$this->path_to_backup_folder.$file;
	    exec("$path_to_mysql/bin/mysql --user=".BAY_DB_USER." --password=".BAY_DB_PASS." ".BAY_DB_NAME." <".$this->path_to_backup_folder.$file." 2>&1",$output);
	    if( count($output) ){
		file_put_contents($this->path_to_backup_folder.date('Y-m-d_H-i-s').'-IMPORT.log', implode( "\n", $output ));
	    }
	}
    }
    public function backupList(){
	$this->Base->set_level(4);
	$files = array_diff(scandir($this->path_to_backup_folder), array('.', '..'));
	arsort($files);
	return array_values ($files);
    }
    public function backupListNamed(){
	$this->Base->set_level(4);
	$named=[];
	$list=$this->backupList();
	foreach($list as $file){
	    $named[]=['file'=>$file];
	}
	return $named;
    }
    public function backupDown( $file ){
	$this->Base->set_level(4);
	if( file_exists ($this->path_to_backup_folder.$file) ){
	    header('Content-type: application/force-download');
	    header('Content-Disposition: attachment; filename="'.$file.'"');
	    echo file_get_contents($this->path_to_backup_folder.$file);
	} else {
	    show_error('X-isell-error: File not found!'.$this->path_to_backup_folder.$file, 404);
	}
    }
    public function backupUp(){
	if( $_FILES['upload_file'] ){
	    return move_uploaded_file ( $_FILES['upload_file']["tmp_name"] , $this->path_to_backup_folder.$_FILES['upload_file']['name'] );
	}
    }
    public function backupDelete( $file ){
	$this->Base->set_level(4);
	return unlink($this->path_to_backup_folder.$file);
    }
}
