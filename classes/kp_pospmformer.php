<?
require_once('abstractitem.php');
require_once('pl_curritem.php');
require_once('pl_positem.php');
require_once('pl_pospriceitem.php');
require_once('price_kind_item.php');
require_once('authuser.php');
require_once('actionlog.php');
require_once('kppositem.php');
require_once('kpnotesitem.php');

require_once('pl_pospriceitem.php');
require_once('pl_positem.php');

require_once('kpitem.php');

require_once('round_define.class.php');


//механизм построения цен и стоимости КП
class KpPosPMFormer{
	
	public $_round_define;
	
	function __construct(){
		$this->_round_define=new RoundDefine;	
	}
	
	
	//считаем общую стоимость по введенным позициям
	public function CalcCost(&$positions, $apply_delivery_ddpm=false, $synhro_prices=false, $result=NULL){
		//$apply_delivery_ddpm - нигде не используется
		//$synhro_prices - синхронизировать цены согласно тек. п/л (вызывать при утв. КП)
		if($synhro_prices){
			$au=new AuthUser;
			if($result===NULL) $result=$au->Auth();
		}
		
		$cost=0;
				
		//найти главную позицию
		$main_id=0; $main_index=-1;
		
		$count_of_options=0;
		foreach($positions as $k=>$v){
			if($v['parent_id']==0){
				 $main_id=$v['pl_position_id'];	
				 $main_index=$k;
				 break;
			} 
		}
		
		foreach($positions as $k=>$v){
			if($v['parent_id']!=0) $count_of_options++;
		}
		
		
		//проверить, есть ли вообще опции... если они есть - то работать по алгоритму с опциями:
		//вносить общую стоимость в первую позицию КП
		
		//если опций нет - то стоимость=сумме по всем позициям
		
		
		
		
		if($count_of_options>0){
			//!!!!!!!! первичное выполнение блока - найдем старую цену без изменений в ценах
			//считать собственную цену гл. позиции со скидкой
			$price_f_main=$this->CalcPriceFMain($main_index, $positions);
			//echo $price_f_main; die();
			
			//считать собственную цену не главных позиций  со скидкой
			$this->CalcPriceFNotMain($main_index, $positions);
			
				
			
			//найти общую стоимость
			$cost=$this->CalcAllCost($main_index, $positions);
			
		}else{
			//найти общую стоимость
			//$cost=$this->CalcAllCost($main_index, $positions);
			
			$cost=$this->CalcSimpleCost($positions);
		}
		$old_total=$cost;
		
		
		if($count_of_options>0){
			//!!!!!!!!! повторное выполнение блока - найдем и внесем все новые цены
			
			//считать собственную цену гл. позиции со скидкой
			$price_f_main=$this->CalcPriceFMain($main_index, $positions,  $synhro_prices, $result);
			//echo $price_f_main; die();
			
			//считать собственную цену не главных позиций  со скидкой
			$this->CalcPriceFNotMain($main_index, $positions,  $synhro_prices, $result);
			
				
			//найти общую стоимость
			$cost=$this->CalcAllCost($main_index, $positions);
			
		}else{
			//найти общую стоимость
			//$cost=$this->CalcAllCost($main_index, $positions);
			$cost=$this->CalcSimpleCost($positions);
		}
		
		//если мониторим изменения цен - внести сведения об изменении стоимости КП (итог. цена гл. позиции)
		$log=new ActionLog; $_kpi=new KpPosItem; $_kni=new KpNotesItem; $_curr=new PlCurrItem; $_kp=new KpItem;
		
		if($synhro_prices&&($old_total!=$cost)){
			//если синхронизируем цены и прошло изменение цены - то сохранить эти записи в БД
			//обновить total
			$currency=$_curr->GetItemById($positions[$main_index]['currency_id']);
			$kp=$_kp->GetItemById($positions[$main_index]['kp_id']);
			   
			$_kpi->Edit($positions[$main_index]['p_id'], array( 
										 
										  'total'=>$cost));
			
			$log->PutEntry($result['id'],'автоматическое изменение суммы при утверждении коммерческого предложения',NULL, 701,NULL,SecStr('автоматически изменилась сумма при утверждении коммерческого предложения '.$kp['code'].',  старая сумма '.$old_total.' '.$currency['signature'].', новая сумма '.$cost.' '.$currency['signature'].''),$positions[$main_index]['kp_id']);
			
			$notes_params=array();
			$notes_params['is_auto']=1;
			$notes_params['user_id']=$kp['id'];
			$notes_params['pdate']=time();
			$notes_params['posted_user_id']=$result['id'];
			
			$notes_params['note']='Автоматическое примечание: автоматически изменилась сумма при утверждении коммерческого предложения '.$kp['code'].' пользователем '.SecStr($result['name_s'].' '.$result['login'].', старая сумма '.$old_total.' '.$currency['signature'].', новая сумма '.$cost.' '.$currency['signature'].'');
			$_kni->Add($notes_params); 		  
			  
		}
		
		if($count_of_options>0){
			//внести в общую стоимость главной позиции общую стоимость всего КП
			
			$positions[$main_index]['total']=$cost;
		}
		
		
		//пересортируем массив - главную позицию в начало
		
		$old_array=$positions;
		$new_array=array();
		
		$new_array[]=$positions[$main_index];
		
		foreach($positions as $k=>$v){
			//if(($v['parent_id']!=0)){
			if($k!=$main_index){	
				
				$new_array[]=$v;
			}
			//}
			
		}
		
		
			
		
		
		$positions=$new_array;
		
		return $cost;	
	}
	
	

	
	
	
	public function CalcPriceFMain($index, &$positions,  $synhro_prices=false,  $result=NULL){
		$log=new ActionLog; $_kpi=new KpPosItem; $_kni=new KpNotesItem; $_curr=new PlCurrItem; $_kp=new KpItem;
		
		
		 
		
		$digits=$this->_round_define->DefineDigits($positions[$index]['two_group_id']);
		
		 
		
		//синхронизировать цены
		$was_changed=false;
		if($synhro_prices){
			  //найдем исходный элемент
			  //найдем его цену текущего вида
			  //если цена отличается - обновить цену в данной записи
			  //запись в журнал событий и автом. прим.
			  $_current_pl=new PlPosItem;
			  $current_pl=$_current_pl->GetItemById($positions[$index]['pl_position_id']);
			  $current_price=$_current_pl->DispatchCalcPrice(
			  	$positions[$index]['pl_position_id'], 
				$positions[$index]['currency_id'],
				NULL,
				$positions[$index]['price_kind_id'],
				NULL,
				NULL,
				NULL,
				$positions[$index]['extra_charges']
				);
			  $currency=$_curr->GetItemById($positions[$index]['currency_id']);
			  $kp=$_kp->GetItemById($positions[$index]['kp_id']);
			  
			  if((float)$current_price!=(float)$positions[$index]['price']){
				  $was_changed=true;
				  
				  
				  $_kpi->Edit($positions[$index]['p_id'], array('price'=>$current_price));
				  
				  $log->PutEntry($result['id'],'автоматическое изменение цены при утверждении коммерческого предложения',NULL, 701,NULL,SecStr('автоматически изменилась цена позиции при утверждении коммерческого предложения '.$kp['code'].', позиция '.$positions[$index]['position_name'].' старая цена '.$positions[$index]['price'].' '.$currency['signature'].', новая цена '.$current_price.' '.$currency['signature']),$positions[$index]['kp_id']);
				  
				  $notes_params=array();
				  $notes_params['is_auto']=1;
				  $notes_params['user_id']=$kp['id'];
				  $notes_params['pdate']=time();
				  $notes_params['posted_user_id']=$result['id'];
				  
				  $notes_params['note']='Автоматическое примечание: автоматически изменилась цена позиции при утверждении коммерческого предложения '.$kp['code'].' пользователем '.SecStr($result['name_s'].' '.$result['login'].', позиция '.$positions[$index]['position_name'].' старая цена '.$positions[$index]['price'].' '.$currency['signature'].', новая цена '.$current_price.' '.$currency['signature']);
				  $_kni->Add($notes_params);	
				  
				  $positions[$index]['price']=$current_price;
					  
			  }
		}
		
		
		$price=(float)$positions[$index]['price'];
		//echo $price.' ';
		
		 
			if($positions[$index]['pl_discount_rub_or_percent']==0) $price-=(float)$positions[$index]['pl_discount_value'];
			else $price-=$price*((float)$positions[$index]['pl_discount_value']/100);
		
		
		$old_price_f= $positions[$index]['price_f'];
		
		 $positions[$index]['price_f']=round($price, $digits);
		 
		
		 $positions[$index]['price_pm']=round($price, $digits);
		 
		
		// echo $price.' ';
		
		
		//$cost_main= $price*$positions[$index]['quantity'];
		$old_total=$positions[$index]['cost'];
		
		 $positions[$index]['cost']=round($price*$positions[$index]['quantity'],  $digits);
		 
		 $positions[$index]['total']=round($price*$positions[$index]['quantity'],  $digits);
		 
		
		//echo  $positions[$index]['total'];
		
		//если синхронизируем цены и прошло изменение цены - то сохранить эти записи в БД
		if($synhro_prices&&$was_changed){
			  $_kpi->Edit($positions[$index]['p_id'], array('price_f'=>$positions[$index]['price_f'],
											'price_pm'=>$positions[$index]['price_pm'],
										   
											'total'=>$positions[$index]['total']));
			  
			  $log->PutEntry($result['id'],'автоматическое изменение цены при утверждении коммерческого предложения',NULL, 701,NULL,SecStr('автоматически изменилась итоговая цена позиции при утверждении коммерческого предложения '.$kp['code'].', позиция '.$positions[$index]['position_name'].' старая итоговая цена '.$old_price_f.' '.$currency['signature'].', новая итоговая цена '.$positions[$index]['price_f'].' '.$currency['signature'].''),$positions[$index]['kp_id']);
			  
			  $notes_params=array();
			  $notes_params['is_auto']=1;
			  $notes_params['user_id']=$kp['id'];
			  $notes_params['pdate']=time();
			  $notes_params['posted_user_id']=$result['id'];
			  
			  $notes_params['note']='Автоматическое примечание: автоматически изменилась итоговая цена позиции при утверждении коммерческого предложения '.$kp['code'].' пользователем '.SecStr($result['name_s'].' '.$result['login'].', позиция '.$positions[$index]['position_name'].' старая итоговая цена '.$old_price_f.' '.$currency['signature'].', новая итоговая цена '.$positions[$index]['price_f'].' '.$currency['signature'].'');
			  $_kni->Add($notes_params);			  
		}
		
			
		  return round($price,  $digits);
		 
		
	}
	
