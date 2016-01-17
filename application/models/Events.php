<?php
require_once 'Catalog.php';
class Events extends Catalog{
    public $min_level=2;
    /*
     * event_statuses
     * undone
     * done
     * 
     */
    public function activeDatesGet() {//must be optimized
	$user_id = $this->Base->svar('user_id');
	$user_level = $this->Base->svar('user_level');
	$sql="SELECT 
		DISTINCT(DATE(event_date)) event_date
	    FROM 
		event_list 
	    WHERE 
		event_label<>'chat' 
		AND ( NOT event_is_private OR event_is_private AND (event_user_id='$user_id' OR $user_level>=3) )
	    ORDER BY event_date DESC";
	return $this->get_list($sql);
    }
    
    public function listFetch( $date ){
	$this->check($date,'\d\d\d\d-\d\d-\d\d');
	$sql="
	    SELECT
		*,
		DATE_FORMAT(event_date,'%d.%m.%Y') date_dmy,
		(SELECT nick FROM user_list WHERE user_id=modified_by) nick
	    FROM
		event_list
	    WHERE
		DATE(event_date)='$date' AND event_label<>'chat'
	    ORDER BY event_label";
	return $this->get_list($sql);
    }
    
    public function eventDelete( $event_id ){
	$this->check($event_id,'int');
	return $this->delete("event_list",['event_id'=>$event_id]);
    }
    
    public function eventMove( $olddate, $newdate, $event_id, $label, $mode ){
	$this->check($event_id,'int');
	$this->check($olddate,'\d\d\d\d-\d\d-\d\d');
	$this->check($newdate,'\d\d\d\d-\d\d-\d\d');
	$this->check($label);
	if( $mode=='all' ){
	    $this->query("UPDATE event_list SET event_date='$newdate' WHERE DATE(event_date)='$olddate' AND event_label='$label'");
	    return $this->db->affected_rows();
	}
	return $this->update('event_list',['event_date'=>$newdate],['event_id'=>$event_id]);
    }
}
