<?
require_once('abstractitem.php');

require_once('memoitem.php');
/*require_once('user_s_item.php');
require_once('user_d_item.php');
require_once('messageitem.php');
*/
//require_once('specdelgroup.php');

//����������� �������
class MemoHistoryItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='memo_history';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='memo_id';	
	}
	
	public function Add($params){
		$code=AbstractItem::Add($params);
		
		
		//���������, ��������� �� ��� �������...
		/*
		$_oi=new ClaimItem;
		$oi=$_oi->GetItemById($params['claim_id']);
			
		$sql='select count(*) from '.$this->tablename.' where '.$this->subkeyname.'="'.$params['claim_id'].'" and user_id<>"'.$params['user_id'].'" ';
		$s=new mysqlset($sql);
		
		$rs=$s->getResult();
		$f=mysqli_fetch_array($rs);
		
		
		//����� �� ������� ���������, ����� �����
		$_di=new UserDItem;
		$di=$_di->GetItemById($params['user_id']);
		
		
		
		if(($f[0]>0)&&($di!==false)&&($di['group_id']==3)){
			//������� ���������
			//���������, ������� �� ����� ���������
			$_ui=new UserSItem;
			$ui=$_ui->getitembyid($oi['s_user_id']);
			if(($ui===false)||($ui['is_active']==0)){
				//�������� �� ������� - ���������� ��������� �� ������ ��������
				
				$message_to_managers="
				<div><em>������ ��������� ������������� �������������.</em></div>
				<div>��������� �������!</div>
				<div>����� $di[name_d] ($di[login]) ������� ������� �� ������ � $oi[id] �� ".date("d.m.Y H:i:s",$oi['pdate']).":</div>
				<div><em>$params[txt]</em></div>
				";
				if($ui!==false){	
					$message_to_managers.="
				<div>���������� �� ������ ������ �������� $ui[name_s] ($ui[login]), ����� ������� ���������� ���������.</div>
				";
				}else{
					$message_to_managers.="
					<div>����� ��������� ������ ������ �� �������.</div>
					";	
					
				}
				
				$message_to_managers.="
				<div>���������� � ���������� ����� ������������� �� ��������� ������.</div>
				";	
				
				$_sdg=new SpecDelGroup;
				$mi=new MessageItem;	
				$spec_arr=$_sdg->GetItemsInArr();
				foreach($spec_arr as $kk=>$vv){
					//$vv['user_id']
					$params1['topic']=' ��������! ����� ������� �� ������ � '.$oi['id'].' �� '.date("d.m.Y H:i:s",$oi['pdate']).', ����� ��������� �� ������ ���������';
					$params1['txt']=$message_to_managers;
					$params1['to_id']= $vv['user_id'];
					$params1['from_id']=-1; //�������������� ������� �������� ���������
					$params1['pdate']=time();	
					$mi->Send(0,0,$params1,false);
					//echo $message_to_managers;
					
				}
				
			}
			
		}
		
		*/
		return $code;
	}
	
}
?>