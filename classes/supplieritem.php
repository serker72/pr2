<?
require_once('abstractitem.php');
require_once('discr_man_group.php');
require_once('discr_rightuseritem.php');



require_once('bdetailsgroup.php');
require_once('fagroup.php');

require_once('contractitem.php');
require_once('contractgroup.php');

require_once('sh_i_group.php');
require_once('acc_group.php');
require_once('paygroup.php');
require_once('billgroup.php');
require_once('trust_group.php');


require_once('sh_i_in_group.php');
require_once('acc_in_group.php');
require_once('pay_in_group.php');
require_once('bill_in_group.php');
require_once('trust_group.php');


require_once('docstatusitem.php');
require_once('user_s_group.php');
require_once('user_s_item.php');
require_once('array_sorter.php');


//kontragent
class SupplierItem extends AbstractItem{
	protected $is_org;
	protected $is_org_name;
	
	public function __construct($is_org=0){
		$this->init($is_org);
	}
	
	//��������� ���� ����
	protected function init($is_org){
		$this->is_org=$is_org;
		$this->is_org_name='is_org';
		
		
		$this->tablename='supplier';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_active';	
		//$this->subkeyname='mid';	
	}
	
	
	public function Add($params){
		$params[$this->is_org_name]=$this->is_org;
		$code=parent::Add($params);
		
		if($code!=0){
		  //������������ ���������������� �������
		
		 
		  //������� �������
		  //$this->SetQuestions($code,$questions);
		}
		
		//��������� ����� ������������ ���� ��?
		
		
		return $code;
	}
	
	public function Edit($id,$params){
		$item=$this->GetItemById($id);
		
		$params[$this->is_org_name]=$this->is_org;
		//$this->SetQuestions($id,$questions);
		
		
		//�� ������������� ����������� ����������: ���������, ������ �� ��� ���, � ���������� ���� ����� ��������
		if(isset($params['is_active'])&&($params['is_active']==1)&&($item['is_active']==0)){
			//$params['restore_pdate']=0;	
			if($item['active_first_was_set']==0){
				$params['active_first_pdate']=time();
				$params['active_first_was_set']=1;
				
					
			}elseif($item['active_first_was_set']==1){
				//��� ��� �����������, ������� ��������
				$params['active_first_was_set']=2;	
			}
			
		}
		
		
		
		parent::Edit($id,$params);
	}
	
	//�������
	public function Del($id){
		
		
		new NonSet('delete from banking_details where user_id="'.$id.'"');
		new NonSet('delete from fact_address where user_id="'.$id.'"');
		new NonSet('delete from supplier_notes where user_id="'.$id.'"');
		
		new NonSet('delete from supplier_responsible_user where supplier_id="'.$id.'"');
		
		new NonSet('delete from contract_file where user_d_id="'.$id.'"');
		new NonSet('delete from contract_file_folder where sup_id="'.$id.'"');
		
		new NonSet('delete from supplier_shema_file where user_d_id="'.$id.'"');
		new NonSet('delete from supplier_shema_file_folder where sup_id="'.$id.'"');
		
		
		new NonSet('delete from supplier_sprav_city where supplier_id="'.$id.'"');
		
		new NonSet('delete from supplier_contact_data where contact_id in(select id from supplier_contact where supplier_id="'.$id.'")');
		new NonSet('delete from supplier_contact where supplier_id="'.$id.'"');
		
		new NonSet('delete from supplier_ruk where supplier_id="'.$id.'"');
		
		new NonSet('delete from supplier_contract where user_id="'.$id.'"');
		
		
		parent::Del($id);
	}	
	
	
	
	//������ � ����������� ��������� � ����������� �������, ������ ������ ������������
	public function DocCanAnnul($id,&$reason){
		$can=true;	
		$reason=''; $reasons=array();
		$item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		
		
		
		//��������� ��������� kp
		$set=new mysqlSet('select count(*) from kp where supplier_id="'.$id.'" and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� ������������ �����������: '.$g[0];	
		}
		
		
		
		//��������� ��������� s4eta
		$_accg=new BillGroup;
		$_accg->setidname('supplier_id');
		$arr=$_accg->getitemsbyidarr($id);
				
		if(count($arr)>0){
			$cter=0;
			foreach($arr as $k=>$v){
			  if($v['status_id']!=3) {
			  	$can=$can&&false;
				$cter++;
			  }
			  	
			}
			if($cter>0) $reasons[]='��������� �� �������������� ��������� ������: '.$cter;
		}
		
		$_accg=new BillInGroup;
		$_accg->setidname('supplier_id');
		$arr=$_accg->getitemsbyidarr($id);
				
		if(count($arr)>0){
			$cter=0;
			foreach($arr as $k=>$v){
			  if($v['status_id']!=3) {
			  	$can=$can&&false;
				$cter++;
			  }
			  	
			}
			if($cter>0) $reasons[]='��������� �� �������������� �������� ������: '.$cter;
		}
		
		
		//��������� ��������� raspor
		$set=new mysqlSet('select count(*) from sh_i where bill_id in(select id from bill where supplier_id="'.$id.'" and status_id<>3 and is_incoming=0)');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� ������������ �� ��������: '.$g[0];	
		}
		
