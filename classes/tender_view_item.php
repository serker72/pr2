<?
require_once('abstractitem.php');
 

//�������� �������
class Tender_ViewItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='tender_id';	
	}
	
	 
	
}
?>