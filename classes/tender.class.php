<?
 
require_once('abstractitem.php');
 
require_once('supplieritem.php');
require_once('suppliercontactitem.php');
require_once('suppliercontactgroup.php');
require_once('suppliercontactdatagroup.php');
require_once('user_s_group.php');
require_once('supplier_cities_group.php');

require_once('supplier_responsible_user_group.php');
require_once('supplier_responsible_user_item.php');
require_once('actionlog.php');

require_once('tender_history_group.php');
require_once('tender_history_item.php');
require_once('user_s_item.php');
require_once('discr_man.php');

require_once('pl_curritem.php');
require_once('tender_view_item.php');
require_once('lead.class.php');
require_once('user_s_group.php');

require_once('tender_field_rules.php');
require_once('docstatusitem.php');
require_once('tender_view.class.php');
require_once('user_pos_item.php');

require_once('abstract_working_group.php');
require_once('abstract_working_kind_group.php');

//���������� ������� �������


//����������� ������ �������

class Tender_AbstractItem extends AbstractItem{
	public $kind_id=1;
	protected function init(){
		$this->tablename='tender';
		$this->item=NULL;
		$this->pagename='ed_tender.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}	
	
	
	public function Add($params){
		/**/
		$digits=5;
		
		/*switch($params['kind_id']){
			case 1:
				$begin='�';
			break;
			case 2:
				$begin='��';
			break;
			case 3:
				$begin='��';
			break;
			case 4:
				$begin='��';
			break;
			case 5:
				$begin='��';
			break;
			default:
				$begin='�';
			break;
				
			
		}
		*/
		
		$begin='��';
		
		$sql='select max(code) from '.$this->tablename.' where  code REGEXP "^'.$begin.'[0-9]+"';
		
		//echo $sql;
		
		$set=new mysqlset($sql);
		
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		if($rc>0){
			$f=mysqli_fetch_array($rs);	
			
			//$f1=mysqli_fetch_array($rs1);	
			
			//echo $f[0];
			eregi($begin."([[:digit:]]{".$digits."})",$f[0],$regs);
			//print_r($regs);
			
			
			$number=(int)$regs[1];
			//print_r($regs); die();
			$number++;
			
			$test_login=$begin.sprintf("%0".$digits."d",$number);
			
			 
			$login=$test_login;
		}else{
			
			//$f=mysqli_fetch_array($rs);	
			$login=$begin.sprintf("%0".$digits."d",1);
			
		 
		}
		
		// echo $login; die();
		
		$params['code']=$login;
		
		
		return AbstractItem::Add($params);
		
	}
	
	
	
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		 
		return $can;
	}
	
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanRestore($id,&$reason,$item=NULL){
		$can=true;	
		 
		return $can;
	}
	
	
	
	//������ � ���� ������ ��� ���
	public function DocCanUnconfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		return $can;
	}
	
	//������ � ���� ��� ���
	public function DocCanConfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		return $can;
	}
	
	//������ � �����������  ��� ���� � ����������� �������, ������ ������ 
	public function DocCanConfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		
		return $can;
	}
	
	
	//������ � ����������� ������ ��� ���� � ����������� �������, ������ ������ 
	public function DocCanUnconfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		
		return $can;
	}
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		
		 
		
		AbstractItem::Edit($id, $params);
		
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	
	//�������� � ��������� ������� 
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		 
	}
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		return '������, ������ '.$stat['name'];
	}
	
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		return '������ '.$item['code'].', ������ '.$stat['name'];
	}
	
	public function ConstructBeginDate($id, $item=NULL){
		 
		
		if($item===NULL) $item=$this->getitembyid($id);  
		
		return date('d.m.Y', $item['pdate_beg']);
	}
	
	public function ConstructEndDate($id, $item=NULL){
		if($item===NULL) $item=$this->getitembyid($id);  
		
		$res='';
		
	 	if($item['pdate_end']!="") $res.=$item['pdate_end'].'T'.$item['ptime_end'];
		
		return $res; 
	}
	
	//�������� �������� ��� �� ������
	public function ConstructContacts($id, $item=NULL){
		
	}
	
	//�������, ��������� � ��������
	public function GetStatuses($current_id=0){
		 
		$arr=Array();
		 $set=new MysqlSet('select * from document_status where id in(9,10,3) order by  id asc');
		 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	 
	}
	
	
	//�������� ������ ���-���, ��� ����� �������
	/*public function GetUsersArr($id, $item=NULL){
		$_ug=new Sched_UserGroup;
		return $_ug->GetItemsByIdArr($id, $item);
	}
	public function GetUserIdsArr($id, $right_id=NULL){
		$_ug=new Sched_UserGroup;
		return $_ug->GetItemsIdsById($id, $right_id);
	}*/
	
	
	//������ ������� ���-���, ��� �����
	//������� �������
	/*public function AddUsers($current_id, array $positions,  $result=NULL){
		$_kpi=new Sched_UserItem;
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$this->GetUsersArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('sched_id'=>$v['sched_id'],'user_id'=>$v['user_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				//$_kpi->Add(array('komplekt_ved_id'=>$v['komplekt_ved_id'],'position_id'=>$v['position_id'], 'quantity'=>$v['quantity']));
				
				$add_array=array();
				$add_array['sched_id']=$v['sched_id'];
				$add_array['user_id']=$v['user_id'];
				
				$add_array['right_id']=$v['right_id'];
			 
				
				 
				$_kpi->Add($add_array);
				
				 
				
				$log_entries[]=array(
					'action'=>0,
					'sched_id'=>$v['sched_id'],
					'user_id'=>$v['user_id'],
					'right_id'=>$v['right_id'] 
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				$add_array['sched_id']=$v['sched_id'];
				$add_array['user_id']=$v['user_id'];
				
				$add_array['right_id']=$v['right_id'];
				
				 
				$_kpi->Edit($kpi['id'],$add_array, $add_pms,$can_change_cascade,$check_delta_summ,$result);
				
				 
				
				//���� ���� ���������
				
				//��� ����������? ���������� prava
				
				$to_log=false;
				if($kpi['right_id']!=$add_array['right_id']) $to_log=$to_log||true;
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					  'sched_id'=>$v['sched_id'],
					'user_id'=>$v['user_id'],
					'right_id'=>$v['right_id'] 
				  );
				}
				
			}
		}
		
		//����� � ������� ��������� �������:
		//����. ���. - ��� �������, ������� ��� � ������� $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(($vv['sched_id']==$v['sched_id'])&&($vv['user_id']==$v['user_id'])
				 
				){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//������� ��������� �������
		foreach($_to_delete_positions as $k=>$v){
			
			//��������� ������ ��� �������
			 
			
			$log_entries[]=array(
					'action'=>2,
					'sched_id'=>$v['sched_id'],
					'user_id'=>$v['user_id'],
					'right_id'=>$v['right_id'] 
			);
			
			//������� �������
			$_kpi->Del($v['id']);
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	*/
	
}


/*************************************************************************************************/
//������
class Tender_Item extends Tender_AbstractItem{
	public $kind_id=1;
	
	
	
	
	
