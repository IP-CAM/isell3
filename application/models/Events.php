<?php
require_once 'Catalog.php';
class Events extends Catalog{
    public $min_level=1;
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
		NOT event_is_private 
		OR event_is_private AND (event_user_id='$user_id' OR $user_level>=3)
	    ORDER BY event_date DESC
	    ";
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
		DATE(event_date)='$date'";
	return $this->get_list($sql);
    }
}
