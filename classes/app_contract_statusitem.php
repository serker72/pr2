<?
require_once('abstractitem.php');

//абстрактный элемент
class AppContractStatusItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='app_contract_status';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>