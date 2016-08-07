<?
require_once('abstractitem.php');

// элемент отдел
class UserDepItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='user_department';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>