	public function NewTenderMessage($code){
		$params=$this->GetItemById($code);
		 
		//����� ���� ������������� � ������� 947
		if(!isset($params['manager_id'])||($params['manager_id']==0)){		
		  $sql='select p.* from user as p 
		  left join user_position as up on up.id=p.position_id
		  left join user_department as ud on ud.id=p.department_id
		  
		  where p.is_active=1 and (p.id in( select distinct user_id from user_rights where right_id=2 and object_id=947)  or  (up.name LIKE "%������������ ������%" and ud.name LIKE "%����� ������%") )';
		  
		  //echo $sql;
		  
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  
		  $users_to_send=array();
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  
			  if(($f['is_in_vacation']==1)&&(($f['vacation_till_pdate']+24*60*60)>time())){
				  //continue;	
			  }
			  $users_to_send[]=$f;
		  }
		  
		  //print_r($users_to_send); die();
		  
		  
		  $topic='����� ������';
		  $_mi=new MessageItem;  
		  foreach($users_to_send as $k1=>$user){
			  
			  $txt='<div>';
			  $txt.='<em>������ ��������� ������������� �������������.</em>';
			  $txt.=' </div>';
			  
			  
			  $txt.='<div>&nbsp;</div>';
			  
			  $txt.='<div>';
			  $txt.='���������(��) '.$user['name_s'].'!';
			  $txt.='</div>';
			  $txt.='<div>&nbsp;</div>';
			  
			  
			  $txt.='<div>';
			  $txt.='<strong>������ ����� ������:</strong>';
			  $txt.='</div>';
			  
			  $txt.='<div>&nbsp;</div><ul>';
			  
			 
				  
			  $txt.='<li>';
			  $txt.='<a href="ed_tender.php?action=1&id='.$code.'" target="_blank">������ '.$params['code'].'</a>, <em>'.$params['topic'].'</em> ';
			    
			  $txt.='</li>';
			  
			  
			  $txt.='<li>';
			  $txt.='<strong>����������(�):</strong> ';
			  
			 
			  
			  $sql2='select s.*, opf.name as opf_name from
			  supplier as s
			  inner join tender_suppliers as ts on ts.supplier_id=s.id
			  left join opf on opf.id=s.opf_id 
			  where ts.sched_id="'.$code.'"
			  order by s.full_name asc '; 
			   
			  $set2=new mysqlset($sql2);
			  $rs2=$set2->GetResult();
			  $rc2=$set2->GetResultNumRows();
			  
			  
			  $suppliers=array();
			  for($k=0; $k<$rc2; $k++){
				  $h=mysqli_fetch_array($rs2);
				  $suppliers[]=$h['full_name'].', '.$h['opf_name'];
				  
				  
				  
			  }	 
			  $txt.=implode('; ', $suppliers);
			  
			  $txt.='</li>'; 
			  
			  
			  $txt.='<li>';
			  $_kind=new TenderKindItem;
			  $kind=$_kind->getitembyid($params['kind_id']);
			  $txt.='<strong>��� �������: </strong> '.$kind['name'];
			  
			  $txt.='</li>'; 
			  
			   $txt.='<li>';
			   $_type=new Tender_EqTypeItem;
			   $type=$_type->GetItemById($params['eq_type_id']);
			    $txt.='<strong>��� ������������: </strong> '.$type['name'];
			  
			  $txt.='</li>'; 
			  
			    $txt.='<li>';
				$_cu=new PlCurrItem;
				$cu=$_cu->GetItemById($params['currency_id']);
			  $txt.='<strong>������������ ����: </strong> '.number_format($params['max_price'],2,'.',' ').' '.$cu['signature'];
			  $txt.='</li>'; 
			  
			  
			   $txt.='<li>';
			  $txt.='<strong>���� ����������: </strong> '.datefromYmd($params['pdate_placing']);
			  $txt.='</li>'; 
			  
			  
			  
			   $txt.='<li>';
			  $txt.='<strong>���� ������ ������: </strong> '.datefromYmd($params['pdate_claiming']).' '.$params['ptime_claiming'];
			  $txt.='</li>'; 
			  
			   $txt.='<li>';
			  $txt.='<strong>���� ��������� ������������ : </strong> '.datefromYmd($params['pdate_finish']).' '.$params['ptime_finish'];
			  $txt.='</li>'; 
			  
			  
			  
			   	 
				 
			  
			  
			  $txt.='</ul><div>&nbsp;</div>';
			  
			  
			  //$txt.='<div>&nbsp;</div>';
		  
			  $txt.='<div>';
			  $txt.='C ���������, ��������� "'.SITETITLE.'".';
			  $txt.='</div>';
			  
			  
			  
			  //echo $txt;
			  
			  
			  $_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
		   
		  }
		}	
	}
	
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL, $apply_to_leads=true){
		$item=$this->GetItemById($id);
		
		$_au=new AuthUser();
		if($_result===NULL) $_result=$_au->Auth();
		
		 
		
		AbstractItem::Edit($id, $params);
		
		
		$this->ScanResp($id, $params['manager_id'], $_result['id']);
		
		
		if(isset($params['manager_id'])&&($params['manager_id']!=0)&&($item['manager_id']!=$params['manager_id'])){
			//����� ��� ��������� ���� � � ��� ���������� ����������� ���������
			//$current_id, array $positions,
			$sql='select id from lead where tender_id="'.$id.'"';
			$set=new mysqlSet($sql);
			
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$_li=new Lead_Item;
			 
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
									
				$_li->Edit($f['id'], array('manager_id'=>$params['manager_id']),false,$_result);
			}
			
		}
		
		
		//���� ��� ������� ��������� ��������� �������� ���� ������ ������ - �� ������� ��������� �� ���� ���������...
		if(($item['manager_id']!=0)&&isset($params['pdate_claiming'])&&(
		
		($params['pdate_claiming']!=$item['pdate_claiming'])||
		($params['ptime_claiming']!=$item['ptime_claiming'])
		
		)){
			
		 
		 	$topic='����� ����� ������ ������ �� ������� '.$item['code'];
		  	$_mi=new MessageItem;  
		    $_user=new UserSItem;
			$user=$_user->GetItemById($item['manager_id']); 
			  
			  $txt='<div>';
			  $txt.='<em>������ ��������� ������������� �������������.</em>';
			  $txt.=' </div>';
			  
			  
			  $txt.='<div>&nbsp;</div>';
			  
			  $txt.='<div>';
			  $txt.='���������(��) '.$user['name_s'].'!';
			  $txt.='</div>';
			  $txt.='<div>&nbsp;</div>';
			  
			  
			  $txt.='<div>';
			  $txt.='<strong>�� ������ ������� �������� ���� ������ ������:</strong>';
			  $txt.='</div>';
			  
			  $txt.='<div>&nbsp;</div><ul>';
			  
			 
				  
			  $txt.='<li>';
			  $txt.='<a href="ed_tender.php?action=1&id='.$id.'" target="_blank">������ '.$item['code'].'</a>, <em>'.$item['topic'].'</em>, ';
			    
			  
			  
			  $txt.='<strong>������ ���� ������ ������: </strong> '.datefromYmd($item['pdate_claiming']).' '.$item['ptime_claiming'];
			  
			  $txt.=', <strong>����� ���� ������ ������: </strong> '.datefromYmd($params['pdate_claiming']).' '.$params['ptime_claiming'];
			  $txt.='</li>'; 
			  
			 
			  
			  
			   	 
				 
			  
			  
			  $txt.='</ul><div>&nbsp;</div>';
			  
			  
			  //$txt.='<div>&nbsp;</div>';
		  
			  $txt.='<div>';
			  $txt.='C ���������, ��������� "'.SITETITLE.'".';
			  $txt.='</div>';
			  
			  
			  
			  //echo $txt;
			  
			  
			  $_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	 
		
		}
		 
		if($scan_status) $this->ScanDocStatus($id,$item, $params,NULL,$_result);
		
		//���������� ��������� � �������� ����
		if(($apply_to_leads)&&(isset($params['status_id'])||isset($params['is_confirmed'])||isset($params['is_confirmed_done'])||isset($params['is_fulfiled']))){
			$this->ScanLeads($id, $item, $params,  $_result);
		}
		
		
		//�������� ���� ����� �������
		if(isset($params['status_id'])&&isset($item['status_id'])&&($params['status_id']!=$item['status_id'])){
			//AbstractItem::Edit($id, array('pdate_status_change'=>time()));
			
			
			if(($item['status_id']==28)&&($params['status_id']!=28)){
				$_wi=new Tender_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'in_or_out'=>1, 'pdate'=>time()));
					
			}elseif(($item['status_id']!=28)&&($params['status_id']==28)){
				$_wi=new Tender_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'in_or_out'=>0, 'pdate'=>time()));
			}
				
		}
	}
	
	
	//���������� ��������� � �������� ����
	protected function ScanLeads($tender_id, $old_params, $new_params, $_result=NULL){
		$_au=new AuthUser();
		
		if($_result===NULL) $_result=$_au->Auth();
		$log=new ActionLog;
		
		
		//var_dump($_result); die();
		
		
		//die();
		//������ ����
		$sql='select p.id, p.code from lead as p where p.tender_id="'.$tender_id.'" and p.status_id<>3';
		$leads=array();
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++) {
			$leads[]=mysqli_fetch_array($rs);	
		}
		
		
		
		
		//print_r($leads); die();
 
		
		
		$_li=new Lead_Item; $_stat=new DocStatusItem;
		if(isset($new_params['status_id'])&&isset($old_params['status_id'])&&($new_params['status_id']!=$old_params['status_id'])){
			foreach($leads as $k=>$lead){
				
				//���� ������� ��� �������� - ������ ����������� 100 ��� 0
				$lparams=array('status_id'=>$new_params['status_id']);
				if($new_params['status_id']==30){
					$lparams['probability']=100;	
				}
				if($new_params['status_id']==31){
					$lparams['probability']=0;	
				}
				
				//������ �����   - ���-�� 0
				if($new_params['status_id']==34){
					$lparams['probability']=0;	
				}
				
				 
				
				//������ ������� 29 - ����������� 70
				if($new_params['status_id']==29){
					$lparams['probability']=70;	
				}
				
				
				$_li->Edit($lead['id'], $lparams,false,$_result, false);
				//����� ������� ����
				$stat=$_stat->Getitembyid($new_params['status_id']);
				$log->PutEntry(0/*$_result['id']*/,'����� ������� ��������� ���� ��� ����� ������� �������',NULL,950,NULL,'��� '.$lead['code'].': ���������� ������ '.$stat['name'],$lead['id']);
				
				$log->PutEntry(0/*$_result['id']*/,'����� ������� ��������� ���� ��� ����� ������� �������',NULL,931,NULL,'��� '.$lead['code'].': ���������� ������ '.$stat['name'],$tender_id);
			}
		}
		
		
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])&&($new_params['is_confirmed']!=$old_params['is_confirmed'])){
			foreach($leads as $k=>$lead){
				$_li->Edit($lead['id'], array('is_confirmed'=>$new_params['is_confirmed'], ' 	user_confirm_id'=>0/*$_result['id']*/,'confirm_pdate'=>time()),false,$_result,false);
				
				if($new_params['is_confirmed']==1) $comment='����������� ';
				else $comment='������ ����������� ';
				 
				$log->PutEntry(0/*$_result['id']*/,$comment.' ��������� ���� ��� ����� ������� �������',NULL,950,NULL,'��� '.$lead['code'].': '.$comment,$lead['id']);
				
				$log->PutEntry(0/*$_result['id']*/,$comment.' ��������� ���� ��� ����� ������� �������',NULL,931,NULL,'��� '.$lead['code'].': '.$comment,$tender_id);
			}
		}
		
		if(isset($new_params['is_confirmed_done'])&&isset($old_params['is_confirmed_done'])&&($new_params['is_confirmed_done']!=$old_params['is_confirmed_done'])){
			foreach($leads as $k=>$lead){
				$_li->Edit($lead['id'], array('is_confirmed_done'=>$new_params['is_confirmed_done'], ' 	user_confirm_done_id'=>0/*$_result['id']*/,'confirm_done_pdate'=>time()),false, $_result,false);
				
				if($new_params['is_confirmed_done']==1) $comment='����������� ����������';
				else $comment='������ ����������� ����������';
				 
				$log->PutEntry(0/*$_result['id']*/,$comment.' ��������� ���� ��� ����� ������� �������',NULL,950,NULL,'��� '.$lead['code'].': '.$comment,$lead['id']);
				
				$log->PutEntry(0/*$_result['id']*/,$comment.' ��������� ���� ��� ����� ������� �������',NULL,931,NULL,'��� '.$lead['code'].': '.$comment,$tender_id);
			}
		}
		
		
		if(isset($new_params['is_fulfiled'])&&isset($old_params['is_fulfiled'])&&($new_params['is_fulfiled']!=$old_params['is_fulfiled'])){
			foreach($leads as $k=>$lead){
				$_li->Edit($lead['id'], array('is_fulfiled'=>$new_params['is_fulfiled'], ' 	user_fulfiled_id'=>0/*$_result['id']*/,'fulfiled_pdate'=>time()),false,$_result, false);
				
				if($new_params['is_fulfiled']==1) $comment='����������� ������ ������';
				else $comment='������ ����������� ������ ������';
				 
				$log->PutEntry(0/*$_result['id']*/,$comment.' ��������� ���� ��� ����� ������� �������',NULL,950,NULL,'��� '.$lead['code'].': '.$comment,$lead['id']);
				
				$log->PutEntry(0/*$_result['id']*/,$comment.' ��������� ���� ��� ����� ������� �������',NULL,931,NULL,'��� '.$lead['code'].': '.$comment,$tender_id);
			}
		}
		
		
		
			
	}
	
	
	//�������������� �������� ��� ���� � ����� �����������, ���� ��� ��� ���
	protected function ScanResp($tender_id, $manager_id, $result_id){
		//$_si=new SupplierItem;
		$_resp=new SupplierResponsibleUserGroup;
		$_respi=new SupplierResponsibleUserItem;
		$_sup=new Tender_SupplierItem;
		$_si=new SupplierItem;
		$_ui=new UserSItem;
		$log=new ActionLog;
		//$resp=$_resp->GetUsersArr($supplier_id, $resps);
		
		//����� ����������� �������, ���� �� ������ ����!!!
		$supplier=$_sup->GetItemByFields(array('sched_id'=>$tender_id));
		
		if(($supplier!==false)&&($manager_id!=0)){
			
			$supplier_id=$supplier['supplier_id'];
			
			$test_resp=$_respi->GetItemByFields(array('supplier_id'=>$supplier_id, 'user_id'=>$manager_id));
			
			
			
			if($test_resp===false){
				$_respi->Add(array(
					'supplier_id'=>$supplier_id,
					'user_id'=>$manager_id
				));
				
				$su=$_si->getitembyid($supplier_id);
				$user=$_ui->GetItemById($manager_id);
				
				$descr=SecStr('���������� '.$su['code'].' '.$su['full_name'].', ��������� '.$user['name_s']);
				
				$log->PutEntry((int)$result_id, '�������������� ���������� �������������� ���������� � ����� ����������� ��� ���������� �������',NULL,931,NULL,$descr,$tender_id);
				$log->PutEntry((int)$result_id, '�������������� ���������� �������������� ���������� � ����� ����������� ��� ���������� �������',NULL,87,NULL,$descr,$supplier_id);
				
				
			}
		}
	}
	
	
	
	//������ � �����������  �������� ������� � ���� ����������, ���� ��������
	//�� ������� ��������� ����� � ������� � ������, �� ������������
	public function DocCanPutStatus2326($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;

		
		
		//����� ��������� ����
		$sql='select count(*) from lead where tender_id="'.$id.'"  and status_id in(28,35,26)';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$correct=mysqli_fetch_array($rs);
		
		$sql='select count(*) from lead where tender_id="'.$id.'" and status_id not in(3)';
		//echo $sql;
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=mysqli_fetch_array($rs);
		
		//echo $correct[0]. ' vs '.(int)$total[0].' end';
		
		if(((int)$correct[0]==0)||(((int)$total[0]>(int)$correct[0])&&(int)$correct[0]!=0)){
			$can=$can&&false;
			$reasons[]='��� �������� � ������� ���� ����������, ���� �������� �� ������� ������ ���� ��������� ���� � �������� � ������ ��� �� ������������';
			
			//���������, ����� ���� (�� ��������������)
			$sql1='select l.*, st.name as status_name
			 from lead as l
			left join document_status as st on st.id=l.status_id
			 where l.tender_id="'.$id.'" and l.status_id not in(3,28,35,26)';
			
			$set1=new mysqlset($sql1);
		 	$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			
			$leads=array();
			for($i=0; $i<$rc1; $i++){
				$g=mysqli_fetch_array($rs1);
				
				$leads[]=$g['code'].', ������ '.$g['status_name'];
				
				
					
			}
			if(count($leads)>0) $reasons[]='���������� ��������� ��������� ���� � ������������ �������: '.implode('; ', $leads);
			
		}
		
		 
		
		
		$reason.=implode(";\n",$reasons);
		return $can;
	}
	
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
			
		
		$_dsi=new DocStatusItem;
		if($item['status_id']!=18){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������ ���������: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		} 
		
		 
		return $can;
	}
	
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanRestore($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=3){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������ ���������: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	
	
	

	//������ � �����������  ��� ����� � ����������� �������, ������ ������ 
	public function DocCanConfirmFulfil($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_fulfiled']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� ������� ���������� ��������';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed_done']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� ������� �� ���������� ����������';
		 
		}elseif($item['is_confirmed']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� ������� �� ���������� ����������';
		}elseif(!in_array($item['status_id'], array(30,31,32,34,37))){
			$can=$can&&false;
			$reasons[]='��� ����������� �������� ������ ������ ������ ���� � ����� �� ��������� ��������: �����, �������, �������, ��������, ����������� �����';
			
		}
		
		return $can;
	}
	
	
	//������ � ����������� ������ ��� ������ � ����������� �������, ������ ������ 
	public function DocCanUnconfirmFulfil($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_fulfiled']!=1){ 
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� ������� �� ���������� ��������';
			$reason.=implode(', ',$reasons);
		} 
		
		return $can;
	}
	
	
	//������ � ���� ������ ��� ���
	public function DocCanUnconfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=1){
			
			$can=$can&&false;
			$reasons[]='� ������� �� ���������� ����������';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirm_done']==1){
			
			$can=$can&&false;
			$reasons[]='� ������� ���������� ����������';
			$reason.=implode(', ',$reasons);
		 
		}
		
		return $can;
	}
	
	//������ � ���� ��� ���
	public function DocCanConfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� ������� ���������� ����������';
			$reason.=implode(', ',$reasons);
		}else{
			
			
			if($item['fz_id']==0){
				$can=$can&&false;
			 
				$reasons[]='�� ������ ��';
			}
			
			if($item['max_price']==''){
				$can=$can&&false;
			 
				$reasons[]='�� ������� ������������ ����';
			}
			
			if($item['currency_id']==0){
				$can=$can&&false;
			 
				$reasons[]='�� ������� ������';
			}
			
			
			if($item['kind_id']==0){
				$can=$can&&false;
			 
				$reasons[]='�� ������ ��� �������';
			}
			
			if($item['eq_type_id']==0){
				$can=$can&&false;
			 
				$reasons[]='�� ������ ��� ������������';
			}
			
			if(strlen($item['topic'])==0){
				$can=$can&&false;
			 
				$reasons[]='�� ������� �������� �������';
			}
			
			 $reason.=implode(', ',$reasons);
			
		}
		
		return $can;
	}
	
	//������ � �����������  ��� ���� � ����������� �������, ������ ������ 
	public function DocCanConfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirm_done']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� ������� ���������� ����������';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� ������� �� ���������� ����������';
		}else{
			
			if($item['manager_id']==0){
				$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
				$reasons[]='� ������� �� ������ ������������� ���������';
			}
			
			 $reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	
	//������ � ����������� ������ ��� ���� � ����������� �������, ������ ������ 
	public function DocCanUnconfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_done']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� ������� �� ���������� ����������';
			$reason.=implode(', ',$reasons);
		} 
		elseif($item['is_fulfiled']!=0){
			$can=$can&&false;
			$reasons[]='� ������� ���������� �������� ������';
			$reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	
	
	//����� ������ ��������� ���������� (�����!)
	protected function FindBindedDocs($id, $item=NULL){
		$docs=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		$sql='select t.*, st.name as status_name from
		lead as t left join document_status as st on t.status_id=st.id
		where t.status_id<>3 and t.tender_id="'.$item['id'].'"
		order by t.id';
		
		//echo $sql;
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->getresultnumrows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$docs[]='<li>��� <a href="ed_lead.php?action=1&id='.$f['id'].'" target="_blank">'.$f['code'].'</a>, ������ '.$f['status_name'].'</li>';
		
		}
		
		return $docs;	
	}
	
	//���� �� ��������� ��������� �� ������ 2 �������?
	public function DocUncomfirmShipDocs($id, &$reason, $item=NULL){
		$has=false;
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		$docs=$this->FindBindedDocs($id, $item);
		$has=(count($docs)>0);
		if($has){
			$reason.=implode(',<br>',$docs);	
		}
		
		return $has;
	}
	
	//���� �� ��������� ��������� �� ������ 3 �������?
	public function DocUncomfirmFulfilDocs($id, &$reason, $item=NULL){
		$has=false;
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		$docs=$this->FindBindedDocs($id, $item);
		$has=(count($docs)>0);
		if($has){
			$reason.=implode('',$docs);	
		}
		
		return $has;
	}
	
	
	
	//�������� � ��������� ������� 
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth(false,false);
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		$setted_status_id=$item['status_id'];
		
	 
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
			if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)){
				//����� ������� �� 2
				
				if
				(
				(isset($new_params['manager_id'])&&($new_params['manager_id']!=0))||
				(!isset($new_params['manager_id'])&&($old_params['manager_id']!=0))
				) {
					$setted_status_id=2;
				}else{
					$setted_status_id=33;
				}
				
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� �������',NULL,931,NULL,'���������� ������ '.$stat['name'],$item['id']);
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)){
				//����� ������� �� 18
				$setted_status_id=18;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� �������',NULL,931,NULL,'���������� ������ '.$stat['name'],$item['id']);
			}
		}elseif(isset($new_params['is_confirmed_done'])&&isset($old_params['is_confirmed_done'])){
		
			if(($new_params['is_confirmed_done']==1)&&($old_params['is_confirmed_done']==0)){
				//����� ������� �� 10
				$setted_status_id=10;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� �������',NULL,931,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
			}elseif(($new_params['is_confirmed_done']==0)&&($old_params['is_confirmed_done']==1)){
				
				
				if
				(
				(isset($new_params['manager_id'])&&($new_params['manager_id']!=0))||
				(!isset($new_params['manager_id'])&&($old_params['manager_id']!=0))
				) {
					$setted_status_id=2;
				}else{
					$setted_status_id=33;
				}
				
				//$setted_status_id=2;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� �������',NULL,931,NULL,'���������� ������ '.$stat['name'],$item['id']);
			}
			
		 
		
		}elseif(isset($new_params['is_fulfiled'])&&isset($old_params['is_fulfiled'])){
			if(($new_params['is_fulfiled']==1)&&($old_params['is_fulfiled']==0)&&($old_params['status_id']==37)){
				//����� ������� c 37 �� 34
				$setted_status_id=34;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� �������',NULL,931,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
			}elseif(($new_params['is_fulfiled']==0)&&($old_params['is_fulfiled']==1)&&($old_params['status_id']==34)){
				//����� ������� c 34 �� 37
				$setted_status_id=37;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� �������',NULL,931,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
			}
		}elseif(!isset($new_params['is_confirmed'])&&!isset($new_params['is_confirmed_done'])&&!isset($new_params['is_fulfiled'])&&($old_params['is_confirmed']==1)&&($old_params['is_confirmed_done']==0)&&($old_params['is_fulfiled']==0)){ 
			
				if
				
				(isset($new_params['manager_id'])&&($new_params['manager_id']!=0)&&($old_params['manager_id']==0))
				 {
					$setted_status_id=2;
				}elseif(isset($new_params['manager_id'])&&($new_params['manager_id']==0)&&($old_params['manager_id']!=0)){
					$setted_status_id=33;
				}
				
				//$setted_status_id=2;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� �������',NULL,931,NULL,'���������� ������ '.$stat['name'],$item['id']);
		}
		
		if(isset($new_params['status_id'])&&isset($old_params['status_id'])){
			//������ ������ ����� - ������������� ��� ���.
			//echo 'zzzzzzzzzzzzzzzzzz'; die();
			if(in_array($new_params['status_id'], array(30,31,32,34,37,36))&&!in_array($old_params['status_id'], array(30,31,32,34,37,36))){

					$this->Edit($id,array('is_confirmed_done'=>1,'confirm_done_pdate'=>time(), 'user_confirm_done_id'=>(int)$_result['id'] ));
					
					$stat=$_stat->GetItemById($new_params['status_id']);
					$log->PutEntry($_result['id'],'�������������� ����������� ���������� �������',NULL,931,NULL,'������������� ���������� ���������� ������� '.$item['code'].' ��� ������������ ������� '.$stat['name'],$item['id']);
					 
			}
			
			//������ ������� - ������������� ���������� ����� ������
			if(in_array($new_params['status_id'], array(36))&&!in_array($old_params['status_id'], array(36))){

					$this->Edit($id,array('is_fulfiled'=>1,'fulfiled_pdate'=>time(), 'user_fulfiled_id'=>0 ));
					
					$stat=$_stat->GetItemById($new_params['status_id']);
					$log->PutEntry($_result['id'],'�������������� ����������� ������ ������ �������',NULL,931,NULL,'������������� ��������� ����� ������ ������� '.$item['code'].' ��� ������������ ������� '.$stat['name'],$item['id']);
					 
			}
			//���������� ������ � ������ - ��������� ����
			if(in_array($new_params['status_id'], array(28))&&!in_array($old_params['status_id'], array(28))){
				$this->Edit($id,array('working_pdate'=>time()));
				
			}
			
			
			
			
			//����� �������� � ���� �������� (26) - � ���� ���������� (23)
			if(in_array($new_params['status_id'], array(23))&&in_array($old_params['status_id'], array(26))){
				
					//��������� ���������
					
					
					$_tm=new Tender_Messages;
					$_tm->SendMessage26to23($id);			
			}
			
			//����� �������� � ���� �������� (26) - � ����������� ����� (37)
			if(in_array($new_params['status_id'], array(37))&&in_array($old_params['status_id'], array(26))){
				
					//��������� ���������
					$_tm=new Tender_Messages;
					$_tm->SendMessage26to37($id);			
			}
			
		}
		
		
		//die();
	}
	 
	
	
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		
		//$res.=', �������: '.$this->ConstructContacts($id, $item).', ������ '.$stat['name'];
		$res.='������, ������ '.$stat['name'];
		
		//������ �-���
		$_sg=new Tender_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($id);
		foreach($sg as $k=>$v){
			$res.=', ���������� '.$v['opf_name'].' '.$v['full_name'];	
		}
		
		//������ �������
		
		
		return $res; //', ������ '.$stat['name'];
	}
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		$res.='������ '.$item['code'].', ������ '.$stat['name'];
		
		//������ �-���
		$_sg=new Tender_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($id);
		foreach($sg as $k=>$v){
			$res.=', ���������� '.$v['opf_name'].' '.$v['full_name'];	
		}
		
		//������ �������
		/*$_sg=new Sched_CityGroup;
		$sg=$_sg->GetItemsByIdArr($id);
		foreach($sg as $k=>$v){
			$res.=', ����� '.$v['name'].', '.$v['okrug_name'].', '.$v['region_name'].', '.$v['country_name'];	
		}
		*/
		return $res; //', ������ '.$stat['name'];
	}
	
	public function ConstructBeginDate($id, $item=NULL){
		 
		
		if($item===NULL) $item=$this->getitembyid($id);  
		
		$res='';
		
		$res.=$item['pdate_beg'].'T'.$item['ptime_beg'];
		
		//if($item['pdate_end']!="") $res.=$item['pdate_end'].'T'.$item['ptime_end'];
		
		return $res; 
		
		//return date('d.m.Y', $item['pdate_beg']);
	}
	
	
	public function ConstructEndDate($id, $item=NULL){
		if($item===NULL) $item=$this->getitembyid($id);  
		
		$res='';
		
	 	if($item['pdate_end']!="") $res.=$item['pdate_end'].'T'.$item['ptime_end'];
		
		return $res; 
	}
	
	
	public function ConstructContacts($id, $item=NULL){
		
		if($item===NULL) $item=$this->GetItemById($id);
		
		 
				 
		
			$_addr=new TenderContactItem;
			$addr=$_addr->GetItemByFields(array('sched_id'=>$id));
			
			
			$_si=new SupplierItem; $_sci=new SupplierContactItem; $_opf=new OpfItem;
			
			$si=$_si->getitembyid($addr['supplier_id']); $opf=$_opf->GetItemById($si['opf_id']);
			$sci=$_sci->getitembyid($addr['contact_id']);
			$res=SecStr($opf['name'].' '.$si['full_name'].', '.$sci['name'].', '.$sci['position']).': '.$addr['value'];
		
		 
		
		
		return $res;
	}
	
	 
}


   

 


