<?
require_once('abstractitem.php');

//����������� �������
class PetitionUserPresetItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='petition_user_preset';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='petition_id';
		
	}
	
	
	
	
}
?>