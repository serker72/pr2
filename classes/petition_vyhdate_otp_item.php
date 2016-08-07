<?
require_once('abstractitem.php');

//абстрактный элемент
class PetitionVyhDateOtpItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='petition_vyh_date_otp';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='petition_id';
		
	}
	
	
	
	 
}
?>