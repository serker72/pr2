<?
require_once('abstractitem.php');

// ������� �����
class UserDepItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='user_department';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>