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
	if (method_exists($this, 'initApplication')) {
	    $this->initApplication();
	}
	//$this->DataBase();
    }
    
    public function get_list( $sql ){
	$list=array();
	$query=$this->db->query($sql);
	if( !$query || $query->num_rows()==0 ){
	    return 0;
	}
	foreach( $query->result() as $row ){
	    $list[]=$row;
	}
	$query->free_result();
	return $list;
    }
    
    
    public function svar($name, $value = NULL) {
	if (isset($value)) {
	    $_SESSION[$name] = $value;
	}
	return isset($_SESSION[$name])?$_SESSION[$name]:'';
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

    public function response( $response ){
	$this->output->set_header("X-isell-msg:".urlencode($this->msg));
	$this->output->set_header("X-isell-type:".$this->rtype);
	
	if(is_array($response) ){
	    $this->output->set_header("Content-type:text/plain;charset=utf8"); 
	    $this->output->set_output(json_encode($response,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));	    
	}
	else{
	    $this->output->set_header("Content-type:text/html;charset=utf8"); 
	    $this->output->set_output($response);	    
	}
    }

}