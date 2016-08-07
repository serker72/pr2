<?
require_once('abstractitem.php');
require_once('kppositem.php');
require_once('billpospmformer.php');

require_once('kp_pospmformer.php');
require_once('kpposgroup.php');
require_once('docstatusitem.php');

require_once('trust_group.php');
require_once('sh_i_group.php');
require_once('acc_group.php');

require_once('paygroup.php');

require_once('actionlog.php');
require_once('authuser.php');

require_once('payforbillitem.php');
require_once('payitem.php');
require_once('pay_in_item.php');
require_once('invcalcitem.php');
require_once('payforbillgroup.php');
require_once('payforbill_in_group.php');

require_once('pl_curritem.php');

//require_once('komplitem.php');
require_once('period_checker.php');
require_once('kpnotesitem.php');


require_once('invcalcitem.php');

require_once('invcalcnotesitem.php');
require_once('paynotesitem.php');
require_once('pl_dismaxvalitem.php');

//коммерческое предложение
class KpItem extends AbstractItem{
	public $currency; //используем для вывода валюты в реестре
	public $storage_path; //папка для сохранения генерируемых pdf файлов
	
	
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
		
		$this->storage_path=ABSPATH.'upload/email/kp/';	
	}
	
	
	//удалить
	public function Del($id){
		
		$query = 'delete from kp_position_pm where kp_position_id in(select id from kp_position where kp_id='.$id.');';
		$it=new nonSet($query);
		
		
		$query = 'delete from kp_position where kp_id='.$id.';';
		$it=new nonSet($query);
		
		
		
		parent::Del($id);
	}	
	
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		$log=new ActionLog;
		
		//мы устанавливаем утверждение 1 гал.
		if(isset($params['is_confirmed_price'])&&($params['is_confirmed_price']==1)&&($item['is_confirmed_price']==0)){
			$params['restore_pdate']=0;	
		}
		
		if(isset($params['status_id'])&&($params['status_id']!=3)&&($item['status_id']==3)){
			$params['restore_pdate']=time();	
		}
		
		
		
		AbstractItem::Edit($id, $params);
		
		//$this->billpaysync->CatchStatus($
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
		
		
		if(isset($params['is_confirmed_price'])&&($params['is_confirmed_price']==1)&&($item['is_confirmed_price']==0)){
			
			//также нужно синхронизировать цены с ПЛ - после Нового года....
			$positions=$this->GetPositionsArr($id,false,$item);
			$_kpf=new KpPosPMFormer;
			
			
			//синхронизировать цены.......
			$_kpf->CalcCost($positions, false, true, $_result);
			
			//утверждаем КП. нужно пересчитать рент-ть
			
			
			$_kpf->CalcProfit($positions, $profit_percent, $profit_value, $profit_currency);
			$do_log=false;
			$do_log=$do_log||(((float)$profit_percent!=(float)$item['profit_percent'])||((float)$profit_value!=(float)$item['profit_value'])||($profit_currency!=$item['profit_currency'])	);
			
			$new_params=array();
			$new_params['profit_percent']=$profit_percent;
			$new_params['profit_value']=$profit_value;
			$new_params['profit_currency']=$profit_currency;
			
			AbstractItem::Edit($id, $new_params);
			if($do_log){
				$_curr=new PlCurrItem;
				$currency=$_curr->Getitembyid($profit_currency);
				$log->PutEntry($_result['id'],'изменение рентабельности при утверждении коммерческого предложения',NULL, 701,NULL,SecStr('автоматически изменилась рентабельность при утверждении коммерческого предложения '.$item['code'].', установлено значение '.$profit_percent.'%, '.$profit_value.' '.$currency['signature']),$id);	
			}
			
			
			
			
		}
	}
	
	
	
	//добавим позиции
	public function AddPositions($current_id, array $positions,$can_change_cascade=false, $check_delta_summ=false, $result=NULL,$bill=NULL){
		$_kpi=new KpPosItem;
		
		$log_entries=array();
		
		//сформируем список старых позиций
		if($bill===NULL) $bill=$this->getitembyid($current_id);
		$old_positions=array();
		$old_positions=$this->GetPositionsArr($current_id,true,$bill);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array(
			'kp_id'=>$v['kp_id'],
			'position_id'=>$v['position_id'], 
			'pl_position_id'=>$v['pl_position_id']
			/*'pl_discount_id'=>$v['pl_discount_id'],
			'pl_discount_value'=>$v['pl_discount_value'],
			'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent']*/
			
			));
			
			//$f['hash']=md5($f['pl_position_id'].'_'.$f['position_id'].'_'.$f['pl_discount_id'].'_'.$f['pl_discount_value'].'_'.$f['pl_discount_rub_or_percent']);
			
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['kp_id']=$v['kp_id'];
				
				$add_array['pl_position_id']=$v['pl_position_id'];
				$add_array['pl_discount_id']=$v['pl_discount_id'];
				$add_array['pl_discount_value']=$v['pl_discount_value'];
				$add_array['pl_discount_rub_or_percent']=$v['pl_discount_rub_or_percent'];
				
				
				$add_array['parent_id']=$v['parent_id'];
				$add_array['currency_id']=$v['currency_id'];
				
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['extra_charges']=$v['extra_charges'];
				$add_array['price_f']=$v['price_f'];
				$add_array['price_pm']=$v['price_pm'];
				$add_array['total']=$v['total'];
				$add_array['price_kind_id']=$v['price_kind_id'];
				$add_array['delivery_ddpm']=$v['delivery_ddpm'];
				
				$add_array['print_form_has_komplekt']=$v['print_form_has_komplekt'];
				
				
				
				$add_pms=$v['pms'];
				$_kpi->Add($add_array, $add_pms);
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'name'=>$v['name'],
					'quantity'=>$v['quantity'],
					'price'=>$v['price'],
					'price_f'=>$v['price_f'],
					'price_pm'=>$v['price_pm'],
					'parent_id'=>$v['parent_id'],
					'currency_id'=>$v['currency_id'],
					'pl_position_id'=>$v['pl_position_id'],
					'pl_discount_id'=>$v['pl_discount_id'],
					'pl_discount_value'=>$v['pl_discount_value'],
					'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
					'price_kind_id'=>$v['price_kind_id'],
					'delivery_ddpm'=>$v['delivery_ddpm'],
					'pms'=>$v['pms']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['pl_position_id']=$v['pl_position_id'];
				$add_array['pl_discount_id']=$v['pl_discount_id'];
				$add_array['pl_discount_value']=$v['pl_discount_value'];
				$add_array['pl_discount_rub_or_percent']=$v['pl_discount_rub_or_percent'];
				
				
				$add_array['parent_id']=$v['parent_id'];
				$add_array['currency_id']=$v['currency_id'];
				
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['extra_charges']=$v['extra_charges'];
				$add_array['price_f']=$v['price_f'];
				$add_array['price_pm']=$v['price_pm'];
				$add_array['total']=$v['total'];
				$add_array['price_kind_id']=$v['price_kind_id'];
				$add_array['delivery_ddpm']=$v['delivery_ddpm'];
				
				
				$add_array['print_form_has_komplekt']=$v['print_form_has_komplekt'];
				
				
				$add_pms=$v['pms'];
				$_kpi->Edit($kpi['id'],$add_array, $add_pms,$can_change_cascade,$check_delta_summ,$result);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//если есть изменения
				
				//как определить? изменились кол-ва, цены, +/-, 
				
				$to_log=false;
				if($kpi['quantity']!=$add_array['quantity']) $to_log=$to_log||true;
				if($kpi['pl_discount_id']!=$add_array['pl_discount_id']) $to_log=$to_log||true;
				if($kpi['pl_discount_value']!=$add_array['pl_discount_value']) $to_log=$to_log||true;
				//if($kpi['pl_discount_rub_or_percent']!=$add_array['pl_discount_rub_or_percent']) $to_log=$to_log||true;
				
				if($kpi['price_f']!=$add_array['price_f']) $to_log=$to_log||true;
				if($kpi['price']!=$add_array['price']) $to_log=$to_log||true;
				if($kpi['delivery_ddpm']!=$add_array['delivery_ddpm']) $to_log=$to_log||true;
				
				
			//	if($kpi['price_pm']!=$add_array['price_pm']) $to_log=$to_log||true;
			//	if($kpi['total']!=$add_array['total']) $to_log=$to_log||true;
				
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					  'name'=>$v['name'],
					  'quantity'=>$v['quantity'],
					  'price'=>$v['price'],
					  'price_f'=>$v['price_f'],
					  'price_pm'=>$v['price_pm'],
					  'parent_id'=>$v['parent_id'],
					  'currency_id'=>$v['currency_id'],
					  'pl_position_id'=>$v['pl_position_id'],
					  'pl_discount_id'=>$v['pl_discount_id'],
					  'pl_discount_value'=>$v['pl_discount_value'],
					  'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
					  'price_kind_id'=>$v['price_kind_id'],
					  'delivery_ddpm'=>$v['delivery_ddpm'],
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
			//$f['hash']=md5($f['pl_position_id'].'_'.$f['position_id'].'_'.$f['pl_discount_id'].'_'.$f['pl_discount_value'].'_'.$f['pl_discount_rub_or_percent']);
			
			foreach($positions as $kk=>$vv){
				if(($vv['pl_position_id']==$v['pl_position_id'])
				&&($vv['position_id']==$v['position_id'])
				/*&&($vv['pl_discount_id']==$v['pl_discount_id'])
				&&($vv['pl_discount_value']==$v['pl_discount_value'])
				&&($vv['pl_discount_rub_or_percent']==$v['pl_discount_rub_or_percent'])*/
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
					 'name'=>$v['name'],
					  'quantity'=>$v['quantity'],
					  'price'=>$v['price'],
					  'price_f'=>$v['price_f'],
					  'price_pm'=>$v['price_pm'],
					  'parent_id'=>$v['parent_id'],
 					  'currency_id'=>$v['currency_id'],
					  'pl_position_id'=>$v['pl_position_id'],
					  'pl_discount_id'=>$v['pl_discount_id'],
					  'pl_discount_value'=>$v['pl_discount_value'],
					  'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
					  'price_kind_id'=>$v['price_kind_id'],
					  'delivery_ddpm'=>$v['delivery_ddpm'],
					'pms'=>$pms
			);
			
			//удаляем позицию
			$_kpi->Del($v['p_id']);
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
	
	
	
	//получим позиции
	public function GetPositionsArr($id,$show_statistics=true, $bill=NULL){
		$kpg=new KpPosGroup;
		$arr=$kpg->GetItemsByIdArr($id,0,$show_statistics,$bill);
		
		return $arr;		
		
	}
	
	
	
	//найдем стоииость по заказу
	public function CalcCost($id, $positions=NULL, $result=NULL){
		if($positions===NULL) $positions=$this->GetPositionsArr($id,false);	
		
		
		$_bpm=new KpPosPMFormer;
		
		$total_cost=$_bpm->CalcCost($positions,false,false,$result);
		$this->currency=$_bpm->GetCurrency($positions);
		return round($total_cost,2);
	}
	
	
	
	//контроль возможности удаления
	public function CanDelete($id, &$reason){
		$can_delete=true;
		
		$reason='';
		
		$itm=$this->GetItemById($id);
		
		if(($itm!==false)&&(($itm['is_confirmed_price']!=0))) {
			$reason.='коммерческое предложение утверждено';
			$can_delete=$can_delete&&false;
		}
		
		
		
		
		
		return $can_delete;
	}
	
	
	
	
	
	
	
	//проверка и автосмена статуса (1-2)
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		if(isset($new_params['is_confirmed_price'])&&isset($old_params['is_confirmed_price'])){
			
			
			
			if(($new_params['is_confirmed_price']==1)&&($old_params['is_confirmed_price']==0)&&($old_params['status_id']==1)){
				//смена статуса с 1 на 2
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'смена статуса коммерческого предложения',NULL,701,NULL,'установлен статус '.$stat['name'],$item['bill_id']);
				
			}elseif(($new_params['is_confirmed_price']==0)&&($old_params['is_confirmed_price']==1)&&(($old_params['status_id']==2)||($old_params['status_id']==9)||($old_params['status_id']==10)||($old_params['status_id']==27))){
				$this->Edit($id,array('status_id'=>1));
				
				$stat=$_stat->GetItemById(1);
				$log->PutEntry($_result['id'],'смена статуса коммерческого предложения',NULL,701,NULL,'установлен статус '.$stat['name'],$item['bill_id']);
			}
		}else{
			//отследить переходы из 2-9, 9-2, 9-10, 10-9
		  
		 
			
		}
		
		//добавить контроль смены статуса заявки
		//получить все заявки по счету
		//перебрать их и для каждой - вызвать контроль
		/*$_ki=new KomplItem;
		$sql='select distinct komplekt_ved_id from bill_position where bill_id="'.$id.'"';
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->getresultnumrows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			//print_r($f);
			$_ki->ScanDocStatus($f['komplekt_ved_id'],array(),array(), NULL,$_result);	
		}*/
		//die();
	}
	
	
	
	
	//запрос о совпадении позиций по подчиненным поступлениям
	public function CheckDeltaPositions($id){
		$res=true;
		
		
	
		
		return $res;
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
			$reason.=implode(', ',$reasons);
		}else{
		
		 
		  
		 
		  
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
	
	//аннулирование документа
	public function DocAnnul($id){
		if($this->DocCanAnnul($id,$rz)){
			$this->Edit($id, array('status_id'=>3));	
		}
	}
	
	//список связанных не аннулированных не утв документов для автоаннулирования
	public function GetBindedDocumentsToAnnul($id){
		$reason=''; $reasons=array();
		
		
	
		return $reason;
	}
	
	public function AnnulBindedDocuments($id){
		
	}
	
	
	
	//получить список связанных оплат
	public function GetBindedPayments($bill_id,&$summ){
		$summ=0;
		$names=array();	
		
		
		return implode(', ',$names);
	}
	
	public function GetBindedPaymentsFull($bill_id){
		$summ=0;
		$alls=array();	
		
		
		
		
		return $alls;
	}
	
	//удалить платежи из связанных оплат
	public function FreeBindedPayments($bill_id, $is_auto=0, $_result=NULL){
		
		
		
	}
	
	//привязать счет к оплатам с авансом
	public function BindPayments($bill_id,$org_id, $_result=NULL){
		
		
	}
	
	
	
	
	
	
	
	
	//проверим дату на попадание в закрытый период
	public function CheckClosePdate($id, &$rss, $item=NULL, $periods=NULL){
		$can=true;
		if($item===NULL) $item=$this->GetItemById($id);
		
		$_pch=new PeriodChecker;
		
		//var_dump($item);
		//echo $item['supplier_bill_pdate'];
		//контроль закрытого периода 
		/*if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $rss=' заданная дата комм. предложения '.$rss23;
			  //echo'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';	
		  }
		  */
		
		return $can;			
	}
	
	
	//Запрос о возм снятия утв цен
	public function DocCanUnconfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_price']!=1){
			
			$can=$can&&false;
			$reasons[]='коммерческое предложение не утверждено';
			$reason.=implode(', ',$reasons);
		}else{
		
		  
		   //контроль закрытого периода 
		    $reasons=array();
		/*  if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]=' заданная дата комм. предложения '.$rss23;	
		  }*/
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
		
		$_au=new AuthUser;
		$result=$_au->Auth();
		
		if($item['is_confirmed_price']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='коммерческое предложение утверждено';
			$reason.=implode(', ',$reasons);
		}else{
			//контроль закрытого периода 
		    $reasons=array();
			
			
			if($item['supplier_id']==0){
				$can=$can&&false;
				$reasons[]='Не выбран контрагент ';
			}
			
			
			if($item['contact_id']==0){
				$can=$can&&false;
				$reasons[]='Не выбран контакт ';
			}
			/*
			if(strlen(trim($item['supplier_name']))<4){
				$can=$can&&false;
				$reasons[]='Длина поля В компанию меньше 4 символов';
			}
			
			
			//контроль недоспустимых названий компаний и имен менеджера
			$restricted_names= array();
			$restricted_names[]="Индивидуальный предприниматель";
			$restricted_names[]="В компанию";
			$restricted_names[]="Частное лицо";
			
			$local_can=true;
			foreach($restricted_names as $k=>$v){
				if(strtoupper(trim($v))==strtoupper(trim($item['supplier_name']))){
					$local_can=$local_can&&false;
				}
			}
			if(!$local_can){
				$can=$can&&false;
				$reasons[]='Введено некорректное значение в поле В компанию! Просьба ввести корректное название контрагента!';
			}
			
			
			if(preg_match('/(.)\1{3,}/',trim($item['supplier_name']))){
				$can=$can&&false;
				$reasons[]='Введено некорректное значение в поле В компанию! Просьба ввести корректное название контрагента!';
			}
			
			$local_can=true;
			foreach($restricted_names as $k=>$v){
				if(strtoupper(trim($v))==strtoupper(trim($item['supplier_fio']))){
					$local_can=$local_can&&false;
				}
			}
			if(!$local_can){
				$can=$can&&false;
				$reasons[]='Введено некорректное значение в поле значение в поле Вниманию! Просьба ввести корректное имя менеджера!';
			}
			
			if(preg_match('/(.)\1{3,}/' , trim($item['supplier_fio']))){
				$can=$can&&false;
				$reasons[]='Введено некорректное значение в поле значение в поле Вниманию! Просьба ввести корректное имя менеджера!';
			}
			 */
			
			
			//если нестд оплата или гарантия - проверить спецправа, вывести сообщение
			if((($item['warranty_id']==2)||($item['paymode_id']==2)||($item['paymode_id']==5))&&!$_au->user_rights->CheckAccess('w',817)){
				$can=$can&&false;
				
				$_usg=new UsersSGroup;
				$unst=$_usg->GetUsersByRightArr('w', 817);
				
				foreach($unst as $k=>$v){
					if(in_array($v['id'], array(2,3))) unset($unst[$k]);
				}
				
				$mess="У Вас недостаточно прав для утверждения коммерческого предложения с нестандартными условиями гарантии или оплаты.\nКоммерческое предложение необходимо согласовать с руководителем.\nСотрудники, имеющие право утвердить коммерческое предложение:";	
				foreach($unst as $k=>$v) $mess.= $v['name_s'].' ('.$v['login'].')'."\n ";
				$reasons[]=$mess;
				
			}
				$positions=$this->GetPositionsArr($id,false);
						//echo 'zz';
				
			 	//нет доступа к скидке руководителя, а скидка в КП есть
				/*if(!$_au->user_rights->CheckAccess('w',753)){
					$pos_id=0; $disc_id=0; $disc_max=0; $disc_value=0;
					foreach($positions as $k=>$v){
						if($v['parent_id']==0){
							$pos_id=$v['id'];
							$disc_id=$v['pl_discount_id'];
							//$disc_max=$v['pl_discount_id'];
							$disc_value=$v['pl_discount_value'];
							
							
							if($disc_id==2){
								$can=$can&&false;
								
								$mess="У Вас недостаточно прав для утверждения коммерческого предложения со скидкой руководителя ".$disc_value."%.\nКоммерческое предложение необходимо согласовать с руководителем.\nСотрудники, имеющие право утвердить коммерческое предложение:";	
							
								$_usg=new UsersSGroup;
								$unst=$_usg->GetUsersByRightArr('w', 753);
								foreach($unst as $k=>$v) if(!in_array($v['id'],array(2,3))) $mess.= $v['name_s'].' ('.$v['login'].')'."\n ";
								$reasons[]=$mess;
								
								break;	
							}
							
							
							
						}
					}
					
					
				}*/
				
			 
			 
				//превышение скидки максимально допустимой 
				
			 
				
				if(!$_au->user_rights->CheckAccess('w',752)){
					
					 
				
					
					$pos_id=0; $disc_id=0; $disc_max=0; $disc_value=0;
					foreach($positions as $k=>$v){
						if($v['parent_id']==0){
							$pos_id=$v['id'];
							$disc_id=$v['pl_discount_id'];
							
							//если указана скидка руководителя, и это менеджер (нет доступа к скидке рук-ля) - то проверяем правила для скидки менеджера
							if(!$_au->user_rights->CheckAccess('w',753)&&($disc_id==2)) $disc_id=1;
							
							 
							$disc_value=$v['pl_discount_value'];
							
							
							
							$_pdm=new PlDisMaxValItem;
							//$pdm=$_pdm->GetItemByFields(array('pl_position_id'=>$pos_id, 'discount_id'=>$disc_id, 'price_kind_id'=>$item['price_kind_id'] ));
							$pdm=$_pdm->GetItemByFields(array('pl_position_id'=>$pos_id, 'discount_id'=>$disc_id, 'price_kind_id'=>$item['price_kind_id'] ));
							
							//если есть ограничения на макс. скидку
							if($pdm!==false){
							
							  $max_sk=$pdm['value'];
							}else{
								$max_sk=100;
							}
							  
							  //получить индивидуальные  правила скидок
							  $_gi=new PosGroupItem;
							  $_pi=new PlPosItem;
							  $_skg=new PlDisMaxValUserGroup;
							  
							  $pi=$_pi->GetItemById($pos_id); 
					   
							  //найти группу (последовательность) и/или поставщика
							  //по ним найдем правила!
							  $_resolve=array();
							  
							  
							  $gi=$_gi->GetItemById($pi['group_id']);
							  
							  //print_r($gi);
							  
							  
							  if($gi['parent_group_id']>0){
								  $gi2=$_gi->GetItemById($gi['parent_group_id']);	
								  if($gi2['parent_group_id']>0){
									  $gi3=$_gi->GetItemById($gi2['parent_group_id']);		
									  
									  //$f['group_name']=stripslashes($gi3['name'].'-> '.$gi2['name'].'-> '.$gi['name']);
									  
									   
									  
									  $_resolve[]=array(
										  'name'=>'parent_group_id',
										  'value'=>$pi['group_id']				
									  );
									  
									  
									  
									  $_resolve[]=array(
										  'name'=>'parent_group_id',
										  'value'=>$gi2['id']				
									  );
															  
									  $_resolve[]=array(
										  'name'=>'producer_id',
										  'value'=>$pi['producer_id']				
									  );	
									  
									  $_resolve[]=array(
										  'name'=>'parent_group_id',
										  'value'=>$gi3['id']				
									  );						
									  
								  }else{
									  
									  //$f['group_name']=stripslashes($gi2['name'].'-> '.$gi['name']);	
														  
										  
									  $_resolve[]=array(
										  'name'=>'parent_group_id',
										  'value'=>$pi['group_id']				
									  );
									  
									  $_resolve[]=array(
										  'name'=>'producer_id',
										  'value'=>$pi['producer_id']				
									  );
									  
									  $_resolve[]=array(
										  'name'=>'parent_group_id',
										  'value'=>$gi2['id']				
									  );				
									  
								  }
							  }else{
								  $_resolve[]=array(
									  'name'=>'parent_group_id',
									  'value'=>$pi['group_id']				
								  );
							  }
							  
							  
							  
							  
							  
							  
							  $rules=$_skg->GetItemsByIdArr($result['id']);
							  //var_dump($rules);	
							  
							  //перебор критериев совпадения
							  //внутри цикла - перебор правил
							  //если совпало хоть одно из правил на текущем критерии - подставить макс. значение, выход
							  //echo '<pre>';
							  //print_r($_resolve);
							  
							  foreach($_resolve as $k=>$v){
								  $matched=false;
								  
								  //print_r($v);
								  
								  foreach($rules as $rk=>$rv){
									  //if($rk	
									  //print_r($f);
									  //$v['name']==$rv[
									  if(($v['value']==$rv[$v['name']])&&($disc_id==$rv['discount_id'])){
										  $matched=$matched||true;
										  
										  $max_sk=$rv['value'];
										  //echo $dl_value;
										  break;	
									  }
								  }
								  if($matched) break;
								  
							  }
							  
							  
							  //echo $max_sk;
							// echo ' максимум: '.$max_sk.' введено: '.$disc_value.' ';
							   
							  if($max_sk<$disc_value){
								  $can=$can&&false;
								  
								  $mess="У Вас недостаточно прав для утверждения коммерческого предложения со скидкой ".$disc_value."%, т.к. она превышает максимальную скидку ".$max_sk."%.\nКоммерческое предложение необходимо согласовать с руководителем.\nСотрудники, имеющие право утвердить коммерческое предложение:";	
							  
								  $_usg=new UsersSGroup;
								  $unst=$_usg->GetUsersByRightArr('w', 752);
								  foreach($unst as $k=>$v) if(!in_array($v['id'],array(2,3))) $mess.= $v['name_s'].' ('.$v['login'].')'."\n ";
								  $reasons[]=$mess;
								  
								  break;	
							  }
							  
							}
							
							
						 
					}
					
					
					
					
				}
				 
				 
			 
			
			 
			 $reason.=implode(', ',$reasons);
			
			/*if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
				$can=$can&&false;
				$reasons[]=' заданная дата комм. предложения '.$rss23;	
			}
			 $reason.=implode(', ',$reasons);
			 */
			 
			 //проверка по числу позиций 
			$can=$can&&$this->CanConfirmByPositions($id,$rss,$item);
			if(strlen($rss)>0){
				if(strlen($reason)>0){
					$reason.=', ';
				}
				$reason.=$rss;
			} 
		}
		
		
		  
		
		return   $can;
	}
	
	
	
	//проверка, свободны ли позиции счета для восстановления/утверждения
	public function CanConfirmByPositions($id,&$reason,$item=NULL){
		$reason=''; $reasons=array();
		$can=true;	
		
		
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	//есть ли данная позиция в расп. на отгрузку. есть - вернуть поз. расп на пр, нет - false
	protected function PosInSh($acc_position, $sh_positions, &$find_pos){
		$has=false;
		$find_pos=NULL;
		foreach($sh_positions as $k=>$v){
			if(($v['position_id']==$acc_position['id'])&&($v['komplekt_ved_id']==$acc_position['komplekt_ved_id'])){
				$has=true;
				$find_pos=$v;
				break;	
			}
		}
		
		return $has;
	}
	
	
	
	
	public function HasShsorAccs($id){
		 
		  $can=false;
		 
		
		  
		  return $can;	
	}
	
	
	
	
	
	//метод для выравнивания по поступлениям
	public function DoEq($id, array $args, &$output, $is_auto=0, $sh=NULL, $_result=NULL, $express_scan=false, $extra_reason=''){
		
	}
	
	
	
	
	//сканирование утвержденных подчиненных докум-тов с позицией
	public function ScanEq($id, array $args, &$output, $sh=NULL, $express_scan=false, $continue_message=".\nПродолжить выравнивание данной позиции?"){
		
		
		return array();
	}
	
	
	
	
	
	
	
	
	
	
	//контроль возможности редактирования kol-va позиций
	public function CanEditQuantities($id, &$reason, $itm=NULL){
		$can_delete=true;
		
		$reason='';
		
		if($itm===NULL) $itm=$this->GetItemById($id);
		
		if(($itm!==false)&&(($itm['is_confirmed_price']!=0)||($itm['is_confirmed_shipping']!=0))) {
			$reason.='коммерческое предложение утверждено';
			$can_delete=$can_delete&&false;
		}
		
		
		
		
		return $can_delete;
	}
	
	
	
	
		
		
	
	
	//уменьшение суммы привязанных к счету оплат при уменьшении сумммы счета
	public function LowPayments($id, $_result=NULL, $old_bill_summ=0, $new_bill_summ=NULL, $calc_payed=NULL, $actor_id=NULL){
		
		
	}
	
	
	
	//удаление старых файлов- вложений к письмам с КП
	public function ClearLostFiles($ttl=86400){
		//$files=array();
		$message_files=array();
		
		//$message_files=$this->GetFilenamesArr();
		//print_r($message_files);
		$iterator = new DirectoryIterator($this->storage_path);
		foreach ($iterator as $fileinfo) {
			if ($fileinfo->isFile()) {
				//echo $fileinfo->__toString();
				//$filenames[$fileinfo->getMTime()] = $fileinfo->getFilename();
			//	if(!in_array($fileinfo->__toString(), $message_files)){
					
					$tm=$fileinfo->getMTime();
					if($tm<(time()-$ttl)){
					  //проверять дату
					  @unlink($this->storage_path.$fileinfo->__toString());
					}
				//}
			}
		}
		
	}
	
	
	
}
?>