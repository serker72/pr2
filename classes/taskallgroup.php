<?

require_once('taskgroup.php');
require_once('taskblink.php');
require_once('taskallhistorygroup.php');

// ��������� ������
class TaskAllGroup extends TaskGroup {
	public $prefix='_3';
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='task';
		$this->pagename='tasks.php';		
		$this->subkeyname='order_id';	
		$this->vis_name='is_shown';		
		
		$this->_thg=new TaskAllHistoryGroup;
		$this->_tblink=new TaskBlink;
		
	}
	
	public function GainSql($user_id, &$sql, &$sql_count){
		$sql='select t.*,
			k.name as kind_name,
			st.name as status_name,
			u.login as login, u.name_s as name_s
		from task as t
			left join task_kind as k on t.kind_id=k.id
			left join task_status as st on st.id=t.status_id
			left join user as u on u.id=t.user_id
		
		where t.id<>0 
		
		';
		
		$sql_count='select count(*)
		from task as t
			left join task_kind as k on t.kind_id=k.id
			left join task_status as st on st.id=t.status_id
			left join user as u on u.id=t.user_id
		
		where t.id<>0  
		
		';
		
	}
	
	
}
?>