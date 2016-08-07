<?

require_once('abstractgroup.php');

require_once('memohistorygroup.php');

require_once('memofilegroup.php');
require_once('memoitem.php');

// ������ ������� �������� �����
class MemoOutcomingHistoryGroup extends MemoHistoryGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='memo_history';
		$this->pagename='memohistory.php';		
		$this->subkeyname='memo_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//������� ����� ������� ������� ������
	public function CountNew($order_id,$user_id){
		$ts=new mysqlSet('select * from memo where id="'.$order_id.'" and user_id="'.$user_id.'"');
		$trs=$ts->GetResult();
		$trc=$ts->GetResultNumRows();
		if($trc==0) return 0;
		$order=mysqli_fetch_array($trs);
		
		if(($order['user_id']==0)||($order['user_id']==$user_id)){
		
			$ts=new mysqlSet('select count(*) from memo_history where memo_id="'.$order_id.'" and is_new="1" and user_id<>"'.$user_id.'"');
			
			$trs=$ts->GetResult();
			$g=mysqli_fetch_array($trs);
			
			return $g[0];
		}else return 0;
	}
	
	
	
	//������� ����� ������� � ������ ���������
	public function CountNewOrders($user_id){
		
		$man=new DiscrMan;
		
			 $ts=new mysqlSet('select count(distinct id) from memo where user_id="'.$user_id.'"  and id in(select distinct memo_id from memo_history where user_id<>"'.$user_id.'" and is_new=1) /*and status_id<>4*/');
			 
			
		
		$trs=$ts->GetResult();
		$g=mysqli_fetch_array($trs);
		
		return $g[0];
	}
	
}
?>