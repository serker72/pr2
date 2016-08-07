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

require_once('doc_in_history_group.php');
require_once('doc_vn_history_group.php');
require_once('user_s_item.php');
require_once('discr_man.php');

require_once('pl_curritem.php');
require_once('lead_view_item.php');
require_once('tender.class.php');
require_once('lead_field_rules.php');
require_once('pl_prodgroup.php');
require_once('doc_vn_view.class.php');
 

require_once('doc_vn1_field_rules.php');
require_once('doc_vn2_field_rules.php'); 
require_once('abstract_working_kind_group.php');

require_once('doc_vn_history_item.php');
require_once('lead.class.php');
 

require_once('kp_supply_pdate_group.php');
require_once('kp_supply_pdate_item.php');
require_once('docstatusitem.php');
require_once('messageitem.php');

require_once('holy_dates.php');
require_once('sched.class.php');
require_once('user_pos_item.php');
require_once('currency/currency_solver.class.php');

//���������� ������� ����� ����������


//����������� ������ ����� ���������
class DocVn_AbstractItem extends AbstractItem{
	public $kind_id=1;
	protected function init(){
		$this->tablename='doc_vn';
		$this->item=NULL;
		$this->pagename='ed_doc_vn.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}	
	
	
	public function Add($params){
		/**/
		$digits=8;
		
		switch($params['kind_id']){
			/*case 0:
				$begin='���';
			break;
			case 1:
				$begin='���';
			break;*/
			 
			default:
				$begin='��';
			break;
				
			
		}
		
		
		 
		
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
	public function DocCanAnnul($id,&$reason,$item=NULL, $result=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		$au=new AuthUser;
		if($result===NULL) $result=$au->Auth(false,false);	
		
		$_dsi=new DocStatusItem;
		if($item['status_id']!=18){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������ ���������: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}else{
			//�������� ��������, ���� ����� ������������
			if(!$au->user_rights->CheckAccess('w',1085)){
				$_hg=new DocIn_HistoryGroup;
				$cou=$_hg->CountHistory($id);
				if($cou>0) {
					$can=$can&&false;
					$reasons[]='�� ����. ��������� �������� '.$cou.' ������������';
					$reason.=implode(', ',$reasons);
				}
			}	 
			
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
	
	
	
	
	//������ � ���� ������ ��� ���
	public function DocCanUnconfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=1){
			
			$can=$can&&false;
			$reasons[]='� ��. ��������� �� ���������� ����������';
			$reason.=implode(', ',$reasons);
		/*}elseif($item['is_ruk']==1){
			
			$can=$can&&false;
			$reasons[]='� ��. ��������� ���������� ������������ ��';
			$reason.=implode(', ',$reasons);
		 
		 
		}elseif($item['is_dir']==1){
			
			$can=$can&&false;
			$reasons[]='� ��. ��������� ���������� ��';
			$reason.=implode(', ',$reasons);
		*/ 
		}elseif($item['is_confirmed_done']==1){
			
			$can=$can&&false;
			$reasons[]='� ��. ��������� ���������� ����������';
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
			$reasons[]='� ��. ��������� ���������� ����������';
			$reason.=implode(', ',$reasons);
		}else{
			
			
			//������� �� ������������, ������������
			
			if($item['manager_id']==0){
				$can=$can&&false;
				//$dsi=$_dsi->GetItemById($item['status_id']);
				$reasons[]='� ��. ��������� �� ������ ������������� ���������';
			
			} 
			
			if($item['sched_id']==0){
				$can=$can&&false;
				//$dsi=$_dsi->GetItemById($item['status_id']);
				$reasons[]='� ��. ��������� �� ������� ������������';
			
			} 
			
			
			
			$can=$can&&$this->DocCanByMission($item['sched_id'], $rss_m, $id);
			if(strlen($rss_m)>0) $reasons[]=$rss_m;
			 
			
			//���� ���� ��� ��� - �� ���������, ����� ���� ������� ����� ����������� � ���� ���=2 - �� ����� ��������� ����� ���!
			//����� ������ ����� ����������, ������� ���/����������� ���� ��������
			
			$_hd=new HolyDates;
			$_sc=new Sched_MissionItem; $sc=$_sc->getitembyid($item['sched_id']);
			$pdate1=datefromdmy(datefromYmd($sc['pdate_beg']));	 $pdate2=datefromdmy(datefromYmd($sc['pdate_end']));	
			$hd_count=0;
			for($pdate=$pdate1; $pdate<=$pdate2; $pdate=$pdate+24*60*60){
				if($_hd->IsHolyday($pdate)) $hd_count++;
			}
			//$editing_user['hd_count']=$hd_count;
			
			
			
			if($hd_count>0){
			
					
					
					if($item['vyh_reason_id']==0){
						$can=$can&&false;
						 
						$reasons[]='� ��. ��������� �� ������ ����� ������ � ��������';
					
					} 
					if($item['vyh_reason_id']==2){
						
							 
						if($item['vyh_later']!=1){
							$_vyh=new DocVn_VyhDateGroup;
							$vd=$_vyh->GetItemsArrById($id);
							if(count($vd)!=$hd_count) {
								$can=$can&&false;
							 
								$reasons[]='����� ���� ������ �� ��������� � ������ �������� � ������������';
								
								 
								 
	
							}
						}
					}
					
				
			}
			
			$reason.=implode(', ',$reasons);	
		}
		
		
		 
		return $can;
	}
	
	//����������� �������� ���� ����������� ���-�� ��� ������� ������ ���������� �� �-�� - �� ��������������
	public function DocCanByMission($sched_id, &$rss_m, $id=NULL){
		$can=true; $rss_m='';
		
		$sql='select count(*) from '.$this->tablename.' where status_id<>3 and (is_ruk=1 or is_dir=1) and sched_id="'.$sched_id.'" ';
		if($id!==NULL) $sql.=' and id<>"'.$id.'"';
		
		//echo $sql;
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		$cter=(int)$f[0];
		
		if($cter>0){
		  $can=false; 	
			
		  $docs=array();		
		  $sql='select p.*, st.name as status_name from '.$this->tablename.' as p
		  left join document_status as st on st.id=p.status_id
		  where p.status_id<>3  and (is_ruk=1 or is_dir=1) and p.sched_id="'.$sched_id.'" ';
		  if($id!==NULL) $sql.=' and p.id<>"'.$id.'"';
		  $sql.=' order by p.code asc';			  
		  
		  
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  $docs[]=$f['code'].', ������ '.$f['status_name'];
		  }
		  
		  $rss_m='�� ��������� ������������ ��� ������� ��������� ��������� �������: '.implode('; ', $docs);
		}
		
		return $can;
	}
	
	
	
	//������ � ���� ������ ��� ���
	public function DocCanUnconfirmView($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_viewed']!=1){
			
			$can=$can&&false;
			$reasons[]='� ��. ��������� �� ���������� ������������';
			$reason.=implode(', ',$reasons);
		}/*elseif($item['is_confirm_done']==1){
			
			$can=$can&&false;
			$reasons[]='� ��������� �� ���������� ����������';
			$reason.=implode(', ',$reasons);
		 
		}*/
		
		return $can;
	}
	
	//������ � ���� ��� ���
	public function DocCanConfirmView($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_viewed']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� ��. ��������� ���������� ������������';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed']==0){
			
			$can=$can&&false;
			$reasons[]='� ��. ��������� �� ���������� ����������';
			$reason.=implode(', ',$reasons);
		 
		}else{
			
			/*if($item['manager_id']==0){
				$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� �� �� ������ ������������� ���������';
			
			}*/
			
			$reason.=implode(', ',$reasons);	
		}
		
		return $can;
	}
	
	//������� �������
	public function AddPositions($id,  array $positions, $result=NULL,$document=NULL){
	}
	
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		
		 
		
		parent::Edit($id, $params);
		
		 
		//if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	
	
	//�������� � ��������� ������� 
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth(false,false);
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		$_hi=new DocVn_HistoryItem;
		$setted_status_id=$item['status_id'];
		
 
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
			if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)){
				//����� ������� �� 41 //33
				$setted_status_id=41;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ��. ���������',NULL,1091,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
				//���� ������������ ���-�� ���-��� ������ - �� ����� ��������� � ���� ���-�� ������
				$_us=new Sched_UsersSGroup; $_ui=new UserSItem;
				$ui=$_ui->getitembyid($item['manager_id']);
				$ruk=$_us->GetRuk($ui);
				if(($ruk!==false)&&($ruk['id']==$_result['id'])){
					$this->Edit($id,array('is_ruk'=>1, 'user_ruk_id'=>$_result['id'],'ruk_pdate'=>time() ), true, $_result);
				}
				
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)){
				//����� ������� �� 18
				
				//����� ��� ����������� ���, ���
				
				$setted_status_id=18;
				$this->Edit($id,array('status_id'=>$setted_status_id, 'is_ruk'=>0, 'is_dir'=>0, '	user_ruk_id'=>0, 'user_dir_id'=>0, 'ruk_pdate'=>time(),'dir_pdate'=>time()));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ��. ���������',NULL,1091,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
				 
				
			}
		} 
		
		elseif(isset($new_params['is_ruk'])&&isset($old_params['is_ruk'])){
			if(($new_params['is_ruk']==1)&&($old_params['is_ruk']==0)){
				//����� ������� �� 43
				$setted_status_id=43;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ��. ���������',NULL,1091,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
			}elseif(($new_params['is_ruk']==0)&&($old_params['is_ruk']==1)){
				//����� ������� �� 41
				
				//����� ��� ����������� ���, ���
				
				$setted_status_id=41;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ��. ���������',NULL,1091,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
				 
				
			}
		} 
		
		
		
		elseif(isset($new_params['is_dir'])&&isset($old_params['is_dir'])){
			if(($new_params['is_dir']==1)&&($old_params['is_dir']==0)){
				//����� ������� �� 2
				$setted_status_id=2;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ��. ���������',NULL,1091,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				//�������� ��������� �� ���������, ��������� ��������� ���-�� ���
				$_mes=new DocVn_Messages;
				$_mes->SendMessageToAho($id);
				new NonSet('delete from doc_vn_view where doc_vn_id="'.$id.'"');
				
				
			}elseif(($new_params['is_dir']==0)&&($old_params['is_dir']==1)){
				//����� ������� �� 43
				
				//����� ��� ����������� ���, ���
				
				$setted_status_id=43;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ��. ���������',NULL,1091,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
				 
				
			}
		} 
		
		
		
		elseif(isset($new_params['is_confirmed_done'])&&isset($old_params['is_confirmed_done'])){
			if(($new_params['is_confirmed_done']==1)&&($old_params['is_confirmed_done']==0)){
				//����� ������� �� 48
				$setted_status_id=48;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ��. ���������',NULL,1091,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
				//���� ������������ ���-�� ���-��� ������ - �� ����� ��������� � ���� ���-�� ������
				$_us=new Sched_UsersSGroup; $_ui=new UserSItem;
				$ui=$_ui->getitembyid($item['manager_id']);
				$ruk=$_us->GetRuk($ui);
				if(($ruk!==false)&&($ruk['id']==$_result['id'])){
					$this->Edit($id,array('is_ruk_ot'=>1, 'ruk_ot_id'=>$_result['id'],'ruk_ot_pdate'=>time() ), true, $_result);
				}
				
				
			}elseif(($new_params['is_confirmed_done']==0)&&($old_params['is_confirmed_done']==1)){
				//����� ������� �� 2
				
				//����� ��� ����������� ���, ���
				
				$setted_status_id=2;
				$this->Edit($id,array('status_id'=>$setted_status_id, 'is_ruk_ot'=>0, 'is_dir_ot'=>0, '	ruk_ot_id'=>0, 'dir_ot_id'=>0, 'ruk_ot_pdate'=>time(),'dir_ot_pdate'=>time()));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ��. ���������',NULL,1091,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
				 
				
			}
		} 
		
		
		
		elseif(isset($new_params['is_ruk_ot'])&&isset($old_params['is_ruk_ot'])){
			if(($new_params['is_ruk_ot']==1)&&($old_params['is_ruk_ot']==0)){
				//����� ������� �� 50
				$setted_status_id=50;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ��. ���������',NULL,1091,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
			}elseif(($new_params['is_ruk_ot']==0)&&($old_params['is_ruk_ot']==1)){
				//����� ������� �� 48
				
				//����� ��� ����������� ���, ���
				
				$setted_status_id=48;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ��. ���������',NULL,1091,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
				 
				
			}
		} 
		
		
		
		elseif(isset($new_params['is_dir_ot'])&&isset($old_params['is_dir_ot'])){
			if(($new_params['is_dir_ot']==1)&&($old_params['is_dir_ot']==0)){
				//����� ������� �� 51
				$setted_status_id=51;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ��. ���������',NULL,1091,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
				//�������� ��������� �� ���������, ��������� ��������� ����������
				 
				$_mes=new DocVn_Messages;
				$_mes->SendMessageToBuh($id);
				new NonSet('delete from doc_vn_view where doc_vn_id="'.$id.'"');
				
			}elseif(($new_params['is_dir_ot']==0)&&($old_params['is_dir_ot']==1)){
				//����� ������� �� 50
				
			 	$setted_status_id=50;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ��. ���������',NULL,1091,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
				 
				
			}
		} 
		
		
		 
		
		
		//die();
	}
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		return '�������� ��������, ������ '.$stat['name'];
	}
	
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		return '�������� �������� '.$item['code'].', ������ '.$stat['name'];
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


