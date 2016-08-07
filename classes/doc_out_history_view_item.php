<?
require_once('abstractitem.php');
 

//просмотр комментария к исх док-ту
class DocOut_HistoryViewItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='doc_out_history_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sched_id';	
	}
	
	 
	
}
?>