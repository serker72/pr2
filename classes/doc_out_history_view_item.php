<?
require_once('abstractitem.php');
 

//�������� ����������� � ��� ���-��
class DocOut_HistoryViewItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_out_history_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sched_id';	
	}
	
	 
	
}
?>