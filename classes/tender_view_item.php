<?
require_once('abstractitem.php');
 

//просмотр тендера
class Tender_ViewItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='tender_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='tender_id';	
	}
	
	 
	
}
?>