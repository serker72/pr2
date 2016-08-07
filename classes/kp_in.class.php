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

require_once('lead_history_group.php');
require_once('user_s_item.php');
require_once('discr_man.php');

require_once('pl_curritem.php');
require_once('lead_view_item.php');
require_once('tender.class.php');
require_once('lead_field_rules.php');
require_once('pl_prodgroup.php');
require_once('kp_in_view.class.php');
require_once('kp_out_view.class.php');

require_once('kp_in_field_rules.php');

require_once('abstract_working_kind_group.php');

require_once('lead_history_item.php');
require_once('lead.class.php');
require_once('tz.class.php');


require_once('kp_supply_pdate_group.php');
require_once('kp_supply_pdate_item.php');


//библиотека классов КП вх


//абстрактная запись КП вх
class KpIn_AbstractItem extends AbstractItem{
	public $kind_id=1;
	protected function init(){
		$this->tablename='kp_in';
		$this->item=NULL;
		$this->pagename='ed_kp_in.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}	
	
	
	public function Add($params){
		/**/
		$digits=5;
		
		switch($params['kind_id']){
			case 0:
				$begin='КПВ';
			break;
			case 1:
				$begin='КПИ';
			break;
			 
			default:
				$begin='КПВ';
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
	
	
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		 
		return $can;
	}
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanRestore($id,&$reason,$item=NULL){
		$can=true;	
		 
		return $can;
	}
	
	
	
	//Запрос о возм снятия утв цен
	public function DocCanUnconfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		return $can;
	}
	
	//запрос о возм утв цен
	public function DocCanConfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		return $can;
	}
	
	//запрос о возможности  утв отгр и возвращение причины, почему нельзя 
	public function DocCanConfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		
		return $can;
	}
	
	
	//запрос о возможности снятия утв отгр и возвращение причины, почему нельзя 
	public function DocCanUnconfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		
		return $can;
	}
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		
		 
		
		AbstractItem::Edit($id, $params);
		
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	
	//проверка и автосмена статуса 
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		 
	}
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		return 'КП входящее, статус '.$stat['name'];
	}
	
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		return 'КП входящее '.$item['code'].', статус '.$stat['name'];
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
	
	//получить адресата или их список
	public function ConstructContacts($id, $item=NULL){
		
	}
	
	//статусы, доступные к переходу
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
	
	 
	
}


/*************************************************************************************************/
//КП вх
class KpIn_Item extends KpIn_AbstractItem{
	public $kind_id=0;
	
 
	
	public function NewTenderMessage($code){
		 
	}
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		$log=new ActionLog;
		
		 
		
		AbstractItem::Edit($id, $params);
		
		//if(isset($params['manager_id'])&&($params['manager_id']!=0)) $this->ScanResp($id, $params['manager_id'], $_result['id']);
		
		//фиксация даты смены статуса
		/*if(isset($params['status_id'])&&isset($item['status_id'])&&($params['status_id']!=$item['status_id'])){
			 
			
			if(($params['status_id']==2)&&($item['status_id']!=2)){
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'in_or_out'=>0, 'pdate'=>time()));
					
			} 
				
		}
		
		if(isset($params['was_sent'])&&isset($item['was_sent'])&&($params['was_sent']!=$item['was_sent'])){
			if(($params['was_sent']==1)&&($item['was_sent']!=1)){
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'in_or_out'=>1, 'pdate'=>time()));
			}
		}*/
		
		
		if(isset($params['is_confirmed'])&&isset($item['is_confirmed'])&&($params['is_confirmed']!=$item['is_confirmed'])){
			//echo 'zzzzzzzzzzzzz'; die();	 
			
			if(($params['is_confirmed']==1)&&($item['is_confirmed']!=1)){
				
				//остановить счетички 3 в тз, лиде
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['tz_id'], 'kind_id'=>3, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>3, 'in_or_out'=>1, 'pdate'=>time()));
				
