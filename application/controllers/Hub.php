<?php
include 'HubBase.php';
class Hub extends HubBase{

    public function index(){
	include "index.html";
    }
    
    public function on( $model, $method ){
	$method_args = array_slice(func_get_args(), 2);
	if( $model ){
	    $this->load->model($model,NULL,true);
	    if( method_exists($this->{$model},$method) ){
		$this->{$model}->Base=$this;
		$response=call_user_func_array(array($this->{$model}, $method),$method_args);
		$this->response($response);
	    }
	    else{
		show_error("X-isell-error: Such module function '$model->$method' not found!", 500);
	    }
	}
	else {
	    show_error('X-isell-error: Model is not set!', 500);
	}
    }
    
    public function page(){
	$file_name = "application/views/".implode('/',func_get_args());
	if( file_exists($file_name) ){
	    include $file_name;
	}
	else{
	    show_error('X-isell-error: File not found!', 404);
	}
	exit;
    }
    
    
    public function db_msg(){
	switch( $this->db->_error_number() ){
	    case 1451:
		$this->msg('Элемент ипользуется, поэтому не может быть изменен или удален!');
		break;
	    
	    
	    default:
		$this->msg($this->db->_error_message());
		break;
	}
    }
    
}