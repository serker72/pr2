<?
require_once('notesitem.php');

//примечание к КП
class AppContractNotesItem extends NotesItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='app_contract_notes';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='app_contract_id';	
	}
	
	
}
?>