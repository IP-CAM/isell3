<?php
require_once 'iSellBase.php';
class LoginProc extends iSellBase{
    public function LoginProc(){
        $this->ProcessorBase();
    }
    public function onLogin(){
        $user_login=$this->request('user_login');
        $user_pass=$this->request('user_pass');

        if( !$this->login($user_login,$user_pass) ){
            $this->response_wrn( "Неверый пароль или логин!" );	
        }
    }
    public function onLogout(){ 
        $kick=$this->request('kick',1);
        $this->logout($kick);
    }
    public function onUserCheck(){ 
        $resp=array();
        $resp['user_id']=$this->svar('user_id');
        $resp['user_login']=$this->svar('user_login');
        $resp['user_level']=$this->svar('user_level');
        $resp['user_level_name']=$this->svar('user_level_name');
        if( $resp['user_id'] )/*Only if logged in*/
            $resp['active_company_name']=$this->acomp('company_name');
        $this->response( $resp );
    }
    public function onAutorize(){ 
        if( !$this->svar('user_id') )
            $this->set_level(1);
    }
}
?>