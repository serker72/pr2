<?
require_once('notesgroup.php');

// ����������� ������
class ShINotesGroup extends NotesGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='sh_i_notes';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>