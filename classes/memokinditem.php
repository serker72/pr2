<?
require_once('abstractitem.php');

//����������� �������
class MemoKindItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='memo_kind';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>