/*************************************************************************************************/
//�������� � ���-��
class DocVn_Item extends DocVn_AbstractItem{
	public $kind_id=1;
	
  
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		$log=new ActionLog;
		
		 
		
		DocVn_AbstractItem::Edit($id, $params);
		
		 
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	 
	 
	 
	
	
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		
		//$res.=', �������: '.$this->ConstructContacts($id, $item).', ������ '.$stat['name'];
		$res.='��������� ������� �� ������������ '.$item['code'].', ������ '.$stat['name'];
	//	if($item['is_urgent']==1) $res.=', ������';
		
		//������ �-���
/*		$_sg=new DocIn_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($item['id']);
		foreach($sg as $k=>$v){
			$res.=', ���������� '.$v['opf_name'].' '.$v['full_name'];	
		}*/
		
		//������ �������
		
		
		return $res; //', ������ '.$stat['name'];
	}
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		$res.='�������� ������ '.$item['code'].', ������ '.$stat['name'];
		if($item['is_urgent']==1) $res.=', ������';
		
		//������ �-���
		$_sg=new DocIn_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($item['id']);
		foreach($sg as $k=>$v){
			$res.=', ���������� '.$v['opf_name'].' '.$v['full_name'];	
		}
		
	 
		return $res; //', ������ '.$stat['name'];
	}
	
	 
	//������� �������
	public function AddPositions($id,  array $positions, $result=NULL,$document=NULL){
		
		 
		
		$_kpi=new DocVn_ExpensesItem; $_kpr=new DocVn_ExpensesBlock;  
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		if($document===NULL) $document=$this->getitembyid($id);
		$old_positions=array();
		
		$old_positions=$_kpr->ConstructById($id, $document, $result); 
		 
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array(
			'doc_vn_id'=>$v['doc_vn_id'],
			 
			'expenses_id'=>$v['expenses_id'],
			'pdate'=>$v['pdate']
			
			));
			
 
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['doc_vn_id']=$v['doc_vn_id'];
				$add_array['expenses_id']=$v['expenses_id'];
				$add_array['pdate']=$v['pdate'];
				
				$add_array['plan']=$v['plan'];
				$add_array['fact']=$v['fact'];
				 
				$add_array['plan_currency_id']=$v['plan_currency_id'];
				$add_array['fact_currency_id']=$v['fact_currency_id'];
				
				$add_array['fact_l_or_korp']=$v['fact_l_or_korp'];
				$add_array['doc_name']=$v['doc_name'];
				$add_array['doc_no']=$v['doc_no'];
				$add_array['doc_pdate']=$v['doc_pdate'];
			 
				$_kpi->Add($add_array );
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'doc_vn_id'=>$v['doc_vn_id'],
					 
					'expenses_id'=>$v['expenses_id'],
					'plan'=>$v['plan'],
					'fact'=>$v['fact'],
					
					'plan_currency_id'=>$v['plan_currency_id'],
					'fact_currency_id'=>$v['fact_currency_id'],
					'fact_l_or_korp'=>$v['fact_l_or_korp'],
					'doc_name'=>$v['doc_name'],
					'doc_no'=>$v['doc_no'],
					'doc_pdate'=>$v['doc_pdate'] 
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['doc_vn_id']=$v['doc_vn_id'];
			 
				$add_array['expenses_id']=$v['expenses_id'];
				$add_array['plan']=$v['plan'];
				$add_array['fact']=$v['fact'];
				$add_array['pdate']=$v['pdate'];
				 
				$add_array['plan_currency_id']=$v['plan_currency_id'];
				$add_array['fact_currency_id']=$v['fact_currency_id'];
				
				$add_array['fact_l_or_korp']=$v['fact_l_or_korp'];
				$add_array['doc_name']=$v['doc_name'];
				$add_array['doc_no']=$v['doc_no'];
				$add_array['doc_pdate']=$v['doc_pdate'];
				 
				$_kpi->Edit($kpi['id'], $add_array);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//���� ���� ���������
				
				//��� ����������? ���������� ���-��, ����, +/-, 
				
				$to_log=false;
				if($kpi['doc_vn_id']!=$add_array['doc_vn_id']) $to_log=$to_log||true;
				 
				if($kpi['expenses_id']!=$add_array['expenses_id']) $to_log=$to_log||true;
				if($kpi['pdate']!=$add_array['pdate']) $to_log=$to_log||true;
				 
				
				if($kpi['plan']!=$add_array['plan']) $to_log=$to_log||true;
				if($kpi['fact']!=$add_array['fact']) $to_log=$to_log||true;
				
				if($kpi['plan_currency_id']!=$add_array['plan_currency_id']) $to_log=$to_log||true;
				if($kpi['fact_currency_id']!=$add_array['fact_currency_id']) $to_log=$to_log||true;
				if($kpi['fact_l_or_korp']!=$add_array['fact_l_or_korp']) $to_log=$to_log||true;
				if($kpi['doc_name']!=$add_array['doc_name']) $to_log=$to_log||true;
				if($kpi['doc_no']!=$add_array['doc_no']) $to_log=$to_log||true;
				if($kpi['doc_pdate']!=$add_array['doc_pdate']) $to_log=$to_log||true;
			 	
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					  'doc_vn_id'=>$v['doc_vn_id'],
					 
					'expenses_id'=>$v['expenses_id'],
					'plan'=>$v['plan'],
					'fact'=>$v['fact'],
					
					'plan_currency_id'=>$v['plan_currency_id'],
					'fact_currency_id'=>$v['fact_currency_id'],
					'fact_l_or_korp'=>$v['fact_l_or_korp'],
					'doc_name'=>$v['doc_name'],
					'doc_no'=>$v['doc_no'],
					'doc_pdate'=>$v['doc_pdate'] 
						 
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
			
			/*echo $vv['bdr_id']==$v['bdr_id'];
			echo  $vv['version_id']==$version_id*/
			 
			foreach($positions as $kk=>$vv){
				if(($vv['doc_vn_id']==$v['doc_vn_id'])
				 
				&&($vv['expenses_id']==$v['expenses_id'])
				&&($vv['pdate']==$v['pdate'])
				 
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
					'doc_vn_id'=>$v['doc_vn_id'],
					 
					'expenses_id'=>$v['expenses_id'],
					'plan'=>$v['plan'],
					'fact'=>$v['fact'],
					
					'plan_currency_id'=>$v['plan_currency_id'],
					'fact_currency_id'=>$v['fact_currency_id'],
					'fact_l_or_korp'=>$v['fact_l_or_korp'],
					'doc_name'=>$v['doc_name'],
					'doc_no'=>$v['doc_no'],
					'doc_pdate'=>$v['doc_pdate'] 
			);
			
			//������� �������
			$_kpi->Del($v['id']);
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	
	
	 
}

/*************************************************************************************************/
//����� � ������� �� ��-�
class DocVn_OtItem extends DocVn_AbstractItem{
	public $kind_id=2;
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		$log=new ActionLog;
		
		 
		
		DocVn_AbstractItem::Edit($id, $params);
		
		 
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	 
	
	
	 
	 
	
	
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		
		//$res.=', �������: '.$this->ConstructContacts($id, $item).', ������ '.$stat['name'];
		$res.='�������� �� '.$item['code'].', ������ '.$stat['name'];
		if($item['is_urgent']==1) $res.=', ������';
		
		//������ �-���
		$_sg=new DocIn_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($item['id']);
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
		 
		$res.='�������� �� '.$item['code'].', ������ '.$stat['name'];
		if($item['is_urgent']==1) $res.=', ������';
		
		//������ �-���
		$_sg=new DocIn_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($item['id']);
		foreach($sg as $k=>$v){
			$res.=', ���������� '.$v['opf_name'].' '.$v['full_name'];	
		}
		
	 
		return $res; //', ������ '.$stat['name'];
	}
	
}
 

 


/***********************************************************************************************/
//����������� ������ ������
class DocVn_Resolver{
	public $instance, $group_instance, $rules_instance;
	function __construct($kind_id=1){
		switch($kind_id){
			case 1:
				$this->instance= new DocVn_Item;
				$this->group_instance=new DocVn_Group;
				$this->rules_instance=new DocVn_1_FieldRules;
			break;
			case 2:
				$this->instance= new DocVn_OtItem;
				$this->group_instance= new DocVn_OtGroup;
				$this->rules_instance=new DocVn_2_FieldRules;
			break;
			 
			 
			default:
				$this->instance= new DocVn_Item;
				$this->group_instance=new DocVn_Group;
				$this->rules_instance=new DocVn_1_FieldRules;
			break;
		}; 
		 
	}
	
	 
}


/****************************************************************************************************/
//����������� ������ �� ����������
class DocVn_AbstractGroup extends AbstractGroup{
	public $kind_id=1;
	
	protected $_auth_result;
	protected $_view, $_unconfirm_right_id;
	
	protected $new_list; //������ ����� ���������� ��� �������� ������������ � ��������� �� �� ������
	 
	protected function init(){
		$this->tablename='doc_vn';
		$this->pagename='doc_inners.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		
		$this->_view=new DocVn_ViewsGroup1;
		 
		$this->_auth_result=NULL;
		
		$this->new_list=NULL; $this->_unconfirm_right_id=1098;
	}
	
	
	protected function GainSql(&$sql, &$sql_count){
	 
			
	}
	
	//������ ����������
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
		$can_email=true, //12
		 
	 	$prefix='' //13
	 
		
		){
		 
		 if($is_ajax) $sm=new SmartyAj;
		 else $sm=new SmartyAdm;
		 
		 $sm->assign('has_header', $has_header);
		 $sm->assign('can_restore', $can_restore);
		  $sm->assign('can_create', $can_create);
		 $sm->assign('can_delete', $can_delete);
		 $sm->assign('can_confirm_price', $can_confirm_price);
		 $sm->assign('can_unconfirm_price', $can_unconfirm_price);
		 
		 $sm->assign('can_email', $can_email);
		 $sm->assign('kind_id', $this->kind_id);
		
		
		
		
		 //������� ������ ���, ��� ����� ����� ����������� ����������
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', $this->_unconfirm_right_id);
		$sm->assign('can_unconfirm_users',$usg1); 
 
 
		
		 
		$_sg=new DocVn_SupplierGroup;
		$_lds=new DocVn_LeadGroup;
		$_cg=new DocVn_CityGroup;	
	 	$_hd=new HolyDates;
		
		$this->GainSql($sql, $sql_count);	
		
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
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
		
		$link=$dec->GenFltUri('&', $prefix);
		//echo $prefix;
	//	$link=eregi_replace('&sortmode'.$prefix.'','&sortmode',$link);
		$link=eregi_replace('action'.$prefix,'action',$link);
		$link=eregi_replace('&id'.$prefix,'&id',$link);
		
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10, '&'.$link);
		$navig->SetFirstParamName('from'.$prefix);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		$man=new DiscrMan;
		
		$this->new_list=NULL;
		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
		 
		 
			
			$f['pdate_f']=date('d.m.Y', $f['pdate']);
			
			$f['pdate_crea']=date('d.m.Y H:i:s', $f['pdate']);
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date('d.m.Y H:i:s', $f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			
			//����� � ������ �������� ����
			$pdate1=datefromdmy(datefromYmd($f['pdate_beg']));	 $pdate2=datefromdmy(datefromYmd($f['pdate_end']));	
			$hd_count=0;
			$hdays=array();
			for($pdate=$pdate1; $pdate<=$pdate2; $pdate=$pdate+24*60*60){
				if($_hd->IsHolyday($pdate)) {
					$hd_count++;
					$hdays[]=date('d.m.Y', $pdate);
				}
			}
			$f['hdays']=$hdays;
			$f['hd_count']=$hd_count;
		 
			//�������������� ��� ������-��
			if($f['pdate_beg']!==""){
				$exp_ptime	= Datefromdmy( DateFromYmd($f['pdate_beg']))+ (int)substr($f['ptime_beg'], 0,2)*60*60 + (int)substr($f['ptime_beg'],3,2)*60;
				
			  
			}
			
			if(
			
			($f['m_status_id']!=10) && ($f['m_status_id']!=3) && ($f['m_status_id']!=1)
			
			&&
			($exp_ptime!==NULL) && ($exp_ptime<time())
			
			) $expired=true;
			$f['expired']=$expired; 
			

			 
			$f['pdate_beg']=DateFromYmd($f['pdate_beg']);
			
			if($f['pdate_end']!=="") $f['pdate_end']=DateFromYmd($f['pdate_end']);
			
			
			 
			 
			$_res=new  DocVn_Resolver($f['kind_id']);
				//$f['contact_value']=$_res->instance->ConstructContacts($f['id'], $f);
			 
			
			$f['can_annul']=$_res->instance->DocCanAnnul($f['id'],$reason,$f, $this->_auth_result)&&$can_delete;
			if(!$can_delete) $reason='������������ ���� ��� ������ ��������';
			$f['can_annul_reason']=$reason;
			
			$f['suppliers']=$_sg->GetItemsByIdArr($f['sched_id']);	
			
			$f['cities']=$_cg->GetItemsByIdArr($f['sched_id']);	
				
		 		
			$f['field_rules']=$_res->rules_instance->GetFields($f,$this->_auth_result['id'],$f['status_id']);  
			 
			
			 //�������� ����� "����� ��������"
			$f['new_blocks']=$this->DocumentNewBlocks($f['id'], $this->_auth_result['id']);
			 
			$f['leads']=$_lds->GetItemsByIdArr($f['id'], false, $f);
			
			
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
		
		 
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$sm->assign('prefix',$prefix);
		 
		
		$sm->assign('can_edit',$can_edit);
	 
 
		 //����� ������������
		 
			$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
			$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		 
	 	
		
		//������ ��� ������ ����������
			$link=$dec->GenFltUri('&', $prefix);
		//echo $prefix;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$prefix.'=[[:digit:]]+','',$link);
		$link=eregi_replace('action'.$prefix,'action',$link);
		$link=eregi_replace('&id'.$prefix,'&id',$link);
		$sm->assign('link',$link);
		
//		echo $link;
		
		
		return $sm->fetch($template);
	}
	
	
	//�������� �� ������� �������� ��� ������� ������������ � ���������, ���� �������� - ������� ������ ��� ���������� �����
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
				
				$sub_id=$type['sub_ids'][array_search($document_id,$type['doc_ids'])];
				
								
				$url=str_replace('{sub_id}',$sub_id, $url, $subst_count1);
				$subst_count+=$subst_count1;
				
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
	
	
	//����� ����� ����� - ����������� ���������
	public function CountNewDocsExtended($user_id, $do_iconv=true){
		$data=array();
			
		 
		
		
		if($do_iconv) foreach($data as $k=>$v) $data[$k]['comment']=iconv('windows-1251', 'utf-8', $v['comment']);
		
		return $data;
	}	
	
}


