<?
require_once('abstractitem.php');

//����������� �������
class MissionExpItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='mission_expenses';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>