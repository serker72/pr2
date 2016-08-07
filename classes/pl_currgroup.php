<?

require_once('abstractgroup.php');
require_once('posgroupgroup.php');
require_once('posgroupitem.php');
require_once('positem.php');
require_once('pl_positem.php');

//  группа валют
class PlCurrGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='pl_currency';
		$this->pagename='pricelist.php';		
		$this->subkeyname='position_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>