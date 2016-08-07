<?

require_once('abstractgroup.php');

require_once('memohistorygroup.php');

require_once('memofilegroup.php');
require_once('memoitem.php');

// группа событий входящих задач
class MemoIncomingHistoryGroup extends MemoHistoryGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='memo_history';
		$this->pagename='memohistory.php';		
		$this->subkeyname='memo_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//подсчет новых историй данного заказа
	public function CountNew($order_id,$user_id){
		//$sql='select * from memo where id="'.$order_id.'" and id in(select memo_id from memo_user where user_id="'.$user_id.'")';
		
		
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
			
			
			 
		where t.id="'.$order_id.'"
		 and (
		 		(conf.user_id="'.$user_id.'" ) /*я утв-л*/ 
				or
				(unconf.user_id="'.$user_id.'" and t.id not in(select distinct memo_id from memo_user where memo_id=t.id and is_confirmed=1 and is_not_to_next=1)) /*были утв-ия и среди них не было is_not_to_next	, моя очередь*/
				or
				(nonconf.user_id="'.$user_id.'") /*не было ни одного утв-ия и я 1й в списке*/
				
		 	 )
		';
		
		
		$ts=new mysqlSet($sql);
		$trs=$ts->GetResult();
		$trc=$ts->GetResultNumRows();
		if($trc==0) return 0;
		$order=mysqli_fetch_array($trs);
		
		 
		
		$ts=new mysqlSet('select count(*) from memo_history where memo_id="'.$order_id.'" and is_new="1" and user_id<>"'.$user_id.'"');
		
		$trs=$ts->GetResult();
		$g=mysqli_fetch_array($trs);
		
		return $g[0];
		 
	}
	
	
	
	//сколько всего новых заданий
	public function CountNewOrders($user_id){
		
		$man=new DiscrMan;
		
			 //$sql='select count(distinct id) from memo where id in (select memo_id from memo_user where user_id="'.$user_id.'") and id in(select distinct memo_id from memo_history where user_id<>"'.$user_id.'" and is_new=1) /*and status_id<>4*/';
			 
			 
			 $sql='select count(distinct t.id)
			
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
		 and t.id in(select distinct memo_id from memo_history where user_id<>"'.$user_id.'" and is_new=1)
		';
			 
			 
			 $ts=new mysqlSet($sql);
			 
		
		//echo $sql;
		
		$trs=$ts->GetResult();
		$g=mysqli_fetch_array($trs);
		
		return $g[0];
	}
}
?>