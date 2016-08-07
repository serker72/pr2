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
require_once('lead_view.class.php');

require_once('lead_history_item.php');

require_once('tz.class.php');
require_once('kp_in.class.php');
require_once('bdr.class.php');
require_once('kpitem.php');

require_once('user_pos_item.php');


require_once('abstract_working_kind_group.php');
require_once('docstatusitem.php');

//библиотека классов лида


//абстрактная запись лида

class Lead_AbstractItem extends AbstractItem{
	public $kind_id=1;
	protected function init(){
		$this->tablename='lead';
		$this->item=NULL;
		$this->pagename='ed_lead.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}	
	
	
	public function Add($params){
		/**/
		$digits=5;
		
		
		$begin='ЛД';
		
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
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL, $apply_to_tenders=true){
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
		
		return 'Лид, статус '.$stat['name'];
	}
	
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		return 'Лид '.$item['code'].', статус '.$stat['name'];
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
	
	public function GetFactTZ($id, $item=NULL){
		$docs=array();
		
	}
	
	 
	
}


/*************************************************************************************************/
//лид
class Lead_Item extends Lead_AbstractItem{
	public $kind_id=1;
	
	//добавить 
	/*public function Add($params){
		 
		$code=parent::Add($params);
		
		
		

		return $code;
	} */
	
	
	public function NewTenderMessage($code){
		 
	}
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL,  $apply_to_tenders=true){
		$item=$this->GetItemById($id);
		
		$log=new ActionLog;
		
		$_au=new AuthUser();
		if($_result===NULL) $_result=$_au->Auth();
		
		
		 
		
		AbstractItem::Edit($id, $params);
		
		if(isset($params['manager_id'])&&($params['manager_id']!=0)) $this->ScanResp($id, $params['manager_id'], $_result['id']);
		
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
		
		//трансляция изменений в родительский тендер
		if(($apply_to_tenders)&&( isset($params['is_confirmed_done'])||isset($params['is_fulfiled']))){
			$this->ScanTenders($id, $item, $params,  $_result);
			
		}
		
		//фиксация даты смены статуса
		if(isset($params['status_id'])&&isset($item['status_id'])&&($params['status_id']!=$item['status_id'])){
			//AbstractItem::Edit($id, array('pdate_status_change'=>time()));
			
			
			if(($item['status_id']==28)&&($params['status_id']!=28)){
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
					
			}elseif(($item['status_id']!=28)&&($params['status_id']==28)){
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>0, 'in_or_out'=>0, 'pdate'=>time()));
			}
			
			
			//перевод в отказ, отложен - перенос подчиненных документов в статус 27 не актуально
			if((($item['status_id']!=25)&&($params['status_id']==25))||
			(($item['status_id']!=34)&&($params['status_id']==34))||
			(($item['status_id']!=37)&&($params['status_id']==37))
			){
				
				//остановка счетчиков
				$_wi=new Lead_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>2, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>3, 'in_or_out'=>1, 'pdate'=>time()));
				$_wi->Add(array('sched_id'=>$id, 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));
				
				$setted_status=27;
				$_dsi=new DocStatusItem;
				$status=$_dsi->GetItemById($setted_status);	
				$_tz=new TZ_Item;
				$_kp_in=new KpIn_Item; $_kp_out=new KpOut_Item; $_kp=new KpItem; $_bdr=new BDR_Item;
				if($_result===NULL){
					$_auth=new AuthUser();
					$_result=$_auth->Auth();	
				}
					
				//найти, перенести ТЗ
				$sql='select t.* from tz as t where t.status_id<>3 and t.lead_id="'.$id.'"';
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				$_wi=new TZ_WorkingItem;
				
				for($i=0; $i<$rc; $i++) {
					$f=mysqli_fetch_array($rs);	
					
					$_tz->Edit($f['id'], array('status_id'=>$setted_status),false,$_result);
					$log->PutEntry($_result['id'], 'автоматическая смена статуса ТЗ при смене статуса лида', NULL, 1009, NULL, SecStr('ТЗ '.$f['code'].': установлен статус '.$status['name'].' при смене статуса лида '.$item['code']), $f['id']);
					
					$log->PutEntry($_result['id'], 'автоматическая смена статуса ТЗ при смене статуса лида', NULL, 950, NULL, SecStr('ТЗ '.$f['code'].': установлен статус '.$status['name'].' при смене статуса лида '.$item['code']), $id);
					
					
					$_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
					$_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
					$_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>3, 'in_or_out'=>1, 'pdate'=>time()));
					$_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));
					 
				
				}
				
				
				//найти, перенести КП ПЛ
				$sql='select t.* from kp as t where t.status_id<>3 and t.lead_id="'.$id.'"';
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				
				for($i=0; $i<$rc; $i++) {
					$f=mysqli_fetch_array($rs);	
					
					$_kp->Edit($f['id'], array('status_id'=>$setted_status),false,$_result);
					$log->PutEntry($_result['id'], 'автоматическая смена статуса КП при смене статуса лида', NULL, 701, NULL, SecStr('КП '.$f['code'].': установлен статус '.$status['name'].' при смене статуса лида '.$item['code']), $f['id']);
					
					$log->PutEntry($_result['id'], 'автоматическая смена статуса КП при смене статуса лида', NULL, 950, NULL, SecStr('КП '.$f['code'].': установлен статус '.$status['name'].' при смене статуса лида '.$item['code']), $id);
					
					 /*
					 
					$_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
					$_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>2, 'in_or_out'=>1, 'pdate'=>time()));*/
					 
				}
				
				//найти, перенести КП, КПВ не ПЛ	
				$sql='select t.* from kp_in as t where t.status_id<>3 and t.lead_id="'.$id.'"';
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				$_wi=new KpIn_WorkingItem;
				$_wo=new KpOut_WorkingItem;;
				
				for($i=0; $i<$rc; $i++) {
					$f=mysqli_fetch_array($rs);	
					
					if($f['kind_id']==1){
						//исход.
						$_kp_out->Edit($f['id'], array('status_id'=>$setted_status),false,$_result);
						
						$_wo->Add(array('sched_id'=>$f['id'], 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));
					 
							
					}else{
						//вход.	
						$_kp_in->Edit($f['id'], array('status_id'=>$setted_status),false,$_result);
						
						$_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>0, 'in_or_out'=>1, 'pdate'=>time()));
						$_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>1, 'in_or_out'=>1, 'pdate'=>time()));
						$_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>2, 'in_or_out'=>1, 'pdate'=>time()));
						$_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));
					}
					
					
					$log->PutEntry($_result['id'], 'автоматическая смена статуса КП при смене статуса лида', NULL, 1021, NULL, SecStr('КП '.$f['code'].': установлен статус '.$status['name'].' при смене статуса лида '.$item['code']), $f['id']);
						
						$log->PutEntry($_result['id'], 'автоматическая смена статуса КП при смене статуса лида', NULL, 950, NULL, SecStr('КП '.$f['code'].': установлен статус '.$status['name'].' при смене статуса лида '.$item['code']), $id);
				}	
				
				
				
				//найти, перенести БДР	
				$sql='select t.* from bdr as t where t.status_id<>3 and t.lead_id="'.$id.'"';
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				$_wi=new KpIn_WorkingItem;
				 
				for($i=0; $i<$rc; $i++) {
					$f=mysqli_fetch_array($rs);	
					
					 
						
					$_bdr->Edit($f['id'], NULL, array('status_id'=>$setted_status), NULL, false, $_result);
						
						//$_wo->Add(array('sched_id'=>$f['id'], 'kind_id'=>4, 'in_or_out'=>1, 'pdate'=>time()));
					 
					
					$log->PutEntry($_result['id'], 'автоматическая смена статуса БДР при смене статуса лида', NULL, 1041, NULL, SecStr('КП '.$f['code'].': установлен статус '.$status['name'].' при смене статуса лида '.$item['code']), $f['id']);
						
						$log->PutEntry($_result['id'], 'автоматическая смена статуса БДР при смене статуса лида', NULL, 950, NULL, SecStr('КП '.$f['code'].': установлен статус '.$status['name'].' при смене статуса лида '.$item['code']), $id);
				}		
			}
				
		}
		
		
		if(isset($params['is_confirmed'])&&isset($item['is_confirmed'])&&($params['is_confirmed']!=$item['is_confirmed'])&&($params['is_confirmed']==0)){
			//снимаем утверждение лида. автоматом снимаем утверждения:
			/*
			-ТЗ
			-КПВ
			-КПИ
			-БДР
			*/
			
			$_tz=new TZ_Item; $_kp_in=new KpIn_Item; $_kp_out=new KpOut_Item; $_bdr=new BDR_Item;
			$_ntz=new TzNotesItem; $_nkp=new KpInNotesItem; $_nl=new Lead_HistoryItem; $_nbdr=new BDRNotesItem; 
			$setted_status=1;
			$_dsi=new DocStatusItem;
			$status=$_dsi->GetItemById($setted_status);
			 
			//ТЗ
			$sql=' select * from tz where lead_id="'.$id.'" and status_id not in(3,27) and is_confirmed=1';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++) {
				$f=mysqli_fetch_array($rs);
				$_tz->Edit($f['id'], array(
								'is_confirmed'=>0,
								'confirm_pdate'=>time(),
								'user_confirm_id'=>0,
								'is_not_eq'=>0,
								'not_eq_id'=>0,
								'not_eq_pdate'=>time(),
								'force_eq_in'=>0,
								'force_eq_in_id'=>0,
								'force_eq_in_pdate'=>time(),
								'status_id'=>$setted_status), false, $_result);
				$comment=SecStr('Автоматически снято утверждение заполнения ТЗ № '.$f['code'].' при снятии утверждения заполнения лида №'.$item['code'].' сотрудником '.$_result['name_s'].', установлен статус '.$status['name']);				
				$log->PutEntry($_result['id'], 'автоматическое снятие утверждения ТЗ при снятии утверждения родительского лида', NULL, 1011,NULL,$comment, $f['id']);
				$log->PutEntry(	$_result['id'], 'автоматическое снятие утверждения ТЗ при снятии утверждения родительского лида', NULL, 965,NULL,$comment, $id);			
				
				$_ntz->Add(array(
					'note'=>'Автоматическое примечание: '.$comment,
					'pdate'=>time(),
					'user_id'=>$f['id'],
					'posted_user_id'=>0
				));
				
				$_nl->Add(array(
					'sched_id'=>$id,
					'user_id'=>0,
					'txt'=>'Автоматический комментарий: '.$comment,
					 
					'pdate'=>time()
				));
				
				//остановить все счетичики по ТЗ
				$_wi=new TZ_WorkingItem;
				for($j=0; $j<=5; $j++) $_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>$j, 'in_or_out'=>1, 'pdate'=>time()));
				
					
				
			}
			
			//КПВ
			$sql=' select * from kp_in where lead_id="'.$id.'" and kind_id=0 and status_id not in(3,27) and is_confirmed=1';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++) {
				$f=mysqli_fetch_array($rs);
				$_kp_in->Edit($f['id'], array(
								'is_confirmed'=>0,
								'confirm_pdate'=>time(),
								'user_confirm_id'=>0,
								'fulful_kp'=>0,
								'fulfil_user_id'=>0,
								'fulfil_pdate'=>time(),
								'fulfil_user_id1'=>0,
								'fulfil_user_id1'=>0,
								'fulfil_pdate1'=>time(),
								'status_id'=>$setted_status), false, $_result);
				$comment=SecStr('Автоматически снято утверждение заполнения КП вх. № '.$f['code'].' при снятии утверждения заполнения лида №'.$item['code'].' сотрудником '.$_result['name_s'].', установлен статус '.$status['name']);				
				$log->PutEntry($_result['id'], 'автоматическое снятие утверждения КП вх. при снятии утверждения родительского лида', NULL, 1023,NULL,$comment, $f['id']);
				$log->PutEntry(	$_result['id'], 'автоматическое снятие утверждения КП вх. при снятии утверждения родительского лида', NULL, 965,NULL,$comment, $id);			
				
				$_nkp->Add(array(
					'note'=>'Автоматическое примечание: '.$comment,
					'pdate'=>time(),
					'user_id'=>$f['id'],
					'posted_user_id'=>0
				));
				
				$_nl->Add(array(
					'sched_id'=>$id,
					'user_id'=>0,
					'txt'=>'Автоматический комментарий: '.$comment,
					 
					'pdate'=>time()
				));
				
				//остановить все счетичики по КП вх.
				$_wi=new KpIn_WorkingItem;
				for($j=0; $j<=5; $j++) $_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>$j, 'in_or_out'=>1, 'pdate'=>time()));
				
			}
			
			
			//КПИ
			$sql=' select * from kp_in where lead_id="'.$id.'" and kind_id=1 and status_id not in(3,27) and is_confirmed=1';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++) {
				$f=mysqli_fetch_array($rs);
				$_kp_out->Edit($f['id'], array(
								'is_confirmed'=>0,
								'confirm_pdate'=>time(),
								'user_confirm_id'=>0,
								'fulful_kp'=>0,
								'fulfil_user_id'=>0,
								'fulfil_pdate'=>time(),
								'fulfil_user_id1'=>0,
								'fulfil_user_id1'=>0,
								'fulfil_pdate1'=>time(),
								'status_id'=>$setted_status), false, $_result);
				$comment=SecStr('Автоматически снято утверждение заполнения КП исх. № '.$f['code'].' при снятии утверждения заполнения лида №'.$item['code'].' сотрудником '.$_result['name_s'].', установлен статус '.$status['name']);				
				$log->PutEntry($_result['id'], 'автоматическое снятие утверждения КП исх. при снятии утверждения родительского лида', NULL, 1023,NULL,$comment, $f['id']);
				$log->PutEntry(	$_result['id'], 'автоматическое снятие утверждения КП исх. при снятии утверждения родительского лида', NULL, 965,NULL,$comment, $id);			
				
				$_nkp->Add(array(
					'note'=>'Автоматическое примечание: '.$comment,
					'pdate'=>time(),
					'user_id'=>$f['id'],
					'posted_user_id'=>0
				));
				
				$_nl->Add(array(
					'sched_id'=>$id,
					'user_id'=>0,
					'txt'=>'Автоматический комментарий: '.$comment,
					 
					'pdate'=>time()
				));
				
				//остановить все счетичики по КП исх.
				$_wi=new KpOut_WorkingItem;
				for($j=0; $j<=5; $j++) $_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>$j, 'in_or_out'=>1, 'pdate'=>time()));
				
			}
			
			//БДР
			//$sql=' select * from bdr where lead_id="'.$id.'" and kind_id=1 and status_id not in(3,27) and is_confirmed=1';
			$sql='select t.*, l.* from bdr as t
		
		left join bdr_version as l on l.bdr_id=t.id and l.vid in (select max(ll.vid) as `s_q` from bdr_version as ll where ll.bdr_id=t.id ) 
		where t.lead_id="'.$id.'" and t.status_id not in(3,27) and l.is_confirmed=1';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++) {
				$f=mysqli_fetch_array($rs);
				$_bdr->Edit($f['id'], NULL, array('status_id'=>$setted_status), 
							 array(
								'is_confirmed'=>0,
								'confirm_pdate'=>time(),
								'user_confirm_id'=>0,
								'is_confirmed_version'=>0,
								'confirm_version_pdate'=>time(),
								'user_confirm_version_id'=>0
								 
								), false, $_result);
				$comment=SecStr('Автоматически снято утверждение заполнения БДР № '.$f['code'].' при снятии утверждения заполнения лида №'.$item['code'].' сотрудником '.$_result['name_s'].', установлен статус '.$status['name']);				
				$log->PutEntry($_result['id'], 'автоматическое снятие утверждения БДР при снятии утверждения родительского лида', NULL, 1043,NULL,$comment, $f['id']);
				$log->PutEntry(	$_result['id'], 'автоматическое снятие утверждения БДР при снятии утверждения родительского лида', NULL, 965,NULL,$comment, $id);			
				
				$_nbdr->Add(array(
					'note'=>'Автоматическое примечание: '.$comment,
					'pdate'=>time(),
					'user_id'=>$f['id'],
					'posted_user_id'=>0
				));
				
				$_nl->Add(array(
					'sched_id'=>$id,
					'user_id'=>0,
					'txt'=>'Автоматический комментарий: '.$comment,
					 
					'pdate'=>time()
				));
				
				//остановить все счетичики по БДР
				//$_wi=new KpOut_WorkingItem;
				//for($j=0; $j<=5; $j++) $_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>$j, 'in_or_out'=>1, 'pdate'=>time()));
				
			}
			
			//перевод?
			
			
			//Остановить все счетчики по лиду
			$_wi=new Lead_WorkingItem;
			for($j=0; $j<=5; $j++) $_wi->Add(array('sched_id'=>$id, 'kind_id'=>$j, 'in_or_out'=>1, 'pdate'=>time()));
		}
		elseif(isset($params['is_confirmed'])&&isset($item['is_confirmed'])&&($params['is_confirmed']!=$item['is_confirmed'])&&($params['is_confirmed']==1)){
			
			//снимаем утверждение лида. автоматом снимаем утверждения:
			/*
			-ТЗ
			-КПВ
			-КПИ
			-БДР
			*/
			
			$_tz=new TZ_Item; $_kp_in=new KpIn_Item; $_kp_out=new KpOut_Item; $_bdr=new BDR_Item;
			$_ntz=new TzNotesItem; $_nkp=new KpInNotesItem; $_nl=new Lead_HistoryItem; $_nbdr=new BDRNotesItem; 
			//$setted_status=1;
			//$_dsi=new DocStatusItem;
			//$status=$_dsi->GetItemById($setted_status);
			 
			//ТЗ
			$sql=' select * from tz where lead_id="'.$id.'" and status_id not in(3,27) and is_confirmed=0';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++) {
				$f=mysqli_fetch_array($rs);
				$_tz->Edit($f['id'], array(
								'is_confirmed'=>1,
								'confirm_pdate'=>time(),
								'user_confirm_id'=>0), true, $_result);
				$comment=SecStr('Автоматически установлен утверждение заполнения ТЗ № '.$f['code'].' при снятии утверждения заполнения лида №'.$item['code'].' сотрудником '.$_result['name_s']);				
				$log->PutEntry($_result['id'], 'автоматическое утверждение ТЗ при утверждении родительского лида', NULL, 1010,NULL,$comment, $f['id']);
				$log->PutEntry(	$_result['id'], 'автоматическое утверждение ТЗ при утверждении родительского лида', NULL, 950,NULL,$comment, $id);			
				
				$_ntz->Add(array(
					'note'=>'Автоматическое примечание: '.$comment,
					'pdate'=>time(),
					'user_id'=>$f['id'],
					'posted_user_id'=>0
				));
				
				$_nl->Add(array(
					'sched_id'=>$id,
					'user_id'=>0,
					'txt'=>'Автоматический комментарий: '.$comment,
					 
					'pdate'=>time()
				));
				
				//остановить все счетичики по ТЗ
				//$_wi=new TZ_WorkingItem;
				//for($j=0; $j<=5; $j++) $_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>$j, 'in_or_out'=>1, 'pdate'=>time()));
				
					
				
			}
			
			//КПВ
			$sql=' select * from kp_in where lead_id="'.$id.'" and kind_id=0 and status_id not in(3,27) and is_confirmed=0';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++) {
				$f=mysqli_fetch_array($rs);
				$_kp_in->Edit($f['id'], array(
								'is_confirmed'=>1,
								'confirm_pdate'=>time(),
								'user_confirm_id'=>0), true, $_result);
				$comment=SecStr('Автоматически утверждено заполнение КП вх. № '.$f['code'].' при утверждении заполнения лида №'.$item['code'].' сотрудником '.$_result['name_s']);				
				$log->PutEntry($_result['id'], 'автоматическое утверждение КП вх. при утверждении родительского лида', NULL, 1022,NULL,$comment, $f['id']);
				$log->PutEntry(	$_result['id'], 'автоматическое утверждение КП вх. при утверждении родительского лида', NULL, 950,NULL,$comment, $id);			
				
				$_nkp->Add(array(
					'note'=>'Автоматическое примечание: '.$comment,
					'pdate'=>time(),
					'user_id'=>$f['id'],
					'posted_user_id'=>0
				));
				
				$_nl->Add(array(
					'sched_id'=>$id,
					'user_id'=>0,
					'txt'=>'Автоматический комментарий: '.$comment,
					 
					'pdate'=>time()
				));
				
				//остановить все счетичики по КП вх.
				//$_wi=new KpIn_WorkingItem;
				//for($j=0; $j<=5; $j++) $_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>$j, 'in_or_out'=>1, 'pdate'=>time()));
				
			}
			
			
			//КПИ
			$sql=' select * from kp_in where lead_id="'.$id.'" and kind_id=1 and status_id not in(3,27) and is_confirmed=0';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++) {
				$f=mysqli_fetch_array($rs);
				$_kp_out->Edit($f['id'], array(
								'is_confirmed'=>1,
								'confirm_pdate'=>time(),
								'user_confirm_id'=>0), true, $_result);
				$comment=SecStr('Автоматически утверждено заполнение КП исх. № '.$f['code'].' при утверждении заполнения лида №'.$item['code'].' сотрудником '.$_result['name_s']);				
				$log->PutEntry($_result['id'], 'автоматическое утверждение КП исх. при утверждении родительского лида', NULL, 1022,NULL,$comment, $f['id']);
				$log->PutEntry(	$_result['id'], 'автоматическое утверждение КП исх. при утверждении родительского лида', NULL, 950,NULL,$comment, $id);			
				
				$_nkp->Add(array(
					'note'=>'Автоматическое примечание: '.$comment,
					'pdate'=>time(),
					'user_id'=>$f['id'],
					'posted_user_id'=>0
				));
				
				$_nl->Add(array(
					'sched_id'=>$id,
					'user_id'=>0,
					'txt'=>'Автоматический комментарий: '.$comment,
					 
					'pdate'=>time()
				));
				
				//остановить все счетичики по КП исх.
				//$_wi=new KpOut_WorkingItem;
				//for($j=0; $j<=5; $j++) $_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>$j, 'in_or_out'=>1, 'pdate'=>time()));
				
			}
			
			//БДР
			$sql='select t.*, l.* from bdr as t
		
		left join bdr_version as l on l.bdr_id=t.id and l.vid in (select max(ll.vid) as `s_q` from bdr_version as ll where ll.bdr_id=t.id ) 
		where t.lead_id="'.$id.'" and t.status_id not in(3,27) and l.is_confirmed=0';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++) {
				$f=mysqli_fetch_array($rs);
				$_bdr->Edit($f['id'], NULL, NULL, 
							 array(
								'is_confirmed'=>1,
								'confirm_pdate'=>time(),
								'user_confirm_id'=>0 
								 
								 
								), true, $_result);
				$comment=SecStr('Автоматически утверждено заполнения БДР № '.$f['code'].' при утверждении заполнения лида №'.$item['code'].' сотрудником '.$_result['name_s']);				
				$log->PutEntry($_result['id'], 'автоматическое утверждение БДР при утверждении родительского лида', NULL, 1042,NULL,$comment, $f['id']);
				$log->PutEntry(	$_result['id'], 'автоматическое утверждение БДР при утверждении родительского лида', NULL, 950,NULL,$comment, $id);			
				
				$_nbdr->Add(array(
					'note'=>'Автоматическое примечание: '.$comment,
					'pdate'=>time(),
					'user_id'=>$f['id'],
					'posted_user_id'=>0
				));
				
				$_nl->Add(array(
					'sched_id'=>$id,
					'user_id'=>0,
					'txt'=>'Автоматический комментарий: '.$comment,
					 
					'pdate'=>time()
				));
				
				//остановить все счетичики по БДР
				//$_wi=new KpOut_WorkingItem;
				//for($j=0; $j<=5; $j++) $_wi->Add(array('sched_id'=>$f['id'], 'kind_id'=>$j, 'in_or_out'=>1, 'pdate'=>time()));
				
			}
		}
	}
	
	
	//для тендерного лида: автоматическая синхронизация смены 2,3 галочки в родит. тендер
	protected function ScanTenders($lead_id, $old_params, $new_params, $_result=NULL){
		$_au=new AuthUser();
		if($_result===NULL) $_result=$_au->Auth();
		$log=new ActionLog;
		
		$item=$this->GetItemById($lead_id);
		
		//найдем Тендеры 
		$sql='select p.id, p.code from tender as p where p.id="'.$item['tender_id'].'" and p.status_id<>3';
		$tenders=array();
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++) {
			$tenders[]=mysqli_fetch_array($rs);	
		}
		
		$_ti=new Tender_Item; $_stat=new DocStatusItem;
		//is_confirmed_done, is_fulfiled
		
		if(isset($new_params['is_confirmed_done'])&&isset($old_params['is_confirmed_done'])&&($new_params['is_confirmed_done']!=$old_params['is_confirmed_done'])){
			foreach($tenders as $k=>$tender){
				$_ti->Edit($tender['id'], array('is_confirmed_done'=>$new_params['is_confirmed_done'], ' 	user_confirm_done_id'=>$_result['id'],'confirm_done_pdate'=>time()),true, $_result, false);
				
				if($new_params['is_confirmed_done']==1) $comment='утверждение выполнения';
				else $comment='снятие утверждения выполнения';
				 
				 
				
				$log->PutEntry($_result['id'],$comment.' родительского тендера при смене статуса лида',NULL,931,NULL,'лид '.$item['code'].', тендер '.$tender['code'].': '.$comment,$tender['id']);
				
				$log->PutEntry($_result['id'],$comment.' родительского тендера при смене статуса лида',NULL,950,NULL,'лид '.$item['code'].', тендер '.$tender['code'].': '.$comment,$item['id']);
			}
		}
		
		if(isset($new_params['is_fulfiled'])&&isset($old_params['is_fulfiled'])&&($new_params['is_fulfiled']!=$old_params['is_fulfiled'])){
			foreach($tenders as $k=>$tender){
				$_ti->Edit($tender['id'], array('is_fulfiled'=>$new_params['is_fulfiled'], ' 	user_fulfiled_id'=>$_result['id'],'fulfiled_pdate'=>time()) ,true,$_result, false);
				
				if($new_params['is_fulfiled']==1) $comment='утверждение приема работы';
				else $comment='снятие утверждения приема работы';
				 
				$log->PutEntry($_result['id'],$comment.' родительского тендера при смене статуса лида',NULL,931,NULL,'лид '.$item['code'].', тендер '.$tender['code'].': '.$comment,$tender['id']);
				
				$log->PutEntry($_result['id'],$comment.' родительского тендера при смене статуса лида',NULL,950,NULL,'лид '.$item['code'].', тендер '.$tender['code'].': '.$comment,$item['id']);
			}
		}
		
		
		
		
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
		if($item['status_id']!=18){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}else{
			//контроль спецправ, либо числа комментариев
			if(!$au->user_rights->CheckAccess('w',960)){
				$_hg=new Lead_HistoryGroup;
				$cou=$_hg->CountHistory($id);
				if($cou>0) {
					$can=$can&&false;
					$reasons[]='по лиду написано '.$cou.' комментариев';
					$reason.=implode(', ',$reasons);
				}
			}	
			
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
	
	
	
	

	//запрос о возможности  утв прием и возвращение причины, почему нельзя 
	public function DocCanConfirmFulfil($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_fulfiled']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у лида утверждено принятие';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed_done']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у лида не утверждено выполнение';
		 
		}elseif($item['is_confirmed']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у лида не утверждено заполнение';
		}
		
		return $can;
	}
	
	
	//запрос о возможности снятия утв приема и возвращение причины, почему нельзя 
	public function DocCanUnconfirmFulfil($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_fulfiled']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у лида не утверждено принятие';
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
			$reasons[]='у лида не утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirm_done']==1){
			
			$can=$can&&false;
			$reasons[]='у лида утверждено выполнение';
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
			$reasons[]='у лида утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}else{
			
			if($item['manager_id']==0){
				$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у лида не указан ответственный сотрудник';
			
			}
			
			
			if(datefromdmy(datefromymd($item['pdate_finish']))<time()){
				$can=$can&&false;
				$reasons[]='прогнозируемая дата заключения контракта должна быть позднее сегодняшней даты';
			}
			
			$reason.=implode(', ',$reasons);	
		}
		
		return $can;
	}
	
	//запрос о возможности  утв отгр и возвращение причины, почему нельзя 
	public function DocCanConfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirm_done']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у лида утверждено выполнение';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у лида не утверждено заполнение';
			 $reason.=implode(', ',$reasons);
		}else{
			
			if($item['manager_id']==0){
				$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
				$reasons[]='у лида не выбран ответственный сотрудник';
			}
			
			 $reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	
	//запрос о возможности снятия утв отгр и возвращение причины, почему нельзя 
	public function DocCanUnconfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_done']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у лида не утверждено выполнение';
			$reason.=implode(', ',$reasons);
		} 
		elseif($item['is_fulfiled']!=0){
			$can=$can&&false;
			$reasons[]='у лида утверждено принятие работы';
			$reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	//есть ли активные ТЗ по лиду?
	public function HasActiveTZ($id, &$reason){
		$reason=''; $reasons=array();
		$has=false;
		
		$sql='select t.*, st.name as status_name from
		tz as t left join document_status as st on t.status_id=st.id
		where 
			(t.is_not_eq=1 
			and t.force_eq_in=0)
			
		and t.lead_id="'.$id.'"
		order by t.id';
		
		//echo $sql;
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->getresultnumrows();
		
		$has=($rc>0);
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$reasons[]='<li>ТЗ <a href="ed_tz.php?action=1&id='.$f['id'].'" target="_blank">'.$f['code'].'</a>, статус '.$f['status_name'].'</li>';
		
		}
		
		$reason=implode('<br>', $reasons);
		
		return $has;
	}

	
	
	//найти список связанных документов (тендеров!)
	protected function FindBindedDocs($id, $item=NULL){
		$docs=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		$sql='select t.*, st.name as status_name from
		tender as t left join document_status as st on t.status_id=st.id
		where t.status_id<>3 and t.id="'.$item['tender_id'].'"
		order by t.id';
		
		//echo $sql;
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->getresultnumrows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$docs[]='<li>Тендер <a href="ed_tender.php?action=1&id='.$f['id'].'" target="_blank">'.$f['code'].'</a>, статус '.$f['status_name'].'</li>';
		
		}
		
		return $docs;	
	}
	
	//найти список связанных дочерних документов, автоматически утверждаемых при утверждении лида
	public function FindBindedChild($id, $item=NULL){
		$docs=array();
		if($item===NULL) $item=$this->GetItemById($id);
		//ТЗ
		$sql=' select t.*, st.name as status_name  from tz as t 
		left join document_status as st on st.id=t.status_id
		where t.lead_id="'.$id.'" and t.status_id not in(3,27) and t.is_confirmed=0';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++) {
			$f=mysqli_fetch_array($rs);
			
			$docs[]='<li>ТЗ <a href="ed_tz.php?action=1&id='.$f['id'].'" target="_blank">'.$f['code'].'</a>, статус '.$f['status_name'].'</li>';
		}
		
		//КПВ
		$sql=' select t.*,
		st.name as status_name
		 from kp_in as t 
		 left join document_status as st on st.id=t.status_id
		 
		 where t.lead_id="'.$id.'" and t.kind_id=0 and t.status_id not in(3,27) and t.is_confirmed=0';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++) {
			$f=mysqli_fetch_array($rs);
			
			
			$docs[]='<li>КП вх. <a href="ed_kp_in.php?action=1&id='.$f['id'].'" target="_blank">'.$f['code'].'</a>, статус '.$f['status_name'].'</li>';
		}
		
			 
			
		//КПИ
		$sql=' select t.*,
		st.name as status_name
		from kp_in  as t 
		 left join document_status as st on st.id=t.status_id
		where t.lead_id="'.$id.'" and t.kind_id=1 and t.status_id not in(3,27) and t.is_confirmed=0';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++) {
			$f=mysqli_fetch_array($rs);
			
			
			$docs[]='<li>КП исх. <a href="ed_kp_in.php?action=1&id='.$f['id'].'" target="_blank">'.$f['code'].'</a>, статус '.$f['status_name'].'</li>';
		}
		
		
		//БДР
		$sql='select t.*, l.*,
		st.name as status_name from bdr as t
		
		left join bdr_version as l on l.bdr_id=t.id and l.vid in (select max(ll.vid) as `s_q` from bdr_version as ll where ll.bdr_id=t.id )
		 left join document_status as st on st.id=t.status_id
		where t.lead_id="'.$id.'" and t.status_id not in(3,27) and l.is_confirmed=0';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++) {
				$f=mysqli_fetch_array($rs);
				
				$docs[]='<li>БДР <a href="ed_bdr.php?action=1&id='.$f['id'].'" target="_blank">'.$f['code'].'</a>, статус '.$f['status_name'].'</li>';
		}
				
		
		return $docs;	
		
	}
	
	
	
	//есть ли связанные документы по снятию 2 галочки?
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
	
	//есть ли связанные документы по снятию 3 галочки?
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
	
	
	
	
	//контроль прогноз. даты (для связанных с лидом тендеров)
	public function ControlPdateFinish($tender_id, $pdate_finish, $ptime_finish_h, $ptime_finish_m, &$reason){
		$can=true;	
		$reason=''; $reasons=array();
		
		$_ti=new Tender_Item;
		$ti=$_ti->GetItemById($tender_id);
		
		$tender_date=DateFromdmY(DateFromYmd($ti['pdate_finish']))+60*60*(int)substr($ti['ptime_finish'],0,2)+ 60*(int)substr($ti['ptime_finish'],3,2);
		
		$our_date=DateFromdmY(($pdate_finish)) +60*60*$ptime_finish_h+60*$ptime_finish_m;
		
		//tender_date должна быть больше, чем our_date, на 7 дней и выше!!!
		
		$diff=$our_date-$tender_date;
		
		if($diff<(1*24*60*60)){
			$can=$can&&false;
			$reasons[]='прогнозируемая дата заключения контракта по лиду должна быть позднее даты окончания рассмотрения тендера: '.DateFromYmd($ti['pdate_finish']).' '.$ti['ptime_finish'];
			
		//	echo date('d.m.Y H:i:s',$tender_date).', vs '.date('d.m.Y H:i:s',$our_date);
		
		
		}
		
		$reason=implode(', ', $reasons);
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
					$log->PutEntry($_result['id'],'смена статуса лида',NULL,950,NULL,'установлен статус '.$stat['name'],$item['id']);
				}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)){
					//смена статуса на 18
					$setted_status_id=18;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса лида',NULL,950,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
			}elseif(isset($new_params['is_confirmed_done'])&&isset($old_params['is_confirmed_done'])){
			
				if(($new_params['is_confirmed_done']==1)&&($old_params['is_confirmed_done']==0)){
					 //...
					
				}elseif(($new_params['is_confirmed_done']==0)&&($old_params['is_confirmed_done']==1)){
					 
					
						$setted_status_id=2;
						$this->Edit($id,array('status_id'=>$setted_status_id));
						
						$stat=$_stat->GetItemById($setted_status_id);
						$log->PutEntry($_result['id'],'смена статуса лида',NULL,950,NULL,'установлен статус '.$stat['name'],$item['id']);
					 
				}
				
			 
			
			 
			}elseif(isset($new_params['is_fulfiled'])&&isset($old_params['is_fulfiled'])){
				if(($new_params['is_fulfiled']==1)&&($old_params['is_fulfiled']==0)&&($old_params['status_id']==37)){
					//смена статуса c 37 на 34
					$setted_status_id=34;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса лида',NULL,950,NULL,'установлен статус '.$stat['name'],$item['id']);
					
				}elseif(($new_params['is_fulfiled']==0)&&($old_params['is_fulfiled']==1)&&($old_params['status_id']==34)){
					//смена статуса c 34 на 37
					$setted_status_id=37;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса лида',NULL,950,NULL,'установлен статус '.$stat['name'],$item['id']);
					
				}
			
			}elseif(isset($new_params['status_id'])&&isset($old_params['status_id'])){
				if(($new_params['status_id']==34)&&($old_params['status_id']!=34)){
						$this->Edit($id,array('is_confirmed_done'=>1,'confirm_done_pdate'=>time(), 'user_confirm_done_id'=>(int)$_result['id'],'probability'=>0));
						
						$stat=$_stat->GetItemById($new_params['status_id']);
						$log->PutEntry($_result['id'],'автоматическое утверждение выполнения лида',NULL,950,NULL,'автоматически утверждено выполнение лида '.$item['code'].' при установлении статуса '.$stat['name'],$item['id']);
						
						$log->PutEntry($_result['id'],'автоматическая смена вероятности заключения контракта лида',NULL,950,NULL,'лид '.$item['code'].', установлено значение 0% '.' при установлении статуса '.$stat['name'],$item['id']);
				}
				
				if(($new_params['status_id']==37)&&($old_params['status_id']!=37)){
						$this->Edit($id,array('is_confirmed_done'=>1,'confirm_done_pdate'=>time(), 'user_confirm_done_id'=>(int)$_result['id'],'probability'=>0));
						
						$stat=$_stat->GetItemById($new_params['status_id']);
						$log->PutEntry($_result['id'],'автоматическое утверждение выполнения лида',NULL,950,NULL,'автоматически утверждено выполнение лида '.$item['code'].' при установлении статуса '.$stat['name'],$item['id']);
						
						$log->PutEntry($_result['id'],'автоматическая смена вероятности заключения контракта лида',NULL,950,NULL,'лид '.$item['code'].', установлено значение 0% '.' при установлении статуса '.$stat['name'],$item['id']);
				}
				
				if(($new_params['status_id']==30)&&($old_params['status_id']!=30)){
						$this->Edit($id,array('is_confirmed_done'=>1,'confirm_done_pdate'=>time(), 'user_confirm_done_id'=>(int)$_result['id'],'probability'=>100));
						
						$stat=$_stat->GetItemById($new_params['status_id']);
						$log->PutEntry($_result['id'],'автоматическое утверждение выполнения лида',NULL,950,NULL,'автоматически утверждено выполнение лида '.$item['code'].' при установлении статуса '.$stat['name'],$item['id']);
						
						$log->PutEntry($_result['id'],'автоматическая смена вероятности заключения контракта лида',NULL,950,NULL,'лид '.$item['code'].', установлено значение 100% '.' при установлении статуса '.$stat['name'],$item['id']);
				}
				
				if(($new_params['status_id']==31)&&($old_params['status_id']!=31)){
						$this->Edit($id,array('is_confirmed_done'=>1,'confirm_done_pdate'=>time(), 'user_confirm_done_id'=>(int)$_result['id'],'probability'=>0));
						
						$stat=$_stat->GetItemById($new_params['status_id']);
						$log->PutEntry($_result['id'],'автоматическое утверждение выполнения лида',NULL,950,NULL,'автоматически утверждено выполнение лида '.$item['code'].' при установлении статуса '.$stat['name'],$item['id']);
						
						$log->PutEntry($_result['id'],'автоматическая смена вероятности заключения контракта лида',NULL,950,NULL,'лид '.$item['code'].', установлено значение 0% '.' при установлении статуса '.$stat['name'],$item['id']);
				}
				
				
				/*if(($new_params['status_id']==3)&&($old_params['status_id']!=3)){
					$this->Edit($id,array('probability'=>0));
					$stat=$_stat->GetItemById($new_params['status_id']);
					
					$log->PutEntry($_result['id'],'автоматическая смена вероятности заключения контракта лида',NULL,950,NULL,'лид '.$item['code'].', установлено значение 0% '.' при установлении статуса '.$stat['name'],$item['id']);
				}*/
				
			}
			
			
			/*elseif(isset($new_params['is_fulfiled'])&&isset($old_params['is_fulfiled'])){
				if(($new_params['is_fulfiled']==1)&&($old_params['is_fulfiled']==0)){
					
					if($old_params['status_id']==31){
						//не меняем: проигран значит проигран
					}else{
						//смена статуса на 10
						$setted_status_id=10;
						
						$this->Edit($id,array('status_id'=>$setted_status_id));
						
						$stat=$_stat->GetItemById($setted_status_id);
						$log->PutEntry($_result['id'],'смена статуса лида',NULL,931,NULL,'установлен статус '.$stat['name'],$item['id']);
					}
					
				}elseif(($new_params['is_fulfiled']==0)&&($old_params['is_fulfiled']==1)){
					if($old_params['status_id']==31){
						//не меняем: проигран значит проигран
					}else{
						$setted_status_id=26;
						$this->Edit($id,array('status_id'=>$setted_status_id));
						
						$stat=$_stat->GetItemById($setted_status_id);
						$log->PutEntry($_result['id'],'смена статуса лида',NULL,931,NULL,'установлен статус '.$stat['name'],$item['id']);
					}
				}
				
			}*/
		 
		
		
		//die();
	}
	 
	
	
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		
		//$res.=', контакт: '.$this->ConstructContacts($id, $item).', статус '.$stat['name'];
		$res.='лид, статус '.$stat['name'];
		
		//список к-тов
		$_sg=new Tender_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($id);
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
		 
		$res.='Лид '.$item['code'].', статус '.$stat['name'];
		
		//список к-тов
		$_sg=new lead_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($id);
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
	
	
	//найти список документов отправки ТЗ, КП, ...
	public function GetFactTZ($id, $item=NULL){
		$docs=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		$sql='select t.*, st.name  as status_name,
		cp.name as pos_name
		 from
		tz as t left join document_status as st on t.status_id=st.id
		left join catalog_position as cp on cp.id=t.pl_position_id and t.pl_position_id<>0
		where t.status_id<>3 and t.lead_id="'.$id.'"
		order by t.id';
		
		//echo $sql;
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->getresultnumrows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$elem='<a href="ed_tz.php?action=1&id='.$f['id'].'" target="_blank">'.$f['code'].'</a>, статус '.$f['status_name'].'';
			if($f['pos_name']!="") $elem.='; оборудование '.$f['pos_name'];
			
			//найдем КП по лиду и ТЗ
			$sql1='select t.*, st.name 
		 
		 as status_name from
		kp  as t left join document_status as st on t.status_id=st.id
	 
		where  t.status_id<>3 and t.lead_id="'.$id.'"
		order by t.id';
			$set1=new mysqlset($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->getresultnumrows();
			for($i1=0; $i1<$rc1; $i1++){
				$f1=mysqli_fetch_array($rs1);
				$elem.='; <a href="ed_kp.php?action=1&id='.$f1['id'].'" target="_blank">'.$f1['code'].'</a>, статус '.$f1['status_name'].'';
			}
			
			$docs[]=$elem;
		}
		
		return $docs;	
	}
	
	 
}


   

 


