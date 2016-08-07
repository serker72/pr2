<?
require_once('abstractitem.php');
require_once('petitionfileitem.php');
require_once('docstatusitem.php');


require_once('authuser.php');
require_once('actionlog.php');

require_once('petitionuseritem.php');
require_once('petition_client_item.php');
require_once('petition_client_group.php');


require_once('petition_vyhdate_group.php');
require_once('petition_vyhdate_item.php');

require_once('petition_vyhdate_otp_group.php');
require_once('petition_vyhdate_otp_item.php');
require_once('petition_field_rules.php');
require_once('petitionkinditem.php');
require_once('sched.class.php');
require_once('holy_dates.php');
require_once('user_s_item.php');
require_once('user_s_group.php');

//���������
class PetitionItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='petition';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
	
	
	/*public function Add($params){
		
		
		$code=parent::Add($params);	
		
		
		return $code;
	}
	*/
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		$_kind=new PetitionKindItem;
		$kind=$_kind->GetItemById($item['kind_id']);
		
		
		
		return '��������� '.$item['code'].'. ���: '.$kind['name'].', ������ '.$stat['name'];
	}
	
	
	
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		parent::Edit($id,$params);	
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	
	public function SendStatusMessage($order_id, $status_id){
		
		 
	}
	
	
	
	//�������
	public function Del($id){
		//������� ��� �����
		$fset=new MysqlSet('select * from petition_file where  petition_id="'.$id.'"');
		$fc=$fset->GetResultNumRows();
		$rfs=$fset->GetResult();
		
		$fi=new PetitionFileItem;
		for($i=0; $i<$fc; $i++){
			$f=mysqli_fetch_array($rfs);
			//GetStoragePath()
			@unlink($fi->GetStoragePath().$f['filename']);
		}
		
		
		
		//������� ����� �� ��
		$query = 'delete from petition_file where   petition_id="'.$id.'" ';
		$it=new nonSet($query);
		
		//������� �������
		/*$query = 'delete from petition_history where petition_id="'.$id.'";';
		$it=new nonSet($query);
		*/
		parent::Del($id);
	}	
	
	
	
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		
		$_dsi=new DocStatusItem;
		if(($item['status_id']!=1)&&($item['status_id']!=18)){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������ ���������: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}else{
		
		 
		  
		 
		  
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
	
	
	
	
	//�������� � ��������� ������� 
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		$_messages=new Petition_Messages;
		
		$setted_status_id=$item['status_id'];
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
			
			
			
			if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)){
				//����� ������� �� 41
				$setted_status_id=41;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ���������',NULL,725,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
				
				//���� ������������ ���-�� ���-��� ������ - �� ����� ��������� � ���� ���-�� ������
				$_us=new Sched_UsersSGroup; $_ui=new UserSItem;
				$ui=$_ui->getitembyid($item['user_id']);
				$ruk=$_us->GetRuk($ui);
				if(($ruk!==false)&&($ruk['id']==$_result['id'])){
					$this->Edit($id,array('is_ruk'=>1, 'user_ruk_id'=>$_result['id'],'ruk_pdate'=>time() ), true, $_result);
				}
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)){
				$setted_status_id=18;
				
				//����� ����� ��� ������ �����������!
				$this->Edit($id,array('status_id'=>$setted_status_id, 'is_ruk'=>0, 'is_dir'=>0, '	user_ruk_id'=>0, 'user_dir_id'=>0, 'ruk_pdate'=>time(),'dir_pdate'=>time()));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'����� ������� ���������',NULL,725,NULL,'���������� ������ '.$stat['name'],$item['id']);
			}
			
			 }elseif(isset($new_params['is_ruk'])&&isset($old_params['is_ruk'])){
			
				if(($new_params['is_ruk']==1)&&($old_params['is_ruk']!=1)){
					//����� ������� �� 43
					$setted_status_id=43;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'����� ������� ���������',NULL,725,NULL,'���������� ������ '.$stat['name'],$item['id']);
					
				}elseif(($new_params['is_ruk']==0)&&($old_params['is_ruk']!=0)){
					$setted_status_id=41;
					$this->Edit($id,array('status_id'=>$setted_status_id, 'is_dir'=>0,'user_dir_id'=>0, 'dir_pdate'=>time() ));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'����� ������� ���������',NULL,725,NULL,'���������� ������ '.$stat['name'],$item['id']);
				 
				}elseif(($new_params['is_ruk']==2)&&($old_params['is_ruk']!=2)){
					//����� ������� �� 52
					$setted_status_id=52;
					$this->Edit($id,array('status_id'=>$setted_status_id, 'is_dir'=>0,'user_dir_id'=>0, 'dir_pdate'=>time() ));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'����� ������� ���������',NULL,725,NULL,'���������� ������ '.$stat['name'],$item['id']);
					
					$_messages->SendMessageNotConfirmed($id, 0);
					
				}
				  
				
			 
				
			 }elseif(isset($new_params['is_dir'])&&isset($old_params['is_dir'])){
				
				if(($new_params['is_dir']==1)&&($old_params['is_dir']!=1)){
					//����� ������� �� 2
					$setted_status_id=2;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'����� ������� ���������',NULL,725,NULL,'���������� ������ '.$stat['name'],$item['id']);
					
					//�������� ��������� �� �����������
					if($item['kind_id']!=6){
						$_messages->SendMessageOK($id);
						$_messages->SendMessageToOK($id);
						
						//echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzz'; die();
						
						
					}elseif($item['kind_id']==6){
						$_messages->SendMessageAHO($id);
						$_messages->SendMessageToAHO($id);	
					}
				
				}elseif(($new_params['is_dir']==2)&&($old_params['is_dir']!=2)){
					$_messages->SendMessageNotConfirmed($id, 1);
					
				}elseif(($new_params['is_dir']==0)&&($old_params['is_dir']!=0)){
					$setted_status_id=43;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'����� ������� ���������',NULL,725,NULL,'���������� ������ '.$stat['name'],$item['id']);
				}elseif(($new_params['is_dir']==2)&&($old_params['is_dir']!=2)){
					//����� ������� �� 1
					$setted_status_id=1;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'����� ������� ���������',NULL,725,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				}
			
		}
			 
		 
		
		//echo $setted_status_id.'<br>';
		
		  
		 
		//die();
	}
	
	
	
	//������ � ���� ��� ���
	public function DocCanConfirm($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� ��������� ���������� ����������';
			$reason.=implode(', ',$reasons);
		}else{
			 
			
			//�������� ������������
			
			
			if($item['manager_id']==0){
					$can=$can&&false;
					$reasons[]='�� ������ ���������';
				}
			
			if(($item['kind_id']==6)||($item['kind_id']==7)){
				if($item['city_id']==0){
					$can=$can&&false;
					$reasons[]='�� ������ �����';
				}
			}
			
			/*if(($item['kind_id']==7)){
				if(strlen(trim($item['exh_name']))==0){
					$can=$can&&false;
					$reasons[]='�� ������� �������� ��������';
				}
			}*/
			
			if(($item['kind_id']==4)||($item['kind_id']==5)||($item['kind_id']==6)||($item['kind_id']==7)){
				if($item['given_pdate']==0){
					$can=$can&&false;
					$reasons[]='�� ������� ���� �������� ���������';
				}
			}
			
			if(($item['kind_id']==1)||($item['kind_id']==2)){
				if($item['begin_pdate']==0){
					$can=$can&&false;
					$reasons[]='�� ������� ���� ������ �������� ���������';
				}
				if($item['end_pdate']==0){
					$can=$can&&false;
					$reasons[]='�� ������� ���� ��������� �������� ���������';
				}
				if($item['begin_pdate']>$item['end_pdate']){
					$can=$can&&false;
					$reasons[]='���� ��������� �������� ��������� ������ ���� ����� ���� ������ �������� ���������';
				}
			}
			
			
			if(($item['kind_id']==1)||($item['kind_id']==2)||($item['kind_id']==3)){
				if(($item['instead_id']==0)&&($item['wo_instead']==0))	{
					$can=$can&&false;
					$reasons[]='������� ����������, ������������ ����������� � ������ ���������, ���� �������� ������� ��� ���������� ������������';
				}
			}
			
			if(($item['kind_id']==4)||($item['kind_id']==5)){
				if(strlen(trim($item['txt']))<20){
					$can=$can&&false;
					$reasons[]='�� ������ ����������� ��������� (���. ����� 20 ��������)';
				}
				
				 
			}
			
			
			/*$_tug=new PetitionUserItem;

			$users=$_tug->GetUserByPetitionId($id);
			if($users===false){
				$can=$can&&false;
				$reasons[]='�� ������ ���������������� ������������ ��� ������������ ���������';
			}*/
			
			if(($item['kind_id']==6)||($item['kind_id']==7)){
				/*$_tug=new PetitionClientItem;

				$users=$_tug->GetClientByPetitionId($id);
				
				if($users===false){
					$can=$can&&false;
					$reasons[]='�� ������� �� ������ �������';
				}*/
				
				
				if($item['wo_sched']==0){
				
				  
				  if($item['sched_id']==0){
					  $can=$can&&false;
					  $reasons[]='�� ������� ������� �� ���������';
				  }
				
				}else{
				  if(strlen(trim($item['txt']))<20){
					  $can=$can&&false;
					  $reasons[]='�� ������ ����������� ��������� (���. ����� 20 ��������)';
				  }
				}
			}
	
			
			//�������� ��� �� ��������� ���� 8 (������ �� �������)
			if($item['kind_id']==8){
				$otp_days=$this->GetVyhdatesOtp($id);
				
				//CheckByVyhBolDates
				
				if(count($otp_days)==0){
					$can=$can&&false;
					$reasons[]='�� ������� �� ����� ���� ������� �� �������';
				}
				
				$_dates2=array();
				foreach($otp_days as $v) $_dates2[]=$v['pdate_unf'];
				
				 
				
				$lo_can=$this->CheckByVyhBolDates($_dates2,    $item['manager_id'],  $id, $rss);
				$can=$can&&$lo_can;
				if(!$lo_can){
					$reasons[]=$rss;
				}
			}
			
			
			//�������� ��� �� ��������� ���� 3 (������ �� ������ � ��������)
			if($item['kind_id']==3){
				$rab_days=$this->GetVyhdates($id);
				$otp_days=$this->GetVyhdatesOtp($id);
				
				//$can=false; var_dump($otp_days);
				
				if(count($rab_days)==0){
					$can=$can&&false;
					$reasons[]='�� ������� �� ����� ���� ������ � ��������';
				}
				
				if(count($otp_days)==0){
					$can=$can&&false;
					$reasons[]='�� ������� �� ����� ���� ������� �� ������ � ��������';
				}
				
				$_rab_days=array(); $_otp_days=array();
				foreach($rab_days as $k=>$v) if(!in_array($v['pdate'], $_rab_days)) $_rab_days[]=$v['pdate'];
				foreach($otp_days as $k=>$v) if(!in_array($v['pdate'], $_otp_days)) $_otp_days[]=$v['pdate'];
				if(count($_rab_days)!=count($_otp_days)){
					$can=$can&&false;
					$reasons[]='�� ��������� ���������� ������������ ���� � ���������� ���� �������';
				}
				
				//��������� �������� ����
				$_dates1=array();
				foreach($rab_days as $v) $_dates1[]=$v['pdate_unf'];
				$_dates2=array();
				foreach($otp_days as $v) $_dates2[]=$v['pdate_unf'];
				
				$lo_can=$this->CheckByVyhDates($_dates1, $item['sched_id'],  $item['manager_id'], $id, $rss);
				$can=$can&&$lo_can;
				if(!$lo_can){
					$reasons[]=$rss;
				}
				
				$lo_can=$this->CheckByVyhOtpDates($_dates2,  $item['sched_id'],  $item['manager_id'],  $id, $rss);
				$can=$can&&$lo_can;
				if(!$lo_can){
					$reasons[]=$rss;
				}
			}
			
			
			//�������� ��� �� ��������� ���� 6 (������� ���-��)
			if($item['kind_id']==6){
				$lo_can=$this->CheckMissionDate($item['given_pdate'], $item['sched_id'], $item['manager_id'], $id, $rss);
				$can=$can&&$lo_can;
				if(!$lo_can){
					$reasons[]=$rss;
				}
				
			}
			 
			 
			 
			 $reason.=implode(', ',$reasons);
			
			
		}
		
		return $can;
	}
	
	
	
	//������ � ���� ������ ��� ���
	public function DocCanUnconfirm($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=1){
			
			$can=$can&&false;
			$reasons[]='� ��������� �� ���������� ����������';
			$reason.=implode(', ',$reasons);
		}else{
		
		  
		   //�������� ��������� ������� 
		   /* $reasons=array();
		  if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]=' ���� ����� ����������� '.$rss23;	
		  }
		   $reason.=implode(', ',$reasons);
		*/
		  	
		  
		}
		
		return $can;
	}
	
	
	//������ � �����������  ��� ��� � ����������� �������, ������ ������ 
	public function DocCanConfirmRuk($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_ruk']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='��������� ���������� ������������� ������';
			$reason.=implode(', ',$reasons);
		}else{
			//�������� ��������� ������� 
		   /* $reasons=array();
			if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
				$can=$can&&false;
				$reasons[]='���� ����� ����������� '.$rss23;	
			}
			 $reason.=implode(', ',$reasons);
			 */
			
			/*//�������� �� ����� ������� 
			$can=$can&&$this->CanConfirmByPositions($id,$rss,$item);
			if(strlen($rss)>0){
				if(strlen($reason)>0){
					$reason.=', ';
				}
				$reason.=$rss;
			} */
		}
		
		return $can;
	}
	
	
	//������ � ����������� ������ ��� ��� � ����������� �������, ������ ������ 
	public function DocCanUnconfirmRuk($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_ruk']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='��������� �� ���������� ������������� ������';
			$reason.=implode(', ',$reasons);
		}else{
			
			if($item['is_dir']!=0){
				$can=$can&&false;
				//$dsi=$_dsi->GetItemById($item['status_id']);
				$reasons[]='���������  ���������� ����������� ����������';
				$reason.=implode(', ',$reasons);	
			}
		
		 
		  
		 
		  
		}
		
		return $can;
	}
	
	
	//������ � �����������  ��� ��� � ����������� �������, ������ ������ 
	public function DocCanConfirmDir($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_dir']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='��������� ���������� ����������� ����������';
			$reason.=implode(', ',$reasons);
		}else{
			
			
			if($item['is_ruk']==0){
			
				$can=$can&&false;
				//$dsi=$_dsi->GetItemById($item['status_id']);
				$reasons[]='��������� �� ���������� ������������� ������';
				$reason.=implode(', ',$reasons);
			}
		}
		
		return $can;
	}
	
	
	//������ � ����������� ������ ��� ��� � ����������� �������, ������ ������ 
	public function DocCanUnconfirmDir($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_dir']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='��������� �� ���������� ����������� ����������';
			$reason.=implode(', ',$reasons);
		}else{
		 
		 
		  
		 
		  
		}
		
		return $can;
	}
	
	
	//������ � ��������� �� ���������...
	public function AddClients($id, $positions){
		$_kpi=new PetitionClientItem;
		
		$log_entries=array();	
		
		$old_positions=array();
		$old_positions=$this->GetClients($id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array(
			'petition_id'=>$v['petition_id'],
			'name'=>$v['name'], 
			'purpose_id'=>$v['purpose_id'],
			'purpose_txt'=>$v['purpose_txt']
			
			));
			
		
			
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['petition_id']=$v['petition_id'];
				
				$add_array['name']=$v['name'];
				$add_array['purpose_id']=$v['purpose_id'];
				$add_array['purpose_txt']=$v['purpose_txt'];
				 
				
				 
				$_kpi->Add($add_array);
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
				 
					'petition_id'=>$v['petition_id'],
					'name'=>$v['name'],
					'purpose_id'=>$v['purpose_id'],
					'purpose_txt'=>$v['purpose_txt']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['petition_id']=$v['petition_id'];
				
								$add_array['name']=$v['name'];
				$add_array['purpose_id']=$v['purpose_id'];
				$add_array['purpose_txt']=$v['purpose_txt'];
				 
				 
				
				
				
				 
				$_kpi->Edit($kpi['id'],$add_array);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//���� ���� ���������
				
				//��� ����������? ���������� ����, �������
				
				$to_log=false;
				if($kpi['name']!=$add_array['name']) $to_log=$to_log||true;
				if($kpi['purpose_id']!=$add_array['purpose_id']) $to_log=$to_log||true;
				if($kpi['purpose_txt']!=$add_array['purpose_txt']) $to_log=$to_log||true;
				 
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					  'petition_id'=>$v['petition_id'],
					'name'=>$v['name'],
					'purpose_id'=>$v['purpose_id'],
					'purpose_txt'=>$v['purpose_txt']
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
				if(($vv['petition_id']==$v['petition_id'])
				&&($vv['name']==$v['name'])
				&&((int)$vv['purpose_id']==(int)$v['purpose_id'])
				&&((int)$vv['purpose_txt']==(int)$v['purpose_txt'])
				 
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
					'name'=>$v['name'],
					'purpose_id'=>$v['purpose_id'],
					'purpose_txt'=>$v['purpose_txt'] 
			);
			
			//������� �������
			$_kpi->Del($v['id']);
		}
		
		
		return $log_entries;
	}
	
	
	
	public function GetVyhdates($id){
		$_pvd=new PetitionVyhDateGroup;
		
		return $_pvd->GetItemsArrById($id);	
		
	}
	
	
	
	
	
	//������ � ������ ������ � ��������...
	public function AddVyhDates($id, $positions){
		$_kpi=new PetitionVyhDateItem;
		
		$log_entries=array();	
		
		$old_positions=array();
		$old_positions=$this->GetVyhdates($id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array(
			'petition_id'=>$v['petition_id'],
			'pdate'=>$v['pdate'], 
			'time_from_h'=>$v['time_from_h'],
			'time_from_m'=>$v['time_from_m'],
			'time_to_h'=>$v['time_to_h'],
			'time_to_m'=>$v['time_to_m'] 
			
			));
			
			//$f['hash']=md5($f['pl_position_id'].'_'.$f['position_id'].'_'.$f['pl_discount_id'].'_'.$f['pl_discount_value'].'_'.$f['pl_discount_rub_or_percent']);
			
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['petition_id']=$v['petition_id'];
				
				$add_array['pdate']=$v['pdate'];
				$add_array['time_from_h']=$v['time_from_h'];
				$add_array['time_from_m']=$v['time_from_m'];
				$add_array['time_to_h']=$v['time_to_h'];
				
				
				$add_array['time_to_m']=$v['time_to_m'];
				 
				
				
				 
				$_kpi->Add($add_array);
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
				 
					'petition_id'=>$v['petition_id'],
					'pdate'=>$v['pdate'],
					'time_from_h'=>$v['time_from_h'],
					'time_from_m'=>$v['time_from_m'],
					'time_to_h'=>$v['time_to_h'],
					'time_to_m'=>$v['time_to_m'] 
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['petition_id']=$v['petition_id'];
				
				$add_array['pdate']=$v['pdate'];
				$add_array['time_from_h']=$v['time_from_h'];
				$add_array['time_from_m']=$v['time_from_m'];
				$add_array['time_to_h']=$v['time_to_h'];
				
				
				$add_array['time_to_m']=$v['time_to_m'];
				 
				
				
				
				
				$add_pms=$v['pms'];
				$_kpi->Edit($kpi['id'],$add_array);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//���� ���� ���������
				
				//��� ����������? ���������� ����, �������
				
				$to_log=false;
				if($kpi['pdate']!=$add_array['pdate']) $to_log=$to_log||true;
				if($kpi['time_from_h']!=$add_array['time_from_h']) $to_log=$to_log||true;
				if($kpi['time_from_m']!=$add_array['time_from_m']) $to_log=$to_log||true;
				if($kpi['time_to_h']!=$add_array['time_to_h']) $to_log=$to_log||true;
				if($kpi['time_to_m']!=$add_array['time_to_m']) $to_log=$to_log||true;
				
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					  'petition_id'=>$v['petition_id'],
					'pdate'=>$v['pdate'],
					'time_from_h'=>$v['time_from_h'],
					'time_from_m'=>$v['time_from_m'],
					'time_to_h'=>$v['time_to_h'],
					'time_to_m'=>$v['time_to_m'] 
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
				if(($vv['petition_id']==$v['petition_id'])
				&&($vv['pdate']==$v['pdate_unf'])
				&&((int)$vv['time_from_h']==(int)$v['time_from_h'])
				&&((int)$vv['time_from_m']==(int)$v['time_from_m'])
				&&((int)$vv['time_to_h']==(int)$v['time_to_h'])
				&&((int)$vv['time_to_m']==(int)$v['time_to_m'])
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
					'pdate'=>$v['pdate_unf'],
					'time_from_h'=>$v['time_from_h'],
					'time_from_m'=>$v['time_from_m'],
					'time_to_h'=>$v['time_to_h'],
					'time_to_m'=>$v['time_to_m'] 
			);

			
			//������� �������
			$_kpi->Del($v['id']);
		}
		
		
		return $log_entries;
	}
	
	
	
	public function GetClients($id){
		$_pcd=new PetitionClientGroup;
		
		return $_pcd->GetItemsByIdArr($id);
	}
	
	
	
	
	
	
	
	public function GetVyhdatesOtp($id){
		$_pvd=new PetitionVyhDateOtpGroup;
		
		return $_pvd->GetItemsArrById($id);	
		
	}
	
	
	
	
	
	//������ � ������ ������ � ��������...
	public function AddVyhDatesOtp($id, $positions){
		$_kpi=new PetitionVyhDateOtpItem;
		
		$log_entries=array();	
		
		$old_positions=array();
		$old_positions=$this->GetVyhdatesOtp($id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array(
			'petition_id'=>$v['petition_id'],
			'pdate'=>$v['pdate'] 
			
			));
			
			//$f['hash']=md5($f['pl_position_id'].'_'.$f['position_id'].'_'.$f['pl_discount_id'].'_'.$f['pl_discount_value'].'_'.$f['pl_discount_rub_or_percent']);
			
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['petition_id']=$v['petition_id'];
				
				$add_array['pdate']=$v['pdate'];
				 
				
				
				 
				$_kpi->Add($add_array);
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
				 
					'petition_id'=>$v['petition_id'],
					'pdate'=>$v['pdate']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['petition_id']=$v['petition_id'];
				
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
					  'petition_id'=>$v['petition_id'],
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
				if(($vv['petition_id']==$v['petition_id'])
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
	
	
	
	//�������� ��������� ���� �� ������������
	public function GetDatesByMission($mission_id, $except_id=0, $user_id=0){
		
		$dates=array();
		$_dem=new Sched_AbstractItem;
		$mission=$_dem->getitembyid($mission_id);
		
		$_hd=new HolyDates;
		
		$pdate=datefromdmy(datefromymd($mission['pdate_beg']));
		$begin_pdate=$pdate;
		$end_pdate=datefromdmy(datefromymd($mission['pdate_end']));
		
		while($pdate<=$end_pdate){
			if($_hd->IsHolyday($pdate)){
				
				//���������, �� ������ �� ��� ���� � ������ ����������, ���:
				//kind_id==3 is_dir==1 user_id==$mission['manager_id']
				
				$sql_c='select count(*) from petition_vyh_date where  pdate="'.$pdate.'" and petition_id in(select id from petition where id<>"'.$except_id.'" and kind_id=3 and is_dir=1 and manager_id="'.$user_id.'")';
				
				//echo $sql_c.'<br>';
				
				$set=new mysqlset($sql_c);
				$rs=$set->GetResult();
				$f=mysqli_fetch_array($rs);
				if((int)$f[0]==0) $dates[]=array(
					'id'=>md5(time().$pdate),
					'pdate'=>date('d.m.Y', $pdate),
					'w_day'=>PetitionVyhDateGroup::$weekdays[(int)date('w', $pdate)],
					'time_from_h'=>'09',
					'time_from_m'=>'00',
					'time_to_h'=>'18',
					'time_to_m'=>'00'
				);	
			}
			
			$pdate+=24*60*60;	
		}
			
		return $dates;
	}
	
	
	//��������, �������� �� ������ �� ��������� ���!
	public function CheckByVyhDates($dates, $sched_id=0, $user_id=0, $except_id=0, &$rss){
		$res=true; $rss=''; $reasons=array();
		$_dem=new Sched_AbstractItem;
		$mission=$_dem->getitembyid($sched_id);
		
		$_hd=new HolyDates;
		
		foreach($dates as $k=>$pdate){
			//1. �������� �� ��������
			//2. �� ������ �� � ������ ����������, ��� 	kind_id==3 is_dir==1 user_id==$mission['manager_id']
			//3. ������ �� � ������ ������������
			if(!$_hd->IsHolyday($pdate)){
				$res=$res&&false;
				$reasons[]="���� ".date('d.m.Y', $pdate)." �� �������� �������� ����";					
			}
			
			
			$sql_c='select count(*) from petition_vyh_date where  pdate="'.$pdate.'" and petition_id in(select id from petition where id<>"'.$except_id.'" and kind_id=3 and is_dir=1 and manager_id="'.$user_id.'")';
				
			//echo $sql_c.'<br>';
			
			$set=new mysqlset($sql_c);
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			
			if((int)$f[0]>0) {
				$res=$res&&false;
				$reasons[]="���� ".date('d.m.Y', $pdate)." ������������ � ������ ���������� ����������";	
			}
						
			if($mission!==false){
				$begin_pdate=datefromdmy(datefromymd($mission['pdate_beg']));
		 
				$end_pdate=datefromdmy(datefromymd($mission['pdate_end']));	
				
				if(!(($pdate>=$begin_pdate)&& ($pdate<=$end_pdate)  )){
					$res=$res&&false;
					$reasons[]="���� ".date('d.m.Y', $pdate)." �� ������ � ������ ��������� ������������ � ".date('d.m.Y', $begin_pdate)." �� ".date('d.m.Y', $end_pdate)."";
				}
			}
		}
		
		if(!$res){
			$rss=implode("; \n", $reasons);
		}
		
		return $res;	
	}
	
	//��������, �������� �� ������ �� ��������� ��� �������!
	public function CheckByVyhOtpDates($dates, $sched_id=0, $user_id=0, $except_id=0, &$rss){
		$res=true; $rss=''; $reasons=array();
		$_dem=new Sched_AbstractItem;
		$mission=$_dem->getitembyid($sched_id);
		
		$_hd=new HolyDates;
		
		foreach($dates as $k=>$pdate){
			//1. �� �������� �� ��������
			//2. �� ������ �� � ������ ����������, ��� 	kind_id==3 is_dir==1 manager_id==$mission['manager_id']
			//3. �� ������ �� � ������ ����������, ��� 	kind_id==1,2 is_dir==1 manager_id==$mission['manager_id']
			//4. �� ������ �� � ������ ������������
			
			//1.
			if($_hd->IsHolyday($pdate)){
				$res=$res&&false;
				$reasons[]="���� ".date('d.m.Y', $pdate)." �������� �������� ����";					
			}
			
			//2
			$sql_c='select count(*) from petition_vyh_date_otp where  pdate="'.$pdate.'" and petition_id in(select id from petition where id<>"'.$except_id.'" and kind_id=3 and is_dir=1 and manager_id="'.$user_id.'")';
				
			//echo $sql_c.'<br>';
			
			$set=new mysqlset($sql_c);
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			
			if((int)$f[0]>0) {
				$res=$res&&false;
				$reasons[]="���� ".date('d.m.Y', $pdate)." ������������ � ������ ���������� �� ������ �� ������ � �������� ����������";	
			}
			
			//3
			$sql_c='select count(*) from petition where  begin_pdate>="'.$pdate.'" and end_pdate<="'.$pdate.'"  and kind_id in(1,2) and is_dir=1 and manager_id="'.$user_id.'" and id<>"'.$except_id.'"';
				
			//echo $sql_c.'<br>';
			
			$set=new mysqlset($sql_c);
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			
			if((int)$f[0]>0) {
				$res=$res&&false;
				$reasons[]="���� ".date('d.m.Y', $pdate)." ������������ � ������ ���������� �� ������ �� ���� ����, ���� ������������ ������ ����������";	
			}
			
			
			//4			
			if($mission!==false){
				$begin_pdate=datefromdmy(datefromymd($mission['pdate_beg']));
		 
				$end_pdate=datefromdmy(datefromymd($mission['pdate_end']));	
				
				if(($pdate>=$begin_pdate)&& ($pdate<=$end_pdate)  ){
					$res=$res&&false;
					$reasons[]="���� ".date('d.m.Y', $pdate)."  ������ � ������ ��������� ������������ � ".date('d.m.Y', $begin_pdate)." �� ".date('d.m.Y', $end_pdate)."";
				}
			}
		}
		
		if(!$res){
			$rss=implode("; \n", $reasons);
		}
		
		return $res;	
	}
	
	
	
		//��������, �������� �� ������ �� ��������� ��� ������� �� �������!
	public function CheckByVyhBolDates($dates,   $user_id=0, $except_id=0, &$rss){
		$res=true; $rss=''; $reasons=array();
		$_dem=new Sched_AbstractItem;
		$mission=$_dem->getitembyid($sched_id);
		
		$_hd=new HolyDates; 
		
		foreach($dates as $k=>$pdate){
			//1. �� �������� �� ��������
			//2. �� ������ �� � ������ ����������, ��� 	kind_id==8 is_dir==1 manager_id==$mission['manager_id']
			 //3. �� ����� ���� ���� � ����: ��������� ������ ����, �������� ����. ������ ����� �� ������� ���� �� �������� ��������� �� ���� �������� ���������
			
			//1.
			if($_hd->IsHolyday($pdate)){
				$res=$res&&false;
				$reasons[]="���� ".date('d.m.Y', $pdate)." �������� �������� ����";					
			}
			
			//2
			$sql_c='select count(*) from petition_vyh_date_otp where  pdate="'.$pdate.'" and petition_id in(select id from petition where id<>"'.$except_id.'" and kind_id=8 and is_dir=1 and manager_id="'.$user_id.'")';
				
			//echo $sql_c.'<br>';
			
			$set=new mysqlset($sql_c);
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			
			if((int)$f[0]>0) {
				$res=$res&&false;
				$reasons[]="���� ".date('d.m.Y', $pdate)." ������������ � ������ ���������� � ������������ ������ �� ������� ����������";	
			}
			
			//3
			$y=date('Y', $pdate);
			$yfrom=mktime(0,0,0,1,1,$y);
			$yto=mktime(0,0,0,12,31,$y);
			
			
			 
			
			$sql_c='select count(id) from petition_vyh_date_otp where (pdate between "'.$yfrom.'" and "'.$yto.'") and petition_id in (select id from petition where  kind_id =8 and is_dir=1 and manager_id="'.$user_id.'" and id<>"'.$except_id.'" )';
				
			//echo $sql_c.'<br>';
			
			$set=new mysqlset($sql_c);
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			
			$resu=(int)$f[0];
			
			//��������� ��� ���� �������� ���� �� �������� ������!
			foreach($dates as $kk=>$vv) if(date('Y', $vv)==$y) $resu++;
			
			
			
			if($resu>3) {
				$res=$res&&false;
				$reasons[]="���� ".date('d.m.Y', $pdate).": ���������� ���� ������������� ������� �� �������� ������ �������� �� ������ ��������� ���� ���� � ���. ��� ����� ��������. ������� ������������ ������ ��� ���������";
			}
			
			
			
			
		}
		
		if(!$res){
			$rss=implode("; \n", $reasons);
		}
		
		return $res;	
	}
	
	
	
	
	//�������� ���� ������� ������������
	public function CheckMissionDate($pdate, /*$time_from_h, $time_from_m, $time_to_h, $time_to_m,*/ $sched_id=0, $user_id=0, $except_id=0, &$rss){
		$res=true; $rss=''; $reasons=array();
		$_dem=new Sched_AbstractItem;
		$mission=$_dem->getitembyid($sched_id);
		
		$_hd=new HolyDates;
		
		
		//���� ��������� � ����� �������!
		//���� �� ���������� � ������ ���������� ���� 6  is_dir==1 user_id==$mission['manager_id']
		/*
		if($pdate!=datefromdmy(datefromymd($mission['pdate_beg']))){
			$res=$res&&false;
			$reasons[]="���� �������� ��������� ".date('d.m.Y', $pdate)." �� ��������� � ����� ������� ".datefromymd($mission['pdate_beg']);		
		}
		*/
		
		$sql_c='select count(*) from petition where id<>"'.$except_id.'" and kind_id=6 and is_dir=1 and manager_id="'.$user_id.'" and given_pdate="'.$pdate.'"';
		
		//echo $sql_c;
		
		$set=new mysqlset($sql_c);
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			
			if((int)$f[0]>0) {
				$res=$res&&false;
				
				//�������� �������� ���� ���������
				$sql_l='select * from petition where id<>"'.$except_id.'" and kind_id=6 and is_dir=1 and manager_id="'.$user_id.'" and given_pdate="'.$pdate.'"';
				
				$set=new mysqlset($sql_l);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				$_docs=array();
				for($j=0; $j<$rc; $j++){
					$g=mysqli_fetch_array($rs);
					$_docs[]=$this->ConstructName($g['id']);
				}
				
				
				$reasons[]="���� ".date('d.m.Y', $pdate)." ������������ � ������ ���������� �� ������� ����������� ����������: ".implode(', ', $_docs);	
			}
			
		 
			
		
		if(!$res){
			$rss=implode("; \n", $reasons);
		}
		
		return $res;	
	}
	
	
	
}


/**************************************************************************************************/
//����� ���������
class Petition_Messages{
 
	//�������� ��������� ���������� ��� ����������� ����� ���. ������ � �������� ��������� � ��
	public function SendMessageOK($id){
		$_dem=new PetitionItem;
		$_doc=$_dem->GetItemById($id);
		 
		
		$doc=$_dem->GetItemById($id); 
		$_mi=new MessageItem;	
		
		$_ui=new UserSItem;
		$signer=$_ui->GetItemById($doc['manager_id']);
		
		$topic="���� ��������� ����������� � ���������� � ����� ���������";
		
		$txt='<div>';
		$txt.='<em>������ ��������� ������������� �������������.</em>';
		$txt.=' </div>';
		
		
		$txt.='<div>&nbsp;</div>';
		
		$txt.='<div>';
		$txt.='���������(��) '.$signer['name_s'].'!';
		$txt.='</div>';
		$txt.='<div>&nbsp;</div>';
		
		
		$txt.='<div>';
	 	$txt.='<strong>��������� ���� ��������� ���� ����������� � ���������� � ����� ���������:</strong>';
		$txt.='</div><ul>';
		
		$txt.='<li><a href="petition_my_history.php?action=1&id='.$id.'&from_begin=1" target="_blank">'.$_dem->ConstructName($id,  $doc).'</a></li>';
  
		$txt.='</ul><div></div>';
		
		
		$txt.='<div>&nbsp;</div>';
	
		$txt.='<div>';
		$txt.='C ���������, ��������� "'.SITETITLE.'".';
		$txt.='</div>';
		
		$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$signer['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
		
	}
	
	
	
	//�������� ��������� ���������� ��� �� ����������� ��� ����������
	public function SendMessageNotConfirmed($id, $ruk_or_dir=0){
		$_dem=new PetitionItem;
		$_doc=$_dem->GetItemById($id);
		 
		
		$doc=$_dem->GetItemById($id); 
		$_mi=new MessageItem;	
		
		$_ui=new UserSItem;
		$signer=$_ui->GetItemById($doc['manager_id']);
		
		if($ruk_or_dir==0) $topic="���� ��������� �� ���������� ������������� ������";
		else $topic="���� ��������� �� ���������� ����������� ����������";
		
		$txt='<div>';
		$txt.='<em>������ ��������� ������������� �������������.</em>';
		$txt.=' </div>';
		
		
		$txt.='<div>&nbsp;</div>';
		
		$txt.='<div>';
		$txt.='���������(��) '.$signer['name_s'].'!';
		$txt.='</div>';
		$txt.='<div>&nbsp;</div>';
		
		
		$txt.='<div>';
	 	if($ruk_or_dir==0)  $txt.='<strong>��������� ���� ��������� �� ���� ���������� ������������� ������:</strong>';
		else $txt.='<strong>��������� ���� ��������� �� ���� ���������� ����������� ����������:</strong>';
		$txt.='</div><ul>';
		
		$txt.='<li><a href="petition_my_history.php?action=1&id='.$id.'&from_begin=1" target="_blank">'.$_dem->ConstructName($id,  $doc).'</a></li>';
  
		$txt.='</ul><div></div>';
		
		$txt.='<div>&nbsp;</div>';
	
		
		$txt.='<div>����������, ��������� ��������� � ��������� �������� �� ��������� ������������.</div>';
		$txt.='<div>&nbsp;</div>';
	
		$txt.='<div>';
		$txt.='C ���������, ��������� "'.SITETITLE.'".';
		$txt.='</div>';
		
		$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$signer['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
		
	}
	
	
	
	//�������� ��������� ���������� �� ��� ����������� ����� ���. ������ � �������� ��������� � ��
	public function SendMessageToOK($id){
		$_dem=new PetitionItem;
		$_doc=$_dem->GetItemById($id);
		 
		
		$doc=$_dem->GetItemById($id); 
		$_mi=new MessageItem;	
		
		$_ui=new UserSItem;
		
		$ug=new UsersSGroup;
		
		$dec=new DBDecorator();
		//.dep.name
		$dec ->AddEntry(new SqlEntry('dep.name','����� ���������', SqlEntry::LIKE));
		$dec ->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
		
		$users=$ug->GetItemsByDecArr($dec);
		
		
		
		foreach($users as $k=>$user){
		
			$signer=$_ui->GetItemById($user['id']);
			
			
			
			
			$topic="����� ���������";
			
			$txt='<div>';
			$txt.='<em>������ ��������� ������������� �������������.</em>';
			$txt.=' </div>';
			
			
			$txt.='<div>&nbsp;</div>';
			
			$txt.='<div>';
			$txt.='���������(��) '.$signer['name_s'].'!';
			$txt.='</div>';
			$txt.='<div>&nbsp;</div>';
			
			
			$txt.='<div>';
			$txt.='<strong>� ��� ����� ��������� ��������� ������������� ���������:</strong>';
			$txt.='</div><ul>';
			
			$txt.='<li><a href="petition_my_history.php?action=1&id='.$id.'&from_begin=1" target="_blank">'.$_dem->ConstructName($id,  $doc).'</a></li>';
	  
			$txt.='</ul><div></div>';
			
			$txt.='<div>';
			$txt.='<strong>�� ������ ��������� ��� ����������� ���������:</strong>';
			$txt.='</div><ul>';
			
			$txt.='<li><a href="petition_my_history.php?action=1&id='.$id.'&from_begin=1&force_print=1" target="_blank">��������� ��� �������� ���������</a></li>';
	  
			$txt.='</ul><div></div>';
			
			
			
			
			$txt.='<div>&nbsp;</div>';
		
			$txt.='<div>';
			$txt.='C ���������, ��������� "'.SITETITLE.'".';
			$txt.='</div>';
			
			$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$signer['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
		}
	}
	
	
	
	
	//�������� ��������� ���������� ��� ����������� ����� ���. ������ � �������� ��������� � ���
	public function SendMessageAHO($id){
		$_dem=new PetitionItem;
		$_doc=$_dem->GetItemById($id);
		 
		
		$doc=$_dem->GetItemById($id); 
		$_mi=new MessageItem;	
		
		$_ui=new UserSItem;
		$signer=$_ui->GetItemById($doc['manager_id']);
		
		$topic="���� ��������� ����������� � ���������� � ����� ���";
		
		$txt='<div>';
		$txt.='<em>������ ��������� ������������� �������������.</em>';
		$txt.=' </div>';
		
		
		$txt.='<div>&nbsp;</div>';
		
		$txt.='<div>';
		$txt.='���������(��) '.$signer['name_s'].'!';
		$txt.='</div>';
		$txt.='<div>&nbsp;</div>';
		
		
		$txt.='<div>';
	 	$txt.='<strong>��������� ���� ��������� ���� ����������� � ���������� � ����� ���:</strong>';
		$txt.='</div><ul>';
		
		$txt.='<li><a href="petition_my_history.php?action=1&id='.$id.'&from_begin=1" target="_blank">'.$_dem->ConstructName($id,  $doc).'</a></li>';
  
		$txt.='</ul><div></div>';
		
		
		$txt.='<div>&nbsp;</div>';
	
		$txt.='<div>';
		$txt.='C ���������, ��������� "'.SITETITLE.'".';
		$txt.='</div>';
		
		$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$signer['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
		
	}
	
	
	//�������� ��������� ���������� ��� ��� ����������� ����� ���. ������ � �������� ��������� � ���
	public function SendMessageToAHO($id){
		$_dem=new PetitionItem;
		$_doc=$_dem->GetItemById($id);
		 
		
		$doc=$_dem->GetItemById($id); 
		$_mi=new MessageItem;	
		
		$_ui=new UserSItem;
		
		$ug=new UsersSGroup;
		
		$dec=new DBDecorator();
		//.dep.name
		$dec ->AddEntry(new SqlEntry('dep.name','���', SqlEntry::LIKE));
		$dec ->AddEntry(new SqlEntry('u.is_active',1, SqlEntry::E));
		
		$users=$ug->GetItemsByDecArr($dec);
		
		
		
		foreach($users as $k=>$user){
		
			$signer=$_ui->GetItemById($user['id']);
			
			
			
			
			$topic="����� ���������";
			
			$txt='<div>';
			$txt.='<em>������ ��������� ������������� �������������.</em>';
			$txt.=' </div>';
			
			
			$txt.='<div>&nbsp;</div>';
			
			$txt.='<div>';
			$txt.='���������(��) '.$signer['name_s'].'!';
			$txt.='</div>';
			$txt.='<div>&nbsp;</div>';
			
			
			$txt.='<div>';
			$txt.='<strong>� ��� ����� ��������� ��������� ������������� ���������:</strong>';
			$txt.='</div><ul>';
			
			$txt.='<li><a href="petition_my_history.php?action=1&id='.$id.'&from_begin=1" target="_blank">'.$_dem->ConstructName($id,  $doc).'</a></li>';
	  
			$txt.='</ul><div></div>';
			
			
			$txt.='<div>&nbsp;</div>';
		
			$txt.='<div>';
			$txt.='C ���������, ��������� "'.SITETITLE.'".';
			$txt.='</div>';
			
			$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$signer['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
		}
	}
	
	
	
	
}


//������� ������� �����
class PetitionEarlyReasonItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='petition_early_reasons';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}

//������ ������  ������� �����
class PetitionEarlyReasonGroup extends AbstractGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='petition_early_reasons';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//������ �������
	public function GetItemsArr($current_id=0,  $is_shown=0){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		if($is_shown==0) $set=new MysqlSet('select * from '.$this->tablename.' order by name asc, id asc');
		else $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->vis_name.'="1" order by name asc, id asc');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$f=array();
		$f['id']=0;
		$f['name']='-��������-';
		$f['is_current']=(bool)($current_id==0);
		
		$arr[]=$f;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
}



?>