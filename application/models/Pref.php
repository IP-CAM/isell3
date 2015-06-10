<?php
require_once 'Catalog.php';
class Pref extends Catalog {
    public function getStaffList() {
        $sql = "SELECT 
                    user_id,
                    user_position,
                    first_name,
                    middle_name,
                    last_name,
                    id_type,
                    id_serial,
                    id_number,
                    id_given_by,
                    id_date,
                    CONCAT(last_name,' ',first_name,' ',middle_name) AS full_name,
                    CONCAT(last_name,' ',first_name,' ',middle_name) AS label 
                FROM 
		    " . BAY_DB_MAIN . ".user_list
                WHERE 
		    first_name IS NOT NULL AND last_name IS NOT NULL";
        return $this->get_list($sql);
    }
}
