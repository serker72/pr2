<?

require_once('petitiongroup.php');
require_once('petitionblink.php');

 

// moi zayavleniya
class PetitionMyGroup extends PetitionGroup {
	public $prefix='_2';
	
	//установка всех имен
	protected function init(){
		$this->tablename='petition';
		$this->pagename='petitions.php';		
		$this->subkeyname='petition_id';	
		$this->vis_name='is_shown';		
		
		$this->_thg=new petitionmyHistoryGroup;
		$this->_tblink=new petitionBlink;
		
	}
	
	public function GainSql($user_id, &$sql, &$sql_count){
		$sql='select t.*,
			k.name as kind_name,
			st.name as status_name,
			u.login as login, u.name_s as name_s, u.position_s as position_s
		from petition as t
			left join petition_kind as k on t.kind_id=k.id
			left join petition_status as st on st.id=t.status_id
			left join user as u on u.id=t.user_id
		
		where t.user_id="'.$user_id.'" 
		
		';
		
		$sql_count='select count(*)
		from petition as t
			left join petition_kind as k on t.kind_id=k.id
			left join petition_status as st on st.id=t.status_id
			left join user as u on u.id=t.user_id
		
		where t.user_id="'.$user_id.'" 
		
		';
		
	}
	
	
}
?>