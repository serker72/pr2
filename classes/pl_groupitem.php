<?
require_once('abstractitem.php');
require_once('posgroupitem.php');
require_once('pl_groupgroup.php');

//группа опций итем
class PlGroupItem extends PosGroupItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='pl_group';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		
		$this->keyname='pl_group_id';	
		$this->subkeyname='parent_group_id';
		
		$this->group=new PlGroupGroup;	
	}
	
	
	
}
?>