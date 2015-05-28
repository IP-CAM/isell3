<?php
require_once 'Catalog.php';
class Chat extends Catalog{
    public function getUserList(){
        $sql="SELECT 
                user_id,
                user_login
            FROM
                user_list";
        return $this->get_list($sql);
    }
    public function sendRecieve( $counterpart='all', $msg=null ){
        if( $counterpart && $msg ){
            $counterpart=$this->db->escape($counterpart);
            $msg=$this->db->escape($msg);
            $this->addMessage($counterpart, $msg);
        }
        return $this->getMessages();
    }
    private function addMessage( $counterpart, $msg ){
        $user_id = $this->Base->svar('user_id');
        $sql="INSERT INTO
                event_list
              SET 
                event_label='Chat',
                event_date=NOW(),
                event_user_id=$user_id,
                event_target=$counterpart,
                event_descr=$msg,
                event_is_private=1";
        $this->db->query($sql);
    }
    private function getMessages(){
        $user_login = $this->Base->svar('user_login');
        $user_id = $this->Base->svar('user_id');
        $sql="SELECT *,IF(event_target='$user_login','left','right') me FROM event_list WHERE event_label='Chat' AND (event_user_id='$user_id' OR event_target='$user_login')";
        return $this->get_list($sql);
    }
}
