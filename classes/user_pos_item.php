<?
require_once('abstractitem.php');

//����������� �������
class UserPosItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='user_position';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>