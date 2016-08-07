<?
require_once('abstractitem.php');
require_once('pl_curritem.php');
require_once('pl_positem.php');
require_once('pl_pospriceitem.php');
require_once('price_kind_item.php');

//механизм построени€ цен и стоимости  ѕ
class KpPosPMFormer{
	
	
	
	
	
	//считаем общую стоимость по введенным позици€м
	public function CalcCost(&$positions){
		$cost=0;
		
		//найти главную позицию
		$main_id=0; $main_index=-1;
		
		foreach($positions as $k=>$v){
			if($v['parent_id']==0){
				 $main_id=$v['pl_position_id'];	
				 $main_index=$k;
				 break;
			}
		}
		//
		//считать собственную цену гл. позиции со скидкой
		$price_f_main=$this->CalcPriceFMain($main_index, $positions);
		//echo $price_f_main; die();
		
		//считать собственную цену не главных позиций  со скидкой
		$this->CalcPriceFNotMain($main_index, $positions);
		
		$cost=$this->CalcAllCost($main_index, $positions);
		
		
		
		if($v['price_kind_id']==1){
			//наложить стоимость доставки (если у нас прайс-лист - ddpm)
			//нужно знать общую стоимость - $cost
			
			//стоимость доставки ddpm  $pl['delivery_ddpm']
			//и стоимости всех позиций $positions
			
			//найдем стоимость доставки ddpm
			$_pl=new PlPosItem;
			$pl=$_pl->GetItemById($v['pl_position_id']);
			$pl['delivery_ddpm'];
			
			
			//var_dump( $pl['delivery_ddpm']);
			
			//вызов функции наложени€ доставки на позиции
			$this->ApplyDeliveryDDPM($cost, $pl['delivery_ddpm'], $positions);
			
		}else{
			
		
		}
		
		
		//найти общую стоимость
		$cost=$this->CalcAllCost($main_index, $positions);
		
		//внести в общую стоимость главной позиции общую стоимость всего  ѕ
		$positions[$main_index]['total']=$cost;
	
		
		//пересортируем массив - главную позицию в начало
		
		$old_array=$positions;
		$new_array=array();
		
		$new_array[]=$positions[$main_index];
		
		foreach($positions as $k=>$v){
			if($v['parent_id']!=0){
				$new_array[]=$v;
			}
			
		}
		
		
			
		
		
		$positions=$new_array;
		
		return $cost;	
	}
	
	
	
	//наложение доставки DDPM
	protected function ApplyDeliveryDDPM($cost, $ddpm, &$positions){
		//обновить 	cost, total у позиции
		foreach($positions as $k=>$v){
			
			$x=$ddpm*$v['cost']/$cost;
			
			//echo "ѕозици€ $v[pl_position_id]  было $v[cost] добавим $x будет ".($positions[$k]['cost']+$x)."<br />";
			
			$positions[$k]['cost']=round($positions[$k]['cost']+$x,2);
			$positions[$k]['total']=round($positions[$k]['total']+$x,2);
		}
	}
	
	
	
	
	
	public function CalcPriceFMain($index, &$positions){

		$price=(float)$positions[$index]['price'];
		//echo $price.' ';
		
		 
			if($positions[$index]['pl_discount_rub_or_percent']==0) $price-=(float)$positions[$index]['pl_discount_value'];
			else $price-=$price*((float)$positions[$index]['pl_discount_value']/100);
		
		$positions[$index]['price_f']=round($price, 2);
		
		//$cost_main= $price*$positions[$index]['quantity'];
		$positions[$index]['cost']=round($price*$positions[$index]['quantity'], 2);
		$positions[$index]['total']=round($price*$positions[$index]['quantity'], 2);
		
		
			
		return round($price, 2);
		
	}
	
	public function CalcPriceFNotMain($index, &$positions){
		foreach($positions as $k=>$v){
			if($v['parent_id']!=0){
			  $price=(float)$positions[$k]['price'];
			  
			 /* echo '<pre>';
			  print_r($v);
			  echo '</pre>';
			  */
			  
			  //не учитывать в этом  блоке скидку менеджера, если опци€ - это ѕЌ–:	
			  if(!isset($v['is_install'])) {
				  $_pl=new PlPosItem;
				  $pl=$_pl->GetItemById($v['id']);
				  $v['is_install']=$pl['is_install'];
			  }
			  if(!isset($v['is_calc_price'])) {
				  $_pl=new PriceKindItem;
				  $pl=$_pl->GetItemById($v['price_kind_id']);
				  $v['is_calc_price']=$pl['is_calc_price'];
			  }
			  
			  
			  if(!(($v['is_install']==1)&&($v['is_calc_price']==1))){
		 		//not PNR - –аботаем как обычно
			  	if($positions[$index]['pl_discount_rub_or_percent']==0) $price-=(float)$positions[$index]['pl_discount_value'];
			  	else $price-=$price*((float)$positions[$index]['pl_discount_value']/100);
			  }else{
				  //echo 'PNR!!!!!!!!!!!';	
				  //  если скидка руководител€ - применим ее
				  if($v['pl_discount_id']==2){
					  if($v['pl_discount_rub_or_percent']==0) $price-=(float)$v['pl_discount_value'];
			  		  else $price-=$price*((float)$v['pl_discount_value']/100);
					  //echo $price;
				  }
			  }
			  $positions[$k]['price_f']=round($price, 2);	
			  $positions[$k]['price_pm']=round($price, 2);	
			  
			  $positions[$k]['cost']=round($price*$positions[$k]['quantity'], 2);
			  $positions[$k]['total']=round($price*$positions[$k]['quantity'], 2);
			  
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
	
}
?>