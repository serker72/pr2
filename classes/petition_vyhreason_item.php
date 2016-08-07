<?
require_once('abstractitem.php');

//причина работы в выходные
class PetitionVyhReasonItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='petition_vyh_reason';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>