/***********************************************************************************************/
//����������� ������ ������
class Tender_Resolver{
	public $instance;
	function __construct($kind_id=1){
		/*switch($kind_id){
			case 1:
				$this->instance= new Sched_TaskItem;
			break;
			case 2:
				$this->instance= new Sched_MissionItem;
			break;
			case 3:
				$this->instance= new Sched_MeetItem;
			break;
			case 4:
				$this->instance= new Sched_CallItem;
			break;
			case 5:
				$this->instance= new Sched_NoteItem;
			break;
			default:
				$this->instance=new Sched_AbstractItem;
			break;
		};*/
		$this->instance=new Tender_Item;
	}
	
	 
}





// ������ ��������
class  Tender_Group extends AbstractGroup {
	protected $_auth_result;
	protected $_view;
	protected $new_list; //������ ����� �������� ��� �������� ������������ � ��������� �� �� ������
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender';
		$this->pagename='tenders.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		
		
		$this->_auth_result=NULL;
		$this->_view=new Tender_ViewsGroup;
		$this->new_list=NULL;
	} 
	
	
	//������ ��������
    public function ShowPos( 
		$template, //0
		DBDecorator $dec, //1
		$can_create=false, //2
		$can_edit=false,  //3
		$from, //4
		$to_page, //5
		$has_header=true,  //6
		$is_ajax=false, //7
		$can_delete=true, //8
		$can_restore=true, //9
		$can_confirm_price=true, //10
		$can_unconfirm_price=true, //11
		$can_confirm_shipping=true, //12
		$can_unconfirm_shipping=true, //13
		$can_confirm_all_shipping=false, //14
		$can_confirm_fulfil=false, //15
		
		$can_unconfirm_all_shipping=false, //16
		$can_unconfirm_fulfil=false, //17
		$can_unconfirm_price_nw=false //18 ������ ��� ��� �� � ������
	 
		
		){
		 
		 if($is_ajax) $sm=new SmartyAj;
		 else $sm=new SmartyAdm;
		 
		 $sm->assign('has_header', $has_header);
		 $sm->assign('can_restore', $can_restore);
		  $sm->assign('can_create', $can_create);
		 $sm->assign('can_delete', $can_delete);
		 $sm->assign('can_confirm_price', $can_confirm_price);
		 $sm->assign('can_unconfirm_price', $can_unconfirm_price);
		 $sm->assign('can_confirm_shipping', $can_confirm_shipping);
		 $sm->assign('can_unconfirm_shipping', $can_unconfirm_shipping);
		 
		 $sm->assign('can_unconfirm_all_shipping', $can_unconfirm_all_shipping);
		 $sm->assign('can_unconfirm_fulfil', $can_unconfirm_fulfil);
		  $sm->assign('can_unconfirm_price_nw', $can_unconfirm_price_nw);
		 
		
		
		//������� ������ ���, ��� ����� ����� ����������� ����������
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 962);
		$sm->assign('can_unconfirm_users',$usg1); 
 
		
		$_au=new authuser;
		if($this->_auth_result===NULL){
			$_result=$_au->Auth();
			$this->_auth_result=$_result;
		}else{
			$_result=$this->_auth_result;	
		}
		$sm->assign('user_id',$_result['id']);
		 
		$_sg=new Tender_SupplierGroup; $man=new DiscrMan;
		
		
		$sql='select distinct p.*,
		s.name as status_name, s.weight as status_weight,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login, p.confirm_done_pdate as confirm_shipping_pdate,
			 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
		uf.name_s as confirmed_fulfil_name, uf.login as confirmed_fulfil_login,
		
		eq.name as eq_name, kind.name as kind_name,
		cur.name as currency_name, cur.signature as currency_signature,
		fz.name as fz_name 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				 
				
				 
				
				left join tender_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				 
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
				
				left join tender_eq_types as eq on eq.id=p.eq_type_id
				
				left join tender_kind as kind on kind.id=p.kind_id
				
				left join pl_currency as cur on p.currency_id=cur.id
				
				left join tender_fz as fz on p.fz_id=fz.id
					 
				 ';
				
		$sql_count='select count(*) 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				 
				
				 
				
				left join tender_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				 
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
				
				left join tender_eq_types as eq on eq.id=p.eq_type_id
				
				left join tender_kind as kind on kind.id=p.kind_id
				left join pl_currency as cur on p.currency_id=cur.id
				
				left join tender_fz as fz on p.fz_id=fz.id	 
				 ';
				
		
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
			$sql_count.=' where '.$db_flt;	
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri('&', $kind_id));
		$navig->SetFirstParamName('from'.$kind_id);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		
		$this->new_list=NULL;
		
		
		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			 
			//���������� ��� ���
			 
		//	������ != 10 !=3 !=1
			//� ������� ���� !=null <now
			
			$expired=false;
			$exp_ptime=NULL;
			if($f['pdate_claiming']!==""){
				$exp_ptime	= Datefromdmy( DateFromYmd($f['pdate_claiming']))+3*24*60*60-1 + (int)substr($f['ptime_claiming'], 0,2)*60*60 + (int)substr($f['ptime_claiming'],3,2)*60;
				
			 
				 
//				echo date('d.m.Y H:i:s', $exp_ptime).'<br>';
			}
			
			if(
			
			($f['status_id']!=10) && ($f['status_id']!=3) && ($f['status_id']!=1) && ($f['status_id']!=30) && ($f['status_id']!=31) && ($f['status_id']!=32) && ($f['status_id']!=32) && ($f['status_id']!=36)
			&& ($f['status_id']!=34) && ($f['status_id']!=36) && ($f['status_id']!=37)
			&&
			($exp_ptime!==NULL) && ($exp_ptime<time())
			
			) $expired=true;
			$f['expired']=$expired; 
			

			 
		//	$f['pdate_beg']=DateFromYmd($f['pdate_beg']);
			
			if($f['pdate_placing']!=="") $f['pdate_placing']=DateFromYmd($f['pdate_placing']);
			
			if($f['pdate_claiming']!=="") $f['pdate_claiming']=DateFromYmd($f['pdate_claiming']);
			
			if($f['pdate_finish']!=="") $f['pdate_finish']=DateFromYmd($f['pdate_finish']);
			
			
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date('d.m.Y H:i:s', $f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			 
			if($f['confirm_shipping_pdate']!=0) $f['confirm_shipping_pdate']=date('d.m.Y H:i:s', $f['confirm_shipping_pdate']);
			else $f['confirm_shipping_pdate']='-';
			
			
			if($f['fulfiled_pdate']!=0) $f['fulfiled_pdate']=date('d.m.Y H:i:s', $f['fulfiled_pdate']);
			else $f['fulfiled_pdate']='-';
			 
				$_res=new Tender_Resolver();
				//$f['contact_value']=$_res->instance->ConstructContacts($f['id'], $f);
			 
			
			$f['can_annul']=$_res->instance->DocCanAnnul($f['id'],$reason,$f)&&(
			
			$can_delete||
			($this->_auth_result['id']==$f['created_id'])
			
			);
			if(!($can_delete||
			($this->_auth_result['id']==$f['created_id']))) $reason='������������ ���� ��� ������ ��������';
			$f['can_annul_reason']=$reason;
			
			
			 
			
			
			 
				$f['suppliers']=$_sg->GetItemsByIdArr($f['id']);	
				
			 
			 
			 //����� ����� ��� ���.
			 $f['can_unconfirm_price']=$can_unconfirm_price||($can_unconfirm_price_nw&&in_array($f['status_id'], array(2,18,33)));
			
			 
				
				//����� ���, ������ ��� ������
				
				if($f['is_confirmed_done']==1) $f['can_unconfirm_done']=$can_confirm_shipping&&($can_unconfirm_all_shipping )&&($f['is_fulfiled']==0);
				else $f['can_confirm_done']=$can_confirm_shipping&&($can_confirm_all_shipping )&&($f['is_fulfiled']==0);
				
				
				
				
				
				
				
				
				//����� ���/������. ����� ������
				$f['can_confirm_fulfil']=$can_confirm_fulfil;
				$f['can_unconfirm_fulfil']=$can_unconfirm_fulfil;
				 
				if($f['is_fulfiled']==1) $f['can_unconfirm_fulfil']=$f['can_unconfirm_fulfil']&&$_res->instance->DocCanUnconfirmFulfil($f['id'],$reason,$f);
				else  $f['can_confirm_fulfil']=$f['can_confirm_fulfil']&&$_res->instance->DocCanConfirmFulfil($f['id'],$reason,$f);
				
			 
			
			$f['max_price_formatted']=number_format($f['max_price'],2,'.',' ');//sprintf("", $f['max_price']);
			
			 
		 
			//�������� ����� "����� ��������"
			$f['new_blocks']=$this->DocumentNewBlocks($f['id'], $this->_auth_result['id']);
			 
			 
			
			
			$f['is_to_fulfil']=false;
			if($can_confirm_fulfil&&($f['is_fulfiled']==0)&&($f['is_confirmed_done']==1)&&in_array($f['status_id'], array(30,31,34,37))){
				$f['is_to_fulfil']=true;
			}
			 
			
			//print_r($f);	
			$alls[]=$f;
		}
		 
		
		//�������� ������ ������
	
		$current_supplier='';
		$user_confirm_id='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
		 
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		$_tks=new TenderKindGroup;
		
		$tks1=$_tks->GetItemsArr();
		$tks[]=array('id'=>'', 'name'=>'-���-');
		foreach($tks1 as $k=>$v) $tks[]=$v;
		$sm->assign('kinds', $tks);
		
		//���� ������������ 
		$_eqs=new Tender_EqTypeGroup;
		$eqs1=$_eqs->GetItemsArr();
		$eqs[]=array('id'=>'', 'name'=>'-���-');
		foreach($eqs1 as $k=>$v) $eqs[]=$v;
		$sm->assign('eqs', $eqs);
		
		
		$sm->assign('can_confirm_fulfil', $can_confirm_fulfil);
		
		
		$sm->assign('can_confirm_all_shipping', $can_confirm_all_shipping);
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		 
		
		$sm->assign('can_edit',$can_edit);
	 
	 	//����� ������������
		$sm->assign('view', $this->_view->GetColsArr($_result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($_result['id']));
		
	 	
	 
		
		//������ ��� ������ ����������
			$link=$dec->GenFltUri();
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
 
		$sm->assign('link',$link);
		
		return $sm->fetch($template); 
	}
	
	
	
	
	//������ ID ��������, ������� ����� ������ ������� ���������
	public function GetAvailableTenderIds($user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//��������� �����������
		//���� �� ���� - �� ��� ��� �������
		if($_man->CheckAccess($user_id,'w',934)){
			$sql='select id from tender';
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
		}else{
			//����
			$sql='select id from tender where manager_id="'.$user_id.'" ';	
			
			//����� ������� ��� ���������
			if($_man->CheckAccess($user_id,'w',940)){
				$sql.=' or manager_id=0 ';	
			}
			
			//��� ������� � ����������
			if($_man->CheckAccess($user_id,'w',941)){
				$sql.=' or manager_id<>0 ';	
			}
			
			//��������� ����� � � � ���������
			if($user_id==63){
				$sql.=' or manager_id=75 ';	
			}
			
			//������������ ������ - ������ ������ � ���������� �����������
			$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if($upos['is_ruk_otd']==1){
			 	$sql.=' or manager_id in(select id from user where department_id="'.$user['department_id'].'")';
					
			} 
			
			//echo $sql;
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
			
		}
		
		 
		//������� -1 ��� ������������
		//if(!$except_me) $arr[]=$user_id;
		//else 
		if(count($arr)==0) $arr[]=-1;
		
		return $arr;	
		
	}   
	
	
	//��������, ����� �� �������� ������ ��� ��������� ��������� ���������� ����������
	public function ScanAvailableByUserId($manager_id, $user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//��������� �����������
		//���� �� ���� - �� ��� ��� �������
		if($_man->CheckAccess($user_id,'w',934)){
			 
			
			return true;
		}else{
			//����
			 
			//����� ������� ��� ���������
			if(($manager_id==0)&&$_man->CheckAccess($user_id,'w',940)){
				//$sql.=' or manager_id=0 ';	
				return true;
			}
			
			//��� ������� � ����������
			if(($manager_id!=0)&&$_man->CheckAccess($user_id,'w',941)){
				return true;
			}
			//echo $sql;
			
			if($manager_id==$user_id){
				return true;	
			}
			
			
			//������������ ������ - ������ ������ � ���������� �����������
			$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if($upos['is_ruk_otd']==1){
			 	//$sql.=' or manager_id in(select id from user where department_id="'.$user['department_id'].'")';
				$user_ids=array();
				$sql=' select id from user where department_id="'.$user['department_id'].'"';
				$set=new mysqlset($sql); $rs=$set->GetResult(); $rc=$set->GetResultnumrows();
				for($i=0; $i<$rc; $i++){
					$f=mysqli_fetch_array($rs); $user_ids[]=$f['id'];	
				}
				if(in_array($manager_id,$user_ids)) return true;	 
			} 
			 
			
		}
		
		 
		return false;
		
	} 
	
	
	
	//����� ����� �������� ��� �������� ����������
	public function CountNewTenders($user_id){
		
		$tender_ids=$this->GetAvailableTenderIds($user_id);
		
		
		
		$man=new DiscrMan;
		
		//
		if($man->CheckAccess($user_id,'w',975)){
			//���� �-�� - ������ � ���-��
			$sql='select count(*) from 
			tender as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			and t.status_id=26
			and t.id not in(select tender_id from tender_view where user_id="'.$user_id.'")
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
			
		  
				$f=mysqli_fetch_array($rs);	
		}elseif($man->CheckAccess($user_id,'w',989)){
		
			$sql='select count(*) from 
			tender as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			and t.manager_id=0
			and t.is_confirmed=1
			and t.is_confirmed_done=0
			and t.id not in(select tender_id from tender_view where user_id="'.$user_id.'")
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
			
		  
				$f=mysqli_fetch_array($rs);	
		
		}
		//echo $f[0];			
		return (int)$f[0];	
			 
	}
	
	
	//����� ����� �������� - ����������� ���������
	public function CountNewTendersExtended($user_id, $do_iconv=true){
		$data=array();
			
		$tender_ids=$this->GetAvailableTenderIds($user_id);
		
		  
		$man=new DiscrMan;
		
		
		//
		if($man->CheckAccess($user_id,'w',975)){
			//���� �-�� - ������ � ���-��
			$sql='select count(*) from 
			tender as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			and t.status_id=26
			/*and t.id not in(select tender_id from tender_view where user_id="'.$user_id.'")*/
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
		
	  
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.* from 
				tender as t
			
				where t.id in ('.implode(', ',$tender_ids).')
				and t.status_id=26
				/*and t.id not in(select tender_id from tender_view where user_id="'.$user_id.'")*/
				order by t.id asc limit 1
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_tender.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_waitc',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'������� ���� ��������!'
				
				);
			}
		}
		
		if($man->CheckAccess($user_id,'w',936)){
			//��������� ����� ������ - ������ � ���-��
			$sql='select count(*) from 
			tender as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			and t.is_confirmed_done=1
			and t.is_fulfiled=0
			/*and t.id not in(select tender_id from tender_view where user_id="'.$user_id.'")*/
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
		
	  
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.* from 
				tender as t
			
				where t.id in ('.implode(', ',$tender_ids).')
				and t.is_confirmed_done=1
				and t.is_fulfiled=0
				/*and t.id not in(select tender_id from tender_view where user_id="'.$user_id.'")*/
				order by t.id asc limit 1
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_tender.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_att',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'��������� ���������� ������!'
				
				);
			}
		}
		
		
		//�� ������ 989
		//��� ������������ ������ ������
			$dec=new DBDecorator();
		//.dep.name
			$dec ->AddEntry(new SqlEntry('pos.name','������������ ������', SqlEntry::LIKE));
			$dec ->AddEntry(new SqlEntry('dep.name','����� ������', SqlEntry::LIKE));
			$dec ->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
			$ug=new UsersSGroup;
			$users=$ug->GetItemsByDecArr($dec);
			 
			$user_ids=array(); foreach($users as $k=>$v) $user_ids[]=$v['id'];
		
		if($man->CheckAccess($user_id,'w',989)
			||in_array($user_id, $user_ids)
		){
			
			 
			$sql='select count(*) from 
			tender as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			and t.manager_id=0
			and t.is_confirmed=1
			and t.is_confirmed_done=0
			/*and t.id not in(select tender_id from tender_view where user_id="'.$user_id.'")*/
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.* from 
					tender as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				and t.manager_id=0
				and t.is_confirmed=1
				and t.is_confirmed_done=0
				/*and t.id not in(select tender_id from tender_view where user_id="'.$user_id.'")*/
				order by t.id asc limit 1
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_tender.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_to_work',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'����� �������'
				
				);
			}
		}
		
		//������� � ������ ������: ������ � ������� ��������� ��� ���������
	 
		$sql='select count(*) from 
		tender as t
		
		where t.id in ('.implode(', ',$tender_ids).')
		and t.manager_id="'.$user_id.'"
		and t.is_confirmed=1
		and t.status_id=2
		and t.is_confirmed_done=0
		/*and t.id not in(select tender_id from tender_view where user_id="'.$user_id.'")*/
		';
		
		//echo $sql;
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$f=mysqli_fetch_array($rs);	
		
		if((int)$f[0]>0){
			//������� ������ ���, ���������� �������� �������
			$sql='select t.* from 
				tender as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			and t.manager_id="'.$user_id.'"
			and t.is_confirmed=1
			and t.status_id=2
			and t.is_confirmed_done=0
			/*and t.id not in(select tender_id from tender_view where user_id="'.$user_id.'")*/
			order by t.id asc limit 1
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$g=mysqli_fetch_array($rs);		
			$url='ed_tender.php?action=1&id='.$g['id'].'&from_begin=1';
			
			$data[]=array(
				'class'=>'menu_new_att',
				'num'=>(int)$f[0],
				'first_url'=>$url,
				'comment'=>'������� � ������ ������!'
			
			);
		}
		 
		
		
		
		//��������� ����� ���������
		$_hg=new Tender_HistoryGroup;
		
		$count_new=$_hg->CalcNew($user_id,$tender_ids, $first_id);
		if($count_new>0){
			$data[]=array(
					'class'=>'menu_new_m',
					'num'=>$count_new,
					'first_url'=>'ed_tender.php?action=1&id='.$first_id.'&from_begin=1#new_comment',
					'comment'=>'����� �����������'
				
				);
		}
		
		/*$data[]=array(
					'class'=>'menu_new_m',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'������� ���� � ������!'
				
				);
		*/
		
		if($do_iconv) foreach($data as $k=>$v) $data[$k]['comment']=iconv('windows-1251', 'utf-8', $v['comment']);
		
		return $data;
	}
	
	
	
	
	
	//�������� �� ������� ������ ��� ������� ������������ � ���������, ���� �������� - ������� ������ ��� ���������� �����
	public function DocumentNewBlocks($document_id, $user_id){
		$data=array();
		
		if($this->new_list===NULL) $this->ConstructNewList($user_id);
		
		 
		
		/*$data[]=array(
					'class'=>'menu_new_m',
					
					'url'=>$url,
					'comment'=>'������� ���� � ������!'
				
				);
		*/
		
		//������������ ������ ������
		foreach($this->new_list as $k=>$type){
			if(in_array($document_id, $type['doc_ids'])){
				
				$url=str_replace('{id}',$document_id, $type['url'], $subst_count);
				
				if($subst_count==0) $url=$type['url'].$document_id;
				$data[]=array(
					'class'=>$type['class'],
					
					'url'=>$url,
					'comment'=>$type['comment'],
					'doc_counters'=>(int)$type['doc_counters'][array_search($document_id, $type['doc_ids'])]
				
				);	
			}
		}
		
		
		return $data;	
	}
	
	
	//��������������� ������ ����� �������� ��� ��������� ������������
	protected function ConstructNewList($user_id){
		$this->new_list=array();
		
		/*
		$this->new_list[]=array(
					'class'=>'menu_new_m',
					'num'=>(int)$f[0],
					'url'=>''
					'doc_ids'=>array(),
					'doc_counters'=>array(),
					'comment'=>'������� ���� � ������!'
				
				);
		*/	
		
		$tender_ids=$this->GetAvailableTenderIds($user_id);
		
		  
		$man=new DiscrMan;
		
		
		//
		if($man->CheckAccess($user_id,'w',975)){
			//���� �-�� - ������ � ���-��
			$sql='select count(*) from 
			tender as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			and t.status_id=26
			/*and t.id not in(select tender_id from tender_view where user_id="'.$user_id.'")*/
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
		
	  
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.id from 
				tender as t
			
				where t.id in ('.implode(', ',$tender_ids).')
				and t.status_id=26
				/*and t.id not in(select tender_id from tender_view where user_id="'.$user_id.'")*/
				order by t.id asc  
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				$doc_ids=array();
				for($j=0; $j<$rc; $j++){ 
				    $g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['id'];
				}
				$this->new_list[]=array(
					'class'=>'reestr_menu_waitc',
					'num'=>(int)$rc,
					'doc_ids'=>$doc_ids,
					'doc_counters'=>array(),
					'url'=>'ed_tender.php?action=1&id=',
					'comment'=>'������ ���� ��������!'
				
				);
			}
		}
		
		if($man->CheckAccess($user_id,'w',936)){
			//��������� ����� ������ - ������ � ���-��
			$sql='select count(*) from 
			tender as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			and t.is_confirmed_done=1
			and t.is_fulfiled=0
			/*and t.id not in(select tender_id from tender_view where user_id="'.$user_id.'")*/
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
		
	  
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.id from 
				tender as t
			
				where t.id in ('.implode(', ',$tender_ids).')
				and t.is_confirmed_done=1
				and t.is_fulfiled=0
				/*and t.id not in(select tender_id from tender_view where user_id="'.$user_id.'")*/
				order by t.id asc 
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array();
				for($j=0; $j<$rc; $j++){ 
				    $g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['id'];
				}
				
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_att',
					'num'=>(int)$rc,
					'doc_ids'=>$doc_ids,
					'doc_counters'=>array(),
					'url'=>'ed_tender.php?action=1&id=',
					'comment'=>'��������� ���������� ������!'
				
				);
			}
		}	
		
		
		//�� ������ 989
		//��� ������������ ������ ������
		
			$dec=new DBDecorator();
		//.dep.name
			$dec ->AddEntry(new SqlEntry('pos.name','������������ ������', SqlEntry::LIKE));
			$dec ->AddEntry(new SqlEntry('dep.name','����� ������', SqlEntry::LIKE));
			$dec ->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
			$ug=new UsersSGroup;
			$users=$ug->GetItemsByDecArr($dec);
			 
			$user_ids=array(); foreach($users as $k=>$v) $user_ids[]=$v['id'];
		
		if($man->CheckAccess($user_id,'w',989)
			||in_array($user_id, $user_ids)
		){
			//������� ���������
			$sql='select count(*) from 
			tender as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			and t.manager_id=0
			and t.is_confirmed=1
			and t.is_confirmed_done=0
			 
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.* from 
					tender as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				and t.manager_id=0
				and t.is_confirmed=1
				and t.is_confirmed_done=0
				 
				order by t.id asc  
				';
				
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array();
				for($j=0; $j<$rc; $j++){ 
				    $g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['id'];
					
					//echo $g['id'].'<br>';
				}
				
				$this->new_list[]=array(
					'class'=>'reestr_menu_to_work',
					'num'=>(int)$rc,
					'doc_ids'=>$doc_ids,
					'doc_counters'=>array(),
					'url'=>'ed_tender.php?action=1&id=',
					'comment'=>'����� ������!'
				
				);
			}
		}
		 
		//������� � ������ ������: ������� � ������� ���-� �� ���������
		$sql='select count(*) from 
		tender as t
		
		where t.id in ('.implode(', ',$tender_ids).')
		and t.manager_id="'.$user_id.'"
		and t.is_confirmed=1
		and t.status_id=2
		and t.is_confirmed_done=0
		/*and t.id not in(select tender_id from tender_view where user_id="'.$user_id.'")*/
		';
		
		//echo $sql;
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$f=mysqli_fetch_array($rs);	
		
		if((int)$f[0]>0){
			//������� ������ ���, ���������� �������� �������
			$sql='select t.* from 
				tender as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			and t.manager_id="'.$user_id.'"
			and t.is_confirmed=1
			and t.status_id=2
			and t.is_confirmed_done=0
			/*and t.id not in(select tender_id from tender_view where user_id="'.$user_id.'")*/
			order by t.id asc  
			';
			
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$doc_ids=array();
			for($j=0; $j<$rc; $j++){ 
				$g=mysqli_fetch_array($rs);		
			 
				$doc_ids[]=$g['id'];
			}
			
			$this->new_list[]=array(
				'class'=>'reestr_menu_new_att',
				'num'=>(int)$rc,
				'doc_ids'=>$doc_ids,
				'doc_counters'=>array(),
				'url'=>'ed_tender.php?action=1&id=',
				'comment'=>'������� � ������ ������!'
			
			);
		}
		
		//��������� ����� ���������	
		$check=time()-7*24*60*60;
		$sql='select count(*) from tender_history where user_id<>"'.$user_id.'" and user_id<>0
		and sched_id in(
			'.implode(', ',$tender_ids).'
		)
		and sched_id not in(select id from tender where status_id=3) 
		and id not in(select history_id from tender_history_view where user_id="'.$user_id.'" )
		and pdate>"'.$check.'"
		';
		//echo $sql.'<br>';
		$set=new mysqlSet($sql); 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$f=mysqli_fetch_array($rs);
		
		if((int)$f[0]>0){
			//������� ������ ����������
			$doc_ids=array(); 	$doc_counters=array();
			
			$sql='select distinct t.id, count(h.id) as s_q from tender_history as h
			inner join tender as t on t.id=h.sched_id
			 where h.user_id<>"'.$user_id.'" and h.user_id<>0
			and h.sched_id in(
				'.implode(', ',$tender_ids).'
			)
			and sched_id not in(select id from tender where status_id=3) 
			 
			and h.id not in(select history_id from tender_history_view where user_id="'.$user_id.'" )
			and h.pdate>"'.$check.'"
			group by h.sched_id
			';
			
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$doc_ids=array();
			for($j=0; $j<$rc; $j++){ 
				$g=mysqli_fetch_array($rs);		
			 
				$doc_ids[]=$g['id'];
				$doc_counters[]=(int)$g['s_q'];
			}
			
			$this->new_list[]=array(
				'class'=>'reestr_menu_new_m',
				'num'=>(int)$rc,
				'doc_ids'=>$doc_ids,
				'doc_counters'=>$doc_counters,
				'url'=>'ed_tender.php?action=1&id={id}&from_begin=1#new_comment',
				'comment'=>'����� �����������'
			
			);
		}
		
		 
		//var_dump($this->new_list);	
	}
	
	
	//�������������� ������� � ������ "������"
	public function AutoFail($annul_status_id=36){
		
		$log=new ActionLog();
		
		$_stat=new DocStatusItem;
		$check_pdate=date('Y-m-d', time()+24*60*60);
		
	 
		//$_ni=new KpNotesItem;
		$_ti=new Tender_Item;
		$_thi=new Tender_HistoryItem;
		
		$sql='select 
		 distinct p.*,
		s.name as status_name,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login, p.confirm_done_pdate as confirm_shipping_pdate,
			 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
		uf.name_s as confirmed_fulfil_name, uf.login as confirmed_fulfil_login,
		
		eq.name as eq_name, kind.name as kind_name,
		cur.name as currency_name, cur.signature as currency_signature,
		fz.name as fz_name 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				 
				
				 
				
				left join tender_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				 
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
				
				left join tender_eq_types as eq on eq.id=p.eq_type_id
				
				left join tender_kind as kind on kind.id=p.kind_id
				
				left join pl_currency as cur on p.currency_id=cur.id
				
				left join tender_fz as fz on p.fz_id=fz.id
		
		
		
		where p.is_confirmed=1 and p.status_id in(33, 2, 28) and p.pdate_claiming<="'.$check_pdate.'" order by p.code desc';
		
		$set=new MysqlSet($sql);
		
		//echo $sql; 
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
		 
			
			
			
			
			
			
			
		 
				$_ti->Edit($f['id'], array('status_id'=>$annul_status_id),  true,  array('id'=>0));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
				$comment='� �������: '.$f['code'].' ���������� ������ '.$stat['name'].', �������: �����, ��� 48 ���. �� ����� ������ ������';
				
				$log->PutEntry(0,'�������������� ����� ������� �������',NULL,931,NULL,$comment,$f['id']);
				
				//������� ����������
				$_thi=new Tender_HistoryItem;
				$t_params=array();
				$t_params['sched_id']=$f['id'];
				$t_params['txt']=SecStr('<div>�������������� �����������: '.$comment.'</div>');
				$t_params['user_id']=0;
				$t_params['pdate']=time();
				
				$_thi->Add($t_params);
				 
		}
		
	}
	
	
	
	//�������������� �������������
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		
		$log=new ActionLog();
		
		$_stat=new DocStatusItem;
		$_res=new Tender_Item;
	 
		$_ni=new Tender_HistoryItem;
		 
		
		$set=new MysqlSet('select * from '.$this->tablename.' where status_id<>'.$annul_status_id.' and status_id=18 order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			$reason='';
			
			
			 
			
			
			
			//������ 1 - ��� ������ �������:
			if($f['is_confirmed']==0){
				
				
					
				//�������� ���� ��������������
				if($f['restore_pdate']>0){
					if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;	
						$reason='������ ����� '.$days_after_restore.' ���� � ���� �������������� �������,  �������� �� ���������';
					}
				}else{
					//�������� � ����� ��������	
					
					
					if(($f['pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='������ ����� '.$days.' ���� � ���� �������� �������,  �������� �� ���������';
					}
				}
			 }
			 
			
			
			
			
			
			
			if($can_annul){
				 
					//$_res->instance->Edit($id, $params);
				
				$_res->Edit($f['id'], array('is_confirmed'=>0, 'status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
				
				$log->PutEntry(0,'�������������� ������������� �������',NULL,931,NULL,'� ���������: '.$f['code'].' ���������� ������ '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'sched_id'=>$f['id'],
			 
				'pdate'=>time(),
				'user_id'=>0,
				'txt'=>'�������������� ����������: ������ ��� ������������� �����������, �������: '.$reason.'.'
				)); 
					
			}
		}
		
	}
	
	
	
	
}





   












