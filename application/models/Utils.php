<?php

class Utils extends CI_Model {

    //////////////////////////////////////
    //FOLOWING FUNCTIONS ARE DEPRECATED
    //////////////////////////////////////
    public function spellAmount($number, $unit = NULL, $return_cents = true) {
	if (!$unit) {
	    $unit[0] = array('копійка', 'копійки', 'копійок');
	    $unit[1] = array('гривня', 'гривні', 'гривень');
	    $unit[2] = array('тисяча', 'тисячи', 'тисяч');
	    $unit[3] = array('мільон', 'мільони', 'мільонів');
	}
	$millions = $this->getNumberPosition($number, 1000000, 100);
	$thousands = $this->getNumberPosition($number, 1000, 100);
	$ones = $this->getNumberPosition($number, 1, 100);
	$cents = $this->getNumberPosition($number * 100, 1, 10);

	$str = $this->spellNumber($millions, $unit[3]) . ' ' . $this->spellNumber($thousands, $unit[2]) . ' ' . $this->spellNumber($ones, $unit[1]) . ' ' . $this->spellNumber($cents, $unit[0], $return_cents);
	$str = trim($str);
	return mb_strtoupper(mb_substr($str, 0, 1, 'utf-8'), 'utf-8') . mb_substr($str, 1, mb_strlen($str) - 1, 'utf-8');
    }

    public function spellNumber($number, $units=null, $ret_number = false) {
	$hundreds_i = $this->getNumberPosition($number, 100, 1);
	$tens_i = $this->getNumberPosition($number, 10, 1);
	$ones_i = $this->getNumberPosition($number, 1, 1);
	if (!($hundreds_i || $tens_i || $ones_i) && !$ret_number){
	    return '';
	}
	if( $units ){
	    if ($ones_i === 1 && $tens_i != 1){
		$unit = $units[0];
	    } else 
	    if ($ones_i > 1 && $ones_i < 5){
		$unit = $units[1];
	    } else {
		$unit = $units[2];
	    }
	    if ($ret_number) {
		if ($number < 10)
		    return "0$number $unit";
		return "$number $unit";
	    }
	} else {
	    $unit='';
	}

	$ones = array("", "одна", "дві", "три", "чотири", "п'ять", "шість", "сім", "вісім", "дев'ять");
	$tens = array("", "десять", "двадцять", "тридцять", "сорок", "п'ятдесят", "шістдесят", "сімдесят", "вісімдесят", "дев'яносто");
	$teens = array("", "одинадцять", "дванадцять", "тринадцять", "чотирнадцять", "п'ятнадцять", "шістнадцять", "сімнадцять", "вісімнадцять", "дев'ятнадцять");
	$hundreds = array("", "сто", "двісті", "триста", "чотириста", "п'ятсот", "шістсот", "сімсот", "вісімсот", "дев'ятсот");

	if ($tens_i == 1){
	    return "$hundreds[$hundreds_i] $teens[$ones_i] $unit";
	}
	else{
	    return "$hundreds[$hundreds_i] $tens[$tens_i] $ones[$ones_i] $unit";
	}
    }

    private function getNumberPosition($number, $position, $range = 1) {//DEPRECATED
	$number-=$position * 10 * $range * floor($number / $position / 10 / $range);
	return floor($number / $position);
    }
    public function getLocalDate($tstamp) {
	$time = strtotime($tstamp);
	$months = array("січня", "лютого", "березня", "квітня", "травня", "червня", "липня", "серпня", "вересня", "жовтня", "листопада", "грудня");
	return date('d', $time) . ' ' . $months[date('m', $time) - 1] . ' ' . date('Y', $time);
    }
    ///////////////////////////////////////////
    //END OF DEPRECATED FUNCTIONS
    ///////////////////////////////////////////
    
    
    
    
    
    
    
