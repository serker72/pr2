<?

require_once('posgroupitem.php');


class RoundDefine{

	protected static $group_id;
	protected static $digits;
	protected $_group_item;
	
	function __construct(){
		$this->_group_item=new PosGroupItem;
	}
	
	
	public function DefineDigits($group_id){
			
		if(self::$group_id!=$group_id){
			self::$group_id=$group_id;	
			//����� �� ��� ���, ���� $parent_group_id 	=0. ����� ��==0, �� ������� round_digits
			
			$group_item=$this->_group_item->GetItemById($group_id);
			if($group_item['parent_group_id']!=0){
				
				$group_item=$this->_group_item->GetItemById($group_item['parent_group_id']);
				 
				if($group_item['parent_group_id']!=0){
					
					
					$group_item=$this->_group_item->GetItemById($group_item['parent_group_id']);
					self::$digits=(int)$group_item['round_digits'];
						
					
				}else{
					 self::$digits=(int)$group_item['round_digits'];
					 //echo (int)$group_item['round_digits'];
					 
					 
				}
					
			}else{
				 self::$digits=(int)$group_item['round_digits'];
				 
			}
			
			
			
			
			
		}
		
		
		
		return self::$digits;
	}
	


}
?>