/****************************************************************************************************/
// ������ �������� �� ���-��
class  DocVn_Group extends DocVn_AbstractGroup {
	 public $kind_id=1;
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn';
		$this->pagename='doc_inners.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		
		$this->_view=new DocVn_ViewsGroup1;
		 
		$this->_auth_result=NULL;
		
		$this->new_list=NULL;  $this->_unconfirm_right_id=1098;
	} 
	
	
	
	protected function GainSql(&$sql, &$sql_count){
		$sql='select distinct p.*,
		s.name as status_name, s.weight as status_weight,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active ,
	 
		kind.name as kind_name,
		sc.code as mission_code, sc.pdate_beg, sc.pdate_end, sc.ptime_beg, sc.ptime_end, sc.status_id as m_status_id
		 
		 
		 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
			 	left join sched as sc on p.sched_id=sc.id and sc.kind_id=2
				left join sched_suppliers as ss on ss.sched_id=sc.id
				left join sched_cities as cit on cit.sched_id=sc.id
				left join sprav_city as city on city.id=cit.city_id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join doc_vn_leads as dl on dl.doc_out_id=p.id
				left join lead as ld on ld.id=dl.lead_id
				  
				
				left join user as cr on cr.id=p.created_id
			 	
				left join user as send on send.id=p.user_ruk_id
				left join doc_vn_kind as kind on p.kind_id=kind.id
			 
			where p.kind_id="'.$this->kind_id.'"	 
				 	 
				 ';
				
		$sql_count='select count(*) 
					 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
			 	left join sched as sc on p.sched_id=sc.id and sc.kind_id=2
				left join sched_suppliers as ss on ss.sched_id=sc.id
				left join sched_cities as cit on cit.sched_id=sc.id
				left join sprav_city as city on city.id=cit.city_id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join doc_vn_leads as dl on dl.doc_out_id=p.id
				left join lead as ld on ld.id=dl.lead_id
				  
				
				left join user as cr on cr.id=p.created_id
			 	
				left join user as send on send.id=p.user_ruk_id
				left join doc_vn_kind as kind on p.kind_id=kind.id
			 
			 
			where p.kind_id="'.$this->kind_id.'"	 
		 
				 ';
			
	}
	
	
	
	//������ ID ����������, ������� ����� ������ ������� ���������
	public function GetAvailableDocIds($user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//��������� �����������
		//���� �� ���� - �� ��� ��� ����
		if($_man->CheckAccess($user_id,'w',1095)){
			$sql='select id from doc_vn';
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
		}else{
			//����
			$sql='select id from doc_vn where manager_id="'.$user_id.'" or created_id="'.$user_id.'" ';	
			
			//������������ ������ - ������ ������ � ���������� �����������
			$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if($upos['is_ruk_otd']==1){
			 	$sql.=' or manager_id in(select id from user where department_id="'.$user['department_id'].'")';
					
			} 
			
			//������������ - ����� ��� ��������� � 3 ��������� (�.�. ������ 2)!
			$dec=new DBDecorator();
		//.dep.name
			$dec ->AddEntry(new SqlEntry('pos.name','�����������', SqlEntry::LIKE));
			$dec ->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
			$ug=new UsersSGroup;
			$users=$ug->GetItemsByDecArr($dec); $uids=array();
		    foreach($users as $k=>$v) $uids[]=$v['id'];
			
			if(in_array($user_id, $uids)){
				$sql.=' or (status_id=2)';
				 
			}
			
			
			
			//����� ��������� - ��� � ������� 51 - ����� ���������
			$dec=new DBDecorator();
		//.dep.name
			$dec ->AddEntry(new SqlEntry('dep.name','����� ���������', SqlEntry::LIKE));
			$dec ->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
			$ug=new UsersSGroup;
			$users=$ug->GetItemsByDecArr($dec); $uids=array();
		    foreach($users as $k=>$v) $uids[]=$v['id'];
			
			if(in_array($user_id, $uids)){
				$sql.=' or (status_id=51)';
				 
			}
			
			//���-�� ����� ��� ����� ��� ���-�� � 3�� ���������, �.�. ������ ���-�=2
			$dec=new DBDecorator();
		//.dep.name
			$dec ->AddEntry(new SqlEntry('pos.name','������������ ������', SqlEntry::LIKE));
			$dec->AddEntry(new SqlEntry('dep.name','���', SqlEntry::LIKE));
			$dec->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
			$ug=new UsersSGroup;
			$users=$ug->GetItemsByDecArr($dec); $uids=array();
		    foreach($users as $k=>$v) $uids[]=$v['id'];
			
			 
			if(in_array($user_id, $uids)){
				$sql.=' or (status_id=2)';
			 
			}
			
			//������� ���-�, ����������� ����� ��� ���-�� ��������� �� ����� ���������, �.�. ������=����� ��������� 51
			$dec=new DBDecorator();
		//.dep.name
			$dec ->AddEntry(new SqlEntry('pos.name','������� ���������', SqlEntry::LIKE));
			$dec->AddEntry(new SqlEntry('dep.name','�����������', SqlEntry::LIKE));
			$dec->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
			$ug=new UsersSGroup;
			$users=$ug->GetItemsByDecArr($dec); $uids=array();
		    foreach($users as $k=>$v) $uids[]=$v['id'];
			
			 
			if(in_array($user_id, $uids)){
				$sql.=' or (status_id=51)';
			 
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
	
	//��������, ����� �� �������� �������� ��� ��������� ��������� ���������� ����������
	public function ScanAvailableByUserId($manager_id, $user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//��������� �����������
		//���� �� ���� - �� ��� ��� ����
		if($_man->CheckAccess($user_id,'w',1095)){
			 
			
			return true;
		}else{
			//����
			
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
	
	
	  
	
	
	//��������������� ������ ����� ����� ��� ��������� ������������
	protected function ConstructNewList($user_id){
		$this->new_list=array();
		
		/*
		$this->new_list[]=array(
					'class'=>'menu_new_m',
					'num'=>(int)$f[0],
					'url'=>''
					'doc_ids'=>array(),
					'sub_ids'=>array(),
					'doc_counters'=>array(),
					'comment'=>'������� ���� � ������!'
				
				);
		*/	
		
		$tender_ids=$this->GetAvailableDocIds($user_id);
		
		  
		$man=new DiscrMan;
		
			
		$_ui=new UserSItem;
		$user=$_ui->GetItemById($user_id);
		 
		  
		 
		 
		 //�������� + ����������� �� - ������ ����� - ����������� �� �� ������-��
		
		$sql='select count(*) from 
				'.$this->tablename.' as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				and t.status_id=33
				
				';
	  
			$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if(!eregi('�����������',$upos['name'])){
			 	$sql.='and  t.manager_id="'.$user_id.'" ';
					
			} 		
				
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		   
		$f=mysqli_fetch_array($rs);	
		
		if((int)$f[0]>0){
			//������� ������ ���, ���������� �������� �������
			$sql='select t.* from 
				'.$this->tablename.' as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				 and t.status_id=33 
				 
			';
			
			$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if(!eregi('�����������',$upos['name'])){
			 	$sql.='and  t.manager_id="'.$user_id.'" ';
					
			} 	
			
			//echo $sql;
			
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$doc_ids=array(); $sub_ids=array();
			for($j=0; $j<$rc; $j++){ 
				$g=mysqli_fetch_array($rs);		
			 
				$doc_ids[]=$g['id']; 
			}	
			
			$this->new_list[]=array(
				'class'=>'reestr_menu_new_km',
				'num'=>(int)$rc,
					'doc_ids'=>$doc_ids,
					'doc_counters'=>array(),
					'sub_ids'=>$sub_ids,
					'url'=>'ed_doc_vn.php?action=1&id={id}'.'&from_begin=1',
				'comment'=>'����������� ��������� ������� �� ������������!'
			
			);
			
			 
		}
		
		
		
		
		
		
		
		//�������� - ������ ��������� - ����������� ����� �� ������-��
		//+ ����������� ��
		$sql='select count(*) from 
				'.$this->tablename.' as t
				
				
				where t.id in ('.implode(', ',$tender_ids).')
				 and t.status_id=8
				 
				';
				
				$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if(!eregi('�����������',$upos['name'])){
			 	$sql.='and  t.manager_id="'.$user_id.'" ';
					
			} 	
				
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		   
		$f=mysqli_fetch_array($rs);	
		
		if((int)$f[0]>0){
			//������� ������ ���, ���������� �������� �������
			$sql='select t.* from 
				'.$this->tablename.' as t
				
				
				where t.id in ('.implode(', ',$tender_ids).')
				 and t.status_id=8 
				
				  
			';
			
			$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if(!eregi('�����������',$upos['name'])){
			 	$sql.='and  t.manager_id="'.$user_id.'" ';
					
			} 	
			
			//echo $sql;
			
			
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$doc_ids=array(); $sub_ids=array();
			for($j=0; $j<$rc; $j++){ 
				$g=mysqli_fetch_array($rs);		
			 
				$doc_ids[]=$g['id']; 
			}	
			
			$this->new_list[]=array(
				'class'=>'reestr_menu_new_km',
				'num'=>(int)$rc,
					'doc_ids'=>$doc_ids,
					'doc_counters'=>array(),
					'sub_ids'=>$sub_ids,
					'url'=>'ed_doc_vn.php?action=1&id={id}'.'&from_begin=1',
				'comment'=>'����������� ����� � ������� ��������� ������� �� ������������!'
			
			);
			
			
			 
		}
		
		
		
		
		//������������ + ��������� -  ��������� � 3 ��������� (�.�. ������ 2) - ����������, ��������� ����� �� ����������� ������
			
		$sql='select count(*) from 
				'.$this->tablename.' as t
				left join sched as ss on t.sched_id=ss.id
				
				where t.id in ('.implode(', ',$tender_ids).')
				 and t.status_id=2
				 and ss.is_confirmed_done=1
				';
				
					$dec=new DBDecorator();
		//.dep.name
			$dec ->AddEntry(new SqlEntry('pos.name','�����������', SqlEntry::LIKE));
			$dec ->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
			$ug=new UsersSGroup;
			$users=$ug->GetItemsByDecArr($dec); $uids=array();
		    foreach($users as $k=>$v) $uids[]=$v['id'];
			
			if(!in_array($user_id, $uids))  $sql.='and  t.manager_id="'.$user_id.'" ';
			
		//echo $sql;	
				
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		   
		$f=mysqli_fetch_array($rs);	
		
		if((int)$f[0]>0){
			//������� ������ ���, ���������� �������� �������
			$sql='select t.* from 
				'.$this->tablename.' as t
				left join sched as ss on t.sched_id=ss.id
				
				where t.id in ('.implode(', ',$tender_ids).')
				 and t.status_id=2 
				   and ss.is_confirmed_done=1 
			';
			
			if(!in_array($user_id, $uids)) $sql.='and  t.manager_id="'.$user_id.'" ';
			
			//echo $sql;
			
			
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$doc_ids=array(); $sub_ids=array();
			for($j=0; $j<$rc; $j++){ 
				$g=mysqli_fetch_array($rs);		
			 
				$doc_ids[]=$g['id']; 
			}	
			
			$this->new_list[]=array(
				'class'=>'reestr_menu_new_km',
				'num'=>(int)$rc,
					'doc_ids'=>$doc_ids,
					'doc_counters'=>array(),
					'sub_ids'=>$sub_ids,
					'url'=>'ed_doc_vn.php?action=1&id={id}'.'&from_begin=1',
				'comment'=>'����������, ��������� ����� �� ����������� ������!'
			
			);
			
			
			 
		}
		
		
		
		 
		$_upos=new UserPosItem;
		$upos=$_upos->GetItemById($user['position_id']);
		
		
		//������������ ������ - ������ �� ������������ - ���������� ��...
		if(eregi('������������ ������', $upos['name'])
			||$man->CheckAccess($user_id,'w',1099)	
		){	
			$add=''; 
			if(!$man->CheckAccess($user_id,'w',1099)) $add=' and m.department_id="'.$user['department_id'].'" ';
			
			$sql='select count(*) from 
					'.$this->tablename.' as t
					inner join user as m on m.id=t.manager_id
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=41
				 
					'.$add;
					
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.* from 
					'.$this->tablename.' as t
					inner join user as m on m.id=t.manager_id
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=41
				  
				'.$add;
				
				//echo $sql;
				
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array(); $sub_ids=array();
				for($j=0; $j<$rc; $j++){ 
					$g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['id']; 
				}	
				
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_km',
					'num'=>(int)$rc,
						'doc_ids'=>$doc_ids,
						'doc_counters'=>array(),
						'sub_ids'=>$sub_ids,
						'url'=>'ed_doc_vn.php?action=1&id={id}'.'&from_begin=1',
					'comment'=>'���������� ��������� ������� �� ������������!'
				
				);
				 
			}	
			
		}
		
		
		//������������ ������ - ������ �� ����������� - ��������� ��...
		if(eregi('����������� ��������', $upos['name'])
			||$man->CheckAccess($user_id,'w',1111)	
		){	
			$sql='select count(*) from 
					'.$this->tablename.' as t
					inner join user as m on m.id=t.manager_id
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=43
					/*and m.department_id="'.$user['department_id'].'" */
					';
					
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.* from 
					'.$this->tablename.' as t
					inner join user as m on m.id=t.manager_id
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=43
					/*and m.department_id="'.$user['department_id'].'" */
					 
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array(); $sub_ids=array();
				for($j=0; $j<$rc; $j++){ 
					$g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['id']; 
				}	
				
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_km',
					'num'=>(int)$rc,
						'doc_ids'=>$doc_ids,
						'doc_counters'=>array(),
						'sub_ids'=>$sub_ids,
						'url'=>'ed_doc_vn.php?action=1&id={id}'.'&from_begin=1',
					'comment'=>'��������� ��������� ������� �� ������������!'
				
				);
					 
			}	
			
		}
		
		
		
		//������������ ������ - ������ ����� �� ������������ - ���������� �����...
		if(eregi('������������ ������', $upos['name'])
			||$man->CheckAccess($user_id,'w',1109)	
		){	
			
			$add=''; 
			if(!$man->CheckAccess($user_id,'w',1109)) $add=' and m.department_id="'.$user['department_id'].'" ';
			$sql='select count(*) from 
					'.$this->tablename.' as t
					inner join user as m on m.id=t.manager_id
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=48
					 
					'.$add;
					
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.* from 
					'.$this->tablename.' as t
					inner join user as m on m.id=t.manager_id
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=48
					 
					 
				'.$add;
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array(); $sub_ids=array();
				for($j=0; $j<$rc; $j++){ 
					$g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['id']; 
				}	
				
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_km',
					'num'=>(int)$rc,
						'doc_ids'=>$doc_ids,
						'doc_counters'=>array(),
						'sub_ids'=>$sub_ids,
						'url'=>'ed_doc_vn.php?action=1&id={id}'.'&from_begin=1',
					'comment'=>'���������� ����� � ������������ ��������� ������� �� ������������!'
				
				);
					 
			}	
			
		}
		
		
		//������������ ������ - ������ ����� �� ����������� - ��������� �����...
		if(eregi('����������� ��������', $upos['name'])
			||$man->CheckAccess($user_id,'w',1113)	
		){	
			$sql='select count(*) from 
					'.$this->tablename.' as t
					inner join user as m on m.id=t.manager_id
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=50
					/*and m.department_id="'.$user['department_id'].'" */
					';
					
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.* from 
					'.$this->tablename.' as t
					inner join user as m on m.id=t.manager_id
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=50
					/*and m.department_id="'.$user['department_id'].'" */
					 
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array(); $sub_ids=array();
				for($j=0; $j<$rc; $j++){ 
					$g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['id']; 
				}	
				
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_km',
					'num'=>(int)$rc,
						'doc_ids'=>$doc_ids,
						'doc_counters'=>array(),
						'sub_ids'=>$sub_ids,
						'url'=>'ed_doc_vn.php?action=1&id={id}'.'&from_begin=1',
					'comment'=>'��������� ����� � ������������ ��������� ������� �� ������������!'
				
				);
					 
			}	
			
		}
		
		 
		 
		 
		//������ ������ �� ���, �����������
		
		//������������ ��� ����� ����������� ��� ������� 2 = ���������
		$dec=new DBDecorator();
		//.dep.name
		$dec ->AddEntry(new SqlEntry('pos.name','������������ ������', SqlEntry::LIKE));
		$dec->AddEntry(new SqlEntry('dep.name','���', SqlEntry::LIKE));
		$dec->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
		$ug=new UsersSGroup;
		$users=$ug->GetItemsByDecArr($dec); $uids=array();
		foreach($users as $k=>$v) $uids[]=$v['id'];
		
		 
		if(in_array($user_id, $uids)){
		  $sql='select count(*) from 
				  '.$this->tablename.' as t
				  
				  
				  where t.id in ('.implode(', ',$tender_ids).')
				  and t.status_id=2
				  and t.id not in(select doc_vn_id from doc_vn_view where user_id="'.$user_id.'") 
				  ';
				  
				 
				  
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
			 
		  $f=mysqli_fetch_array($rs);	
		  
		  if((int)$f[0]>0){
			  //������� ������ ���, ���������� �������� �������
			  $sql='select t.* from 
				  '.$this->tablename.' as t
				  
				  
				  where t.id in ('.implode(', ',$tender_ids).')
				  and t.status_id=2 
				  and t.id not in(select doc_vn_id from doc_vn_view where user_id="'.$user_id.'") 
					
			  ';
			   
			  
			  //echo $sql;
			  
			  
			  $set=new mysqlset($sql);
			  $rs=$set->GetResult();
			  $rc=$set->GetResultNumRows();
			  $doc_ids=array(); $sub_ids=array();
			  for($j=0; $j<$rc; $j++){ 
				  $g=mysqli_fetch_array($rs);		
			   
				  $doc_ids[]=$g['id']; 
			  }	
			  
			  $this->new_list[]=array(
				  'class'=>'reestr_menu_new_km',
				  'num'=>(int)$rc,
					  'doc_ids'=>$doc_ids,
					  'doc_counters'=>array(),
					  'sub_ids'=>$sub_ids,
					  'url'=>'ed_doc_vn.php?action=1&id={id}'.'&from_begin=1',
				  'comment'=>'��������! ���������� ����� ������������!'
			  
			  );
			  
			  
			   
		  }
		}
		
		//������� ����� ����������� ��� ������� 51 = ���������
		$dec=new DBDecorator();
		//.dep.name
		$dec ->AddEntry(new SqlEntry('pos.name','������� ���������', SqlEntry::LIKE));
		$dec->AddEntry(new SqlEntry('dep.name','�����������', SqlEntry::LIKE));
		$dec->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
		$ug=new UsersSGroup;
		$users=$ug->GetItemsByDecArr($dec); $uids=array();
		foreach($users as $k=>$v) $uids[]=$v['id'];
		
		 
		if(in_array($user_id, $uids)){
		  $sql='select count(*) from 
				  '.$this->tablename.' as t
				  
				  
				  where t.id in ('.implode(', ',$tender_ids).')
				  and t.status_id=51
				  and t.id not in(select doc_vn_id from doc_vn_view where user_id="'.$user_id.'") 
				  ';
				  
				 
				  
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
			 
		  $f=mysqli_fetch_array($rs);	
		  
		  if((int)$f[0]>0){
			  //������� ������ ���, ���������� �������� �������
			  $sql='select t.* from 
				  '.$this->tablename.' as t
				  
				  
				  where t.id in ('.implode(', ',$tender_ids).')
				  and t.status_id=51 
				  and t.id not in(select doc_vn_id from doc_vn_view where user_id="'.$user_id.'") 
					
			  ';
			   
			  
			  //echo $sql;
			  
			  
			  $set=new mysqlset($sql);
			  $rs=$set->GetResult();
			  $rc=$set->GetResultNumRows();
			  $doc_ids=array(); $sub_ids=array();
			  for($j=0; $j<$rc; $j++){ 
				  $g=mysqli_fetch_array($rs);		
			   
				  $doc_ids[]=$g['id']; 
			  }	
			  
			  $this->new_list[]=array(
				  'class'=>'reestr_menu_new_km',
				  'num'=>(int)$rc,
					  'doc_ids'=>$doc_ids,
					  'doc_counters'=>array(),
					  'sub_ids'=>$sub_ids,
					  'url'=>'ed_doc_vn.php?action=1&id={id}'.'&from_begin=1',
				  'comment'=>'��������! ��������� ����� ��������� �����!'
			  
			  );
			  
			  
			   
		  }
		}
		 
		
	}
	
	
	
	//����� ����� ���-��� - ����������� ���������
	public function CountNewDocsExtended($user_id, $do_iconv=true){
		$data=array();
			
		$tender_ids=$this->GetAvailableDocIds($user_id);
		
		  
		$man=new DiscrMan;
		
		
		//������� ����� ����������� ��� ������� 51 = ���������
		$dec=new DBDecorator();
		//.dep.name
		$dec ->AddEntry(new SqlEntry('pos.name','������� ���������', SqlEntry::LIKE));
		$dec->AddEntry(new SqlEntry('dep.name','�����������', SqlEntry::LIKE));
		$dec->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
		$ug=new UsersSGroup;
		$users=$ug->GetItemsByDecArr($dec); $uids=array();
		foreach($users as $k=>$v) $uids[]=$v['id'];
		
		 
		if(in_array($user_id, $uids)){
			$sql='select count(*) from 
					'.$this->tablename.' as t
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=51
					 and t.id not in(select doc_vn_id from doc_vn_view where user_id="'.$user_id.'")  
					';
					
				
					
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.* from 
					'.$this->tablename.' as t
					
					where t.id in ('.implode(', ',$tender_ids).')
					 and t.status_id=51 
					  and t.id not in(select doc_vn_id from doc_vn_view where user_id="'.$user_id.'") 
				';
				
				 
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_doc_vn.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_km',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'��������! ��������� ����� ��������� �����!'
				
				);
			}
		
		}
		
		//������������ ��� ����� ����������� ��� ������� 2 = ���������
		$dec=new DBDecorator();
		//.dep.name
		$dec ->AddEntry(new SqlEntry('pos.name','������������ ������', SqlEntry::LIKE));
		$dec->AddEntry(new SqlEntry('dep.name','���', SqlEntry::LIKE));
		$dec->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
		$ug=new UsersSGroup;
		$users=$ug->GetItemsByDecArr($dec); $uids=array();
		foreach($users as $k=>$v) $uids[]=$v['id'];
		
		 
		if(in_array($user_id, $uids)){
			$sql='select count(*) from 
					'.$this->tablename.' as t
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=2
					 and t.id not in(select doc_vn_id from doc_vn_view where user_id="'.$user_id.'")  
					';
					
				
					
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.* from 
					'.$this->tablename.' as t
					
					where t.id in ('.implode(', ',$tender_ids).')
					 and t.status_id=2 
					  and t.id not in(select doc_vn_id from doc_vn_view where user_id="'.$user_id.'") 
				';
				
				 
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_doc_vn.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_km',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'��������! ���������� ����� ������������!'
				
				);
			}
		
		}
		
		//�������� - ������ ����� - ����������� �� �� ������-��
		//+����������� ��
		$sql='select count(*) from 
				'.$this->tablename.' as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				 and t.status_id=33
				 
				';
				
		$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if(!eregi('�����������',$upos['name'])){
			 	$sql.='and  t.manager_id="'.$user_id.'" ';
					
			} 			
				
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		   
		$f=mysqli_fetch_array($rs);	
		
		if((int)$f[0]>0){
			//������� ������ ���, ���������� �������� �������
			$sql='select t.* from 
				'.$this->tablename.' as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				 and t.status_id=33 
				 
			';
			
			$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if(!eregi('�����������',$upos['name'])){
			 	$sql.='and  t.manager_id="'.$user_id.'" ';
					
			} 	
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$g=mysqli_fetch_array($rs);		
			$url='ed_doc_vn.php?action=1&id='.$g['id'].'&from_begin=1';
			
			$data[]=array(
				'class'=>'menu_new_km',
				'num'=>(int)$f[0],
				'first_url'=>$url,
				'comment'=>'����������� ��������� ������� �� ������������!'
			
			);
		}
		
		//�������� - ������ ��������� - ����������� ����� �� ������-��
		//+����������� ��
		$sql='select count(*) from 
				'.$this->tablename.' as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				 and t.status_id=8
				 
				';
				
		$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if(!eregi('�����������',$upos['name'])){
			 	$sql.='and  t.manager_id="'.$user_id.'" ';
					
			} 			
				
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		   
		$f=mysqli_fetch_array($rs);	
		
		if((int)$f[0]>0){
			//������� ������ ���, ���������� �������� �������
			$sql='select t.* from 
				'.$this->tablename.' as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				 and t.status_id=8 
				 
			';
			
			$_ui=new UserSItem;
			$user=$_ui->GetItemById($user_id);
			$_upos=new UserPosItem;
			$upos=$_upos->GetItemById($user['position_id']);
			if(!eregi('�����������',$upos['name'])){
			 	$sql.='and  t.manager_id="'.$user_id.'" ';
					
			} 	
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$g=mysqli_fetch_array($rs);		
			$url='ed_doc_vn.php?action=1&id='.$g['id'].'&from_begin=1';
			
			$data[]=array(
				'class'=>'menu_new_km',
				'num'=>(int)$f[0],
				'first_url'=>$url,
				'comment'=>'����������� ����� � ������� ��������� ������� �� ������������!'
			
			);
		}
		
		
		
		//������������ + ��������� -  ��������� � 3 ��������� (�.�. ������ 2) - ����������, ��������� ����� �� ����������� ������
			
		$sql='select count(*) from 
				'.$this->tablename.' as t
				left join sched as ss on t.sched_id=ss.id
				
				where t.id in ('.implode(', ',$tender_ids).')
				 and t.status_id=2
				and ss.is_confirmed_done=1 
				';
				
					$dec=new DBDecorator();
		//.dep.name
			$dec ->AddEntry(new SqlEntry('pos.name','�����������', SqlEntry::LIKE));
			$dec ->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
			$ug=new UsersSGroup;
			$users=$ug->GetItemsByDecArr($dec); $uids=array();
		    foreach($users as $k=>$v) $uids[]=$v['id'];
			
			if(!in_array($user_id, $uids))  $sql.='and  t.manager_id="'.$user_id.'" ';
			
		//echo $sql;	
				
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		   
		$f=mysqli_fetch_array($rs);	
		
		if((int)$f[0]>0){
			//������� ������ ���, ���������� �������� �������
			$sql='select t.* from 
				'.$this->tablename.' as t
				left join sched as ss on t.sched_id=ss.id
				where t.id in ('.implode(', ',$tender_ids).')
				 and t.status_id=2 
				 
				 and ss.is_confirmed_done=1
			';
			
			if(!in_array($user_id, $uids))  $sql.='and  t.manager_id="'.$user_id.'" ';
			
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$g=mysqli_fetch_array($rs);		
			$url='ed_doc_vn.php?action=1&id='.$g['id'].'&from_begin=1';
			
			$data[]=array(
				'class'=>'menu_new_km',
				'num'=>(int)$f[0],
				'first_url'=>$url,
				'comment'=>'����������, ��������� ����� �� ����������� ������!'
			
			);
			
			 
			
			 
		}
		
		
		$_ui=new UserSItem;
		$user=$_ui->GetItemById($user_id);
		$_upos=new UserPosItem;
		$upos=$_upos->GetItemById($user['position_id']);
		
		
		//������������ ������ - ������ �� ������������ - ���������� ��...
		if(eregi('������������ ������', $upos['name'])
			||$man->CheckAccess($user_id,'w',1099)	
		){	
			
			$add=''; 
			if(!$man->CheckAccess($user_id,'w',1099)) $add=' and m.department_id="'.$user['department_id'].'" ';
			
			$sql='select count(*) from 
					'.$this->tablename.' as t
					inner join user as m on m.id=t.manager_id
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=41
					 
					'.$add;
					
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.* from 
					'.$this->tablename.' as t
					inner join user as m on m.id=t.manager_id
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=41
					'.$add;
					 
				
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_doc_vn.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_km',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'���������� ��������� ������� �� ������������!'
				
				);
			}	
			
		}
		
		
		//������������ ������ - ������ �� ����������� - ��������� ��...
		if(eregi('����������� ��������', $upos['name'])
			||$man->CheckAccess($user_id,'w',1111)	
		){	
			$sql='select count(*) from 
					'.$this->tablename.' as t
					inner join user as m on m.id=t.manager_id
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=43
					/*and m.department_id="'.$user['department_id'].'" */
					';
					
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.* from 
					'.$this->tablename.' as t
					inner join user as m on m.id=t.manager_id
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=43
					/*and m.department_id="'.$user['department_id'].'" */
					 
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_doc_vn.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_km',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'��������� ��������� ������� �� ������������!'
				
				);
			}	
			
		}
		
		
		
		//������������ ������ - ������ ����� �� ������������ - ���������� �����...
		if(eregi('������������ ������', $upos['name'])
			||$man->CheckAccess($user_id,'w',1109)	
		){	
			
			$add=''; 
			if(!$man->CheckAccess($user_id,'w',1109)) $add=' and m.department_id="'.$user['department_id'].'" ';
			$sql='select count(*) from 
					'.$this->tablename.' as t
					inner join user as m on m.id=t.manager_id
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=48
					
					'.$add;
					
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.* from 
					'.$this->tablename.' as t
					inner join user as m on m.id=t.manager_id
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=48
					 
					 
				'.$add;
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_doc_vn.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_km',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'���������� ����� � ������������ ��������� ������� �� ������������!'
				
				);
			}	
			
		}
		
		
		//������������ ������ - ������ ����� �� ����������� - ��������� �����...
		if(eregi('����������� ��������', $upos['name'])
			||$man->CheckAccess($user_id,'w',1113)	
		){	
			$sql='select count(*) from 
					'.$this->tablename.' as t
					inner join user as m on m.id=t.manager_id
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=50
					/*and m.department_id="'.$user['department_id'].'" */
					';
			
			//echo $sql.'<br>';
					
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//������� ������ ���, ���������� �������� �������
				$sql='select t.* from 
					'.$this->tablename.' as t
					inner join user as m on m.id=t.manager_id
					
					where t.id in ('.implode(', ',$tender_ids).')
					and t.status_id=50
					/*and m.department_id="'.$user['department_id'].'" */
					 
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_doc_vn.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_km',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'��������� ����� � ������������ ��������� ������� �� ������������!'
				
				);
			}	
			
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
	
	
	//�������������� �������������
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		 $log=new ActionLog();
		
		$_stat=new DocStatusItem;
		
	 
		$_ni=new DocVn_HistoryItem;
		
		$sql='select * from '.$this->tablename.' where status_id<>'.$annul_status_id.' order by id desc';
		//echo $sql;
		$set=new MysqlSet($sql);
		
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
						$reason='������ ����� '.$days_after_restore.' ���� � ���� �������������� ��������� �������,  �������� �� ���������';
					}
				}else{
					//�������� � ����� ��������	
					
					
					if(($f['pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='������ ����� '.$days.' ���� � ���� �������� ��������� �������,  �������� �� ���������';
					}
				}
			}
			 
			
			
			
			
			
			
			
			if($can_annul){
				
					$_res=new  DocVn_Resolver($f['kind_id']);
				//$f['contact_value']=$_res->instance->ConstructContacts($f['id'], $f);
			 
			
			 $_res->instance->Edit($f['id'], array('is_confirmed'=>0, 'status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
				
				$log->PutEntry(0,'�������������� ������������� ��������� �������',NULL,1096,NULL,'� ���������: '.$f['code'].' ���������� ������ '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'sched_id'=>$f['id'],
			 
				'pdate'=>time(),
				'user_id'=>0,
				'txt'=>'�������������� ����������: ��������� ������� ���� ������������� ������������, �������: '.$reason.'.'
				));
					
			}
		}
		
	}
	
}




