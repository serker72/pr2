<?
 

//абстрактный элемент
class PlRuleItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='pl_rule';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	//удалить
	public function Del($id){
		
		 
		
		
		$query = 'delete from pl_rule_item where rule_id='.$id.';';
		$it=new nonSet($query);
		
		
		
		parent::Del($id);
	}	
	
}
?>