<?
require_once('notesgroup.php');

// ������ ���������� ��
class AppContractNotesGroup extends NotesGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='app_contract_notes';
		$this->pagename='view.php';		
		$this->subkeyname='app_contract_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>