	public function CalcPriceFNotMain($index, &$positions,  $synhro_prices=false, $result=NULL){
		$log=new ActionLog; $_kpi=new KpPosItem; $_kni=new KpNotesItem; $_curr=new PlCurrItem; $_kp=new KpItem;
		foreach($positions as $k=>$v){
			if($v['parent_id']!=0){
			  $digits=$this->_round_define->DefineDigits($v['two_group_id']);
			  
			   
			  
			  //синхронизировать цены
			  $was_changed=false;
			  if($synhro_prices){
			  		//найдем исходный элемент
					//найдем его цену текущего вида
					//если цена отличается - обновить цену в данной записи
					//запись в журнал событий и автом. прим.
					$_current_pl=new PlPosItem;
					$current_pl=$_current_pl->GetItemById($v['pl_position_id']);
					$current_price=$_current_pl->DispatchCalcPrice($v['pl_position_id'], $v['currency_id'],NULL,$v['price_kind_id']);
					$currency=$_curr->GetItemById($v['currency_id']);
					$kp=$_kp->GetItemById($v['kp_id']);
					
					if((float)$current_price!=(float)$positions[$k]['price']){
						$was_changed=true;
						
						
						$_kpi->Edit($v['p_id'], array('price'=>$current_price));
						
						$log->PutEntry($result['id'],'автоматическое изменение цены при утверждении коммерческого предложения',NULL, 701,NULL,SecStr('автоматически изменилась цена позиции при утверждении коммерческого предложения '.$kp['code'].', позиция '.$v['position_name'].' старая цена '.$positions[$k]['price'].' '.$currency['signature'].', новая цена '.$current_price.' '.$currency['signature']),$v['kp_id']);
						
						$notes_params=array();
						$notes_params['is_auto']=1;
						$notes_params['user_id']=$kp['id'];
						$notes_params['pdate']=time();
						$notes_params['posted_user_id']=$result['id'];
						
						$notes_params['note']='Автоматическое примечание: автоматически изменилась цена позиции при утверждении коммерческого предложения '.$kp['code'].' пользователем '.SecStr($result['name_s'].' '.$result['login'].', позиция '.$v['position_name'].' старая цена '.$positions[$k]['price'].' '.$currency['signature'].', новая цена '.$current_price.' '.$currency['signature']);
						$_kni->Add($notes_params);	
						
						$positions[$k]['price']=$current_price;
							
					}
			  }
			  
			  
			  
			  
			  $price=(float)$positions[$k]['price'];
			  
			 /* echo '<pre>';
			  print_r($v);
			  echo '</pre>';
			  */
			  
			  //не учитывать в этом  блоке скидку менеджера, если опция - это ПНР:	
			  if(!isset($v['is_install'])) {
				  $_pl=new PlPosItem;
				  $pl=$_pl->GetItemById($v['pl_position_id']);
				  $v['is_install']=$pl['is_install'];
				 ;
			  }
			  if(!isset($v['is_calc_price'])) {
				  $_pl=new PriceKindItem;
				  $pl=$_pl->GetItemById($v['price_kind_id']);
				  $v['is_calc_price']=$pl['is_calc_price'];
				   //var_dump($pl);
			  }
			  
			  //echo $v['pl_position_id'].' is_install='.$v['is_install'].' is_calc_price='.$v['is_calc_price'].' $v[price_kind_id]='.$v['price_kind_id'].'<br>';
			  
			  if(!(($v['is_install']==1)&&($v['is_calc_price']==1))){
		 		//not PNR - Работаем как обычно
			  	if($positions[$index]['pl_discount_rub_or_percent']==0) $price-=(float)$positions[$index]['pl_discount_value'];
			  	else $price-=$price*((float)$positions[$index]['pl_discount_value']/100);
			  }else{
				 // echo 'PNR!!!!!!!!!!!';	
				  //  если скидка руководителя - применим ее
				  if($v['pl_discount_id']==2){
					  if($v['pl_discount_rub_or_percent']==0) $price-=(float)$v['pl_discount_value'];
			  		  else $price-=$price*((float)$v['pl_discount_value']/100);
					   //echo $price;
				  }
			  }
			  $old_price_f= $positions[$k]['price_f'];
			  
			 
			    $positions[$k]['price_f']=round($price, $digits);	
			  
			  
			 $positions[$k]['price_pm']=round($price, $digits);	
			  
			  
			  
			  
			  $old_total=$positions[$k]['cost'];
			  
			   $positions[$k]['cost']=round($price*$positions[$k]['quantity'],  $digits);
			   
			  
			   $positions[$k]['total']=round($price*$positions[$k]['quantity'], $digits);
			   
			  
			  //если синхронизируем цены и прошло изменение цены - то сохранить эти записи в БД
			  if($synhro_prices&&$was_changed){
			  		$_kpi->Edit($v['p_id'], array('price_f'=>$positions[$k]['price_f'],
												  'price_pm'=>$positions[$k]['price_pm'],
												 
												  'total'=>$positions[$k]['total']));
					
					$log->PutEntry($result['id'],'автоматическое изменение цены при утверждении коммерческого предложения',NULL, 701,NULL,SecStr('автоматически изменилась итоговая цена позиции при утверждении коммерческого предложения '.$kp['code'].', позиция '.$v['position_name'].' старая итоговая цена '.$old_price_f.' '.$currency['signature'].', новая итоговая цена '.$positions[$k]['price_f'].' '.$currency['signature'].''),$v['kp_id']);
					
					$notes_params=array();
					$notes_params['is_auto']=1;
					$notes_params['user_id']=$kp['id'];
					$notes_params['pdate']=time();
					$notes_params['posted_user_id']=$result['id'];
					
					$notes_params['note']='Автоматическое примечание: автоматически изменилась итоговая цена позиции при утверждении коммерческого предложения '.$kp['code'].' пользователем '.SecStr($result['name_s'].' '.$result['login'].', позиция '.$v['position_name'].' старая итоговая цена '.$old_price_f.' '.$currency['signature'].', новая итоговая цена '.$positions[$k]['price_f'].' '.$currency['signature'].'');
					$_kni->Add($notes_params);			  
			  }
			  
			  //echo "price=$price; total= ".$positions[$k]['total']."<br>";		
			}
		}
	}
	
	
	
	
	
