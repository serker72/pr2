<?
require_once('abstractitem.php');

//����������� �������
class MissionExpNameItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='mission_expenses_name';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>