    /////////////////////////////
    //EMAIL FUNCTIONS
    /////////////////////////////
    private function sendEmail($to,$subject,$body,$file=null){
        $this->Base->set_level(1);
        $this->load->library('email');
        $this->email->initialize([
            'useragent'=>'iSell',
            'protocol'=>'smtp',
            'charset'=>'utf8',
	    'smtp_timeout'=>10,
            'smtp_host'=>BAY_SMTP_SERVER,
            'smtp_user'=>BAY_SMTP_USER,
            'smtp_pass'=>BAY_SMTP_PASS,
	    'smtp_port'=>BAY_SMTP_PORT,
	    'smtp_crypto'=>BAY_SMTP_CRYPTO
        ]);
	$this->email->set_newline("\r\n");
        $this->email->from(BAY_SMTP_SENDER_MAIL,BAY_SMTP_SENDER_NAME);
        $this->email->to($to);
        //$this->email->cc(BAY_SMTP_SENDER_MAIL);
        $this->email->subject($subject);
        $this->email->message($body);
        if( $file ){
	    $this->email->attach($file['data'], 'attachment', $file['name'], $file['mime']);
	}
        $ok=$this->email->send(false);
        if( !$ok ){
            $err=$this->email->print_debugger(['headers', 'subject', 'body']);
            $this->Base->msg($err);
        }
        return $ok;
    }
    public function postEmail(){
	$to=$this->input->get_post('to');
	$subject=$this->input->get_post('subject');
	$body=$this->input->get_post('body');
	$dump_id=$this->input->get_post('dump_id');
        $out_type=$this->input->get_post('out_type');
        $send_file=$this->input->get_post('send_file');
	$file=$send_file?$this->generateFile($dump_id,$out_type,$subject):null;
	return $this->sendEmail($to, $subject, $body, $file);
    }
    private $mimes=[
        '.html'=>'text/html',
        '.xls'=>'application/vnd.ms-excel',
        '.xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        '.pdf'=>'application/pdf',
        '.xml'=>'text/xml'
        ];
    private function generateFile($dump_id,$out_type='.xlsx',$subject='file'){
        $ViewManager=$this->Base->load_model('ViewManager');
	$file_data=$ViewManager->getFile($dump_id,$out_type);
	if( $file_data ){
	    return [
		'data'=>$file_data,
		'mime'=>$this->mimes[$out_type],
		'name'=>str_replace(' ', '_', $subject).$out_type
	    ];
	}
	$this->Base->response(0);
        return null;
    }
    /////////////////////////////
    //TREE MAINTAINANCE FUNCTIONS
    /////////////////////////////
    public function treeRecalculate(){
        foreach(['acc_tree','companies_tree','stock_tree'] as $table){
            $this->treePathRecalculate($table, 0);
            $this->treeTopRecalculate($table);
        }
    }
    private function treeTopRecalculate($table){
        $res = $this->db->query("SELECT branch_id,path FROM $table WHERE parent_id=0");
	foreach ($res->result() as $row) {
            $this->db->query("UPDATE $table SET top_id='{$row->branch_id}' WHERE path LIKE '{$row->path}%'");
	}
	$res->free_result();        
    }
    private function treePathRecalculate( $table, $parent_id = 0) {
	$where="";
	if( $parent_id!==null ){
	    $where="parent_id=$parent_id";
	}
	$res = $this->db->query("SELECT * FROM $table WHERE $where");
	foreach ($res->result() as $row) {
	    $this->treeUpdatePath($table, $row->branch_id);
            $this->treePathRecalculate($table, $row->branch_id);
	}
	$res->free_result();
    }
    private function treeUpdatePath($table, $branch_id) {
	$this->db->query(
		"SELECT @old_path:=COALESCE(t1.path, ''),@new_path:=CONCAT(COALESCE(t2.path, '/'), t1.label, '/')
		FROM (SELECT * FROM $table) t1
			LEFT JOIN
		    (SELECT * FROM $table) t2 ON t1.parent_id = t2.branch_id 
		WHERE
		    t1.branch_id = $branch_id");
	$this->db->query(
		"UPDATE $table 
		SET 
		    path = IF(@old_path,REPLACE(path, @old_path, @new_path),@new_path)
		WHERE
		    IF(@old_path,path LIKE CONCAT(@old_path, '%'),branch_id=$branch_id)");
    }
    /////////////////////////////
    //STOCK MAINTAINANCE FUNCTIONS
    /////////////////////////////
    public function stockQtyRecalculate(){
	$sql="
	    UPDATE 
		stock_entries se
	    SET 
		se.product_quantity = 
		(SELECT 
			SUM(IF(doc_type = 2,de.product_quantity,- de.product_quantity))
		    FROM
			document_entries de
			    JOIN
			document_list dl USING (doc_id)     
		    WHERE
			de.product_code=se.product_code AND dl.is_commited = 1 AND dl.notcount = 0
		GROUP BY product_code)";
	$this->db->query($sql);
	return $this->db->affected_rows();
    }
    public function stockSelfRecalculate(){
	$idate='2015-12-01'.' 00:00:00';
	$fdate='2015-12-31'.' 23:59:59';
	$this->query("DROP TEMPORARY TABLE IF EXISTS tmp_self_recalculate;");
	$main_table_sql="CREATE TEMPORARY TABLE tmp_self_recalculate ( INDEX(product_code) ) ENGINE=MyISAM AS (
	    SELECT
		product_code,
		SUM( IF(cstamp<'$idate',IF(doc_type = 2,de.product_quantity,- de.product_quantity),0) ) idate_quantity,
		SUM( IF(cstamp<'$fdate',IF(doc_type = 2,de.product_quantity,- de.product_quantity),0) ) fdate_quantity

	    FROM
		document_entries de
		    JOIN
		document_list dl USING (doc_id)     
	    WHERE
		dl.is_commited = 1 AND dl.notcount = 0
	    GROUP BY product_code
	)";
	
    }
}
