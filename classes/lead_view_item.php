<?
require_once('abstractitem.php');
 

//просмотр лида
class Lead_ViewItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='lead_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='lead_id';	
	}
	
	 
	
}
?>