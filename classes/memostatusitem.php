<?
require_once('abstractitem.php');

//����������� �������
class MemoStatusItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='memo_status';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>