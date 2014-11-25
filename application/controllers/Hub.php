<?php
class Hub extends CI_Controller{
    private $app_folder="isell/";
    
    public function index(){
	include $this->app_folder."index.html";
	
    }
    
    public function on(){
	$arguments = func_get_args();
	if( $arguments[0] ){
	    $fn=explode('-',array_shift($arguments));
	    $model_name=$fn[0];
	    $this->load->model("../../{$this->app_folder}$model_name/$model_name",NULL,true);//file_exists("{$this->app_folder}$model_name/$model_name.php") 
	    if( method_exists($this->{$model_name},$fn[1]) ){
		$response=call_user_func_array(array($this->{$model_name}, $fn[1]),$arguments);
		$this->response($response);
	    }
	    else{
		show_error("iSell: Such module function '$model_name->$fn[1]' not found!", 500);
	    }
	}
	else {
	    echo 2222;
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