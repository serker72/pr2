<?
require_once('abstractitem.php');
require_once('taskfileitem.php');


require_once('authuser.php');
require_once('actionlog.php');
require_once('taskhistoryitem.php');

//����������� �������
class TaskItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='task';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
	
	
	public function Add($params){
		
		
		$code=parent::Add($params);	
		
		
		return $code;
	}
	
	/*
	
	//�������� 
	public function Add($params){
		$names='';
		$vals='';
		
		
		foreach($params as $key=>$val){
			if($names=='') $names.=$key;
			else $names.=','.$key;
			
			if($vals=='') $vals.='"'.$val.'"';
			else $vals.=','.'"'.$val.'"';
		}
		$query='insert into '.$this->tablename.' ('.$names.') values('.$vals.');';
		$it=new nonSet($query);
		echo $query;
		
		$code=$it->getResult();
		unset($it);
		return $code;
	}*/
	
	
	
	public function Edit($id,$params){
		
		
		parent::Edit($id,$params);	
	}
	
	
	public function SendStatusMessage($order_id, $status_id){
		
		/*
		if(($status_id==4)){
			
			$itm=$this->GetItemById($order_id);
			
			
			$_oi=new OrderItem;
			
			$f=$_oi->getitembyid($itm['order_id']);
			if($f===false) return;
			
			$message="";
			
			
			
			$_kur=new UserItem;
			$_dealer=new UserItem;
			
			$dealer=$_dealer->getitembyid($f['d_user_id']);
			$kur=$_kur->getitembyid($dealer['kurator_id']);
			
			
			if(($status_id==4)&&($itm['kind_id']==13)){
				
				//���������, ����� ���-� ���� �� ���������...
				$was_not_annuled=true;
				$tset=new mysqlset('select count(*) from claim_history where claim_id="'.$order_id.'"  ');
				$rs1=$tset->GetResult();
				$f1=mysqli_fetch_array($rs1);
				if((int)$f1[0]>1) $was_not_annuled=false;
				
				
				if($was_not_annuled){
					//���������	
					  $message="
					<div><em>������ ��������� ������������� �������������.</em></div>
					<div>��������� �������!</div>
					<div>��� �������� � ���������� �������� ������, ���: $itm[srok], �� ����� � $f[id], �������� � $f[fab_no] �� ".date("d.m.Y H:i:s",$f['pdate']).".</div>";
					if($kur==false) $message.="<div>�� ���� ��������, ����������, ����������� � ������ ��������.</div>
					";
				   if($kur!==false) $message.='<div>�� ���� ��������, ����������, ����������� � <a href=\"info.html?name='.$kur[login].'\" target=\"_blank\">������ ��������</a>.</div>  ';
					if($kur!==false) $message.="<div>��� �������: $kur[name_s] ($kur[login]).</div>";
						  
						  
					  $mi=new MessageItem;
				  
					  
					  //�������� ���������
					  $params1=array();
						
					  $params1['topic']='����� � ���������� �������� '."�� ����� � $f[id], �������� � $f[fab_no] �� ".date("d.m.Y H:i:s",$f['pdate']);
					  $params1['txt']=$message;
					  $params1['to_id']= $itm['d_user_id'];
					  $params1['from_id']=-1; //�������������� ������� �������� ���������
					  $params1['pdate']=time();
					  
					  $mi->Send(0,0,$params1,false);
			
				}else{
					//�� ���������...
					  $message="
					<div><em>������ ��������� ������������� �������������.</em></div>
					<div>��������� �������!</div>
					<div>���� ������ �� ���������� �������� ������, ���: $itm[srok], �� ����� � $f[id], �������� � $f[fab_no] �� ".date("d.m.Y H:i:s",$f['pdate']).", ������������.</div>";
					if($kur==false) $message.="<div>�� ���� ��������, ����������, ����������� � ������ ��������.</div>
					";
				   if($kur!==false) $message.='<div>�� ���� ��������, ����������, ����������� � <a href=\"info.html?name='.$kur[login].'\" target=\"_blank\">������ ��������</a>.</div>  ';
					if($kur!==false) $message.="<div>��� �������: $kur[name_s] ($kur[login]).</div>";
						  
						  
					  $mi=new MessageItem;
				  
					  
					  //�������� ���������
					  $params1=array();
						
					  $params1['topic']='���� ������ �� ���������� �������� '."�� ����� � $f[id], �������� � $f[fab_no] �� ".date("d.m.Y H:i:s",$f['pdate']).', ������������';
					  $params1['txt']=$message;
					  $params1['to_id']= $itm['d_user_id'];
					  $params1['from_id']=-1; //�������������� ������� �������� ���������
					  $params1['pdate']=time();
					  
					  $mi->Send(0,0,$params1,false);
				}
			}
			
		}*/
	}
	
	
	
	//�������
	public function Del($id){
		//������� ��� �����
		$fset=new MysqlSet('select * from task_file where history_id in (select id from task_history where task_id="'.$id.'")');
		$fc=$fset->GetResultNumRows();
		$rfs=$fset->GetResult();
		
		$fi=new TaskFileItem;
		for($i=0; $i<$fc; $i++){
			$f=mysqli_fetch_array($rfs);
			//GetStoragePath()
			@unlink($fi->GetStoragePath().$f['filename']);
		}
		
		
		
		//������� ����� �� ��
		$query = 'delete from task_file where history_id in (select id from task_history where task_id="'.$id.'")';
		$it=new nonSet($query);
		
		//������� �������
		$query = 'delete from task_history where task_id="'.$id.'";';
		$it=new nonSet($query);
		
		parent::Del($id);
	}	
	
	
	
	
	
}
?>