//��� �������
class TenderKindItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_kind';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_active';	
		//$this->subkeyname='mid';	
	}
	

}

//������ ����� �������
class TenderKindGroup extends AbstractGroup {

	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_kind';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_active';		
		
		
		
	}
	
	//������ �������
	public function GetItemsArr(){
		$arr=array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		 $set=new MysqlSet('select * from '.$this->tablename.' order by  name asc');
		 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			//$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	//������ ������� ��� ������ � ���� �������
	public function GetItemsList($can_add, $can_del){
		$arr=array();
		 
		
		$sql='select p.*, count(pt.id) as s_q from '.$this->tablename.' as p 
		left join tender as pt on pt.kind_id=p.id  and pt.status_id<>3
		group by p.id
		order by  p.name asc';
		//echo $sql;
				
		$set=new MysqlSet($sql);
		 
		 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			//$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['can_delete']=   ($can_del&&((int)$f['s_q']==0));
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
}





// ���������� ��������� �-��
class Tender_SupplierContactGroup extends SupplierContactGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='supplier_contact';
		$this->pagename='view.php';		
		$this->subkeyname='supplier_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	//������ �������
	public function GetItemsByIdArr($id, $current_id=0, $current_k_id=0){
		$arr=array();
		
                // KSK - 01.04.2016
                // ��������� ������� ������ ������� ��� ����������� is_shown=1
		//$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" order by id asc');
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and '.$this->vis_name.'=1 order by id asc');
		
		
		
		$_sdg=new Tender_SupplierContactDataGroup;
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			$f['data']=$_sdg->GetItemsByIdArr($f['id'], $current_k_id, array(1,2,3,4,5,6,7,8) );
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	
}


//  ������ ��������� �������� �����������
class Tender_SupplierContactDataGroup extends SupplierContactDataGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='supplier_contact_data';
		$this->pagename='view.php';		
		$this->subkeyname='contact_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//������ �������
	public function GetItemsByIdArr($id, $current_id=0, array $kinds){
		$arr=Array();
		$set=new MysqlSet('select p.*,
			pc.name as pc_name, pc.icon as pc_icon
		
		 from '.$this->tablename.' as p left join supplier_contact_kind as pc on pc.id=p.kind_id
		  where p.'.$this->subkeyname.'="'.$id.'" and pc.id in('.implode(', ', $kinds).') order by p.id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
}











  



// users S
class Tender_UsersSGroup extends UsersSGroup {
	protected $group_id;
	public $instance;
	public $pagename;

	
	//��������� ���� ����
	protected function init(){
		$this->tablename='user';
		$this->pagename='users_s.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_active';		
		$this->group_id=1;
		$this->instance=new UserSItem;

	}
	
	 
	
	
	
	
	//����� ����������� ��� ������ � ������ ����
	public function GetItemsForBill(  DBDecorator $dec){
		$txt='';
		
		 
		
		$sql='select p.*, up.name as position_s, p.id as user_id, dep.name as dep_name from '.$this->tablename.' as p 
		
		left join user_position as up on up.id=p.position_id
		left join user_department as dep on dep.id=p.department_id
		 where p.group_id="'.$this->group_id.'"

			 ';
		
	
		
		 
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		/*if(strlen($db_flt)>0) $sql.=' and ';
		else */$sql.=' and ';
		
		$sql.=' p.is_active=1 ';
		
		
		
		$sql.=' order by p.name_s asc, p.login asc ';
		
		
		//echo $sql;
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	//	$total=$set->GetResultNumRowsUnf();
		
		
		$alls=array();
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
		
			//print_r($f);
			$alls[]=$f;
		}
		
		 
	 
		
		return $alls;
	}
	
	
	
	 
}



   







/*************************************************************************************************/


//����������� �������
class Tender_SupplierItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_suppliers';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}



class Tender_SupplierGroup extends AbstractGroup {
	 
	public $pagename;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_suppliers';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	//����� ����������� ��� ������� ������������
	public function GetItemsForBill($template, DBDecorator $dec, $is_ajax=false, &$alls,$resu=NULL, $current_id=0){
		$_csg=new SupplierCitiesGroup;
		
		$txt='';
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$au=new AuthUser();
		if($resu===NULL) $resu=$au->Auth(false,false);
		
		$sql='select p.*, po.name as opf_name from supplier as p 
			left join opf as po on p.opf_id=po.id  ';
		
	
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		if(strlen($db_flt)>0) $sql.=' and ';
		else $sql.=' where ';
		
		//$sql.='  p.is_active=1 ';
		
		$sql.='(( p.is_org=0 and p.is_active=1 and p.org_id='.$resu['org_id'].') or (p.is_org=1 and p.is_active=1 and p.id<>'.$resu['org_id'].')) ';
		
		
		
		$sql.=' order by p.full_name asc ';
		
		/*$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}*/
		
		//echo $sql;
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	//	$total=$set->GetResultNumRowsUnf();
		
		
		$alls=array(); $_acc=new SupplierItem;
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//
			$csg=$_csg->GetItemsByIdArr($f['id']);
			$f['cities']= $csg;	 
			 
			$f['is_current']=($f['id']==$current_id);
			
			//print_r($f);
			$alls[]=$f;
		}
		
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
	
		$sm->assign('items',$alls);
		
		if($is_ajax) $sm->assign('pos',$alls);
		
		
		
		
		//������ ��� ������ ����������
		$link=$dec->GenFltUri();
		$link='suppliers.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	 
	//������ �������, ����� ����
	public function GetItemsByIdArr($id){
		$arr=array(); $_csg=new SupplierCitiesGroup;
		$_sc=new Tender_ScGroup;
		
		$sql='select ct.sched_id, ct.id as c_id, ct.note,  s.full_name, opf.name as opf_name,
		s.id as supplier_id, s.id as id
		
		 from '.$this->tablename.' as ct 
		 left join supplier as s on ct.supplier_id=s.id
		 left join opf as opf on s.opf_id=opf.id
		  
		
		where ct.sched_id="'.$id.'" order by s.full_name asc';
		 
		
		 
		 
		
		//echo $sql."<p>";
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			$csg=$_csg->GetItemsByIdArr($f['supplier_id']);
			$f['cities']= $csg;	
			
			$f['contacts']=$_sc->GetItemsByIdArr( $f['c_id']);
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	
	//������� �������
	public function AddSuppliers($current_id, array $positions,  $result=NULL){
		$_kpi=new Tender_SupplierItem; $_cg=new Tender_ScGroup;
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$this->GetItemsByIdArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('sched_id'=>$v['sched_id'],'supplier_id'=>$v['supplier_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				 
				
				$add_array=array();
				$add_array['sched_id']=$v['sched_id'];
				$add_array['supplier_id']=$v['supplier_id'];
				
				$add_array['note']=$v['note'];
				
			 
				
				 
				$code=$_kpi->Add($add_array);
				
				$_cg->ClearById($code); $_cg->PutIds($code, $v['contacts']);
				
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'note'=>$v['note']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['sched_id']=$v['sched_id'];
				$add_array['supplier_id']=$v['supplier_id'];
				
				$add_array['note']=$v['note'];
				 
				$_kpi->Edit($kpi['id'],$add_array);
				
				$_cg->ClearById($kpi['id']); 
				$_cg->PutIds($kpi['id'], $v['contacts']);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//���� ���� ���������
				
				//��� ����������? ���������� prava
				
				$to_log=false;
				if($kpi['city_id']!=$add_array['city_id']) $to_log=$to_log||true;
				if($kpi['supplier_id']!=$add_array['supplier_id']) $to_log=$to_log||true;
				if($kpi['note']!=$add_array['note']) $to_log=$to_log||true;
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					 
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'note'=>$v['note']
				  );
				}
				
			}
		}
		
		//����� � ������� ��������� �������:
		//����. ���. - ��� �������, ������� ��� � ������� $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(($vv['supplier_id']==$v['supplier_id'])&&($vv['sched_id']==$v['sched_id'])
				 
				){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//������� ��������� �������
		foreach($_to_delete_positions as $k=>$v){
			
			//��������� ������ ��� �������
			 
			
			$log_entries[]=array(
					'action'=>2,
					
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'note'=>$v['note']
			);
			
			//������� �������
			$_kpi->Del($v['c_id']);
			
			$_cg->ClearById($v['c_id']);
		}
		
		
		//����� ��� ��������� ���� � � ��� ���������� ���������� ������
		//$current_id, array $positions,
		$sql='select id from lead where tender_id="'.$current_id.'"';
		$set=new mysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$_li=new Lead_Item;
		$_lis=new Lead_SupplierGroup;
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$lead_pos=array();
			foreach($positions as $k=>$v){
				if($k==0){
				$lead_pos[]=array(
					  'sched_id'=>$f['id'],
					   
					  'supplier_id'=>$v['supplier_id'],
					  'contacts'=>$v['contacts'],
					  'note'=> $v['note']
				);	
				}
			}
				
			$_lis->AddSuppliers($f['id'], $lead_pos, $result);
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	
	
	
	//������� ������������ ��� ���������
	public function AddSuppliersWoCont($current_id, array $positions,  $result=NULL){
		$_kpi=new Tender_SupplierItem;  
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$this->GetItemsByIdArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('sched_id'=>$v['sched_id'],'supplier_id'=>$v['supplier_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				 
				
				$add_array=array();
				$add_array['sched_id']=$v['sched_id'];
				$add_array['supplier_id']=$v['supplier_id'];
				
				$add_array['note']=$v['note'];
				
			 
				
				 
				$code=$_kpi->Add($add_array);
				 
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'note'=>$v['note']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['sched_id']=$v['sched_id'];
				$add_array['supplier_id']=$v['supplier_id'];
				
				$add_array['note']=$v['note'];
				 
				$_kpi->Edit($kpi['id'],$add_array);
				
			 
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//���� ���� ���������
				
				//��� ����������
				
				$to_log=false;
				 
				if($kpi['supplier_id']!=$add_array['supplier_id']) $to_log=$to_log||true;
				if($kpi['note']!=$add_array['note']) $to_log=$to_log||true;
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					 
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'note'=>$v['note']
				  );
				}
				
			}
		}
		
		//����� � ������� ��������� �������:
		//����. ���. - ��� �������, ������� ��� � ������� $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(($vv['supplier_id']==$v['supplier_id'])&&($vv['sched_id']==$v['sched_id'])
				 
				){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//������� ��������� �������
		foreach($_to_delete_positions as $k=>$v){
			
			//��������� ������ ��� �������
			 
			
			$log_entries[]=array(
					'action'=>2,
					
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'note'=>$v['note']
			);
			
			//������� �������
			$_kpi->Del($v['c_id']);
			
		 
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	 
	
}




/***********************************************************************************************/


//��� ������������ 
class Tender_EqTypeItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_eq_types';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}



class Tender_EqTypeGroup extends AbstractGroup {
	 
	public $pagename;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_eq_types';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	
	//������ �������
	public function GetItemsArr(){
		$arr=array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		 $set=new MysqlSet('select * from '.$this->tablename.' order by  name asc');
		 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			//$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//����� � ����� option
	public function GetItemsOptDec($current_id=0, $decorator, $fieldname='name', $do_no=false, $no_caption='-��������-'){
		$txt='';
		$sql='select * from '.$this->tablename.' ';
		
		 $db_flt=$decorator->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
			 	
		}
		
		
		$sql.=' order by '.$fieldname.' asc';
		
		//echo $sql;
		
		
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		if($do_no){
		  $txt.="<option value=\"0\" ";
		  if($current_id==0) $txt.='selected="selected"';
		  $txt.=">". $no_caption."</option>";
		}
		
		if($tc>0){
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				$txt.="<option value=\"$f[id]\" ";
				
				if($current_id==$f['id']) $txt.='selected="selected"';
				
				$txt.=">".htmlspecialchars(stripslashes($f[$fieldname]))."</option>";
			}
		}
		return $txt;
	}
	
	
	
}


