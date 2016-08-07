<?
require_once('abstractitem.php');
require_once('user_pos_item.php');
require_once('user_dep_item.php');

//юзер итем
class UserItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='user';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_active';	
		//$this->subkeyname='mid';	
	}
	
	public function GetItemById($id,$mode=0){
		$res=AbstractItem::GetItemById($id, $mode);	
		
		//подставим должность
		if($res!==false){
			 
			$_up=new UserPosItem; $_ud=new UserDepItem;
			 
			$up=$_up->GetItemById($res['position_id']);
			$ud=$_ud->GetItemById($res['department_id']);
			 
			 
			 
			$res['position_name']=$up['name'];	
			$res['position_s']=$up['name'];	
			
			$res['department_name']=$ud['name'];	
		}
		
		return $res;
	}
	
	
	
	 
	
}
?>