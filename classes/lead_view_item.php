<?
require_once('abstractitem.php');
 

//�������� ����
class Lead_ViewItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='lead_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='lead_id';	
	}
	
	 
	
}
?>