<?
require_once('abstractitem.php');

//������� �����������
class UserMainDepItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='user_main_department';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>