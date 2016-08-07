<?
require_once('billitem.php');
require_once('sh_i_item.php');
require_once('acc_positem.php');
require_once('billpospmformer.php');
require_once('acc_posgroup.php');

require_once('docstatusitem.php');

require_once('actionlog.php');
require_once('authuser.php');

require_once('shipreports.php');
require_once('sh_i_item.php');
require_once('maxformer.php');
require_once('rights_detector.php');
require_once('supplieritem.php');
require_once('billdates.php');
//require_once('isitem.php');
require_once('acc_sync.php');
require_once('period_checker.php');


require_once('supcontract_item.php');
require_once('supcontract_group.php');
require_once('period_checker.php');

//абстрактный элемент
class AccItem extends BillItem{
	public $rd;
	public $sync;
	
	//установка всех имен
	protected function init(){
		$this->tablename='acceptance';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='bill_id';
		
		$this->rd=new RightsDetector($this);
		$this->sync=new AccSync;	
	}
	
	
	
	public function Add($params){
		$code=AbstractItem::Add($params);
		
		if(isset($params['given_pdate'])){
			$this->SyncPlanShipDate($code, $params['given_pdate']);	
		}
		
		if(isset($params['sh_i_id'])){
			$_sh=new ShIItem;
			$_sh->ScanDocStatus($params['sh_i_id'],array(),array());	
		}
		
		return $code;
	}
	
