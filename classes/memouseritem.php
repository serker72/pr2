<?
require_once('abstractitem.php');

//����������� �������
class MemoUserItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='memo_user';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='memo_id';
		
	}
	
	
	
	
}
?>