/***********************************************************************************************/
//определение класса записи
class Lead_Resolver{
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
		$this->instance=new Lead_Item;
	}
	
	 
}





// группа лидов
class  Lead_Group extends AbstractGroup {
	protected $_auth_result;
	protected $_view;
	protected $new_list; //список новых лидов для текущего пользователя с разбивкой их на группы
	
	//установка всех имен
	protected function init(){
		$this->tablename='lead';
		$this->pagename='leads.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		
		$this->_view=new Lead_ViewsGroup;
		$this->_auth_result=NULL;
		
		$this->new_list=NULL;
	} 
	
	
	//список лидов
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
	 	$prefix='' //18
		
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
		 
		
		
		 //получим список тех, кто может снять утверждение заполнения
		$_usg1=new UsersSGroup;
		$usg1=$_usg1->GetUsersByRightArr('w', 965);
		$sm->assign('can_unconfirm_users',$usg1); 
 
 
		
		 
		$_sg=new Lead_SupplierGroup;
		
		
		$sql='select distinct p.*,
		s.name as status_name, s.weight as status_weight,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login, p.confirm_done_pdate as confirm_shipping_pdate,
			 
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
		uf.name_s as confirmed_fulfil_name, uf.login as confirmed_fulfil_login,
		
		 eq.name as eq_name, kind.name as kind_name,
		 tender.code as tender_code, tender.pdate_placing as pdate_placing,
		 