				//запустить счетик 1,2 в ТЗ, лиде, КП
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>1, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['tz_id'], 'kind_id'=>1, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>1, 'in_or_out'=>0, 'pdate'=>time()));
				
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>2, 'in_or_out'=>0, 'pdate'=>time()));
				 
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>2, 'in_or_out'=>0, 'pdate'=>time()));
				
				
				
				//автоматическое примечание об утв. вх КП во входящее КП и в ТЗ
				$_nkp=new KpInNotesItem; $_ntz=new TzNotesItem;
				$comment=SecStr('Автоматическое примечание: утверждено заполнение КП вх. №'.$item['code'].' сотрудником '.$_result['name_s']);
				
				$_nkp->Add(array(
					'note'=>$comment,
					'pdate'=>time(),
					'user_id'=>$id,
					'posted_user_id'=>0
				));
				
				$_ntz->Add(array(
					'note'=>$comment,
					'pdate'=>time(),
					'user_id'=>$item['tz_id'],
					'posted_user_id'=>0
				));
				
				
					
			}elseif(($params['is_confirmed']==0)&&($item['is_confirmed']!=0)){
				//запустить счетички 3 в тз, лиде
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['tz_id'], 'kind_id'=>3, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>3, 'in_or_out'=>0, 'pdate'=>time()));
				
				//снять счетик 1,2 в ТЗ, лиде, КП
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['tz_id'], 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
				
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>2, 'in_or_out'=>1, 'pdate'=>time()));
			 
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>2, 'in_or_out'=>1, 'pdate'=>time()));
			}
			
			
				
		}
		
		if(isset($params['fulful_kp'])&&isset($item['fulful_kp'])&&($params['fulful_kp']!=$item['fulful_kp'])){
			//echo 'zzzzzzzzzzzzz'; die();	 
			
			//установка галочки
			if(($params['fulful_kp']!=0)&&($item['fulful_kp']==0)){
				//остановка счетчика 1 в ТЗ, лиде КП
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['tz_id'], 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
				 
			//снятие галочки	
			}elseif(($params['fulful_kp']==0)&&($item['fulful_kp']!=0)){
				//запуск счетчика 1 в ТЗ, лиде КП
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>1, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['tz_id'], 'kind_id'=>1, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>1, 'in_or_out'=>0, 'pdate'=>time()));
				//снятие счетчика менеджера в тз, лиде
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));	
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['tz_id'], 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
			}
		}
		
		
		if(isset($params['fulful_kp1'])&&isset($item['fulful_kp1'])&&($params['fulful_kp1']!=$item['fulful_kp1'])){
			//echo 'zzzzzzzzzzzzz'; die();	 
			
			//установка галочки
			if(($params['fulful_kp1']!=0)&&($item['fulful_kp1']==0)){
				//остановка счетчика 2   лиде КП
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>2, 'in_or_out'=>1, 'pdate'=>time()));
				 
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>2, 'in_or_out'=>1, 'pdate'=>time()));
				 
			//снятие галочки	
			}elseif(($params['fulful_kp1']==0)&&($item['fulful_kp1']!=0)){
				//запуск счетчика 2 в  лиде КП
				 $_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>2, 'in_or_out'=>0, 'pdate'=>time()));
				 
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>2, 'in_or_out'=>0, 'pdate'=>time()));
				
				//снятие счетчика менеджера в тз, лиде
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));	
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['tz_id'], 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
			}
		}
		
		//полная простановка 2 галочек, раньше их не было - запуск счетчика менеджера
		if( 
		(isset($params['fulful_kp1'])||isset($params['fulful_kp']))
		&&(($params['fulful_kp1']!=$item['fulful_kp1'])||
		($params['fulful_kp']!=$item['fulful_kp']))
		
		
		){
			$new_item=$this->GetItemById($id);
			
			if(($new_item['fulful_kp1']!=0)&&($new_item['fulful_kp']!=0)
			&&(
				($new_item['fulful_kp1']==2)||($new_item['fulful_kp']==2)
			)
			){
				//хотя бы одно несоответствие
				//запуск счетчика менеджера в ТЗ, ЛИДЕ, КПВ 0
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>0, 'in_or_out'=>0, 'pdate'=>time()));	
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['tz_id'], 'kind_id'=>0, 'in_or_out'=>0, 'pdate'=>time()));
				
				
				//
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['id'], 'kind_id'=>0, 'in_or_out'=>0, 'pdate'=>time()));
			}
			elseif(($new_item['fulful_kp1']!=0)&&($new_item['fulful_kp']!=0)
			&&(
				($new_item['fulful_kp1']==1)||($new_item['fulful_kp']==1)
			)
			){
				//полное соответствие - счетчик менеджера не запускаем
				//автоматически  создаем, утверждаем КПисх.
				//если оно не было создано ранее!!!!
				$sql1='select count(*) from kp_in where status_id not in(3,27) and is_confirmed<>0 and kp_in_id="'.$id.'"';
				$set=new mysqlset($sql1);
				$rs1=$set->GetResult();
				$g=mysqli_fetch_array($rs1);
				if((int)$g[0]==0){
				
				
					$_kp_out=new KpOut_Item;
					$a_params=array();
					$a_params['kind_id']=1;
					
					$a_params['lead_id']=$new_item['lead_id'];
					$a_params['tz_id']=$new_item['tz_id'];
					$a_params['kp_in_id']=$id;
					$a_params['created_id']=$new_item['manager_id'];
					$a_params['manager_id']=$new_item['manager_id'];
					$a_params['pdate']=time();
					$a_params['given_pdate']=time();
					
					$kp_out_id=$_kp_out->Add($a_params);
					
					$log->PutEntry(0,'автоматическое создание КП исх. при проверке КП вх. техническим отделом и отделом закупок',NULL,1019,NULL,$kp_out_id, $kp_out_id);
					
					//автоматически утвердим
					$a_params=array();
					$a_params['is_confirmed']=1;
					$a_params['confirm_pdate']=time();
					$a_params['user_confirm_id']=0;
					
					$_kp_out->Edit($kp_out_id, $a_params, true, $_result);
					
					//внести комментарий
					$_kni=new KpInNotesItem;
					$comm=array(
						'note'=>SecStr('Автоматическое примечание: автоматическое создание КП исх. при проверке КП вх. техническим отделом и отделом закупок.'),
						'pdate'=>time(),
						'user_id'=>$kp_out_id,
						'posted_user_id'=>0
					);
					$_kni->Add($comm);
					
				}
				
				
			}
		}
		
		
		if(isset($params['status_id'])&&isset($item['status_id'])&&($params['status_id']!=$item['fulful_kp1'])&&($params['status_id']==34)){
		 
			// обработать перевод в статус Отказ
		 	//запустить счетички 3 в тз, лиде
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['tz_id'], 'kind_id'=>3, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>3, 'in_or_out'=>0, 'pdate'=>time()));
				
				//снять счетик 1,2, 4 в ТЗ, лиде, КП
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['tz_id'], 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));
				
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
				
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>2, 'in_or_out'=>1, 'pdate'=>time()));
			 
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>2, 'in_or_out'=>1, 'pdate'=>time()));
		}
		
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	//автоматическое внесение отв сотр в карту контрагента, если его там нет
	protected function ScanResp($tender_id, $manager_id, $result_id){
		//$_si=new SupplierItem;
		$_resp=new SupplierResponsibleUserGroup;
		$_respi=new SupplierResponsibleUserItem;
		$_sup=new Lead_SupplierItem;
		$_si=new SupplierItem;
		$_ui=new UserSItem;
		$log=new ActionLog;
		//$resp=$_resp->GetUsersArr($supplier_id, $resps);
		
		//найти контрагента лида, если он вообще есть!!!
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
				
				$descr=SecStr('контрагент '.$su['code'].' '.$su['full_name'].', сотрудник '.$user['name_s']);
				
				$log->PutEntry((int)$result_id, 'автоматическое добавление ответственного сотрудника в карту контрагента при сохранении лида',NULL,950,NULL,$descr,$tender_id);
				$log->PutEntry((int)$result_id, 'автоматическое добавление ответственного сотрудника в карту контрагента при сохранении лида',NULL,87,NULL,$descr,$supplier_id);
				
				
			}
		}
	}
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason,$item=NULL, $result=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		$au=new AuthUser;
		if($result===NULL) $result=$au->Auth(false,false);	
		
		$_dsi=new DocStatusItem;
		if($item['status_id']!=1){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}else{
			//контроль спецправ, либо числа комментариев
			/*if(!$au->user_rights->CheckAccess('w',960)){
				$_hg=new Lead_HistoryGroup;
				$cou=$_hg->CountHistory($id);
				if($cou>0) {
					$can=$can&&false;
					$reasons[]='по лиду написано '.$cou.' комментариев';
					$reason.=implode(', ',$reasons);
				}
			}	*/
			
		}
		
		 
		return $can;
	}
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanRestore($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=3){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	
	
	

	 
	
	
	//Запрос о возм снятия утв цен
	public function DocCanUnconfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=1){
			
			$can=$can&&false;
			$reasons[]='у входящего КП не утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirm_done']==1){
			
			$can=$can&&false;
			$reasons[]='у входящего КП утверждено выполнение';
			$reason.=implode(', ',$reasons);
		 
		}
		
		return $can;
	}
	
	//запрос о возм утв цен
	public function DocCanConfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у входящего КП утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}else{
			
			/*if($item['manager_id']==0){
				$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у ТЗ не указан ответственный сотрудник';
			
			}*/
			
			$reason.=implode(', ',$reasons);	
		}
		
		return $can;
	}
	
	
	
	
	//проверка и автосмена статуса 
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth(false,false);
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		$setted_status_id=$item['status_id'];
		
	 
			if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
				if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)){
					//смена статуса на 2
					$setted_status_id=2;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса КП',NULL,1022,NULL,'установлен статус '.$stat['name'],$item['id']);
				}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)){
					//смена статуса на 1
					$setted_status_id=1;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса КП',NULL,1023,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
			} 
			
			
			 
		 if( 
		(isset($new_params['fulful_kp1'])||isset($new_params['fulful_kp']))
		&&(($new_params['fulful_kp1']!=$old_params['fulful_kp1'])||
		($new_params['fulful_kp']!=$old_params['fulful_kp']))
		
		
		){
			$new_item=$this->GetItemById($id);
			
			if(($new_item['fulful_kp1']!=0)&&($new_item['fulful_kp']!=0)){
				//перебросить статус на 40 - проверен
				$setted_status_id=40;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'смена статуса КП',NULL,1022,NULL,'установлен статус '.$stat['name'],$item['id']);	
			}
			else{
				//снять статус 40 - проверен
				//смена статуса на 2
				$setted_status_id=2;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'смена статуса КП',NULL,1022,NULL,'установлен статус '.$stat['name'],$item['id']);	
			}
		}
		
		
		//die();
	}
	 
	
	
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		
		//$res.=', контакт: '.$this->ConstructContacts($id, $item).', статус '.$stat['name'];
		$res.='Входящее КП, статус '.$stat['name'];
		
		//список к-тов
		$_sg=new Lead_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($item['lead_id']);
		foreach($sg as $k=>$v){
			$res.=', контрагент '.$v['opf_name'].' '.$v['full_name'];	
		}
		
		//список городов
		
		
		return $res; //', статус '.$stat['name'];
	}
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		$res.='Входящее КП '.$item['code'].', статус '.$stat['name'];
		
		//список к-тов
		$_sg=new lead_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($item['lead_id']);
		foreach($sg as $k=>$v){
			$res.=', контрагент '.$v['opf_name'].' '.$v['full_name'];	
		}
		
		//список городов
	/*	$_sg=new Sched_CityGroup;
		$sg=$_sg->GetItemsByIdArr($id);
		foreach($sg as $k=>$v){
			$res.=', город '.$v['name'].', '.$v['okrug_name'].', '.$v['region_name'].', '.$v['country_name'];	
		}
		*/
		return $res; //', статус '.$stat['name'];
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