/****************************************************************************************************/
//�������� ����������� �� �������	
class Tender_ScItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_suppliers_contacts';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sc_id';	
	}
	
}



class Tender_ScGroup extends AbstractGroup {
	 
	public $pagename;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_suppliers_contacts';
		$this->pagename='view.php';		
		$this->subkeyname='sc_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	//������, ����� ����
	public function GetItemsByIdArr($id){
		$arr=Array();
		
		$sql='select p.sc_id, p.contact_id, p.contact_id as id, p.id as c_id,
		
		c.name, c.position
		
		
		
		 from '.$this->tablename.' as p
		 
		 inner join supplier_contact as c on p.contact_id=c.id
		 
		  where p.sc_id="'.$id.'" order by  c.name asc';
		//echo $sql;
		 $set=new MysqlSet($sql);
	 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
 	
	public function ClearById($id){
		$sql='delete
		
		 from '.$this->tablename.' 
		 
		 
		  where  sc_id="'.$id.'" ';
		  
		  //echo $sql; die();
		
		 $set=new nonSet($sql);
	}
	
	public function PutIds($id, $ids){
		
		$sql='insert into  '.$this->tablename.' (sc_id, contact_id) values ';
		
		$_pairs=array();
		foreach($ids as $k=>$v) $_pairs[]=' ("'.$id.'", "'.$v.'") ';
		$sql.= implode(', ',$_pairs);
		
		if(count($ids)>0) $set=new nonSet($sql);
	}
		
}




/************************************************************************************************/

//���� �� 
class Tender_FZItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_fz';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}



class Tender_FZGroup extends AbstractGroup {
	 
