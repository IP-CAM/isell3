<?php

class io extends CI_Model{
    public function out( $data, $type='json' ){
	header('Content-type: text/plain;charset=utf-8');
	switch ($type) {
	  case 'json':
	      $this->output
		    ->set_output(json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
	  break;
	};
	//exit;
    }
}