/****************************************************************************************************/
// ������ ������� � ���� ��-�
class  DocVn_OtGroup extends DocVn_AbstractGroup {
	 public $kind_id=2;
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn';
		$this->pagename='doc_inners.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		
		$this->_view=new DocVn_ViewsGroup2;
		 
		$this->_auth_result=NULL;
		
		$this->new_list=NULL;  $this->_unconfirm_right_id=1108;
	} 
	
	
	
	protected function GainSql(&$sql, &$sql_count){
		$sql='select distinct p.*,
		s.name as status_name, s.weight as status_weight,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active ,
		send.name_s as view_user, send.login as view_login, p.view_pdate as view_pdate,
		kind.name as kind_name
		 
		 
		 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
				left join '.$this->tablename.' as par on par.id=p.doc_vn_id 
				 
			 	left join sched as sc on par.sched_id=sc.id and sc.kind_id=2
				left join sched_suppliers as ss on ss.sched_id=sc.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join doc_vn_leads as dl on dl.doc_out_id=par.id
				left join lead as ld on ld.id=dl.lead_id
				  
				
				left join user as cr on cr.id=p.created_id
			 	
				left join user as send on send.id=p.user_ruk_id
				left join doc_vn_kind as kind on p.kind_id=kind.id
			 
			where p.kind_id="'.$this->kind_id.'"	 
				 	 
				 ';
				
		$sql_count='select count(*) 
					 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
			 
			 	 
			    left join '.$this->tablename.' as par on par.id=p.doc_vn_id 
				 
			 	left join sched as sc on par.sched_id=sc.id and sc.kind_id=2
				left join sched_suppliers as ss on ss.sched_id=sc.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join doc_vn_leads as dl on dl.doc_out_id=par.id
				left join lead as ld on ld.id=dl.lead_id
				  
				
				left join user as cr on cr.id=p.created_id
			 	
				left join user as send on send.id=p.user_ruk_id
				left join doc_vn_kind as kind on p.kind_id=kind.id
			 
			 
			where p.kind_id="'.$this->kind_id.'"	 
		 
				 ';
			
	}
	
	
	
	//������ ID ����������, ������� ����� ������ ������� ���������
	public function GetAvailableDocIds($user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//��������� �����������
		//���� �� ���� - �� ��� ��� ����
		if($_man->CheckAccess($user_id,'w',1105)){
			$sql='select id from doc_vn';
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
		}else{
			//����
			$sql='select id from doc_vn where manager_id="'.$user_id.'" or created_id="'.$user_id.'" ';	
			
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
	
	//��������, ����� �� �������� �������� ��� ��������� ��������� ���������� ����������
	public function ScanAvailableByUserId($manager_id, $user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//��������� �����������
		//���� �� ���� - �� ��� ��� ����
		if($_man->CheckAccess($user_id,'w',1105)){
			 
			
			return true;
		}else{
			//����
			
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
	
	
	  
	
	
	//��������������� ������ ����� ����� ��� ��������� ������������
	protected function ConstructNewList($user_id){
		$this->new_list=array();
		
		/*
		$this->new_list[]=array(
					'class'=>'menu_new_m',
					'num'=>(int)$f[0],
					'url'=>''
					'doc_ids'=>array(),
					'sub_ids'=>array(),
					'doc_counters'=>array(),
					'comment'=>'������� ���� � ������!'
				
				);
		*/	
		
		$tender_ids=$this->GetAvailableDocIds($user_id);
		
		  
		$man=new DiscrMan;
		
			
		$_ui=new UserSItem;
		$user=$_ui->GetItemById($user_id);
		 
		
		//�� �������� �������, ����������� ����� � ����� ������� ������ ��������� ������������ �� ��
		/*if(($user['department_id']==4)||
			$man->CheckAccess($user_id,'w',1015)	
		){
			$sql='select count(*) from 
				 
					kp_in as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				and t.kind_id=0 
				and t.is_confirmed=1
				and t.status_id not in(3,27)
				and  t.fulful_kp=0
				';
				
			//echo $sql;	
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				 
				$sql='select t.* from 
					
					kp_in as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				 and t.kind_id=0
				and t.is_confirmed=1
				and t.status_id not in(3,27)
				and  t.fulful_kp=0
				order by t.id 
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array(); $sub_ids=array();
				for($j=0; $j<$rc; $j++){ 
					$g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['id']; 
				}	
				
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_kpv',
					'num'=>(int)$rc,
						'doc_ids'=>$doc_ids,
						'doc_counters'=>array(),
						'sub_ids'=>$sub_ids,
						'url'=>'ed_kp_in.php?action=1&id={id}',
					'comment'=>'����� �������: ��������� ������������ �� ��!'
				
				);
			}
		}*/
		
		 
		
	}
	
	
	
	//�������������� �������������
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		 
	}
	
}


 







   
    




/*******************************************************************************************************/


// ���������� ��������� �-��
class DocVn_SupplierContactGroup extends SupplierContactGroup {
	
	
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
		
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" order by id asc');
		
		
		
		$_sdg=new DocVn_SupplierContactDataGroup;
		
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
class DocVn_SupplierContactDataGroup extends SupplierContactDataGroup {
	
	
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


//����������� ��. ���������
class DocVn_SupplierItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='sched_suppliers';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}



class DocVn_SupplierGroup extends AbstractGroup {
	 
	public $pagename;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='sched_suppliers';
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
		$_sc=new DocVn_ScGroup;
		
		$sql='select ct.sched_id, ct.id as c_id, ct.note,  s.full_name, opf.name as opf_name,
		s.id as supplier_id, s.id as id, ct.result, ct.not_meet
		
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
	
	
	
	 
	 
	
}

//�������� ����������� �� �� ���-��	
class DocVn_ScItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='sched_suppliers_contacts';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sc_id';	
	}
	
}



class DocVn_ScGroup extends AbstractGroup {
	 
	public $pagename;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='sched_suppliers_contacts';
		$this->pagename='view.php';		
		$this->subkeyname='sc_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	//������, ����� ����
	public function GetItemsByIdArr($id){
		$arr=array();
		
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


/******************************************************************************************************/
//���� �� ���-���

//������ ��� �� ���-���
class DocVn_KindItem extends AbstractItem{
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_kind';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
}

//������ ����� �� ���-���
class DocVn_KindGroup extends AbstractGroup {
	 
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_kind';
		$this->pagename='claim.php';		
		$this->subkeyname='doc_out_id';	
		$this->vis_name='is_shown';		
		 
	}
	
}

 


/****************************************************************************************************/
//������������� ����

//������ ������������� ���
class DocVn_LeadItem extends AbstractItem{
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_leads';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
}

//������ �����, ������������� � �� ���
class  DocVn_LeadGroup extends AbstractGroup {
	 
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_leads';
		$this->pagename='claim.php';		
		$this->subkeyname='doc_out_id';	
		$this->vis_name='is_shown';		
		 
	}
	
	
	//������ ������� - ��������� ����� ��� ���������� ������
	public function GetItemsByIdArr($id, $show_statistics=true, $document=NULL){
		$alls=array();
		
		 
		$sql='select des.*,
			doc.code as code, doc.id as d_id, doc.pdate, 
			st.name as status_name
			
		 
		from  doc_vn_leads as des
		inner join lead as doc on doc.id=des.lead_id
		left join document_status as st on doc.status_id=st.id 
		
		where 
		 	des.'.$this->subkeyname.'="'.$id.'" 
			 
		
			order by doc.code asc';
		
		//echo $sql."<br><br>";
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$no=$rc;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$alls[]=$f;
		}
		
		return $alls;
	}
	
	
	//������� ����
	public function AddItems($current_id, array $positions,  $result=NULL){
		$_kpi=new DocVn_LeadItem; 
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$this->GetItemsByIdArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('doc_out_id'=>$v['doc_out_id'],'lead_id'=>$v['lead_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				 
				
				$add_array=array();
				$add_array['doc_out_id']=$v['doc_out_id'];
				$add_array['lead_id']=$v['lead_id'];
				
				$add_array['pdate']=time();
				
			 
				
				 
				$code=$_kpi->Add($add_array);
				 
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'doc_out_id'=>$v['doc_out_id'],
					'lead_id'=>$v['lead_id'] 
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['doc_out_id']=$v['doc_out_id'];
				$add_array['lead_id']=$v['lead_id'];
				
			 
				 
				$_kpi->Edit($kpi['id'],$add_array);
				
			 
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//���� ���� ���������
				
				//��� ����������
				
				$to_log=false;
				 
				if($kpi['doc_out_id']!=$add_array['doc_out_id']) $to_log=$to_log||true;
				if($kpi['lead_id']!=$add_array['lead_id']) $to_log=$to_log||true;
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					 
					'doc_out_id'=>$v['doc_out_id'],
					'lead_id'=>$v['lead_id'] 
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
				if(($vv['doc_out_id']==$v['doc_out_id'])&&($vv['lead_id']==$v['lead_id'])
				 
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
					
					'doc_out_id'=>$v['doc_out_id'],
					'lead_id'=>$v['lead_id'],
					'note'=>$v['note']
			);
			
			//������� �������
			$_kpi->Del($v['id']);
			
		 
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	
}
  

/**************************************************************************************************/

// users S
class DocVn_UsersSGroup extends UsersSGroup {
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
	
	 
	 //����� ���-�� ������
	 public function GetRuk($result){
		$res=false; 
		$sql='select u.*, pos.name as position_name from user as u left join user_position as pos on pos.id=u.position_id where is_active=1 and department_id="'.$result['department_id'].'" and pos.name LIKE "%������������ ������%"';
		
		//echo $sql;
		
		$set=new mysqlset($sql);
		$rc=$set->getresultnumrows();
		$rs=$set->getresult();
		
		if($rc>0){
			$res=mysqli_fetch_array($rs);
		}
		
		return $res; 
	 }
	 
	 
	 //����� ���������
	 public function GetDir($result){
		$res=false; 
		$sql='select u.*, pos.name as position_name from user as u left join user_position as pos on pos.id=u.position_id where is_active=1  and pos.name LIKE "%����������� ��������%"';
		
		//echo $sql;
		
		$set=new mysqlset($sql);
		$rc=$set->getresultnumrows();
		$rs=$set->getresult();
		
		if($rc>0){
			$res=mysqli_fetch_array($rs);
		}
		
		return $res; 
	 }
	
	
	
	
	//����� ����������� ��� ������ � ������ ����
	public function GetItemsForBill(  DBDecorator $dec){
		$txt='';
		
		 
		
		$sql='select p.*, up.name as position_s, p.id as user_id from '.$this->tablename.' as p 
		
		left join user_position as up on up.id=p.position_id
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


/****************************************************************************************************/
//������ ������������
class DocVn_CityGroup extends AbstractGroup {
	 
	public $pagename;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='sched_cities';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	
	
	
	 
	//������ �������, ����� ����
	public function GetItemsByIdArr($sched_id){
		$arr=array();
		
		$sql='select ct.sched_id, ct.id as c_id, c.name as name, r.name as region_name, o.name as okrug_name, 
			sc.name as country_name,
		c.id as city_id, c.id as id, c.country_id
		
		 from '.$this->tablename.' as ct 
		 left join sprav_city as c on ct.city_id=c.id
		 left join sprav_region as r on c.region_id=r.id
		 left join sprav_district as o on o.id=c.district_id
		 left join sprav_country as sc on sc.id=c.country_id
		
		where ct.sched_id="'.$sched_id.'" order by c.name asc, r.name asc, o.name asc';
		 
		
		 
		 
		
		//echo $sql."<p>";
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			 
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	 
	 
	
}


/**************************************************************************************************/
//������� ������ � ��������
//������ ��� ������� ������ � ��������
class DocVn_VyhReasonItem extends AbstractItem{
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_vyh_reason';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
}

//������ ����� ������ ������ � ��������
class DocVn_VyhReasonGroup extends AbstractGroup {
	 
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_vyh_reason';
		$this->pagename='claim.php';		
		$this->subkeyname='doc_out_id';	
		$this->vis_name='is_shown';		
		 
	}
	
}



/*****************************************************************************************************/
//���� �������� �� ���-��
class DocVn_ExpensesBlock{
	
	public static $sut_currency_id=1; public static $sut_currency_sign='���.';
	
	//���������� ������ �� ���� ���������
	public function ConstructById($id, $item=NULL, $result=NULL){
		
		if($item===NULL){
			$_item=new DocVn_AbstractItem; 
			$item=$_item->getitembyid($id);
		}
		if($result===NULL){
			$au=new authuser();
			$result=$au->Auth(false,false,false);
		}
		
		$sql='select des.*,
		doc.name, doc.is_auto,
		plc.signature as pl_currency, fc.signature as f_currency
		
			 
		from  doc_vn_expenses as des
		left join doc_vn_expenses_kinds as doc on doc.id=des.expenses_id
		left join pl_currency as plc on plc.id=des.plan_currency_id
		left join pl_currency as fc on fc.id=des.fact_currency_id
		 
		
		 
		where 
		 	des.doc_vn_id="'.$id.'"  
			
			order by des.doc_pdate asc, doc.ord desc, doc.id asc';
		
		//echo $sql."<br><br>";
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$no=$rc;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			if($f['doc_pdate']!=NULL) $f['doc_pdate']=date('d.m.Y', $f['doc_pdate']);
			
			if($f['is_auto']==1){
				$f['plan_currency_id']=self::$sut_currency_id;
				$f['fact_currency_id']=self::$sut_currency_id;
				$f['pl_currency']=self::$sut_currency_sign;
				//$f['f_currency']=self::$sut_currency_sign;
				
				$f['plan']=$this->CalcSut($item['sched_id'], NULL, $result);
				//$f['fact']=$f['plan']; 
				
			}
			
			$f['no']=($i+1).'.';
			
			$f['hash']=md5($f['expenses_id'].'_'.$f['pdate']);
			
			$alls[]=$f;
		}
		
		//var_dump($alls);
		
		return $alls;
	}
	
	
	//���������� ������ �� ���� ������
	public function ConstructByAccounts($accounts, $sched_id=0, $result=NULL){
		
		if($result===NULL){
			$au=new authuser();
			$result=$au->Auth(false,false,false);
		}
		
		foreach($accounts['hashes'] as $kh=>$hash){
			
			$sql='select 
			doc.*
			
				 
			from 
			doc_vn_expenses_kinds as doc 
		 
			
			 
			where 
				doc.id="'.$accounts['ids'][$kh].'"
				
				order by  doc.ord desc, doc.id asc';	
			
			//echo $sql."<br><br>";
			
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$no=$rc;
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				
				//
				$f['expenses_id']=$f['id'];
				
				if($f['is_auto']==1){
					$f['plan_currency_id']=self::$sut_currency_id;
					$f['fact_currency_id']=self::$sut_currency_id;
					$f['pl_currency']=self::$sut_currency_sign;
					$f['f_currency']=$accounts['data'][$hash]['fact_currency_id']; //self::$sut_currency_sign;
					
					$f['plan']=$this->CalcSut($sched_id, NULL, $result);
					$f['fact']=$accounts['data'][$hash]['fact']; //  $f['plan']; 
					 
					
				}else{
					//  /	
				//	var_dump( $accounts['data'][$f['id']]['plan']);
					
					$f['plan_currency_id']=$accounts['data'][$hash]['plan_currency_id'];
					$f['fact_currency_id']=$accounts['data'][$hash]['fact_currency_id'];
				
				
					$f['plan']=$accounts['data'][$hash]['plan'];
					$f['fact']=$accounts['data'][$hash]['fact'];
				}
				
				$f['doc_name']= $accounts['data'][$hash]['doc_name'];  
				$f['doc_no']= $accounts['data'][$hash]['doc_no']; 
				$f['doc_pdate']= $accounts['data'][$hash]['doc_pdate']; 
				$f['fact_l_or_korp']= $accounts['data'][$hash]['fact_l_or_korp']; 
				
				$f['pdate']= $accounts['data'][$hash]['pdate']; 
				
				$f['no']=($kh+1).'.';
				
				$f['hash']=md5($f['expenses_id'].'_'.$accounts['data'][$hash]['pdate']);
				
				
				$alls[]=$f;
				
				 
			}
		}
			
		return $alls;	
		
	}
	
	
	//����� ������� ����� (�� ���� �������)
	public function CalcItogoArr($accounts, $id, $item=NULL, $result=NULL){
		$alls=array();
		
		if($item===NULL){
			$_item=new DocVn_AbstractItem;;
			$item=$_item->getitembyid($id);
		}
		if($result===NULL){
			$au=new authuser();
			$result=$au->Auth(false,false,false);
		}
		
		$curr_ids=array();
		//��������� ������, ������ ������, ���������� ������ ������ �����
		$alls[0]=array('name'=>'����� �� ���� �������','plan'=>0, 'fact'=>0, 'plan_currency'=>'���.', 'fact_currency'=>'���.', 'id'=>0); 
		foreach($accounts as $k=>$v){
			if(!in_array($v['plan_currency_id'], $curr_ids)) $curr_ids[]=$v['plan_currency_id'];
			if(!in_array($v['fact_currency_id'], $curr_ids)) $curr_ids[]=$v['fact_currency_id'];
			
		}
		
		 
		$_curr=new PlCurrItem;
		foreach($curr_ids as $v){
			 $curr=$_curr->GetItemById($v);
			 $alls[$v]=array('name'=>'�����','plan'=>0, 'fact'=>0, 'plan_currency'=>$curr['signature'], 'fact_currency'=>$curr['signature'], 'id'=>$v); 
		}
		
		//��������� ������, ���������� ������ �����
		foreach($accounts as $k=>$v){
			$alls[$v['plan_currency_id']]['plan']+=$v['plan'];
			$alls[$v['fact_currency_id']]['fact']+=$v['fact'];
			
		}
		
		//���������, �������������, ������ ����� � ���. ���� ����������� �������� �����!
		$_solver=new CurrencySolver;
		//			
		//			$dol=round(CurrencySolver::Convert(1, $rates, 3, 1),5);
		//			$eur=round(CurrencySolver::Convert(1, $rates, 2, 1),5);
		
		if(isset($item['confirm_pdate'])&&($item['is_confirmed']==1)) $pdate1=	date('d.m.Y',$item['confirm_pdate']); else $pdate1=	date('d.m.Y');
		
		if(isset($item['confirm_done_pdate'])&&($item['is_confirmed_done']==1)) $pdate2=	date('d.m.Y',$item['confirm_done_pdate']); else $pdate2=date('d.m.Y');
		
		$rates1=$_solver->GetToDate($pdate1); $rates2=$_solver->GetToDate($pdate2);
		
		foreach($alls as $k=>$v){
			if($k==0) continue;
			
			$alls[0]['plan']+=round(CurrencySolver::Convert($v['plan'],  $rates1,  $k,  1), 2);	
			$alls[0]['fact']+=round(CurrencySolver::Convert($v['fact'],  $rates2,   $k,  1), 2);	
		}
		
		$alls=array_reverse($alls);
		
		return $alls;
	}
	
	//������ ����� �������� ����� ���� (��� �������� �����), � ���������� �� ������ � ���� �����
	public function CalcFactItogoArr($id, $item=NULL, $result=NULL){
		$alls=array();
		
		if($item===NULL){
			$_item=new DocVn_AbstractItem;;
			$item=$_item->getitembyid($id);
		}
		if($result===NULL){
			$au=new authuser();
			$result=$au->Auth(false,false,false);
		}
		
		$accounts=$this->ConstructById($id,$item, $result);
		
		$curr_ids=array();
		//��������� ������, ������ ������, ���������� ������ ������ �����
		//$alls[0]=array('name'=>'����� �� ���� �������','plan'=>0, 'fact'=>0, 'plan_currency'=>'���.', 'fact_currency'=>'���.', 'id'=>0); 
		foreach($accounts as $k=>$v){
			if(!in_array($v['plan_currency_id'], $curr_ids)) $curr_ids[]=$v['plan_currency_id'];
			if(!in_array($v['fact_currency_id'], $curr_ids)) $curr_ids[]=$v['fact_currency_id'];
			
		}
		
		 
		$_curr=new PlCurrItem;
		foreach($curr_ids as $v){
			 $curr=$_curr->GetItemById($v);
			 $alls[$v][0]=array('name'=>'�����','plan'=>0, 'fact'=>0, 'plan_currency'=>$curr['signature'], 'fact_currency'=>$curr['signature'], 'id'=>$v); 
			 $alls[$v][1]=array('name'=>'�����','plan'=>0, 'fact'=>0, 'plan_currency'=>$curr['signature'], 'fact_currency'=>$curr['signature'], 'id'=>$v); 
		}
		
		//��������� ������, ���������� ������ �����
		foreach($accounts as $k=>$v){
			$alls[$v['plan_currency_id']][0]['plan']+=$v['plan'];
			$alls[$v['plan_currency_id']][1]['plan']+=$v['plan'];
			
			 
			$alls[$v['fact_currency_id']][$v['fact_l_or_korp']]['fact']+=$v['fact'];
			
		}
		
		//���������, �������������, ������ ����� � ���. ���� ����������� �������� �����!
		$_solver=new CurrencySolver;
		//			
		//			$dol=round(CurrencySolver::Convert(1, $rates, 3, 1),5);
		//			$eur=round(CurrencySolver::Convert(1, $rates, 2, 1),5);
		
		/*if(isset($item['confirm_pdate'])&&($item['is_confirmed']==1)) $pdate1=	date('d.m.Y',$item['confirm_pdate']); else $pdate1=	date('d.m.Y');
		
		if(isset($item['confirm_done_pdate'])&&($item['is_confirmed_done']==1)) $pdate2=	date('d.m.Y',$item['confirm_done_pdate']); else $pdate2=date('d.m.Y');
		
		$rates1=$_solver->GetToDate($pdate1); $rates2=$_solver->GetToDate($pdate2);
		
		foreach($alls as $k=>$v){
			if($k==0) continue;
			
			$alls[0]['plan']+=round(CurrencySolver::Convert($v['plan'],  $rates1,  $k,  1), 2);	
			$alls[0]['fact']+=round(CurrencySolver::Convert($v['fact'],  $rates2,   $k,  1), 2);	
		}*/
		
		//$alls=array_reverse($alls);
		
		return $alls;
	}
	
	
	//����� ������� �������� (���� ����� ���� ������������)
	public function CalcSut($sched_id, $item=NULL, $result=NULL){
		$cost=0;
		
		if($item===NULL){
			$_item=new Sched_AbstractItem;
			$item=$_item->getitembyid($sched_id);
		}
		if($result===NULL){
			$au=new authuser();
			$result=$au->Auth(false,false,false);
		}
		
		
		//$_hd=new HolyDates;
		 
		$begin_pdate=datefromdmy(datefromYmd($item['pdate_beg']));
		$end_pdate=datefromdmy(datefromYmd($item['pdate_end']));
		
		$total_days=0;
		for($date=$begin_pdate; $date<=$end_pdate; $date=$date+24*60*60){
			$total_days++;	
		}
		
		//echo $begin_pdate;
		//echo $total_days.'<br>';
		
		//����� �� � ������? 
		$not_in_rus=false;
		$_cg=new DocVn_CityGroup;
		$cities=$_cg->GetItemsByIdArr($sched_id);
		foreach($cities as $k=>$v){
			if($v['country_id']!=1) $not_in_rus=$not_in_rus||true;
		}
		
		$_rules=new DocVn_SutItem;
		$rules=$_rules->GetActualByPdate($result['org_id'], date('d.m.Y',$begin_pdate));
		
		 
		
		if($rules!==false){
			
			if($total_days<$rules['min_days']) $cost=0;
			else{
				
				
				if($not_in_rus){
					$cost=($total_days-1)*(float)$rules['cost_not_rus']+(float)$rules['last_day_cost'];
				}else{
					
					$cost=$total_days*(float)$rules['cost_rus'];	
					//echo $rules['cost_rus'].'<br>';
				}
					
			}
		}
		
		
		
		return $cost;	
	}
		
}

 //������ ��������� �� ������� ��������
class DocVn_ExpensesItem extends AbstractItem{
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_expenses';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
}


//������� ������ ��������

//������ ��������
class DocVn_ExpensesKindsItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_expenses_kinds';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
	 
	
	//�������
	public function Del($id){
		
		$query = 'delete from '.$this->tablename.' where parent_id="'.$id.'"';
		$it=new nonSet($query);
		
		 	
		
		parent::Del($id); 
	}	
	
	//����� ������������
	public function CountSubs($id, $p_or_m=0){
		$item=new mysqlSet('select count(*) from '.$this->tablename.' where parent_id="'.$id.'" ');
		

		$result=$item->getResult();
		$rc=$item->getResultNumRows();
		 
		 
			$res=mysqli_fetch_array($result);	
			
		return (int)$res[0];	
	}
	
}

//������ ������ ��������
class DocVn_ExpensesKindsGroup extends AbstractGroup {
	 
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_expenses_kinds';
		$this->pagename='claim.php';		
		$this->subkeyname='supplier_id';	
		$this->vis_name='is_shown';		
		 
	}
	
	
	public function LoadBranchArr($parent_id=0, $except_ids=NULL){
		
		$alls=array();
		
		
		
		$sql='select * from '.$this->tablename.' where parent_id="'.$parent_id.'"  ';
		
		//��������� ������ ��������, ���� ��� ���� ��������� �����!
		if(is_array($except_ids)) $sql.=' and id not in( select id from  '.$this->tablename.' where id in  ('.implode(', ',$except_ids).')  and is_auto=1 )'; // and is_auto=1)';
		
		$sql.='  order by ord desc, name asc';
		
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_it=new DocVn_ExpensesKindsItem;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['has_subbranches']=($_it->CountSubs($f['id'], $p_or_m)>0);
			
			$alls[]=$f;
		}	
			
		return $alls;	
		
	}
	
	
}

