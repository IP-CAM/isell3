<?php
/**
 * COMPANY SPECIFIC FUNCTIONS
 *
 * @author Baycik
 */
require_once 'Catalog.php';
class Company extends Catalog{
    public function branchFetch() {
	$parent_id = $this->input->get('id') or $parent_id = 0;
	$table = "companies_tree LEFT JOIN companies_list USING(branch_id)";
	return $this->treeFetch($table, $parent_id, 'top');
    }
    public function listFetch(){
	$q = $this->input->get('q') or $q = 0;
	$query=$this->db->like('label', $q, 'both')->get("companies_tree LEFT JOIN companies_list USING(branch_id)");
	return $this->get_list( $query );
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
	$company=$this->get('companies_list',array('company_id'=>$company_id));
	$this->Base->svar('pcomp',$company);
	return $company;
    }
}