		 cur.name as currency_name, cur.signature as currency_signature,
		 prod.name as producer_name
		 
					 
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
				 	 
				 ';
				
		$sql_count='select count(*) 
					 
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
			
			//просрочено или нет
			/*
			статус != 10 !=3 !=1
			и крайний срок !=null <now
			*/
			$expired=false;
			$exp_ptime=NULL;
			if($f['pdate_finish']!==""){
				$exp_ptime	= Datefromdmy( DateFromYmd($f['pdate_finish']))+24*60*60-1 + (int)substr($f['ptime_finish'], 0,2)*60*60 + (int)substr($f['ptime_finish'],3,2)*60;
				
			 
				 
//				echo date('d.m.Y H:i:s', $exp_ptime).'<br>';
			}
			
			if(
			
			($f['status_id']!=10) && ($f['status_id']!=3) && ($f['status_id']!=1) && ($f['status_id']!=30) && ($f['status_id']!=31) && ($f['status_id']!=32) && ($f['status_id']!=36)
			
			&&
			($exp_ptime!==NULL) && ($exp_ptime<time())
			
			) $expired=true;
			$f['expired']=$expired; 
			

			 
		//	$f['pdate_beg']=DateFromYmd($f['pdate_beg']);
			
		
			
			if($f['pdate_finish']!=="") $f['pdate_finish']=DateFromYmd($f['pdate_finish']);
			