	public function Edit($id,$params,$scan_status=false, $_auth_result=NULL){
		$item=$this->GetItemById($id);
		
		$_bi=new BillItem;
		$old_bill_summ=$_bi->CalcCost($item['bill_id']);
		
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==1)&&($item['is_confirmed']==0)){
			$params['restore_pdate']=0;	
		}
		
		if(isset($params['status_id'])&&($params['status_id']!=6)&&($item['status_id']==6)){
			$params['restore_pdate']=time();	
		}
		
		
		AbstractItem::Edit($id, $params);
		
		
		
		//if(isset($params['given_pdate'])){
		
		//синхронизация даты оплаты по договору в счете	
		$this->SyncPlanShipDate($id);	
		//}
		
		//если утверждаем - то пересобрать позиции
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==1)){
			
			if($item!==false){
				 $this->ResyncPositions($id,$item['change_high_mode'],$item['change_low_mode'],$_auth_result);
				 
				 //перепривязка оплат в случае повышения суммы счета
				 //также должна быть перепривязвка в случае уменьшения суммы счета
				 $new_bill_summ=$_bi->CalcCost($item['bill_id']);
				
				 if($new_bill_summ!=$old_bill_summ) $this->ResyncPayments($id, $_auth_result, $old_bill_summ, $new_bill_summ);
				 
			}
			/*if(($item!==false)&&($item['interstore_id']!=0)){
				$this->sync->PutChanges($id, $item,$_auth_result);	
			}*/
		}
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params,$_auth_result);
		
		
		
		
		if(isset($item['sh_i_id'])){
			$_sh=new ShIItem;
			$_sh->ScanDocStatus($item['sh_i_id'],array(),array(),NULL,$_auth_result);	
		}
		
		//die();
		
	}
	
	
	//синхронизация даты оплаты по договору в счете
	public function SyncPlanShipDate($id, $pdate_shipping_plan, $item=NULL, $auth=NULL){
		if($item===NULL) $item=$this->getitembyid($id);
		
	
		
		$_log=new ActionLog;
		
		$_au=new AuthUser;
		if($auth===NULL) $auth=$_au->Auth();
		
		// var_dump($item); die();
		////синхронизация даты оплаты по договору в счете
		if(($item['is_confirmed']==1)&&($item['given_pdate']!=0)){
		  
		  $ts=new mysqlSet('select count(*) from '.$this->tablename.' where bill_id="'.$item['bill_id'].'" and id<>"'.$id.'" and given_pdate>"'.$item['given_pdate'].'"');
		  //echo 'select count(*) from '.$this->tablename.' where bill_id="'.$item['bill_id'].'" and id<>"'.$id.'" and given_pdate>"'.$item['given_pdate'].'"';
		  
		 // die();
		  
		  $rs=$ts->getResult();
		  $rc=$ts->getResultNumRows();
		  $f=mysqli_fetch_array($rs);
		  if($f[0]==0){
			  
			//  echo 'nu;';
			  
			  $_bi=new BillItem();
			  $bi=$_bi->GetItemById($item['bill_id']);
			  
			  $_si=new SupplierItem;
			  $supplier=$_si->GetItemById($bi['supplier_id']);
			  
			   $_sci=new SupContractItem;
			  $sci=$_sci->GetItemById($bi['contract_id']);
			  
			  
			  $_bd=new BillDates;
			  
			  $eth=$_bd->FindEthalon($item['given_pdate'],$sci['contract_prolongation'], $sci['contract_prolongation_mode']);
			  
			  if($eth!=$bi['pdate_payment_contract']){
				$_bi->Edit($item['bill_id'], array('pdate_payment_contract'=>$eth),false,$auth);	
				
				$_log->PutEntry($auth['id'],'обновление даты оплаты счета по договору при обновлении заданной даты реализации',NULL, 93,NULL,'старое значение '.date('d.m.Y', $bi['pdate_payment_contract']).', новое значение даты оплаты по договору '.date('d.m.Y', $eth), $item['bill_id']);
				 $_log->PutEntry($auth['id'],'обновление даты оплаты счета по договору при обновлении заданной даты реализации',NULL, 235,NULL,'старое значение '.date('d.m.Y', $bi['pdate_payment_contract']).', новое значение даты оплаты по договору '.date('d.m.Y', $eth), $id);
			  
			  }
			 
			  
		  }
		}
	}
	
	
	//удалить
	public function Del($id){
		
		$query = 'delete from acceptance_position_pm where acceptance_position_id in(select id from acceptance_position where acceptance_id='.$id.');';
		$it=new nonSet($query);
		
		
		$query = 'delete from acceptance_position where acceptance_id='.$id.';';
		$it=new nonSet($query);
		
		
		
		parent::Del($id);
	}	
	
	
	
	//добавим позиции
	public function AddPositions($current_id, array $positions,$change_high_mode=0,$change_low_mode=0){
		$_kpi=new AccPosItem;
		
		$log_entries=array();
		
		//сформируем список старых позиций
		$old_positions=array();
		$old_positions=$this->GetPositionsArr($current_id);
		
		foreach($positions as $k=>$v){
			//$kpi=$_kpi->GetItemByFields(array('acceptance_id'=>$v['acceptance_id'],'position_id'=>$v['position_id'],'komplekt_ved_id'=>$v['komplekt_ved_id']));
			$kpi=$_kpi->GetItemByFields(array(
			'acceptance_id'=>$v['acceptance_id'],
			'position_id'=>$v['position_id'], 
			'pl_position_id'=>$v['pl_position_id'],
			'pl_discount_id'=>$v['pl_discount_id'],
			'pl_discount_value'=>$v['pl_discount_value'],
			'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
			'kp_id'=>$v['kp_id']
			
			));
			
			if($kpi===false){
				//dobavim pozicii	
				
				$add_array=array();
				$add_array['acceptance_id']=$v['acceptance_id'];
				
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['price_f']=$v['price_f'];
				$add_array['price_pm']=$v['price_pm'];
				$add_array['total']=$v['total'];
				
				$add_array['pl_position_id']=$v['pl_position_id'];
				$add_array['pl_discount_id']=$v['pl_discount_id'];
				$add_array['pl_discount_value']=$v['pl_discount_value'];
				$add_array['pl_discount_rub_or_percent']=$v['pl_discount_rub_or_percent'];
				$add_array['kp_id']=$v['kp_id'];
				
				
				$add_pms=$v['pms'];
				$_kpi->Add($add_array, $add_pms,$change_high_mode,$change_low_mode);
				
				$log_entries[]=array(
					'action'=>0,
					'name'=>$v['name'],
					'old_quantity'=>0,
					'quantity'=>$v['quantity'],
					'price'=>$v['price'],
					'price_f'=>$v['price_f'],
					'pl_position_id'=>$v['pl_position_id'],
					'pl_discount_id'=>$v['pl_discount_id'],
					'pl_discount_value'=>$v['pl_discount_value'],
					'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
					'kp_id'=>$v['kp_id'],
					'pms'=>$v['pms']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				$add_array['acceptance_id']=$v['acceptance_id'];
				$add_array['position_id']=$v['position_id'];
				
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['price_f']=$v['price_f'];
				$add_array['price_pm']=$v['price_pm'];
				$add_array['total']=$v['total'];
				
				$add_array['pl_position_id']=$v['pl_position_id'];
				$add_array['pl_discount_id']=$v['pl_discount_id'];
				$add_array['pl_discount_value']=$v['pl_discount_value'];
				$add_array['pl_discount_rub_or_percent']=$v['pl_discount_rub_or_percent'];
				$add_array['kp_id']=$v['kp_id'];
				
				
				
				$add_pms=$v['pms'];
				$_kpi->Edit($kpi['id'],$add_array, $add_pms,$change_high_mode,$change_low_mode);
				//кол-во
				$to_log=false;
				if($kpi['quantity']!=$add_array['quantity']) $to_log=$to_log||true;
				if($kpi['price']!=$add_array['price']) $to_log=$to_log||true;
				
				if($to_log){
				  //если есть изменения
				  $log_entries[]=array(
					  'action'=>1,
					  'name'=>$v['name'],
					  'old_quantity'=>$kpi['quantity'],
					  'quantity'=>$v['quantity'],
					  'price'=>$v['price'],
					  'price_f'=>$v['price_f'],
					  'pl_position_id'=>$v['pl_position_id'],
					  'pl_discount_id'=>$v['pl_discount_id'],
					  'pl_discount_value'=>$v['pl_discount_value'],
					  'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
					  'kp_id'=>$v['kp_id'],
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
				if(
				($vv['position_id']==$v['id'])
				&&($vv['pl_position_id']==$v['pl_position_id'])
				&&($vv['pl_discount_id']==$v['pl_discount_id'])
				&&($vv['pl_discount_value']==$v['pl_discount_value'])
				&&($vv['pl_discount_rub_or_percent']==$v['pl_discount_rub_or_percent'])
				&&($vv['kp_id']==$v['kp_id'])
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
					'old_quantity'=>$v['quantity'],
					
					'quantity'=>$v['quantity'],
					'price'=>$v['price'],
					 'price_f'=>$v['price_f'],
					  'price_pm'=>$v['price_pm'],
					  'pl_position_id'=>$v['pl_position_id'],
					  'pl_discount_id'=>$v['pl_discount_id'],
					  'pl_discount_value'=>$v['pl_discount_value'],
					  'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
					  'kp_id'=>$v['kp_id'],
					'pms'=>$pms
			);
			
			//удаляем позицию
			$_kpi->Del($v['p_id']);
		}
		
		
		$item=$this->getitembyid($current_id);
		if(isset($item['sh_i_id'])){
			$_sh=new ShIItem;
			$_sh->ScanDocStatus($item['sh_i_id'],array(),array());	
		}
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
	
	
	//ресинхронизировать позиции со счетом и распоряжением
	public function ResyncPositions($id,$change_high_mode=0,$change_low_mode=0, $_result=NULL){
		$positions=$this->GetPositionsArr($id);
		$_kpi=new AccPosItem;
		
		$item=$this->GetItemById($id);
		
		foreach($positions as $k=>$v){
			//$kpi=$_kpi->GetItemByFields(array('acceptance_id'=>$v['acceptance_id'],'position_id'=>$v['position_id']));
			
			//print_r($v);
			
			$_kpi->SetChainQuantity($v['acceptance_id'],$v['position_id'], $v['pl_position_id'], $v['pl_discount_id'], $v['pl_discount_value'], $v['pl_discount_rub_or_percent'], $v['quantity'],$change_high_mode,$change_low_mode, $_result, $ite0m,$v['out_bill_id'],$v['kp_id'] );
		}
			
	}
	
	
	//ресинхронизация оплат
	public function ResyncPayments($id, $_result=NULL, $old_bill_summ=0, $new_bill_summ=NULL){
		
		
		$au=new AuthUser();
		if($_result===NULL) $_result=$au->Auth();
		
		$item=$this->getitembyid($id);
		$_bi=new BillItem;
		
		if($new_bill_summ===NULL) $new_bill_summ=$_bi->CalcCost($id);
		
		
		$calc_payed=$_bi->CalcPayed($item['bill_id']);
		
		//$summ_by_bill=$_bi->CalcCost($item['bill_id']); $summ_by_acc=$_bi->CalcAcc($item['bill_id']);
		
		if($new_bill_summ>$old_bill_summ) {
		  //если уже были оплаты по счету, и сумма по поступлениям больше суммы счета - то допривязывать оплаты.
		  if(($calc_payed>0)/*&&($summ_by_acc>$summ_by_bill)*/){
			  
			  //echo 'zzz'; die();
			  
			  $_bi->FreeBindedPayments($item['bill_id'], 1, $_result);
			  $_bi->BindPayments(	$item['bill_id'], $_result['org_id'], $_result);
		  }
				
		}elseif($new_bill_summ<$old_bill_summ){
		  	//уменьшили сумму счета, и по счету были оплаты
			//если сумма оплат больше, чем сумма счета - уменьшить сумму оплат.
			if(($calc_payed>0)&&($calc_payed>$new_bill_summ)){
				$_bi->LowPayments($item['bill_id'], $_result, $old_bill_summ, $new_bill_summ,$calc_payed,$id);
			}
		}
		
	}
	
	
	//получим позиции
	public function GetPositionsArr($id, $show_statiscits=true, $show_boundaries=true){
		$kpg=new AccPosGroup;
		$arr=$kpg->GetItemsByIdArr($id,0,true,true,$show_statiscits, $show_boundaries);
		
		return $arr;		
		
	}
	
	
	
	//найдем стоииость по заказу
	public function CalcCost($id, $positions=NULL, $changed_totals=NULL){
		if($positions===NULL) $positions=$this->GetPositionsArr($id,false);	
		$_bpm=new BillPosPMFormer;
		$total_cost=$_bpm->CalcCost($positions,$changed_totals);
		return $total_cost;
	}
	
	
	//проверка и автосмена статуса (1-2)
	public function ScanDocStatus($id, $old_params, $new_params, $_result=NULL, $item=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
			
			
			if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)&&($old_params['status_id']==4)){
				//смена статуса с 1 на 2
				$this->Edit($id,array('status_id'=>5));
				
				$stat=$_stat->GetItemById(5);
				$log->PutEntry($_result['id'],'смена статуса реализации',NULL,93,NULL,'установлен статус '.$stat['name'],$item['bill_id']);
				
				$log->PutEntry($_result['id'],'смена статуса реализации',NULL,219,NULL,'установлен статус '.$stat['name'],$item['sh_i_id']);
				
				$log->PutEntry($_result['id'],'смена статуса реализации',NULL,235,NULL,'установлен статус '.$stat['name'],$item['id']);
				
				
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)&&($old_params['status_id']==5)){
				$this->Edit($id,array('status_id'=>4));
				
				$stat=$_stat->GetItemById(4);
				$log->PutEntry($_result['id'],'смена статуса реализации',NULL,93,NULL,'установлен статус '.$stat['name'],$item['bill_id']);
				
				$log->PutEntry($_result['id'],'смена статуса реализации',NULL,219,NULL,'установлен статус '.$stat['name'],$item['sh_i_id']);
				
				$log->PutEntry($_result['id'],'смена статуса реализации',NULL,235,NULL,'установлен статус '.$stat['name'],$item['id']);
				
			}
		}
		
		//$_sh=new 
	}
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=4){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
		}
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	//аннулирование документа
	public function DocAnnul($id){
		if($this->DocCanAnnul($id,$rz)){
			$this->Edit($id, array('status_id'=>6));	
		}
	}
	
	public function DocCanRestore($id,&$reason){
		$can=true;	
		$reason=''; $reasons=array();
		$item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=6){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
		}
		
		
		$can=$can&&$this->CanConfirmByPositions($id,$rss);
		
		
		$reason=implode(', ',$reasons);
		if(strlen($rss)>0){
			if(strlen($reason)>0){
				$reason.=', ';
			}
			$reason.=$rss;
		}
		
		
		//$reason=implode(', ',$reasons);
		return $can;
	}
	
	
	
	//запрос о возможности утверждения и возвращеня причины, почему нельзя утвердить
	public function DocCanConfirm($id,&$reason, $item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']==1){
			
			$can=$can&&false;
			
			$reasons[]='документ утвержден';
		}
		
		if($item['given_pdate']==0){
			$can=$can&&false;
			$reasons[]='не введена заданная дата с/ф ';	
			
		}elseif($item['given_pdate']>DateFromdmY(date('d.m.Y'))){
			$can=$can&&false;
			$reasons[]='заданная дата с/ф '.date('d.m.Y',$item['given_pdate']).' превышает текущую';	
		}
		
		
		if($item['given_no']==''){
			$can=$can&&false;
			$reasons[]='не введен заданный номер с/ф ';	
			
		}
		
		
		//контроль закрытого периода 
		if(!$_pch->CheckDateByPeriod($item['given_pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='заданная дата с/ф '.$rss23;	
		}
		
		
		
		//контроль простановки утверждения: 1.1*(число свободных позиций по заявке) д.быть больше
		//чем кол-во в реализации
		
		$can=$can&&$this->CanConfirmByPositions($id,$rss,$item);
		
		
		$reason=implode(', ',$reasons);
		if(strlen($rss)>0){
			if(strlen($reason)>0){
				$reason.=', ';
			}
			$reason.=$rss;
		}
		
		return $can;	
	}
	
	
	
	//запрос о возможности СНЯТИЯ утверждения и возвращеня причины, почему нельзя НЕ утвердить
	public function DocCanUnConfirm($id,&$reason, $item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']==0){
			
			$can=$can&&false;
			
			$reasons[]='документ не утвержден';
		}
		
		
		//контроль закрытого периода 
		if(!$_pch->CheckDateByPeriod($item['given_pdate'], $item['org_id'],$rss1,$periods)){
			$can=$can&&false;
			$reasons[]='заданная дата с/ф '.$rss1;	
		}
		
		
		
		$reason=implode(', ',$reasons);
		if(strlen($rss)>0){
			if(strlen($reason)>0){
				$reason.=', ';
			}
			$reason.=$rss;
		}
		
		return $can;	
	
	}
	//проверка, свободны ли позиции поступления для восстановления/утверждения
	public function CanConfirmByPositions($id,&$reason,$item=NULL){
		$reason=''; $reasons=array();
		$can=true;	
		$_sh_r=new ShipReports;
		if($item===NULL) $item=$this->getitembyid($id);
		$mf=new MaxFormer;
		
		
		  
		$_sh=new ShIItem;
		$ship=$_sh->GetItemById($item['sh_i_id']);
		if(($ship['is_confirmed']==1)&&($ship!==false)){
		  
		  
		  $ship_positions=$_sh->GetPositionsArr($item['sh_i_id']);
		  $positions=$this->GetPositionsArr($id);
		  
		  //переберем позиции поступления
		  //сравним со статистикой распоряжения на отгрузку
		  //если превышение - то заносим в список причин
		  
		  foreach($positions as $k=>$v){
			  if(!$this->PosInSh($v,$ship_positions,$find_pos)){
				  $can=$can&&false;
				  $reasons[]='в родительском распоряжении на отгрузку не найдена позиция '.SecStr($v['position_name']);	
				  continue;	
			  }
			  
			  //найдена.. сравним количества
			  $vsego=$find_pos['quantity'];
			  
			  $free=$mf->MaxForAcc($item['sh_i_id'], $v['id'], $id,  $v['pl_position_id'], $v['pl_discount_id'], $v['pl_discount_value'], $v['pl_discount_rub_or_percent'] ) ;
			  
						  
			  if(round($v['quantity'],3)>round($free*PPUP,3)){
				  //превышение
				  $can=$can&&false;
				  $reasons[]='количество позиции '.SecStr($v['position_name']).' '.$v['quantity'].' '.SecStr($v['dim_name']).' превышает доступное по распоряжению на отгрузку ('.round($free*PPUP,3).'  '.SecStr($v['dim_name']).')';	
				  continue;		
			  }
			  
			  //$_sh_r->InAcc($v['id'],$item['bill_id'],'',
		  }
		}else{
			$can=$can&&false;
				  $reasons[]='не утверждено родительское распоряжение на отгрузку № '.$item['sh_i_id'];	
				  
		}
		
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	//есть ли данная позиция в расп. на отгрузку. есть - вернуть поз. расп на пр, нет - false
	protected function PosInSh($acc_position, $sh_positions, &$find_pos){
		$has=false;
		$find_pos=NULL;
		foreach($sh_positions as $k=>$v){
			if(($v['position_id']==$acc_position['position_id'])
			&&($v['pl_position_id']==$acc_position['pl_position_id'])
			&&($v['pl_discount_id']==$acc_position['pl_discount_id'])
			&&($v['pl_discount_value']==$acc_position['pl_discount_value'])
			&&($v['pl_discount_rub_or_percent']==$acc_position['pl_discount_rub_or_percent'])
			
			){
				$has=true;
				$find_pos=$v;
				break;	
			}
		}
		
		return $has;
	}
	
	//есть ли услуги в поступлении
	public function HasUsl($id){
		
		$positions=$this->GetPositionsArr($id,false,false);
		
		$has=false;
		foreach($positions as $k=>$v){
			if($v['is_usl']==1){
				$has=true;
				break;	
			}
		}
			
		return $has;
	}
	
	
	//есть ли +/- в родительском счете
	public function ParentBillHasPms($acc_id, $acc_item=NULL){
		
		if($acc_item===NULL) $acc_item=$this->getitembyid($acc_id);
		
		$sql='select count(*) from bill_position_pm where bill_position_id in(select id from  bill_position where bill_id="'.$acc_item['bill_id'].'")';	
		
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		//var_dump((int)$f[0]>0);
		
		return ((int)$f[0]>0);
			
	}
}
?>