	public $pagename;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_fz';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	
	//������ �������
	public function GetItemsArr(){
		$arr=array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		 $set=new MysqlSet('select * from '.$this->tablename.' order by  id asc');
		 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			//$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
}



/*****************************************************************************************************/


//������� ������
class Tender_FailItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='lead_fail';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}



class Tender_FailGroup extends AbstractGroup {
	 
	public $pagename;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='lead_fail';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	
	//������ �������
	public function GetItemsArr(){
		$arr=array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		 $set=new MysqlSet('select * from '.$this->tablename.' order by  id asc');
		 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			//$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
}



/*****************************************************************************************************/
//������� ������� �������� ���������
class Tender_CountsItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_counts';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}



/****************************************************************************************************/
//������ � ����� �������� ��� ����������� ����
class Tender_MarkerItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_marker';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}

//������, ��� �� ���������� ������� ����� 24/12 ����� ��������
class Tender_MarkerRefuseItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_marker_refuse';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}


//����� ��� ��������� ������ ��� ����������� ����
class Tender_PopupGroup extends AbstractGroup{
	
	//������ ����� �������� ��� ���� (1) - � ������ ������
	public function CalcKind1(){
		
		$check_pdate=date('Y-m-d', time()+48*60*60);
		
		$sql='select count(*) from tender where is_confirmed=1 and status_id in(33, 2, 28, 26) and pdate_claiming<="'.$check_pdate.'"';
		
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		return (int)$f[0];
		
			
		
	}
	
