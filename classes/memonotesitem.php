<?
require_once('notesitem.php');

//примечания к СЗ
class MemoNotesItem extends NotesItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='memo_notes';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
	
}
?>