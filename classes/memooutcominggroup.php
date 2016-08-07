<?

require_once('memogroup.php');
require_once('memoblink.php');

require_once('memooutcominghistorygroup.php');

// исходящие заявки
class MemoOutcomingGroup extends MemoGroup {
	public $prefix='_2';
	
	//установка всех имен
	protected function init(){
		$this->tablename='memo';
		$this->pagename='memos.php';		
		$this->subkeyname='memo_id';	
		$this->vis_name='is_shown';		
		
		$this->_thg=new MemoOutcomingHistoryGroup;
		$this->_tblink=new MemoBlink;
		
	}
	
	public function GainSql($user_id, &$sql, &$sql_count){
		$sql='select t.*,
			k.name as kind_name,
			st.name as status_name,
			u.login as login, u.name_s as name_s, u.position_s as position_s
		from memo as t
			left join memo_kind as k on t.kind_id=k.id
			left join memo_status as st on st.id=t.status_id
			left join user as u on u.id=t.user_id
		
		where t.user_id="'.$user_id.'" 
		
		';
		
		$sql_count='select count(*)
		from memo as t
			left join memo_kind as k on t.kind_id=k.id
			left join memo_status as st on st.id=t.status_id
			left join user as u on u.id=t.user_id
		
		where t.user_id="'.$user_id.'" 
		
		';
		
	}
	
	
}
?>