/******************************************************************************************************/
//КП исх
class KpOut_Item extends KpIn_AbstractItem{
	public $kind_id=1;
	
 	public function Add($params){
		$code=parent::Add($params);
		
		$log=new ActionLog;
		
		if(isset($params['kp_in_id'])){	
			$_kp_in=new KpIn_Item;
			$kp_in=$_kp_in->GetItemById($params['kp_in_id']);
			
			if(($kp_in!==false)&&(($kp_in['fulful_kp']==2)||($kp_in['fulful_kp1']==2))){
				$_kni=new KpInNotesItem;
				$_tz=new TZ_Item;
				$_lead=new Lead_Item;
				$_ui=new UserSItem;
				$user=$_ui->GetItemById($params['created_id']);
				$item=$this->GetItemById($code);
				
				$lead=$_lead->GetItemById($params['lead_id']);
				$tz=$_tz->getitembyid($params['tz_id']);
				
				
				//автоматическое примечание
				
				
				$comm=array(
				'note'=>SecStr('Автоматическое примечание: Исходящее коммерческое предложение '.$item['code'].' создано при подтверждении сотрудником '.$user['name_s'].', что им согласовано с покупателем (клиентом) несоответствие КП '.$kp_in['code'].' ТЗ '.$tz['code'].' в рамках работы по лиду '.$lead['code'].'.'),
				'pdate'=>time(),
				'user_id'=>$code,
				'posted_user_id'=>0
					);
				$_kni->Add($comm);
				$comm['user_id']=$params['kp_in_id'];
				
				$_kni->Add($comm);
				
				//в ЖС
				$log->PutEntry($params['created_id'], 'создание КП', NULL,1018,NULL,$comm['note'], $code);
			}
			
		}
		
		return $code;	
	}
	
	public function NewTenderMessage($code){
		 
	}
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		
		
		 
		
		AbstractItem::Edit($id, $params);
		
		//if(isset($params['manager_id'])&&($params['manager_id']!=0)) $this->ScanResp($id, $params['manager_id'], $_result['id']);
		
