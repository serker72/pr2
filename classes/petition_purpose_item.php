<?
require_once('abstractitem.php');

// 
class PetitionPurposeItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='petition_purpose';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>