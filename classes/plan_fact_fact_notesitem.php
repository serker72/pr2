<?
require_once('notesitem.php');

//абстрактный элемент
class PlanFactFactNotesItem extends NotesItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='plan_fact_fact_notes';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
	
}
?>