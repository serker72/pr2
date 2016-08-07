<?
require_once('abstractitem.php');
require_once('lead.class.php');
require_once('actionlog.php');
require_once('docstatusitem.php');
require_once('useritem.php'); 
 
//запись ленты вх док-та
class DocIn_HistoryItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='doc_in_history';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sched_id';	
	}
	
	
	
	 
}
?>