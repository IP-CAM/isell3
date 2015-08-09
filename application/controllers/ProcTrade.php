<?php
include 'iSellBase.php';
class ProcTrade extends iSellBase{
    
    public function index(){
	header("X-isell-type:OK");
	include 'views/trade/trade_main.html';
    }
}
