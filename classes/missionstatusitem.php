<?
require_once('abstractitem.php');

//����������� �������
class MissionStatusItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='mission_status';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>