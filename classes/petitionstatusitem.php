<?
require_once('abstractitem.php');

//����������� �������
class PetitionStatusItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='petition_status';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>