<?

require_once('memogroup.php');
require_once('memoblink.php');

require_once('memoincominghistorygroup.php');

// входящие заявки
class MemoIncomingGroup extends MemoGroup {
	public $prefix='_1';
	
	//установка всех имен
	protected function init(){
		$this->tablename='memo';
		$this->pagename='memos.php';		
		$this->subkeyname='order_id';	
		$this->vis_name='is_shown';		
		
		$this->_thg=new MemoIncomingHistoryGroup;
		$this->_tblink=new MemoBlink;
		
	}
	
	
	
	public function GainSql($user_id, &$sql, &$sql_count){
		
		//добавить доп.условие:
		//в адресатах либо:
		//	- я утвердил
		//  - утвердил предыдущий передо мной сотр-к и нет is_not_to_next
		/*
		$sql='select t.*,
		
		 mu.user_id,  	mu.ord as ord, 	mu.is_confirmed, 	mu.confirm_pdate, 	mu.confirm_id, 	mu.is_not_to_next, 	mu.not_to_next_pdate, 	mu.not_to_next_id,
		 
			k.name as kind_name,
			st.name as status_name,
			u.login as login, u.name_s as name_s, u.position_s as position_s,
			
			c.name_s as c_name_s, c.login as c_login, c.position_s as c_position_s,
			n.name_s as n_name_s, n.login as n_login, n.position_s as n_position_s,
			
			conf.ord as conf_ord, conf.is_confirmed as conf_is_confirmed, conf.user_id as conf_user_id,
			unconf.ord as unconf_ord, unconf.is_confirmed as unconf_is_confirmed, unconf.user_id as unconf_user_id
		
		from memo as t
			inner join memo_user as mu on t.id=mu.memo_id and mu.user_id="'.$user_id.'"
			
			left join memo_kind as k on t.kind_id=k.id
			left join memo_status as st on st.id=t.status_id
			left join user as u on u.id=t.user_id
			
			
			left join user as c on c.id=mu.confirm_id
			left join user as n on n.id=mu.not_to_next_id
		
			left join memo_user as conf on conf.memo_id=t.id and conf.is_confirmed=1  
			left join memo_user as unconf on unconf.memo_id=t.id and unconf.is_confirmed=0 and unconf.ord=conf.ord+1   
			
		where t.id<>0
		and (
				(conf.user_id="'.$user_id.'")  
				or
				(unconf.user_id="'.$user_id.'")  
			)
		';
		
		*/
		
		
		
		$sql='select t.*,
		
		 mu.user_id,  	mu.ord as ord, 	mu.is_confirmed, 	mu.confirm_pdate, 	mu.confirm_id, 	mu.is_not_to_next, 	mu.not_to_next_pdate, 	mu.not_to_next_id,
		 
			k.name as kind_name,
			st.name as status_name,
			u.login as login, u.name_s as name_s, u.position_s as position_s,
			
			c.name_s as c_name_s, c.login as c_login, c.position_s as c_position_s,
			n.name_s as n_name_s, n.login as n_login, n.position_s as n_position_s,			
			 
			conf.ord as conf_ord, conf.is_confirmed as conf_is_confirmed, conf.user_id as conf_user_id,
			unconf.ord as unconf_ord, unconf.is_confirmed as unconf_is_confirmed, unconf.user_id as unconf_user_id,
			
			nonconf.ord as nonconf_ord, nonconf.is_confirmed as nonconf_is_confirmed, nonconf.user_id as nonconf_user_id
			
		from memo as t
			inner join memo_user as mu on t.id=mu.memo_id and mu.user_id="'.$user_id.'"
			
			left join memo_kind as k on t.kind_id=k.id
			left join memo_status as st on st.id=t.status_id
			left join user as u on u.id=t.user_id
			
			
			left join user as c on c.id=mu.confirm_id
			left join user as n on n.id=mu.not_to_next_id
			
			left join memo_user as conf on conf.memo_id=t.id and conf.is_confirmed=1 /*and conf.user_id="'.$user_id.'"  */
			left join memo_user as unconf on unconf.memo_id=t.id and unconf.is_confirmed=0 and unconf.ord=conf.ord+1  and unconf.user_id="'.$user_id.'" 
			
			left join memo_user as nonconf on nonconf.memo_id=t.id and nonconf.is_confirmed=0 and nonconf.ord=0  and nonconf.user_id="'.$user_id.'" 
			
			
			 
		where t.id<>0
		 and (
		 		(conf.user_id="'.$user_id.'" ) /*я утв-л*/ 
				or
				(unconf.user_id="'.$user_id.'" and t.id not in(select distinct memo_id from memo_user where memo_id=t.id and is_confirmed=1 and is_not_to_next=1)) /*были утв-ия и среди них не было is_not_to_next	, моя очередь*/
				or
				(nonconf.user_id="'.$user_id.'") /*не было ни одного утв-ия и я 1й в списке*/
				
		 	 )
		';
		
		
		
		
		//echo htmlspecialchars($sql).'<br>';
		
		$sql_count='select count(*)
		from memo as t
			inner join memo_user as mu on t.id=mu.memo_id and mu.user_id="'.$user_id.'"
			
			left join memo_kind as k on t.kind_id=k.id
			left join memo_status as st on st.id=t.status_id
			left join user as u on u.id=t.user_id
			
			
			left join user as c on c.id=mu.confirm_id
			left join user as n on n.id=mu.not_to_next_id
			
			left join memo_user as conf on conf.memo_id=t.id and conf.is_confirmed=1 /*and conf.user_id="'.$user_id.'"  */
			left join memo_user as unconf on unconf.memo_id=t.id and unconf.is_confirmed=0 and unconf.ord=conf.ord+1  and unconf.user_id="'.$user_id.'" 
			
			left join memo_user as nonconf on nonconf.memo_id=t.id and nonconf.is_confirmed=0 and nonconf.ord=0  and nonconf.user_id="'.$user_id.'" 
			
		
		
		where t.id<>0
		 and (
		 		(conf.user_id="'.$user_id.'" ) /*я утв-л*/ 
				or
				(unconf.user_id="'.$user_id.'" and t.id not in(select distinct memo_id from memo_user where memo_id=t.id and is_confirmed=1 and is_not_to_next=1)) /*были утв-ия и среди них не было is_not_to_next	, моя очередь*/
				or
				(nonconf.user_id="'.$user_id.'") /*не было ни одного утв-ия и я 1й в списке*/
				
		 	 )
		';
		
	}
	
	
}
?>