		//фиксация даты смены статуса
		if(isset($params['status_id'])&&isset($item['status_id'])&&($params['status_id']!=$item['status_id'])){
			 
			
			if(($params['status_id']==2)&&($item['status_id']!=2)){
				//$_wi=new KpOut_WorkingItem;
				//$_wi->Add(array('sched_id'=>$id, 'in_or_out'=>0, 'pdate'=>time()));
				
				//утвердили - остановка счетчика менеджера
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));	
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['tz_id'], 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
				
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['kp_in_id'], 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
				//запуск счетчтика коордиинаторв ДПиМ (4)
				
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>4, 'in_or_out'=>0, 'pdate'=>time()));	
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['tz_id'], 'kind_id'=>4, 'in_or_out'=>0, 'pdate'=>time()));
				
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['kp_in_id'], 'kind_id'=>4, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi=new KpOut_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['id'], 'kind_id'=>4, 'in_or_out'=>0, 'pdate'=>time()));
					
			}elseif(($params['status_id']==1)&&($item['status_id']!=1)){
				//сняли утверждение - обратный ход
				//запуск счетчика менеджера
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>0, 'in_or_out'=>0, 'pdate'=>time()));	
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['tz_id'], 'kind_id'=>0, 'in_or_out'=>0, 'pdate'=>time()));
				
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['kp_in_id'], 'kind_id'=>0, 'in_or_out'=>0, 'pdate'=>time()));
				//остановка счетчтика коордиинаторв ДПиМ (4)
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));	
				$_wi=new TZ_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['tz_id'], 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));
				
				$_wi=new KpIn_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['kp_in_id'], 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new KpOut_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['id'], 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));	
			}
				
		}
		
		if(isset($params['was_sent'])&&isset($item['was_sent'])&&($params['was_sent']!=$item['was_sent'])){
			if(($params['was_sent']==1)&&($item['was_sent']!=1)){
				//$_wi=new  KpOut_WorkingItem;
				//$_wi->Add(array('sched_id'=>$id, 'in_or_out'=>1, 'pdate'=>time()));
			}
		}
		
		
		if(isset($params['is_prepared_kp'])&&isset($item['is_prepared_kp'])&&($params['is_prepared_kp']!=$item['is_prepared_kp'])){
			
			//простановка второй галочки
			if(($params['is_prepared_kp']==1)&&($item['is_prepared_kp']!=1)){
				
				//выкл сч-к ДПИМ в кпи, лид
				//вкл. сч-к менеджера - кпи, лид
				
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new KpOut_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));
				
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>0, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi=new KpOut_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>0, 'in_or_out'=>0, 'pdate'=>time()));
				
			}elseif(($params['is_prepared_kp']==0)&&($item['is_prepared_kp']!=0)){
				 //вкл сч-к ДПИМ в кпи, лид
				//выкл. сч-к менеджера - кпи, лид
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>4, 'in_or_out'=>0, 'pdate'=>time()));
				$_wi=new KpOut_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>4, 'in_or_out'=>0, 'pdate'=>time()));
				
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$item['lead_id'], 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi=new KpOut_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
			}
			
		}		
		
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	//автоматическое внесение отв сотр в карту контрагента, если его там нет
	protected function ScanResp($tender_id, $manager_id, $result_id){
		//$_si=new SupplierItem;
		$_resp=new SupplierResponsibleUserGroup;
		$_respi=new SupplierResponsibleUserItem;
		$_sup=new Lead_SupplierItem;
		$_si=new SupplierItem;
		$_ui=new UserSItem;
		$log=new ActionLog;
		//$resp=$_resp->GetUsersArr($supplier_id, $resps);
		
		//найти контрагента лида, если он вообще есть!!!
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
				
				$descr=SecStr('контрагент '.$su['code'].' '.$su['full_name'].', сотрудник '.$user['name_s']);
				
				$log->PutEntry((int)$result_id, 'автоматическое добавление ответственного сотрудника в карту контрагента при сохранении лида',NULL,950,NULL,$descr,$tender_id);
				$log->PutEntry((int)$result_id, 'автоматическое добавление ответственного сотрудника в карту контрагента при сохранении лида',NULL,87,NULL,$descr,$supplier_id);
				
				
			}
		}
	}
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason,$item=NULL, $result=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		$au=new AuthUser;
		if($result===NULL) $result=$au->Auth(false,false);	
		
		$_dsi=new DocStatusItem;
		if($item['status_id']!=1){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}else{
			//контроль спецправ, либо числа комментариев
			/*if(!$au->user_rights->CheckAccess('w',960)){
				$_hg=new Lead_HistoryGroup;
				$cou=$_hg->CountHistory($id);
				if($cou>0) {
					$can=$can&&false;
					$reasons[]='по лиду написано '.$cou.' комментариев';
					$reason.=implode(', ',$reasons);
				}
			}	*/
			
		}
		
		 
		return $can;
	}
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanRestore($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=3){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	
	
	

	 
	
	
	//Запрос о возм снятия утв цен
	public function DocCanUnconfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=1){
			
			$can=$can&&false;
			$reasons[]='у исходящего КП не утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirm_done']==1){
			
			$can=$can&&false;
			$reasons[]='у исходящего КП утверждено выполнение';
			$reason.=implode(', ',$reasons);
		 
		}
		
		return $can;
	}
	
	//запрос о возм утв цен
	public function DocCanConfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у исходящего КП утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}else{
			
			/*if($item['manager_id']==0){
				$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у ТЗ не указан ответственный сотрудник';
			
			}*/
			
			$reason.=implode(', ',$reasons);	
		}
		
		return $can;
	}
	
	  
	
	  
	//запрос о возможности  утв подготовку, вложение файла КП и возвращение причины, почему нельзя 
	public function DocCanConfirmPrepare($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_prepared_kp']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]=' у КПИ утверждена подготовка, вложение файла КП';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='КПИ не утверждено';
			 $reason.=implode(', ',$reasons);
		}else{
			 
			
		}
		
		return $can;
	}
	
	
	//запрос о возможности снятия утв подготовку, вложение файла КП и возвращение причины, почему нельзя 
	public function DocCanUnconfirmPrepare($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_prepared_kp']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]=' у КПИ не утверждена подготовка, вложение файла КП';
			$reason.=implode(', ',$reasons);
		} 
		 
		return $can;
	}  
	
	
	
	
	//проверка и автосмена статуса 
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth(false,false);
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		$setted_status_id=$item['status_id'];
		
	 
			if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
				if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)){
					//смена статуса на 2
					$setted_status_id=2;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса КП',NULL,1022,NULL,'установлен статус '.$stat['name'],$item['id']);
					
					//написать примечание в кп ВХ., в журнал кп вх
					$_nkp=new KpInNotesItem;
					$_kp_in=new KpIn_Item; $kp_in=$_kp_in->GetItemById($item['kp_in_id']);
					
					$comment=SecStr('утверждено заполнение КП исх. № '.$item['code'].' по КП вх. № '.$kp_in['code'].' сотрудником '.$_result['name_s']);
					
					$log->PutEntry($_result['id'],'смена статуса КП',NULL,1022,NULL,$comment, $item['kp_in_id']);
					
					$_nkp->Add(array(
						'note'=>'Автоматическое примечание: '.$comment,
						'pdate'=>time(),
						'user_id'=>$item['kp_in_id'],
						'posted_user_id'=>0
					));
					
					$_nkp->Add(array(
						'note'=>'Автоматическое примечание: '.$comment,
						'pdate'=>time(),
						'user_id'=>$id,
						'posted_user_id'=>0
					));
					
					
				}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)){
					//смена статуса на 1
					$setted_status_id=1;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса КП',NULL,1023,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
			} 
			
			
			 
		 
		
		
		//die();
	}
	 
	
	
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		
		//$res.=', контакт: '.$this->ConstructContacts($id, $item).', статус '.$stat['name'];
		$res.='Исходящее КП, статус '.$stat['name'];
		
		//список к-тов
		$_sg=new Lead_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($item['lead_id']);
		foreach($sg as $k=>$v){
			$res.=', контрагент '.$v['opf_name'].' '.$v['full_name'];	
		}
		
		//список городов
		
		
		return $res; //', статус '.$stat['name'];
	}
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		$res.='Исходящее КП '.$item['code'].', статус '.$stat['name'];
		
		//список к-тов
		$_sg=new lead_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($item['lead_id']);
		foreach($sg as $k=>$v){
			$res.=', контрагент '.$v['opf_name'].' '.$v['full_name'];	
		}
		
		//список городов
	/*	$_sg=new Sched_CityGroup;
		$sg=$_sg->GetItemsByIdArr($id);
		foreach($sg as $k=>$v){
			$res.=', город '.$v['name'].', '.$v['okrug_name'].', '.$v['region_name'].', '.$v['country_name'];	
		}
		*/
		return $res; //', статус '.$stat['name'];
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
//определение класса записи
class KpIn_Resolver{
	public $instance;
	function __construct($kind_id=0){
		switch($kind_id){
			case 0:
				$this->instance= new KpIn_Item;
			break;
			case 1:
				$this->instance= new KpOut_Item;
			break;
			 
			default:
				$this->instance=new KpIn_Item;
			break;
		}; 
		 
	}
	
	 
}





// группа КП вх
class  KpIn_Group extends AbstractGroup {
	protected $_auth_result;
	protected $_view;
	
	protected $new_list; //список новых лидов для текущего пользователя с разбивкой их на группы
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_in';
		$this->pagename='leads.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		
		$this->_view=new KpIn_ViewsGroup;
		$this->_view1=new KpOut_ViewsGroup;
		$this->_auth_result=NULL;
		