//������� ������� ��������
class DocVn_SutItem extends AbstractItem{
	
	 
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_sut';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_confirmed';	
		 
		 
	}
	
	//����� ��������� �������: ���� �� ����� �����������, ��������� ��
	public function GetActual($org_id){
		$sql='select p.* 
					
					
				from '.$this->tablename.' as p
					
					 
				where p.begin_pdate<="'.mktime(0,0,0,date('m'),date('d'), date('Y')).'"	
				and p.org_id="'.$org_id.'"
				order by begin_pdate desc limit 1
					'; 	
		
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$rc=$set->getresultnumrows();
		
		if($rc==0){
			return false;	
		}else{
			$f=mysqli_fetch_array($rs);
			
			return $f;	
		}
	}
	
	//�����  ������� �� ����: ���� �� ����� ��������, ��������� ��
	public function GetActualByPdate($org_id, $pdate){
		$sql='select p.* 
					
				from '.$this->tablename.' as p
					
				 
				where p.begin_pdate<="'.datefromdmy($pdate).'"	
				and p.org_id="'.$org_id.'"
				order by begin_pdate desc limit 1
					'; 	
		
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$rc=$set->getresultnumrows();
		
		if($rc==0){
			return false;	
		}else{
			$f=mysqli_fetch_array($rs);
			
			return $f;	
		}
	}
	
}


