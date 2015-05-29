<?php
require_once 'Catalog.php';
class Chat extends Catalog{
    public function getUserList(){
        $sql="SELECT 
                user_id,
                user_login
            FROM
                " . BAY_DB_MAIN . ".user_list";
        return $this->get_list($sql);
    }
    public function sendRecieve( $counterpart='all', $msg=null ){
        $counterpart=$this->db->escape($counterpart);
        if( $counterpart && $msg ){
            $msg=$this->db->escape(rawurldecode($msg));
            $this->addMessage($counterpart, $msg);
        }
        return $this->getMessages($counterpart);
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
    private function getMessages( $he ){
        $me = $this->db->escape($this->Base->svar('user_login'));
        $sql="SELECT
            event_list.*,
            event_target=$me is_me,
            event_target reciever,
            user_login sender
                FROM
                    event_list
                        JOIN
                    " . BAY_DB_MAIN . ".user_list ON event_user_id=user_id
                WHERE 
                    event_label='Chat' 
                HAVING
                    IF($he='all',
                        sender=$me OR reciever=$me,
                        sender=$me AND reciever=$he OR sender=$he AND reciever=$me)
                ORDER BY event_date";
        return $this->get_list($sql);
    }
}