		$this->new_list=NULL;
	} 
	
	
	//список Документов
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
		 
	 	$prefix='', //13
		$kind_id=0 //14
		
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
		 $sm->assign('kind_id', $kind_id);
		
		
		
		
		 //получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1023);
		$sm->assign('can_unconfirm_users',$usg1); 
 
 
		
		 
		$_sg=new Lead_SupplierGroup;
		
		$_tsg=new KpIn_SupplierGroup;
		
		
		
		$sql='select distinct p.*,
		s.name as status_name, s.weight as status_weight,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
		lead.code as lead_code 
		 
		 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
				left join lead as lead on lead.id=p.lead_id
				 
				
				left join lead_suppliers as ss on ss.sched_id=lead.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join kp_in_suppliers as ss1 on ss1.sched_id=p.id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				
				 
				
				left join user as cr on cr.id=p.created_id
			 
			 
				 
				 	 
				 ';
				
		$sql_count='select count(*) 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				  
				
				left join lead as lead on lead.id=p.lead_id
				 
				
				left join lead_suppliers as ss on ss.sched_id=lead.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join kp_in_suppliers as ss1 on ss1.sched_id=p.id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				 
				
				left join user as cr on cr.id=p.created_id
				 
		 
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
		
		$link=$dec->GenFltUri('&', $prefix);
		//echo $prefix;
		$link=eregi_replace('&sortmode'.$prefix.'','&sortmode',$link);
		$link=eregi_replace('action'.$prefix,'action',$link);
		$link=eregi_replace('&id'.$prefix,'&id',$link);
		
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$link);
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
			
		 
			 $f['given_pdate']=date('d.m.Y', $f['given_pdate']);
			
			
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date('d.m.Y H:i:s', $f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			 
			 
			 
			$_res=new KpIn_Resolver($f['kind_id']);
				//$f['contact_value']=$_res->instance->ConstructContacts($f['id'], $f);
			 
			
			$f['can_annul']=$_res->instance->DocCanAnnul($f['id'],$reason,$f, $this->_auth_result)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$f['suppliers']=$_sg->GetItemsByIdArr($f['lead_id']);	
				
			$f['supplierstz']=$_tsg->GetItemsByIdArr($f['id']);	
				
			  
			 
			
			 //получить блоки "новый документ"
			$f['new_blocks']=$this->DocumentNewBlocks($f['id'], $this->_auth_result['id']);
			 
			
			
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
	
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
	 
 
		 //показ конфигурации
		 if($kind_id==0){
			$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
			$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		 }else{
		 	$sm->assign('view', $this->_view1->GetColsArr($this->_auth_result['id']));
			$sm->assign('unview', $this->_view1->GetColsUnArr($this->_auth_result['id']));

		 }
	 	
		
		//ссылка для кнопок сортировки
			$link=$dec->GenFltUri('&', $prefix);
		//echo $prefix;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$prefix.'=[[:digit:]]+','',$link);
		$link=eregi_replace('action'.$prefix,'action',$link);
		$link=eregi_replace('&id'.$prefix,'&id',$link);
		$sm->assign('link',$link);
		
//		echo $link;
		
		
		return $sm->fetch($template);
	}
	
	
	
	
	//список ID тз, которых может видеть текущий сотрудник
	public function GetAvailableKpInIds($user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//проверить супердоступ
		//если он есть - то это все лиды
		if($_man->CheckAccess($user_id,'w',1020)){
			$sql='select id from kp_in';
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
		}else{
			//свои
			$sql='select id from kp_in where manager_id="'.$user_id.'" or created_id="'.$user_id.'" ';	
			
			//руководитель отдела - давать доступ к документам подчиненных
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
		
		 
		//вставка -1 для корректности
		//if(!$except_me) $arr[]=$user_id;
		//else 
		if(count($arr)==0) $arr[]=-1;
		
		return $arr;	
		
	}  
	
	//проверка, будет ли доступен лид при указанном менеджере указанному сотруднику
	public function ScanAvailableByUserId($manager_id, $user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//проверить супердоступ
		//если он есть - то это все лиды
		if($_man->CheckAccess($user_id,'w',1020)){
			 
			
			return true;
		}else{
			//свои
			
			//echo $sql;
			
			if($manager_id==$user_id){
				return true;	
			}
			
			//руководитель отдела - давать доступ к документам подчиненных
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
	
	
	
	//число новых лидов для текущего сотрудника
	public function CountNewKpIns($user_id){
		
		$tender_ids=$this->GetAvailableKpInIds($user_id);
		
		
		
		$man=new DiscrMan;
		
	
		if($man->CheckAccess($user_id,'w',1016)){	
			$sql='select count(*) from 
			kp_in as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			 
			and t.status_id=2
		 
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
	
	//попадает ли текущее ТЗ при текущем пользователе в индикацию, если попадает - вернуть данные для построения блока
	public function DocumentNewBlocks($document_id, $user_id){
		$data=array();
		
		if($this->new_list===NULL) $this->ConstructNewList($user_id);
		/*$data[]=array(
					'class'=>'menu_new_m',
					
					'url'=>$url,
					'comment'=>'Примите лиды в работу!'
				
				);
		*/
		
		//пересмотреть список данных
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
	
	
	//конструирование списка новых лидов для заданного пользователя
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
					'comment'=>'Примите лиды в работу!'
				
				);
		*/	
		
		$tender_ids=$this->GetAvailableKpInIds($user_id);
		
		  
		$man=new DiscrMan;
		
			
		$_ui=new UserSItem;
		$user=$_ui->GetItemById($user_id);
		 
		
		//КП входящее создано, технический отдел и отдел закупок должен утвердить соответствие кп тз
		if(($user['department_id']==4)||
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
					'comment'=>'Отдел закупок: утвердите соответствие КП ТЗ!'
				
				);
			}
		}
		
		if(($user['department_id']==7)||
			$man->CheckAccess($user_id,'w',1027)	
		){
			$sql='select count(*) from 
				 
					kp_in as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				and t.kind_id=0 
				and t.is_confirmed=1
				and t.status_id not in(3,27)
				and  t.fulful_kp1=0
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
				and  t.fulful_kp1=0
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
					'comment'=>'Технический отдел: утвердите соответствие КП ТЗ!'
				
				);
			}
		}
		
		
		//менеджер: есть КПв, проставлены обе галочки, нет КПи с первой галочкой
		//переход - в карту КПв
		//также кроме менеджера добавить видимость у всех сотрудников, кто  
		// видит лиды менеджеров
		if( 
			$man->CheckAccess($user_id,'w',1018)	
		){
			$sql='select count(*) from 
					kp_in as t
					
					where t.id in ('.implode(', ',$tender_ids).')
					/*and t.lead_id in (select id from lead where manager_id="'.$user_id.'")*/
					and t.kind_id=0
					and t.is_confirmed=1
					and t.status_id not in(3,27)
					and  t.fulful_kp<>0
					and  t.fulful_kp1<>0
					and id not in(select distinct kp_in_id from kp_in where kp_in_id=t.id and is_confirmed=1)
			
			 
				';
				
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select t.* from 
				kp_in as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				/*and t.lead_id in (select id from lead where manager_id="'.$user_id.'")*/
				and t.kind_id=0
				and t.is_confirmed=1
				and t.status_id not in(3,27)
				and  t.fulful_kp<>0
				and  t.fulful_kp1<>0
				and id not in(select distinct kp_in_id from kp_in where kp_in_id=t.id and is_confirmed=1)
				order by t.id asc  
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array(); $sub_ids=array();
					for($j=0; $j<$rc; $j++){ 
						$g=mysqli_fetch_array($rs);		
					 
						$doc_ids[]=$g['id'];
						//$sub_ids[]=$g['id'];
					}	
				
				$this->new_list[]=array(
					 
					'class'=>'reestr_menu_new_kpi',
						'num'=>(int)$rc,
							'doc_ids'=>$doc_ids,
							'doc_counters'=>array(),
							'sub_ids'=>$sub_ids,
							'url'=>'ed_kp_in.php?action=1&id={id}',
					'comment'=>'Создайте исх. КП по вх. КП!'
				
				);
			}
		}
		
	}
	
	
	
	//автоматическое аннулирование
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		
		/*$log=new ActionLog();
		
		$_stat=new DocStatusItem;
		$_res=new Lead_Item;
	 
		$_ni=new Lead_HistoryItem;
		 
		
		$set=new MysqlSet('select * from '.$this->tablename.' where status_id<>'.$annul_status_id.' and status_id=18 order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			$reason='';
			
			
			 
			
			
			
			//случай 1 - нет первой галочки:
			if($f['is_confirmed']==0){
				
				
					
				//проверим дату восстановления
				if($f['restore_pdate']>0){
					if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;	
						$reason='прошло более '.$days_after_restore.' дней с даты восстановления лида,  документ не утвержден';
					}
				}else{
					//работаем с датой создания	
					
					
					if(($f['pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты создания лида,  документ не утвержден';
					}
				}
			 }
			 
			
			
			
			
			
			
			if($can_annul){
				 
					//$_res->instance->Edit($id, $params);
				
				$_res->Edit($f['id'], array('is_confirmed'=>0, 'status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
				
				$log->PutEntry(0,'автоматическое аннулирование лида',NULL,950,NULL,'№ документа: '.$f['code'].' установлен статус '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'sched_id'=>$f['id'],
			 
				'pdate'=>time(),
				'user_id'=>0,
				'txt'=>'Автоматическое примечание: лид был автоматически аннулирован, причина: '.$reason.'.'
				)); 
					
			}
		}*/
		
	}
	
}





