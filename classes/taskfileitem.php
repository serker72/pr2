<?
require_once('abstractfileitem.php');

//����������� ����
class TaskFileItem extends AbstractFileItem{
	
	//��������� ���� ����
	protected function init($id){
		$this->tablename='task_file';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='history_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/task/';	
	}
	
	
	
	
}
?>