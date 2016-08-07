<?
require_once('abstractitem.php');


//абстрактный элемент
class TaskSupplierItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='task_supplier';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='task_id';
		
	}
	
	
	
	
	
	
	
}
?>