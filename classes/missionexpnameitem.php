<?
require_once('abstractitem.php');

//абстрактный элемент
class MissionExpNameItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='mission_expenses_name';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>