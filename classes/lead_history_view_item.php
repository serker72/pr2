<?
require_once('abstractitem.php');
 

//просмотр комментария к лиду
class Lead_HistoryViewItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='lead_history_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sched_id';	
	}
	
	 
	
}
?>