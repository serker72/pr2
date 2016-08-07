<?
require_once('abstractgroup.php');

// абстрактная группа
class ShIPosPMGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sh_i_position_pm';
		$this->pagename='view.php';		
		$this->subkeyname='sh_i_position_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>