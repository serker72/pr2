<?
require_once('notesitem.php');

//����������� �������
class ShINotesItem extends NotesItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='sh_i_notes';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
	
}
?>