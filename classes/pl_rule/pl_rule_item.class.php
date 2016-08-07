<?
 

//абстрактный элемент
class PlRuleItemItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='pl_rule_item';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>