			if($f['pdate_placing']!=="") $f['pdate_placing']=DateFromYmd($f['pdate_placing']);
			
			
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date('d.m.Y H:i:s', $f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			 
			if($f['confirm_shipping_pdate']!=0) $f['confirm_shipping_pdate']=date('d.m.Y H:i:s', $f['confirm_shipping_pdate']);
			else $f['confirm_shipping_pdate']='-';
			
			
			if($f['fulfiled_pdate']!=0) $f['fulfiled_pdate']=date('d.m.Y H:i:s', $f['fulfiled_pdate']);
			else $f['fulfiled_pdate']='-';
			 
				$_res=new Lead_Resolver();
				//$f['contact_value']=$_res->instance->ConstructContacts($f['id'], $f);
			 
			
			$f['can_annul']=$_res->instance->DocCanAnnul($f['id'],$reason,$f, $this->_auth_result)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			
			 
			
			
			 
				$f['suppliers']=$_sg->GetItemsByIdArr($f['id']);	
				
			 
			 
			
			 
				
				//можно утв, разутв вып работы
				
				if($f['is_confirmed_done']==1) $f['can_unconfirm_done']=$can_confirm_shipping&&($can_unconfirm_all_shipping/*||($f['manager_id']==$this->_auth_result['id'])*/)&&($f['is_fulfiled']==0);
				else $f['can_confirm_done']=$can_confirm_shipping&&($can_confirm_all_shipping/*||($f['manager_id']==$this->_auth_result['id'])*/)&&($f['is_fulfiled']==0);
				
				
				
				
				
				
				
				
				//можно утв/разутв. прием работы
				$f['can_confirm_fulfil']=$can_confirm_fulfil;
				$f['can_unconfirm_fulfil']=$can_unconfirm_fulfil;
				 
				if($f['is_fulfiled']==1) $f['can_unconfirm_fulfil']=$f['can_unconfirm_fulfil']&&$_res->instance->DocCanUnconfirmFulfil($f['id'],$reason,$f);
				else  $f['can_confirm_fulfil']=$f['can_confirm_fulfil']&&$_res->instance->DocCanConfirmFulfil($f['id'],$reason,$f);
				
			 
		 
			$f['max_price_formatted']=number_format($f['max_price'],2,'.',' ');//sprintf("", $f['max_price']);
			
			//является ли новым лидом?
			/* $do_check_new=false;
			if($man->CheckAccess($this->_auth_result['id'],'w',957)){
			//не утв вып - мигает у рук-ля
			 
				if(($f['is_confirmed_done']==1)&&($f['is_fulfiled']==0)&&in_array($f['status_id'], array(30,31,34,37))) $do_check_new=true;
				
				
			}elseif(($f['manager_id']==$this->_auth_result['id'])&&($f['status_id']==2)){
				//if(($f['manager_id']==0)&&($f['is_confirmed']==1)) $do_check_new=true;
				$do_check_new=true;
			
			}
			
			
			
			if( $do_check_new){
				$sql2='select count(*)  from lead_view where user_id="'.$this->_auth_result['id'].'" and lead_id="'.$f['id'].'" ';  
				$set2=new mysqlSet($sql2);
				$rs2=$set2->GetResult();
				
				$g=mysqli_fetch_array($rs2);
				if((int)$g[0]==0) $f['is_new']=true;
			}*/
			
			//получить блоки "новый документ"
			$f['new_blocks']=$this->DocumentNewBlocks($f['id'], $this->_auth_result['id']);
			 
			
			$f['is_to_fulfil']=false;
			if($can_confirm_fulfil&&($f['is_fulfiled']==0)&&($f['is_confirmed_done']==1)){
				$f['is_to_fulfil']=true;
			}
			
			
			
			
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
		
		
		$_tks=new LeadKindGroup;
		
		$tks1=$_tks->GetItemsArr();
		$tks[]=array('id'=>'', 'name'=>'-все-');
		foreach($tks1 as $k=>$v) $tks[]=$v;
		$sm->assign('kinds', $tks);
		
		 //типы оборудования 
		$_eqs=new Lead_EqTypeGroup;
		$eqs1=$_eqs->GetItemsArr();
		$eqs[]=array('id'=>'', 'name'=>'-все-');
		foreach($eqs1 as $k=>$v) $eqs[]=$v;
		$sm->assign('eqs', $eqs);
		
		
		//производители
		$_prods=new PlProdGroup;
		$prods1=$_prods->GetItemsArr();
		$prods[]=array('id'=>'', 'name'=>'-все-');
		$prods[]=array('id'=>'0', 'name'=>'-не в линейке-');
		foreach($prods1 as $k=>$v) $prods[]=$v;
		$sm->assign('prods', $prods);
		
		//состояния
		$states=array();
		$states[]=array('id'=>'', 'name'=>'-все-');
		$states[]=array('id'=>'0', 'name'=>'холодный');
		$states[]=array('id'=>'1', 'name'=>'теплый');
		$states[]=array('id'=>'2', 'name'=>'горячий');
		
		$sm->assign('states', $states);
		
		$sm->assign('can_confirm_fulfil', $can_confirm_fulfil);
		
		
		$sm->assign('can_confirm_all_shipping', $can_confirm_all_shipping);
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$sm->assign('prefix',$prefix);
		 
		
		$sm->assign('can_edit',$can_edit);
	 
 
		 //показ конфигурации
		$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		
	 	
		
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
	
	
	
	
	//список ID лидов, которых может видеть текущий сотрудник
	public function GetAvailableLeadIds($user_id){
		$arr=array();
		
		$_man=new DiscrMan;
		
		//проверить супердоступ
		//если он есть - то это все лиды
		if($_man->CheckAccess($user_id,'w',953)){
			$sql='select id from lead';
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
		}else{
			//свои
			$sql='select id from lead where manager_id="'.$user_id.'" '; // or created_id="'.$user_id.'" ';	
			
			//новые лиды без менеджера
			if($_man->CheckAccess($user_id,'w',954)){
				$sql.=' or manager_id=0 ';	
			}
			
			//все лиды с менеджером
			if($_man->CheckAccess($user_id,'w',955)){
				$sql.=' or manager_id<>0 ';	
			}
			
			//алешкевич видит т и л чепиковой
			if($user_id==63){
				$sql.=' or manager_id=75 ';	
			}
			
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
		if($_man->CheckAccess($user_id,'w',953)){
			 
			
			return true;
		}else{
			//свои
			 
			//новые лиды без менеджера
			if(($manager_id==0)&&$_man->CheckAccess($user_id,'w',954)){
				//$sql.=' or manager_id=0 ';	
				return true;
			}
			
			//все лиды с менеджером
			if(($manager_id!=0)&&$_man->CheckAccess($user_id,'w',955)){
				return true;
			}
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
	public function CountNewLeads($user_id){
		
		$tender_ids=$this->GetAvailableLeadIds($user_id);
		
		 
		
		
		$man=new DiscrMan;
		
		//
		if($man->CheckAccess($user_id,'w',957)){
			// мигает у рук-ля
			$sql='select count(*) from 
			lead as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			and t.is_confirmed_done=1
			and t.is_fulfiled=0
			and t.status_id in (30,31,34,37)
			and t.id not in(select lead_id from lead_view where user_id="'.$user_id.'")
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
			
		  
				$f=mysqli_fetch_array($rs);	
		}else{
			
			$sql='select count(*) from 
			lead as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			and t.manager_id="'.$user_id.'"
			and t.status_id=2
			and t.id not in(select lead_id from lead_view where user_id="'.$user_id.'")
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
	
	
	//число новых лидов - расширенная индикация
	public function CountNewLeadsExtended($user_id, $do_iconv=true){
		$data=array();
			
		$tender_ids=$this->GetAvailableLeadIds($user_id);
		
		  
		$man=new DiscrMan;
		
		
		//
		if($man->CheckAccess($user_id,'w',957)){
			// мигает у рук-ля
			$sql='select count(*) from 
			lead as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			and t.is_confirmed_done=1
			and t.is_fulfiled=0
			and t.status_id in (30,31,34,37)
			/*and t.id not in(select lead_id from lead_view where user_id="'.$user_id.'")*/
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
		
	  
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select t.* from 
				lead as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				and t.is_confirmed_done=1
				and t.is_fulfiled=0
				and t.status_id in (30,31,34,37)
				/*and t.id not in(select lead_id from lead_view where user_id="'.$user_id.'")*/
				order by t.id asc limit 1
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_lead.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_att',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'Утвердите выполнение работы!'
				
				);
			}
		}else{
			
			$sql='select count(*) from 
			lead as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			and t.manager_id="'.$user_id.'"
			and t.status_id=2
			/*and t.id not in(select lead_id from lead_view where user_id="'.$user_id.'")*/
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select t.* from 
				lead as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				and t.manager_id="'.$user_id.'"
				and t.status_id=2
				/*and t.id not in(select lead_id from lead_view where user_id="'.$user_id.'")*/
				order by t.id asc limit 1
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_lead.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_to_work',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'Примите лиды в работу!'
				
				);
			}
		}
		
		
		//Индикация новых комментов
		$_hg=new Lead_HistoryGroup;
		
		$count_new=$_hg->CalcNew($user_id,$tender_ids, $first_id);
		if($count_new>0){
			$data[]=array(
					'class'=>'menu_new_m',
					'num'=>$count_new,
					'first_url'=>'ed_lead.php?action=1&id='.$first_id.'&from_begin=1#new_comment',
					'comment'=>'Новые комментарии'
				
				);
		}
		
		
		//Новые ТЗ
		$_ui=new UserSItem;
		$user=$_ui->GetItemById($user_id);
		 
		
		// отдел закупок: проставлена вторая галочка, должно мигать КПВ
		if(($user['department_id']==4)||
			$man->CheckAccess($user_id,'w',1028)	
		){
			
			$sql='select count(*) from 
			tz as t
			
			where t.lead_id in ('.implode(', ',$tender_ids).')
			 
			and t.is_not_eq=1
			and t.force_eq_in=0
			and t.status_id not in(3, 27)
			and t.id not in(select distinct tz_id from kp_in where kind_id=0 and is_confirmed=1)
			
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select t.* from 
					tz as t
				
				where t.lead_id in ('.implode(', ',$tender_ids).')
				 
				and t.is_not_eq=1
				and t.force_eq_in=0
				and t.status_id not in(3, 27)
				and t.id not in(select distinct tz_id from kp_in where kind_id=0 and is_confirmed=1)
				order by t.id asc limit 1
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_tz.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_tz',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'Создайте входящее КП по ТЗ!'
				
				);
			}
		}
	
		
		//создайте КП ПО ТЗ
		if( 
			$man->CheckAccess($user_id,'w',696)	
		){
			$sql='select count(*) from 
			tz as t
			
			where t.lead_id in ('.implode(', ',$tender_ids).')
			 
				and t.force_eq_in=1
				and t.id not in(select distinct tz_id from kp where status_id not in(3,27) and is_confirmed_price=1 and tz_id=t.id)
			
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select t.* from 
					tz as t
				
				where t.lead_id in ('.implode(', ',$tender_ids).')
				 
					and t.force_eq_in=1
				and t.id not in(select distinct tz_id from kp where status_id not in(3,27) and is_confirmed_price=1 and tz_id=t.id)
				order by t.id asc limit 1
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_tz.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_kp',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'Создайте КП по ТЗ!'
				
				);
			}
		}
		
		
		//'Отдел закупок: утвердите соответствие КП ТЗ!'
		if(($user['department_id']==4)||
			$man->CheckAccess($user_id,'w',1015)	
		){
			$sql='select count(*) from 
				kp_in as t
				
				where t.lead_id in ('.implode(', ',$tender_ids).')
				and t.kind_id=0 
				and t.is_confirmed=1
				and t.status_id not in(3,27)
				and  t.fulful_kp=0
				';
				
				
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select t.* from 
					kp_in as t
				
				where t.lead_id in ('.implode(', ',$tender_ids).')
				and t.kind_id=0
				and t.is_confirmed=1
				and t.status_id not in(3,27)
				and  t.fulful_kp=0
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_kp_in.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_kpv',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'Отдел закупок: утвердите соответствие КП ТЗ!'
				
				);
			}
			
		}
		//Технический отдел: утвердите соответствие КП ТЗ!
		if(($user['department_id']==7)||
			$man->CheckAccess($user_id,'w',1027)	
		){
			$sql='select count(*) from 
				kp_in as t
				
				where t.lead_id in ('.implode(', ',$tender_ids).')
				 and t.kind_id=0
				and t.is_confirmed=1
				and t.status_id not in(3,27)
				and  t.fulful_kp1=0
				';
				
				
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select t.* from 
					kp_in as t
				
				where t.lead_id in ('.implode(', ',$tender_ids).')
				 and t.kind_id=0
				and t.is_confirmed=1
				and t.status_id not in(3,27)
				and  t.fulful_kp1=0
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_kp_in.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_kpv',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'Технический отдел: утвердите соответствие КП ТЗ!'
				
				);
			}
			
		}
		
		
		//менеджер: есть КПв, проставлены обе галочки, нет КПи с первой галочкой
		//переход - в карту КПв
		if( 
			$man->CheckAccess($user_id,'w',1018)	
		){
			$sql='select count(*) from 
					kp_in as t
					
					where t.lead_id in ('.implode(', ',$tender_ids).')
					/*and t.lead_id in (select id from lead where manager_id="'.$user_id.'")*/
					and t.kind_id=0
					and t.is_confirmed=1
					and t.status_id not in(3,27)
					and  t.fulful_kp<>0
					and  t.fulful_kp1<>0
					and id not in(select distinct kp_in_id from kp_in  where kp_in_id=t.id and is_confirmed=1)
			
			 
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
				
				where t.lead_id in ('.implode(', ',$tender_ids).')
				/*and t.lead_id in (select id from lead where manager_id="'.$user_id.'")*/
				and t.kind_id=0
				and t.is_confirmed=1
				and t.status_id not in(3,27)
				and  t.fulful_kp<>0
				and  t.fulful_kp1<>0
				and id not in(select distinct kp_in_id from kp_in where kp_in_id=t.id and is_confirmed=1)
				order by t.id asc limit 1
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_kp_in.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_kpi',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'Создайте исх. КП по вх. КП!'
				
				);
			}
		}
		
		//координаторы ДПИМ: новые исходящие КП
		//переход в карту КПИ
		if(
		(/*($user['main_department_id']==1) 
		&&	*/($user['position_id']==36))
		||($man->CheckAccess($user_id,'w',1039))
		){
			$sql='select count(*) from 
					kp_in as t
					
					where t.lead_id in ('.implode(', ',$tender_ids).')
					 
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
					
					where t.lead_id in ('.implode(', ',$tender_ids).')
					 
					and t.kind_id=1
					and t.is_confirmed=1
					and t.status_id not in(3,27)
					and t.id not in(select distinct b.kp_out_id from bdr as b where b.kp_out_id=t.id  and b.status_id not in(1, 3, 27))
				order by t.id asc limit 1
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_kp_in.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_bdr',
					'num'=>(int)$f[0],
					'first_url'=>$url,
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
					
					where t.lead_id in ('.implode(', ',$tender_ids).')
					 
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
					
					where t.lead_id in ('.implode(', ',$tender_ids).')
					 
					and t.kind_id=1
					and t.is_confirmed=1
					and t.status_id not in(3,27)
					and t.id in(select distinct b.kp_out_id from bdr as b where b.kp_out_id=t.id  and b.status_id  in(40))
					and t.is_prepared_kp=0
				order by t.id asc limit 1
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_kp_in.php?action=1&id='.$g['id'].'&from_begin=1';
				
				$data[]=array(
					'class'=>'menu_new_kpi',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'Подготовьте файл КП, вложите его в КП исх., утвердите вложение!'
				
				);
			}	
			 
		}
		
		
		//ФИН служба или права 1057:
		//есть утв. БДР, но не проставлена галочка проверки
		if(($user['main_department_id']==5)||
			$man->CheckAccess($user_id,'w',1057)	
		){
			$sql='select
			
			count(distinct t.id) 
		  	from bdr as t
			 left join bdr_version as l on l.bdr_id=t.id and l.vid in (select max(ll.vid) as `s_q` from bdr_version as ll where ll.bdr_id=t.id )
			 
			where t.lead_id in ('.implode(', ',$tender_ids).')
			 
			and l.is_confirmed=1
			and l.is_confirmed_version=0
			and t.status_id not in(3, 27)
			 
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select distinct t.*, l.*  
						from bdr as t
			 left join bdr_version as l on l.bdr_id=t.id and l.vid in (select max(ll.vid) as `s_q` from bdr_version as ll where ll.bdr_id=t.id )
			 	
				where t.lead_id in ('.implode(', ',$tender_ids).')
				 
				and l.is_confirmed=1
				and l.is_confirmed_version=0
				and t.status_id not in(3, 27)
				order by t.id asc   limit 1
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_bdr.php?action=1&id='.$g['id'].'&from_begin=1';
				 
				$data[]=array(
					'class'=>'menu_new_bdr',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'Финансовая служба: утвердите проверку БДР!'
				
				);
			}	
		}
		
		
		//гендир или права 1057:
		//есть утв. БДР, но не проставлена галочка проверки
		if(($user['main_department_id']==5)||
			$man->CheckAccess($user_id,'w',1055)	
		){
			$sql='select
			
			count(distinct t.id) 
		  		from bdr as t
			 left join bdr_version as l on l.bdr_id=t.id and l.vid in (select max(ll.vid) as `s_q` from bdr_version as ll where ll.bdr_id=t.id )
			 
			 
			where t.lead_id in ('.implode(', ',$tender_ids).')
			 
			and l.is_confirmed=1
			and l.is_confirmed_version1=0
			and t.status_id not in(3, 27)
			 
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select distinct t.*, l.*  
						from bdr as t
			 left join bdr_version as l on l.bdr_id=t.id and l.vid in (select max(ll.vid) as `s_q` from bdr_version as ll where ll.bdr_id=t.id )
			 
			 
				where t.lead_id in ('.implode(', ',$tender_ids).')
				 
				and l.is_confirmed=1
				and l.is_confirmed_version1=0
				and t.status_id not in(3, 27)
				order by t.id asc limit 1
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$g=mysqli_fetch_array($rs);		
				$url='ed_bdr.php?action=1&id='.$g['id'].'&from_begin=1';
				 
				$data[]=array(
					'class'=>'menu_new_bdr',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'Генеральный директор: утвердите проверку БДР!'
				
				);
			}	
		}
		
		
		
		/*$data[]=array(
					'class'=>'menu_new_m',
					'num'=>(int)$f[0],
					'first_url'=>$url,
					'comment'=>'Примите лиды в работу!'
				
				);
		*/
		
		
		
		if($do_iconv) foreach($data as $k=>$v) $data[$k]['comment']=iconv('windows-1251', 'utf-8', $v['comment']);
		
		return $data;
	}
	
	//попадает ли текущий лид при текущем пользователе в индикацию, если попадает - вернуть данные для построения блока
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
		
		$tender_ids=$this->GetAvailableLeadIds($user_id);
		
		  
		$man=new DiscrMan;
		
		
		//
		if($man->CheckAccess($user_id,'w',957)){
			// мигает у рук-ля
			$sql='select count(*) from 
			lead as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			and t.is_confirmed_done=1
			and t.is_fulfiled=0
			and t.status_id in (30,31,34,37)
			/*and t.id not in(select lead_id from lead_view where user_id="'.$user_id.'")*/
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
		
	  
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим список документов
				$sql='select t.id from 
				lead as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				and t.is_confirmed_done=1
				and t.is_fulfiled=0
				and t.status_id in (30,31,34,37)
				/*and t.id not in(select lead_id from lead_view where user_id="'.$user_id.'")*/
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
					'sub_ids'=>array(),
					'url'=>'ed_lead.php?action=1&id=',
					'comment'=>'Утвердите выполнение работы!'
				
				);
			}
		}else{
			
			$sql='select count(*) from 
			lead as t
			
			where t.id in ('.implode(', ',$tender_ids).')
			and t.manager_id="'.$user_id.'"
			and t.status_id=2
			/*and t.id not in(select lead_id from lead_view where user_id="'.$user_id.'")*/
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select t.id from 
				lead as t
				
				where t.id in ('.implode(', ',$tender_ids).')
				and t.manager_id="'.$user_id.'"
				and t.status_id=2
				/*and t.id not in(select lead_id from lead_view where user_id="'.$user_id.'")*/
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
					'class'=>'reestr_menu_to_work',
					'num'=>(int)$rc,
					'doc_ids'=>$doc_ids,
					'doc_counters'=>array(),
					'sub_ids'=>array(),
					'url'=>'ed_lead.php?action=1&id=',
					'comment'=>'Примите лид в работу!'
				
				);
			}
		}
		
		//новые комменты
		//индикация новых комментов	
		$check=time()-7*24*60*60;
		$sql='select count(*) from lead_history where user_id<>"'.$user_id.'" and user_id<>0
		and sched_id in(
			'.implode(', ',$tender_ids).'
		)
		and sched_id not in(select id from lead where status_id=3)
		 
		and id not in(select history_id from lead_history_view where user_id="'.$user_id.'" )
		and pdate>"'.$check.'"
		';
		//echo $sql.'<br>';
		$set=new mysqlSet($sql); 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$f=mysqli_fetch_array($rs);
		
		if((int)$f[0]>0){
			//получим список документов
			$doc_ids=array(); 	$doc_counters=array();
			
			$sql='select distinct t.id, count(h.id) as s_q from lead_history as h
			inner join lead as t on t.id=h.sched_id
			 where h.user_id<>"'.$user_id.'" and h.user_id<>0
			and h.sched_id in(
				'.implode(', ',$tender_ids).'
			)
			and h.sched_id not in(select id from lead where status_id=3)
			 
			and h.id not in(select history_id from lead_history_view where user_id="'.$user_id.'" )
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
				'sub_ids'=>array(),
				'doc_counters'=>$doc_counters,
				'url'=>'ed_lead.php?action=1&id={id}&from_begin=1#new_comment',
				'comment'=>'новые комментарии'
			
			);
		}
		
		//новые ТЗ
		$_ui=new UserSItem;
		$user=$_ui->GetItemById($user_id);
	 
		
		// отдел закупок: проставлена вторая галочка, должно мигать КПВ
		if(($user['department_id']==4)||
			$man->CheckAccess($user_id,'w',1028)	
		){
			
			$sql='select count(*) from 
			tz as t
			
			where t.lead_id in ('.implode(', ',$tender_ids).')
			 
			and t.is_not_eq=1
			and t.force_eq_in=0
			and t.id not in(select distinct tz_id from kp_in where kind_id=0 and is_confirmed=1)
			
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select t.* from 
					tz as t
				
				where t.lead_id in ('.implode(', ',$tender_ids).')
				 
				and t.is_not_eq=1
				and t.force_eq_in=0
				and t.status_id not in(3, 27)
				and t.id not in(select distinct tz_id from kp_in where kind_id=0 and is_confirmed=1)
				order by t.id asc  
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array(); $doc_counters=array();
				for($j=0; $j<$rc; $j++){ 
					$g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['lead_id'];
					$doc_counters[]=(int)$g['s_q'];
				}
				
				
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_tz',
					'num'=>(int)$rc,
					'doc_ids'=>$doc_ids,
					'sub_ids'=>array(),
					'doc_counters'=>$doc_counters,
					'url'=>'ed_lead.php?action=1&id={id}&show_tz=1',
					'comment'=>'Создайте входящее КП по ТЗ!'
				
				);
			}
		}
	
		//отправьте КП по ТЗ
		if( 
			$man->CheckAccess($user_id,'w',696)	
		){
			$sql='select count(*) from 
			tz as t
			
			where t.lead_id in ('.implode(', ',$tender_ids).')
			 
			and t.force_eq_in=1
			and t.id not in(select distinct tz_id from kp where status_id not in(3,27) and is_confirmed_price=1 and tz_id=t.id)
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select distinct t.lead_id, count(distinct t.id) as s_q from 
					tz as t
				
				where t.lead_id in ('.implode(', ',$tender_ids).')
				 
				and t.force_eq_in=1
				and t.id not in(select distinct tz_id from kp where status_id not in(3,27) and is_confirmed_price=1 and tz_id=t.id)
				group by t.lead_id
				order by t.id asc  
				'; 
				
			 
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array(); $doc_counters=array();
				for($j=0; $j<$rc; $j++){ 
					$g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['lead_id'];
					$doc_counters[]=(int)$g['s_q'];
				}
				
				 
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_kp',
					'num'=>(int)$rc,
					'doc_ids'=>$doc_ids,
					'sub_ids'=>array(),
					'doc_counters'=>$doc_counters,
					'url'=>'ed_lead.php?action=1&id={id}&show_tz=1',
					'comment'=>'Создайте КП по ТЗ!'
					
					 
				
				);
			}
		}
		
		
		//КП входящее создано, технический отдел и отдел закупок должен утвердить соответствие кп тз
		if(($user['department_id']==4)||
			$man->CheckAccess($user_id,'w',1015)	
		){
			$sql='select count(*) from 
				 
					kp_in as t
				
				where t.lead_id in ('.implode(', ',$tender_ids).')
				and t.kind_id=0 
				and t.is_confirmed=1
				and t.status_id<>3
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
				
				where t.lead_id in ('.implode(', ',$tender_ids).')
				 and t.kind_id=0
				and t.is_confirmed=1
				and t.status_id<>3
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
				 
					$doc_ids[]=$g['lead_id'];
					$sub_ids[]=$g['id'];
				}	
				
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_kpv',
					'num'=>(int)$rc,
						'doc_ids'=>$doc_ids,
						'doc_counters'=>array(),
						'sub_ids'=>$sub_ids,
						'url'=>'ed_kp_in.php?action=1&id={sub_id}',
					'comment'=>'Отдел закупок: утвердите соответствие КП ТЗ!'
				
				);
			}
		}
		
		if(($user['department_id']==7)||
			$man->CheckAccess($user_id,'w',1027)	
		){
			$sql='select count(*) from 
				 
					kp_in as t
				
				where t.lead_id in ('.implode(', ',$tender_ids).')
				and t.kind_id=0 
				and t.is_confirmed=1
				and t.status_id<>3
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
				
				where t.lead_id in ('.implode(', ',$tender_ids).')
				and t.kind_id=0 
				and t.is_confirmed=1
				and t.status_id<>3
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
				 
					$doc_ids[]=$g['lead_id'];
					$sub_ids[]=$g['id'];
				}	
				
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_kpv',
					'num'=>(int)$rc,
						'doc_ids'=>$doc_ids,
						'doc_counters'=>array(),
						'sub_ids'=>$sub_ids,
						'url'=>'ed_kp_in.php?action=1&id={sub_id}',
					'comment'=>'Технический отдел: утвердите соответствие КП ТЗ!'
				
				);
			}
		}
		
		
		
		//менеджер: есть КПв, проставлены обе галочки, нет КПи с первой галочкой
		//есть права на создание КПИ
		//переход - в карту КПв
		if( 
			$man->CheckAccess($user_id,'w',1018)	
		){
			$sql='select count(*) from 
					kp_in as t
					
					where t.lead_id in ('.implode(', ',$tender_ids).')
					/*and t.lead_id in (select id from lead where manager_id="'.$user_id.'")*/
					and t.kind_id=0
					and t.is_confirmed=1
					and t.status_id not in(3,27)
					and  t.fulful_kp<>0
					and  t.fulful_kp1<>0
					and id not in(select id from kp_in where kp_in_id=t.id and is_confirmed=1)
			
			 
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
				
				where t.lead_id in ('.implode(', ',$tender_ids).')
				/*and t.lead_id in (select id from lead where manager_id="'.$user_id.'")*/
				and t.kind_id=0
				and t.is_confirmed=1
				and t.status_id not in(3,27)
				and  t.fulful_kp<>0
				and  t.fulful_kp1<>0
				and id not in(select id from kp_in where kp_in_id=t.id and is_confirmed=1)
				order by t.id asc  
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array(); $sub_ids=array();
					for($j=0; $j<$rc; $j++){ 
						$g=mysqli_fetch_array($rs);		
					 
						$doc_ids[]=$g['lead_id'];
						$sub_ids[]=$g['id'];
					}	
				
				$this->new_list[]=array(
					 
					'class'=>'reestr_menu_new_kpi',
						'num'=>(int)$rc,
							'doc_ids'=>$doc_ids,
							'doc_counters'=>array(),
							'sub_ids'=>$sub_ids,
							'url'=>'ed_kp_in.php?action=1&id={sub_id}',
					'comment'=>'Создайте исх. КП по вх. КП!'
				
				);
			}
		}
		
		//координаторы ДПИМ: новые исходящие КП
		//переход в карту КПИ
		if(
		(($user['main_department_id']==1) 
		&&	($user['position_id']==36))
		||($man->CheckAccess($user_id,'w',1039))
		){
			$sql='select count(*) from 
					kp_in as t
					
					where t.lead_id in ('.implode(', ',$tender_ids).')
					 
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
					
					where t.lead_id in ('.implode(', ',$tender_ids).')
					 
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
				 
					$doc_ids[]=$g['lead_id'];
					$sub_ids[]=$g['id'];
				}	
			
			$this->new_list[]=array(
				 
				'class'=>'reestr_menu_new_bdr',
					'num'=>(int)$rc,
						'doc_ids'=>$doc_ids,
						'doc_counters'=>array(),
						'sub_ids'=>$sub_ids,
						'url'=>'ed_kp_in.php?action=1&id={sub_id}',
				'comment'=>'Создайте БДР по исх. КП!'
			
			);
			}	
			
			
		}
		
		
		//координаторы ДПИМ, или права утв вложения файла КПИ: приложите файл к КПИ, утвердите вложение
		//переход в карту КПИ
		if(
		(($user['main_department_id']==1) 
		&&	($user['position_id']==36))
		||($man->CheckAccess($user_id,'w',1057))
		){
			$sql='select count(*) from 
					kp_in as t
					
					where t.lead_id in ('.implode(', ',$tender_ids).')
					 
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
					
					where t.lead_id in ('.implode(', ',$tender_ids).')
					 
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
				 
					$doc_ids[]=$g['lead_id'];
					$sub_ids[]=$g['id'];
				}	
			
			$this->new_list[]=array(
				 
				'class'=>'reestr_menu_new_kpi',
					'num'=>(int)$rc,
						'doc_ids'=>$doc_ids,
						'doc_counters'=>array(),
						'sub_ids'=>$sub_ids,
						'url'=>'ed_kp_in.php?action=1&id={sub_id}',
				'comment'=>'Подготовьте файл КП, вложите его в КП исх., утвердите вложение!'
			
			);
			}	
			
			
		}
		
		
		//ФИН служба или права 1057:
		//есть утв. БДР, но не проставлена галочка проверки
		if(($user['main_department_id']==5)||
			$man->CheckAccess($user_id,'w',1057)	
		){
			$sql='select
			
			count(distinct t.id) 
		  	from bdr as t
			 left join bdr_version as l on l.bdr_id=t.id and l.vid in (select max(ll.vid) as `s_q` from bdr_version as ll where ll.bdr_id=t.id )
			 
			where t.lead_id in ('.implode(', ',$tender_ids).')
			 
			and l.is_confirmed=1
			and l.is_confirmed_version=0
			and t.status_id not in(3, 27)
			 
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select distinct t.*, l.*  
						from bdr as t
			 left join bdr_version as l on l.bdr_id=t.id and l.vid in (select max(ll.vid) as `s_q` from bdr_version as ll where ll.bdr_id=t.id )
			 	
				where t.lead_id in ('.implode(', ',$tender_ids).')
				 
				and l.is_confirmed=1
				and l.is_confirmed_version=0
				and t.status_id not in(3, 27)
				order by t.id asc  
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array(); $sub_ids=array();
				for($j=0; $j<$rc; $j++){ 
				    $g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['lead_id'];
					$sub_ids[]=$g['id'];
					
				}	
				 
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_bdr',
					'num'=>(int)$rc,
					'doc_ids'=>$doc_ids,
					'sub_ids'=>$sub_ids,
					'doc_counters'=>array(),
					'url'=>'ed_bdr.php?action=1&id={sub_id}',
					'comment'=>'Финансовая служба: утвердите проверку БДР!'
				
				);
			}	
		}
		
		
		//гендир или права 1057:
		//есть утв. БДР, но не проставлена галочка проверки
		if(($user['main_department_id']==5)||
			$man->CheckAccess($user_id,'w',1055)	
		){
			$sql='select
			
			count(distinct t.id) 
		  		from bdr as t
			 left join bdr_version as l on l.bdr_id=t.id and l.vid in (select max(ll.vid) as `s_q` from bdr_version as ll where ll.bdr_id=t.id )
			 
			 
			where t.lead_id in ('.implode(', ',$tender_ids).')
			 
			and l.is_confirmed=1
			and l.is_confirmed_version1=0
			and t.status_id not in(3, 27)
			 
			';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			   
			$f=mysqli_fetch_array($rs);	
			
			if((int)$f[0]>0){
				//получим первый УРЛ, сформируем выходной элемент
				$sql='select distinct t.*, l.*  
						from bdr as t
			 left join bdr_version as l on l.bdr_id=t.id and l.vid in (select max(ll.vid) as `s_q` from bdr_version as ll where ll.bdr_id=t.id )
			 
			 
				where t.lead_id in ('.implode(', ',$tender_ids).')
				 
				and l.is_confirmed=1
				and l.is_confirmed_version1=0
				and t.status_id not in(3, 27)
				order by t.id asc  
				';
				
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				$doc_ids=array(); $sub_ids=array();
				for($j=0; $j<$rc; $j++){ 
				    $g=mysqli_fetch_array($rs);		
				 
					$doc_ids[]=$g['lead_id'];
					$sub_ids[]=$g['id'];
					
				}	
				 
				$this->new_list[]=array(
					'class'=>'reestr_menu_new_bdr',
					'num'=>(int)$rc,
					'doc_ids'=>$doc_ids,
					'sub_ids'=>$sub_ids,
					'doc_counters'=>array(),
					'url'=>'ed_bdr.php?action=1&id={sub_id}',
					'comment'=>'Генеральный директор: утвердите проверку БДР!'
				
				);
			}	
		}
		
		
		
		
		//var_dump($this->new_list);	
	}
	
	
	//автоматическое аннулирование
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		
		$log=new ActionLog();
		
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
		}
		
	}
	
}





   












