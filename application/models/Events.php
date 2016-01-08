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
		DISTINCT(SUBSTR(event_date,1,10)) event_date
	    FROM 
		event_list 
	    WHERE 
		NOT event_is_private 
		OR event_is_private AND (event_user_id='$user_id' OR $user_level>=3)
	    ORDER BY event_date DESC
	    LIMIT 90";
	return $this->get_list($sql);
    }
}