	//������ ����� �������� ��� ���� (2) - ��� �����
	public function CalcKind2($user_id){
		
		$check_pdate=  time()-48*60*60 ;
		$check_pdate1= date('Y-m-d', time()+5*24*60*60 ); 
		
		
		$sql='select count(*) from tender where is_confirmed=1 and (status_id=28 and working_pdate<= "'.$check_pdate.'" ) and manager_id="'.$user_id.'" and id not in(select distinct tender_id from lead where is_confirmed=1)';
		
//		echo $sql;
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		return (int)$f[0];
		
			
		
	}
	
	//������ ����� �������� ��� ���� (3) - ���������� ���� ������ ������
	public function CalcKind3($user_id){
		
		$check_pdate=date('Y-m-d', time()+5*24*60*60);
		
		$sql='select count(*) from tender where is_confirmed=1 and status_id in(33, 2, 28) and pdate_claiming<="'.$check_pdate.'" and manager_id="'.$user_id.'" ';
		
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		return (int)$f[0];
		
			
		
	}
	
	//������ ����� �������� ��� ���� (4) - ������ ����� - ������� � ������
	public function CalcKind4(){
		
	//	$check_pdate=date('Y-m-d', time()+5*24*60*60);
		
		$sql='select count(*) from tender where is_confirmed=1 and status_id in(33)';
		
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		return (int)$f[0];
		
			
		
	}
	
	//������ ����� �������� ��� ���� (5.6.7) - ������ ��������� - ������� � ������
	public function CalcKind567($user_id){
		
	//	$check_pdate=date('Y-m-d', time()+5*24*60*60);
		
		$sql='select count(*) from tender where is_confirmed=1 and status_id in(2) and manager_id="'.$user_id.'"';
		
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		return (int)$f[0];
		
			
		
	}
	
	//������ ����� �������� ��� ���� (8) - ������ � ������ - 24 ���� � ����� ��� �������������
	public function CalcKind8($user_id){
		
		$check_pdate=date('Y-m-d', time()+5*24*60*60);
		
		 
		
		$sql='select count(*) from tender where is_confirmed=1 and status_id in ( 28) and manager_id="'.$user_id.'" and pdate_claiming<="'.$check_pdate.'" and id not in(select distinct sched_id from tender_history where  user_id<>0 and pdate between "'.(time()-24*60*60).'" and "'.time().'") ';
		
//		echo $sql;
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		return (int)$f[0];
		
			
		
	}
	
	
	
	//����� ����� �������� ���� (1) - � ������ ������
	public function ShowKind1(){
		$sql=$this->GainSql();
		
		$check_pdate=date('Y-m-d', time()+48*60*60);
		
		$sql.=' where p.is_confirmed=1 and p.status_id in(33, 2, 28, 26) and p.pdate_claiming<="'.$check_pdate.'" order by p.code desc';
		
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$arr=array();
		for($i=0;$i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			
			$this->ProcessFields($f);
			
			$arr[]=$f;		
		}
		
		
		
		return $arr;
	}
	
	//����� ����� �������� ���� (2) - ��� �����, ��� �����. ����.
	public function ShowKind2($user_id){
		$sql=$this->GainSql();
		
		$check_pdate= time()-48*60*60 ; 
		$check_pdate1= date('Y-m-d', time()+5*24*60*60 ); 
		
		
		$sql.=' where  p.is_confirmed=1 and (p.status_id=28 and p.working_pdate<= "'.$check_pdate.'" ) and p.manager_id="'.$user_id.'" and p.id not in(select distinct tender_id from lead where is_confirmed=1)   order by p.code desc';
		
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		
		$arr=array();
		for($i=0;$i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			
			$this->ProcessFields($f);
			
			//�������� ����� ����� - ����, ���, �����
			$sql2='select p.*, st.name as status_name from lead as p
			left join document_status as st on st.id=p.status_id
			where
			p.tender_id='.$f['id'].'
			order by p.code desc';
			
			$leads=array();
			
			$set2=new mysqlset($sql2);
			$rs2=$set2->GetResult();
			$rc2=$set2->GetResultNumRows();
			for($j=0;$j<$rc2; $j++){
				$g=mysqli_fetch_array($rs2);
				$leads[]=$g;	
			}
			$f['leads']=$leads;
				
			$arr[]=$f;		
		}
		
		
		
		return $arr;
	}
	
	
	
	
	//����� ����� �������� ���� (3) - ������������
	public function ShowKind3($user_id){
		$sql=$this->GainSql();
		
		$check_pdate=date('Y-m-d', time()+5*24*60*60);
		
		$sql.=' where p.is_confirmed=1 and p.status_id in(33, 2, 28) and p.pdate_claiming<="'.$check_pdate.'" and p.manager_id="'.$user_id.'" order by p.code desc';
		
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$arr=array();
		for($i=0;$i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			
			$this->ProcessFields($f);
			
			$arr[]=$f;		
		}
		
		
		
		return $arr;
	}
	
	
	//����� ����� �������� ���� (4) - ������ �����, ������� � ������
	public function ShowKind4($user_id, &$fail_ids, &$fail_vals){
		$sql=$this->GainSql($user_id,1);
		
		//$check_pdate=date('Y-m-d', time()+5*24*60*60);
		
		$sql.=' where p.is_confirmed=1 and p.status_id in(33) order by p.code desc';
		
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$arr=array();
		for($i=0;$i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			
			$this->ProcessFields($f);
			
			$arr[]=$f;		
		}
		
		//������� ������
		$_fails=new Lead_FailGroup;
		$fails=$_fails->GetItemsArr();
		$fail_ids=array(); $fail_vals=array();
		$fail_ids[]=0; $fail_vals[]='-��������-';
		foreach($fails as $k=>$v){
			$fail_ids[]=$v['id'];
			$fail_vals[]=$v['name'];
		}
		
		return $arr;
	}
	
	
	
	
	//����� ����� �������� ���� (5,6,7) - ������ ���������, ������� � ������
	public function ShowKind567($user_id, &$fail_ids, &$fail_vals){
		$sql=$this->GainSql($user_id,2);
		
		//$check_pdate=date('Y-m-d', time()+5*24*60*60);
		
		$sql.=' where p.is_confirmed=1 and p.status_id in(2) and p.manager_id="'.$user_id.'" order by p.code desc';
		
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$arr=array();
		for($i=0;$i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			
			$this->ProcessFields($f);
			
			$arr[]=$f;		
		}
		
		//������� ������
		$_fails=new Lead_FailGroup;
		$fails=$_fails->GetItemsArr();
		$fail_ids=array(); $fail_vals=array();
		$fail_ids[]=0; $fail_vals[]='-��������-';
		foreach($fails as $k=>$v){
			$fail_ids[]=$v['id'];
			$fail_vals[]=$v['name'];
		}
		
		return $arr;
	}
	
	
	