// ������ ������ ������� ��������
class DocVn_SutGroup extends AbstractGroup {
	 
	protected $_auth_result;
	
	
	
	public $prefix='_cash';
 
	protected $_item;
	protected $_notes_group;
	 
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_sut';
		$this->pagename='doc_sut.php';		
		 
		$this->vis_name='is_confirmed';		
		 
		$this->_item=new DocVn_SutItem;
		//$this->_notes_group=new CashNotesGroup;
		 
		
		$this->_auth_result=NULL;
	}
	
	
	
	
	
	
	
	public function ShowAllPos($template, 
	$dec,
	&$alls 
	 
	){
		
		
		
				
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$sql='select p.* 
					
					
				from '.$this->tablename.' as p
					
				 
					
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					
					 
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
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		 
	 

		
		$alls=array();
		
//		echo $total;
		
		$actual=$this->_item->GetActual($this->_auth_result['org_id']);
		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['begin_pdate']=date('d.m.Y', $f['begin_pdate']);
			
			$f['is_active']=(($actual!==false)&&($actual['id']==$f['id'])) ;
			
			$alls[]=$f;
		}
		
		//�������� ������ ������
		$current_supplier='';
		$user_confirm_id='';
	
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
			if($v->GetName()=='supplier_id') $current_supplier=$v->GetValue();
						
			if($v->GetName()=='user_confirm_id') $current_user_confirm_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		 
		$au=new AuthUser();
		//$result=$au->Auth();
		
		if($this->_auth_result===NULL){
			$result=$au->Auth();
			$this->_auth_result=$result;
		}else{
			$result=$this->_auth_result;	
		}
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		
		//$sm->assign('code',37);
		$sm->assign('pdate',date('d.m.Y')); 
		
		$sm->assign('prefix',$this->prefix);
		
		//������ ��� ������ ����������
		//������ ��� ������ ����������
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		
		
		
		
		return $sm->fetch($template);
	}
	
  
 
	public function SetSubkeyTable($t){
		$this->sub_tablename=$t;	
	}
}





