<?
require_once('notesitem.php');

//примечание к заявлению
class PetitionNotesItem extends NotesItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='petition_notes';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
	
}
?>