// группа КП исх
class  KpIn_Out_Group extends AbstractGroup {
	protected $_auth_result;
	protected $_view;
	
	protected $new_list; //список новых лидов для текущего пользователя с разбивкой их на группы
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_in';
		$this->pagename='leads.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		
		$this->_view=new KpIn_ViewsGroup;
		$this->_view1=new KpOut_ViewsGroup;
		$this->_auth_result=NULL;
		
		$this->new_list=NULL;
	} 
	
	
	//список Документов
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
		 
	 	$prefix='', //13
		$kind_id=0 //14
		
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
		 $sm->assign('kind_id', $kind_id);
		
		
		
		
		 //получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 1023);
		$sm->assign('can_unconfirm_users',$usg1); 
 
 
		
		 
		$_sg=new Lead_SupplierGroup;
		
		$_tsg=new KpIn_SupplierGroup;
		
		
		
		$sql='select distinct p.*,
		s.name as status_name, s.weight as status_weight,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
		lead.code as lead_code,
		kp_in.code as kp_in_code 
		 
		 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
			 	 
				left join lead as lead on lead.id=p.lead_id
				 
				
				left join lead_suppliers as ss on ss.sched_id=lead.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join kp_in_suppliers as ss1 on ss1.sched_id=p.kp_in_id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				
				 
				
				left join user as cr on cr.id=p.created_id
			 
			    left join kp_in as kp_in on kp_in.id=p.kp_in_id
				 
				 	 
				 ';
				
		$sql_count='select count(*) 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				  
				
				left join lead as lead on lead.id=p.lead_id
				 
				
				left join lead_suppliers as ss on ss.sched_id=lead.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join kp_in_suppliers as ss1 on ss1.sched_id=p.kp_in_id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				 
				
				left join user as cr on cr.id=p.created_id
				
				left join kp_in as kp_in on kp_in.id=p.kp_in_id
				 
		 
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
		
		$link=$dec->GenFltUri('&', $prefix);
		//echo $prefix;
		$link=eregi_replace('&sortmode'.$prefix.'','&sortmode',$link);
		$link=eregi_replace('action'.$prefix,'action',$link);
		$link=eregi_replace('&id'.$prefix,'&id',$link);
		
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$link);
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
			
		 
			 $f['given_pdate']=date('d.m.Y', $f['given_pdate']);
			
			
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date('d.m.Y H:i:s', $f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			 
			 
			 
			$_res=new KpIn_Resolver($f['kind_id']);
				//$f['contact_value']=$_res->instance->ConstructContacts($f['id'], $f);
			 
			
			$f['can_annul']=$_res->instance->DocCanAnnul($f['id'],$reason,$f, $this->_auth_result)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$f['suppliers']=$_sg->GetItemsByIdArr($f['lead_id']);	
			
			
			//var_dump($_tsg->GetItemsByIdArr($f['kp_in_id']));
				
			$f['supplierstz']=$_tsg->GetItemsByIdArr($f['kp_in_id']);	
				
			  
			 
			
			 //получить блоки "новый документ"
			$f['new_blocks']=$this->DocumentNewBlocks($f['id'], $this->_auth_result['id']);
			 
			
			
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
	
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
	 
 
		 //показ конфигурации
		 if($kind_id==0){
			$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
			$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		 }else{
		 	$sm->assign('view', $this->_view1->GetColsArr($this->_auth_result['id']));
			$sm->assign('unview', $this->_view1->GetColsUnArr($this->_auth_result['id']));

		 }
	 	
		
		//ссылка для кнопок сортировки
			$link=$dec->GenFltUri('&', $prefix);
		//echo $prefix;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$prefix.'=[[:digit:]]+','',$link);
		$link=eregi_replace('action'.$prefix,'action',$link);
		$link=eregi_replace('&id'.$prefix,'&id',$link);
		$sm->assign('link',$link);
		
//		echo $link;
		
		
		return $sm->fetch($template);
	}
	
	
	
	
	//список ID тз, которых может видеть текущий сотрудник
	public function GetAvailableKpInIds($user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//проверить супердоступ
		//если он есть - то это все лиды
		if($_man->CheckAccess($user_id,'w',1020)){
			$sql='select id from kp_in';
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
		}else{
			//свои
			$sql='select id from kp_in where manager_id="'.$user_id.'" or created_id="'.$user_id.'" ';	
			
			
			//руководитель отдела - давать доступ к документам подчиненных
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
		
		 
		//вставка -1 для корректности
		//if(!$except_me) $arr[]=$user_id;
		//else 
		if(count($arr)==0) $arr[]=-1;
		
		return $arr;	
		
	}  
	
	//проверка, будет ли доступен лид при указанном менеджере указанному сотруднику
	public function ScanAvailableByUserId($manager_id, $user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//проверить супердоступ
		//если он есть - то это все лиды
		if($_man->CheckAccess($user_id,'w',1020)){
			 
			
			return true;
		}else{
			//свои
			 
			 
			//echo $sql;
			
			if($manager_id==$user_id){
				return true;	
			}
			
			
			//руководитель отдела - давать доступ к документам подчиненных
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
	
	
	
	//число новых лидов для текущего сотрудника
	public function CountNewKpIns($user_id){
		
		$tender_ids=$this->GetAvailableKpInIds($user_id);
		
		
		
		$man=new DiscrMan;
		
	
		if($man->CheckAccess($user_id,'w',1016)){	
			$sql='select count(*) from 
			kp_in as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			 
			and t.status_id=2
		 
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
	
	//попадает ли текущее ТЗ при текущем пользователе в индикацию, если попадает - вернуть данные для построения блока
	public function DocumentNewBlocks($document_id, $user_id){
		$data=array();
		
		if($this->new_list===NULL) $this->ConstructNewList($user_id);
		/*$data[]=array(
					'class'=>'menu_new_m',
					
					'url'=>$url,
					'comment'=>'Примите лиды в работу!'
				
				);
		*/
		
		//пересмотреть список данных
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
	
	
	//конструирование списка новых лидов для заданного пользователя
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
					'comment'=>'Примите лиды в работу!'
				
				);
		*/	
		
		$tender_ids=$this->GetAvailableKpInIds($user_id);
		
		  
		$man=new DiscrMan;
		
			
		$_ui=new UserSItem;
		$user=$_ui->GetItemById($user_id);
		 
		
		//координаторы ДПИМ, или права на создание БДР: новые исходящие КП - и нет БДР с 1й галочкой
		//переход в карту КПИ
		if(
		(/*($user['main_department_id']==1) 
		&&*/	($user['position_id']==36))
		||($man->CheckAccess($user_id,'w',1039))
		){
			$sql='select count(*) from 
					kp_in as t
					
					where t.id in ('.implode(', ',$tender_ids).')
					 
					and t.kind_id=1
					and t.is_confirmed=1
					and t.status_id not in(3,27)
					and t.id not in(select distinct b.kp_out_id from bdr as b where b.kp_out_id=t.id  and b.status_id not in(1, 3, 27))
			
			 
				';
				
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select t.* from 
					kp_in as t
					
					where t.id in ('.implode(', ',$tender_ids).')
					 
					and t.kind_id=1
					and t.is_confirmed=1
					and t.status_id not in(3,27)
					and t.id not in(select distinct b.kp_out_id from bdr as b where b.kp_out_id=t.id  and b.status_id not in(1, 3, 27))
				order by t.id asc  
				';
				
				//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$doc_ids=array(); $sub_ids=array();
				for($j=0; $j<$rc; $j++){ 
					$g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['id'];
//					$sub_ids[]=$g['id'];
				}	
			
			$this->new_list[]=array(
				 
				'class'=>'reestr_menu_new_bdr',
					'num'=>(int)$rc,
						'doc_ids'=>$doc_ids,
						'doc_counters'=>array(),
						'sub_ids'=>$sub_ids,
						'url'=>'ed_kp_in.php?action=1&id={id}',
				'comment'=>'Создайте БДР по исх. КП!'
			
			);
			}	
			
			
		}
		
		
		
		
		
		
		//координаторы ДПИМ, или права утв вложения файла КПИ: приложите файл к КПИ, утвердите вложение
		//переход в карту КПИ
		if(
		(/*($user['main_department_id']==1) 
		&&	*/($user['position_id']==36))
		||($man->CheckAccess($user_id,'w',1057))
		){
			$sql='select count(*) from 
					kp_in as t
					
					where t.id in ('.implode(', ',$tender_ids).')
					 
					and t.kind_id=1
					and t.is_confirmed=1
					and t.status_id not in(3,27)
					and t.id in(select distinct b.kp_out_id from bdr as b where b.kp_out_id=t.id  and b.status_id  in(40))
					and t.is_prepared_kp=0
			 
				';
				
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select t.* from 
					kp_in as t
					
					where t.id in ('.implode(', ',$tender_ids).')
					 
					and t.kind_id=1
					and t.is_confirmed=1
					and t.status_id not in(3,27)
					and t.id in(select distinct b.kp_out_id from bdr as b where b.kp_out_id=t.id  and b.status_id  in(40))
					and t.is_prepared_kp=0
				order by t.id asc  
				';
				
				//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$doc_ids=array(); $sub_ids=array();
				for($j=0; $j<$rc; $j++){ 
					$g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['id'];
//					$sub_ids[]=$g['id'];
				}	
			
			$this->new_list[]=array(
				 
				'class'=>'reestr_menu_new_kpi',
					'num'=>(int)$rc,
						'doc_ids'=>$doc_ids,
						'doc_counters'=>array(),
						'sub_ids'=>$sub_ids,
						'url'=>'ed_kp_in.php?action=1&id={id}',
				'comment'=>'Подготовьте файл КП, вложите его в КП исх., утвердите вложение!'
			
			);
			}	
			
			
		}
		
	}
	
	
	
	//автоматическое аннулирование
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		
		/*$log=new ActionLog();
		
		$_stat=new DocStatusItem;
		$_res=new Lead_Item;
	 
		$_ni=new Lead_HistoryItem;
		 
		
		$set=new MysqlSet('select * from '.$this->tablename.' where status_id<>'.$annul_status_id.' and status_id=18 order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			$reason='';
			
			
			 
			
			
			
			//случай 1 - нет первой галочки:
			if($f['is_confirmed']==0){
				
				
					
				//проверим дату восстановления
				if($f['restore_pdate']>0){
					if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;	
						$reason='прошло более '.$days_after_restore.' дней с даты восстановления лида,  документ не утвержден';
					}
				}else{
					//работаем с датой создания	
					
					
					if(($f['pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты создания лида,  документ не утвержден';
					}
				}
			 }
			 
			
			
			
			
			
			
			if($can_annul){
				 
					//$_res->instance->Edit($id, $params);
				
				$_res->Edit($f['id'], array('is_confirmed'=>0, 'status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
				
				$log->PutEntry(0,'автоматическое аннулирование лида',NULL,950,NULL,'№ документа: '.$f['code'].' установлен статус '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'sched_id'=>$f['id'],
			 
				'pdate'=>time(),
				'user_id'=>0,
				'txt'=>'Автоматическое примечание: лид был автоматически аннулирован, причина: '.$reason.'.'
				)); 
					
			}
		}*/
		
	}
	
} 









   
 

