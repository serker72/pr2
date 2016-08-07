<?

require_once('petitiongroup.php');
require_once('petitionblink.php');
 

// vse zayavleniya
class PetitionAllGroup extends PetitionGroup {
	public $prefix='_3';
	
	//установка всех имен
	protected function init(){
		$this->tablename='petition';
		$this->pagename='petitions.php';		
		$this->subkeyname='order_id';	
		$this->vis_name='is_shown';		
		
//		$this->_thg=new petitionAllHistoryGroup;
		$this->_tblink=new petitionBlink;
		$this->_item=new PetitionItem;
		
		$this->_view=new Petition_ViewGroup;
			$this->new_list=NULL; 
		
	}
	
	public function GainSql($user_id, &$sql, &$sql_count){
		$sql='select distinct t.*,
			k.name as kind_name,
			st.name as status_name,
			u.login as login, u.name_s as name_s, u.position_s as position_s,
			u1.login as confirmed_login, u1.name_s as confirmed_name,
			u2.login as confirmed_ruk_login, u2.name_s as confirmed_ruk_name, 
			u3.login as confirmed_dir_login, u3.name_s as confirmed_dir_name, 
			crea.name_s as crea_name_s
			
			
			
		from petition as t
			left join petition_kind as k on t.kind_id=k.id
			left join document_status as st on st.id=t.status_id
			left join user as u on u.id=t.manager_id
			left join user as crea on crea.id=t.user_id
			left join user as u1 on u1.id=t.user_confirm_id
			left join user as u2 on u2.id=t.user_ruk_id
			left join user as u3 on u3.id=t.user_dir_id
			
			left join  petition_vyh_date as vd on vd.petition_id=t.id
		
		where t.id<>0 
		
		';
		
		$sql_count='select count(*)
		from petition as t
			left join petition_kind as k on t.kind_id=k.id
			left join document_status as st on st.id=t.status_id
			left join user as u on u.id=t.manager_id
			left join user as crea on crea.id=t.user_id
			left join user as u1 on u1.id=t.user_confirm_id
			left join user as u2 on u2.id=t.user_ruk_id
			left join user as u3 on u3.id=t.user_dir_id
			left join  petition_vyh_date as vd on vd.petition_id=t.id
		
		where t.id<>0  
		
		';
		
	}
	
	
}
?>