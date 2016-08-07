<?
require_once('abstractitem.php');
 

//просмотр комментария к вх док-ту
class DocIn_HistoryViewItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='doc_in_history_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sched_id';	
	}
	
	 
	
}
?>