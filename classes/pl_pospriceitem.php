<?
require_once('abstractitem.php');
require_once('positem.php');

//элемент цена товара прайс-листа в валюте
class PlPositionPriceItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='pl_position_price';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='position_id';	
	}
	
	
	
	
}
?>