<?php
class Hub extends CI_Controller{
    private $app_folder="isell/";
    
    public function index(){
	include $this->app_folder."index.html";
    }
    
    public function on(){
	$arguments = func_get_args();
	if( isset($arguments[0]) ){
	    $fn=explode('-',array_shift($arguments));
	    $model_name=$fn[0];
	    $method_name=  isset($fn[1])?$fn[1]:null;
	    
	    //echo "../../{$this->app_folder}$model_name/$model_name";//file_exists("{$this->app_folder}$model_name/$model_name.php") 
	    $this->load->model($model_name,NULL,true);
	    if( method_exists($this->{$model_name},$method_name) ){
		$response=call_user_func_array(array($this->{$model_name}, $method_name),$arguments);
		$this->response($response);
	    }
	    else{
		show_error("iSell3: Such module function '$model_name->$method_name' not found!", 500);
	    }
	}
	else {
	    show_error('iSell3: No further command is set!', 500);
	}
    }
    
    private function response( $response ){
	if( empty($_SERVER['HTTP_X_REQUESTED_WITH']) ){
	    $this->output->set_header("Content-type:text/plain;charset=utf8"); 
	}
	$this->output
		->set_output(json_encode($response,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));	
    }
}
function msg($msg){
    //header('Content-Type: text/plain; charset=utf8');
    header("iSell-Message: ".  urlencode($msg));
}