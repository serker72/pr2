<?
require_once('abstractitem.php');

//����������� �������
class TaskStatusItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='task_status';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>