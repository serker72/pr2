<?
require_once('abstractitem.php');

//������� ������ � ��������
class PetitionVyhReasonItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='petition_vyh_reason';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>