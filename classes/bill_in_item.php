<?
require_once('abstractitem.php');
require_once('billitem.php');

require_once('billpositem.php');
require_once('billpospmformer.php');
require_once('billposgroup.php');
require_once('bill_in_posgroup.php');
require_once('docstatusitem.php');

require_once('trust_group.php');
require_once('sh_i_group.php');
require_once('acc_group.php');
require_once('paygroup.php');

require_once('actionlog.php');
require_once('authuser.php');

require_once('payforbillitem.php');
require_once('payitem.php');
require_once('invcalcitem.php');
require_once('payforbillgroup.php');

//require_once('komplitem.php');
require_once('period_checker.php');
require_once('billnotesitem.php');


require_once('invcalcitem.php');

require_once('invcalcnotesitem.php');
require_once('paynotesitem.php');
require_once('bill_in_positem.php');

//входящий счет
class BillInItem  extends BillItem{

	
	//установка всех имен
	protected function init(){
		$this->tablename='bill';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
		
	}
	
	
	
	//получим позиции
	public function GetPositionsArr($id,$show_statistics=true, $bill=NULL){
		$kpg=new BillInPosGroup;
		$arr=$kpg->GetItemsByIdArr($id,0,$show_statistics,$bill);
		
		return $arr;		
		
	}
	
	
	//добавим позиции
	public function AddPositions($current_id, array $positions,$can_change_cascade=false, $check_delta_summ=false, $result=NULL,$bill=NULL){
		$_kpi=new BillInPosItem;
		
		$log_entries=array();
		
		//сформируем список старых позиций
		if($bill===NULL) $bill=$this->getitembyid($current_id);
		$old_positions=array();
		$old_positions=$this->GetPositionsArr($current_id,true,$bill);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array(
			'bill_id'=>$v['bill_id'],
			'position_id'=>$v['position_id'], 
			'pl_position_id'=>$v['pl_position_id'],
			'pl_discount_id'=>$v['pl_discount_id'],
			'pl_discount_value'=>$v['pl_discount_value'],
			'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
			'out_bill_id'=>$v['out_bill_id']
			
			));
			
			//$f['hash']=md5($f['pl_position_id'].'_'.$f['position_id'].'_'.$f['pl_discount_id'].'_'.$f['pl_discount_value'].'_'.$f['pl_discount_rub_or_percent']);
			
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['bill_id']=$v['bill_id'];
				
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
				if($kpi['pl_discount_rub_or_percent']!=$add_array['pl_discount_rub_or_percent']) $to_log=$to_log||true;
				
				if($kpi['price_f']!=$add_array['price_f']) $to_log=$to_log||true;
				if($kpi['price']!=$add_array['price']) $to_log=$to_log||true;
				if($kpi['price_pm']!=$add_array['price_pm']) $to_log=$to_log||true;
				if($kpi['total']!=$add_array['total']) $to_log=$to_log||true;
				
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
			//$f['hash']=md5($f['pl_position_id'].'_'.$f['position_id'].'_'.$f['pl_discount_id'].'_'.$f['pl_discount_value'].'_'.$f['pl_discount_rub_or_percent']);
			
			foreach($positions as $kk=>$vv){
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
					'pms'=>$pms
			);
			
			//удаляем позицию
			$_kpi->Del($v['p_id']);
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
	
	
	
	
	//найдем стоимость по подвозу
	public function CalcAcc($id, $item=NULL, $positions=NULL, $before_pdate=NULL){
		//  $sql3='select * from acceptance where bill_id="'.$id.'" and is_confirmed=1 order by given_pdate asc';
		  
		  $before_flt='';	
		  if($before_pdate!==NULL)	$before_flt=' and given_pdate<="'.$before_pdate.'" ';
		  	
		  $sql3='select sum(total) from acceptance_position where acceptance_id in(select id from acceptance where bill_id="'.$id.'" and is_confirmed=1 '.$before_flt.')';
			
			  
		  $set3=new mysqlSet($sql3);//,$to_page, $from,$sql_count);
		  $rs3=$set3->GetResult();
		  $rc3=$set3->GetResultNumRows();	
		  
		  
		  $g=mysqli_fetch_array($rs3);
		  
		  return round((float)$g[0],2);
	}
	
