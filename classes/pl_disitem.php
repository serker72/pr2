<?
require_once('abstractitem.php');
require_once('positem.php');

//элемент вид скидки
class PlDisItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='pl_discount';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='position_id';	
	}
	
	
	
	
}
?>