//вид лида
class LeadKindItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='lead_kind';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_active';	
		//$this->subkeyname='mid';	
	}
	

}

//группа видов лида
class LeadKindGroup extends AbstractGroup {

	//установка всех имен
	protected function init(){
		$this->tablename='lead_kind';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_active';		
		
		
		
	}
	
	//список позиций
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
	
}





// справочник контактов к-та
class Lead_SupplierContactGroup extends SupplierContactGroup {
	
	
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
		
                // KSK - 01.04.2016
                // добавляем условие выбора записей для отображения is_shown=1
		//$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" order by id asc');
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and '.$this->vis_name.'=1 order by id asc');
		
		
		
		$_sdg=new Lead_SupplierContactDataGroup;
		
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
class Lead_SupplierContactDataGroup extends SupplierContactDataGroup {
	
	
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











  



// users S
class Lead_UsersSGroup extends UsersSGroup {
	protected $group_id;
	public $instance;
	public $pagename;

	
	//установка всех имен
	protected function init(){
		$this->tablename='user';
		$this->pagename='users_s.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_active';		
		$this->group_id=1;
		$this->instance=new UserSItem;

	}
	
	 
	
	
	
	
	//Отбор сотрудников для задачи и других карт
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



   







/*************************************************************************************************/


//контрагенты лида
class Lead_SupplierItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='lead_suppliers';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}



class Lead_SupplierGroup extends AbstractGroup {
	 
