<?
require_once('abstractitem.php');

//абстрактный элемент
class MemoUserItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='memo_user';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='memo_id';
		
	}
	
	
	
	
}
?>