	protected function CalcAllCost($index, &$positions){
		$sum=0;
		
		$sum+=$positions[$index]['total'];
		
		foreach($positions as $k=>$v){
			if($v['parent_id']!=0){
				$sum+=$positions[$k]['total'];
		
			}
		}
		
		return $sum;	
	}
	
	
	public function CalcNDS(array $positions){
		$cost=0;
		
		$cost=sprintf("%.2f",($this->CalcCost($positions)-$this->CalcCost($positions)/((100+NDS)/100)));
		
		return $cost;	
	}
	
	
	public function GetCurrency($positions){
		//найти главную позицию
		$main_id=0; $main_index=-1;
		
		foreach($positions as $k=>$v){
			if($v['parent_id']==0){
				 $main_id=$v['pl_position_id'];	
				 $main_index=$k;
				 break;
			}
		}
		
		if(!isset($positions[$main_index]['signature'])){
			$_curr=new PlCurrItem;
			$currency=$_curr->GetItemById($positions[$main_index]['currency_id']);
			$signature=$currency['signature'];
		}else $signature=$positions[$main_index]['signature'];
		
		$currency=array('currency_id'=>$positions[$main_index]['currency_id'],
						'signature'=>$signature);
		return $currency;
	}
	
	
	
	
	//найдем рентабельность
	public function CalcProfit($positions, &$profit_percent, &$profit_value, &$profit_currency){
		
		$cost=$this->CalcCost($positions);
		
		$_profit_currency=$this->GetCurrency($positions);
		$profit_currency=$_profit_currency['currency_id'];
		
		
		$main=array();
		foreach($positions as $k=>$v){
			if($v['parent_id']==0){
				$main=$v;
			}			
		}
		
		//print_r($main);
		//другой алгоритм. ищем стоимость текущую,
		//ищем стоимость по ценам поставщика (price_kind_id=3)
		
		$cost_supplier=$this->CalcCostSupplier($positions);
		
	/*	echo $cost; 
		echo '   '.$cost_supplier;
		*/
		
		$profit_value=$cost-$cost_supplier;
		$profit_percent=round(100*$profit_value/$cost);
		
		
		
		
	}
	
