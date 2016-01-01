<?php
define('BAY_DB_HOST','127.0.0.1');
define('BAY_DB_USER','root');
define('BAY_DB_PASS','');
define('BAY_DB_NAME','nilua');
set_time_limit(600);
class Migrate{
    function __construct() {
	$this->mysqli = new mysqli(BAY_DB_HOST, BAY_DB_USER, BAY_DB_PASS, BAY_DB_NAME);
	$res=  $this->mysqli->query("SHOW VARIABLES LIKE 'basedir'");
	$row=$res->fetch_array(MYSQLI_NUM);
	$this->path_to_mysql=$row[0];
    }
    private $path_to_backup_folder="/ISELL-DB-BACKUP/";
    public function backupDump(){
	if( !file_exists ($this->path_to_backup_folder) ){
	    mkdir($this->path_to_backup_folder);
	}
        $filename=$this->path_to_backup_folder.date('Y-m-d_H-i-s')."-".BAY_DB_NAME.'-ISELL-DB-BACKUP-BEFOREMULTICOMP.sql';
        exec("$this->path_to_mysql/bin/mysqldump --user=".BAY_DB_USER." --password=".BAY_DB_PASS."  --default-character-set=utf8 --single-transaction=TRUE --routines --events  ".BAY_DB_NAME." >".$filename,$output);
        if( count($output) ){
            file_put_contents($filename.'.log', implode( "\n", $output ));
            return false;
        }
        return true;
    }
}
