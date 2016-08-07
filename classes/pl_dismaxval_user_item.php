<?
require_once('abstractitem.php');
require_once('positem.php');

//элемент макс скидка по товару
class PlDisMaxValUserItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='pl_discount_user_maxval';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='pl_position_id';	
	}
	
	
	
	
}
?>