	//стоимость по ценам поставщика (без скидок)!
	public function CalcCostSupplier($positions){
		$cost=0;	
		
		$_price=new PlPositionPriceItem; $_pi=new PlPosItem;
		$currency=$this->GetCurrency($positions);
		
		$_currency_solver=new CurrencySolver;
		$rates=$_currency_solver->GetActual();
		
		$group_id=0;
		
		$main=array();
		foreach($positions as $k=>$v){
			if($v['parent_id']==0){
				$main=$v;
			}			
		}
		
		foreach($positions as $k=>$v){
			
			$group_id=$v['two_group_id'];
			
			$price=$_price->GetItemByFields(array('currency_id'=>$currency['currency_id'], 'price_kind_id'=>3, 'pl_position_id'=>$v['pl_position_id']));
			$pi=$_pi->GetItemById($v['pl_position_id']);
			
			$current_producer_id=$pi['producer_id'];
			$calculation_form_id=$_pi->GetCalculationFormId($current_producer_id);
			
			//echo $calculation_form_id; die();
			
			//если КП ddpm - то базовая цена ddpm
			//если КП exw - то базовая цена exw
			
			$price=$_pi->DispatchCalcPriceF($v['pl_position_id'],   $currency['currency_id'],   NULL, NULL, NULL,  NULL,  NULL,   3);
			
			if($main['price_kind_id']==1){
				//добавить все добавки	
				if($v['parent_id']==0){ 
				//оборудование
					
					
					if($calculation_form_id==1){
						//считать НДС
						$v['nds']=$_pi->CalcNDS($price, 	$v['pl_position_id'], $currency['currency_id'], NULL, 3, NULL,   true);
						
						//страховка
						$v['insur']=$_pi->CalcInsur($price, $v['nds'],   $v['pl_position_id'], $currency['currency_id'], NULL, 3, NULL, true);
						
						//комиссия
						$v['commission']=$_pi->CalcComission($price, $v['nds'],   $v['pl_position_id'], $currency['currency_id'], NULL, NULL,  3, true);
						
						
						
						
						//итоговая цена ddpm
						$price=$price+$pi['delivery_ddpm']+$v['nds']+$pi['duty_ddpm']+$pi['svh_broker']+$v['insur']+$v['commission'];
					}elseif($calculation_form_id==2){
						
						$v['customs_value']=$_pi->CalcCustomsValue($price, $v['pl_position_id'], $currency['currency_id'], NULL, 3, NULL, true, $rates);
						
						
						$v['delivery_rub_p']=$_pi->CalcDeliveryRub($v['pl_position_id'], $currency['currency_id'],  NULL, 3, NULL, $rates, true);
						
						//считать НДС
						$v['nds']=$_pi->CalcNDS($price, 	$v['pl_position_id'], $currency['currency_id'], NULL, 3, NULL,   true);
						
						//страховка
						$v['insur']=$_pi->CalcInsur($price, $v['nds'],   $v['pl_position_id'], $currency['currency_id'], NULL, 3, NULL, true);
						
						//комиссия
						//$v['commission']=$_pi->CalcComission($price, $v['nds'],   $v['pl_position_id'], $currency['currency_id'], NULL, NULL,  3, true);
						
						
						$price=$price+$pi['delivery_value']+$v['delivery_rub_p']+$v['nds']+$pi['duty_ddpm']+$pi['svh_broker']+$v['insur']/*+$v['commission']*/+ $v['customs_value'] + $pi['broker_costs'];
						
						 
					}
					
					 
				}else{
				//опция
					 
					$price=$_pi->CalcOptionPriceF($v['pl_position_id'], $currency['currency_id'],   NULL, NULL, NULL,  NULL,  NULL,  3  );
					
					
					if($calculation_form_id==1){
					
						//считать НДС
						$v['nds']=$_pi->CalcOptionNDS($price, 	$v['pl_position_id'], $currency['currency_id'], NULL,  3,NULL,true);
						
						//страховка
						$v['insur']=$_pi->CalcOptionInsur($price, $v['nds'],   $v['pl_position_id'], $currency['currency_id'], NULL, 3,NULL, true);
						
						//комиссия
						$v['commission']=$_pi->CalcOptionComission($price, $v['nds'],   $v['pl_position_id'], $currency['currency_id'], NULL, 3, NULL, true);
						
						//итоговая цена ddpm
						$price=$price+$pi['delivery_ddpm']+$v['nds']+$pi['duty_ddpm']+$pi['svh_broker']+$v['insur']+$v['commission'];
					 
					
					}elseif($calculation_form_id==2){
						//$v['delivery_rub_p']=$_pi->CalcDeliveryRub($v['pl_position_id'], $currency['currency_id'], NULL, $main['price_kind_id'], NULL, $rates, true);
						
						//$price=$price;+$pi['delivery_value']+$v['delivery_rub_p'];
						
						$v['customs_value']=$_pi->CalcCustomsValue($price, $v['pl_position_id'], $currency['currency_id'], NULL,3, NULL,true,$rates);
						
						$v['nds']=$_pi->CalcOptionNDS($price, 	$v['pl_position_id'], $currency['currency_id'], NULL,  3,NULL,true);
						
						//страховка
						$v['insur']=$_pi->CalcOptionInsur($price, $v['nds'],   $v['pl_position_id'], $currency['currency_id'], NULL, 3,NULL, true);
						
						//комиссия
						//$v['commission']=$_pi->CalcOptionComission($price, $v['nds'],   $v['pl_position_id'], $currency['currency_id'], NULL, 3, NULL, true);
						
						//итоговая цена ddpm
						$price=$price+$v['nds']+$pi['duty_ddpm']+$pi['svh_broker']+$v['insur']/*+$v['commission']*/+ $v['customs_value'] + $pi['broker_costs'];
					}
					
				}
			}
			
			//echo $v['pl_position_id'].' '. $price.'<br>';
			
			$cost=$cost+$price*$v['quantity'];
			
			//echo $pi['id'].' ' .$price*$v['quantity'].'<br>';
		}
		//echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
		
		$digits=$this->_round_define->DefineDigits($group_id);
		
		 
		 return round($cost, $digits);
	}
	
	
	
