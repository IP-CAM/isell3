<?php
require_once('iSellBase.php');
class ProcDialogs extends iSellBase {
	public function ProcDialogs(){
		$this->ProcessorBase(1);
	}
	public function onDefault(){
		$dialog_name=$this->request('dialog_name');
		header('Content-type: text/html; charset=utf-8;');
		if( $dialog_name=='productCard' )
			require_once ('tpl/dialogs/productcard.htm');
		exit;
	}
	public function onGetProductCard(){
		$product_code=$this->request('product_code');
		$parent_id=$this->request('parent_id',1,0);
		
		$this->LoadClass('Dialogs');
		$pdata=$this->Dialogs->getProduct( $product_code, $parent_id );
		$this->response( $pdata );
	}
	public function onSaveProductCard(){
		$product_code=$this->request('product_code');
		$pdata=array();
		$pdata['ru']=$this->request('ru');
		$pdata['ua']=$this->request('ua');
		$pdata['en']=$this->request('en');
		$pdata['new_product_code']=$this->request('new_product_code');
		$pdata['product_uktzet']=$this->request('product_uktzet');
		$pdata['product_unit']=$this->request('product_unit');
		$pdata['product_spack']=$this->request('product_spack',1);
		$pdata['product_bpack']=$this->request('product_bpack',1);
		$pdata['product_weight']=$this->request('product_weight',2);
		$pdata['product_volume']=$this->request('product_volume',2);
		$pdata['is_service']=$this->request('is_service',1);
		
		$pdata['sell']=$this->request('sell',2);
		$pdata['buy']=$this->request('buy',2);
                $pdata['curr_code']=$this->request('curr_code');
		
		$pdata['parent_id']=$this->request('parent_id',1);
		$pdata['product_wrn_quantity']=$this->request('product_wrn_quantity',1);
		$pdata['party_label']=$this->request('party_label');
		
		$this->LoadClass('Dialogs');
		$this->Dialogs->updateProduct( $product_code, $pdata );
	}
	public function onDeleteCode(){
		$product_code=$this->request('product_code');
		$this->LoadClass('Dialogs');
		$ok=$this->Dialogs->deleteProduct($product_code);
		$this->response($ok);
	}
	public function onEventList(){
		$selected_label=$this->request('label',0,'');
		$selected_date=$this->request('date');
		$table_query=$this->get_table_query();
		
		$this->LoadClass('Dialogs');
		$table_data=$this->Dialogs->eventListData( $selected_label, $selected_date, $table_query );
		$table_data['activeDates']=$this->Dialogs->getEventDates();
		$this->response($table_data);
	}
	public function onEventListViewOut(){
		$selected_label=$this->request('label',0,'');
		$selected_date=$this->request('date');
		$table_query=$this->get_table_query();
		$out_type=$this->request('out_type',0,'.html');
		
		$this->LoadClass('Dialogs');
		$view=$this->Dialogs->eventListData( $selected_label, $selected_date, $table_query );
		$view['date']=$selected_date;
		$view['label']=$selected_label;

		$this->LoadClass('FileEngine');
		$this->FileEngine->assign( $view, 'xlsx/TPL_EventList.xlsx' );
		$this->FileEngine->show_controls=true;
		$this->FileEngine->send($out_type);
		exit;
	}
	public function onSaveEvent(){
		$event_id=$this->request('event_id',1);
		$eventObj=array();
		$eventObj['event_date']=$this->request('event_date');
		$eventObj['event_name']=$this->request('event_name');
		$eventObj['event_label']=$this->request('event_label');
		$eventObj['event_target']=$this->request('event_target');
		$eventObj['event_place']=$this->request('event_place');
		$eventObj['event_note']=$this->request('event_note');
		$eventObj['event_descr']=$this->request('event_descr');
		$eventObj['event_is_private']=$this->request('event_is_private',1);
		$this->LoadClass('Dialogs');
		$this->Dialogs->updateEvent( $event_id, $eventObj );
	}
	public function onGetEventLabels(){
		$selected_day=$this->request('selectedDay');
		$this->LoadClass('Dialogs');
		$labels=$this->Dialogs->getEventLabels($selected_day);
		$this->response($labels);
	}
	public function onActiveEventDates(){
		$this->LoadClass('Dialogs');
		$dates=$this->Dialogs->getEventDates();
		$this->response($dates);
	}
}
?>