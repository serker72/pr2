<?
require_once('abstractitem.php');
require_once('petitionitem.php');
require_once('petitionuseritem.php');
require_once('authuser.php');
require_once('discr_man.php');

class PetitionBlink{
	
	
	
	
	
	
	
	//���������� ������� ����� � �������
	public function OverallBlink($task_id,  &$color, $task=NULL, $can_confirm_chief=false, $can_unconfirm_chief=false, $result=NULL){
		$res=false;
		
		
		$color='black';
		
		$_ti=new petitionitem;
		if($task===NULL) $task=$_ti->getitembyid($task_id);
		
		$au=new AuthUser;
		if($result===NULL) $result=$au->Auth();
		
		$_pu=new PetitionUserItem;
		$user=$_pu->GetUserByPetitionId($task_id);
		
		 
		if(($task['status_id']==1)||($task['status_id']==2)){
			$color='gray';
		}
		elseif($task['status_id']==3){
			//���� ���� ����� �� ���. �������������, � ������� ���-�� ��������  �������������, ��...
			if($can_confirm_chief&&($result['id']==$user['id'])){
				$color='red';
				$res=$res||true;	
			}
			
		}
		elseif($task['status_id']==4){
			//���� ���� ����� �� ���. �������������, � ������� ���-�� ��������  �������������, ��...
			if($can_unconfirm_chief&&($result['id']==$user['id'])){
				$color='green';
				 	
			}
		}
		
		
		
		return $res;
	}
	
	
	//������� �������� ���������
	public function CountNewOrders($user_id){
		$ret=0;
		
		//���� � �������� ���-�� ���� ����� �� ����������� ��������� � ���� ���-��...
		
		$_man=new DiscrMan;
		if($_man->CheckAccess($user_id, 'w', 831)){
			//����� ��� ��������� � ������� 3 - � ���� ������������ ������� ���������
			$sql='select count(distinct t.id) from petition as t
			inner join petition_user as tu on t.id=tu.petition_id
			where t.status_id=3 and tu.user_id="'.$user_id.'" ';	
			$set=new mysqlset($sql);
			
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			$ret=(int)$f[0];
			
		}
		
		
		return $ret;
	}
}
?>