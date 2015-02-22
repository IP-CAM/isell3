<?php
class HubBase extends CI_Controller{
    private $rtype='OK';
    private $msg='';
    function HubBase(){
	$this->Session();
	parent::__construct();
    }
    private function Session() {
	session_set_cookie_params(36000, '/');
	session_name('baycikSid' . BAY_COOKIE_NAME);
	session_start();
    }
    
    public function acomp($name){
	$acomp=$this->svar('acomp');

	return isset($acomp[$name])?$acomp[$name]:NULL;
    }
    
    public function pcomp($name){
	$pcomp=$this->svar('pcomp');
	return isset($pcomp->$name)?$pcomp->$name:NULL;
    }
    
    public function svar($name, $value = NULL) {
	if (isset($value)) {
	    $_SESSION[$name] = $value;
	}
	return isset($_SESSION[$name])?$_SESSION[$name]:NULL;
    }
    public function load_model( $name ){
	$this->load->model($name);
	$this->{$name}->Base=$this;
    }
    
    public function set_level($allowed_level) {
	if ($this->svar('user_level') < $allowed_level) {
	    if ($this->svar('user_level') == 0) {
		msg("Текущий уровень <b>" . $this->level_names[$this->svar('user_level') * 1] . "</b><br>");
		msg("Необходим уровень доступа <b>" . $this->level_names[$allowed_level] . "</b>");
		$this->kick_out();
	    } else {
		$this->response_wrn("Текущий уровень '" . $this->level_names[$this->svar('user_level') * 1] . "'\nНеобходим мин. уровень доступа '" . $this->level_names[$allowed_level] . "'");
	    }
	}
    }
    private function kick_out() {
	include 'views/dialog/loginform.html';
	$this->response_dialog();
    }
    
    protected function response_dialog($msg) {
	$this->rtype = 'dialog';
	$this->response($msg);
    }
    
    public function msg($msg) {
	$this->msg.="$msg\n";
    }

    public function db_msg(){
	switch( $this->db->_error_number() ){
	    case 1451:
		$this->msg('Элемент ипользуется, поэтому не может быть изменен или удален!');
		break;
	    default:
		header("X-isell-type:error");
		show_error($this->msg."<br>".$this->db->_error_message()."<pre>".$this->db->last_query()."<pre>", 500);
		break;
	}
    }
    
    
    public function response( $response ){
	$this->output->set_header("X-isell-msg:".urlencode($this->msg));
	$this->output->set_header("X-isell-type:".$this->rtype);
	
	if( is_array($response) || is_object($response) ){
	    $this->output->set_header("Content-type:text/plain;charset=utf8"); 
	    $this->output->set_output(json_encode($response,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));	    
	}
	else{
	    $this->output->set_header("Content-type:text/html;charset=utf8"); 
	    $this->output->set_output($response);	    
	}
    }

}