	//����� ����� �������� ���� (8) - ������ � ������, 24 � � ����� ��� ������������
	public function ShowKind8($user_id, &$fail_ids, &$fail_vals){
		$sql=$this->GainSql($user_id,0);
		
		$check_pdate=date('Y-m-d', time()+5*24*60*60);
		
		$sql.=' where p.is_confirmed=1 and p.status_id in(28) and p.pdate_claiming<="'.$check_pdate.'" and p.manager_id="'.$user_id.'"  and p.id not in(select distinct sched_id from tender_history where  user_id<>0 and pdate between "'.(time()-24*60*60).'" and "'.time().'") order by p.code desc';
		
	 
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$arr=array();
		for($i=0;$i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			
			$this->ProcessFields($f);
			
			$arr[]=$f;		
		}
		
		//������� ������
		$_fails=new Lead_FailGroup;
		$fails=$_fails->GetItemsArr();
		$fail_ids=array(); $fail_vals=array();
		$fail_ids[]=0; $fail_vals[]='-��������-';
		foreach($fails as $k=>$v){
			$fail_ids[]=$v['id'];
			$fail_vals[]=$v['name'];
		}
		
		return $arr;
	}
	
	
	//�������� ��������� � �������� ���� (9,10) - 24/12 ��� �� ������������ � ������ ������ 36
	public function Send910($kind_id){
		$sql=$this->GainSql();
		
		if($kind_id==9) {
			$check_pdate=date('Y-m-d', time()+24*60*60);
			$check_ptime=date('H:i:s', time()+24*60*60);
		}else{
			$check_pdate=date('Y-m-d', time()+36*60*60);
			$check_ptime=date('H:i:s', time()+36*60*60);
			  
		}
		
		$sql.=' where p.is_confirmed=1 and p.status_id in(33, 2, 28) 
		and (p.pdate_claiming<"'.$check_pdate.'" 
			or (p.pdate_claiming="'.$check_pdate.'" and p.ptime_claiming>="'.$check_ptime.'")
			)
			 and p.id not in(select tender_id from tender_marker_refuse where kind_id="'.$kind_id.'")  order by p.code desc';
		
		//echo $sql;
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$arr=array();
		for($i=0;$i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			
			$this->ProcessFields($f);
			
			$arr[]=$f;		
		}
		
		//������� ����������, �������� ���������
		//print_r($arr);
		
		$_ui=new UserSItem;
		 
		$_mi=new MessageItem;	
		$_dem=new Tender_Item;
		
		$_marker=new Tender_MarkerRefuseItem;
			
		foreach( $arr as $k=>$doc){
			 
			$_marker->Add(array('tender_id'=>$doc['id'], 'kind_id'=>$kind_id));
			
			$_ui=new UserSItem;
			$signer=$_ui->GetItemById($doc['manager_id']);
			
			$topic="������� ������� � ������ ������ ����� ";
			if($kind_id==9) $topic.=' 24 ����';
			else   $topic.=' 12 �����';
			
			$txt='<div>';
			$txt.='<em>������ ��������� ������������� �������������.</em>';
			$txt.=' </div>';
			
			
			$txt.='<div>&nbsp;</div>';
			
			$txt.='<div>';
			$txt.='���������(��) '.$signer['name_s'].'!';
			$txt.='</div>';
			$txt.='<div>&nbsp;</div>';
			
			
			$txt.='<div>';
			$txt.='<strong>��������� ������ ����� ��������� � ������ ������ ����� ';
			if($kind_id==9) $txt.=' 24 ����';
			else   $txt.=' 12 �����';
			
			$txt.=':</strong>';
			$txt.='</div><ul>';
			
			$txt.='<li><a href="ed_tender.php?action=1&id='.$doc['id'].'&from_begin=1" target="_blank">'.$_dem->ConstructFullName($doc['id'], $doc).'</a></li>';
	  
			$txt.='</ul>';
			
			
		 
			
			$txt.='<div>&nbsp;</div>';
			
			$txt.='<div>��������� ������ �� ������������ ������������. ���� �� �� �������� �����, �� ������ ����� ������������� ��������� � ������ ������.</div>';
			
			$txt.='<div>&nbsp;</div>';
		
			$txt.='<div>';
			$txt.='C ���������, ��������� "'.SITETITLE.'".';
			$txt.='</div>';
			
			$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$signer['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
			
			//������ ����� - ������������ ������ ������
			$dec=new DBDecorator();
		//.dep.name
			$dec ->AddEntry(new SqlEntry('pos.name','������������ ������', SqlEntry::LIKE));
			$dec ->AddEntry(new SqlEntry('dep.name','����� ������', SqlEntry::LIKE));
			$dec ->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
			$ug=new UsersSGroup;
			$users=$ug->GetItemsByDecArr($dec); $uids=array();
		   // foreach($users as $k=>$v) $uids[]=$v['id'];
		   
		   foreach($users as $kk=>$vv) {
			   $_ui=new UserSItem;
				$signer=$_ui->GetItemById($vv['id']);
				
				$topic="������� ������� � ������ ������ ����� ";
				if($kind_id==9) $topic.=' 24 ����';
				else   $topic.=' 12 �����';
				
				$txt='<div>';
				$txt.='<em>������ ��������� ������������� �������������.</em>';
				$txt.=' </div>';
				
				
				$txt.='<div>&nbsp;</div>';
				
				$txt.='<div>';
				$txt.='���������(��) '.$signer['name_s'].'!';
				$txt.='</div>';
				$txt.='<div>&nbsp;</div>';
				
				
				$txt.='<div>';
				$txt.='<strong>��������� ������ ����� ��������� � ������ ������ ����� ';
				if($kind_id==9) $txt.=' 24 ����';
				else   $txt.=' 12 �����';
				
				$txt.=':</strong>';
				$txt.='</div><ul>';
				
				$txt.='<li><a href="ed_tender.php?action=1&id='.$doc['id'].'&from_begin=1" target="_blank">'.$_dem->ConstructFullName($doc['id'], $doc).'</a></li>';
		  
				$txt.='</ul>';
				
				
			 
				
				$txt.='<div>&nbsp;</div>';
				
				$txt.='<div>��������� ������ �� ������������ ������������. ���� �� �� �������� �����, �� ������ ����� ������������� ��������� � ������ ������.</div>';
				
				$txt.='<div>&nbsp;</div>';
			
				$txt.='<div>';
				$txt.='C ���������, ��������� "'.SITETITLE.'".';
				$txt.='</div>';
				
				$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$signer['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);
		   }
		   
			
		}
		
		
		return $arr;
	}
	
	
	
	protected $_auth_result, $_sg;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender';
		$this->pagename='tenders.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		$this->_sg=new Tender_SupplierGroup;
		
		$this->_auth_result=NULL;
	} 
	
	
	protected function GainSql($user_id=0, $refuse_kind_id=0){
		$sql='select distinct p.*,
		s.name as status_name,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login, p.confirm_done_pdate as confirm_shipping_pdate,
			 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
		uf.name_s as confirmed_fulfil_name, uf.login as confirmed_fulfil_login,
		
		eq.name as eq_name, kind.name as kind_name,
		cur.name as currency_name, cur.signature as currency_signature,
		fz.name as fz_name, lc.value as refuse_counts
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				 
				
				 
				
				left join tender_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				 
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
				
				left join tender_eq_types as eq on eq.id=p.eq_type_id
				
				left join tender_kind as kind on kind.id=p.kind_id
				
				left join pl_currency as cur on p.currency_id=cur.id
				
				left join tender_fz as fz on p.fz_id=fz.id
				left join tender_counts as lc on lc.tender_id=p.id and lc.user_id="'.$user_id.'" and lc.kind_id="'.$refuse_kind_id.'"
					 
				 ';
		return $sql;		 	
	}
	
	//��������� ���� ����� �������
	protected function ProcessFields(&$f){
		foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['refuse_counts']=(int)$f['refuse_counts'];
			
			if($f['pdate_placing']!=="") $f['pdate_placing']=DateFromYmd($f['pdate_placing']);
			
			if($f['pdate_claiming']!=="") $f['pdate_claiming']=DateFromYmd($f['pdate_claiming']);
			
			if($f['pdate_finish']!=="") $f['pdate_finish']=DateFromYmd($f['pdate_finish']);
			
			
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date('d.m.Y H:i:s', $f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			 
			if($f['confirm_shipping_pdate']!=0) $f['confirm_shipping_pdate']=date('d.m.Y H:i:s', $f['confirm_shipping_pdate']);
			else $f['confirm_shipping_pdate']='-';
			
			
			if($f['fulfiled_pdate']!=0) $f['fulfiled_pdate']=date('d.m.Y H:i:s', $f['fulfiled_pdate']);
			else $f['fulfiled_pdate']='-';
			 
			
			 
			$f['suppliers']=$this->_sg->GetItemsByIdArr($f['id']);	
				
			 
			
			$f['max_price_formatted']=number_format($f['max_price'],2,'.',' ');//sprintf("", $f['max_price']);
			
			
			
		
	}
}

//������ ������� ������� ��� ������ �������
class Tender_LogAn{
	
	//�������� ����������� ������ � ���� ������
	public function GetRefuses($user_id, $tender_id, $object_id, $ac_name, $ac_name1, $ac_name2, $ac_name3){
		$txt='';
		$now=time();
		
		//����� ���� ������ ������� ����������� �������� ������
		$prev_id=NULL;
		$sql='select * from action_log where pdate<="'.$now.'" and user_subj_id="'.$user_id.'" and  	object_id="'.$object_id.'" and description="'.$ac_name.'" and affected_object_id="'.$tender_id.'" order by id desc limit 1';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0) {
			$f=mysqli_fetch_array($rs);
			$prev_id=$f['id'];
		}
		
		//������� - ������, �� ������� - ���� �� ����� �������
		//���� ������ ������ �� ���������� ������, ���� ����
		$sql='select * from action_log where pdate<="'.$now.'" and user_subj_id="'.$user_id.'" and  	object_id="'.$object_id.'" and description="'.$ac_name1.'" and affected_object_id="'.$tender_id.'" ';
		if($prev_id!==NULL) $sql.=' and id>'.$prev_id.' ';
		$sql.=' order by id desc limit 1';
		$set=new mysqlset($sql); $rs=$set->GetResult(); $rc=$set->GetResultNumRows();
		if($rc>0) {
			$f=mysqli_fetch_array($rs);
			 
			$txt.='<div>������ �����: '.date('d.m.Y H:i:s', $f['pdate']).'</div>';
		}else $txt.='<div>������ �����: �� ������������</div>';
		
		//...
		 //���� 2 ������ �� ���������� ������, ���� ����
		$sql='select * from action_log where pdate<="'.$now.'" and user_subj_id="'.$user_id.'" and  	object_id="'.$object_id.'" and description="'.$ac_name2.'" and affected_object_id="'.$tender_id.'"  ';
		
		if($prev_id!==NULL) $sql.=' and id>'.$prev_id.' ';
		$sql.=' order by id desc limit 1';
		//$txt.="<div>$sql</div>";
		$set=new mysqlset($sql); $rs=$set->GetResult(); $rc=$set->GetResultNumRows();
		if($rc>0) {
			$f=mysqli_fetch_array($rs);
			 
			$txt.='<div>������ �����: '.date('d.m.Y H:i:s', $f['pdate']).'</div>';
		}else $txt.='<div>������ �����: �� ������������</div>';
		
		 //���� 3 ������ �� ���������� ������, ���� ����
		$sql='select * from action_log where pdate<="'.$now.'" and user_subj_id="'.$user_id.'" and  	object_id="'.$object_id.'" and description="'.$ac_name3.'" and affected_object_id="'.$tender_id.'" ';
		if($prev_id!==NULL) $sql.=' and id>'.$prev_id.' ';
		$sql.=' order by id desc limit 1';
		$set=new mysqlset($sql); $rs=$set->GetResult(); $rc=$set->GetResultNumRows();
		if($rc>0) {
			$f=mysqli_fetch_array($rs);
			 
			$txt.='<div>������ �����: '.date('d.m.Y H:i:s', $f['pdate']).'</div>';
		}else $txt.='<div>������ �����: �� ������������</div>';
		
		return $txt;	
	}
}




//������ � ������ � �����/������ ������� �� ������� "� ������"
class Tender_WorkingItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_working';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sched_id';	
	}
	
}


//������ ������� � ������ � �����/������ ������� �� ������� "� ������"
class Tender_WorkingGroup extends Abstract_WorkingKindGroup {
	 
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='tender_working';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		
		 
	}
	
}




/**************************************************************************************************/
//����� ���������
class Tender_Messages{
 	
	
	//�������� ��������� ��������� � �������� �-�� � ������ ���� ����������
	public function SendMessage26to23($id){
	 
		$_dem=new Tender_Item;
		$doc=$_dem->GetItemById($id); 
		
		$_ui=new UserSItem;
		 
			$_mi=new MessageItem;	
			
			
			$_ui=new UserSItem;
			$signer=$_ui->GetItemById($doc['manager_id']);
			
			$topic="������ �� ������� ������� ����������!";
			
			$txt='<div>';
			$txt.='<em>������ ��������� ������������� �������������.</em>';
			$txt.=' </div>';
			
			
			$txt.='<div>&nbsp;</div>';
			
			$txt.='<div>';
			$txt.='���������(��) '.$signer['name_s'].'!';
			$txt.='</div>';
			$txt.='<div>&nbsp;</div>';
			
			
			$txt.='<div>';
			$txt.='<strong>������� ��������� ������ �� ������� :</strong>';
			$txt.='</div><ul>';
			
			$txt.='<li><a href="ed_tender.php?action=1&id='.$id.'&from_begin=1" target="_blank">'.$_dem->ConstructFullName($id, $doc).'</a></li>';
	  
			$txt.='</ul>';
			
			
			$txt.='<div>';
			$txt.='������� ������� � ����� ��������� � ��������� ������ � ������ �������. �� ��������� ������������ ����������� ��������� � ����� �������!';
			$txt.='</div> ';
			
			$txt.='<div><strong>������ ������������ ������������� ��� ����������� ��������!</strong></div>';
			
			
			$txt.='<div>&nbsp;</div>';
		
			$txt.='<div>';
			$txt.='C ���������, ��������� "'.SITETITLE.'".';
			$txt.='</div>';
			
			$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$signer['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
		 
		
	}
	
	//�������� ��������� ��������� � �������� �-�� � ������ �����. �����
	public function SendMessage26to37($id){
	 
		$_dem=new Tender_Item;
		$doc=$_dem->GetItemById($id); 
		
		$_ui=new UserSItem;
		 
			$_mi=new MessageItem;	
			
			
			$_ui=new UserSItem;
			$signer=$_ui->GetItemById($doc['manager_id']);
			
			$topic="������ �� ������� ������� �� ����������!";
			
			$txt='<div>';
			$txt.='<em>������ ��������� ������������� �������������.</em>';
			$txt.=' </div>';
			
			
			$txt.='<div>&nbsp;</div>';
			
			$txt.='<div>';
			$txt.='���������(��) '.$signer['name_s'].'!';
			$txt.='</div>';
			$txt.='<div>&nbsp;</div>';
			
			
			$txt.='<div>';
			$txt.='<strong>��������� ������ ��������� � ������ ����������� ����� :</strong>';
			$txt.='</div><ul>';
			
			$txt.='<li><a href="ed_tender.php?action=1&id='.$id.'&from_begin=1" target="_blank">'.$_dem->ConstructFullName($id, $doc).'</a></li>';
	  
			$txt.='</ul>';
			
			
			/*$txt.='<div>';
			$txt.='������� ������� � ����� ��������� � ��������� ������ � ������ �������. �� ��������� ������������ ����������� ��������� � ����� �������!';
			$txt.='</div> ';
			*/
			$txt.='<div>������ ������� ������ � ������� ���������!</div>';
			
			
			$txt.='<div>&nbsp;</div>';
		
			$txt.='<div>';
			$txt.='C ���������, ��������� "'.SITETITLE.'".';
			$txt.='</div>';
			
			$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$signer['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
		 
		
	}
}

?>