	public $pagename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='lead_suppliers';
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
		$_sc=new Lead_ScGroup;
		
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
		$_kpi=new Lead_SupplierItem; $_cg=new Lead_ScGroup;
		
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
		$_kpi=new Lead_SupplierItem;  
		
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



/***********************************************************************************************/


//тип оборудования 
class Lead_EqTypeItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='tender_eq_types';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}



class Lead_EqTypeGroup extends AbstractGroup {
	 
	public $pagename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='tender_eq_types';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	
	//список позиций
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
	
	
}


 
 

/****************************************************************************************************/
//контакты контрагента по лиду	
class Lead_ScItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='lead_suppliers_contacts';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sc_id';	
	}
	
}



class Lead_ScGroup extends AbstractGroup {
	 
	public $pagename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='lead_suppliers_contacts';
		$this->pagename='view.php';		
		$this->subkeyname='sc_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	//список, какие есть
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





/***********************************************************************************************/


//причина отказа
class Lead_FailItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='lead_fail';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}



class Lead_FailGroup extends AbstractGroup {
	 
	public $pagename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='lead_fail';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	
	//список позиций
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
//счетчик отказов действий менеджера
class Lead_CountsItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='lead_counts';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}



/****************************************************************************************************/
//маркер о факте проверки для всплывающих окон
class Lead_MarkerItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='lead_marker';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}

//класс для получения данных для всплывающих окон
class Lead_PopupGroup extends AbstractGroup{
	
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




//запись в журнал о входе/выходе лида из статуса "в работе"
class Lead_WorkingItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='lead_working';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sched_id';	
	}
	
}


//список записей в журнал о входе/выходе лида из статуса "в работе"
class Lead_WorkingGroup extends Abstract_WorkingKindGroup {
	 
	
	//установка всех имен
	protected function init(){
		$this->tablename='lead_working';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		
		 
	}
	
}

?>