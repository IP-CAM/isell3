<?php
/**
 * COMPANY SPECIFIC FUNCTIONS
 *
 * @author Baycik
 */
require_once 'Catalog.php';
class Company extends Catalog{
    public function branchFetch() {
	$parent_id = $this->input->get('id');
	$table = "companies_tree LEFT JOIN companies_list USING(branch_id)";
	$assigned_path=  $this->Base->svar('user_assigned_path');
	if( $parent_id ){
	    $parent=$parent_id;
	} else {
	    $parent=$assigned_path;
	}
	return $this->treeFetch($table, $parent, 'top');
    }
    public function listFetch( $mode='' ){
	$q = $this->input->get('q') or $q = 0;
	$assigned_path=$this->Base->svar('user_assigned_path');
	if( $q ){
	    $sql="SELECT *
		FROM
		    companies_tree
		JOIN 
		    companies_list USING(branch_id)
		WHERE
		    label LIKE '%$q%'
			AND
		    is_leaf=1
			AND
		    path LIKE '$assigned_path%'";
	    return $this->get_list( $sql );
	}
	else if( $mode=='selected_passive_if_empty' ){
	    return array($this->Base->svar('pcomp'));    
	}
	return array();
    }

    public function companyGet( $company_id=0 ){
	$assigned_path=$this->Base->svar('user_assigned_path');
	$sql="SELECT
		*
	    FROM
		companies_list cl
	    JOIN
		companies_tree USING(branch_id)
	    WHERE
		path LIKE '$assigned_path%'
		    AND
		company_id=$company_id";
	return $this->get_row($sql);
    }
    
    
    public function companyCreate($parent_id){
    }
    public function companyUpdate($company_id, $field, $value) {
	$key = array(
	    'company_id' => $company_id
	);
	$data = array(
	    $field => $value
	);
	return $this->update("companies_tree LEFT JOIN companies_list USING(branch_id)", $key, $data);
    }
    public function companyDelete($company_id){
	$company_id=(int) $company_id;
	$row = $this->db->query("SELECT branch_id FROM companies_list WHERE company_id='$company_id'")->row();
	if( $row && $row->branch_id ){
	    // don't forget delete from companies_list
	    return $this->treeDelete('companies_tree', $row->branch_id);
	}
	return false;
    }

    public function selectPassiveCompany( $company_id ){
	$company=$this->companyGet( $company_id );
	$this->Base->svar('pcomp',$company);
	return $company;
    }
}