	//контроль возможности удаления
	public function CanDelete($id, &$reason){
		$can_delete=true;
		
		$reason='';
		
		$itm=$this->GetItemById($id);
		
		if(($itm!==false)&&(($itm['is_confirmed_price']!=0)||($itm['is_confirmed_shipping']!=0))) {
			$reason.='счет утвержден';
			$can_delete=$can_delete&&false;
		}
		
		
		$set=new mysqlSet('select * from sh_i where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		if($rc>0){
			if(strlen($reason)>0) $reason.=', ';
			$reason.='по счету имеются распоряжения на приемку: ';
		 	$nums=array();
			for($i=0;$i<$rc;$i++){
				$f=mysqli_fetch_array($rs);
				$nums[]='№'.$f['id'];
				
			}
			$reason.=implode(', ',$nums);
			$can_delete=$can_delete&&false;
		}
		
		$set=new mysqlSet('select * from payment where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		if($rc>0){
			if(strlen($reason)>0) $reason.=', ';
			$reason.='по счету имеются оплаты: ';
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
	
	
	public function HasR($id){
		$coun=0;
		
		
		$set=new mysqlSet('select count(*) from acceptance where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		//$rc=$set->GetResultNumRows();
		$f=mysqli_fetch_array($rs);
		$coun+=(int)$f[0];
		
		return $coun;
	}
	
	public function HasRList($id){
		$txt='';
		
		$nums=array();
		
		$set=new mysqlSet('select * from acceptance where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0;$i<$rc;$i++){
				$f=mysqli_fetch_array($rs);
				$nums[]='поступление №'.$f['id'];
				
			}
			
		$txt=implode(', ',$nums);
		
		return $txt;
	}
	
	
	
	public function CalcPayed($id, $except_id=NULL, $except_inv=NULL){
		
		$res=0;
		
		$sql='select sum(bp.value) from payment_for_bill as bp inner join payment as p on bp.payment_id=p.id where p.is_confirmed=1 and bp.bill_id="'.$id.'" ';
		
		if($except_id!==NULL) $sql.=' and p.id<>"'.$except_id.'"';
		
		$set=new mysqlset($sql);
		
		
		
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		//$rc=$set->GetResultNumRows();
		//echo $rc;
		
		$res+=(float)$f[0];
		
		
		$sql='select sum(bp.value) from payment_for_bill as bp inner join invcalc as p on bp.invcalc_id=p.id where p.is_confirmed_inv=1 and bp.bill_id="'.$id.'" ';
		
		if($except_inv!==NULL) $sql.=' and p.id<>"'.$except_inv.'"';
		
		$set=new mysqlset($sql);
		
		
		
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		
		$res+=(float)$f[0];
		
		return round($res,2);
		
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
				$log->PutEntry($_result['id'],'смена статуса счета',NULL,613,NULL,'установлен статус '.$stat['name'],$item['bill_id']);
				
			}elseif(($new_params['is_confirmed_price']==0)&&($old_params['is_confirmed_price']==1)&&(($old_params['status_id']==2)||($old_params['status_id']==9)||($old_params['status_id']==10))){
				$this->Edit($id,array('status_id'=>1));
				
				$stat=$_stat->GetItemById(1);
				$log->PutEntry($_result['id'],'смена статуса счета',NULL,613,NULL,'установлен статус '.$stat['name'],$item['bill_id']);
			}
		}else{
			//отследить переходы из 2-9, 9-2, 9-10, 10-9
		  
		  //переход 2-9
		  if($item['status_id']==2){
			  //проверить количество п	
			  //также может произойти переход 2-10
			  if($this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>10));
				  
				  $stat=$_stat->GetItemById(10);
				  $log->PutEntry($_result['id'],'смена статуса счета',NULL,613,NULL,'установлен статус '.$stat['name'],$id);
				  
				  
				  //выровнять стоимости в реализациях, если есть расхождения по суммам
				  
			  }else{
				  $this->Edit($id,array('status_id'=>9));
				  
				  $stat=$_stat->GetItemById(9);
				  $log->PutEntry($_result['id'],'смена статуса счета',NULL,613,NULL,'установлен статус '.$stat['name'],$id);
			  }
		  }
		  
		  //переход 9-10 - все позиции завезены, все совпадает (либо совершили выравнивание)
		  if($item['status_id']==9){
			  //проверить количество п	
			  if($this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>10));
				  
				  $stat=$_stat->GetItemById(10);
				  $log->PutEntry($_result['id'],'смена статуса счета',NULL,613,NULL,'установлен статус '.$stat['name'],$id);
				  
				  //выровнять стоимости в поступлениях, если есть расхождения по суммам
				  
			  }
		  }
		  
		  //переход 10-9 - не все позиции совпадают
		  if($item['status_id']==10){
			  //проверить количество п	
			  if(!$this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>9));
				  
				  $stat=$_stat->GetItemById(9);
				  $log->PutEntry($_result['id'],'смена статуса счета',NULL,613,NULL,'установлен статус '.$stat['name'],$id);
			  }
		  }	
			
			
			
			
		}
		
		
		//die();
	}
	
	
	
	
	//запрос о совпадении позиций по подчиненным поступлениям
	public function CheckDeltaPositions($id){
		$res=false;
		
		
		$positions=$this->GetPositionsArr($id,false);
		
		$delta=0;
		foreach($positions as $k=>$v){
			
			
			
			$sql='select sum(quantity) as s_q from acceptance_position 
			
			where acceptance_id in(
				select id from acceptance 
				where is_confirmed=1 
				
				and sh_i_id in(
					select id from sh_i 
					where is_confirmed=1 
					and bill_id="'.$id.'" 
					)
				) 
				and position_id="'.$v['position_id'].'" 
				and pl_position_id="'.$v['pl_position_id'].'" 
				and pl_discount_id="'.$v['pl_discount_id'].'" 
				and pl_discount_value="'.$v['pl_discount_value'].'" 
				and pl_discount_rub_or_percent="'.$v['pl_discount_rub_or_percent'].'" 
				and out_bill_id="'.$v['out_bill_id'].'" 
				';
			
			
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
		//die();
		
		$res=($delta==0);
		
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
		
		  //проверить связанные поступления
		 
		  $set=new mysqlSet('select p.*, s.name from acceptance as p inner join document_status as s on p.status_id=s.id where is_confirmed=1 and bill_id="'.$id.'"');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			  	  
			  //if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' реализация <a target=_blank href=ed_acc.php?id='.$v['id'].'&action=1&from_begin=1>№ '.$v['id'].'</a> статус документа: '.$v['name'];	
			  //}
			  
		  }
		  if(count($reasons)>0) $reason.="<br />По счету имеются утвержденные поступления: ";
		  $reason.=implode('<br /> ',$reasons);
		  
		  
		  //проверить связанные распоряжения на отгрузку
		
		    $set=new mysqlSet('select p.*, s.name from sh_i as p inner join document_status as s on p.status_id=s.id where is_confirmed=1 and bill_id="'.$id.'"');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);	  
			  
			 // if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' распоряжение на приемку <a target=_blank href=ed_ship.php?id='.$v['id'].'&action=1&from_begin=1>№ '.$v['id'].'</a> статус документа: '.$v['name'];	
			 // }
			  
		  }
		  if(count($reasons)>0) $reason.="<br />По счету имеются утвержденные распоряжения на приемку: ";
		  $reason.=implode('<br /> ',$reasons);
		  
		 
		  
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
		
		$_dsi=new DocStatusItem;
		
		
		//проверить связанные поступления
		
			  
		 $set=new mysqlSet('select p.*, s.name from acceptance as p inner join document_status as s on p.status_id=s.id where bill_id="'.$id.'"');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);	  
			  if($v['status_id']==4) {
				  $can=$can&&false;
				  $reasons[]=' поступление № '.$v['id'].' статус документа: '.$v['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.=" по счету имеются неутвержденные поступления: ";
		  $reason.=implode(', ',$reasons);
		  
		  
		  //проверить связанные распоряжения на отгрузку
		
 		$set=new mysqlSet('select p.*, s.name from sh_i as p inner join document_status as s on p.status_id=s.id where bill_id="'.$id.'"');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);	  			  
			  if($v['status_id']==1) {
				  $can=$can&&false;
				  $reasons[]=' распоряжение на приемку № '.$v['id'].' статус документа: '.$v['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.=" по счету имеются неутвержденные распоряжения на приемку: ";
		  $reason.=implode(', ',$reasons);
		  
		  //проверить связанные доверенности
		  /*$_accg=new TrustGroup;
		  $_accg->setidname('bill_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  if($v['status_id']==1) {
				  $can=$can&&false;
				  $reasons[]=' доверенность № '.$v['id'].' статус документа: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.=" по счету имеются неутвержденные доверенности: ";
		  $reason.=implode(', ',$reasons);
		  
		  //проверить связ оплаты
		  $set=new mysqlSet('select * from payment where status_id=1 and id in(select distinct payment_id from payment_for_bill where bill_id="'.$id.'")');
		  $rs=$set->getResult();
		  
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			  $dsi=$_dsi->GetItemById($v['status_id']);
			
				  $can=$can&&false;
				  $reasons[]=' оплата № '.$v['code'].' статус документа: '.$dsi['name'];	
			 
			  
		  }
		  if(count($reasons)>0) $reason.=" по счету имеются неутвержденные оплаты: ";
		  $reason.=implode(', ',$reasons);*/
		
	
		return $reason;
	}
	
	public function AnnulBindedDocuments($id){
		
		$log=new ActionLog();
		$au=new AuthUser;
		$_result=$au->Auth();
		$_stat=new DocStatusItem;
		$stat=$_stat->GetItemById(6);
		
		$set=new MysqlSet('select * from acceptance where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'аннулирование поступления в связи с аннулированием счета',NULL,94,NULL,'поступление № '.$f['id'].': установлен статус '.$stat['name'],$f['bill_id']);
			
			$log->PutEntry($_result['id'],'аннулирование поступления в связи с аннулированием счета',NULL,242,NULL,'поступление № '.$f['id'].': установлен статус '.$stat['name'],$f['id']);
		}	
		
		$ns=new NonSet('update acceptance set status_id=6 where bill_id="'.$id.'"');
		
		
		/*$set=new MysqlSet('select * from trust where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'аннулирование доверенности в связи с аннулированием счета',NULL,94,NULL,'доверенность № '.$f['id'].': установлен статус '.$stat['name'],$f['bill_id']);
		}	
		
		$ns=new NonSet('update trust set status_id=3 where bill_id="'.$id.'"');*/
		
		
		
		
		$set=new MysqlSet('select * from sh_i where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'аннулирование распоряжения на приемку в связи с аннулированием счета',NULL,94,NULL,'распоряжение № '.$f['id'].': установлен статус '.$stat['name'],$f['bill_id']);
			
			
			$log->PutEntry($_result['id'],'аннулирование распоряжения на приемку в связи с аннулированием счета',NULL,226,NULL,'распоряжение № '.$f['id'].': установлен статус '.$stat['name'],$f['id']);
		}	
		
		$ns=new NonSet('update sh_i set status_id=3 where bill_id="'.$id.'"');
		
		
		/*$set=new MysqlSet('select * from payment where id in(select distinct payment_id from payment_for_bill where bill_id="'.$id.'")');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'аннулирование оплаты в связи с аннулированием счета',NULL,613,NULL,'оплата № '.$f['code'].': установлен статус '.$stat['name'],$f['bill_id']);
			
			$log->PutEntry($_result['id'],'аннулирование оплаты в связи с аннулированием счета',NULL,279,NULL,'оплата № '.$f['code'].': установлен статус '.$stat['name'],$f['id']);
		}	
		
		$ns=new NonSet('update payment set status_id=3 where id in(select distinct payment_id from payment_for_bill where bill_id="'.$id.'")');	*/
	}
	
	
	
	//получить список связанных оплат
	public function GetBindedPayments($bill_id,&$summ){
		$summ=0;
		$names=array();	
		$_inv=new InvCalcItem;
		
		//$set=new mysqlSet('select * from payment where is_confirmed=1 and id in(select distinct payment_id from payment_for_bill where bill_id="'.$bill_id.'")');
		$set=new mysqlSet('select distinct pb.payment_id, pb.id, b.code as bill_code, p.code, pb.value, p.given_no, p.given_pdate
					 from payment_for_bill as pb 
						inner join payment as p on p.id=pb.payment_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed=1  order by p.given_pdate desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$names[]=$f['code'];
			$summ+=(float)$f['value'];
		}
		
		//добавим оплаты по инв. актам
		
		$set=new mysqlSet('select distinct pb.invcalc_id, pb.id, b.code as bill_code, p.code, pb.value, p.given_no, p.invcalc_pdate
					 from payment_for_bill as pb 
						inner join invcalc as p on p.id=pb.invcalc_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed_inv=1 order by p.invcalc_pdate desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$names[]=$f['code'];
			$summ+=(float)$f['value'];
		}
		
		
		
		return implode(', ',$names);
	}
	
	public function GetBindedPaymentsFull($bill_id){
		$summ=0;
		$alls=array();	
		
		$_inv=new InvCalcItem;
		
		//$set=new mysqlSet('select * from payment where is_confirmed=1 and id in(select distinct payment_id from payment_for_bill where bill_id="'.$bill_id.'")');
		$set=new mysqlSet('select distinct pb.payment_id, "0" as kind, pb.id, b.code as bill_code, p.code, p.value, p.given_no, p.given_pdate as given_pdate,  p.given_pdate as given_payment_pdate, p.given_pdate as given_payment_pdate_unf 
					 from payment_for_bill as pb 
						inner join payment as p on p.id=pb.payment_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed=1 order by p.given_pdate desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$alls[]=$f;
		}
		
		//добавим инв. акты
		$sql='select distinct pb.invcalc_id, "1" as kind, pb.id, b.code as bill_code, p.code, pb.value, p.given_no,  p.invcalc_pdate as invcalc_pdate, p.invcalc_pdate as given_payment_pdate, p.invcalc_pdate as given_payment_pdate_unf 
					 from payment_for_bill as pb 
						inner join invcalc as p on p.id=pb.invcalc_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed_inv=1 order by p.invcalc_pdate desc';
		//echo $sql;
		$set=new mysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$real_debt_stru=$_inv->FindRealDebt($f['invcalc_id']);
			//$f['real_debt']=$real_debt_stru['real_debt'];
			//$f['real_debt_id']=$real_debt_stru['real_debt_id'];
			$f['debt']=$real_debt_stru['real_debt'];
			
			
			//$f['value']=$f['real_debt'];
			
			$alls[]=$f;
		}
		
		
		
		return $alls;
	}
	
	//удалить платежи из связанных оплат
	public function FreeBindedPayments($bill_id, $is_auto=0, $_result=NULL){
		$log=new ActionLog;
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_pi=new PayItem;
		
		$_pb=new PayForBillItem;
		$_inv=new InvCalcItem;
		
		$auto_flt='';
		if($is_auto==1) $auto_flt=' and pb.is_auto=1 ';
		
		$sql='select distinct pb.payment_id, pb.id, b.code as bill_code, p.code, pb.value
					 from payment_for_bill as pb 
						inner join payment as p on p.id=pb.payment_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed=1 '.$auto_flt;
		
		$set=new mysqlSet($sql);
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$_pb->Del($f['id']);
			
			$log->PutEntry($_result['id'],'удаление платежа по счету из входящей оплаты',NULL,613,NULL,'входящая оплата № '.$f['code'].': удален платеж по счету '.$f['bill_code'].' на сумму '.$f['value'].' руб. ',$bill_id);
			
			$log->PutEntry($_result['id'],'удаление платежа по счету из входящей оплаты',NULL,272,NULL,'входящая оплата № '.$f['code'].': удален платеж по счету '.$f['bill_code'].' на сумму '.$f['value'].' руб. ',$f['payment_id']);
			
			//привяжем оплату по счету к неоплаченным счетам данного контрагента...
			$_pi->BindPayments($f['payment_id'], $_result['org_id']);
		}	
		
		//удалим платежи из инв. актов
		$sql='select distinct pb.invcalc_id, pb.id, b.code as bill_code, p.code, pb.value
					 from payment_for_bill as pb 
						inner join invcalc as p on p.id=pb.invcalc_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed_inv=1 '.$auto_flt;
		$set=new mysqlSet($sql);
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$_pb->Del($f['id']);
			
			$log->PutEntry($_result['id'],'удаление платежа по счету из инвентаризационного акта',NULL,613,NULL,'акт № '.$f['code'].': удален платеж по счету '.$f['bill_code'].' на сумму '.$f['value'].' руб. ',$bill_id);
			
			$log->PutEntry($_result['id'],'удаление платежа по счету из инвентаризационного акта',NULL,452,NULL,'акт № '.$f['code'].': удален платеж по счету '.$f['bill_code'].' на сумму '.$f['value'].' руб. ',$f['invcalc_id']);
			
			//привяжем оплату по счету к неоплаченным счетам данного контрагента...
			//$_pi->BindPayments($f['payment_id'], $_result['org_id']);
		}
		
		
	}
	
	//привязать счет к оплатам с авансом
	public function BindPayments($bill_id,$org_id, $_result=NULL){
		$bill=$this->GetItemById($bill_id);
		if($bill===false) return;
		$_bpi=new PayForBillItem;
		$_pfg=new PayForBillgroup;
		$_bpf=new BillPosPMFormer;
		
		
		$_inv=new InvCalcItem;
		
		$log=new ActionLog;
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		
		
		$opl_codes=$_pfg->GetAvans($bill['supplier_id'],$org_id,$bill_id,$avans,$opl_ids,$inv_ids,  $bill['contract_id']);
		if((count($opl_ids)==0)&&(count($inv_ids)==0)) return;
		if($avans<=0) return;
		
		
		
		$total_cost=$_bpf->CalcCost($this->GetPositionsArr($bill_id));
		
		$sum_by_bill=$_pfg->SumByBill($bill_id);
		if($total_cost<=$sum_by_bill) return;
		
		$delta=$total_cost-$sum_by_bill;
		
		
		//суммы по инв актам...
		if(count($inv_ids)>0){
			$set=new mysqlset('select * from invcalc where is_confirmed_inv=1 and supplier_id="'.$bill['supplier_id'].'" and org_id="'.$org_id.'" and id in('.implode(', ',$inv_ids).') and invcalc_pdate<"'.$bill['supplier_bill_pdate'].'"');
			
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  
			  $real_debt_stru=$_inv->FindRealDebt($f['id'],$f);
			  $f['real_debt']=$real_debt_stru['real_debt'];
			  $f['real_debt_id']=$real_debt_stru['real_debt_id'];
			  
			 // echo 'zz';
			  if(!(($real_debt_stru['real_debt_id']==2)&&($real_debt_stru['real_debt']!=0))) continue;
			  
			  $set1=new mysqlset('select sum(value) from payment_for_bill where invcalc_id="'.$f['id'].'"');
			  $rs1=$set1->GetResult();
			  $g=mysqli_fetch_array($rs1);
			  
			   //если эта оплата уже в счете - то ее величину добавлять к value (без влияние на дельту)
			  $by_bill=0;
			  $set2=new mysqlset('select sum(value) from payment_for_bill where invcalc_id="'.$f['id'].'" and bill_id="'.$bill_id.'"');
			  $rs2=$set2->GetResult();
			  $g2=mysqli_fetch_array($rs2);
			  $by_bill+=(float)$g2[0];
			  
			  
			  if((float)$f['real_debt']>(float)$g[0]){
				  
				  $delta_local=((float)$f['real_debt']-(float)$g[0]);
				  
				  $test=$_bpi->GetItemByFields(array('invcalc_id'=>$f['id'],'bill_id'=>$bill_id));
				  if($delta_local>$delta){
					  $delta_local=$delta;
					  $delta=0;	
				  }else{
					  $delta-=$delta_local;	
				  }
				  if($test===false){
					  $_bpi->Add(array('invcalc_id'=>$f['id'],'bill_id'=>$bill_id,'value'=>$delta_local+$by_bill, 'is_auto'=>1));	
				  }else{
					  //$delta_local-=$test['value'];
					  $_bpi->Edit($test['id'],array('invcalc_id'=>$f['id'],'bill_id'=>$bill_id,'value'=>$delta_local+$by_bill, 'is_auto'=>1));	
				  }
				  
				  $log->PutEntry($_result['id'],'добавление платежа по счету в инвентаризационный акт',NULL,613,NULL,'акт № '.$f['code'].': добавлен платеж по счету '.$bill['code'].' на сумму '.($delta_local+$by_bill).' руб. ',$bill_id);
				  
				  
				  $log->PutEntry($_result['id'],'добавление платежа по счету в инвентаризационный акт',NULL,452,NULL,'акт № '.$f['code'].': добавлен платеж по счету '.$bill['code'].' на сумму '.($delta_local+$by_bill).' руб. ',$f['id']);
				  
				  if($delta==0) break;	
			  }
			  
		  }
			//die();
		}
		
		
		if($delta==0) return;
		//суммы по оплатам
		if(count($opl_ids)>0){
		  $set=new mysqlset('select * from payment
		   where 
		   	is_confirmed=1 
			and is_incoming=0 
			and contract_id="'.$bill['contract_id'].'"
			and supplier_id="'.$bill['supplier_id'].'" 
			and org_id="'.$org_id.'" 
			and id in('.implode(', ',$opl_ids).')');
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  
			  //echo 'zz';
			  
			  $set1=new mysqlset('select sum(value) from payment_for_bill where payment_id="'.$f['id'].'"');
			  $rs1=$set1->GetResult();
			  $g=mysqli_fetch_array($rs1);
			  
			  
			  //если эта оплата уже в счете - то ее величину добавлять к value (без влияние на дельту)
			  $by_bill=0;
			  $set2=new mysqlset('select sum(value) from payment_for_bill where payment_id="'.$f['id'].'" and bill_id="'.$bill_id.'"');
			  $rs2=$set2->GetResult();
			  $g2=mysqli_fetch_array($rs2);
			  $by_bill+=(float)$g2[0];
			  
			  if((float)$f['value']>(float)$g[0]){
				  
				  $delta_local=((float)$f['value']-(float)$g[0]);
				  
				  $test=$_bpi->GetItemByFields(array('payment_id'=>$f['id'],'bill_id'=>$bill_id));
				  if($delta_local>$delta){
					  $delta_local=$delta;
					  $delta=0;	
				  }else{
					  $delta-=$delta_local;	
				  }
				  if($test===false){
					  $_bpi->Add(array('payment_id'=>$f['id'],'bill_id'=>$bill_id,'value'=>$delta_local+$by_bill, 'is_auto'=>1));	
				  }else{
					  //$delta_local-=$test['value'];
					  $_bpi->Edit($test['id'],array('payment_id'=>$f['id'],'bill_id'=>$bill_id,'value'=>$delta_local+$by_bill, 'is_auto'=>1));	
				  }
				  
				  $log->PutEntry($_result['id'],'добавление платежа по счету в исходящую оплату',NULL,613,NULL,'исходящая оплата № '.$f['code'].': добавлен платеж по счету '.$bill['code'].' на сумму '.($delta_local+$by_bill).' руб. ',$bill_id);
				  
				  
				  $log->PutEntry($_result['id'],'добавление платежа по счету в исходящую оплату',NULL,272,NULL,'исходящая оплата № '.$f['code'].': добавлен платеж по счету '.$bill['code'].' на сумму '.($delta_local+$by_bill).' руб. ',$f['id']);
				  
				  if($delta==0) break;	
			  }
			  
		  }
		}
		
	}
	
	
	
	
	
	
	
	
	//проверим дату на попадание в закрытый период
	public function CheckClosePdate($id, &$rss, $item=NULL, $periods=NULL){
		$can=true;
		if($item===NULL) $item=$this->GetItemById($id);
		
		$_pch=new PeriodChecker;
		
		//var_dump($item);
		//echo $item['supplier_bill_pdate'];
		//контроль закрытого периода 
		if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $rss=' дата счета контрагента '.$rss23;
			  //echo'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';	
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
		
		if($item['is_confirmed_price']!=1){
			
			$can=$can&&false;
			$reasons[]='у счета не утверждены цены';
			$reason.=implode(', ',$reasons);
		}else{
		
		  
		   //контроль закрытого периода 
		    $reasons=array();
		  if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]=' дата счета контрагента '.$rss23;	
		  }
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
		
		if($item['is_confirmed_price']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у счета утверждены цены';
			$reason.=implode(', ',$reasons);
		}else{
			//контроль закрытого периода 
		    $reasons=array();
			if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
				$can=$can&&false;
				$reasons[]='дата счета контрагента '.$rss23;	
			}
			 $reason.=implode(', ',$reasons);
			 
			 
			 //проверка по числу позиций 
			$can=$can&&$this->CanConfirmByPositions($id,$rss,$item);
			if(strlen($rss)>0){
				if(strlen($reason)>0){
					$reason.=', ';
				}
				$reason.=$rss;
			} 
		}
		
		return $can;
	}
	
	//запрос о возможности снятия утв отгр и возвращение причины, почему нельзя 
	public function DocCanConfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_shipping']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у счета утверждена отгрузка';
			$reason.=implode(', ',$reasons);
		}else{
			//контроль закрытого периода 
		    $reasons=array();
			if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
				$can=$can&&false;
				$reasons[]='дата счета контрагента '.$rss23;	
			}
			 $reason.=implode(', ',$reasons);
			 
			
			/*//проверка по числу позиций 
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
	
	
	//запрос о возможности снятия утв отгр и возвращение причины, почему нельзя 
	public function DocCanUnconfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_shipping']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у счета не утверждена отгрузка';
			$reason.=implode(', ',$reasons);
		}else{
		
		  //проверить связанные поступления
		  $_accg=new AccInGroup;
		  $_accg->setidname('bill_id');
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
			 if(strlen($reason)!=0) $reason.='; ';
			  $reason.=" по счету имеются утвержденные поступления: ";
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  
		  
		  //проверить связанные распоряжения на отгрузку
		  $_accg=new ShIInGroup;
		  $_accg->setidname('bill_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			  if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' распоряжение на приемку № '.$v['id'].'';	
			  }
			  
		  }
		  if(count($reasons)>0) {
			  if(strlen($reason)>0) $reason.='; ';
			  $reason.=" по счету имеются утвержденные распоряжения на приемку: ";
		  }
		  $reason.=implode(', ',$reasons);
		  
		  
		   //контроль закрытого периода 
		    $reasons=array();
		  if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]=' дата счета контрагента '.$rss23;	
		  }
		  if(count($reasons)>0) {
		   if(strlen($reason)!=0) $reason.='; ';
		   $reason.=implode(' ',$reasons);
		  }
		  
		  
		  
		  //проверить связанные доверенности
		 /* $_accg=new TrustGroup;
		  $_accg->setidname('bill_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			//  $dsi=$_dsi->GetItemById($v['status_id']);
			 if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' доверенность № '.$v['id'].'';	
			  }
			  
		  }
		  
		  if(count($reasons)>0) {
			  if(strlen($reason)>0) $reason.=',';
			  $reason.=" по счету имеются утвержденные доверенности: ";
		  }
		  $reason.=implode(', ',$reasons);
		  
		  //проверить связ оплаты
		  $set=new mysqlSet('select * from payment where is_confirmed=1 and id in(select distinct payment_id from payment_for_bill where bill_id="'.$id.'")');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			 // $dsi=$_dsi->GetItemById($v['status_id']);
			  
				  $can=$can&&false;
				  $reasons[]=' оплата № '.$v['code'].'';	
			 
			  
		  }
		  
		  if(count($reasons)>0) {
			  if(strlen($reason)>0) $reason.=',';
			  $reason.=" по счету имеются утвержденные оплаты: ";
		  }
		  $reason.=implode(', ',$reasons);*/
		  
		}
		
		return $can;
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
			if(
				($v['position_id']==$acc_position['id'])
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
	
	
	
	
	public function HasShsorAccs($id){
		 
		  $can=false;
		 
		//проверить связанные поступления
		  $_accg=new AccGroup;
		  $_accg->setidname('bill_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed']==1) {
				  $can=$can||true;
				  //$reasons[]=' поступление № '.$v['id'].'';	
			  }
			  
		  }
		  /*if(count($reasons)>0) {
			// if(strlen($reason)!=0) $reason.='; ';
			//  $reason.=" по счету имеются утвержденные поступления: ";
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  */
		  
		  //проверить связанные распоряжения на отгрузку
		  $_accg=new ShIGroup;
		  $_accg->setidname('bill_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			  if($v['is_confirmed']==1) {
				  $can=$can||true;
				//  $reasons[]=' распоряжение на отгрузку № '.$v['id'].'';	
			  }
			  
		  }
		 /* if(count($reasons)>0) {
			  if(strlen($reason)>0) $reason.='; ';
			 // $reason.=" по счету имеются утвержденные распоряжения на отгрузку: ";
		  }
		  $reason.=implode(', ',$reasons);*/
		  
		  return $can;	
	}
	
	
	
	
	
	//метод для выравнивания по поступлениям
	public function DoEq($id, array $args, &$output, $is_auto=0, $sh=NULL, $_result=NULL, $express_scan=false, $extra_reason=''){
		$output=''; $items=array();
		if($sh===NULL) $sh=$this->GetItemById($id);
		$_sh1=new ShIInItem;
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		
		if($sh['is_confirmed_shipping']==0){
			$output='Выравнивание позиций невозможно: не утверждена отгрузка счета.';
			return;
		}
		
		//проверить число утвержд. поступлений
		$items=$this->ScanEq($id,  $args, $output1, $sh, $express_scan);
		
		$_ni=new BillNotesItem;
		
		//находим все расп. на отгрузку, ровняем их
		if($is_auto==0){
		  foreach($args as $k=>$v){
			  $_t_arr=explode(';',$v);
			  
			  $sql='select sp.quantity, s.id 
			  from sh_i as s inner join sh_i_position as sp on s.id=sp.sh_i_id 
			  where 
			  	s.is_confirmed=1 
				and s.bill_id="'.$id.'" 
				and sp.position_id="'.$_t_arr[0].'" 
				and sp.pl_position_id="'.$_t_arr[1].'"
				and sp.pl_discount_id="'.$_t_arr[2].'"
				and sp.pl_discount_value="'.$_t_arr[3].'"
				and sp.pl_discount_rub_or_percent="'.$_t_arr[4].'"
				and sp.out_bill_id="'.$_t_arr[6].'"
				
				';
			  //echo $sql;
			  
			  $set=new MysqlSet($sql);
			  $rs=$set->GetResult();
			  $rc=$set->GetResultNumRows();
			  for($i=0; $i<$rc; $i++){
				  $f=mysqli_fetch_array($rs);
				  $args_sh=array();
				  $args_sh[]=$_t_arr[0].';'.$_t_arr[1].';'.$_t_arr[2].';'.$_t_arr[3].';'.$_t_arr[4].';'.$f['quantity'].';'.$_t_arr[6];
				  //echo $_t_arr[0].';'.$f['quantity'].';'.$_t_arr[4];
				  $_sh1->DoEq($f['id'], $args_sh,$output);
				  
			  }
			  
		  }
		}
		//выравниваем позиции счета
		
		$_sh_p=new BillInPosItem;
		$_sh_pm=new BillPosPMItem;
		foreach($items as $k=>$v){
			if($v['delta']==0) continue;
			$sh_p=$_sh_p->GetItemByFields(array(
				'bill_id'=>$id, 
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
				
				  $params['quantity']=round(($v['quantity']-$v['delta']),3);
				  
				  //получить +/- его подставить
				  $sh_pm=$_sh_pm->GetItemByFields(array('bill_position_id'=>$sh_p['id']));
				  if($sh_pm!==false){
					  $pms=array(
						  'plus_or_minus'=>$sh_pm['plus_or_minus'],
						  'rub_or_percent'=>$sh_pm['rub_or_percent'],
						  'value'=>$sh_pm['value'],
						  //'discount_plus_or_minus'=>$sh_pm['discount_plus_or_minus'],
						  'discount_rub_or_percent'=>$sh_pm['discount_rub_or_percent'],
						  'discount_value'=>$sh_pm['discount_value']
					  );	
				  }else $pms=NULL;
				  
				  
				  $_sh_p->Edit($sh_p['id'], $params, $pms);
				  
				  $description='Счет №'.$sh['code'].': '.$sh_p['name'].' <br /> Кол-во: '.$v['quantity'].' было изменено на:  '.round($params['quantity'],3).'<br /> ';
				 
				  //создать примечание 
				  if($is_auto==1){
					 $log->PutEntry(0,'автоматическое редактирование позиции счета в связи с выравниванием позиций',NULL,613,NULL,$description.$extra_reason,$id);	 
					 $posted_user_id=0;
					 $note='Автоматическое примечание: позиция счета '.$sh_p['name'].' была выровнена при автоматическом выравнивании, кол-во '.$v['quantity'].' было изменено на '.round($params['quantity'],3).''.$extra_reason;
				  }else{
					 $log->PutEntry($_result['id'],'редактировал позицию счета в связи с выравниванием позиций',NULL,613,NULL,$description.$extra_reason,$id);	
					 
					 $posted_user_id=$_result['id'];
					 $note='Автоматическое примечание: позиция счета '.$sh_p['name'].' была выровнена, кол-во '.$v['quantity'].' было изменено на '.round($params['quantity'],3).''.$extra_reason; 
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
		$output='Выравнивание позиций завершено.';
		if(!$express_scan) $this->ScanDocStatus($id,array(),array());	
	}
	
	
	
	
	//сканирование утвержденных подчиненных докум-тов с позицией
	public function ScanEq($id, array $args, &$output, $sh=NULL, $express_scan=false, $continue_message=".\nПродолжить выравнивание данной позиции?"){
		if($sh===NULL) $sh=$this->GetItemById($id);
		$items=array();
		$total_summ=0; $summ_in_doc=0;
		$output='';
		$docs=array();
		$_pos=new PlPosItem;
		$_pdi=new PosDimItem;
		
		$count_acc=0;
		//перебор по позициям
		foreach($args as $k=>$v){
			$_t_arr=explode(';',$v);
			$summ=0;
			
			/*
		  stri=$("#new_position_id_"+thash).val()+";";  //0
				  stri=stri+$("#new_pl_position_id_"+thash).val()+";";	//1
				  stri=stri+$("#new_pl_discount_id_"+thash).val()+";";	//2
				  stri=stri+$("#new_pl_discount_value_"+thash).val()+";";	//3	
				  stri=stri+$("#new_pl_discount_rub_or_percent_"+thash).val()+";";	//4
				  stri=stri+$("#new_quantity_"+thash).val()+";";	//5
				  stri=stri+$("#new_out_bill_id_"+thash).val();	//6
				  */
			
			
			$summ_in_doc+=$_t_arr[5];
			
			//по каждой позиции перебрать все позиции подчиненных поступлений
			$sql='select * from acceptance_position 
			where acceptance_id in(
				select id from acceptance where 
					is_confirmed=1 
					
					and bill_id="'.$id.'" 
				) 
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
				$count_acc+=$f['quantity'];
				if(!in_array('№'.$f['acceptance_id'],$docs)) $docs[]='№'.$f['acceptance_id'];
			}
			
			$pos=$_pos->GetItemById($_t_arr[0]);
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
			
			
		
		
		$docs1=array();
		$count_sh=0;
		
		if(!$express_scan){
		
		  //перебор по позициям
		  foreach($args as $k=>$v){
			  $_t_arr=explode(';',$v);
			  $summ=0;
			  
			  
			  //по каждой позиции перебрать все позиции подчиненных расп на отгруз
			  $sql='select * from sh_i_position where  
			  sh_i_id in(
			  		select id from sh_i where 
						is_confirmed=1 
						and bill_id="'.$id.'" 
						
					) 
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
				  $count_sh+=$f['quantity'];
				  if(!in_array('№'.$f['sh_i_id'],$docs1)) $docs1[]='№'.$f['sh_i_id'];
			  }
			  
		  
			  $total_summ+=$summ;	
			  
		  }
		}
		
		
		if($total_summ==0){
			$output.="\nПозиция ".htmlspecialchars($pos["name"])." не найдена ни в одном утвержденном подчиненном документе. Количество будет обнулено. Продолжить выравнивание данной позиции?";
		}else{
			
			if(!$express_scan){
			  //в распоряжениях
			  if(count($docs1)){
				   $output.="\nПозиция ".htmlspecialchars($pos["name"])." найдена в утвержденных распоряжениях на приемку: ".implode(", ",$docs1)." в количестве ".$count_sh." ".htmlspecialchars($pdi["name"]);
				   
					if($count_sh>$summ_in_doc){
					   $output.=', что превышает количество в cчете '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).'';
				   }
			  }else $output.="\nПозиция ".htmlspecialchars($pos["name"])." не найдена в утвержденных распоряжениях на приемку.";
			}
			
			
			
			//в поступлениях 
			if(count($docs)){
				 $output.="\nПозиция ".htmlspecialchars($pos["name"])." найдена в утвержденных поступлениях: ".implode(", ",$docs)." в количестве ".$count_acc." ".htmlspecialchars($pdi["name"]);
				 if($count_acc>$summ_in_doc){
					 $output.=', что превышает количество в cчете '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).'';
				 }elseif($count_acc<$summ_in_doc){
					 $output.=', что меньше количества в cчете '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).'';
				 }
			}else $output.="\nПозиция ".htmlspecialchars($pos["name"])." не найдена в утвержденных поступлениях.";
			
			if($count_acc>=$summ_in_doc){
				$output.=".\nПозиция выравниванию не подлежит.";
			}else $output.=$continue_message; //".\nПродолжить выравнивание данной позиции?";
			//.
		}
		
		
		
		
		
		
		return $items;
	}
	
	
	
	
	
	
	
	
	
	
	//контроль возможности редактирования kol-va позиций
	public function CanEditQuantities($id, &$reason, $itm=NULL){
		$can_delete=true;
		
		$reason='';
		
		if($itm===NULL) $itm=$this->GetItemById($id);
		
		if(($itm!==false)&&(($itm['is_confirmed_price']!=0)||($itm['is_confirmed_shipping']!=0))) {
			$reason.='счет утвержден';
			$can_delete=$can_delete&&false;
		}
		
		
		$set=new mysqlSet('select * from sh_i where bill_id="'.$id.'" and is_confirmed=1');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		if($rc>0){
			if(strlen($reason)>0) $reason.=', ';
			$reason.='по счету имеются утвержденные распоряжения на приемку: ';
		 	$nums=array();
			for($i=0;$i<$rc;$i++){
				$f=mysqli_fetch_array($rs);
				$nums[]='№'.$f['id'];
				
			}
			$reason.=implode(', ',$nums);
			$can_delete=$can_delete&&false;
		}
		
		$set=new mysqlSet('select * from acceptance where is_confirmed=1 and sh_i_id in(select id from sh_i where bill_id="'.$id.'" and is_confirmed=1)');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		if($rc>0){
			if(strlen($reason)>0) $reason.=', ';
			$reason.='по счету имеются утвержденные поступления: ';
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
	
	
	//проверка возможности редактирования "счет в бухгалтерии"
	public function CanIsInBuh($bill_id, &$rss, $bill_item=NULL, $can_confirm_in_buh=false, $can_unconfirm_in_buh=false, $summ_by_bill=NULL, $summ_by_payed=NULL){
		$can_is_in_buh=true;
		
		//echo '<h1>zzzz</h1>';
		
		$_rss=array();
		if($bill_item===NULL) $bill_item=$this->getitembyid($bill_id);
		
		if($summ_by_bill===NULL) $summ_by_bill=$this->CalcCost($bill_id);
		if($summ_by_payed===NULL) $summ_by_payed=$this->CalcPayed($bill_id);
		
		
		if($bill_item['is_in_buh']==1){
			$can_is_in_buh=$can_is_in_buh&&$can_unconfirm_in_buh;
			
			if(!$can_unconfirm_in_buh) $_rss[]=' у Вас недостаточно прав для снятия опции, пожалуйста, обратитесь к администратору для получения прав доступа';
		}else{
			$can_is_in_buh=$can_is_in_buh&&$can_confirm_in_buh;
			if(!$can_confirm_in_buh) $_rss[]=' у Вас недостаточно прав для установки опции, пожалуйста, обратитесь к администратору для получения прав доступа';
		}
		
		if(!(($bill_item['is_confirmed_price']==1)&&($bill_item['is_confirmed_shipping']==1))){
			$can_is_in_buh=$can_is_in_buh&&$false;	
			$_rss[]=' у счета должны быть утверждены цены и отгрузка ';
		}
		
		
		if($summ_by_bill<=$summ_by_payed){
			$can_is_in_buh=$can_is_in_buh&&$false;	
			$_rss[]=' счет полностью оплачен ';	
		}
		
		
		if($bill_item['is_in_buh']==1){
			if($bill_item['user_in_buh_id']==-1) $_rss[]=' наличие счета в бухгалтерии утверждено автоматически на основании 100% оплаты счета';
		}
		
		$rss=implode(', ',$_rss);
		
		return $can_is_in_buh;
	}
	
		
		
	
	
	//уменьшение суммы привязанных к счету оплат при уменьшении сумммы счета
	public function LowPayments($id, $_result=NULL, $old_bill_summ=0, $new_bill_summ=NULL, $calc_payed=NULL, $actor_id=NULL){
		
		$log=new ActionLog;
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		
		if($new_bill_summ===NULL) $new_bill_summ=$this->CalcCost($id);
		if($calc_payed===NULL) $calc_payed=$this->CalcPayed($id);
		
		
		$_pbi=new PayForBillItem;
		$_pi=new PayItem;
		
		//уменьшили сумму счета, и по счету были оплаты
		//если сумма оплат больше, чем сумма счета - уменьшить сумму оплат.
		if(($new_bill_summ<$old_bill_summ)&&($calc_payed>0)&&($calc_payed>$new_bill_summ)){
			$delta=$calc_payed-$new_bill_summ;
			
			//перебираем прикрепленные к счету инв акты, оплаты
			$sql='select pb.*, p.code as invcalc_code, b.code as bill_code, pp.code as payment_code
			 from payment_for_bill as pb
				left join invcalc as p on p.id=pb.invcalc_id
				left join bill as b on b.id=pb.bill_id
				left join payment as pp on pb.payment_id=pp.id
			where pb.bill_id="'.$id.'"';
			
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				if($delta<=0) break;
				
				//$delta_local=$delta-(float)$f['value'];
				if($delta>=(float)$f['value']){
					//сумма прикрепления меньше, чем разница
					//выбросить это прикрепление полностью
					$_pbi->Del($f['id']);
					
					//автоматическое примечание (в счет, оплату, инвкальк)
					if($f['payment_id']!=0){
						$note='исходящая оплата № '.$f['payment_code'].': удален платеж по счету '.$f['bill_code'].' на сумму '.$f['value'].' руб. на основании уменьшения суммы счета с '.$old_bill_summ.' руб. на '.$new_bill_summ.' руб. при утверждении поступления №'.$actor_id.' без довоза.';	
					}elseif($f['invcalc_id']!=0){
						$note='акт № '.$f['invcalc_code'].': удален платеж по счету '.$f['bill_code'].' на сумму '.$f['value'].' руб. на основании уменьшения суммы счета с '.$old_bill_summ.' руб. на '.$new_bill_summ.' руб. при утверждении поступления №'.$actor_id.' без довоза.';
					}
					
					
					$delta-=(float)$f['value'];
						
				}else{
					//сумма прикрепления больше, чем разница
					//уменьшить сумму прикрепления	
					$_pbi->Edit($f['id'], array('value'=>((float)$f['value']-$delta)));
					
					//автоматическое примечание (в счет, оплату, инвкальк)
					if($f['payment_id']!=0){
						$note='исходящая оплата № '.$f['payment_code'].': уменьшен платеж по счету '.$f['bill_code'].' с суммы '.$f['value'].' руб. на сумму '.((float)$f['value']-$delta).' руб. на основании уменьшения суммы счета с '.$old_bill_summ.' руб. на '.$new_bill_summ.' руб. при утверждении поступления №'.$actor_id.' без довоза.';	
					}elseif($f['invcalc_id']!=0){
						$note='акт № '.$f['invcalc_code'].': уменьшен платеж по счету '.$f['bill_code'].' с суммы '.$f['value'].' руб. на сумму '.((float)$f['value']-$delta).' руб. на основании уменьшения суммы счета с '.$old_bill_summ.' руб. на '.$new_bill_summ.' руб. при утверждении поступления №'.$actor_id.' без довоза.';	
					}
					
					$delta=0;
				}
				
				
				
				
				//автоматическое примечание (в счет, оплату, инвкальк)
				$_bni=new BillNotesItem;	
				if($f['payment_id']!=0){
					$log->PutEntry($_result['id'],'удаление платежа по счету из исходящей оплаты',NULL,613,NULL,$note,$f['bill_id']);
		
					$log->PutEntry($_result['id'],'удаление платежа по счету из исходящей оплаты',NULL,272,NULL,$note,$f['payment_id']);
					
					$_pni=new PaymentNotesItem;	
					
					 $_pni->Add(array(
							  'user_id'=>$f['payment_id'],
							  'is_auto'=>1,
							  'pdate'=>time(),
							  'posted_user_id'=>$_result['id'],
							  'note'=>$note
						  
					));
				}elseif($f['invcalc_id']!=0){
					$log->PutEntry($_result['id'],'удаление платежа по счету из инвентаризационного акта',NULL,613,NULL,$note,$bill_id);
		
					$log->PutEntry($_result['id'],'удаление платежа по счету из инвентаризационного акта',NULL,452,NULL,$note,$f['invcalc_id']);
					
					$_pni=new InvCalcNotesItem();
					 $_pni->Add(array(
							  'user_id'=>$f['invcalc_id'],
							  'is_auto'=>1,
							  'pdate'=>time(),
							  'posted_user_id'=>$_result['id'],
							  'note'=>$note
						  
					));
				}
				
				 $_bni->Add(array(
							  'user_id'=>$f['bill_id'],
							  'is_auto'=>1,
							  'pdate'=>time(),
							  'posted_user_id'=>$_result['id'],
							  'note'=>$note
						  
				));
				
				//переприкрепить аванс к счетам...
				if($f['payment_id']!=0){
					//привязать аванс оплаты к счетам
					//$_pi->BindPayments(	$f['payment_id'], $_result['org_id']);	
					//сделать позже
				}
			}
		}
	}
	
	
}
?>