/****************************************************************************************************/
//маркер о факте проверки для всплывающих окон
class KpIn_MarkerItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_in_marker';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}

//класс для получения данных для всплывающих окон
class KpIn_PopupGroup extends AbstractGroup{
	
	//расчет числа лидов для вида (1,2,3)  - не взяты в работу
	public function CalcKind123($user_id){
		
//		$check_pdate=date('Y-m-d', time()+48*60*60);
		
		$sql='select count(*) from lead where is_confirmed=1 and status_id=2 and manager_id="'.$user_id.'"';
		
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		return (int)$f[0];
		
			
		
	}
	
	
	
	
		//показ числа лидов для вида (1,2,3)  - не взяты в работу
	public function ShowKind123($user_id){
		$sql=$this->GainSql($user_id, 1);
		
	//	$check_pdate=date('Y-m-d', time()+48*60*60);
		
		$sql.=' where p.is_confirmed=1 and p.status_id=2 and p.manager_id="'.$user_id.'"    order by p.code desc';
		
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
		
		//print_r($arr);
		
		return $arr;
	}
	
	
	
	
	//расчет числа лидов для вида (4)  - дата прошла
	public function CalcKind4($user_id){
		
		$check_pdate=date('Y-m-d', time());
		
		$sql='select count(*) from lead where is_confirmed=1 and status_id in (2,28,35) and manager_id="'.$user_id.'" and pdate_finish<="'.$check_pdate.'"';
		
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		return (int)$f[0];
		
			
		
	}
	
	
		//показ числа лидов для вида  (4)  - дата прошла
	public function ShowKind4($user_id, &$fail_ids, &$fail_vals){
		$sql=$this->GainSql($user_id, 1);
		
	//	$check_pdate=date('Y-m-d', time()+48*60*60);
	$check_pdate=date('Y-m-d', time());
		
		
		$sql.=' where p.is_confirmed=1 and p.status_id in (2,28,35) and p.manager_id="'.$user_id.'"    and p.pdate_finish<="'.$check_pdate.'" order by p.code desc';
		
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
		
		//причины отказа
		$_fails=new Lead_FailGroup;
		$fails=$_fails->GetItemsArr();
		$fail_ids=array(); $fail_vals=array();
		$fail_ids[]=0; $fail_vals[]='-выберите-';
		foreach($fails as $k=>$v){
			$fail_ids[]=$v['id'];
			$fail_vals[]=$v['name'];
		}
		//$sm1->assign('fail_ids', $_ids); $sm1->assign('fail_vals', $_vals);
		
		
		//print_r($arr);
		
		return $arr;
	}
	
