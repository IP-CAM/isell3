<?php

class Maintain extends CI_Model {

    public function getCurrentVersionStamp(){
	$this->dirWork = realpath('.');
	if( file_exists($this->dirWork.'/.git') ){
	    return date(time());
	}
	return date ("Y-m-d\TH:i:s\Z", filemtime($this->dirWork));
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
	$this->zipSubFolder = $this->dirUnpack.'/isell3-master/';	
    }
    
    public function appUpdate($action = 'download') {
	$this->Base->set_level(2);
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
	$this->delTree($this->zipSubFolder);
	$zip = new ZipArchive;
	if ($zip->open($this->zipPath) === TRUE) {
	    $zip->extractTo($this->dirUnpack);
	    $zip->close();
	    return true;
	} else {
	    return false;
	}
    }
    
//    private function safeRename( $old, $new ){
//	$this->delTree($new);
//	$atempt=10;
//	while( $atempt-- ){
//	    sleep(1);
//	    if( rename($old,$new) ){
//		return true;
//	    }
//	}
//	return false;
//    }
    
    private function safeRename( $old, $new ){
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	if( file_exists($old) ){
	    $this->delTree($new);
	    if( rename($old,$new) ){
		return true;
	    } else {
		exec("move $old $new",$output,$code);
		return $code==0;
	    }
	}
    }
    
    private function updateSwap() {
	if( file_exists($this->dirWork) && file_exists($this->zipSubFolder) ){
            return  $this->safeRename($this->dirWork, $this->dirBackup) && 
		    $this->safeRename($this->zipSubFolder, $this->dirWork) &&
		    $this->delTree($this->dirUnpack);
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
    
    public function updateInstall(){
	$this->dirWork = realpath('.');
	$file = str_replace("\\", "/", $this->dirWork.'/install/db_update.sql');
	return $this->backupImportExecute($file);
    }

    private function setupConf(){
	$conf_file=  tempnam($this->dirWork,'hah');
	$conf='[client]
	    user="'.BAY_DB_USER.'"
	    password="'.BAY_DB_PASS.'"';
	file_put_contents($conf_file, $conf);
	return $conf_file;
    }
    
    private function backupImportExecute( $file ){
	$output=[];
	$conf_file=$this->setupConf();
        $path_to_mysql=$this->db->query("SHOW VARIABLES LIKE 'basedir'")->row()->Value;
	exec("$path_to_mysql/bin/mysql --defaults-file=$conf_file ".BAY_DB_NAME." <".$file." 2>&1",$output);
	unlink($conf_file);
	if( count($output) ){
	    file_put_contents($this->path_to_backup_folder.date('Y-m-d_H-i-s').'-IMPORT.log', implode( "\n", $output ));
	    return false;
	}
	return true;
    }
    
    public function backupImport(){
	$this->Base->set_level(4);
        $file=$this->input->post('filename');
	if( file_exists($this->path_to_backup_folder.$file) ){
	    return $this->backupImportExecute($this->path_to_backup_folder.$file);
	}
        return false;
    }
    
    private $path_to_backup_folder="/ISELL-DB-BACKUP/";
    public function backupDump(){
        $this->Base->set_level(4);
        $path_to_mysql=$this->db->query("SHOW VARIABLES LIKE 'basedir'")->row()->Value;
	if( !file_exists ($this->path_to_backup_folder) ){
	    mkdir($this->path_to_backup_folder);
	}
        $output=[];
        $filename=$this->path_to_backup_folder.date('Y-m-d_H-i-s')."-".BAY_DB_NAME.'-ISELL-DB-BACKUP.sql';
        exec("$path_to_mysql/bin/mysqldump --user=".BAY_DB_USER." --password=".BAY_DB_PASS."  --default-character-set=utf8 --single-transaction=TRUE --routines --events  ".BAY_DB_NAME." >".$filename,$output);
        if( count($output) ){
            file_put_contents($filename.'.log', implode( "\n", $output ));
            return false;
        }
        return true;
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
	if( !file_exists ($this->path_to_backup_folder) ){
	    mkdir($this->path_to_backup_folder);
	}
	if( $_FILES['upload_file'] && !$_FILES['upload_file']['error'] ){
	    return 'uploaded'.move_uploaded_file( $_FILES['upload_file']["tmp_name"] , $this->path_to_backup_folder.$_FILES['upload_file']['name'] );
	}
        return 'error'.$_FILES['upload_file']['error'];
    }
    public function backupDelete(){
	$this->Base->set_level(4);
        $file=$this->input->post('filename');
	return unlink($this->path_to_backup_folder.$file);
    }
}