		$set=new mysqlSet('select count(*) from sh_i where bill_id in(select id from bill where supplier_id="'.$id.'" and status_id<>3 and is_incoming=1)');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� ������������ �� �������: '.$g[0];	
		}
		
		
		
		$set=new mysqlSet('select count(*) from acceptance where bill_id in(select id from bill where supplier_id="'.$id.'" and status_id<>6 and is_incoming=1)');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� �����������: '.$g[0];	
		}
		
		$set=new mysqlSet('select count(*) from acceptance where bill_id in(select id from bill where supplier_id="'.$id.'" and status_id<>6 and is_incoming=0)');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� ����������: '.$g[0];	
		}
		
		
		//��������� ��������� doverennosti
		$set=new mysqlSet('select count(*) from trust where bill_id in(select id from bill where supplier_id="'.$id.'" and status_id<>3)');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� �������������: '.$g[0];
		}
		
		//��������� ��������� oplaty
		$_accg=new PayGroup;
		$_accg->setidname('supplier_id');
		$arr=$_accg->getitemsbyidarr($id);
		
		
		if(count($arr)>0){
			$cter=0;
			foreach($arr as $k=>$v){
			  if($v['status_id']!=3) {
			  	$can=$can&&false;
				$cter++;
			  }
			  	
			}
			if($cter>0) $reasons[]='��������� �� �������������� ��������� �����: '.$cter;
		}
		
		//��������� ��������� oplaty
		$_accg=new PayInGroup;
		$_accg->setidname('supplier_id');
		$arr=$_accg->getitemsbyidarr($id);
		
		
		if(count($arr)>0){
			$cter=0;
			foreach($arr as $k=>$v){
			  if($v['status_id']!=3) {
			  	$can=$can&&false;
				$cter++;
			  }
			  	
			}
			if($cter>0) $reasons[]='��������� �� �������������� �������� �����: '.$cter;
		}
		
		
		//���� ������
		$set=new mysqlSet('select count(*) from plan_fact_fact where supplier_id="'.$id.'" and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� ������ ������: '.$g[0];	
		}
		
		
		
		//���� �����������
		$set=new mysqlSet('select count(*) from sched where id in(select distinct sched_id from sched_suppliers where supplier_id="'.$id.'" ) and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� ������� ������������: '.$g[0];
		}
		$set=new mysqlSet('select count(*) from sched where id in(select distinct sched_id from sched_contacts where supplier_id="'.$id.'" ) and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� ������� ������������: '.$g[0];
		}
		
		
		//�������, ����,  ��,   ��� ���
		$set=new mysqlSet('select count(*) from tender where id in(select distinct sched_id from tender_suppliers where supplier_id="'.$id.'" ) and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� ��������: '.$g[0];
		}
		
		$set=new mysqlSet('select count(*) from lead where id in(select distinct sched_id from lead_suppliers where supplier_id="'.$id.'" ) and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� �����: '.$g[0];
		}
		
		$set=new mysqlSet('select count(*) from tz where id in(select distinct sched_id from tz_suppliers where supplier_id="'.$id.'" ) and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� ��: '.$g[0];
		}
		
		$set=new mysqlSet('select count(*) from kp_in where id in(select distinct sched_id from kp_in_suppliers where supplier_id="'.$id.'" ) and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� ���, ���: '.$g[0];
		}
		
		
		//���������������: �����, ����,
		$set=new mysqlSet('select count(*) from doc_in where id in(select distinct sched_id from doc_in_suppliers where supplier_id="'.$id.'" ) and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� ��. ����������: '.$g[0];
		}
		
		$set=new mysqlSet('select count(*) from doc_out where id in(select distinct sched_id from doc_out_suppliers where supplier_id="'.$id.'" ) and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� ���. ����������: '.$g[0];
		}
		
		
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	//��������� ������� ����� �� ������ �����
	public function GetItemByFields($params){
		$params[$this->is_org_name]=$this->is_org;
		return parent::GetItemByFields($params);
	}
	
	
	
	
	//����������� ��������� ����������
	public function CanConfirmActive($id, &$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		/*-�������
			-��������
			-1 �������
			-1 �������
			-1 �����*/
		
		
		if($item['is_active']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='���������� ����������';
			$reason.=implode(', ',$reasons);
		}else{
			
			if($item['full_name']==""){
				$can=$can&&false;
				$reasons[]='�� ������ �������� ';
			}
			
			$sql='select count(*) from supplier_sprav_city where supplier_id="'.$id.'"';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$f=mysqli_fetch_array($rs);
			if((int)$f[0]==0){
				$can=$can&&false;
				$reasons[]='�� ������ ����� ����������� ';
			}
			
			
			$sql='select count(*) from supplier_contact where supplier_id="'.$id.'"';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$f=mysqli_fetch_array($rs);
			if((int)$f[0]==0){
				$can=$can&&false;
				$reasons[]='�� ������� �� ������ �������� ';
			}
			
			$sql='select count(*) from supplier_contact_data as scd inner join supplier_contact  as sc on sc.id=scd.contact_id where sc.supplier_id="'.$id.'" and scd.kind_id in(1,3,4)';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$f=mysqli_fetch_array($rs);
			if((int)$f[0]==0){
				$can=$can&&false;
				$reasons[]='�� ������� �� ������ ����������� �������� ';
			}
			
			
			$sql='select count(*) from supplier_contact_data as scd inner join supplier_contact  as sc on sc.id=scd.contact_id where sc.supplier_id="'.$id.'" and scd.kind_id in(5)';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$f=mysqli_fetch_array($rs);
			if((int)$f[0]==0){
				$can=$can&&false;
				$reasons[]='�� ������� �� ������ ����������� email ';
			}
			
			
		
		}
		
		 $reason.=implode(', ',$reasons);
		
		return $can;	
	}
	
	
	
	//������ � ����������� ������ ���-�� ���������� � ����������� �������, ������ ������ 
	public function CanUnConfirmActive($id,&$reason){
		$can=true;	
		$reason=''; $reasons=array();
		$item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		
		
		
		//��������� ��������� kp
		/*$set=new mysqlSet('select count(*) from kp where supplier_id="'.$id.'" and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� ������������ �����������: '.$g[0];	
		}
		
		
		$set=new mysqlSet('select count(*) from plan_fact_fact where supplier_id="'.$id.'" and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� ���������: '.$g[0];	
		}
		 
		//��������� �� �������������� ������� ������������
		
		 $set=new mysqlSet('select count(*) from sched where (id in(select distinct sched_id from sched_suppliers where supplier_id="'.$id.'" ) or id in(select distinct sched_id from sched_contacts where supplier_id="'.$id.'" )) and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='��������� �� �������������� ������� ������������: '.$g[0];	
		}
		*/
		
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	
	//������ � ����������� ������ ������� ���������� � ����������� �������, ������ ������ 
	public function CanUnCheckIsCustomer($id,&$reason){
		$can=true;	
		$reason=''; $reasons=array();
		$item=$this->GetItemById($id);
	//	$_dsi=new DocStatusItem;
		
		
		
		//��������� ��������� �� �������
		$set=new mysqlSet('select * from tender_monitor where supplier_id="'.$id.'" ');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0) $can=$can&&false;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$reasons[]='� '.$f['id'].' �� '.date('d.m.Y', $f['given_pdate']);
		}
		
		 
		
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	//������ �� ������ �������������, ���� �������� ������� ������������� � ���� �����������
	public function GetUsersForResp($id, $supplier=NULL, &$ids){
		if($supplier===NULL) $supplier=$this->GetItemById($id);
		$data=array(); $_ug=new UsersSGroup; $_ui=new UserSItem;
		$_rg=new SupplierResponsibleUserGroup;
		 
		// $au->user_rights->CheckAccess('w',910)
		
		//�������
		 $users=$_rg->GetUsersArr($id);
		foreach($users as $k=>$v){
			$d=array('id'=>$v['user_id'], 'name_s'=>$v['name_s']);
			
			if(!in_array($d, $data)) $data[]=$d;	
		} 
		
		//���������
		$user=$_ui->GetItemById($supplier['created_id']);
		$d=array('id'=>$user['id'], 'name_s'=>$user['name_s']);
		if(!in_array($d, $data)) $data[]=$d;	
		
		 
	 
		//����������
		
		
		$users=$_ug->GetUsersByRightArr('w', 910);
		foreach($users as $k=>$v){
			$d=array('id'=>$v['id'], 'name_s'=>$v['name_s']);
			
			if(!in_array($d, $data)) $data[]=$d;	
		} 
		
		
		//��������� - ������������ ������ �������
		if($supplier['is_supplier']==1){
			
			$users=$_ug->GetUsersByPositionKeyArr('name', '������������ ������', array(4));	
			foreach($users as $k=>$v){
				$d=array('id'=>$v['id'], 'name_s'=>$v['name_s']);
				
				if(!in_array($d, $data)) $data[]=$d;	
			}
		}
		 
		
		//����������� - ������������ ������ ������
		if($supplier['is_customer']==1){
			
			$users=$_ug->GetUsersByPositionKeyArr('name', '������������ ������', array(1));	
			foreach($users as $k=>$v){
				$d=array('id'=>$v['id'], 'name_s'=>$v['name_s']);
				
				if(!in_array($d, $data)) $data[]=$d;	
			}
		}
		 
		$ids=array();
		foreach($data as $k=>$v) $ids[]=$v['id'];
		
		$data=ArraySorter::SortArr($data, 'name_s', 0);
		
		$ret=array();
		foreach($data as $k=>$v) $ret[]=$v['name_s']; 
		
		return $ret;
	}
	
	//������ �� ������ �������������, ���� �������� ������ ����������
	//���������, �������������, ���������
	public function GetUsersWhoAvailable($id, $supplier=NULL, &$ids){
		if($supplier===NULL) $supplier=$this->GetItemById($id);
		$data=array(); $_ug=new UsersSGroup; $_ui=new UserSItem;
		$_rg=new SupplierResponsibleUserGroup;
		 
		// $au->user_rights->CheckAccess('w',910)
		
		//�������
		 $users=$_rg->GetUsersArr($id);
		foreach($users as $k=>$v){
			$d=array('id'=>$v['user_id'], 'name_s'=>$v['name_s'], 'is_active'=>$v['is_active']);
			
			if(!in_array($d, $data)) $data[]=$d;	
		} 
		
		//���������
		$user=$_ui->GetItemById($supplier['created_id']);
		$d=array('id'=>$user['id'], 'name_s'=>$user['name_s'], 'is_active'=>$v['is_active']);
		if(!in_array($d, $data)) $data[]=$d;	
		
		 
	 
		//����������
		
		
		$users=$_ug->GetUsersByRightArr('w', 909);
		foreach($users as $k=>$v){
			$d=array('id'=>$v['id'], 'name_s'=>$v['name_s'], 'is_active'=>$v['is_active']);
			
			if(!in_array($d, $data)) $data[]=$d;	
		} 
		
		//���������
		if($supplier['is_supplier']==1){
			$users=$_ug->GetUsersByRightArr('w', 919);
			foreach($users as $k=>$v){
				$d=array('id'=>$v['id'], 'name_s'=>$v['name_s'], 'is_active'=>$v['is_active']);
				
				if(!in_array($d, $data)) $data[]=$d;	
			} 
		}
		
		//����������
		if($supplier['is_customer']==1){
			$users=$_ug->GetUsersByRightArr('w', 920);
			foreach($users as $k=>$v){
				$d=array('id'=>$v['id'], 'name_s'=>$v['name_s'], 'is_active'=>$v['is_active']);
				
				if(!in_array($d, $data)) $data[]=$d;	
			} 
		}
		
		//�������
		if($supplier['is_partner']==1){
			$users=$_ug->GetUsersByRightArr('w', 921);
			foreach($users as $k=>$v){
				$d=array('id'=>$v['id'], 'name_s'=>$v['name_s'], 'is_active'=>$v['is_active']);
				
				if(!in_array($d, $data)) $data[]=$d;	
			} 
		}
		
		
		 
		$ids=array();
		foreach($data as $k=>$v) $ids[]=$v['id'];
		
		$data=ArraySorter::SortArr($data, 'name_s', 0);
		
		$ret=array();
		foreach($data as $k=>$v) $ret[]=$v['name_s']; 
		
		return $data;
	}
	
	
}
?>