/******************************************************************************************************/
//��� ������ - �������
class DocVn_VyhDateItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_vyh_date';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='doc_vn_id';
		
	}
	
}


//��� ������ -  ������
class DocVn_VyhDateGroup extends AbstractGroup {
	static public $weekdays=array(
			0=>'�����������',
			1=>'�����������',
			2=>'�������',
			3=>'�����',
			4=>'�������',
			5=>'�������',
			6=>'�������',
			
		
		);
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_vyh_date';
		$this->pagename='view.php';		
		$this->subkeyname='doc_vn_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//������ �������
	public function GetItemsArrById($id){
		//echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
		$arr=array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		
		$sql='select u.* 
		from '.$this->tablename.' as u
	 
		
		 where '.$this->subkeyname.'="'.$id.'" order by pdate asc,  id asc';
		
		//echo $sql.'<br>';
		
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate_unf']=$f['pdate'];
			
			$f['w']=date('w', $f['pdate']);
			$f['w_day']=self::$weekdays[(int)$f['w']];
			
			$f['pdate']=date('d.m.Y', $f['pdate']);
			
		
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	//������ � ������ ������ � ��������...
	public function AddVyhDates($id, $positions){
		$_kpi=new DocVn_VyhDateItem;
		
		$log_entries=array();	
		
		$old_positions=array();
		$old_positions=$this->GetItemsArrById($id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array(
			'doc_vn_id'=>$v['doc_vn_id'],
			'pdate'=>$v['pdate'] 
			
			));
			
 
			
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['doc_vn_id']=$v['doc_vn_id'];
				
				$add_array['pdate']=$v['pdate'];
				 
				
				
				 
				$_kpi->Add($add_array);
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
				 
					'doc_vn_id'=>$v['doc_vn_id'],
					'pdate'=>$v['pdate']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['doc_vn_id']=$v['doc_vn_id'];
				
				$add_array['pdate']=$v['pdate']; 
				
				$_kpi->Edit($kpi['id'],$add_array);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//���� ���� ���������
				
				//��� ����������? ���������� ����, �������
				
				$to_log=false;
				if($kpi['pdate']!=$add_array['pdate']) $to_log=$to_log||true;
				 
				
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					  'doc_vn_id'=>$v['doc_vn_id'],
					'pdate'=>$v['pdate']
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
			//$f['hash']=md5($f['pl_position_id'].'_'.$f['position_id'].'_'.$f['pl_discount_id'].'_'.$f['pl_discount_value'].'_'.$f['pl_discount_rub_or_percent']);
			
			foreach($positions as $kk=>$vv){
				if(($vv['doc_vn_id']==$v['doc_vn_id'])
				&&($vv['pdate']==$v['pdate_unf'])
				 
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
					'pdate'=>$v['pdate_unf'] 
			);

			
			//������� �������
			$_kpi->Del($v['id']);
		}
		
		
		return $log_entries;
	}
 
	
}




















/**************************************************************************************************/
//����� ���������
class DocVn_Messages{
 	
	
	//�������� ��������� ���-�� ��� � ��������� � ������ 2
	public function SendMessageToAho($id){
		$_dem=new DocVn_AbstractItem;
		$_doc=$_dem->GetItemById($id);
		$_res=new DocVn_Resolver($_doc['kind_id']);
		
		
		$doc=$_res->instance->GetItemById($id); 
		
		
		$dec=new DBDecorator();
	//.dep.name
		$dec ->AddEntry(new SqlEntry('pos.name','������������ ������', SqlEntry::LIKE));
		$dec->AddEntry(new SqlEntry('dep.name','���', SqlEntry::LIKE));
		$dec->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
		$ug=new UsersSGroup;
		$users=$ug->GetItemsByDecArr($dec); 
		
		
		foreach($users as $k=>$signer){
			$_mi=new MessageItem;	
			
			
			$_ui=new UserSItem;
			$signer=$_ui->GetItemById($signer['id']);
			
			$topic="��������! ����� ������������!";
			
			$txt='<div>';
			$txt.='<em>������ ��������� ������������� �������������.</em>';
			$txt.=' </div>';
			
			
			$txt.='<div>&nbsp;</div>';
			
			$txt.='<div>';
			$txt.='���������(��) '.$signer['name_s'].'!';
			$txt.='</div>';
			$txt.='<div>&nbsp;</div>';
			
			
			$txt.='<div>';
			$txt.='<strong>� GYDEX.��������������� ���������� ����� ������������:</strong>';
			$txt.='</div><ul>';
			
			$txt.='<li><a href="ed_doc_vn.php?action=1&id='.$id.'&from_begin=1" target="_blank">'.$_res->instance->ConstructName($id, $doc).'</a></li>';
	  
			$txt.='</ul><div><strong>������ ������������ ������������� ��� ����������� ��������!</strong></div>';
			
			
			$txt.='<div>&nbsp;</div>';
		
			$txt.='<div>';
			$txt.='C ���������, ��������� "'.SITETITLE.'".';
			$txt.='</div>';
			
			$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$signer['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
		}
		
	}
	
	//�������� ��������� ���� ��� � ��������� � ������ 51
	public function SendMessageToBuh($id){
		$_dem=new DocVn_AbstractItem;
		$_doc=$_dem->GetItemById($id);
		$_res=new DocVn_Resolver($_doc['kind_id']);
		
		
		$doc=$_res->instance->GetItemById($id); 
		
		
		$dec=new DBDecorator();
	//.dep.name
		$dec ->AddEntry(new SqlEntry('pos.name','������� ���������', SqlEntry::LIKE));
		$dec->AddEntry(new SqlEntry('dep.name','�����������', SqlEntry::LIKE));
		$dec->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
		$ug=new UsersSGroup;
		$users=$ug->GetItemsByDecArr($dec); 
		
		
		foreach($users as $k=>$signer){
			$_mi=new MessageItem;	
			
			
			$_ui=new UserSItem;
			$signer=$_ui->GetItemById($signer['id']);
			
			$topic="��������! ��������� ����� ��������� �����!";
			
			$txt='<div>';
			$txt.='<em>������ ��������� ������������� �������������.</em>';
			$txt.=' </div>';
			
			
			$txt.='<div>&nbsp;</div>';
			
			$txt.='<div>';
			$txt.='���������(��) '.$signer['name_s'].'!';
			$txt.='</div>';
			$txt.='<div>&nbsp;</div>';
			
			
			$txt.='<div>';
			$txt.='<strong>� GYDEX.��������������� ��������� ����� ��������� �����:</strong>';
			$txt.='</div><ul>';
			
			$txt.='<li><a href="ed_doc_vn.php?action=1&id='.$id.'&from_begin=1" target="_blank">'.$_res->instance->ConstructName($id, $doc).'</a></li>';
	  
			$txt.='</ul>';
			
			$txt.='<div>';
			$txt.='������� ������� � �������� ��� �������� ���������� �� ���������� ������������.';
			$txt.=' </div>';
			
			$txt.='<div><strong>������ ������������ ������������� ��� ����������� ��������!</strong></div>';
			
			
			$txt.='<div>&nbsp;</div>';
		
			$txt.='<div>';
			$txt.='C ���������, ��������� "'.SITETITLE.'".';
			$txt.='</div>';
			
			$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$signer['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
		}
		
	}
	
	
	
	//�������� ��������� �������� ��� ������� �����������
	public function SendMessageToView($id){
		$_dem=new DocIn_AbstractItem;
		$_doc=$_dem->GetItemById($id);
		$_res=new DocIn_Resolver($_doc['kind_id']);
		
		
		$doc=$_res->instance->GetItemById($id); 
		$_mi=new MessageItem;	
		
		$_ui=new UserSItem;
		$signer=$_ui->GetItemById($doc['manager_id']);
		
		$topic="�������� �������� �������� ��� �� ������������";
		
		$txt='<div>';
		$txt.='<em>������ ��������� ������������� �������������.</em>';
		$txt.=' </div>';
		
		
		$txt.='<div>&nbsp;</div>';
		
		$txt.='<div>';
		$txt.='���������(��) '.$signer['name_s'].'!';
		$txt.='</div>';
		$txt.='<div>&nbsp;</div>';
		
		
		$txt.='<div>';
		$txt.='<strong>� GYDEX.��������������� ��� �������� �� ������������ ��������� �������� ��������:</strong>';
		$txt.='</div><ul>';
		
		$txt.='<li><a href="ed_doc_in.php?action=1&id='.$id.'&from_begin=1" target="_blank">'.$_res->instance->ConstructName($id, $doc).'</a></li>';
  
		$txt.='</ul><div><strong>������ ������������ ������������� ��� ����������� ��������!</strong></div>';
		
		
		$txt.='<div>&nbsp;</div>';
	
		$txt.='<div>';
		$txt.='C ���������, ��������� "'.SITETITLE.'".';
		$txt.='</div>';
		
		$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$signer['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
		
	}
	
	
	
	//����������� � �������, �� ������� �� ����������
	//�������: ���� (0-00-00) + 5 ���� = ������� 0-00-00 � ���� (23-59-59) + 5 ���� = ������� 23-59-59
	//�� �������: ���� (0-00-00) + 10 ���� = ������� 0-00-00 � ���� (23-59-59) + 10 ���� = ������� 23-59-59
	public function SendMessageRemind(){
		$filters=array();
		$checking_pdate=mktime(0,0,0,date('m'),date('d'), date('Y'));
		
		$_mi=new MessageItem;	
		
		 
		for($i=5; $i<=10; $i=$i+5){
			$filters[$i]='(  (p.pdate between "'.($checking_pdate-$i*24*60*60).'" and "'.($checking_pdate-$i*24*60*60+23*60*60+59*60+59).'") )';
			 
		}
		 
		$sql=' select * from user where is_active=1 and id in(select p.manager_id from doc_in as p where p.is_confirmed=1 and ('.implode(' or ',$filters).')) order by id asc';
		
		//echo $sql;
			
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$user=mysqli_fetch_array($rs);
			
			//��������� ������ ���������
			
			$cter=1; 
			foreach($filters as $k=>$filter){
				if($cter==1) $filter=' p.is_urgent=1 and '.$filter;
				
				$sql1='select p.* from doc_in as p where p.manager_id="'.$user['id'].'" and p.is_confirmed=1 and '.$filter.' order by p.code asc';
				
				//echo $sql1.'<br><br>';
				$set1=new mysqlset($sql1);
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				
				if($rc1==0) continue;
				
				$docs=array();
				for($i1=0; $i1<$rc1; $i1++){
					$f=mysqli_fetch_array($rs1);
					$_res=new DocIn_Resolver($f['kind_id']);
					
					$docs[]='<li><a href="ed_doc_in.php?action=1&id='.$f['id'].'&from_begin=1" target="_blank">'.$_res->instance->ConstructName($f['id']).'</a></li>';
					
				}
				
				
				$topic="��������� ����� �� �������� ���������";
		
				$txt='<div>';
				$txt.='<em>������ ��������� ������������� �������������.</em>';
				$txt.=' </div>';
				
				
				$txt.='<div>&nbsp;</div>';
				
				$txt.='<div>';
				$txt.='���������(��) '.$signer['name_s'].'!';
				$txt.='</div>';
				$txt.='<div>&nbsp;</div>';
				
				
				$txt.='<div>';
				$txt.='<strong>� GYDEX.��������������� ������� ��������� ����� �� ��������� �������� ���������:</strong>';
				$txt.='</div><ul>';
				
				$txt.=implode(' ', $docs);
		  
				$txt.='</ul><div><strong>������ ������������ �������� �� ��� �������� ��������!</strong></div>';
				
				
				$txt.='<div>&nbsp;</div>';
			
				$txt.='<div>';
				$txt.='C ���������, ��������� "'.SITETITLE.'".';
				$txt.='</div>';
				
				$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);
				
				
				$cter++;
			}
			
				
		}
		
		
			
	}
	
	
	// ��������� � 3 ��������� (�.�. ������ 2) - ����������, ��������� ����� �� ����������� ������
	public function SendFactRemind(){	
	
		$_mi=new MessageItem;	
		
	 
		$sql=' select * from user where is_active=1 and id in(select p.manager_id from doc_vn as p where p.status_id=2 ) order by id asc';
		
		//echo $sql;
			
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$user=mysqli_fetch_array($rs);
				
			$sql1='select t.* from 
			doc_vn as t
			left join sched as ss on t.sched_id=ss.id
			
			where t.manager_id="'.$user['id'].'"
			 and t.status_id=2
			 and ss.is_confirmed_done=1
			';
			
			
		
			//echo $sql1.'<br><br>';
			$set1=new mysqlset($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			
			if($rc1==0) continue;
			
			$docs=array();
			for($i1=0; $i1<$rc1; $i1++){
				$f=mysqli_fetch_array($rs1);
				$_res=new DocVn_Resolver($f['kind_id']);
				
				$docs[]='<li><a href="ed_doc_vn.php?action=1&id='.$f['id'].'&from_begin=1" target="_blank">'.$_res->instance->ConstructName($f['id']).'</a></li>';
				
			}
			
			
			$topic="���������� ��������� ����� �� ����������� ������";
	
			$txt='<div>';
			$txt.='<em>������ ��������� ������������� �������������.</em>';
			$txt.=' </div>';
			
			
			$txt.='<div>&nbsp;</div>';
			
			$txt.='<div>';
			$txt.='���������(��) '.$signer['name_s'].'!';
			$txt.='</div>';
			$txt.='<div>&nbsp;</div>';
			
			
			$txt.='<div>';
			$txt.='<strong>� GYDEX.��������������� ���������� ��������� ����� �� ����������� ������ �� ��������� ����������:</strong>';
			$txt.='</div><ul>';
			
			$txt.=implode(' ', $docs);
	  
			$txt.='</ul><div><strong>������ ������������ ��������� ��������� ���������!</strong></div>';
			
			
			$txt.='<div>&nbsp;</div>';
		
			$txt.='<div>';
			$txt.='C ���������, ��������� "'.SITETITLE.'".';
			$txt.='</div>';
			
			$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);
		}
	}
}




//�������� ����� ���
class DocVn_ViewItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='doc_vn_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='tender_id';	
	}
	
	 
	
}
?>