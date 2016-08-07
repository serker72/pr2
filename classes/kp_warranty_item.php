<?
require_once('abstractitem.php');

//абстрактный элемент
class KpWarrantyItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_warranty';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>