	//старый расчет итого - для КП без опций
	protected function CalcSimpleCost($positions){
		$total=0; $group_id=0;
		foreach($positions as $index=>$v){
			$price=(float)$positions[$index]['price'];
			//echo $price.' ';
			
			$group_id=$positions[$index]['two_group_id'];
			
			$digits=$this->_round_define->DefineDigits($positions[$index]['two_group_id']);
			
			 
			if($positions[$index]['pl_discount_rub_or_percent']==0) $price-=(float)$positions[$index]['pl_discount_value'];
			else $price-=$price*((float)$positions[$index]['pl_discount_value']/100);
		
			
		 
			
			 $positions[$index]['price_f']=round($price,  $digits);  // -1);
			 
			
			 $positions[$index]['price_pm']=round($price, $digits);  //-1);
			 
			// echo $price.' ';
			
			//echo $digits. '  ';
			 
			
			 $positions[$index]['cost']=round($price*$positions[$index]['quantity'], $digits);  //-1);
			 
			 $positions[$index]['total']=round($price*$positions[$index]['quantity'],  $digits); //-1);
			 
			$total+=$price*$positions[$index]['quantity'];
		}
		
		
		$digits=$this->_round_define->DefineDigits( $group_id);
			
		//echo $digits;
		return round($total,$digits);
	}
}
?>