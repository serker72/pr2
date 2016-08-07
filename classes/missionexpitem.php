<?
require_once('abstractitem.php');

//абстрактный элемент
class MissionExpItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='mission_expenses';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>