<?php
/**
 * COMPANY SPECIFIC FUNCTIONS
 *
 * @author Baycik
 */
require_once 'Catalog.php';
class Company extends Catalog{
    public function branchFetch() {
	$parent_id = $this->input->get('id') or $parent_id=0;
	$table = "companies_tree LEFT JOIN companies_list USING(branch_id)";
	$assigned_path=  $this->Base->svar('user_assigned_path');
	$level=$this->Base->svar('user_level');
	return $this->treeFetch($table, $parent_id, 'top', $assigned_path, $level);
    }
    
    public function listFetch( $mode='' ){
	$q = $this->input->get('q') or $q = 0;
	$assigned_path=$this->Base->svar('user_assigned_path');
	$level=$this->Base->svar('user_level');
	$companies=[];
	if( $q ){
	    $sql="SELECT 
		    company_id,
		    label,
		    path
		FROM
		    companies_tree
		JOIN 
		    companies_list USING(branch_id)
		WHERE
		    label LIKE '%$q%'
			AND
		    is_leaf=1
			AND
		    path LIKE '$assigned_path%'
			AND
		    level<=$level";
	    $companies=$this->get_list( $sql );
	}
	else if( $mode=='selected_passive_if_empty' ){
	    array_push($companies,['company_id'=>$this->Base->pcomp('company_id'),'label'=>$this->Base->pcomp('label'),'path'=>$this->Base->pcomp('path')]);
	} else {
	    array_push($companies,['company_id'=>0,'label'=>'-','path'=>'']);
	}
	if( $mode=='with_active' ){
	    array_push($companies,['company_id'=>$this->Base->acomp('company_id'),'label'=>$this->Base->acomp('company_name'),'path'=>'']);
	}
	return $companies;
    }

    public function companyGet( $company_id=0 ){
	$this->check($company_id,'int');
	$assigned_path=$this->Base->svar('user_assigned_path');
	$sql="SELECT
		*
	    FROM
		companies_list cl
	    LEFT JOIN
		companies_tree USING(branch_id)
	    LEFT JOIN 
		curr_list USING(curr_code)
	    WHERE
		(path LIKE '$assigned_path%' OR path IS NULL)
		    AND
		company_id=$company_id";
	return $this->get_row($sql);
    }
    
    
    public function companyTreeCreate($parent_id,$label,$branch_type){
	$this->Base->set_level(2);
	$this->check($parent_id,'int');
	$this->check($label);
        $def_lang = $this->Base->acomp('language');
        $def_curr_code = $this->Base->acomp('curr_code');
	$branch_id=$this->treeCreate('companies_tree', $branch_type, $parent_id,$label);
	if( $branch_type=='leaf' ){
	    $this->query("INSERT INTO companies_list SET 
                branch_id=$branch_id,
                company_name='$label',
                language='$def_lang',
                curr_code='$def_curr_code'");
	    return $this->db->insert_id();
	}
	return 0;
    }
    public function companyUpdate($company_id, $field, $value='') {
	$this->Base->set_level(2);
	$this->check($value);
	$fields="company_name/company_jaddress/company_vat_id/company_code/company_vat_licence_id/company_phone/company_agreement_num/
		 company_agreement_date/company_bank_account/company_bank_id/company_bank_name/label/company_person/company_director/
		 company_mobile/company_address/company_email/company_web/company_description/company_acc_list";
	if( strpos($fields, $field)!==false ){
	    return $this->db->query("UPDATE companies_tree LEFT JOIN companies_list USING(branch_id) SET $field='$value' WHERE company_id=$company_id");
	}
	return false;
    }
    public function companyTreeUpdate($branch_id,$field,$value) {
	$this->Base->set_level(2);
	$this->check($branch_id,'int');
	$this->check($field);
	$this->check($value);
	return $this->treeUpdate('companies_tree', $branch_id, $field, $value);
    }
    public function companyTreeDelete( $branch_id ){
	$this->Base->set_level(4);
	$this->check($branch_id,'int');
	$sub_ids=$this->treeGetSub('companies_tree', $branch_id);
	$in=implode(',', $sub_ids);
	$this->query("START TRANSACTION");
	$this->query("DELETE FROM companies_tree WHERE branch_id IN ($in)");
	$this->query("DELETE FROM companies_list WHERE branch_id IN ($in)");
	$deleted=$this->db->affected_rows();
	$this->query("COMMIT");
	return $deleted;
    }

    public function selectPassiveCompany( $company_id ){
	$company=$this->companyGet( $company_id );
	$this->Base->svar('pcomp',$company);
	return $company;
    }
    public function selectActiveCompany( $company_id ){
	$company=$this->companyGet( $company_id );
	$this->Base->svar('acomp',$company);
	return $company;
    }
    
    public function companyPrefsGet(){
	$passive_company_id=$this->Base->pcomp('company_id');
	if( !$passive_company_id ){
	    return null;
	}
	$sql_disct="SELECT
		st.branch_id,
		label,
		discount
	    FROM
		stock_tree st
	    LEFT JOIN
		companies_discounts cd ON st.branch_id=cd.branch_id AND company_id=$passive_company_id
	    WHERE 
		parent_id=0
	    ORDER BY label";
	$sql_other="SELECT
		deferment,
		curr_code,
		manager_id,
		is_supplier,
		company_acc_list,
		language,
		'".$this->Base->pcomp('path')."' path
	    FROM
		companies_list
	    WHERE 
		company_id='$passive_company_id'
	    ";
	return array(
	    'discounts'=>$this->get_list($sql_disct),
	    'other'=>$this->get_row($sql_other)
		);
    }
    public function companyPrefsUpdate( $type, $field, $value ){
	$this->Base->set_level(2);
	switch( $type ){
	    case 'discount':
		return $this->discountUpdate($field,$value);
	    case 'other':
		if( in_array($field, array('deferment','curr_code','manager_id','is_supplier','company_acc_list','language')) ){
		    $passive_company_id = $this->Base->pcomp('company_id');
		    return $this->db->query("UPDATE companies_list SET $field='$value' WHERE company_id=$passive_company_id");
		}
		return false;
	}
    }
    private function discountUpdate( $branch_id, $discount ){
	$passive_company_id = $this->Base->pcomp('company_id');
	if( $discount==1 ){/*Discount is zero so lets delete it*/
	    $this->db->query("DELETE FROM companies_discounts WHERE branch_id=$branch_id AND company_id=$passive_company_id");
	} else {
	    $this->db->query("REPLACE INTO companies_discounts SET company_id=$passive_company_id, branch_id=$branch_id, discount=$discount");
	}
	return true;
    }
}