	//расчет числа лидов для вида (5,6,7)  - неделю и больше нет комментариев
	public function CalcKind5($user_id, $kind){
		
		//$check_pdate=date('Y-m-d', time());
		
		$flt='';
		/*    %{if $items[rowsec].probability>=0 and  $items[rowsec].probability<=29.99}% 
    <span style="color:#aaaaaa;"> холодный</span>
    %{elseif $items[rowsec].probability>=30 and  $items[rowsec].probability<=69.99}%
    <span style="color:#3a87ad;  ">теплый</span>
    %{elseif $items[rowsec].probability>=70}%
    <span style="color:#e9510f;  ">горячий</span>
    %{/if}%
*/
		if($kind==5){
			$flt.=' and (probability between 0 and 29.99) ';
		}elseif($kind==6){
			$flt.=' and (probability between 30 and 69.99) ';
		}elseif($kind==7){
			$flt.=' and probability>=70';
		}
		
		$sql='select count(*) from lead where is_confirmed=1 and status_id in (2,28,35) and manager_id="'.$user_id.'" and id not in(select distinct sched_id from lead_history where  user_id<>0 and pdate between "'.(time()-7*24*60*60).'" and "'.time().'")  '.$flt;
		
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		return (int)$f[0];
		
			
		
	}
	
	
		//показ числа лидов для вида  (5,6,7)  - неделю и больше нет комментариев
	public function ShowKind5($user_id, $kind){
		$sql=$this->GainSql($user_id, 2);
		
	//	$check_pdate=date('Y-m-d', time()+48*60*60);
		$flt='';
		if($kind==5){
			$flt.=' and (p.probability between 0 and 29.99) ';
		}elseif($kind==6){
			$flt.=' and (p.probability between 30 and 69.99) ';
		}elseif($kind==7){
			$flt.=' and p.probability>=70';
		}
		
		$sql.=' where p.is_confirmed=1 and p.status_id in (2,28,35) and p.manager_id="'.$user_id.'"    and p.id not in(select distinct sched_id from lead_history where user_id<>0 and pdate between "'.(time()-7*24*60*60).'" and "'.time().'")   '.$flt.' order by p.code desc';
		
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
		
		 
		
		//print_r($arr);
		
		return $arr;
	}
	
	
	
	protected $_auth_result, $_sg;
	
	//установка всех имен
	protected function init(){
		$this->tablename='lead';
		$this->pagename='leads.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		$this->_sg=new Lead_SupplierGroup;
		
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
		 tender.code as tender_code,
		 
		 cur.name as currency_name, cur.signature as currency_signature,
		 prod.name as producer_name, lc.value as refuse_counts
		 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				 
				
				 
				
				left join lead_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				 
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
				
				 left join tender_eq_types as eq on eq.id=p.eq_type_id
				left join lead_kind as kind on kind.id=p.kind_id
				left join tender as tender on tender.id=p.tender_id
				left join pl_currency as cur on p.currency_id=cur.id
				
				left join pl_producer as prod on p.producer_id=prod.id
				left join lead_counts as lc on lc.lead_id=p.id and lc.user_id="'.$user_id.'" and lc.kind_id="'.$refuse_kind_id.'"
				
				 	 
				 ';
		return $sql;		 	
	}
	
	//обработка всех полей тендера
	protected function ProcessFields(&$f){
		foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
		 
			$f['refuse_counts']=(int)$f['refuse_counts'];
			
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






//запись в журнал о входе/выходе вх КП из обработки
class KpIn_WorkingItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_in_working';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sched_id';	
	}
	
}


//список записей в журнал о входе/выходе вх КП из обработки
class KpIn_WorkingGroup extends Abstract_WorkingKindGroup {
	 
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_in_working';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		
		  
	}
	
}



//запись в журнал о входе/выходе вх КП из обработки
class KpOut_WorkingItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_in_out_working';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sched_id';	
	}
	
}


//список записей в журнал о входе/выходе вх КП из обработки
class KpOut_WorkingGroup extends Abstract_WorkingKindGroup {
	 
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_in_out_working';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		
		 
	}
	
}









/*******************************************************************************************************/


// справочник контактов к-та
class KpIn_SupplierContactGroup extends SupplierContactGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_contact';
		$this->pagename='view.php';		
		$this->subkeyname='supplier_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0, $current_k_id=0){
		$arr=array();
		
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" order by id asc');
		
		
		
		$_sdg=new KpIn_SupplierContactDataGroup;
		
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


//  список телефонов контакта контрагента
class KpIn_SupplierContactDataGroup extends SupplierContactDataGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_contact_data';
		$this->pagename='view.php';		
		$this->subkeyname='contact_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//список позиций
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


//контрагенты КПВ
class KpIn_SupplierItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_in_suppliers';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}



class KpIn_SupplierGroup extends AbstractGroup {
	 
	public $pagename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_in_suppliers';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	//Отбор поставщиков для события планировщика
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
		
		
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link='suppliers.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	 
	//список позиций, какие были
	public function GetItemsByIdArr($id){
		$arr=array(); $_csg=new SupplierCitiesGroup;
		$_sc=new KpIn_ScGroup;
		
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
	
	
	
	
	
	//добавим позиции
	public function AddSuppliers($current_id, array $positions,  $result=NULL){
		$_kpi=new KpIn_SupplierItem; $_cg=new KpIn_ScGroup;
		
		$log_entries=array();
		
		//сформируем список старых позиций
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
				
				//если есть изменения
				
				//как определить? изменились prava
				
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
		
		//найти и удалить удаляемые позиции:
		//удал. поз. - это позиция, которой нет в массиве $positions
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
		
		//удаляем найденные позиции
		foreach($_to_delete_positions as $k=>$v){
			
			//формируем записи для журнала
			 
			
			$log_entries[]=array(
					'action'=>2,
					
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'note'=>$v['note']
			);
			
			//удаляем позицию
			$_kpi->Del($v['c_id']);
			
			$_cg->ClearById($v['c_id']);
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
	
	
	
	//добавим контрагентов без контактов
	public function AddSuppliersWoCont($current_id, array $positions,  $result=NULL){
		$_kpi=new KpIn_SupplierItem;  
		
		$log_entries=array();
		
		//сформируем список старых позиций
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
				
				//если есть изменения
				
				//как определить
				
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
		
		//найти и удалить удаляемые позиции:
		//удал. поз. - это позиция, которой нет в массиве $positions
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
		
		//удаляем найденные позиции
		foreach($_to_delete_positions as $k=>$v){
			
			//формируем записи для журнала
			 
			
			$log_entries[]=array(
					'action'=>2,
					
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'note'=>$v['note']
			);
			
			//удаляем позицию
			$_kpi->Del($v['c_id']);
			
		 
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
	 
	
}

//контакты контрагента по ТЗ	
class KpIn_ScItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_in_suppliers_contacts';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sc_id';	
	}
	
}



class KpIn_ScGroup extends AbstractGroup {
	 
	public $pagename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_in_suppliers_contacts';
		$this->pagename='view.php';		
		$this->subkeyname='sc_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	//список, какие есть
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









// группа примечаний КП
class KpInNotesGroup extends NotesGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_in_notes';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}

//примечание к КП
class KpInNotesItem extends NotesItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_in_notes';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
	
}





// группа сроков поставки
class KpInSupplyPdateGroup extends KpSupplyPdateGroup{
	//установка всех имен
	protected function init(){
		$this->tablename='kp_supply_pdate_mode';
		$this->pagename='view.php';		
		$this->subkeyname='group_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
}

//срок поставки
class KpInSupplyPdateItem extends KpSupplyPdateItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_supply_pdate_mode';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}

?>