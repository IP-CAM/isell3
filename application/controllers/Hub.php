<?php
class Hub extends CI_Controller{

    public function index(){
	include "index.html";
    }
    
    public function on( $model, $method ){
	$method_args = array_slice(func_get_args(), 2);
	if( $model ){
	    $this->load->model($model,NULL,true);
	    if( method_exists($this->{$model},$method) ){
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
	$file_name = implode('/',func_get_args());
	include "application/views/$file_name";
	exit;
    }
    
    private function response( $response ){
	$this->output->set_header("Content-type:text/plain;charset=utf8"); 
	$this->output->set_output(json_encode($response,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));	
    }
}
function msg($msg){
    header("X-isell-msg: ".  urlencode($msg));
}