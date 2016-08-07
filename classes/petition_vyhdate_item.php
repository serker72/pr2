<?
require_once('abstractitem.php');

//абстрактный элемент
class PetitionVyhDateItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='petition_vyh_date';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='petition_id';
		
	}
	
	
	
	 
}
?>