<?

require_once('abstractgroup.php');

require_once('taskhistorygroup.php');

require_once('taskfilegroup.php');
require_once('taskitem.php');

// группа событий входящих задач
class TaskOutcomingHistoryGroup extends TaskHistoryGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='task_history';
		$this->pagename='taskhistory.php';		
		$this->subkeyname='task_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//подсчет новых историй данного заказа
	public function CountNew($order_id,$user_id){
		$ts=new mysqlSet('select * from task where id="'.$order_id.'" and user_id="'.$user_id.'"');
		$trs=$ts->GetResult();
		$trc=$ts->GetResultNumRows();
		if($trc==0) return 0;
		$order=mysqli_fetch_array($trs);
		
		if(($order['user_id']==0)||($order['user_id']==$user_id)){
		
			$ts=new mysqlSet('select count(*) from task_history where task_id="'.$order_id.'" and is_new="1" and user_id<>"'.$user_id.'"');
			
			$trs=$ts->GetResult();
			$g=mysqli_fetch_array($trs);
			
			return $g[0];
		}else return 0;
	}
	
	
	
	//подсчет числа заданий с новыми событиями
	public function CountNewOrders($user_id){
		
		$man=new DiscrMan;
		
			 $ts=new mysqlSet('select count(distinct id) from task where user_id="'.$user_id.'"  and id in(select distinct task_id from task_history where user_id<>"'.$user_id.'" and is_new=1) /*and status_id<>4*/');
			 
			
		
		$trs=$ts->GetResult();
		$g=mysqli_fetch_array($trs);
		
		return $g[0];
	}
	
}
?>