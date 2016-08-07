<?
require_once('billitem.php');
require_once('sh_i_positem.php');
require_once('billpospmformer.php');
require_once('sh_i_posgroup.php');
require_once('acc_group.php');

require_once('actionlog.php');
require_once('authuser.php');

require_once('sh_i_blink.php');
require_once('sh_i_notesitem.php');
require_once('rights_detector.php');

require_once('maxformer.php');
require_once('period_checker.php');

require_once('sh_i_item.php');
require_once('sh_i_in_posgroup.php');

require_once('posdimitem.php');
require_once('bill_in_item.php');

//распор на пр-ку
class ShIInItem extends ShIItem{
	public $sh_i_blink;
	public $rd;
	
	//установка всех имен
	protected function init(){
		$this->tablename='sh_i';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='bill_id';	
		$this->sh_i_blink=new ShIBlink;
		
		$this->rd=new RightsDetector($this);
	}
	
	public function Add($params){
		$code=AbstractItem::Add($params);
		
		if(isset($params['pdate_shipping_plan'])){
			$this->SyncPlanShipDate($code, $params['pdate_shipping_plan']);	
		}
		
		return $code;
	}
	
	public function Edit($id,$params,$scan_status=false, $_auth_result=NULL){
		$item=$this->GetItemById($id);
		
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==1)&&($item['is_confirmed']==0)){
			$params['restore_pdate']=0;	
		}
		
		if(isset($params['status_id'])&&($params['status_id']!=3)&&($item['status_id']==3)){
			$params['restore_pdate']=time();	
		}
		
		
		AbstractItem::Edit($id, $params);
		
		
		if(isset($params['pdate_shipping_plan'])){
			
			$this->SyncPlanShipDate($id, $params['pdate_shipping_plan']);	
		}
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_auth_result);
	}
	
	
	//синхронизация план даты поставки с заказом
	public function SyncPlanShipDate($id, $pdate_shipping_plan){
		$item=$this->getitembyid($id);
		
		$ts=new mysqlSet('select count(*) from '.$this->tablename.' where bill_id="'.$item['bill_id'].'" and id<>"'.$id.'" and pdate_shipping_plan>"'.$pdate_shipping_plan.'"');
		$rs=$ts->getResult();
		$rc=$ts->getResultNumRows();
		$f=mysqli_fetch_array($rs);
		if($f[0]==0){
			
			
			
			$_bi=new BillInItem();
			$bi=$_bi->GetItemById($item['bill_id']);
			//echo $pdate_shipping_plan;  var_dump($bi); die();
			if(($bi['is_confirmed_shipping']==0)&&($bi['pdate_shipping_plan']<$pdate_shipping_plan)){
				//echo 'zzzzzzzzzzzzz'; die();
				
				$_bi->Edit($item['bill_id'], array('pdate_shipping_plan'=>$pdate_shipping_plan));	
			}
		}
			
	}
	
	
	
	//удалить
	public function Del($id){
		
		$query = 'delete from sh_i_position_pm where sh_i_position_id in(select id from sh_i_position where sh_i_id='.$id.');';
		$it=new nonSet($query);
		
		
		$query = 'delete from sh_i_position where sh_i_id='.$id.';';
		$it=new nonSet($query);
		
		
		
		parent::Del($id);
	}	
	
	
	
	//добавим позиции
	public function AddPositions($current_id, array $positions){
		
		$_kpi=new ShIPosItem;
		
		$log_entries=array();
		
		//сформируем список старых позиций
		$old_positions=array();
		$old_positions=$this->GetPositionsArr($current_id);
		
		foreach($positions as $k=>$v){
			//$kpi=$_kpi->GetItemByFields(array('sh_i_id'=>$v['sh_i_id'],'position_id'=>$v['position_id'],'komplekt_ved_id'=>$v['komplekt_ved_id']));
			
			$kpi=$_kpi->GetItemByFields(array(
			'sh_i_id'=>$v['sh_i_id'],
			'position_id'=>$v['position_id'], 
			'pl_position_id'=>$v['pl_position_id'],
			'pl_discount_id'=>$v['pl_discount_id'],
			'pl_discount_value'=>$v['pl_discount_value'],
			'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
			'out_bill_id'=>$v['out_bill_id']
			
			));
			
			if($kpi===false){
				//dobavim pozicii	
				
				$add_array=array();
				$add_array['sh_i_id']=$v['sh_i_id'];
				
				$add_array['pl_position_id']=$v['pl_position_id'];
				$add_array['pl_discount_id']=$v['pl_discount_id'];
				$add_array['pl_discount_value']=$v['pl_discount_value'];
				$add_array['pl_discount_rub_or_percent']=$v['pl_discount_rub_or_percent'];
				$add_array['out_bill_id']=$v['out_bill_id'];
				
				
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['price_f']=$v['price_f'];
				$add_array['price_pm']=$v['price_pm'];
				$add_array['total']=$v['total'];
				
				
				$add_pms=$v['pms'];
				$_kpi->Add($add_array, $add_pms);
				
				$log_entries[]=array(
					'action'=>0,
					'name'=>$v['name'],
					'quantity'=>$v['quantity'],
					'price'=>$v['price'],
					'price_f'=>$v['price_f'],
					'price_pm'=>$v['price_pm'],
					'pl_position_id'=>$v['pl_position_id'],
					'pl_discount_id'=>$v['pl_discount_id'],
					'pl_discount_value'=>$v['pl_discount_value'],
					'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
					'out_bill_id'=>$v['out_bill_id'],
					'pms'=>$v['pms']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				$add_array['sh_i_id']=$v['sh_i_id'];
				
				$add_array['pl_position_id']=$v['pl_position_id'];
				$add_array['pl_discount_id']=$v['pl_discount_id'];
				$add_array['pl_discount_value']=$v['pl_discount_value'];
				$add_array['pl_discount_rub_or_percent']=$v['pl_discount_rub_or_percent'];
				$add_array['out_bill_id']=$v['out_bill_id'];
				
								
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['price_f']=$v['price_f'];
				$add_array['price_pm']=$v['price_pm'];
				$add_array['total']=$v['total'];
				
				$add_pms=$v['pms'];
				$_kpi->Edit($kpi['id'],$add_array, $add_pms);
				
				//если есть изменения
				//кол-во
				$to_log=false;
				if($kpi['quantity']!=$add_array['quantity']) $to_log=$to_log||true;
				
				if($to_log){
				  $log_entries[]=array(
					  'action'=>1,
					  'name'=>$v['name'],
					  'quantity'=>$v['quantity'],
					  'price'=>$v['price'],
					  'price_f'=>$v['price_f'],
					  'price_pm'=>$v['price_pm'],
					  'pl_position_id'=>$v['pl_position_id'],
					  'pl_discount_id'=>$v['pl_discount_id'],
					  'pl_discount_value'=>$v['pl_discount_value'],
					  'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
					  'out_bill_id'=>$v['out_bill_id'],
					  'pms'=>$v['pms']
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
				//if(($vv['position_id']==$v['id'])&&($vv['komplekt_ved_id']==$v['komplekt_ved_id'])){
				if(($vv['pl_position_id']==$v['pl_position_id'])
				&&($vv['position_id']==$v['position_id'])
				&&($vv['pl_discount_id']==$v['pl_discount_id'])
				&&($vv['pl_discount_value']==$v['pl_discount_value'])
				&&($vv['pl_discount_rub_or_percent']==$v['pl_discount_rub_or_percent'])
				&&($vv['out_bill_id']==$v['out_bill_id'])
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
			$pms=NULL;
			if($v['plus_or_minus']==1){
				$pms=array(
						'plus_or_minus'=>$v['plus_or_minus'],
						'rub_or_percent'=>$v['rub_or_percent'],
						'value'=>$v['value']
					);	
			}
			
			$log_entries[]=array(
					'action'=>2,
					'name'=>$v['position_name'],
					'quantity'=>$v['quantity'],
					'price'=>$v['price'],
					  'price_f'=>$v['price_f'],
					  'price_pm'=>$v['price_pm'],
					  'pl_position_id'=>$v['pl_position_id'],
					  'pl_discount_id'=>$v['pl_discount_id'],
					  'pl_discount_value'=>$v['pl_discount_value'],
					  'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
					  'out_bill_id'=>$v['out_bill_id'],
					'pms'=>$pms
			);
			
			//удаляем позицию
			$_kpi->Del($v['p_id']);
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
	
	
	
	//получим позиции
	public function GetPositionsArr($id, $dec2=NULL, $for_an_onway=false){
		$kpg=new ShIInPosGroup;
		$arr=$kpg->GetItemsByIdArr($id, 0, $dec2,$for_an_onway);
		
		return $arr;		
		
	}
	
	
	
	
	//найдем стоииость по заказу
	public function CalcCost($id){
		$positions=$this->GetPositionsArr($id);	
		$_bpm=new BillPosPMFormer;
		$total_cost=$_bpm->CalcCost($positions);
		return $total_cost;
	}
	
	
	
	//проверка и автосмена статуса (1-2)
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		if($item===NULL) $item=$this->GetItemById($id);
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		
		
		//переходы 1-2, 2-1
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
			
			
			
			
			if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)&&($old_params['status_id']==1)){
				
				
				//смена статуса с 1 на 2
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'смена статуса распоряжения на приемку',NULL,613,NULL,'установлен статус '.$stat['name'],$item['bill_id']);
				
				$log->PutEntry($_result['id'],'смена статуса распоряжения на приемку',NULL,644,NULL,'установлен статус '.$stat['name'],$id);
				
				
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)&&(($old_params['status_id']==2)||($old_params['status_id']==7)||($old_params['status_id']==8))){
				
				$this->Edit($id,array('status_id'=>1));
				
				$stat=$_stat->GetItemById(1);
				$log->PutEntry($_result['id'],'смена статуса распоряжения на приемку',NULL,613,NULL,'установлен статус '.$stat['name'],$item['bill_id']);
				
				$log->PutEntry($_result['id'],'смена статуса распоряжения на приемку',NULL,644,NULL,'установлен статус '.$stat['name'],$id);
			}
		}else{
		
		  //отследить переходы из 2-7, 7-2, 7-8, 8-7
		  
		  //переход 2-7 - создали хотя бы 1 поступление
		  if($item['status_id']==2){
			  //проверить количество п	
			  //также может произойти переход 2-8
			  if($this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>8));
				  
				  $stat=$_stat->GetItemById(8);
				  $log->PutEntry($_result['id'],'смена статуса распоряжения на приемку',NULL,613,NULL,'установлен статус '.$stat['name'],$item['bill_id']);
				  
				  $log->PutEntry($_result['id'],'смена статуса распоряжения на приемку',NULL,644,NULL,'установлен статус '.$stat['name'],$id);
			  }else{
				  $this->Edit($id,array('status_id'=>7));
				  
				  $stat=$_stat->GetItemById(7);
				  $log->PutEntry($_result['id'],'смена статуса распоряжения на приемку',NULL,613,NULL,'установлен статус '.$stat['name'],$item['bill_id']);
				  
				  $log->PutEntry($_result['id'],'смена статуса распоряжения на приемку',NULL,644,NULL,'установлен статус '.$stat['name'],$id);
			  }
		  }
		  
		  //переход 7-8 - все позиции завезены, все совпадает (либо совершили выравнивание)
		  if($item['status_id']==7){
			  //проверить количество п	
			  if($this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>8));
				  
				  $stat=$_stat->GetItemById(8);
				  $log->PutEntry($_result['id'],'смена статуса распоряжения на приемку',NULL,613,NULL,'установлен статус '.$stat['name'],$item['bill_id']);
				  
				  $log->PutEntry($_result['id'],'смена статуса распоряжения на приемку',NULL,644,NULL,'установлен статус '.$stat['name'],$id);
			  }
		  }
		  
		  //переход 8-7 - не все позиции совпадают
		  if($item['status_id']==8){
			  //проверить количество п	
			  if(!$this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>7));
				  
				  $stat=$_stat->GetItemById(7);
				  $log->PutEntry($_result['id'],'смена статуса распоряжения на приемку',NULL,613,NULL,'установлен статус '.$stat['name'],$item['bill_id']);
				  
				  $log->PutEntry($_result['id'],'смена статуса распоряжения на приемку',NULL,644,NULL,'установлен статус '.$stat['name'],$id);
			  }
		  }
		  
		}
		
		$_bill=new BillInItem;
		$bill=$_bill->GetItemById($item['bill_id']);
		$_bill->ScanDocStatus($item['bill_id'],array(),array(),$bill,$_result);
	}
	
	
	//запрос о совпадении позиций по подчиненным поступлениям
	public function CheckDeltaPositions($id){
		$res=false;
		
		
		$positions=$this->GetPositionsArr($id);
		
		$delta=0;
		foreach($positions as $k=>$v){
			$sql='select sum(quantity) as s_q from acceptance_position where acceptance_id in(select id from acceptance where is_confirmed=1 and sh_i_id="'.$id.'") 
			and position_id="'.$v['position_id'].'" 
			and pl_position_id="'.$v['pl_position_id'].'"
			and out_bill_id="'.$v['out_bill_id'].'" 
			and pl_discount_id="'.$v['pl_discount_id'].'" 
			and pl_discount_value="'.$v['pl_discount_value'].'" 			
			and pl_discount_rub_or_percent="'.$v['pl_discount_rub_or_percent'].'"';
			
			
			
			$set=new MysqlSet($sql);
			$rs=$set->GetResult();
			
			$f=mysqli_fetch_array($rs);
			//$delta+=($v['quantity']-$f['s_q']);
			$zc=($v['quantity']-$f['s_q']);
			if($zc>=0) $delta+=$zc; 
			
			/*echo $sql;
			echo '<pre>';
			print_r($v);
			print_r($f);
			echo '</pre>';*/
		}
		
		//print_r($delta);
		
		$res=($delta==0);
		
		return $res;
	}
	
	
	
	public function DocCanUnconfirm($id,&$reason,$periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		$item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		
		if($item['is_confirmed']!=1){
			
			$can=$can&&false;
			$reasons[]='распоряжение на приемку не утверждено';
			$reason.=implode(', ',$reasons);
		}else{
		
		  //проверить связанные поступления
		  $_accg=new AccInGroup;
		  $_accg->setidname('sh_i_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' поступление № '.$v['id'].'';	
			  }
			  
		  }
		  if(count($reasons)>0) {
			  $reason.=' по распоряжению на приемку имеются утвержденные поступления: ';
			 // if(strlen($reason)>0) $reason.=',';
			 $reason.=implode(', ',$reasons);
		  }
		  
		  
		  
		 $control_pdate=$this->GetClosePdate($id,$descr78,$item);
		 $reasons=array();
		 //контроль закрытого периода 
		  if(!$_pch->CheckDateByPeriod($control_pdate, $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]=$descr78.$rss23;	
		  }
		  if(count($reasons)>0){
			  if(strlen($reason)>0) $reason.='; ';
		  	$reason.=implode(', ',$reasons);
		  }
		  
		}
		
		return $can;
	}
	
	
	
	
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=1){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
		}else{
		
		  //проверить связанные поступления
				  
		   $set=new mysqlSet('select p.*, s.name from acceptance as p inner join document_status as s on p.status_id=s.id where is_confirmed=1 and sh_i_id="'.$id.'"');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);	 	  
			  
			  if($v['status_id']==5) {
				  
				  $can=$can&&false;
				  $reasons[]='связанное поступление <a target=_blank href=ed_acc.php?id='.$v['id'].'&action=1&from_begin=1>№ '.$v['id'].'</a> статус документа: '.$v['name'];	
			  }
			  
			  //??? куда-то вписать связанные не аннулированные документы
		  }
		}
		$reason=implode('<br /> ',$reasons);
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
		}
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	
	//запрос о возможности утверждения и возвращеня причины, почему нельзя утвердить
	public function DocCanConfirm($id,&$reason, $item=NULL,$periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']==1){
			
			$can=$can&&false;
			
			$reasons[]='документ утвержден';
		}
		
		
		
		//контроль простановки утверждения: 1.1*(число свободных позиций по заявке) д.быть больше
		//чем кол-во в поступлении
		
		$can=$can&&$this->CanConfirmByPositions($id,$rss,$item);
		
		
		$control_pdate=$this->GetClosePdate($id,$descr78,$item);
		
		 //контроль закрытого периода 
		  if(!$_pch->CheckDateByPeriod($control_pdate, $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]=$descr78.$rss23;	
		  }
		  
		
		if(strlen($reason)!=0) $reason.=', ';
		$reason=implode(', ',$reasons);
		if(strlen($rss)>0){
			if(strlen($reason)>0){
				$reason.=', ';
			}
			$reason.=$rss;
		}
		
		return $can;	
	}
	
	
	
	
	
	//определить заданную дату последнего утв. поступления по распоряжению...
	public function GetClosePdate($id, &$descr, $item=NULL){
		
		if($item===NULL) $item=$this->getitembyid($id);
		
		$descr='';
		$sql='select distinct a.given_pdate, a.id, a.given_no from acceptance as a 
		where a.sh_i_id='.$id.' and a.is_confirmed=1 order by a.given_pdate desc limit 1';	
		
		//echo $sql;
		
		$set=new MysqlSet($sql);
		$rc=$set->GetResultNumRows();
		$rs=$set->GetResult();
		
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			$descr='заданная дата с/ф последнего утвержденного поступления по распоряжению (поступление № '.$f[1].', заданный номер '.$f[2].', заданная дата '.date('d.m.Y',$f[0]).')';
			return $f[0];
		}else{
			$descr='плановая дата поставки по распоряжению на приемку ';
			return $item['pdate_shipping_plan'];
		}
	}
	
	
	
	//проверим дату на попадание в закрытый период
	public function CheckClosePdate($id, &$rss, $item=NULL, $periods=NULL){
		$can=true;
		if($item===NULL) $item=$this->GetItemById($id);
		
		$_pch=new PeriodChecker;
		
		$_test_pdate=$this->GetClosePdate($id,$descr78,$item);
		
		//контроль закрытого периода 
		if(!$_pch->CheckDateByPeriod($_test_pdate, $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$rss=$descr78.$rss23;	
		}
		  
		
		return $can;			
	}
	
	//проверка, свободны ли позиции поступления для восстановления/утверждения
	public function CanConfirmByPositions($id,&$reason,$item=NULL){
		$reason=''; $reasons=array();
		$can=true;	
		
		if($item===NULL) $item=$this->getitembyid($id);
		$mf=new MaxFormer;
		
		$_sh=new BillInItem;
		$bill=$_sh->getitembyid($item['bill_id']);
		
		if(($bill!==false)&&($bill['is_confirmed_price']==1)&&($bill['is_confirmed_shipping']==1)){
		
		  $ship_positions=$_sh->GetPositionsArr($item['bill_id']);
		  $positions=$this->GetPositionsArr($id);
		  
		  //переберем позиции поступления
		  //сравним со статистикой распоряжения на приемку
		  //если превышение - то заносим в список причин
		  
		  foreach($positions as $k=>$v){
			  if(!$this->PosInSh($v,$ship_positions,$find_pos)){
				  $can=$can&&false;
				  $reasons[]='в родительском счете не найдена позиция '.SecStr($v['position_name']);	
				  continue;	
			  }
			  
			  //найдена.. сравним количества
			  $vsego=$find_pos['quantity'];
			  
			  $free=$mf->MaxForShI($item['bill_id'],$v['id'], $id, $v['pl_position_id'], $v['pl_discout_id'],  $v['pl_discount_value'],  $v['pl_discount_rub_or_percent'], $v['out_bill_id']) ;
			  
						  
			  if(round($v['quantity'],3)>round($free*PPUP,3)){
				  //превышение
				  $can=$can&&false;
				  $reasons[]=' количество позиции '.SecStr($v['position_name']).' '.$v['quantity'].' '.SecStr($v['dim_name']).' превышает доступное по входящему счету ('.round($free*PPUP,3).'  '.SecStr($v['dim_name']).')';	
				  continue;		
			  }
			  
			  
		  }
		}else{
			$can=$can&&false;
			$reasons[]='родительский входящий счет №'.$bill['code'].' не утвержден';	
		}
		
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	//есть ли данная позиция в расп. на приемку. есть - вернуть поз. расп на пр, нет - false
	protected function PosInSh($acc_position, $sh_positions, &$find_pos){
		$has=false;
		$find_pos=NULL;
		foreach($sh_positions as $k=>$v){
			if(($v['position_id']==$acc_position['position_id'])
			&&($v['pl_position_id']==$acc_position['pl_position_id'])
			&&($v['pl_discount_id']==$acc_position['pl_discount_id'])
			&&($v['pl_discount_value']==$acc_position['pl_discount_value'])
			&&($v['pl_discount_rub_or_percent']==$acc_position['pl_discount_rub_or_percent'])
			&&($v['out_bill_id']==$acc_position['out_bill_id'])
			){
				$has=true;
				$find_pos=$v;
				break;	
			}
		}
		
		return $has;
	}
	
	
	
	
	
	
	
	
	//аннулирование документа
	public function DocAnnul($id){
		if($this->DocCanAnnul($id,$rz)){
			$this->Edit($id, array('status_id'=>3));	
		}
	}
	
	//список связанных не аннулированных не утв документов для автоаннулирования
	public function GetBindedDocumentsToAnnul($id){
		$reason=''; $reasons=array();
		
		$_dsi=new DocStatusItem;
		
		//проверить связанные поступления
		$_accg=new AccGroup;
		$_accg->setidname('sh_i_id');
		$arr=$_accg->getitemsbyidarr($id);
		foreach($arr as $k=>$v){
			$dsi=$_dsi->GetItemById($v['status_id']);
			
			
			if($v['status_id']==4) {
				
				$can=$can&&false;
				$reasons[]='связанное поступление № '.$v['id'].' статус документа: '.$dsi['name'];	
			}
			
			//??? куда-то вписать связанные не аннулированные документы
		}
		
		$reason=implode(', ',$reasons);
		return $reason;
	}
	
	public function AnnulBindedDocuments($id){
		$log=new ActionLog();
		$au=new AuthUser;
		$_result=$au->Auth();
		$_stat=new DocStatusItem;
		$stat=$_stat->GetItemById(6);
		
		
		$set=new MysqlSet('select * from acceptance where sh_i_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'аннулирование поступления в связи с аннулированием распоряжения на приемку',NULL,94,NULL,'поступление № '.$f['id'].': установлен статус '.$stat['name'],$f['bill_id']);
			
			
			$log->PutEntry($_result['id'],'аннулирование поступления в связи с аннулированием распоряжения на приемку',NULL,242,NULL,'поступление № '.$f['id'].': установлен статус '.$stat['name'],$f['id']);
		}	
		
		
		$ns=new NonSet('update acceptance set status_id=6 where sh_i_id="'.$id.'"');	
	}
	
	
	
	
	
	//метод для выравнивания по поступлениям
	public function DoEq($id, array $args, &$output, $is_auto=0, $sh=NULL, $_result=NULL,$extra_reason=''){
		$output=''; $items=array();
		if($sh===NULL) $sh=$this->GetItemById($id);
		
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		
		if($sh['is_confirmed']==0){
			$output='Выравнивание позиций невозможно: распоряжение на приемку не утверждено.';
			return;
		}
		
		
		
		$items=$this->ScanEq($id, $args, $output1, $sh);
		
		
	
		
		//print_r($items);
		$_sh_p=new ShIPosItem;
		$_sh_pm=new ShIPosPMItem;
		$_ni=new ShINotesItem;
		foreach($items as $k=>$v){
			if($v['delta']==0) continue;
			$sh_p=$_sh_p->GetItemByFields(array(
			'sh_i_id'=>$id, 
			'position_id'=>$v['position_id'],
			'pl_position_id'=>$v['pl_position_id'],
			'pl_discount_id'=>$v['pl_discount_id'],
			'pl_discount_value'=>$v['pl_discount_value'],
			'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
			'out_bill_id'=>$v['out_bill_id']
			));
			
			if($sh_p!==false){
				$params=array();
				
				
				if($v['delta']>=0){ //выравнивать только недовоз! перевоз не выравнивать!
				  //вручную - мы можем обнулять, в автомате - мы обнулять не можем, пропускаем позицию
				  if(($is_auto==0)||
				  ($is_auto==1)&&(round(($v['quantity']-$v['delta']),3)>0)){
				  
					  $params['quantity']=round(($v['quantity']-$v['delta']),3);
					  
					  
					  
					  //получить +/- его подставить
					  $sh_pm=$_sh_pm->GetItemByFields(array('sh_i_position_id'=>$sh_p['id']));
					  if($sh_pm!==false){
						  $pms=array(
							  'plus_or_minus'=>$sh_pm['plus_or_minus'],
							  'rub_or_percent'=>$sh_pm['rub_or_percent'],
							  'value'=>$sh_pm['value']
						  );	
					  }else $pms=NULL;
					  
					  
					  $_sh_p->Edit($sh_p['id'], $params, $pms);
					  
					  $description='Распоряжение на приемку №'.$id.': '.$sh_p['name'].' <br /> Кол-во: '.$v['quantity'].' было изменено на:  '.round($params['quantity'],3).'<br /> ';
					  
					  
				
				  
					  //создать примечание к распоряжению
					  if($is_auto==1){
						  
						  
						  $log->PutEntry(NULL,'автоматическое редактирование позиции распоряжения на приемку в связи с автоматическим выравниванием позиций',NULL,613,NULL,$description.$extra_reason,$sh['bill_id']);	
					  
					  	  $log->PutEntry(NULL,'автоматическое редактирование позиции распоряжения на приемку в связи с автоматическим выравниванием позиций',NULL,644,NULL,$description.$extra_reason,$sh['id']);	
						  
						  $posted_user_id=0;
						  $note='Автоматическое примечание: позиция распоряжения на приемку '.$sh_p['name'].' была выровнена при автоматическом выравнивании, кол-во '.$v['quantity'].' было изменено на '.round($params['quantity'],3).''.$extra_reason;
					  }else{
						  
						  $log->PutEntry($_result['id'],'редактировал позицию распоряжения на приемку в связи с выравниванием позиций',NULL,613,NULL,$description.$extra_reason,$sh['bill_id']);	
					  
					  	  $log->PutEntry($_result['id'],'редактировал позицию распоряжения на приемку в связи с выравниванием позиций',NULL,644,NULL,$description.$extra_reason,$sh['id']);	
				  
						  
						  $posted_user_id=$_result['id'];
						  $note='Автоматическое примечание: позиция распоряжения на приемку '.$sh_p['name'].' была выровнена, кол-во '.$v['quantity'].' было изменено на '.round($params['quantity'],3).''.$extra_reason;
					  }
					  $_ni->Add(array(
						'user_id'=>$id,
						'is_auto'=>1,
						'pdate'=>time(),
						'posted_user_id'=>$posted_user_id,
						'note'=>$note
						));
				  }
				}
			}
			
		}
		$output='Выравнивание позиций завершено.';
		
		$this->ScanDocStatus($id, array(), array(),$sh,$_result);		
	}
	
	//сканирование утвержденных подчиненных докум-тов с позицией
	public function ScanEq($id, array $args, &$output, $sh=NULL){
		if($sh===NULL) $sh=$this->GetItemById($id);
		$items=array();
		$total_summ=0; $summ_in_doc=0;
		$output='';
		$docs=array();
		$_pos=new PlPosItem;
		$_pdi=new PosDimItem;
		
		/*
		   stri=$("#new_position_id_"+thash).val()+";";  //0
				  stri=stri+$("#new_pl_position_id_"+thash).val()+";";	//1
				  stri=stri+$("#new_pl_discount_id_"+thash).val()+";";	//2
				  stri=stri+$("#new_pl_discount_value_"+thash).val()+";";	//3	
				  stri=stri+$("#new_pl_discount_rub_or_percent_"+thash).val()+";";	//4
				  stri=stri+$("#new_quantity_"+thash).val()+";";	//5
				  stri=stri+$("#new_out_bill_id_"+thash).val();	//6*/
		
		//перебор по позициям
		foreach($args as $k=>$v){
			$_t_arr=explode(';',$v);
			$summ=0;
			
			$summ_in_doc+=$_t_arr[5];
			
			//по каждой позиции перебрать все позиции подчиненных поступлений
			$sql='select * from acceptance_position 
			where acceptance_id in(
				select id from acceptance 
				where is_confirmed=1 
				and sh_i_id="'.$id.'") 
			and position_id="'.$_t_arr[0].'" 
			and pl_position_id="'.$_t_arr[1].'"
			and pl_discount_id="'.$_t_arr[2].'"
			and pl_discount_value="'.$_t_arr[3].'"
			and pl_discount_rub_or_percent="'.$_t_arr[4].'"
			and out_bill_id="'.$_t_arr[6].'"
			';
			
			$set=new MysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$summ+=$f['quantity'];
				if(!in_array('№'.$f['acceptance_id'],$docs)) $docs[]='№'.$f['acceptance_id'];
			}
			
			$pos=$_pos->GetItemById($_t_arr[1]);
			$pdi=$_pdi->GetItemById($pos['dimension_id']);
			
			$items[]=array(
			'position_id'=>$_t_arr[0], 
			'quantity'=>$_t_arr[5], 
			'pl_position_id'=>$_t_arr[1],
			'pl_discount_id'=>$_t_arr[2],
			'pl_discount_value'=>$_t_arr[3],
			'pl_discount_rub_or_percent'=>$_t_arr[4], 
			'out_bill_id'=>$_t_arr[6], 
			'delta'=>round(($_t_arr[5]-$summ),3));
			
			$total_summ+=$summ;	
			
		}
		
		if($total_summ==0){
			$output='Позиция '.htmlspecialchars($pos['name']).' '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).' не найдена ни в одном утвержденном подчиненном документе. Количество будет обнулено. Продолжить выравнивание данной позиции?';
		}else{
			if($total_summ>$summ_in_doc){
				$output='Позиция '.htmlspecialchars($pos['name']).' найдена в утвержденных поступлениях: '.implode(', ',$docs).' в количестве '.$total_summ.' '.htmlspecialchars($pdi['name']).', что превышает количество в распоряжении на приемку '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).'. Позиция не подлежит выравниванию.';
				
			}elseif($total_summ==$summ_in_doc){
				$output='Позиция '.htmlspecialchars($pos['name']).' найдена в утвержденных поступлениях: '.implode(', ',$docs).' в количестве '.$total_summ.' '.htmlspecialchars($pdi['name']).', что равно количеству в распоряжении на приемку '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).'. Позиция не подлежит выравниванию.';
				
			
			}else $output='Позиция '.htmlspecialchars($pos['name']).' '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).' найдена в утвержденных поступлениях: '.implode(', ',$docs).' в количестве '.$total_summ.' '.htmlspecialchars($pdi['name']).'. Продолжить выравнивание данной позиции?';
		}
		
		return $items;
	}
	
	
	
	
	
	
	//контроль возможности редактирования kol-va позиций
	public function CanEditQuantities($id, &$reason,$itm=NULL){
		$can_delete=true;
		
		$reason='';
		
		if($itm===NULL) $itm=$this->GetItemById($id);
		
		if(($itm!==false)&&(($itm['is_confirmed']!=0))) {
			$reason.='распоряжение на приемку утверждено';
			$can_delete=$can_delete&&false;
		}
		
		
		
		
		$set=new mysqlSet('select * from acceptance where is_confirmed=1 and sh_i_id="'.$id.'" and is_confirmed=1');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		if($rc>0){
			if(strlen($reason)>0) $reason.=', ';
			$reason.='по распоряжению на приемку имеются утвержденные поступления: ';
		 	$nums=array();
			for($i=0;$i<$rc;$i++){
				$f=mysqli_fetch_array($rs);
				$nums[]='№'.$f['id'];
				
			}
			$reason.=implode(', ',$nums);
			$can_delete=$can_delete&&false;
		}
		
		
		
		
		return $can_delete;
	}
}
?>