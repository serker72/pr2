<?
require_once('abstractitem.php');

require_once('missionitem.php');
/*require_once('user_s_item.php');
require_once('user_d_item.php');
require_once('messageitem.php');
*/
//require_once('specdelgroup.php');

//абстрактный элемент
class MissionHistoryItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='mission_history';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mission_id';	
	}
	
	public function Add($params){
		$code=AbstractItem::Add($params);
		
		
	
		return $code;
	}
	
}
?>